<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

Route::get('/', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

// Rute buat form pengembalian barang
Route::get('/peminjaman/{id}/kembali', [PeminjamanController::class, 'formPengembalian'])->name('peminjaman.form_pengembalian');
Route::post('/peminjaman/{id}/kembali', [PeminjamanController::class, 'prosesPengembalian'])->name('peminjaman.proses_pengembalian');

// Rute buat export
Route::get('/peminjaman/export/excel', [PeminjamanController::class, 'exportExcel'])->name('peminjaman.export');

// Rute buat download pdf 
Route::get('/peminjaman/{id}/pdf', [PeminjamanController::class, 'cetakPDF'])->name('peminjaman.pdf');