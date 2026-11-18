<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount('employees');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $departments = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'filters'     => $request->only(['search', 'status']),
            'stats'       => [
                'total'    => Department::count(),
                'active'   => Department::where('is_active', true)->count(),
                'inactive' => Department::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Departments/Create');
    }

    public function store(StoreDepartmentRequest $request)
    {
        Department::create($request->validated());

        return redirect()
            ->route('admin.departments.index')
            ->with('success', '✅ Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->loadCount('employees');

        return Inertia::render('Admin/Departments/Show', [
            'department' => $department,
        ]);
    }

    public function edit(Department $department)
    {
        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return redirect()
            ->route('admin.departments.index')
            ->with('success', '✅ Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete: this department has employees assigned.',
            ]);
        }

        $department->delete();

        return redirect()
            ->route('admin.departments.index')
            ->with('success', '✅ Department deleted successfully.');
    }

    public function toggleStatus(Department $department)
    {
        $department->update(['is_active' => !$department->is_active]);

        return back()->with('success', '✅ Status updated.');
    }
}
