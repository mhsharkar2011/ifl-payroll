<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceToday;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZKTecoPushController extends Controller
{
    /* ============================================================
     |  INITIAL HANDSHAKE
     |  Device calls this first when connecting.
     |  URL: GET /iclock/cdata?SN={serial}&options=all
     * ============================================================ */
    public function handshake(Request $request)
    {
        $serial = $request->query('SN');
        $options = $request->query('options');

        Log::info('ZKTeco Push: Handshake', [
            'serial' => $serial,
            'options' => $options,
            'ip' => $request->ip(),
        ]);

        // Register / Update device last_sync
        $device = Device::where('serial_number', $serial)->first();
        if ($device) {
            $device->update(['last_sync_at' => now()]);
        }

        // Response format required by ZKTeco
        $response = "GET OPTION FROM: {$serial}\n";
        $response .= "Stamp=" . now()->format('YmdHis') . "\n";
        $response .= "OpStamp=" . now()->format('YmdHis') . "\n";
        $response .= "ErrorDelay=30\n";
        $response .= "Delay=10\n";
        $response .= "TransTimes=00:00;14:05\n";
        $response .= "TransInterval=1\n";
        $response .= "TransFlag=1111000000\n";
        $response .= "TimeZone=6\n";
        $response .= "Realtime=1\n";
        $response .= "Encrypt=0\n";

        return response($response, 200)
            ->header('Content-Type', 'text/plain');
    }

    /* ============================================================
     |  RECEIVE DATA
     |  Device POSTs attendance/user data here.
     |  URL: POST /iclock/cdata?SN={serial}&table=ATTLOG
     * ============================================================ */
    public function cdata(Request $request)
    {
        $serial = $request->query('SN');
        $table = $request->query('table');
        $rawBody = $request->getContent();

        Log::info('ZKTeco Push: Data received', [
            'serial' => $serial,
            'table' => $table,
            'size' => strlen($rawBody),
        ]);

        if ($table === 'ATTLOG') {
            $this->processAttendanceLog($serial, $rawBody);
        } elseif ($table === 'OPERLOG') {
            $this->processOperationLog($serial, $rawBody);
        } elseif ($table === 'USERINFO') {
            $this->processUserInfo($serial, $rawBody);
        }

        // Update device last sync
        $device = Device::where('serial_number', $serial)->first();
        if ($device) {
            $device->update(['last_sync_at' => now()]);
        }

        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }

    /* ============================================================
     |  DEVICE POLLS FOR COMMANDS
     |  Device checks if server has any commands for it.
     |  URL: GET /iclock/getrequest?SN={serial}
     * ============================================================ */
    public function getRequest(Request $request)
    {
        $serial = $request->query('SN') ?? $request->input('SN');

        if (!$serial) {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $device = Device::where('serial_number', $serial)->first();

        // ★ ADD THIS GUARD
        if (!$device || !$device->serial_number) {
            Log::warning('ZKTecoPush: unknown or incomplete device', [
                'sn'  => $serial,
                'ip'  => $request->ip(),
                'db_serial' => $device?->serial_number,
            ]);
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        // Update last_seen_at so Devices page flips to Online
        $device->forceFill(['last_seen_at' => now()])->saveQuietly();

        // Safely fetch next command
        $cmd = DeviceCommand::nextFor($device->serial_number);

        if (!$cmd) {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $cmd->forceFill(['status' => 'sent', 'sent_at' => now()])->saveQuietly();

        return response("C:{$cmd->id}:{$cmd->command}", 200)
            ->header('Content-Type', 'text/plain');
    }
    /* ============================================================
     |  COMMAND ACKNOWLEDGMENT
     |  Device confirms command completion.
     |  URL: POST /iclock/devicecmd?SN={serial}
     * ============================================================ */
    public function deviceCmd(Request $request)
    {
        $serial = $request->query('SN');
        $body = $request->getContent();

        Log::info('ZKTeco Push: Command ack', [
            'serial' => $serial,
            'body' => $body,
        ]);

        // Parse: ID={id}&Return={code}&CMD={cmd}
        parse_str($body, $params);
        $commandId = $params['ID'] ?? null;
        $returnCode = $params['Return'] ?? 0;

        if ($commandId) {
            $command = DeviceCommand::find($commandId);
            if ($command) {
                $command->update([
                    'status' => $returnCode == 0 ? 'completed' : 'failed',
                    'completed_at' => now(),
                ]);
            }
        }

        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }

    /* ============================================================
     |  PROCESS ATTENDANCE LOG
     * ============================================================ */
    protected function processAttendanceLog(string $serial, string $rawBody): void
    {
        $lines = preg_split('/[\r\n]+/', trim($rawBody));
        $synced = 0;
        $failed = 0;

        $employees = Employee::whereNotNull('device_user_id')
            ->get()
            ->keyBy('device_user_id');

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = preg_split('/\t+/', $line);

            if (count($parts) < 2) {
                Log::warning('Invalid ATTLOG line', ['line' => $line]);
                $failed++;
                continue;
            }

            try {
                $uid = trim($parts[0]);
                $timestamp = trim($parts[1]);
                $status = (int) ($parts[2] ?? 0);
                $verifyType = (int) ($parts[3] ?? 0);

                $employee = $employees->get($uid);

                if (!$employee) {
                    Log::warning("Employee not found for device_user_id: {$uid}");
                    $failed++;
                    continue;
                }

                $carbonTimestamp = Carbon::parse($timestamp);

                // ✅ Record in TODAY's table (fast)
                $attendance = AttendanceToday::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'timestamp'   => $carbonTimestamp,
                        'status'      => $this->mapStatus($status),
                    ],
                    [
                        'verified_by'     => $verifyType,
                        'device_serial'   => $serial,
                        'ip_address'      => request()->ip(),
                        'attendance_date' => $carbonTimestamp->toDateString(),
                    ]
                );

                if ($attendance->wasRecentlyCreated) {
                    $synced++;
                    Log::info("✅ Today attendance created", [
                        'employee' => $employee->first_name,
                        'time' => $carbonTimestamp->format('Y-m-d H:i:s'),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to process attendance line', [
                    'line' => $line,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        Log::info("Push ATTLOG processed", [
            'synced' => $synced,
            'failed' => $failed,
            'total' => count($lines),
        ]);
    }

    /* ============================================================
     |  PROCESS OPERATION LOG
     * ============================================================ */
    protected function processOperationLog(string $serial, string $rawBody): void
    {
        Log::info('Operation log received', ['body' => $rawBody]);
        // Can be extended for real-time monitoring
    }

    /* ============================================================
     |  PROCESS USER INFO
     * ============================================================ */
    protected function processUserInfo(string $serial, string $rawBody): void
    {
        Log::info('User info received', ['body' => $rawBody]);
        // Can be extended to sync users from device
    }

    /* ============================================================
     |  STATUS MAPPER
     * ============================================================ */
    protected function mapStatus(int $status): string
    {
        // ZKTeco Push SDK status codes
        return match ($status) {
            0 => 'check_in',
            1 => 'check_out',
            2 => 'break_in',
            3 => 'break_out',
            4 => 'overtime_in',
            5 => 'overtime_out',
            default => 'check_in',
        };
    }
}
