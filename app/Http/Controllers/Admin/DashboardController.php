<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private const RECENT_LIMIT = 10;

    public function index()
    {
        $today    = Carbon::today();
        $dayStart = $today->copy()->startOfDay();
        $dayEnd   = $today->copy()->endOfDay();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        /* -------------------------------------------------------
         |  Employee stats
         * ------------------------------------------------------- */
        $totalActive = Employee::where('status', 'active')->count();

        $presentToday = Attendance::whereBetween('timestamp', [$dayStart, $dayEnd])
            ->distinct()
            ->count('employee_id');

        $todayAbsent = max(0, $totalActive - $presentToday);

        /* -------------------------------------------------------
         |  Overtime — count punches this month
         * ------------------------------------------------------- */
        $totalOvertime = Attendance::whereBetween('timestamp', [$monthStart, $monthEnd])
            ->whereIn('status', ['overtime_in', 'overtime_out'])
            ->count();

        /* -------------------------------------------------------
         |  Recent attendance — DEFENSIVE
         * ------------------------------------------------------- */

        // Step 1: fetch raw attendances (no joins, no eager loading)
        $recentRows = Attendance::query()
            ->orderByDesc('timestamp')
            ->limit(self::RECENT_LIMIT)
            ->get();

        // Step 2: fetch the employees in one query
        $employeeIds = $recentRows->pluck('employee_id')->filter()->unique();
        $employees   = Employee::whereIn('id', $employeeIds)
            ->get()
            ->keyBy('id');

        // Step 3: stitch them together in PHP
        $recentAttendance = $recentRows->map(function (Attendance $a) use ($employees) {
            $employee = $employees->get($a->employee_id);

            return [
                'id'                  => $a->id,
                'employee_id'         => $employee?->employee_id,
                'employee_name'       => $employee
                    ? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))
                    : 'Unknown Employee',
                'employee_avatar_url' => $this->avatarUrl($employee),
                'timestamp'           => $a->timestamp?->format('Y-m-d H:i:s'),
                'status'              => $a->status,
                'verified_by'         => $a->verified_by,
            ];
        });

        /* -------------------------------------------------------
         |  Payroll summary
         * ------------------------------------------------------- */
        $payrollSummary = [
            'total_payroll' => (float) Payroll::where('month', now()->month)
                ->where('year', now()->year)
                ->sum('net_salary'),
            'paid_count'    => Payroll::where('month', now()->month)
                ->where('year', now()->year)
                ->where('status', 'paid')
                ->count(),
            'pending_count' => Payroll::where('month', now()->month)
                ->where('year', now()->year)
                ->where('status', '!=', 'paid')
                ->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalEmployees' => $totalActive,
                'todayPresent'   => $presentToday,
                'todayAbsent'    => $todayAbsent,
                'totalOvertime'  => $totalOvertime,
                'maxOvertime'    => 40,
            ],
            'recentAttendance' => $recentAttendance,
            'payrollSummary'   => $payrollSummary,
        ]);
    }

    /**
     * Build the public URL for an employee's avatar.
     * Returns null if the employee has no avatar — the frontend
     * falls back to ui-avatars.com automatically.
     */
    protected function avatarUrl(?Employee $employee): ?string
    {
        if (!$employee || empty($employee->avatar)) {
            return null;
        }

        // If the column already has a full URL (http...), use it
        if (str_starts_with($employee->avatar, 'http')) {
            return $employee->avatar;
        }

        // Otherwise assume it's a path under storage/app/public
        return asset('storage/' . ltrim($employee->avatar, '/'));
    }
}
