<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitKerja extends Model
{
    protected $fillable = ['kode', 'nama', 'deskripsi'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
