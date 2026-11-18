<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report
                            {--date= : Generate report for specific date (Y-m-d)}
                            {--export : Export report to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily attendance report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $date = $this->option('date')
                ? Carbon::parse($this->option('date'))
                : Carbon::today();

            $this->info("📊 Generating attendance report for {$date->format('Y-m-d')}");
            $this->newLine();

            // Get attendance data
            $present = Attendance::whereDate('timestamp', $date)
                ->where('status', 'check_in')
                ->distinct('employee_id')
                ->count();

            $total = Employee::count();
            $absent = $total - $present;

            // Get check-in and check-out counts
            $checkIns = Attendance::whereDate('timestamp', $date)
                ->where('status', 'check_in')
                ->count();

            $checkOuts = Attendance::whereDate('timestamp', $date)
                ->where('status', 'check_out')
                ->count();

            $report = [
                'date' => $date->format('Y-m-d'),
                'total_employees' => $total,
                'present' => $present,
                'absent' => $absent,
                'check_ins' => $checkIns,
                'check_outs' => $checkOuts,
                'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];

            // Display report
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Date', $report['date']],
                    ['Total Employees', $report['total_employees']],
                    ['Present', $report['present']],
                    ['Absent', $report['absent']],
                    ['Attendance Rate', $report['attendance_rate'] . '%'],
                    ['Total Check-ins', $report['check_ins']],
                    ['Total Check-outs', $report['check_outs']],
                ]
            );

            // Log report
            Log::info('Daily Attendance Report', $report);

            // Export if requested
            if ($this->option('export')) {
                $filename = "attendance_report_{$date->format('Y-m-d')}.json";
                $path = storage_path("app/reports/{$filename}");

                if (!is_dir(storage_path('app/reports'))) {
                    mkdir(storage_path('app/reports'), 0755, true);
                }

                file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT));
                $this->info("✅ Report exported to: {$path}");
            }

            $this->newLine();
            $this->info('🎉 Report generated successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to generate report: ' . $e->getMessage());
            Log::error('Report generation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
