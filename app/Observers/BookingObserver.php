<?php

namespace App\Observers;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingObserver
{
    /**
     * Enforce the status state-machine declared on the Booking model.
     *
     * - Allows the initial save (no original status yet).
     * - Allows same-state saves (no transition).
     * - Otherwise checks Booking::canTransitionTo(); rejects with
     *   ValidationException so admin forms surface a friendly error.
     *
     * Also emits a structured log entry on every successful transition
     * to make payment / status debugging straightforward.
     */
    public function updating(Booking $booking): void
    {
        if (! $booking->isDirty('status')) {
            return;
        }

        $from = $booking->getOriginal('status');
        $to = $booking->status;

        if (! Booking::canTransitionTo($from, $to)) {
            throw ValidationException::withMessages([
                'status' => "Tidak bisa mengubah status booking dari '{$from}' menjadi '{$to}'.",
            ]);
        }
    }

    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status')) {
            Log::info('Booking status changed', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'from' => $booking->getOriginal('status'),
                'to' => $booking->status,
            ]);
        }
    }
}
