<?php

namespace App\Services;

use App\Models\BankSampah;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Impor daftar induk BSU: satu sheet per kecamatan, satu BSU dimulai di baris bernomor,
 * baris di bawahnya tanpa nomor adalah lanjutan (nama, nomor dan penerbit legalitas).
 * NIK tidak dibaca. Impor ulang memperbarui BSU yang sama, tidak menggandakan.
 */
class BsuImporter
{
    private const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
        'juli' => 7, 'agustus' => 8, 'agu' => 8, 'september' => 9, 'oktober' => 10, 'okt' => 10,
        'november' => 11, 'nov' => 11, 'desember' => 12, 'des' => 12,
    ];

    public function import(string $path, bool $simpan = true): array
    {
        $book = IOFactory::load($path);
        $wil  = new WilayahLookup();
        $out  = ['sheet_dibaca' => 0, 'bsu_baru' => 0, 'bsu_diperbarui' => 0,
                 'dilewati' => [], 'perlu_dicek' => [], 'disimpan' => $simpan];

        DB::beginTransaction();
        try {
            foreach ($book->getWorksheetIterator() as $sheet) {
                $judul = $sheet->getTitle();
                $rows  = $sheet->toArray(null, true, true, false);
                $c     = $this->kolom($rows);
                if (!$c) { $out['dilewati'][] = "sheet '$judul': kosong atau tanpa header"; continue; }
                $out['sheet_dibaca']++;

                foreach ($this->kelompokkan($rows, $c) as $rec) {
                    $r = $rec['r']; $baris = $rec['baris']; $at = "[$judul] baris $baris";

                    $teksKec  = $this->bersih($this->sel($r, $c['kec'])) ?? preg_replace('/^\d+\.\s*/', '', $judul);
                    $kodeKec  = $wil->kecamatan($teksKec);
                    if (!$kodeKec) $out['perlu_dicek'][] = "$at: kecamatan '$teksKec' tidak dikenal";

                    $teksDesa = $this->bersih($this->sel($r, $c['desa']));
                    $kodeDesa = null;
                    if ($teksDesa !== null) {
                        $kodeDesa = $wil->desa($kodeKec, $teksDesa, $cat);
                        if ($kodeDesa === null)  $out['perlu_dicek'][] = "$at: desa '$teksDesa' tidak cocok, BSU dicatat tanpa desa";
                        elseif ($cat)            $out['perlu_dicek'][] = "$at: desa '$teksDesa' $cat";
                    }

                    $bagian = [];
                    if (($n = $this->bersih($this->sel($r, $c['nama']))) !== null) $bagian[] = $n;
                    foreach ($rec['nama_extra'] as $x) {
                        // lanjutan yang sama persis dengan bagian sebelumnya (mis. "Anggrek" dua kali) dilewati
                        $dobel = false;
                        foreach ($bagian as $b) if (WilayahLookup::norm($b) === WilayahLookup::norm($x)) $dobel = true;
                        if (!$dobel) $bagian[] = $x;
                    }
                    $nama = $bagian ? implode(' ', $bagian) : null;

                    if ($nama === null && $teksDesa === null) { $out['dilewati'][] = "$at: tanpa nama dan desa"; continue; }
                    if ($nama === null) {
                        $nama = 'BSU ' . $teksDesa;
                        $out['perlu_dicek'][] = "$at: nama BSU belum diisi, sementara dipakai '$nama'";
                    }

                    $gagal   = false;
                    $tglMentah = $this->sel($r, $c['tgl']);
                    $tanggal = $this->tanggal($tglMentah, $gagal);
                    if ($gagal) $out['perlu_dicek'][] = "$at: tanggal terbit '$tglMentah' tidak terbaca, dikosongkan";

                    $data = [
                        'ketua'                => $this->bersih($this->sel($r, $c['ketua'])),
                        'no_hp'                => $this->telepon($this->sel($r, $c['hp'])),
                        'email'                => ($e = $this->bersih($this->sel($r, $c['email']))) ? mb_strtolower($e) : null,
                        'tenaga_pria'          => $this->bilangan($this->sel($r, $c['pria'])),
                        'tenaga_wanita'        => $this->bilangan($this->sel($r, $c['wanita'])),
                        'legalitas_jenis'      => $this->bersih($this->sel($r, $c['legal'])),
                        'legalitas_keterangan' => $rec['legal_extra'] ? implode(' | ', $rec['legal_extra']) : null,
                        'legalitas_tanggal'    => $tanggal,
                    ];

                    $bank = $this->cari($kodeKec, $kodeDesa, $nama);
                    // BSU sementara dari impor timbangan ("BSU <Desa>") diambil alih, bukan digandakan
                    if (!$bank && $kodeDesa && $teksDesa !== null) {
                        $di = BankSampah::where('kode_desa', $kodeDesa)->get();
                        $sementara = $di->first(fn ($b) => WilayahLookup::norm($b->nama) === WilayahLookup::norm('BSU ' . $teksDesa));
                        if ($sementara && $di->count() === 1) $bank = $sementara;
                    }

                    $baru = !$bank;
                    $bank ??= new BankSampah(['kode_kecamatan' => $kodeKec, 'kode_desa' => $kodeDesa,
                                              'jenis' => $kodeDesa ? 'desa' : 'lainnya', 'aktif' => true]);
                    $bank->nama = $nama;
                    foreach ($data as $k => $v) if ($v !== null) $bank->$k = $v;

                    $berubah = $bank->isDirty();
                    $bank->save();
                    if ($baru) $out['bsu_baru']++; elseif ($berubah) $out['bsu_diperbarui']++;
                }
            }
            $simpan ? DB::commit() : DB::rollBack();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return $out;
    }

    private function cari(?string $kodeKec, ?string $kodeDesa, string $nama): ?BankSampah
    {
        return BankSampah::where('kode_kecamatan', $kodeKec)->where('kode_desa', $kodeDesa)->get()
            ->first(fn ($b) => WilayahLookup::norm($b->nama) === WilayahLookup::norm($nama));
    }

    private function kolom(array $rows): ?array
    {
        $c = ['no' => null, 'kec' => null, 'desa' => null, 'nama' => null, 'legal' => null, 'tgl' => null,
              'ketua' => null, 'hp' => null, 'email' => null, 'pria' => null, 'wanita' => null];
        $tenaga = [];
        foreach (($rows[0] ?? []) as $i => $v) {
            $k = mb_strtolower(trim((string) $v));
            if ($k === '') continue;
            if ($k === 'no' || $k === 'no.')            $c['no'] = $i;
            elseif (str_contains($k, 'kecamatan'))      $c['kec'] = $i;
            elseif (str_contains($k, 'desa'))           $c['desa'] = $i;
            elseif (str_contains($k, 'nama'))           $c['nama'] = $i;
            elseif (str_contains($k, 'legalitas'))      $c['legal'] = $i;
            elseif (str_contains($k, 'tanggal'))        $c['tgl'] = $i;
            elseif (str_contains($k, 'ketua'))          $c['ketua'] = $i;
            elseif (str_contains($k, 'hp'))             $c['hp'] = $i;
            elseif (str_contains($k, 'email'))          $c['email'] = $i;
            elseif (str_contains($k, 'tenaga'))         $tenaga[] = $i;
        }
        if ($c['no'] === null || $c['desa'] === null || $c['nama'] === null) return null;

        $c['pria']   = $tenaga[0] ?? null;
        $c['wanita'] = $tenaga[1] ?? null;
        foreach (($rows[1] ?? []) as $i => $v) {
            $k = mb_strtolower(trim((string) $v));
            if ($k === 'pria')   $c['pria'] = $i;
            if ($k === 'wanita') $c['wanita'] = $i;
        }
        return $c;
    }

    private function kelompokkan(array $rows, array $c): array
    {
        $recs = []; $cur = null;
        for ($i = 1, $n = count($rows); $i < $n; $i++) {
            $r  = $rows[$i];
            $no = trim((string) ($r[$c['no']] ?? ''));
            if (preg_match('/^\d+\.?$/', $no)) {
                if ($cur) $recs[] = $cur;
                $cur = ['baris' => $i + 1, 'r' => $r, 'nama_extra' => [], 'legal_extra' => []];
                continue;
            }
            if (!$cur) continue; // baris sub-header
            if (($x = $this->bersih($this->sel($r, $c['nama']))) !== null)  $cur['nama_extra'][]  = $x;
            if (($y = $this->bersih($this->sel($r, $c['legal']))) !== null) $cur['legal_extra'][] = $y;
        }
        if ($cur) $recs[] = $cur;
        return $recs;
    }

    private function sel(array $r, ?int $idx): mixed
    {
        return $idx === null ? null : ($r[$idx] ?? null);
    }

    private function bersih(mixed $v): ?string
    {
        if ($v === null) return null;
        $s = trim((string) preg_replace('/\s+/u', ' ', (string) $v));
        $s = ltrim($s, "'");
        return in_array($s, ['', '_', '-', '–', '—'], true) ? null : $s;
    }

    private function telepon(mixed $v): ?string
    {
        $s = $this->bersih($v);
        if ($s === null) return null;
        $d = preg_replace('/[^\d+]/', '', $s);
        return $d === '' ? null : $d;
    }

    private function bilangan(mixed $v): ?int
    {
        $s = $this->bersih($v);
        return ($s !== null && is_numeric($s)) ? (int) $s : null;
    }

    private function tanggal(mixed $v, bool &$gagal): ?string
    {
        $gagal = false;
        if ($this->bersih($v) === null) return null;
        if (is_numeric($v) && (float) $v > 20000) {
            return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d');
        }
        $s = mb_strtolower(trim((string) $v));
        if (preg_match('/^(\d{1,2})\s+([a-z]+)\s+(\d{4})$/', $s, $m) && isset(self::BULAN[$m[2]])) {
            return sprintf('%04d-%02d-%02d', $m[3], self::BULAN[$m[2]], $m[1]);
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m)) return "$m[1]-$m[2]-$m[3]";
        $gagal = true;
        return null;
    }
}