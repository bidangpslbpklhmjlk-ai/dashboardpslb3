@extends('layouts.admin')

@section('title', 'Edit Kendaraan Masuk')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.gatepass.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Kendaraan Masuk</h1>
            <p class="text-slate-500 mt-1">{{ $gatepass->nomor_polisi }} — {{ $gatepass->waktu_masuk->format('d M Y H:i') }}</p>
        </div>
    </div>

    <form action="{{ route('admin.gatepass.update', $gatepass) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        {{-- Tanggal --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $gatepass->waktu_masuk->format('Y-m-d')) }}" required
                max="{{ now()->format('Y-m-d') }}"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('tanggal_masuk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Armada: nomor polisi, tipe kendaraan, perusahaan/dinas --}}
        @include('admin.gatepass._armada-picker', [
            'armadaOptions' => $armadaOptions,
            'selectedId'    => old('armada_id', $gatepass->armada_id),
        ])
        {{-- Sumber Sampah (dari rute armada, tanpa Kantor DLH dan TPA) --}}
        <div id="sumber-wrap">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Sumber Sampah <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
            <div id="sumber-list" class="flex flex-wrap gap-x-4 gap-y-1.5 text-sm text-slate-700"></div>
            @error('sumber_sampah')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        {{-- Jenis Muatan --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Muatan <span class="text-red-500">*</span></label>
            <select name="jenis_muatan" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                @foreach(['Sampah Domestik','Sampah Komersial','Sampah B3','Limbah Organik','Residu'] as $jenis)
                    <option value="{{ $jenis }}" {{ old('jenis_muatan', $gatepass->jenis_muatan) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                @endforeach
            </select>
            @error('jenis_muatan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Volume --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Volume (Ton) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="volume_ton" required value="{{ old('volume_ton', $gatepass->volume_ton) }}"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('volume_ton')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Foto --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto Kendaraan <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
            @if($gatepass->photo)
                <img src="{{ Storage::url($gatepass->photo) }}" class="w-20 h-20 object-cover rounded-lg border border-slate-200 mb-2">
            @endif
            <input type="file" name="photo" accept="image/*"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            <p class="text-xs text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah foto.</p>
            @error('photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.gatepass.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                💾 Perbarui
            </button>
        </div>
    </form>
</div>
<script>
(function () {
    const peta    = @json($sumberPerArmada);
    const dipilih = @json(old('sumber_sampah', array_values(array_filter(array_map('trim', explode(';', (string) $gatepass->sumber_sampah))))));
    const box     = document.getElementById('sumber-list');
    let terakhir  = null;

    function armadaId() {
        const el = document.querySelector('[name=armada_id]');
        return el ? el.value : '';
    }
    function tampil() {
        const id = armadaId();
        if (id === terakhir) return;
        terakhir = id;
        // daftar rute + nilai tersimpan yang tidak ada di rute (misalnya hasil impor rekap), supaya tidak hilang saat disimpan
        const daftar = [...new Set([...(peta[id] || []), ...dipilih])];
        box.innerHTML = !daftar.length
            ? '<span class="text-slate-400">Armada ini belum punya rute.</span>'
            : daftar.map(n =>
                '<label class="inline-flex items-center gap-1.5"><input type="checkbox" name="sumber_sampah[]" value="'
                + n.replace(/"/g, '&quot;') + '"' + (dipilih.includes(n) ? ' checked' : '') + '> ' + n + '</label>').join('');
    }
    document.addEventListener('change', tampil);
    document.addEventListener('input', tampil);
    setInterval(tampil, 400);   // cadangan bila picker mengisi nilai lewat JavaScript
    tampil();
})();
</script>
@endsection