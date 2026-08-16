<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\SiteSetting;
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
     * Attach the rendered invoice PDF.
     *
     * The QR code is NOT listed here — it's embedded inline by
     * {{ $message->embed(...) }} in the markdown view, which attaches it
     * to the message itself and needs no help from this array. Adding it
     * here too used to double-attach the same QR as a second, redundant
     * downloadable file on top of the inline image.
     */
    public function attachments(): array
    {
        // Invoice PDF — rendered fresh so it always reflects the latest state.
        $pdf = Pdf::loadView('pdf.invoice', ['booking' => $this->booking])
            ->setPaper('a4', 'portrait')
            ->output();

        return [
            Attachment::fromData(fn () => $pdf, 'Invoice-'.$this->booking->booking_code.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
