<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'timezone',
        'language',
        'theme',
        'notify_email',
        'notify_push',
        'notify_weekly',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'notify_email'      => 'boolean',
        'notify_push'       => 'boolean',
        'notify_weekly'     => 'boolean',
    ];


    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
