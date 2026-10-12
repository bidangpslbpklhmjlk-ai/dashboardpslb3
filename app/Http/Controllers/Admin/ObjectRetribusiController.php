<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObjectRetribusi;
use App\Models\KategoriObjectRetribusi;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class ObjectRetribusiController extends Controller
{
    public function index(Request $request)
    {
        $query = ObjectRetribusi::with(['kategori', 'pegawai']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_object_retribusi_id', $request->kategori_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pegawai_id')) {
            $query->whereHas('pegawai', fn ($q) => $q->where('pegawais.id', $request->pegawai_id));
        }

        $objectRetribusi = $query->latest()->paginate(15)->withQueryString();
        $kategoriList = KategoriObjectRetribusi::orderBy('urutan')->orderBy('id')->get();
        $pegawaiList = Pegawai::where('status', 'aktif')->orderBy('nama_lengkap')->get();

        return view('admin.object-retribusi.index', compact('objectRetribusi', 'kategoriList', 'pegawaiList'));
    }

    public function create()
    {
        $kategoriList = KategoriObjectRetribusi::orderBy('urutan')->orderBy('id')->get();
        $pegawaiList = Pegawai::where('status', 'aktif')->orderBy('nama_lengkap')->get();

        return view('admin.object-retribusi.create', compact('kategoriList', 'pegawaiList'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $pegawaiIds = $validated['pegawai_id'] ?? [];
        unset($validated['pegawai_id']);

        $objectRetribusi = ObjectRetribusi::create($validated);
        $objectRetribusi->pegawai()->sync($pegawaiIds);

        return redirect()->route('admin.object-retribusi.index')
            ->with('success', 'Object Retribusi berhasil didaftarkan');
    }

    public function edit(ObjectRetribusi $objectRetribusi)
    {
        $kategoriList = KategoriObjectRetribusi::orderBy('urutan')->orderBy('id')->get();
        $pegawaiList = Pegawai::where('status', 'aktif')->orderBy('nama_lengkap')->get();

        return view('admin.object-retribusi.edit', compact('objectRetribusi', 'kategoriList', 'pegawaiList'));
    }

    public function update(Request $request, ObjectRetribusi $objectRetribusi)
    {
        $validated = $this->validated($request);
        $pegawaiIds = $validated['pegawai_id'] ?? [];
        unset($validated['pegawai_id']);

        $objectRetribusi->update($validated);
        $objectRetribusi->pegawai()->sync($pegawaiIds);

        return redirect()->route('admin.object-retribusi.index')
            ->with('success', 'Data object retribusi berhasil diperbarui');
    }

    public function destroy(ObjectRetribusi $objectRetribusi)
    {
        if ($objectRetribusi->kunjungan()->exists()) {
            return redirect()->route('admin.object-retribusi.index')
                ->with('error', 'Object Retribusi tidak bisa dihapus karena sudah punya riwayat kunjungan/pungutan');
        }

        $objectRetribusi->delete();

        return redirect()->route('admin.object-retribusi.index')
            ->with('success', 'Object Retribusi berhasil dihapus');
    }

    private function validated(Request $request): array
    {
        $request->merge([
            'is_kolektif' => $request->boolean('is_kolektif'),
        ]);

        return $request->validate([
            'kategori_object_retribusi_id' => 'required|exists:kategori_object_retribusi,id',
            'pegawai_id' => 'nullable|array',
            'pegawai_id.*' => 'exists:pegawais,id',
            'nama' => 'required|string|max:150',
            'alamat' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'telepon' => 'nullable|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
            'is_kolektif' => 'boolean',
            'jumlah_unit' => 'nullable|required_if:is_kolektif,true|integer|min:1',
        ]);
    }
}