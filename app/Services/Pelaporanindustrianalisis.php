<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Analisis pelaporan Neraca Sampah seluruh industri untuk satu tahun:
 * timbulan (organik, anorganik, residu), kepatuhan melapor, dan kualitas pengisian.
 */
class PelaporanIndustriAnalisis
{
    private const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function data(int $tahun): array
    {
        $f0     = fn ($n) => number_format((float) $n, 0, ',', '.');
        $persen = fn ($x, $tot) => $tot > 0 ? round($x / $tot * 100, 1) : null;

        $awal  = sprintf('%d-01-01', $tahun);
        $akhir = sprintf('%d-12-31', $tahun);

        $berjalan      = $tahun === (int) now()->year;
        $bulanBerjalan = $berjalan ? (int) now()->month : null;
        $acuan         = $berjalan ? $bulanBerjalan - 1 : 12;   // bulan lengkap terakhir
        if ($acuan < 1) $acuan = null;

        $industri = DB::table('industri')->get(['id', 'nama', 'aktif'])->keyBy('id');
        $aktifIds = $industri->filter(fn ($i) => (bool) $i->aktif)->keys()->all();

        // ---------- data harian, diringkas per industri per bulan ----------
        $rows = DB::table('neraca_sampah_harian as h')
            ->join('neraca_sampah_laporan as l', 'l.id', '=', 'h.laporan_id')
            ->whereBetween('h.tanggal', [$awal, $akhir])
            ->groupBy('l.industri_id', 'ym')
            ->selectRaw('l.industri_id, substr(h.tanggal, 1, 7) as ym, sum(h.organik_kg) as o, sum(h.anorganik_kg) as a, sum(h.residu_kg) as r, count(distinct h.tanggal) as hari')
            ->get();

        $o = $a = $r = array_fill(1, 12, 0.0);
        $melapor = array_fill(1, 12, []);
        $perInd  = [];
        foreach ($rows as $x) {
            $m  = (int) substr($x->ym, 5, 2);
            $xo = (float) $x->o; $xa = (float) $x->a; $xr = (float) $x->r;
            $o[$m] += $xo; $a[$m] += $xa; $r[$m] += $xr;
            $melapor[$m][$x->industri_id] = true;

            $p = &$perInd[$x->industri_id];
            $p['o'] = ($p['o'] ?? 0) + $xo;
            $p['a'] = ($p['a'] ?? 0) + $xa;
            $p['r'] = ($p['r'] ?? 0) + $xr;
            $p['bulan'][$m] = ['kg' => $xo + $xa + $xr, 'hari' => (int) $x->hari];
            unset($p);
        }

        $tO = array_sum($o); $tA = array_sum($a); $tR = array_sum($r);
        $total = $tO + $tA + $tR;
        $totalBulan = [];
        foreach (range(1, 12) as $m) $totalBulan[$m] = $o[$m] + $a[$m] + $r[$m];

        // ---------- tahun lalu (pembanding) ----------
        $laluRows = DB::table('neraca_sampah_harian')
            ->whereBetween('tanggal', [sprintf('%d-01-01', $tahun - 1), sprintf('%d-12-31', $tahun - 1)])
            ->groupBy('ym')
            ->selectRaw('substr(tanggal, 1, 7) as ym, sum(organik_kg + anorganik_kg + residu_kg) as kg')
            ->pluck('kg', 'ym');
        $lalu = [];
        foreach (range(1, 12) as $m) $lalu[$m] = (float) ($laluRows[sprintf('%d-%02d', $tahun - 1, $m)] ?? 0);

        // ---------- kepatuhan melapor ----------
        $adaLaporan = DB::table('neraca_sampah_laporan')
            ->whereDate('periode_mulai', '<=', $akhir)->whereDate('periode_selesai', '>=', $awal)
            ->distinct()->pluck('industri_id')->all();

        $tanpaLaporan = collect($aktifIds)->reject(fn ($id) => in_array($id, $adaLaporan))
            ->map(fn ($id) => $industri[$id]->nama)->sort()->values();

        $belumMengisi = collect($aktifIds)->reject(fn ($id) => isset($perInd[$id]))->count();
        $industriMelapor = count($perInd);
        $aktifJml = count($aktifIds);
        $aktifMelapor = collect($aktifIds)->filter(fn ($id) => isset($perInd[$id]))->count();

        $belumMelapor = collect(); $tidakLengkap = collect();
        if ($acuan) {
            $a1 = Carbon::create($tahun, $acuan, 1)->startOfMonth();
            $a2 = $a1->copy()->endOfMonth();
            $laporanBulan = DB::table('neraca_sampah_laporan')
                ->whereDate('periode_mulai', '<=', $a2->toDateString())->whereDate('periode_selesai', '>=', $a1->toDateString())
                ->distinct()->pluck('industri_id')->all();

            $belumMelapor = collect($laporanBulan)
                ->filter(fn ($id) => isset($industri[$id]) && $industri[$id]->aktif && !isset($melapor[$acuan][$id]))
                ->map(fn ($id) => $industri[$id]->nama)->sort()->values();

            $hariBulan = $a1->daysInMonth;
            $tidakLengkap = collect($perInd)->map(function ($p, $id) use ($acuan, $hariBulan, $industri) {
                $h = $p['bulan'][$acuan]['hari'] ?? 0;
                return ($h > 0 && $h < $hariBulan / 2)
                    ? (object) ['nama' => $industri[$id]->nama ?? '-', 'hari' => $h, 'dari' => $hariBulan] : null;
            })->filter()->sortBy('hari')->values();
        }

        // ---------- peringkat dan kualitas ----------
        $list = collect($perInd)->map(fn ($p, $id) => (object) [
            'nama' => $industri[$id]->nama ?? '-',
            'o' => $p['o'], 'a' => $p['a'], 'r' => $p['r'],
            'kg' => $p['o'] + $p['a'] + $p['r'],
        ])->values();

        $top = $list->sortByDesc('kg')->take(10)->values();

        $residuTinggi = $list->filter(fn ($i) => $i->kg >= 100)
            ->map(fn ($i) => (object) ['nama' => $i->nama, 'pct' => round($i->r / $i->kg * 100, 1), 'kg' => $i->r])
            ->sortByDesc('pct')->take(5)->values();

        // ---------- ringkasan otomatis ----------
        $ringkas = [];
        if ($bulanBerjalan && ($totalBulan[$bulanBerjalan] ?? 0) > 0) {
            $ringkas[] = self::BLN[$bulanBerjalan - 1] . " {$tahun} masih berjalan, datanya belum lengkap.";
        }
        $terisi = collect(range(1, 12))->filter(fn ($m) => $totalBulan[$m] > 0 && $m !== $bulanBerjalan)->values();
        if ($terisi->isNotEmpty()) {
            $iMax = $terisi->sortByDesc(fn ($m) => $totalBulan[$m])->first();
            $ringkas[] = 'Timbulan tertinggi terjadi pada bulan ' . self::BLN[$iMax - 1] . ' (' . $f0($totalBulan[$iMax]) . ' kg).';
            if ($terisi->count() > 1) {
                $iMin = $terisi->sortBy(fn ($m) => $totalBulan[$m])->first();
                $ringkas[] = 'Timbulan terendah terjadi pada bulan ' . self::BLN[$iMin - 1] . ' (' . $f0($totalBulan[$iMin]) . ' kg).';
            }
        }
        if ($acuan) {
            $now  = array_sum(array_slice($totalBulan, 0, $acuan));
            $sama = array_sum(array_slice($lalu, 0, $acuan));
            if ($sama > 0) {
                $sel = round(($now - $sama) / $sama * 100, 1);
                $ringkas[] = 'Dibanding periode yang sama tahun ' . ($tahun - 1) . ', timbulan ' . ($sel >= 0 ? 'naik ' : 'turun ')
                    . number_format(abs($sel), 1, ',', '.') . '%.';
            }
        }
        if ($aktifJml) {
            $ringkas[] = "{$aktifMelapor} dari {$aktifJml} industri aktif sudah mengisi data tahun {$tahun} (" . round($aktifMelapor / $aktifJml * 100) . '%).';
        }
        if ($total > 0) {
            $ringkas[] = 'Residu ' . number_format($persen($tR, $total), 1, ',', '.') . '% dari total timbulan; '
                . number_format($persen($tO + $tA, $total), 1, ',', '.') . '% terpilah (organik dan anorganik).';
            if ($top->isNotEmpty()) {
                $ringkas[] = $top->first()->nama . ' menyumbang ' . number_format($top->first()->kg / $total * 100, 1, ',', '.') . '% dari total.';
            }
        }

        $daftarTahun = DB::table('neraca_sampah_harian')->selectRaw('substr(tanggal, 1, 4) as y')->distinct()->pluck('y')
            ->map(fn ($y) => (int) $y)->push((int) now()->year)->push($tahun)->unique()->sortDesc()->values()->all();

        return [
            'tahun' => $tahun, 'berjalan' => $berjalan, 'acuan' => $acuan, 'acuanNama' => $acuan ? self::BLN[$acuan - 1] : null,
            'daftarTahun' => $daftarTahun,
            'total' => round($total, 1), 'organik' => round($tO, 1), 'anorganik' => round($tA, 1), 'residu' => round($tR, 1),
            'pctResidu' => $persen($tR, $total), 'pctTerpilah' => $persen($tO + $tA, $total),
            'aktifJml' => $aktifJml, 'aktifMelapor' => $aktifMelapor, 'industriMelapor' => $industriMelapor,
            'pctMelapor' => $aktifJml ? (int) round($aktifMelapor / $aktifJml * 100) : 0,
            'belumMengisi' => $belumMengisi,
            'ringkas' => $ringkas,
            'belumMelapor' => $belumMelapor, 'tidakLengkap' => $tidakLengkap,
            'tanpaLaporan' => $tanpaLaporan, 'residuTinggi' => $residuTinggi,
            'chart' => [
                'labels' => self::BLN, 'tahun' => $tahun, 'lalu' => $tahun - 1,
                'organik' => array_map(fn ($v) => round($v, 1), array_values($o)),
                'anorganik' => array_map(fn ($v) => round($v, 1), array_values($a)),
                'residu' => array_map(fn ($v) => round($v, 1), array_values($r)),
                'totalLalu' => array_map(fn ($v) => round($v, 1), array_values($lalu)),
                'melapor' => array_map(fn ($m) => count($melapor[$m]), range(1, 12)),
                'aktif' => $aktifJml,
                'topNama' => $top->pluck('nama')->all(),
                'topO' => $top->map(fn ($i) => round($i->o, 1))->all(),
                'topA' => $top->map(fn ($i) => round($i->a, 1))->all(),
                'topR' => $top->map(fn ($i) => round($i->r, 1))->all(),
                'porsi' => [round($tO, 1), round($tA, 1), round($tR, 1)],
            ],
        ];
    }
}