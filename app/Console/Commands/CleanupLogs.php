<?php

namespace App\Console\Commands;

use App\Models\ConnectionLog;
use Illuminate\Console\Command;

class CleanupLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'homelabtv:cleanup-logs {--days=30 : Number of days to keep logs}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old connection logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = ConnectionLog::where('created_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info('No old logs to clean up.');

            return self::SUCCESS;
        }

        $this->info("Deleting {$count} connection logs older than {$days} days...");

        ConnectionLog::where('created_at', '<', $cutoff)->delete();

        $this->info('Cleanup completed.');

        return self::SUCCESS;
    }
}
