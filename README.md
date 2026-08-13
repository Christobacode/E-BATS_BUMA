# E-BATS BUMA (Electronic Berita Acara Serah Terima)

Aplikasi ini dibuat khusus buat bantu digitalisasi proses serah terima aset IT kita. Biar nggak ada lagi ceritanya BAST fisik ilang, numpuk di meja, atau repot nge-print dan scan kertas. Semua udah otomatis jadi PDF plus ada tanda tangan digitalnya.

---

## Teknologi di Balik Layar (Tech Stack)
- **Backend:** Laravel (PHP 8.2+)
- **Database:** MySQL / MariaDB
- **Frontend / UI:** Tailwind CSS, HTML5, CSS3
- **Biar Interaktif:** JavaScript, AJAX (jQuery)
- **Cetak PDF:** DomPDF
- **Digital Signature:** HTML5 Canvas API

---

## Fitur Utama Aplikasi
Biar kerjaan kita makin sat-set, aplikasi ini udah dilengkapi:
- **Tanda Tangan Langsung di Layar:** Menangkap tanda tangan langsung pakai *mouse*, layar sentuh, atau **bisa juga menggunakan *wacom***, dan langsung otomatis nempel di PDF BAST.
- **Pencarian Cepat (Live Search):** Ngetik NIK atau Kode SAP langsung muncul datanya secara *real-time* tanpa perlu nunggu loading halaman. Bebas typo!
- **Import Data Aman (Transaction):** Kalau lagi masukin data Excel massal terus tiba-tiba ada error di tengah jalan, database nggak bakal berantakan karena sistem bakal otomatis ngebatalin (Rollback).
- **Support Data MS Access Lama:** Tenang aja, aplikasi ini cukup pintar buat ngebaca log transaksi jadul. Kalau ada NIK "IT IPR" (barang ditarik ke gudang), sistem nggak bakal error dan PDF lama tetep bisa dicetak.

---

## Skema Penggunaan & Keamanan
Aplikasi ini didesain spesifik untuk dioperasikan **secara offline (Lokal/Standalone)** di satu komputer khusus milik Admin IT. Karena penggunaannya tertutup di satu perangkat fisik, sistem ini sengaja dibuat *seamless* tanpa proses Login (Authentication) biar pelayanan *IT Support* makin cepet. 

Keamanan dari input data tetap terjaga lewat:
- **Anti-SQL Injection & Anti-XSS:** Pakai ORM Laravel, input teks otomatis dibersihkan dari sisipan kode jahat.
- **Anti-CSRF:** Formulir mutasi dilindungi token khusus.
- **Folder Signatures Terkunci (.htaccess):** Folder tempat nyimpen gambar tanda tangan udah dikunci rapat pakai `.htaccess`. Gambar nggak bisa diakses langsung lewat URL (mencegah pencurian TTD) dan mencegah eksekusi *script* jahat.

---

## Syarat Wajib PC Admin IT
Sebelum di-install di PC lokal IT, pastiin hal-hal ini udah siap ya:
1. **PHP** (Minimal banget versi 8.2)
2. **Composer** (Buat ngatur *library* PHP-nya)
3. **XAMPP / Laragon** (Biar gampang dapet Apache & MySQL-nya)
4. Aktifin ekstensi PHP ini di file `php.ini`: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`, `GD`.

---

## Cara Install & Setup (Tinggal Ikutin Aja)

### 1. Masukin Folder ke Server Lokal
Copy folder repository `e-bats_buma` ke dalam folder web lokal kalian.
- Lingkungan XAMPP: `C:\xampp\htdocs\e-bats_buma`
- Lingkungan Laragon: `C:\laragon\www\e-bats_buma`

### 2. Download Dependencies (Library)
Buka CMD atau Terminal, arahin ke folder aplikasi tadi, terus ketik ini buat download semua file inti Laravel-nya:

    composer install

### 3. Setting Database (.env)
- Copy file `.env.example`, terus *rename* jadi `.env`.
- Buka file `.env`-nya pakai Notepad atau VS Code.
- Pastiin kalian udah bikin database kosong namanya `db_bast_it` di phpMyAdmin. Terus samain settingan ini:

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_bast_it
    DB_USERNAME=root
    DB_PASSWORD=

*(Catatan: Biarin aja `DB_PASSWORD` kosong kalau XAMPP kalian emang nggak dipakein password).*

### 4. Bikin Key & Generate Tabel Database
Tinggal ketik dua perintah ini satu-satu di CMD untuk mengamankan sesi dan membangun kerangka tabel (`barangs`, `karyawans`, dan `peminjamans`):

    php artisan key:generate
    php artisan migrate

### 5. Konfigurasi Keamanan Folder Signatures (Penting!)
Bikin folder tempat nyimpen tanda tangan dan langsung kita gembok pakai `.htaccess`:
- Di CMD, ketik perintah ini buat bikin foldernya: `mkdir public\signatures`
- Buka folder `public/signatures/` itu, lalu bikin file baru dengan nama `.htaccess` (pakai titik di depan).
- Isi file `.htaccess` tersebut dengan kode ini lalu *Save*:

    Deny from all
    <IfModule mod_php7.c>
        php_flag engine off
    </IfModule>
    <IfModule mod_php8.c>
        php_flag engine off
    </IfModule>
    AddType text/plain .php .php3 .php4 .php5 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi

### 6. Jalanin Aplikasinya
Pastikan service Apache dan MySQL di XAMPP sudah menyala. Aplikasi bisa langsung diakses lewat browser di alamat:  
**`http://localhost/e-bats_buma/public`**

