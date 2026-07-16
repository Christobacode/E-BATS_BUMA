<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Peminjaman extends Model
{
    protected $table = 'peminjamans';
    
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

    public function barang() {
        return $this->belongsTo(Barang::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::updated(function ($model) {
            if($model->isDirty('status')) {
                \App\Models\AuditLog::create([
                    'user_id' => Auth::id() ?? null,
                    'peminjaman_id' => $model->id,
                    'aksi' => 'UBAH_STATUS',
                    'nilai_lama' => $model->getOriginal('status'),
                    'nilai_baru' => $model->status,
                    'ip_address' => request()->ip()
                ]);
            }
        });
    }
}  