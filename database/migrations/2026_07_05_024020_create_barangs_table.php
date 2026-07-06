<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
        $table->id();
        $table->string('kode_aset', 50)->unique();
        $table->string('nama_barang', 100);
        $table->string('kategori', 30);
        $table->enum('status_ketersediaan', ['Tersedia', 'Dipinjam'])->default('Tersedia');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
