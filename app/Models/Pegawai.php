<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nip',
        'jabatan_id',
        'no_telepon',
        'alamat',
        'status',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function objectRetribusi()
    {
        return $this->belongsToMany(ObjectRetribusi::class, 'pegawai_object_retribusi')->withTimestamps();
    }

    /**
     * Pegawai yang boleh ditampilkan: yang belum punya akun (data lama)
     * atau yang akunnya BUKAN superadmin.
     */
    public function scopeTampil($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('user_id')
              ->orWhereHas('user', fn ($u) => $u->where('role', '!=', 'superadmin'));
        });
    }
}