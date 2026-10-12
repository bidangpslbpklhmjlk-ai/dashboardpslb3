<?php

namespace App\Services;

use App\Models\NeracaSampahArea;
use App\Models\NeracaSampahHarian;
use App\Models\NeracaSampahLaporan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Membaca workbook "Log Book dan Neraca Pengelolaan Sampah" (format asli industri).
 * Hanya sheet bulanan yang dibaca. Tiga sheet rekap dihitung ulang oleh aplikasi.
 */
class NeracaSampahImporter
{
    /** Kolom awal (indeks, A=1) blok 4 kolom tiap area: organik, anorganik, residu, total(rumus) */
    private const AREA_KOLOM = [
        'kantor' => 3,   // C
        'parkir' => 7,   // G
        'tunggu' => 11,  // K
        'makan'  => 15,  // O
        'kapal'  => 19,  // S
        'lain'   => 23,  // W
    ];

    private const BARIS_AWAL  = 8;   // tanggal 1
    private const BARIS_AKHIR = 38;  // tanggal 31

    private const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'nopember' => 11,
        'desember' => 12,
    ];

    /**
     * @return array{total_baris:int, bulan:array, warnings:array}
     * @throws \RuntimeException untuk kesalahan yang aman ditampilkan ke pengguna
     */
    public function import(NeracaSampahLaporan $laporan, string $path, array $bulanDipilih = []): array
    {
        // Nomor bulan -> tanggal awal bulan di dalam periode laporan (menentukan tahun)
        $petaBulan = [];
        foreach ($laporan->bulanList() as $awal) {
            if (isset($petaBulan[$awal->month])) {
                throw new \RuntimeException('Periode lebih dari 12 bulan belum didukung untuk impor.');
            }
            $petaBulan[$awal->month] = $awal;
        }

        $areaIds = NeracaSampahArea::whereIn('kode', array_keys(self::AREA_KOLOM))->pluck('id', 'kode');
        if ($areaIds->count() < count(self::AREA_KOLOM)) {
            throw new \RuntimeException('Master area belum lengkap. Jalankan NeracaSampahAreaSeeder dulu.');
        }

        // Pilih sheet bulanan berdasarkan nama sheet (bukan teks di dalam sel)
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $sheetTarget = [];
        foreach ($reader->listWorksheetNames($path) as $nama) {
            $n = self::BULAN[mb_strtolower(trim($nama))] ?? null;
            if ($n && isset($petaBulan[$n])) {
                $sheetTarget[$nama] = $petaBulan[$n];
            }
        }
        if (!$sheetTarget) {
            throw new \RuntimeException('Tidak ada sheet bulanan (Juli, Agustus, dst.) yang sesuai dengan periode laporan.');
        }
        if ($bulanDipilih) {
            $sheetTarget = array_filter(
                $sheetTarget,
                fn ($awal) => in_array($awal->format('Y-m'), $bulanDipilih, true)
            );

            if (!$sheetTarget) {
                throw new \RuntimeException('Sheet untuk bulan yang dipilih tidak ditemukan di file.');
            }
        }
        $reader->setLoadSheetsOnly(array_keys($sheetTarget));
        $spreadsheet = $reader->load($path);

        $rows = [];
        $warnings = [];
        $ringkasan = [];
        $now = now();

        foreach ($sheetTarget as $nama => $awalBulan) {
            $ws = $spreadsheet->getSheetByName($nama);

            if (!$this->layoutValid($ws)) {
                $warnings[] = "Sheet {$nama}: susunan kolom tidak sesuai format, dilewati.";
                continue;
            }

            $jumlahBaris = 0;
            $totalKg = 0.0;
            $luarPeriode = 0;

            for ($r = self::BARIS_AWAL; $r <= self::BARIS_AKHIR; $r++) {
                $hari = $r - self::BARIS_AWAL + 1;

                // Tanggal 29-31 di bulan yang lebih pendek dilewati
                if (!checkdate($awalBulan->month, $hari, $awalBulan->year)) {
                    continue;
                }

                $tgl = Carbon::create($awalBulan->year, $awalBulan->month, $hari)->startOfDay();
                if ($tgl->lt($laporan->periode_mulai) || $tgl->gt($laporan->periode_selesai)) {
                    $luarPeriode++;
                    continue;
                }

                foreach (self::AREA_KOLOM as $kode => $kolom) {
                    $nilai = [];
                    $ada = false;
                    $salah = false;

                    foreach ([0, 1, 2] as $i) {
                        [$v, $err] = $this->baca($ws, $kolom + $i, $r);
                        if ($err) {
                            $sel = Coordinate::stringFromColumnIndex($kolom + $i) . $r;
                            $warnings[] = "Sheet {$nama} sel {$sel}: {$err}, baris area ini dilewati.";
                            $salah = true;
                        }
                        if ($v !== null) $ada = true;
                        $nilai[$i] = $v ?? 0.0;
                    }

                    if ($salah || !$ada) {
                        continue; // sel salah atau kosong semua: tidak disimpan
                    }

                    $rows[] = [
                        'laporan_id'   => $laporan->id,
                        'area_id'      => $areaIds[$kode],
                        'tanggal'      => $tgl->toDateString(),
                        'organik_kg'   => $nilai[0],
                        'anorganik_kg' => $nilai[1],
                        'residu_kg'    => $nilai[2],
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                    $jumlahBaris++;
                    $totalKg += array_sum($nilai);
                }
            }

            if ($luarPeriode > 0) {
                $warnings[] = "Sheet {$nama}: {$luarPeriode} tanggal di luar periode laporan dilewati.";
            }

            $ringkasan[$awalBulan->translatedFormat('F Y')] = [
                'baris'    => $jumlahBaris,
                'total_kg' => $totalKg,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if (!$rows) {
            throw new \RuntimeException('Tidak ada data angka yang bisa dibaca dari file.');
        }

        // Upsert: unggah ulang file yang sudah dikoreksi menimpa data, tidak menggandakan
        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                NeracaSampahHarian::upsert(
                    $chunk,
                    ['laporan_id', 'area_id', 'tanggal'],
                    ['organik_kg', 'anorganik_kg', 'residu_kg', 'updated_at']
                );
            }
        });

        return [
            'total_baris' => count($rows),
            'bulan'       => $ringkasan,
            'warnings'    => $warnings,
        ];
    }

    /** Pastikan judul kolom tiap blok area sesuai format yang diharapkan */
    private function layoutValid(Worksheet $ws): bool
    {
        $tanggal = mb_strtolower(trim((string) $ws->getCell('B4')->getValue()));
        if ($tanggal !== 'tanggal') {
            return false;
        }

        foreach (self::AREA_KOLOM as $kolom) {
            $organik   = mb_strtolower((string) $ws->getCell([$kolom, 7])->getValue());
            $anorganik = mb_strtolower((string) $ws->getCell([$kolom + 1, 7])->getValue());
            $residu    = mb_strtolower((string) $ws->getCell([$kolom + 2, 7])->getValue());

            if (!str_contains($organik, 'organik')
                || !str_contains($anorganik, 'anorganik')
                || !str_contains($residu, 'residu')) {
                return false;
            }
        }
        return true;
    }

    /** @return array{0:?float, 1:?string} [nilai, pesan error] */
    private function baca(Worksheet $ws, int $kolom, int $baris): array
    {
        $cell = $ws->getCell([$kolom, $baris]);
        $v = $cell->getValue();

        // Kalau selnya rumus, pakai nilai cache dari Excel (tanpa menghitung ulang)
        if (is_string($v) && str_starts_with($v, '=')) {
            $v = $cell->getOldCalculatedValue();
        }

        if ($v === null || (is_string($v) && in_array(trim($v), ['', '-'], true))) {
            return [null, null];
        }
        if (!is_numeric($v)) {
            return [null, 'bukan angka'];
        }
        $v = (float) $v;
        if ($v < 0) {
            return [null, 'bernilai negatif'];
        }
        return [$v, null];
    }
}