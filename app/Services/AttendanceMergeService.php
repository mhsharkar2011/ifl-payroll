<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceToday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceMergeService
{
    /**
     * Merge today's attendance to main table.
     */
    public function mergeDate(Carbon $date): array
    {
        $dateString = $date->toDateString();

        Log::info("Starting attendance merge for {$dateString}");

        $pending = AttendanceToday::where('attendance_date', $dateString)->get();

        if ($pending->isEmpty()) {
            Log::info("No records to merge for {$dateString}");
            return ['merged' => 0, 'skipped' => 0];
        }

        $merged = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($pending as $record) {
                // Check if already exists in main table
                $exists = Attendance::where('employee_id', $record->employee_id)
                    ->where('timestamp', $record->timestamp)
                    ->where('status', $record->status)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Insert into main table
                Attendance::create([
                    'employee_id'   => $record->employee_id,
                    'timestamp'     => $record->timestamp,
                    'status'        => $record->status,
                    'verified_by'   => $record->verified_by,
                    'device_serial' => $record->device_serial,
                    'ip_address'    => $record->ip_address,
                    'latitude'      => $record->latitude,
                    'longitude'     => $record->longitude,
                    'notes'         => $record->notes,
                ]);

                $merged++;
            }

            // Delete from today's table after successful merge
            AttendanceToday::where('attendance_date', $dateString)->delete();

            DB::commit();

            Log::info("Attendance merge completed", [
                'date'    => $dateString,
                'merged'  => $merged,
                'skipped' => $skipped,
            ]);

            return [
                'merged'  => $merged,
                'skipped' => $skipped,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Attendance merge failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Merge all past days (before today).
     */
    public function mergeAllPastDays(): array
    {
        $dates = AttendanceToday::where('attendance_date', '<', today())
            ->distinct()
            ->pluck('attendance_date');

        $totalMerged = 0;
        $totalSkipped = 0;

        foreach ($dates as $date) {
            try {
                $result = $this->mergeDate(Carbon::parse($date));
                $totalMerged += $result['merged'];
                $totalSkipped += $result['skipped'];
            } catch (\Exception $e) {
                Log::error("Failed to merge date {$date}: " . $e->getMessage());
            }
        }

        return [
            'dates'   => $dates->count(),
            'merged'  => $totalMerged,
            'skipped' => $totalSkipped,
        ];
    }

    /**
     * Clean old records from today's table (safety cleanup).
     */
    public function cleanOldRecords(int $daysOld = 7): int
    {
        $cutoff = today()->subDays($daysOld);

        $deleted = AttendanceToday::where('attendance_date', '<', $cutoff)->delete();

        Log::info("Cleaned {$deleted} old attendance-today records older than {$daysOld} days");

        return $deleted;
    }
}
