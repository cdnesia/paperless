<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('surat_keluar_id')->nullable();
            $table->foreignId('pengguna_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->text('alasan')->nullable();
            $table->enum('status', ['diteruskan', 'diterima', 'ditolak', 'selesai'])->default('diteruskan');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();

            $table->foreign('surat_keluar_id')
                  ->references('id')->on('surat_keluars')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisis');
    }
};
