# E-BATS BUMA (Electronic Berita Acara Serah Terima)

Sistem Manajemen Serah Terima Aset IT berbasis Web (Paperless) khusus untuk lingkungan operasional internal Departemen IT PT Bukit Makmur Mandiri Utama (BUMA) Site IPR. Sistem ini mendigitalisasi proses BAST fisik menjadi dokumen PDF otomatis yang dilengkapi dengan *Digital Signature* dan *Audit Trail*.

---

## 📊 Analisis & Tinjauan Sistem

Aplikasi ini dirancang dengan standar *Enterprise Asset Management System* skala ringan, disesuaikan khusus dengan SOP (Standar Operasional Prosedur) Departemen IT dalam mengelola keluar-masuknya aset perusahaan.

### 1. Fitur Unggulan (Key Features)
- **Paperless BAST & Digital Signature:** Menangkap tanda tangan langsung melalui layar sentuh / *mouse* (Canvas) dan menyematkannya ke dalam dokumen PDF BAST secara otomatis. Tidak perlu lagi *print* dan *scan* kertas.
- **Live Search AJAX (Autocomplete):** Pencarian NIK Karyawan dan Kode SAP Aset berjalan secara *real-time* tanpa perlu *reload* halaman, mempercepat kerja *IT Support* dan meminimalisir *human error* (typo).
- **Safe Mass Data Migration:** Modul Import CSV dilengkapi dengan mekanisme Database Transaction (`DB::beginTransaction()`). Sistem akan melakukan *Rollback* otomatis jika terjadi *error* pada sebagian baris data Excel, sehingga integritas database tetap aman dan tidak korup.
- **Auto-Audit Trail:** Sistem otomatis merekam jejak rekam (siapa, IP address, waktu, dan aksi) setiap kali terjadi perubahan status aset di latar belakang menggunakan *Eloquent Events Laravel*.
- **Smart Fallback Legacy Data:** Algoritma pembacaan data *legacy* (transaksi lawas MS Access) akan secara dinamis meregistrasikan aset/karyawan yang belum terdaftar dan memberikan *bypass* gambar *signature* agar PDF dokumen lama tetap valid dan dapat dicetak.

### 2. Keamanan Sistem (Security)
Aplikasi ini dikembangkan untuk penggunaan **Intranet (Lokal Area Network IT)**, sehingga dirancang *seamless* tanpa proses Login (Authentication) guna mempercepat mobilitas operasional. Meskipun terbuka secara internal, sistem ini kebal terhadap serangan umum:
- **Anti-SQL Injection:** Menggunakan *ORM Eloquent Laravel* dan *Query Builder* yang ter-parameterisasi.
- **Anti-XSS (Cross-Site Scripting):** Seluruh input teks difilter menggunakan pembersih tag HTML sebelum disimpan ke database.
- **Anti-CSRF:** Setiap formulir aksi mutasi data dilindungi oleh token *Cross-Site Request Forgery*.
- **File Validation:** Modul Import membatasi ketat *MIME type* khusus untuk file `.csv` dan `.txt`.

### 3. Kelayakan Operasional
Sistem ini sangat layak diimplementasikan karena:
- **Sesuai Workflow Lapangan:** Mengadaptasi alur kerja nyata (Pilih Aset -> TTD IT -> TTD Peminjam -> Status 'Habis' -> TTD Pengembali -> Status 'Tersedia').
- **Modern & Ringan:** Antarmuka dibangun menggunakan *Tailwind CSS* yang responsif dan sangat ringan dijalankan di PC kantor berspesifikasi menengah ke bawah.

---

## 💻 Persyaratan Server (Prerequisites)

Sebelum melakukan *deployment* atau instalasi di PC Server/Lokal IT, pastikan telah ter-install spesifikasi berikut:
1. **PHP** (Minimal versi 8.2)
2. **Composer** (PHP Dependency Manager)
3. **MySQL / MariaDB** (Disarankan menggunakan XAMPP, MAMP, atau Laragon)
4. Ekstensi PHP yang wajib diaktifkan (`php.ini`): `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`, `GD`.

---

## 🚀 Panduan Instalasi (Step-by-Step)

### 1. Persiapkan Folder Aplikasi
Pindahkan atau *Copy* folder repository `e-bats_buma` ke dalam folder *root server intranet* Anda.
- Lingkungan XAMPP: `C:\xampp\htdocs\e-bats_buma`
- Lingkungan Laragon: `C:\laragon\www\e-bats_buma`

