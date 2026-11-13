{{-- resources/views/admin/bank-sampah/_dashboard.blade.php --}}
@php
    $b  = $bankSampah;
    $f0 = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden" id="bankSampah">
    <div class="bg-green-700 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-bold text-white">Bank Sampah</h3>
            <p class="text-xs text-green-100">
                {{ $b['berjalan'] ? 'Tahun berjalan, per ' . now()->translatedFormat('d F Y') : 'Data setahun penuh' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Pilihan tahun sendiri, terpisah dari kartu TPA (param tahun_bs). Param tahun milik TPA dipertahankan. --}}
            <form method="GET" action="{{ route('admin.dashboard') }}#bankSampah" class="flex items-center gap-2">
                @if(request()->filled('tahun'))
                    <input type="hidden" name="tahun" value="{{ (int) request('tahun') }}">
                @endif
                @if(request()->filled('tahun_ind'))
                <input type="hidden" name="tahun_ind" value="{{ (int) request('tahun_ind') }}">
                @endif
                <label for="tahunBank" class="text-xs font-medium text-green-100">Tahun</label>
                <select name="tahun_bs" id="tahunBank" onchange="this.form.requestSubmit()"
                        class="bg-white text-slate-800 text-sm font-semibold rounded-lg border-0 py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-green-300">
                    @foreach($b['daftarTahun'] as $th)
                        <option value="{{ $th }}" @selected($th === $b['tahun'])>{{ $th }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.bank-sampah.index', ['tahun' => $b['tahun']]) }}"
               class="text-xs font-semibold text-white underline-offset-2 hover:underline">Buka halaman</a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-slate-100">
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total tahun {{ $b['tahun'] }}</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f0($b['total']) }} <span class="text-sm font-medium text-slate-500">kg</span></p>
            @if($b['kabupaten'] > 0)
                <p class="text-xs text-slate-400 mt-0.5">termasuk {{ $f0($b['kabupaten']) }} kg tingkat kabupaten</p>
            @endif
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Desa menimbang</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $b['desaAktif'] }} <span class="text-sm font-medium text-slate-500">dari {{ $b['desaTotal'] }}</span></p>
            <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-100"><div class="h-1.5 rounded-full bg-green-600" style="width: {{ $b['persenDesa'] }}%"></div></div>
            <p class="text-xs text-slate-400 mt-1">{{ $b['persenDesa'] }}% desa</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">BSU menimbang</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $b['bsuMenimbang'] }} <span class="text-sm font-medium text-slate-500">dari {{ $b['bsuAktif'] }} aktif</span></p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata per bulan</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f0($b['rataBulan']) }} <span class="text-sm font-medium text-slate-500">kg</span></p>
            <p class="text-xs text-slate-400 mt-0.5">bulan yang sudah terisi</p>
        </div>
    </div>

    {{-- Detail analisa: bisa dibuka dan ditutup --}}
    <div class="border-t border-slate-100">
        <button type="button" id="btnBank" aria-expanded="false" aria-controls="panelBank"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 transition">
            <span>
                <span class="block text-sm font-bold text-slate-900">Analisa Bank Sampah</span>
                <span class="block text-xs text-slate-500">Tahun {{ $b['tahun'] }}, klik untuk menampilkan</span>
            </span>
            <svg id="iconBank" class="w-5 h-5 text-slate-500 transition-transform duration-200"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div id="panelBank" class="hidden border-t border-slate-100 p-5 space-y-6">
            @if($b['ringkas'])
                <ul class="space-y-1.5 text-sm text-slate-600 list-disc pl-5">
                    @foreach($b['ringkas'] as $r) <li>{{ $r }}</li> @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-400">Belum ada data timbangan untuk tahun {{ $b['tahun'] }}.</p>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Volume per Bulan (kg): {{ $b['tahun'] }} vs {{ $b['tahun'] - 1 }}</h4>
                    <div class="h-64"><canvas id="cBankBulan"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">10 Kecamatan dengan Volume Terbesar (kg)</h4>
                    <div class="h-64"><canvas id="cBankKec"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4 xl:col-span-2">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Persentase Desa yang Sudah Menimbang per Kecamatan</h4>
                    <div style="height: 30rem"><canvas id="cBankCakupan"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const d     = @json($b['chart']);
    const btn   = document.getElementById('btnBank');
    const panel = document.getElementById('panelBank');
    const icon  = document.getElementById('iconBank');
    const KEY   = 'dashboardBankOpen';

    const num = v => Number(v).toLocaleString('id-ID');
    const opt = { responsive: true, maintainAspectRatio: false };
    const noLegend = { legend: { display: false } };

    // Chart.js sudah dimuat kartu TPA; bila kartu itu tidak tampil, muat sendiri
    function muatChart(cb) {
        if (window.Chart) return cb();
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
        s.onload = cb;
        document.head.appendChild(s);
    }

    let rendered = false;
    function renderCharts() {
        if (rendered) return;
        rendered = true;

        new Chart('cBankBulan', {
            data: { labels: d.labels, datasets: [
                { type: 'bar',  label: d.tahun, data: d.kg,     backgroundColor: '#15803d' },
                { type: 'line', label: d.lalu,  data: d.kgLalu, borderColor: '#f59e0b', backgroundColor: '#f59e0b', tension: .3 }
            ]},
            options: { ...opt, scales: { y: { beginAtZero: true, ticks: { callback: v => num(v) } } },
                       plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + num(c.parsed.y) + ' kg' } } } }
        });

        new Chart('cBankKec', {
            type: 'bar',
            data: { labels: d.topNama, datasets: [{ label: 'kg', data: d.topKg, backgroundColor: '#0f766e' }] },
            options: { ...opt, indexAxis: 'y',
                       plugins: { ...noLegend, tooltip: { callbacks: { label: c => num(c.parsed.x) + ' kg' } } },
                       scales: { x: { beginAtZero: true, ticks: { callback: v => num(v) } } } }
        });

        new Chart('cBankCakupan', {
            type: 'bar',
            data: { labels: d.cakNama, datasets: [{ label: '% desa menimbang', data: d.cakPersen, backgroundColor: '#2563eb' }] },
            options: { ...opt, indexAxis: 'y',
                       plugins: { ...noLegend, tooltip: { callbacks: { label: c => c.parsed.x + '% desa' } } },
                       scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } } }
        });
    }

    function toggle(open) {
        panel.classList.toggle('hidden', !open);
        icon.classList.toggle('rotate-180', open);
        btn.setAttribute('aria-expanded', open);
        sessionStorage.setItem(KEY, open ? '1' : '0');
        if (open) muatChart(renderCharts);   // dibuat saat panel terlihat agar ukuran canvas benar
    }

    btn.addEventListener('click', () => toggle(panel.classList.contains('hidden')));
    if (sessionStorage.getItem(KEY) === '1') toggle(true);
});
</script>