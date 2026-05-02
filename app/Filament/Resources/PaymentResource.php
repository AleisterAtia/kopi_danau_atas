<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Payment Information')
                ->schema([
                    Forms\Components\TextInput::make('midtrans_order_id')->disabled(),
                    Forms\Components\TextInput::make('midtrans_transaction_id')->disabled(),
                    Forms\Components\TextInput::make('payment_type')->disabled(),
                    Forms\Components\TextInput::make('status')->disabled(),
                    Forms\Components\TextInput::make('gross_amount')->prefix('Rp')->disabled(),
                    Forms\Components\DateTimePicker::make('paid_at')->disabled(),
                    Forms\Components\Textarea::make('midtrans_response')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                        ->disabled()
                        ->rows(10)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('midtrans_order_id')
                    ->label('Order ID')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('booking.booking_code')
                    ->label('Booking')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Method')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'settlement',
                        'danger' => fn ($state) => in_array($state, ['expire', 'cancel', 'deny']),
                        'info' => 'refund',
                    ]),

                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not paid'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'settlement' => 'Settlement',
                        'expire' => 'Expired',
                        'cancel' => 'Cancelled',
                        'deny' => 'Denied',
                        'refund' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
