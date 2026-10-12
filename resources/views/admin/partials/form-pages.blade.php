@if($activeItem)
    <form action="{{ route('admin.pages.update', $activeItem->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold text-green-700 bg-green-50 border border-green-100 uppercase tracking-widest">
                    {{ $activeItem->category }}
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-2">
                    {{ $activeItem->title }}
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Terakhir diperbarui: {{ $activeItem->updated_at ? $activeItem->updated_at->translatedFormat('d M Y, H:i') : '—' }}
                </p>
            </div>

            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-green-700 hover:bg-green-800 rounded-xl transition shadow-lg shadow-green-700/20 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>

        {{-- Bento Card Body --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Input Title --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Judul Halaman</label>
                    <input type="text" name="title" value="{{ old('title', $activeItem->title) }}" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition font-medium text-slate-800">
                </div>

                {{-- Select Category --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kategori Induk</label>
                    <select name="category" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition font-medium text-slate-800">
                        @foreach(['Profil', 'Layanan', 'Informasi'] as $cat)
                            <option value="{{ $cat }}" {{ $activeItem->category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Textarea Content --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Isi Konten</label>
                <textarea name="content" rows="15" class="w-full p-4 text-sm font-normal border border-slate-200 rounded-2xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition leading-relaxed">{{ old('content', $activeItem->content) }}</textarea>
            </div>
        </div>
    </form>
@else
    <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-slate-100 shadow-sm">
        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <p class="text-slate-400 text-sm font-medium">Pilih halaman dari sidebar untuk mulai mengedit.</p>
    </div>
@endif