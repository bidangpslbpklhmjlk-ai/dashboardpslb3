@extends('layouts.admin')
@section('title', 'Dashboard')

@php
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $kondisiLabel = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak ringan', 'rusak_berat' => 'Rusak berat', 'tidak_beroperasi' => 'Tidak beroperasi', 'belum_diisi' => 'Belum diisi'];

    // Analisis singkat otomatis
    $analisis = [];
    if ($tpa) {
        $n = count($tpa['ton']);
        if ($n >= 2 && $tpa['ton'][$n-2] > 0) {
            $d = round(($tpa['ton'][$n-1] - $tpa['ton'][$n-2]) / $tpa['ton'][$n-2] * 100, 1);
            $analisis[] = 'Volume sampah masuk TPA bulan ini ' . ($d >= 0 ? 'naik ' : 'turun ') . abs($d) . '% dibanding bulan lalu.';
        }
        if ($tpa['unpaidJml'] > 0) {
            $analisis[] = "Ada {$tpa['unpaidJml']} invoice retribusi TPA belum dibayar senilai " . $rp($tpa['unpaidNom']) . '.';
        }
    }
    if ($armada && $armada['rusakAktif'] > 0) {
        $analisis[] = "{$armada['rusakAktif']} armada sedang rusak dan perlu pengalihan/perbaikan.";
    }
    if ($pungutan && $pungutan['belumLunas'] > 0) {
        $analisis[] = "Pungutan belum lunas: {$pungutan['belumLunas']} invoice (" . $rp($pungutan['nomBelumLunas']) . ').';
    }
    if ($neraca && $neraca['pctResidu'] !== null) {
        $analisis[] = "Dari data neraca 6 bulan terakhir, {$neraca['pctResidu']}% timbulan berupa residu dan {$neraca['pctOlah']}% terkelola (organik + anorganik).";
    }
@endphp

@section('content')
<div class="p-6 lg:p-8 space-y-6">

    {{-- Analisis ringkas --}}
    @if($analisis)
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-3">Analisis Ringkas</h3>
            <ul class="space-y-1.5 text-sm text-slate-600 list-disc pl-5">
                @foreach($analisis as $a) <li>{{ $a }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @if($armada)
            @include('admin.partials.stat', ['label' => 'Armada Aktif', 'value' => $armada['aktif'], 'sub' => 'dari ' . $armada['total'] . ' terdata'])
            @include('admin.partials.stat', ['label' => 'Armada Rusak', 'value' => $armada['rusakAktif'], 'sub' => 'belum selesai', 'warn' => $armada['rusakAktif'] > 0])
        @endif
        @if($rute)
            @include('admin.partials.stat', ['label' => 'Rute / Lokasi', 'value' => $rute['rute'] . ' / ' . $rute['lokasi'], 'sub' => $rute['armadaTerjadwal'] . ' armada terjadwal'])
            @include('admin.partials.stat', ['label' => 'Check-in Hari Ini', 'value' => $rute['checkinHariIni'], 'sub' => 'pengambilan sampah'])
        @endif
        @if($tpa)
            @include('admin.partials.stat', ['label' => 'Retribusi TPA (bulan ini)', 'value' => $rp($tpa['paidBulan']), 'sub' => $tpa['unpaidJml'] . ' belum dibayar'])
        @endif
        @if($pungutan)
            @include('admin.partials.stat', ['label' => 'Pungutan Hari Ini', 'value' => $pungutan['hariIni'], 'sub' => $pungutan['selesaiBln'] . ' selesai, ' . $pungutan['batalBln'] . ' batal (bulan ini)'])
        @endif
        @if($industri)
            @include('admin.partials.stat', ['label' => 'Industri Aktif', 'value' => $industri['aktif'], 'sub' => $industri['punyaLaporan'] . ' punya laporan ' . now()->year])
        @endif
        @if($neraca)
            @include('admin.partials.stat', ['label' => 'Timbulan (6 bln)', 'value' => number_format($neraca['totalKg'] / 1000, 1, ',', '.') . ' ton', 'sub' => 'neraca sampah industri'])
        @endif
    </div>

    {{-- Grafik --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @if($tpa)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3">TPA: Volume (ton) &amp; Retribusi Terbayar</h3>
                <div class="h-64"><canvas id="cTpa"></canvas></div>
            </div>
        @endif
        @if($pungutan)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Pungutan Terbayar per Bulan</h3>
                <div class="h-64"><canvas id="cPungutan"></canvas></div>
            </div>
        @endif
        @if($neraca)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Neraca Sampah per Bulan (kg)</h3>
                <div class="h-64"><canvas id="cNeraca"></canvas></div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3">5 Industri dengan Timbulan Terbesar ({{ now()->year }})</h3>
                @forelse($neraca['top'] as $row)
                    <div class="flex justify-between text-sm py-2 border-b border-slate-100 last:border-0">
                        <span class="text-slate-700 truncate pr-3">{{ $row['nama'] }}</span>
                        <span class="font-semibold text-slate-900">{{ number_format($row['kg'], 1, ',', '.') }} kg</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada data harian tahun ini.</p>
                @endforelse
            </div>
        @endif
        @if($armada)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Kondisi Armada Aktif</h3>
                <div class="h-64"><canvas id="cArmada"></canvas></div>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const opt = { responsive: true, maintainAspectRatio: false };
    const mk = (id, cfg) => { const el = document.getElementById(id); if (el) new Chart(el, cfg); };

    @if($tpa)
    mk('cTpa', { data: { labels: @json($tpa['labels']), datasets: [
        { type: 'bar',  label: 'Volume (ton)', data: @json($tpa['ton']), backgroundColor: '#15803d', yAxisID: 'y' },
        { type: 'line', label: 'Retribusi (Rp)', data: @json($tpa['retribusi']), borderColor: '#f59e0b', yAxisID: 'y1' }
    ]}, options: { ...opt, scales: { y: { beginAtZero: true }, y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } } } } });
    @endif

    @if($pungutan)
    mk('cPungutan', { type: 'bar', data: { labels: @json($pungutan['labels']), datasets: [
        { label: 'Rp', data: @json($pungutan['nilai']), backgroundColor: '#0f766e' }
    ]}, options: { ...opt, plugins: { legend: { display: false } } } });
    @endif

    @if($neraca)
    mk('cNeraca', { type: 'bar', data: { labels: @json($neraca['labels']), datasets: [
        { label: 'Organik',   data: @json($neraca['organik']),   backgroundColor: '#16a34a' },
        { label: 'Anorganik', data: @json($neraca['anorganik']), backgroundColor: '#2563eb' },
        { label: 'Residu',    data: @json($neraca['residu']),    backgroundColor: '#94a3b8' }
    ]}, options: { ...opt, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } } });
    @endif

    @if($armada)
    @php
        $lbl = collect($armada['kondisi'])->keys()->map(fn ($k) => $kondisiLabel[$k] ?? $k)->values();
    @endphp
    mk('cArmada', { type: 'doughnut', data: { labels: @json($lbl), datasets: [
        { data: @json(array_values($armada['kondisi'])), backgroundColor: ['#16a34a', '#f59e0b', '#dc2626', '#64748b', '#cbd5e1'] }
    ]}, options: opt });
    @endif
});
</script>
@endsection