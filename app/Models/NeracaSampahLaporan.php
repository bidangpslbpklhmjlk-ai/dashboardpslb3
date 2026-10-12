<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class NeracaSampahLaporan extends Model
{
    protected $table = 'neraca_sampah_laporan';

    protected $guarded = ['id'];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'tanggal_ttd' => 'date',
    ];

    public function industri(): BelongsTo
    {
        return $this->belongsTo(Industri::class, 'industri_id');
    }

    public function harian(): HasMany
    {
        return $this->hasMany(NeracaSampahHarian::class, 'laporan_id');
    }

    /**
     * Daftar bulan dalam periode laporan.
     *
     * Format:
     * ['2025-07' => Carbon, ...]
     */
    public function bulanList(): array
    {
        $out = [];

        if (!$this->periode_mulai || !$this->periode_selesai) {
            return $out;
        }

        $c = $this->periode_mulai->copy()->startOfMonth();
        $akhir = $this->periode_selesai->copy()->startOfMonth();

        while ($c->lessThanOrEqualTo($akhir)) {
            $out[$c->format('Y-m')] = $c->copy();
            $c->addMonth();
        }

        return $out;
    }
}