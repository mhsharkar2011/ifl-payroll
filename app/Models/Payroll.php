<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'total_hours',
        'overtime_hours',
        'overtime_amount',
        'off_day_hours',
        'off_day_amount',
        'total_earnings',
        'deductions',
        'net_salary',
        'status',
        'generated_at',
        'paid_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'paid_at' => 'datetime',
        'basic_salary' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'off_day_hours' => 'decimal:2',
        'off_day_amount' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
