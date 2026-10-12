<form action="{{ $activeItem->exists ? route('admin.berita.update', $activeItem->id) : route('admin.berita.store') }}" 
      method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf
    @if($activeItem->exists) @method('PUT') @endif

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Judul Berita</label>
            <input type="text" name="title" value="{{ old('title', $activeItem->title) }}" 
                   class="w-full text-lg font-semibold border-0 border-b-2 border-slate-100 focus:border-blue-500 focus:ring-0 p-0 py-2 transition">
            
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-6 mb-2">Isi Berita</label>
            <textarea name="content" rows="15" 
                      class="w-full p-4 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('content', $activeItem->content) }}</textarea>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-sm mb-4">Pengaturan</h3>
            
            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Thumbnail Utama</label>
                <div class="aspect-video bg-slate-50 rounded-xl border border-dashed border-slate-300 flex items-center justify-center overflow-hidden relative">
                    @if($activeItem->image)
                        <img src="{{ asset('storage/'.$activeItem->image) }}" class="object-cover w-full h-full">
                    @else
                        <span class="text-[10px] text-slate-400">Pilih Gambar</span>
                    @endif
                    <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Kategori</label>
                <select name="category" class="w-full text-sm border-slate-200 rounded-xl">
                    <option value="Berita Utama">Berita Utama</option>
                    <option value="Kegiatan">Kegiatan</option>
                </select>
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-600/20 transition">
                {{ $activeItem->exists ? 'Simpan Perubahan' : 'Terbitkan Berita' }}
            </button>
        </div>
    </div>
</form>