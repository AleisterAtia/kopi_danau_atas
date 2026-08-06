<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_active_packages_and_static_pages(): void
    {
        $active = TourPackage::factory()->create(['is_active' => true]);
        $inactive = TourPackage::factory()->create(['is_active' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(route('packages.show', $active->slug), false);
        $response->assertSee(route('home'), false);
        $response->assertDontSee(route('packages.show', $inactive->slug), false);
    }
}
