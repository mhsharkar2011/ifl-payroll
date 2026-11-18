<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\ZKTecoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    private const IMPORT_MAX_ROWS = 100_000;

    /* =========================================================
     |  PAGE: INDEX
     ========================================================= */
    public function index(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        [$start, $end] = $this->dayRange($date);

        $perPage = (int) $request->input('per_page');
        $perPage = max(15, min($perPage, 200));

        $query = $this->groupedQuery($start, $end);
        $this->applyFilters($query, $request);

        $paginator = $query
            ->orderByDesc('first_check_in')
            ->paginate($perPage)
            ->withQueryString();

        $employees = $this->hydrateEmployees($paginator->pluck('employee_id'));

        $paginator->getCollection()->transform(
            fn($row) => $this->transformGroupedRow($row, $employees->get($row->employee_id))
        );

        return Inertia::render('Admin/Attendance/Index', [
            'attendances'  => $paginator,
            'stats'        => $this->computeStats($start, $end),
            'departments'  => Department::active()->orderBy('name')->get(['id', 'name']),
            'designations' => Designation::active()->orderBy('name')->get(['id', 'name']),
            'employees'    => $this->activeEmployeeOptions(),
            'latestDate'   => $this->latestAttendanceDate(),
            'filters'      => [
                'search'        => $request->input('search', ''),
                'date'          => $date,
                'department_id' => $request->input('department_id', ''),
                'per_page'      => $perPage,
            ],
        ]);
    }

    public function monthly(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);

        $month = max(1, min($month, 12));
        $year  = max(2000, min($year, 2100));

        $perPage = (int) $request->input('per_page', 25);
        $perPage = max(5, min($perPage, 200));

        $departmentId = $request->input('department_id');

        $start = Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();

        $query = Attendance::query()
            ->selectRaw('`attendances`.`employee_id`')
            ->selectRaw('DATE(`attendances`.`timestamp`) as attendance_date')
            ->selectRaw('MIN(`attendances`.`timestamp`) as first_punch')
            ->selectRaw('MAX(`attendances`.`timestamp`) as last_punch')
            ->selectRaw('MIN(CASE WHEN `attendances`.`status` = "check_in"  THEN `attendances`.`timestamp` END) as first_check_in')
            ->selectRaw('MAX(CASE WHEN `attendances`.`status` = "check_out" THEN `attendances`.`timestamp` END) as last_check_out')
            ->selectRaw('COUNT(*) as punch_count')
            ->selectRaw('GROUP_CONCAT(DISTINCT `attendances`.`status`) as statuses')
            ->selectRaw('MIN(`attendances`.`id`) as id')
            ->selectRaw('MAX(`attendances`.`notes`) as notes')
            ->whereBetween('attendances.timestamp', [$start, $end])
            ->groupBy('attendances.employee_id', DB::raw('DATE(`attendances`.`timestamp`)'));

        if ($request->filled('department_id')) {
            $query->whereIn(
                'attendances.employee_id',
                Employee::where('department_id', $departmentId)->pluck('id')
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereIn(
                'attendances.employee_id',
                Employee::query()
                    ->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name',  'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    })
                    ->pluck('id')
            );
        }

        $paginator = $query
            ->orderByDesc('attendance_date')
            ->orderBy('attendances.employee_id')
            ->paginate($perPage)
            ->withQueryString();

        $employees = $this->hydrateEmployees($paginator->pluck('employee_id'));

        $paginator->getCollection()->transform(
            fn($row) => $this->transformGroupedRow($row, $employees->get($row->employee_id))
        );

        $perEmployeeSummary = Attendance::query()
            ->selectRaw('`attendances`.`employee_id`')
            ->selectRaw('COUNT(DISTINCT DATE(`attendances`.`timestamp`)) as days_present')
            ->selectRaw('COUNT(*) as total_punches')
            ->whereBetween('attendances.timestamp', [$start, $end])
            ->groupBy('attendances.employee_id')
            ->get()
            ->map(function ($row) use ($employees) {
                $emp = $employees->get($row->employee_id);
                return [
                    'employee_id'   => $emp?->employee_id,
                    'employee_name' => $emp
                        ? trim($emp->first_name . ' ' . $emp->last_name)
                        : 'Unknown',
                    'days_present'  => (int) $row->days_present,
                    'total_punches' => (int) $row->total_punches,
                ];
            })
            ->sortByDesc('days_present')
            ->values()
            ->take(10);

        $totalDaysInMonth = $start->daysInMonth;

        $stats = [
            'working_days'   => $totalDaysInMonth,
            'total_punches'  => Attendance::whereBetween('timestamp', [$start, $end])->count(),
            'unique_employees' => Attendance::whereBetween('timestamp', [$start, $end])
                ->distinct()
                ->count('employee_id'),
            'avg_punches_per_day' => $totalDaysInMonth > 0
                ? round(
                    Attendance::whereBetween('timestamp', [$start, $end])->count() / $totalDaysInMonth,
                    1
                )
                : 0,
        ];

        return Inertia::render('Admin/Attendance/Monthly', [
            'records'   => $paginator,
            'stats'     => $stats,
            'employees' => $perEmployeeSummary,
            'departments' => Department::active()->orderBy('name')->get(['id', 'name']),
            'filters'   => [
                'month'         => $month,
                'year'          => $year,
                'per_page'      => $perPage,
                'search'        => $request->input('search', ''),
                'department_id' => $request->input('department_id', ''),
            ],
        ]);
    }

    public function exportMonthly(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);
        $month = max(1, min($month, 12));

        $start = Carbon::create($year, $month, 1)->startOfMonth()->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();

        $rows = Attendance::query()
            ->selectRaw('`attendances`.`employee_id`')
            ->selectRaw('DATE(`attendances`.`timestamp`) as attendance_date')
            ->selectRaw('MIN(CASE WHEN `attendances`.`status` = "check_in"  THEN `attendances`.`timestamp` END) as first_check_in')
            ->selectRaw('MAX(CASE WHEN `attendances`.`status` = "check_out" THEN `attendances`.`timestamp` END) as last_check_out')
            ->selectRaw('COUNT(*) as punch_count')
            ->whereBetween('attendances.timestamp', [$start, $end])
            ->groupBy('attendances.employee_id', DB::raw('DATE(`attendances`.`timestamp`)'))
            ->orderBy('attendance_date')
            ->orderBy('attendances.employee_id')
            ->get();

        $employees = Employee::whereIn('id', $rows->pluck('employee_id')->unique())
            ->get()
            ->keyBy('id');

        $filename = sprintf('attendance-%04d-%02d.csv', $year, $month);

        return response()->streamDownload(function () use ($rows, $employees) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Date',
                'Employee ID',
                'Employee Name',
                'Check In',
                'Check Out',
                'Punch Count',
            ]);

            foreach ($rows as $r) {
                $emp = $employees->get($r->employee_id);
                fputcsv($out, [
                    $r->attendance_date,
                    $emp?->employee_id,
                    $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Unknown',
                    $r->first_check_in ? Carbon::parse($r->first_check_in)->format('H:i:s') : '',
                    $r->last_check_out ? Carbon::parse($r->last_check_out)->format('H:i:s') : '',
                    $r->punch_count,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function getData(Request $request)
    {
        try {
            $date = $request->input('date', today()->toDateString());
            [$start, $end] = $this->dayRange($date);

            $perPage = (int) $request->input('per_page', 15);
            $perPage = max(5, min($perPage, 200));

            $query = $this->groupedQuery($start, $end);
            $this->applyFilters($query, $request);

            $paginator = $query->orderByDesc('first_check_in')->paginate($perPage);

            $employees = $this->hydrateEmployees($paginator->pluck('employee_id'));

            $data = $paginator->getCollection()->map(
                fn($row) => $this->transformGroupedRow($row, $employees->get($row->employee_id))
            );

            return response()->json([
                'data' => $data,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                ],
                'latest_date' => $this->latestAttendanceDate(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Attendance fetch failed: ' . $e->getMessage());
            return response()->json([
                'data'  => [],
                'meta'  => ['current_page' => 1, 'last_page' => 1, 'total' => 0, 'from' => 0, 'to' => 0],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function latestAttendanceDate(): ?string
    {
        return Attendance::query()
            ->selectRaw('DATE(MAX(`timestamp`)) as latest')
            ->value('latest');
    }

    public function sync(ZKTecoService $zkService)
    {
        try {
            Log::info('Attendance sync started');
            $result = $zkService->syncAttendance();
            Log::info('Attendance sync completed', [
                'synced' => count($result['synced'] ?? []),
                'failed' => count($result['failed'] ?? []),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance synced successfully',
                'synced'  => count($result['synced'] ?? []),
                'failed'  => count($result['failed'] ?? []),
            ]);
        } catch (\Throwable $e) {
            Log::error('Attendance sync failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function syncStatus(Request $request)
    {
        $since = $request->query('since')
            ? Carbon::createFromTimestampMs($request->query('since'))
            : now()->subMinutes(5);

        return response()->json([
            'recent_attendance' => Attendance::where('created_at', '>=', $since)->count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Attendance/Create', [
            'employees' => $this->activeEmployeeOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'in_time'     => 'nullable|date_format:H:i',
            'out_time'    => 'nullable|date_format:H:i',
            'notes'       => 'nullable|string',
        ]);

        $date = $validated['date'];
        $userId = auth()->id();
        $notes = $validated['notes'] ?? null;

        if ($request->filled('in_time')) {
            Attendance::create([
                'employee_id' => $validated['employee_id'],
                'timestamp'   => Carbon::parse("{$date} {$request->in_time}"),
                'status'      => 'check_in',
                'verified_by' => 0,
                'notes'       => $notes,
                'created_by'  => $userId,
            ]);
        }

        if ($request->filled('out_time')) {
            Attendance::create([
                'employee_id' => $validated['employee_id'],
                'timestamp'   => Carbon::parse("{$date} {$request->out_time}"),
                'status'      => 'check_out',
                'verified_by' => 0,
                'notes'       => $notes,
                'created_by'  => $userId,
            ]);
        }

        return back()->with('success', 'Manual attendance saved successfully.');
    }

    public function show(Attendance $attendance)
    {
        $attendance->load([
            'employee:id,employee_id,first_name,last_name,email,phone,department_id,designation_id',
            'employee.department:id,name',
            'employee.designation:id,name',
        ]);

        [$start, $end] = $this->dayRange($attendance->timestamp->toDateString());

        $sameDay = Attendance::with('employee:id,first_name,last_name')
            ->where('employee_id', $attendance->employee_id)
            ->whereBetween('timestamp', [$start, $end])
            ->orderBy('timestamp')
            ->get(['id', 'employee_id', 'timestamp', 'status', 'verified_by', 'in_time', 'out_time']);

        return Inertia::render('Admin/Attendance/Show', [
            'attendance' => $attendance,
            'sameDay'    => $sameDay,
        ]);
    }

    public function edit(Request $request, Attendance $attendance)
    {
        $backTo  = $request->input('back', 'admin.attendance.index');
        $allowed = ['admin.attendance.index', 'admin.attendance.today'];

        if (!in_array($backTo, $allowed, true)) {
            $backTo = 'admin.attendance.index';
        }

        return Inertia::render('Admin/Attendance/Edit', [
            'attendance' => $attendance->load('employee'),
            'employees'  => $this->activeEmployeeOptions(),
            'backTo'     => $backTo,
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'in_time'     => 'nullable|date_format:H:i',
            'out_time'    => 'nullable|date_format:H:i',
            'notes'       => 'nullable|string',
        ]);

        $date = $validated['date'];
        $notes = $validated['notes'] ?? null;
        $userId = auth()->id();

        if ($request->filled('in_time')) {
            $inTimestamp = Carbon::parse("{$date} {$request->in_time}");

            Attendance::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'status'      => 'check_in',
                    'timestamp'   => $inTimestamp,
                ],
                [
                    'verified_by' => 0,
                    'notes'       => $notes,
                    'updated_by'  => $userId,
                ]
            );
        }

        if ($request->filled('out_time')) {
            $outTimestamp = Carbon::parse("{$date} {$request->out_time}");

            Attendance::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'status'      => 'check_out',
                    'timestamp'   => $outTimestamp,
                ],
                [
                    'verified_by' => 0,
                    'notes'       => $notes,
                    'updated_by'  => $userId,
                ]
            );
        }

        if ($attendance->timestamp) {
            $oldTime = $attendance->timestamp->format('H:i');
            $newIn = $request->in_time;
            $newOut = $request->out_time;

            if ($oldTime !== $newIn && $oldTime !== $newOut) {
                $attendance->delete();
            }
        }

        return back()->with('success', 'Attendance updated successfully!');
    }

    public function destroy(Attendance $attendance)
    {
        try {
            $attendance->delete();

            if (request()->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route('admin.attendance.index')
                ->with('success', 'Attendance deleted successfully!');
        } catch (\Throwable $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Attendance::whereIn('id', $ids)->delete();
        return response()->json(['success' => true]);
    }

    public function today(Request $request)
    {
        $date = $request->input('date')
            ?? $this->latestAttendanceDate()
            ?? today()->toDateString();

        $start = Carbon::parse($date)->startOfDay();
        $end   = Carbon::parse($date)->endOfDay();

        $query = Attendance::query()
            ->selectRaw('`attendances`.`employee_id`')
            ->selectRaw('MIN(`attendances`.`id`) as id')
            ->selectRaw('MIN(CASE WHEN `attendances`.`status` = "check_in"  THEN `attendances`.`timestamp` END) as first_check_in')
            ->selectRaw('MAX(CASE WHEN `attendances`.`status` = "check_out" THEN `attendances`.`timestamp` END) as last_check_out')
            ->selectRaw('MIN(`attendances`.`timestamp`) as first_punch')
            ->selectRaw('MAX(`attendances`.`timestamp`) as last_punch')
            ->selectRaw('COUNT(*) as punch_count')
            ->selectRaw('MAX(`attendances`.`verified_by`) as verified_by')
            ->whereBetween('attendances.timestamp', [$start, $end])
            ->groupBy('attendances.employee_id');

        $paginator = $query->orderByDesc('first_punch')->paginate(20)->withQueryString();

        $paginator->load('employee:id,first_name,last_name,employee_id,avatar');

        $rows = Attendance::whereBetween('timestamp', [$start, $end])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $uniqueEmployees = $paginator->total();

        return Inertia::render('Admin/Attendance/Today', [
            'attendances' => $paginator,
            'stats'       => [
                'check_in'         => (int) ($rows['check_in']  ?? 0),
                'check_out'        => (int) ($rows['check_out'] ?? 0),
                'unique_employees' => $uniqueEmployees,
            ],
            'date'        => $date,
            'latestDate'  => $this->latestAttendanceDate(),
            'employees'   => $this->activeEmployeeOptions(),
        ]);
    }

    public function bulkUpload()
    {
        return Inertia::render('Admin/Attendance/BulkUpload');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new AttendanceTemplateExport(),
            'attendance_upload_template_' . date('Y-m-d') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt|max:20480',
        ]);

        $path = $request->file('file')->getRealPath();

        try {
            $content = file_get_contents($path);
            $lines = preg_split('/\r\n|\r|\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not read file: ' . $e->getMessage()]);
        }

        if (empty($lines)) {
            return back()->withErrors(['file' => 'The file is empty.']);
        }

        $employeeMap = Employee::query()
            ->whereNotNull('employee_id')
            ->pluck('id', 'employee_id')
            ->toArray();

        $imported    = 0;
        $duplicates  = 0;
        $unknown     = 0;
        $invalid     = 0;
        $unknownPins = [];

        foreach ($lines as $line) {
            $columns = preg_split('/\t+|  +/', trim($line));

            if (count($columns) < 2) {
                $invalid++;
                continue;
            }

            $pin = trim($columns[0]);
            $timeRaw = trim($columns[1]);

            if ($pin === '' || $timeRaw === '') {
                $invalid++;
                continue;
            }

            $employeeId = $employeeMap[$pin] ?? null;
            if (!$employeeId) {
                $unknown++;
                $unknownPins[$pin] = true;
                continue;
            }

            try {
                $timestamp = Carbon::parse($timeRaw);
            } catch (\Throwable $e) {
                $invalid++;
                continue;
            }

            try {
                $attendance = Attendance::firstOrNew([
                    'employee_id' => $employeeId,
                    'timestamp'   => $timestamp,
                ]);

                if (!$attendance->exists) {
                    $attendance->device_serial = 'TXT_UPLOAD';
                    $attendance->verified_by   = 0;
                    $attendance->save();
                    $imported++;
                } else {
                    $duplicates++;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23000') {
                    $duplicates++;
                } else {
                    Log::error('TXT import row failed', [
                        'pin'   => $pin,
                        'time'  => $timeRaw,
                        'error' => $e->getMessage(),
                    ]);
                    $invalid++;
                }
            }
        }

        $total = $imported + $duplicates + $unknown + $invalid;

        return back()
            ->with('success', "Imported {$imported} record" . ($imported === 1 ? '' : 's') . ' from TXT file.')
            ->with('import_summary', [
                'total'       => $total,
                'imported'    => $imported,
                'duplicates'  => $duplicates,
                'unknown'     => $unknown,
                'unknown_ids' => array_keys($unknownPins),
            ]);
    }

    protected function dayRange(string $date): array
    {
        $d = Carbon::parse($date);
        return [
            $d->copy()->startOfDay(),
            $d->copy()->endOfDay(),
        ];
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereIn(
                'attendances.employee_id',
                Employee::query()
                    ->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name',  'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    })
                    ->pluck('id')
            );
        }

        if ($request->filled('department_id')) {
            $query->whereIn(
                'attendances.employee_id',
                Employee::where('department_id', $request->department_id)->pluck('id')
            );
        }
    }

    protected function groupedQuery(Carbon $start, Carbon $end)
    {
        return Attendance::query()
            ->selectRaw('`attendances`.`employee_id`')
            ->selectRaw('DATE(`attendances`.`timestamp`) as attendance_date')
            ->selectRaw('MIN(`attendances`.`timestamp`) as first_punch')
            ->selectRaw('MAX(`attendances`.`timestamp`) as last_punch')
            ->selectRaw('MIN(CASE WHEN `attendances`.`status` = "check_in"  THEN `attendances`.`timestamp` END) as first_check_in')
            ->selectRaw('MAX(CASE WHEN `attendances`.`status` = "check_out" THEN `attendances`.`timestamp` END) as last_check_out')
            ->selectRaw('COUNT(*) as punch_count')
            ->selectRaw('GROUP_CONCAT(DISTINCT `attendances`.`status`) as statuses')
            ->selectRaw('MIN(`attendances`.`id`) as id')
            ->selectRaw('MAX(`attendances`.`notes`) as notes')
            ->whereBetween('attendances.timestamp', [$start, $end])
            ->groupBy('attendances.employee_id', DB::raw('DATE(`attendances`.`timestamp`)'));
    }

    protected function hydrateEmployees($employeeIds)
    {
        // ✅ Added 'avatar' to eager load so we can generate the URL
        return Employee::with([
            'department:id,name',
            'designation:id,name',
        ])
            ->whereIn('id', collect($employeeIds)->unique()->filter())
            ->get()
            ->keyBy('id');
    }

    protected function activeEmployeeOptions()
    {
        return Employee::where('status', 'active')
            ->orderBy('employee_id')
            ->get(['id', 'employee_id', 'first_name', 'last_name']);
    }

    protected function transformGroupedRow($row, ?Employee $employee): array
    {
        $in  = $row->first_check_in  ? Carbon::parse($row->first_check_in)  : null;
        $out = $row->last_check_out  ? Carbon::parse($row->last_check_out)  : null;

        $firstPunch = $row->first_punch ? Carbon::parse($row->first_punch) : null;
        $lastPunch  = $row->last_punch  ? Carbon::parse($row->last_punch)  : null;

        $datePivot = $in ?? $firstPunch ?? $out ?? $lastPunch;

        $hours = '—';
        if ($in && $out) {
            $minutes = $in->diffInMinutes($out);
            if ($minutes > 60) {
                $minutes -= 60;
            }
            $hours = sprintf('%dh %02dm', intdiv($minutes, 60), $minutes % 60);
        }

        $statuses = explode(',', (string) $row->statuses);
        $status   = 'present';

        if (in_array('check_in', $statuses, true) && !in_array('check_out', $statuses, true)) {
            $status = 'check_in';
        } elseif (in_array('check_out', $statuses, true) && !in_array('check_in', $statuses, true)) {
            $status = 'check_out';
        } elseif ($in && $out) {
            $status = 'present';
        }

        if ($in && $in->format('H:i') > '09:15') {
            $status = 'late';
        }

        return [
            'id'            => $row->id,
            'employee_id'   => $employee?->id,
            'employee_code' => $employee?->employee_id,
            'employee_name' => $employee
                ? trim($employee->first_name . ' ' . $employee->last_name)
                : 'Unknown',
            // ✅ Pass the avatar URL and initials for fallback
            'employee_avatar' => $employee?->avatar_url,
            'employee_initials' => $employee
                ? strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1))
                : 'N/A',
            'department'    => $employee?->department?->name,
            'designation'   => $employee?->designation?->name,
            'date'          => $datePivot?->format('d M, Y') ?? '—',
            'timestamp'     => $datePivot?->toIso8601String(),
            'check_in'      => $in?->format('h:i A'),
            'check_out'     => $out?->format('h:i A'),
            'in_time'       => $in?->format('H:i'),
            'out_time'      => $out?->format('H:i'),
            'total_hours'   => $hours,
            'punch_count'   => (int) $row->punch_count,
            'status'        => $status,
            'notes'         => $row->notes ?? null,
        ];
    }

    protected function computeStats(Carbon $start, Carbon $end): array
    {
        $presentIds = Attendance::whereBetween('timestamp', [$start, $end])
            ->distinct()
            ->pluck('employee_id');

        $lateIds = Attendance::whereBetween('timestamp', [$start, $end])
            ->where('status', 'check_in')
            ->whereTime('timestamp', '>', '09:15:00')
            ->distinct()
            ->pluck('employee_id')
            ->diff($presentIds);

        $totalActive = Employee::where('status', 'active')->count();

        return [
            'present'  => $presentIds->count(),
            'absent'   => max(0, $totalActive - $presentIds->count()),
            'on_leave' => 0,
            'late'     => $presentIds->count() ? $lateIds->count() : 0,
        ];
    }
}
