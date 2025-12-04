<?php

namespace App\Filament\Resources\LoadBalancerResource\Pages;

use App\Filament\Resources\LoadBalancerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoadBalancer extends EditRecord
{
    protected static string $resource = LoadBalancerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_stats')
                ->label('View Statistics')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.load-balancers.stats', ['record' => $this->record])),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
