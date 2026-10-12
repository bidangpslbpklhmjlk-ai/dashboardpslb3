<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenugasanHarian extends Model
{
    protected $table = 'penugasan_harian';
    protected $fillable = [
        'tanggal', 'rute_angkut_id', 'armada_id', 'pegawai_id',
         'armada_asal_id', 'status',
        'dialihkan', 'alasan_alih', 'catatan',
    ];
    protected $casts = ['tanggal' => 'date', 'dialihkan' => 'boolean'];

    public function rute(): BelongsTo { return $this->belongsTo(RuteAngkut::class, 'rute_angkut_id'); }
    public function armada(): BelongsTo { return $this->belongsTo(Armada::class); }
    public function armadaAsal(): BelongsTo { return $this->belongsTo(Armada::class, 'armada_asal_id'); }
    public function pegawai(): BelongsTo { return $this->belongsTo(Pegawai::class); }
    public function jadwalRencana(): BelongsTo { return $this->belongsTo(JadwalRencana::class); }
    public function checkins(): HasMany { return $this->hasMany(Checkin::class, 'penugasan_id'); }

    // Total berat per ritasi: [ritasi_ke => kg]
    public function getBeratPerRitasiAttribute(): array
    {
        return CheckinSampah::query()
            ->join('checkins', 'checkins.id', '=', 'checkin_sampah.checkin_id')
            ->where('checkins.penugasan_id', $this->id)
            ->whereNull('checkins.deleted_at')
            ->groupBy('checkins.ritasi_ke')
            ->selectRaw('checkins.ritasi_ke, SUM(checkin_sampah.berat_kg) as kg')
            ->pluck('kg', 'ritasi_ke')
            ->map(fn ($v) => (float) $v)
            ->all();
    }
}