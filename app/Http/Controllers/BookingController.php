<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TourPackage;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function myBookings()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with(['tourPackage', 'payment'])
            ->latest()
            ->paginate(10);

        return view('pages.booking.index', compact('bookings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'visit_date' => 'required|date|after:today',
            'guest_count' => 'required|integer|min:1',
        ]);

        $package = TourPackage::findOrFail($request->tour_package_id);
        $available = $package->getAvailableQuota($request->visit_date);

        if ($request->guest_count > $available) {
            return back()->withErrors(['guest_count' => __('Kuota tidak mencukupi. Sisa kuota: ') . $available]);
        }

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode($request->visit_date),
            'user_id' => auth()->id(),
            'tour_package_id' => $package->id,
            'visit_date' => $request->visit_date,
            'guest_count' => $request->guest_count,
            'total_price' => $package->price * $request->guest_count,
            'status' => 'pending',
        ]);

        return redirect()->route('payment.checkout', $booking)
            ->with('success', __('Booking berhasil dibuat! Silakan lakukan pembayaran.'));
    }

    public function show(Booking $booking)
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        $booking->load(['tourPackage', 'payment', 'review']);
        return view('pages.booking.show', compact('booking'));
    }

    public function checkQuota(int $packageId, string $date)
    {
        $package = TourPackage::findOrFail($packageId);
        return response()->json([
            'available' => $package->getAvailableQuota($date),
            'capacity' => $package->daily_capacity,
        ]);
    }
}
