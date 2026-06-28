<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PackageImage;
use App\Models\Review;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Performance regression guards. We render the homepage and the tour
 * package listing with a non-trivial dataset and assert that the
 * number of database queries stays under a budget. If the controller
 * starts producing N+1 queries again, these tests will fail loudly.
 *
 * Numbers are deliberately generous to avoid false failures on minor
 * non-regression changes; tighten them once the system is stable.
 */
class PerformanceQueryCountTest extends TestCase
{
    use RefreshDatabase;

    private function seedPackagesWithReviews(int $packageCount = 5, int $reviewsPerPackage = 4): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < $packageCount; $i++) {
            $package = TourPackage::create([
                'name' => "Package {$i}",
                'description' => 'A test package.',
                'price' => 250000,
                'duration_hours' => 4,
                'daily_capacity' => 20,
                'is_active' => true,
                'is_featured' => $i < 3,
            ]);

            // 1 image each
            PackageImage::create([
                'tour_package_id' => $package->id,
                'image_path' => "packages/{$i}.jpg",
                'sort_order' => 0,
            ]);

            for ($r = 0; $r < $reviewsPerPackage; $r++) {
                // A booking is required because reviews.booking_id is
                // NOT NULL with a foreign key constraint. Each review
                // gets its own completed booking.
                $booking = Booking::create([
                    'booking_code' => "TEST-{$package->id}-{$r}",
                    'user_id' => $user->id,
                    'tour_package_id' => $package->id,
                    'visit_date' => now()->subDays(30)->toDateString(),
                    'guest_count' => 1,
                    'total_price' => 250000,
                    'status' => 'completed',
                ]);

                Review::create([
                    'user_id' => $user->id,
                    'tour_package_id' => $package->id,
                    'booking_id' => $booking->id,
                    'rating' => ($r % 5) + 1,
                    'comment' => 'A reasonably long sample comment for testing.',
                    'status' => 'approved',
                ]);
            }
        }
    }

    public function test_homepage_query_count_stays_within_budget(): void
    {
        $this->seedPackagesWithReviews(packageCount: 5, reviewsPerPackage: 4);

        $count = 0;
        DB::listen(function () use (&$count): void {
            $count++;
        });

        $this->get('/')->assertOk();

        // Budget covers: homepage_sections cold-cache reads (4),
        // featured packages query + image eager load + count + avg
        // (~3), reviews query + relations (~2), site_settings (~1),
        // session/auth helpers, and a small slack.
        $this->assertLessThanOrEqual(
            18,
            $count,
            "Homepage executed {$count} DB queries; expected <= 18. ".
            'Did a controller change introduce N+1 behaviour?'
        );
    }

    public function test_packages_listing_query_count_stays_within_budget(): void
    {
        $this->seedPackagesWithReviews(packageCount: 9, reviewsPerPackage: 6);

        $count = 0;
        DB::listen(function () use (&$count): void {
            $count++;
        });

        $this->get('/paket-wisata')->assertOk();

        // Budget covers: paginated packages query, count for pagination,
        // images eager load, reviews_count + reviews_avg_rating, plus
        // a small slack for session/headers.
        $this->assertLessThanOrEqual(
            10,
            $count,
            "Tour packages listing executed {$count} DB queries; expected <= 10. ".
            'Did a controller change introduce N+1 behaviour on the package list?'
        );
    }
}
