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
        try {
            $item = static::where('kunci', $kunci)->first();
            return $item ? $item->nilai : $default;
        } catch (\Illuminate\Database\QueryException $e) {
            return $default;
        }
    }

    /**
     * Set nilai by kunci (upsert).
     */
    public static function atur(string $kunci, mixed $nilai): void
    {
        try {
            static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist yet — silently ignore
        }
    }

    /**
     * Get multiple values at once.
     */
    public static function dapatkanBanyak(array $kuncis): array
    {
        try {
            return static::whereIn('kunci', $kuncis)->pluck('nilai', 'kunci')->toArray();
        } catch (\Illuminate\Database\QueryException $e) {
            return [];
        }
    }
}
