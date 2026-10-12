<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jenis_perhitungan',
        'nominal',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function isPerRitasi(): bool
    {
        return $this->jenis_perhitungan === 'per_ritasi';
    }

    public function isPerTon(): bool
    {
        return $this->jenis_perhitungan === 'per_ton';
    }

    public function labelJenis(): string
    {
        return $this->isPerRitasi() ? 'Per Ritasi' : 'Per Ton';
    }
}