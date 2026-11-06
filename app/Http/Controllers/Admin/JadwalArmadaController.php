<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Services\PenugasanService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Jadwal mingguan per nomor polisi untuk armada Dinas LH (hanya tampilan).
 * Sumber data: rute_angkuts (armada + hari) dan lokasi per pekan di pivot rute_angkut_lokasi.
 * Jadwal diubah di menu Rute Pengangkutan.
 */
class JadwalArmadaController extends Controller
{
    public const HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    public function index(Request $request, PenugasanService $svc)
    {
        $siklus   = max(1, (int) config('jadwal.siklus', 2));
        $pekanIni = $svc->pekanKe(Carbon::today());

        $pekan = (int) $request->input('pekan', $pekanIni);
        if ($pekan < 1 || $pekan > $siklus) {
            $pekan = $pekanIni;
        }

        $armadas = Armada::dinasLh()->aktif()
            ->with([
                'sopir:id,nama_lengkap',
                'rutes' => fn ($q) => $q->select('id', 'armada_id', 'nama', 'hari', 'aktif')
                    ->where('aktif', true)
                    ->with(['lokasis' => fn ($l) => $l->select(
                        'lokasi_angkuts.id', 'lokasi_angkuts.nama', 'lokasi_angkuts.desa', 'lokasi_angkuts.jenis'
                    )]),
            ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn ($w) => $w->where('nomor_polisi', 'like', "%{$s}%")
                    ->orWhere('nama', 'like', "%{$s}%"));
            })
            ->orderBy('nomor_polisi')
            ->get();

        // $jadwal[armada_id][hari 1..7] = [ ['rute' => nama, 'lokasi' => [..]], ... ]
        $jadwal = [];
        foreach ($armadas as $a) {
            $perHari = array_fill_keys(array_keys(self::HARI), []);

            foreach ($a->rutes as $r) {
                $lokasi = $r->lokasis->filter(
                    fn ($l) => $l->pivot->pekan === null || (int) $l->pivot->pekan === $pekan
                );

                // Rute yang seluruh lokasinya hanya untuk pekan lain tidak ditampilkan di pekan ini
                if ($r->lokasis->isNotEmpty() && $lokasi->isEmpty()) {
                    continue;
                }

                $entri = [
                    'rute'   => $r->nama,
                    'lokasi' => $lokasi->map(fn ($l) => [
                        'nama'  => $l->nama,
                        'desa'  => $l->desa,
                        'pekan' => $l->pivot->pekan,
                    ])->values()->all(),
                ];

                foreach ($svc->hariList($r->hari) as $h) {
                    $perHari[$h][] = $entri;
                }
            }

            $jadwal[$a->id] = $perHari;
        }

        return view('admin.jadwal-armada.index', [
            'armadas'    => $armadas,
            'jadwal'     => $jadwal,
            'hari'       => self::HARI,
            'pekan'      => $pekan,
            'pekanIni'   => $pekanIni,
            'siklus'     => $siklus,
            'hariIni'    => Carbon::today()->dayOfWeekIso,
            'search'     => (string) $request->input('search', ''),
        ]);
    }
}