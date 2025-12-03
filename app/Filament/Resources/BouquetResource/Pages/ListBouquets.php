<?php

namespace App\Filament\Resources\BouquetResource\Pages;

use App\Filament\Resources\BouquetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListBouquets extends ListRecords
{
    protected static string $resource = BouquetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
