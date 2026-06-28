<?php

namespace App\Filament\Pages;

use App\Services\TicketCheckInService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * On-site staff page to scan/validate e-ticket QR codes and check guests in.
 *
 * The QR encodes a deep-link to this page (?token=...). Scanning it from a
 * device logged into the admin panel prefills the token and validates in
 * one step; staff can also paste a token manually. All decision logic lives
 * in TicketCheckInService so it is unit-testable independent of the UI.
 */
class TicketCheckIn extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'ticket-check-in';

    protected static ?string $title = 'Check-in Tiket';

    protected static ?string $navigationLabel = 'Check-in Tiket';

    protected static string $view = 'filament.pages.ticket-check-in';

    public string $token = '';

    /** @var array{result: string, booking: ?array}|null */
    public ?array $outcome = null;

    public function mount(): void
    {
        $token = request()->query('token');

        if (is_string($token) && $token !== '') {
            $this->token = $token;
            $this->submit();
        }
    }

    public function submit(): void
    {
        $token = trim($this->token);

        if ($token === '') {
            $this->outcome = null;
            Notification::make()->title('Masukkan atau pindai token tiket.')->warning()->send();

            return;
        }

        $result = app(TicketCheckInService::class)->checkIn($token, auth()->user());
        $booking = $result['booking'];

        $this->outcome = [
            'result' => $result['result'],
            'booking' => $booking ? [
                'code' => $booking->booking_code,
                'package' => $booking->tourPackage?->name,
                'guest_name' => $booking->guest_name ?? $booking->user?->name,
                'guest_count' => $booking->guest_count,
                'visit_date' => optional($booking->visit_date)->format('d M Y'),
                'status' => $booking->status,
                'checked_in_at' => optional($booking->checked_in_at)->format('d M Y H:i'),
                'checked_in_by' => $booking->checkedInBy?->name,
            ] : null,
        ];

        match ($result['result']) {
            TicketCheckInService::RESULT_SUCCESS => Notification::make()
                ->title('Check-in berhasil')
                ->body($booking?->booking_code)
                ->success()->send(),
            TicketCheckInService::RESULT_ALREADY => Notification::make()
                ->title('Tiket sudah pernah check-in')
                ->warning()->send(),
            TicketCheckInService::RESULT_NOT_ELIGIBLE => Notification::make()
                ->title('Tiket belum lunas / tidak valid')
                ->danger()->send(),
            default => Notification::make()
                ->title('Tiket tidak ditemukan')
                ->danger()->send(),
        };

        // Clear the input so the next guest can be scanned immediately.
        $this->token = '';
    }
}
