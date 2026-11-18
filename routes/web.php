<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Api\ZKTecoPushController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\OffDayDutyController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\TodayAttendanceController;
use App\Http\Controllers\Admin\UserSettingController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\IclockController;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return inertia('Welcome', [
        'canLogin'      => true,
        'canRegister'   => true,
        'laravelVersion' => app()->version(),
        'phpVersion'    => phpversion(),
        'auth'          => ['user' => auth()->user()],
    ]);
})->name('home');


Route::get('/storage/{path}', function ($path) {
    // Check if the file exists in the public storage disk
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    // Return the file directly
    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*')->name('storage.local');


/*
|--------------------------------------------------------------------------
| Dashboard Redirect (Role Based)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('employee.dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected by Auth + Role)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:Admin|HR|Manager'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ---- Departments ----
        Route::resource('departments', DepartmentController::class)
            ->only(['index', 'create', 'store', 'show', 'edit']);
        Route::post('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::post('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');

        // ---- Designations ----
        Route::resource('designations', DesignationController::class)
            ->only(['index', 'create', 'store', 'show', 'edit']);
        Route::post('designations/{designation}', [DesignationController::class, 'update'])->name('designations.update');
        Route::post('designations/{designation}/toggle-status', [DesignationController::class, 'toggleStatus'])->name('designations.toggle-status');

        // ---- Employees ----
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/',                              [EmployeeController::class, 'index'])->name('index');
            Route::get('/create',                        [EmployeeController::class, 'create'])->name('create');
            Route::post('/',                             [EmployeeController::class, 'store'])->name('store');

            // Literal sub-routes first
            Route::get('/bulk-upload',                   [EmployeeController::class, 'bulkUpload'])->name('bulk-upload');
            Route::get('/download-template',             [EmployeeController::class, 'downloadTemplate'])->name('download-template');
            Route::post('/import',                       [EmployeeController::class, 'import'])->name('import');
            Route::get('/analytics',                     [EmployeeController::class, 'analytics'])->name('analytics');
            Route::get('/analytics/export',              [EmployeeController::class, 'exportAnalytics'])->name('analytics.export');

            // Wildcard routes last
            Route::get('/{employee}',                    [EmployeeController::class, 'show'])->name('show');
            Route::get('/{employee}/edit',               [EmployeeController::class, 'edit'])->name('edit');
            Route::post('/{employee}',                   [EmployeeController::class, 'update'])->name('update');
            Route::post('/{employee}/delete',            [EmployeeController::class, 'destroy'])->name('destroy');
            Route::post('/{employee}/toggle-status',     [EmployeeController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{employee}/reset-device-id',   [EmployeeController::class, 'resetDeviceId'])->name('reset-device-id');
        });

        Route::prefix('salary')->name('salary.')->group(function () {
            Route::get('/',                 [SalaryController::class, 'index'])->name('index');
            Route::get('/create',           [SalaryController::class, 'create'])->name('create');
            Route::post('/',                [SalaryController::class, 'store'])->name('store');
            Route::get('/{salary}/edit',    [SalaryController::class, 'edit'])->name('edit');
            Route::put('/{salary}',         [SalaryController::class, 'update'])->name('update');
            Route::delete('/{salary}',      [SalaryController::class, 'destroy'])->name('destroy');
        });

        /*
        |----------------------------------------------------------------------
        | Attendance
        |----------------------------------------------------------------------
        */
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/today',             [TodayAttendanceController::class, 'index'])->name('today');
            Route::post('/today/sync',       [TodayAttendanceController::class, 'sync'])->name('today.sync');
            Route::get('/today/sync-status', [TodayAttendanceController::class, 'syncStatus'])->name('today.sync-status');
            Route::get('/',             [AttendanceController::class, 'index'])->name('index');
            Route::get('/create',       [AttendanceController::class, 'create'])->name('create');
            Route::post('/',            [AttendanceController::class, 'store'])->name('store');

            Route::get('/data',         [AttendanceController::class, 'getData'])->name('data');
            Route::get('/sync-status',  [AttendanceController::class, 'syncStatus'])->name('sync.status');
            Route::get('/bulk-upload',  [AttendanceController::class, 'bulkUpload'])->name('bulk-upload');
            Route::get('/download-template', [AttendanceController::class, 'downloadTemplate'])->name('download-template');

            Route::post('/sync',        [AttendanceController::class, 'sync'])->name('sync');
            Route::post('/export',      [AttendanceController::class, 'export'])->name('export');
            Route::post('/import',      [AttendanceController::class, 'import'])->name('import');
            Route::post('/bulk-delete', [AttendanceController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/monthly/export', [AttendanceController::class, 'exportMonthly'])->name('monthly.export');
            Route::get('/monthly',        [AttendanceController::class, 'monthly'])->name('monthly');

            Route::post('/{device}/sync-device', [AttendanceController::class, 'syncDevice'])->name('sync.device');
            Route::get('/{attendance}/edit',     [AttendanceController::class, 'edit'])->name('edit');
            // Route::post('/{attendance}',         [AttendanceController::class, 'update'])->name('update');
            Route::put('/{attendance}',          [AttendanceController::class, 'update'])->name('update');
            Route::post('/{attendance}/delete',  [AttendanceController::class, 'destroy'])->name('destroy');
            Route::get('/{attendance}',          [AttendanceController::class, 'show'])->name('show');
        });

        /*
        |----------------------------------------------------------------------
        | Payroll
        |----------------------------------------------------------------------
        */
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/',                          [PayrollController::class, 'index'])->name('index');
            Route::post('/generate',                  [PayrollController::class, 'generate'])->name('generate');
            // Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('generate.submit');
            Route::get('/{payroll}',     [PayrollController::class, 'show'])->name('show');
            Route::get('/{payroll}/edit', [PayrollController::class, 'edit'])->name('edit');
            Route::put('/{payroll}',     [PayrollController::class, 'update'])->name('update');
            Route::delete('/{payroll}',  [PayrollController::class, 'destroy'])->name('destroy');

            Route::post('/process',                  [PayrollController::class, 'process'])->name('process');
            Route::post('/bulk-process',             [PayrollController::class, 'bulkProcess'])->name('bulk-process');
            Route::post('/bulk-approve',             [PayrollController::class, 'bulkApprove'])->name('bulk-approve');

            Route::get('/{payroll}/slip',            [PayrollController::class, 'slip'])->name('slip');
            Route::post('/{payroll}/approve',        [PayrollController::class, 'approve'])->name('approve');
            Route::post('/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('mark-paid');
            // Route::post('/{payroll}/mark-as-paid',   [PayrollController::class, 'markAsPaid'])->name('mark-as-paid');
            // Route::post('/{payroll}/delete',         [PayrollController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('off-day-duties')->name('off-day-duties.')->group(function () {
            Route::get('/', [OffDayDutyController::class, 'index'])->name('index');
            Route::post('/', [OffDayDutyController::class, 'store'])->name('store');
            Route::get('/hours', [OffDayDutyController::class, 'getHoursFromAttendance'])->name('hours');
            Route::put('/{offDayDuty}', [OffDayDutyController::class, 'update'])->name('update');
            Route::delete('/{offDayDuty}', [OffDayDutyController::class, 'destroy'])->name('destroy');
            Route::get('/report', [OffDayDutyController::class, 'printReport'])->name('report');
        });

        /*
        |----------------------------------------------------------------------
        | Devices (ZKTeco)
        |----------------------------------------------------------------------
        */
        Route::prefix('devices')->name('devices.')->group(function () {
            Route::get('/',                  [DeviceController::class, 'index'])->name('index');
            Route::get('/create',            [DeviceController::class, 'create'])->name('create');
            Route::post('/',                 [DeviceController::class, 'store'])->name('store');
            Route::get('/ping-all', [DeviceController::class, 'pingAll'])->name('ping-all');
            Route::get('/{device}',          [DeviceController::class, 'show'])->name('show');
            Route::get('/{device}/edit',     [DeviceController::class, 'edit'])->name('edit');
            Route::post('/{device}',         [DeviceController::class, 'update'])->name('update');
            Route::post('/{device}/delete',  [DeviceController::class, 'destroy'])->name('destroy');

            Route::post('/{device}/test',         [DeviceController::class, 'testConnection'])->name('test');
            Route::post('/{device}/sync',         [DeviceController::class, 'syncAttendance'])->name('sync');
            Route::post('/{device}/force-sync',   [DeviceController::class, 'forceSync'])->name('force-sync');
            Route::get('/{device}/status',        [DeviceController::class, 'status'])->name('status');
        });

        /*
        |----------------------------------------------------------------------
        | Reports
        |----------------------------------------------------------------------
        */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/',                       [ReportController::class, 'index'])->name('index');
            Route::get('/attendance',             [ReportController::class, 'attendance'])->name('attendance');
            Route::get('/payroll',                [ReportController::class, 'payroll'])->name('payroll');
            Route::get('/employees',              [ReportController::class, 'employees'])->name('employees');

            Route::post('/export/attendance',     [ReportController::class, 'exportAttendance'])->name('export.attendance');
            Route::post('/export/payroll',        [ReportController::class, 'exportPayroll'])->name('export.payroll');
            Route::post('/export/employees',      [ReportController::class, 'exportEmployees'])->name('export.employees');
        });

        /*
        |----------------------------------------------------------------------
        | Backups (Admin only)
        |----------------------------------------------------------------------
        */
        Route::middleware(['role:Admin'])->prefix('backups')->name('backups.')->group(function () {
            Route::get('/',                       [BackupController::class, 'index'])->name('index');
            Route::post('/',                      [BackupController::class, 'store'])->name('store');
            Route::get('/{backup}/download',      [BackupController::class, 'download'])->name('download');
            Route::post('/{backup}/restore',      [BackupController::class, 'restore'])->name('restore');
            Route::post('/{backup}/delete',       [BackupController::class, 'destroy'])->name('destroy');
            Route::post('/clean',                 [BackupController::class, 'clean'])->name('clean');
            Route::get('/info',                   [BackupController::class, 'info'])->name('info');
        });

        /*
        |----------------------------------------------------------------------
        | User Settings
        |----------------------------------------------------------------------
        */
        Route::prefix('settings/user')->name('settings.user.')->group(function () {
            Route::get('/',                          [UserSettingController::class, 'index'])->name('index');
            Route::post('/profile',                  [UserSettingController::class, 'updateProfile'])->name('profile');
            Route::post('/avatar',                   [UserSettingController::class, 'updateAvatar'])->name('avatar');
            Route::delete('/avatar',                 [UserSettingController::class, 'deleteAvatar'])->name('avatar.delete');
            Route::post('/password',                 [UserSettingController::class, 'updatePassword'])->name('password');
            Route::post('/preferences',              [UserSettingController::class, 'updatePreferences'])->name('preferences');
            Route::post('/resend-verification',      [UserSettingController::class, 'resendVerification'])->name('resend-verification');

            // Literal routes before wildcard
            Route::delete('/sessions',               [UserSettingController::class, 'destroyOtherSessions'])->name('sessions.destroy-others');
            Route::delete('/sessions/{session}',     [UserSettingController::class, 'destroySession'])->name('sessions.destroy');
            Route::delete('/account',                [UserSettingController::class, 'destroy'])->name('account.destroy');
        });

        /*
        |----------------------------------------------------------------------
        | System Settings (Admin only)
        |----------------------------------------------------------------------
        */
        Route::middleware(['role:Admin'])->prefix('settings')->name('settings.')->group(function () {
            Route::get('/',              fn() => inertia('Admin/Settings/Index'))->name('index');
            Route::get('/roles',         fn() => inertia('Admin/Settings/Roles'))->name('roles');
            Route::get('/permissions',   fn() => inertia('Admin/Settings/Permissions'))->name('permissions');
            Route::get('/system',        fn() => inertia('Admin/Settings/System'))->name('system');
        });
    });

