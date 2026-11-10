<?php

namespace App\Services;

use App\Models\BankSampah;
use App\Models\BankSampahTimbangan;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Impor timbangan (sheet "Detail Data", bentuk lebar: bulan = kolom) ke bank_sampah_timbangan.
 * Semua angka ditempelkan ke sebuah BSU:
 *   - kolom Nama Bank Sampah terisi -> BSU dengan nama itu di desanya (baru dibuat bila belum ada)
 *   - hanya Desa (data lama per desa):
 *       desa punya tepat satu BSU -> ditempel ke BSU itu
 *       belum punya BSU           -> dibuat "BSU <Desa>" (sementara, diambil alih saat daftar induk diimpor)
 *       punya lebih dari satu     -> ditempel ke "<Desa> (rekap desa, belum per bank)"
 *   - tanpa Desa (mis. PKK Kabupaten) -> unit tingkat kecamatan/kabupaten
 */
class BankSampahImporter
{
    private const BULAN = [
        'januari' => 1, 'jan' => 1, 'februari' => 2, 'feb' => 2, 'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4, 'mei' => 5, 'juni' => 6, 'jun' => 6, 'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8, 'ags' => 8, 'september' => 9, 'sep' => 9,
        'oktober' => 10, 'okt' => 10, 'november' => 11, 'nov' => 11, 'desember' => 12, 'des' => 12,
    ];
    private const JENIS = ['desa', 'sekolah', 'pkk', 'lainnya'];

