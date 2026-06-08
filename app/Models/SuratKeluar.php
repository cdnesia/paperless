<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratKeluar extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id', 'nomor_surat', 'perihal', 'tanggal_surat',
        'jenis_surat', 'metode_surat', 'google_doc_id',
        'file_pdf', 'lampiran', 'dibaca', 'status', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'dibaca' => 'boolean',
            'tanggal_surat' => 'date',
            'sent_at' => 'datetime',
            'tujuan' => 'array',
            'jenis_surat' => 'string',
            'metode_surat' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penerima(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'surat_keluar_penerima')
            ->withPivot('dibaca', 'dibaca_at', 'status', 'alasan')
            ->withTimestamps();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SuratKeluarHistory::class);
    }

    public function logHistory(string $action, ?string $keterangan = null, ?array $data = null)
    {
        return $this->histories()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'keterangan' => $keterangan,
            'data' => $data,
        ]);
    }

    public function disposisis(): HasMany
    {
        return $this->hasMany(Disposisi::class);
    }

    public function tandaTanganDigital(): HasMany
    {
        return $this->hasMany(TandaTanganDigital::class);
    }

    /**
     * Dapatkan URL untuk mengakses file PDF surat.
     * Mengembalikan null jika tidak ada file_pdf.
     */
    public function pdfUrl(): ?string
    {
        if (!$this->file_pdf) {
            return null;
        }
        return route('files.serve', ['path' => $this->file_pdf]);
    }

    /**
     * Dapatkan URL untuk mengakses file lampiran.
     * Mengembalikan null jika tidak ada lampiran.
     */
    public function lampiranUrl(): ?string
    {
        if (!$this->lampiran) {
            return null;
        }
        return route('files.serve', ['path' => $this->lampiran]);
    }
}
