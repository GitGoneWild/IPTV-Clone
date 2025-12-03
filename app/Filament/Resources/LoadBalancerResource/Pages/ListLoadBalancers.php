<?php

namespace App\Filament\Resources\LoadBalancerResource\Pages;

use App\Filament\Resources\LoadBalancerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListLoadBalancers extends ListRecords
{
    protected static string $resource = LoadBalancerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('register_instructions')
                ->label('Registration Instructions')
                ->icon('heroicon-o-information-circle')
                ->color('info')
                ->modalWidth(MaxWidth::ThreeExtraLarge)
                ->modalHeading('Load Balancer Registration')
                ->modalDescription('Follow these instructions to register a new load balancer')
                ->modalContent(view('filament.pages.load-balancer-registration'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
            
            Actions\CreateAction::make()
                ->label('Manual Registration')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LoadBalancerResource\Widgets\LoadBalancerStatsWidget::class,
        ];
    }
}
