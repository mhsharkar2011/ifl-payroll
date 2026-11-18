<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\OffDayDuty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PayrollController extends Controller
{
    /* ============================================================
     |  INDEX
     * ============================================================ */
    public function index(Request $request)
    {
        $month  = (int) $request->input('month',  now()->month);
        $year   = (int) $request->input('year',   now()->year);
        $status = $request->input('status');
        $search = trim((string) $request->input('search'));

        $query = Payroll::with('employee:id,employee_id,first_name,last_name,avatar')
            ->where('month', $month)
            ->where('year', $year);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $payrolls = $query
            ->orderBy('net_salary', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_employees' => Payroll::where('month', $month)->where('year', $year)->count(),
            'total_net'       => (float) Payroll::where('month', $month)->where('year', $year)->sum('net_salary'),
            'draft_count'     => Payroll::where('month', $month)->where('year', $year)->where('status', 'draft')->count(),
            'paid_count'      => Payroll::where('month', $month)->where('year', $year)->where('status', 'paid')->count(),
        ];

        return Inertia::render('Admin/Payroll/Index', [
            'payrolls' => $payrolls,
            'stats'    => $stats,
            'filters'  => [
                'month'  => $month,
                'year'   => $year,
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /* ============================================================
     |  SHOW
     * ============================================================ */
    public function show(Payroll $payroll)
    {
        $payroll->load([
            'employee:id,employee_id,first_name,last_name,avatar,email,department_id,designation_id',
            'employee.department:id,name',
            'employee.designation:id,name',
        ]);

        return Inertia::render('Admin/Payroll/Show', [
            'payroll' => $payroll,
        ]);
    }

    /* ============================================================
     |  OFF DAY PAYSLIP
     * ============================================================ */
    public function offDayPayslip(Payroll $payroll)
    {
        $payroll->load([
            'employee:id,employee_id,first_name,last_name,department_id,designation_id',
            'employee.department:id,name',
            'employee.designation:id,name',
        ]);

        return Inertia::render('Admin/Payroll/OffDayPayslip', [
            'payroll' => $payroll
        ]);
    }

    /* ============================================================
     |  MANUAL OFF DAY ENTRY
     * ============================================================ */
    public function offDayManualEntry(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $payrolls = Payroll::with('employee:id,employee_id,first_name,last_name')
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function ($payroll) {
                return [
                    'id' => $payroll->id,
                    'employee_id' => $payroll->employee_id,
                    'employee_name' => $payroll->employee->first_name . ' ' . $payroll->employee->last_name,
                    'employee_code' => $payroll->employee->employee_id,
                    'basic_salary' => (float) $payroll->basic_salary,
                    'off_day_hours' => (float) $payroll->off_day_hours,
                    'off_day_amount' => (float) $payroll->off_day_amount,
                    'is_generated' => $payroll->status !== 'draft',
                ];
            });

        $employees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'employee_id', 'first_name', 'last_name']);

        return Inertia::render('Admin/Payroll/OffDayManual', [
            'payrolls' => $payrolls,
            'employees' => $employees,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ]
        ]);
    }

    public function storeOffDayManual(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month'       => 'required|integer|min:1|max:12',
            'year'        => 'required|integer|min:2000|max:2100',
            'off_day_hours' => 'required|numeric|min:0',
            'notes'       => 'nullable|string|max:255',
        ]);

        $employee = Employee::with(['Salary' => function ($q) {
            $q->where('is_active', true)->latest();
        }])->findOrFail($validated['employee_id']);

        $structure = $employee->Salary->first();

        if (!$structure) {
            return back()->with('error', 'Cannot calculate allowance. This employee has no active Salary Structure.');
        }

        $basicHourlyRate = $structure->basic_salary / 208;
        $offDayAmount = round($validated['off_day_hours'] * ($basicHourlyRate * 2), 2);

        $payroll = Payroll::firstOrNew([
            'employee_id' => $employee->id,
            'month'       => $validated['month'],
            'year'        => $validated['year'],
        ]);

        if ($payroll->exists && $payroll->status === 'paid') {
            return back()->with('error', 'Cannot modify a payroll that has already been paid.');
        }

        if (!$payroll->exists) {
            $payroll->fill([
                'basic_salary'    => $structure->basic_salary,
                'total_hours'     => 0,
                'overtime_hours'  => 0,
                'overtime_amount' => 0,
                'deductions'      => 0,
                'status'          => 'draft',
                'generated_at'    => now(),
            ]);
        }

        $payroll->off_day_hours  = $validated['off_day_hours'];
        $payroll->off_day_amount = $offDayAmount;

        $payroll->total_earnings = $payroll->basic_salary + $payroll->overtime_amount + $payroll->off_day_amount;
        $payroll->net_salary     = $payroll->total_earnings - $payroll->deductions + ($structure->bonus ?? 0);

        $payroll->save();

        return back()->with('success', "Off Day Duty saved for {$employee->first_name}. Allowance: " . number_format($offDayAmount, 2));
    }

    /* ============================================================
     |  GENERATE
     * ============================================================ */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $month = $validated['month'];
        $year  = $validated['year'];

        $existingEmployeeIds = Payroll::where('month', $month)
            ->where('year', $year)
            ->pluck('employee_id')
            ->all();

        $employees = Employee::where('status', 'active')
            ->whereNotIn('id', $existingEmployeeIds)
            ->with(['Salary' => function ($q) {
                $q->where('is_active', true)->latest();
            }])
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('success', 'Nothing to generate — all active employees already have payroll for this period.');
        }

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
        $periodEnd   = $periodStart->copy()->endOfMonth()->endOfDay();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($employees, $month, $year, $periodStart, $periodEnd, &$created, &$skipped) {
            foreach ($employees as $employee) {

                $structure = $employee->Salary->first();

                if (!$structure) {
                    $skipped++;
                    continue;
                }

                // 1. Calculate normal hours from attendance
                $normalPunches = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('timestamp', [$periodStart, $periodEnd])
                    ->count();

                $totalDays    = (int) ceil($normalPunches / 2);
                $totalHours   = round($totalDays * 8, 2);

                // 2. Fetch approved Off Day Duties from the separate table
                $offDayDuties = OffDayDuty::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereBetween('duty_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                    ->sum('total_amount');

                $offDayHours = OffDayDuty::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereBetween('duty_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                    ->sum('hours_worked');

                // 3. Create Payroll Record
                Payroll::create([
                    'employee_id'     => $employee->id,
                    'month'           => $month,
                    'year'            => $year,
                    'basic_salary'    => $structure->basic_salary,
                    'total_hours'     => $totalHours,
                    'overtime_hours'  => 0,
                    'overtime_amount' => 0,
                    'off_day_hours'   => $offDayHours,
                    'off_day_amount'  => $offDayDuties,
                    'total_earnings'  => $structure->total_gross_salary + $offDayDuties,
                    'deductions'      => 0,
                    'net_salary'      => $structure->total_gross_salary + $offDayDuties + $structure->bonus,
                    'status'          => 'draft',
                    'generated_at'    => now(),
                ]);

                $created++;
            }
        });

        $message = "✅ Generated {$created} payroll record(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} employee(s) skipped (no salary structure found).";
        }

        return back()->with('success', $message);
    }

    /* ============================================================
     |  EDIT
     * ============================================================ */
    public function edit(Payroll $payroll)
    {
        $payroll->load('employee:id,employee_id,first_name,last_name');

        return Inertia::render('Admin/Payroll/Edit', [
            'payroll' => $payroll,
        ]);
    }

    /* ============================================================
     |  UPDATE
     * ============================================================ */
    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'basic_salary'    => 'required|numeric|min:0',
            'total_hours'     => 'required|numeric|min:0',
            'overtime_hours'  => 'required|numeric|min:0',
            'overtime_amount' => 'required|numeric|min:0',
            'off_day_hours'   => 'required|numeric|min:0',
            'off_day_amount'  => 'required|numeric|min:0',
            'deductions'      => 'required|numeric|min:0',
        ]);

        $validated['total_earnings'] = $validated['basic_salary'] + $validated['overtime_amount'] + $validated['off_day_amount'];
        $validated['net_salary']     = max(0, $validated['total_earnings'] - $validated['deductions']);

        $payroll->update($validated);

        return redirect()
            ->route('admin.payroll.index', [
                'month' => $payroll->month,
                'year'  => $payroll->year,
            ])
            ->with('success', '✅ Payroll updated successfully!');
    }

    /* ============================================================
     |  APPROVE
     * ============================================================ */
    public function approve(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Only draft payrolls can be approved.');
        }

        $payroll->update(['status' => 'approved']);

        return back()->with('success', '✅ Payroll approved.');
    }

    /* ============================================================
     |  MARK PAID
     * ============================================================ */
    public function markPaid(Payroll $payroll)
    {
        if ($payroll->status !== 'approved') {
            return back()->with('error', 'Only approved payrolls can be marked as paid.');
        }

        $payroll->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', '✅ Payroll marked as paid.');
    }

    /* ============================================================
     |  DESTROY
     * ============================================================ */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Cannot delete a paid payroll.');
        }

        $payroll->delete();

        return back()->with('success', '✅ Payroll deleted.');
    }
}
