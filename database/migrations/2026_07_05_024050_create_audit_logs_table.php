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
        Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained('users'); // Admin/IT Support
        $table->foreignId('peminjaman_id')->constrained('peminjamans')->onDelete('cascade');
        $table->string('aksi');
        $table->string('nilai_lama')->nullable();
        $table->string('nilai_baru')->nullable();
        $table->ipAddress('ip_address')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
