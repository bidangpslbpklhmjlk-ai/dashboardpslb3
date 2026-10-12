<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuteAngkut extends Model
{
    protected $fillable = ['armada_id', 'nama', 'hari', 'total_jarak', 'route_geometry', 'aktif'];

    protected $casts = [
        'route_geometry' => 'array',
        'total_jarak'    => 'float',
        'aktif'          => 'boolean',
    ];

    public function armada()
    {
        return $this->belongsTo(Armada::class);
    }

    public function lokasis()
    {
        return $this->belongsToMany(LokasiAngkut::class, 'rute_angkut_lokasi')
            ->withPivot('urutan')
            ->orderBy('rute_angkut_lokasi.urutan');
    }
}