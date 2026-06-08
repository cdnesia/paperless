<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tanda_tangan_digital', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('surat_keluar_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('hash_sha256_original', 64);
            $table->string('hash_sha256_final', 64)->nullable();
            $table->text('qr_code')->nullable()->comment('Path gambar QR code');
            $table->text('file_pdf_final')->nullable()->comment('Path PDF final yang sudah ditandatangani');
            $table->string('penandatangan');
            $table->string('lokasi', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->foreign('surat_keluar_id')
                  ->references('id')->on('surat_keluars')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanda_tangan_digital');
    }
};
