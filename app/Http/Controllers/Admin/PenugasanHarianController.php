<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\LokasiAngkut;
use App\Models\RuteAngkut;
use App\Support\Label;
use Illuminate\Support\Facades\DB;

class PenugasanHarianController extends Controller
{
    /** Armada yang ditampilkan. Tambahkan nama armada lain di sini. */
    private const ARMADA = ['Kantor DLH'];

    private const NAMA_HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    private const PETA_HARI = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, "jum'at" => 5, 'sabtu' => 6, 'minggu' => 7, 'ahad' => 7];

    /** Terima "Senin", "1", "Senin, Kamis", "1,4" -> [1,4] */
    private function hariList($v): array
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

    public function index()
    {
        $armadas = Armada::whereIn('nama', self::ARMADA)->get()
            ->sortBy(fn ($a) => $a->nomor_polisi, SORT_NATURAL | SORT_FLAG_CASE)->values();

        $rute = RuteAngkut::where('aktif', true)
            ->whereIn('armada_id', $armadas->pluck('id'))
            ->orderBy('id')->get();

        // Lokasi per rute, urut sesuai kolom "urutan"
        $pivot = DB::table('rute_angkut_lokasi')
            ->whereIn('rute_angkut_id', $rute->pluck('id'))
            ->orderBy('urutan')->get()
            ->groupBy('rute_angkut_id');

        $lokasi = LokasiAngkut::whereIn('id', $pivot->flatten()->pluck('lokasi_angkut_id')->unique())
            ->get()->keyBy('id');

        $data = $armadas->map(function ($a) use ($rute, $pivot, $lokasi) {
            $hari = array_fill_keys(array_keys(self::NAMA_HARI), []);

            foreach ($rute->where('armada_id', $a->id) as $r) {
                $item = [
                    'rute'   => Label::of($r),
                    'lokasi' => ($pivot[$r->id] ?? collect())
                        ->map(fn ($p) => isset($lokasi[$p->lokasi_angkut_id]) ? Label::of($lokasi[$p->lokasi_angkut_id]) : null)
                        ->filter()->values()->all(),
                ];
                foreach ($this->hariList($r->hari) as $h) {
                    $hari[$h][] = $item;
                }
            }

            return ['nopol' => $a->nomor_polisi, 'nama' => $a->nama, 'hari' => $hari];
        });

        return view('admin.penugasan-harian.index', [
            'armada'   => $data,
            'namaHari' => self::NAMA_HARI,
        ]);
    }
}