{{-- resources/views/admin/peran/_form.blade.php --}}
@php
    $editing = !is_null($peran ?? null);
    $dipilih = old('menus', $peran->menus ?? []);
@endphp

<div class="p-6 max-w-3xl space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.peran.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $editing ? 'Edit Peran' : 'Tambah Peran' }}</h1>
            <p class="text-sm text-slate-500">Beri nama peran, lalu centang menu yang boleh dibuka.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm border border-red-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($editing && ($peran->users_count ?? 0) > 0)
        <div class="px-4 py-3 rounded-xl bg-amber-50 text-amber-700 text-sm border border-amber-100">
            Peran ini dipakai <strong>{{ $peran->users_count }} pengguna</strong>. Perubahan menu langsung berlaku untuk mereka semua.
        </div>
    @endif

    <form action="{{ $editing ? route('admin.peran.update', $peran) : route('admin.peran.store') }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200/80 p-6 space-y-5">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Peran <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $peran->nama ?? '') }}" required maxlength="100"
                       placeholder="mis. Petugas Armada"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi (opsional)</label>
                <input type="text" name="deskripsi" value="{{ old('deskripsi', $peran->deskripsi ?? '') }}" maxlength="255"
                       placeholder="mis. Mengelola armada dan jadwal harian"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Menu yang boleh diakses <span class="text-red-500">*</span></label>

            <div class="space-y-4">
                @foreach($menuGroups as $grup => $items)
                    <div class="border border-slate-200 rounded-xl">
                        <label class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200 rounded-t-xl cursor-pointer">
                            <input type="checkbox" onchange="toggleGroup(this, {{ $loop->index }})" class="rounded text-green-700 focus:ring-green-700">
                            <span class="text-sm font-semibold text-slate-700">{{ $grup }}</span>
                            <span class="text-xs text-slate-400">(pilih semua)</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4">
                            @foreach($items as $item)
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="menus[]" value="{{ $item['key'] }}" data-group="{{ $loop->parent->index }}"
                                           {{ in_array($item['key'], $dipilih) ? 'checked' : '' }}
                                           class="rounded text-green-700 focus:ring-green-700">
                                    {{ $item['label'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800">
                Simpan
            </button>
            <a href="{{ route('admin.peran.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">Batal</a>
        </div>
    </form>
</div>

<script>
function toggleGroup(src, idx) {
    document.querySelectorAll('input[data-group="' + idx + '"]').forEach(c => c.checked = src.checked);
}
</script>