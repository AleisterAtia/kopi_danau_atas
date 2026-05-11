<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function download(Booking $booking)
    {
        // Only the owner can download their invoice
        abort_if($booking->user_id !== auth()->id(), 403);

        // Only allow download for paid/confirmed/completed bookings
        abort_unless(in_array($booking->status, ['paid', 'confirmed', 'completed']), 403, 'Invoice hanya tersedia untuk pesanan yang sudah lunas.');

        $booking->load(['tourPackage', 'payment', 'user']);

        $pdf = Pdf::loadView('pdf.invoice', compact('booking'))
            ->setPaper('a4', 'portrait');

        $filename = 'Invoice-' . $booking->booking_code . '.pdf';

        return $pdf->download($filename);
    }
}
