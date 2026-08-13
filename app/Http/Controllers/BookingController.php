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
        // Eager-load tourPackage.images so each card thumbnail does not
        // trigger an extra query per booking. Also eager-load payment
        // (1:1) and review so action buttons can be rendered without
        // additional queries.
        $bookings = Booking::where('user_id', auth()->id())
            ->with(['tourPackage.images', 'payment', 'review'])
            ->latest()
            ->paginate(10);

        return view('pages.booking.index', compact('bookings'));
    }

    // Background polling target for the "Pesanan Saya" page: returns just the
    // table rows so status changes made by an admin show up without a full
    // page reload (mirrors how Filament's own polling works).
    public function pollRows()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with(['tourPackage.images', 'payment', 'review'])
            ->latest()
            ->paginate(10);

        return view('pages.booking._rows', compact('bookings'));
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
                ->withErrors(['guest_count' => __('Kuota tidak mencukupi. Sisa kuota: ').$available]);
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
                    'guest_count' => __('Kuota tidak mencukupi. Sisa kuota: ').$available,
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

    /**
     * Cancel a booking on the owner's request.
     *
     * Per the T&C only an unpaid (`pending`) booking may be self-cancelled;
     * paid bookings must go through admin refund. Ownership is enforced
     * (non-owners get 403) and the actual status change is validated by the
     * BookingObserver state-machine. Cancelling frees the quota automatically
     * because `cancelled` bookings are never counted by getAvailableQuota().
     */
    public function cancel(Booking $booking)
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        if ($booking->status !== 'pending') {
            return back()->withErrors([
                'status' => __('Hanya pesanan yang belum dibayar yang dapat dibatalkan.'),
            ]);
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('booking.index')
            ->with('success', __('Pesanan berhasil dibatalkan.'));
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
