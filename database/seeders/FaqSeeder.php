<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        Faq::create([
            'question' => [
                'id' => 'Bagaimana cara memesan paket wisata?',
                'en' => 'How do I book a tour package?',
            ],
            'answer' => [
                'id' => '<p>Pilih paket wisata yang Anda inginkan, tentukan tanggal kunjungan dan jumlah tamu, lalu lakukan pembayaran melalui Midtrans. Setelah pembayaran berhasil, e-tiket berisi kode QR akan otomatis dikirim ke email Anda.</p>',
                'en' => '<p>Choose the tour package you want, set your visit date and number of guests, then pay via Midtrans. Once payment succeeds, an e-ticket with a QR code will automatically be sent to your email.</p>',
            ],
            'sort_order' => 1,
        ]);

        Faq::create([
            'question' => [
                'id' => 'Metode pembayaran apa saja yang tersedia?',
                'en' => 'What payment methods are available?',
            ],
            'answer' => [
                'id' => '<p>Pembayaran diproses melalui Midtrans dan mendukung berbagai metode, termasuk transfer bank, kartu kredit/debit, GoPay, QRIS, dan e-wallet lainnya.</p>',
                'en' => '<p>Payments are processed via Midtrans and support various methods, including bank transfer, credit/debit card, GoPay, QRIS, and other e-wallets.</p>',
            ],
            'sort_order' => 2,
        ]);

        Faq::create([
            'question' => [
                'id' => 'Bagaimana kebijakan pembatalan dan refund?',
                'en' => 'What is the cancellation and refund policy?',
            ],
            'answer' => [
                'id' => '<p>Pesanan yang <strong>belum dibayar</strong> dapat Anda batalkan sendiri kapan saja tanpa biaya melalui halaman "Pesanan Saya". Untuk pesanan yang <strong>sudah dibayar</strong>, silakan hubungi admin kami untuk mengajukan pembatalan — status pesanan akan diperbarui oleh admin dan dana dikembalikan secara manual setelah dikonfirmasi.</p>',
                'en' => '<p>Orders that are <strong>not yet paid</strong> can be cancelled by yourself at any time free of charge via the "My Orders" page. For orders that are <strong>already paid</strong>, please contact our admin to request a cancellation — the order status will be updated by the admin and funds refunded manually after confirmation.</p>',
            ],
            'sort_order' => 3,
        ]);

        Faq::create([
            'question' => [
                'id' => 'Apakah ada batasan jumlah pengunjung per hari?',
                'en' => 'Is there a limit on the number of visitors per day?',
            ],
            'answer' => [
                'id' => '<p>Ya, setiap paket wisata memiliki kuota harian tersendiri. Jika kuota untuk tanggal yang Anda pilih sudah penuh, sistem akan menampilkan tanggal tersebut sebagai tidak tersedia — silakan pilih tanggal lain.</p>',
                'en' => '<p>Yes, each tour package has its own daily quota. If the quota for your chosen date is full, the system will show that date as unavailable — please choose another date.</p>',
            ],
            'sort_order' => 4,
        ]);

        Faq::create([
            'question' => [
                'id' => 'Bagaimana cara menggunakan e-tiket saat kunjungan?',
                'en' => 'How do I use my e-ticket during the visit?',
            ],
            'answer' => [
                'id' => '<p>Setelah pembayaran berhasil, Anda akan menerima e-tiket berisi kode QR melalui email dan dapat mengunduh invoice PDF-nya. Tunjukkan kode QR tersebut kepada petugas kami saat tiba di lokasi untuk verifikasi kunjungan.</p>',
                'en' => '<p>After successful payment, you will receive an e-ticket with a QR code via email and can download the PDF invoice. Show this QR code to our staff upon arrival at the location for visit verification.</p>',
            ],
            'sort_order' => 5,
        ]);

        Faq::create([
            'question' => [
                'id' => 'Kapan jam operasional agrowisata?',
                'en' => 'What are the agrotourism operating hours?',
            ],
            'answer' => [
                'id' => '<p>Kami buka setiap hari mulai pukul 07.00 hingga 17.00 WIB. Untuk paket khusus seperti Sunrise Photography, kunjungan dimulai lebih pagi sesuai jadwal yang tertera pada masing-masing paket.</p>',
                'en' => '<p>We are open every day from 07:00 to 17:00 WIB (local time). For special packages such as Sunrise Photography, visits start earlier according to the schedule listed on each package.</p>',
            ],
            'sort_order' => 6,
        ]);
    }
}
