<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectRetribusi extends Model
{
    protected $table = 'object_retribusi';

    protected $fillable = [
        'kategori_object_retribusi_id',
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'telepon',
        'status',
        'is_kolektif',
        'jumlah_unit',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_kolektif' => 'boolean',
        'jumlah_unit' => 'integer',
    ];

    public function kategori() { return $this->belongsTo(KategoriObjectRetribusi::class, 'kategori_object_retribusi_id'); }

    public function pegawai()
    {
        return $this->belongsToMany(Pegawai::class, 'pegawai_object_retribusi')->withTimestamps();
    }

    public function kunjungan() { return $this->hasMany(Kunjungan::class); }
    public function invoices() { return $this->hasMany(InvoiceObjectRetribusi::class); }

    public function getTotalTagihanAttribute(): float
    {
        $tarif = $this->kategori->tarif ?? 0;

        return $this->is_kolektif
            ? $tarif * ($this->jumlah_unit ?? 0)
            : $tarif;
    }
}