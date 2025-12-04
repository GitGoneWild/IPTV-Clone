<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StreamResource\Pages;
use App\Models\Stream;
use App\Services\StreamVerificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\HtmlString;

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
                            ->helperText('Enter the direct stream URL (HLS, MPEG-TS, RTMP, or HTTP)')
                            ->live(onBlur: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('testStream')
                                    ->icon('heroicon-m-signal')
                                    ->color('info')
                                    ->tooltip('Test stream connectivity')
                                    ->action(function (Get $get, Forms\Components\TextInput $component) {
                                        $url = $get('stream_url');
                                        $streamType = $get('stream_type') ?? 'hls';

                                        if (empty($url)) {
                                            Notification::make()
                                                ->title('No URL provided')
                                                ->body('Please enter a stream URL to test.')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        $service = new StreamVerificationService();
                                        $result = $service->verifyUrl($url, $streamType);

                                        if ($result['is_online']) {
                                            Notification::make()
                                                ->title('Stream is online!')
                                                ->body('Response time: '.$result['response_time_ms'].'ms')
                                                ->success()
                                                ->duration(5000)
                                                ->send();
                                        } else {
                                            $description = StreamVerificationService::getErrorDescription($result['error_type']);
                                            Notification::make()
                                                ->title('Stream verification failed')
                                                ->body($result['error_message']."\n\n".$description)
                                                ->danger()
                                                ->duration(8000)
                                                ->send();
                                        }
                                    })
                            ),
                        Forms\Components\Select::make('stream_type')
                            ->options(config('homelabtv.stream_types'))
                            ->default('hls')
                            ->required()
                            ->native(false)
                            ->live(),
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

                // Stream Validation Section - shows validation results
                Forms\Components\Section::make('Stream Validation')
                    ->description('Test your stream URL before saving')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('validateStream')
                                ->label('Validate Stream Now')
                                ->icon('heroicon-o-play-circle')
                                ->color('success')
                                ->action(function (Get $get) {
                                    $url = $get('stream_url');
                                    $streamType = $get('stream_type') ?? 'hls';

                                    if (empty($url)) {
                                        Notification::make()
                                            ->title('Missing URL')
                                            ->body('Please enter a stream URL first.')
                                            ->warning()
                                            ->send();

                                        return;
                                    }

                                    $service = new StreamVerificationService();
                                    $result = $service->verifyUrl($url, $streamType);

                                    if ($result['is_online']) {
                                        $details = [
                                            '✅ Status: Online',
                                            '⏱️ Response time: '.$result['response_time_ms'].'ms',
                                        ];

                                        if ($result['content_type']) {
                                            $details[] = '📄 Content-Type: '.$result['content_type'];
                                        }

                                        if ($result['http_status']) {
                                            $details[] = '🌐 HTTP Status: '.$result['http_status'];
                                        }

                                        Notification::make()
                                            ->title('Stream Validation Passed!')
                                            ->body(implode("\n", $details))
                                            ->success()
                                            ->duration(8000)
                                            ->send();
                                    } else {
                                        $description = StreamVerificationService::getErrorDescription($result['error_type']);

                                        Notification::make()
                                            ->title('Stream Validation Failed')
                                            ->body("Error: {$result['error_message']}\n\n💡 {$description}")
                                            ->danger()
                                            ->duration(10000)
                                            ->send();
                                    }
                                }),
                        ])->columnSpanFull(),
                        Forms\Components\Placeholder::make('validation_help')
                            ->content(new HtmlString(
                                '<div class="text-sm text-gray-500 dark:text-gray-400">'.
                                '<p>Click "Validate Stream Now" to test the stream URL before saving.</p>'.
                                '<p class="mt-1">You can also use the 🔊 button next to the URL field for quick testing.</p>'.
                                '</div>'
                            ))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

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
                        $service = new StreamVerificationService();
                        $result = $service->verify($record);

                        $record->update([
                            'last_check_at' => now(),
                            'last_check_status' => $result['status'],
                        ]);

                        if ($result['is_online']) {
                            Notification::make()
                                ->title('Stream is online')
                                ->body("Response time: {$result['response_time_ms']}ms")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Stream is offline')
                                ->body($result['error_message'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
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
                            $service = new StreamVerificationService();
                            $online = 0;
                            $offline = 0;

                            foreach ($records as $record) {
                                $result = $service->verify($record);
                                $record->update([
                                    'last_check_at' => now(),
                                    'last_check_status' => $result['status'],
                                ]);

                                if ($result['is_online']) {
                                    $online++;
                                } else {
                                    $offline++;
                                }
                            }

                            Notification::make()
                                ->title('Health check completed')
                                ->body("Online: {$online}, Offline: {$offline}")
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
