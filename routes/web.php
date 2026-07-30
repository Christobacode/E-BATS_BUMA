<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

// 1. Dashboard (Beranda)
Route::get('/', [PeminjamanController::class, 'dashboard'])->name('peminjaman.dashboard');

// 2. Pusat Data BAST & Riwayat
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');

// 3. API Autocomplete Karyawan
Route::get('/karyawan/search', [PeminjamanController::class, 'searchKaryawan'])->name('karyawan.search');

// 4. Transaksi Peminjaman & Pengembalian
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
Route::get('/peminjaman/{id}/return', [PeminjamanController::class, 'formPengembalian'])->name('peminjaman.return');
Route::post('/peminjaman/{id}/return', [PeminjamanController::class, 'prosesPengembalian'])->name('peminjaman.proses_pengembalian');
Route::get('/peminjaman/{id}/pdf', [PeminjamanController::class, 'cetakPDF'])->name('peminjaman.pdf');

// ==========================================
// 5. HALAMAN MANAGE (Baru: Import, Export, Edit Barang/Karyawan)
// ==========================================
Route::get('/manage', [PeminjamanController::class, 'manage'])->name('manage.index');
Route::get('/export-csv', [PeminjamanController::class, 'exportExcel'])->name('peminjaman.export');

// Route Import
Route::post('/peminjaman/import', [PeminjamanController::class, 'importExcel'])->name('peminjaman.import');
Route::post('/barang/import', [PeminjamanController::class, 'importBarang'])->name('barang.import');
Route::post('/karyawan/import', [PeminjamanController::class, 'importKaryawan'])->name('karyawan.import');

// Route CRUD Master Barang
Route::post('/barang', [PeminjamanController::class, 'storeBarang'])->name('barang.store');
Route::put('/barang/{id}', [PeminjamanController::class, 'updateBarang'])->name('barang.update');
Route::delete('/barang/{id}', [PeminjamanController::class, 'destroyBarang'])->name('barang.destroy');

// Route CRUD Master Karyawan
Route::post('/karyawan', [PeminjamanController::class, 'storeKaryawan'])->name('karyawan.store');
Route::put('/karyawan/{id}', [PeminjamanController::class, 'updateKaryawan'])->name('karyawan.update');
Route::delete('/karyawan/{id}', [PeminjamanController::class, 'destroyKaryawan'])->name('karyawan.destroy');