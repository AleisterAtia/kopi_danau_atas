<?php

namespace App\Notifications;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Native OS push notification (via the browser's Push API) telling an admin
 * a booking was just paid — fires alongside the WhatsApp and in-panel
 * notifications from MidtransService::notifyAdminsOfPayment(), so it reaches
 * admins whether their phone has the panel open, WhatsApp, or neither.
 */
class BookingPaidPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Pembayaran diterima')
            ->icon('/favicon.ico')
            ->body("{$this->booking->booking_code} — {$this->booking->guest_name} ({$this->booking->guest_count} orang) sudah bayar.")
            ->data(['url' => BookingResource::getUrl('edit', ['record' => $this->booking])]);
    }
}
