<?php

namespace App\Services;

use App\Models\Backup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupService
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');

        // Create folder if not exists
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /* =========================================================
     |  BACKUP
     ========================================================= */

    /**
     * Create database backup
     */
    public function createBackup(string $type = 'manual', ?string $notes = null): Backup
    {
        $filename = 'backup_' . date('Y_m_d_His') . '.sql';
        $fullPath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        // Create backup record
        $backup = Backup::create([
            'filename'        => $filename,
            'disk'            => 'local',
            'path'            => $fullPath,
            'size'            => 0,
            'type'            => $type,
            'status'          => 'pending',
            'last_action'     => 'backup',
            'status_message'  => 'Backup in progress...',
            'last_action_at'  => now(),
            'notes'           => $notes,
            'created_by'      => Auth::id() ?? null,
        ]);

        try {
            // Generate SQL dump
            $sql = $this->generateSqlDump();

            // Save to file
            File::put($fullPath, $sql);

            // Update backup record → success
            $backup->update([
                'size'           => File::size($fullPath),
                'status'         => 'completed',
                'status_message' => 'Backup completed',
                'last_action_at' => now(),
            ]);

            Log::info('Backup created successfully', [
                'filename' => $filename,
                'size'     => $backup->size,
            ]);

            return $backup->fresh();

        } catch (\Exception $e) {
            // Update backup record → failure
            $backup->update([
                'status'         => 'failed',
                'status_message' => 'Backup failed: ' . $e->getMessage(),
                'last_action_at' => now(),
            ]);

            Log::error('Backup failed: ' . $e->getMessage());

            throw new \Exception('Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate SQL dump using mysqldump or PHP
     */
    protected function generateSqlDump(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $result = $this->tryMysqldump();
            if ($result !== null) {
                return $result;
            }
        }

        return $this->generatePhpDump();
    }

    /**
     * Try to use mysqldump command
     */
    protected function tryMysqldump(): ?string
    {
        try {
            $host     = config('database.connections.mysql.host');
            $port     = config('database.connections.mysql.port', 3306);
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            $mysqldumpPaths = [
                'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
                'C:\laragon\bin\mysql\*\bin\mysqldump.exe',
                'C:\xampp\mysql\bin\mysqldump.exe',
                'C:\wamp64\bin\mysql\*\bin\mysqldump.exe',
                'mysqldump',
            ];

            $mysqldump = null;
            foreach ($mysqldumpPaths as $path) {
                if (strpos($path, '*') !== false) {
                    $matches = glob($path);
                    if (!empty($matches)) {
                        $mysqldump = $matches[0];
                        break;
                    }
                } elseif (file_exists($path) || $path === 'mysqldump') {
                    $mysqldump = $path;
                    break;
                }
            }

            if (!$mysqldump) {
                return null;
            }

            $cmd = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s %s 2>&1',
                $mysqldump,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password ? '--password=' . escapeshellarg($password) : '',
                escapeshellarg($database)
            );

            $output = shell_exec($cmd);

            if ($output && strpos($output, 'mysqldump:') === false && strlen($output) > 100) {
                return $output;
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('mysqldump failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * PHP-based SQL dump (fallback)
     */
    protected function generatePhpDump(): string
    {
        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.mysql.database');
        $key = 'Tables_in_' . $database;

        $sql = "-- Database Backup\n";
        $sql .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key;

            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "\n-- Table: {$tableName}\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ((array) $row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(',', $rowValues) . ')';
                }

                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /* =========================================================
     |  RESTORE
     ========================================================= */

    /**
     * Restore database from backup.
     * Updates status → 'restore completed' or 'restore failed'.
     */
    public function restoreBackup(Backup $backup): bool
    {
        if (!$backup->fileExists()) {
            $backup->update([
                'status'         => 'failed',
                'last_action'    => 'restore',
                'status_message' => 'Restore failed: backup file not found',
                'last_action_at' => now(),
            ]);

            throw new \Exception('Backup file not found');
        }

        // Mark as "restore in progress"
        $backup->update([
            'status'         => 'pending',
            'last_action'    => 'restore',
            'status_message' => 'Restore in progress...',
            'last_action_at' => now(),
        ]);

        try {
            $sql = File::get($backup->path);

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $statements = $this->parseSqlStatements($sql);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    DB::statement($statement);
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // ✅ Success
            $backup->update([
                'status'         => 'completed',
                'last_action'    => 'restore',
                'status_message' => 'Restore completed',
                'restored_at'    => now(),
                'last_action_at' => now(),
            ]);

            Log::info('Backup restored', [
                'backup_id' => $backup->id,
                'filename'  => $backup->filename,
            ]);

            return true;

        } catch (\Exception $e) {
            // Re-enable foreign keys safely
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $t) {
                // ignore
            }

            // ❌ Failure
            $backup->update([
                'status'         => 'failed',
                'last_action'    => 'restore',
                'status_message' => 'Restore failed: ' . $e->getMessage(),
                'last_action_at' => now(),
            ]);

            Log::error('Restore failed: ' . $e->getMessage());

            throw new \Exception('Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse SQL statements safely
     */
    protected function parseSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';

        $length = strlen($sql);
        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prevChar = $i > 0 ? $sql[$i - 1] : '';

            if (($char === '"' || $char === "'") && $prevChar !== '\\') {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }

            if ($char === ';' && !$inString) {
                $statements[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }

        return array_filter($statements);
    }

    /* =========================================================
     |  DELETE / CLEAN / INFO
     ========================================================= */

    public function deleteBackup(Backup $backup): bool
    {
        try {
            if ($backup->fileExists()) {
                File::delete($backup->path);
            }

            $backup->delete();

            Log::info('Backup deleted', ['backup_id' => $backup->id]);

            return true;

        } catch (\Exception $e) {
            Log::error('Backup delete failed: ' . $e->getMessage());
            throw new \Exception('Delete failed: ' . $e->getMessage());
        }
    }

    public function cleanOldBackups(int $days = 30): int
    {
        $cutoff = Carbon::now()->subDays($days);

        $oldBackups = Backup::where('created_at', '<', $cutoff)->get();
        $count = 0;

        foreach ($oldBackups as $backup) {
            try {
                $this->deleteBackup($backup);
                $count++;
            } catch (\Exception $e) {
                Log::warning("Failed to delete old backup {$backup->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    public function getDatabaseInfo(): array
    {
        try {
            $database = config('database.connections.mysql.database');
            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . $database;

            $totalSize = 0;
            foreach ($tables as $table) {
                $tableName = $table->$key;
                $size = DB::select("SELECT
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size
                    FROM information_schema.TABLES
                    WHERE table_schema = ? AND table_name = ?",
                    [$database, $tableName]
                );

                if (!empty($size)) {
                    $totalSize += $size[0]->size ?? 0;
                }
            }

            return [
                'database'    => $database,
                'table_count' => count($tables),
                'total_size'  => round($totalSize, 2) . ' MB',
                'driver'      => DB::connection()->getDriverName(),
            ];

        } catch (\Exception $e) {
            return [
                'database'    => config('database.connections.mysql.database'),
                'table_count' => 0,
                'total_size'  => '0 MB',
                'driver'      => 'unknown',
            ];
        }
    }
}
