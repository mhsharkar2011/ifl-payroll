<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Salary::with('employee:id,employee_id,first_name,last_name,avatar');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($sub) use ($search) {
                      $sub->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('employee_id', 'like', "%{$search}%");
                  });
            });
        }

        $structures = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'          => Salary::count(),
            'avg_gross'      => (float) Salary::avg('gross_salary'),
            'avg_net_gross'  => (float) Salary::avg('total_gross_salary'),
            'total_bonus'    => (float) Salary::sum('bonus'),
        ];

        return Inertia::render('Admin/Salary/Index', [
            'structures' => $structures,
            'stats'      => $stats,
            'filters'    => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Salary/Create', [
            'employees' => Employee::where('status', 'active')
                ->orderBy('employee_id')
                ->get(['id', 'employee_id', 'first_name', 'last_name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $computed = Salary::compute(
            $validated['gross_salary'],
            $validated['medical_allowance'],
            $validated['conveyance'],
            $validated['food_allowance'],
            $validated['bonus'] ?? 0
        );

        Salary::create(array_merge($validated, $computed));

        return redirect()
            ->route('admin.salary.index')
            ->with('success', '✅ Salary structure created.');
    }

    public function edit(Salary $salary)
    {
        return Inertia::render('Admin/Salary/Edit', [
            'structure' => $salary,
            'employees' => Employee::where('status', 'active')
                ->get(['id', 'employee_id', 'first_name', 'last_name']),
        ]);
    }

    public function update(Request $request, Salary $salary)
    {
        $validated = $this->validatePayload($request);

        $computed = Salary::compute(
            $validated['gross_salary'],
            $validated['medical_allowance'],
            $validated['conveyance'],
            $validated['food_allowance'],
            $validated['bonus'] ?? 0
        );

        $salary->update(array_merge($validated, $computed));

        return redirect()
            ->route('admin.salary.index')
            ->with('success', '✅ Salary structure updated.');
    }

    public function destroy(Salary $salary)
    {
        $salary->delete();

        return back()->with('success', '✅ Salary structure deleted.');
    }

    /** Shared validation rules */
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'employee_id'       => 'nullable|exists:employees,id',
            'name'              => 'nullable|string|max:100',
            'gross_salary'      => 'required|numeric|min:0',
            'medical_allowance' => 'required|numeric|min:0',
            'conveyance'        => 'required|numeric|min:0',
            'food_allowance'    => 'required|numeric|min:0',
            'bonus'             => 'nullable|numeric|min:0',
            'bonus_note'        => 'nullable|string|max:100',
            'is_active'         => 'boolean',
        ]);
    }
}
