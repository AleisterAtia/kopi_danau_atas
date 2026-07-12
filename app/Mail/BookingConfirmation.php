<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\SiteSetting;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent automatically after a booking has been paid.
 *
 * Includes:
 *  - Inline QR code (embedded as cid: image)
 *  - PDF invoice (rendered on the fly via dompdf) as attachment
 *  - Bilingual content via locale stored on the model
 */
class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The locale to render the email in (e.g. 'id' or 'en').
     */
    public string $emailLocale;

    public function __construct(public Booking $booking, ?string $locale = null)
    {
        // Capture caller's locale at queue time so the queued job renders
        // in the same language even if APP_LOCALE differs.
        $this->emailLocale = $locale ?? app()->getLocale();
        $this->locale($this->emailLocale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address(
                    $this->booking->guest_email ?? $this->booking->user->email,
                    $this->booking->guest_name ?? $this->booking->user->name
                ),
            ],
            subject: __('mailer.booking_confirmation_subject', [
                'code' => $this->booking->booking_code,
            ]),
        );
    }

    public function content(): Content
    {
        // Pre-load relations the view needs.
        $this->booking->loadMissing(['tourPackage', 'user', 'payment']);

        $settings = [
            'company_name' => SiteSetting::getValue('company_name', 'CV Kopi Danau Diatas'),
            'company_phone' => SiteSetting::getValue('company_phone'),
            'company_whatsapp' => SiteSetting::getValue('company_whatsapp'),
            'company_address' => SiteSetting::getValue('company_address'),
        ];

        return new Content(
            markdown: 'emails.booking.confirmation',
            with: [
                'booking' => $this->booking,
                'settings' => $settings,
            ],
        );
    }

    /**
     * Attach: QR PNG (referenced inline via cid:qr-code in the markdown view)
     * and the rendered invoice PDF.
     */
    public function attachments(): array
    {
        $attachments = [];

        // Invoice PDF — rendered fresh so it always reflects the latest state.
        $pdf = Pdf::loadView('pdf.invoice', ['booking' => $this->booking])
            ->setPaper('a4', 'portrait')
            ->output();

        $attachments[] = Attachment::fromData(fn () => $pdf, 'Invoice-'.$this->booking->booking_code.'.pdf')
            ->withMime('application/pdf');

        // QR code — attached inline so the email body can reference it via
        // {{ $message->embed(...) }} or our pre-resolved cid.
        $qrPath = app(QrCodeService::class)->absolutePath($this->booking);
        if ($qrPath) {
            $attachments[] = Attachment::fromPath($qrPath)
                ->as('eticket-qr-'.$this->booking->booking_code.pathinfo($qrPath, PATHINFO_EXTENSION))
                ->withMime(str_ends_with($qrPath, '.svg') ? 'image/svg+xml' : 'image/png');
        }

        return $attachments;
    }
}
