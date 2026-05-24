<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    /**
     * Show the "Review Order" page where the user fills in their details
     * before being sent to checkout. This is the new step between the
     * package detail page and the actual booking creation.
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'visit_date' => 'required|date|after:today',
            'guest_count' => 'required|integer|min:1',
        ]);

        $package = TourPackage::with('images')->findOrFail($data['tour_package_id']);
        $available = $package->getAvailableQuota($data['visit_date']);

        if ($data['guest_count'] > $available) {
            return redirect()
                ->route('packages.show', $package->slug)
                ->withErrors(['guest_count' => __('Kuota tidak mencukupi. Sisa kuota: ') . $available]);
        }

        return view('pages.booking.create', [
            'package' => $package,
            'visitDate' => $data['visit_date'],
            'guestCount' => (int) $data['guest_count'],
            'totalPrice' => (float) $package->price * (int) $data['guest_count'],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'visit_date' => 'required|date|after:today',
            'guest_count' => 'required|integer|min:1',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:30',
            'guest_email' => 'required|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'accepted',
        ], [
            'terms.accepted' => __('Anda harus menyetujui syarat dan ketentuan untuk melanjutkan.'),
        ]);

        // Lock the package row + recheck quota inside a transaction to avoid
        // race conditions where two users book the last seats simultaneously.
        // We throw ValidationException so the framework rolls back the
        // transaction cleanly and the caller gets a normal flash-back response
        // instead of an `abort(redirect)` short-circuit (which loses session
        // flashes when the transaction rolls back).
        $booking = DB::transaction(function () use ($request) {
            $package = TourPackage::lockForUpdate()->findOrFail($request->tour_package_id);

            $available = $package->getAvailableQuota($request->visit_date);
            if ($request->guest_count > $available) {
                throw ValidationException::withMessages([
                    'guest_count' => __('Kuota tidak mencukupi. Sisa kuota: ') . $available,
                ]);
            }

            return Booking::create([
                'booking_code' => Booking::generateBookingCode($request->visit_date),
                'user_id' => auth()->id(),
                'tour_package_id' => $package->id,
                'visit_date' => $request->visit_date,
                'guest_count' => $request->guest_count,
                'guest_name' => $request->guest_name,
                'guest_phone' => $request->guest_phone,
                'guest_email' => $request->guest_email,
                'notes' => $request->notes,
                'total_price' => $package->price * $request->guest_count,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('payment.checkout', $booking)
            ->with('success', __('Booking berhasil dibuat! Silakan lakukan pembayaran.'));
    }

    public function show(Booking $booking)
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        $booking->load(['tourPackage.images', 'payment', 'review']);
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
