<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\CheckinFoto;
use App\Models\CheckinSampah;
use App\Models\LokasiAngkut;
use App\Models\PenugasanHarian;
use App\Models\RuteAngkut;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckinService
{
    public function __construct(private PenugasanService $penugasan) {}

    /**
     * Jadwal satu tanggal, dikelompokkan per armada EFEKTIF (memperhitungkan pengalihan):
     * [armada_id => [rute_id => ['rute' => RuteAngkut, 'lokasi' => [['lokasi','penugasan_id','checkins'], ...]]]]
     */
    public function jadwalHari(Carbon $tgl): array
    {
        $rutes = RuteAngkut::where('aktif', true)->orderBy('id')->get()
            ->filter(fn ($r) => in_array($tgl->dayOfWeekIso, $this->penugasan->hariList($r->hari), true));
        if ($rutes->isEmpty()) {
            return [];
        }

        $rows = PenugasanHarian::with([
                'lokasi',
                'checkins' => fn ($q) => $q->with(['sampah.jenisSampah', 'fotos', 'pencatat'])->orderBy('waktu_kejadian'),
            ])
            ->whereDate('tanggal', $tgl->toDateString())->where('status', '!=', 'batal')
            ->whereIn('rute_angkut_id', $rutes->pluck('id'))->get()
            ->groupBy('rute_angkut_id');

        $pivot = DB::table('rute_angkut_lokasi')->whereIn('rute_angkut_id', $rutes->pluck('id'))
            ->orderBy('urutan')->get()->groupBy('rute_angkut_id');
        $lokasi = LokasiAngkut::whereIn('id', $pivot->flatten()->pluck('lokasi_angkut_id')->unique())->get()->keyBy('id');

        $out = [];
        foreach ($rutes as $r) {
            foreach ($pivot->get($r->id, collect()) as $p) {
                $lid = (int) $p->lokasi_angkut_id;
                if (! isset($lokasi[$lid])) {
                    continue;
                }
                $row = $this->cariPenugasan($rows->get($r->id, collect()), $lid);
                $armadaId = $row?->armada_id ?? $r->armada_id;

                $out[$armadaId][$r->id]['rute'] ??= $r;
                $out[$armadaId][$r->id]['lokasi'][] = [
                    'lokasi'       => $lokasi[$lid],
                    'penugasan_id' => $row?->id,
                    'checkins'     => $row ? $row->checkins->where('lokasi_angkut_id', $lid)->values() : collect(),
                ];
            }
        }
        return $out;
    }

    public function simpan(array $d, array $foto, int $userId): Checkin
    {
        return DB::transaction(function () use ($d, $foto, $userId) {
            $rute = RuteAngkut::lockForUpdate()->findOrFail($d['rute_id']);
            $tgl  = Carbon::parse($d['tanggal']);
            $lid  = (int) $d['lokasi_angkut_id'];

            if (! in_array($tgl->dayOfWeekIso, $this->penugasan->hariList($rute->hari), true)) {
                throw ValidationException::withMessages(['rute_id' => 'Rute ini tidak dijadwalkan pada tanggal tersebut.']);
            }
            if (! in_array($lid, $this->penugasan->lokasiRute($rute->id) ?? [], true)) {
                throw ValidationException::withMessages(['lokasi_angkut_id' => 'Lokasi bukan bagian dari rute ini.']);
            }
            if (Carbon::parse($d['waktu_kejadian'])->toDateString() !== $tgl->toDateString()) {
                throw ValidationException::withMessages(['waktu_kejadian' => 'Waktu check-in harus berada di tanggal yang dipilih.']);
            }

            $rows = PenugasanHarian::with('lokasi')->whereDate('tanggal', $tgl->toDateString())
                ->where('rute_angkut_id', $rute->id)->where('status', '!=', 'batal')->get();
            $row = $this->cariPenugasan($rows, $lid) ?? PenugasanHarian::create([
                'tanggal'        => $tgl->toDateString(),
                'rute_angkut_id' => $rute->id,
                'armada_id'      => $rute->armada_id,
                'pegawai_id'     => null,
                'status'         => 'direncanakan',
                'sumber'         => 'manual',
            ]);



            $c = Checkin::create([
                'penugasan_id'     => $row->id,
                'ritasi_ke'        => 1,
                'lokasi_angkut_id' => $lid,
                'waktu_kejadian'   => $d['waktu_kejadian'],
                'sumber'           => 'admin',
                'dicatat_oleh'     => $userId,
                'client_uuid'      => (string) Str::uuid(),
                'catatan'          => $d['catatan'] ?? null,
            ]);

            $this->isiSampah($c, $d['sampah']);
            $this->simpanFoto($c, $foto);

            if ($row->status === 'direncanakan') {
                $row->update(['status' => 'berjalan']);
            }
            $this->hitungUlangRitasi((int) $row->armada_id, $tgl->toDateString());
            return $c->fresh();
        });
    }

    public function ubah(Checkin $c, array $d, array $foto, array $hapusFoto, int $userId): Checkin
    {
        return DB::transaction(function () use ($c, $d, $foto, $hapusFoto, $userId) {
            $c = Checkin::lockForUpdate()->findOrFail($c->id);
            $tgl = $c->penugasan->tanggal;

            if (Carbon::parse($d['waktu_kejadian'])->toDateString() !== $tgl->toDateString()) {
                throw ValidationException::withMessages(['waktu_kejadian' => 'Waktu check-in harus berada di tanggal penugasan (' . $tgl->format('d/m/Y') . ').']);
            }
            

            $flag = $c->flag ?? [];
            $flag['direvisi_admin'] = $userId;

            $c->update([
                'waktu_kejadian' => $d['waktu_kejadian'],
                'catatan'        => $d['catatan'] ?? null,
                'alasan_manual'  => $d['alasan_manual'],
                'flag'           => $flag,
            ]);

            $this->isiSampah($c, $d['sampah']);

            if ($hapusFoto) {
                CheckinFoto::where('checkin_id', $c->id)->whereIn('id', $hapusFoto)->get()->each(function ($f) {
                    Storage::disk('public')->delete($f->path);
                    $f->delete();
                });
            }
            $this->simpanFoto($c, $foto);

            $this->hitungUlangRitasi((int) $c->penugasan->armada_id, $tgl->toDateString());
            return $c->fresh();
        });
    }

    /** Soft delete: riwayat tetap ada di database. */
    public function hapus(Checkin $c): void
    {
        $penugasan = $c->penugasan;
        $c->delete();
        $this->hitungUlangRitasi((int) $penugasan->armada_id, $penugasan->tanggal->toDateString());
    }

    // ---------------------------------------------------------------------
    private function cariPenugasan(Collection $rows, int $lokasiId): ?PenugasanHarian
    {
        return $rows->first(fn ($p) => $p->lokasi->pluck('id')->contains($lokasiId))
            ?? $rows->first(fn ($p) => $p->lokasi->isEmpty());
    }

    private function pastikanUnik(int $penugasanId, int $lokasiId, int $ritasi, ?int $kecualiId): void
    {
        $ada = Checkin::where('penugasan_id', $penugasanId)->where('lokasi_angkut_id', $lokasiId)
            ->where('ritasi_ke', $ritasi)->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))->exists();
        if ($ada) {
            throw ValidationException::withMessages(['ritasi_ke' => "Lokasi ini sudah punya check-in untuk ritasi ke-$ritasi."]);
        }
    }

    private function isiSampah(Checkin $c, array $sampah): void
    {
        $c->sampah()->delete();
        foreach ($sampah as $s) {
            CheckinSampah::create([
                'checkin_id'      => $c->id,
                'jenis_sampah_id' => $s['jenis_sampah_id'],
                'berat_kg'        => $s['berat_kg'],
            ]);
        }
    }

    private function simpanFoto(Checkin $c, array $files): void
    {
        foreach ($files as $f) {
            CheckinFoto::create([
                'checkin_id' => $c->id,
                'path'       => $f->store('checkin/' . now()->format('Y/m'), 'public'),
            ]);
        }
    }
        /** Hitung ulang ritasi_ke semua check-in satu armada pada satu tanggal. */
    public function hitungUlangRitasi(int $armadaId, string $tanggal): void
    {
        $items = Checkin::query()
            ->join('penugasan_harian as p', 'p.id', '=', 'checkins.penugasan_id')
            ->join('lokasi_angkuts as l', 'l.id', '=', 'checkins.lokasi_angkut_id')
            ->where('p.armada_id', $armadaId)
            ->whereDate('p.tanggal', $tanggal)
            ->where('p.status', '!=', 'batal')
            ->orderBy('checkins.waktu_kejadian')
            ->orderBy('checkins.id')
            ->get(['checkins.id', 'checkins.ritasi_ke', 'l.jenis']);

        $ritasi = 1;
        foreach ($items as $it) {
            if ((int) $it->ritasi_ke !== $ritasi) {
                Checkin::whereKey($it->id)->update(['ritasi_ke' => $ritasi]);
            }
            if ($it->jenis === 'tpa') {
                $ritasi++;
            }
        }
    }
}