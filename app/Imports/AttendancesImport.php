<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

// ✅ Added WithCustomCsvSettings to handle multiple tabs
class AttendancesImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, WithCustomCsvSettings
{
    public int $imported = 0;
    public int $skipped  = 0;
    public int $failed   = 0;

    public array $importErrors = [];
    public array $notFoundEmployees = [];

    protected array $employeeMap = [];
    protected array $seenKeys = [];
    protected array $dayPunchCount = [];

    private const DUPLICATE_LOOKBACK_DAYS = 30;
    private const MAX_SEEN_KEYS           = 500_000;
    private const DEFAULT_DEVICE          = 'MB460';

    protected array $headerAliases = [
        'employee_id'   => 'employee_id',
        'employee_code' => 'employee_id',
        'emp_id'        => 'employee_id',
        'emp_code'      => 'employee_id',
        'enroll_number' => 'employee_id',
        'enrollnumber'  => 'employee_id',
        'enroll_no'     => 'employee_id',
        'ssn'           => 'employee_id',
        'user_id'       => 'employee_id',
        'userid'        => 'employee_id',
        'ac_no'         => 'employee_id',
        'acno'          => 'employee_id',
        'ac_no_'        => 'employee_id',
        'id'            => 'employee_id',
        'no'            => 'employee_id',
        'no_'           => 'employee_id',
        'pin'           => 'employee_id',

        'timestamp'       => 'timestamp',
        'stime'           => 'timestamp',
        's_time'          => 'timestamp',
        'date_time'       => 'timestamp',
        'datetime'        => 'timestamp',
        'check_time'      => 'timestamp',
        'checktime'       => 'timestamp',
        'punch_time'      => 'timestamp',
        'punchtime'       => 'timestamp',
        'attendance_time' => 'timestamp',
        'time'            => 'timestamp',

        'date'      => 'date',
        'time_only' => 'time',

        'verify_type'  => 'verify_type',
        'verifytype'   => 'verify_type',
        'verify_mode'  => 'verify_type',
        'verifymode'   => 'verify_type',
        'verification' => 'verify_type',
        'verifycode'   => 'verify_type',
        'type'         => 'verify_type',
        'mode'         => 'verify_type',
        'state'        => 'verify_type',
        'sensor'       => 'verify_type',

        'status'     => 'status',
        'punch_type' => 'status',
        'direction'  => 'status',

        'device'        => 'device_serial',
        'device_id'     => 'device_serial',
        'device_serial' => 'device_serial',
        'terminal'      => 'device_serial',
    ];

    public function __construct()
    {
        $this->primeEmployeeCache();
        $this->primeSeenKeys();
    }

