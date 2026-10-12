@extends('layouts.admin')

@section('title', 'Neraca Sampah')

@section('content')
@php
    // Helper format, ikut terbawa ke semua partial
    $ton = fn ($v) => number_format($v, $bulanAktif ? 3 : 2, ',', '.');
    $pct = fn ($v) => is_null($v) ? '-' : number_format($v * 100, 2, ',', '.') . '%';

    $tahunLabel = $laporan->periode_mulai->year === $laporan->periode_selesai->year
        ? (string) $laporan->periode_mulai->year
        : $laporan->periode_mulai->year . '/' . $laporan->periode_selesai->year;
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex justify-between items-start">
        <div>
            <a href="{{ route('admin.neraca-sampah.industri', [$laporan->industri_id, 'laporan' => $laporan->id]) }}" class="text-xs text-slate-500 hover:text-slate-700">&larr; {{ $laporan->industri->nama }}</a>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Neraca Sampah Tahun {{ $tahunLabel }}</h1>
            <p class="text-slate-500 mt-1">
                {{ $laporan->industri->nama }} ·
                {{ $laporan->periode_mulai->translatedFormat('F Y') }} – {{ $laporan->periode_selesai->translatedFormat('F Y') }}
            </p>
        </div>
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $laporan->status === 'final' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
            {{ $laporan->status === 'final' ? 'Final' : 'Draft' }}
        </span>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Tab --}}
    <div class="flex flex-wrap gap-1 border-b border-slate-200">
        @foreach ([
            'neraca'     => 'Rekap Neraca',
            'bulanan'    => 'Rekap Terkelola',
            'area'       => 'Rekap Area',
            'input'      => 'Input Harian',
            'pengaturan' => 'Pengaturan & Tanda Tangan',
        ] as $key => $label)
            <a href="{{ route('admin.neraca-sampah.show', [$laporan, 'tab' => $key]) }}"
               class="px-4 py-2.5 text-sm font-medium -mb-px {{ $tab === $key ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

        @if($tab === 'neraca')
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Tampilkan</span>
            <a href="{{ route('admin.neraca-sampah.show', [$laporan, 'tab' => 'neraca']) }}"
               class="px-3 py-1.5 text-sm font-medium rounded-lg {{ !$bulanAktif ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Setahun</a>
            @foreach($laporan->bulanList() as $ym => $tgl)
                <a href="{{ route('admin.neraca-sampah.show', [$laporan, 'tab' => 'neraca', 'bulan' => $ym]) }}"
                   class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $bulanAktif === $ym ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $tgl->translatedFormat('F') }}</a>
            @endforeach
        </div>
    @endif

    @includeWhen($tab === 'neraca',     'admin.neraca-sampah.partials.neraca')
    @includeWhen($tab === 'bulanan',    'admin.neraca-sampah.partials.rekap-bulanan')
    @includeWhen($tab === 'area',       'admin.neraca-sampah.partials.rekap-area')
    @includeWhen($tab === 'input',      'admin.neraca-sampah.partials.pilih-input')
    @includeWhen($tab === 'pengaturan', 'admin.neraca-sampah.partials.pengaturan')
</div>
@endsection