<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinSampah extends Model
{
    protected $table = 'checkin_sampah';
    protected $fillable = ['checkin_id', 'jenis_sampah_id', 'berat_kg'];
    protected $casts = ['berat_kg' => 'decimal:2'];

    public function checkin(): BelongsTo { return $this->belongsTo(Checkin::class); }
    public function jenisSampah(): BelongsTo { return $this->belongsTo(JenisSampah::class); }
}