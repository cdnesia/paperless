<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nomor_surat')->nullable()->unique();
            $table->string('perihal');
            $table->date('tanggal_surat')->nullable();
            $table->enum('jenis_surat', ['internal', 'eksternal', 'broadcast'])->default('internal');
            $table->enum('metode_surat', ['upload', 'gdocs'])->default('upload');
            $table->string('google_doc_id')->nullable();
            $table->text('file_pdf')->nullable();
            $table->text('lampiran')->nullable();
            $table->boolean('dibaca')->default(false);
            $table->enum('status', ['d', 'a', 's', 'e'])->default('d');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluars');
    }
};
