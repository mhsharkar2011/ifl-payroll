<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Reports/Index');
    }

    public function attendance(Request $request)
    {
        $query = Attendance::with('employee');

        if ($request->filled('start_date')) {
            $query->whereDate('timestamp', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('timestamp', '<=', $request->end_date);
        }

        $attendances = $query->latest('timestamp')->paginate(50)->withQueryString();
        $employees = Employee::where('status', 'active')->get();

        return Inertia::render('Admin/Reports/Attendance', [
            'attendances' => $attendances,
            'employees' => $employees,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function payroll(Request $request)
    {
        $query = Payroll::with('employee');

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $payrolls = $query->latest()->paginate(50)->withQueryString();

        return Inertia::render('Admin/Reports/Payroll', [
            'payrolls' => $payrolls,
            'filters' => $request->only(['month', 'year']),
        ]);
    }

    public function employees()
    {
        $employees = Employee::with('user')->latest()->paginate(50);
        return Inertia::render('Admin/Reports/Employees', ['employees' => $employees]);
    }

    public function exportAttendance(Request $request)
    {
        // Export logic
        return back()->with('success', 'Export started!');
    }

    public function exportPayroll(Request $request)
    {
        // Export logic
        return back()->with('success', 'Export started!');
    }
}
