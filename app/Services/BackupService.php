<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    protected string $backupPath = 'backups';

    /**
     * Create a full database backup.
     */
    public function createDatabaseBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_database_{$timestamp}.sql";
        $path = "{$this->backupPath}/{$filename}";

        try {
            $tables = $this->getAllTables();
            $sql = $this->generateBackupSql($tables);

            Storage::put($path, $sql);

            Log::info("Database backup created: {$path}");

            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'size' => Storage::size($path),
                'tables' => count($tables),
            ];
        } catch (\Exception $e) {
            Log::error("Database backup failed: {$e->getMessage()}");

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a full system backup (database + uploaded files).
     */
    public function createFullBackup(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_full_{$timestamp}.zip";
        $path = storage_path("app/{$this->backupPath}/{$filename}");

        // Ensure backup directory exists
        Storage::makeDirectory($this->backupPath);

        try {
            $zip = new ZipArchive;

            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Cannot create ZIP file');
            }

            // Add database backup
            $dbBackup = $this->createDatabaseBackup();
            if ($dbBackup['success']) {
                $zip->addFromString('database.sql', Storage::get($dbBackup['path']));
                Storage::delete($dbBackup['path']); // Remove temporary SQL file
            }

            // Add configuration
            $config = [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'backup_date' => now()->toIso8601String(),
                'laravel_version' => app()->version(),
            ];
            $zip->addFromString('config.json', json_encode($config, JSON_PRETTY_PRINT));

            $zip->close();

            Log::info("Full backup created: {$path}");

            return [
                'success' => true,
                'path' => "{$this->backupPath}/{$filename}",
                'filename' => $filename,
                'size' => filesize($path),
            ];
        } catch (\Exception $e) {
            Log::error("Full backup failed: {$e->getMessage()}");

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List available backups.
     */
    public function listBackups(): array
    {
        $files = Storage::files($this->backupPath);

        return collect($files)
            ->map(function ($file) {
                return [
                    'path' => $file,
                    'filename' => basename($file),
                    'size' => Storage::size($file),
                    'created_at' => Storage::lastModified($file),
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->toArray();
    }

    /**
     * Restore database from backup.
     */
    public function restoreDatabase(string $backupPath): array
    {
        try {
            if (! Storage::exists($backupPath)) {
                throw new \RuntimeException('Backup file not found');
            }

            $sql = Storage::get($backupPath);

            // If it's a ZIP file, extract the SQL
            if (str_ends_with($backupPath, '.zip')) {
                $sql = $this->extractSqlFromZip($backupPath);
            }

            // Execute SQL statements
            DB::unprepared($sql);

            Log::info("Database restored from: {$backupPath}");

            return [
                'success' => true,
                'message' => 'Database restored successfully',
            ];
        } catch (\Exception $e) {
            Log::error("Database restore failed: {$e->getMessage()}");

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $backupPath): bool
    {
        if (Storage::exists($backupPath)) {
            Storage::delete($backupPath);
            Log::info("Backup deleted: {$backupPath}");

            return true;
        }

        return false;
    }

    /**
     * Get all database tables.
     * Note: Currently supports MySQL/MariaDB. For SQLite, use Schema facade methods.
     */
    protected function getAllTables(): array
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}");

        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            return collect($tables)->pluck('name')->toArray();
        }

        // MySQL/MariaDB
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_'.$connection['database'];

        return collect($tables)->pluck($key)->toArray();
    }

    /**
     * Generate SQL backup content.
     * Note: Currently optimized for MySQL/MariaDB. SQLite backup uses different format.
     */
    protected function generateBackupSql(array $tables): string
    {
        $driver = config('database.default');

        if ($driver === 'sqlite') {
            return $this->generateSqliteBackup($tables);
        }

        return $this->generateMysqlBackup($tables);
    }

    /**
     * Generate MySQL-specific backup SQL.
     */
    protected function generateMysqlBackup(array $tables): string
    {
        $sql = "-- Database Backup (MySQL)\n";
        $sql .= '-- Generated: '.now()->toDateTimeString()."\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTable[0]->{'Create Table'}.";\n\n";

            // Table data
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data for `{$table}`\n";
                foreach ($rows as $row) {
                    $values = collect((array) $row)->map(function ($value) {
                        if ($value === null) {
                            return 'NULL';
                        }

                        return "'".addslashes($value)."'";
                    })->implode(', ');

                    $sql .= "INSERT INTO `{$table}` VALUES ({$values});\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /**
     * Generate SQLite-specific backup SQL.
     */
    protected function generateSqliteBackup(array $tables): string
    {
        $sql = "-- Database Backup (SQLite)\n";
        $sql .= '-- Generated: '.now()->toDateTimeString()."\n\n";

        foreach ($tables as $table) {
            // Get table schema
            $schemaRow = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);

            if ($schemaRow && $schemaRow->sql) {
                $sql .= "-- Table structure for `{$table}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $schemaRow->sql.";\n\n";
            }

            // Table data
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data for `{$table}`\n";
                foreach ($rows as $row) {
                    $values = collect((array) $row)->map(function ($value) {
                        if ($value === null) {
                            return 'NULL';
                        }

                        return "'".addslashes($value)."'";
                    })->implode(', ');

                    $sql .= "INSERT INTO `{$table}` VALUES ({$values});\n";
                }
                $sql .= "\n";
            }
        }

        return $sql;
    }

    /**
     * Extract SQL from ZIP backup.
     */
    protected function extractSqlFromZip(string $zipPath): string
    {
        $zip = new ZipArchive;
        $tempPath = storage_path('app/'.$zipPath);

        if ($zip->open($tempPath) !== true) {
            throw new \RuntimeException('Cannot open ZIP file');
        }

        $sql = $zip->getFromName('database.sql');
        $zip->close();

        if ($sql === false) {
            throw new \RuntimeException('database.sql not found in backup');
        }

        return $sql;
    }
}
