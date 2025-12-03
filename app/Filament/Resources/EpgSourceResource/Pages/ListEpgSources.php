<?php

namespace App\Filament\Resources\EpgSourceResource\Pages;

use App\Filament\Resources\EpgSourceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListEpgSources extends ListRecords
{
    protected static string $resource = EpgSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
