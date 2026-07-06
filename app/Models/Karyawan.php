<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    // app/Models/Karyawan.php
    protected $fillable = ['nik', 'nama', 'departemen']; 
}
