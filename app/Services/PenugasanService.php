<?php

namespace App\Services;

use App\Exceptions\JadwalBentrokException;
use App\Models\Armada;
use App\Models\ArmadaKerusakan;
use App\Models\Pegawai;
use App\Models\PenugasanHarian;
use App\Models\RuteAngkut;
use App\Support\Label;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Jadwal dasar = rute_angkuts (armada + hari). Service ini hanya mengurus:
 *  - konflikUntuk()/assertTidakBentrok() : deteksi tabrakan (per hari, rute tidak punya jam)
 *  - terdampak()/alihkan()/kembalikan()  : armada rusak -> pengalihan sementara (baris penugasan_harian)
 */
class PenugasanService
{
    /** Berapa hari ke depan yang diperiksa. */
    public const HORIZON_HARI = 30;

    private const PETA_HARI = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, "jum'at" => 5, 'sabtu' => 6, 'minggu' => 7, 'ahad' => 7];

    /** "Senin", "1", "Senin, Kamis", "1,4" -> [1,4] */
    public function hariList($v): array
    {
        $teks = strtolower((string) (is_array($v) ? implode(',', $v) : $v));
        $hasil = [];
        foreach (preg_split('/[^a-z0-9\']+/', $teks, -1, PREG_SPLIT_NO_EMPTY) as $t) {
            if (ctype_digit($t) && $t >= 1 && $t <= 7) {
                $hasil[] = (int) $t;
            } elseif (isset(self::PETA_HARI[$t])) {
                $hasil[] = self::PETA_HARI[$t];
            }
        }
        return array_values(array_unique($hasil));
    }

    private function ruteJatuhPada(RuteAngkut $r, Carbon $d): bool
    {
        return in_array($d->dayOfWeekIso, $this->hariList($r->hari), true);
    }

    // =====================================================================
    // KUNCI & DETEKSI BENTROK
    // =====================================================================
    /** Kunci baris master (urutan tetap -> bebas deadlock) agar dua admin tidak bisa lolos bersamaan. */
    public function kunci(array $armadaIds, array $pegawaiIds, array $ruteIds): void
    {
        foreach ([[Armada::class, $armadaIds], [Pegawai::class, $pegawaiIds], [RuteAngkut::class, $ruteIds]] as [$cls, $ids]) {
            $ids = array_values(array_unique(array_filter($ids)));
            sort($ids);
            if ($ids) {
                $cls::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get(['id']);
            }
        }
    }

    public function konflikUntuk(iterable $touched): array
    {
        $cek = collect($touched)->where('status', '!=', 'batal')->values();
        if ($cek->isEmpty()) {
            return [];
        }

        $tgl = $cek->map(fn ($r) => $r->tanggal->toDateString())->unique()->values()->all();

        $semua = PenugasanHarian::with('lokasi')->whereIn('tanggal', $tgl)->where('status', '!=', 'batal')->get();
        $rusak = ArmadaKerusakan::efektif()->get();
        $rutes = RuteAngkut::where('aktif', true)->get();
        $armada = Armada::all()->keyBy('id');
        $ruteAll = RuteAngkut::all()->keyBy('id');
        $pegawai = Pegawai::all()->keyBy('id');

        // Beban per tanggal: baris penugasan + rute dasar yang belum punya baris
        $beban = [];
        foreach ($tgl as $t) {
            $d = Carbon::parse($t);
            $beban[$t] = [];
            foreach ($semua->filter(fn ($r) => $r->tanggal->toDateString() === $t) as $r) {
                $beban[$t][] = [
                    'row' => $r->id, 'rute' => $r->rute_angkut_id, 'armada' => $r->armada_id,
                    'pegawai' => $r->pegawai_id, 'lokasi' => $r->lokasi->pluck('id')->all(),
                ];
            }
            $adaRute = $semua->filter(fn ($r) => $r->tanggal->toDateString() === $t)->pluck('rute_angkut_id')->all();
            foreach ($rutes as $r) {
                if (! $this->ruteJatuhPada($r, $d) || in_array($r->id, $adaRute, true)) {
                    continue;
                }
                $beban[$t][] = ['row' => null, 'rute' => $r->id, 'armada' => $r->armada_id, 'pegawai' => null, 'lokasi' => []];
            }
        }

        $nA = fn ($id) => isset($armada[$id]) ? Label::of($armada[$id]) : "#$id";
        $nR = fn ($id) => isset($ruteAll[$id]) ? Label::of($ruteAll[$id]) : "#$id";
        $nP = fn ($id) => isset($pegawai[$id]) ? Label::of($pegawai[$id]) : "#$id";

        $agg = [];
        $add = function (string $jenis, string $subjek, Carbon $tgl, array $ids, string $pesan) use (&$agg) {
            $ids = array_values(array_unique($ids));
            sort($ids);
            $k = $jenis . '|' . $subjek . '|' . implode('-', $ids);
            $agg[$k] ??= ['jenis' => $jenis, 'pesan' => $pesan, 'ids' => $ids, 'tanggal_list' => []];
            $agg[$k]['tanggal_list'][] = $tgl->toDateString();
        };

        foreach ($cek as $a) {
            $t = $a->tanggal->toDateString();

            foreach ($rusak as $k) {
                if ($k->armada_id == $a->armada_id && $k->mencakup($a->tanggal)) {
                    $add('ARMADA_RUSAK', (string) $a->armada_id, $a->tanggal, [$a->id],
                        'Armada ' . $nA($a->armada_id) . ' sedang rusak/perbaikan tetapi dijadwalkan di rute ' . $nR($a->rute_angkut_id) . '.');
                }
            }

            foreach ($beban[$t] as $b) {
                if ($b['row'] === $a->id) {
                    continue;
                }
                $ids = array_filter([$a->id, $b['row']]);

                if ($b['armada'] == $a->armada_id && $b['rute'] != $a->rute_angkut_id) {
                    $add('ARMADA_GANDA', (string) $a->armada_id, $a->tanggal, $ids,
                        'Armada ' . $nA($a->armada_id) . ' sudah memegang rute ' . $nR($b['rute']) . ' di hari yang sama dengan rute ' . $nR($a->rute_angkut_id) . '.');
                }
                if ($b['rute'] == $a->rute_angkut_id && $b['armada'] != $a->armada_id
                    && $this->lokasiBeririsan($a->lokasi->pluck('id')->all(), $b['lokasi'])) {
                    $add('RUTE_GANDA', (string) $a->rute_angkut_id, $a->tanggal, $ids,
                        'Rute ' . $nR($a->rute_angkut_id) . ' dipegang dua armada (' . $nA($a->armada_id) . ' & ' . $nA($b['armada']) . ').');
                }
                if ($a->pegawai_id && $b['pegawai'] == $a->pegawai_id && $b['armada'] != $a->armada_id) {
                    $add('DRIVER_GANDA', (string) $a->pegawai_id, $a->tanggal, $ids,
                        'Pegawai ' . $nP($a->pegawai_id) . ' ditugaskan di dua armada (' . $nA($a->armada_id) . ' & ' . $nA($b['armada']) . ') pada hari yang sama.');
                }
            }
        }

        return collect($agg)->map(function ($k) {
            $t = collect($k['tanggal_list'])->unique()->sort()->values()->all();
            $k['tanggal_list'] = $t;
            $k['pesan'] .= ' Tanggal: ' . implode(', ', array_slice($t, 0, 3)) . (count($t) > 3 ? ' (+' . (count($t) - 3) . ' lainnya)' : '') . '.';
            return $k;
        })->values()->all();
    }

    /** Tanpa pembatasan lokasi = seluruh rute. */
    private function lokasiBeririsan(array $x, array $y): bool
    {
        return ! $x || ! $y || count(array_intersect($x, $y)) > 0;
    }

    public function assertTidakBentrok(iterable $touched): void
    {
        $konflik = $this->konflikUntuk(collect($touched));
        if ($konflik) {
            throw new JadwalBentrokException($konflik);
        }
    }

    // =====================================================================
    // ARMADA RUSAK -> TERDAMPAK -> ALIHKAN -> KEMBALIKAN
    // =====================================================================
    /** Rute milik armada rusak yang jatuh dalam jendela kerusakan (dan belum dialihkan). */
    public function terdampak(ArmadaKerusakan $k): Collection
    {
        $from = Carbon::today()->greaterThan($k->tanggal_mulai) ? Carbon::today() : $k->tanggal_mulai->copy();
        $max  = $from->copy()->addDays(self::HORIZON_HARI);
        $to   = ($k->tanggal_selesai && $k->tanggal_selesai->lessThan($max)) ? $k->tanggal_selesai->copy() : $max;
        if ($k->status === 'batal' || $to->lessThan($from)) {
            return collect();
        }

        $rutes = RuteAngkut::where('aktif', true)->where('armada_id', $k->armada_id)->get();
        $rows = PenugasanHarian::whereIn('rute_angkut_id', $rutes->pluck('id'))
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->where('status', '!=', 'batal')->get()
            ->keyBy(fn ($r) => $r->rute_angkut_id . '|' . $r->tanggal->toDateString());

        $items = collect();
        $item = fn ($ruteId, $tgl, $m, $s, $pid) => [
            'key' => 'm' . $ruteId, 'rencana_id' => null, 'rute_id' => $ruteId, 'tanggal' => $tgl,
            'mulai' => substr($m ?? '', 0, 5), 'selesai' => substr($s ?? '', 0, 5), 'penugasan_id' => $pid,
        ];

        foreach ($rutes as $r) {
            for ($d = $from->copy(); $d->lessThanOrEqualTo($to); $d->addDay()) {
                if (! $this->ruteJatuhPada($r, $d)) {
                    continue;
                }
                $row = $rows->get($r->id . '|' . $d->toDateString());
                if ($row === null) {
                    $items->push($item($r->id, $d->toDateString(), null, null, null));
                } elseif ($row->armada_id == $k->armada_id && $row->status === 'direncanakan') {
                    $items->push($item($r->id, $d->toDateString(), $row->jam_mulai, $row->jam_selesai, $row->id));
                }
                // baris sudah ada di armada lain = sudah dialihkan -> tidak ditampilkan
            }
        }

        return $items->sortBy('tanggal')->values();
    }

    /**
     * @param array $targets rute  : [['armada'=>id,'pegawai'=>?id]]
     *                       lokasi: [['armada'=>id,'pegawai'=>?id,'lokasi'=>[ids]], ...]  (harus mencakup SEMUA lokasi rute)
     */
    public function alihkan(ArmadaKerusakan $k, Collection $items, string $mode, array $targets, Carbon $sampai, ?string $alasan): Collection
    {
        $rows = PenugasanHarian::whereIn('id', $items->pluck('penugasan_id')->filter()->all())->get();
        $ruteMap = RuteAngkut::whereIn('id', $items->pluck('rute_id')->all())->get()->keyBy('id');

        $this->kunci(
            array_merge([$k->armada_id], array_column($targets, 'armada')),
            array_merge(array_filter(array_column($targets, 'pegawai')), $rows->pluck('pegawai_id')->filter()->all()),
            $items->pluck('rute_id')->all()
        );

        $touched = collect();
        foreach ($items as $it) {
            $row = $it['penugasan_id']
                ? PenugasanHarian::lockForUpdate()->findOrFail($it['penugasan_id'])
                : $this->buatDariRute($ruteMap[$it['rute_id']], Carbon::parse($it['tanggal']));

            if ($mode === 'lokasi' && count($targets) > 1) {
                foreach ($this->pecah($row, $targets, $k, $sampai, $alasan) as $r) {
                    $touched->push($r);
                }
            } else {
                $touched->push($this->terapkan($row, $targets[0], $k, $sampai, $alasan));
            }
        }

        $this->assertTidakBentrok($touched);
        return $touched;
    }

    private function buatDariRute(RuteAngkut $r, Carbon $d): PenugasanHarian
    {
        return PenugasanHarian::create([
            'tanggal'        => $d->toDateString(),
            'rute_angkut_id' => $r->id,
            'armada_id'      => $r->armada_id,
            'pegawai_id'     => null,
            'status'         => 'direncanakan',
            'sumber'         => 'alih',
        ]);
    }

    private function terapkan(PenugasanHarian $row, array $t, ArmadaKerusakan $k, Carbon $sampai, ?string $alasan): PenugasanHarian
    {
        $row->fill([
            'armada_asal_id' => $row->armada_asal_id ?? $row->armada_id,
            'armada_id'      => $t['armada'],
            'pegawai_id'     => $t['pegawai'] ?? $row->pegawai_id,
            'dialihkan'      => true,
            'alasan_alih'    => $alasan ?: 'Armada rusak',
            'kerusakan_id'   => $k->id,
            'alih_sampai'    => $sampai->toDateString(),
        ])->save();
        return $row;
    }

    private function pecah(PenugasanHarian $row, array $targets, ArmadaKerusakan $k, Carbon $sampai, ?string $alasan): array
    {
        $row->loadMissing('lokasi');
        $universe = $row->lokasi->pluck('id')->map(fn ($v) => (int) $v)->all() ?: ($this->lokasiRute($row->rute_angkut_id) ?? []);
        $semuaAlokasi = collect($targets)->flatMap(fn ($t) => $t['lokasi'])->map(fn ($v) => (int) $v)->unique()->all();

        if (! $universe || array_diff($universe, $semuaAlokasi)) {
            throw ValidationException::withMessages([
                'alokasi' => 'Semua lokasi pada rute harus dialokasikan ke armada pengganti (armada rusak tidak boleh menyisakan lokasi).',
            ]);
        }

        $asal = $row->replicate();      // snapshot kondisi awal
        $out = [];
        $pertama = true;
        foreach ($targets as $t) {
            $lok = array_values(array_intersect(array_map('intval', $t['lokasi']), $universe));
            if (! $lok) {
                continue;
            }

            if ($pertama) {
                $this->terapkan($row, $t, $k, $sampai, $alasan);
                $row->lokasi()->sync($lok);
                $out[] = $row;
                $pertama = false;
            } else {
                $baru = $asal->replicate();
                $baru->fill([
                    'armada_id'      => $t['armada'],
                    'pegawai_id'     => $t['pegawai'] ?? $asal->pegawai_id,
                    'armada_asal_id' => $asal->armada_asal_id ?? $asal->armada_id,
                    'sumber'         => 'alih',
                    'status'         => 'direncanakan',
                    'dialihkan'      => true,
                    'alasan_alih'    => $alasan ?: 'Armada rusak',
                    'kerusakan_id'   => $k->id,
                    'alih_sampai'    => $sampai->toDateString(),
                ])->save();
                $baru->lokasi()->sync($lok);
                $out[] = $baru;
            }
        }
        return $out;
    }

    /**
     * Batalkan pengalihan. Baris override dibuang (rute kembali ke armada di rute_angkuts);
     * bila sudah punya check-in, barisnya dipertahankan dan hanya armadanya dikembalikan.
     */
    public function kembalikan(Collection $rows): Collection
    {
        $pulih = collect();
        foreach ($rows as $r) {
            $adaCheckin = $r->checkins()->withTrashed()->exists();

            if ($r->sumber === 'alih' && ! $adaCheckin) {
                $r->lokasi()->detach();
                $r->delete();
                continue;
            }
            if ($r->sumber === 'alih' && $adaCheckin && ! $r->armada_asal_id) {
                $r->update(['status' => 'batal']);
                continue;
            }

            $r->lokasi()->detach();
            $r->update([
                'armada_id'      => $r->armada_asal_id ?? $r->armada_id,
                'armada_asal_id' => null,
                'dialihkan'      => false,
                'alasan_alih'    => null,
                'kerusakan_id'   => null,
                'alih_sampai'    => null,
            ]);
            $pulih->push($r);
        }
        return $pulih;
    }

    /** Daftar id lokasi milik sebuah rute (tabel pivot rute_angkut_lokasi). */
    public function lokasiRute(int $ruteId): ?array
    {
        return DB::table('rute_angkut_lokasi')->where('rute_angkut_id', $ruteId)
            ->orderBy('urutan')->pluck('lokasi_angkut_id')->map(fn ($v) => (int) $v)->all();
    }
}