<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposisi extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'disposisis';

    protected $fillable = [
        'surat_keluar_id',
        'pengguna_id',
        'pengirim_id',
        'keterangan',
        'alasan',
        'status',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    /**
     * Get the outgoing letter
     */
    public function suratKeluar(): BelongsTo
    {
        return $this->belongsTo(SuratKeluar::class);
    }

    /**
     * Get the recipient user
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    /**
     * Get the sender user (who forwarded this)
     */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}

