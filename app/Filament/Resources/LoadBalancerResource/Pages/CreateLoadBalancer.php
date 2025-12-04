<?php

namespace App\Filament\Resources\LoadBalancerResource\Pages;

use App\Filament\Resources\LoadBalancerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateLoadBalancer extends CreateRecord
{
    protected static string $resource = LoadBalancerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate API key when creating manually
        $data['api_key'] = bcrypt(Str::random(64));
        $data['status'] = $data['status'] ?? 'offline';
        $data['current_connections'] = 0;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
