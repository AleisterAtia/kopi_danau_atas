<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBookingsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()->with(['user', 'tourPackage'])->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Code')
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Guest'),

                Tables\Columns\TextColumn::make('tourPackage.name')
                    ->label('Package')
                    ->limit(25),

                Tables\Columns\TextColumn::make('visit_date')
                    ->date(),

                Tables\Columns\TextColumn::make('guest_count')
                    ->label('Guests')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'paid',
                        'primary' => 'confirmed',
                        'success' => 'completed',
                        'danger' => fn ($state) => in_array($state, ['cancelled', 'expired']),
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since(),
            ])
            ->paginated(false);
    }
}
