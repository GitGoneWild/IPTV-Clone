<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'streampilot:health-check';

    /**
     * The console command description.
     */
    protected $description = 'Perform system health checks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Running HomelabTV health checks...');
        $this->newLine();

        $checks = [
            'Database Connection' => $this->checkDatabase(),
            'Redis Connection' => $this->checkRedis(),
            'Storage Permissions' => $this->checkStorage(),
            'EPG Directory' => $this->checkEpgDirectory(),
            'Configuration' => $this->checkConfiguration(),
        ];

        foreach ($checks as $name => $result) {
            if ($result) {
                $this->line("<fg=green>✓</> {$name}: OK");
            } else {
                $this->line("<fg=red>✗</> {$name}: FAILED");
            }
        }

        $this->newLine();
        $failed = count(array_filter($checks, fn ($v) => ! $v));

        if ($failed === 0) {
            $this->info('All health checks passed!');

            return self::SUCCESS;
        }

        $this->error("{$failed} health check(s) failed!");

        return self::FAILURE;
    }

    /**
     * Check database connectivity.
     */
    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            $this->error("Database error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Check Redis connectivity.
     */
    protected function checkRedis(): bool
    {
        try {
            Redis::connection()->ping();

            return true;
        } catch (\Exception $e) {
            $this->warn("Redis error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Check storage directory permissions.
     */
    protected function checkStorage(): bool
    {
        $directories = [
            storage_path('app'),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($directories as $dir) {
            if (! is_writable($dir)) {
                $this->error("Directory not writable: {$dir}");

                return false;
            }
        }

        return true;
    }

    /**
     * Check EPG directory.
     */
    protected function checkEpgDirectory(): bool
    {
        $epgDir = config('streampilot.epg_storage_path');

        try {
            if (! file_exists($epgDir)) {
                if (! mkdir($epgDir, 0755, true)) {
                    $error = error_get_last();
                    $message = $error ? $error['message'] : 'Unknown error';
                    $this->error("Failed to create EPG directory '{$epgDir}': {$message}");

                    return false;
                }
            }

            if (! is_writable($epgDir)) {
                $this->error("EPG directory is not writable: {$epgDir}");

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Exception while checking/creating EPG directory: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Check critical configuration values.
     */
    protected function checkConfiguration(): bool
    {
        $required = [
            'app.key',
            'app.url',
            'database.default',
            'streampilot.port',
        ];

        foreach ($required as $key) {
            if (empty(config($key))) {
                $this->error("Missing configuration: {$key}");

                return false;
            }
        }

        return true;
    }
}
