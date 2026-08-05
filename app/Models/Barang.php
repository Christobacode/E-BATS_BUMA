<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    
    // Kasih tau Laravel kalau model ini pakai tabel 'barangs' di database
    protected $table = 'barangs';
    
    // Kolom-kolom ini aja yang boleh diisi datanya secara langsung dari form (mass assignment)
    protected $fillable = ['kode_aset', 'nama_barang', 'status', 'stok', 'kategori_barang', 'merk_barang'];

    // Relasi ke tabel peminjaman: Satu barang bisa dipinjam berkali-kali (punya riwayat)
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class);
    }
}