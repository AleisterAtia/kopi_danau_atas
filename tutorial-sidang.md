# Naskah Presentasi Tugas Akhir: Rancang Bangun Sistem Informasi E-Tourism pada CV Kopi Danau Diatas

**Tips Sebelum Memulai:**
* Pastikan intonasi suara Anda jelas dan tidak terburu-buru.
* Jaga kontak mata dengan dosen penguji atau audiens.
* Gunakan *pointer* atau kursor *mouse* untuk menunjuk poin penting di layar jika diperlukan.

---

### [Slide 1: Judul Presentasi]
**(Kondisi Layar: Menampilkan slide 1 saat Anda bersiap di depan)**

**Narasi:**
"Assalamualaikum Warahmatullahi Wabarakatuh. Selamat pagi/siang dan salam sejahtera untuk kita semua. 

Yang saya hormati, Bapak/Ibu Dosen Penguji, 
Serta yang saya hormati, Dosen Pembimbing saya, Ibu Meri Azmi, S.T., M.Cs., dan Ibu Dr. Ulya Ilhami Arsyah, S.Kom., M.Kom.

Perkenalkan, saya Fadhil Dzaky Arhab dari program studi Manajemen Informatika, Jurusan Teknologi Informasi, Politeknik Negeri Padang[cite: 1]. Pada kesempatan kali ini, saya akan mempresentasikan hasil Tugas Akhir saya yang berjudul **'Rancang Bangun Sistem Informasi E-Tourism pada CV Kopi Danau Diatas Berbasis Web'**[cite: 1]."

**(Instruksi Transisi: Klik tombol *Next* atau panah kanan pada *keyboard* / *presenter pen* untuk berpindah ke Slide 2)**

---

### [Slide 2: Latar Belakang]
**(Kondisi Layar: Menampilkan Slide 2 - Latar Belakang)**

**Narasi:**
"Proyek Tugas Akhir ini dilatarbelakangi oleh potensi besar yang dimiliki CV Kopi Danau Diatas[cite: 1]. Mereka mengelola perkebunan kopi Arabika seluas 20 hektare di dataran tinggi Alahan Panjang pada ketinggian 1.650 mdpl, dengan enam varietas unggulan[cite: 1]. 

Saat ini, perusahaan sedang merintis layanan agrowisata[cite: 1]. Namun, operasionalnya masih terkendala karena dikelola secara manual[cite: 1]. Proses reservasi tiket hanya mengandalkan pesan WhatsApp atau telepon tanpa pencatatan terpusat[cite: 1]. Hal ini menimbulkan risiko tinggi terjadinya *double booking* atau tumpang tindih jadwal saat kunjungan sedang ramai, serta kurangnya media informasi resmi bagi wisatawan terkait jadwal, paket wisata, dan lokasi kebun[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 3)**

---

### [Slide 3: Rumusan Masalah]
**(Kondisi Layar: Menampilkan Slide 3 - Rumusan Masalah)**

**Narasi:**
"Dari latar belakang tersebut, terdapat empat rumusan masalah utama dalam perancangan sistem ini:
Pertama, bagaimana merancang sistem untuk menggantikan pemesanan via WhatsApp dan mencegah *double booking*[cite: 1]?
Kedua, bagaimana mengimplementasikan fitur informasi tempat wisata dan peta lokasi resmi ke dalam sebuah *website*[cite: 1]?
Ketiga, bagaimana menyajikan edukasi kopi secara digital, khususnya untuk katalog varietas Lini-S, Gayo, Typica, Kartika, Sigararutang, dan Komasti[cite: 1]?
Dan keempat, bagaimana merancang mekanisme notifikasi otomatis via WhatsApp untuk memudahkan admin saat ada pembayaran masuk[cite: 1]?"

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 4)**

---

### [Slide 4: Tujuan]
**(Kondisi Layar: Menampilkan Slide 4 - Tujuan)**

