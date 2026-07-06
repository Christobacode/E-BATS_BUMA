<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;
//Buat bikin langsung ke halaman peminjaman
Route::redirect('/', '/peminjaman');
// ROUTE PUBLIK: Pastikan kodenya utuh dari Route::get sampai ->name()
Route::get('/verifikasi-bast/{uuid}', [PeminjamanController::class, 'verifikasi'])->name('peminjaman.verifikasi');


// ROUTE ADMIN
Route::prefix('peminjaman')->name('peminjaman.')->group(function () {
    
    Route::get('/create', [PeminjamanController::class, 'create'])->name('create');
    Route::post('/store', [PeminjamanController::class, 'store'])->name('store');
    Route::patch('/{id}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('kembalikan');
    Route::get('/{id}/cetak', [PeminjamanController::class, 'cetakPdf'])->name('cetak');
    Route::get('/', [PeminjamanController::class, 'index'])->name('index');
    
});