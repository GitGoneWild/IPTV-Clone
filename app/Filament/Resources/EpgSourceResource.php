<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EpgSourceResource\Pages;
use App\Models\EpgProgram;
use App\Models\EpgSource;
use App\Models\Stream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

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
                Forms\Components\Section::make('EPG Source Information')
                    ->description('Configure your XMLTV EPG source')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., US TV Guide'),
                        Forms\Components\TextInput::make('url')
                            ->url()
                            ->placeholder('https://example.com/epg.xml')
                            ->helperText('URL to XMLTV file (.xml or .gz). Supports gzipped files.'),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload EPG File')
                            ->disk('epg')
                            ->acceptedFileTypes(['application/xml', 'text/xml', 'application/gzip'])
                            ->helperText('Or upload XMLTV file directly'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Enable automatic imports')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Import Statistics')
                    ->description('Information from the last import')
                    ->schema([
                        Forms\Components\Placeholder::make('last_import_status')
                            ->label('Status')
                            ->content(fn (?EpgSource $record) => $record?->last_import_status ?? 'Never imported'),
                        Forms\Components\Placeholder::make('last_import_at')
                            ->label('Last Import')
                            ->content(fn (?EpgSource $record) => $record?->last_import_at?->diffForHumans() ?? 'Never'),
                        Forms\Components\Placeholder::make('channels_count')
                            ->label('Channels')
                            ->content(fn (?EpgSource $record) => number_format($record?->channels_count ?? 0)),
                        Forms\Components\Placeholder::make('programs_count')
                            ->label('Programs')
                            ->content(fn (?EpgSource $record) => number_format($record?->programs_count ?? 0)),
                    ])->columns(4)
                    ->visibleOn('edit'),

                Forms\Components\Section::make('Channel Linking')
                    ->description('Link EPG channels to your streams')
                    ->schema([
                        Forms\Components\Placeholder::make('channel_linking_info')
                            ->content('After importing EPG data, you can link channels to streams by setting the EPG Channel ID in the stream settings.'),
                        Forms\Components\Repeater::make('available_channels')
                            ->label('Available Channels from EPG')
                            ->schema([
                                Forms\Components\TextInput::make('channel_id')
                                    ->label('Channel ID')
                                    ->disabled(),
                                Forms\Components\TextInput::make('display_name')
                                    ->label('Display Name')
                                    ->disabled(),
                            ])
                            ->columns(2)
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->visible(fn (?EpgSource $record) => $record && $record->programs_count > 0),
                    ])
                    ->visibleOn('edit')
                    ->collapsed(),
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
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('channels_count')
                    ->label('Channels')
                    ->numeric(),
                Tables\Columns\TextColumn::make('programs_count')
                    ->label('Programs')
                    ->numeric(),
                Tables\Columns\TextColumn::make('last_import_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => str_starts_with($state ?? '', 'error') ? 'danger' : 'gray',
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('last_import_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->label('Last Import'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('import')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (EpgSource $record) {
                        Artisan::call('homelabtv:import-epg', ['--source' => $record->id]);
                        $record->refresh();

                        Notification::make()
                            ->title('EPG Import Complete')
                            ->body("Imported {$record->programs_count} programs for {$record->channels_count} channels")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('link_channels')
                    ->label('Link Channels')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->modalHeading('Link EPG Channels to Streams')
                    ->modalDescription('Select streams to link with EPG channel IDs')
                    ->form(function (EpgSource $record) {
                        $channelIds = EpgProgram::distinct()->pluck('channel_id')->toArray();

                        return [
                            Forms\Components\Repeater::make('links')
                                ->schema([
                                    Forms\Components\Select::make('stream_id')
                                        ->label('Stream')
                                        ->options(Stream::where('is_active', true)->pluck('name', 'id'))
                                        ->searchable()
                                        ->required(),
                                    Forms\Components\Select::make('epg_channel_id')
                                        ->label('EPG Channel ID')
                                        ->options(array_combine($channelIds, $channelIds))
                                        ->searchable()
                                        ->required(),
                                ])
                                ->columns(2)
                                ->addActionLabel('Add Link')
                                ->defaultItems(1),
                        ];
                    })
                    ->action(function (array $data) {
                        $linked = 0;
                        foreach ($data['links'] ?? [] as $link) {
                            if (! empty($link['stream_id']) && ! empty($link['epg_channel_id'])) {
                                Stream::where('id', $link['stream_id'])
                                    ->update(['epg_channel_id' => $link['epg_channel_id']]);
                                $linked++;
                            }
                        }

                        Notification::make()
                            ->title('Channels Linked')
                            ->body("Linked {$linked} stream(s) to EPG channels")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
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