**Narasi:**
"Untuk menjawab permasalahan tersebut, pengembangan sistem ini bertujuan untuk:
1. Membangun sistem reservasi tiket otomatis berbasis *web* yang mencegah tumpang tindih jadwal[cite: 1].
2. Mengembangkan *website* E-Tourism yang terintegrasi dengan Google Maps[cite: 1].
3. Menyediakan sistem *review* atau ulasan untuk menjaring umpan balik dari pengunjung[cite: 1].
4. Menyediakan halaman katalog edukatif mengenai karakteristik enam varietas kopi Arabika Solok[cite: 1].
5. Serta, mengimplementasikan notifikasi WhatsApp *real-time* menggunakan *gateway* Fonnte ketika transaksi berhasil[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 5)**

---

### [Slide 5: Metodologi]
**(Kondisi Layar: Menampilkan Slide 5 - Metodologi)**

**Narasi:**
"Dalam pengembangan sistem ini, saya menggunakan metode SDLC model **Waterfall**[cite: 1].
Fase ini berjalan secara sekuensial, dimulai dari tahap *Requirement* atau pengumpulan data varietas kopi, lalu *Design* meliputi UI/UX, arsitektur MVC, dan ERD[cite: 1]. Selanjutnya tahap *Implementation* menggunakan *framework* Laravel dan Filament PHP, diteruskan dengan *Integration & Testing* menggunakan *Black Box Testing*, dan diakhiri dengan fase *Operation & Maintenance* di *server*[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 6)**

---

### [Slide 6: Alur Sistem Yang Sedang Berjalan]
**(Kondisi Layar: Menampilkan Slide 6 - Alur Sistem Berjalan)**

**Narasi:**
"Berikut adalah gambaran sistem operasional yang sedang berjalan. *(Tunjuk menggunakan pointer jika ada)* 
Saat ini, proses sangat informal dan sepenuhnya manual[cite: 1]. Wisatawan mendengar info dari mulut ke mulut, lalu mengontak pengelola via WhatsApp[cite: 1]. Kesepakatan jadwal hanya berupa kesepakatan lisan tanpa pencatatan, yang kemudian dilanjutkan dengan kunjungan[cite: 1]. 
Kelemahan alur ini adalah jangkauan promosi yang sangat terbatas pada relasi terdekat saja, dan risiko *double booking* sangat tinggi karena ketiadaan manajemen kuota harian[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 7)**

---

### [Slide 7: Sistem Yang Diusulkan]
**(Kondisi Layar: Menampilkan Slide 7 - Sistem Yang Diusulkan)**

**Narasi:**
"Oleh karena itu, saya mengusulkan pembaruan sistem yang mendigitalisasi operasional dari hulu ke hilir[cite: 1].
Perubahan utamanya adalah:
1. Reservasi lisan digantikan dengan sistem reservasi otomatis berkuota *real-time*[cite: 1].
2. Risiko *double booking* diatasi dengan fitur *row locking* pada *database* terpusat[cite: 1].
3. Informasi dari mulut ke mulut diubah menjadi *website* E-Tourism dengan Katalog Digital, Peta Lokasi (Maps), dan Artikel Blog[cite: 1].
4. Pembayaran tunai kini bisa dilakukan melalui integrasi *Payment Gateway* Midtrans yang akan menghasilkan E-Tiket berupa QR Code[cite: 1].
5. Dan pengecekan transfer manual oleh admin kini digantikan dengan Notifikasi WhatsApp otomatis via API Fonnte[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 8)**

---

### [Slide 8: Use Case Diagram]
**(Kondisi Layar: Menampilkan Slide 8 - Use Case Diagram)**

**Narasi:**
"Sistem ini dirancang untuk memfasilitasi 3 aktor utama dengan total 29 *use case* yang terintegrasi[cite: 1].
1. **Pengunjung:** dapat mengakses informasi destinasi, mengeksplorasi peta, dan mempelajari katalog edukasi digital[cite: 1].
2. **Wisatawan:** memiliki akses untuk reservasi tiket, proses pembayaran *gateway*, mengelola e-tiket, serta memberikan ulasan[cite: 1].
3. **Admin:** memegang kendali penuh melalui *panel* Filament PHP untuk memvalidasi tiket masuk menggunakan *scan* QR, mengatur kuota, mempublikasikan blog, dan memantau laporan operasional[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 9)**

