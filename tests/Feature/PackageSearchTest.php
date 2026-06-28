<?php

namespace Tests\Feature;

use App\Models\PackageCategory;
use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public catalogue search & filter: keyword (name/description), category,
 * price range, sorting, and query-string preservation across pagination.
 */
class PackageSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Render Blade without depending on a built Vite manifest.
        $this->withoutVite();
    }

    public function test_keyword_search_returns_only_matching_packages(): void
    {
        TourPackage::factory()->create(['name' => 'Paket Petik Kopi Arabika', 'description' => 'Memetik ceri kopi.']);
        TourPackage::factory()->create(['name' => 'Workshop Roasting', 'description' => 'Belajar menyangrai.']);

        $response = $this->get(route('packages.index', ['q' => 'Petik']));

        $response->assertOk()
            ->assertSee('Paket Petik Kopi Arabika')
            ->assertDontSee('Workshop Roasting');
    }

    public function test_filter_by_category_returns_only_that_category(): void
    {
        $fotografi = PackageCategory::create(['name' => 'Fotografi']);
        $edukasi = PackageCategory::create(['name' => 'Edukasi']);

        TourPackage::factory()->create(['name' => 'Sunrise Photography', 'category_id' => $fotografi->id]);
        TourPackage::factory()->create(['name' => 'Roasting Experience', 'category_id' => $edukasi->id]);

        $response = $this->get(route('packages.index', ['category' => 'fotografi']));

        $response->assertOk()
            ->assertSee('Sunrise Photography')
            ->assertDontSee('Roasting Experience');
    }

    public function test_price_range_filter_excludes_out_of_range_packages(): void
    {
        TourPackage::factory()->create(['name' => 'Paket Murah', 'price' => 75_000]);
        TourPackage::factory()->create(['name' => 'Paket Mahal', 'price' => 300_000]);

        $response = $this->get(route('packages.index', ['price_min' => 100_000, 'price_max' => 250_000]));

        $response->assertOk()
            ->assertDontSee('Paket Murah')
            ->assertDontSee('Paket Mahal');

        $inRange = $this->get(route('packages.index', ['price_min' => 50_000, 'price_max' => 100_000]));
        $inRange->assertSee('Paket Murah')->assertDontSee('Paket Mahal');
    }

    public function test_filters_can_be_combined(): void
    {
        $edukasi = PackageCategory::create(['name' => 'Edukasi']);
        $fotografi = PackageCategory::create(['name' => 'Fotografi']);

        TourPackage::factory()->create(['name' => 'Edukasi Kopi Hemat', 'price' => 80_000, 'category_id' => $edukasi->id]);
        TourPackage::factory()->create(['name' => 'Edukasi Kopi Premium', 'price' => 400_000, 'category_id' => $edukasi->id]);
        TourPackage::factory()->create(['name' => 'Fotografi Kopi Hemat', 'price' => 80_000, 'category_id' => $fotografi->id]);

        $response = $this->get(route('packages.index', [
            'q' => 'Kopi',
            'category' => 'edukasi',
            'price_max' => 100_000,
        ]));

        $response->assertOk()
            ->assertSee('Edukasi Kopi Hemat')
            ->assertDontSee('Edukasi Kopi Premium')
            ->assertDontSee('Fotografi Kopi Hemat');
    }

    public function test_inactive_packages_are_never_listed(): void
    {
        TourPackage::factory()->create(['name' => 'Paket Aktif']);
        TourPackage::factory()->inactive()->create(['name' => 'Paket Nonaktif']);

        $this->get(route('packages.index'))
            ->assertOk()
            ->assertSee('Paket Aktif')
            ->assertDontSee('Paket Nonaktif');
    }

    public function test_pagination_preserves_the_query_string(): void
    {
        TourPackage::factory()->count(12)->create([
            'description' => 'Pengalaman kopi terbaik.',
        ]);
        // Ensure all 12 match the keyword regardless of the random factory name.
        TourPackage::query()->update(['description' => 'Pengalaman kopi terbaik.']);

        $response = $this->get(route('packages.index', ['q' => 'kopi']));

        $response->assertOk()
            ->assertSee('q=kopi')   // pagination links keep the filter
            ->assertSee('page=2');
    }
}
