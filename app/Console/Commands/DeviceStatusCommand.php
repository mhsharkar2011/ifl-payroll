<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZKTecoService;

class DeviceStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check ZKTeco device status';

    /**
     * @var ZKTecoService
     */
    protected $zkService;

    /**
     * Create a new command instance.
     */
    public function __construct(ZKTecoService $zkService)
    {
        parent::__construct();
        $this->zkService = $zkService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('📊 ZKTeco Device Status');
            $this->newLine();

            $info = $this->zkService->getDeviceInfo();

            $this->table(
                ['Property', 'Value'],
                [
                    ['IP Address', $info['ip'] ?? 'N/A'],
                    ['Port', $info['port'] ?? 'N/A'],
                    ['Device Name', $info['device_name'] ?? 'N/A'],
                    ['Version', $info['version'] ?? 'N/A'],
                    ['Serial Number', $info['serial_number'] ?? 'N/A'],
                    ['Platform', $info['platform'] ?? 'N/A'],
                    ['Device Time', $info['time'] ?? 'N/A'],
                    ['Total Users', $info['user_count'] ?? 'N/A'],
                    ['Attendance Logs', $info['attendance_count'] ?? 'N/A'],
                    ['Fingerprint Count', $info['fingerprint_count'] ?? 'N/A'],
                    ['Face Count', $info['face_count'] ?? 'N/A'],
                ]
            );

            $this->newLine();
            $this->info('✅ Device is operational');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to get device status: ' . $e->getMessage());
            return 1;
        }
    }
}
