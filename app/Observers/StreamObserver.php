<?php

namespace App\Observers;

use App\Models\Stream;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StreamObserver
{
    /**
     * Handle the Stream "created" event.
     */
    public function created(Stream $stream): void
    {
        $this->clearUserCaches($stream);
    }

    /**
     * Handle the Stream "updated" event.
     */
    public function updated(Stream $stream): void
    {
        $this->clearUserCaches($stream);
    }

    /**
     * Handle the Stream "deleted" event.
     */
    public function deleted(Stream $stream): void
    {
        $this->clearUserCaches($stream);
    }

    /**
     * Clear cache for all users that have access to this stream.
     * Optimized to use a direct database query instead of loading full objects.
     *
     * Note: If a stream is not yet associated with any bouquets, this will clear
     * zero caches, which is expected and safe behavior for new streams.
     */
    protected function clearUserCaches(Stream $stream): void
    {
        // Get user IDs directly from the database without loading full objects
        $userIds = DB::table('user_bouquets')
            ->join('bouquet_streams', 'user_bouquets.bouquet_id', '=', 'bouquet_streams.bouquet_id')
            ->where('bouquet_streams.stream_id', $stream->id)
            ->distinct()
            ->pluck('user_bouquets.user_id');

        foreach ($userIds as $userId) {
            Cache::forget("user_streams_{$userId}");
            Cache::forget("user_categories_{$userId}");
        }
    }
}
