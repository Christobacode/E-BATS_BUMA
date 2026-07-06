<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = ['kode_aset', 'nama_barang', 'kategori', 'status_ketersediaan'];
}
