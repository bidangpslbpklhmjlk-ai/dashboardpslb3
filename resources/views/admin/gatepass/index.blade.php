@extends('layouts.admin')

@section('title', 'Pencatatan Kendaraan Sampah')

@push('styles')
<style>
    @keyframes fadeSlideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes countUp { from { opacity: 0; transform: scale(0.92); } to { opacity: 1; transform: scale(1); } }
    .anim-stat { animation: countUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .anim-row { animation: fadeSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .anim-card { animation: fadeIn 0.5s ease-out both; }
    .row-hover { transition: background-color 0.2s ease; }
    .btn-press { transition: transform 0.15s ease, background-color 0.2s ease, box-shadow 0.2s ease; }
    .btn-press:active { transform: scale(0.96); }
    .icon-btn { transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s ease; }
    .icon-btn:hover { transform: translateY(-1px) scale(1.06); }
    .icon-btn:active { transform: scale(0.9); }
    .stat-card { transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -8px rgba(16, 24, 40, 0.12); }
    @media (prefers-reduced-motion: reduce) {
        .anim-stat, .anim-row, .anim-card, .row-hover, .btn-press, .icon-btn, .stat-card {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $inputCls = 'w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white transition-shadow';
    $labelCls = 'block text-xs font-medium text-slate-600 mb-1.5 uppercase';

    // ---- urutan tabel ----
    $linkUrut = function (string $key, string $arahAwal) use ($urut, $arah) {
        $baru = $urut === $key ? ($arah === 'asc' ? 'desc' : 'asc') : $arahAwal;
        return request()->fullUrlWithQuery(['urut' => $key, 'arah' => $baru, 'page' => null]);
    };
    $panah = fn (string $key) => $urut === $key ? ($arah === 'asc' ? '▲' : '▼') : '↕';

    // key, judul, arah awal, kelas tambahan (rata & responsif)
    $kolom = [
        ['waktu',  'Waktu',              'desc', ''],
        ['nopol',  'Nomor Polisi',       'asc',  ''],
        ['tipe',   'Tipe',               'asc',  'hidden md:table-cell'],
        ['muatan', 'Muatan',             'asc',  'hidden lg:table-cell'],
        ['armada', 'Perusahaan / Dinas', 'asc',  ''],
        ['volume', 'Volume (Ton)',       'desc', 'text-right'],
    ];

    // ---- data modal detail ----
    $rowDetail = fn ($i, $n) => [
        'ritasi'          => $n,
        'waktu'           => $i->waktu_masuk->format('d M Y, H:i'),
        'volume'          => number_format($i->volume_ton, 2),
        'muatan'          => $i->jenis_muatan,
        'photo'           => $i->photo ? Storage::url($i->photo) : null,
        'has_invoice'     => (bool) $i->invoice,
        'invoice_url'     => $i->invoice ? route('admin.invoice.print', $i->invoice) : null,
        'invoice_printed' => (bool) ($i->invoice->printed_at ?? false),
    ];
    $buatDetail = fn ($group, $tipe, $muatan, $volume, $count, $rows) => [
        'nomor_polisi'   => $group->nomor_polisi,
        'tipe_kendaraan' => $tipe,
        'jenis_muatan'   => $muatan,
        'total_volume'   => number_format($volume, 2),
        'ritasi_count'   => $count,
        'armada_nama'    => $group->armada?->nama ?? '-',
        'armada_tipe'    => $group->armada?->tipe_label ?? '-',
        'armada_alamat'  => $group->armada?->alamat ?? '-',
        'armada_telepon' => $group->armada?->telepon ?? '-',
        'pembayaran'     => $group->armada?->pembayaran ?? '-',
        'items'          => $rows,
    ];
@endphp

<div class="max-w-7xl mx-auto space-y-5 sm:space-y-6" x-data="{ showModal: false, detailModal: false, detailData: null }">

     {{-- Header --}}
    <div class="space-y-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Pencatatan Kendaraan Sampah</h1>
                <p class="text-slate-500 text-sm mt-0.5">Gatepass TPA</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center lg:justify-end">
                {{-- Aksi file --}}
                <div class="grid grid-cols-3 gap-2 sm:flex sm:items-center">
                    <a href="{{ route('admin.gatepass.template') }}"
                       class="btn-press inline-flex items-center justify-center border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 sm:px-4 py-2.5 rounded-lg font-medium text-sm whitespace-nowrap">
                        Template
                    </a>
                    <a href="{{ route('admin.gatepass.export', request()->only(['tahun', 'dari', 'sampai', 'tipe_kendaraan', 'perusahaan_id', 'nomor_polisi'])) }}"
                       class="btn-press inline-flex items-center justify-center border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 sm:px-4 py-2.5 rounded-lg font-medium text-sm whitespace-nowrap"
                       title="Ekspor data sesuai filter yang sedang aktif">
                        Ekspor CSV
                    </a>
                    <form action="{{ route('admin.gatepass.import') }}" method="POST" enctype="multipart/form-data" x-ref="formImport" class="contents">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv,.txt" class="hidden" x-ref="berkas"
                               @change="$refs.formImport.submit()">
                        <button type="button" @click="$refs.berkas.click()"
                                class="btn-press inline-flex items-center justify-center border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 sm:px-4 py-2.5 rounded-lg font-medium text-sm whitespace-nowrap">
                            Import CSV
                        </button>
                    </form>
                </div>

                {{-- Aksi utama --}}
                <a href="{{ route('admin.gatepass.create') }}"
                   class="btn-press inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 sm:px-5 py-2.5 rounded-lg font-medium text-sm shadow-sm hover:shadow-md whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Catat Kendaraan
                </a>
            </div>
        </div>

        {{-- Petunjuk import (dilipat) --}}
        <details class="group bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-xs text-slate-600">
            <summary class="cursor-pointer select-none font-medium text-slate-700 hover:text-emerald-700 list-none flex items-center justify-between">
                Petunjuk import CSV
                <span class="text-slate-400 transition-transform group-open:rotate-180">▾</span>
            </summary>
            <ul class="mt-2 list-disc pl-5 space-y-1">
                <li>Wajib: <b>waktu_masuk</b> (YYYY-MM-DD atau YYYY-MM-DD HH:MM) dan <b>nomor_polisi</b> (armada aktif).</li>
                <li><b>jenis_muatan</b> salah satu: Sampah Domestik, Sampah Komersial, Sampah B3, Limbah Organik, Residu. Kosong = Sampah Domestik.</li>
                <li><b>volume_ton</b> kosong = 2,5. Jam kosong = 08:00, lalu +1 menit per ritasi berikutnya.</li>
                <li>Satu baris = satu ritasi. Kolom <b>ritasi</b> cukup dikosongkan.</li>
                <li><b>sumber_sampah</b>: pisahkan dengan tanda | . Hapus baris contoh sebelum mengunggah.</li>
            </ul>
        </details>
    </div>
    {{-- Stats --}}
    <div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="stat-card anim-stat bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-slate-200" style="animation-delay: 0.05s">
                <p class="text-slate-500 text-sm font-medium">Total Ritasi ({{ $stats['label'] }})</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_kendaraan']) }}</p>
            </div>
            <div class="stat-card anim-stat bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-slate-200" style="animation-delay: 0.12s">
                <p class="text-slate-500 text-sm font-medium">Total Volume</p>
                <p class="text-2xl sm:text-3xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_volume'], 2) }} <span class="text-base sm:text-lg">Ton</span></p>
            </div>
            <div class="stat-card anim-stat bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-slate-200" style="animation-delay: 0.19s">
                <p class="text-slate-500 text-sm font-medium">Rata-rata Volume</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-600 mt-1">{{ number_format($stats['rata_volume'], 2) }} <span class="text-base sm:text-lg">Ton</span></p>
            </div>
        </div>
        @if($stats['volume_estimasi'] > 0)
            <p class="text-xs text-amber-600 mt-2">Termasuk estimasi {{ number_format($stats['volume_estimasi'], 2) }} Ton (data rekap).</p>
        @endif
    </div>

    {{-- Chart Tren --}}
    <div class="anim-card bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5" style="animation-delay: 0.08s"
         x-data="{ activeChart: 'jumlah' }">
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-4">
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-slate-700">{{ $trendJudul }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $trendKeterangan }}</p>
            </div>
            <div class="flex gap-1 bg-slate-100 p-1 rounded-lg self-start sm:self-auto">
                <button type="button" data-mode="jumlah" @click="activeChart = 'jumlah'"
                    :class="activeChart === 'jumlah' ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500'"
                    class="js-chart-btn btn-press px-3 py-1.5 rounded-md text-xs font-medium">Jumlah Kendaraan</button>
                <button type="button" data-mode="volume" @click="activeChart = 'volume'"
                    :class="activeChart === 'volume' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'"
                    class="js-chart-btn btn-press px-3 py-1.5 rounded-md text-xs font-medium">Volume (Ton)</button>
            </div>
        </div>
        @if($chartLabels->isEmpty())
            <div class="flex items-center justify-center text-sm text-slate-400 h-48 sm:h-64">
                Tidak ada data untuk filter ini.
            </div>
        @else
            <div class="relative h-48 sm:h-64">
                <canvas id="trendChart"></canvas>
            </div>
        @endif
    </div>

    {{-- Filter --}}
    <div class="anim-card bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5" style="animation-delay: 0.1s">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center mb-4">
            <h3 class="text-sm font-semibold text-slate-700">Filter Data</h3>
            <button type="button" @click="showModal = true"
               class="btn-press inline-flex items-center gap-1.5 self-start text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 rounded-lg cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Perusahaan / Instansi
            </button>
        </div>

        <form action="{{ route('admin.gatepass.index') }}" method="GET"
              class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 items-end">
            <input type="hidden" name="urut" value="{{ $urut }}">
            <input type="hidden" name="arah" value="{{ $arah }}">

            <div>
                <label class="{{ $labelCls }}">Tahun</label>
                <select name="tahun" class="{{ $inputCls }}">
                    <option value="">Semua</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelCls }}">Dari</label>
                <input type="date" name="dari" value="{{ request('dari', request('tanggal')) }}" class="{{ $inputCls }}">
            </div>
            <div>
                <label class="{{ $labelCls }}">Sampai</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="{{ $inputCls }}">
            </div>
            <div>
                <label class="{{ $labelCls }}">Tipe Kendaraan</label>
                <select name="tipe_kendaraan" class="{{ $inputCls }}">
                    <option value="">Semua</option>
                    @foreach($tipeKendaraanList as $tipe)
                        <option value="{{ $tipe }}" {{ request('tipe_kendaraan') == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelCls }}">Perusahaan / Dinas</label>
                <select name="perusahaan_id" class="{{ $inputCls }}">
                    <option value="">Semua</option>
                    @foreach($armadaList as $p)
                        <option value="{{ $p->id }}" {{ request('perusahaan_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}{{ $p->tipe === 'dinas_lh' ? ' (Dinas LH)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelCls }}">Nomor Polisi</label>
                <input type="text" name="nomor_polisi" value="{{ request('nomor_polisi') }}" placeholder="D 1234 ABC"
                    class="{{ $inputCls }} uppercase">
            </div>

            <div class="col-span-2 md:col-span-3 xl:col-span-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <a href="{{ route('admin.gatepass.index') }}"
                   class="btn-press text-center px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50">Reset</a>
                <button type="submit"
                   class="btn-press px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm shadow-sm hover:shadow-md">🔍 Filter</button>
            </div>
        </form>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="anim-row bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error') || $errors->has('csv_file'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ session('error') ?? $errors->first('csv_file') }}
        </div>
    @endif
    @if(session('import_errors'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach(array_slice(session('import_errors'), 0, 30) as $g)<li>{{ $g }}</li>@endforeach
            </ul>
            @if(count(session('import_errors')) > 30)
                <p class="mt-1 text-xs">...dan {{ count(session('import_errors')) - 30 }} baris lain.</p>
            @endif
        </div>
    @endif

    {{-- Tabel --}}
    <div class="anim-card bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" style="animation-delay: 0.15s">
        <div class="overflow-x-auto min-h-[16rem]">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        @foreach($kolom as [$key, $judul, $arahAwal, $kls])
                            <th class="px-3 sm:px-5 py-3.5 whitespace-nowrap {{ $kls }}">
                                <a href="{{ $linkUrut($key, $arahAwal) }}"
                                   class="inline-flex items-center gap-1 hover:text-emerald-700"
                                   title="Urutkan berdasarkan {{ strtolower($judul) }}">
                                    {{ $judul }}
                                    <span class="text-[10px] {{ $urut === $key ? 'text-emerald-600' : 'text-slate-300' }}">{{ $panah($key) }}</span>
                                </a>
                            </th>
                        @endforeach
                        <th class="px-3 sm:px-5 py-3.5 text-center hidden sm:table-cell">Foto</th>
                        <th class="px-3 sm:px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" x-data="{ editOpen: {}, viewOpen: {}, statusOpen: {} }">
                    @forelse($logs as $index => $group)
                    <tr class="row-hover anim-row hover:bg-slate-50" style="animation-delay: {{ min($loop->index * 0.04, 0.4) }}s">
                        <td class="px-3 sm:px-5 py-3 sm:py-4 whitespace-nowrap text-slate-900 font-medium">
                            {{ $group->latest->waktu_masuk->format('H:i') }}
                            <div class="text-xs text-slate-400">{{ $group->latest->waktu_masuk->format('d M Y') }}</div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 whitespace-nowrap font-semibold text-slate-800">
                            <div class="flex items-center gap-2">
                                {{ $group->nomor_polisi }}
                                @if($group->ritasi_count > 1)
                                    <span class="inline-flex items-center justify-center px-1.5 h-5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">
                                        {{ $group->ritasi_count }}x
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 whitespace-nowrap hidden md:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                {{ $group->tipe_kendaraan }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 whitespace-nowrap text-slate-600 hidden lg:table-cell">{{ $group->jenis_muatan }}</td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4">
                            <span class="text-xs font-medium {{ $group->armada?->tipe === 'dinas_lh' ? 'text-blue-600' : 'text-slate-600' }}">
                                {{ $group->armada?->nama ?? '-' }}
                            </span>
                            @if($group->armada)
                                <span class="text-[10px] text-slate-400 block">{{ $group->armada->tipe_label }}</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 whitespace-nowrap text-right font-bold text-emerald-600">
                            {{ number_format($group->total_volume, 2) }}
                            @if($group->ritasi_count > 1)
                                <div class="text-[10px] text-slate-400 font-normal">total {{ $group->ritasi_count }} ritasi</div>
                            @endif
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 whitespace-nowrap text-center hidden sm:table-cell">
                            @if($group->latest->photo)
                                <button type="button" onclick="showPhoto('{{ Storage::url($group->latest->photo) }}')" class="icon-btn inline-block">
                                    <img src="{{ Storage::url($group->latest->photo) }}" alt="Foto kendaraan" class="w-10 h-10 object-cover rounded-lg border border-slate-200 hover:opacity-80 mx-auto">
                                </button>
                            @else
                                <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-5 py-3 sm:py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1">

                                {{-- Lihat detail --}}
                                @if($group->ritasi_count > 1)
                                    <div class="relative">
                                        <button type="button" @click="viewOpen[{{ $index }}] = !viewOpen[{{ $index }}]"
                                            class="icon-btn p-1.5 text-slate-600 hover:bg-slate-100 rounded-lg" title="Pilih ritasi untuk dilihat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <div x-show="viewOpen[{{ $index }}]" x-cloak
                                             @click.away="viewOpen[{{ $index }}] = false"
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="absolute right-0 sm:left-0 sm:right-auto z-30 mt-1 w-56 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden text-left">
                                            <p class="px-3 py-2 text-[11px] font-semibold text-slate-400 uppercase border-b border-slate-100 bg-slate-50">
                                                Pilih ritasi yang dilihat
                                            </p>
                                            @foreach($group->items->values() as $idx => $item)
                                                <button type="button"
                                                    @click="viewOpen[{{ $index }}] = false; detailData = {{ Illuminate\Support\Js::from($buatDetail($group, $item->tipe_kendaraan, $item->jenis_muatan, $item->volume_ton, 1, [$rowDetail($item, $idx + 1)])) }}; detailModal = true"
                                                    class="w-full flex items-center justify-between px-3 py-2 text-sm hover:bg-blue-50 text-slate-700">
                                                    <span class="font-semibold text-slate-800">#{{ $idx + 1 }}</span>
                                                    <span class="text-xs text-slate-400">{{ $item->waktu_masuk->format('H:i, d M Y') }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <button type="button"
                                        @click="detailData = {{ Illuminate\Support\Js::from($buatDetail($group, $group->tipe_kendaraan, $group->jenis_muatan, $group->total_volume, $group->ritasi_count, $group->items->values()->map(fn ($i, $n) => $rowDetail($i, $n + 1))->all())) }}; detailModal = true"
                                        class="icon-btn p-1.5 text-slate-600 hover:bg-slate-100 rounded-lg" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Status pembayaran --}}
                                @if($group->ritasi_count > 1)
                                    <div class="relative">
                                        <button type="button" @click="statusOpen[{{ $index }}] = !statusOpen[{{ $index }}]"
                                            class="icon-btn p-1.5 rounded-lg {{ $group->items->contains(fn($i) => $i->invoice && !$i->invoice->isPaid()) ? 'text-amber-600 hover:bg-amber-50' : ($group->items->contains(fn($i) => $i->invoice) ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-50') }}"
                                            title="Status pembayaran per ritasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                        <div x-show="statusOpen[{{ $index }}]" x-cloak
                                             @click.away="statusOpen[{{ $index }}] = false"
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="absolute right-0 z-30 mt-1 w-52 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden text-left">
                                            <p class="px-3 py-2 text-[11px] font-semibold text-slate-400 uppercase border-b border-slate-100 bg-slate-50">
                                                Status Pembayaran
                                            </p>
                                            @foreach($group->items->values() as $idx => $item)
                                                <div class="flex items-center justify-between px-3 py-2 text-sm border-b border-slate-50 last:border-0">
                                                    <span class="font-semibold text-slate-800">#{{ $idx + 1 }}</span>
                                                    @if($item->invoice)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $item->invoice->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                            {{ $item->invoice->isPaid() ? 'Lunas' : 'Belum Lunas' }}
                                                        </span>
                                                    @elseif($item->needsInvoice())
                                                        <span class="text-[11px] text-red-500 font-medium">Perlu Invoice</span>
                                                    @else
                                                        <span class="text-[11px] text-slate-400">Gratis</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    @if($group->latest->invoice)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $group->latest->invoice->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}"
                                              title="{{ $group->latest->invoice->isPaid() ? 'Sudah lunas' : 'Belum dibayar' }}">
                                            {{ $group->latest->invoice->isPaid() ? 'Lunas' : 'Belum Lunas' }}
                                        </span>
                                    @elseif($group->latest->needsInvoice())
                                        <span class="text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded font-medium">Perlu Invoice</span>
                                    @endif
                                @endif

                                {{-- Edit --}}
                                @if($group->ritasi_count > 1)
                                    <div class="relative">
                                        <button type="button" @click="editOpen[{{ $index }}] = !editOpen[{{ $index }}]"
                                            class="icon-btn p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Pilih ritasi untuk diedit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <div x-show="editOpen[{{ $index }}]" x-cloak
                                             @click.away="editOpen[{{ $index }}] = false"
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="absolute right-0 z-30 mt-1 w-56 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden text-left">
                                            <p class="px-3 py-2 text-[11px] font-semibold text-slate-400 uppercase border-b border-slate-100 bg-slate-50">
                                                Pilih ritasi yang diedit
                                            </p>
                                            @foreach($group->items as $item)
                                                <a href="{{ route('admin.gatepass.edit', $item) }}"
                                                   class="flex items-center justify-between px-3 py-2 text-sm hover:bg-blue-50 text-slate-700">
                                                    <span class="font-semibold text-slate-800">#{{ $loop->iteration }}</span>
                                                    <span class="text-xs text-slate-400">{{ $item->waktu_masuk->format('H:i, d M Y') }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('admin.gatepass.edit', $group->latest) }}"
                                       class="icon-btn p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endif

                                {{-- Hapus --}}
                                <form action="{{ route('admin.gatepass.destroy', $group->latest) }}" method="POST" onsubmit="return confirm('Hapus data ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                            <p>Tidak ada data untuk filter ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-4 sm:px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $logs->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    {{-- Modal Tambah Perusahaan / Dinas --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50" style="display: none;"
        x-data="{
            notif: { show: false, type: '', message: '' },
            loading: false,
            showNotif(type, msg) {
                this.notif = { show: true, type: type, message: msg };
                setTimeout(() => this.notif.show = false, 4000);
            }
        }">

        <div x-show="notif.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            :class="notif.type === 'success' ? 'bg-emerald-500' : 'bg-red-500'"
            class="fixed top-4 right-4 left-4 sm:left-auto z-[60] text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 sm:max-w-sm">
            <svg x-show="notif.type === 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="notif.type === 'error'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span class="text-sm font-medium" x-text="notif.message"></span>
        </div>

        <div x-show="showModal"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="showModal = false"></div>

        <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
            <div x-show="showModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="pointer-events-auto bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[92vh] flex flex-col overflow-hidden">

                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Tambah Perusahaan / Dinas</h3>
                        <p class="text-xs text-slate-500">Data akan tersimpan di master perusahaan</p>
                    </div>
                    <button type="button" @click="showModal = false" class="icon-btn p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="
                    loading = true;
                    fetch('{{ route('admin.armada.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(Object.fromEntries(new FormData($event.target)))
                    })
                    .then(r => r.json())
                    .then(data => {
                        loading = false;
                        if (data.success) {
                            showNotif('success', data.message);
                            setTimeout(() => { showModal = false; window.location.reload(); }, 1500);
                        } else {
                            showNotif('error', data.message);
                        }
                    })
                    .catch(err => {
                        loading = false;
                        showNotif('error', 'Terjadi kesalahan koneksi');
                    });
                " class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required class="{{ $inputCls }}" placeholder="Contoh: PT. Maju Bersih">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 flex-1 justify-center">
                                <input type="radio" name="tipe" value="perusahaan" checked class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm text-slate-700 font-medium">Perusahaan</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 flex-1 justify-center">
                                <input type="radio" name="tipe" value="dinas" class="text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-slate-700 font-medium">Dinas</span>
                            </label>
                        </div>
                    </div>

                    <div x-data="{ pembayaran: 'gratis' }">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Pembayaran <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 flex-1 justify-center">
                                <input type="radio" name="pembayaran" value="gratis" x-model="pembayaran" class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm text-slate-700 font-medium">Gratis</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 flex-1 justify-center">
                                <input type="radio" name="pembayaran" value="bayar" x-model="pembayaran" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-sm text-slate-700 font-medium">Berbayar</span>
                            </label>
                        </div>
                        <p class="text-xs text-slate-400 mt-1" x-show="pembayaran === 'bayar'" x-transition>
                            Tarif akan diatur saat pembuatan invoice
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor Polisi</label>
                        <input type="text" name="nomor_polisi" class="{{ $inputCls }} uppercase" placeholder="D 1234 ABC">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" class="{{ $inputCls }}">
                            <option value="">Pilih Jenis</option>
                            <option value="Dump Truck">Dump Truck</option>
                            <option value="Arm Roll">Arm Roll</option>
                            <option value="Compactor">Compactor</option>
                            <option value="Pickup">Pickup</option>
                            <option value="Truk Sampah">Truk Sampah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
                        <textarea name="alamat" rows="2" class="{{ $inputCls }} resize-none" placeholder="Alamat lengkap..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Telepon</label>
                        <input type="text" name="telepon" class="{{ $inputCls }}" placeholder="0812-3456-7890">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="btn-press flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="loading"
                            class="btn-press flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300 text-white rounded-lg font-medium text-sm shadow-sm flex items-center justify-center gap-2">
                            <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Detail Gatepass --}}
    <div x-show="detailModal" x-cloak class="fixed inset-0 z-50" style="display: none;">
        <div x-show="detailModal"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="detailModal = false"></div>

        <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4" @click.self="detailModal = false">
            <div x-show="detailModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">

                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold text-slate-900 truncate" x-text="detailData?.nomor_polisi"></h3>
                        <p class="text-xs text-slate-500" x-text="detailData?.tipe_kendaraan"></p>
                    </div>
                    <button type="button" @click="detailModal = false" class="icon-btn p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-5 overflow-y-auto" x-show="detailData">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-[11px] text-slate-400 uppercase font-medium">Total Ritasi</p>
                            <p class="text-lg font-bold text-slate-900" x-text="detailData?.ritasi_count + 'x'"></p>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-3">
                            <p class="text-[11px] text-slate-400 uppercase font-medium">Total Volume</p>
                            <p class="text-lg font-bold text-emerald-600" x-text="detailData?.total_volume + ' Ton'"></p>
                        </div>
                    </div>

                    <div class="text-sm">
                        <span class="text-xs text-slate-400 uppercase font-medium">Jenis Muatan: </span>
                        <span class="text-slate-700 font-medium" x-text="detailData?.jenis_muatan"></span>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">Perusahaan / Dinas</h4>
                        <div class="bg-white border border-slate-200 rounded-lg p-3 space-y-1.5 text-sm">
                            <p class="font-semibold text-slate-800" x-text="detailData?.armada_nama"></p>
                            <p class="text-slate-500 text-xs">
                                <span x-text="detailData?.armada_tipe"></span>
                                &middot;
                                <span x-text="detailData?.pembayaran === 'bayar' ? 'Berbayar' : 'Gratis'"></span>
                            </p>
                            <p class="text-slate-600 text-xs" x-text="detailData?.armada_alamat"></p>
                            <p class="text-slate-600 text-xs" x-text="detailData?.armada_telepon"></p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">Riwayat Ritasi</h4>
                        <div class="space-y-2">
                            <template x-for="item in (detailData?.items ?? [])" :key="item.ritasi">
                                <div class="flex items-center gap-3 border border-slate-200 rounded-lg p-3">
                                    <template x-if="item.photo">
                                        <img :src="item.photo" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shrink-0">
                                    </template>
                                    <template x-if="!item.photo">
                                        <div class="w-12 h-12 rounded-lg border border-slate-200 shrink-0 bg-slate-100 flex items-center justify-center text-slate-300 text-[10px]">
                                            No Foto
                                        </div>
                                    </template>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2">
                                            <span class="text-xs font-bold text-amber-700 bg-amber-100 px-1.5 rounded" x-text="'#' + item.ritasi"></span>
                                            <span class="text-sm font-medium text-slate-800" x-text="item.waktu"></span>
                                        </div>
                                        <p class="text-xs text-slate-500" x-text="item.muatan + ' — ' + item.volume + ' Ton'"></p>
                                    </div>
                                    <a :href="item.invoice_url" x-show="item.has_invoice" target="_blank"
                                       class="icon-btn p-1.5 rounded-lg shrink-0"
                                       :class="item.invoice_printed ? 'text-slate-400 hover:bg-slate-50' : 'text-emerald-600 hover:bg-emerald-50'"
                                       :title="item.invoice_printed ? 'Sudah dicetak' : 'Cetak Invoice'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </a>
                                    <span x-show="item.invoice_printed" class="text-[10px] text-slate-400 font-medium">Dicetak</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-5 sm:px-6 py-4 border-t border-slate-100 bg-slate-50 shrink-0">
                    <button type="button" @click="detailModal = false"
                        class="btn-press w-full px-4 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Photo Modal --}}
