<?php

namespace Tests\Unit;

use App\Models\TourPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HasAutoTranslationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'fake-key']);
    }

    private function geminiResponse(array $translated): array
    {
        return [
            'candidates' => [[
                'content' => ['parts' => [['text' => json_encode($translated)]]],
            ]],
        ];
    }

    public function test_stale_english_translation_is_refreshed_when_indonesian_source_changes(): void
    {
        // Http::fake() stacks stubs rather than replacing them, so both
        // responses this test needs must be queued upfront via one sequence.
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push($this->geminiResponse(['name' => 'Initial Name (EN)', 'description' => 'Initial Desc (EN)']))
                ->push($this->geminiResponse(['name' => 'Updated Name (EN)'])),
        ]);

        $package = TourPackage::factory()->create(['name' => 'Nama Awal']);

        $this->assertSame('Initial Name (EN)', $package->getTranslation('name', 'en', false));

        // Admin edits the Indonesian source only — English was already filled,
        // so before the fix this would never be re-translated and would go stale.
        $package->setTranslation('name', 'id', 'Nama Baru');
        $package->save();

        $this->assertSame('Updated Name (EN)', $package->getTranslation('name', 'en', false));
    }

    public function test_manually_edited_english_translation_is_not_overwritten_when_source_is_untouched(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(
                $this->geminiResponse(['name' => 'Initial Name (EN)', 'description' => 'Initial Desc (EN)'])
            ),
        ]);

        $package = TourPackage::factory()->create(['name' => 'Nama Awal']);

        // Admin manually corrects the English tab in Filament, Indonesian untouched.
        Http::fake(); // reset the recorded-request log; no stub should even be hit below.
        $package->setTranslation('name', 'en', 'Manually Fixed Name');
        $package->save();

        $this->assertSame('Manually Fixed Name', $package->getTranslation('name', 'en', false));
        Http::assertNothingSent();
    }
}
