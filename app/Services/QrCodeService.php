<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Generate and store QR code images for booking e-tickets.
 *
 * The QR encodes a check-in deep-link carrying the booking's unguessable
 * `ticket_token` (NOT the enumerable booking_code), so a forged or
 * guessed QR cannot be used to check in at the venue.
 */
class QrCodeService
{
    /**
     * Generate a PNG QR code for the given booking, save it to public disk,
     * and return the relative path (suitable for Storage::url()).
     *
     * Idempotent: if the file already exists, just returns the existing path.
     */
    public function generate(Booking $booking): string
    {
        // Older bookings created before the ticket_token column existed may
        // not have one yet — backfill lazily so their QR is still secure.
        if (empty($booking->ticket_token)) {
            $booking->forceFill(['ticket_token' => Booking::generateTicketToken()])->save();
        }

        $payload = $booking->ticketQrPayload();
        $relativePath = 'qrcodes/'.$booking->booking_code.'.png';

        if (Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        // PNG requires the `imagick` extension. Fall back to SVG when PNG
        // generation fails (no imagick) so the system still works on
        // bare-bones PHP installs.
        try {
            $png = QrCode::format('png')
                ->size(400)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($payload);

            Storage::disk('public')->put($relativePath, $png);
        } catch (\Throwable $e) {
            // Imagick not available — emit SVG instead.
            $relativePath = 'qrcodes/'.$booking->booking_code.'.svg';

            if (Storage::disk('public')->exists($relativePath)) {
                return $relativePath;
            }

            $svg = QrCode::format('svg')
                ->size(400)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($payload);

            Storage::disk('public')->put($relativePath, $svg);
        }

        return $relativePath;
    }

    /**
     * Get the absolute filesystem path of the QR file (for embedding in emails / PDFs).
     */
    public function absolutePath(Booking $booking): ?string
    {
        if (! $booking->qr_code_path) {
            return null;
        }

        $abs = Storage::disk('public')->path($booking->qr_code_path);

        return file_exists($abs) ? $abs : null;
    }
}
