<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Designation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $query = Designation::withCount('employees');

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

        $designations = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Designations/Index', [
            'designations' => $designations,
            'filters'      => $request->only(['search', 'status']),
            'stats'        => [
                'total'    => Designation::count(),
                'active'   => Designation::where('is_active', true)->count(),
                'inactive' => Designation::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Designations/Create');
    }

    public function store(StoreDesignationRequest $request)
    {
        Designation::create($request->validated());

        return redirect()
            ->route('admin.designations.index')
            ->with('success', '✅ Designation created successfully.');
    }

    public function show(Designation $designation)
    {
        $designation->loadCount('employees');

        return Inertia::render('Admin/Designations/Show', [
            'designation' => $designation,
        ]);
    }

    public function edit(Designation $designation)
    {
        return Inertia::render('Admin/Designations/Edit', [
            'designation' => $designation,
        ]);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation)
    {
        $designation->update($request->validated());

        return redirect()
            ->route('admin.designations.index')
            ->with('success', '✅ Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        if ($designation->employees()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete: this designation has employees assigned.',
            ]);
        }

        $designation->delete();

        return redirect()
            ->route('admin.designations.index')
            ->with('success', '✅ Designation deleted successfully.');
    }

    public function toggleStatus(Designation $designation)
    {
        $designation->update(['is_active' => !$designation->is_active]);

        return back()->with('success', '✅ Status updated.');
    }
}
