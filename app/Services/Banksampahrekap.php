<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Menyusun data halaman utama Bank Sampah: rekap per kecamatan, total, tren, dan analisis.
 * Filter: tahun, rentang bulan (dari/sampai, atau satu bulan lewat "bulan"), kecamatan, jenis unit.
 */
class BankSampahRekap
{
    public const JENIS = ['desa' => 'Desa', 'sekolah' => 'Sekolah', 'pkk' => 'PKK', 'lainnya' => 'Lainnya'];
    private const REKAP_DESA = '(rekap desa, belum per bank)';

    public function susun(Request $request): array
    {
        // ---------- filter ----------
        $tahun = max(2000, min(2100, (int) $request->input('tahun', 2026)));
        $klem  = fn ($v) => max(1, min(12, (int) $v));
        if ($request->filled('bulan')) {
            $dari = $sampai = $klem($request->input('bulan'));
        } else {
            $dari   = $request->filled('dari') ? $klem($request->input('dari')) : 1;
            $sampai = $request->filled('sampai') ? $klem($request->input('sampai')) : 12;
            if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
        }
        $bulan = $dari === $sampai ? $dari : null;

        $kecamatanOpsi = DB::table('wilayah_kecamatan')->orderBy('nama')->get(['kode', 'nama']);
        $kecFilter = $kecamatanOpsi->contains('kode', (string) $request->input('kecamatan'))
            ? (string) $request->input('kecamatan') : null;
        $jenisFilter = array_key_exists((string) $request->input('jenis'), self::JENIS)
            ? (string) $request->input('jenis') : null;

        // ---------- data dasar ----------
        $pb = []; // [bank_id][bulan] => kg
        foreach (DB::table('bank_sampah_timbangan')->where('tahun', $tahun)->get(['bank_sampah_id', 'bulan', 'berat_kg']) as $t) {
            $pb[$t->bank_sampah_id][(int) $t->bulan] = (float) $t->berat_kg;
        }
        $jumlahRentang = function (array $perBulan, int $a, int $b): float {
            $s = 0.0;
            foreach ($perBulan as $m => $v) if ($m >= $a && $m <= $b) $s += $v;
            return $s;
        };

        $bsu = DB::table('bank_sampah')->orderBy('nama')
            ->get(['id', 'nama', 'jenis', 'aktif', 'kode_kecamatan', 'kode_desa'])
            ->filter(fn ($b) => (!$kecFilter || $b->kode_kecamatan === $kecFilter) && (!$jenisFilter || $b->jenis === $jenisFilter))
            ->values()
            ->map(function ($b) use ($pb, $jumlahRentang, $dari, $sampai) {
                $b->per_bulan = $pb[$b->id] ?? [];
                $b->kg        = $jumlahRentang($b->per_bulan, $dari, $sampai);
                return $b;
            });

        $namaDesa   = DB::table('wilayah_desa')->pluck('nama', 'kode');
        $bsuPerDesa = $bsu->whereNotNull('kode_desa')->groupBy('kode_desa');
        $unitPerKec = $bsu->whereNull('kode_desa')->whereNotNull('kode_kecamatan')->groupBy('kode_kecamatan');
        $desaPerKec = DB::table('wilayah_desa')->orderBy('nama')->get(['kode', 'kode_kecamatan', 'nama'])->groupBy('kode_kecamatan');

        // ---------- rekap per kecamatan ----------
        $rekap = DB::table('wilayah_kecamatan')->orderBy('nama')
            ->when($kecFilter, fn ($q) => $q->where('kode', $kecFilter))
            ->get()
            ->map(function ($k) use ($desaPerKec, $bsuPerDesa, $unitPerKec) {
                $desa = ($desaPerKec[$k->kode] ?? collect())->map(function ($d) use ($bsuPerDesa) {
                    $daftar = $bsuPerDesa[$d->kode] ?? collect();
                    $kg     = (float) $daftar->sum('kg');
                    return (object) [
                        'kode' => $d->kode, 'nama' => $d->nama, 'bsu' => $daftar->values(),
                        'jumlah_bsu' => $daftar->where('aktif', true)->count(),
                        'kg' => $kg, 'aktif' => $kg > 0,
                    ];
                })->values();

                $unit   = ($unitPerKec[$k->kode] ?? collect())->values();
                $aktif  = $desa->where('aktif', true)->count();
                $kgDesa = (float) $desa->sum('kg');
                $kgUnit = (float) $unit->sum('kg');

                return (object) [
                    'kode' => $k->kode, 'nama' => $k->nama, 'desa' => $desa, 'unit' => $unit,
                    'jumlah_desa' => $desa->count(),
                    'jumlah_bsu'  => $desa->sum('jumlah_bsu') + $unit->where('aktif', true)->count(),
                    'desa_aktif'  => $aktif,
                    'desa_belum'  => $desa->count() - $aktif,
                    'persen'      => $desa->count() ? (int) round($aktif / $desa->count() * 100) : 0,
                    'kg_desa' => $kgDesa, 'kg_unit' => $kgUnit, 'kg_total' => $kgDesa + $kgUnit,
                ];
            });

        $kabupaten = $bsu->whereNull('kode_kecamatan')->whereNull('kode_desa')->sortBy('nama')->values();

        $total = (object) [
            'desa'      => (float) $rekap->sum('kg_desa'),
            'unit_kec'  => (float) $rekap->sum('kg_unit'),
            'kabupaten' => (float) $kabupaten->sum('kg'),
        ];
        $total->semua = $total->desa + $total->unit_kec + $total->kabupaten;

        $bsuAktif = $bsu->where('aktif', true)->count();
        $tren = collect(range(1, 12))->map(fn ($m) => (float) $bsu->sum(fn ($b) => $b->per_bulan[$m] ?? 0));

        // ---------- analisis ----------
        $namaKec = $rekap->pluck('nama', 'kode');
        $lokasi  = function ($b) use ($namaDesa, $namaKec) {
            if ($b->kode_desa)      return ($namaDesa[$b->kode_desa] ?? '-') . ', ' . ($namaKec[$b->kode_kecamatan] ?? '');
            if ($b->kode_kecamatan) return 'Kec. ' . ($namaKec[$b->kode_kecamatan] ?? '');
            return 'Tingkat Kabupaten';
        };

        $sekarang      = now();
        $bulanBerjalan = $tahun === (int) $sekarang->year ? (int) $sekarang->month : null;
        $belumLengkap  = $bulanBerjalan !== null && $bulanBerjalan >= $dari && $bulanBerjalan <= $sampai;

        // 1. Perbandingan
        $panjang = $sampai - $dari + 1;
        $kgSebelumnya = ($dari - $panjang) >= 1
            ? (float) $bsu->sum(fn ($b) => $jumlahRentang($b->per_bulan, $dari - $panjang, $dari - 1))
            : null;
        $kgTahunLalu = (float) DB::table('bank_sampah_timbangan')
            ->where('tahun', $tahun - 1)->whereBetween('bulan', [$dari, $sampai])
            ->whereIn('bank_sampah_id', $bsu->pluck('id')->all())->sum('berat_kg');
        $persenBanding = fn ($now, $lalu) => ($lalu !== null && $lalu > 0) ? round(($now - $lalu) / $lalu * 100, 1) : null;

        $bulanTerisi = collect(range(1, 12))->filter(fn ($m) => $tren[$m - 1] > 0 && $m !== $bulanBerjalan);
        $tertinggi = $bulanTerisi->isEmpty() ? null : $bulanTerisi->sortByDesc(fn ($m) => $tren[$m - 1])->first();
        $terendah  = $bulanTerisi->count() < 2 ? null : $bulanTerisi->sortBy(fn ($m) => $tren[$m - 1])->first();

        $bsuMenimbang = $bsu->where('kg', '>', 0)->count();
        $desaAktifJml = $rekap->sum('desa_aktif');

        $perbandingan = (object) [
            'sebelumnya'       => $kgSebelumnya,
            'persen_sebelumnya'=> $persenBanding($total->semua, $kgSebelumnya),
            'tahun_lalu'       => $kgTahunLalu > 0 ? $kgTahunLalu : null,
            'persen_tahun_lalu'=> $persenBanding($total->semua, $kgTahunLalu > 0 ? $kgTahunLalu : null),
            'tertinggi'        => $tertinggi ? ['bulan' => $tertinggi, 'kg' => $tren[$tertinggi - 1]] : null,
            'terendah'         => $terendah ? ['bulan' => $terendah, 'kg' => $tren[$terendah - 1]] : null,
            'bsu_menimbang'    => $bsuMenimbang,
            'rata_bsu'         => $bsuMenimbang ? $total->semua / $bsuMenimbang : null,
            'rata_desa'        => $desaAktifJml ? $total->desa / $desaAktifJml : null,
        ];

        // 2. Peringkat
        $peringkat = (object) [
            'kecamatan' => $rekap->where('kg_total', '>', 0)->sortByDesc('kg_total')->take(5)
                ->map(fn ($k) => (object) ['nama' => $k->nama, 'info' => '', 'kg' => $k->kg_total])->values(),
            'desa' => $rekap->flatMap(fn ($k) => $k->desa->map(fn ($d) => (object) ['nama' => $d->nama, 'info' => 'Kec. ' . $k->nama, 'kg' => $d->kg]))
                ->where('kg', '>', 0)->sortByDesc('kg')->take(5)->values(),
            'bsu' => $bsu->where('kg', '>', 0)->sortByDesc('kg')->take(5)
                ->map(fn ($b) => (object) ['nama' => $b->nama, 'info' => $lokasi($b), 'kg' => $b->kg])->values(),
        ];

        // 3. Cakupan
        $belumMenimbang = $rekap->map(fn ($k) => (object) [
                'kecamatan' => $k->nama, 'desa' => $k->desa->where('aktif', false)->pluck('nama')->values(),
            ])->filter(fn ($x) => $x->desa->isNotEmpty())->values();

        $bsuDiam = $sampai > 1
            ? $bsu->filter(fn ($b) => $b->aktif && ($b->per_bulan[$sampai - 1] ?? 0) > 0 && ($b->per_bulan[$sampai] ?? 0) <= 0)
                ->map(fn ($b) => (object) ['nama' => $b->nama, 'info' => $lokasi($b), 'kg' => $b->per_bulan[$sampai - 1]])->values()
            : collect();

        $bsuBelumPernah = $bsu->filter(fn ($b) => $b->aktif && array_sum($b->per_bulan) <= 0)
            ->map(fn ($b) => (object) ['nama' => $b->nama, 'info' => $lokasi($b)])->values();

        // 4. Kualitas data
        $lonjakan = collect();
        foreach ($bsu as $b) {
            $nilai = array_filter($b->per_bulan, fn ($v) => $v > 0);
            if (count($nilai) < 4) continue;
            foreach ($nilai as $m => $v) {
                if ($m < $dari || $m > $sampai) continue;
                $lain = array_values(array_filter($nilai, fn ($x, $k) => $k !== $m, ARRAY_FILTER_USE_BOTH));
                sort($lain);
                $c   = count($lain);
                $med = $c % 2 ? $lain[intdiv($c, 2)] : ($lain[$c / 2 - 1] + $lain[$c / 2]) / 2;
                if ($med > 0 && $v >= 3 * $med && ($v - $med) >= 100) {
                    $lonjakan->push((object) ['nama' => $b->nama, 'info' => $lokasi($b), 'bulan' => $m,
                                              'kg' => $v, 'median' => $med, 'rasio' => $v / $med]);
                }
            }
        }
        $lonjakan = $lonjakan->sortByDesc('rasio')->take(10)->values();

        $belumDipecah = $bsu->filter(fn ($b) => str_ends_with($b->nama, self::REKAP_DESA)
                || ($b->kode_desa && isset($namaDesa[$b->kode_desa]) && $b->nama === 'BSU ' . $namaDesa[$b->kode_desa]))
            ->map(fn ($b) => (object) ['nama' => $b->nama, 'info' => $lokasi($b)])->values();

        $analisis = (object) [
            'perbandingan' => $perbandingan, 'peringkat' => $peringkat,
            'belum_menimbang' => $belumMenimbang, 'bsu_diam' => $bsuDiam, 'bsu_belum_pernah' => $bsuBelumPernah,
            'lonjakan' => $lonjakan, 'belum_dipecah' => $belumDipecah,
            'belum_lengkap' => $belumLengkap, 'bulan_berjalan' => $bulanBerjalan,
        ];

        return [
            'rekap' => $rekap, 'tahun' => $tahun, 'bulan' => $bulan, 'dari' => $dari, 'sampai' => $sampai,
            'kecFilter' => $kecFilter, 'jenisFilter' => $jenisFilter,
            'kecamatanOpsi' => $kecamatanOpsi, 'jenisOpsi' => self::JENIS,
            'total' => $total, 'kabupaten' => $kabupaten, 'bsuAktif' => $bsuAktif, 'tren' => $tren,
            'analisis' => $analisis,
        ];
    }
}