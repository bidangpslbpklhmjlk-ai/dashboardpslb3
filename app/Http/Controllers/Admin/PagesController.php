<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page; 
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// UBAH NAMA CLASS MENJADI PagesController (jamak)
class PagesController extends Controller
{
    // 1. Tampilkan List Halaman
    public function index()
    {
        $items = Page::latest()->get();
        return view('admin.pages.index', compact('items'));
    }

    // 2. Masuk ke Form Tambah Halaman
    public function create()
    {
        $item = new Page();
        return view('admin.pages.form', compact('item'));
    }

    // 3. Simpan Halaman Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
            'category' => 'required', // Tambahkan validasi category jika Anda memasukkannya di form
        ]);

        $validated['slug'] = Str::slug($request->title);

        Page::create($validated);
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dibuat!');
    }

    // 4. Masuk ke Form Edit Halaman
    public function edit($id)
    {
        $item = Page::findOrFail($id);
        return view('admin.pages.form', compact('item'));
    }

    // 5. Update Data Halaman
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
            'category' => 'required',
        ]);

        $validated['slug'] = Str::slug($request->title);

        $page->update($validated);
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil diperbarui!');
    }

    // 6. Hapus Halaman
    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dihapus!');
    }

    public function uploadImage(Request $request)
{
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('uploads/pages', 'public');
        return response()->json(['url' => asset('storage/' . $path)]);
    }
}
public function show($category, $slug)
{
    // Mengambil halaman berdasarkan kategori dan slug
    $page = Page::where('category', $category)
                ->where('slug', $slug)
                ->firstOrFail();

    // Pastikan view 'pages.show' ada di resources/views/pages/show.blade.php
    return view('pages.show', compact('page'));
}
}