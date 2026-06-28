<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Owner-scoped self-cancellation of bookings. Only the owner may cancel,
 * only `pending` (unpaid) bookings are cancellable, and cancelling frees
 * the daily quota again.
 */
class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_cancel_a_pending_booking_and_quota_is_freed(): void
    {
        $package = TourPackage::factory()->create(['daily_capacity' => 1]);
        $user = User::factory()->create();
        $visitDate = now()->addDays(5)->toDateString();

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 1,
            'status' => 'pending',
        ]);

        // Quota is fully occupied by the pending booking.
        $this->assertSame(0, $package->fresh()->getAvailableQuota($visitDate));

        $response = $this->actingAs($user)->post(route('booking.cancel', $booking));

        $response->assertRedirect(route('booking.index'));
        $this->assertSame('cancelled', $booking->fresh()->status);
        // Cancelling releases the seat back to the pool.
        $this->assertSame(1, $package->fresh()->getAvailableQuota($visitDate));
    }

    public function test_owner_cannot_cancel_a_paid_booking(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->paid()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('booking.cancel', $booking));

        $response->assertSessionHasErrors('status');
        $this->assertSame('paid', $booking->fresh()->status);
    }

    public function test_non_owner_cannot_cancel_a_booking(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($intruder)->post(route('booking.cancel', $booking));

        $response->assertStatus(403);
        $this->assertSame('pending', $booking->fresh()->status);
    }
}
