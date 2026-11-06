<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Pencocokan nama kecamatan/desa ke tabel wilayah. Desa selalu dicocokkan di dalam kecamatannya. */
class WilayahLookup
{
    private array $kec  = []; // norm nama => kode
    private array $desa = []; // kode_kecamatan => [norm nama => [kode, nama]]

    public function __construct()
    {
        foreach (DB::table('wilayah_kecamatan')->get(['kode', 'nama']) as $k) {
            $this->kec[self::norm($k->nama)] = $k->kode;
        }
        foreach (DB::table('wilayah_desa')->get(['kode', 'kode_kecamatan', 'nama']) as $d) {
            $this->desa[$d->kode_kecamatan][self::norm($d->nama)] = [$d->kode, $d->nama];
        }
    }

    public static function norm(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('/^(desa|kelurahan|kel|kecamatan|kec)\.?\s+/u', '', $s);
        return preg_replace('/[^a-z0-9]/', '', $s);
    }

    public function kecamatan(?string $nama): ?string
    {
        return $nama ? ($this->kec[self::norm($nama)] ?? null) : null;
    }

    /** Kode desa, atau null. $catatan terisi bila cocoknya hanya mirip (salah ketik kecil). */
    public function desa(?string $kodeKec, ?string $nama, ?string &$catatan = null): ?string
    {
        $catatan = null;
        if (!$kodeKec || !$nama) return null;

        $peta = $this->desa[$kodeKec] ?? [];
        $n    = self::norm($nama);
        if (isset($peta[$n])) return $peta[$n][0];
        if (strlen($n) < 5) return null;

        $terbaik = null; $jarak = 3; $seri = false;
        foreach ($peta as $kunci => $isi) {
            $j = levenshtein($n, (string) $kunci);
            if ($j < $jarak)                     { $jarak = $j; $terbaik = $isi; $seri = false; }
            elseif ($j === $jarak && $terbaik)   { $seri = true; }
        }
        if ($terbaik && !$seri) {
            $catatan = "dicocokkan mirip dengan '{$terbaik[1]}'";
            return $terbaik[0];
        }
        return null;
    }
}