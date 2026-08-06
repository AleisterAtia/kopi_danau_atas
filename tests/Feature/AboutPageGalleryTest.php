<?php

namespace Tests\Feature;

use App\Models\HomepageImage;
use App\Models\HomepageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_beyond_one_page_renders_as_multiple_rotating_chunks(): void
    {
        $section = HomepageSection::create(['section_key' => 'about', 'sort_order' => 1]);

        for ($i = 1; $i <= 10; $i++) {
            HomepageImage::create([
                'homepage_section_id' => $section->id,
                'image_path' => "homepage/photo-{$i}.jpg",
                'sort_order' => $i,
            ]);
        }

        $response = $this->get(route('about'));

        $response->assertOk();
        // 10 images chunked 8-per-page => 2 rotating grid blocks.
        $response->assertSeeInOrder(['ab-gal__grid', 'ab-gal__grid']);
        $response->assertSee('photo-1.jpg', false);
        $response->assertSee('photo-10.jpg', false);
    }

    public function test_gallery_with_a_single_page_does_not_render_rotation_interval(): void
    {
        $section = HomepageSection::create(['section_key' => 'about', 'sort_order' => 1]);
        HomepageImage::create([
            'homepage_section_id' => $section->id,
            'image_path' => 'homepage/photo-1.jpg',
            'sort_order' => 1,
        ]);

        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertDontSee('setInterval', false);
    }
}
