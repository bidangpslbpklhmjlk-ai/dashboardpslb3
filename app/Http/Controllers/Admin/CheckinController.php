<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\Checkin;
use App\Models\JenisSampah;
use App\Services\CheckinService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\CheckinPengecualian;
use App\Models\LokasiAngkut;
use Illuminate\Validation\Rule;

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

        $blok = $jadwal[$armadaId] ?? [];

        return view('admin.checkin.index', [
            'tanggal'         => $tgl,
            'armadaList'      => $armadaList,
            'armadaId'        => $armadaId,
            'blok'            => $blok,
            'jenisList'       => JenisSampah::all(),
            'kelengkapan'     => $this->kelengkapan($blok, $armadaId, $tgl),
            'armadaPengganti' => Armada::where('tipe', 'dinas_lh')->where('aktif', true)
                                    ->where('id', '!=', $armadaId)->orderBy('nomor_polisi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $jenis = LokasiAngkut::whereKey($request->input('lokasi_angkut_id'))->value('jenis') ?? 'pengambilan';

        $d = $request->validate([
            'tanggal'                  => ['required', 'date', 'before_or_equal:today'],
            'rute_id'                  => ['required', 'integer', 'exists:rute_angkuts,id'],
            'lokasi_angkut_id'         => ['required', 'integer', 'exists:lokasi_angkuts,id'],
            'waktu_kejadian'           => ['required', 'date', 'before_or_equal:now'],
            'ritasi_ke'                => ['required', 'integer', 'min:1', 'max:20'],
            'sampah'                   => [Rule::requiredIf($jenis !== 'pool'), 'nullable', 'array'],
            'sampah.*.jenis_sampah_id' => ['required', 'distinct', 'exists:jenis_sampah,id'],
            'sampah.*.berat_kg'        => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'foto'                     => [Rule::requiredIf($jenis === 'pengambilan'), 'nullable', 'array', 'max:5'],
            'foto.*'                   => ['image', 'max:5120'],
            'catatan'                  => ['nullable', 'string', 'max:500'],
        ], [
            'foto.required'   => 'Foto wajib diunggah untuk titik pengambilan.',
            'sampah.required' => 'Isi jenis dan berat sampah.',
        ]);
        $d['sampah'] = $d['sampah'] ?? [];

        $this->svc->simpan($d, $request->file('foto', []), $request->user()->id);

        return $this->kembali($request, 'Check-in tersimpan.');
    }

    public function update(Request $request, Checkin $checkin)
    {
        $jenis = $checkin->lokasi->jenis ?? 'pengambilan';
        $d = $request->validate([
            'waktu_kejadian'           => ['required', 'date', 'before_or_equal:now'],
            'ritasi_ke'                => ['required', 'integer', 'min:1', 'max:20'],
            'sampah'                   => [Rule::requiredIf($jenis !== 'pool'), 'nullable', 'array'],
            'sampah.*.jenis_sampah_id' => ['required', 'distinct', 'exists:jenis_sampah,id'],
            'sampah.*.berat_kg'        => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'foto'                     => ['nullable', 'array', 'max:5'],
            'foto.*'                   => ['image', 'max:5120'],
            'hapus_foto'               => ['nullable', 'array'],
            'hapus_foto.*'             => ['integer'],
            'catatan'                  => ['nullable', 'string', 'max:500'],
            'alasan_manual'            => ['required', 'string', 'max:255'],
        ]);

        $d['sampah'] = $d['sampah'] ?? [];
        $this->svc->ubah($checkin, $d, $request->file('foto', []), $d['hapus_foto'] ?? [], $request->user()->id);

        return $this->kembali($request, 'Check-in direvisi.');
    }

    public function destroy(Request $request, Checkin $checkin)
    {
        $this->svc->hapus($checkin);
        return $this->kembali($request, 'Check-in dihapus.');
    }

        public function pengecualian(Request $request)
    {
        $d = $request->validate([
            'tanggal'             => ['required', 'date', 'before_or_equal:today'],
            'armada'              => ['required', 'integer', 'exists:armadas,id'],
            'lokasi_angkut_id'    => ['required', 'integer', 'exists:lokasi_angkuts,id'],
            'status'              => ['required', 'in:tidak_diambil,diambil_armada_lain'],
            'armada_pengganti_id' => ['required_if:status,diambil_armada_lain', 'nullable', 'integer', 'exists:armadas,id', 'different:armada'],
            'alasan'              => ['required', 'string', 'max:255'],
        ], [
            'armada_pengganti_id.required_if' => 'Pilih armada yang mengambil lokasi ini.',
        ]);

        CheckinPengecualian::updateOrCreate(
            ['tanggal' => $d['tanggal'], 'armada_id' => $d['armada'], 'lokasi_angkut_id' => $d['lokasi_angkut_id']],
            [
                'status'              => $d['status'],
                'armada_pengganti_id' => $d['status'] === 'diambil_armada_lain' ? $d['armada_pengganti_id'] : null,
                'alasan'              => $d['alasan'],
                'dicatat_oleh'        => $request->user()->id,
            ]
        );

        return $this->kembali($request, 'Alasan tersimpan.');
    }

    public function hapusPengecualian(Request $request, CheckinPengecualian $pengecualian)
    {
        $pengecualian->delete();
        return $this->kembali($request, 'Alasan dibatalkan.');
    }

    /** Status kelengkapan: sudah kembali ke pool? lokasi pengambilan mana yang belum diambil? */
    private function kelengkapan(array $blok, int $armadaId, Carbon $tgl): array
    {
        $entri = collect($blok)->flatMap(fn ($b) => collect($b['lokasi']));
        $unik  = $entri->unique(fn ($l) => $l['lokasi']->id);

        $jumlahPool = $entri->filter(fn ($l) => $l['lokasi']->jenis === 'pool')
            ->flatMap(fn ($l) => $l['checkins'])->unique('id')->count();

        $pengecualian = CheckinPengecualian::with('pengganti')
            ->where('tanggal', $tgl->toDateString())
            ->where('armada_id', $armadaId)
            ->get()->keyBy('lokasi_angkut_id');

        $belum = $unik->filter(fn ($l) => $l['lokasi']->jenis === 'pengambilan'
                && $l['checkins']->isEmpty()
                && ! $pengecualian->has($l['lokasi']->id))
            ->pluck('lokasi')->values();

        return [
            'kembali'      => $jumlahPool >= 2,   // check-in pool ke-2 = armada sudah balik
            'belum'        => $belum,
            'pengecualian' => $pengecualian,
        ];
    }

    private function kembali(Request $request, string $pesan)
    {
        return redirect()->route('admin.checkin.index', $request->only('tanggal', 'armada'))->with('success', $pesan);
    }
}