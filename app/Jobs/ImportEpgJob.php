<?php

namespace App\Jobs;

use App\Models\EpgProgram;
use App\Models\EpgSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportEpgJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        public EpgSource $epgSource
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting EPG import for source: {$this->epgSource->name}");

            $xmlContent = $this->fetchXmlContent();

            if (! $xmlContent) {
                $this->updateSourceStatus('failed', 'Failed to fetch XML content');

                return;
            }

            $result = $this->parseAndImportXml($xmlContent);

            $this->updateSourceStatus(
                'success',
                "Imported {$result['programs']} programs for {$result['channels']} channels"
            );

            $this->epgSource->update([
                'programs_count' => $result['programs'],
                'channels_count' => $result['channels'],
            ]);

            Log::info("EPG import completed: {$result['programs']} programs, {$result['channels']} channels");
        } catch (\Exception $e) {
            Log::error("EPG import failed: {$e->getMessage()}");
            $this->updateSourceStatus('failed', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch XML content from URL or file.
     */
    protected function fetchXmlContent(): ?string
    {
        if ($this->epgSource->url) {
            $response = Http::timeout(120)->get($this->epgSource->url);

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        }

        if ($this->epgSource->file_path && Storage::exists($this->epgSource->file_path)) {
            return Storage::get($this->epgSource->file_path);
        }

        return null;
    }

    /**
     * Parse XMLTV and import programs.
     */
    protected function parseAndImportXml(string $xmlContent): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if (! $xml) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            $errorMessages = array_map(function ($error) {
                return sprintf('Line %d: %s', $error->line, trim($error->message));
            }, $errors);

            throw new \RuntimeException('Invalid XML content: '.implode('; ', $errorMessages));
        }

        $channelIds = [];
        $programsImported = 0;

        // Parse channels
        foreach ($xml->channel as $channel) {
            $channelId = (string) $channel['id'];
            $channelIds[$channelId] = [
                'id' => $channelId,
                'name' => (string) $channel->{'display-name'},
                'icon' => isset($channel->icon) ? (string) $channel->icon['src'] : null,
            ];
        }

        // Clear existing programs for this source's channels
        EpgProgram::whereIn('channel_id', array_keys($channelIds))->delete();

        // Parse programs in batches
        $programs = [];
        foreach ($xml->programme as $programme) {
            $channelId = (string) $programme['channel'];

            if (! isset($channelIds[$channelId])) {
                continue;
            }

            $programs[] = [
                'channel_id' => $channelId,
                'title' => (string) $programme->title,
                'description' => isset($programme->desc) ? (string) $programme->desc : null,
                'start_time' => $this->parseXmltvTime((string) $programme['start']),
                'end_time' => $this->parseXmltvTime((string) $programme['stop']),
                'category' => isset($programme->category) ? (string) $programme->category : null,
                'lang' => isset($programme->title['lang']) ? (string) $programme->title['lang'] : 'en',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches of 1000
            if (count($programs) >= 1000) {
                EpgProgram::insert($programs);
                $programsImported += count($programs);
                $programs = [];
            }
        }

        // Insert remaining programs
        if (count($programs) > 0) {
            EpgProgram::insert($programs);
            $programsImported += count($programs);
        }

        return [
            'channels' => count($channelIds),
            'programs' => $programsImported,
        ];
    }

    /**
     * Parse XMLTV datetime format.
     */
    protected function parseXmltvTime(string $time): \DateTime
    {
        // XMLTV format: 20231215120000 +0000
        $time = preg_replace('/\s+/', '', $time);

        if (strlen($time) >= 14) {
            $dateString = substr($time, 0, 14);
            $offset = strlen($time) > 14 ? substr($time, 14) : '+0000';

            // Create DateTime with the date string, then adjust timezone using offset
            $date = \DateTime::createFromFormat('YmdHis', $dateString, new \DateTimeZone('UTC'));

            if ($date && preg_match('/^([+-])(\d{2})(\d{2})$/', $offset, $matches)) {
                $hours = (int) $matches[2];
                $minutes = (int) $matches[3];
                $totalMinutes = ($hours * 60 + $minutes) * ($matches[1] === '-' ? -1 : 1);

                // Adjust the time to account for timezone offset
                $date->modify(($totalMinutes >= 0 ? '-' : '+').abs($totalMinutes).' minutes');
            }

            return $date;
        }

        return new \DateTime($time);
    }

    /**
     * Update EPG source status.
     */
    protected function updateSourceStatus(string $status, ?string $message = null): void
    {
        $this->epgSource->update([
            'last_import_at' => now(),
            'last_import_status' => $status,
        ]);
    }
}
