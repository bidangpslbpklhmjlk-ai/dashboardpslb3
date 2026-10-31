@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="p-6 lg:p-8 space-y-6">

@if($tpa)
    @php
        $f   = fn ($n) => number_format($n, 2, ',', '.');
        $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $tahun = $tpa['tahun'];
        $lalu  = $tahun - 1;
        $batas = $tpa['berjalan'] ? now()->month : 12;   // jumlah bulan yang sudah berjalan

        $tonBerjalan = array_slice($tpa['bulanan']['ton'], 0, $batas);
        $tonLaluSama = array_sum(array_slice($tpa['bulananLalu']['ton'], 0, $batas));

        // Ringkasan otomatis
        $ringkas = [];
        if (array_sum($tonBerjalan) > 0) {
            $iMax = array_search(max($tonBerjalan), $tonBerjalan);
            $iMin = array_search(min($tonBerjalan), $tonBerjalan);
            $ringkas[] = "Volume tertinggi terjadi pada bulan {$bln[$iMax]} ({$f($tonBerjalan[$iMax])} ton).";
            $ringkas[] = "Volume terendah terjadi pada bulan {$bln[$iMin]} ({$f($tonBerjalan[$iMin])} ton).";
        }
        if ($tonLaluSama > 0) {
            $selisih   = round(($tpa['tahunTon'] - $tonLaluSama) / $tonLaluSama * 100, 1);
            $ringkas[] = "Dibanding periode yang sama tahun {$lalu}, volume " . ($selisih >= 0 ? 'naik ' : 'turun ') . abs($selisih) . '%.';
        }

        // Data untuk Chart.js
        $chartData = [
            'labels'     => $bln,
            'tahun'      => $tahun,
            'lalu'       => $lalu,
            'ton'        => $tpa['bulanan']['ton'],
            'tonLalu'    => $tpa['bulananLalu']['ton'],
            'trip'       => $tpa['bulanan']['trip'],
            'rataTrip'   => $tpa['bulanan']['rataTrip'],
            'retribusi'  => $tpa['bulanan']['retribusi'],
        ];
    @endphp

    {{-- ============ Kartu Sampah Masuk TPA ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="bg-green-700 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-white">Sampah Masuk TPA</h3>
                <p class="text-xs text-green-100">
                    @if($tpa['berjalan'])
                        Tahun berjalan, per {{ now()->translatedFormat('d F Y') }}
                    @else
                        Data setahun penuh
                    @endif
                </p>
            </div>
            <form id="formTahun" method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <label for="tahun" class="text-xs font-medium text-green-100">Tahun</label>
                <select name="tahun" id="tahun" onchange="this.form.requestSubmit()"
                        class="bg-white text-slate-800 text-sm font-semibold rounded-lg border-0 py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-green-300">
                    @foreach($tpa['daftarTahun'] as $th)
                        <option value="{{ $th }}" @selected($th === $tahun)>{{ $th }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-slate-100">
            <div class="p-5">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata per hari</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f($tpa['rataHari']) }} <span class="text-sm font-medium text-slate-500">ton</span></p>
            </div>
            <div class="p-5">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata per bulan</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f($tpa['rataBulan']) }} <span class="text-sm font-medium text-slate-500">ton</span></p>
            </div>
            <div class="p-5">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata per trip</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f($tpa['rataTrip']) }} <span class="text-sm font-medium text-slate-500">ton</span></p>
            </div>
            <div class="p-5">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total tahun {{ $tahun }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f($tpa['tahunTon']) }} <span class="text-sm font-medium text-slate-500">ton</span></p>
                <p class="text-xs text-slate-400 mt-0.5">{{ number_format($tpa['tahunTrip'], 0, ',', '.') }} trip</p>
            </div>
        </div>
    </div>

    {{-- ============ Analisa Grafis (dropdown) ============ --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden" id="analisa">
        <button type="button" id="btnAnalisa" aria-expanded="false" aria-controls="panelAnalisa"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 transition">
            <span>
                <span class="block text-sm font-bold text-slate-900">Analisa Grafis Sampah Masuk TPA</span>
                <span class="block text-xs text-slate-500">Tahun {{ $tahun }}, klik untuk menampilkan</span>
            </span>
            <svg id="iconAnalisa" class="w-5 h-5 text-slate-500 transition-transform duration-200"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div id="panelAnalisa" class="hidden border-t border-slate-100 p-5 space-y-6">
            @if($ringkas)
                <ul class="space-y-1.5 text-sm text-slate-600 list-disc pl-5">
                    @foreach($ringkas as $r) <li>{{ $r }}</li> @endforeach
                </ul>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Volume per Bulan (ton): {{ $tahun }} vs {{ $lalu }}</h4>
                    <div class="h-64"><canvas id="cTpaTon"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Jumlah Trip per Bulan</h4>
                    <div class="h-64"><canvas id="cTpaTrip"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Rata-rata Muatan per Trip (ton)</h4>
                    <div class="h-64"><canvas id="cTpaRata"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Retribusi Terbayar per Bulan (Rp)</h4>
                    <div class="h-64"><canvas id="cTpaRetribusi"></canvas></div>
                </div>
            </div>
        </div>
    </div>
@endif

</div>

@if($tpa)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const d     = @json($chartData);
    const btn   = document.getElementById('btnAnalisa');
    const panel = document.getElementById('panelAnalisa');
    const icon  = document.getElementById('iconAnalisa');
    const KEY   = 'dashboardAnalisaOpen';

    const rp  = v => 'Rp ' + Number(v).toLocaleString('id-ID');
    const opt = { responsive: true, maintainAspectRatio: false };
    const noLegend = { legend: { display: false } };
    const yZero = { y: { beginAtZero: true } };

    let rendered = false;
    function renderCharts() {
        if (rendered) return;
        rendered = true;

        new Chart('cTpaTon', {
            data: { labels: d.labels, datasets: [
                { type: 'bar',  label: d.tahun, data: d.ton,     backgroundColor: '#15803d' },
                { type: 'line', label: d.lalu,  data: d.tonLalu, borderColor: '#f59e0b', backgroundColor: '#f59e0b', tension: .3 }
            ]},
            options: { ...opt, scales: yZero }
        });

        new Chart('cTpaTrip', {
            type: 'bar',
            data: { labels: d.labels, datasets: [{ label: 'Trip', data: d.trip, backgroundColor: '#0f766e' }] },
            options: { ...opt, plugins: noLegend, scales: yZero }
        });

        new Chart('cTpaRata', {
            type: 'line',
            data: { labels: d.labels, datasets: [
                { label: 'ton/trip', data: d.rataTrip, borderColor: '#2563eb', backgroundColor: '#2563eb', tension: .3 }
            ]},
            options: { ...opt, plugins: noLegend, scales: yZero }
        });

        new Chart('cTpaRetribusi', {
            type: 'bar',
            data: { labels: d.labels, datasets: [{ label: 'Retribusi', data: d.retribusi, backgroundColor: '#f59e0b' }] },
            options: {
                ...opt,
                plugins: { ...noLegend, tooltip: { callbacks: { label: c => rp(c.parsed.y) } } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => rp(v) } } }
            }
        });
    }

    function toggle(open) {
        panel.classList.toggle('hidden', !open);
        icon.classList.toggle('rotate-180', open);
        btn.setAttribute('aria-expanded', open);
        sessionStorage.setItem(KEY, open ? '1' : '0');
        if (open) renderCharts();   // dibuat saat panel terlihat agar ukuran canvas benar
    }

    btn.addEventListener('click', () => toggle(panel.classList.contains('hidden')));

    // Setelah ganti tahun, panel kembali ke kondisi terakhir (terbuka/tertutup)
    if (sessionStorage.getItem(KEY) === '1') toggle(true);
});
</script>
@endif
@endsection