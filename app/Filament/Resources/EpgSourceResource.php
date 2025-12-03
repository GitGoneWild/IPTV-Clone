<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EpgSourceResource\Pages;
use App\Models\EpgSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EpgSourceResource extends Resource
{
    protected static ?string $model = EpgSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Streaming';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'EPG Sources';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->url()
                    ->placeholder('https://example.com/epg.xml')
                    ->helperText('URL to XMLTV file (.xml or .gz)'),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload EPG File')
                    ->disk('epg')
                    ->acceptedFileTypes(['application/xml', 'text/xml', 'application/gzip'])
                    ->helperText('Upload XMLTV file directly'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('channels_count')
                    ->label('Channels'),
                Tables\Columns\TextColumn::make('programs_count')
                    ->label('Programs'),
                Tables\Columns\TextColumn::make('last_import_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('last_import_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('import')
                    ->label('Import Now')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (EpgSource $record) {
                        \Artisan::call('homelabtv:import-epg', ['--source' => $record->id]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEpgSources::route('/'),
            'create' => Pages\CreateEpgSource::route('/create'),
            'edit' => Pages\EditEpgSource::route('/{record}/edit'),
        ];
    }
}
