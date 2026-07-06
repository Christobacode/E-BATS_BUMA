<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'peminjaman_id', 'aksi', 'nilai_lama', 'nilai_baru', 'ip_address'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function peminjaman() {
        return $this->belongsTo(Peminjaman::class);
    }
}
