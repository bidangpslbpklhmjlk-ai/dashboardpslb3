<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\NeracaSampahArea;
use App\Models\NeracaSampahLaporan;

/**
 * Analisis data harian neraca sampah untuk satu industri: rekap per bulan,
 * kelengkapan, dan deteksi anomali. Dihitung dari neraca_sampah_harian (kg/hari).
 */
class NeracaSampahAnalisis
{
    private const AMBANG_PERUBAHAN = 0.5;   // perubahan total bulanan yang dianggap mencolok
    private const AMBANG_RESIDU    = 0.15;  // selisih porsi residu (poin) terhadap rata-rata bulan lain
    private const AMBANG_LENGKAP   = 0.8;   // bulan dianggap cukup lengkap untuk dibandingkan
    private const MIN_SAMPEL       = 10;    // minimal data harian per area untuk deteksi lonjakan
    private const MIN_NILAI_SAMA   = 4;     // hari berturut-turut dengan nilai identik
    private const MAKS_LONJAKAN    = 15;

    public function analisa(Collection $laporans): array
    {
        $kosong = ['ada_data' => false, 'bulan' => [], 'area' => [], 'anomali' => [], 'ringkasan' => null, 'maks_bulan' => 0];

        if ($laporans->isEmpty()) {
            return $kosong;
        }

        $bulan = $this->kerangkaBulan($laporans);

        $rows = DB::table('neraca_sampah_harian as h')
            ->join('neraca_sampah_area as a', 'a.id', '=', 'h.area_id')
            ->whereIn('h.laporan_id', $laporans->pluck('id')->all())
            ->orderBy('h.tanggal')
            ->get(['h.area_id', 'a.nama as area', 'h.tanggal', 'h.organik_kg', 'h.anorganik_kg', 'h.residu_kg']);

        if ($rows->isEmpty()) {
            return $kosong;
        }
        $hariIniStr = Carbon::today()->toDateString();
        $depan = [];
        $area = [];
        foreach ($rows as $r) {
            $tgl = substr((string) $r->tanggal, 0, 10);
            $ym  = substr($tgl, 0, 7);
            if (!isset($bulan[$ym])) {
                continue;
            }
            if ($tgl > $hariIniStr) {
                $depan[$ym][] = (int) substr($tgl, 8, 2);
                continue;
            }
            $o = (float) $r->organik_kg;
            $n = (float) $r->anorganik_kg;
            $s = (float) $r->residu_kg;

            $bulan[$ym]['organik']   += $o;
            $bulan[$ym]['anorganik'] += $n;
            $bulan[$ym]['residu']    += $s;
            $bulan[$ym]['hari'][$tgl] = true;

            $area[$r->area_id]['nama']  = $r->area;
            $area[$r->area_id]['total'] = ($area[$r->area_id]['total'] ?? 0) + $o + $n + $s;
            $area[$r->area_id]['hari'][$tgl] = [
                'total' => $o + $n + $s,
                'kunci' => $r->organik_kg . '|' . $r->anorganik_kg . '|' . $r->residu_kg,
            ];
        }

        foreach ($bulan as &$b) {
            $b['total']              = $b['organik'] + $b['anorganik'] + $b['residu'];
            $b['jumlah_hari']        = count($b['hari']);
            $b['kelengkapan']        = $b['harapan'] > 0 ? $b['jumlah_hari'] / $b['harapan'] : null;
            $b['persen_residu']      = $b['total'] > 0 ? $b['residu'] / $b['total'] : null;
            $b['persen_terkelola']   = $b['total'] > 0 ? ($b['organik'] + $b['anorganik']) / $b['total'] : null;
            $b['perubahan']          = null;
            $b['perubahan_mencolok'] = false;
            $b['status'] = match (true) {
                $b['harapan'] === 0       => 'belum_berjalan',
                $b['jumlah_hari'] === 0   => 'kosong',
                $b['kelengkapan'] >= 1    => 'lengkap',
                default                   => 'sebagian',
            };
        }
        unset($b);

        $anomali = array_merge(
            $this->anomaliBulanan($bulan),
            $this->anomaliArea($area)
        );
        foreach ($depan as $ym => $hari) {
            $anomali[] = [
                'tingkat' => 'perhatian',
                'judul'   => $bulan[$ym]['awal']->translatedFormat('F Y'),
                'detail'  => count($hari) . ' tanggal berisi data padahal belum terjadi (' . $this->ringkasTanggal($hari)
                    . '). Data ini tidak dihitung dalam analisis. Kemungkinan isian contoh atau sheet bulan lain ikut terimpor.',
            ];
        }
        $urut = ['perhatian' => 0, 'peringatan' => 1, 'catatan' => 2];
        usort($anomali, fn ($x, $y) => $urut[$x['tingkat']] <=> $urut[$y['tingkat']]);

        $total      = array_sum(array_column($bulan, 'total'));
        $organik    = array_sum(array_column($bulan, 'organik'));
        $anorganik  = array_sum(array_column($bulan, 'anorganik'));
        $residu     = array_sum(array_column($bulan, 'residu'));
        $hariTerisi = array_sum(array_column($bulan, 'jumlah_hari'));

        $lengkap = array_filter($bulan, fn ($m) => $this->lengkap($m) && $m['total'] > 0);
        $tertinggi = $terendah = null;
        if (count($lengkap) >= 2) {
            usort($lengkap, fn ($x, $y) => $y['total'] <=> $x['total']);
            $a = reset($lengkap);
            $z = end($lengkap);
            $tertinggi = ['label' => $a['awal']->translatedFormat('F Y'), 'total' => $a['total']];
            $terendah  = ['label' => $z['awal']->translatedFormat('F Y'), 'total' => $z['total']];
        }

        $areaList = [];
        foreach ($area as $a) {
            $areaList[] = [
                'nama'   => $a['nama'],
                'total'  => $a['total'],
                'persen' => $total > 0 ? $a['total'] / $total : 0,
            ];
        }
        usort($areaList, fn ($x, $y) => $y['total'] <=> $x['total']);

        return [
            'ada_data'   => true,
            'bulan'      => array_values($bulan),
            'area'       => $areaList,
            'anomali'    => $anomali,
            'maks_bulan' => $bulan ? max(array_column($bulan, 'total')) : 0,
            'ringkasan'  => [
                'total'            => $total,
                'hari_terisi'      => $hariTerisi,
                'rata_harian'      => $hariTerisi > 0 ? $total / $hariTerisi : 0,
                'persen_terkelola' => $total > 0 ? ($organik + $anorganik) / $total : null,
                'persen_residu'    => $total > 0 ? $residu / $total : null,
                'tertinggi'        => $tertinggi,
                'terendah'         => $terendah,
            ],
        ];
    }

