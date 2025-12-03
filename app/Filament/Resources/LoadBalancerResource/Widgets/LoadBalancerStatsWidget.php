<?php

namespace App\Filament\Resources\LoadBalancerResource\Widgets;

use App\Models\LoadBalancer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoadBalancerStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = LoadBalancer::count();
        $online = LoadBalancer::where('status', 'online')->where('is_active', true)->count();
        $offline = LoadBalancer::where('status', 'offline')->count();
        $maintenance = LoadBalancer::where('status', 'maintenance')->count();
        
        $totalConnections = LoadBalancer::where('is_active', true)->sum('current_connections');
        $maxConnections = LoadBalancer::where('is_active', true)->whereNotNull('max_connections')->sum('max_connections');
        
        $avgLoad = $maxConnections > 0 ? round(($totalConnections / $maxConnections) * 100, 1) : 0;
        
        $healthy = LoadBalancer::where('is_active', true)
            ->where('status', 'online')
            ->where('last_heartbeat_at', '>=', now()->subMinutes(5))
            ->count();

        return [
            Stat::make('Online Load Balancers', $online)
                ->description("{$total} total")
                ->descriptionIcon('heroicon-m-server')
                ->color('success')
                ->chart([7, 5, 10, 5, 12, 10, $online]),
            
            Stat::make('Active Connections', $totalConnections)
                ->description($maxConnections > 0 ? "of {$maxConnections} max" : 'Unlimited')
                ->descriptionIcon('heroicon-m-users')
                ->color($avgLoad > 80 ? 'danger' : ($avgLoad > 50 ? 'warning' : 'success'))
                ->chart([10, 20, 15, 22, 18, 25, $totalConnections]),
            
            Stat::make('Average Load', "{$avgLoad}%")
                ->description('Across all LBs')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($avgLoad > 80 ? 'danger' : ($avgLoad > 50 ? 'warning' : 'success')),
            
            Stat::make('Healthy Load Balancers', $healthy)
                ->description('Received heartbeat < 5min')
                ->descriptionIcon('heroicon-m-heart')
                ->color($healthy > 0 ? 'success' : 'danger'),
        ];
    }
}
