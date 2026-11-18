<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'device_user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department_id',
        'designation_id',
        'salary',
        'status',
        'joining_date',
        'date_of_birth',
        'address',
        'avatar',
    ];

    protected $casts = [
        'joining_date'  => 'date',
        'date_of_birth' => 'date',
        'salary'        => 'decimal:2',
    ];

    protected $appends = ['avatar_url'];

    /* ---------- Relations ---------- */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function salary()
    {
        return $this->hasMany(Salary::class);
    }

    /* ---------- Accessors ---------- */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }


    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }

            // Generate the storage URL based on APP_URL
            return asset('storage/' . ltrim($this->avatar, '/'));
        }
        return null;
    }

    /* ---------- Scopes ---------- */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }


    public function bank()
{
    return $this->hasOne(EmployeeBank::class);
}

}
