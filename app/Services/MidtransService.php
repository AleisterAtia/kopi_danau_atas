<?php

namespace App\Services;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
                $payment->booking->update(['status' => 'paid']);

                if (! $wasAlreadyPaid) {
                    $this->finalizePaidBooking($payment->booking->fresh());
                }
                break;

            case 'expire':
                $payment->update(['status' => 'expire']);
                $payment->booking->update(['status' => 'expired']);
                break;

            case 'cancel':
            case 'deny':
                $payment->update(['status' => $incomingStatus]);
                $payment->booking->update(['status' => 'cancelled']);
                break;

            case 'pending':
                $payment->update(['status' => 'pending']);
                break;

            case 'refund':
            case 'partial_refund':
                $payment->update(['status' => 'refund']);
                $payment->booking->update(['status' => 'cancelled']);
                break;

            default:
                Log::warning('Midtrans notification with unknown status', [
                    'order_id' => $orderId,
                    'status' => $incomingStatus,
                ]);
        }

        Log::info('Midtrans notification processed', [
            'order_id' => $orderId,
            'transaction_id' => $incomingTransactionId,
            'status' => $incomingStatus,
        ]);
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
    }
}
