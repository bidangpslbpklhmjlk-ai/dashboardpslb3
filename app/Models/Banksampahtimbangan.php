<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankSampahTimbangan extends Model
{
    protected $table = 'bank_sampah_timbangan';
    protected $fillable = ['bank_sampah_id', 'tahun', 'bulan', 'berat_kg'];
    protected $casts = ['berat_kg' => 'float'];

    public function bankSampah(): BelongsTo
    {
        return $this->belongsTo(BankSampah::class);
    }
}