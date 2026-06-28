<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Validates e-ticket QR tokens and performs on-site check-in.
 *
 * The check-in write is atomic: a conditional UPDATE ... WHERE
 * checked_in_at IS NULL guarantees that two staff scanning the same
 * ticket at the same moment can never both succeed — the second scan is
 * reported as "already checked in" rather than silently double-marking.
 */
class TicketCheckInService
{
    public const RESULT_SUCCESS = 'success';

    public const RESULT_ALREADY = 'already_checked_in';

    public const RESULT_NOT_FOUND = 'not_found';

    public const RESULT_NOT_ELIGIBLE = 'not_eligible';

    /**
     * Look up a booking by its ticket token without mutating anything —
     * used to preview booking details before the staff confirms check-in.
     */
    public function lookup(?string $token): ?Booking
    {
        $token = trim((string) $token);

        if ($token === '') {
            return null;
        }

        return Booking::with(['tourPackage', 'user', 'checkedInBy'])
            ->where('ticket_token', $token)
            ->first();
    }

    /**
     * Attempt to check a ticket in.
     *
     * @return array{result: string, booking: ?Booking}
     */
    public function checkIn(?string $token, ?User $staff = null): array
    {
        $booking = $this->lookup($token);

        if (! $booking) {
            return ['result' => self::RESULT_NOT_FOUND, 'booking' => null];
        }

        if (! $booking->isCheckInEligible()) {
            return ['result' => self::RESULT_NOT_ELIGIBLE, 'booking' => $booking];
        }

        if ($booking->isCheckedIn()) {
            return ['result' => self::RESULT_ALREADY, 'booking' => $booking];
        }

        // Atomic claim: only the scan that flips checked_in_at from NULL wins.
        $claimed = Booking::where('id', $booking->id)
            ->whereNull('checked_in_at')
            ->update([
                'checked_in_at' => now(),
                'checked_in_by' => $staff?->id,
            ]);

        $booking = $booking->fresh(['tourPackage', 'user', 'checkedInBy']);

        if ($claimed === 0) {
            // A concurrent scan beat us to it between read and write.
            return ['result' => self::RESULT_ALREADY, 'booking' => $booking];
        }

        Log::info('Ticket checked in', [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'staff_id' => $staff?->id,
        ]);

        return ['result' => self::RESULT_SUCCESS, 'booking' => $booking];
    }
}
