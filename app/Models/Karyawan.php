<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    // Hubungkan model ini ke tabel 'karyawans'
    protected $table = 'karyawans';
    
    // Model karyawan untuk mengelola data karyawan
    // Daftar kolom yang diizinkan buat disimpen atau diupdate
    protected $fillable = ['nik', 'nama', 'departemen', 'lokasi_site'];
}