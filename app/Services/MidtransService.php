<?php

namespace App\Services;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Snap;

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
     * Create a Snap token for a booking.
     */
    public function createSnapToken(Booking $booking): string
    {
        $booking->load(['user', 'tourPackage']);

        $orderId = 'KDA-' . $booking->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $booking->total_price,
            ],
            'customer_details' => [
                'first_name' => $booking->guest_name ?? $booking->user->name,
                'email' => $booking->guest_email ?? $booking->user->email,
                'phone' => $booking->guest_phone ?? $booking->user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => $booking->tour_package_id,
                    'price' => (int) $booking->tourPackage->price,
                    'quantity' => $booking->guest_count,
                    'name' => substr($booking->tourPackage->name, 0, 50),
                ],
            ],
            'callbacks' => [
                'finish' => url('/booking/' . $booking->id),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        // Store or update payment record
        Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'midtrans_order_id' => $orderId,
                'snap_token' => $snapToken,
                'gross_amount' => $booking->total_price,
                'status' => 'pending',
            ]
        );

        return $snapToken;
    }

    /**
     * Verify webhook signature from Midtrans.
     */
    public function verifySignature(array $notification): bool
    {
        $signatureKey = hash('sha512',
            $notification['order_id'] .
            $notification['status_code'] .
            $notification['gross_amount'] .
            config('midtrans.server_key')
        );

        return $signatureKey === ($notification['signature_key'] ?? '');
    }

    /**
     * Handle notification from Midtrans webhook.
     */
    public function handleNotification(array $notification): void
    {
        $payment = Payment::where('midtrans_order_id', $notification['order_id'])->first();

        if (! $payment) {
            return;
        }

        $payment->update([
            'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
            'payment_type' => $notification['payment_type'] ?? null,
            'midtrans_response' => $notification,
        ]);

        $transactionStatus = $notification['transaction_status'] ?? '';

        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                // Idempotency: only fire post-payment side-effects (QR + email)
                // when this booking transitions INTO paid for the first time.
                $wasAlreadyPaid = in_array($payment->booking->status, ['paid', 'confirmed', 'completed']);

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
                $payment->update(['status' => $transactionStatus]);
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
        }
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
            Log::error('Failed to generate QR code for booking ' . $booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            Mail::to($booking->guest_email ?? $booking->user->email)
                ->queue(new BookingConfirmation($booking->fresh(), app()->getLocale()));
        } catch (\Throwable $e) {
            Log::error('Failed to queue booking confirmation email for ' . $booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
