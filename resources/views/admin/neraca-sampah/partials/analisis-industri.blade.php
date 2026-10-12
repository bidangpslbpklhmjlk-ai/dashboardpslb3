@php
    $kg  = fn ($v) => number_format($v, 0, ',', '.');
    $pct = fn ($v) => is_null($v) ? '-' : number_format($v * 100, 1, ',', '.') . '%';
    $rk  = $analisis['ringkasan'];

    $status = [
        'lengkap'        => ['Lengkap', 'bg-emerald-100 text-emerald-700'],
        'sebagian'       => ['Sebagian', 'bg-amber-100 text-amber-700'],
        'kosong'         => ['Belum ada data', 'bg-slate-100 text-slate-500'],
        'belum_berjalan' => ['Belum berjalan', 'bg-slate-100 text-slate-400'],
    ];
    $warna = [
        'perhatian'  => 'border-red-200 bg-red-50 text-red-800',
        'peringatan' => 'border-amber-200 bg-amber-50 text-amber-800',
        'catatan'    => 'border-slate-200 bg-slate-50 text-slate-700',
    ];
    $hitung = collect($analisis['anomali'])->countBy('tingkat');
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200">
        <h2 class="text-lg font-semibold text-slate-900">Analisis Data · Tahun {{ $tahunLabel($laporan) }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">Dihitung dari data harian laporan tahun ini (satuan kg).</p>
    </div>

    @if(!$analisis['ada_data'])
        <p class="px-5 py-10 text-center text-sm text-slate-500">
            Belum ada data harian untuk dianalisis. Import file Excel atau isi data harian terlebih dahulu.
        </p>
    @else
        {{-- Ringkasan --}}
        <div class="p-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-lg border border-slate-200 px-4 py-3">
                <p class="text-xs text-slate-500">Total timbulan</p>
                <p class="text-xl font-bold text-slate-900">{{ $kg($rk['total']) }} kg</p>
                <p class="text-xs text-slate-400">{{ number_format($rk['total'] / 1000, 2, ',', '.') }} ton</p>
            </div>
            <div class="rounded-lg border border-slate-200 px-4 py-3">
                <p class="text-xs text-slate-500">Rata-rata per hari terisi</p>
                <p class="text-xl font-bold text-slate-900">{{ $kg($rk['rata_harian']) }} kg</p>
                <p class="text-xs text-slate-400">{{ $rk['hari_terisi'] }} hari terisi</p>
            </div>
            <div class="rounded-lg border border-slate-200 px-4 py-3">
                <p class="text-xs text-slate-500">Sampah terkelola</p>
                <p class="text-xl font-bold text-emerald-700">{{ $pct($rk['persen_terkelola']) }}</p>
                <p class="text-xs text-slate-400">Residu {{ $pct($rk['persen_residu']) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 px-4 py-3">
                <p class="text-xs text-slate-500">Perlu dicek</p>
                <p class="text-xl font-bold {{ count($analisis['anomali']) ? 'text-amber-700' : 'text-emerald-700' }}">{{ count($analisis['anomali']) }}</p>
                <p class="text-xs text-slate-400">{{ $hitung['perhatian'] ?? 0 }} perhatian · {{ $hitung['peringatan'] ?? 0 }} peringatan · {{ $hitung['catatan'] ?? 0 }} catatan</p>
            </div>
        </div>

        @if($rk['tertinggi'] && $rk['terendah'])
            <p class="px-5 -mt-2 pb-4 text-xs text-slate-500">
                Bulan tertinggi: <strong>{{ $rk['tertinggi']['label'] }}</strong> ({{ $kg($rk['tertinggi']['total']) }} kg)
                · terendah: <strong>{{ $rk['terendah']['label'] }}</strong> ({{ $kg($rk['terendah']['total']) }} kg).
                Hanya bulan yang terisi minimal 80% yang dibandingkan.
            </p>
        @endif

        {{-- Tabel per bulan --}}
        <div class="overflow-x-auto border-t border-slate-200 max-h-[28rem] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left">Bulan</th>
                        <th class="px-4 py-3 text-center">Hari terisi</th>
                        <th class="px-4 py-3 text-right">Organik</th>
                        <th class="px-4 py-3 text-right">Anorganik</th>
                        <th class="px-4 py-3 text-right">Residu</th>
                        <th class="px-4 py-3 text-left w-48">Total (kg)</th>
                        <th class="px-4 py-3 text-right">Perubahan</th>
                        <th class="px-4 py-3 text-right">% Residu</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($analisis['bulan'] as $b)
                        @php $lebar = $analisis['maks_bulan'] > 0 ? round($b['total'] / $analisis['maks_bulan'] * 100) : 0; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 font-medium text-slate-800 whitespace-nowrap">{{ $b['awal']->translatedFormat('F Y') }}</td>
                            <td class="px-4 py-2.5 text-center text-slate-600">{{ $b['harapan'] > 0 ? $b['jumlah_hari'] . ' / ' . $b['harapan'] : '-' }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $kg($b['organik']) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $kg($b['anorganik']) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $kg($b['residu']) }}</td>
                            <td class="px-4 py-2.5">
                                <div class="font-semibold text-slate-800">{{ $kg($b['total']) }}</div>
                                <div class="h-1.5 rounded-full bg-slate-100 mt-1">
                                    <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $lebar }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-right {{ $b['perubahan_mencolok'] ? 'text-amber-700 font-semibold' : 'text-slate-600' }}">
                                @if(is_null($b['perubahan']))
                                    -
                                @else
                                    {{ $b['perubahan'] > 0 ? '+' : '' }}{{ number_format($b['perubahan'] * 100, 1, ',', '.') }}%
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right text-slate-600">{{ $pct($b['persen_residu']) }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $status[$b['status']][1] }}">{{ $status[$b['status']][0] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Anomali --}}
        <div class="p-5 border-t border-slate-200 space-y-2">
            <h3 class="text-sm font-semibold text-slate-800">Perlu dicek</h3>
            @forelse(array_slice($analisis['anomali'], 0, 40) as $a)
                <div class="rounded-lg border px-3 py-2 text-sm {{ $warna[$a['tingkat']] }}">
                    <span class="font-semibold">{{ $a['judul'] }}</span> · {{ $a['detail'] }}
                </div>
            @empty
                <p class="text-sm text-emerald-700">Tidak ada anomali terdeteksi pada data yang ada.</p>
            @endforelse
            @if(count($analisis['anomali']) > 40)
                <p class="text-xs text-slate-500">Menampilkan 40 dari {{ count($analisis['anomali']) }} temuan.</p>
            @endif
        </div>

        {{-- Kontribusi area --}}
        <div class="p-5 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Kontribusi per area</h3>
            <div class="space-y-2">
                @foreach($analisis['area'] as $a)
                    <div>
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>{{ $a['nama'] }}</span>
                            <span>{{ $kg($a['total']) }} kg · {{ $pct($a['persen']) }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 mt-1">
                            <div class="h-1.5 rounded-full bg-sky-500" style="width: {{ round($a['persen'] * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>