---

## Panduan Buat yang Mau Ngulik Kodenya
Kalau ke depannya ada tim IT yang mau nerusin atau nambahin fitur, ini bocoran foldernya:
- `app/Http/Controllers/PeminjamanController.php` -> Otak utamanya (Logika BAST, AJAX, PDF).
- `app/Models/` -> Pengatur relasi database (`Barang.php`, `Karyawan.php`, `Peminjaman.php`).
- `database/migrations/` -> Blueprint struktur database.
- `resources/views/` -> Tampilan UI (Blade).
- `public/signatures/` -> Tempat gambar TTD disimpan (sudah diamankan `.htaccess`).
- `routes/web.php` -> Pengatur URL/Link aplikasi.

---

## Alur Cara Pakainya (Workflow)
1. **Peminjaman Baru:** Klik "Buat BAST Baru". Masukin NIK & Kode SAP (otomatis muncul dari AJAX).
2. **Tanda Tangan:** Peminjam dan staf IT langsung corat-coret di layar (atau pakai Wacom).
3. **Cetak PDF:** Sistem bakal ngarahin ke halaman BAST berformat PDF yang sah buat diarsipkan.
4. **Pengembalian:** Cari data di tabel "Sedang Dipinjam", klik "Kembalikan", minta TTD pengembalian, data otomatis masuk ke "Riwayat".

---

## Cara Pindahin Data Lama (Wajib Sesuai Urutan!)
Untuk migrasi data dari MS Access jadul, **wajib** pakai menu Import CSV dengan urutan:
1. Import **Master Karyawan** (`tbkaryawan.csv`).
2. Import **Master Barang** (`tbbarang.csv`).
3. Import **Transaksi BAST Lama** (`tbtndterima.csv`).

*Penting: Pastiin file Excel dari MS Access udah di-Save As jadi format `CSV UTF-8 (Comma Delimited)` sebelum di-upload!*

---

## Troubleshooting (Solusi Masalah Umum)

### 1. Gagal Import CSV (File too large / Timeout)
Buka `php.ini` di folder PHP XAMPP Anda. Ubah angka-angka ini:
- `upload_max_filesize = 50M`
- `post_max_size = 50M`
- `max_execution_time = 300`
Lalu jangan lupa *Restart* Apache.

### 2. Tanda Tangan Nggak Muncul di PDF / Error 500
Cek folder `public/signatures/`, pastikan foldernya sudah dibuat dan periksa apakah ada *typo* saat menuliskan file `.htaccess`.

### 3. Ada Error "Route Not Defined"
Pastiin kalian nggak nggak sengaja ngehapus tulisan `->name('...')` waktu ngedit file `routes/web.php`.

### 4. Kok Nama Peminjamnya "IT IPR"?
Ini bukan *bug*. Sistem ngebaca transaksi MS Access di mana entitas "IT IPR" dipakai sebagai NIK penampung buat barang yang ditarik kembali jadi stok gudang.

---

**Developed for BUMA Site IPR IT Dept.**  
*Created by: Christoba Joshua Hutagalung (Teknik Informatika, Telkom University Purwokerto)*