<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//Buat tabel peminjaman di database
return new class extends Migration {
    public function up(): void {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            
            // Tahap saat akan melakukan pengambilan barang
            $table->string('nik_peminjam')->nullable();    // Bisa kosong kalau input manual
            $table->string('nama_peminjam');       
            $table->string('departemen_peminjam'); 
            $table->string('lokasi_peminjam')->nullable(); // Bisa kosong kalau input manual
            
            $table->string('nama_admin_penyerah');
            $table->date('tanggal_peminjaman');
            $table->string('tanda_tangan_peminjam'); 
            $table->string('tanda_tangan_admin_penyerah'); 
            
            //Penambahan status invalid dikarenakan format nya mengikuti csv data peminjaman lama
            $table->enum('status', ['Dipinjam', 'Dikembalikan', 'Invalid'])->default('Dipinjam');

            // tahap saat akan melakukan pengembalian barang
            $table->string('nik_pengembali')->nullable();  // BARU
            $table->string('nama_pengembali')->nullable();
            $table->string('departemen_pengembali')->nullable();
            $table->string('lokasi_pengembali')->nullable(); // BARU
            
            $table->string('nama_admin_penerima')->nullable();
            $table->string('kondisi_dikembalikan')->nullable();
            $table->date('tanggal_pengembalian')->nullable();
            $table->string('tanda_tangan_pengembali')->nullable(); 
            $table->string('tanda_tangan_admin_penerima')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('peminjamans');
    }
};