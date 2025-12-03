<?php

namespace App\Observers;

use App\Models\Bouquet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BouquetObserver
{
    /**
     * Handle the Bouquet "updated" event.
     * Clear cache when bouquet associations change.
     */
    public function updated(Bouquet $bouquet): void
    {
        $this->clearUserCaches($bouquet);
    }

    /**
     * Handle the Bouquet "deleted" event.
     */
    public function deleted(Bouquet $bouquet): void
    {
        $this->clearUserCaches($bouquet);
    }

    /**
     * Clear cache for all users assigned to this bouquet.
     */
    protected function clearUserCaches(Bouquet $bouquet): void
    {
        $userIds = DB::table('user_bouquets')
            ->where('bouquet_id', $bouquet->id)
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            Cache::forget("user_streams_{$userId}");
            Cache::forget("user_categories_{$userId}");
        }
    }
}
