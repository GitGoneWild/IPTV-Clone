<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Movie;
use App\Services\MediaDownloadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MediaDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param  Movie|Episode  $media
     */
    public function __construct(
        public Model $media
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MediaDownloadService $downloadService): void
    {
        $downloadService->downloadMedia($this->media);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->media->update([
            'download_status' => 'failed',
            'download_error' => $exception->getMessage(),
        ]);
    }
}
