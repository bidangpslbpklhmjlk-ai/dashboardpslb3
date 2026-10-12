<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ArmadaKerusakan extends Model
{
    protected $table = 'armada_kerusakan';
    protected $fillable = ['armada_id', 'tanggal_mulai', 'tanggal_selesai', 'status', 'keterangan'];
    protected $casts = ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];

    public function armada() { return $this->belongsTo(Armada::class); }
    public function penugasan() { return $this->hasMany(PenugasanHarian::class, 'kerusakan_id'); }

    /** Status batal diabaikan. */
    public function scopeEfektif($q) { return $q->whereIn('status', ['rusak', 'selesai']); }

    public function mencakup(Carbon $d): bool
    {
        return $this->tanggal_mulai->lessThanOrEqualTo($d)
            && ($this->tanggal_selesai === null || $this->tanggal_selesai->greaterThanOrEqualTo($d));
    }
}