<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class FlushActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'streampilot:flush-activity-logs {--days=7 : Number of days to keep activity logs}';

    /**
     * The console command description.
     */
    protected $description = 'Flush old activity logs (scheduled every 6 hours)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = Activity::where('created_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info('No old activity logs to flush.');

            return self::SUCCESS;
        }

        $this->info("Deleting {$count} activity logs older than {$days} days...");

        Activity::where('created_at', '<', $cutoff)->delete();

        $this->info('Activity logs flushed successfully.');

        return self::SUCCESS;
    }
}
