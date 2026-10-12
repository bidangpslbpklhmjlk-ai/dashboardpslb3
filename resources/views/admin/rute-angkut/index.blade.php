@extends('layouts.admin')

@section('title', 'Rute Angkut')

@section('content')
@php
    $groups = $rutes->groupBy('armada_id');
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Rute Angkut</h1>
            <p class="text-slate-500 mt-1">Rute pengangkutan sampah per armada</p>
        </div>
        <a href="{{ route('admin.rute-angkut.create') }}"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Rute
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Ringkasan coverage (rute aktif) --}}
    @php
        $pctDesa = $totalDesaMajalengka > 0 ? round($totalDesaTercover / $totalDesaMajalengka * 100) : 0;
        $pctKec  = $totalKecamatan > 0 ? round($totalKecamatanTercover / $totalKecamatan * 100) : 0;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Desa tercover</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalDesaTercover }}
                <span class="text-sm font-normal text-slate-400">/ {{ $totalDesaMajalengka }}</span></p>
            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-emerald-500" style="width: {{ $pctDesa }}%"></div>
            </div>
            <p class="mt-1 text-xs text-slate-400">{{ $pctDesa }}% dari seluruh desa</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Kecamatan tercover</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalKecamatanTercover }}
                <span class="text-sm font-normal text-slate-400">/ {{ $totalKecamatan }}</span></p>
            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-blue-500" style="width: {{ $pctKec }}%"></div>
            </div>
            <p class="mt-1 text-xs text-slate-400">{{ $pctKec }}% dari seluruh kecamatan</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total jarak rute aktif</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalJarak, 2, ',', '.') }}
                <span class="text-sm font-normal text-slate-400">km</span></p>
            <p class="mt-3 text-xs text-slate-400">{{ $rutes->where('aktif', true)->count() }} rute aktif dari {{ $rutes->count() }} rute</p>
        </div>
    </div>

    {{-- Peta --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800 text-sm">Peta Rute</h2>
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                @forelse($legend as $nopol => $color)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-1.5 rounded-full" style="background: {{ $color }}"></span>{{ $nopol }}
                    </span>
                @empty
                    <span class="text-slate-400">Belum ada rute aktif</span>
                @endforelse
            </div>
        </div>
        <div id="peta-rute" class="w-full" style="height: 400px; z-index: 0;"></div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.rute-angkut.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Armada</label>
                <select name="armada_id" class="w-56 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua armada</option>
                    @foreach($armadas as $a)
                        <option value="{{ $a->id }}" {{ (string) request('armada_id') === (string) $a->id ? 'selected' : '' }}>
                            {{ $a->nomor_polisi }}{{ $a->jenis_kendaraan ? ' — ' . $a->jenis_kendaraan : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Hari</label>
                <select name="hari" class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach($hariList as $h)
                        <option value="{{ $h }}" {{ request('hari') === $h ? 'selected' : '' }}>{{ $h }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">Filter</button>
                <a href="{{ route('admin.rute-angkut.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Daftar rute, dikelompokkan per armada --}}
    @forelse($groups as $armadaId => $items)
        @php
            $armada   = $items->first()->armada;
            $nopol    = $armada->nomor_polisi ?? '-';
            $color    = $legend[$nopol] ?? '#94a3b8';
            $jumlahAktif = $items->where('aktif', true)->count();
            $jarakGrup   = $items->sum('total_jarak');
            $lokasiGrup  = $items->sum(fn ($r) => $r->lokasis->count());
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header armada --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-slate-50 border-b border-slate-200"
                 style="border-left: 4px solid {{ $color }}">
                <div>
                    <h3 class="font-bold text-slate-900">{{ $nopol }}</h3>
                    <p class="text-xs text-slate-500">
                        {{ $armada->jenis_kendaraan ?? 'Jenis kendaraan belum diisi' }}
                        @if(!empty($armada->nama)) · {{ $armada->nama }} @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-600">
                    <span><strong class="text-slate-900">{{ $items->count() }}</strong> rute ({{ $jumlahAktif }} aktif)</span>
                    <span><strong class="text-slate-900">{{ $lokasiGrup }}</strong> lokasi</span>
                    <span><strong class="text-slate-900">{{ number_format($jarakGrup, 2, ',', '.') }}</strong> km</span>
                    @if($jumlahAktif > 0)
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" checked data-toggle-armada="{{ $nopol }}"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Tampil di peta
                        </label>
                    @endif
                </div>
            </div>

            {{-- Tabel rute armada ini --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="min-width: 760px;">
                    <thead class="text-slate-500 text-xs uppercase tracking-wide border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 w-28">Hari</th>
                            <th class="px-5 py-3">Nama Rute</th>
                            <th class="px-5 py-3">Urutan Lokasi</th>
                            <th class="px-5 py-3 text-right whitespace-nowrap">Jarak</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $rute)
                        <tr class="hover:bg-slate-50 transition-colors align-top">
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">{{ $rute->hari }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $rute->nama ?: 'Rute ' . $rute->hari }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                @if($rute->lokasis->isEmpty())
                                    <span class="text-slate-400">-</span>
                                @else
                                    <ol class="space-y-0.5 max-h-28 overflow-y-auto pr-2">
                                        @foreach($rute->lokasis as $l)
                                            <li class="flex gap-2">
                                                <span class="text-slate-400 w-4 text-right shrink-0">{{ $loop->iteration }}.</span>
                                                <span>{{ $l->nama }}
                                                    @if($l->desa)<span class="text-slate-400">· {{ $l->desa }}</span>@endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap text-slate-700">{{ number_format($rute->total_jarak, 2, ',', '.') }} km</td>
                            <td class="px-5 py-3.5 text-center">
                                @if($rute->aktif)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($rute->aktif)
                                        <button type="button" onclick="fokusRute({{ $rute->id }})"
                                                class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Lihat di peta">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.rute-angkut.edit', $rute) }}"
                                       class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.rute-angkut.destroy', $rute) }}" method="POST"
                                          onsubmit="return confirm('Hapus rute ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-slate-200 px-5 py-12 text-center text-slate-500">
            Belum ada rute angkut. Klik "Tambah Rute" untuk membuat yang pertama.
        </div>
    @endforelse
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const rutes = @json($routesForMap);
    const peta = L.map('peta-rute').setView([-6.8366, 108.2274], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(peta);

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const grupArmada = {};   // nopol -> featureGroup
    const layerRute  = {};   // id rute -> featureGroup

    rutes.forEach(function (r) {
        if (!grupArmada[r.nopol]) {
            grupArmada[r.nopol] = L.featureGroup().addTo(peta);
        }
        const layer = L.featureGroup();
        const pts = (r.points || []).filter(p => !isNaN(p.lat) && !isNaN(p.lng));

        const popup =
            '<strong>' + esc(r.nama) + '</strong><br>' +
            '<span style="font-size:12px;color:#64748b">' + esc(r.nopol) +
            (r.jenis ? ' · ' + esc(r.jenis) : '') + '<br>' +
            esc(r.hari) + ' · ' + r.jarak.toFixed(2) + ' km</span>';

        if (r.geometry && r.geometry.length > 1) {
            L.polyline(r.geometry, { color: r.color, weight: 5, opacity: 0.85 })
                .bindPopup(popup).addTo(layer);
        } else if (pts.length > 1) {
            L.polyline(pts.map(p => [p.lat, p.lng]), { color: r.color, weight: 4, dashArray: '8 8' })
                .bindPopup(popup).addTo(layer);
        }

        pts.forEach(function (p, i) {
            L.circleMarker([p.lat, p.lng], {
                radius: 7, color: '#fff', weight: 2, fillColor: r.color, fillOpacity: 1
            }).bindTooltip((i + 1) + '. ' + esc(p.name) + ' (' + esc(r.hari) + ')').addTo(layer);
        });

        layer.addTo(grupArmada[r.nopol]);
        layerRute[r.id] = layer;
    });

    const semua = L.featureGroup(Object.values(grupArmada));
    if (semua.getLayers().length && semua.getBounds().isValid()) {
        peta.fitBounds(semua.getBounds().pad(0.1));
    }

    // Tampilkan / sembunyikan semua rute satu armada
    document.querySelectorAll('[data-toggle-armada]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const g = grupArmada[cb.dataset.toggleArmada];
            if (!g) return;
            cb.checked ? g.addTo(peta) : peta.removeLayer(g);
        });
    });

    window.fokusRute = function (id) {
        const layer = layerRute[id];
        if (!layer) return;

        // pastikan armada rute ini sedang ditampilkan
        const r = rutes.find(x => x.id === id);
        const g = r ? grupArmada[r.nopol] : null;
        if (g && !peta.hasLayer(g)) {
            g.addTo(peta);
            const cb = document.querySelector('[data-toggle-armada="' + CSS.escape(r.nopol) + '"]');
            if (cb) cb.checked = true;
        }

        document.getElementById('peta-rute').scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (layer.getBounds().isValid()) peta.fitBounds(layer.getBounds().pad(0.2));
    };
})();
</script>
@endsection