<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Google API (Docs & Drive)
    | Menggunakan OAuth 2.0 dengan refresh token.
    |
    | Cara setup:
    | 1. Set GOOGLE_OAUTH_CREDENTIALS_PATH di .env
    | 2. Jalankan: php artisan google:auth
    | 3. Buka URL yang muncul, login ke Google, copy code
    | 4. Paste code ke terminal
    | 5. Refresh token akan tersimpan di .env
    |
    */
    'oauth' => [
        'credentials_path' => env('GOOGLE_OAUTH_CREDENTIALS_PATH', storage_path('app/private/client_secret_89513022741-puk09lda7s3pv0ldeu6jgip9h7g2le82.apps.googleusercontent.com.json')),
        'redirect_uri' => env('GOOGLE_OAUTH_REDIRECT_URI', 'http://localhost:8000/google/callback'),
        'refresh_token' => env('GOOGLE_OAUTH_REFRESH_TOKEN'),
    ],
    'docs' => [
        'template_file_id' => env('GOOGLE_DOCS_TEMPLATE_ID', null),
        'template_docx_path' => env('GOOGLE_DOCS_TEMPLATE_DOCX', storage_path('app/private/template_surat.docx')),
    ],
];
