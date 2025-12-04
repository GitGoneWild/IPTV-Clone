<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Users & Access';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),

                Forms\Components\Section::make('Role & Permissions')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Primary Role')
                            ->options([
                                'guest' => 'Guest (No Access)',
                                'user' => 'User (Stream Access)',
                                'reseller' => 'Reseller',
                                'admin' => 'Administrator',
                            ])
                            ->default('guest')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                // Sync legacy fields with role selection
                                $set('is_admin', $state === 'admin');
                                $set('is_reseller', in_array($state, ['admin', 'reseller']));
                            })
                            ->helperText('Note: Guest users are automatically upgraded to User when a package is assigned.'),
                        Forms\Components\Toggle::make('is_admin')
                            ->default(false)
                            ->readonly()
                            ->dehydrated(true),
                        Forms\Components\Toggle::make('is_reseller')
                            ->default(false)
                            ->readonly()
                            ->dehydrated(true),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Select::make('reseller_id')
                            ->label('Parent Reseller')
                            ->relationship('reseller', 'name', fn ($query) => $query->where('is_reseller', true))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Subscription & Limits')
                    ->schema([
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiry Date'),
                        Forms\Components\TextInput::make('max_connections')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        Forms\Components\TextInput::make('credits')
                            ->numeric()
                            ->default(0)
                            ->visible(fn ($get) => $get('is_reseller')),
                        Forms\Components\CheckboxList::make('allowed_outputs')
                            ->options(config('homelabtv.output_formats'))
                            ->default(['m3u', 'xtream', 'enigma2']),
                    ])->columns(2),

                Forms\Components\Section::make('Bouquets')
                    ->schema([
                        Forms\Components\Select::make('bouquets')
                            ->multiple()
                            ->relationship('bouquets', 'name')
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'reseller' => 'warning',
                        'user' => 'success',
                        'guest' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean()
                    ->label('Admin')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_reseller')
                    ->boolean()
                    ->label('Reseller')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($state) => $state && $state < now() ? 'danger' : null),
                Tables\Columns\TextColumn::make('max_connections'),
                Tables\Columns\TextColumn::make('bouquets_count')
                    ->counts('bouquets')
                    ->label('Bouquets'),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin'),
                Tables\Filters\TernaryFilter::make('is_reseller'),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn ($query) => $query->where('expires_at', '<', now()))
                    ->label('Expired Only'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
