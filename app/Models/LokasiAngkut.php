<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiAngkut extends Model
{
    use HasFactory;

    protected $table = 'lokasi_angkuts';

    protected $fillable = [
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'keterangan',
        'aktif',
        'kecamatan',
        'desa',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function getMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function armadas()
{
    return $this->belongsToMany(Armada::class, 'armada_lokasi_angkut')
        ->withPivot('urutan');
}
public function getWilayahAttribute(): string
{
    return implode(', ', array_filter([$this->desa, $this->kecamatan]));
}

public function ruteAngkuts()
{
    return $this->belongsToMany(RuteAngkut::class, 'rute_angkut_lokasi', 'lokasi_angkut_id', 'rute_angkut_id')
        ->withPivot('urutan');
}
}