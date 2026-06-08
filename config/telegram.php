<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Notification Templates
    |--------------------------------------------------------------------------
    | Variables:
    |   {name}      – Nama penerima
    |   {nomor}     – Nomor surat
    |   {perihal}   – Perihal surat
    |   {pengirim}  – Nama pengirim
    |   {keterangan} – Keterangan disposisi
    */
    'templates' => [

        'surat_masuk' => env('TELEGRAM_TEMPLATE_SURAT_MASUK', implode("\n", [
            "📨 <b>Surat Masuk Baru</b>",
            "",
            "Halo {name},",
            "",
            "Anda menerima surat masuk:",
            "📄 <b>{perihal}</b>",
            "🔢 No. Surat: {nomor}",
            "👤 Pengirim: {pengirim}",
            "",
            "Silakan buka aplikasi " . env('APP_NAME', 'E-Office') . " untuk melihat detailnya.",
        ])),

        'disposisi_masuk' => env('TELEGRAM_TEMPLATE_DISPOSISI_MASUK', implode("\n", [
            "↻ <b>Disposisi Masuk Baru</b>",
            "",
            "Halo {name},",
            "",
            "Surat telah didisposisikan kepada Anda:",
            "📄 <b>{perihal}</b>",
            "🔢 No. Surat: {nomor}",
            "👤 Dari: {pengirim}",
            "📝 Keterangan: {keterangan}",
            "",
            "Silakan buka aplikasi " . env('APP_NAME', 'E-Office') . " untuk menindaklanjuti.",
        ])),
    ],
];
