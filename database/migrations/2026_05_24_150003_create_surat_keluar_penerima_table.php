<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluar_penerima', function (Blueprint $table) {
            $table->id();
            $table->uuid('surat_keluar_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('dibaca')->default(false);
            $table->timestamp('dibaca_at')->nullable();
            $table->string('status')->nullable()->comment('diterima, ditolak, diteruskan');
            $table->text('alasan')->nullable();
            $table->timestamps();

            $table->foreign('surat_keluar_id')
                  ->references('id')->on('surat_keluars')
                  ->cascadeOnDelete();

            $table->unique(['surat_keluar_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluar_penerima');
    }
};
