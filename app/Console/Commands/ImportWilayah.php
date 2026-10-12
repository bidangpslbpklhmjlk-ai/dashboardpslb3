<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Mengisi master kecamatan dan desa/kelurahan dari wilayah.id (kode Kemendagri).
 * Aman dijalankan ulang: data dicocokkan lewat kode dan diperbarui, tidak digandakan.
 */
class ImportWilayah extends Command
{
    protected $signature = 'wilayah:import {kode=32.10 : Kode kabupaten/kota Kemendagri (Majalengka = 32.10)}';

    protected $description = 'Impor master kecamatan dan desa/kelurahan satu kabupaten dari wilayah.id';

    private const BASE = 'https://wilayah.id/api';

    public function handle(): int
    {
        $kab = $this->argument('kode');

        $kecamatan = $this->ambil("/districts/{$kab}.json");
        if (!$kecamatan) {
            $this->error("Gagal mengambil daftar kecamatan untuk kode {$kab}. Periksa koneksi internet server dan kode kabupaten.");
            return self::FAILURE;
        }

        $rowsKec = [];
        $rowsDesa = [];

        foreach ($kecamatan as $k) {
            $rowsKec[] = ['kode' => $k['code'], 'nama' => $k['name']];

            $desa = $this->ambil("/villages/{$k['code']}.json");
            if ($desa === null) {
                $this->error("Gagal mengambil desa/kelurahan kecamatan {$k['name']}. Tidak ada data yang disimpan, coba jalankan lagi.");
                return self::FAILURE;
            }

            foreach ($desa as $d) {
                // Kode akhir berawalan 1 = kelurahan, 2 = desa
                $akhir = substr($d['code'], strrpos($d['code'], '.') + 1);
                $rowsDesa[] = [
                    'kode'           => $d['code'],
                    'kode_kecamatan' => $k['code'],
                    'nama'           => $d['name'],
                    'jenis'          => ($akhir[0] ?? '2') === '1' ? 'kelurahan' : 'desa',
                ];
            }

            $this->line("  {$k['name']}: " . count($desa) . ' desa/kelurahan');
        }

        DB::transaction(function () use ($rowsKec, $rowsDesa) {
            DB::table('wilayah_kecamatan')->upsert($rowsKec, ['kode'], ['nama']);
            foreach (array_chunk($rowsDesa, 200) as $chunk) {
                DB::table('wilayah_desa')->upsert($chunk, ['kode'], ['kode_kecamatan', 'nama', 'jenis']);
            }
        });

        $this->info('Selesai: ' . count($rowsKec) . ' kecamatan, ' . count($rowsDesa) . ' desa/kelurahan.');

        return self::SUCCESS;
    }

    private function ambil(string $path): ?array
    {
        $res = Http::timeout(30)->retry(3, 500, throw: false)->acceptJson()->get(self::BASE . $path);

        if (!$res->successful()) {
            return null;
        }

        return $res->json('data');
    }
}