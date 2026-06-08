<?php

namespace App\Console\Commands;

use App\Services\GoogleDocsService;
use Illuminate\Console\Command;

class GoogleAuth extends Command
{
    protected $signature = 'google:auth';
    protected $description = 'Setup Google OAuth 2.0 untuk mendapatkan refresh token';

    public function handle(GoogleDocsService $googleDocs)
    {
        $this->info('=== Google OAuth Setup ===');
        $this->newLine();

        // Cek apakah refresh token sudah ada
        $refreshToken = config('google.oauth.refresh_token');
        if ($refreshToken) {
            if (!$this->confirm('Refresh token sudah terkonfigurasi. Apakah ingin mendapatkan ulang?', false)) {
                $this->info('Setup dibatalkan.');
                return 0;
            }
        }

        $authUrl = $googleDocs->getAuthUrl();

        $this->info('1. Buka URL berikut di browser:');
        $this->newLine();
        $this->line($authUrl);
        $this->newLine();

        $this->info('2. Login dengan Google Account yang akan digunakan.');
        $this->info('3. Setelah login, akan muncul redirect ke localhost (gagal, itu normal).');
        $this->info('4. Copy parameter "code" dari URL redirect tersebut.');
        $this->newLine();

        $code = $this->ask('Paste authorization code di sini');

        if (!$code) {
            $this->error('Authorization code tidak boleh kosong.');
            return 1;
        }

        try {
            $token = $googleDocs->authenticateWithCode($code);

            if (!$token['refresh_token']) {
                $this->warn('Tidak mendapatkan refresh token. Pastikan akun Google sudah pernah memberikan izin.');
                $this->warn('Coba hapus izin aplikasi di https://myaccount.google.com/permissions lalu ulangi.');
                return 1;
            }

            // Simpan refresh token ke .env
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            if (str_contains($envContent, 'GOOGLE_OAUTH_REFRESH_TOKEN=')) {
                $envContent = preg_replace(
                    '/GOOGLE_OAUTH_REFRESH_TOKEN=.*/',
                    'GOOGLE_OAUTH_REFRESH_TOKEN=' . $token['refresh_token'],
                    $envContent
                );
            } else {
                $envContent .= "\nGOOGLE_OAUTH_REFRESH_TOKEN=" . $token['refresh_token'] . "\n";
            }

            file_put_contents($envPath, $envContent);

            $this->newLine();
            $this->info('✅ Refresh token berhasil disimpan ke .env!');
            $this->line('GOOGLE_OAUTH_REFRESH_TOKEN=' . $token['refresh_token']);
            $this->newLine();
            $this->info('Sekarang Google Docs API siap digunakan.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Gagal mendapatkan token: ' . $e->getMessage());
            return 1;
        }
    }
}
