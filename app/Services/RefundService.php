<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Admin-initiated refunds.
 *
 * Midtrans' sandbox does not reliably expose a programmatic refund API, so
 * this records the refund decision in our own state (payment marked
 * `refund` with an audit note, booking moved to `cancelled` so quota is
 * freed) and relies on the operator completing the actual money movement
 * in the Midtrans dashboard. This keeps application state authoritative and
 * prevents it from silently drifting from the gateway — the gap the webhook
 * alone could not close, since it only ever *received* refund events.
 */
class RefundService
{
    /**
     * Statuses from which a refund is meaningful. `completed` is terminal
     * (the visit already happened) and is intentionally excluded.
     */
    private const REFUNDABLE_STATUSES = ['paid', 'confirmed'];

    public function refund(Booking $booking, ?string $note = null, ?User $admin = null): Booking
    {
        if (! in_array($booking->status, self::REFUNDABLE_STATUSES, true)) {
            throw new RuntimeException(
                "Pesanan berstatus '{$booking->status}' tidak dapat di-refund."
            );
        }

        DB::transaction(function () use ($booking, $note) {
            if ($payment = $booking->payment) {
                $payment->update([
                    'status' => 'refund',
                    'refunded_at' => now(),
                    'refund_note' => $note,
                ]);
            }

            // paid|confirmed -> cancelled is an allowed state-machine move;
            // the BookingObserver will reject anything illegal.
            $booking->update(['status' => 'cancelled']);
        });

        Log::info('Booking refunded by admin', [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'admin_id' => $admin?->id,
            'note' => $note,
        ]);

        return $booking->fresh('payment');
    }
}
