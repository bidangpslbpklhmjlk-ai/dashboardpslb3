<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berita extends Model
{
    use HasFactory;

    // 1. Agar data bisa di-update via $berita->update([...])
    protected $fillable = [
        'title', 
        'category', 
        'content', 
        'image' // untuk thumbnail utama
    ];

    // 2. Relasi ke tabel gambar carousel (Fix error foreach)
    public function images()
    {
        return $this->hasMany(BeritaImage::class);
    }
}