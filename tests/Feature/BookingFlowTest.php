<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end guards for the booking creation flow: the verified-email
 * gate, the Review Order quota pre-check, terms acceptance, and the
 * resulting pending booking with a generated booking code.
 */
class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $package = TourPackage::factory()->create();
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/booking/create?'.http_build_query([
            'tour_package_id' => $package->id,
            'visit_date' => now()->addDays(3)->toDateString(),
            'guest_count' => 1,
        ]));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_review_order_rejects_request_exceeding_quota(): void
    {
        $package = TourPackage::factory()->create(['daily_capacity' => 1]);
        $user = User::factory()->create();
        $visitDate = now()->addDays(4)->toDateString();

        Booking::factory()->paid()->create([
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 1,
        ]);

        $response = $this->actingAs($user)->get('/booking/create?'.http_build_query([
            'tour_package_id' => $package->id,
            'visit_date' => $visitDate,
            'guest_count' => 1,
        ]));

        $response->assertRedirect(route('packages.show', $package->slug));
        $response->assertSessionHasErrors('guest_count');
    }

    public function test_store_requires_terms_acceptance(): void
    {
        $package = TourPackage::factory()->create(['daily_capacity' => 10]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/booking', [
            'tour_package_id' => $package->id,
            'visit_date' => now()->addDays(4)->toDateString(),
            'guest_count' => 1,
            'guest_name' => 'Test Guest',
            'guest_phone' => '08123456789',
            'guest_email' => 'guest@example.com',
            // 'terms' intentionally omitted
        ]);

        $response->assertSessionHasErrors('terms');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_successful_booking_creates_pending_record_with_generated_code(): void
    {
        $package = TourPackage::factory()->create(['daily_capacity' => 10, 'price' => 120000]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/booking', [
            'tour_package_id' => $package->id,
            'visit_date' => now()->addDays(4)->toDateString(),
            'guest_count' => 1,
            'guest_name' => 'Test Guest',
            'guest_phone' => '08123456789',
            'guest_email' => 'guest@example.com',
            'terms' => 'on',
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertSame('pending', $booking->status);
        $this->assertStringStartsWith('KDA-', $booking->booking_code);
        $response->assertRedirect(route('payment.checkout', $booking));
    }
}