    public function import(string $path, int $tahun, bool $simpan = true): array
    {
        $book  = IOFactory::load($path);
        $sheet = $book->getSheetByName('Detail Data') ?? $book->getSheet(0);
        $rows  = $sheet->toArray(null, true, false, false);
        $c     = $this->bacaHeader($rows);
        $wil   = new WilayahLookup();

        $out = ['baris_dibaca' => 0, 'bank_baru' => 0, 'timbangan_disimpan' => 0,
                'dilewati' => [], 'perlu_dicek' => [], 'disimpan' => $simpan];

        DB::beginTransaction();
        try {
            $kecTerakhir = null;

            for ($i = $c['hdr'] + 1, $n = count($rows); $i < $n; $i++) {
                $r  = $rows[$i];
                $no = $i + 1;
                $teksKec   = $this->sel($r, $c['kec']);
                $teksDesa  = $this->sel($r, $c['desa']);
                $teksBank  = $this->sel($r, $c['bank']);
                $teksJenis = mb_strtolower($this->sel($r, $c['jenis']));

                foreach ([$teksKec, $teksDesa, $teksBank] as $t) {
                    if (preg_match('/^(jumlah|total)\b/i', $t)) { $out['dilewati'][] = "baris $no: $t"; continue 2; }
                }
                if ($teksDesa === '' && $teksBank === '') {
                    $out['dilewati'][] = "baris $no: tanpa nama (subtotal/angka lepas)";
                    continue;
                }

                $berat = [];
                foreach ($c['bulan'] as $bulan => $idx) {
                    $v = $this->angka($r[$idx] ?? null);
                    if ($v !== null) $berat[$bulan] = $v;
                }
                $out['baris_dibaca']++;

                // --- kecamatan ---
                $kodeKec = null;
                if ($teksKec !== '') {
                    $kecTerakhir = $teksKec;
                    $kodeKec = $wil->kecamatan($teksKec);
                    if (!$kodeKec) $out['perlu_dicek'][] = "baris $no: kecamatan '$teksKec' tidak dikenal, unit dicatat tingkat kabupaten";
                } elseif ($kecTerakhir && $teksDesa !== '') {
                    // sel kosong (mis. sel gabungan): pakai kecamatan sebelumnya hanya bila desanya ada di sana
                    $kk = $wil->kecamatan($kecTerakhir);
                    if ($kk && $wil->desa($kk, $teksDesa)) $kodeKec = $kk;
                }

                // --- desa ---
                $kodeDesa = null;
                if ($kodeKec && $teksDesa !== '') {
                    $kodeDesa = $wil->desa($kodeKec, $teksDesa, $cat);
                    if ($kodeDesa && $cat) $out['perlu_dicek'][] = "baris $no: desa '$teksDesa' $cat";
                }
                if ($teksDesa !== '' && !$kodeDesa && !$this->sama($teksDesa, $teksBank)) {
                    $out['perlu_dicek'][] = "baris $no: '$teksDesa' tidak cocok dengan desa"
                        . ($kodeKec ? " di $teksKec" : '') . ', unit dicatat tanpa desa';
                }

                // --- cari/buat BSU ---
                $jenis = in_array($teksJenis, self::JENIS, true) ? $teksJenis
                    : ($kodeDesa ? 'desa'
                    : (preg_match('/^(sd|smp|sma|smk|mts|ma|sekolah)\b/i', $teksDesa ?: $teksBank) ? 'sekolah'
                    : (preg_match('/pkk/i', $teksDesa ?: $teksBank) ? 'pkk' : 'lainnya')));

                [$bank, $baru, $catatan] = $this->tentukanBank($kodeKec, $kodeDesa, $teksDesa, $teksBank, $jenis);
                if ($catatan) $out['perlu_dicek'][] = "baris $no: $catatan";
                if ($baru) $out['bank_baru']++;

                foreach ($berat as $bulan => $kg) {
                    BankSampahTimbangan::updateOrCreate(
                        ['bank_sampah_id' => $bank->id, 'tahun' => $tahun, 'bulan' => $bulan],
                        ['berat_kg' => $kg]
                    );
                    $out['timbangan_disimpan']++;
                }
            }
            $simpan ? DB::commit() : DB::rollBack();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return $out;
    }

    /** @return array{0:BankSampah,1:bool,2:?string} bank, apakah baru dibuat, catatan untuk laporan */
    private function tentukanBank(?string $kodeKec, ?string $kodeDesa, string $teksDesa, string $teksBank, string $jenis): array
    {
        // Tanpa desa: unit tingkat kecamatan/kabupaten
        if (!$kodeDesa) {
            $nama = $teksBank !== '' ? $teksBank : $teksDesa;
            return $this->cariAtauBuat($kodeKec, null, $nama, $jenis, null);
        }

        // Dengan desa dan nama BSU yang bukan sekadar nama desa
        if ($teksBank !== '' && !$this->sama($teksBank, $teksDesa)) {
            $ada = $this->cari($kodeKec, $kodeDesa, $teksBank);
            if ($ada) return [$ada, false, null];
            $bank = BankSampah::create(['nama' => $teksBank, 'kode_kecamatan' => $kodeKec, 'kode_desa' => $kodeDesa,
                                        'jenis' => $jenis, 'aktif' => true]);
            return [$bank, true, "BSU '$teksBank' belum ada di daftar induk, dibuat baru"];
        }

        // Data per desa
        $di = BankSampah::where('kode_desa', $kodeDesa)->get();
        if ($di->count() === 1) return [$di->first(), false, null];

        $namaDesa = $teksDesa !== '' ? $teksDesa : $teksBank;
        if ($di->isEmpty()) {
            $bank = BankSampah::create(['nama' => "BSU $namaDesa", 'kode_kecamatan' => $kodeKec, 'kode_desa' => $kodeDesa,
                                        'jenis' => 'desa', 'aktif' => true]);
            return [$bank, true, "desa '$namaDesa' belum punya BSU di daftar induk, dibuat 'BSU $namaDesa' sementara"];
        }

        $nama = "$namaDesa (rekap desa, belum per bank)";
        [$bank, $baru] = $this->cariAtauBuat($kodeKec, $kodeDesa, $nama, 'desa', null);
        return [$bank, $baru, "desa '$namaDesa' punya " . $di->count() . ' BSU, angka ditempel ke rekap desa'];
    }

    private function cariAtauBuat(?string $kodeKec, ?string $kodeDesa, string $nama, string $jenis, ?string $catatan): array
    {
        $ada = $this->cari($kodeKec, $kodeDesa, $nama);
        if ($ada) return [$ada, false, $catatan];
        $bank = BankSampah::create(['nama' => $nama, 'kode_kecamatan' => $kodeKec, 'kode_desa' => $kodeDesa,
                                    'jenis' => $jenis, 'aktif' => true]);
        return [$bank, true, $catatan];
    }

    private function cari(?string $kodeKec, ?string $kodeDesa, string $nama): ?BankSampah
    {
        return BankSampah::where('kode_kecamatan', $kodeKec)->where('kode_desa', $kodeDesa)->get()
            ->first(fn ($b) => $this->sama($b->nama, $nama));
    }

    private function sama(string $a, string $b): bool
    {
        return $b !== '' && WilayahLookup::norm($a) === WilayahLookup::norm($b);
    }

    private function sel(array $r, ?int $idx): string
    {
        return $idx === null ? '' : trim((string) ($r[$idx] ?? ''));
    }

    private function bacaHeader(array $rows): array
    {
        foreach (array_slice($rows, 0, 15, true) as $i => $r) {
            $bulan = [];
            foreach ($r as $idx => $cell) {
                $k = mb_strtolower(trim((string) $cell));
                if (isset(self::BULAN[$k])) $bulan[self::BULAN[$k]] = $idx;
            }
            if (count($bulan) < 3) continue;

            $c = ['hdr' => $i, 'bulan' => $bulan, 'kec' => null, 'desa' => null, 'bank' => null, 'jenis' => null];
            foreach ($r as $idx => $cell) {
                $k = mb_strtolower(trim((string) $cell));
                if ($k === '' || isset(self::BULAN[$k])) continue;
                if ($c['kec'] === null && str_contains($k, 'kecamatan'))     $c['kec'] = $idx;
                elseif ($c['jenis'] === null && str_contains($k, 'jenis'))   $c['jenis'] = $idx;
                elseif ($c['desa'] === null && str_contains($k, 'desa'))     $c['desa'] = $idx;
                elseif ($c['bank'] === null && str_contains($k, 'bank'))     $c['bank'] = $idx;
            }
            if ($c['desa'] === null && $c['bank'] === null) {
                throw new \RuntimeException('Kolom Nama Bank Sampah atau Desa tidak ditemukan di baris header.');
            }
            return $c;
        }
        throw new \RuntimeException('Baris header bulan tidak ditemukan di sheet Detail Data.');
    }

    /** "-", kosong, atau teks = tidak ada data (null), bukan 0. */
    private function angka(mixed $v): ?float
    {
        if ($v === null) return null;
        if (is_int($v) || is_float($v)) return (float) $v;
        $s = trim((string) $v);
        if ($s === '' || $s === '-' || $s === '–') return null;
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $s)) $s = str_replace(['.', ','], ['', '.'], $s);
        elseif (preg_match('/^\d+,\d+$/', $s)) $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float) $s : null;
    }
}