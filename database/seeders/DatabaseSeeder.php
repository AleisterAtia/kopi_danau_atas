<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CoffeeVariety;
use App\Models\HomepageSection;
use App\Models\PackageCategory;
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

        // ── Package Categories ──────────────────────────────
        $catEdukasi = PackageCategory::create(['name' => ['id' => 'Edukasi', 'en' => 'Education']]);
        $catPetik = PackageCategory::create(['name' => ['id' => 'Petik & Olah', 'en' => 'Picking & Processing']]);
        $catFotografi = PackageCategory::create(['name' => ['id' => 'Fotografi', 'en' => 'Photography']]);
        $catLengkap = PackageCategory::create(['name' => ['id' => 'Paket Lengkap', 'en' => 'Complete Package']]);

        // ── Tour Packages ───────────────────────────────────
        TourPackage::create([
            'name' => [
                'id' => 'Paket Petik Kopi',
                'en' => 'Coffee Picking Package',
            ],
            'category_id' => $catPetik->id,
            'description' => [
                'id' => '<p>Nikmati pengalaman memetik buah kopi langsung dari kebun kopi Arabika Solok di dataran tinggi Danau Diatas. Anda akan dipandu oleh petani lokal yang berpengalaman untuk memilih cherry kopi merah yang matang sempurna.</p><p>Paket ini mencakup tur kebun selama 2 jam, sesi foto, dan secangkir kopi segar hasil petikan sendiri.</p>',
                'en' => '<p>Enjoy the experience of picking coffee cherries straight from the Solok Arabica coffee farm in the Lake Diatas highlands. You will be guided by experienced local farmers to choose perfectly ripe red coffee cherries.</p><p>This package includes a 2-hour farm tour, a photo session, and a fresh cup of self-picked coffee.</p>',
            ],
            'price' => 75000,
            'duration_hours' => 2,
            'daily_capacity' => 20,
            'facilities' => [
                'id' => "Pemandu wisata lokal\nAlat petik kopi\nSecangkir kopi gratis\nSesi foto di kebun\nSouvenir biji kopi 100g",
                'en' => "Local tour guide\nCoffee picking tools\nFree cup of coffee\nPhoto session in the farm\nCoffee bean souvenir 100g",
            ],
            'is_active' => true,
            'is_featured' => true,
        ]);

        TourPackage::create([
            'name' => [
                'id' => 'Paket Roasting Experience',
                'en' => 'Roasting Experience Package',
            ],
            'category_id' => $catEdukasi->id,
            'description' => [
                'id' => '<p>Pelajari seni menyangrai biji kopi dari para ahli roaster kami. Mulai dari green bean hingga menjadi biji kopi sangrai yang sempurna, Anda akan memahami setiap tahapan proses roasting.</p><p>Setelah sesi roasting, Anda dapat mencicipi hasil roasting sendiri melalui sesi cupping profesional.</p>',
                'en' => '<p>Learn the art of roasting coffee beans from our expert roasters. From green beans to perfectly roasted coffee beans, you will understand every stage of the roasting process.</p><p>After the roasting session, you can taste your own roasted coffee through a professional cupping session.</p>',
            ],
            'price' => 150000,
            'duration_hours' => 3,
            'daily_capacity' => 15,
            'facilities' => [
                'id' => "Peralatan roasting lengkap\nBimbingan roaster profesional\nSesi cupping\nBiji kopi hasil roasting untuk dibawa pulang (200g)\nSertifikat keikutsertaan",
                'en' => "Complete roasting equipment\nProfessional roaster guidance\nCupping session\nRoasted coffee beans to take home (200g)\nCertificate of participation",
            ],
            'is_active' => true,
            'is_featured' => true,
        ]);

        TourPackage::create([
            'name' => [
                'id' => 'Paket Full Day Coffee Tour',
                'en' => 'Full Day Coffee Tour Package',
            ],
            'category_id' => $catLengkap->id,
            'description' => [
                'id' => '<p>Paket lengkap yang mencakup seluruh rangkaian pengalaman agrowisata kopi dari pagi hingga sore. Dimulai dengan tur kebun, dilanjutkan dengan proses pemetikan, pengolahan basah, roasting, dan diakhiri dengan sesi cupping dan makan siang bersama.</p>',
                'en' => '<p>A complete package that includes the entire series of coffee agrotourism experiences from morning to afternoon. Starting with a farm tour, followed by the picking process, wet processing, roasting, and ending with a cupping session and lunch together.</p>',
            ],
            'price' => 250000,
            'duration_hours' => 6,
            'daily_capacity' => 10,
            'facilities' => [
                'id' => "Tur kebun lengkap\nPetik kopi\nProses pengolahan basah (wet processing)\nSesi roasting\nSesi cupping profesional\nMakan siang tradisional\nBiji kopi oleh-oleh (250g)\nSertifikat",
                'en' => "Complete farm tour\nCoffee picking\nWet processing\nRoasting session\nProfessional cupping session\nTraditional lunch\nCoffee bean souvenir (250g)\nCertificate",
            ],
            'is_active' => true,
            'is_featured' => true,
        ]);

        TourPackage::create([
            'name' => [
                'id' => 'Paket Sunrise Photography',
                'en' => 'Sunrise Photography Package',
            ],
            'category_id' => $catFotografi->id,
            'description' => [
                'id' => '<p>Paket khusus bagi pecinta fotografi yang ingin menangkap keindahan sunrise di atas Danau Diatas dengan latar kebun kopi. Dimulai dari pukul 05.00 pagi, Anda akan diantar ke spot terbaik untuk fotografi.</p>',
                'en' => '<p>A special package for photography lovers who want to capture the beauty of the sunrise over Lake Diatas against the backdrop of a coffee farm. Starting at 05:00 AM, you will be taken to the best spots for photography.</p>',
            ],
            'price' => 100000,
            'duration_hours' => 3,
            'daily_capacity' => 12,
            'facilities' => [
                'id' => "Transportasi ke spot sunrise\nPemandu foto\nKopi pagi gratis\nSnack ringan",
                'en' => "Transport to sunrise spot\nPhoto guide\nFree morning coffee\nLight snack",
            ],
            'is_active' => true,
            'is_featured' => false,
        ]);

        // ── Coffee Varieties ────────────────────────────────
        CoffeeVariety::create([
            'name' => 'Lini-S',
            'origin' => 'Danau Diatas, Kabupaten Solok',
            'description' => '<p>Lini-S adalah varietas kopi Arabika unggulan yang dikembangkan khusus untuk dataran tinggi Sumatera Barat. Memiliki ketahanan yang baik terhadap penyakit karat daun dan menghasilkan produksi yang stabil.</p>',
            'flavor_profile' => 'Body tebal, keasaman medium, aroma cokelat dan rempah, aftertaste manis karamel',
            'is_active' => true,
        ]);

        CoffeeVariety::create([
            'name' => 'Gayo',
            'origin' => 'Aceh (ditanam di Alahan Panjang)',
            'description' => '<p>Kopi Arabika Gayo yang terkenal di seluruh dunia, kini juga dibudidayakan di kebun CV Kopi Danau Diatas. Tumbuh optimal di ketinggian 1.400 mdpl dengan iklim sejuk Danau Diatas.</p>',
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
        $catKopi = BlogCategory::create(['name' => ['id' => 'Jenis Kopi', 'en' => 'Coffee Types']]);
        $catWisata = BlogCategory::create(['name' => ['id' => 'Tips Wisata', 'en' => 'Travel Tips']]);
        $catBerita = BlogCategory::create(['name' => ['id' => 'Berita', 'en' => 'News']]);

        // ── Sample Blog Posts ───────────────────────────────
        BlogPost::create([
            'title' => [
                'id' => 'Mengenal Varietas Kopi Arabika Solok',
                'en' => 'Getting to Know Solok Arabica Coffee Varieties',
            ],
            'content' => [
                'id' => '<p>Kopi Arabika Solok adalah salah satu jenis kopi premium yang tumbuh di dataran tinggi Sumatera Barat. Beberapa varietas unggulan yang kami budidayakan antara lain Lini-S, Gayo, dan Typica.</p><p>Setiap varietas memiliki karakteristik rasa yang unik, dipengaruhi oleh ketinggian lokasi tanam, jenis tanah vulkanik, dan proses pengolahan pasca panen yang kami terapkan.</p>',
                'en' => '<p>Solok Arabica coffee is one of the premium coffee types grown in the highlands of West Sumatra. Some of the superior varieties we cultivate include Lini-S, Gayo, and Typica.</p><p>Each variety has a unique flavor characteristic, influenced by the altitude of the planting location, the type of volcanic soil, and the post-harvest processing we apply.</p>',
            ],
            'category_id' => $catKopi->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        BlogPost::create([
            'title' => [
                'id' => 'Tips Berkunjung ke Agrowisata Kopi Danau Diatas',
                'en' => 'Tips for Visiting Kopi Danau Diatas Agrotourism',
            ],
            'content' => [
                'id' => '<p>Berencana mengunjungi agrowisata kami? Berikut beberapa tips agar kunjungan Anda semakin menyenangkan:</p><ul><li>Kenakan pakaian dan sepatu yang nyaman untuk berjalan di kebun</li><li>Bawa kamera untuk mengabadikan momen indah</li><li>Datang pagi hari untuk menikmati udara segar dan pemandangan sunrise</li><li>Pesan tiket secara online melalui website untuk menghindari antrean</li></ul>',
                'en' => '<p>Planning to visit our agrotourism? Here are some tips to make your visit more enjoyable:</p><ul><li>Wear comfortable clothes and shoes for walking in the farm</li><li>Bring a camera to capture beautiful moments</li><li>Come in the morning to enjoy the fresh air and sunrise view</li><li>Book tickets online through the website to avoid queues</li></ul>',
            ],
            'category_id' => $catWisata->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // ── Homepage Sections ───────────────────────────────
        HomepageSection::create([
            'section_key' => 'hero',
            'title' => [
                'id' => 'Jelajahi Keindahan Agrowisata Kopi Solok',
                'en' => 'Explore the Beauty of Solok Coffee Agrotourism',
            ],
            'description' => [
                'id' => 'Rasakan pengalaman unik memetik kopi langsung dari kebun Arabika di dataran tinggi Danau Diatas, Alahan Panjang.',
                'en' => 'Experience the unique sensation of picking coffee straight from the Arabica farm in the highlands of Lake Diatas, Alahan Panjang.',
            ],
            'extra_data' => [
                'cta_text' => 'Pesan Sekarang',
                'cta_url' => '/paket-wisata',
            ],
            'sort_order' => 1,
        ]);

        HomepageSection::create([
            'section_key' => 'about',
            'title' => [
                'id' => 'Tentang CV Kopi Danau Diatas',
                'en' => 'About CV Kopi Danau Diatas',
            ],
            'description' => [
                'id' => '<p>CV Kopi Danau Diatas adalah perusahaan pengolahan dan perdagangan kopi Arabika Solok yang berlokasi di kawasan strategis Danau Diatas, Alahan Panjang. Kami menawarkan pengalaman agrowisata yang memadukan keindahan alam dataran tinggi dengan edukasi proses produksi kopi dari hulu ke hilir.</p>',
                'en' => '<p>CV Kopi Danau Diatas is a Solok Arabica coffee processing and trading company located in the strategic area of Lake Diatas, Alahan Panjang. We offer an agrotourism experience combining the natural beauty of the highlands with education on the coffee production process from upstream to downstream.</p>',
            ],
            'extra_data' => [
                'cta_text' => 'Lihat Selengkapnya',
                'cta_url' => '/paket-wisata',
            ],
            'sort_order' => 2,
        ]);

        HomepageSection::create([
            'section_key' => 'education',
            'title' => [
                'id' => 'Pengalaman Edukasi Kopi',
                'en' => 'Coffee Education Experience',
            ],
            'description' => [
                'id' => 'Pelajari proses produksi kopi dari pohon hingga secangkir kopi yang nikmat. Kami menyediakan berbagai kegiatan edukasi yang interaktif dan menyenangkan.',
                'en' => 'Learn the coffee production process from the tree to a delicious cup of coffee. We provide various interactive and fun educational activities.',
            ],
            'extra_data' => [
                'bullet_1' => 'Lebih dari 500 wisatawan puas dengan pengalaman edukasi kami',
                'bullet_2' => 'Dipandu langsung oleh petani kopi berpengalaman',
            ],
            'sort_order' => 3,
        ]);

        HomepageSection::create([
            'section_key' => 'testimonials',
            'title' => [
                'id' => 'Apa Kata Pengunjung?',
                'en' => 'What Visitors Say?',
            ],
            'description' => [
                'id' => 'Dengarkan pengalaman dari mereka yang sudah berkunjung ke agrowisata kami.',
                'en' => 'Listen to the experiences of those who have visited our agrotourism.',
            ],
            'sort_order' => 4,
        ]);

        // ── Site Settings ───────────────────────────────────
        SiteSetting::setValue('company_name', 'CV Kopi Danau Diatas');
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
