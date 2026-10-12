<?php

namespace App\Http\Controllers\Admin; // WAJIB: Sesuaikan namespace

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    // 1. Menampilkan Halaman List Berita
    public function index()
    {
        $items = Berita::latest()->get();
        return view('admin.berita.index', compact('items'));
    }

    // 2. Menampilkan Halaman Form (Mode Tambah Baru)
    public function create()
    {
        $item = new Berita(); 
        return view('admin.berita.form', compact('item'));
    }

    // 3. Memproses Data Tambah Berita Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|max:255',
            'content'  => 'required',
            'category' => 'required',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('berita', 'public');
        }

        Berita::create($validated);
        return redirect()->route('admin.berita.index')->with('success', 'Berita berhasil ditambah!');
    }

    // 4. Menampilkan Halaman Form (Mode Edit dengan Data Lama)
    public function edit($id)
    {
        $item = Berita::findOrFail($id);
        return view('admin.berita.form', compact('item'));
    }

    // 5. Memproses Data Perubahan/Edit Berita
    public function update(Request $request, $id)
    {
        $berita = Berita::findOrFail($id);
        
        $validated = $request->validate([
            'title'    => 'required|max:255',
            'content'  => 'required',
            'category' => 'required',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada gambar baru
            if ($berita->image) {
                Storage::disk('public')->delete($berita->image);
            }
            $validated['image'] = $request->file('image')->store('berita', 'public');
        }

        $berita->update($validated);
        return redirect()->route('admin.berita.index')->with('success', 'Berita berhasil diperbarui!');
    }

    // 6. Menghapus Data Berita
    public function destroy($id)
    {
        $berita = Berita::findOrFail($id);
        
        if ($berita->image) {
            Storage::disk('public')->delete($berita->image);
        }
        
        $berita->delete();
        return redirect()->route('admin.berita.index')->with('success', 'Berita berhasil dihapus!');
    }
}