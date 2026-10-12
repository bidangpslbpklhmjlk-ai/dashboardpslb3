<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriObjectRetribusi;

class TarifController extends Controller
{
    /** Kelompok kategori yang ditampilkan sebagai tarif */
    private const KELOMPOK_TARIF = [
        'Pembuangan Sampah Langsung ke TPA',
        'Pembuangan Sampah Langsung ke TPSS',
    ];

    public function index()
    {
        $kelompoks = KategoriObjectRetribusi::whereIn('kelompok', self::KELOMPOK_TARIF)
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get()
            ->groupBy('kelompok');

        return view('admin.tarif.index', compact('kelompoks'));
    }
}