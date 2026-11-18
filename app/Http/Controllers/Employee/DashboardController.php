<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return Inertia::render('Employee/Dashboard', [
                'stats' => [],
                'recentAttendance' => [],
                'employee' => null,
            ]);
        }

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $stats = [
            'presentDays' => Attendance::where('employee_id', $employee->id)
                ->whereBetween('timestamp', [$monthStart, $monthEnd])
                ->where('status', 'check_in')
                ->distinct('timestamp')
                ->count(),
            'absentDays' => 0,
            'overtimeHours' => 0,
            'lastSalary' => Payroll::where('employee_id', $employee->id)
                ->latest()
                ->value('net_salary') ?? 0,
        ];

        $recentAttendance = Attendance::where('employee_id', $employee->id)
            ->latest('timestamp')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'date' => $a->timestamp?->format('d M, Y'),
                'check_in' => $a->status === 'check_in' ? $a->timestamp?->format('h:i A') : null,
                'check_out' => $a->status === 'check_out' ? $a->timestamp?->format('h:i A') : null,
                'hours' => 8,
            ]);

        return Inertia::render('Employee/Dashboard', [
            'stats' => $stats,
            'recentAttendance' => $recentAttendance,
            'employee' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'department' => $employee->department,
                'designation' => $employee->designation,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'joining_date' => $employee->joining_date?->format('d M, Y'),
                'salary' => $employee->salary,
                'address' => $employee->address,
            ],
        ]);
    }
}
