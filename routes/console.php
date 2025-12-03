<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule EPG import
Schedule::command('homelabtv:import-epg')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule stream health check
Schedule::command('homelabtv:check-streams')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Clean up old connection logs
Schedule::command('homelabtv:cleanup-logs')
    ->daily()
    ->withoutOverlapping();

// Flush activity logs every 6 hours
Schedule::command('homelabtv:flush-activity-logs --days=7')
    ->everySixHours()
    ->withoutOverlapping();
