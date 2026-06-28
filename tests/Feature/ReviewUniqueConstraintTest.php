<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "One review per booking" must be enforced at the database level, not only in
 * the controller (which is racy and bypassable from the admin panel).
 */
class ReviewUniqueConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_second_review_for_the_same_booking_is_rejected_by_the_database(): void
    {
        $user = User::factory()->create();
        $package = TourPackage::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'user_id' => $user->id,
            'tour_package_id' => $package->id,
        ]);

        Review::create([
            'user_id' => $user->id,
            'tour_package_id' => $package->id,
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Pengalaman luar biasa.',
            'status' => 'approved',
        ]);

        $this->expectException(QueryException::class);

        Review::create([
            'user_id' => $user->id,
            'tour_package_id' => $package->id,
            'booking_id' => $booking->id,
            'rating' => 3,
            'comment' => 'Mencoba menulis ulasan kedua.',
            'status' => 'approved',
        ]);
    }
}
