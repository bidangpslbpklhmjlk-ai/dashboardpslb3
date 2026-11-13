@extends('layouts.admin')

@section('title', 'Bank Sampah')

@section('content')
@php
    $hasil = session('hasil_import');
    $fmt   = fn ($n) => number_format((float) $n, 0, ',', '.');
    $fmt1  = fn ($n) => number_format((float) $n, 1, ',', '.');
    $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $jml   = [
        'desa'  => $rekap->sum('jumlah_desa'),
        'bsu'   => $rekap->sum('jumlah_bsu'),
        'aktif' => $rekap->sum('desa_aktif'),
        'belum' => $rekap->sum('desa_belum'),
    ];
    $persen = $jml['desa'] ? (int) round($jml['aktif'] / $jml['desa'] * 100) : 0;

    $penuh = $dari === 1 && $sampai === 12;
    $periode = $penuh ? '' : ($dari === $sampai
        ? $namaBulan[$dari - 1] . ' '
        : mb_substr($namaBulan[$dari - 1], 0, 3) . '–' . mb_substr($namaBulan[$sampai - 1], 0, 3) . ' ');

    $filterAktif = $kecFilter || $jenisFilter || !$penuh;
    $a  = $analisis;
    $pb = $a->perbandingan;
    $input = 'rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600';

    // Panah perubahan: naik hijau, turun merah
    $delta = function ($p) {
        if ($p === null) return '<span class="text-slate-400">tidak ada pembanding</span>';
        $cls = $p >= 0 ? 'text-green-700' : 'text-red-600';
        return '<span class="font-semibold ' . $cls . '">' . ($p >= 0 ? '▲ ' : '▼ ') . number_format(abs($p), 1, ',', '.') . '%</span>';
    };
@endphp

