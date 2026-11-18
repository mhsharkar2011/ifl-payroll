<?php

namespace App\Jobs;

use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Job timeout in seconds
     */
    public $timeout = 600;

    /**
     * Number of tries
     */
    public $tries = 1;

    public function __construct(
        public string $type = 'auto',
        public ?string $notes = null
    ) {}

    public function handle(BackupService $service): void
    {
        try {
            Log::info('🔄 Auto backup started', [
                'type'  => $this->type,
                'notes' => $this->notes,
            ]);

            $backup = $service->createBackup($this->type, $this->notes);

            Log::info('✅ Auto backup completed', [
                'filename' => $backup->filename,
                'size'     => $backup->human_size,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Auto backup failed: ' . $e->getMessage());

            // Optionally re-throw for retry
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ CreateBackupJob failed permanently', [
            'message' => $exception->getMessage(),
        ]);
    }
}
