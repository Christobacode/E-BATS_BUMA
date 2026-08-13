<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Peminjaman extends Model
{
    // Hubungkan model ini ke tabel 'peminjamans'
    protected $table = 'peminjamans';
    
    // Semua kolom ini wajib didaftarkan biar datanya bisa masuk pas di-save
    protected $fillable = [
        'uuid', 'barang_id', 
        'nik_peminjam', 'nama_peminjam', 'departemen_peminjam', 'lokasi_peminjam',
        'nama_admin_penyerah', 'tanggal_peminjaman', 'tanda_tangan_peminjam',
        'tanda_tangan_admin_penyerah',
        'status', 
        'nik_pengembali', 'nama_pengembali', 'departemen_pengembali', 'lokasi_pengembali', 
        'nama_admin_penerima', 'kondisi_dikembalikan', 'tanggal_pengembalian', 
        'tanda_tangan_pengembali', 'tanda_tangan_admin_penerima'
    ];

    // Relasi ke tabel barang: Tiap catatan peminjaman pasti nyambung ke 1 barang
    public function barang() {
        return $this->belongsTo(Barang::class);
    }

    // Fungsi bawaan Laravel yang jalan otomatis di belakang layar
    protected static function booted()
    {
        // Pas data peminjaman baru mau disimpen, otomatis buatin kode UUID kalau masih kosong
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}