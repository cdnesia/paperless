<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaSurat extends Model
{
    protected $table = 'surat_keluar_penerima';

    protected $fillable = [
        'surat_keluar_id',
        'user_id',
        'dibaca',
        'dibaca_at',
        'status',
        'alasan',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
        'dibaca_at' => 'datetime',
    ];

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
