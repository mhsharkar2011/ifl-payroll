<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffDayDuty extends Model
{
    protected $fillable = [
        'employee_id',
        'duty_date',
        'hours_worked',
        'compensatory_leave_days', // ✅ Added
        'hourly_rate',
        'total_amount',
        'reason',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'duty_date' => 'date',
        'hours_worked' => 'float',
        'compensatory_leave_days' => 'float', // ✅ Added
        'hourly_rate' => 'float',
        'total_amount' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
