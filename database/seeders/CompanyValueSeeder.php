<?php

namespace Database\Seeders;

use App\Models\CompanyValue;
use Illuminate\Database\Seeder;

class CompanyValueSeeder extends Seeder
{
    public function run(): void
    {
        CompanyValue::create([
            'icon' => 'flag',
            'title' => [
                'id' => 'Kualitas dari Ketinggian',
                'en' => 'Quality from the Highlands',
            ],
            'description' => [
                'id' => 'Arabika 1.400 mdpl, dipetik matang dan diolah dengan cermat.',
                'en' => 'Arabica at 1,400 masl, picked ripe and processed with care.',
            ],
            'sort_order' => 1,
        ]);

        CompanyValue::create([
            'icon' => 'book-open',
            'title' => [
                'id' => 'Edukasi Terbuka',
                'en' => 'Open Education',
            ],
            'description' => [
                'id' => 'Kami berbagi setiap tahap proses kopi kepada pengunjung.',
                'en' => 'We share every stage of the coffee process with visitors.',
            ],
            'sort_order' => 2,
        ]);

        CompanyValue::create([
            'icon' => 'user-group',
            'title' => [
                'id' => 'Kemitraan Petani',
                'en' => 'Farmer Partnership',
            ],
            'description' => [
                'id' => 'Tumbuh bersama masyarakat kebun, bukan di atas mereka.',
                'en' => 'Growing together with the farm community, not above them.',
            ],
            'sort_order' => 3,
        ]);

        CompanyValue::create([
            'icon' => 'globe-alt',
            'title' => [
                'id' => 'Keberlanjutan',
                'en' => 'Sustainability',
            ],
            'description' => [
                'id' => 'Menjaga lanskap Danau Diatas untuk generasi berikutnya.',
                'en' => 'Preserving the Lake Diatas landscape for future generations.',
            ],
            'sort_order' => 4,
        ]);
    }
}
