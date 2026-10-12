<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NeracaSampahArea extends Model
{
    protected $table = 'neraca_sampah_area';

    protected $guarded = ['id'];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    public function harian(): HasMany
    {
        return $this->hasMany(NeracaSampahHarian::class, 'area_id');
    }
}