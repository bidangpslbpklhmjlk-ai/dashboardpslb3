<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinPengecualian extends Model
{
    protected $table = 'checkin_pengecualian';
    protected $fillable = [
        'tanggal', 'armada_id', 'lokasi_angkut_id', 'status',
        'armada_pengganti_id', 'alasan', 'dicatat_oleh',
    ];
    protected $casts = ['tanggal' => 'date'];

    public function armada(): BelongsTo { return $this->belongsTo(Armada::class); }
    public function pengganti(): BelongsTo { return $this->belongsTo(Armada::class, 'armada_pengganti_id'); }
    public function lokasi(): BelongsTo { return $this->belongsTo(LokasiAngkut::class, 'lokasi_angkut_id'); }
}