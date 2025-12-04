<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StreamResource\Pages;
use App\Models\Stream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;

class StreamResource extends Resource
{
    protected static ?string $model = Stream::class;

    protected static ?string $navigationIcon = 'heroicon-o-play';

    protected static ?string $navigationGroup = 'Streaming';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stream Information')
                    ->description('Basic information about the stream')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., BBC News HD'),
                        Forms\Components\TextInput::make('stream_url')
                            ->required()
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull()
                            ->placeholder('https://example.com/stream.m3u8')
                            ->helperText('Enter the direct stream URL (HLS, MPEG-TS, RTMP, or HTTP)'),
                        Forms\Components\Select::make('stream_type')
                            ->options(config('homelabtv.stream_types'))
                            ->default('hls')
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Select::make('server_id')
                            ->relationship('server', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Optional: Select a server for load balancing'),
                    ])->columns(2),

                Forms\Components\Section::make('EPG & Display')
                    ->description('Electronic Program Guide and visual settings')
                    ->schema([
                        Forms\Components\TextInput::make('epg_channel_id')
                            ->label('EPG Channel ID')
                            ->placeholder('channel.id.from.epg')
                            ->helperText('Match this with EPG source channel ID for program guide'),
                        Forms\Components\TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->placeholder('https://example.com/logo.png'),
                        Forms\Components\TextInput::make('stream_icon')
                            ->label('Stream Icon URL')
                            ->url()
                            ->placeholder('https://example.com/icon.png'),
                        Forms\Components\TextInput::make('custom_sid')
                            ->label('Custom SID')
                            ->placeholder('Optional custom stream ID'),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Settings')
                    ->description('Control stream visibility and ordering')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Enable or disable this stream')
                            ->default(true),
                        Forms\Components\Toggle::make('is_hidden')
                            ->label('Hidden')
                            ->helperText('Hide from user playlists')
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first'),
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Internal notes about this stream...')
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Stream Health')
                    ->description('Last health check information')
                    ->schema([
                        Forms\Components\Placeholder::make('last_check_status')
                            ->label('Status')
                            ->content(fn (?Stream $record) => $record?->last_check_status ?? 'Not checked'),
                        Forms\Components\Placeholder::make('last_check_at')
                            ->label('Last Checked')
                            ->content(fn (?Stream $record) => $record?->last_check_at?->diffForHumans() ?? 'Never'),
                        Forms\Components\Placeholder::make('bitrate')
                            ->label('Bitrate')
                            ->content(fn (?Stream $record) => $record?->bitrate ?? 'Unknown'),
                        Forms\Components\Placeholder::make('resolution')
                            ->label('Resolution')
                            ->content(fn (?Stream $record) => $record?->resolution ?? 'Unknown'),
                    ])->columns(4)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Stream $record) => $record->epg_channel_id),
                Tables\Columns\TextColumn::make('stream_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hls' => 'success',
                        'rtmp' => 'warning',
                        'mpegts' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->placeholder('Uncategorized'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('last_check_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        default => 'gray',
                    })
                    ->label('Health'),
                Tables\Columns\TextColumn::make('last_check_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->label('Last Check'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('stream_type')
                    ->options(config('homelabtv.stream_types'))
                    ->label('Type'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('last_check_status')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ])
                    ->label('Health Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('check')
                    ->label('Check')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (Stream $record) {
                        Artisan::call('homelabtv:check-streams', ['--stream' => $record->id]);
                        $record->refresh();
                        Notification::make()
                            ->title('Stream checked')
                            ->body("Status: {$record->last_check_status}")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('check_health')
                        ->label('Check Health')
                        ->icon('heroicon-o-signal')
                        ->color('info')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                Artisan::call('homelabtv:check-streams', ['--stream' => $record->id]);
                            }
                            Notification::make()
                                ->title('Health check completed')
                                ->body("Checked {$records->count()} streams")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('restart')
                        ->label('Restart')
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                Artisan::call('homelabtv:restart-channels', ['--stream' => $record->id]);
                            }
                            Notification::make()
                                ->title('Channels restarted')
                                ->body("Restarted {$records->count()} channels")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStreams::route('/'),
            'create' => Pages\CreateStream::route('/create'),
            'edit' => Pages\EditStream::route('/{record}/edit'),
        ];
    }
}