### 2. Install Dependencies
Buka Terminal / Command Prompt, arahkan ke dalam folder aplikasi, lalu jalankan perintah berikut untuk mengunduh seluruh *library core* framework Laravel dan PDF Generator (DomPDF):

    composer install

### 3. Konfigurasi Database (.env)
- Gandakan file `.env.example` dan ubah namanya menjadi `.env`.
- Buka file `.env` menggunakan *text editor* (Notepad/VS Code).
- Sesuaikan kredensial database dengan komputer/server lokal (pastikan Anda telah membuat database kosong bernama `db_bast_it` di MySQL/phpMyAdmin terlebih dahulu):

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_bast_it
    DB_USERNAME=root
    DB_PASSWORD=

*(Catatan: Kosongkan nilai `DB_PASSWORD` jika MySQL XAMPP Anda menggunakan konfigurasi default tanpa password).*

### 4. Generate Application Key
Jalankan perintah berikut untuk mengamankan enkripsi sesi Laravel:

    php artisan key:generate

### 5. Eksekusi Migrasi Database
Jalankan perintah ini untuk membangun seluruh relasi dan struktur tabel (`barangs`, `karyawans`, `peminjamans`, dan `audit_logs`) ke dalam database MySQL secara otomatis:

    php artisan migrate

### 6. Konfigurasi Folder Signatures
Sistem membutuhkan folder khusus untuk menyimpan *cache* gambar tanda tangan digital. Pastikan di dalam direktori `public/` terdapat folder bernama `signatures`. Jika belum ada, buat foldernya secara manual melalui Terminal:

    mkdir public/signatures

### 7. Jalankan Aplikasi
Jika menggunakan XAMPP/Laragon yang Apache-nya sudah menyala, aplikasi dapat langsung diakses melalui web browser dengan URL `http://localhost/e-bats_buma/public`. 

Jika ingin menjalankan server *development* bawaan PHP agar bisa diakses lewat jaringan LAN ruangan IT, gunakan perintah:

    php artisan serve --host=0.0.0.0 --port=8000

*(Aplikasi kini dapat diakses dari PC lain yang berada di satu jaringan WiFi/LAN yang sama melalui `http://[IP_ADDRESS_KOMPUTER_SERVER]:8000`).*

---

## 📂 Panduan Import Data Awal (Migrasi Data MS Access Legacy)

Untuk melakukan migrasi data dari sistem lama tanpa menyebabkan kerusakan sistem (*Foreign Key Constraint Error*), Anda **wajib** melakukan import data CSV melalui menu **Kelola Data** dengan urutan yang ketat:

1. Import **Master Karyawan** (`tbkaryawan.csv`).
2. Import **Master Barang** (`tbbarang.csv`).
3. Import **Transaksi BAST Lama** (`tbtndterima.csv`).

**PENTING:** Pastikan file Excel yang ditarik dari MS Access telah di-*Save As* ke dalam format `CSV UTF-8 (Comma Delimited)` sebelum diunggah ke sistem E-BATS.

---

## ⚙️ Troubleshooting (Solusi Masalah Umum)

### 1. Error saat Import CSV (File too large / Timeout)
Buka konfigurasi `php.ini` di dalam folder PHP XAMPP Anda (`C:\xampp\php\php.ini`).
- Cari variabel `upload_max_filesize` dan ubah ukurannya menjadi `50M`.
- Cari variabel `post_max_size` dan ubah ukurannya menjadi `50M`.
- Cari variabel `max_execution_time` dan ubah menjadi `300`.
- Setelah di-save, *Restart Service Apache* pada XAMPP Control Panel.

### 2. Tanda Tangan Tidak Muncul di PDF / Error 500 saat Cetak
Pastikan folder `public/signatures/` sudah dibuat dan memilki *permission* Write/Read yang aktif.

### 3. Error Route Not Defined
Pastikan Anda tidak secara tidak sengaja menghapus penamaan *route* (`->name('...')`) pada file `routes/web.php`.

### 4. Nama Peminjam Tertulis "IT IPR"
Ini bukan *bug*. Sistem membaca data historis dari MS Access di mana entitas "IT IPR" digunakan sebagai NIK penampung untuk barang yang ditarik kembali sebagai *spare/stok* gudang.

---

**Developed for BUMA Site IPR IT Dept.**  
*Created by: Christoba Joshua Hutagalung (Teknik Informatika, Telkom University Purwokerto)*