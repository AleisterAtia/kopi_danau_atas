<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Without an explicit order, MySQL doesn't guarantee row order between
 * executions of the same query. The homepage's featured-packages query had
 * no orderBy at all, so the hero's rotating spotlight card (which always
 * starts at array index 0) could show a different package per request —
 * visibly, a different package on the Indonesian vs. English page load,
 * since a locale switch is a full navigation (a fresh query), not a
 * client-side re-render.
 */
class HomepageFeaturedPackagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_packages_are_returned_in_a_stable_deterministic_order(): void
    {
        $older = TourPackage::factory()->create([
            'name' => 'Paket Lama',
            'is_active' => true,
            'is_featured' => true,
            'created_at' => now()->subDays(2),
        ]);
        $newer = TourPackage::factory()->create([
            'name' => 'Paket Baru',
            'is_active' => true,
            'is_featured' => true,
            'created_at' => now()->subDay(),
        ]);

        $order = fn () => $this->get('/')->viewData('featuredPackages')->pluck('id')->all();

        // Same query, run twice — the order must not depend on MySQL's
        // unordered-scan whims, so both runs must agree with each other
        // and with the expected newest-first order.
        $this->assertSame([$newer->id, $older->id], $order());
        $this->assertSame([$newer->id, $older->id], $order());
    }
}
