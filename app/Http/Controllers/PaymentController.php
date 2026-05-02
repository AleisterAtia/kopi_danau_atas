<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\MidtransService;
use Illuminate\Http\Request;

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
}
