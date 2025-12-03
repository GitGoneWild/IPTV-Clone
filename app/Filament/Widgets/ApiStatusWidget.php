<?php

namespace App\Filament\Widgets;

use App\Models\ApiUsageLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApiStatusWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $last24Hours = now()->subHours(24);

        $totalRequests24h = ApiUsageLog::where('created_at', '>=', $last24Hours)->count();
        $successRequests = ApiUsageLog::where('created_at', '>=', $last24Hours)
            ->whereBetween('response_status', [200, 299])->count();
        $errorRequests = ApiUsageLog::where('created_at', '>=', $last24Hours)
            ->where('response_status', '>=', 400)->count();
        $avgResponseTime = (int) ApiUsageLog::where('created_at', '>=', $last24Hours)
            ->avg('response_time_ms');

        $uniqueUsers = ApiUsageLog::where('created_at', '>=', $last24Hours)
            ->distinct('user_id')
            ->count('user_id');

        $successRate = $totalRequests24h > 0
            ? round(($successRequests / $totalRequests24h) * 100, 1)
            : 100;

        return [
            Stat::make('API Requests (24h)', number_format($totalRequests24h))
                ->description("{$uniqueUsers} unique users")
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),

            Stat::make('Success Rate', "{$successRate}%")
                ->description("{$successRequests} successful, {$errorRequests} errors")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 95 ? 'success' : ($successRate >= 80 ? 'warning' : 'danger')),

            Stat::make('Avg Response Time', "{$avgResponseTime}ms")
                ->description('Average response time')
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgResponseTime <= 200 ? 'success' : ($avgResponseTime <= 500 ? 'warning' : 'danger')),
        ];
    }
}
