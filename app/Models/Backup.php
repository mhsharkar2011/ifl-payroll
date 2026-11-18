<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'disk',
        'path',
        'size',
        'type',
        'status',
        'last_action',
        'status_message',
        'last_action_at',
        'restored_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_action_at' => 'datetime',
        'restored_at'    => 'datetime',
    ];

    /**
     * Relationship with User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get size in human readable format
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /**
     * Check if file exists
     */
    public function fileExists(): bool
    {
        return file_exists($this->path);
    }
}
