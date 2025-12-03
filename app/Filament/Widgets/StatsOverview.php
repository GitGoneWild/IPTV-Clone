<?php

namespace App\Filament\Widgets;

use App\Models\Stream;
use App\Models\User;
use App\Models\Category;
use App\Models\Server;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalStreams = Stream::count();
        $onlineStreams = Stream::where('last_check_status', 'online')->count();
        $offlineStreams = Stream::where('last_check_status', 'offline')->count();
        
        $totalUsers = User::where('is_admin', false)->count();
        $activeUsers = User::where('is_admin', false)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();
        
        $activeServers = Server::where('is_active', true)->count();
        $totalCategories = Category::count();

        return [
            Stat::make('Total Streams', $totalStreams)
                ->description($onlineStreams . ' online, ' . $offlineStreams . ' offline')
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),
                
            Stat::make('Active Users', $activeUsers)
                ->description('of ' . $totalUsers . ' total users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            Stat::make('Categories', $totalCategories)
                ->descriptionIcon('heroicon-m-folder')
                ->color('warning'),
                
            Stat::make('Active Servers', $activeServers)
                ->descriptionIcon('heroicon-m-server')
                ->color('primary'),
        ];
    }
}
