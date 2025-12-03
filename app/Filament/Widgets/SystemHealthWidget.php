<?php

namespace App\Filament\Widgets;

use App\Models\EpgSource;
use App\Models\Server;
use App\Models\Stream;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Stream health
        $totalStreams = Stream::where('is_active', true)->count();
        $onlineStreams = Stream::where('is_active', true)
            ->where('last_check_status', 'online')->count();
        $offlineStreams = Stream::where('is_active', true)
            ->where('last_check_status', 'offline')->count();
        $uncheckedStreams = Stream::where('is_active', true)
            ->whereNull('last_check_status')->count();

        $streamHealthPercent = $totalStreams > 0
            ? round(($onlineStreams / $totalStreams) * 100, 1)
            : 100;

        // Server health
        $totalServers = Server::count();
        $activeServers = Server::where('is_active', true)->count();

        // EPG health
        $totalEpgSources = EpgSource::where('is_active', true)->count();
        $successEpg = EpgSource::where('is_active', true)
            ->where('last_import_status', 'success')->count();

        $epgHealthPercent = $totalEpgSources > 0
            ? round(($successEpg / $totalEpgSources) * 100, 1)
            : 100;

        return [
            Stat::make('Stream Health', "{$streamHealthPercent}%")
                ->description("{$onlineStreams} online, {$offlineStreams} offline, {$uncheckedStreams} unchecked")
                ->descriptionIcon('heroicon-m-signal')
                ->color($streamHealthPercent >= 90 ? 'success' : ($streamHealthPercent >= 70 ? 'warning' : 'danger')),

            Stat::make('Active Servers', "{$activeServers}/{$totalServers}")
                ->description('Load balancing servers')
                ->descriptionIcon('heroicon-m-server-stack')
                ->color($activeServers === $totalServers ? 'success' : 'warning'),

            Stat::make('EPG Sources', "{$epgHealthPercent}%")
                ->description("{$successEpg}/{$totalEpgSources} sources healthy")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($epgHealthPercent === 100 ? 'success' : ($epgHealthPercent >= 50 ? 'warning' : 'danger')),
        ];
    }
}
