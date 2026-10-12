@extends('layouts.admin')

@section('title', 'Edit Data Object Retribusi')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.object-retribusi.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Data Object Retribusi</h1>
            <p class="text-slate-500 mt-1">{{ $objectRetribusi->nama }}</p>
        </div>
    </div>

    <form action="{{ route('admin.object-retribusi.update', $objectRetribusi) }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="nama" value="{{ old('nama', $objectRetribusi->nama) }}" required
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori_object_retribusi_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                <option value="">Pilih kategori</option>
                @foreach($kategoriList as $k)
                    <option value="{{ $k->id }}" {{ old('kategori_object_retribusi_id', $objectRetribusi->kategori_object_retribusi_id) == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} — Rp {{ number_format($k->tarif, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
            @error('kategori_object_retribusi_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

@php
    $selectedPegawai = old('pegawai_id', $objectRetribusi->pegawai->pluck('id')->toArray());
@endphp
...
<label class="pegawai-option flex items-center gap-2.5 px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm"
    data-name="{{ strtolower($p->nama_lengkap) }}">
    <input type="checkbox" name="pegawai_id[]" value="{{ $p->id }}"
        {{ in_array($p->id, $selectedPegawai) ? 'checked' : '' }}
        class="pegawai-checkbox w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
    <span>{{ $p->nama_lengkap }} <span class="text-slate-400 text-xs">— {{ $p->jabatan->nama_jabatan ?? '-' }}</span></span>
</label>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat <span class="text-red-500">*</span></label>
            <textarea name="alamat" rows="2" required
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm resize-none">{{ old('alamat', $objectRetribusi->alamat) }}</textarea>
            @error('alamat')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex justify-between items-center mb-1.5">
                <label class="block text-sm font-medium text-slate-700">Titik Koordinat <span class="text-red-500">*</span></label>
                <button type="button" onclick="ambilLokasi()" id="btnLokasi"
                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 px-2 py-1 rounded-lg">
                    📍 Ambil lokasi saat ini
                </button>
            </div>
<div class="grid grid-cols-2 gap-3">
    <div>
        <input type="number" step="any" name="latitude" id="latitude"
            value="{{ old('latitude', $objectRetribusi->latitude) }}" required placeholder="Latitude"
            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
        @error('latitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <input type="number" step="any" name="longitude" id="longitude"
            value="{{ old('longitude', $objectRetribusi->longitude) }}" required placeholder="Longitude"
            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
        @error('longitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
            <p class="text-xs text-slate-400 mt-1">Bisa juga diambil manual dari Google Maps (klik kanan lokasi → salin koordinat).</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Telepon</label>
            <input type="text" name="telepon" value="{{ old('telepon', $objectRetribusi->telepon) }}" placeholder="0812-3456-7890"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('telepon')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="border-t border-slate-200 pt-5">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="hidden" name="is_kolektif" value="0">
                <input type="checkbox" id="is_kolektif" name="is_kolektif" value="1"
                    {{ old('is_kolektif', $objectRetribusi->is_kolektif) ? 'checked' : '' }}
                    onchange="document.getElementById('jumlah_unit_wrapper').classList.toggle('hidden', !this.checked)"
                    class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="text-sm font-medium text-slate-700">Kolektif?</span>
                <span class="text-xs text-slate-400">(Permukiman/RW, Pasar, atau Terminal)</span>
            </label>

            <div id="jumlah_unit_wrapper" class="mt-4 {{ old('is_kolektif', $objectRetribusi->is_kolektif) ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Jumlah Unit <span class="text-red-500">*</span>
                </label>
                <input type="number" min="1" name="jumlah_unit" value="{{ old('jumlah_unit', $objectRetribusi->jumlah_unit) }}"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm"
                    placeholder="Contoh: 120 (rumah tangga), 45 (pedagang)">
                <p class="text-xs text-slate-400 mt-1">Total tagihan = tarif kategori × jumlah unit ini. Update angka ini tiap ada perubahan di lapangan.</p>
                @error('jumlah_unit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Status <span class="text-red-500">*</span></label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 flex-1 justify-center">
                    <input type="radio" name="status" value="aktif" {{ old('status', $objectRetribusi->status) == 'aktif' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-slate-700 font-medium">Aktif</span>
                </label>
                <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100 flex-1 justify-center">
                    <input type="radio" name="status" value="nonaktif" {{ old('status', $objectRetribusi->status) == 'nonaktif' ? 'checked' : '' }} class="text-slate-600 focus:ring-slate-500">
                    <span class="text-sm text-slate-700 font-medium">Nonaktif</span>
                </label>
            </div>
            @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        @if($objectRetribusi->kunjungan()->count() > 0)
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-500">
            Object Retribusi ini sudah memiliki <span class="font-semibold text-slate-700">{{ $objectRetribusi->kunjungan()->count() }} riwayat kunjungan</span>.
        </div>
        @endif

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.object-retribusi.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                💾 Perbarui
            </button>
        </div>
    </form>
</div>

<script>
function ambilLokasi() {
    const btn = document.getElementById('btnLokasi');
    if (!navigator.geolocation) {
        alert('Browser tidak mendukung geolokasi');
        return;
    }
    btn.textContent = 'Mengambil lokasi...';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('latitude').value = pos.coords.latitude.toFixed(7);
            document.getElementById('longitude').value = pos.coords.longitude.toFixed(7);
            btn.textContent = '📍 Ambil lokasi saat ini';
        },
        () => {
            alert('Gagal mengambil lokasi. Pastikan izin lokasi diaktifkan.');
            btn.textContent = '📍 Ambil lokasi saat ini';
        }
    );
}
</script>
@endsection