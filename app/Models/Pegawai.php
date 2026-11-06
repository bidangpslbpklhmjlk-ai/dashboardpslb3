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

    /** Armada yang sopir defaultnya pegawai ini. */
    public function armadas()
    {
        return $this->hasMany(Armada::class, 'pegawai_id');
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

    /**
     * Pegawai yang boleh dipilih sebagai sopir armada:
     * aktif, berjabatan Sopir, dan punya akun pengguna yang aktif.
     */
    public function scopeSopirBerakun($query)
    {
        return $query->where('status', 'aktif')
            ->whereHas('jabatan', fn ($j) => $j->sopir())
            ->whereHas('user', fn ($u) => $u->where('is_active', true));
    }

    /**
     * Alasan pegawai ini tidak memenuhi syarat sopir, atau null kalau memenuhi.
     * Pakai relasi jabatan dan user yang sudah dimuat (with) agar tidak menambah query.
     */
    public function masalahSopir(): ?string
    {
        if (! $this->jabatan || ! $this->jabatan->isSopir()) {
            return 'jabatan bukan Sopir';
        }
        if (! $this->user_id || ! $this->user) {
            return 'belum punya akun';
        }
        if (! $this->user->is_active) {
            return 'akun nonaktif';
        }
        if ($this->status !== 'aktif') {
            return 'pegawai nonaktif';
        }

        return null;
    }
}