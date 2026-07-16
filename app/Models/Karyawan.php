<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawans';
    
    // Model karyawan untuk mengelola data karyawan
    protected $fillable = ['nik', 'nama', 'departemen', 'lokasi_site'];
}