<div class="p-6 lg:p-8 space-y-6">

    @include('admin.bank-sampah._tabs')

    {{-- Judul + filter --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Bank Sampah</h1>
            <p class="text-sm text-slate-500">Rekap penimbangan per kecamatan. Status desa dihitung dari ada atau tidaknya penimbangan.</p>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label for="tahun" class="mb-1 block text-xs text-slate-500">Tahun</label>
                <input type="number" id="tahun" name="tahun" value="{{ $tahun }}" min="2000" max="2100" class="w-24 {{ $input }}">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">Dari bulan</label>
                <select name="dari" class="{{ $input }}">
                    @foreach($namaBulan as $i => $nm)<option value="{{ $i + 1 }}" @selected($dari === $i + 1)>{{ $nm }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">Sampai bulan</label>
                <select name="sampai" class="{{ $input }}">
                    @foreach($namaBulan as $i => $nm)<option value="{{ $i + 1 }}" @selected($sampai === $i + 1)>{{ $nm }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">Kecamatan</label>
                <select name="kecamatan" class="{{ $input }}">
                    <option value="">Semua</option>
                    @foreach($kecamatanOpsi as $k)<option value="{{ $k->kode }}" @selected($kecFilter === $k->kode)>{{ $k->nama }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">Jenis unit</label>
                <select name="jenis" class="{{ $input }}">
                    <option value="">Semua</option>
                    @foreach($jenisOpsi as $v => $l)<option value="{{ $v }}" @selected($jenisFilter === $v)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <button class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Tampilkan</button>
            @if($filterAktif || $tahun !== 2026)
                <a href="{{ route('admin.bank-sampah.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Atur ulang</a>
            @endif
        </form>
    </div>

    @if($filterAktif)
        <p class="text-xs text-slate-500">
            Menampilkan {{ $periode ?: 'setahun penuh ' }}{{ $tahun }}
            @if($kecFilter) · kecamatan {{ $kecamatanOpsi->firstWhere('kode', $kecFilter)->nama }} @endif
            @if($jenisFilter) · jenis unit {{ $jenisOpsi[$jenisFilter] }} @endif
        </p>
    @endif

    {{-- Pesan galat --}}
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Hasil impor terakhir --}}
    @if($hasil)
        <div class="rounded-xl border {{ $hasil['disimpan'] ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }} p-4 text-sm space-y-3">
            <p class="font-semibold {{ $hasil['disimpan'] ? 'text-green-800' : 'text-amber-800' }}">
                {{ $hasil['disimpan'] ? 'Impor selesai dan data tersimpan.' : 'Pratinjau saja, belum ada data yang disimpan.' }}
            </p>
            <div class="flex flex-wrap gap-x-6 gap-y-1 text-slate-700">
                <span>Baris dibaca: <b>{{ $hasil['baris_dibaca'] }}</b></span>
                <span>Bank sampah baru: <b>{{ $hasil['bank_baru'] }}</b></span>
                <span>Data timbangan: <b>{{ $hasil['timbangan_disimpan'] }}</b></span>
            </div>
            @if(!empty($hasil['perlu_dicek']))
                <details open>
                    <summary class="cursor-pointer font-medium text-slate-800">Perlu dicek ({{ count($hasil['perlu_dicek']) }})</summary>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5 text-slate-700">@foreach($hasil['perlu_dicek'] as $c)<li>{{ $c }}</li>@endforeach</ul>
                </details>
            @endif
            @if(!empty($hasil['dilewati']))
                <details>
                    <summary class="cursor-pointer font-medium text-slate-800">Baris dilewati ({{ count($hasil['dilewati']) }})</summary>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5 text-slate-600">@foreach($hasil['dilewati'] as $c)<li>{{ $c }}</li>@endforeach</ul>
                </details>
            @endif
        </div>
    @endif

    {{-- Form impor (dilipat) --}}
    <details class="rounded-2xl border border-slate-200 bg-white shadow-sm" @if($errors->any() || $hasil) open @endif>
        <summary class="cursor-pointer px-5 py-3 text-sm font-bold uppercase tracking-wider text-slate-500">Impor timbangan dari Excel</summary>
        <form method="POST" action="{{ route('admin.bank-sampah.import') }}" enctype="multipart/form-data" class="border-t border-slate-100 p-5">
            @csrf
            <div class="mb-3 flex justify-end">
                <a href="{{ route('admin.bank-sampah.template') }}"
                   class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Unduh template</a>
            </div>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="mb-1 block text-sm text-slate-600" for="file">File (sheet "Detail Data")</label>
                    <input type="file" id="file" name="file" accept=".xlsx,.xls" required
                           class="block text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-slate-200">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-slate-600" for="tahun_impor">Tahun data</label>
                    <input type="number" id="tahun_impor" name="tahun" value="{{ old('tahun', $tahun) }}" min="2000" max="2100" required class="w-24 {{ $input }}">
                </div>
                <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                    <input type="checkbox" name="pratinjau" value="1" checked class="rounded border-slate-300 text-green-700 focus:ring-green-600">
                    Pratinjau saja (jangan simpan)
                </label>
                <button class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Proses</button>
            </div>
            <p class="mt-3 text-xs text-slate-500">Impor ulang aman: data bulan yang sama diperbarui, bukan digandakan. Bulan kosong atau "-" tidak disimpan.</p>
        </form>
    </details>

    {{-- Ringkasan total --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Desa</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ $fmt($total->desa) }} <span class="text-sm font-medium text-slate-400">kg</span></p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Unit Non-desa (Kecamatan)</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ $fmt($total->unit_kec) }} <span class="text-sm font-medium text-slate-400">kg</span></p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat Kabupaten</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ $fmt($total->kabupaten) }} <span class="text-sm font-medium text-slate-400">kg</span></p>
        </div>
        <div class="rounded-2xl border border-green-200 bg-green-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-green-700">Total {{ $periode }}{{ $tahun }}</p>
            <p class="mt-1 text-xl font-bold text-green-800">{{ $fmt($total->semua) }} <span class="text-sm font-medium text-green-600">kg</span></p>
            <p class="mt-1 text-xs text-green-700">{{ $bsuAktif }} BSU aktif, {{ $jml['aktif'] }} dari {{ $jml['desa'] }} desa menimbang ({{ $persen }}%)</p>
        </div>
    </div>

    {{-- ================= ANALISIS ================= --}}
    <section class="space-y-4">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Analisis</h2>

        @if($a->belum_lengkap)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                {{ $namaBulan[$a->bulan_berjalan - 1] }} {{ $tahun }} masih berjalan, datanya belum lengkap. Perbandingan dan daftar "BSU belum menimbang bulan ini" bersifat sementara.
            </div>
        @endif

        {{-- 1. Perbandingan --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dibanding periode sebelumnya</p>
                <p class="mt-1 text-lg">{!! $delta($pb->persen_sebelumnya) !!}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $pb->sebelumnya !== null ? $fmt($pb->sebelumnya) . ' kg' : 'periode sebelumnya di luar data tahun ini' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dibanding tahun {{ $tahun - 1 }}</p>
                <p class="mt-1 text-lg">{!! $delta($pb->persen_tahun_lalu) !!}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $pb->tahun_lalu !== null ? $fmt($pb->tahun_lalu) . ' kg pada periode yang sama' : 'belum ada data tahun ' . ($tahun - 1) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Bulan tertinggi / terendah</p>
                @if($pb->tertinggi)
                    <p class="mt-1 text-sm text-slate-700">▲ {{ $namaBulan[$pb->tertinggi['bulan'] - 1] }}: <b>{{ $fmt($pb->tertinggi['kg']) }}</b> kg</p>
                    <p class="text-sm text-slate-700">{{ $pb->terendah ? '▼ ' . $namaBulan[$pb->terendah['bulan'] - 1] . ': ' : '' }}@if($pb->terendah)<b>{{ $fmt($pb->terendah['kg']) }}</b> kg @else <span class="text-xs text-slate-400">terendah menunggu minimal dua bulan lengkap</span>@endif</p>
                    <p class="mt-1 text-xs text-slate-400">setahun {{ $tahun }}, tanpa bulan yang masih berjalan</p>
                @else
                    <p class="mt-1 text-sm text-slate-400">belum ada bulan lengkap</p>
                @endif
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Rata-rata periode ini</p>
                <p class="mt-1 text-sm text-slate-700"><b>{{ $pb->rata_bsu !== null ? $fmt($pb->rata_bsu) : '-' }}</b> kg per BSU ({{ $pb->bsu_menimbang }} BSU menimbang)</p>
                <p class="text-sm text-slate-700"><b>{{ $pb->rata_desa !== null ? $fmt($pb->rata_desa) : '-' }}</b> kg per desa aktif</p>
            </div>
        </div>

        {{-- 2. Peringkat --}}
        <div class="grid gap-4 lg:grid-cols-3">
            @foreach([['Kecamatan teratas', $a->peringkat->kecamatan], ['Desa teratas', $a->peringkat->desa], ['BSU teratas', $a->peringkat->bsu]] as [$judul, $daftar])
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">{{ $judul }}</p>
                    @forelse($daftar as $i => $r)
                        <div class="flex items-baseline justify-between gap-3 border-t border-slate-100 py-1.5 first:border-0">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-800">{{ $i + 1 }}. {{ $r->nama }}</p>
                                @if($r->info)<p class="truncate text-xs text-slate-400">{{ $r->info }}</p>@endif
                            </div>
                            <span class="shrink-0 text-sm text-slate-700">{{ $fmt($r->kg) }} kg</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada data.</p>
                    @endforelse
                </div>
            @endforeach
        </div>

        {{-- 3 & 4. Cakupan dan kualitas data --}}
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Cakupan</p>

                <details>
                    <summary class="cursor-pointer text-sm font-medium text-slate-800">
                        Desa belum menimbang ({{ $jml['belum'] }})
                    </summary>
                    <div class="mt-2 max-h-64 space-y-2 overflow-y-auto text-sm text-slate-600">
                        @forelse($a->belum_menimbang as $g)
                            <p><b class="text-slate-700">{{ $g->kecamatan }}</b> ({{ $g->desa->count() }}): {{ $g->desa->implode(', ') }}</p>
                        @empty
                            <p class="text-slate-400">Semua desa sudah menimbang pada periode ini.</p>
                        @endforelse
                    </div>
                </details>

                <details>
                    <summary class="cursor-pointer text-sm font-medium text-slate-800">
                        BSU yang menimbang {{ $sampai > 1 ? mb_strtolower($namaBulan[$sampai - 2]) : 'bulan lalu' }} tetapi tidak {{ mb_strtolower($namaBulan[$sampai - 1]) }} ({{ $a->bsu_diam->count() }})
                    </summary>
                    <div class="mt-2 max-h-64 overflow-y-auto text-sm">
                        @forelse($a->bsu_diam as $r)
                            <div class="flex justify-between gap-3 border-t border-slate-100 py-1 first:border-0">
                                <span class="text-slate-700">{{ $r->nama }} <span class="text-xs text-slate-400">{{ $r->info }}</span></span>
                                <span class="shrink-0 text-slate-500">{{ $fmt($r->kg) }} kg</span>
                            </div>
                        @empty
                            <p class="text-slate-400">Tidak ada.</p>
                        @endforelse
                    </div>
                </details>

                <details>
                    <summary class="cursor-pointer text-sm font-medium text-slate-800">
                        BSU aktif yang belum pernah menimbang di {{ $tahun }} ({{ $a->bsu_belum_pernah->count() }})
                    </summary>
                    <div class="mt-2 max-h-64 overflow-y-auto text-sm">
                        @forelse($a->bsu_belum_pernah as $r)
                            <p class="border-t border-slate-100 py-1 text-slate-700 first:border-0">{{ $r->nama }} <span class="text-xs text-slate-400">{{ $r->info }}</span></p>
                        @empty
                            <p class="text-slate-400">Tidak ada.</p>
                        @endforelse
                    </div>
                </details>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kualitas data</p>

                <details {{ $a->lonjakan->isNotEmpty() ? 'open' : '' }}>
                    <summary class="cursor-pointer text-sm font-medium text-slate-800">Angka yang melonjak ({{ $a->lonjakan->count() }})</summary>
                    <div class="mt-2 text-sm">
                        @forelse($a->lonjakan as $r)
                            <div class="border-t border-slate-100 py-1.5 first:border-0">
                                <p class="text-slate-800">{{ $r->nama }} · {{ $namaBulan[$r->bulan - 1] }}: <b>{{ $fmt($r->kg) }}</b> kg</p>
                                <p class="text-xs text-slate-400">{{ $fmt1($r->rasio) }}× dari nilai tengah bulan lain ({{ $fmt($r->median) }} kg), {{ $r->info }}</p>
                            </div>
                        @empty
                            <p class="text-slate-400">Tidak ada angka yang mencurigakan.</p>
                        @endforelse
                        <p class="mt-2 text-xs text-slate-400">Ditandai bila minimal 3× nilai tengah BSU itu sendiri dan selisihnya minimal 100 kg. BSU dengan kurang dari 4 bulan data tidak diperiksa. Bukan berarti salah, hanya perlu dicek ke sumbernya.</p>
                    </div>
                </details>

                <details>
                    <summary class="cursor-pointer text-sm font-medium text-slate-800">Nama sementara atau rekap desa ({{ $a->belum_dipecah->count() }})</summary>
                    <div class="mt-2 max-h-64 overflow-y-auto text-sm">
                        @forelse($a->belum_dipecah as $r)
                            <p class="border-t border-slate-100 py-1 text-slate-700 first:border-0">{{ $r->nama }} <span class="text-xs text-slate-400">{{ $r->info }}</span></p>
                        @empty
                            <p class="text-slate-400">Semua BSU sudah bernama dan terpecah per bank.</p>
                        @endforelse
                        <p class="mt-2 text-xs text-slate-400">Unit ini dibuat otomatis saat impor atau belum punya nama di daftar induk. Lengkapi di tab Daftar BSU.</p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    {{-- Tren bulanan: klik batang untuk memfilter satu bulan --}}
    @php $maks = max($tren->max(), 1); @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Tren bulanan {{ $tahun }}</h2>
        <div class="mt-4 flex h-44 items-end gap-2">
            @foreach($tren as $i => $kg)
                <a href="{{ request()->fullUrlWithQuery(['bulan' => $bulan === $i + 1 ? null : $i + 1]) }}"
                   title="{{ $namaBulan[$i] }}: {{ $fmt($kg) }} kg"
                   class="flex flex-1 flex-col items-center justify-end gap-1">
                    <span class="text-[10px] text-slate-500">{{ $kg ? $fmt($kg) : '' }}</span>
                    <div class="w-full rounded-t {{ $bulan === $i + 1 ? 'bg-green-700' : 'bg-green-300 hover:bg-green-400' }}"
                         style="height: {{ max(2, (int) round($kg / $maks * 110)) }}px"></div>
                    <span class="text-[10px] text-slate-500">{{ mb_substr($namaBulan[$i], 0, 3) }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Rekap per kecamatan: klik baris kecamatan untuk membuka desa dan BSU --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kecamatan</th>
                        <th class="px-4 py-3 text-right">Desa</th>
                        <th class="px-4 py-3 text-right">BSU</th>
                        <th class="px-4 py-3 text-right">Aktif</th>
                        <th class="px-4 py-3 text-right">Belum</th>
                        <th class="px-4 py-3 text-right">% Aktif</th>
                        <th class="px-4 py-3 text-right">Desa (kg)</th>
                        <th class="px-4 py-3 text-right">Non-desa (kg)</th>
                        <th class="px-4 py-3 text-right">Total (kg)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rekap as $k)
                        <tr class="cursor-pointer hover:bg-slate-50" data-toggle="{{ $k->kode }}">
                            <td class="px-4 py-2.5 font-medium text-slate-800">
                                <span class="panah mr-1 inline-block text-slate-400 transition-transform">▸</span>{{ $k->nama }}
                            </td>
                            <td class="px-4 py-2.5 text-right">{{ $k->jumlah_desa }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $k->jumlah_bsu }}</td>
                            <td class="px-4 py-2.5 text-right text-green-700">{{ $k->desa_aktif }}</td>
                            <td class="px-4 py-2.5 text-right {{ $k->desa_belum ? 'text-red-600' : 'text-slate-400' }}">{{ $k->desa_belum }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $k->persen }}%</td>
                            <td class="px-4 py-2.5 text-right">{{ $fmt($k->kg_desa) }}</td>
                            <td class="px-4 py-2.5 text-right text-slate-500">{{ $k->kg_unit ? $fmt($k->kg_unit) : '-' }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-slate-800">{{ $fmt($k->kg_total) }}</td>
                        </tr>

                        @foreach($k->desa as $d)
                            <tr class="hidden bg-slate-50/60" data-parent="{{ $k->kode }}">
                                <td class="py-2 pl-10 pr-4 text-slate-700">{{ $d->nama }}</td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2 text-right text-slate-500">{{ $d->jumlah_bsu ?: '-' }}</td>
                                <td class="px-4 py-2 text-xs {{ $d->aktif ? 'text-green-700' : 'text-red-600' }}" colspan="3">
                                    {{ $d->aktif ? 'Sudah menimbang' : 'Belum menimbang' }}
                                </td>
                                <td class="px-4 py-2 text-right">{{ $d->aktif ? $fmt($d->kg) : '-' }}</td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2 text-right text-slate-600">{{ $d->aktif ? $fmt($d->kg) : '-' }}</td>
                            </tr>
                            @foreach($d->bsu as $b)
                                <tr class="hidden bg-slate-50/60 text-xs text-slate-500" data-parent="{{ $k->kode }}">
                                    <td class="py-1.5 pl-16 pr-4" colspan="3">
                                        {{ $b->nama }}
                                        @unless($b->aktif)<span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">nonaktif</span>@endunless
                                    </td>
                                    <td class="px-4 py-1.5" colspan="3"></td>
                                    <td class="px-4 py-1.5 text-right">{{ $b->kg ? $fmt($b->kg) : '-' }}</td>
                                    <td class="px-4 py-1.5" colspan="2"></td>
                                </tr>
                            @endforeach
                        @endforeach

                        @foreach($k->unit as $u)
                            <tr class="hidden bg-slate-50/60 text-xs text-slate-500" data-parent="{{ $k->kode }}">
                                <td class="py-1.5 pl-10 pr-4" colspan="3">{{ $u->nama }} <span class="text-slate-400">(unit non-desa)</span></td>
                                <td class="px-4 py-1.5" colspan="3"></td>
                                <td class="px-4 py-1.5"></td>
                                <td class="px-4 py-1.5 text-right">{{ $u->kg ? $fmt($u->kg) : '-' }}</td>
                                <td class="px-4 py-1.5"></td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-semibold text-slate-800">
                    <tr>
                        <td class="px-4 py-3">Jumlah Kecamatan</td>
                        <td class="px-4 py-3 text-right">{{ $jml['desa'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $jml['bsu'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $jml['aktif'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $jml['belum'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $persen }}%</td>
                        <td class="px-4 py-3 text-right">{{ $fmt($total->desa) }}</td>
                        <td class="px-4 py-3 text-right">{{ $fmt($total->unit_kec) }}</td>
                        <td class="px-4 py-3 text-right">{{ $fmt($total->desa + $total->unit_kec) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
    document.querySelectorAll('[data-toggle]').forEach(function (tr) {
        tr.addEventListener('click', function () {
            document.querySelectorAll('[data-parent="' + tr.dataset.toggle + '"]').forEach(function (r) {
                r.classList.toggle('hidden');
            });
            tr.querySelector('.panah').classList.toggle('rotate-90');
        });
    });
    </script>

    {{-- Unit tingkat kabupaten (PKK Kabupaten, dll.) --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Unit Tingkat Kabupaten</h2>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3 text-right">Total (kg)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($kabupaten as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $u->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $u->jenis }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $fmt($u->kg) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-4 text-center text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
            @if($kabupaten->isNotEmpty())
                <tfoot class="bg-slate-50 font-semibold text-slate-800">
                    <tr>
                        <td class="px-4 py-3" colspan="2">Jumlah Tingkat Kabupaten</td>
                        <td class="px-4 py-3 text-right">{{ $fmt($total->kabupaten) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection