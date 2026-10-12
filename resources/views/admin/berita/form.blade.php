@extends('layouts.admin')

@section('content')
<div class="p-8 max-w-7xl mx-auto" x-data="{ imagePreview: '{{ $item->image ? asset('storage/'.$item->image) : '' }}' }">
    <div class="flex items-center justify-between mb-8">
        <div>
            {{-- Mengubah Judul Otomatis --}}
            <h1 class="text-2xl font-bold text-slate-900">{{ $item->exists ? 'Edit Berita' : 'Tambah Berita Baru' }}</h1>
            <p class="text-slate-500 text-sm">Kelola konten berita dan unggah gambar carousel.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.berita.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50">Batal</a>
            
            {{-- Mengubah Teks Tombol Otomatis --}}
            <button type="submit" form="beritaForm" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/20">
                {{ $item->exists ? 'Update Berita' : 'Simpan Berita' }}
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-600 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Mengubah Action Rute Otomatis --}}
    <form id="beritaForm" action="{{ $item->exists ? route('admin.berita.update', $item->id) : route('admin.berita.store') }}" 
          method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        {{-- Otomatis menambahkan method PUT jika mode edit --}}
        @if($item->exists) @method('PUT') @endif

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Judul Berita</label>
                    <input type="text" name="title" value="{{ old('title', $item->title) }}" 
                           class="w-full text-2xl font-bold border-0 border-b-2 border-slate-100 focus:border-blue-500 focus:ring-0 p-0 py-2 transition" 
                           placeholder="Masukkan judul berita..." required>
                </div>
                
                <div class="mt-8">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Isi Konten</label>
                    <textarea name="content" rows="18" 
                              class="w-full p-4 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition" 
                              placeholder="Tulis detail berita di sini..." required>{{ old('content', $item->content) }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm sticky top-6">
                <h3 class="font-bold text-sm mb-6 uppercase tracking-wider text-slate-800">Pengaturan Utama</h3>
                
                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Thumbnail Utama</label>
                    <div class="group aspect-video bg-slate-50 rounded-xl border border-dashed border-slate-300 flex items-center justify-center overflow-hidden relative cursor-pointer">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="object-cover w-full h-full">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-center p-4 text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs">Klik untuk upload</span>
                            </div>
                        </template>
                        <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer" 
                               @change="const file = $event.target.files[0]; if(file) imagePreview = URL.createObjectURL(file)">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Kategori</label>
                    <select name="category" class="w-full text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20">
                        @foreach(['Berita Utama', 'Kegiatan', 'Pengumuman'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $item->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection