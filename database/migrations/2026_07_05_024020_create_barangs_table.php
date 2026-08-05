<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Isi table barangs dengan kolom-kolom yang sesuai dengan kebutuhan aplikasi
return new class extends Migration {
    public function up(): void {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->unique();
            $table->string('nama_barang');
            $table->integer('stok')->default(0);
            $table->enum('status', ['Tersedia', 'Habis'])->default('Tersedia');
            $table->string('kategori_barang');
            $table->string('merk_barang');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('barangs');
    }
};