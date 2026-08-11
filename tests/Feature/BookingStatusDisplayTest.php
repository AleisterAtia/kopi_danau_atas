<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The booking list/detail pages hand-enumerate a badge per status instead
 * of deriving it generically, so a status the enumeration forgets renders
 * as a blank cell — which is exactly what happened for 'expired' bookings
 * (bookings:expire-pending sets this status, but the public pages only
 * ever knew about pending/paid/confirmed/completed/cancelled).
 */
class BookingStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_booking_shows_a_status_badge_on_the_list_page(): void
    {
        $user = User::factory()->create();
        Booking::factory()->create(['user_id' => $user->id, 'status' => 'expired']);

        $this->actingAs($user)
            ->get(route('booking.index'))
            ->assertOk()
            ->assertSee('Kadaluarsa');
    }

    public function test_expired_booking_shows_a_status_badge_on_the_detail_page(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $user->id, 'status' => 'expired']);

        $this->actingAs($user)
            ->get(route('booking.show', $booking))
            ->assertOk()
            ->assertSee('Kadaluarsa');
    }
}
