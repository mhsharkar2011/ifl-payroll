<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Device;
use App\Models\DeviceCommand;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IclockController extends Controller
{
    /** How many pending commands to hand out per heartbeat. */
    private const COMMANDS_PER_POLL = 10;

    /** Reject any body larger than this (safety against malformed devices). */
    private const MAX_BODY_BYTES = 5 * 1024 * 1024; // 5 MB

    /* ==================================================================
     |  Handshake — GET /iclock/cdata?SN=...&options=all
     | ================================================================== */

    public function handshake(Request $request)
    {
        $device = $this->touchDevice($request);

        if ($device) {
            $device->forceFill(['ip_address' => $request->ip()])->saveQuietly();
        }

        $this->log('handshake', $request, $device);

        $sn = $request->query('SN', '');

        $response = implode("\r\n", [
            "GET OPTION FROM: {$sn}",
            'Stamp=9999',
            'OpStamp=9999',
            'ErrorDelay=30',
            'Delay=10',             // heartbeat every 10s
            'TransTimes=00:00;14:05',
            'TransInterval=1',
            'TransFlag=TransData AttLog',
            'TimeZone=6',           // Bangladesh = UTC+6
            'Realtime=1',           // push attendance immediately
            'Encrypt=0',
        ]);

        return response($response, 200)
            ->header('Content-Type', 'text/plain');
    }

    /* ==================================================================
     |  Attendance push — POST /iclock/cdata?SN=...&table=ATTLOG
     | ================================================================== */

    public function cdata(Request $request)
    {
        $device = $this->touchDevice($request);
        $table  = $request->query('table', 'ATTLOG');

        $this->log("cdata:{$table}", $request, $device);

        // Only ATTLOG is handled here; other tables just get an OK so the
        // device clears its buffer.
        if ($table !== 'ATTLOG') {
            return $this->textResponse(
                'OK: ' . $this->countLines($request->getContent())
            );
        }

        $body = $request->getContent();

        if (empty(trim($body))) {
            return $this->textResponse('OK: 0');
        }

        if (strlen($body) > self::MAX_BODY_BYTES) {
            Log::warning('iclock:cdata body exceeds limit', [
                'serial' => $request->query('SN'),
                'bytes'  => strlen($body),
            ]);
            return $this->textResponse('ERROR', 413);
        }

        $deviceSerial = $request->query('SN')
            ?? $device?->serial_number
            ?? 'UNKNOWN';

        $lines = preg_split('/\r\n|\r|\n/', trim($body));

        $stored  = 0;
        $skipped = 0;
        $failed  = 0;

        // Pre-load employee map once — avoids an N+1 per punch.
        $pins = [];
        foreach ($lines as $line) {
            if ($line === '') continue;
            $parts = preg_split('/\t+/', trim($line));
            if (!empty($parts[0])) $pins[$parts[0]] = true;
        }

        $employeeMap = Employee::whereIn('employee_id', array_keys($pins))
            ->pluck('id', 'employee_id')
            ->all();

        DB::beginTransaction();
        try {
            foreach ($lines as $line) {
                if (trim($line) === '') continue;

                $parsed = $this->parseAttLogLine($line);
                if (!$parsed) {
                    $skipped++;
                    continue;
                }

                $employeeId = $employeeMap[$parsed['pin']] ?? null;

                if (!$employeeId) {
                    // Unknown pin — record the punch anyway with employee_id = null?
                    // No — FK constraint requires a valid employee. Skip and log.
                    $skipped++;
                    Log::warning('iclock:cdata unknown pin', [
                        'serial' => $deviceSerial,
                        'pin'    => $parsed['pin'],
                    ]);
                    continue;
                }

                try {
                    Attendance::create([
                        'employee_id'   => $employeeId,
                        'device_serial' => $deviceSerial,
                        'timestamp'     => $parsed['timestamp'],
                        'status'        => $this->mapStatus($parsed['status']),
                        'verified_by'   => $parsed['verify'],
                    ]);
                    $stored++;
                } catch (QueryException $e) {
                    // 23000 = integrity constraint violation (unique key)
                    // Duplicate punch — safe to ignore.
                    if ($e->getCode() === '23000') {
                        $skipped++;
                    } else {
                        throw $e;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('iclock:cdata insert failed', [
                        'serial' => $deviceSerial,
                        'pin'    => $parsed['pin'],
                        'error'  => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('iclock:cdata transaction failed', [
                'serial' => $deviceSerial,
                'error'  => $e->getMessage(),
                'body'   => substr($body, 0, 500),
            ]);
            return $this->textResponse('ERROR', 500);
        }

        // Only stamp last_sync_at when we actually stored data.
        if ($device && $stored > 0) {
            $device->forceFill(['last_sync_at' => now()])->saveQuietly();
        }

        Log::info("iclock:cdata stored={$stored} skipped={$skipped} failed={$failed}", [
            'serial' => $deviceSerial,
        ]);

        // ZKTeco expects "OK: <lines_received>"
        return $this->textResponse('OK: ' . ($stored + $skipped));
    }

    /* ==================================================================
     |  Heartbeat + command poll — GET /iclock/getrequest?SN=...
     | ================================================================== */

    // public function getRequest(Request $request)
    // {
    //     $device = $this->touchDevice($request);

    //     $this->log('getrequest', $request, $device);

    //     if (!$device) {
    //         return $this->textResponse('OK');
    //     }

    //     $commands = DeviceCommand::where('device_id', $device->id)
    //         ->where('status', 'pending')
    //         ->orderBy('id')
    //         ->limit(self::COMMANDS_PER_POLL)
    //         ->get();

    //     if ($commands->isEmpty()) {
    //         return $this->textResponse('OK');
    //     }

    //     $lines = [];
    //     foreach ($commands as $cmd) {
    //         $lines[] = "C:{$cmd->id}:{$cmd->command}";

    //         $cmd->forceFill([
    //             'status'  => 'sent',
    //             'sent_at' => now(),
    //         ])->saveQuietly();
    //     }

    //     return $this->textResponse(implode("\r\n", $lines));
    // }

    public function getRequest(Request $request)
    {
        $device = $this->touchDevice($request);
        $this->log('getrequest', $request, $device);

        if (!$device) {
            return $this->textResponse('OK');
        }

        // ★ Use device_serial + atomic claim
        $commands = DeviceCommand::claimForDevice(
            $device->serial_number,
            self::COMMANDS_PER_POLL
        );

        if (empty($commands)) {
            return $this->textResponse('OK');
        }

        $lines = [];
        foreach ($commands as $cmd) {
            $lines[] = "C:{$cmd->id}:{$cmd->command}";
        }

        return $this->textResponse(implode("\r\n", $lines));
    }

    /* ==================================================================
     |  Command confirmation — POST /iclock/devicecmd?SN=...
     | ================================================================== */

    public function deviceCmd(Request $request)
    {
        $device = $this->touchDevice($request);
        $this->log('devicecmd', $request, $device);

        $body = $request->getContent();
        parse_str($body, $params);

        $commandId  = $params['ID']     ?? null;
        $returnCode = $params['Return'] ?? null;

        if ($commandId) {
            $cmd = DeviceCommand::find($commandId);
            if ($cmd) {
                $cmd->forceFill([
                    'status'       => ((int) $returnCode === 0) ? 'done' : 'failed',
                    'return_code'  => $returnCode,
                    'completed_at' => now(),
                ])->saveQuietly();
            }
        }

        return $this->textResponse('OK');
    }

    /* ==================================================================
     |  Misc endpoints
     | ================================================================== */

    public function ping(Request $request)
    {
        $this->touchDevice($request);
        return $this->textResponse('OK');
    }

    public function test(Request $request)
    {
        $this->touchDevice($request);
        return $this->textResponse('OK');
    }

    /* ==================================================================
     |  Helpers
     | ================================================================== */

    /**
     * Update device.last_seen_at on every SDK request.
     * Drives the Online / Offline indicator on the admin page.
     */
    protected function touchDevice(Request $request): ?Device
    {
        $serial = trim((string) ($request->query('SN') ?? $request->input('SN')));

        if ($serial === '') return null;

        $device = Device::where('serial_number', $serial)->first();

        if (!$device) {
            Log::warning('iclock:touch — unknown serial', [
                'sn' => $serial,
                'ip' => $request->ip(),
            ]);
            return null;
        }

        $device->forceFill(['last_seen_at' => now()])->saveQuietly();

        return $device;
    }

    /**
     * Parse one ATTLOG line.
     *
     * Format: PIN \t Timestamp \t Status \t Verify \t WorkCode \t Reserved1 \t Reserved2
     * Example: 1001\t2026-10-07 09:15:42\t0\t1\t0\t0\t0
     */
    protected function parseAttLogLine(string $line): ?array
    {
        $parts = preg_split('/\t+/', trim($line));
        if (count($parts) < 2) return null;

        $pin = trim((string) ($parts[0] ?? ''));
        if ($pin === '') return null;

        try {
            $timestamp = Carbon::parse(trim($parts[1]));
        } catch (\Throwable $e) {
            return null;
        }

        return [
            'pin'        => $pin,
            'timestamp'  => $timestamp,
            'status'     => isset($parts[2]) ? (int) $parts[2] : 0,
            'verify'     => isset($parts[3]) ? (int) $parts[3] : 0,
            'work_code'  => isset($parts[4]) && $parts[4] !== '' ? $parts[4] : null,
        ];
    }

    /**
     * ZKTeco status code → our status string.
     *
     *   0 = Check-In
     *   1 = Check-Out
     *   2 = Break-Out
     *   3 = Break-In
     *   4 = OT-In
     *   5 = OT-Out
     */
    protected function mapStatus(int $code): string
    {
        return match ($code) {
            0 => 'check_in',
            1 => 'check_out',
            2 => 'break_out',
            3 => 'break_in',
            4 => 'overtime_in',
            5 => 'overtime_out',
            default => 'check_in',
        };
    }

    protected function countLines(string $body): int
    {
        $body = trim($body);
        if ($body === '') return 0;
        return count(preg_split('/\r\n|\r|\n/', $body));
    }

    /**
     * Standard plain-text response.
     */
    protected function textResponse(string $body, int $status = 200)
    {
        return response($body, $status)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Structured log to the dedicated iclock channel.
     */
    protected function log(string $tag, Request $request, ?Device $device): void
    {
        Log::channel('iclock')->info("[iclock:{$tag}]", [
            'serial' => $request->query('SN'),
            'device' => $device?->id,
            'ip'     => $request->ip(),
            'query'  => $request->query(),
            'body'   => substr($request->getContent(), 0, 500),
        ]);
    }
}
