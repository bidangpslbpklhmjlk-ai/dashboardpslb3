@extends('layouts.admin')

@section('title', 'Lokasi Angkut')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Lokasi Angkut</h1>
            <p class="text-slate-500 mt-1">Titik-titik pengangkutan sampah armada</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.lokasi.template') }}"
               class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-lg font-medium transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Template CSV
            </a>

            {{-- Import CSV: pilih file langsung upload --}}
            <form id="importForm" action="{{ route('admin.lokasi.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="csv_file" id="csvInput" accept=".csv,.txt" class="hidden"
                       onchange="if (this.files.length) this.form.submit()">
                <label for="csvInput"
                       title="Kolom: nama, alamat, kecamatan, desa, latitude, longitude, keterangan, aktif. Maks 2MB."
                       class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-lg font-medium transition-colors text-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import CSV
                </label>
            </form>

            <a href="{{ route('admin.lokasi.create') }}"
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Lokasi
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    @if($errors->has('csv_file'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ $errors->first('csv_file') }}</div>
    @endif

    @if(session('import_errors'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg">
            <p class="font-semibold mb-1">Beberapa baris gagal diimport:</p>
            <ul class="list-disc list-inside text-sm space-y-0.5 max-h-40 overflow-y-auto">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Peta --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800 text-sm">Peta Titik Angkut</h2>
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Aktif</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>Nonaktif</span>
                <span class="font-medium text-slate-700">{{ $titik->count() }} titik</span>
            </div>
        </div>
        <div id="peta-lokasi" class="w-full" style="height: 380px; z-index: 0;"></div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.lokasi.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm"
                    placeholder="Nama, alamat, desa, atau kecamatan...">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Kecamatan</label>
                <select name="kecamatan" class="w-48 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach($kecamatanList as $kec)
                        <option value="{{ $kec }}" {{ request('kecamatan') === $kec ? 'selected' : '' }}>{{ $kec }}</option>
                    @endforeach
                </select>
            </div>
                        <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Jenis</label>
                <select name="jenis" class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="pengambilan" {{ request('jenis') === 'pengambilan' ? 'selected' : '' }}>Pengambilan</option>
                    <option value="pool" {{ request('jenis') === 'pool' ? 'selected' : '' }}>Pool</option>
                    <option value="tpa" {{ request('jenis') === 'tpa' ? 'selected' : '' }}>TPA</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Status</label>
                <select name="aktif" class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="1" {{ request('aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">Filter</button>
                <a href="{{ route('admin.lokasi.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Tabel: bisa digeser horizontal & vertikal --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-auto" style="max-height: 520px;">
            <table class="w-full text-left text-sm" style="min-width: 1040px;">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3.5 w-12 bg-slate-50">No</th>
                        <th class="px-4 py-3.5 whitespace-nowrap bg-slate-50">Nama Lokasi</th>
                        <th class="px-4 py-3.5 whitespace-nowrap bg-slate-50">Jenis</th>
                        <th class="px-4 py-3.5 whitespace-nowrap bg-slate-50">Desa / Kelurahan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap bg-slate-50">Kecamatan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap bg-slate-50">Alamat</th>
                        <th class="px-4 py-3.5 whitespace-nowrap bg-slate-50">Koordinat</th>
                        <th class="px-4 py-3.5 text-center bg-slate-50">Status</th>
                        <th class="px-4 py-3.5 text-center bg-slate-50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lokasis as $lokasi)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-slate-400">{{ $lokasis->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">{{ $lokasi->nama }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
    @php
        $jenisBadge = [
            'pool' => ['Pool', 'bg-blue-100 text-blue-700'],
            'tpa'  => ['TPA', 'bg-amber-100 text-amber-700'],
        ][$lokasi->jenis] ?? ['Pengambilan', 'bg-slate-100 text-slate-600'];
    @endphp
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $jenisBadge[1] }}">{{ $jenisBadge[0] }}</span>
</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($lokasi->desa)
                                {{ $lokasi->desa }}
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Belum diisi</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $lokasi->kecamatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $lokasi->alamat ?: '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ $lokasi->maps_url }}" target="_blank"
                               class="text-xs font-mono text-blue-600 hover:underline">
                                {{ $lokasi->latitude }}, {{ $lokasi->longitude }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($lokasi->aktif)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" onclick="fokusTitik({{ $lokasi->id }})"
                                        class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Lihat di peta">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </button>
                                <a href="{{ route('admin.lokasi.edit', $lokasi) }}"
                                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.lokasi.destroy', $lokasi) }}" method="POST" onsubmit="return confirm('Hapus lokasi ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-500">
                            Belum ada lokasi angkut. Klik "Tambah Lokasi" untuk membuat yang pertama.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lokasis->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $lokasis->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
</div>

{{-- Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const titik = @json($titik);
    const peta = L.map('peta-lokasi').setView([-6.8366, 108.2274], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(peta);

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const markers = {};
    const bounds = [];

    titik.forEach(function (t) {
        const warna = t.aktif ? '#10b981' : '#94a3b8';
        const m = L.circleMarker([t.lat, t.lng], {
            radius: 8, color: '#fff', weight: 2, fillColor: warna, fillOpacity: 0.95
        }).addTo(peta);

        m.bindPopup(
            '<strong>' + esc(t.nama) + '</strong><br>' +
            '<span style="font-size:12px;color:#64748b">' +
            esc(t.desa || '-') + ', Kec. ' + esc(t.kecamatan || '-') + '</span><br>' +
            '<a href="' + esc(t.edit_url) + '" style="font-size:12px">Edit lokasi</a>'
        );

        markers[t.id] = m;
        bounds.push([t.lat, t.lng]);
    });

    if (bounds.length > 1) {
        peta.fitBounds(bounds, { padding: [30, 30] });
    } else if (bounds.length === 1) {
        peta.setView(bounds[0], 15);
    }

    window.fokusTitik = function (id) {
        const m = markers[id];
        if (!m) { alert('Lokasi ini tidak ada di peta (koordinat kosong).'); return; }
        document.getElementById('peta-lokasi').scrollIntoView({ behavior: 'smooth', block: 'center' });
        peta.setView(m.getLatLng(), 16);
        m.openPopup();
    };
})();
</script>
@endsection