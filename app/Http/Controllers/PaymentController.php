<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function checkout(Booking $booking)
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        // Only pending bookings can be paid. For anything else (paid, cancelled,
        // expired…) send the user back to the booking detail with a message
        // instead of a dead-end 404 — matters now that the browser Back button
        // re-requests this URL after a successful payment.
        if ($booking->status !== 'pending') {
            return redirect()
                ->route('booking.show', $booking)
                ->with('success', __('Pesanan ini tidak lagi menunggu pembayaran.'));
        }

        $booking->load(['tourPackage', 'payment']);

        return view('pages.booking.checkout', compact('booking'));
    }

    public function createSnapToken(Booking $booking, MidtransService $midtrans)
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        try {
            $snapToken = $midtrans->createSnapToken($booking);
        } catch (\RuntimeException $e) {
            // Booking already paid, or other guard rejection.
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Throwable $e) {
            // Was previously swallowed — the frontend only shows a generic
            // "gagal mendapatkan token" alert, so without this the real
            // Midtrans/API error (bad order_id reuse, expired keys, etc.)
            // left no trace anywhere.
            Log::error('Midtrans Snap token creation failed', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'exception' => get_class($e).': '.$e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token pembayaran: '.$e->getMessage(),
            ], 500);
        }

        return response()->json(['snap_token' => $snapToken]);
    }

    /**
     * Called from client-side after Midtrans Snap reports success/pending.
     * Checks the real status with Midtrans API and updates the local DB.
     */
    public function updateStatus(Booking $booking, MidtransService $midtrans)
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        $payment = Payment::where('booking_id', $booking->id)->latest()->first();

        if (! $payment || ! $payment->midtrans_order_id) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        try {
            $status = Transaction::status($payment->midtrans_order_id);
            $notification = (array) $status;
            $midtrans->handleNotification($notification);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'status' => $notification['transaction_status'] ?? 'unknown']);
            }

            return redirect()->route('booking.show', $booking)->with('success', 'Status pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal mengecek status: '.$e->getMessage());
        }
    }
}
