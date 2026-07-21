<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $table = 'barangs';
    protected $fillable = ['kode_aset', 'nama_barang', 'status'];

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class);
    }
}