<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves the overbooking protection in BookingController::store works:
 * quota is re-checked inside the locked transaction, so a booking cannot
 * be created once the daily capacity for a date is full.
 */
class BookingQuotaTest extends TestCase
{
    use RefreshDatabase;

    private array $guestDetails = [
        'guest_name' => 'Test Guest',
        'guest_phone' => '08123456789',
        'guest_email' => 'guest@example.com',
        'terms' => 'on',
    ];

    public function test_booking_is_rejected_when_daily_quota_is_full(): void
    {
        $package = TourPackage::factory()->create(['daily_capacity' => 2, 'price' => 100000]);
        $user = User::factory()->create();
        $visitDate = now()->addDays(5)->toDateString();

        // An existing paid booking already occupies the full quota (2 guests).
        Booking::factory()->paid()->create([
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 2,
        ]);

        $response = $this->actingAs($user)->post('/booking', array_merge($this->guestDetails, [
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 1,
        ]));

        $response->assertSessionHasErrors('guest_count');
        // Only the seeded booking exists; the rejected attempt created nothing.
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_booking_succeeds_when_quota_is_available(): void
    {
        $package = TourPackage::factory()->create(['daily_capacity' => 5, 'price' => 100000]);
        $user = User::factory()->create();
        $visitDate = now()->addDays(5)->toDateString();

        $response = $this->actingAs($user)->post('/booking', array_merge($this->guestDetails, [
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 2,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'tour_package_id' => $package->id,
            'guest_count' => 2,
            'status' => 'pending',
        ]);
        // Price is computed server-side: package price * guest count.
        $this->assertEquals(200000, (float) Booking::first()->total_price);
    }

    public function test_stale_pending_booking_does_not_occupy_quota(): void
    {
        $package = TourPackage::factory()->create(['daily_capacity' => 1]);
        $visitDate = now()->addDays(5)->toDateString();

        $stale = Booking::factory()->create([
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 1,
            'status' => 'pending',
        ]);

        // Backdate creation beyond the 1-hour reservation window so it is
        // treated as releasable (the scheduler will auto-expire it).
        Booking::where('id', $stale->id)->update(['created_at' => now()->subHours(2)]);

        $this->assertSame(1, $package->fresh()->getAvailableQuota($visitDate));
    }
}