    /** Satu entri per bulan di semua laporan, lengkap dengan jumlah hari yang seharusnya sudah terisi */
    private function kerangkaBulan(Collection $laporans): array
    {
        $hariIni = Carbon::today();
        $bulan = [];

        foreach ($laporans as $l) {
            foreach ($l->bulanList() as $ym => $awal) {
                $mulai = $awal->copy()->startOfDay()->max($l->periode_mulai->copy()->startOfDay());
                $akhir = $awal->copy()->endOfMonth()->startOfDay()->min($l->periode_selesai->copy()->startOfDay());
                if ($akhir->gt($hariIni)) {
                    $akhir = $hariIni->copy(); // hari yang belum terjadi tidak dihitung
                }
                $harapan = $akhir->gte($mulai) ? (int) $mulai->diffInDays($akhir, true) + 1 : 0;

                if (isset($bulan[$ym])) { // dua laporan berbagi bulan yang sama
                    $bulan[$ym]['harapan'] += $harapan;
                    continue;
                }

                $bulan[$ym] = [
                    'ym' => $ym, 'awal' => $awal->copy(), 'harapan' => $harapan, 'hari' => [],
                    'organik' => 0.0, 'anorganik' => 0.0, 'residu' => 0.0,
                ];
            }
        }

        ksort($bulan);

        return $bulan;
    }
    /**
     * Neraca satu bulan (ton/bulan), bentuk datanya sama dengan NeracaSampahService::neraca().
     * Asumsi: timbulan = organik + anorganik + residu, terkelola = organik + anorganik.
     */
    public function neracaBulan(NeracaSampahLaporan $laporan, string $ym): array
    {
        $awal = Carbon::createFromFormat('!Y-m', $ym)->startOfMonth();

        $jumlah = DB::table('neraca_sampah_harian')
            ->where('laporan_id', $laporan->id)
            ->whereBetween('tanggal', [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()])
            ->groupBy('area_id')
            ->selectRaw('area_id, sum(organik_kg) as organik, sum(anorganik_kg) as anorganik, sum(residu_kg) as residu')
            ->get()
            ->keyBy('area_id');

        $rows = [];
        $tO = $tN = $tS = 0.0;

        foreach (NeracaSampahArea::where('aktif', true)->orderBy('urutan')->get() as $a) {
            $j = $jumlah->get($a->id);
            $o = ((float) ($j->organik ?? 0)) / 1000;
            $n = ((float) ($j->anorganik ?? 0)) / 1000;
            $s = ((float) ($j->residu ?? 0)) / 1000;

            $rows[] = $this->barisNeraca($a->nama, $o, $n, $s);
            $tO += $o;
            $tN += $n;
            $tS += $s;
        }

        return ['rows' => $rows, 'total' => $this->barisNeraca('Total', $tO, $tN, $tS)];
    }

