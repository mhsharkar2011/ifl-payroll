<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'device_id',
        'password',
        'timeout',
        'serial_number',
        'model',
        'platform',
        'version',
        'is_active',
        'last_sync_at',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_seen_at'  => 'datetime',
        'port' => 'integer',
        'device_id' => 'integer',
        'password' => 'integer',
        'timeout' => 'integer',
    ];

    public function attendances()
    {
        // Explicit keys because the FK isn't `device_id`
        return $this->hasMany(Attendance::class, 'device_serial', 'serial_number');
    }


    /**
     * Command queue relationship.
     */
    public function commands()
    {
        return $this->hasMany(DeviceCommand::class, 'device_serial', 'serial_number');
    }
}
