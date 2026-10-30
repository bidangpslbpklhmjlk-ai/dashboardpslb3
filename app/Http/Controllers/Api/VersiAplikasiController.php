<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VersiAplikasi;

class VersiAplikasiController extends Controller
{
    public function terbaru()
    {
        $v = VersiAplikasi::where('platform', 'android')
            ->where('aktif', true)
            ->orderByDesc('version_code')
            ->first();

        if (!$v) {
            return response()->json(['message' => 'Belum ada versi'], 404);
        }

        return response()->json([
            'version_code' => $v->version_code,
            'version_name' => $v->version_name,
            'download_url' => $v->download_url,
            'catatan'      => $v->catatan,
        ]);
    }
}