    private function barisNeraca(string $nama, float $o, float $n, float $s): array
    {
        $timbulan  = $o + $n + $s;
        $terkelola = $o + $n;

        return [
            'area'             => $nama,
            'timbulan'         => $timbulan,
            'organik'          => $o,
            'anorganik'        => $n,
            'terkelola'        => $terkelola,
            'persen_terkelola' => $timbulan > 0 ? $terkelola / $timbulan : null,
            'residu'           => $s,
            'persen_residu'    => $timbulan > 0 ? $s / $timbulan : null,
        ];
    }
    private function lengkap(array $m): bool
    {
        return $m['jumlah_hari'] > 0 && ($m['kelengkapan'] ?? 0) >= self::AMBANG_LENGKAP;
    }

    private function anomaliBulanan(array &$bulan): array
    {
        $anomali = [];

        // Perubahan total dibanding bulan sebelumnya
        $prev = null;
        foreach ($bulan as $ym => &$b) {
            if ($prev !== null
                && $prev['awal']->copy()->addMonth()->format('Y-m') === $ym
                && $this->lengkap($prev) && $this->lengkap($b) && $prev['total'] > 0) {

                $b['perubahan'] = ($b['total'] - $prev['total']) / $prev['total'];

                if (abs($b['perubahan']) >= self::AMBANG_PERUBAHAN) {
                    $b['perubahan_mencolok'] = true;
                    $anomali[] = [
                        'tingkat' => 'peringatan',
                        'judul'   => $b['awal']->translatedFormat('F Y'),
                        'detail'  => 'Total ' . ($b['perubahan'] > 0 ? 'naik ' : 'turun ')
                            . number_format(abs($b['perubahan']) * 100, 0, ',', '.') . '% dibanding '
                            . $prev['awal']->translatedFormat('F Y') . ' ('
                            . $this->kg($prev['total']) . ' menjadi ' . $this->kg($b['total'])
                            . '). Cek apakah ada hari yang belum diisi atau salah input.',
                    ];
                }
            }
            $prev = $b;
        }
        unset($b);

        // Porsi residu menyimpang dari rata-rata bulan lain
        $lengkap = array_filter($bulan, fn ($m) => $this->lengkap($m) && $m['persen_residu'] !== null);
        if (count($lengkap) >= 3) {
            foreach ($lengkap as $ym => $m) {
                $lain = array_filter($lengkap, fn ($x) => $x['ym'] !== $ym);
                $rata = array_sum(array_column($lain, 'persen_residu')) / count($lain);

                if (abs($m['persen_residu'] - $rata) >= self::AMBANG_RESIDU) {
                    $anomali[] = [
                        'tingkat' => 'peringatan',
                        'judul'   => $m['awal']->translatedFormat('F Y'),
                        'detail'  => 'Porsi residu ' . number_format($m['persen_residu'] * 100, 1, ',', '.')
                            . '% jauh dari rata-rata bulan lain (' . number_format($rata * 100, 1, ',', '.') . '%).',
                    ];
                }
            }
        }

        // Tanggal bolong: kosong di antara hari yang sudah terisi
        foreach ($bulan as $b) {
            if ($b['jumlah_hari'] < 2) {
                continue;
            }
            $tgl = array_keys($b['hari']);
            sort($tgl);

            $hilang = [];
            $d = Carbon::parse($tgl[0]);
            $akhir = Carbon::parse(end($tgl));
            while ($d->lt($akhir)) {
                if (!isset($b['hari'][$d->format('Y-m-d')])) {
                    $hilang[] = (int) $d->format('j');
                }
                $d->addDay();
            }

            if ($hilang) {
                $anomali[] = [
                    'tingkat' => 'catatan',
                    'judul'   => $b['awal']->translatedFormat('F Y'),
                    'detail'  => 'Tanggal tanpa data di antara hari yang terisi: ' . $this->ringkasTanggal($hilang) . '.',
                ];
            }
        }

        return $anomali;
    }

