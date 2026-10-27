<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\GatepassLog;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class GatepassController extends Controller
{
    /** Data master untuk di-cache di HP (dipakai saat offline). */
    public function master()
    {
        $hariIni = GatepassLog::whereDate('waktu_masuk', today())
            ->selectRaw('nomor_polisi, COUNT(*) as jumlah')
            ->groupBy('nomor_polisi')
            ->pluck('jumlah', 'nomor_polisi');

        $armada = Armada::with('kategori')->aktif()
            ->whereNotNull('nomor_polisi')->where('nomor_polisi', '!=', '')
            ->orderBy('nomor_polisi')->get()
            ->map(function (Armada $a) use ($hariIni) {
                $gratis = $a->isGratis();
                $kat    = $a->kategori;

                return [
                    'id'              => $a->id,
                    'nomor_polisi'    => $a->nomor_polisi,
                    'nama'            => $a->nama,
                    'jenis_kendaraan' => $a->jenis_kendaraan,
                    'tipe_label'      => $a->tipe_label,
                    'gratis'          => $gratis,
                    'tarif'           => ($gratis || ! $kat) ? null : (float) $kat->tarif,
                    'satuan'          => ($gratis || ! $kat) ? null : $kat->satuan_label,
                    'ritasi_hari_ini' => (int) ($hariIni[$a->nomor_polisi] ?? 0),
                ];
            })->values();

        $jenis = DB::table('jenis_sampah')->where('aktif', true)->orderBy('nama')->pluck('nama');
        if ($jenis->isEmpty()) {
            $jenis = GatepassLog::whereNotNull('jenis_muatan')->distinct()
                ->orderBy('jenis_muatan')->pluck('jenis_muatan');
        }

        return response()->json([
            'tanggal'      => today()->toDateString(),
            'armada'       => $armada,
            'jenis_sampah' => $jenis->values(),
        ]);
    }

    /** Riwayat satu hari (default hari ini), lengkap dengan ritasi_ke. */
    public function index(Request $request)
    {
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->tanggal) : today();

        $logs = GatepassLog::whereDate('waktu_masuk', $tanggal->toDateString())
            ->orderBy('waktu_masuk')->get();

        $urut = [];
        $data = $logs->map(function (GatepassLog $l) use (&$urut) {
            $urut[$l->nomor_polisi] = ($urut[$l->nomor_polisi] ?? 0) + 1;

            return $this->resource($l, $urut[$l->nomor_polisi]);
        })->reverse()->values();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'client_uuid'  => 'required|uuid',
            'armada_id'    => 'required|integer',
            'jenis_muatan' => 'required|string|max:50',
            'volume_ton'   => 'required|numeric|min:0|max:999.99',
            'waktu_masuk'  => 'required|date',
            'catatan'      => 'nullable|string|max:500',
            'photo'        => 'nullable|image|max:2048',
        ]);

        // Kiriman ulang (sinyal putus saat respons) -> kembalikan data yang sudah ada.
        if ($ada = GatepassLog::where('client_uuid', $v['client_uuid'])->first()) {
            return $this->balas($ada, true, 200);
        }

        $armada = Armada::find($v['armada_id']);
        if (! $armada || blank($armada->nomor_polisi) || ! $armada->aktif) {
            throw ValidationException::withMessages([
                'armada_id' => 'Armada tidak aktif atau belum punya nomor polisi.',
            ]);
        }

        $waktu = Carbon::parse($v['waktu_masuk'])->setTimezone(config('app.timezone'));
        if ($waktu->gt(now()->addMinutes(10))) {
            throw ValidationException::withMessages([
                'waktu_masuk' => 'Waktu masuk tidak boleh di masa depan. Periksa jam HP.',
            ]);
        }

        $data = [
            'client_uuid'    => $v['client_uuid'],
            'armada_id'      => $armada->id,
            'nomor_polisi'   => $armada->nomor_polisi,
            'tipe_kendaraan' => $armada->jenis_kendaraan ?: 'Lainnya',
            'jenis_muatan'   => $v['jenis_muatan'],
            'volume_ton'     => $v['volume_ton'],
            'waktu_masuk'    => $waktu,
            'catatan'        => $v['catatan'] ?? null,
            'ritasi'         => 1,          // sama dengan web; ritasi ke-N dihitung dari jumlah baris
            'asal_data'      => 'aplikasi',
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('gatepass', 'public');
        }

        try {
            $log = GatepassLog::create($data); // Observer membuat invoice jika armada berbayar
        } catch (UniqueConstraintViolationException) {
            $log = $this->cariLog($uuid);

            return $this->balas($log, true, 200);
        }

        return $this->balas($log, false, 201);
    }

    private function balas(GatepassLog $log, bool $duplikat, int $status)
    {
        $ritasiKe = GatepassLog::where('nomor_polisi', $log->nomor_polisi)
            ->whereDate('waktu_masuk', $log->waktu_masuk->toDateString())
            ->where('waktu_masuk', '<=', $log->waktu_masuk)
            ->count();

        return response()->json([
            'duplikat' => $duplikat,
            'data'     => $this->resource($log, $ritasiKe),
        ], $status);
    }

    private function resource(GatepassLog $l, int $ritasiKe): array
    {
        return [
            'id'             => $l->id,
            'client_uuid'    => $l->client_uuid,
            'nomor_polisi'   => $l->nomor_polisi,
            'jenis_muatan'   => $l->jenis_muatan,
            'volume_ton'     => (float) $l->volume_ton,
            'waktu_masuk'    => $l->waktu_masuk->toIso8601String(),
            'ritasi_ke'      => $ritasiKe,
            'invoice_dibuat' => $l->hasInvoice(),
            'photo_url'      => $l->photo ? asset('storage/' . $l->photo) : null,
        ];
    }
    public function update(Request $request, string $uuid)
{
    $log = GatepassLog::where('client_uuid', $uuid)->firstOrFail();

    $v = $request->validate([
        'jenis_muatan' => 'required|string|max:50',
        'volume_ton'   => 'required|numeric|min:0|max:999.99',
    ]);

    $log->update($v);

    return $this->balas($log->fresh(), false, 200);
}

