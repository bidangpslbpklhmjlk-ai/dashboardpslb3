<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenAkuntabilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AkuntabilitasController extends Controller
{
    public function index()
    {
        $items = DokumenAkuntabilitas::latest()->paginate(10);

        return view(
            'admin.akuntabilitas.index',
            compact('items')
        );
    }

    public function create()
    {
        return view('admin.akuntabilitas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|max:255',
            'jenis' => 'required',
            'tahun' => 'required',
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        $file = $request->file('file');

        $path = $file->store(
            'akuntabilitas',
            'public'
        );

        DokumenAkuntabilitas::create([
            'judul' => $request->judul,
            'jenis' => $request->jenis,
            'tahun' => $request->tahun,
            'nama_file' => $file->getClientOriginalName(),
            'path_file' => $path,
            'ukuran_file' => $file->getSize(),
            'jumlah_download' => 0,
            'is_publish' => $request->has('is_publish'),
            'created_by' => Auth::id()
        ]);

        return redirect()
            ->route('admin.akuntabilitas.index')
            ->with('success', 'Dokumen berhasil diupload');
    }
    public function edit($id)
{
    $item = DokumenAkuntabilitas::findOrFail($id);

    return view(
        'admin.akuntabilitas.edit',
        compact('item')
    );
}
public function update(Request $request, $id)
{
    $item = DokumenAkuntabilitas::findOrFail($id);

$request->validate([
    'judul' => 'required|max:255',
    'jenis' => 'required',
    'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 5),
    'file' => 'nullable|mimes:pdf|max:10240',
]);

    $data = [
        'judul' => $request->judul,
        'jenis' => $request->jenis,
        'tahun' => $request->tahun,
        'is_publish' => $request->has('is_publish')
    ];

    if ($request->hasFile('file')) {

if (
    $item->path_file &&
    Storage::disk('public')->exists($item->path_file)
) {
    Storage::disk('public')->delete(
        $item->path_file
    );
}

        $file = $request->file('file');

        $path = $file->store(
            'akuntabilitas',
            'public'
        );

        $data['nama_file'] = $file->getClientOriginalName();
        $data['path_file'] = $path;
        $data['ukuran_file'] = $file->getSize();
    }

    $item->update($data);

    return redirect()
        ->route('admin.akuntabilitas.index')
        ->with(
            'success',
            'Dokumen berhasil diperbarui'
        );
}
public function destroy($id)
{
    $item = DokumenAkuntabilitas::findOrFail($id);

    Storage::disk('public')
        ->delete($item->path_file);

    $item->delete();

    return redirect()
        ->route('admin.akuntabilitas.index')
        ->with(
            'success',
            'Dokumen berhasil dihapus'
        );
}
public function show($id)
{
    $item = DokumenAkuntabilitas::findOrFail($id);

    return redirect(
        asset('storage/' . $item->path_file)
    );
}
public function download($id)
{
    $item = DokumenAkuntabilitas::findOrFail($id);

    $item->increment('jumlah_download');

    return Storage::disk('public')
        ->download(
            $item->path_file,
            $item->nama_file
        );
}
}