    private function anomaliArea(array $area): array
    {
        $anomali = [];
        $lonjakan = [];

        foreach ($area as $a) {
            $hari = $a['hari'];
            ksort($hari);

            // Lonjakan harian
            $nilai = array_values(array_filter(array_column($hari, 'total'), fn ($v) => $v > 0));
            if (count($nilai) >= self::MIN_SAMPEL) {
                sort($nilai);
                $q1  = $this->persentil($nilai, 0.25);
                $q3  = $this->persentil($nilai, 0.75);
                $med = $this->persentil($nilai, 0.5);
                $batas = $q3 + 3 * max($q3 - $q1, $med * 0.25);

                foreach ($hari as $tgl => $h) {
                    if ($h['total'] > $batas) {
                        $lonjakan[] = [
                            'tingkat' => 'perhatian',
                            'rasio'   => $med > 0 ? $h['total'] / $med : 0,
                            'judul'   => Carbon::parse($tgl)->translatedFormat('j F Y') . ' · ' . $a['nama'],
                            'detail'  => $this->kg($h['total']) . ', sekitar '
                                . number_format($med > 0 ? $h['total'] / $med : 0, 1, ',', '.')
                                . '× nilai tengah area ini (' . $this->kg($med)
                                . '). Periksa salah ketik atau salah satuan (kg/ton).',
                        ];
                    }
                }
            }

            // Nilai identik berhari-hari
            foreach ($this->cariNilaiSama($hari) as $run) {
                $anomali[] = [
                    'tingkat' => 'perhatian',
                    'judul'   => $a['nama'],
                    'detail'  => 'Nilai identik ' . $run['jumlah'] . ' hari berturut-turut ('
                        . Carbon::parse($run['mulai'])->translatedFormat('j M Y') . ' sampai '
                        . Carbon::parse($run['selesai'])->translatedFormat('j M Y')
                        . '): organik, anorganik, dan residu sama persis. Kemungkinan salin-tempel.',
                ];
            }
        }

        usort($lonjakan, fn ($x, $y) => $y['rasio'] <=> $x['rasio']);
        $sisa = count($lonjakan) - self::MAKS_LONJAKAN;
        $lonjakan = array_slice($lonjakan, 0, self::MAKS_LONJAKAN);
        if ($sisa > 0) {
            $lonjakan[] = [
                'tingkat' => 'perhatian',
                'rasio'   => 0,
                'judul'   => 'Lonjakan lain',
                'detail'  => "Masih ada {$sisa} lonjakan harian lain yang lebih kecil.",
            ];
        }

        return array_merge($lonjakan, $anomali);
    }

    /** @return array<int, array{mulai:string, selesai:string, jumlah:int, kunci:string}> */
    private function cariNilaiSama(array $hari): array
    {
        $hasil = [];
        $run = null;

        foreach ($hari as $tgl => $h) {
            $lanjut = $run
                && $run['kunci'] === $h['kunci']
                && Carbon::parse($run['selesai'])->addDay()->format('Y-m-d') === $tgl;

            if ($lanjut) {
                $run['selesai'] = $tgl;
                $run['jumlah']++;
                continue;
            }

            if ($run && $run['jumlah'] >= self::MIN_NILAI_SAMA) {
                $hasil[] = $run;
            }
            $run = $h['total'] > 0
                ? ['mulai' => $tgl, 'selesai' => $tgl, 'jumlah' => 1, 'kunci' => $h['kunci']]
                : null;
        }

        if ($run && $run['jumlah'] >= self::MIN_NILAI_SAMA) {
            $hasil[] = $run;
        }

        return $hasil;
    }

    /** Persentil dengan interpolasi linear. $terurut harus sudah diurutkan naik dan tidak kosong. */
    private function persentil(array $terurut, float $p): float
    {
        $idx   = (count($terurut) - 1) * $p;
        $bawah = (int) floor($idx);
        $atas  = (int) ceil($idx);

        return $terurut[$bawah] + ($terurut[$atas] - $terurut[$bawah]) * ($idx - $bawah);
    }

    /** [5, 12, 13, 14] menjadi "5, 12–14" */
    private function ringkasTanggal(array $hari): string
    {
        sort($hari);
        $out = [];
        $mulai = $akhir = null;

        foreach ($hari as $d) {
            if ($mulai === null) {
                $mulai = $akhir = $d;
            } elseif ($d === $akhir + 1) {
                $akhir = $d;
            } else {
                $out[] = $mulai === $akhir ? (string) $mulai : "{$mulai}–{$akhir}";
                $mulai = $akhir = $d;
            }
        }
        if ($mulai !== null) {
            $out[] = $mulai === $akhir ? (string) $mulai : "{$mulai}–{$akhir}";
        }

        return implode(', ', $out);
    }

    private function kg(float $v): string
    {
        return number_format($v, 0, ',', '.') . ' kg';
    }
}