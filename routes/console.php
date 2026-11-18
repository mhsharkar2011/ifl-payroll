<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncAttendanceJob;
use App\Jobs\ProcessPayrollJob;
use App\Jobs\CreateBackupJob;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| এখানে সব scheduled tasks defined করা আছে। Windows এ চালানোর জন্য
| Task Scheduler এ `php artisan schedule:run` প্রতি মিনিটে চালান।
|
| অথবা development এ: `php artisan schedule:work`
|
*/

/*
|--------------------------------------------------------------------------
| Attendance Sync
|--------------------------------------------------------------------------
| প্রতি ৩০ মিনিটে ZKTeco machine থেকে attendance sync
*/
// Schedule::job(new SyncAttendanceJob())
//     // ->everyThirtyMinutes()
//     ->everyMinute()
//     ->name('sync_attendance')
//     ->withoutOverlapping()
//     ->onOneServer();

/*
|--------------------------------------------------------------------------
| Payroll Processing
|--------------------------------------------------------------------------
| প্রতি মাসের ১ তারিখে রাত ১২টায় payroll process
*/
// Schedule::job(new ProcessPayrollJob())
//     ->monthlyOn(1, '00:00')
//     ->name('process_payroll')
//     ->withoutOverlapping()
//     ->onOneServer();

/*
|--------------------------------------------------------------------------
| Daily Attendance Report
|--------------------------------------------------------------------------
| প্রতি দিন রাত ১১:৫৯ এ report generate
*/
Schedule::command('attendance:generate-report')
    // ->dailyAt('23:59')
    ->everyMinute()
    ->name('daily_report')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Database Backup
|--------------------------------------------------------------------------
| প্রতি রাতে ২টায় automatic database backup
*/
Schedule::job(new CreateBackupJob('auto', 'Daily automatic backup'))
    // ->dailyAt('02:00')
    ->dailyAt('14:00')
    ->name('daily_backup')
    ->withoutOverlapping()
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| Cleanup Old Backups
|--------------------------------------------------------------------------
| প্রতি সপ্তাহে ৩০ দিনের পুরানো backup delete
*/
Schedule::call(function () {
    $count = app(\App\Services\BackupService::class)->cleanOldBackups(30);
    Log::info("Cleaned {$count} old backups");
})
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->name('cleanup_old_backups')
    ->withoutOverlapping();


/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Sync attendance every 30 minutes
Schedule::job(new SyncAttendanceJob())
    ->everyThirtyMinutes()
    ->name('sync_attendance')
    ->withoutOverlapping();

// Process payroll on 1st of every month
// Schedule::job(new ProcessPayrollJob())
//     ->monthlyOn(1, '00:00')
//     ->name('process_payroll')
//     ->withoutOverlapping();

// ✅ Merge today's attendance at 23:59
// Schedule::command('attendance:merge-today')
//     ->dailyAt('23:59')
//     ->name('merge_today_attendance')
//     ->withoutOverlapping();

// ✅ Clean old records weekly
// Schedule::command('attendance:merge-today --clean')
//     ->weekly()
//     ->sundays()
//     ->at('03:00')
//     ->name('clean_attendance_today')
//     ->withoutOverlapping();

// Daily database backup
Schedule::job(new CreateBackupJob('auto', 'Daily automatic backup'))
    ->dailyAt('14:00')
    ->name('daily_backup')
    ->withoutOverlapping();
