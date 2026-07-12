<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Relasi ke barang saja (karyawan dihapus karena ketik manual)
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            
            // --- FASE 1: FORM PENGAMBILAN ---
            $table->string('nama_peminjam');       // Ketik manual
            $table->string('departemen_peminjam'); // Ketik manual
            $table->string('nama_admin_penyerah');
            $table->date('tanggal_peminjaman');
            $table->string('tanda_tangan_peminjam'); // Simpan path gambar PNG
            
            $table->enum('status', ['Dipinjam', 'Dikembalikan'])->default('Dipinjam');

            // --- FASE 2: FORM PENGEMBALIAN (Bisa kosong di awal) ---
            $table->string('nama_pengembali')->nullable();
            $table->string('departemen_pengembali')->nullable();
            $table->string('nama_admin_penerima')->nullable();
            $table->string('kondisi_dikembalikan')->nullable();
            $table->date('tanggal_pengembalian')->nullable();
            $table->string('tanda_tangan_pengembali')->nullable(); // Simpan path gambar PNG

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('peminjamans');
    }
};