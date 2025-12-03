<?php

namespace App\Filament\Resources\EpgSourceResource\Pages;

use App\Filament\Resources\EpgSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEpgSource extends EditRecord
{
    protected static string $resource = EpgSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
