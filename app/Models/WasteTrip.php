<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteTrip extends Model {
    use HasFactory;

    protected $fillable = [
        'tanggal', 'nomor_polisi', 'jenis_kendaraan',
        'jam_mulai', 'jam_selesai', 
        'start_lat', 'start_lng', 'start_nama',
        'start_desa', 'start_kecamatan', 'start_kabupaten', 'start_provinsi',
        'pickup_points', 'route_geometry',
        'end_lat', 'end_lng', 'end_nama',
        'end_desa', 'end_kecamatan', 'end_kabupaten', 'end_provinsi',
        'total_jarak'
    ];

    protected $casts = [
        'pickup_points' => 'array',
        'route_geometry' => 'array',
        'tanggal' => 'date',
        'start_lat' => 'float',
        'start_lng' => 'float',
        'end_lat' => 'float',
        'end_lng' => 'float',
        'total_jarak' => 'float',
    ];

    /**
     * Get full address string for start point
     */
    public function getStartAddressAttribute(): string
    {
        $parts = array_filter([
            $this->start_desa,
            $this->start_kecamatan,
            $this->start_kabupaten,
            $this->start_provinsi
        ]);
        return implode(', ', $parts);
    }

    /**
     * Get full address string for end point
     */
    public function getEndAddressAttribute(): string
    {
        $parts = array_filter([
            $this->end_desa,
            $this->end_kecamatan,
            $this->end_kabupaten,
            $this->end_provinsi
        ]);
        return implode(', ', $parts);
    }
}