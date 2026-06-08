<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Docs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleDocsService
{
    protected GoogleClient $client;
    protected Drive $drive;
    protected Docs $docs;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setAuthConfig(config('google.oauth.credentials_path'));
        $this->client->setRedirectUri(config('google.oauth.redirect_uri'));
        $this->client->addScope([
            Drive::DRIVE,
            Drive::DRIVE_FILE,
            'https://www.googleapis.com/auth/documents',
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        // Set access token from stored refresh token
        $this->setAccessToken();

        $this->drive = new Drive($this->client);
        $this->docs = new Docs($this->client);
    }

    /**
     * Set access token using stored refresh token.
     */
    protected function setAccessToken(): void
    {
        $refreshToken = config('google.oauth.refresh_token');

        if (!$refreshToken) {
            Log::warning('Google Refresh Token belum dikonfigurasi. Jalankan php artisan google:auth');
            return;
        }

        try {
            $tokenData = Cache::remember('google_access_token', 3500, function () use ($refreshToken) {
                $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                return $this->client->getAccessToken();
            });

            $this->client->setAccessToken($tokenData);

            // If token expired, refresh it
            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                $newToken = $this->client->getAccessToken();
                Cache::put('google_access_token', $newToken, 3500);
                $this->client->setAccessToken($newToken);
            }
        } catch (\Exception $e) {
            Log::error('Gagal set Google access token: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dapatkan URL untuk OAuth authorization.
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code untuk refresh token.
     */
    public function authenticateWithCode(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        $this->client->setAccessToken($token);

        return [
            'access_token' => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_in' => $token['expires_in'] ?? null,
        ];
    }

    /**
     * Buat dokumen Google Docs baru dari template DOCX.
     */
    public function createDocumentFromTemplate(string $title, ?string $folderId = null): string
    {
        $templatePath = config('google.docs.template_docx_path');

        if (!file_exists($templatePath)) {
            Log::warning('Template DOCX tidak ditemukan, buat dokumen kosong', ['path' => $templatePath]);
            return $this->createEmptyDocument($title, $folderId);
        }

        try {
            $fileMetadata = new Drive\DriveFile([
                'name' => $title,
                'mimeType' => 'application/vnd.google-apps.document',
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            $file = $this->drive->files->create(
                $fileMetadata,
                [
                    'data' => file_get_contents($templatePath),
                    'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'uploadType' => 'multipart',
                    'supportsAllDrives' => true,
                ]
            );

            $documentId = $file->getId();

            Log::info('Google Docs berhasil dibuat dari template', [
                'document_id' => $documentId,
                'title' => $title,
            ]);

            return $documentId;
        } catch (\Exception $e) {
            Log::error('Gagal membuat Google Docs dari template: ' . $e->getMessage(), [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return $this->createEmptyDocument($title, $folderId);
        }
    }

    /**
     * Buat dokumen Google Docs kosong.
     */
    public function createEmptyDocument(string $title, ?string $folderId = null): string
    {
        try {
            $document = new Docs\Document(['title' => $title]);
            $createdDoc = $this->docs->documents->create($document);
            $documentId = $createdDoc->documentId;

            if ($folderId) {
                $this->drive->files->update($documentId, new Drive\DriveFile(), [
                    'addParents' => $folderId,
                    'removeParents' => 'root',
                    'supportsAllDrives' => true,
                ]);
            }

            Log::info('Google Docs kosong berhasil dibuat', [
                'document_id' => $documentId,
                'title' => $title,
            ]);

            return $documentId;
        } catch (\Exception $e) {
            Log::error('Gagal membuat Google Docs kosong: ' . $e->getMessage(), [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update judul dokumen Google Docs.
     */
    public function updateDocumentTitle(string $documentId, string $newTitle): void
    {
        try {
            $file = new Drive\DriveFile();
            $file->setName($newTitle);
            $this->drive->files->update($documentId, $file, [
                'supportsAllDrives' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal update judul Google Docs: ' . $e->getMessage(), [
                'document_id' => $documentId,
            ]);
        }
    }

    /**
     * Hapus dokumen Google Docs.
     */
    public function deleteDocument(string $documentId): void
    {
        try {
            $this->drive->files->delete($documentId, [
                'supportsAllDrives' => true,
            ]);
            Log::info('Google Docs berhasil dihapus', ['document_id' => $documentId]);
        } catch (\Exception $e) {
            Log::error('Gagal hapus Google Docs: ' . $e->getMessage(), [
                'document_id' => $documentId,
            ]);
        }
    }

    /**
     * Dapatkan link edit Google Docs.
     */
    public function getEditLink(string $documentId): string
    {
        return "https://docs.google.com/document/d/{$documentId}/edit";
    }

    /**
     * Share dokumen Google Docs agar siapa saja dengan link bisa edit.
     */
    public function shareDocument(string $documentId): void
    {
        try {
            $permission = new Drive\Permission([
                'type' => 'anyone',
                'role' => 'writer',
            ]);

            $this->drive->permissions->create($documentId, $permission, [
                'supportsAllDrives' => true,
            ]);

            Log::info('Google Docs berhasil di-share ke publik (anyone with link can edit)', [
                'document_id' => $documentId,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal share Google Docs: ' . $e->getMessage(), [
                'document_id' => $documentId,
            ]);
        }
    }

    /**
     * Export Google Docs ke PDF dan simpan ke storage lokal.
     *
     * @return string Path file PDF yang tersimpan (relative ke public disk).
     */
    public function exportToPdf(string $documentId, string $outputPath): string
    {
        try {
            $content = $this->drive->files->export($documentId, 'application/pdf');

            $fullPath = storage_path("app/public/{$outputPath}");
            $dir = dirname($fullPath);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($fullPath, (string) $content->getBody());

            Log::info('Google Docs berhasil di-export ke PDF', [
                'document_id' => $documentId,
                'path' => $outputPath,
            ]);

            return $outputPath;
        } catch (\Exception $e) {
            Log::error('Gagal export Google Docs ke PDF: ' . $e->getMessage(), [
                'document_id' => $documentId,
            ]);
            throw $e;
        }
    }
}
