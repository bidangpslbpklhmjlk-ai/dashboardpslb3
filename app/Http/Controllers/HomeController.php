<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman utama dengan data berita terbaru.
     */
    public function index(): View
    {
        // Mengambil berita terbaru. 
        // Jika Anda ingin Carousel lebih bervariasi, Anda bisa menambah jumlahnya (misal: take(5))
        // atau menghapus take() jika ingin menampilkan semua.
        $berita = Berita::latest()->get();

        // Mengirimkan data ke view 'welcome'
        return view('welcome', compact('berita'));
    }
}