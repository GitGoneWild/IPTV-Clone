<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StreamResource\Pages;
use App\Models\Stream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('stream_url')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('stream_type')
                            ->options(config('homelabtv.stream_types'))
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

                Forms\Components\Section::make('EPG & Display')
                    ->schema([
                        Forms\Components\TextInput::make('epg_channel_id')
                            ->label('EPG Channel ID'),
                        Forms\Components\TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->url(),
                        Forms\Components\TextInput::make('stream_icon')
                            ->label('Stream Icon URL')
                            ->url(),
                        Forms\Components\TextInput::make('custom_sid')
                            ->label('Custom SID'),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_hidden')
                            ->default(false),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stream_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hls' => 'success',
                        'rtmp' => 'warning',
                        'mpegts' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_check_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('last_check_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('stream_type')
                    ->options(config('homelabtv.stream_types')),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('last_check_status')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
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
            ]);
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
