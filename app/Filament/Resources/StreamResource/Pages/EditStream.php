<?php

namespace App\Filament\Resources\StreamResource\Pages;

use App\Filament\Resources\StreamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStream extends EditRecord
{
    protected static string $resource = StreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
