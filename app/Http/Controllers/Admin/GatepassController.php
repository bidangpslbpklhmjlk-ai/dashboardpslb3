<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatepassLog;
use App\Models\Armada;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class GatepassController extends Controller
{
    public function index(Request $request)
    {
        $query = GatepassLog::with(['armada', 'invoice']);

        $dari   = $request->filled('dari')   ? Carbon::parse($request->dari)->startOfDay()   : null;
        $sampai = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay()   : null;
        if (! $dari && ! $sampai && $request->filled('tanggal')) {
            $dari = Carbon::parse($request->tanggal)->startOfDay();
        }
        if ($dari && ! $sampai) { $sampai = $dari->copy()->endOfDay(); }
        if ($sampai && ! $dari) { $dari   = $sampai->copy()->startOfDay(); }

        if ($dari) {
            $query->whereBetween('waktu_masuk', [$dari, $sampai]);
        }
        if ($request->filled('tipe_kendaraan')) {
            $query->where('tipe_kendaraan', $request->tipe_kendaraan);
        }
        if ($request->filled('perusahaan_id')) {
            $query->where('armada_id', $request->perusahaan_id);
        }
        if ($request->filled('nomor_polisi')) {
            $query->where('nomor_polisi', 'like', '%' . $request->nomor_polisi . '%');
        }

        $dasarGrafik = clone $query; 
        $allLogs = $query->orderBy('waktu_masuk', 'desc')->get();

        $grouped = $allLogs->groupBy(function ($log) {
            return $log->nomor_polisi . '_' . $log->waktu_masuk->format('Y-m-d');
        })->map(function ($items) {
            $items = $items->sortBy('waktu_masuk')->values();
            $latest = $items->last();
            return (object) [
                'nomor_polisi'   => $latest->nomor_polisi,
                'items'          => $items,
                'ritasi_count'   => $items->count(),
                'total_volume'   => $items->sum('volume_ton'),
                'waktu_terakhir' => $latest->waktu_masuk,
                'tipe_kendaraan' => $latest->tipe_kendaraan,
                'jenis_muatan'   => $latest->jenis_muatan,
                'armada'         => $latest->armada,
                'latest'         => $latest,
            ];
        })->sortByDesc('waktu_terakhir')->values();

        $perPage = 25;
        $page = $request->input('page', 1);
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped->forPage($page, $perPage)->values(),
            $grouped->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $tipeKendaraanList = GatepassLog::select('tipe_kendaraan')->distinct()->pluck('tipe_kendaraan');
        $jenisMuatanList   = GatepassLog::select('jenis_muatan')->distinct()->pluck('jenis_muatan');
        $armadaList        = Armada::where('aktif', true)->orderBy('nomor_polisi')->get();
       $awalStat  = $dari ?? today()->startOfDay();
$akhirStat = $sampai ?? today()->endOfDay();
$statQuery = GatepassLog::whereBetween('waktu_masuk', [$awalStat, $akhirStat]);

$stats = [
    'total_kendaraan' => (clone $statQuery)->count(),
    'total_volume'    => (clone $statQuery)->sum('volume_ton'),
    'rata_volume'     => (clone $statQuery)->avg('volume_ton') ?? 0,
    'volume_estimasi' => (clone $statQuery)->where('asal_data', 'impor_rekap')->sum('volume_ton'),
    'label'           => ! $dari ? 'Hari Ini'
        : ($awalStat->isSameDay($akhirStat)
            ? $awalStat->translatedFormat('d M Y')
            : $awalStat->translatedFormat('d M Y') . ' – ' . $akhirStat->translatedFormat('d M Y')),
];

// Grafik mengikuti filter yang sama dengan tabel.
// Tanpa filter tanggal: dari data pertama sampai data terakhir yang cocok.
$rentang = (clone $dasarGrafik)->toBase()
    ->selectRaw('MIN(waktu_masuk) as awal, MAX(waktu_masuk) as akhir')->first();

$tgAwal  = $dari   ? $dari->copy()->startOfDay()   : ($rentang?->awal  ? Carbon::parse($rentang->awal)->startOfDay()  : null);
$tgAkhir = $sampai ? $sampai->copy()->startOfDay() : ($rentang?->akhir ? Carbon::parse($rentang->akhir)->startOfDay() : null);

$trendData       = collect();
$trendJudul      = 'Tren Ritasi';
$trendKeterangan = 'Tidak ada data pada filter ini';

if ($tgAwal && $tgAkhir && $tgAwal->lte($tgAkhir)) {
    $perHari = (clone $dasarGrafik)->toBase()
        ->selectRaw('DATE(waktu_masuk) as tgl, COUNT(*) as jumlah, SUM(volume_ton) as volume')
        ->whereBetween('waktu_masuk', [$tgAwal->copy()->startOfDay(), $tgAkhir->copy()->endOfDay()])
        ->groupBy('tgl')->get()->keyBy('tgl');

    // sampai 62 hari: per hari. Lebih panjang: per bulan.
    $perBulan = $tgAwal->diffInDays($tgAkhir) > 62;

    if (! $perBulan) {
        for ($d = $tgAwal->copy(); $d->lte($tgAkhir); $d->addDay()) {
            $r = $perHari->get($d->toDateString());
            $trendData->push([
                'tanggal' => $d->translatedFormat('d M'),
                'jumlah'  => (int) ($r->jumlah ?? 0),
                'volume'  => (float) ($r->volume ?? 0),
            ]);
        }
    } else {
        $perBln = $perHari->groupBy(fn ($r) => substr($r->tgl, 0, 7));
        for ($m = $tgAwal->copy()->startOfMonth(); $m->lte($tgAkhir); $m->addMonth()) {
            $baris = $perBln->get($m->format('Y-m'), collect());
            $trendData->push([
                'tanggal' => $m->translatedFormat('M Y'),
                'jumlah'  => (int) $baris->sum('jumlah'),
                'volume'  => round((float) $baris->sum('volume'), 2),
            ]);
        }
    }

    $trendJudul      = $perBulan ? 'Tren per Bulan' : 'Tren per Hari';
    $trendKeterangan = 'Mengikuti filter · ' . ($tgAwal->isSameDay($tgAkhir)
        ? $tgAwal->translatedFormat('d M Y')
        : $tgAwal->translatedFormat('d M Y') . ' – ' . $tgAkhir->translatedFormat('d M Y'));
}

        $chartLabels = $trendData->pluck('tanggal');
        $chartJumlah = $trendData->pluck('jumlah');
        $chartVolume = $trendData->pluck('volume');

return view('admin.gatepass.index', compact(
    'logs', 'stats', 'tipeKendaraanList', 'jenisMuatanList', 'armadaList',
    'chartLabels', 'chartJumlah', 'chartVolume', 'trendJudul', 'trendKeterangan'
));
    }

    public function create()
    {
        return view('admin.gatepass.create', [
            'armadaOptions'   => $this->armadaOptions(),
            'sumberPerArmada' => $this->sumberPerArmada(),
        ]);
    }


public function store(Request $request)
{
    $validated = $request->validate([
        'tanggal_masuk' => 'required|date|before_or_equal:today',
        'armada_id'     => 'required|exists:armadas,id',
        'jenis_muatan'  => 'required|string|max:50',
        'volume_ton'    => 'required|numeric|min:0|max:999.99',
        'photo'         => 'nullable|image|max:2048',
        'sumber_sampah'   => 'nullable|array',
        'sumber_sampah.*' => 'string|max:150',
    ], [
        'armada_id.required' => 'Pilih armada dari daftar.',
        'armada_id.exists'   => 'Armada tidak ditemukan.',
    ]);

    $armada = $this->resolveArmada((int) $validated['armada_id']);

    $data = [
        'armada_id'      => $armada->id,
        'nomor_polisi'   => $armada->nomor_polisi,
        'tipe_kendaraan' => $armada->jenis_kendaraan ?: 'Lainnya',
        'jenis_muatan'   => $validated['jenis_muatan'],
        'sumber_sampah' => $request->filled('sumber_sampah') ? implode('; ', $request->input('sumber_sampah')) : null,
        'volume_ton'     => $validated['volume_ton'],
        'waktu_masuk'    => Carbon::parse($validated['tanggal_masuk'])
                                ->setTimeFromTimeString(now()->format('H:i:s')),
        'ritasi'         => 1,
    ];

    if ($request->hasFile('photo')) {
        $data['photo'] = $request->file('photo')->store('gatepass', 'public');
    }

    $gatepass = GatepassLog::create($data);
    // Invoice otomatis dibuat oleh GatepassLogObserver::created() jika armada berbayar

    $ritasiKe = GatepassLog::where('nomor_polisi', $gatepass->nomor_polisi)
        ->whereDate('waktu_masuk', $gatepass->waktu_masuk->toDateString())
        ->count();

    return redirect()->route('admin.gatepass.index')
        ->with('success', 'Kendaraan berhasil dicatat (Ritasi ke-' . $ritasiKe . ')'
            . ($gatepass->fresh()->hasInvoice() ? ' dan invoice dibuat' : ''));
}

    public function edit(GatepassLog $gatepass)
    {
        return view('admin.gatepass.edit', [
            'gatepass'        => $gatepass,
            'armadaOptions'   => $this->armadaOptions($gatepass->armada_id),
            'sumberPerArmada' => $this->sumberPerArmada(),
        ]);
    }

    public function update(Request $request, GatepassLog $gatepass)
{
    $validated = $request->validate([
        'tanggal_masuk' => 'required|date|before_or_equal:today',
        'armada_id'     => 'required|exists:armadas,id',
        'jenis_muatan'  => 'required|string|max:50',
        'volume_ton'    => 'required|numeric|min:0|max:999.99',
        'photo'         => 'nullable|image|max:2048',
        'sumber_sampah'   => 'nullable|array',
        'sumber_sampah.*' => 'string|max:150',
    ], [
        'armada_id.required' => 'Pilih armada dari daftar.',
        'armada_id.exists'   => 'Armada tidak ditemukan.',
    ]);

    $armada = $this->resolveArmada((int) $validated['armada_id'], $gatepass->armada_id);

    $data = [
        'armada_id'      => $armada->id,
        'nomor_polisi'   => $armada->nomor_polisi,
        'tipe_kendaraan' => $armada->jenis_kendaraan ?: 'Lainnya',
        'jenis_muatan'   => $validated['jenis_muatan'],
        'sumber_sampah' => $request->filled('sumber_sampah') ? implode('; ', $request->input('sumber_sampah')) : null,
        'volume_ton'     => $validated['volume_ton'],
        'waktu_masuk'    => Carbon::parse($validated['tanggal_masuk'])
                                ->setTimeFromTimeString($gatepass->waktu_masuk->format('H:i:s')),
    ];

    if ($request->hasFile('photo')) {
        if ($gatepass->photo && Storage::disk('public')->exists($gatepass->photo)) {
            Storage::disk('public')->delete($gatepass->photo);
        }
        $data['photo'] = $request->file('photo')->store('gatepass', 'public');
    }

    $gatepass->update($data);

    return redirect()->route('admin.gatepass.index')
        ->with('success', 'Data berhasil diperbarui');
}

/**
 * Daftar armada untuk dropdown. Armada yang sedang dipakai log ini
 * (saat edit) tetap ikut walau sudah nonaktif.
 */
private function armadaOptions(?int $includeId = null): array
{
    return Armada::query()
        ->whereNotNull('nomor_polisi')
        ->where('nomor_polisi', '!=', '')
        ->where(function ($q) use ($includeId) {
            $q->where('aktif', true);
            if ($includeId) {
                $q->orWhere('id', $includeId);
            }
        })
        ->orderBy('nomor_polisi')
        ->get()
        ->map(fn (Armada $a) => [
            'id'              => $a->id,
            'nomor_polisi'    => $a->nomor_polisi,
            'jenis_kendaraan' => $a->jenis_kendaraan,
            'nama'            => $a->nama,
            'tipe'            => $a->tipe,
            'gratis'          => $a->isGratis(),
        ])
        ->values()
        ->all();
}

/**
 * Armada harus aktif dan punya nomor polisi.
 * Saat edit, armada lama boleh tetap dipakai walau sudah nonaktif.
 */
private function resolveArmada(int $id, ?int $currentId = null): Armada
{
    $armada = Armada::find($id);

    $boleh = $armada
        && filled($armada->nomor_polisi)
        && ($armada->aktif || $armada->id === $currentId);

    if (! $boleh) {
        throw ValidationException::withMessages([
            'armada_id' => 'Armada tidak aktif atau belum punya nomor polisi.',
        ]);
    }

    return $armada;
}
    public function destroy(GatepassLog $gatepass)
    {
        if ($gatepass->invoice) {
            $gatepass->invoice->delete();
        }
        if ($gatepass->photo) {
            Storage::disk('public')->delete($gatepass->photo);
        }
        $gatepass->delete();

        return redirect()->route('admin.gatepass.index')
            ->with('success', 'Data berhasil dihapus');
    }
    private function sumberPerArmada(): array
{
    return \App\Models\RuteAngkut::with('lokasis:id,nama,jenis')->get()
        ->groupBy('armada_id')
        ->map(fn ($rutes) => $rutes->flatMap->lokasis
            ->filter(fn ($l) => ($l->jenis ?? 'pengambilan') === 'pengambilan')
            ->pluck('nama')->unique()->sort()->values()->all())
        ->all();
}
}