<div id="photoModal" class="fixed inset-0 z-50 hidden" onclick="closePhoto()">
    <div class="absolute inset-0 bg-black/80" style="animation: fadeIn 0.2s ease-out;"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <img id="photoImg" src="" class="max-w-full max-h-full rounded-lg shadow-2xl" style="animation: countUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
    </div>
</div>

<script>
function showPhoto(src) {
    document.getElementById('photoImg').src = src;
    document.getElementById('photoModal').classList.remove('hidden');
}
function closePhoto() {
    document.getElementById('photoModal').classList.add('hidden');
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('trendChart');
    if (!canvas) return; // tidak ada data untuk filter ini

    const labels     = @json($chartLabels);
    const dataJumlah = @json($chartJumlah);
    const dataVolume = @json($chartVolume);
    const ctx = canvas.getContext('2d');

    const gradientEmerald = ctx.createLinearGradient(0, 0, 0, 260);
    gradientEmerald.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
    gradientEmerald.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

    const gradientBlue = ctx.createLinearGradient(0, 0, 0, 260);
    gradientBlue.addColorStop(0, 'rgba(37, 99, 235, 0.35)');
    gradientBlue.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

    const trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Kendaraan',
                data: dataJumlah,
                borderColor: '#059669',
                backgroundColor: gradientEmerald,
                borderWidth: 2.5,
                pointRadius: labels.length > 31 ? 0 : 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#059669',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                tension: 0.35,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1200, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1f2937', padding: 10, cornerRadius: 8, displayColors: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 12, maxRotation: 0 } }
            }
        }
    });

    document.querySelectorAll('.js-chart-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ds = trendChart.data.datasets[0];
            if (this.dataset.mode === 'volume') {
                ds.label = 'Volume (Ton)';
                ds.data = dataVolume;
                ds.borderColor = '#2563eb';
                ds.backgroundColor = gradientBlue;
                ds.pointBackgroundColor = '#2563eb';
            } else {
                ds.label = 'Kendaraan';
                ds.data = dataJumlah;
                ds.borderColor = '#059669';
                ds.backgroundColor = gradientEmerald;
                ds.pointBackgroundColor = '#059669';
            }
            trendChart.update();
        });
    });
});
</script>
@endsection