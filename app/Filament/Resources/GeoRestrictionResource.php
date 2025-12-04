<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeoRestrictionResource\Pages;
use App\Models\GeoRestriction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GeoRestrictionResource extends Resource
{
    protected static ?string $model = GeoRestriction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Geo Restriction Details')
                    ->schema([
                        Forms\Components\TextInput::make('country_code')
                            ->label('Country Code')
                            ->required()
                            ->maxLength(2)
                            ->placeholder('US')
                            ->helperText('ISO 3166-1 alpha-2 country code (e.g., US, UK, DE)'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'allow' => 'Allow',
                                'block' => 'Block',
                            ])
                            ->default('block')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(3),

                Forms\Components\Section::make('Scope')
                    ->schema([
                        Forms\Components\Select::make('restrictable_type')
                            ->label('Apply To')
                            ->options([
                                '' => 'Global (All)',
                                'App\\Models\\User' => 'Specific User',
                                'App\\Models\\Stream' => 'Specific Stream',
                                'App\\Models\\Bouquet' => 'Specific Bouquet',
                            ])
                            ->nullable()
                            ->reactive(),
                        Forms\Components\TextInput::make('restrictable_id')
                            ->label('Entity ID')
                            ->numeric()
                            ->nullable()
                            ->visible(fn ($get) => ! empty($get('restrictable_type'))),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Reason for this restriction...')
                            ->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'allow' => 'success',
                        'block' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('restrictable_type')
                    ->label('Scope')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        null => 'Global',
                        'App\\Models\\User' => 'User',
                        'App\\Models\\Stream' => 'Stream',
                        'App\\Models\\Bouquet' => 'Bouquet',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('restrictable_id')
                    ->label('Entity ID')
                    ->placeholder('N/A'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(30)
                    ->placeholder('No notes'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'allow' => 'Allow',
                        'block' => 'Block',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\Filter::make('global')
                    ->query(fn ($query) => $query->global())
                    ->label('Global Only'),
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
            'index' => Pages\ListGeoRestrictions::route('/'),
            'create' => Pages\CreateGeoRestriction::route('/create'),
            'edit' => Pages\EditGeoRestriction::route('/{record}/edit'),
        ];
    }
}
