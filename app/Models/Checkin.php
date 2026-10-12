<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'penugasan_id', 'ritasi_ke', 'lokasi_angkut_id', 'waktu_kejadian',
        'latitude', 'longitude', 'akurasi_gps', 'sumber', 'dicatat_oleh',
        'alasan_manual', 'flag', 'client_uuid', 'catatan',
    ];
    protected $casts = ['waktu_kejadian' => 'datetime', 'flag' => 'array'];

    public function penugasan(): BelongsTo { return $this->belongsTo(PenugasanHarian::class, 'penugasan_id'); }
    public function lokasi(): BelongsTo { return $this->belongsTo(LokasiAngkut::class, 'lokasi_angkut_id'); }
    public function pencatat(): BelongsTo { return $this->belongsTo(User::class, 'dicatat_oleh'); }
    public function sampah(): HasMany { return $this->hasMany(CheckinSampah::class); }

    // Dihitung, tidak disimpan
    public function getTotalBeratKgAttribute(): float
    {
        return (float) $this->sampah()->sum('berat_kg');
    }
    public function fotos(): HasMany { return $this->hasMany(CheckinFoto::class); }
}