public function destroy(string $uuid)
{
    // Hanya data yang dibuat lewat aplikasi (punya client_uuid).
    $log = $this->cariLog($uuid);

    if ($log->invoice) {
        $log->invoice->delete();
    }
    if ($log->photo) {
        Storage::disk('public')->delete($log->photo);
    }
    $log->delete();

    return response()->json(['message' => 'OK']);
}
/** Riwayat N hari terakhir (semua sumber: web, impor, aplikasi), dipecah per halaman. */
/** Riwayat bertahap: data dengan id > setelah_id, maksimal 500 baris per panggilan. */
public function riwayat(Request $request)
{
    $setelah = max((int) $request->input('setelah_id', 0), 0);
    $batas   = 500;

    // ritasi ke-N per nopol per tanggal dihitung di database, jadi tetap benar walau hanya sebagian data yang diambil
    $sub = GatepassLog::query()->selectRaw(
        'id, client_uuid, armada_id, nomor_polisi, jenis_muatan, volume_ton, waktu_masuk,
         ROW_NUMBER() OVER (PARTITION BY nomor_polisi, DATE(waktu_masuk) ORDER BY waktu_masuk, id) AS ritasi_ke'
    );

    $rows = DB::query()->fromSub($sub, 't')
        ->where('id', '>', $setelah)
        ->orderBy('id')
        ->limit($batas)
        ->get()
        ->map(fn ($r) => [
            'id'           => (int) $r->id,
            'client_uuid'  => $r->client_uuid,
            'armada_id'    => $r->armada_id,
            'nomor_polisi' => $r->nomor_polisi,
            'jenis_muatan' => $r->jenis_muatan,
            'volume_ton'   => $r->volume_ton !== null ? (float) $r->volume_ton : null,
            'waktu_masuk'  => Carbon::parse($r->waktu_masuk, config('app.timezone'))->toIso8601String(),
            'ritasi_ke'    => (int) $r->ritasi_ke,
        ]);

    return response()->json([
        'data'    => $rows->values(),
        'ada_lagi' => $rows->count() === $batas,
    ]);
}
private function cariLog(string $uuid): GatepassLog
{
    return str_starts_with($uuid, 'srv-')
        ? GatepassLog::findOrFail((int) substr($uuid, 4))
        : GatepassLog::where('client_uuid', $uuid)->firstOrFail();
}
}