<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovieResource\Pages;
use App\Models\Movie;
use App\Services\MediaDownloadService;
use App\Services\TmdbService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-film';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
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

                                        if (! $tmdb->isConfigured()) {
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
                    ->description('Enter a stream URL or local file path. URLs can be downloaded for local caching.')
                    ->schema([
                        Forms\Components\Textarea::make('stream_url')
                            ->label('Stream URL')
                            ->helperText('Enter the direct URL to the movie file (can be downloaded locally)')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('local_path')
                            ->label('Local Path')
                            ->helperText('Path to locally cached file (auto-filled when downloaded)')
                            ->disabled()
                            ->columnSpanFull()
                            ->visibleOn('edit'),
                        Forms\Components\Grid::make(4)
                            ->schema([
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
                                Forms\Components\Placeholder::make('download_status_display')
                                    ->label('Download Status')
                                    ->content(fn (?Movie $record) => match ($record?->download_status) {
                                        'pending' => '⏳ Pending',
                                        'downloading' => '⬇️ Downloading ('.$record->download_progress.'%)',
                                        'completed' => '✅ Downloaded',
                                        'failed' => '❌ Failed: '.($record->download_error ?? 'Unknown error'),
                                        default => '—',
                                    })
                                    ->visibleOn('edit'),
                            ]),
                    ]),

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
                Tables\Columns\TextColumn::make('download_status')
                    ->label('Download')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'completed' => 'success',
                        'downloading' => 'info',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state, Movie $record) => match ($state) {
                        'downloading' => 'Downloading '.$record->download_progress.'%',
                        'completed' => 'Local',
                        'pending' => 'Queued',
                        'failed' => 'Failed',
                        default => 'Remote',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('download_status')
                    ->options([
                        'pending' => 'Pending',
                        'downloading' => 'Downloading',
                        'completed' => 'Downloaded',
                        'failed' => 'Failed',
                    ])
                    ->label('Download Status'),
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
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (Movie $record) => $record->stream_url && ! in_array($record->download_status, ['downloading', 'completed', 'pending']))
                    ->action(function (Movie $record) {
                        app(MediaDownloadService::class)->queueDownload($record);
                        Notification::make()
                            ->title('Download queued')
                            ->body("'{$record->title}' has been queued for download.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('delete_local')
                    ->label('Delete Local')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (Movie $record) => $record->download_status === 'completed')
                    ->requiresConfirmation()
                    ->action(function (Movie $record) {
                        app(MediaDownloadService::class)->deleteLocalFile($record);
                        Notification::make()
                            ->title('Local file deleted')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('download_selected')
                        ->label('Download Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $downloadService = app(MediaDownloadService::class);
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->stream_url && ! in_array($record->download_status, ['downloading', 'completed', 'pending'])) {
                                    $downloadService->queueDownload($record);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title("Queued {$count} movies for download")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