/*
|--------------------------------------------------------------------------
| Employee Routes (Self Service)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::get('/attendance', function () {
        $employee    = Employee::where('user_id', auth()->id())->first();
        $attendances = $employee
            ? Attendance::where('employee_id', $employee->id)->latest('timestamp')->paginate(20)
            : collect();
        return inertia('Employee/Attendance', ['attendances' => $attendances]);
    })->name('attendance');

    Route::get('/payroll', function () {
        $employee = Employee::where('user_id', auth()->id())->first();
        $payrolls = $employee
            ? Payroll::where('employee_id', $employee->id)->latest()->paginate(20)
            : collect();
        return inertia('Employee/Payroll', ['payrolls' => $payrolls]);
    })->name('payroll');

    Route::get('/profile', function () {
        $employee = Employee::where('user_id', auth()->id())->first();
        return inertia('Employee/Profile', ['employee' => $employee]);
    })->name('profile');
});

/*
|--------------------------------------------------------------------------
| ZKTeco Push SDK (device-facing — no CSRF, no auth)
|--------------------------------------------------------------------------
*/
Route::prefix('iclock')
    ->name('iclock.')
    ->withoutMiddleware([PreventRequestForgery::class])
    ->group(function () {
        Route::get('cdata',      [IclockController::class, 'handshake'])->name('handshake');
        Route::post('cdata',     [IclockController::class, 'cdata'])->name('cdata');
        Route::get('getrequest', [IclockController::class, 'getRequest'])->name('getrequest');
        Route::post('devicecmd', [IclockController::class, 'deviceCmd'])->name('devicecmd');
        Route::get('ping',       [IclockController::class, 'ping'])->name('ping');
        Route::get('test',       [IclockController::class, 'test'])->name('test');
    });

require __DIR__ . '/zkteco.php';
require __DIR__ . '/auth.php';
