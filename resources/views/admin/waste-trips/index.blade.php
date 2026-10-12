@extends('layouts.admin')

@section('title', 'Data Perjalanan Truk Sampah')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header & Stats --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Data Perjalanan Truk</h1>
            <p class="text-slate-500 mt-1">Kelola data pengangkutan sampah</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.waste-trips.report') }}" 
               class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan
            </a>
            <a href="{{ route('admin.waste-trips.create') }}" 
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Data
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Total Trips</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $trips->total() }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Active Trucks</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $trips->getCollection()->unique('nomor_polisi')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Total Distance</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($trips->sum('total_jarak'), 1) }} km</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Today</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $trips->where('tanggal', today())->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium flex items-center gap-1">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Desa Tercover
            </p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $totalDesaTercover }}</p>
            <p class="text-xs text-slate-400 mt-0.5">dari {{ $totalDesaMajalengka }} desa/kelurahan</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium flex items-center gap-1">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Kecamatan Tercover
            </p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $totalKecamatanTercover }}</p>
            <p class="text-xs text-slate-400 mt-0.5">dari 26 kecamatan</p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
    <div class="flex justify-between items-center mb-2">
        <h4 class="text-sm font-semibold text-slate-700">Coverage Wilayah Pengangkutan</h4>
        <span class="text-xs text-slate-500">{{ number_format(($totalDesaTercover / $totalDesaMajalengka) * 100, 1) }}% desa terlayani</span>
    </div>
    <div class="w-full bg-slate-100 rounded-full h-2.5">
        <div class="bg-emerald-500 h-2.5 rounded-full transition-all" style="width: {{ ($totalDesaTercover / $totalDesaMajalengka) * 100 }}%"></div>
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($kecamatanTercoverList as $kec)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ $kec }}
            </span>
        @endforeach
    </div>
