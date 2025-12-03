<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServerResource\Pages;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Server Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('base_url')
                            ->required()
                            ->url()
                            ->placeholder('http://server.local:8080'),
                        Forms\Components\TextInput::make('rtmp_url')
                            ->url()
                            ->placeholder('rtmp://server.local'),
                    ])->columns(1),

                Forms\Components\Section::make('Ports')
                    ->schema([
                        Forms\Components\TextInput::make('http_port')
                            ->numeric()
                            ->default(80),
                        Forms\Components\TextInput::make('https_port')
                            ->numeric()
                            ->default(443),
                        Forms\Components\TextInput::make('rtmp_port')
                            ->numeric()
                            ->default(1935),
                    ])->columns(3),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_primary')
                            ->default(false)
                            ->helperText('Only one server should be primary'),
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->default(1)
                            ->helperText('Higher weight = more traffic'),
                        Forms\Components\TextInput::make('max_connections')
                            ->numeric()
                            ->nullable()
                            ->helperText('Leave empty for unlimited'),
                    ])->columns(2),

                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_url')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('current_connections')
                    ->label('Connections'),
                Tables\Columns\TextColumn::make('load_percentage')
                    ->label('Load')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state): string => match (true) {
                        $state > 80 => 'danger',
                        $state > 50 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('last_check_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_primary'),
            ])
            ->actions([
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
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }
}
