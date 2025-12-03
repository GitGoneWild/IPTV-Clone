<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaDownloadService
{
    /**
     * The storage disk for media files.
     */
    protected string $disk = 'public';

    /**
     * Download media from URL and store locally.
     *
     * @param  Movie|Episode  $media
     */
    public function downloadMedia(Model $media): bool
    {
        if (! $media->stream_url) {
            $this->updateDownloadStatus($media, 'failed', 'No stream URL provided');

            return false;
        }

        // Check for duplicate download
        if ($this->isDuplicate($media)) {
            return true;
        }

        $this->updateDownloadStatus($media, 'downloading');

        try {
            $directory = $media instanceof Movie ? 'movies' : 'episodes';
            $filename = $this->generateFilename($media);
            $path = "{$directory}/{$filename}";

            // Stream download with progress tracking
            $response = Http::withOptions([
                'sink' => Storage::disk($this->disk)->path($path),
                'progress' => function ($downloadTotal, $downloadedBytes) use ($media) {
                    if ($downloadTotal > 0) {
                        $progress = (int) (($downloadedBytes / $downloadTotal) * 100);
                        $this->updateProgress($media, $progress);
                    }
                },
            ])->timeout(3600)->get($media->stream_url);

            if ($response->successful()) {
                $media->update([
                    'local_path' => $path,
                    'download_status' => 'completed',
                    'download_progress' => 100,
                    'download_error' => null,
                    'downloaded_at' => now(),
                ]);

                Log::info("Media downloaded successfully: {$media->title}", ['path' => $path]);

                return true;
            }

            throw new \Exception("HTTP error: {$response->status()}");
        } catch (\Exception $e) {
            $this->updateDownloadStatus($media, 'failed', $e->getMessage());
            Log::error("Media download failed: {$media->title}", ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Queue a media download job.
     *
     * @param  Movie|Episode  $media
     */
    public function queueDownload(Model $media): void
    {
        $media->update([
            'download_status' => 'pending',
            'download_progress' => 0,
            'download_error' => null,
        ]);

        dispatch(new \App\Jobs\MediaDownloadJob($media));
    }

    /**
     * Check if a file with the same content already exists.
     *
     * @param  Movie|Episode  $media
     */
    protected function isDuplicate(Model $media): bool
    {
        // Check by URL hash to avoid re-downloading same content
        $urlHash = md5($media->stream_url);
        $directory = $media instanceof Movie ? 'movies' : 'episodes';

        $existingMedia = $media instanceof Movie
            ? Movie::where('stream_url', $media->stream_url)
                ->where('id', '!=', $media->id)
                ->whereNotNull('local_path')
                ->where('download_status', 'completed')
                ->first()
            : Episode::where('stream_url', $media->stream_url)
                ->where('id', '!=', $media->id)
                ->whereNotNull('local_path')
                ->where('download_status', 'completed')
                ->first();

        if ($existingMedia && Storage::disk($this->disk)->exists($existingMedia->local_path)) {
            // Re-use existing local file
            $media->update([
                'local_path' => $existingMedia->local_path,
                'download_status' => 'completed',
                'download_progress' => 100,
                'downloaded_at' => now(),
            ]);

            Log::info("Re-using existing download for: {$media->title}");

            return true;
        }

        return false;
    }

    /**
     * Generate a unique filename for the media.
     *
     * @param  Movie|Episode  $media
     */
    protected function generateFilename(Model $media): string
    {
        $extension = $this->getExtensionFromUrl($media->stream_url);
        $slug = Str::slug($media->title);

        if ($media instanceof Episode) {
            $slug = Str::slug($media->series->title ?? 'series')
                .'-s'.str_pad($media->season_number, 2, '0', STR_PAD_LEFT)
                .'e'.str_pad($media->episode_number, 2, '0', STR_PAD_LEFT)
                .'-'.$slug;
        }

        return $slug.'-'.Str::random(8).'.'.$extension;
    }

    /**
     * Get file extension from URL.
     */
    protected function getExtensionFromUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // Default to mp4 if no extension found
        return $extension ?: 'mp4';
    }

    /**
     * Update download status.
     *
     * @param  Movie|Episode  $media
     */
    protected function updateDownloadStatus(Model $media, string $status, ?string $error = null): void
    {
        $media->update([
            'download_status' => $status,
            'download_error' => $error,
        ]);
    }

    /**
     * Update download progress.
     *
     * @param  Movie|Episode  $media
     */
    protected function updateProgress(Model $media, int $progress): void
    {
        // Only update every 5% to reduce database writes
        if ($progress % 5 === 0 && $media->download_progress !== $progress) {
            $media->update(['download_progress' => $progress]);
        }
    }

    /**
     * Delete local file for media.
     *
     * @param  Movie|Episode  $media
     */
    public function deleteLocalFile(Model $media): bool
    {
        if (! $media->local_path) {
            return true;
        }

        try {
            if (Storage::disk($this->disk)->exists($media->local_path)) {
                Storage::disk($this->disk)->delete($media->local_path);
            }

            $media->update([
                'local_path' => null,
                'download_status' => null,
                'download_progress' => 0,
                'downloaded_at' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete local file: {$media->local_path}", ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get download statistics.
     */
    public function getStats(): array
    {
        $movieStats = [
            'total' => Movie::count(),
            'pending' => Movie::where('download_status', 'pending')->count(),
            'downloading' => Movie::where('download_status', 'downloading')->count(),
            'completed' => Movie::where('download_status', 'completed')->count(),
            'failed' => Movie::where('download_status', 'failed')->count(),
        ];

        $episodeStats = [
            'total' => Episode::count(),
            'pending' => Episode::where('download_status', 'pending')->count(),
            'downloading' => Episode::where('download_status', 'downloading')->count(),
            'completed' => Episode::where('download_status', 'completed')->count(),
            'failed' => Episode::where('download_status', 'failed')->count(),
        ];

        return [
            'movies' => $movieStats,
            'episodes' => $episodeStats,
        ];
    }
}
