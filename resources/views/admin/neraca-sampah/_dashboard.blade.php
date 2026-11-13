{{-- resources/views/admin/neraca-sampah/_dashboard.blade.php --}}
@php
    $p  = $pelaporan;
    $f0 = fn ($n) => number_format((float) $n, 0, ',', '.');
    $f1 = fn ($n) => number_format((float) $n, 1, ',', '.');

    // Daftar kepatuhan: [judul, keterangan, item (nama atau objek), tipe]
    $daftar = [
        ['title' => 'Belum melapor bulan ' . ($p['acuanNama'] ?? '-'),
         'note'  => 'Punya laporan berjalan tetapi bulan itu kosong',
         'items' => $p['acuan'] ? $p['belumMelapor']->map(fn ($n) => ['nama' => $n, 'ket' => null]) : collect(),
         'skip'  => !$p['acuan']],
        ['title' => 'Data tidak lengkap',
         'note'  => 'Terisi kurang dari separuh hari di bulan ' . ($p['acuanNama'] ?? '-'),
         'items' => $p['tidakLengkap']->map(fn ($i) => ['nama' => $i->nama, 'ket' => "{$i->hari}/{$i->dari} hari"]),
         'skip'  => !$p['acuan']],
        ['title' => 'Belum punya laporan ' . $p['tahun'],
         'note'  => 'Industri aktif tanpa laporan di tahun ini',
         'items' => $p['tanpaLaporan']->map(fn ($n) => ['nama' => $n, 'ket' => null]),
         'skip'  => false],
        ['title' => 'Residu tinggi',
         'note'  => 'Porsi residu terbesar (timbulan ≥ 100 kg)',
         'items' => $p['residuTinggi']->map(fn ($i) => ['nama' => $i->nama, 'ket' => $f1($i->pct) . '%']),
         'skip'  => false],
    ];
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden" id="pelaporanIndustri">
    <div class="bg-green-700 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-bold text-white">Pelaporan Neraca Sampah Industri</h3>
            <p class="text-xs text-green-100">
                {{ $p['berjalan'] ? 'Tahun berjalan, per ' . now()->translatedFormat('d F Y') : 'Data setahun penuh' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Param tahun_ind terpisah dari kartu TPA (tahun) dan Bank Sampah (tahun_bs) --}}
            <form method="GET" action="{{ route('admin.dashboard') }}#pelaporanIndustri" class="flex items-center gap-2">
                @if(request()->filled('tahun'))
                    <input type="hidden" name="tahun" value="{{ (int) request('tahun') }}">
                @endif
                @if(request()->filled('tahun_bs'))
                    <input type="hidden" name="tahun_bs" value="{{ (int) request('tahun_bs') }}">
                @endif
                <label for="tahunInd" class="text-xs font-medium text-green-100">Tahun</label>
                <select name="tahun_ind" id="tahunInd" onchange="this.form.requestSubmit()"
                        class="bg-white text-slate-800 text-sm font-semibold rounded-lg border-0 py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-green-300">
                    @foreach($p['daftarTahun'] as $th)
                        <option value="{{ $th }}" @selected($th === $p['tahun'])>{{ $th }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.neraca-sampah.index') }}"
               class="text-xs font-semibold text-white underline-offset-2 hover:underline">Buka halaman</a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-slate-100">
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total timbulan {{ $p['tahun'] }}</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f0($p['total']) }} <span class="text-sm font-medium text-slate-500">kg</span></p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Industri melapor</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $p['aktifMelapor'] }} <span class="text-sm font-medium text-slate-500">dari {{ $p['aktifJml'] }} aktif</span></p>
            <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-100"><div class="h-1.5 rounded-full bg-green-600" style="width: {{ $p['pctMelapor'] }}%"></div></div>
            <p class="text-xs text-slate-400 mt-1">{{ $p['pctMelapor'] }}% industri aktif</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Terpilah</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $p['pctTerpilah'] !== null ? $f1($p['pctTerpilah']) . '%' : '-' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">organik + anorganik</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Residu</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $p['pctResidu'] !== null ? $f1($p['pctResidu']) . '%' : '-' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $f0($p['residu']) }} kg</p>
        </div>
    </div>

    {{-- Detail analisa: bisa dibuka dan ditutup --}}
    <div class="border-t border-slate-100">
        <button type="button" id="btnInd" aria-expanded="false" aria-controls="panelInd"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 transition">
            <span>
                <span class="block text-sm font-bold text-slate-900">Analisa Pelaporan Industri</span>
                <span class="block text-xs text-slate-500">Tahun {{ $p['tahun'] }}, klik untuk menampilkan</span>
            </span>
            <svg id="iconInd" class="w-5 h-5 text-slate-500 transition-transform duration-200"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div id="panelInd" class="hidden border-t border-slate-100 p-5 space-y-6">
            @if($p['ringkas'])
                <ul class="space-y-1.5 text-sm text-slate-600 list-disc pl-5">
                    @foreach($p['ringkas'] as $r) <li>{{ $r }}</li> @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-400">Belum ada data neraca sampah untuk tahun {{ $p['tahun'] }}.</p>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Timbulan per Bulan (kg): {{ $p['tahun'] }} vs {{ $p['tahun'] - 1 }}</h4>
                    <div class="h-64"><canvas id="cIndBulan"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Jumlah Industri yang Melapor per Bulan</h4>
                    <div class="h-64"><canvas id="cIndMelapor"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Komposisi Timbulan</h4>
                    <div class="h-64"><canvas id="cIndKomposisi"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">10 Industri dengan Timbulan Terbesar (kg)</h4>
                    <div class="h-64"><canvas id="cIndTop"></canvas></div>
                </div>
            </div>

            {{-- Daftar kepatuhan dan kualitas pengisian --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($daftar as $d)
                    @continue($d['skip'])
                    <div class="rounded-xl border border-slate-200/80 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-sm font-bold text-slate-900">{{ $d['title'] }}</h4>
                            <span class="text-xs font-semibold text-slate-500">{{ $d['items']->count() }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $d['note'] }}</p>
                        @if($d['items']->isEmpty())
                            <p class="mt-3 text-sm text-slate-400">Tidak ada.</p>
                        @else
                            <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                                @foreach($d['items']->take(8) as $i)
                                    <li class="flex items-start justify-between gap-2">
                                        <span class="min-w-0 break-words">{{ $i['nama'] }}</span>
                                        @if($i['ket'])<span class="shrink-0 text-xs font-semibold text-slate-500">{{ $i['ket'] }}</span>@endif
                                    </li>
                                @endforeach
                            </ul>
                            @if($d['items']->count() > 8)
                                <p class="mt-2 text-xs text-slate-400">+{{ $d['items']->count() - 8 }} lainnya</p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const d     = @json($p['chart']);
    const btn   = document.getElementById('btnInd');
    const panel = document.getElementById('panelInd');
    const icon  = document.getElementById('iconInd');
    const KEY   = 'dashboardIndustriOpen';

    const num = v => Number(v).toLocaleString('id-ID');
    const opt = { responsive: true, maintainAspectRatio: false };
    const COL = { o: '#15803d', a: '#2563eb', r: '#f59e0b' };

    // Chart.js sudah dimuat kartu TPA; bila belum ada, muat sekali dan bagikan ke kartu lain
    function muatChart(cb) {
        if (window.Chart) return cb();
        if (!window.__chartMuat) {
            window.__chartMuat = new Promise(res => {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
                s.onload = res;
                document.head.appendChild(s);
            });
        }
        window.__chartMuat.then(cb);
    }

    let rendered = false;
    function renderCharts() {
        if (rendered) return;
        rendered = true;

        new Chart('cIndBulan', {
            data: { labels: d.labels, datasets: [
                { type: 'bar',  label: 'Organik',   data: d.organik,   backgroundColor: COL.o, stack: 's' },
                { type: 'bar',  label: 'Anorganik', data: d.anorganik, backgroundColor: COL.a, stack: 's' },
                { type: 'bar',  label: 'Residu',    data: d.residu,    backgroundColor: COL.r, stack: 's' },
                { type: 'line', label: 'Total ' + d.lalu, data: d.totalLalu, borderColor: '#64748b', backgroundColor: '#64748b', tension: .3, borderDash: [5, 4] }
            ]},
            options: { ...opt,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { callback: v => num(v) } } },
                plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + num(c.parsed.y) + ' kg' } } } }
        });

        new Chart('cIndMelapor', {
            data: { labels: d.labels, datasets: [
                { type: 'bar',  label: 'Industri melapor', data: d.melapor, backgroundColor: '#0f766e' },
                { type: 'line', label: 'Industri aktif',   data: d.labels.map(() => d.aktif), borderColor: '#f59e0b', backgroundColor: '#f59e0b', pointRadius: 0, borderDash: [5, 4] }
            ]},
            options: { ...opt, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        new Chart('cIndKomposisi', {
            type: 'doughnut',
            data: { labels: ['Organik', 'Anorganik', 'Residu'],
                    datasets: [{ data: d.porsi, backgroundColor: [COL.o, COL.a, COL.r] }] },
            options: { ...opt, plugins: { legend: { position: 'bottom' },
                       tooltip: { callbacks: { label: c => c.label + ': ' + num(c.parsed) + ' kg' } } } }
        });

        new Chart('cIndTop', {
            type: 'bar',
            data: { labels: d.topNama, datasets: [
                { label: 'Organik',   data: d.topO, backgroundColor: COL.o },
                { label: 'Anorganik', data: d.topA, backgroundColor: COL.a },
                { label: 'Residu',    data: d.topR, backgroundColor: COL.r }
            ]},
            options: { ...opt, indexAxis: 'y',
                scales: { x: { stacked: true, beginAtZero: true, ticks: { callback: v => num(v) } }, y: { stacked: true } },
                plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + num(c.parsed.x) + ' kg' } } } }
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