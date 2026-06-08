<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturans';
    protected $fillable = ['kunci', 'nilai', 'deskripsi'];

    /**
     * Get nilai by kunci.
     */
    public static function dapatkan(string $kunci, mixed $default = null): mixed
    {
        $item = static::where('kunci', $kunci)->first();
        return $item ? $item->nilai : $default;
    }

    /**
     * Set nilai by kunci (upsert).
     */
    public static function atur(string $kunci, mixed $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    /**
     * Get multiple values at once.
     */
    public static function dapatkanBanyak(array $kuncis): array
    {
        return static::whereIn('kunci', $kuncis)->pluck('nilai', 'kunci')->toArray();
    }
}
