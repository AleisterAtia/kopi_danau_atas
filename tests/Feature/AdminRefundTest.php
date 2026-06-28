<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin-initiated refund: marks the payment refunded (with an audit note),
 * cancels the booking so quota is freed, and refuses to refund bookings
 * that are not in a refundable state.
 */
class AdminRefundTest extends TestCase
{
    use RefreshDatabase;

    private function service(): RefundService
    {
        return app(RefundService::class);
    }

    public function test_refunding_a_paid_booking_cancels_it_and_records_audit(): void
    {
        $admin = User::factory()->admin()->create();
        $package = TourPackage::factory()->create(['daily_capacity' => 1]);
        $visitDate = now()->addDays(6)->toDateString();

        $booking = Booking::factory()->paid()->create([
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 1,
        ]);
        $payment = Payment::factory()->settled()->create([
            'booking_id' => $booking->id,
            'gross_amount' => $booking->total_price,
        ]);

        $this->assertSame(0, $package->fresh()->getAvailableQuota($visitDate));

        $this->service()->refund($booking, 'Pelanggan membatalkan H-3', $admin);

        $this->assertSame('cancelled', $booking->fresh()->status);

        $payment->refresh();
        $this->assertSame('refund', $payment->status);
        $this->assertNotNull($payment->refunded_at);
        $this->assertSame('Pelanggan membatalkan H-3', $payment->refund_note);

        // Refund releases the seat back to the pool.
        $this->assertSame(1, $package->fresh()->getAvailableQuota($visitDate));
    }

    public function test_pending_booking_cannot_be_refunded(): void
    {
        $booking = Booking::factory()->create(['status' => 'pending']);

        $this->expectException(\RuntimeException::class);

        $this->service()->refund($booking, 'invalid');
    }

    public function test_completed_booking_cannot_be_refunded(): void
    {
        $booking = Booking::factory()->completed()->create();

        $this->expectException(\RuntimeException::class);

        $this->service()->refund($booking, 'too late');
    }
}
