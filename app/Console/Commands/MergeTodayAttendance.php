<?php

namespace App\Console\Commands;

use App\Services\AttendanceMergeService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MergeTodayAttendance extends Command
{
    protected $signature = 'attendance:merge-today
                            {--date= : Specific date to merge (Y-m-d)}
                            {--all-past : Merge all past days}
                            {--clean : Clean old today records}';

    protected $description = 'Merge today\'s attendance to main attendance table';

    protected $mergeService;

    public function __construct(AttendanceMergeService $mergeService)
    {
        parent::__construct();
        $this->mergeService = $mergeService;
    }

    public function handle()
    {
        try {
            if ($this->option('clean')) {
                $deleted = $this->mergeService->cleanOldRecords(7);
                $this->info("✅ Cleaned {$deleted} old records");
                return 0;
            }

            if ($this->option('all-past')) {
                $this->info('📊 Merging all past days...');
                $result = $this->mergeService->mergeAllPastDays();
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Dates', $result['dates']],
                        ['Merged', $result['merged']],
                        ['Skipped', $result['skipped']],
                    ]
                );
                return 0;
            }

            $date = $this->option('date')
                ? Carbon::parse($this->option('date'))
                : today();

            $this->info("📅 Merging attendance for: {$date->toDateString()}");

            $result = $this->mergeService->mergeDate($date);

            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Merged', $result['merged']],
                    ['Skipped', $result['skipped']],
                ]
            );

            $this->newLine();
            $this->info('🎉 Merge completed!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Merge failed: ' . $e->getMessage());
            return 1;
        }
    }
}
