@extends('layouts.admin')

@section('title', 'Tambah Lokasi Angkut')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapPicker { height: 320px; border-radius: 12px; z-index: 1; }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.lokasi.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Lokasi Angkut</h1>
            <p class="text-slate-500 mt-1">Pilih wilayah, lalu klik peta untuk menandai titik lokasi</p>
        </div>
    </div>

    <form action="{{ route('admin.lokasi.store') }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf

        {{-- Nama --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lokasi <span class="text-red-500">*</span></label>
            <input type="text" name="nama" value="{{ old('nama') }}" required
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm"
                placeholder="Contoh: TPS Kelurahan Cigasong">
            @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

                {{-- Jenis lokasi --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Lokasi <span class="text-red-500">*</span></label>
            <select name="jenis" required
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                @foreach (['pengambilan' => 'Titik pengambilan sampah', 'pool' => 'Pool (kantor/garasi armada)', 'tpa' => 'TPA'] as $v => $t)
                    <option value="{{ $v }}" @selected(old('jenis', $lokasi->jenis ?? 'pengambilan') === $v)>{{ $t }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">Pool: check-in tanpa foto dan berat. TPA: wajib berat, foto opsional. Pengambilan: wajib keduanya.</p>
            @error('jenis')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Kecamatan & Desa --}}
        @include('admin.lokasi._wilayah')

        {{-- Alamat --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
            <input type="text" name="alamat" value="{{ old('alamat') }}"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm"
                placeholder="Alamat lengkap (opsional)">
            @error('alamat')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Peta --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Titik Koordinat <span class="text-red-500">*</span></label>
            <div id="mapPicker"></div>
            <p class="text-xs text-slate-400 mt-1.5">Klik di peta, geser marker, atau isi manual di bawah untuk menandai lokasi.</p>
        </div>

        {{-- Lat/Lng manual --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Latitude <span class="text-red-500">*</span></label>
                <input type="text" name="latitude" id="latitude" value="{{ old('latitude', '-6.8361') }}" required
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-mono">
                @error('latitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Longitude <span class="text-red-500">*</span></label>
                <input type="text" name="longitude" id="longitude" value="{{ old('longitude', '108.2280') }}" required
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-mono">
                @error('longitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
            <textarea name="keterangan" rows="2"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm resize-none"
                placeholder="Catatan tambahan (opsional)...">{{ old('keterangan') }}</textarea>
            @error('keterangan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Status Aktif --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" name="aktif" id="aktif" value="1"
                {{ old('aktif', $errors->any() ? null : 1) ? 'checked' : '' }}
                class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
            <label for="aktif" class="text-sm text-slate-700">Aktifkan lokasi ini</label>
        </div>

        {{-- Submit --}}
        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.lokasi.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                💾 Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    const startLat = parseFloat(latInput.value) || -6.8361;
    const startLng = parseFloat(lngInput.value) || 108.2280;

    const map = L.map('mapPicker').setView([startLat, startLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    function updateInputs(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
    }

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateInputs(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    [latInput, lngInput].forEach(function (input) {
        input.addEventListener('change', function () {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng]);
            }
        });
    });
});
</script>
@endpush