<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'employee_id',
        'employee_code', // ✅ Added
        'timestamp',
        'status',
        'verified_by',
        'device_serial',
        'ip_address',
        'latitude',
        'longitude',
        'notes',
        'created_by',
        'updated_by',
        'in_time',
        'out_time',
    ];

    protected $casts = [
        'timestamp'   => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'latitude'    => 'decimal:8',
        'longitude'   => 'decimal:8',
        'verified_by' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    protected static function booted()
    {
        // 1. Automatically fill employee_code when creating a new record
        static::creating(function ($attendance) {
            if ($attendance->employee_id && !$attendance->employee_code) {
                $employee = Employee::find($attendance->employee_id);
                if ($employee) {
                    $attendance->employee_code = $employee->employee_id;
                }
            }
        });

        // 2. Handle the 12:00 PM in/out split logic
        static::saving(function ($attendance) {
            if ($attendance->timestamp) {
                $time = Carbon::parse($attendance->timestamp);
                $timeString = $time->format('H:i:s');
                $hour = (int) $time->format('H');

                if ($hour < 12) {
                    $attendance->in_time = $timeString;
                    $attendance->status = 'check_in';
                } else {
                    $attendance->out_time = $timeString;
                    $attendance->status = 'check_out';
                }
            }
        });
    }

    /* =========================================================
     |  Scopes — used by AttendanceController
     ========================================================= */

    /**
     * Filter by date range (index-safe).
     *
     * Prefer this over whereDate() because it uses the `idx_att_time`
     * index on the `timestamp` column.
     */
    public function scopeBetweenDates(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('timestamp', [$start, $end]);
    }

    /**
     * Filter by punch status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Filter by employee.
     */
    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Filter by device serial.
     */
    public function scopeForDevice(Builder $query, string $serial): Builder
    {
        return $query->where('device_serial', $serial);
    }
}