---

### [Slide 9: Entity Relationship Diagram (ERD)]
**(Kondisi Layar: Menampilkan Slide 9 - ERD)**

**Narasi:**
"Secara arsitektur *database* relasional, sistem ini dibangun di atas 17 tabel utama yang saling terhubung secara presisi melalui *foreign key* di MySQL[cite: 1]. 
Database dibagi menjadi tiga kategori utama:
1. **Tabel Interaksi Publik**, seperti *reviews, users,* dan *blog_posts* untuk mengelola jejak digital wisatawan[cite: 1].
2. **Tabel Transaksional**, meliputi *bookings* dan *payments* yang dilengkapi mekanisme *row locking* agar alur reservasi tiket dan validasi pembayaran bebas dari konflik data[cite: 1].
3. Dan **Tabel Inventori & Katalog** untuk manajemen kuota paket serta data varietas Arabika[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 10)**

---

### [Slide 10: Demo Sistem]
**(Kondisi Layar: Menampilkan Slide 10 - Demo Sistem)**

**Narasi:**
"Selanjutnya, saya akan mendemonstrasikan secara singkat bagaimana sistem informasi E-Tourism Kopi Danau Diatas ini berjalan secara langsung melalui *website* `kopidanaudiatas.cloud`."

*(Instruksi Transisi: Jika Anda mempresentasikan langsung aplikasinya, keluar dari PPT (tekan ESC) dan buka browser. Lakukan demo sesuai durasi yang diizinkan. Jika hanya menampilkan dari slide, langsung klik Next).*

**(Instruksi Transisi: Setelah demo selesai, kembali ke mode Fullscreen PPT dan klik *Next* untuk berpindah ke Slide 11)**

---

### [Slide 11: Kesimpulan]
**(Kondisi Layar: Menampilkan Slide 11 - Kesimpulan)**

**Narasi:**
"Sebagai kesimpulan, Sistem Informasi E-Tourism CV Kopi Danau Diatas telah berhasil dirancang dan diimplementasikan untuk mendigitalisasi operasional dan promosi agrowisata secara terpadu[cite: 1]. Sistem ini terbukti sukses menyelesaikan kendala operasional manual melalui otomasi reservasi paket wisata berbasis kuota *real-time* yang efektif mencegah *double booking*[cite: 1]. 
Lebih jauh, keberadaan sistem ini mendongkrak kualitas pariwisata edukatif dengan adanya integrasi peta lokasi akurat, sistem ulasan interaktif, penyajian katalog varietas kopi Arabika, serta otomasi notifikasi WhatsApp *Gateway* Fonnte yang secara langsung mempercepat kinerja administratif pengelola[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 12)**

---

### [Slide 12: Saran]
**(Kondisi Layar: Menampilkan Slide 12 - Saran)**

**Narasi:**
"Untuk pengembangan sistem kedepannya, ada beberapa saran yang dapat dilakukan:
1. Melakukan migrasi Midtrans ke *mode production*[cite: 1].
2. Integrasi akomodasi atau *Homestay*[cite: 1].
3. Integrasi dengan agen *travel* lokal[cite: 1].
4. Pengembangan fitur *Street View*[cite: 1]."

**(Instruksi Transisi: Klik *Next* untuk berpindah ke Slide 13 (Slide Penutup))**

---

### [Slide 13: Penutup]
**(Kondisi Layar: Menampilkan Slide 13 - Terima Kasih)**

**Narasi:**
"Demikianlah presentasi mengenai hasil Tugas Akhir saya. Atas perhatian Bapak/Ibu Dosen Penguji dan Dosen Pembimbing, saya ucapkan terima kasih. 

Saya kembalikan kepada moderator atau ketua sidang, dan saya persilakan apabila ada pertanyaan, masukan, maupun saran."