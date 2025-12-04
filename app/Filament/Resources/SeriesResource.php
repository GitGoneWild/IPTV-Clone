<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeriesResource\Pages;
use App\Models\Series;
use App\Services\TmdbService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SeriesResource extends Resource
{
    protected static ?string $model = Series::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tv';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $pluralModelLabel = 'TV Series';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('TMDB Import')
                    ->description('Search and import TV series details from TMDB')
                    ->schema([
                        Forms\Components\TextInput::make('tmdb_search')
                            ->label('Search TMDB')
                            ->placeholder('Enter series title to search...')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('search_tmdb')
                                    ->label('Search')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->action(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $tmdb = app(TmdbService::class);

                                        if (! $tmdb->isConfigured()) {
                                            Notification::make()
                                                ->title('TMDB API not configured')
                                                ->body('Please set TMDB_API_KEY in your .env file')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        $results = $tmdb->searchSeries($state);

                                        if (empty($results)) {
                                            Notification::make()
                                                ->title('No results found')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        $set('tmdb_id', $results[0]['id']);

                                        // Auto-import first result
                                        $seriesData = $tmdb->getSeries($results[0]['id']);

                                        if ($seriesData) {
                                            foreach ($seriesData as $key => $value) {
                                                $set($key, $value);
                                            }

                                            Notification::make()
                                                ->title('Series imported from TMDB')
                                                ->success()
                                                ->send();
                                        }
                                    })
                            ),
                        Forms\Components\TextInput::make('tmdb_id')
                            ->label('TMDB ID')
                            ->numeric()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('import_tmdb')
                                    ->label('Import')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->action(function (Forms\Get $get, Forms\Set $set, $state) {
                                        if (! $state) {
                                            return;
                                        }

                                        $tmdb = app(TmdbService::class);

                                        if (! $tmdb->isConfigured()) {
                                            Notification::make()
                                                ->title('TMDB API not configured')
                                                ->body('Please set TMDB_API_KEY in your .env file')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        $seriesData = $tmdb->getSeries($state);

                                        if ($seriesData) {
                                            foreach ($seriesData as $key => $value) {
                                                $set($key, $value);
                                            }

                                            Notification::make()
                                                ->title('Series imported from TMDB')
                                                ->success()
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->title('Series not found')
                                                ->warning()
                                                ->send();
                                        }
                                    })
                            ),
                    ])->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Series Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('original_title')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('plot')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('cast')
                            ->label('Cast')
                            ->placeholder('Add actors'),
                        Forms\Components\TextInput::make('genre')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('rating')
                            ->label('Content Rating')
                            ->placeholder('TV-14, TV-MA, etc.'),
                        Forms\Components\TextInput::make('tmdb_rating')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(10),
                        Forms\Components\TextInput::make('release_year')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y') + 5),
                        Forms\Components\TextInput::make('status')
                            ->placeholder('Returning Series, Ended, etc.'),
                        Forms\Components\TextInput::make('num_seasons')
                            ->label('Number of Seasons')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('num_episodes')
                            ->label('Number of Episodes')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('imdb_id')
                            ->label('IMDB ID')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\TextInput::make('poster_url')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('backdrop_url')
                            ->url()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('poster_url')
                    ->label('Poster')
                    ->width(60)
                    ->height(90),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Series $record) => $record->release_year),
                Tables\Columns\TextColumn::make('genre')
                    ->badge()
                    ->separator(',')
                    ->limit(30),
                Tables\Columns\TextColumn::make('num_seasons')
                    ->label('Seasons')
                    ->sortable(),
                Tables\Columns\TextColumn::make('num_episodes')
                    ->label('Episodes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tmdb_rating')
                    ->label('Rating')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state >= 7 ? 'success' : ($state >= 5 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => str_contains($state, 'Returning') ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Returning Series' => 'Returning Series',
                        'Ended' => 'Ended',
                        'Canceled' => 'Canceled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSeries::route('/'),
        ];
    }
}
