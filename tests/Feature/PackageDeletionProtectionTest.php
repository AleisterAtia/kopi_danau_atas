<?php

namespace Tests\Feature;

use App\Exceptions\PackageHasBookingsException;
use App\Models\Booking;
use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A tour package that still has bookings must not be hard-deletable — deleting
 * it would cascade-wipe the bookings and their payments (financial history).
 */
class PackageDeletionProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_package_with_bookings_is_blocked(): void
    {
        $package = TourPackage::factory()->create();
        Booking::factory()->create(['tour_package_id' => $package->id]);

        try {
            $package->delete();
            $this->fail('Expected PackageHasBookingsException was not thrown.');
        } catch (PackageHasBookingsException $e) {
            // expected
        }

        $this->assertDatabaseHas('tour_packages', ['id' => $package->id]);
        $this->assertSame(1, $package->bookings()->count());
    }

    public function test_a_package_without_bookings_can_be_deleted(): void
    {
        $package = TourPackage::factory()->create();

        $package->delete();

        $this->assertModelMissing($package);
    }
}
