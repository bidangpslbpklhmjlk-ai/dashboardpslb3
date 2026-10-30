<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersiAplikasi extends Model
{
    protected $table = 'versi_aplikasi';

    protected $fillable = [
        'platform', 'version_code', 'version_name',
        'download_url', 'catatan', 'aktif',
    ];

    protected $casts = ['aktif' => 'boolean'];
}