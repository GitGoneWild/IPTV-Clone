<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovieResource\Pages;
use App\Models\Movie;
use App\Services\TmdbService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('TMDB Import')
                    ->description('Search and import movie details from TMDB')
                    ->schema([
                        Forms\Components\TextInput::make('tmdb_search')
                            ->label('Search TMDB')
                            ->placeholder('Enter movie title to search...')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('search_tmdb')
                                    ->label('Search')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->action(function (Forms\Get $get, Forms\Set $set, $state) {
                                        $tmdb = app(TmdbService::class);
                                        
                                        if (!$tmdb->isConfigured()) {
                                            Notification::make()
                                                ->title('TMDB API not configured')
                                                ->body('Please set TMDB_API_KEY in your .env file')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $results = $tmdb->searchMovie($state);
                                        
                                        if (empty($results)) {
                                            Notification::make()
                                                ->title('No results found')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $set('tmdb_id', $results[0]['id']);
                                        
                                        // Auto-import first result
                                        $movieData = $tmdb->getMovie($results[0]['id']);
                                        
                                        if ($movieData) {
                                            foreach ($movieData as $key => $value) {
                                                $set($key, $value);
                                            }
                                            
                                            Notification::make()
                                                ->title('Movie imported from TMDB')
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
                                        if (!$state) {
                                            return;
                                        }

                                        $tmdb = app(TmdbService::class);
                                        
                                        if (!$tmdb->isConfigured()) {
                                            Notification::make()
                                                ->title('TMDB API not configured')
                                                ->body('Please set TMDB_API_KEY in your .env file')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $movieData = $tmdb->getMovie($state);
                                        
                                        if ($movieData) {
                                            foreach ($movieData as $key => $value) {
                                                $set($key, $value);
                                            }
                                            
                                            Notification::make()
                                                ->title('Movie imported from TMDB')
                                                ->success()
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->title('Movie not found')
                                                ->warning()
                                                ->send();
                                        }
                                    })
                            ),
                    ])->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Movie Information')
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
                        Forms\Components\TextInput::make('director')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('genre')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('runtime')
                            ->numeric()
                            ->suffix('minutes'),
                        Forms\Components\TextInput::make('rating')
                            ->label('Content Rating')
                            ->placeholder('PG-13, R, etc.'),
                        Forms\Components\TextInput::make('tmdb_rating')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(10),
                        Forms\Components\TextInput::make('release_year')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y') + 5),
                        Forms\Components\DatePicker::make('release_date'),
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
                        Forms\Components\TextInput::make('trailer_url')
                            ->url()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Stream Configuration')
                    ->schema([
                        Forms\Components\Textarea::make('stream_url')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('stream_type')
                            ->options(config('homelabtv.stream_types', [
                                'hls' => 'HLS',
                                'mpegts' => 'MPEG-TS',
                                'rtmp' => 'RTMP',
                                'http' => 'HTTP',
                            ]))
                            ->default('hls')
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('server_id')
                            ->relationship('server', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
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
                    ->description(fn (Movie $record) => $record->release_year),
                Tables\Columns\TextColumn::make('genre')
                    ->badge()
                    ->separator(',')
                    ->limit(30),
                Tables\Columns\TextColumn::make('runtime')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tmdb_rating')
                    ->label('Rating')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state >= 7 ? 'success' : ($state >= 5 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('release_year')
                    ->form([
                        Forms\Components\TextInput::make('year')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['year'],
                            fn ($query, $year) => $query->where('release_year', $year)
                        );
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMovies::route('/'),
        ];
    }
}

