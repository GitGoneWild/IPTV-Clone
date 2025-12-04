<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BouquetResource\Pages;
use App\Models\Bouquet;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BouquetResource extends Resource
{
    protected static ?string $model = Bouquet::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Streaming';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_type')
                    ->label('Category Type')
                    ->options([
                        'live_tv' => 'Live TV',
                        'movie' => 'Movies',
                        'series' => 'TV Shows/Series',
                    ])
                    ->default('live_tv')
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('region')
                    ->label('Region')
                    ->placeholder('e.g., UK, US, FR')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Select::make('streams')
                    ->multiple()
                    ->relationship('streams', 'name')
                    ->preload()
                    ->searchable()
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
                Tables\Columns\TextColumn::make('category_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'live_tv' => 'info',
                        'movie' => 'success',
                        'series' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'live_tv' => 'Live TV',
                        'movie' => 'Movies',
                        'series' => 'TV Shows',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('region')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('streams_count')
                    ->counts('streams')
                    ->label('Streams'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('category_type')
                    ->label('Category Type')
                    ->options([
                        'live_tv' => 'Live TV',
                        'movie' => 'Movies',
                        'series' => 'TV Shows',
                    ]),
                Tables\Filters\SelectFilter::make('region')
                    ->label('Region')
                    ->options(function () {
                        return \App\Models\Bouquet::query()
                            ->whereNotNull('region')
                            ->distinct()
                            ->pluck('region', 'region')
                            ->toArray();
                    }),
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
            'index' => Pages\ListBouquets::route('/'),
            'create' => Pages\CreateBouquet::route('/create'),
            'edit' => Pages\EditBouquet::route('/{record}/edit'),
        ];
    }
}
