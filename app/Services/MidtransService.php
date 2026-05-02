<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
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
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone ?? '',
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
                $payment->update(['status' => 'settlement', 'paid_at' => now()]);
                $payment->booking->update(['status' => 'paid']);
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
}
