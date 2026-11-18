<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Show backup list
     */
    public function index()
    {
        $backups = Backup::with('creator:id,name')
            ->latest()
            ->paginate(20);

        $dbInfo = $this->backupService->getDatabaseInfo();

        return Inertia::render('Admin/Settings/Backups/Index', [
            'backups' => $backups,
            'dbInfo'  => $dbInfo,
            'stats'   => [
                'total'      => Backup::count(),
                'completed'  => Backup::where('status', 'completed')->count(),
                'failed'     => Backup::where('status', 'failed')->count(),
                'total_size' => Backup::where('status', 'completed')->sum('size'),
            ],
        ]);
    }

    /**
     * Create new backup
     */
    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $backup = $this->backupService->createBackup(
                'manual',
                $request->notes
            );

            return redirect()
                ->route('admin.backups.index')
                ->with('success', "✅ Backup created: {$backup->filename} ({$backup->human_size})");
        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Backup failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Download backup file
     */
    public function download(Backup $backup)
    {
        if (!$backup->fileExists()) {
            abort(404, 'Backup file not found');
        }

        return response()->download($backup->path, $backup->filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Restore database from backup
     */
    public function restore(Request $request, Backup $backup)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        try {
            $this->backupService->restoreBackup($backup);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', "✅ Database restored from: {$backup->filename}");
        } catch (\Exception $e) {
            Log::error('Restore failed: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Restore failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete backup
     */
    public function destroy(Backup $backup)
    {
        try {
            $filename = $backup->filename;
            $fileExisted = $backup->fileExists();

            $this->backupService->deleteBackup($backup);

            $message = $fileExisted
                ? "✅ Backup and file deleted: {$filename}"
                : "✅ Backup record deleted: {$filename} (file was missing)";

            return redirect()
                ->route('admin.backups.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Delete failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Clean old backups
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $count = $this->backupService->cleanOldBackups($request->days);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', "✅ Cleaned {$count} old backups (older than {$request->days} days)");
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Clean failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get database info (API)
     */
    public function info()
    {
        return response()->json($this->backupService->getDatabaseInfo());
    }
}
