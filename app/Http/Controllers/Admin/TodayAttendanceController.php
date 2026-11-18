<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\ZKTecoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TodayAttendanceController extends Controller
{
    /**
     * Today's attendance page.
     *
     * GET /admin/attendance/today?date=2026-10-24
     *
     * Prefers explicit ?date=, else the latest date with records, else today.
     * Paginated 20-per-page, ordered by most recent punch.
     */
    public function index(Request $request)
    {
        $date = $request->input('date')
            ?? $this->latestAttendanceDate()
            ?? today()->toDateString();

        $start = Carbon::parse($date)->startOfDay();
        $end   = Carbon::parse($date)->endOfDay();

        // ✅ Group by employee to get 1 row per person with IN and OUT times
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

        // Eager load employee
        $paginator->load('employee:id,first_name,last_name,employee_id,avatar');

        // ✅ Transform the collection to match what the Vue component expects
        $paginator->getCollection()->transform(function ($row) {
            // Map raw timestamps to check_in / check_out strings
            $in = $row->first_check_in ? Carbon::parse($row->first_check_in) : null;
            $out = $row->last_check_out ? Carbon::parse($row->last_check_out) : null;

            return [
                'id'              => $row->id,
                'employee_id'     => $row->employee_id,
                'employee'        => $row->employee,
                'timestamp'       => $row->first_punch, // Used for sorting and formatTime
                'first_check_in'  => $row->first_check_in,
                'last_check_out'  => $row->last_check_out,
                'in_time'         => $in?->format('H:i'),
                'out_time'        => $out?->format('H:i'),
                'punch_count'     => (int) $row->punch_count,
                'verified_by'     => (int) $row->verified_by,
                'status'          => $out ? 'check_out' : 'check_in', // For card colors
            ];
        });

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
        ]);
    }

    /**
     * POST /admin/attendance/today/sync
     * Trigger a device sync, poll for results, reload.
     */
    public function sync(ZKTecoService $zkService)
    {
        try {
            $result = $zkService->syncAttendance();

            return response()->json([
                'success' => true,
                'message' => 'Attendance synced successfully',
                'synced'  => count($result['synced'] ?? []),
                'failed'  => count($result['failed'] ?? []),
            ]);
        } catch (\Throwable $e) {
            Log::error('Today sync failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /admin/attendance/today/sync-status?since=...
     */
    public function syncStatus(Request $request)
    {
        $since = $request->query('since')
            ? Carbon::createFromTimestampMs($request->query('since'))
            : now()->subMinutes(5);

        return response()->json([
            'recent_attendance' => Attendance::where('created_at', '>=', $since)->count(),
        ]);
    }

    /**
     * Most recent date with attendance records.
     */
    protected function latestAttendanceDate(): ?string
    {
        return Attendance::query()
            ->selectRaw('DATE(MAX(`timestamp`)) as latest')
            ->value('latest');
    }
}
