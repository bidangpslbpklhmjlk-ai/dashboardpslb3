<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\JadwalBentrokException;
use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\ArmadaKerusakan;
use App\Models\LokasiAngkut;
use App\Models\Pegawai;
use App\Models\PenugasanHarian;
use App\Services\PenugasanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KerusakanArmadaController extends Controller
{
    public function __construct(private PenugasanService $svc) {}

    public function index()
    {
        return view('admin.kerusakan.index', [
            'items' => ArmadaKerusakan::with('armada')
                ->withCount(['penugasan as jumlah_dialihkan' => fn ($q) => $q->where('dialihkan', true)])
                ->latest('tanggal_mulai')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.kerusakan.create', ['armadaList' => Armada::where('nama', 'Kantor DLH')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'armada_id'       => ['required', Rule::exists((new Armada)->getTable(), 'id')->where('nama', 'Kantor DLH')],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan'      => ['nullable', 'string', 'max:255'],
        ]);

        $k = ArmadaKerusakan::create($data + ['status' => 'rusak']);

        return redirect()->route('admin.kerusakan.show', $k)
            ->with('success', 'Armada ditandai rusak. Jadwal yang terdampak tampil di bawah, silakan alihkan ke armada lain.');
    }

    public function show(ArmadaKerusakan $kerusakan)
    {
        $kerusakan->load('armada');
        $terdampak = $this->svc->terdampak($kerusakan);

        $grup = $terdampak->groupBy('key')->map(fn ($it) => [
            'rute_id'    => $it->first()['rute_id'],
            'rencana_id' => $it->first()['rencana_id'],
            'mulai'      => $it->first()['mulai'],
            'selesai'    => $it->first()['selesai'],
            'tanggal'    => $it->pluck('tanggal')->sort()->values()->all(),
        ]);

        // Lokasi per rute (null = relasi rute->lokasi belum diketahui -> mode lokasi dinonaktifkan)
        $lokasiMap = $grup->mapWithKeys(function ($g) {
            $ids = $this->svc->lokasiRute($g['rute_id']);
            return [$g['rute_id'] => $ids === null ? null : LokasiAngkut::whereIn('id', $ids)->get()];
        });

        $rusakIds = ArmadaKerusakan::efektif()
            ->whereDate('tanggal_mulai', '<=', Carbon::today()->addDays(PenugasanService::HORIZON_HARI))
            ->where(fn ($q) => $q->whereNull('tanggal_selesai')->orWhereDate('tanggal_selesai', '>=', Carbon::today()))
            ->pluck('armada_id')->all();

        return view('admin.kerusakan.show', [
            'kerusakan'  => $kerusakan,
            'grup'       => $grup,
            'lokasiMap'  => $lokasiMap,
            'armadaList' => Armada::where('id', '!=', $kerusakan->armada_id)->get(),
            'rusakIds'   => $rusakIds,
            'pegawaiList'=> Pegawai::all(),
            'dialihkan'  => PenugasanHarian::with(['rute', 'armada', 'pegawai', 'lokasi'])
                ->where('kerusakan_id', $kerusakan->id)->orderBy('tanggal')->get(),
        ]);
    }

    public function alihkan(Request $request, ArmadaKerusakan $kerusakan)
    {
        $armadaTbl = (new Armada)->getTable();
        $data = $request->validate([
            'key'                 => ['required', 'string'],
            'mode'                => ['required', 'in:rute,lokasi'],
            'tanggal_mulai'       => ['required', 'date'],
            'tanggal_selesai'     => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan'              => ['nullable', 'string', 'max:255'],
            'armada_pengganti_id' => ['required_if:mode,rute', 'nullable', Rule::exists($armadaTbl, 'id')],
            'pegawai_pengganti_id'=> ['nullable', Rule::exists((new Pegawai)->getTable(), 'id')],
            'alokasi'             => ['required_if:mode,lokasi', 'nullable', 'array'],   // [lokasi_id => armada_id]
            'alokasi.*'           => ['nullable', Rule::exists($armadaTbl, 'id')],
            'pegawai'             => ['nullable', 'array'],                              // [armada_id => pegawai_id]
        ]);

        $mulai  = Carbon::parse($data['tanggal_mulai'])->toDateString();
        $sampai = Carbon::parse($data['tanggal_selesai']);

        if ($kerusakan->tanggal_selesai && $sampai->greaterThan($kerusakan->tanggal_selesai)) {
            throw ValidationException::withMessages(['tanggal_selesai' => 'Pengalihan sementara tidak boleh melewati tanggal selesai kerusakan (' . $kerusakan->tanggal_selesai->format('d/m/Y') . ').']);
        }

        $items = $this->svc->terdampak($kerusakan)->where('key', $data['key'])
            ->filter(fn ($i) => $i['tanggal'] >= $mulai && $i['tanggal'] <= $sampai->toDateString())->values();
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['tanggal_mulai' => 'Tidak ada jadwal terdampak pada rentang tanggal tersebut.']);
        }

        if ($data['mode'] === 'rute') {
            $targets = [['armada' => (int) $data['armada_pengganti_id'], 'pegawai' => $data['pegawai_pengganti_id'] ?? null]];
        } else {
            $alokasi = collect($data['alokasi'] ?? [])->filter()->map(fn ($v) => (int) $v);
            if ($alokasi->isEmpty()) {
                throw ValidationException::withMessages(['alokasi' => 'Pilih armada pengganti untuk setiap lokasi.']);
            }
            $targets = $alokasi->groupBy(fn ($arm) => $arm, true)
                ->map(fn ($rows, $arm) => [
                    'armada'  => (int) $arm,
                    'pegawai' => ($data['pegawai'][$arm] ?? null) ?: null,
                    'lokasi'  => $rows->keys()->map(fn ($k) => (int) $k)->values()->all(),
                ])->values()->all();
        }

        if (collect($targets)->contains(fn ($t) => $t['armada'] === (int) $kerusakan->armada_id)) {
            throw ValidationException::withMessages(['armada_pengganti_id' => 'Armada pengganti tidak boleh sama dengan armada yang rusak.']);
        }

        try {
            $n = DB::transaction(fn () => $this->svc->alihkan($kerusakan, $items, $data['mode'], $targets, $sampai, $data['alasan'] ?? null)->count());
        } catch (JadwalBentrokException $e) {
            return back()->withInput()->with('konflik', $e->konflik);
        }

        return back()->with('success', "Pengalihan tersimpan ($n penugasan harian ditandai DIALIHKAN). Setelah " . $sampai->format('d/m/Y') . ' jadwal kembali ke armada asal.');
    }

    /** Armada sudah diperbaiki: pengalihan masa depan dikembalikan ke armada asal. */
    public function selesai(ArmadaKerusakan $kerusakan)
    {
        $kemarin = Carbon::yesterday();

        try {
            DB::transaction(function () use ($kerusakan, $kemarin) {
                if ($kemarin->lessThan($kerusakan->tanggal_mulai)) {
                    $kerusakan->update(['status' => 'batal']);
                } else {
                    $baru = ($kerusakan->tanggal_selesai && $kerusakan->tanggal_selesai->lessThan($kemarin)) ? $kerusakan->tanggal_selesai : $kemarin;
                    $kerusakan->update(['status' => 'selesai', 'tanggal_selesai' => $baru]);
                }

$rows = PenugasanHarian::where('kerusakan_id', $kerusakan->id)->where('status', 'direncanakan')
    ->whereDate('tanggal', '>=', Carbon::today())->get();

                $this->svc->kunci($rows->pluck('armada_id')->merge($rows->pluck('armada_asal_id'))->all(), $rows->pluck('pegawai_id')->all(), $rows->pluck('rute_angkut_id')->all());
                $pulih = $this->svc->kembalikan($rows);
                $this->svc->assertTidakBentrok($pulih);
            });
        } catch (JadwalBentrokException $e) {
            return back()->with('konflik', $e->konflik);
        }

        return back()->with('success', 'Armada ditandai sudah diperbaiki. Pengalihan masa depan dikembalikan ke armada asal.');
    }
}
