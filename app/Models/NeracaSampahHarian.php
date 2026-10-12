<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NeracaSampahHarian extends Model
{
    protected $table = 'neraca_sampah_harian';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'organik_kg' => 'decimal:3',
        'anorganik_kg' => 'decimal:3',
        'residu_kg' => 'decimal:3',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(NeracaSampahLaporan::class, 'laporan_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(NeracaSampahArea::class, 'area_id');
    }
}