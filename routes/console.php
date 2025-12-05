<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule EPG import
Schedule::command('streampilot:import-epg')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule stream health check
Schedule::command('streampilot:check-streams')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Clean up old connection logs
Schedule::command('streampilot:cleanup-logs')
    ->daily()
    ->withoutOverlapping();

// Flush activity logs every 6 hours
Schedule::command('streampilot:flush-activity-logs --days=7')
    ->everySixHours()
    ->withoutOverlapping();
