<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

// ==========================================
// 1. HALAMAN UTAMA & DASHBOARD
// ==========================================
// Menampilkan halaman depan (angka statistik, grafik)
Route::get('/', [PeminjamanController::class, 'dashboard'])->name('peminjaman.dashboard');


// ==========================================
// 2. MENU DATA BAST & RIWAYAT
// ==========================================
// Halaman daftar barang yang saat ini statusnya "Dipinjam"
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
// Halaman riwayat semua transaksi (pinjam & kembali)
Route::get('/riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');


// ==========================================
// 3. FITUR PENCARIAN OTOMATIS (AJAX/API)
// ==========================================
// Rute ini dipanggil di belakang layar buat nampilin saran ketikan di form
Route::get('/karyawan/search', [PeminjamanController::class, 'searchKaryawan'])->name('karyawan.search');
Route::get('/barang/search', [PeminjamanController::class, 'searchBarang'])->name('barang.search'); // KODE TAMBAHAN


// ==========================================
// 4. PROSES TRANSAKSI (PINJAM, KEMBALI, PDF)
// ==========================================
// Buka form buat pinjam barang baru
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
// Eksekusi simpan data pinjaman ke database
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

// Buka form buat ngembaliin barang
Route::get('/peminjaman/{id}/return', [PeminjamanController::class, 'formPengembalian'])->name('peminjaman.return');
// Eksekusi simpan data pengembalian ke database
Route::post('/peminjaman/{id}/return', [PeminjamanController::class, 'prosesPengembalian'])->name('peminjaman.proses_pengembalian');

// Fitur download bukti BAST jadi format PDF
Route::get('/peminjaman/{id}/pdf', [PeminjamanController::class, 'cetakPDF'])->name('peminjaman.pdf');


// ==========================================
// 5. HALAMAN KELOLA DATA (MANAGE, EXPORT, IMPORT)
// ==========================================
// Buka halaman menu "Kelola Data" buat ngatur master data
Route::get('/manage', [PeminjamanController::class, 'manage'])->name('manage.index');

// Ekspor data laporan riwayat ke file CSV/Excel
Route::get('/export-csv', [PeminjamanController::class, 'exportExcel'])->name('peminjaman.export');

// Import (Upload) data massal dari file CSV ke database
Route::post('/peminjaman/import', [PeminjamanController::class, 'importExcel'])->name('peminjaman.import');
Route::post('/barang/import', [PeminjamanController::class, 'importBarang'])->name('barang.import');
Route::post('/karyawan/import', [PeminjamanController::class, 'importKaryawan'])->name('karyawan.import');


// ==========================================
// 6. CRUD MASTER BARANG (Tambah, Edit, Hapus)
// ==========================================
Route::post('/barang', [PeminjamanController::class, 'storeBarang'])->name('barang.store');
Route::put('/barang/{id}', [PeminjamanController::class, 'updateBarang'])->name('barang.update');
Route::delete('/barang/{id}', [PeminjamanController::class, 'destroyBarang'])->name('barang.destroy');


// ==========================================
// 7. CRUD MASTER KARYAWAN (Tambah, Edit, Hapus)
// ==========================================
Route::post('/karyawan', [PeminjamanController::class, 'storeKaryawan'])->name('karyawan.store');
Route::put('/karyawan/{id}', [PeminjamanController::class, 'updateKaryawan'])->name('karyawan.update');
Route::delete('/karyawan/{id}', [PeminjamanController::class, 'destroyKaryawan'])->name('karyawan.destroy');