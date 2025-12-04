<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static string|\UnitEnum|null $navigationGroup = 'Users & Access';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Device Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255)
                            ->placeholder('e.g., Living Room TV'),
                        Forms\Components\TextInput::make('mac_address')
                            ->label('MAC Address')
                            ->maxLength(17)
                            ->placeholder('00:00:00:00:00:00'),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->maxLength(45),
                        Forms\Components\Select::make('device_type')
                            ->options([
                                'android' => 'Android',
                                'ios' => 'iOS',
                                'smart_tv' => 'Smart TV',
                                'media_player' => 'Media Player',
                                'windows' => 'Windows',
                                'macos' => 'macOS',
                                'linux' => 'Linux',
                                'other' => 'Other',
                            ])
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_blocked')
                            ->label('Blocked')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('last_seen_at')
                            ->label('Last Seen')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('User Agent')
                    ->schema([
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent String')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->placeholder('Unnamed Device'),
                Tables\Columns\TextColumn::make('mac_address')
                    ->label('MAC Address')
                    ->searchable()
                    ->copyable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('device_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'android' => 'primary',
                        'ios' => 'gray',
                        'smart_tv' => 'info',
                        'media_player' => 'success',
                        'windows' => 'primary',
                        'macos' => 'gray',
                        'linux' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->boolean()
                    ->label('Blocked')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Last Seen')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('Blocked'),
                Tables\Filters\SelectFilter::make('device_type')
                    ->options([
                        'android' => 'Android',
                        'ios' => 'iOS',
                        'smart_tv' => 'Smart TV',
                        'media_player' => 'Media Player',
                        'windows' => 'Windows',
                        'macos' => 'macOS',
                        'linux' => 'Linux',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('block')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! $record->is_blocked)
                    ->action(fn ($record) => $record->block()),
                Tables\Actions\Action::make('unblock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->is_blocked)
                    ->action(fn ($record) => $record->unblock()),
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
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
