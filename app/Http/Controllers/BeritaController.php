<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\View\View;

class BeritaController extends Controller
{
    /**
     * Menampilkan detail berita berdasarkan ID.
     */
    public function show($id): View
    {
        // Mengambil berita atau menampilkan 404 jika tidak ditemukan
        $berita = Berita::findOrFail($id);

        return view('berita.show', compact('berita'));
    }
}