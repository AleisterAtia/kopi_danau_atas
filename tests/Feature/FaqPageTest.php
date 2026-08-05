<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_lists_faqs_ordered_by_sort_order(): void
    {
        Faq::create(['question' => ['id' => 'Pertanyaan Kedua'], 'answer' => ['id' => 'Jawaban kedua.'], 'sort_order' => 2]);
        Faq::create(['question' => ['id' => 'Pertanyaan Pertama'], 'answer' => ['id' => 'Jawaban pertama.'], 'sort_order' => 1]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeInOrder(['Pertanyaan Pertama', 'Pertanyaan Kedua']);
    }

    public function test_package_detail_page_shows_cancellation_policy_block_linking_to_homepage_faq(): void
    {
        $package = TourPackage::factory()->create(['is_active' => true]);

        // Pin the locale: the assertion below checks the Indonesian source
        // string via __(), which the app's default locale renders as-is,
        // but CI's .env.example defaults to APP_LOCALE=en (unlike this
        // project's real .env, which is `id`) and would otherwise translate
        // it to "Cancellation Policy" and fail the assertion.
        $response = $this->withSession(['locale' => 'id'])
            ->get(route('packages.show', $package->slug));

        $response->assertOk();
        $response->assertSee('Kebijakan Pembatalan');
        $response->assertSee(route('home').'#pembatalan', false);
    }
}
