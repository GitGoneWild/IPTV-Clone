<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoadBalancerResource\Pages;
use App\Models\LoadBalancer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;

class LoadBalancerResource extends Resource
{
    protected static ?string $model = LoadBalancer::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Load Balancers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Descriptive name for this load balancer'),
                        Forms\Components\TextInput::make('hostname')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('lb1.yourdomain.com')
                            ->helperText('Public hostname or domain'),
                        Forms\Components\TextInput::make('ip_address')
                            ->required()
                            ->ip()
                            ->helperText('Public IP address'),
                        Forms\Components\TextInput::make('port')
                            ->required()
                            ->numeric()
                            ->default(80)
                            ->minValue(1)
                            ->maxValue(65535),
                        Forms\Components\Toggle::make('use_ssl')
                            ->label('Use HTTPS/SSL')
                            ->default(false)
                            ->helperText('Enable if load balancer uses HTTPS'),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('region')
                            ->maxLength(50)
                            ->placeholder('US-East, EU-West, etc.')
                            ->helperText('Geographic region for routing'),
                        Forms\Components\TextInput::make('weight')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Higher weight = more traffic (1-100)'),
                        Forms\Components\TextInput::make('max_connections')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->helperText('Maximum concurrent connections (leave empty for unlimited)'),
                        Forms\Components\TagsInput::make('capabilities')
                            ->placeholder('hls, rtmp, http')
                            ->helperText('Supported streaming protocols'),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Security')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Enable/disable this load balancer'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'online' => 'Online',
                                'offline' => 'Offline',
                                'maintenance' => 'Maintenance',
                            ])
                            ->default('offline')
                            ->required(),
                        Forms\Components\TextInput::make('api_key')
                            ->maxLength(255)
                            ->helperText('Generated on registration - store securely')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\Placeholder::make('current_connections')
                            ->content(fn (?LoadBalancer $record) => $record?->current_connections ?? 0),
                        Forms\Components\Placeholder::make('load_percentage')
                            ->content(fn (?LoadBalancer $record) => $record ? $record->load_percentage.'%' : '0%'),
                        Forms\Components\Placeholder::make('last_heartbeat_at')
                            ->content(fn (?LoadBalancer $record) => $record?->last_heartbeat_at?->diffForHumans() ?? 'Never'),
                        Forms\Components\Placeholder::make('is_healthy')
                            ->content(fn (?LoadBalancer $record) => $record?->isHealthy() ? '✓ Healthy' : '✗ Unhealthy'),
                    ])
                    ->columns(4)
                    ->visible(fn ($livewire) => $livewire instanceof Pages\EditLoadBalancer),

                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('hostname')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Hostname copied!')
                    ->icon('heroicon-m-globe-alt'),

                Tables\Columns\TextColumn::make('region')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->default('N/A'),

                Tables\Columns\IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'online' => 'heroicon-o-check-circle',
                        'offline' => 'heroicon-o-x-circle',
                        'maintenance' => 'heroicon-o-wrench',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        'maintenance' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_connections')
                    ->numeric()
                    ->sortable()
                    ->suffix(fn (LoadBalancer $record) => $record->max_connections ? " / {$record->max_connections}" : '')
                    ->description(fn (LoadBalancer $record) => "Load: {$record->load_percentage}%"),

                Tables\Columns\TextColumn::make('weight')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('healthy')
                    ->label('Health')
                    ->boolean()
                    ->getStateUsing(fn (LoadBalancer $record) => $record->isHealthy())
                    ->tooltip(fn (LoadBalancer $record) => $record->isHealthy() ? 'Healthy' : 'Unhealthy')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('last_heartbeat_at', $direction);
                    }),

                Tables\Columns\TextColumn::make('last_heartbeat_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'maintenance' => 'Maintenance',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All load balancers')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\SelectFilter::make('region')
                    ->options(fn () => LoadBalancer::distinct()->pluck('region', 'region')->filter()->toArray()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('test_connection')
                        ->label('Test Connection')
                        ->icon('heroicon-o-signal')
                        ->action(function (LoadBalancer $record) {
                            try {
                                $url = $record->buildBaseUrl().'/health';
                                $response = Http::timeout(5)->get($url);

                                if ($response->successful()) {
                                    \Filament\Notifications\Notification::make()
                                        ->success()
                                        ->title('Connection successful')
                                        ->body('Load balancer is responding')
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('Connection failed')
                                        ->body("HTTP {$response->status()}")
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Connection failed')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('set_maintenance')
                        ->label('Set Maintenance')
                        ->icon('heroicon-o-wrench')
                        ->color('warning')
                        ->action(fn (LoadBalancer $record) => $record->update(['status' => 'maintenance']))
                        ->requiresConfirmation()
                        ->visible(fn (LoadBalancer $record) => $record->status !== 'maintenance'),

                    Tables\Actions\Action::make('set_online')
                        ->label('Set Online')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (LoadBalancer $record) => $record->update(['status' => 'online']))
                        ->visible(fn (LoadBalancer $record) => $record->status !== 'online'),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name')
            ->poll('30s'); // Auto-refresh every 30 seconds
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
            'index' => Pages\ListLoadBalancers::route('/'),
            'create' => Pages\CreateLoadBalancer::route('/create'),
            'edit' => Pages\EditLoadBalancer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->where('status', 'online')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();

        return $count > 0 ? 'success' : 'danger';
    }
}
