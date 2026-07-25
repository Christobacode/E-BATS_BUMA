<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

// 1. Dashboard (Beranda)
// Halaman beranda atau halaman yang pertama kali dimunculkan saat dibuka
Route::get('/', [PeminjamanController::class, 'dashboard'])->name('peminjaman.dashboard');

// 2. Pusat Data BAST (Workspace) & Riwayat
// Halaman utama untuk melihat barang yang sedang dipinjam
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
// Halaman untuk melihat log buku riwayat keseluruhan
Route::get('/riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');

// 3. API Autocomplete Karyawan
// untuk mencari nama karyawan di database secara real-time
Route::get('/karyawan/search', [PeminjamanController::class, 'searchKaryawan'])->name('karyawan.search');

// 4. Form Penyerahan Aset (Peminjaman Baru)
// GET: Menampilkan form kosong
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
// POST: Menerima data yang di-submit dari form, lalu menyimpannya ke database
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

// 5. Form Pengembalian Aset
// GET: Menampilkan form pengembalian berdasarkan ID surat
Route::get('/peminjaman/{id}/return', [PeminjamanController::class, 'formPengembalian'])->name('peminjaman.return');
// POST: Menyimpan update data pengembalian ke database
Route::post('/peminjaman/{id}/return', [PeminjamanController::class, 'prosesPengembalian'])->name('peminjaman.proses_pengembalian');

// 6. Cetak Dokumen & Export
// untuk men-generate PDF berdasarkan ID surat dari barang yang dipinjam
Route::get('/peminjaman/{id}/pdf', [PeminjamanController::class, 'cetakPDF'])->name('peminjaman.pdf');
// untuk men-download file CSV (Excel)
Route::get('/export-csv', [PeminjamanController::class, 'exportExcel'])->name('peminjaman.export');

//Untuk menambahkan import csv ke web BATS
Route::post('/peminjaman/import', [PeminjamanController::class, 'importExcel'])->name('peminjaman.import');
// Route untuk Import Master Barang
Route::post('/barang/import', [PeminjamanController::class, 'importBarang'])->name('barang.import');
// Route untuk Import Master Karyawan
Route::post('/karyawan/import', [PeminjamanController::class, 'importKaryawan'])->name('karyawan.import');