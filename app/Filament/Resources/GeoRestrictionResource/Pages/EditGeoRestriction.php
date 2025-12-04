<?php

namespace App\Filament\Resources\GeoRestrictionResource\Pages;

use App\Filament\Resources\GeoRestrictionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGeoRestriction extends EditRecord
{
    protected static string $resource = GeoRestrictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
