<?php

namespace App\Services;

use App\Models\Booking;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
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

        // The `simplesoftwareio/simple-qrcode` PNG backend hard-requires the
        // `imagick` extension. When it's missing, fall back to rendering the
        // PNG ourselves from the raw QR matrix via GD (renderPngWithGd())
        // instead of emitting SVG: SVG is fine for the on-site <img> tag
        // (browsers render it natively), but most email clients — Gmail
        // included — refuse to render inline SVG at all, which left the
        // e-ticket QR broken in the booking confirmation email even though
        // it displayed fine on the booking detail page.
        try {
            $png = QrCode::format('png')
                ->size(400)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($payload);

            Storage::disk('public')->put($relativePath, $png);
        } catch (\Throwable $e) {
            try {
                Storage::disk('public')->put($relativePath, $this->renderPngWithGd($payload));
            } catch (\Throwable $e2) {
                // Neither Imagick nor GD available — last resort. Only
                // usable for the on-site <img> tag, not for emails.
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
        }

        return $relativePath;
    }

    /**
     * Render a QR PNG directly from the raw module matrix using GD.
     *
     * Bypasses bacon-qr-code's path-based Renderer (Module/Eye/ImageBackEnd)
     * entirely — each matrix cell is just painted as a filled square, so
     * there's no polygon winding-rule to get wrong (the finder-pattern
     * "eye" rings are naturally correct because their hole is simply
     * un-set matrix cells, not a shape with a hole punched out of it).
     * `ext-gd` ships enabled on virtually every PHP install, unlike
     * `ext-imagick`.
     */
    private function renderPngWithGd(string $payload): string
    {
        $matrix = Encoder::encode($payload, ErrorCorrectionLevel::H())->getMatrix();
        $moduleCount = $matrix->getWidth();

        $margin = 1;
        $moduleSize = max(1, intdiv(400, $moduleCount + $margin * 2));
        $imageSize = $moduleSize * ($moduleCount + $margin * 2);

        $image = imagecreatetruecolor($imageSize, $imageSize);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $imageSize, $imageSize, $white);

        for ($y = 0; $y < $moduleCount; $y++) {
            for ($x = 0; $x < $moduleCount; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $px = ($x + $margin) * $moduleSize;
                    $py = ($y + $margin) * $moduleSize;
                    imagefilledrectangle($image, $px, $py, $px + $moduleSize - 1, $py + $moduleSize - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return $png;
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
