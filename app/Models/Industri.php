<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Industri extends Model
{
    protected $table = 'industri';

    protected $guarded = ['id'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function laporan(): HasMany
    {
        return $this->hasMany(NeracaSampahLaporan::class, 'industri_id');
    }
}