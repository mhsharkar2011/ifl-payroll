<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Device;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use MshadyDev\ZKTeco\ZKTeco;

class ZKTecoService
{
    /**
     * Pull attendance from all active devices.
     */
    public function syncAttendance(): array
    {
        $synced = [];
        $failed = [];

        foreach (Device::where('is_active', true)->get() as $device) {
            try {
                $result = $this->pullFromDevice($device);
                $synced[] = $result;
            } catch (\Throwable $e) {
                Log::error("ZKTeco pull failed for {$device->name}", [
                    'device_id' => $device->id,
                    'ip'        => $device->ip_address,
                    'error'     => $e->getMessage(),
                ]);
                $failed[] = [
                    'device' => $device->name,
                    'error'  => $e->getMessage(),
                ];
            }
        }

        return ['synced' => $synced, 'failed' => $failed];
    }

    /**
     * Pull from one device (public — used by DeviceController@syncAttendance).
     */
    public function syncDevice(Device $device): array
    {
        return $this->pullFromDevice($device);
    }

    /**
     * Internal pull implementation.
     */
    protected function pullFromDevice(Device $device): array
    {
        $zk = new ZKTeco(
            $device->ip_address,
            $device->port ?? 4370,
            $device->timeout ?? 30,
            (int) ($device->password ?? 0)   // comm key
        );

        $zk->connect();
        $zk->disableDevice();

        try {
            // Returns [uid => ['id' => '150026', 'timestamp' => '2026-10-24 09:15:42', 'state' => 0, 'type' => 1], ...]
            $records = $zk->getAttendance();

            Log::info("ZKTeco pulled " . count($records) . " raw records from {$device->name}");

            if (empty($records)) {
                $device->forceFill(['last_sync_at' => now(), 'last_seen_at' => now()])->saveQuietly();
                return ['device' => $device->name, 'pulled' => 0, 'inserted' => 0, 'skipped' => 0, 'unknown' => 0];
            }

            // Employee maps
            $byDeviceId   = Employee::whereNotNull('device_user_id')->pluck('id', 'device_user_id')->all();
            $byEmployeeId = Employee::whereNotNull('employee_id')->pluck('id', 'employee_id')->all();

            $inserted = $skipped = $unknown = 0;

            foreach ($records as $record) {
                $pin       = trim((string) ($record['id'] ?? ''));
                $timeStr   = trim((string) ($record['timestamp'] ?? ''));
                $stateCode = (int) ($record['state'] ?? 0);
                $typeCode  = (int) ($record['type'] ?? 1);

                // Look up employee
                $employeeId = $byDeviceId[$pin] ?? $byEmployeeId[$pin] ?? null;
                if (!$employeeId) {
                    $unknown++;
                    continue;
                }

                // Parse timestamp
                try {
                    $timestamp = Carbon::parse($timeStr);
                } catch (\Throwable) {
                    $skipped++;
                    continue;
                }

                // Map status
                $status = match ($stateCode) {
                    0 => 'check_in',
                    1 => 'check_out',
                    2 => 'break_out',
                    3 => 'break_in',
                    4 => 'overtime_in',
                    5 => 'overtime_out',
                    default => 'check_in',
                };

                // Map verification
                $verifiedBy = match ($typeCode) {
                    1 => 1, // Fingerprint
                    2 => 2, // Face
                    3 => 3, // Card
                    4 => 0, // Password
                    default => 0,
                };

                try {
                    Attendance::create([
                        'employee_id'   => $employeeId,
                        'device_serial' => $device->serial_number,
                        'timestamp'     => $timestamp,
                        'status'        => $status,
                        'verified_by'   => $verifiedBy,
                    ]);
                    $inserted++;
                } catch (\Illuminate\Database\QueryException $e) {
                    // Duplicate on unique index → skip
                    if ($e->getCode() === '23000') {
                        $skipped++;
                    } else {
                        throw $e;
                    }
                }
            }

            // Update device state (successful pull = "seen")
            $device->forceFill([
                'last_sync_at' => now(),
                'last_seen_at' => now(),
            ])->saveQuietly();

            return [
                'device'   => $device->name,
                'pulled'   => count($records),
                'inserted' => $inserted,
                'skipped'  => $skipped,
                'unknown'  => $unknown,
            ];
        } finally {
            // Always release the device, even on exception
            try { $zk->enableDevice(); } catch (\Throwable) {}
            try { $zk->disconnect();   } catch (\Throwable) {}
        }
    }
}