    /**
     * ✅ FIX: Tell Laravel Excel how to parse the raw .txt file
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => "\t",
            'input_encoding' => 'UTF-8',
        ];
    }

    public function model(array $row): ?Attendance
    {
        $row = $this->normalizeRow($row);

        if (empty($row['employee_id'])) {
            $this->skipped++;
            return null;
        }

        $pin = $this->cleanString($row['employee_id']);
        if ($pin === '') {
            $this->skipped++;
            return null;
        }

        $timestamp = $this->parseTimestamp($row);
        if (!$timestamp) {
            $this->failed++;
            $this->importErrors[] = "Invalid timestamp for employee {$pin}";
            return null;
        }

        $employeeId = $this->employeeMap[$pin] ?? null;
        if (!$employeeId) {
            $this->failed++;
            $this->notFoundEmployees[$pin] = ($this->notFoundEmployees[$pin] ?? 0) + 1;
            return null;
        }

        $status = $this->resolveStatus($employeeId, $timestamp, $row);
        $key = $this->uniqueKey($employeeId, $timestamp, $status);

        if (isset($this->seenKeys[$key])) {
            $this->skipped++;
            return null;
        }

        $this->seenKeys[$key] = true;

        try {
            $this->imported++;

            return new Attendance([
                'employee_id'   => $employeeId,
                'timestamp'     => $timestamp,
                'status'        => $status,
                'verified_by'   => $this->parseVerifyType($row['verify_type'] ?? null),
                'device_serial' => $this->cleanString($row['device_serial'] ?? self::DEFAULT_DEVICE),
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                $this->imported--;
                $this->skipped++;
                return null;
            }
            throw $e;
        }
    }

    public function chunkSize(): int { return 500; }
    public function batchSize(): int { return 500; }

    protected function primeEmployeeCache(): void
    {
        $this->employeeMap = Employee::query()
            ->whereNotNull('employee_id')
            ->get(['id', 'employee_id', 'device_user_id'])
            ->flatMap(function ($e) {
                $map = [];
                if ($e->employee_id) {
                    $map[trim((string) $e->employee_id)] = $e->id;
                }
                if (!empty($e->device_user_id)) {
                    $map[trim((string) $e->device_user_id)] = $e->id;
                }
                return $map;
            })
            ->all();
    }

    protected function primeSeenKeys(): void
    {
        $cutoff = now()->subDays(self::DUPLICATE_LOOKBACK_DAYS)->startOfDay();

        Attendance::query()
            ->where('timestamp', '>=', $cutoff)
            ->select('employee_id', 'timestamp', 'status')
            ->chunk(5000, function ($rows) {
                foreach ($rows as $r) {
                    if (count($this->seenKeys) >= self::MAX_SEEN_KEYS) {
                        return false;
                    }
                    $ts = $r->timestamp instanceof Carbon
                        ? $r->timestamp
                        : Carbon::parse($r->timestamp);

                    $this->seenKeys[
                        $this->uniqueKey($r->employee_id, $ts, $r->status)
                    ] = true;
                }
            });
    }

    protected function resolveStatus(int $employeeId, Carbon $timestamp, array $row): string
    {
        if (!empty($row['status'])) {
            $explicit = $this->mapStatusValue($row['status']);
            if ($explicit !== null) {
                $dayKey = $employeeId . '|' . $timestamp->toDateString();
                $this->dayPunchCount[$dayKey] = ($this->dayPunchCount[$dayKey] ?? 0) + 1;
                return $explicit;
            }
        }
        return $this->inferStatus($employeeId, $timestamp);
    }

    protected function inferStatus(int $employeeId, Carbon $timestamp): string
    {
        $dayKey = $employeeId . '|' . $timestamp->toDateString();

        if (!isset($this->dayPunchCount[$dayKey])) {
            $this->dayPunchCount[$dayKey] = Attendance::where('employee_id', $employeeId)
                ->whereBetween('timestamp', [
                    $timestamp->copy()->startOfDay(),
                    $timestamp->copy()->endOfDay(),
                ])
                ->where('timestamp', '<', $timestamp)
                ->count();
        }

        $priorCount = $this->dayPunchCount[$dayKey];
        $this->dayPunchCount[$dayKey] = $priorCount + 1;

        return ($priorCount % 2 === 0) ? 'check_in' : 'check_out';
    }

    protected function mapStatusValue($value): ?string
    {
        if ($value === null || $value === '') return null;

        $v = strtolower(trim((string) $value));

        if (in_array($v, [
            'check_in', 'check_out',
            'break_in', 'break_out',
            'overtime_in', 'overtime_out',
        ], true)) {
            return $v;
        }

        if (is_numeric($v)) {
            return match ((int) $v) {
                0 => 'check_in',
                1 => 'check_out',
                2 => 'break_out',
                3 => 'break_in',
                4 => 'overtime_in',
                5 => 'overtime_out',
                default => null,
            };
        }

        $v = str_replace(['-', '/', ' '], '_', $v);

        return match (true) {
            $v === 'in',        $v === 'c_in',  $v === 'checkin'  => 'check_in',
            $v === 'out',       $v === 'c_out', $v === 'checkout' => 'check_out',
            $v === 'break_in',  $v === 'breakin'                    => 'break_in',
            $v === 'break_out', $v === 'breakout'                   => 'break_out',
            $v === 'ot_in',     $v === 'overtimein'                 => 'overtime_in',
            $v === 'ot_out',    $v === 'overtimeout'                => 'overtime_out',
            str_contains($v, 'break') && str_contains($v, 'in')     => 'break_in',
            str_contains($v, 'break') && str_contains($v, 'out')    => 'break_out',
            str_contains($v, 'ot') && str_contains($v, 'in')        => 'overtime_in',
            str_contains($v, 'ot') && str_contains($v, 'out')       => 'overtime_out',
            str_contains($v, 'in')                                  => 'check_in',
            str_contains($v, 'out')                                 => 'check_out',
            default                                                 => null,
        };
    }

    protected function normalizeRow(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            $k = strtolower(trim((string) $key));
            $k = str_replace([' ', '-', '.', '/'], '_', $k);
            $k = preg_replace('/_+/', '_', $k);
            $k = trim($k, '_');

            $k = $this->headerAliases[$k] ?? $k;
            $out[$k] = $value;
        }
        return $out;
    }

    protected function parseTimestamp(array $row): ?Carbon
    {
        try {
            $raw = $row['timestamp'] ?? null;

            if (!$raw && !empty($row['date'])) {
                $raw = trim(($row['date'] ?? '') . ' ' . ($row['time'] ?? ''));
            }

            if ($raw === null || $raw === '') {
                return null;
            }

            if (is_numeric($raw) && !str_contains((string) $raw, '-')) {
                try {
                    return Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)
                    );
                } catch (\Throwable $e) {
                }
            }

            $raw = trim((string) $raw);

            $formats = [
                'Y-m-d H:i:s',
                'Y-m-d H:i', // ✅ ADDED: Supports your raw format without seconds
                'Y-m-d\TH:i:s',
                'Y/m/d H:i:s', 'Y/m/d H:i',
                'd-m-Y H:i:s', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd.m.Y H:i:s',
                'm/d/Y H:i:s', 'm/d/Y H:i',
                'd-M-y h:i:s A', 'd-M-Y h:i:s A',
                'd-M-y H:i:s', 'd-M-Y H:i:s',
                'd-M-y h:i A', 'd-M-Y h:i A',
            ];

            foreach ($formats as $fmt) {
                try {
                    $dt = Carbon::createFromFormat($fmt, $raw);
                    if ($dt !== false) return $dt;
                } catch (\Throwable $e) {
                }
            }

            try {
                return Carbon::parse($raw);
            } catch (\Throwable $e) {
                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('Attendance import: timestamp parse failed', [
                'error' => $e->getMessage(),
                'row'   => $row,
            ]);
            return null;
        }
    }

    protected function parseVerifyType($value): int
    {
        if ($value === null || $value === '') return 0;
        $v = strtolower(trim((string) $value));
        if (str_contains($v, 'face'))   return 2;
        if (str_contains($v, 'finger')) return 1;
        if (str_contains($v, 'card'))   return 3;
        if (str_contains($v, 'pass'))   return 0;
        if (str_contains($v, 'manual')) return 0;
        if (str_contains($v, 'other'))  return 0;
        return match ((int) $value) {
            1 => 1, 2 => 2, 3 => 3, 4 => 0, default => 0,
        };
    }

    protected function cleanString($value): string
    {
        if ($value === null) return '';
        if (is_numeric($value)) {
            $s = (string) $value;
            if (str_contains($s, '.')) {
                $s = rtrim(rtrim($s, '0'), '.');
            }
            return $s;
        }
        return trim((string) $value);
    }

    protected function uniqueKey(int $employeeId, Carbon $timestamp, string $status): string
    {
        return $employeeId . '|' . $timestamp->format('Y-m-d H:i:s') . '|' . $status;
    }
}
