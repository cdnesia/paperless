<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique();
            $table->text('nilai')->nullable();
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        // Seed default values
        DB::table('pengaturans')->insert([
            ['kunci' => 'telegram_bot_token', 'nilai' => env('TELEGRAM_BOT_TOKEN', ''), 'deskripsi' => 'Token Bot Telegram untuk notifikasi'],
            ['kunci' => 'telegram_tpl_surat_masuk', 'nilai' => null, 'deskripsi' => 'Template notifikasi surat masuk Telegram'],
            ['kunci' => 'telegram_tpl_disposisi_masuk', 'nilai' => null, 'deskripsi' => 'Template notifikasi disposisi masuk Telegram'],
            ['kunci' => 'telegram_notif_surat_masuk', 'nilai' => '1', 'deskripsi' => 'Aktifkan notifikasi surat masuk (1=ya, 0=tidak)'],
            ['kunci' => 'telegram_notif_disposisi_masuk', 'nilai' => '1', 'deskripsi' => 'Aktifkan notifikasi disposisi masuk (1=ya, 0=tidak)'],
            ['kunci' => 'app_nama', 'nilai' => env('APP_NAME', 'E-Office'), 'deskripsi' => 'Nama aplikasi'],
            ['kunci' => 'app_deskripsi', 'nilai' => 'Sistem Informasi Persuratan Elektronik', 'deskripsi' => 'Deskripsi singkat aplikasi'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturans');
    }
};
