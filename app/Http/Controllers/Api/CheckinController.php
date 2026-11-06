<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\Checkin;
use App\Models\JenisSampah;
use App\Models\LokasiAngkut;
use App\Services\CheckinService;
use App\Support\Label;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckinController extends Controller
{
    private const BATAS_JAUH_METER    = 300;
    private const BATAS_AKURASI_METER = 100;

    public function __construct(private CheckinService $svc) {}

    /** Jadwal hari ini (semua rute aktif), dikelompokkan per armada efektif. */
    public function tugas()
    {
        $jadwal = $this->svc->jadwalHari(today());
        $armada = Armada::whereIn('id', array_keys($jadwal))->get()->keyBy('id');

        $tugas = collect();
        foreach ($jadwal as $armadaId => $rutes) {
            foreach ($rutes as $ruteId => $b) {
                $muncul = [];
                $jumlah = collect($b['lokasi'])->countBy(fn ($x) => $x['lokasi']->id);

                // Lokasi yang muncul dua kali di rute (mis. pangkalan awal dan akhir):
                // kemunculan ke-N memakai check-in ke-N.
                $lokasi = collect($b['lokasi'])->map(function ($x) use (&$muncul, $jumlah) {
                    $l   = $x['lokasi'];
                    $k   = $muncul[$l->id] = ($muncul[$l->id] ?? -1) + 1;
                    $cks = $jumlah[$l->id] > 1 ? $x['checkins']->slice($k, 1)->values() : $x['checkins'];

                    return [
                        'id'        => $l->id,
                        'nama'      => Label::of($l),
                        'jenis'     => $l->jenis,
                        'alamat'    => $l->alamat,
                        'latitude'  => (float) $l->latitude,
                        'longitude' => (float) $l->longitude,
                        'checkin'   => $cks->map(fn ($c) => $this->ringkas($c))->values(),
                    ];
                })->values();

                $tugas->push([
                    'rute_id'      => (int) $ruteId,
                    'penugasan_id' => collect($b['lokasi'])->pluck('penugasan_id')->filter()->first(),
                    'armada_id'    => (int) $armadaId,
                    'rute'         => Label::of($b['rute']),
                    'nomor_polisi' => $armada[$armadaId]->nomor_polisi ?? null,
                    'nama_armada'  => $armada[$armadaId]->nama ?? null,
                    'lokasi'       => $lokasi,
                ]);
            }
        }

        return response()->json([
            'tanggal'      => today()->toDateString(),
            'tugas'        => $tugas->sortBy('nomor_polisi', SORT_NATURAL)->values(),
            'jenis_sampah' => JenisSampah::where('aktif', true)->orderBy('nama')->get()
                ->map(fn ($j) => ['id' => $j->id, 'nama' => Label::of($j)])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'client_uuid'              => 'required|uuid',
            'rute_id'                  => 'required|integer|exists:rute_angkuts,id',
            'lokasi_angkut_id'         => 'required|integer|exists:lokasi_angkuts,id',
            'waktu_kejadian'           => 'required|date',
            'latitude'                 => 'required|numeric|between:-90,90',
            'longitude'                => 'required|numeric|between:-180,180',
            'akurasi_gps'              => 'nullable|numeric|min:0',
            'sampah'                   => 'nullable|array',
            'sampah.*.jenis_sampah_id' => 'required|distinct|exists:jenis_sampah,id',
            'sampah.*.berat_kg'        => 'required|numeric|min:0.01|max:99999999',
            'foto'                     => 'nullable|array|max:5',
            'foto.*'                   => 'image|max:5120',
            'catatan'                  => 'nullable|string|max:500',
        ], [
            'latitude.required' => 'Lokasi GPS wajib. Aktifkan GPS lalu coba lagi.',
        ]);

        // Kiriman ulang (sinyal putus saat respons): kembalikan data yang sudah ada.
        if ($ada = Checkin::withTrashed()->where('client_uuid', $v['client_uuid'])->first()) {
            return response()->json(['duplikat' => true, 'data' => $this->ringkas($ada)], 200);
        }

        $lokasi = LokasiAngkut::findOrFail($v['lokasi_angkut_id']);

        $waktu = Carbon::parse($v['waktu_kejadian'])->setTimezone(config('app.timezone'));
        if ($waktu->gt(now()->addMinutes(10))) {
            throw ValidationException::withMessages(['waktu_kejadian' => 'Waktu tidak boleh di masa depan. Periksa jam HP.']);
        }
        if (! $waktu->isSameDay(today())) {
            throw ValidationException::withMessages(['waktu_kejadian' => 'Waktu check-in harus hari ini. Periksa jam HP.']);
        }

        // Aturan sama dengan web: pool tanpa sampah dan foto, pengambilan wajib foto
        $sampah = $lokasi->jenis === 'pool' ? [] : ($v['sampah'] ?? []);
        $foto   = $lokasi->jenis === 'pool' ? [] : $request->file('foto', []);
        if ($lokasi->jenis !== 'pool' && empty($sampah)) {
            throw ValidationException::withMessages(['sampah' => 'Isi jenis dan berat sampah.']);
        }
        if ($lokasi->jenis === 'pengambilan' && empty($foto)) {
            throw ValidationException::withMessages(['foto' => 'Foto wajib diunggah untuk titik pengambilan.']);
        }

        // Tandai (tidak memblokir) kalau posisi jauh dari titik atau GPS kurang akurat
        $flag  = [];
        $jarak = $this->jarakMeter((float) $v['latitude'], (float) $v['longitude'], (float) $lokasi->latitude, (float) $lokasi->longitude);
        if ($jarak > self::BATAS_JAUH_METER) {
            $flag['jauh_dari_lokasi_m'] = (int) round($jarak);
        }
        if (isset($v['akurasi_gps']) && $v['akurasi_gps'] > self::BATAS_AKURASI_METER) {
            $flag['akurasi_rendah_m'] = (int) round($v['akurasi_gps']);
        }

        try {
            $c = $this->svc->simpanDariApp(
                (int) $v['rute_id'], (int) $lokasi->id,
                [
                    'client_uuid'    => $v['client_uuid'],
                    'waktu_kejadian' => $waktu->format('Y-m-d H:i:s'),
                    'latitude'       => $v['latitude'],
                    'longitude'      => $v['longitude'],
                    'akurasi_gps'    => $v['akurasi_gps'] ?? null,
                    'sampah'         => $sampah,
                    'catatan'        => $v['catatan'] ?? null,
                    'flag'           => $flag,
                ],
                $foto, $request->user()->id, $request->user()->pegawai?->id,
            );
        } catch (UniqueConstraintViolationException) {
            $c = Checkin::withTrashed()->where('client_uuid', $v['client_uuid'])->firstOrFail();

            return response()->json(['duplikat' => true, 'data' => $this->ringkas($c)], 200);
        }

        return response()->json(['duplikat' => false, 'data' => $this->ringkas($c)], 201);
    }

    private function ringkas(Checkin $c): array
    {
        $c->loadMissing('sampah');

        return [
            'id'          => $c->id,
            'client_uuid' => $c->client_uuid,
            'waktu'       => $c->waktu_kejadian->format('H:i'),
            'ritasi_ke'   => (int) $c->ritasi_ke,
            'total_kg'    => (float) $c->sampah->sum('berat_kg'),
        ];
    }

    /** Jarak dua titik (meter), rumus haversine. */
    private function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $a = sin(deg2rad($lat2 - $lat1) / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lng2 - $lng1) / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }
}