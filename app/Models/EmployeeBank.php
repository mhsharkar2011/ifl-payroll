<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'bank_name',
        'bank_account_number',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
