<?php

namespace App\Console\Commands;

use App\Models\Stream;
use Illuminate\Console\Command;

class RestartChannels extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'homelabtv:restart-channels {--stream= : Specific stream ID to restart} {--all : Restart all active streams}';

    /**
     * The console command description.
     */
    protected $description = 'Restart channels on demand (reset status and clear cache)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $streamId = $this->option('stream');
        $all = $this->option('all');

        if (! $streamId && ! $all) {
            $this->error('Please specify --stream=<id> or --all');

            return self::FAILURE;
        }

        $query = Stream::query();

        if ($streamId) {
            $query->where('id', $streamId);
        } elseif ($all) {
            $query->where('is_active', true);
        }

        $streams = $query->get();

        if ($streams->isEmpty()) {
            $this->info('No streams found to restart.');

            return self::SUCCESS;
        }

        $this->info("Restarting {$streams->count()} channel(s)...");

        $restarted = 0;
        foreach ($streams as $stream) {
            $stream->update([
                'last_check_at' => null,
                'last_check_status' => null,
            ]);
            $this->line("<fg=green>✓</> Restarted: {$stream->name}");
            $restarted++;
        }

        $this->newLine();
        $this->info("Successfully restarted {$restarted} channel(s).");
        $this->info('Run `php artisan homelabtv:check-streams` to verify stream status.');

        return self::SUCCESS;
    }
}
