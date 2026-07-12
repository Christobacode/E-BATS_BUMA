<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

Route::get('/', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

// Rute untuk Form Pengembalian
Route::get('/peminjaman/{id}/kembali', [PeminjamanController::class, 'formPengembalian'])->name('peminjaman.form_pengembalian');
Route::post('/peminjaman/{id}/kembali', [PeminjamanController::class, 'prosesPengembalian'])->name('peminjaman.proses_pengembalian');

// Rute untuk Export Excel
Route::get('/peminjaman/export/excel', [PeminjamanController::class, 'exportExcel'])->name('peminjaman.export');