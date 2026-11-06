<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    /** Nama jabatan yang dianggap sopir (huruf besar/kecil tidak berpengaruh). Ubah di sini kalau namanya berbeda. */
    public const NAMA_SOPIR = 'Sopir';

    protected $fillable = [
        'nama_jabatan',
        'keterangan',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function scopeSopir($query)
    {
        return $query->whereRaw('LOWER(TRIM(nama_jabatan)) = ?', [mb_strtolower(self::NAMA_SOPIR)]);
    }

    public function isSopir(): bool
    {
        return mb_strtolower(trim((string) $this->nama_jabatan)) === mb_strtolower(self::NAMA_SOPIR);
    }
}