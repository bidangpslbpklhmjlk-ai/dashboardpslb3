<?php

namespace Database\Seeders;

use App\Models\Armada;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Impor rekap ritasi Januari 2026 ke gatepass_logs (data historis, berat ESTIMASI).
 * Jalankan: php artisan db:seed --class=RekapRitasiJanuariSeeder
 * Aman dijalankan ulang: armada-tanggal yang sudah diimpor dilewati.
 */
class RekapRitasiJanuariSeeder extends Seeder
{
    /** ISI DULU: estimasi ton per ritasi per jenis kendaraan (sesuai kapasitas bak). */
    private array $estimasiTon = [
        'ARM ROLL' => 2.0,
        'D TRUCK'  => 2.0,
        'APV'      => 0.3,   // E 9331 XX (pickup)
    ];

    /** Salah ketik di rekap => nomor polisi yang benar di tabel armadas. */
    private array $alias = [
        'E 8528 U' => 'E 8258 U',
        'E 8374 U' => 'E 8274 U',
    ];

    /** [tanggal, nomor polisi, kategori, jumlah ritasi, sumber sampah] */
    private array $data = [
        ['2026-01-01', 'E 8116 U', 'D TRUCK', 1, 'JL AHMAD YANI + JL SITI ARMILAH'],
        ['2026-01-01', 'E 8147 U', 'D TRUCK', 1, 'JL ABDUL HALIM + YOGYA + TAMAN JOMANTARA + GGM + CIBIUK + H JAJA'],
        ['2026-01-01', 'E 8175 U', 'ARM ROLL', 2, 'RS MITRA; PASAR CIBORELANG'],
        ['2026-01-01', 'E 8179 U', 'ARM ROLL', 2, 'PASAR RAJAGALUH; PASAR LEUWIMUNDING'],
        ['2026-01-01', 'E 8213 U', 'ARM ROLL', 2, 'TPS KUBANG; TPS KUBANG'],
        ['2026-01-01', 'E 8214 U', 'ARM ROLL', 2, 'TPS CIGASONG; GUNUNGSARI'],
        ['2026-01-01', 'E 8259 U', 'D TRUCK', 1, 'JL ABDUL HALIM'],
        ['2026-01-01', 'E 8260 U', 'ARM ROLL', 3, 'OPSIH CISAMBENG; PT LEETEX; DESA BEUSI'],
        ['2026-01-01', 'E 8261 U', 'ARM ROLL', 2, 'PASAR TALAGA; PASAR CIKIJING'],
        ['2026-01-01', 'E 8374 U', 'ARM ROLL', 2, 'REST AREA'],
        ['2026-01-01', 'E 8528 U', 'ARM ROLL', 2, 'PASAR MAJA; PERUM BCA'],
        ['2026-01-01', 'E 9331 XX', 'APV', 1, 'ASABRI'],
        ['2026-01-26', 'E 8116 U', 'D TRUCK', 2, 'JL AHMAD YANI + JL SITI ARMILAH +;  BTN MUNJUL + PANTI YAUMA'],
        ['2026-01-26', 'E 8117 U', 'D TRUCK', 2, 'KNPI CIGASONG + TONJONG; PERUM GHD'],
        ['2026-01-26', 'E 8147 U', 'D TRUCK', 1, 'JL ABDUL HALIM + YOGYA + TAMAN RAHARJA + GGM + CIBIUK + H JAJA'],
        ['2026-01-26', 'E 8150 U', 'ARM ROLL', 1, 'LYG'],
        ['2026-01-26', 'E 8175 U', 'ARM ROLL', 4, 'RS MITRA PLUMBON; PASAR PANJALIN; PASAR CIBORELANG; PABRIK PLASTIK'],
        ['2026-01-26', 'E 8179 U', 'ARM ROLL', 3, 'PASAR RAJAGALUH; RAJAGALUH LOR; SPPG'],
        ['2026-01-26', 'E 8213 U', 'ARM ROLL', 4, 'TPS KUBANG; TPS KUBANG; TPS KUBANG; TPS KUBANG'],
        ['2026-01-26', 'E 8214 U', 'ARM ROLL', 3, 'TPS CIGASONG; TPS CIGASONG; GUNUNGSARI'],
        ['2026-01-26', 'E 8259 U', 'D TRUCK', 1, 'JL ABDUL HALIM'],
        ['2026-01-26', 'E 8260 U', 'ARM ROLL', 3, 'DESA BEUSI; PT LEETEX; PT LEETEX'],
        ['2026-01-26', 'E 8261 U', 'ARM ROLL', 2, 'PASAR RAJAGALUH; PASAR CIKIJING'],
        ['2026-01-26', 'E 8374 U', 'ARM ROLL', 2, 'REST AREA; OPSIH BARIBIS'],
        ['2026-01-26', 'E 8528 U', 'ARM ROLL', 4, 'PASAR MAJA; TPS KADIPATEN; TPS KADIPATEN; PERUM BCA'],
        ['2026-01-26', 'E 9331 XX', 'APV', 2, 'CFD ; KASOKANDEL + MBG'],
        ['2026-01-27', 'E 8116 U', 'D TRUCK', 1, 'JL AHMAD YANI + JL SITI ARMILAH'],
        ['2026-01-27', 'E 8117 U', 'D TRUCK', 1, 'KNPI + CIGASONG + TONJONG'],
        ['2026-01-27', 'E 8147 U', 'D TRUCK', 2, 'JL ABDUL HALIM + YOGYA + TAMAN JOMANTARA + GGM + CIBIUK + H JAJA + MBG; PERUM ANDIR'],
        ['2026-01-27', 'E 8175 U', 'ARM ROLL', 4, 'RS MITRA PLUMBON; PASAR PANJALIN; PABRIK ROKOK; RS CIBORELANG'],
        ['2026-01-27', 'E 8179 U', 'ARM ROLL', 2, 'PASAR RAJAGALUH; LEUWIMUNDING'],
        ['2026-01-27', 'E 8213 U', 'ARM ROLL', 3, 'TPS KUBANG; TPS KUBANG; TPS KUBANG'],
        ['2026-01-27', 'E 8214 U', 'ARM ROLL', 1, 'TPS CIGASONG'],
        ['2026-01-27', 'E 8259 U', 'D TRUCK', 2, 'JL ABDUL HALIM; PERUM SAWALA'],
        ['2026-01-27', 'E 8260 U', 'ARM ROLL', 4, 'RS CIDERES; PT LEETEX; PT LEETEX; PT LEETEX'],
        ['2026-01-27', 'E 8261 U', 'ARM ROLL', 2, 'PASAR TALAGA ; PASAR CIKIJING'],
        ['2026-01-27', 'E 8374 U', 'ARM ROLL', 2, 'REST AREA'],
        ['2026-01-27', 'E 8528 U', 'ARM ROLL', 4, 'RS MAJALENGKA; TPS KADIPATEN; TPS KADIPATEN; PASAR MAJA'],
        ['2026-01-27', 'E 9331 XX', 'APV', 1, 'PERUM GRR + MBG + MBG JL SATARI'],
        ['2026-01-28', 'E 8116 U', 'D TRUCK', 1, 'JL AHMAD YANI + JL SITI ARMILAH + PERUM ALAM JAYA'],
        ['2026-01-28', 'E 8117 U', 'D TRUCK', 1, 'KNPI + CIGASONG + TONJONG'],
        ['2026-01-28', 'E 8147 U', 'D TRUCK', 1, 'JL ABDUL HALIM + YOGYA + TAMAN JOMANTARA + GGM + CIBIUKJ + H JAJA'],
        ['2026-01-28', 'E 8150 U', 'ARM ROLL', 1, 'LYG'],
        ['2026-01-28', 'E 8175 U', 'ARM ROLL', 3, 'RS MITRA PLUMBON; PABRIK PLASTIK; PASAR CIBORELANG'],
        ['2026-01-28', 'E 8179 U', 'ARM ROLL', 1, 'RAJAGALUH KIDUL'],
        ['2026-01-28', 'E 8213 U', 'ARM ROLL', 3, 'TPS KUBANG; TPS KUBANG; TPS KUBANG'],
        ['2026-01-28', 'E 8214 U', 'ARM ROLL', 2, 'PENDOPO; TPS CIGASONG'],
        ['2026-01-28', 'E 8259 U', 'D TRUCK', 1, 'JL ABDUL HALIM'],
        ['2026-01-28', 'E 8260 U', 'ARM ROLL', 3, 'OPSIH TAMAN KOTA; PT LEETEX; PT LEETEX'],
        ['2026-01-28', 'E 8261 U', 'ARM ROLL', 2, 'PASAR TALAGA ; PASAR CIKIJING'],
        ['2026-01-28', 'E 8374 U', 'ARM ROLL', 2, ''],
        ['2026-01-28', 'E 8528 U', 'ARM ROLL', 4, 'PERUM BCA; TPS KADIPATEN; TPS KADIPATEN; PASAR MAJA'],
        ['2026-01-28', 'E 9331 XX', 'APV', 1, 'ASABRI + MBG JL OLAHRAGA + MBG JALAN SATARI'],
        ['2026-01-29', 'E 8116 U', 'D TRUCK', 2, 'JL AHMAD YANI + JL SITI ARMILAH; BTN MUNJUL'],
        ['2026-01-29', 'E 8117 U', 'D TRUCK', 2, 'KNPI + CIGASONG + TONJONG; PERUM SAWALA BARU '],
        ['2026-01-29', 'E 8147 U', 'D TRUCK', 1, 'JL ABDUL HALIM + YOGYA + TAMAN RAHARJA + GGM + CIBIUK + MBG'],
        ['2026-01-29', 'E 8150 U', 'ARM ROLL', 1, 'LYG'],
        ['2026-01-29', 'E 8175 U', 'ARM ROLL', 3, 'RS MITRA PLUMBON; PS CIBORELANG; PT GLORY'],
        ['2026-01-29', 'E 8179 U', 'ARM ROLL', 2, 'PASAR RAJAGALUH; RAJAGALUH LOR'],
        ['2026-01-29', 'E 8213 U', 'ARM ROLL', 4, 'TPS KUBANG; TPS KUBANG;  TPS KUBANG; TPS KUBANG'],
        ['2026-01-29', 'E 8214 U', 'ARM ROLL', 1, 'TPS CIGASONG'],
        ['2026-01-29', 'E 8259 U', 'D TRUCK', 1, 'JL ABDUL HALIM + CIJATI'],
        ['2026-01-29', 'E 8260 U', 'ARM ROLL', 4, 'DESA BEUSI; PT LEETEX; PT LEETEX; PT LEETEX'],
        ['2026-01-29', 'E 8261 U', 'ARM ROLL', 2, 'PASAR TALAGA; PASAR CIKIJING'],
        ['2026-01-29', 'E 8528 U', 'ARM ROLL', 3, 'PASAR MAJA; TPS KADIPATEN; TPS KADIPATEN'],
        ['2026-01-29', 'E 9331 XX', 'APV', 2, 'KASOKANDEL; SPPG JL OLAHRAGA + SPPG JL SATARI'],
        ['2026-01-30', 'E 8116 U', 'D TRUCK', 1, 'JL AHMAD YANI + JL SITI ARMILAH'],
        ['2026-01-30', 'E 8117 U', 'D TRUCK', 1, 'KNPI + CIGASONG + TONJONG'],
        ['2026-01-30', 'E 8147 U', 'D TRUCK', 1, 'JL ABDUL HALIM + YOGYA + TAMAN JOMANTARA + GGM + CIBIUK + H JAJA'],
        ['2026-01-30', 'E 8150 U', 'ARM ROLL', 1, 'LYG'],
        ['2026-01-30', 'E 8175 U', 'ARM ROLL', 3, 'RS MITRA PLUMBON; PASAR PANJALIN; PABRIK PLASTIK'],
        ['2026-01-30', 'E 8179 U', 'ARM ROLL', 1, 'LOR RAJAGALUH'],
        ['2026-01-30', 'E 8213 U', 'ARM ROLL', 3, 'TPS KUBANG; TPS KUBANG; TPS KUBANG'],
        ['2026-01-30', 'E 8214 U', 'ARM ROLL', 1, 'TPS CIGASONG'],
        ['2026-01-30', 'E 8259 U', 'D TRUCK', 1, 'JL ABDUL HALIM'],
        ['2026-01-30', 'E 8260 U', 'ARM ROLL', 3, 'RS CIDERES; PT LEETEX; PT LEETEX'],
        ['2026-01-30', 'E 8261 U', 'ARM ROLL', 3, 'PASAR TALAGA; PASAR CIKIJING; RS TALAGA'],
        ['2026-01-30', 'E 8374 U', 'ARM ROLL', 1, ''],
        ['2026-01-30', 'E 8528 U', 'ARM ROLL', 4, 'RS MAJALENGKA; TPS KADIPATEN; TPS KADIPATEN; PASAR MAJA'],
        ['2026-01-30', 'E 9331 XX', 'APV', 1, 'PERKOTAAN + MBG JL OLGA + MBG JL SATARI'],
        ['2026-01-31', 'E 8116 U', 'D TRUCK', 1, 'JL AHMAD YANI + JL SITI ARMILAH + JL LASWI + VILLA SANGRAJA'],
        ['2026-01-31', 'E 8117 U', 'D TRUCK', 1, ''],
        ['2026-01-31', 'E 8147 U', 'D TRUCK', 1, ''],
        ['2026-01-31', 'E 8150 U', 'ARM ROLL', 2, 'PT GLOBAL; PT LYG'],
        ['2026-01-31', 'E 8175 U', 'ARM ROLL', 4, 'RS MITRA; SINGWELL; PASAR CIBORELANG; TAMAN RAHARJA'],
        ['2026-01-31', 'E 8179 U', 'ARM ROLL', 3, ''],
        ['2026-01-31', 'E 8213 U', 'ARM ROLL', 3, 'TPS KUBANG; TPS KUBANG; TPS KUBANG'],
        ['2026-01-31', 'E 8214 U', 'ARM ROLL', 2, 'TPS CIGASONG; SINDANGKASIH'],
        ['2026-01-31', 'E 8259 U', 'D TRUCK', 1, ''],
        ['2026-01-31', 'E 8260 U', 'ARM ROLL', 5, 'PT LEETEX ; PT LEETEX ; PT LEETEX ; PT GISTEX; PT GISTEX'],
        ['2026-01-31', 'E 8261 U', 'ARM ROLL', 2, ''],
        ['2026-01-31', 'E 8374 U', 'ARM ROLL', 1, 'PENGADILAN NEGERI + REST AREA'],
        ['2026-01-31', 'E 8528 U', 'ARM ROLL', 5, ''],
        ['2026-01-31', 'E 9331 XX', 'APV', 2, 'PERUM GRR; SPPG']
    ];

