{{-- resources/views/admin/neraca-sampah/_analisis-pelaporan.blade.php
     Kartu analisis pelaporan industri. Dipasang dengan: @include('admin.neraca-sampah._analisis-pelaporan') --}}
@if(auth()->user()->canAccessMenu('neraca-sampah'))
@php
    $tahunLap = (int) request('tahun_lap', now()->year);
    if ($tahunLap < 2000 || $tahunLap > now()->year) $tahunLap = now()->year;
    try {
        $pl = app(\App\Services\PelaporanIndustriAnalisis::class)->data($tahunLap);
    } catch (\Throwable $e) {
        report($e);
        $pl = null;
    }
    $f0 = fn ($n) => number_format((float) $n, 0, ',', '.');
    $f1 = fn ($n) => number_format((float) $n, 1, ',', '.');
@endphp

@if($pl)
<div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden" id="pelaporan">
    <div class="bg-green-700 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-bold text-white">Pelaporan Neraca Sampah Industri</h3>
            <p class="text-xs text-green-100">{{ $pl['berjalan'] ? 'Tahun berjalan, per ' . now()->translatedFormat('d F Y') : 'Data setahun penuh' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2" action="{{ url()->current() }}#pelaporan">
                @foreach(request()->except('tahun_lap', 'page') as $k => $v)
                    @if(is_scalar($v) && $v !== '')<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                @endforeach
                <label for="tahunLap" class="text-xs font-medium text-green-100">Tahun</label>
                <select name="tahun_lap" id="tahunLap" onchange="this.form.requestSubmit()"
                        class="bg-white text-slate-800 text-sm font-semibold rounded-lg border-0 py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-green-300">
                    @foreach($pl['daftarTahun'] as $th)
                        <option value="{{ $th }}" @selected($th === $pl['tahun'])>{{ $th }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.neraca-sampah.index') }}" class="text-xs font-semibold text-white underline-offset-2 hover:underline">Buka Neraca Sampah</a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-slate-100">
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total timbulan {{ $pl['tahun'] }}</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $f0($pl['total']) }} <span class="text-sm font-medium text-slate-500">kg</span></p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $f1($pl['total'] / 1000) }} ton</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Industri melapor</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $pl['aktifMelapor'] }} <span class="text-sm font-medium text-slate-500">dari {{ $pl['aktifJml'] }} aktif</span></p>
            <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-100"><div class="h-1.5 rounded-full bg-green-600" style="width: {{ $pl['pctMelapor'] }}%"></div></div>
            <p class="text-xs text-slate-400 mt-1">{{ $pl['pctMelapor'] }}% industri aktif</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Porsi residu</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $pl['pctResidu'] !== null ? $f1($pl['pctResidu']) . '%' : '-' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $pl['pctTerpilah'] !== null ? $f1($pl['pctTerpilah']) . '% terpilah' : 'belum ada data' }}</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Belum melapor {{ $pl['acuanNama'] ? 'bulan ' . $pl['acuanNama'] : '' }}</p>
            <p class="mt-1 text-2xl font-bold {{ $pl['belumMelapor']->isNotEmpty() ? 'text-red-600' : 'text-slate-900' }}">{{ $pl['acuan'] ? $pl['belumMelapor']->count() : '-' }} <span class="text-sm font-medium text-slate-500">industri</span></p>
            <p class="text-xs text-slate-400 mt-0.5">punya laporan tetapi belum ada data</p>
        </div>
    </div>

    <div class="border-t border-slate-100">
        <button type="button" id="btnPelaporan" aria-expanded="false" aria-controls="panelPelaporan"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 transition">
            <span>
                <span class="block text-sm font-bold text-slate-900">Analisa Pelaporan Industri</span>
                <span class="block text-xs text-slate-500">Tahun {{ $pl['tahun'] }}, klik untuk menampilkan</span>
            </span>
            <svg id="iconPelaporan" class="w-5 h-5 text-slate-500 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div id="panelPelaporan" class="hidden border-t border-slate-100 p-5 space-y-6">
            @if($pl['ringkas'])
                <ul class="space-y-1.5 text-sm text-slate-600 list-disc pl-5">
                    @foreach($pl['ringkas'] as $r) <li>{{ $r }}</li> @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-400">Belum ada data neraca sampah untuk tahun {{ $pl['tahun'] }}.</p>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Timbulan per Bulan (kg): {{ $pl['tahun'] }} vs {{ $pl['tahun'] - 1 }}</h4>
                    <div class="h-64"><canvas id="cPelBulan"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Industri yang Mengisi Data per Bulan</h4>
                    <div class="h-64"><canvas id="cPelMelapor"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">10 Industri dengan Timbulan Terbesar (kg)</h4>
                    <div class="h-80"><canvas id="cPelTop"></canvas></div>
                </div>
                <div class="rounded-xl border border-slate-200/80 p-4">
                    <h4 class="text-sm font-bold text-slate-900 mb-3">Komposisi Timbulan {{ $pl['tahun'] }}</h4>
                    <div class="h-80"><canvas id="cPelPorsi"></canvas></div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200/80 p-4 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kepatuhan melapor</p>

                    <details>
                        <summary class="cursor-pointer text-sm font-medium text-slate-800">Belum ada data bulan {{ $pl['acuanNama'] ?? '-' }} ({{ $pl['belumMelapor']->count() }})</summary>
                        <div class="mt-2 max-h-56 overflow-y-auto text-sm text-slate-700">
                            @forelse($pl['belumMelapor'] as $n)<p class="border-t border-slate-100 py-1 first:border-0">{{ $n }}</p>
                            @empty<p class="text-slate-400">{{ $pl['acuan'] ? 'Semua industri dengan laporan sudah mengisi.' : 'Belum ada bulan lengkap untuk diperiksa.' }}</p>@endforelse
                        </div>
                    </details>

                    <details>
                        <summary class="cursor-pointer text-sm font-medium text-slate-800">Pengisian kurang dari separuh bulan {{ $pl['acuanNama'] ?? '' }} ({{ $pl['tidakLengkap']->count() }})</summary>
                        <div class="mt-2 max-h-56 overflow-y-auto text-sm">
                            @forelse($pl['tidakLengkap'] as $r)
                                <div class="flex justify-between gap-3 border-t border-slate-100 py-1 first:border-0">
                                    <span class="text-slate-700">{{ $r->nama }}</span><span class="shrink-0 text-slate-500">{{ $r->hari }} dari {{ $r->dari }} hari</span>
                                </div>
                            @empty<p class="text-slate-400">Tidak ada.</p>@endforelse
                        </div>
                    </details>

                    <details>
                        <summary class="cursor-pointer text-sm font-medium text-slate-800">Industri aktif tanpa laporan tahun {{ $pl['tahun'] }} ({{ $pl['tanpaLaporan']->count() }})</summary>
                        <div class="mt-2 max-h-56 overflow-y-auto text-sm text-slate-700">
                            @forelse($pl['tanpaLaporan'] as $n)<p class="border-t border-slate-100 py-1 first:border-0">{{ $n }}</p>
                            @empty<p class="text-slate-400">Semua industri aktif sudah punya laporan.</p>@endforelse
                        </div>
                    </details>
                </div>

                <div class="rounded-xl border border-slate-200/80 p-4 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kualitas pengelolaan</p>

                    <details {{ $pl['residuTinggi']->isNotEmpty() ? 'open' : '' }}>
                        <summary class="cursor-pointer text-sm font-medium text-slate-800">Porsi residu tertinggi ({{ $pl['residuTinggi']->count() }})</summary>
                        <div class="mt-2 text-sm">
                            @forelse($pl['residuTinggi'] as $r)
                                <div class="flex justify-between gap-3 border-t border-slate-100 py-1 first:border-0">
                                    <span class="text-slate-700">{{ $r->nama }}</span>
                                    <span class="shrink-0 text-slate-500">{{ $f1($r->pct) }}% ({{ $f0($r->kg) }} kg)</span>
                                </div>
                            @empty<p class="text-slate-400">Belum ada data.</p>@endforelse
                            <p class="mt-2 text-xs text-slate-400">Industri dengan total timbulan minimal 100 kg, diurutkan dari porsi residu terbesar. Porsi tinggi berarti pemilahan di sumber masih rendah.</p>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const d     = @json($pl['chart']);
    const btn   = document.getElementById('btnPelaporan');
    const panel = document.getElementById('panelPelaporan');
    const icon  = document.getElementById('iconPelaporan');
    const KEY   = 'pelaporanIndustriOpen';

    const num = v => Number(v).toLocaleString('id-ID');
    const opt = { responsive: true, maintainAspectRatio: false };
    const WARNA = { o: '#15803d', a: '#2563eb', r: '#ef4444' };

    // Chart.js dimuat sendiri bila halaman belum punya
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

        new Chart('cPelBulan', {
            data: { labels: d.labels, datasets: [
                { type: 'bar', label: 'Organik',   data: d.organik,   backgroundColor: WARNA.o, stack: 'tahun' },
                { type: 'bar', label: 'Anorganik', data: d.anorganik, backgroundColor: WARNA.a, stack: 'tahun' },
                { type: 'bar', label: 'Residu',    data: d.residu,    backgroundColor: WARNA.r, stack: 'tahun' },
                { type: 'line', label: 'Total ' + d.lalu, data: d.totalLalu, borderColor: '#f59e0b', backgroundColor: '#f59e0b', tension: .3, stack: 'lalu' }
            ]},
            options: { ...opt, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { callback: v => num(v) } } },
                       plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + num(c.parsed.y) + ' kg' } } } }
        });

        new Chart('cPelMelapor', {
            data: { labels: d.labels, datasets: [
                { type: 'bar',  label: 'Industri mengisi', data: d.melapor, backgroundColor: '#0f766e' },
                { type: 'line', label: 'Industri aktif', data: d.labels.map(() => d.aktif), borderColor: '#94a3b8', borderDash: [6, 4], pointRadius: 0 }
            ]},
            options: { ...opt, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        new Chart('cPelTop', {
            type: 'bar',
            data: { labels: d.topNama, datasets: [
                { label: 'Organik',   data: d.topO, backgroundColor: WARNA.o },
                { label: 'Anorganik', data: d.topA, backgroundColor: WARNA.a },
                { label: 'Residu',    data: d.topR, backgroundColor: WARNA.r }
            ]},
            options: { ...opt, indexAxis: 'y', scales: { x: { stacked: true, beginAtZero: true, ticks: { callback: v => num(v) } }, y: { stacked: true } },
                       plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + num(c.parsed.x) + ' kg' } } } }
        });

        new Chart('cPelPorsi', {
            type: 'doughnut',
            data: { labels: ['Organik', 'Anorganik', 'Residu'], datasets: [{ data: d.porsi, backgroundColor: [WARNA.o, WARNA.a, WARNA.r] }] },
            options: { ...opt, plugins: { tooltip: { callbacks: { label: c => c.label + ': ' + num(c.parsed) + ' kg' } } } }
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
@endif
@endif