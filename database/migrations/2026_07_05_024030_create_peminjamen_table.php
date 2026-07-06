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
        Schema::create('peminjamans', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique(); // Untuk URL QR Code (Security)
        $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
        $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
        $table->date('tanggal_penyerahan');
        $table->longText('tanda_tangan'); // Menampung Base64 Signature
        $table->enum('status', ['Dipinjam', 'Dikembalikan'])->default('Dipinjam');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamen');
    }
};
