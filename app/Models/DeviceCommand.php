<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 *
 * This model exists for the Push SDK (ADMS) workflow, which requires
 * firmware support on the ZKTeco device.
 *
 * The current device (MB460) only supports PULL mode over TCP 4370.
 * Pull mode has no command queue — the server downloads attendance
 * and no commands are pushed to the device.
 *
 * This model is kept for archival purposes only. Its methods return
 * empty results so that any accidental call cannot cause errors or
 * phantom "commands sent" UI states.
 *
 * Remove this model (and the device_commands table) once you're sure
 * no other code references it:
 *
 *     grep -rn "DeviceCommand" app/ routes/
 */
class DeviceCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_serial',
        'command',
        'payload',
        'status',
        'sent_at',
        'completed_at',
        'return_code',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'completed_at' => 'datetime',
    ];

    /* =========================================================
     |  Query scopes (kept for compatibility)
     * ========================================================= */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeForDevice($query, string $deviceSerial)
    {
        return $query->where('device_serial', $deviceSerial);
    }

    /* =========================================================
     |  Static helpers — deprecated, return safe no-ops
     * ========================================================= */

    /**
     * @deprecated Pull mode does not queue commands.
     *
     * Creates a DB record for backward compatibility but logs a
     * warning so you can find lingering call sites.
     */
    public static function queue(string $deviceSerial, string $command, ?string $payload = null): self
    {
        Log::warning('DeviceCommand::queue() called but device is in PULL mode. Command will not be sent.', [
            'device_serial' => $deviceSerial,
            'command'       => $command,
        ]);

        return self::create([
            'device_serial' => $deviceSerial,
            'command'       => $command,
            'payload'       => $payload,
            'status'        => 'abandoned', // not 'pending' — won't be picked up
        ]);
    }

    /**
     * @deprecated Pull mode does not poll for commands.
     */
    public static function nextFor(string $deviceSerial): ?self
    {
        if (!$deviceSerial) {
            return null;
        }

        Log::warning('DeviceCommand::nextFor() called but device is in PULL mode.');

        // Never returns a "pending" row → nothing is sent to the device
        return null;
    }

    /**
     * @deprecated Pull mode has no command queue.
     */
    public static function claimForDevice(string $deviceSerial, int $limit = 10): array
    {
        if (!$deviceSerial) {
            return [];
        }

        Log::warning('DeviceCommand::claimForDevice() called but device is in PULL mode.');

        // Return empty — device never receives anything
        return [];
    }

    /* =========================================================
     |  Diagnostic helper (not deprecated — still useful)
     * ========================================================= */

    /**
     * Count abandoned commands (leftover from the push-era).
     * Use this to safely clean up old rows.
     */
    public static function countAbandoned(?string $deviceSerial = null): int
    {
        $query = self::whereIn('status', ['pending', 'sent']);

        if ($deviceSerial) {
            $query->where('device_serial', $deviceSerial);
        }

        return $query->count();
    }
}
