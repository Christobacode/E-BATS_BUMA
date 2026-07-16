<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

// 1. Dashboard (Beranda)
Route::get('/', [PeminjamanController::class, 'dashboard'])->name('peminjaman.dashboard');

// 2. Pusat Data BAST (Workspace) & Riwayat
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');

// 3. API Autocomplete Karyawan
Route::get('/karyawan/search', [PeminjamanController::class, 'searchKaryawan'])->name('karyawan.search');

// 4. Form Penyerahan Aset (Peminjaman Baru)
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

// 5. Form Pengembalian Aset
Route::get('/peminjaman/{id}/return', [PeminjamanController::class, 'formPengembalian'])->name('peminjaman.return');
Route::post('/peminjaman/{id}/return', [PeminjamanController::class, 'prosesPengembalian'])->name('peminjaman.proses_pengembalian');

// 6. Cetak Dokumen & Export
Route::get('/peminjaman/{id}/pdf', [PeminjamanController::class, 'cetakPDF'])->name('peminjaman.pdf');
Route::get('/export-csv', [PeminjamanController::class, 'exportExcel'])->name('peminjaman.export');