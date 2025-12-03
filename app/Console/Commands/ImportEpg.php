<?php

namespace App\Console\Commands;

use App\Models\EpgProgram;
use App\Models\EpgSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportEpg extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'homelabtv:import-epg {--source= : Specific EPG source ID to import}';

    /**
     * The console command description.
     */
    protected $description = 'Import EPG data from XMLTV sources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceId = $this->option('source');

        $query = EpgSource::active();
        if ($sourceId) {
            $query->where('id', $sourceId);
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->info('No active EPG sources found.');

            return self::SUCCESS;
        }

        foreach ($sources as $source) {
            $this->info("Importing EPG from: {$source->name}");

            try {
                $xmlContent = $this->getXmlContent($source);

                if (! $xmlContent) {
                    $this->error("Could not retrieve content from {$source->name}");
                    $source->update([
                        'last_import_status' => 'failed',
                        'last_import_at' => now(),
                    ]);

                    continue;
                }

                $stats = $this->parseAndImport($xmlContent);

                $source->update([
                    'last_import_status' => 'success',
                    'last_import_at' => now(),
                    'programs_count' => $stats['programs'],
                    'channels_count' => $stats['channels'],
                ]);

                $this->info("Imported {$stats['programs']} programs for {$stats['channels']} channels");

            } catch (\Exception $e) {
                $this->error("Error importing from {$source->name}: {$e->getMessage()}");
                $source->update([
                    'last_import_status' => 'error: '.substr($e->getMessage(), 0, 100),
                    'last_import_at' => now(),
                ]);
            }
        }

        // Clean up old EPG data
        EpgProgram::where('end_time', '<', now()->subDay())->delete();

        return self::SUCCESS;
    }

    /**
     * Get XML content from source
     */
    protected function getXmlContent(EpgSource $source): ?string
    {
        if ($source->file_path && Storage::disk('epg')->exists($source->file_path)) {
            $content = Storage::disk('epg')->get($source->file_path);

            // Handle gzipped files
            if (str_ends_with($source->file_path, '.gz')) {
                $content = gzdecode($content);
            }

            return $content;
        }

        if ($source->url) {
            $content = @file_get_contents($source->url);

            if ($content && str_ends_with($source->url, '.gz')) {
                $content = gzdecode($content);
            }

            return $content ?: null;
        }

        return null;
    }

    /**
     * Parse XMLTV and import programs
     */
    protected function parseAndImport(string $xmlContent): array
    {
        $xml = simplexml_load_string($xmlContent);

        if (! $xml) {
            throw new \Exception('Invalid XML content');
        }

        $channels = [];
        $programsCount = 0;
        $batchSize = 100;
        $programsBatch = [];

        // Parse channels
        foreach ($xml->channel as $channel) {
            $channelId = (string) $channel['id'];
            $channels[$channelId] = (string) $channel->{'display-name'};
        }

        // Parse programs in batches
        foreach ($xml->programme as $programme) {
            $channelId = (string) $programme['channel'];

            if (! isset($channels[$channelId])) {
                continue;
            }

            $startTime = $this->parseXmltvTime((string) $programme['start']);
            $endTime = $this->parseXmltvTime((string) $programme['stop']);

            if (! $startTime || ! $endTime) {
                continue;
            }

            // Skip past programs
            if ($endTime < now()) {
                continue;
            }

            $programsBatch[] = [
                'channel_id' => $channelId,
                'start_time' => $startTime,
                'title' => (string) $programme->title,
                'description' => (string) ($programme->desc ?? ''),
                'end_time' => $endTime,
                'category' => (string) ($programme->category ?? ''),
                'episode_num' => (string) ($programme->{'episode-num'} ?? ''),
                'icon_url' => (string) ($programme->icon['src'] ?? ''),
                'lang' => (string) ($programme->title['lang'] ?? 'en'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert batch when it reaches the batch size
            if (count($programsBatch) >= $batchSize) {
                $this->insertProgramsBatch($programsBatch);
                $programsCount += count($programsBatch);
                $programsBatch = [];
            }
        }

        // Insert remaining programs
        if (! empty($programsBatch)) {
            $this->insertProgramsBatch($programsBatch);
            $programsCount += count($programsBatch);
        }

        return [
            'channels' => count($channels),
            'programs' => $programsCount,
        ];
    }

    /**
     * Insert programs batch using upsert for better performance.
     */
    protected function insertProgramsBatch(array $programs): void
    {
        EpgProgram::upsert(
            $programs,
            ['channel_id', 'start_time'],
            ['title', 'description', 'end_time', 'category', 'episode_num', 'icon_url', 'lang', 'updated_at']
        );
    }

    /**
     * Parse XMLTV datetime format.
     *
     * Supports formats like:
     * - 20240101120000
     * - 20240101120000 +0000
     * - 20240101120000 -0500
     */
    protected function parseXmltvTime(string $timeStr): ?\DateTime
    {
        $timeStr = trim($timeStr);

        if (empty($timeStr)) {
            return null;
        }

        try {
            // Match timestamp with optional timezone offset
            if (preg_match('/^(\d{14})\s*([+-]\d{4})?$/', $timeStr, $matches)) {
                $timestamp = $matches[1];
                $timezone = $matches[2] ?? null;

                if ($timezone) {
                    // Parse with timezone (format expects single space between timestamp and offset)
                    $result = \DateTime::createFromFormat('YmdHis O', $timestamp.' '.$timezone);
                } else {
                    // Parse without timezone
                    $result = \DateTime::createFromFormat('YmdHis', $timestamp);
                }

                return $result ?: null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
