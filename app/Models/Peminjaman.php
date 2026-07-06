<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Peminjaman extends Model
{
    protected $table = 'peminjamans';
    protected $fillable = ['karyawan_id', 'barang_id', 'tanggal_penyerahan', 'tanda_tangan', 'status'];

    public function karyawan() {
        return $this->belongsTo(Karyawan::class);
    }

    public function barang() {
        return $this->belongsTo(Barang::class);
    }

    //SECURITY DAN AUTOMATION ENGINE
    protected static function booted()
    {
        // 1.Auto generated UUID saat form surat terima barang 
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });

        // 2.Auto record Audit trail jika status barang diubah(dikembalikan)
        static::updated(function ($model) {
            if($model->isDirty('status')) {
                AuditLog::create([
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
