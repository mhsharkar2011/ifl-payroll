<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceToday extends Model
{
    use HasFactory;

    protected $table = 'attendances_today';

    protected $fillable = [
        'employee_id',
        'timestamp',
        'status',
        'verified_by',
        'device_serial',
        'ip_address',
        'latitude',
        'longitude',
        'notes',
        'attendance_date',
    ];

    protected $casts = [
        'timestamp'       => 'datetime',
        'attendance_date' => 'date',
        'latitude'        => 'decimal:8',
        'longitude'       => 'decimal:8',
        'verified_by'     => 'integer',
    ];

    /**
     * IMPORTANT: `attendance_date` must stay in sync with `timestamp`.
     * Any time `timestamp` changes, `attendance_date` must also change.
     * Enforce this at the model level so it can't drift.
     */
    protected static function booted(): void
    {
        static::saving(function (AttendanceToday $model) {
            if ($model->isDirty('timestamp') && $model->timestamp) {
                $model->attendance_date = $model->timestamp->toDateString();
            }
        });
    }

    /* ============================================================
     |  Relationships
     * ============================================================ */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /* ============================================================
     |  Scopes
     * ============================================================ */

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('attendance_date', today());
    }

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeCheckIns(Builder $query): Builder
    {
        return $query->where('status', 'check_in');
    }

    public function scopeCheckOuts(Builder $query): Builder
    {
        return $query->where('status', 'check_out');
    }

    /* ============================================================
     |  Static Helpers
     * ============================================================ */

    /**
     * Record a punch in today's table.
     *
     * Uses the DB's unique index on (employee_id, timestamp, status)
     * to prevent duplicates. The unique index MUST exist for this to
     * be safe under concurrent inserts.
     */
    public static function record(array $data): self
    {
        if (empty($data['employee_id'])) {
            throw new \InvalidArgumentException('employee_id is required');
        }

        if (empty($data['timestamp'])) {
            throw new \InvalidArgumentException('timestamp is required');
        }

        if (empty($data['status'])) {
            throw new \InvalidArgumentException('status is required');
        }

        $timestamp = $data['timestamp'] instanceof Carbon
            ? $data['timestamp']
            : Carbon::parse($data['timestamp']);

        $data['timestamp']       = $timestamp;
        $data['attendance_date'] = $timestamp->toDateString();

        // firstOrCreate on the unique key: (employee_id, timestamp, status)
        return self::firstOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'timestamp'   => $timestamp,
                'status'      => $data['status'],
            ],
            $data
        );
    }

    /**
     * Today's stats — one query with conditional aggregates.
     */
    public static function todayStats(): array
    {
        $row = self::query()
            ->whereDate('attendance_date', today())
            ->selectRaw('SUM(CASE WHEN status = "check_in"  THEN 1 ELSE 0 END) as check_in')
            ->selectRaw('SUM(CASE WHEN status = "check_out" THEN 1 ELSE 0 END) as check_out')
            ->selectRaw('COUNT(DISTINCT employee_id) as unique_employees')
            ->selectRaw('COUNT(*) as total_records')
            ->first();

        return [
            'check_in'         => (int) ($row->check_in         ?? 0),
            'check_out'        => (int) ($row->check_out        ?? 0),
            'unique_employees' => (int) ($row->unique_employees ?? 0),
            'total_records'    => (int) ($row->total_records    ?? 0),
        ];
    }

    /**
     * Records not yet merged to the main `attendances` table.
     *
     * Assumes you have a `merged_at` column on `attendances_today`.
     * If you don't have it yet, add it via migration — see below.
     */
    public static function pendingMerge(): Builder
    {
        return self::whereNull('merged_at')
            ->orderBy('attendance_date')
            ->orderBy('timestamp');
    }
}
