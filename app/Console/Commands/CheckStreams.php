<?php

namespace App\Console\Commands;

use App\Models\Stream;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckStreams extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'homelabtv:check-streams {--stream= : Specific stream ID to check}';

    /**
     * The console command description.
     */
    protected $description = 'Check health status of all active streams';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $streamId = $this->option('stream');
        
        $query = Stream::active();
        if ($streamId) {
            $query->where('id', $streamId);
        }
        
        $streams = $query->get();
        
        if ($streams->isEmpty()) {
            $this->info('No active streams to check.');
            return self::SUCCESS;
        }

        $this->info("Checking {$streams->count()} streams...");
        
        $online = 0;
        $offline = 0;

        foreach ($streams as $stream) {
            $status = $this->checkStream($stream);
            
            $stream->update([
                'last_check_at' => now(),
                'last_check_status' => $status,
            ]);

            if ($status === 'online') {
                $online++;
                $this->line("<fg=green>✓</> {$stream->name}");
            } else {
                $offline++;
                $this->line("<fg=red>✗</> {$stream->name}");
            }
        }

        $this->newLine();
        $this->info("Results: {$online} online, {$offline} offline");

        return self::SUCCESS;
    }

    /**
     * Check if stream is accessible
     */
    protected function checkStream(Stream $stream): string
    {
        $url = $stream->getEffectiveUrl();
        $timeout = config('homelabtv.stream_check_timeout', 10);

        try {
            // For HLS streams, check if m3u8 is accessible
            if (in_array($stream->stream_type, ['hls', 'http'])) {
                $response = Http::timeout($timeout)->head($url);
                return $response->successful() ? 'online' : 'offline';
            }

            // For RTMP, just check if URL is parseable (actual check requires different approach)
            if ($stream->stream_type === 'rtmp') {
                $parsed = parse_url($url);
                return $parsed !== false ? 'online' : 'offline';
            }

            // For MPEG-TS, try a HEAD request
            if ($stream->stream_type === 'mpegts') {
                $response = Http::timeout($timeout)->head($url);
                return $response->successful() ? 'online' : 'offline';
            }

            return 'unknown';
            
        } catch (\Exception $e) {
            return 'offline';
        }
    }
}
