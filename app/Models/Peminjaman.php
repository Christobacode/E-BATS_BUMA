<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Peminjaman extends Model
{
    protected $table = 'peminjamans';
    
    protected $fillable = [
        'uuid', 'barang_id', 'nama_peminjam', 'departemen_peminjam',
        'nama_admin_penyerah', 'tanggal_peminjaman', 'tanda_tangan_peminjam',
        'tanda_tangan_admin_penyerah',
        'status', 'nama_pengembali', 'departemen_pengembali', 
        'nama_admin_penerima', 'kondisi_dikembalikan', 'tanggal_pengembalian', 
        'tanda_tangan_pengembali', 'tanda_tangan_admin_penerima' 
    ];

    public function barang() {
        return $this->belongsTo(Barang::class);
    }

    //SECURITY DAN AUTOMATION ENGINE
    protected static function booted()
    {
        // 1.Auto generated UUID saat form surat terima barang 
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // 2.Auto record Audit trail jika status barang diubah(dikembalikan)
        static::updated(function ($model) {
            if($model->isDirty('status')) {
                // Pastikan class AuditLog sudah di-import jika berada di file terpisah
                // use App\Models\AuditLog; (tambahkan di bagian atas jika error)
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