<?php

namespace Tests\Feature;

use App\Models\CompanyValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageValuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_lists_company_values_ordered_by_sort_order(): void
    {
        CompanyValue::create([
            'icon' => 'star',
            'title' => ['id' => 'Nilai Kedua'],
            'description' => ['id' => 'Deskripsi kedua.'],
            'sort_order' => 2,
        ]);
        CompanyValue::create([
            'icon' => 'flag',
            'title' => ['id' => 'Nilai Pertama'],
            'description' => ['id' => 'Deskripsi pertama.'],
            'sort_order' => 1,
        ]);

        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSeeInOrder(['Nilai Pertama', 'Nilai Kedua']);
    }
}
