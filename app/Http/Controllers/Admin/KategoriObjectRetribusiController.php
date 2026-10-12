<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriObjectRetribusi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriObjectRetribusiController extends Controller
{
    public function index()
    {
        $kategoris = KategoriObjectRetribusi::withCount('objectRetribusi')
            // entri BUKAN kolektif dihitung 1 unit per entri
            ->withCount(['objectRetribusi as object_retribusi_individu_count' => fn ($q) => $q->where('is_kolektif', false)])
            // entri kolektif: jumlahkan kolom jumlah_unit
            ->withSum(['objectRetribusi as total_unit_kolektif' => fn ($q) => $q->where('is_kolektif', true)], 'jumlah_unit')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->groupBy('kelompok');

        return view('admin.kategori-object-retribusi.index', compact('kategoris'));
    }

    public function create()
    {
        return view('admin.kategori-object-retribusi.create', [
            'kelompokList' => $this->kelompokList(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['urutan'] = (KategoriObjectRetribusi::max('urutan') ?? 0) + 1;

        KategoriObjectRetribusi::create($validated);

        return redirect()->route('admin.kategori-object-retribusi.index')
            ->with('success', 'Kategori object retribusi berhasil ditambahkan');
    }

    public function edit(KategoriObjectRetribusi $kategoriObjectRetribusi)
    {
        return view('admin.kategori-object-retribusi.edit', [
            'kategoriObjectRetribusi' => $kategoriObjectRetribusi,
            'kelompokList'            => $this->kelompokList(),
        ]);
    }

    public function update(Request $request, KategoriObjectRetribusi $kategoriObjectRetribusi)
    {
        $kategoriObjectRetribusi->update($request->validate($this->rules($kategoriObjectRetribusi->id)));

        return redirect()->route('admin.kategori-object-retribusi.index')
            ->with('success', 'Kategori object retribusi berhasil diperbarui');
    }

    public function destroy(KategoriObjectRetribusi $kategoriObjectRetribusi)
    {
        if ($kategoriObjectRetribusi->objectRetribusi()->exists()) {
            return redirect()->route('admin.kategori-object-retribusi.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh object retribusi terdaftar');
        }

        $kategoriObjectRetribusi->delete();

        return redirect()->route('admin.kategori-object-retribusi.index')
            ->with('success', 'Kategori object retribusi berhasil dihapus');
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'kelompok' => ['required', 'string', 'max:150'],
            'kode'     => ['required', 'string', 'max:20',
                Rule::unique('kategori_object_retribusi', 'kode')->ignore($ignoreId)],
            'nama'     => ['required', 'string', 'max:150'],
            'tarif'    => ['required', 'numeric', 'min:0'],
            'satuan'   => ['required', Rule::in(array_keys(KategoriObjectRetribusi::SATUAN))],
        ];
    }

    private function kelompokList()
    {
        return KategoriObjectRetribusi::query()->distinct()->orderBy('kelompok')->pluck('kelompok');
    }
}