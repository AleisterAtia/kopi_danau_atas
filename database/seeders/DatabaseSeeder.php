<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CoffeeVariety;
use App\Models\HomepageSection;
use App\Models\SiteSetting;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ──────────────────────────────────────
        User::create([
            'name' => 'Admin KDA',
            'email' => 'admin@kopidanauatas.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // ── Sample Tourist User ─────────────────────────────
        User::create([
            'name' => 'Fadhil Dzaky',
            'email' => 'fadhil@example.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // ── Tour Packages ───────────────────────────────────
        TourPackage::create([
            'name' => 'Paket Petik Kopi',
            'description' => '<p>Nikmati pengalaman memetik buah kopi langsung dari kebun kopi Arabika Solok di dataran tinggi Danau Diatas. Anda akan dipandu oleh petani lokal yang berpengalaman untuk memilih cherry kopi merah yang matang sempurna.</p><p>Paket ini mencakup tur kebun selama 2 jam, sesi foto, dan secangkir kopi segar hasil petikan sendiri.</p>',
            'price' => 75000,
            'duration_hours' => 2,
            'daily_capacity' => 20,
            'facilities' => "Pemandu wisata lokal\nAlat petik kopi\nSecangkir kopi gratis\nSesi foto di kebun\nSouvenir biji kopi 100g",
            'is_active' => true,
            'is_featured' => true,
        ]);

        TourPackage::create([
            'name' => 'Paket Roasting Experience',
            'description' => '<p>Pelajari seni menyangrai biji kopi dari para ahli roaster kami. Mulai dari green bean hingga menjadi biji kopi sangrai yang sempurna, Anda akan memahami setiap tahapan proses roasting.</p><p>Setelah sesi roasting, Anda dapat mencicipi hasil roasting sendiri melalui sesi cupping profesional.</p>',
            'price' => 150000,
            'duration_hours' => 3,
            'daily_capacity' => 15,
            'facilities' => "Peralatan roasting lengkap\nBimbingan roaster profesional\nSesi cupping\nBiji kopi hasil roasting untuk dibawa pulang (200g)\nSertifikat keikutsertaan",
            'is_active' => true,
            'is_featured' => true,
        ]);

        TourPackage::create([
            'name' => 'Paket Full Day Coffee Tour',
            'description' => '<p>Paket lengkap yang mencakup seluruh rangkaian pengalaman agrowisata kopi dari pagi hingga sore. Dimulai dengan tur kebun, dilanjutkan dengan proses pemetikan, pengolahan basah, roasting, dan diakhiri dengan sesi cupping dan makan siang bersama.</p>',
            'price' => 250000,
            'duration_hours' => 6,
            'daily_capacity' => 10,
            'facilities' => "Tur kebun lengkap\nPetik kopi\nProses pengolahan basah (wet processing)\nSesi roasting\nSesi cupping profesional\nMakan siang tradisional\nBiji kopi oleh-oleh (250g)\nSertifikat",
            'is_active' => true,
            'is_featured' => true,
        ]);

        TourPackage::create([
            'name' => 'Paket Sunrise Photography',
            'description' => '<p>Paket khusus bagi pecinta fotografi yang ingin menangkap keindahan sunrise di atas Danau Diatas dengan latar kebun kopi. Dimulai dari pukul 05.00 pagi, Anda akan diantar ke spot terbaik untuk fotografi.</p>',
            'price' => 100000,
            'duration_hours' => 3,
            'daily_capacity' => 12,
            'facilities' => "Transportasi ke spot sunrise\nPemandu foto\nKopi pagi gratis\nSnack ringan",
            'is_active' => true,
            'is_featured' => false,
        ]);

        // ── Coffee Varieties ────────────────────────────────
        CoffeeVariety::create([
            'name' => 'Lini-S',
            'origin' => 'Alahan Panjang, Solok',
            'description' => '<p>Lini-S adalah varietas kopi Arabika unggulan yang dikembangkan khusus untuk dataran tinggi Sumatera Barat. Memiliki ketahanan yang baik terhadap penyakit karat daun dan menghasilkan produksi yang stabil.</p>',
            'flavor_profile' => 'Body tebal, keasaman medium, aroma cokelat dan rempah, aftertaste manis karamel',
            'is_active' => true,
        ]);

        CoffeeVariety::create([
            'name' => 'Gayo',
            'origin' => 'Aceh (ditanam di Alahan Panjang)',
            'description' => '<p>Kopi Arabika Gayo yang terkenal di seluruh dunia, kini juga dibudidayakan di kebun CV Kopi Danau Atas. Tumbuh optimal di ketinggian 1.400 mdpl dengan iklim sejuk Danau Diatas.</p>',
            'flavor_profile' => 'Keasaman seimbang, body medium-full, aroma herbal dan fruity, finish clean',
            'is_active' => true,
        ]);

        CoffeeVariety::create([
            'name' => 'Typica',
            'origin' => 'Ethiopia (ditanam di Alahan Panjang)',
            'description' => '<p>Typica merupakan salah satu varietas kopi Arabika tertua di dunia dan menjadi nenek moyang banyak varietas modern. Di kebun kami, Typica tumbuh dengan karakter rasa khas dataran tinggi Solok.</p>',
            'flavor_profile' => 'Body ringan-medium, keasaman tinggi, aroma floral dan citrus, aftertaste bersih dan manis',
            'is_active' => true,
        ]);

        // ── Blog Categories ─────────────────────────────────
        $catKopi = BlogCategory::create(['name' => 'Jenis Kopi']);
        $catWisata = BlogCategory::create(['name' => 'Tips Wisata']);
        $catBerita = BlogCategory::create(['name' => 'Berita']);

        // ── Sample Blog Posts ───────────────────────────────
        BlogPost::create([
            'title' => 'Mengenal Varietas Kopi Arabika Solok',
            'content' => '<p>Kopi Arabika Solok adalah salah satu jenis kopi premium yang tumbuh di dataran tinggi Sumatera Barat. Beberapa varietas unggulan yang kami budidayakan antara lain Lini-S, Gayo, dan Typica.</p><p>Setiap varietas memiliki karakteristik rasa yang unik, dipengaruhi oleh ketinggian lokasi tanam, jenis tanah vulkanik, dan proses pengolahan pasca panen yang kami terapkan.</p>',
            'category_id' => $catKopi->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        BlogPost::create([
            'title' => 'Tips Berkunjung ke Agrowisata Kopi Danau Atas',
            'content' => '<p>Berencana mengunjungi agrowisata kami? Berikut beberapa tips agar kunjungan Anda semakin menyenangkan:</p><ul><li>Kenakan pakaian dan sepatu yang nyaman untuk berjalan di kebun</li><li>Bawa kamera untuk mengabadikan momen indah</li><li>Datang pagi hari untuk menikmati udara segar dan pemandangan sunrise</li><li>Pesan tiket secara online melalui website untuk menghindari antrean</li></ul>',
            'category_id' => $catWisata->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // ── Homepage Sections ───────────────────────────────
        HomepageSection::create([
            'section_key' => 'hero',
            'title' => 'Jelajahi Keindahan Agrowisata Kopi Solok',
            'description' => 'Rasakan pengalaman unik memetik kopi langsung dari kebun Arabika di dataran tinggi Danau Diatas, Alahan Panjang.',
            'extra_data' => [
                'cta_text' => 'Pesan Sekarang',
                'cta_url' => '/paket-wisata',
            ],
            'sort_order' => 1,
        ]);

        HomepageSection::create([
            'section_key' => 'about',
            'title' => 'Tentang CV Kopi Danau Atas',
            'description' => '<p>CV Kopi Danau Atas adalah perusahaan pengolahan dan perdagangan kopi Arabika Solok yang berlokasi di kawasan strategis Danau Diatas, Alahan Panjang. Kami menawarkan pengalaman agrowisata yang memadukan keindahan alam dataran tinggi dengan edukasi proses produksi kopi dari hulu ke hilir.</p>',
            'extra_data' => [
                'cta_text' => 'Lihat Selengkapnya',
                'cta_url' => '/paket-wisata',
            ],
            'sort_order' => 2,
        ]);

        HomepageSection::create([
            'section_key' => 'education',
            'title' => 'Pengalaman Edukasi Kopi',
            'description' => 'Pelajari proses produksi kopi dari pohon hingga secangkir kopi yang nikmat. Kami menyediakan berbagai kegiatan edukasi yang interaktif dan menyenangkan.',
            'extra_data' => [
                'bullet_1' => 'Lebih dari 500 wisatawan puas dengan pengalaman edukasi kami',
                'bullet_2' => 'Dipandu langsung oleh petani kopi berpengalaman',
            ],
            'sort_order' => 3,
        ]);

        HomepageSection::create([
            'section_key' => 'testimonials',
            'title' => 'Apa Kata Pengunjung?',
            'description' => 'Dengarkan pengalaman dari mereka yang sudah berkunjung ke agrowisata kami.',
            'sort_order' => 4,
        ]);

        // ── Site Settings ───────────────────────────────────
        SiteSetting::setValue('company_name', 'CV Kopi Danau Atas');
        SiteSetting::setValue('company_address', 'Alahan Panjang, Kec. Lembah Gumanti, Kab. Solok, Sumatera Barat');
        SiteSetting::setValue('company_phone', '+62 812-3456-7890');
        SiteSetting::setValue('company_email', 'info@kopidanauatas.com');
        SiteSetting::setValue('company_whatsapp', '6281234567890');
        SiteSetting::setValue('google_maps_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.8!2d100.7!3d-1.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMcKwMTInMDAuMCJTIDEwMMKwNDInMDAuMCJF!5e0!3m2!1sid!2sid!4v1234567890');
        SiteSetting::setValue('instagram_url', 'https://instagram.com/kopidanauatas');
        SiteSetting::setValue('facebook_url', 'https://facebook.com/kopidanauatas');
        SiteSetting::setValue('tiktok_url', 'https://tiktok.com/@kopidanauatas');
    }
}
