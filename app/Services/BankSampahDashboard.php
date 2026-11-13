<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Data kartu Bank Sampah di dashboard admin. Satu tahun, ringkas. */
class BankSampahDashboard
{
    private const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function data(int $tahun): array
    {
        $f0 = fn ($n) => number_format((float) $n, 0, ',', '.');

        $berjalan      = $tahun === (int) now()->year;
        $bulanBerjalan = $berjalan ? (int) now()->month : null;

        $perBulan = $this->perBulan($tahun);
        $lalu     = $this->perBulan($tahun - 1);
        $total    = array_sum($perBulan);

        // --- per desa/kecamatan ---
        $rows = DB::table('bank_sampah_timbangan as t')
            ->join('bank_sampah as b', 'b.id', '=', 't.bank_sampah_id')
            ->where('t.tahun', $tahun)
            ->groupBy('b.kode_desa', 'b.kode_kecamatan')
            ->selectRaw('b.kode_desa, b.kode_kecamatan, SUM(t.berat_kg) as kg')
            ->get();

        $kgKec = []; $aktifKec = []; $kgKabupaten = 0.0;
        foreach ($rows as $r) {
            $kg = (float) $r->kg;
            if ($r->kode_kecamatan === null) { $kgKabupaten += $kg; continue; }
            $kgKec[$r->kode_kecamatan] = ($kgKec[$r->kode_kecamatan] ?? 0) + $kg;
            if ($r->kode_desa !== null && $kg > 0) {
                $aktifKec[$r->kode_kecamatan] = ($aktifKec[$r->kode_kecamatan] ?? 0) + 1;
            }
        }

        $jumlahDesa = DB::table('wilayah_desa')->selectRaw('kode_kecamatan, count(*) as n')
            ->groupBy('kode_kecamatan')->pluck('n', 'kode_kecamatan');

        $kecamatan = DB::table('wilayah_kecamatan')->orderBy('nama')->get(['kode', 'nama'])
            ->map(function ($k) use ($kgKec, $aktifKec, $jumlahDesa) {
                $n = (int) ($jumlahDesa[$k->kode] ?? 0);
                $a = (int) ($aktifKec[$k->kode] ?? 0);
                return (object) ['nama' => $k->nama, 'desa' => $n, 'aktif' => $a,
                                 'persen' => $n ? (int) round($a / $n * 100) : 0,
                                 'kg' => round((float) ($kgKec[$k->kode] ?? 0), 1)];
            });

        $desaTotal = (int) $kecamatan->sum('desa');
        $desaAktif = (int) $kecamatan->sum('aktif');
        $persenDesa = $desaTotal ? (int) round($desaAktif / $desaTotal * 100) : 0;

        $bsuMenimbang = (int) DB::table('bank_sampah_timbangan')->where('tahun', $tahun)
            ->where('berat_kg', '>', 0)->distinct()->count('bank_sampah_id');
        $bsuAktif = (int) DB::table('bank_sampah')->where('aktif', true)->count();

        // Rata-rata per bulan: hanya bulan yang ada datanya, tanpa bulan yang masih berjalan
        $bulanTerisi = collect(range(1, 12))->filter(fn ($m) => $perBulan[$m - 1] > 0 && $m !== $bulanBerjalan)->values();
        if ($bulanTerisi->isEmpty()) {
            $bulanTerisi = collect(range(1, 12))->filter(fn ($m) => $perBulan[$m - 1] > 0)->values();
        }
        $rataBulan = $bulanTerisi->isNotEmpty() ? $bulanTerisi->avg(fn ($m) => $perBulan[$m - 1]) : 0;

        // Peringkat kecamatan
        $topKec = $kecamatan->where('kg', '>', 0)->sortByDesc('kg')->values();

        // --- ringkasan otomatis ---
        $ringkas = [];
        if ($bulanBerjalan && $perBulan[$bulanBerjalan - 1] > 0) {
            $ringkas[] = self::BLN[$bulanBerjalan - 1] . " {$tahun} masih berjalan, datanya belum lengkap.";
        }
        if ($bulanTerisi->isNotEmpty() && $total > 0) {
            $iMax = $bulanTerisi->sortByDesc(fn ($m) => $perBulan[$m - 1])->first();
            $ringkas[] = 'Volume tertinggi terjadi pada bulan ' . self::BLN[$iMax - 1] . ' (' . $f0($perBulan[$iMax - 1]) . ' kg).';
            if ($bulanTerisi->count() > 1) {
                $iMin = $bulanTerisi->sortBy(fn ($m) => $perBulan[$m - 1])->first();
                $ringkas[] = 'Volume terendah terjadi pada bulan ' . self::BLN[$iMin - 1] . ' (' . $f0($perBulan[$iMin - 1]) . ' kg).';
            }
        }
        $batas = $berjalan ? $bulanBerjalan - 1 : 12; // bandingkan sampai bulan terakhir yang lengkap
        if ($batas >= 1) {
            $now  = array_sum(array_slice($perBulan, 0, $batas));
            $sama = array_sum(array_slice($lalu, 0, $batas));
            if ($sama > 0) {
                $selisih = round(($now - $sama) / $sama * 100, 1);
                $ringkas[] = 'Dibanding periode yang sama tahun ' . ($tahun - 1) . ', volume ' . ($selisih >= 0 ? 'naik ' : 'turun ')
                    . number_format(abs($selisih), 1, ',', '.') . '%.';
            }
        }
        if ($desaTotal) {
            $ringkas[] = "{$desaAktif} dari {$desaTotal} desa ({$persenDesa}%) sudah menimbang; " . ($desaTotal - $desaAktif) . ' desa belum.';
        }
        if ($total > 0 && $topKec->isNotEmpty()) {
            $ringkas[] = 'Kecamatan ' . $topKec->first()->nama . ' menyumbang ' . number_format($topKec->first()->kg / $total * 100, 1, ',', '.') . '% dari total.';
        }

        $daftarTahun = DB::table('bank_sampah_timbangan')->distinct()->pluck('tahun')
            ->map(fn ($y) => (int) $y)->push((int) now()->year)->push($tahun)->unique()->sortDesc()->values()->all();

        return [
            'tahun' => $tahun, 'berjalan' => $berjalan, 'daftarTahun' => $daftarTahun,
            'total' => round($total, 1), 'rataBulan' => round($rataBulan, 1),
            'desaTotal' => $desaTotal, 'desaAktif' => $desaAktif, 'persenDesa' => $persenDesa,
            'bsuMenimbang' => $bsuMenimbang, 'bsuAktif' => $bsuAktif,
            'kabupaten' => round($kgKabupaten, 1),
            'ringkas' => $ringkas,
            'chart' => [
                'labels'    => self::BLN,
                'tahun'     => $tahun,
                'lalu'      => $tahun - 1,
                'kg'        => $perBulan,
                'kgLalu'    => $lalu,
                'topNama'   => $topKec->take(10)->pluck('nama')->all(),
                'topKg'     => $topKec->take(10)->pluck('kg')->all(),
                'cakNama'   => $kecamatan->sortByDesc('persen')->pluck('nama')->values()->all(),
                'cakPersen' => $kecamatan->sortByDesc('persen')->pluck('persen')->values()->all(),
            ],
        ];
    }

    /** 12 angka (Jan–Des), kg */
    private function perBulan(int $tahun): array
    {
        $rows = DB::table('bank_sampah_timbangan')->where('tahun', $tahun)
            ->groupBy('bulan')->selectRaw('bulan, SUM(berat_kg) as kg')->pluck('kg', 'bulan');
        return collect(range(1, 12))->map(fn ($m) => round((float) ($rows[$m] ?? 0), 1))->all();
    }
}