<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VersiAplikasi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VersiAplikasiController extends Controller
{
    public function index()
    {
        $versi = VersiAplikasi::where('platform', 'android')
            ->orderByDesc('version_code')
            ->paginate(15);

        // Versi yang saat ini dikirim ke aplikasi
        $terbaru = VersiAplikasi::where('platform', 'android')
            ->where('aktif', true)
            ->max('version_code');

        return view('admin.versi-aplikasi.index', compact('versi', 'terbaru'));
    }

    public function create()
    {
        return view('admin.versi-aplikasi.create');
    }

    public function store(Request $request)
    {
        VersiAplikasi::create($this->validated($request) + ['platform' => 'android']);

        return redirect()->route('admin.versi-aplikasi.index')
            ->with('success', 'Versi aplikasi berhasil ditambahkan');
    }

    public function edit(VersiAplikasi $versiAplikasi)
    {
        return view('admin.versi-aplikasi.edit', ['versi' => $versiAplikasi]);
    }

    public function update(Request $request, VersiAplikasi $versiAplikasi)
    {
        $versiAplikasi->update($this->validated($request, $versiAplikasi));

        return redirect()->route('admin.versi-aplikasi.index')
            ->with('success', 'Versi aplikasi berhasil diperbarui');
    }

    public function destroy(VersiAplikasi $versiAplikasi)
    {
        $versiAplikasi->delete();

        return redirect()->route('admin.versi-aplikasi.index')
            ->with('success', 'Versi aplikasi berhasil dihapus');
    }

    private function validated(Request $request, ?VersiAplikasi $versi = null): array
    {
        $request->merge([
            'aktif'        => $request->boolean('aktif'),
            'download_url' => $this->normalisasiLink($request->input('download_url')),
        ]);

        return $request->validate([
            'version_code' => [
                'required', 'integer', 'min:1', 'max:2000000000',
                Rule::unique('versi_aplikasi', 'version_code')
                    ->where('platform', 'android')
                    ->ignore($versi?->id),
            ],
            'version_name' => 'required|string|max:30',
            'download_url' => 'required|url|max:500',
            'catatan'      => 'nullable|string|max:1000',
            'aktif'        => 'boolean',
        ]);
    }

    /**
     * Link "Bagikan" Google Drive otomatis diubah jadi link unduh langsung.
     */
    private function normalisasiLink(?string $url): string
    {
        $url = trim((string) $url);

        if (preg_match('~drive\.google\.com/file/d/([\w-]+)~', $url, $m)
            || preg_match('~drive\.google\.com/(?:open|uc)\?(?:[^#]*&)?id=([\w-]+)~', $url, $m)) {
            return 'https://drive.google.com/uc?export=download&id=' . $m[1];
        }

        return $url;
    }
}