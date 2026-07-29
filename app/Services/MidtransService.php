<?php

namespace App\Services;

use App\Filament\Resources\BookingResource;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\BookingPaidPushNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create (or refresh) a Snap token for a booking.
     *
     * Behaviour:
     * - If the booking is already paid/confirmed/completed → reject.
     *   The user should not be able to pay twice.
     * - If a Payment row already exists with status `pending` → reuse
     *   its `midtrans_order_id` so the existing webhook
     *   correlation continues to work. We still ask Midtrans for a
     *   fresh Snap token (Snap tokens have a short TTL).
     * - Otherwise create a new Payment row with a brand-new order_id.
     */
    public function createSnapToken(Booking $booking): string
    {
        $booking->load(['user', 'tourPackage', 'payment']);

        if (in_array($booking->status, ['paid', 'confirmed', 'completed'], true)) {
            throw new RuntimeException('Booking ini sudah dibayar dan tidak dapat dibayar ulang.');
        }

        $existingPayment = $booking->payment;

        // Reuse the existing order_id when we still have a pending payment.
        // This keeps webhook correlation stable across "Pay Again" clicks.
        $orderId = ($existingPayment && $existingPayment->status === 'pending' && $existingPayment->midtrans_order_id)
            ? $existingPayment->midtrans_order_id
            : 'KDA-'.$booking->id.'-'.time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($booking->total_price),
            ],
            'customer_details' => [
                'first_name' => $booking->guest_name ?? $booking->user->name,
                'email' => $booking->guest_email ?? $booking->user->email,
                'phone' => $booking->guest_phone ?? $booking->user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => $booking->tour_package_id,
                    'price' => (int) round($booking->tourPackage->price),
                    'quantity' => $booking->guest_count,
                    'name' => substr($booking->tourPackage->name, 0, 50),
                ],
            ],
            'callbacks' => [
                'finish' => url('/booking/'.$booking->id),
            ],
            // Kill the payment window exactly when bookings:expire-pending kills
            // the booking (1h after created_at), so a bank-transfer VA — valid
            // 24h by default — can never outlive the booking it pays for and
            // land money on an already-expired row. Counted from created_at, not
            // now(), so "pay again" clicks don't extend the window.
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => $this->minutesLeftToPay($booking),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'midtrans_order_id' => $orderId,
                'snap_token' => $snapToken,
                'gross_amount' => round($booking->total_price, 2),
                'status' => 'pending',
            ]
        );

        Log::info('Midtrans Snap token created', [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'order_id' => $orderId,
            'reused' => $existingPayment && $existingPayment->midtrans_order_id === $orderId,
        ]);

        return $snapToken;
    }

    /**
     * Minutes a booking still has before bookings:expire-pending reclaims it.
     *
     * Floored at 1 because Midtrans rejects a zero/negative duration; a booking
     * that is already past the window simply gets the shortest window Midtrans
     * accepts, and the late-payment guard in handleNotification() catches
     * whatever slips through that last minute.
     */
    protected function minutesLeftToPay(Booking $booking): int
    {
        // ponytail: the 1h window is duplicated in ExpirePendingBookings and
        // TourPackage::getAvailableQuota — promote to one Booking constant if a
        // third rule ever needs it.
        $elapsed = (int) $booking->created_at->diffInMinutes(now());

        return max(1, 60 - $elapsed);
    }

    /**
     * Verify webhook signature from Midtrans.
     */
    public function verifySignature(array $notification): bool
    {
        $signatureKey = hash('sha512',
            ($notification['order_id'] ?? '').
            ($notification['status_code'] ?? '').
            ($notification['gross_amount'] ?? '').
            config('midtrans.server_key')
        );

        return hash_equals($signatureKey, (string) ($notification['signature_key'] ?? ''));
    }

    /**
     * Handle a notification payload (either from the webhook or from
     * the client-side polling fallback).
     *
     * Idempotency: if Midtrans replays a notification we have already
     * processed (same `transaction_id` for the same payment), we
     * short-circuit so QR / email / status updates do not run twice.
     */
    public function handleNotification(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;

        if (! $orderId) {
            Log::warning('Midtrans notification without order_id', $notification);

            return;
        }

        $payment = Payment::where('midtrans_order_id', $orderId)->first();

        if (! $payment) {
            Log::warning('Midtrans notification for unknown order_id', [
                'order_id' => $orderId,
            ]);

            return;
        }

        $incomingTransactionId = $notification['transaction_id'] ?? null;
        $incomingStatus = $notification['transaction_status'] ?? '';

        // Idempotency: same transaction_id + same status already processed.
        if (
            $incomingTransactionId
            && $payment->midtrans_transaction_id === $incomingTransactionId
            && $payment->status === $this->mapTransactionStatus($incomingStatus)
        ) {
            Log::info('Midtrans notification ignored (already processed)', [
                'order_id' => $orderId,
                'transaction_id' => $incomingTransactionId,
                'status' => $incomingStatus,
            ]);

            return;
        }

        // Payment row and booking row must move together or not at all. Without
        // this transaction the payment update commits, the booking update throws
        // on an illegal transition, and Midtrans' retry then hits the
        // idempotency guard above and gets a 200 — silently burying the failure.
        $shouldFinalize = DB::transaction(function () use ($payment, $notification, $incomingTransactionId, $incomingStatus, $orderId) {
            $payment->update([
                'midtrans_transaction_id' => $incomingTransactionId,
                'payment_type' => $notification['payment_type'] ?? null,
                'midtrans_response' => $notification,
            ]);

            switch ($incomingStatus) {
                case 'capture':
                case 'settlement':
                    // Idempotency: only fire post-payment side-effects (QR + email)
                    // when this booking transitions INTO paid for the first time.
                    $wasAlreadyPaid = in_array(
                        $payment->booking->status,
                        ['paid', 'confirmed', 'completed'],
                        true
                    );

                    $payment->update(['status' => 'settlement', 'paid_at' => $payment->paid_at ?? now()]);

                    return $this->applyBookingStatus($payment, 'paid') && ! $wasAlreadyPaid;

                case 'expire':
                    $payment->update(['status' => 'expire']);
                    $this->applyBookingStatus($payment, 'expired');
                    break;

                case 'cancel':
                case 'deny':
                    $payment->update(['status' => $incomingStatus]);
                    $this->applyBookingStatus($payment, 'cancelled');
                    break;

                case 'pending':
                    $payment->update(['status' => 'pending']);
                    break;

                case 'refund':
                case 'partial_refund':
                    $payment->update(['status' => 'refund']);
                    $this->applyBookingStatus($payment, 'cancelled');
                    break;

                default:
                    Log::warning('Midtrans notification with unknown status', [
                        'order_id' => $orderId,
                        'status' => $incomingStatus,
                    ]);
            }

            return false;
        });

        // Runs after commit: QR writes a file and mail queues a job, neither of
        // which should happen for a transaction that ends up rolled back.
        if ($shouldFinalize) {
            $this->finalizePaidBooking($payment->booking->fresh());
        }

        Log::info('Midtrans notification processed', [
            'order_id' => $orderId,
            'transaction_id' => $incomingTransactionId,
            'status' => $incomingStatus,
        ]);
    }

    /**
     * Apply a booking status coming from Midtrans, refusing illegal transitions
     * instead of letting them throw.
     *
     * A notification is a statement about money that already moved — it is never
     * wrong, and it can never be "retried away". If it disagrees with the
     * booking's state (almost always because bookings:expire-pending reclaimed
     * the booking while the customer was paying a still-valid VA), throwing
     * would abort the webhook and leave Midtrans retrying into the idempotency
     * guard. So we record the payment truthfully, leave the booking alone, and
     * shout for a human — the same manual-reconciliation stance RefundService
     * documents for refunds.
     *
     * @return bool whether the booking actually moved
     */
    protected function applyBookingStatus(Payment $payment, string $status): bool
    {
        $booking = $payment->booking;

        if (Booking::canTransitionTo($booking->status, $status)) {
            $booking->update(['status' => $status]);

            return true;
        }

        Log::critical('Midtrans notification could not be applied to booking', [
            'booking_code' => $booking->booking_code,
            'booking_status' => $booking->status,
            'attempted_status' => $status,
            'order_id' => $payment->midtrans_order_id,
            'transaction_id' => $payment->midtrans_transaction_id,
            'gross_amount' => $payment->gross_amount,
            'action_required' => $status === 'paid'
                ? 'Uang diterima untuk booking yang sudah mati — refund manual lewat dashboard Midtrans.'
                : 'Status pembayaran & booking tidak sinkron — rekonsiliasi manual.',
        ]);

        return false;
    }

    /**
     * Map a Midtrans transaction_status value to our Payment.status
     * field. Only used for idempotency comparisons.
     */
    protected function mapTransactionStatus(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'capture', 'settlement' => 'settlement',
            'refund', 'partial_refund' => 'refund',
            default => $midtransStatus,
        };
    }

    /**
     * Run side-effects that should happen exactly once when a booking
     * transitions to paid: generate QR code + send confirmation email.
     *
     * Errors are logged but never thrown — we don't want a flaky SMTP
     * server to mark the webhook handler as failed (which would cause
     * Midtrans to retry and double-update the booking).
     */
    protected function finalizePaidBooking(Booking $booking): void
    {
        try {
            $qrPath = app(QrCodeService::class)->generate($booking);
            $booking->update(['qr_code_path' => $qrPath]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate QR code for booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            Mail::to($booking->guest_email ?? $booking->user->email)
                ->queue(new BookingConfirmation($booking->fresh(), app()->getLocale()));
        } catch (\Throwable $e) {
            Log::error('Failed to queue booking confirmation email for '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            $this->notifyAdminsOfPayment($booking);
        } catch (\Throwable $e) {
            Log::error('Failed to notify admins of paid booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Alert admins the moment a booking is actually paid (not when merely
     * placed) — this is the point at which staff need to act (confirm the
     * booking). Sent to the database (Filament bell) + broadcast over
     * websockets (Reverb) for an instant in-panel toast, to WhatsApp for
     * staff not looking at the panel, and as a native OS push notification
     * for staff who opted in via "Aktifkan Notifikasi" but have no tab open.
     */
    protected function notifyAdminsOfPayment(Booking $booking): void
    {
        $admins = User::where('role', 'admin')->get();

        // Each channel below gets its own try/catch: Reverb being down (or any
        // other channel failing) must not stop the remaining channels from
        // firing — WhatsApp and push are exactly the "admin isn't looking at
        // the panel" fallbacks, so they especially can't depend on the panel's
        // own realtime channel working.
        try {
            Notification::make()
                ->title('Pembayaran diterima')
                ->body("{$booking->booking_code} — {$booking->guest_name} ({$booking->guest_count} orang) sudah bayar, menunggu konfirmasi.")
                ->icon('heroicon-o-banknotes')
                ->iconColor('success')
                ->actions([
                    Action::make('view')
                        ->label('Lihat')
                        ->url(BookingResource::getUrl('edit', ['record' => $booking])),
                ])
                ->sendToDatabase($admins, isEventDispatched: true)
                ->broadcast($admins);
        } catch (\Throwable $e) {
            Log::error('Failed to send in-panel notification for booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }

        $this->notifyAdminsOfPaymentViaWhatsapp($admins, $booking);

        try {
            LaravelNotification::send($admins, new BookingPaidPushNotification($booking));
        } catch (\Throwable $e) {
            Log::error('Failed to send push notification to admins for booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * WhatsApp leg of notifyAdminsOfPayment(), via the Fonnte gateway.
     * No-op when FONNTE_TOKEN is unset (feature off, same fallback stance as
     * GEMINI_API_KEY) — one admin's missing phone or a failed send is logged
     * and skipped rather than blocking the others.
     */
    protected function notifyAdminsOfPaymentViaWhatsapp(\Illuminate\Database\Eloquent\Collection $admins, Booking $booking): void
    {
        if (blank(config('services.fonnte.token'))) {
            return;
        }

        $message = "Booking baru dibayar!\n\n"
            ."Kode: {$booking->booking_code}\n"
            ."Nama: {$booking->guest_name}\n"
            ."Jumlah tamu: {$booking->guest_count}\n\n"
            .'Konfirmasi di: '.BookingResource::getUrl('edit', ['record' => $booking]);

        foreach ($admins as $admin) {
            if (blank($admin->phone)) {
                continue;
            }

            try {
                Http::withHeaders(['Authorization' => config('services.fonnte.token')])
                    ->asForm()
                    ->post('https://api.fonnte.com/send', [
                        'target' => $this->toFonnteTarget($admin->phone),
                        'message' => $message,
                    ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send WhatsApp payment notification to admin '.$admin->id, [
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Fonnte expects the country code — a local "08xx" number never reaches
     * the device. Storage keeps whatever an admin typed into their profile.
     */
    protected function toFonnteTarget(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}
