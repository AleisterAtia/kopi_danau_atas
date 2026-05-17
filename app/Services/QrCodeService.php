<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Generate and store QR code images for booking e-tickets.
 *
 * The QR encodes the human-readable booking_code (e.g. KDA-20260520-00001),
 * which staff at the venue can scan to look up the booking quickly.
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
        $relativePath = 'qrcodes/' . $booking->booking_code . '.png';

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
                ->generate($booking->booking_code);

            Storage::disk('public')->put($relativePath, $png);
        } catch (\Throwable $e) {
            // Imagick not available — emit SVG instead.
            $relativePath = 'qrcodes/' . $booking->booking_code . '.svg';

            if (Storage::disk('public')->exists($relativePath)) {
                return $relativePath;
            }

            $svg = QrCode::format('svg')
                ->size(400)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($booking->booking_code);

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
