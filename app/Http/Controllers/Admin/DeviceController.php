<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Device;
use App\Services\ZKTecoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DeviceController extends Controller
{
    /** Threshold for the "fresh pull" badge (seconds). */
    private const FRESH_PULL_SECONDS = 300; // 5 minutes

    /* ============================================================
     |  INDEX
     * ============================================================ */
    public function index()
    {
        $now       = Carbon::now();
        $freshAt   = $now->copy()->subSeconds(self::FRESH_PULL_SECONDS);
        $dayStart  = $now->copy()->startOfDay();
        $dayEnd    = $now->copy()->endOfDay();

        $devices = Device::query()
            ->withCount([
                'attendances as today_attendance' => fn($q) =>
                $q->whereBetween('timestamp', [$dayStart, $dayEnd]),
                'attendances as total_attendance',
            ])
            ->orderBy('name')
            ->get()
            ->each(function (Device $device) use ($freshAt) {
                // "Fresh" now means: pulled successfully within the last 5 min
                $device->is_fresh = $device->last_sync_at
                    && $device->last_sync_at->gte($freshAt);
            });

        $stats = [
            'total'        => $devices->count(),
            'active'       => $devices->where('is_active', true)->count(),
            'fresh'        => $devices->where('is_fresh', true)->count(),
            'stale'        => $devices->where('is_fresh', false)->count(),
            'synced_today' => Attendance::whereBetween('timestamp', [$dayStart, $dayEnd])->count(),
        ];

        return Inertia::render('Admin/Devices/Index', [
            'devices' => $devices,
            'stats'   => $stats,
        ]);
    }

    /* ============================================================
     |  CREATE
     * ============================================================ */
    public function create()
    {
        return Inertia::render('Admin/Devices/Create');
    }

    /* ============================================================
     |  STORE
     * ============================================================ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'ip_address'    => 'required|ip',
            'port'          => 'required|integer|min:1|max:65535',
            'password'      => 'nullable|integer|min:0',
            'timeout'       => 'nullable|integer|min:5|max:300',
            'serial_number' => 'nullable|string|max:100|unique:devices,serial_number',
            'model'         => 'nullable|string|max:100',
            'is_active'     => 'boolean',
            'notes'         => 'nullable|string|max:500',
        ]);

        $exists = Device::where('ip_address', $validated['ip_address'])
            ->where('port', $validated['port'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['ip_address' => 'A device with this IP:Port already exists'])
                ->withInput();
        }

        Device::create([
            'name'          => $validated['name'],
            'ip_address'    => $validated['ip_address'],
            'port'          => $validated['port'] ?? 4370,
            'password'      => $validated['password'] ?? 0,
            'timeout'       => $validated['timeout'] ?? 30,
            'serial_number' => $validated['serial_number'] ?? null,
            'model'         => $validated['model'] ?? null,
            'is_active'     => $validated['is_active'] ?? true,
            'notes'         => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.devices.index')
            ->with('success', '✅ Device created successfully!');
    }

    /* ============================================================
     |  SHOW
     * ============================================================ */
    public function show(Device $device)
    {
        $todayAttendance = Attendance::where('device_serial', $device->serial_number)
            ->whereBetween('timestamp', [today()->startOfDay(), today()->endOfDay()])
            ->count();

        $totalAttendance = Attendance::where('device_serial', $device->serial_number)->count();

        $recentAttendance = Attendance::with('employee:id,first_name,last_name,employee_id,avatar')
            ->where('device_serial', $device->serial_number)
            ->latest('timestamp')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Devices/Show', [
            'device' => $device,
            'stats'  => [
                'today_attendance' => $todayAttendance,
                'total_attendance' => $totalAttendance,
            ],
            'recentAttendance' => $recentAttendance,
        ]);
    }

    /* ============================================================
     |  EDIT
     * ============================================================ */
    public function edit(Device $device)
    {
        return Inertia::render('Admin/Devices/Edit', [
            'device' => $device,
        ]);
    }

    /* ============================================================
     |  UPDATE
     * ============================================================ */
    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'ip_address'    => 'required|ip',
            'port'          => 'required|integer|min:1|max:65535',
            'password'      => 'nullable|integer|min:0',
            'timeout'       => 'nullable|integer|min:5|max:300',
            'serial_number' => 'nullable|string|max:100|unique:devices,serial_number,' . $device->id,
            'model'         => 'nullable|string|max:100',
            'is_active'     => 'boolean',
            'notes'         => 'nullable|string|max:500',
        ]);

        $device->update($validated);

        return redirect()
            ->route('admin.devices.index')
            ->with('success', '✅ Device updated successfully!');
    }

    /* ============================================================
     |  DESTROY
     * ============================================================ */
    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()
            ->route('admin.devices.index')
            ->with('success', '✅ Device deleted successfully!');
    }

    /* ============================================================
     |  TEST CONNECTION  — actually tries TCP 4370
     * ============================================================ */
    public function testConnection(Device $device)
    {
        $host    = $device->ip_address;
        $port    = $device->port ?: 4370;
        $timeout = 3;

        Log::info('Device test', [
            'device_id' => $device->id,
            'ip'        => $host,
            'port'      => $port,
        ]);

        // 1. TCP reachability test
        $conn = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if (!$conn) {
            return response()->json([
                'success' => false,
                'message' => "❌ Cannot reach {$host}:{$port} — {$errstr} ({$errno})",
                'info'    => [
                    'ip'         => $host,
                    'port'       => $port,
                    'error'      => $errstr,
                    'error_code' => $errno,
                    'hint'       => 'Check the device is powered on, on the network, and the port is correct.',
                ],
            ], 400);
        }

        fclose($conn);

        // 2. TCP 4370 works — pull mode is possible.
        // (We do NOT touch last_sync_at — that only changes on a successful pull.)
        return response()->json([
            'success' => true,
            'message' => "✅ Device reachable on TCP {$port} (Pull mode).",
            'info'    => [
                'ip'            => $host,
                'port'          => $port,
                'serial_number' => $device->serial_number,
                'model'         => $device->model,
                'mode'          => 'pull',
                'last_pulled'   => $device->last_sync_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /* ============================================================
     |  SYNC — real pull from the device
     * ============================================================ */
    public function syncAttendance(Device $device, ZKTecoService $zk)
    {
        Log::info('Pulling attendance from device', [
            'device_id' => $device->id,
            'ip'        => $device->ip_address,
        ]);

        try {
            // Pull from this specific device
            $result = $zk->syncDevice($device);

            // $result should look like:
            //   ['pulled' => N, 'inserted' => N, 'skipped' => N, 'unknown' => N]

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    '✅ Pulled %d records — %d new, %d duplicates, %d unknown.',
                    $result['pulled']   ?? 0,
                    $result['inserted'] ?? 0,
                    $result['skipped']  ?? 0,
                    $result['unknown']  ?? 0
                ),
                'synced'  => $result['inserted'] ?? 0,
                'total'   => Attendance::where('device_serial', $device->serial_number)->count(),
                'details' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Device pull failed', [
                'device_id' => $device->id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Pull failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     |  PULL ALL — pull from every active device
     * ============================================================ */
    public function pullAll(ZKTecoService $zk)
    {
        try {
            $result = $zk->syncAttendance(); // iterates devices internally

            $synced = collect($result['synced'] ?? [])->sum('inserted');
            $failed = count($result['failed'] ?? []);

            return response()->json([
                'success' => $failed === 0,
                'message' => $failed === 0
                    ? "✅ Pulled from all devices — {$synced} new records."
                    : "⚠️ Pulled {$synced} records; {$failed} device(s) failed.",
                'synced'  => $synced,
                'details' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Pull-all failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => '❌ Pull-all failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     |  STATUS — pull freshness
     * ============================================================ */
    public function status(Device $device)
    {
        $lastPull = $device->last_sync_at;
        $secondsAgo = $lastPull ? $lastPull->diffInSeconds(now()) : null;

        $state = 'never';
        if ($secondsAgo !== null) {
            if ($secondsAgo < self::FRESH_PULL_SECONDS) {
                $state = 'fresh';
            } else {
                $state = 'stale';
            }
        }

        return response()->json([
            'success' => true,
            'device'  => $device,
            'status'  => [
                'state'          => $state,
                'last_pull'      => $lastPull?->format('Y-m-d H:i:s'),
                'seconds_ago'    => $secondsAgo,
            ],
        ]);
    }


    public function pingAll()
    {
        $devices = Device::where('is_active', true)->get(['id', 'ip_address', 'port']);

        $results = $devices->map(function (Device $device) {
            $ip   = $device->ip_address;
            $port = $device->port ?: 4370;
            $started = microtime(true);

            $conn = @fsockopen($ip, $port, $errno, $errstr, 1);
            $reachable = (bool) $conn;
            if ($conn) fclose($conn);

            return [
                'id'         => $device->id,
                'is_online'  => $reachable,
                'latency_ms' => round((microtime(true) - $started) * 1000, 1),
                'error'      => $reachable ? null : ($errstr ?: 'Unreachable'),
            ];
        });

        return response()->json([
            'checked_at' => now()->toIso8601String(),
            'devices'    => $results,
        ]);
    }


}
