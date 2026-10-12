<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriObjectRetribusi extends Model
{
    protected $table = 'kategori_object_retribusi';

    protected $fillable = ['kelompok', 'kode', 'nama', 'tarif', 'satuan', 'urutan'];

    protected $casts = ['tarif' => 'decimal:2'];

    public const SATUAN = [
        'angkut'      => 'Per Angkut',
        'bulan'       => 'Per Bulan',
        'hari'        => 'Per Hari',
        'kamar_bulan' => 'Per Kamar / Bulan',
    ];

    public function objectRetribusi()
    {
        return $this->hasMany(ObjectRetribusi::class, 'kategori_object_retribusi_id');
    }

    public function getSatuanLabelAttribute(): string
    {
        return self::SATUAN[$this->satuan] ?? $this->satuan;
    }
}