</div>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.waste-trips.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" 
                    class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Sampai Tanggal</label>
                <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" 
                    class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Nomor Polisi</label>
                <select name="nomor_polisi" class="w-48 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua Truk</option>
                    @foreach($truckListFilter as $nopol)
                        <option value="{{ $nopol }}" {{ request('nomor_polisi') == $nopol ? 'selected' : '' }}>{{ $nopol }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Tampilkan</label>
                <select name="per_page" onchange="this.form.submit()" 
                    class="w-28 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm bg-white cursor-pointer">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 data</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 data</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 data</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 data</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors text-sm">
                    🔍 Filter
                </button>
                <a href="{{ route('admin.waste-trips.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors text-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- MAP VISUALIZATION --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 bg-slate-50 flex flex-wrap justify-between items-center gap-3">
            <h3 class="font-semibold text-slate-800">Peta Visualisasi Rute Truk</h3>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-slate-500 mr-2">Filter:</span>
                <button onclick="filterTruck('all')" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 text-white transition-all" id="btn-all">Semua</button>
                @php
                    $colors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];
                    $truckList = $trips->unique('nomor_polisi')->values();
                @endphp
                @foreach($truckList as $index => $truck)
                    @php $truckId = str_replace(' ', '', $truck->nomor_polisi); @endphp
                    <button onclick="filterTruck('{{ $truckId }}')" 
                        class="px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition-all" 
                        id="btn-{{ $truckId }}"
                        style="border-left: 4px solid {{ $colors[$index % count($colors)] }};">
                        {{ $truck->nomor_polisi }}
                    </button>
                @endforeach
            </div>
        </div>
        <div id="map" class="h-[450px] w-full relative bg-slate-100"></div>
        <div class="px-5 py-2 bg-slate-50 border-t border-slate-200 flex items-center gap-6 text-xs text-slate-500">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Start Point</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border-2 border-blue-500"></span> Pickup Point</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-slate-700"></span> End Point</span>
            <span class="flex items-center gap-1">🚛 Animasi Truk Aktif</span>
        </div>
    </div>

    {{-- DATA TABLE --}}
    {{-- DATA TABLE --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Table Header Info --}}
        <div class="px-5 py-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <div class="text-sm text-slate-600">
                Menampilkan <span class="font-semibold text-slate-900">{{ $trips->firstItem() ?? 0 }} - {{ $trips->lastItem() ?? 0 }}</span> 
                dari <span class="font-semibold text-slate-900">{{ $trips->total() }}</span> data
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Nomor Polisi</th>
                        <th class="px-5 py-3.5">Jenis</th>
                        <th class="px-5 py-3.5">Jam</th>
                        <th class="px-5 py-3.5">Titik Awal</th>
                        <th class="px-5 py-3.5">Pickup Points</th>
                        <th class="px-5 py-3.5">Titik Akhir</th>
                        <th class="px-5 py-3.5">Total Jarak</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($trips as $trip)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 whitespace-nowrap text-slate-900 font-medium">{{ $trip->tanggal->format('d M Y') }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-700 font-semibold">{{ $trip->nomor_polisi }}</td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $trip->jenis_kendaraan }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-600 text-xs">
                            <div>{{ $trip->jam_mulai }}</div>
                            <div class="text-slate-400">s/d {{ $trip->jam_selesai }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-600 text-xs">
                            <div class="font-medium text-slate-800">{{ $trip->start_nama ?? 'Titik Awal' }}</div>
                            @if($trip->start_kecamatan)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700 mt-1">
                                    {{ $trip->start_kecamatan }}
                                </span>
                            @endif
                        </td>
                        {{-- PICKUP POINTS: Hanya nama, dipisah koma --}}
                        <td class="px-5 py-4 text-slate-600 text-xs max-w-[200px]">
                            <div class="truncate" title="{{ collect($trip->pickup_points)->pluck('nama')->filter()->implode(', ') }}">
                                {{ collect($trip->pickup_points)->pluck('nama')->filter()->implode(', ') ?: '-' }}
                            </div>
                            <div class="text-slate-400 mt-0.5">{{ count($trip->pickup_points ?? []) }} titik</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-600 text-xs">
                            <div class="font-medium text-slate-800">{{ $trip->end_nama ?? 'Titik Akhir' }}</div>
                            @if($trip->end_kecamatan)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 mt-1">
                                    {{ $trip->end_kecamatan }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-semibold text-emerald-600">{{ number_format($trip->total_jarak, 2) }} km</span>
                        </td>
                        {{-- AKSI: Detail, Edit, Delete --}}
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="showDetail({{ $trip->id }})" 
                                    class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <a href="{{ route('admin.waste-trips.edit', $trip) }}" 
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.waste-trips.destroy', $trip) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')" class="inline">
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
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p>Belum ada data perjalanan truk.</p>
                            <a href="{{ route('admin.waste-trips.create') }}" class="text-emerald-600 hover:underline mt-2 inline-block">Tambah data pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($trips->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $trips->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    @include('components.admin-footer')
</div>

    {{-- MODAL DETAIL --}}
    <div id="detailModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetail()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">Detail Perjalanan</h3>
                    <button onclick="closeDetail()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="detailContent" class="p-6">
                    {{-- Content diisi via JS --}}
                </div>
            </div>
        </div>
    </div>

    <script>
    const tripsData = @json($trips->keyBy('id'));

    function showDetail(tripId) {
        const trip = tripsData[tripId];
        if (!trip) return;

        const pickupPoints = (trip.pickup_points || []).map((p, i) => `
            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">${i + 1}</div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-slate-800">${p.nama || 'Titik ' + (i + 1)}</div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        📍 ${p.desa || '-'}, ${p.kecamatan || '-'} 
                        <span class="text-emerald-600 font-medium ml-2">${p.jarak || '-'} km</span>
                    </div>
                    <div class="text-xs text-slate-400 font-mono mt-0.5">${p.lat}, ${p.lng}</div>
                </div>
            </div>
        `).join('');

        document.getElementById('detailContent').innerHTML = `
            <div class="space-y-5">
                {{-- Info Utama --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-200">
                        <div class="text-xs text-emerald-600 font-medium">Tanggal</div>
                        <div class="text-sm font-bold text-emerald-800">${new Date(trip.tanggal).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})}</div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                        <div class="text-xs text-blue-600 font-medium">Nomor Polisi</div>
                        <div class="text-sm font-bold text-blue-800">${trip.nomor_polisi}</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
                        <div class="text-xs text-purple-600 font-medium">Jenis Kendaraan</div>
                        <div class="text-sm font-bold text-purple-800">${trip.jenis_kendaraan}</div>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-3 border border-amber-200">
                        <div class="text-xs text-amber-600 font-medium">Total Jarak</div>
                        <div class="text-sm font-bold text-amber-800">${parseFloat(trip.total_jarak).toFixed(2)} km</div>
                    </div>
                </div>

                {{-- Jam --}}
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">🕐</span>
                        <div>
                            <div class="text-xs text-slate-500">Mulai</div>
                            <div class="font-semibold text-slate-800">${trip.jam_mulai}</div>
                        </div>
                    </div>
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">🏁</span>
                        <div>
                            <div class="text-xs text-slate-500">Selesai</div>
                            <div class="font-semibold text-slate-800">${trip.jam_selesai}</div>
                        </div>
                    </div>
                </div>

                {{-- Titik Awal --}}
                <div class="border-l-4 border-emerald-500 pl-4">
                    <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-1">Titik Awal</div>
                    <div class="font-medium text-slate-800">${trip.start_nama || 'Titik Awal'}</div>
                    <div class="text-sm text-slate-500">📍 ${trip.start_desa || '-'}, ${trip.start_kecamatan || '-'}</div>
                    <div class="text-xs text-slate-400 font-mono">${trip.start_lat}, ${trip.start_lng}</div>
                </div>

                {{-- Pickup Points --}}
                <div>
                    <div class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-2">Pickup Points (${trip.pickup_points?.length || 0})</div>
                    <div class="space-y-2">
                        ${pickupPoints || '<p class="text-sm text-slate-400">Tidak ada pickup point</p>'}
                    </div>
                </div>

                {{-- Titik Akhir --}}
                <div class="border-l-4 border-slate-600 pl-4">
                    <div class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Titik Akhir</div>
                    <div class="font-medium text-slate-800">${trip.end_nama || 'Titik Akhir'}</div>
                    <div class="text-sm text-slate-500">📍 ${trip.end_desa || '-'}, ${trip.end_kecamatan || '-'}</div>
                    ${trip.end_lat ? `<div class="text-xs text-slate-400 font-mono">${trip.end_lat}, ${trip.end_lng}</div>` : ''}
                </div>
            </div>
        `;

        document.getElementById('detailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDetail() {
        document.getElementById('detailModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close dengan Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDetail();
    });
    </script>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Data dari controller
const trucks = @json($trucksForMap);

const truckColors = {};
trucks.forEach(t => truckColors[t.id] = t.color);

let map;
let truckMarkers = {};
let routeLines = {};
let endMarkers = {};
let distanceLabels = {};
let animationFrames = {};

function calculateStraightDistance(points) {
    let total = 0;
    for (let i = 0; i < points.length - 1; i++) {
        const lat1 = points[i][0];
        const lng1 = points[i][1];
        const lat2 = points[i + 1][0];
        const lng2 = points[i + 1][1];

        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        total += R * c;
    }
    return total;
}

async function getRouteFromOSRM(points) {
    const coords = points.map(p => `${p[1]},${p[0]}`).join(';');
    const url = `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`;

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);

        const response = await fetch(url, { signal: controller.signal });
        clearTimeout(timeoutId);

        const data = await response.json();

        if (data.code === 'Ok' && data.routes.length > 0) {
            const route = data.routes[0];
            const geometry = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
            return {
                geometry: geometry,
                distance: route.distance / 1000,
                duration: route.duration
            };
        }
    } catch (e) {
        console.warn('OSRM gagal, pakai garis lurus:', e);
    }

    return {
        geometry: points,
        distance: calculateStraightDistance(points),
        duration: 0
    };
}

async function initMap() {
    if (trucks.length === 0) {
        document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full text-slate-400">Belum ada data untuk ditampilkan</div>';
        return;
    }

    const allLats = [];
    const allLngs = [];

    trucks.forEach(t => {
        allLats.push(t.start[0]);
        allLngs.push(t.start[1]);
        t.points.forEach(p => {
            allLats.push(p.lat);
            allLngs.push(p.lng);
        });
        if (t.end) {
            allLats.push(t.end[0]);
            allLngs.push(t.end[1]);
        }
    });

    const centerLat = (Math.min(...allLats) + Math.max(...allLats)) / 2;
    const centerLng = (Math.min(...allLngs) + Math.max(...allLngs)) / 2;

    map = L.map('map').setView([centerLat, centerLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    for (const truck of trucks) {
        await renderTruck(truck);
    }
}

async function renderTruck(truck) {
    const waypoints = [truck.start];
    if (truck.points && truck.points.length > 0) {
        truck.points.forEach(p => waypoints.push([p.lat, p.lng]));
    }
    if (truck.end && truck.end[0] && truck.end[1]) {
        waypoints.push(truck.end);
    }

    const routeData = await getRouteFromOSRM(waypoints);
    const routeGeometry = routeData.geometry;
    const totalKm = routeData.distance.toFixed(2);

    const routeLine = L.polyline(routeGeometry, { 
        color: truck.color, 
        weight: 4, 
        opacity: 0.7,
        dashArray: '12, 8'
    }).addTo(map);
    routeLines[truck.id] = routeLine;

    const midIndex = Math.floor(routeGeometry.length / 2);
    const midPoint = routeGeometry[midIndex] || routeGeometry[0];
    const offsetLat = 0.0008;

    const labelIcon = L.divIcon({
        className: 'distance-label-plain',
        html: `<span style="color:${truck.color};font-size:12px;font-weight:bold;text-shadow:0 1px 3px rgba(255,255,255,0.9);">${totalKm} km</span>`,
        iconSize: [60, 20],
        iconAnchor: [30, 20]
    });
    const distanceLabel = L.marker([midPoint[0] + offsetLat, midPoint[1]], { icon: labelIcon, zIndexOffset: 600, interactive: false }).addTo(map);
    distanceLabels[truck.id] = distanceLabel;

    // Start marker dengan kecamatan
    const startPopup = `<b>Start: ${truck.start_nama}</b><br>${truck.nopol}` + 
        (truck.start_kecamatan ? `<br><span style="color:#10b981;font-size:11px;">📍 ${truck.start_kecamatan}</span>` : '');
    L.circleMarker(truck.start, { radius: 10, fillColor: '#10b981', color: '#fff', weight: 3, fillOpacity: 0.9 })
        .addTo(map).bindPopup(startPopup);

    // Pickup markers dengan kecamatan
    if (truck.points && truck.points.length > 0) {
        truck.points.forEach((point, i) => {
            const popup = `<b>${point.name}</b><br>${truck.nopol}` +
                (point.kecamatan ? `<br><span style="color:#3b82f6;font-size:11px;">📍 ${point.kecamatan}</span>` : '');
            L.circleMarker([point.lat, point.lng], { 
                radius: 7, 
                fillColor: truck.color, 
                color: '#fff', 
                weight: 2, 
                fillOpacity: 0.6 
            }).addTo(map).bindPopup(popup);
        });
    }

    let endMarker = null;
    if (truck.end && truck.end[0] && truck.end[1]) {
        const endPopup = `<b>End: ${truck.end_nama}</b><br>${truck.nopol}` +
            (truck.end_kecamatan ? `<br><span style="color:#334155;font-size:11px;">📍 ${truck.end_kecamatan}</span>` : '');
        endMarker = L.circleMarker(truck.end, { 
            radius: 10, 
            fillColor: '#334155', 
            color: truck.color, 
            weight: 3, 
            fillOpacity: 0.9 
        }).addTo(map).bindPopup(endPopup);
        endMarkers[truck.id] = endMarker;
    }

    const truckIcon = L.divIcon({
        className: 'truck-anim-marker',
        html: `<div class="truck-icon-inner" style="font-size:26px;line-height:1;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));">🚛</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    const truckMarker = L.marker(truck.start, { icon: truckIcon, zIndexOffset: 1000 }).addTo(map);
    truckMarker.bindPopup(`<b>${truck.nopol}</b><br>Total: ${totalKm} km`);
    truckMarkers[truck.id] = truckMarker;

    let currentPointIndex = 0;
    let progress = 0;
    let isPausedAtPoint = false;
    let pauseTimer = null;
    let animFrameId = null;

    const waypointIndices = [0];
    let currentWp = 1;
    for (let i = 1; i < routeGeometry.length && currentWp < waypoints.length; i++) {
        const dist = Math.sqrt(
            Math.pow(routeGeometry[i][0] - waypoints[currentWp][0], 2) + 
            Math.pow(routeGeometry[i][1] - waypoints[currentWp][1], 2)
        );
        if (dist < 0.0005) {
            waypointIndices.push(i);
            currentWp++;
        }
    }
    if (waypointIndices.length < waypoints.length) {
        waypointIndices.push(routeGeometry.length - 1);
    }

    function move() {
        if (isPausedAtPoint) {
            animFrameId = requestAnimationFrame(move);
            return;
        }

        if (currentPointIndex >= routeGeometry.length - 1) {
            isPausedAtPoint = true;
            currentPointIndex = 0;

            truckMarker.setPopupContent(`<b>${truck.nopol}</b><br>✅ Sampai tujuan! Total: ${totalKm} km`);

            pauseTimer = setTimeout(() => {
                truckMarker.setLatLng(routeGeometry[0]);
                truckMarker.setPopupContent(`<b>${truck.nopol}</b><br>Mulai perjalanan...`);
                isPausedAtPoint = false;
            }, 2000);

            animFrameId = requestAnimationFrame(move);
            return;
        }

        const start = routeGeometry[currentPointIndex];
        const end = routeGeometry[currentPointIndex + 1];

        const segmentDist = Math.sqrt(
            Math.pow(end[0] - start[0], 2) + 
            Math.pow(end[1] - start[1], 2)
        );

        const baseSpeed = 0.008;
        const adjustedSpeed = Math.max(0.003, Math.min(0.02, baseSpeed * (0.001 / Math.max(segmentDist, 0.0001))));

        progress += adjustedSpeed;

        if (progress >= 1) {
            progress = 0;
            currentPointIndex++;

            const wpIndex = waypointIndices.indexOf(currentPointIndex);
            if (wpIndex !== -1) {
                isPausedAtPoint = true;

                let pointName = '';
                if (wpIndex === 0) pointName = truck.start_nama || 'Titik Awal';
                else if (wpIndex <= truck.points.length) {
                    pointName = truck.points[wpIndex - 1]?.name || `Titik ${wpIndex}`;
                } else {
                    pointName = truck.end_nama || 'Titik Akhir';
                }

                truckMarker.setPopupContent(`<b>${truck.nopol}</b><br>📍 ${pointName}`);

                pauseTimer = setTimeout(() => {
                    isPausedAtPoint = false;
                }, 1500);

                animFrameId = requestAnimationFrame(move);
                return;
            }
        }

        const lat = start[0] + (end[0] - start[0]) * progress;
        const lng = start[1] + (end[1] - start[1]) * progress;

        truckMarker.setLatLng([lat, lng]);

        const dx = end[1] - start[1];
        const el = truckMarker.getElement();
        if (el) {
            const inner = el.querySelector('.truck-icon-inner');
            if (inner) {
                inner.style.transform = dx > 0 ? 'scaleX(-1)' : 'scaleX(1)';
            }
        }

        animFrameId = requestAnimationFrame(move);
    }

    animFrameId = requestAnimationFrame(move);
    animationFrames[truck.id] = animFrameId;
}

function filterTruck(truckId) {
    document.querySelectorAll('[id^="btn-"]').forEach(btn => {
        btn.classList.remove('bg-slate-800', 'text-white');
        btn.classList.add('bg-white', 'text-slate-600');
    });

    const activeBtn = document.getElementById(`btn-${truckId}`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-slate-600');
        activeBtn.classList.add('bg-slate-800', 'text-white');
    }

    if (truckId === 'all') {
        Object.keys(routeLines).forEach(id => {
            routeLines[id].setStyle({ opacity: 0.7, weight: 4 });
            if (truckMarkers[id]) truckMarkers[id].setOpacity(1);
            if (endMarkers[id]) endMarkers[id].setStyle({ opacity: 1, fillOpacity: 0.9 });
            if (distanceLabels[id]) distanceLabels[id].setOpacity(1);
        });
    } else {
        Object.keys(routeLines).forEach(id => {
            if (id === truckId) {
                routeLines[id].setStyle({ opacity: 1, weight: 5 });
                if (truckMarkers[id]) truckMarkers[id].setOpacity(1);
                if (endMarkers[id]) endMarkers[id].setStyle({ opacity: 1, fillOpacity: 0.9 });
                if (distanceLabels[id]) distanceLabels[id].setOpacity(1);
            } else {
                routeLines[id].setStyle({ opacity: 0.1, weight: 2 });
                if (truckMarkers[id]) truckMarkers[id].setOpacity(0.1);
                if (endMarkers[id]) endMarkers[id].setStyle({ opacity: 0.1, fillOpacity: 0.1 });
                if (distanceLabels[id]) distanceLabels[id].setOpacity(0.1);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initMap);
</script>

<style>
.truck-anim-marker {}
.truck-icon-inner {
    transform-origin: center;
}
.distance-label-plain {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}
.distance-label-plain .leaflet-marker-icon {
    background: transparent !important;
    border: none !important;
}
.leaflet-popup-content-wrapper {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.leaflet-popup-content {
    margin: 12px 16px;
    font-size: 13px;
}
</style>
@endsection