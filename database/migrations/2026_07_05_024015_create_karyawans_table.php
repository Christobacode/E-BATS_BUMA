<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//Buat tabel karyawan di database
return new class extends Migration {
    public function up(): void {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique(); 
            $table->string('nama');
            $table->string('departemen');
            $table->string('lokasi_site'); 
            
            // Kolom baru untuk menampung data jabatan dari CSV
            $table->string('jabatan')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('karyawans');
    }
};