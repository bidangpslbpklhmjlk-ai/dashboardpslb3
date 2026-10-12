@extends('layouts.admin')

@section('title', 'Input Harian Neraca Sampah')

@section('content')
@php
    // Tanggal yang boleh diisi: dalam bulan ini DAN dalam periode laporan
    $hari = [];
    $c = $awal->copy();
    $akhir = $awal->copy()->endOfMonth();
    while ($c <= $akhir) {
        if ($c >= $laporan->periode_mulai && $c <= $laporan->periode_selesai) $hari[] = $c->copy();
        $c->addDay();
    }

    $ym = $awal->format('Y-m');
    $bulanKeys = array_keys($laporan->bulanList());
    $idx = array_search($ym, $bulanKeys);
    $prev = $bulanKeys[$idx - 1] ?? null;
    $next = $bulanKeys[$idx + 1] ?? null;
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <a href="{{ route('admin.neraca-sampah.show', [$laporan, 'tab' => 'input']) }}" class="text-xs text-slate-500 hover:text-slate-700">&larr; Kembali ke pilihan bulan</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $area->nama }}</h1>
        <p class="text-slate-500 mt-1">{{ $laporan->industri->nama }} · {{ $awal->translatedFormat('F Y') }} · satuan kg/hari
            · <strong>{{ $existing->count() }}</strong> dari {{ count($hari) }} tanggal sudah terisi (baris hijau)</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Pindah area --}}
    <div class="flex flex-wrap gap-1">
        @foreach($areas as $a)
            <a href="{{ route('admin.neraca-sampah.harian', [$laporan, $ym, $a]) }}"
               class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $a->id === $area->id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $a->nama }}
            </a>
        @endforeach
    </div>

    <form action="{{ route('admin.neraca-sampah.harian.simpan', [$laporan, $ym, $area]) }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf
        @method('PUT')

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left w-24">Tanggal</th>
                        <th class="px-4 py-3 text-right">Organik Terpilah</th>
                        <th class="px-4 py-3 text-right">Anorganik Terpilah</th>
                        <th class="px-4 py-3 text-right">Residu</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($hari as $d)
                        @php
                            $key = $d->format('Y-m-d');
                            $row = $existing[$key] ?? null;
                        @endphp
                            <tr data-row class="hover:bg-slate-50">
                            <td class="px-4 py-2 font-medium text-slate-700">{{ $d->format('d') }}</td>
                            @foreach(['organik', 'anorganik', 'residu'] as $jenis)
                                <td class="px-4 py-1.5">
                                    <input type="number" step="0.001" min="0"
                                           name="data[{{ $key }}][{{ $jenis }}_kg]"
                                           data-jenis="{{ $jenis }}"
                                           value="{{ old("data.$key.{$jenis}_kg", $row ? (float) $row->{$jenis . '_kg'} : '') }}"
                                           oninput="hitung()"
                                           class="w-full text-right border border-slate-300 rounded-md px-2 py-1.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </td>
                            @endforeach
                            <td class="px-4 py-2 text-right font-semibold text-slate-700 row-total">0</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold text-slate-800 border-t-2 border-slate-300">
                    <tr>
                        <td class="px-4 py-3">Total (kg)</td>
                        <td class="px-4 py-3 text-right" id="tot-organik">0</td>
                        <td class="px-4 py-3 text-right" id="tot-anorganik">0</td>
                        <td class="px-4 py-3 text-right" id="tot-residu">0</td>
                        <td class="px-4 py-3 text-right" id="tot-all">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center">
            <div class="flex gap-2 text-sm">
                @if($prev)
                    <a href="{{ route('admin.neraca-sampah.harian', [$laporan, $prev, $area]) }}" class="text-slate-600 hover:text-slate-900">&larr; Bulan sebelumnya</a>
                @endif
                @if($next)
                    <a href="{{ route('admin.neraca-sampah.harian', [$laporan, $next, $area]) }}" class="text-slate-600 hover:text-slate-900 ml-4">Bulan berikutnya &rarr;</a>
                @endif
            </div>
            <button type="submit" class="px-5 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Simpan</button>
        </div>
    </form>
</div>

<script>
    const fmt = n => n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });

    function hitung() {
        const t = { organik: 0, anorganik: 0, residu: 0 };
        document.querySelectorAll('tr[data-row]').forEach(tr => {
            let sum = 0;
            tr.querySelectorAll('input[data-jenis]').forEach(i => {
                const v = parseFloat(i.value) || 0;
                t[i.dataset.jenis] += v;
                sum += v;
            });
            tr.querySelector('.row-total').textContent = fmt(sum);
        });
        document.getElementById('tot-organik').textContent = fmt(t.organik);
        document.getElementById('tot-anorganik').textContent = fmt(t.anorganik);
        document.getElementById('tot-residu').textContent = fmt(t.residu);
        document.getElementById('tot-all').textContent = fmt(t.organik + t.anorganik + t.residu);
    }
    hitung();
</script>
@endsection