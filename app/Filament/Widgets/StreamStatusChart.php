<?php

namespace App\Filament\Widgets;

use App\Models\Stream;
use Filament\Widgets\ChartWidget;

class StreamStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Stream Status';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $online = Stream::where('last_check_status', 'online')->count();
        $offline = Stream::where('last_check_status', 'offline')->count();
        $unknown = Stream::whereNull('last_check_status')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Stream Status',
                    'data' => [$online, $offline, $unknown],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',  // green for online
                        'rgb(239, 68, 68)',  // red for offline
                        'rgb(156, 163, 175)', // gray for unknown
                    ],
                ],
            ],
            'labels' => ['Online', 'Offline', 'Not Checked'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
