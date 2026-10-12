<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    protected $table = 'jenis_sampah';
    protected $fillable = ['nama', 'aktif'];
    protected $casts = ['aktif' => 'boolean'];
}