<?php

namespace App\Console\Commands;

use App\Jobs\ImportEpgJob;
use App\Models\EpgSource;
use Illuminate\Console\Command;

class ImportEpgCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'homelabtv:import-epg {--source= : Specific EPG source ID to import} {--all : Import from all active sources}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import EPG data from configured XML sources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('source')) {
            $source = EpgSource::find($this->option('source'));

            if (! $source) {
                $this->error('EPG source not found.');

                return self::FAILURE;
            }

            $this->info("Importing EPG from: {$source->name}");
            ImportEpgJob::dispatchSync($source);
            $source->refresh();
            $this->info("Import complete: {$source->programs_count} programs imported.");

            return self::SUCCESS;
        }

        if ($this->option('all')) {
            $sources = EpgSource::active()->get();

            if ($sources->isEmpty()) {
                $this->warn('No active EPG sources found.');

                return self::SUCCESS;
            }

            foreach ($sources as $source) {
                $this->info("Importing EPG from: {$source->name}");
                ImportEpgJob::dispatch($source);
            }

            $this->info("Queued {$sources->count()} EPG import jobs.");

            return self::SUCCESS;
        }

        $this->error('Please specify --source=ID or --all flag.');

        return self::FAILURE;
    }
}
