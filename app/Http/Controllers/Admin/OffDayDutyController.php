<?php
// app/Http/Controllers/Admin/OffDayDutyController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OffDayDuty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OffDayDutyController extends Controller
{
    public function index(Request $request)
    {
        $viewMode = $request->input('view_mode', 'monthly'); // daily, monthly, yearly

        // Default to current date/month/year based on mode
        $dateStr = $request->input('date', now()->toDateString());
        $month   = (int) $request->input('month', now()->month);
        $year    = (int) $request->input('year', now()->year);

        // ✅ FIXED: Eager load employee.bank so the table can display the bank account number
        $query = OffDayDuty::with([
            'employee:id,employee_id,first_name,last_name',
            'employee.bank', // <-- Added this
            'approver:id,name'
        ]);

        // Apply Date Filters based on View Mode
        if ($viewMode === 'daily') {
            $query->whereDate('duty_date', $dateStr);
        } elseif ($viewMode === 'yearly') {
            $query->whereYear('duty_date', $year);
        } else {
            // Monthly (Default)
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            $query->whereBetween('duty_date', [$start->toDateString(), $end->toDateString()]);
        }

        $duties = $query->orderBy('duty_date', 'desc')->get();

        // Fetch employees for the dropdown (no need to load bank here)
        $employees = Employee::where('status', 'active')
            ->orderBy('employee_id')
            ->get(['id', 'employee_id', 'first_name', 'last_name']);

        $totalHours = $duties->sum('hours_worked');
        $totalAmount = $duties->sum('total_amount');
        $totalCompLeave = $duties->sum('compensatory_leave_days');

        return Inertia::render('Admin/Payroll/OffDayDuty/Index', [
            'duties' => $duties,
            'employees' => $employees,
            'totals' => [
                'hours' => $totalHours,
                'amount' => $totalAmount,
                'comp_leave' => $totalCompLeave,
            ],
            'filters' => [
                'view_mode' => $viewMode,
                'date'      => $dateStr,
                'month'     => $month,
                'year'      => $year,
            ]
        ]);
    }

    public function getHoursFromAttendance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
        ]);

        // Fetch the specific date's punches
        $date = Carbon::parse($request->date);
        $attendances = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp')
            ->get();

        if ($attendances->isEmpty()) {
            return response()->json([
                'hours' => 0,
                'message' => 'No attendance records found for this employee on this date.'
            ]);
        }

        // Calculate total hours by finding the first check-in and last check-out
        $firstIn = $attendances->where('status', 'check_in')->first();
        $lastOut = $attendances->where('status', 'check_out')->last();

        $totalHours = 0;

        if ($firstIn && $lastOut) {
            // Calculate diff between first IN and last OUT
            $totalHours = Carbon::parse($firstIn->timestamp)->diffInHours(Carbon::parse($lastOut->timestamp));
            // Round to nearest 0.5 (e.g., 4.5 hours)
            $totalHours = round($totalHours * 2) / 2;
        } elseif ($firstIn) {
            // Only checked in, assume standard 8 hours
            $totalHours = 8;
        }

        return response()->json([
            'hours' => $totalHours,
            'message' => "Fetched {$totalHours} hours from attendance records."
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'duty_date'    => 'required|date',
            'hours_worked' => 'required|numeric|min:0.5',
            'reason'       => 'nullable|string|max:255',
        ]);

        $employee = Employee::with(['Salary' => function ($q) {
            $q->where('is_active', true)->latest();
        }])->findOrFail($validated['employee_id']);

        $structure = $employee->Salary->first();

        if (!$structure) {
            return back()->with('error', 'Employee has no active Salary Structure to calculate the rate.');
        }

        // Prevent duplicate entries for the same employee on the same day
        $exists = OffDayDuty::where('employee_id', $employee->id)
            ->whereDate('duty_date', $validated['duty_date'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This employee already has a duty record for this date.');
        }

        $hours = $validated['hours_worked'];
        $basicSalary = $structure->basic_salary;

        // ✅ Rule 4.6 & 4.5: Calculations
        if ($hours >= 8) {
            // Full day (8+ hours): ((Basic ÷ 30) × 2) × Duty Days
            $totalAmount = round(($basicSalary / 30) * 2, 2);
            $compLeave = 1.00; // 1 full day
            $effectiveRate = round(($basicSalary / 30) * 2, 2);
        } else {
            // Pro-rata (< 8 hours): ((Basic ÷ 30) × 2 ÷ 8) × Duty Hours
            $totalAmount = round((($basicSalary / 30) * 2 / 8) * $hours, 2);
            $compLeave = round($hours / 8, 2);
            $effectiveRate = round((($basicSalary / 30) * 2 / 8), 2);
        }

        OffDayDuty::create([
            'employee_id'             => $employee->id,
            'duty_date'               => $validated['duty_date'],
            'hours_worked'            => $hours,
            'compensatory_leave_days' => $compLeave,
            'hourly_rate'             => $effectiveRate,
            'total_amount'            => $totalAmount,
            'reason'                  => $validated['reason'] ?? 'Off Day Duty',
            'created_by'              => auth()->id(),
        ]);

        return back()->with('success', 'Off Day Duty record added.');
    }

    public function update(Request $request, OffDayDuty $offDayDuty)
    {
        $validated = $request->validate([
            'hours_worked' => 'required|numeric|min:0.5',
            'reason'       => 'nullable|string|max:255',
            'status'       => 'required|in:pending,approved',
        ]);

        $employee = $offDayDuty->employee;
        $structure = $employee->Salary()->where('is_active', true)->latest()->first();
        $basicSalary = $structure ? $structure->basic_salary : 0;

        $hours = $validated['hours_worked'];

        // ✅ Recalculate based on updated hours
        if ($hours >= 8) {
            $totalAmount = round(($basicSalary / 30) * 2, 2);
            $compLeave = 1.00;
        } else {
            $totalAmount = round((($basicSalary / 30) * 2 / 8) * $hours, 2);
            $compLeave = round($hours / 8, 2);
        }

        $approvedBy = $offDayDuty->approved_by;
        if ($validated['status'] === 'approved' && $offDayDuty->status !== 'approved') {
            $approvedBy = auth()->id();
        } elseif ($validated['status'] === 'pending') {
            $approvedBy = null;
        }

        $offDayDuty->update([
            'hours_worked'            => $hours,
            'compensatory_leave_days' => $compLeave,
            'total_amount'            => $totalAmount,
            'reason'                  => $validated['reason'],
            'status'                  => $validated['status'],
            'approved_by'             => $approvedBy,
        ]);

        return back()->with('success', 'Off Day Duty record updated.');
    }

    public function destroy(OffDayDuty $offDayDuty)
    {
        $offDayDuty->delete();
        return back()->with('success', 'Off Day Duty record deleted.');
    }

    public function printReport(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month'       => 'required|integer|min:1|max:12',
            'year'        => 'required|integer|min:2000|max:2100',
        ]);

        // ✅ Eager load bank for the PDF report
        $employee = Employee::with([
            'department:id,name',
            'designation:id,name',
            'bank',
            'Salary' => function ($q) {
                $q->where('is_active', true)->latest();
            }
        ])->findOrFail($request->employee_id);

        $structure = $employee->Salary->first();
        $basicSalary = $structure ? $structure->basic_salary : 0;

        $start = Carbon::create($request->year, $request->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Fetch only approved duties for this month
        $duties = OffDayDuty::with('approver:id,name')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('duty_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('duty_date')
            ->get();

        return Inertia::render('Admin/Payroll/OffDayDuty/Report', [
            'employee'     => $employee,
            'basic_salary' => $basicSalary,
            'duties'       => $duties,
            'month'        => (int) $request->month,
            'year'         => (int) $request->year,
        ]);
    }
}
