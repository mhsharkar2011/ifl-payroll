<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EmployeeTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\EmployeesImport;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /* =========================================================
     |  INDEX
     ========================================================= */

    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department', 'designation'])->orderBy('employee_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->latest()->paginate(15)->withQueryString();

        // ✅ Transform the collection to append the full avatar_url
        $employees->getCollection()->transform(function ($employee) {
            return $this->appendAvatarUrl($employee);
        });

        $totalCount = Employee::count();
        $activeCount = Employee::where('status', 'active')->count();

        $stats = [
            'active'   => $activeCount,
            'inactive' => $totalCount - $activeCount,
        ];

        return Inertia::render('Admin/Employees/Index', [
            'employees'    => $employees,
            'stats'        => $stats,
            'departments'  => Department::active()->orderBy('name')->get(['id', 'name']),
            'designations' => Designation::active()->orderBy('name')->get(['id', 'name']),
            'filters'      => $request->only(['search', 'department_id', 'designation_id', 'status']),
        ]);
    }

    /* =========================================================
     |  CREATE FORM
     ========================================================= */

    public function create()
    {
        $availableUsers = User::whereDoesntHave('employee')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Employees/Create', [
            'departments'    => Department::active()->orderBy('name')->get(['id', 'name']),
            'designations'   => Designation::active()->orderBy('name')->get(['id', 'name']),
            'availableUsers' => $availableUsers,
        ]);
    }

    /* =========================================================
     |  STORE
     ========================================================= */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'email'                => 'required|email|unique:employees,email|unique:users,email',
            'phone'                => 'required|string|max:20',
            'employee_id'          => 'required|string|unique:employees,employee_id',
            'department_id'        => 'required|exists:departments,id',
            'designation_id'       => 'required|exists:designations,id',
            'salary'               => 'required|numeric|min:0',
            'joining_date'         => 'required|date',
            'date_of_birth'        => 'nullable|date|before:today',
            'address'              => 'nullable|string|max:500',
            'device_user_id'       => 'nullable|string|unique:employees,device_user_id',
            'status'               => 'required|in:active,inactive,terminated',

            // ✅ Bank Validation added here
            'bank_name'            => 'nullable|string|max:255',
            'bank_account_number'  => 'nullable|string|max:255',

            'create_user_account'  => 'boolean',
            'password'             => 'nullable|min:8|required_if:create_user_account,true',
        ]);

        DB::beginTransaction();

        try {
            $userId = null;

            if ($request->boolean('create_user_account')) {
                $user = User::create([
                    'name'     => trim($validated['first_name'] . ' ' . $validated['last_name']),
                    'email'    => $validated['email'],
                    'phone'    => $validated['phone'] ?? null,
                    'password' => Hash::make($validated['password'] ?? 'password'),
                ]);

                try {
                    $user->assignRole(Role::firstOrCreate(['name' => 'Employee']));
                } catch (\Throwable $e) {
                    Log::warning('Role assignment failed: ' . $e->getMessage());
                }

                $userId = $user->id;
            }

            $employee = Employee::create([
                'user_id'         => $userId,
                'employee_id'     => $validated['employee_id'],
                'first_name'      => $validated['first_name'],
                'last_name'       => $validated['last_name'],
                'email'           => $validated['email'],
                'phone'           => $validated['phone'],
                'department_id'   => $validated['department_id'],
                'designation_id'  => $validated['designation_id'],
                'salary'          => $validated['salary'],
                'joining_date'    => $validated['joining_date'],
                'date_of_birth'   => $validated['date_of_birth'] ?? null,
                'address'         => $validated['address'] ?? null,
                'device_user_id'  => $validated['device_user_id'] ?? null,
                'status'          => $validated['status'],
            ]);

            // ✅ Create Bank details
            $employee->bank()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'bank_name'           => $validated['bank_name'] ?? null,
                    'bank_account_number' => $validated['bank_account_number'] ?? null,
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee creation failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /* =========================================================
     |  SHOW
     ========================================================= */

    public function show(Employee $employee)
    {
        $employee->load([
            'user',
            'department',
            'designation',
            'bank', // ✅ Load bank relation
            'attendances' => fn($q) => $q->latest()->limit(30),
            'payrolls'    => fn($q) => $q->latest()->limit(12),
        ]);

        return Inertia::render('Admin/Employees/Show', [
            'employee' => $this->appendAvatarUrl($employee),
        ]);
    }

    /* =========================================================
     |  EDIT FORM
     ========================================================= */

    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $designations = Designation::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Employees/Edit', [
            'employee'     => $this->appendAvatarUrl($employee->load('user', 'department', 'designation', 'bank')),
            'departments'  => $departments,
            'designations' => $designations,
        ]);
    }

    /* =========================================================
     |  UPDATE
     ========================================================= */

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'phone'          => 'required|string|max:20',
            'employee_id'    => [
                'required',
                'string',
                Rule::unique('employees', 'employee_id')->ignore($employee->id),
            ],
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'salary'         => 'required|numeric|min:0',
            'joining_date'   => 'required|date',
            'date_of_birth'  => 'nullable|date|before:today',
            'address'        => 'nullable|string|max:500',
            'device_user_id' => [
                'nullable',
                'string',
                Rule::unique('employees', 'device_user_id')->ignore($employee->id),
            ],
            'status'         => 'required|in:active,inactive,terminated',
            'avatar'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar'  => 'nullable|boolean',

            // ✅ Bank Validation added here
            'bank_name'           => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1. Handle Avatar Removal
            if ($request->boolean('remove_avatar') && $employee->avatar) {
                if (Storage::disk('public')->exists($employee->avatar)) {
                    Storage::disk('public')->delete($employee->avatar);
                }
                $validated['avatar'] = null;
            }

            // 2. Handle Avatar Upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
                    Storage::disk('public')->delete($employee->avatar);
                }

                // Store new avatar
                $validated['avatar'] = $request->file('avatar')
                    ->store('employee-avatars', 'public');
            }

            // 3. Extract Bank Data and Remove from $validated
            // (We must remove it because 'bank_name' doesn't exist on the employees table)
            $bankData = [
                'bank_name'           => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
            ];
            unset($validated['bank_name'], $validated['bank_account_number']);

            // 4. Update Employee
            $employee->update($validated);

            // 5. Update Bank Details
            $employee->bank()->updateOrCreate(
                ['employee_id' => $employee->id],
                $bankData
            );

            // 6. Update linked user
            if ($employee->user) {
                $employee->user->update([
                    'name'  => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Employee updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee update failed: ' . $e->getMessage());

            return back()
                ->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        DB::beginTransaction();

        try {
            $user = $employee->user;
            $employee->delete();

            if ($user && $user->employee()->count() === 0) {
                $hasProtectedRole = method_exists($user, 'hasAnyRole')
                    && $user->hasAnyRole(['Admin', 'HR', 'Manager']);

                if (!$hasProtectedRole) {
                    $user->delete();
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Employee deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }

    public function toggleStatus(Employee $employee)
    {
        $employee->update([
            'status' => $employee->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Employee status updated!');
    }

    public function resetDeviceId(Employee $employee)
    {
        $employee->update(['device_user_id' => null]);

        return back()->with('success', 'Device ID reset successfully!');
    }

    public function bulkUpload()
    {
        return Inertia::render('Admin/Employees/BulkUpload');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new EmployeeTemplateExport(),
            'employee_upload_template_' . date('Y-m-d') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        Log::info('=== Employee Import Started ===');

        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            $import = new EmployeesImport();
            Excel::import($import, $request->file('file'));

            $message = "✅ Imported: {$import->imported} employees";
            if ($import->skipped > 0) {
                $message .= ", Skipped: {$import->skipped}";
            }
            if (!empty($import->importErrors)) {
                $message .= " | Errors: " . count($import->importErrors);
            }

            Log::info('Employee import completed', [
                'imported' => $import->imported,
                'skipped'  => $import->skipped,
                'errors'   => $import->importErrors,
            ]);

            return redirect()
                ->route('admin.employees.index')
                ->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            Log::error('Import validation failed', $errors);
            return back()->withErrors(['file' => implode(' | ', $errors)]);
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    public function analytics(Request $request)
    {
        $period = (int) ($request->period ?? 6);

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        $totalSalary = Employee::where('status', 'active')->sum('salary');
        $averageSalary = $activeEmployees > 0 ? $totalSalary / $activeEmployees : 0;

        $thirtyDaysAgo = now()->subDays(30);
        $totalAttendanceRecords = Attendance::where('timestamp', '>=', $thirtyDaysAgo)
            ->distinct('employee_id')
            ->count('employee_id');

        $attendanceRate = $activeEmployees > 0
            ? round(($totalAttendanceRecords / ($activeEmployees * 30)) * 100)
            : 0;
        $attendanceRate = min(100, $attendanceRate);

        $stats = [
            'totalEmployees'  => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'totalSalary'     => $totalSalary,
            'averageSalary'   => $averageSalary,
            'attendanceRate'  => $attendanceRate,
            'employeeGrowth'  => 5.2,
            'activeRate'      => $totalEmployees > 0
                ? round(($activeEmployees / $totalEmployees) * 100)
                : 0,
        ];

        $departments = Department::withCount('employees')
            ->having('employees_count', '>', 0)
            ->orderByDesc('employees_count')
            ->get();

        $colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'];

        $departmentsData = $departments->map(function ($dept, $index) use ($totalEmployees, $colors) {
            return [
                'id'         => $dept->id,
                'name'       => $dept->name,
                'count'      => $dept->employees_count,
                'percentage' => $totalEmployees > 0
                    ? round(($dept->employees_count / $totalEmployees) * 100)
                    : 0,
                'color'      => $colors[$index % count($colors)],
            ];
        })->values()->toArray();

        $designations = Designation::withCount('employees')
            ->withAvg('employees as avg_salary', 'salary')
            ->having('employees_count', '>', 0)
            ->orderByDesc('avg_salary')
            ->limit(8)
            ->get();

        $maxSalary = $designations->max('avg_salary') ?: 1;

        $salaryByDesignation = $designations->map(function ($desig) use ($maxSalary) {
            return [
                'id'         => $desig->id,
                'name'       => $desig->name,
                'avg_salary' => round($desig->avg_salary ?? 0),
                'percentage' => round((($desig->avg_salary ?? 0) / $maxSalary) * 100),
            ];
        })->toArray();

        $attendanceTrend = [];
        $maxCount = 1;

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Attendance::whereDate('timestamp', $date)
                ->where('status', 'check_in')
                ->distinct('employee_id')
                ->count();

            $maxCount = max($maxCount, $count);

            $attendanceTrend[] = [
                'date'  => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'count' => $count,
                'height' => 0,
            ];
        }

        foreach ($attendanceTrend as &$day) {
            $day['height'] = $maxCount > 0
                ? round(($day['count'] / $maxCount) * 100)
                : 0;
            $day['height'] = max(5, $day['height']);
        }
        unset($day);

        $topPerformers = Employee::with(['department:id,name'])
            ->where('status', 'active')
            ->get()
            ->map(function ($emp) {
                $attendanceCount = Attendance::where('employee_id', $emp->id)
                    ->whereMonth('timestamp', now()->month)
                    ->where('status', 'check_in')
                    ->distinct(DB::raw('DATE(timestamp)'))
                    ->count();

                $workingDays = now()->daysInMonth;

                return [
                    'id'              => $emp->id,
                    'name'            => trim($emp->first_name . ' ' . $emp->last_name),
                    'department'      => $emp->department?->name ?? 'N/A',
                    'avatar_url'      => $this->getAvatarUrl($emp),
                    'attendance_rate' => $workingDays > 0
                        ? round(($attendanceCount / $workingDays) * 100)
                        : 0,
                ];
            })
            ->sortByDesc('attendance_rate')
            ->take(3)
            ->values()
            ->toArray();

        return Inertia::render('Admin/Employees/Analytics', [
            'stats'               => $stats,
            'departmentsData'     => $departmentsData,
            'salaryByDesignation' => $salaryByDesignation,
            'attendanceTrend'     => $attendanceTrend,
            'topPerformers'       => $topPerformers,
        ]);
    }

    public function exportAnalytics(Request $request)
    {
        return response()->json([
            'message' => 'Export feature — coming soon',
        ]);
    }

    protected function appendAvatarUrl(Employee $employee): Employee
    {
        $employee->setAttribute('avatar_url', $this->getAvatarUrl($employee));
        $employee->setAttribute('full_name', trim($employee->first_name . ' ' . $employee->last_name));
        return $employee;
    }

    protected function getAvatarUrl(Employee $employee): ?string
    {
        if ($employee->avatar) {
            if (str_starts_with($employee->avatar, 'http')) {
                return $employee->avatar;
            }

            // ✅ FIXED: Prepend 'storage/' so the URL is /storage/employee-avatars/...
            return asset('storage/' . ltrim($employee->avatar, '/'));
        }
        return null;
    }
}
