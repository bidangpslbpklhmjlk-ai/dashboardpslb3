@php
    $bulanPilih = $bulanAktif ?? null;
    $hariIni = \Carbon\Carbon::today();

    // Jumlah tanggal berisi data (di area mana pun) per bulan, hanya sampai hari ini
    $terisiHari = \Illuminate\Support\Facades\DB::table('neraca_sampah_harian')
        ->where('laporan_id', $laporan->id)
        ->where('tanggal', '<=', $hariIni->toDateString())
        ->selectRaw('substr(tanggal, 1, 7) as ym, count(distinct tanggal) as hari')
        ->groupBy('ym')
        ->pluck('hari', 'ym');

    $samaTahun = $laporan->periode_mulai->year === $laporan->periode_selesai->year;
    $bulanBelumTerisi  = [];
    $bulanBelumLengkap = [];
    $bulanLengkap      = 0;

    foreach ($laporan->bulanList() as $ymCek => $awalCek) {
        if ($bulanPilih && $ymCek !== $bulanPilih) {
            continue;
        }

        // Hari yang seharusnya sudah terisi: irisan bulan dengan periode laporan, sampai hari ini
        $mulaiCek = $awalCek->copy()->startOfDay()->max($laporan->periode_mulai->copy()->startOfDay());
        $akhirCek = $awalCek->copy()->endOfMonth()->startOfDay()->min($laporan->periode_selesai->copy()->startOfDay());
        if ($akhirCek->gt($hariIni)) {
            $akhirCek = $hariIni->copy();
        }
        if ($akhirCek->lt($mulaiCek)) {
            continue; // bulan belum berjalan
        }

        $harapan   = (int) $mulaiCek->diffInDays($akhirCek, true) + 1;
        $terisiCek = (int) ($terisiHari[$ymCek] ?? 0);
        $namaBulan = $awalCek->translatedFormat($samaTahun ? 'F' : 'F Y');

        if ($terisiCek === 0) {
            $bulanBelumTerisi[] = $namaBulan;
        } elseif ($terisiCek < $harapan) {
            $bulanBelumLengkap[] = "{$namaBulan} ({$terisiCek}/{$harapan} hari)";
        } else {
            $bulanLengkap++;
        }
    }
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap items-start justify-between gap-3">
        <h2 class="font-semibold text-slate-800">Rekap Neraca Sampah {{ $bulanPilih ? $laporan->bulanList()[$bulanPilih]->translatedFormat('F Y') . ' (ton/bulan)' : '(ton/tahun)' }}</h2>

        <div class="flex flex-col gap-1.5 max-w-xl">
            @if($bulanBelumTerisi)
                <p class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs text-rose-800">
                    <strong>Belum terisi:</strong> {{ implode(', ', $bulanBelumTerisi) }}
                </p>
            @endif
            @if($bulanBelumLengkap)
                <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs text-amber-800">
                    <strong>Belum lengkap:</strong> {{ implode(', ', $bulanBelumLengkap) }}
                </p>
            @endif
            @if($bulanBelumTerisi || $bulanBelumLengkap)
                <p class="text-[11px] text-slate-400">Angka di tabel hanya dari data yang sudah terisi.</p>
            @elseif($bulanLengkap > 0)
                <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs text-emerald-800">
                    {{ $bulanPilih ? 'Bulan ini terisi lengkap.' : 'Semua bulan yang sudah berjalan terisi lengkap.' }}
                </p>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold">
                <tr class="border-b border-slate-200">
                    <th rowspan="2" class="px-4 py-3 text-left">No</th>
                    <th rowspan="2" class="px-4 py-3 text-left">Sumber Sampah</th>
                    <th rowspan="2" class="px-4 py-3 text-right">Timbulan Sampah</th>
                    <th colspan="4" class="px-4 py-2 text-center border-l border-slate-200">Sampah Terkelola</th>
                    <th colspan="3" class="px-4 py-2 text-center border-l border-slate-200">Sampah Lainnya dan/atau Residu</th>
                </tr>
                <tr class="border-b border-slate-200 text-xs">
                    <th class="px-4 py-2 text-right border-l border-slate-200">Organik</th>
                    <th class="px-4 py-2 text-right">Anorganik Terpilah</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-right">Prosentase</th>
                    <th class="px-4 py-2 text-right border-l border-slate-200">Jumlah</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-right">Prosentase</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($neraca['rows'] as $i => $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $r['area'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($r['timbulan']) }}</td>
                    <td class="px-4 py-3 text-right border-l border-slate-100">{{ $ton($r['organik']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($r['anorganik']) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $ton($r['terkelola']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $pct($r['persen_terkelola']) }}</td>
                    <td class="px-4 py-3 text-right border-l border-slate-100">{{ $ton($r['residu']) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $ton($r['residu']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $pct($r['persen_residu']) }}</td>
                </tr>
                @endforeach
            </tbody>
            @php $t = $neraca['total']; @endphp
            <tfoot class="bg-slate-50 font-bold text-slate-800 border-t-2 border-slate-300">
                <tr>
                    <td colspan="2" class="px-4 py-3">Total ({{ $bulanPilih ? 'ton/bulan' : 'ton/tahun' }})</td>
                    <td class="px-4 py-3 text-right">{{ $ton($t['timbulan']) }}</td>
                    <td class="px-4 py-3 text-right border-l border-slate-200">{{ $ton($t['organik']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($t['anorganik']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($t['terkelola']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $pct($t['persen_terkelola']) }}</td>
                    <td class="px-4 py-3 text-right border-l border-slate-200">{{ $ton($t['residu']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($t['residu']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $pct($t['persen_residu']) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @include('admin.neraca-sampah.partials.ttd')
</div>