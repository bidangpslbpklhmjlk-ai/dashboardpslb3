<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DokumenAkuntabilitas extends Model
{
    use HasFactory;

    protected $table = 'dokumen_akuntabilitas';

    protected $fillable = [
        'judul',
        'jenis',
        'tahun',
        'nama_file',
        'path_file',
        'ukuran_file',
        'jumlah_download',
        'is_publish',
        'created_by'
    ];

    protected $casts = [
        'is_publish' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}