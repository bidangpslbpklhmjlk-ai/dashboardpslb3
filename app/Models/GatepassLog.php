<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatepassLog extends Model
{
    use HasFactory;

protected $fillable = [
    'tipe_kendaraan',
    'nomor_polisi',
    'jenis_muatan',
    'armada_id',
    'volume_ton',
    'ritasi',
    'waktu_masuk',
    'photo',
];
    protected $casts = [
        'waktu_masuk' => 'datetime',
        'volume_ton' => 'decimal:2',
    ];

    public function armada()      // ← ganti dari perusahaan()
    {
        return $this->belongsTo(Armada::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function hasInvoice()
    {
        return $this->invoice()->exists();
    }

    public function needsInvoice()
    {
        return $this->armada && !$this->armada->isGratis() && !$this->hasInvoice();
    }
}