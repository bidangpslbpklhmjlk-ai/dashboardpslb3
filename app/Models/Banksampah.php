<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankSampah extends Model
{
    protected $table = 'bank_sampah';

    protected $fillable = [
        'nama', 'kode_kecamatan', 'kode_desa', 'jenis', 'aktif',
        'ketua', 'no_hp', 'email', 'tenaga_pria', 'tenaga_wanita',
        'legalitas_jenis', 'legalitas_keterangan', 'legalitas_tanggal', 'catatan',
    ];

    protected $casts = [
        'aktif'             => 'boolean',
        'legalitas_tanggal' => 'date',
    ];

    public function timbangan(): HasMany
    {
        return $this->hasMany(BankSampahTimbangan::class);
    }
}