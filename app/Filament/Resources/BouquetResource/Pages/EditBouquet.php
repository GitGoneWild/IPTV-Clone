<?php

namespace App\Filament\Resources\BouquetResource\Pages;

use App\Filament\Resources\BouquetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBouquet extends EditRecord
{
    protected static string $resource = BouquetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
