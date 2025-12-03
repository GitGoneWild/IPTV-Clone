<?php

namespace App\Observers;

use App\Models\Stream;
use Illuminate\Support\Facades\Cache;

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
     */
    protected function clearUserCaches(Stream $stream): void
    {
        // Clear cache for users with bouquets containing this stream
        $userIds = $stream->bouquets()
            ->with('users')
            ->get()
            ->pluck('users')
            ->flatten()
            ->pluck('id')
            ->unique();

        foreach ($userIds as $userId) {
            Cache::forget("user_streams_{$userId}");
            Cache::forget("user_categories_{$userId}");
        }
    }
}
