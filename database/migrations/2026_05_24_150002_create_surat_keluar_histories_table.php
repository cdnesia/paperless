<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('surat_keluar_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('keterangan')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('surat_keluar_id')
                  ->references('id')->on('surat_keluars')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar_histories');
    }
};