    public function run(): void
    {
        foreach ($this->estimasiTon as $k => $v) {
            if ($v === null) {
                $this->command->error("Isi dulu estimasi ton untuk '$k' di \$estimasiTon.");
                return;
            }
        }

        $norm = fn ($s) => strtoupper(preg_replace('/\s+/', '', (string) $s));
        $asli = [];
        $data = array_map(function ($r) use (&$asli) {
            $r[5] = false;
            if (isset($this->alias[$r[1]])) { $asli[$this->alias[$r[1]]] = $r[1]; $r[1] = $this->alias[$r[1]]; $r[5] = true; }
            return $r;
        }, $this->data);
        $armada = Armada::all()->keyBy(fn ($a) => $norm($a->nomor_polisi));

        $hilang = collect($data)->pluck(1)->unique()->filter(fn ($p) => ! $armada->has($norm($p)));
        if ($hilang->isNotEmpty()) {
            $this->command->error('Nomor polisi tidak ada di tabel armadas: ' . $hilang->implode(', '));
            return;
        }

        $now = now();
        $rows = [];
        foreach ($data as [$tgl, $plat, $kat, $rit, $src, $koreksi]) {
            $a = $armada->get($norm($plat));
            $ada = DB::table('gatepass_logs')->where('asal_data', 'impor_rekap')
                ->where('armada_id', $a->id)->whereDate('waktu_masuk', $tgl)->exists();
            if ($ada) { continue; }

            for ($k = 1; $k <= $rit; $k++) {
                $rows[] = [
                    'armada_id'      => $a->id,
                    'nomor_polisi'   => $a->nomor_polisi,
                    'tipe_kendaraan' => $a->jenis_kendaraan ?: $kat,
                    'jenis_muatan'   => 'Sampah',
                    'volume_ton'     => $this->estimasiTon[$kat],
                    'ritasi'         => $k,
                    'waktu_masuk'    => $tgl . ' 12:00:00',
                    'asal_data'      => 'impor_rekap',
                    'sumber_sampah'  => $src,
                    'catatan'        => 'Impor rekap historis, berat ESTIMASI per jenis kendaraan.' . ($koreksi ? ' Plat di rekap: ' . $asli[$plat] . '.' : ''),
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 500) as $c) { DB::table('gatepass_logs')->insert($c); }
        });
        $this->command->info(count($rows) . ' baris gatepass dibuat.');
    }
}