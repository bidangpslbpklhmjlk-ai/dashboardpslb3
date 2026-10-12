<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\Checkin;
use App\Models\JenisSampah;
use App\Services\CheckinService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function __construct(private CheckinService $svc) {}

    public function index(Request $request)
    {
        $tgl = Carbon::parse($request->query('tanggal', today()))->startOfDay();
        $jadwal = $this->svc->jadwalHari($tgl);

        $armadaList = Armada::whereIn('id', array_keys($jadwal))->get();
        $armadaId = (int) $request->query('armada');
        if (! isset($jadwal[$armadaId])) {
            $armadaId = (int) ($armadaList->first()->id ?? 0);
        }

        return view('admin.checkin.index', [
            'tanggal'    => $tgl,
            'armadaList' => $armadaList,
            'armadaId'   => $armadaId,
            'blok'       => $jadwal[$armadaId] ?? [],
            'jenisList'  => JenisSampah::all(),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'tanggal'                  => ['required', 'date', 'before_or_equal:today'],
            'rute_id'                  => ['required', 'integer', 'exists:rute_angkuts,id'],
            'lokasi_angkut_id'         => ['required', 'integer', 'exists:lokasi_angkuts,id'],
            'waktu_kejadian'           => ['required', 'date', 'before_or_equal:now'],
            'ritasi_ke'                => ['required', 'integer', 'min:1', 'max:20'],
            'sampah'                   => ['required', 'array', 'min:1'],
            'sampah.*.jenis_sampah_id' => ['required', 'distinct', 'exists:jenis_sampah,id'],
            'sampah.*.berat_kg'        => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'foto'                     => ['nullable', 'array', 'max:5'],   // ganti 'nullable' -> 'required' bila foto wajib
            'foto.*'                   => ['image', 'max:5120'],
            'catatan'                  => ['nullable', 'string', 'max:500'],
        ]);

        $this->svc->simpan($d, $request->file('foto', []), $request->user()->id);

        return $this->kembali($request, 'Check-in tersimpan.');
    }

    public function update(Request $request, Checkin $checkin)
    {
        $d = $request->validate([
            'waktu_kejadian'           => ['required', 'date', 'before_or_equal:now'],
            'ritasi_ke'                => ['required', 'integer', 'min:1', 'max:20'],
            'sampah'                   => ['required', 'array', 'min:1'],
            'sampah.*.jenis_sampah_id' => ['required', 'distinct', 'exists:jenis_sampah,id'],
            'sampah.*.berat_kg'        => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'foto'                     => ['nullable', 'array', 'max:5'],
            'foto.*'                   => ['image', 'max:5120'],
            'hapus_foto'               => ['nullable', 'array'],
            'hapus_foto.*'             => ['integer'],
            'catatan'                  => ['nullable', 'string', 'max:500'],
            'alasan_manual'            => ['required', 'string', 'max:255'],
        ]);

        $this->svc->ubah($checkin, $d, $request->file('foto', []), $d['hapus_foto'] ?? [], $request->user()->id);

        return $this->kembali($request, 'Check-in direvisi.');
    }

    public function destroy(Request $request, Checkin $checkin)
    {
        $this->svc->hapus($checkin);
        return $this->kembali($request, 'Check-in dihapus.');
    }

    private function kembali(Request $request, string $pesan)
    {
        return redirect()->route('admin.checkin.index', $request->only('tanggal', 'armada'))->with('success', $pesan);
    }
}