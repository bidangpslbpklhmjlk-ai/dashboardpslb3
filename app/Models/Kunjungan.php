<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';
    protected $fillable = ['object_retribusi_id', 'petugas_id', 'waktu_kunjungan', 'latitude', 'longitude', 'foto', 'catatan', 'status'];
    protected $casts = ['waktu_kunjungan' => 'datetime', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];

    public function objectRetribusi() { return $this->belongsTo(ObjectRetribusi::class); }
    public function petugas() { return $this->belongsTo(\App\Models\User::class, 'petugas_id'); }
    public function invoice() { return $this->hasOne(InvoiceObjectRetribusi::class); }
}