<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TandaTanganDigital extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'tanda_tangan_digital';

    protected $fillable = [
        'surat_keluar_id',
        'user_id',
        'hash_sha256_original',
        'hash_sha256_final',
        'qr_code',
        'file_pdf_final',
        'penandatangan',
        'lokasi',
        'ip_address',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function suratKeluar(): BelongsTo
    {
        return $this->belongsTo(SuratKeluar::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hapus file terkait saat record dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (TandaTanganDigital $signature) {
            $signature->deleteFiles();
        });
    }

    /**
     * Hapus semua file fisik yang terkait dengan tanda tangan ini.
     */
    public function deleteFiles(): void
    {
        $files = array_filter([
            $this->qr_code,
            $this->file_pdf_final,
        ]);

        foreach ($files as $file) {
            if (Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    /**
     * Dapatkan URL untuk mengakses file PDF final (yang sudah ditandatangani).
     * Mengembalikan null jika tidak ada file_pdf_final.
     */
    public function pdfFinalUrl(): ?string
    {
        if (!$this->file_pdf_final) {
            return null;
        }
        return route('files.serve', ['path' => $this->file_pdf_final]);
    }
}
