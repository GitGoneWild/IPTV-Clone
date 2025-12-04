<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Services\BillingService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => Invoice::generateInvoiceNumber()),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('reseller_id')
                            ->relationship('reseller', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD - US Dollar',
                                'EUR' => 'EUR - Euro',
                                'GBP' => 'GBP - British Pound',
                            ])
                            ->default('USD')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'stripe' => 'Stripe',
                                'paypal' => 'PayPal',
                                'bank_transfer' => 'Bank Transfer',
                                'crypto' => 'Cryptocurrency',
                                'manual' => 'Manual',
                            ])
                            ->nullable(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('due_date')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Package Line Items')
                    ->schema([
                        Forms\Components\Repeater::make('line_items')
                            ->label('Packages to Assign')
                            ->schema([
                                Forms\Components\Select::make('bouquet_id')
                                    ->label('Package (Bouquet)')
                                    ->options(function () {
                                        return \App\Models\Bouquet::pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $bouquet = \App\Models\Bouquet::find($state);
                                            if ($bouquet) {
                                                $set('name', $bouquet->name);
                                                $set('description', $bouquet->description);
                                                $set('type', $bouquet->category_type);
                                                $set('region', $bouquet->region);
                                            }
                                        }
                                    }),
                                Forms\Components\Hidden::make('name'),
                                Forms\Components\Hidden::make('description'),
                                Forms\Components\Hidden::make('type'),
                                Forms\Components\Hidden::make('region'),
                            ])
                            ->columns(1)
                            ->defaultItems(0)
                            ->helperText('Add packages that will be assigned to the user when this invoice is paid.')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Package'),
                    ])
                    ->description('Packages will be automatically assigned when the invoice is marked as paid.')
                    ->collapsible(),

                Forms\Components\Section::make('Additional Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn ($query) => $query->overdue())
                    ->label('Overdue Only'),
            ])
            ->actions([
                Tables\Actions\Action::make('markPaid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Invoice as Paid')
                    ->modalDescription('This will mark the invoice as paid and assign any packages (bouquets) to the user.')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'paypal' => 'PayPal',
                                'manual' => 'Manual',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->placeholder('Transaction ID, check number, etc.'),
                    ])
                    ->action(function ($record, array $data) {
                        $billingService = app(BillingService::class);
                        $success = $billingService->processPaymentAndAssignPackages(
                            $record,
                            $data['payment_method'],
                            $data['payment_reference'] ?? null
                        );

                        if ($success) {
                            Notification::make()
                                ->success()
                                ->title('Invoice Paid')
                                ->body('Invoice marked as paid and packages assigned to user.')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Failed to process payment.')
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
