<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ZKTecoService;
use Illuminate\Support\Facades\Log;

class SyncAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ZKTecoService $zkService)
    {
        try {
            Log::info('Attendance sync started');
            $synced = $zkService->syncAttendance();
            Log::info('Attendance synced successfully', ['count' => count($synced)]);
        } catch (\Exception $e) {
            Log::error('Attendance sync failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
