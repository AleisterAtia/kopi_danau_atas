<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Services\QrCodeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'booking_code';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Booking Details')
                ->schema([
                    Forms\Components\TextInput::make('booking_code')
                        ->disabled(),

                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->disabled()
                        ->searchable(),

                    Forms\Components\Select::make('tour_package_id')
                        ->relationship('tourPackage', 'name')
                        ->disabled(),

                    Forms\Components\DatePicker::make('visit_date')
                        ->disabled(),

                    Forms\Components\TextInput::make('guest_count')
                        ->disabled(),

                    Forms\Components\TextInput::make('total_price')
                        ->prefix('Rp')
                        ->disabled(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'confirmed' => 'Confirmed',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'expired' => 'Expired',
                        ])
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Guest Contact')
                ->description('Detail data pemesan yang diisi saat checkout')
                ->schema([
                    Forms\Components\TextInput::make('guest_name')
                        ->label('Nama Pemesan')
                        ->disabled(),
                    Forms\Components\TextInput::make('guest_phone')
                        ->label('No. WhatsApp')
                        ->disabled(),
                    Forms\Components\TextInput::make('guest_email')
                        ->label('Email')
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan Khusus')
                        ->disabled()
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2)->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('guest_name')
                    ->label('Pemesan')
                    ->searchable()
                    ->description(fn (Booking $record): ?string => $record->guest_phone)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Akun')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tourPackage.name')
                    ->label('Package')
                    ->sortable()
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('visit_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('guest_count')
                    ->label('Pax')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'paid',
                        'primary' => 'confirmed',
                        'success' => 'completed',
                        'danger' => fn ($state) => in_array($state, ['cancelled', 'expired']),
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dipesan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                SelectFilter::make('tour_package_id')
                    ->relationship('tourPackage', 'name')
                    ->label('Package')
                    ->searchable()
                    ->preload(),

                Filter::make('visit_date')
                    ->form([
                        DatePicker::make('visit_from')->label('Visit Date From'),
                        DatePicker::make('visit_until')->label('Visit Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['visit_from'] ?? null, fn ($q, $date) => $q->whereDate('visit_date', '>=', $date))
                            ->when($data['visit_until'] ?? null, fn ($q, $date) => $q->whereDate('visit_date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['visit_from'] ?? null) {
                            $indicators[] = 'Dari ' . \Carbon\Carbon::parse($data['visit_from'])->format('d M Y');
                        }
                        if ($data['visit_until'] ?? null) {
                            $indicators[] = 'Sampai ' . \Carbon\Carbon::parse($data['visit_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => $record->status === 'paid')
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'confirmed']);
                        Notification::make()->title('Booking confirmed')->success()->send();
                    }),

                Tables\Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-flag')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => $record->status === 'confirmed')
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'completed']);
                        Notification::make()->title('Booking marked as completed')->success()->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => in_array($record->status, ['pending', 'paid', 'confirmed']))
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()->title('Booking cancelled')->warning()->send();
                    }),

                Tables\Actions\Action::make('resendEticket')
                    ->label('Resend E-ticket')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim ulang e-tiket')
                    ->modalDescription('Email konfirmasi & e-tiket akan dikirim ulang ke alamat email pemesan.')
                    ->visible(fn (Booking $record) => in_array($record->status, ['paid', 'confirmed', 'completed']))
                    ->action(function (Booking $record) {
                        try {
                            // Pastikan QR sudah ada (regenerate jika tidak ada)
                            if (! $record->qr_code_path) {
                                $qrPath = app(QrCodeService::class)->generate($record);
                                $record->update(['qr_code_path' => $qrPath]);
                                $record->refresh();
                            }

                            Mail::to($record->guest_email ?? $record->user->email)
                                ->queue(new BookingConfirmation($record, app()->getLocale()));

                            Notification::make()
                                ->title('E-tiket masuk antrian email')
                                ->body('Email akan terkirim setelah queue worker memproses.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal mengirim e-tiket')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'paid')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
