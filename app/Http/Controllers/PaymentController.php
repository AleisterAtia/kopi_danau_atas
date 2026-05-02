<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function checkout(Booking $booking)
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        abort_if(!in_array($booking->status, ['pending']), 404);

        $booking->load(['tourPackage', 'payment']);

        return view('pages.booking.checkout', compact('booking'));
    }

    public function createSnapToken(Booking $booking, MidtransService $midtrans)
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        $snapToken = $midtrans->createSnapToken($booking);

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

        if (!$payment || !$payment->midtrans_order_id) {
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
            return back()->with('error', 'Gagal mengecek status: ' . $e->getMessage());
        }
    }
}
