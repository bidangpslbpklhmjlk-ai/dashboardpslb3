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
    private const VOLUME_DEFAULT = 2.5;               // ton per ritasi bila volume_ton kosong (rekap tanpa tonase)
    private const JENIS_DEFAULT  = 'Sampah Domestik'; // jenis muatan bila kolom kosong

    /** Kolom CSV: dipakai bersama oleh template() dan export() agar identik. */
    private const KOLOM_CSV = [
        'waktu_masuk', 'nomor_polisi', 'tipe_kendaraan', 'jenis_muatan',
        'volume_ton', 'ritasi', 'sumber_sampah', 'catatan', 'asal_data',
    ];

    /** Filter yang dipakai bersama oleh index() dan export(). */
    private function filteredQuery(Request $request): array
    {
        $query = GatepassLog::with(['armada', 'invoice']);

        $dari   = $request->filled('dari')   ? Carbon::parse($request->dari)->startOfDay() : null;
        $sampai = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay() : null;
        if (! $dari && ! $sampai && $request->filled('tanggal')) {
            $dari = Carbon::parse($request->tanggal)->startOfDay();
        }
        if (! $dari && ! $sampai && $request->filled('tahun')) {
    $t = (int) $request->tahun;
    if ($t >= 2000 && $t <= 2100) {
        $dari   = Carbon::create($t, 1, 1)->startOfDay();
        $sampai = $dari->copy()->endOfYear();
    }
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

        return [$query, $dari, $sampai];
    }

    public function index(Request $request)
    {
        [$query, $dari, $sampai] = $this->filteredQuery($request);

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
                });

        $urut = $request->input('urut', 'waktu');
        $arah = $request->input('arah', 'desc') === 'asc' ? 'asc' : 'desc';

        $kolom = [
            'waktu'  => fn ($g) => $g->waktu_terakhir->timestamp,
            'nopol'  => fn ($g) => strtolower((string) $g->nomor_polisi),
            'tipe'   => fn ($g) => strtolower((string) $g->tipe_kendaraan),
            'muatan' => fn ($g) => strtolower((string) $g->jenis_muatan),
            'armada' => fn ($g) => strtolower((string) ($g->armada?->nama ?? '')),
            'volume' => fn ($g) => (float) $g->total_volume,
        ];
        if (! isset($kolom[$urut])) {
            $urut = 'waktu';
        }

        $grouped = $grouped->sortBy($kolom[$urut], SORT_REGULAR, $arah === 'desc')->values();

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
        $tahunList = GatepassLog::selectRaw('YEAR(waktu_masuk) as t') ->distinct()->orderByDesc('t')->pluck('t');
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
            'logs', 'stats', 'tipeKendaraanList', 'jenisMuatanList', 'armadaList', 'tahunList',
            'chartLabels', 'chartJumlah', 'chartVolume', 'trendJudul', 'trendKeterangan',
            'urut', 'arah'
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
            'sumber_sampah'  => $request->filled('sumber_sampah') ? implode('; ', $request->input('sumber_sampah')) : null,
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
            'sumber_sampah'  => $request->filled('sumber_sampah') ? implode('; ', $request->input('sumber_sampah')) : null,
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

public function template()
{
    $kemarin = now()->subDay()->format('Y-m-d');

    $callback = function () use ($kemarin) {
        $file = fopen('php://output', 'w');
        fwrite($file, "\xEF\xBB\xBF");      // BOM agar UTF-8 terbaca
        fwrite($file, "sep=;\r\n");          // paksa Excel memisah kolom dengan ;
        fputcsv($file, self::KOLOM_CSV, ';');

        // Contoh 1: semua kolom terisi
        fputcsv($file, [
            $kemarin . ' 08:30', 'X 0000 XX (HAPUS BARIS INI)', 'Dump Truck', 'Sampah Domestik',
            '2.5', '', 'Pasar A|Terminal B', 'contoh lengkap', 'impor_rekap',
        ], ';');
        // Contoh 2: hanya kolom wajib
        fputcsv($file, [
            $kemarin, 'Y 1111 YY (HAPUS BARIS INI)', '', '', '', '', '', '', '',
        ], ';');

        fclose($file);
    };

    return response()->stream($callback, 200, [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="template-gatepass.csv"',
    ]);
}

    /** Eksport CSV sesuai filter index. Kolom identik dengan template import. */
    public function export(Request $request)
    {
        [$query] = $this->filteredQuery($request);

        $filename = 'gatepass-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, self::KOLOM_CSV);

            foreach ($query->orderBy('waktu_masuk')->orderBy('id')->cursor() as $log) {
                fputcsv($file, [
                    $log->waktu_masuk->format('Y-m-d H:i:s'),
                    $log->nomor_polisi,
                    $log->tipe_kendaraan,
                    $log->jenis_muatan,
                    number_format((float) $log->volume_ton, 2, '.', ''),
                    $log->ritasi,
                    str_replace('; ', '|', (string) $log->sumber_sampah),
                    $log->catatan,
                    $log->asal_data,
                ]);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Import rekap ritasi dari CSV. Satu baris = satu ritasi.
     * Kolom wajib: waktu_masuk (YYYY-MM-DD atau YYYY-MM-DD HH:MM[:SS]; juga DD/MM/YYYY), nomor_polisi
     * Kolom opsional: tipe_kendaraan (kosong = dari armada), jenis_muatan (kosong = JENIS_DEFAULT),
     *   volume_ton (kosong = VOLUME_DEFAULT), ritasi (kosong = 1), sumber_sampah (pisahkan dengan |),
     *   catatan, asal_data (aplikasi | impor_rekap, kosong = impor_rekap)
     * Jam kosong -> 08:00 lalu +1 menit per ritasi berikutnya (armada & tanggal sama).
     * Baris yang salah dilewati dan dilaporkan; baris yang sama persis dengan data lama juga dilewati.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        if ($handle === false) {
            return back()->with('error', 'Gagal membaca file CSV.');
        }

$baris1 = preg_replace('/^\xEF\xBB\xBF/', '', (string) fgets($handle));
if (preg_match('/^sep=(.)/i', $baris1, $m)) {
    $delim = $m[1];            // baris sep= sudah terbaca, header ada di baris berikutnya
} else {
    rewind($handle);
    $delim = substr_count($baris1, ';') > substr_count($baris1, ',') ? ';' : ',';
}

        $header = fgetcsv($handle, 0, $delim);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $header = array_map(fn ($h) => strtolower(trim($h)), $header ?: []);

        foreach (['waktu_masuk', 'nomor_polisi'] as $col) {
            if (! in_array($col, $header)) {
                fclose($handle);
                return back()->with('error', "Format CSV tidak sesuai: kolom '{$col}' tidak ditemukan. Silakan gunakan template yang disediakan.");
            }
        }

        $armadaMap = Armada::where('aktif', true)
            ->whereNotNull('nomor_polisi')->where('nomor_polisi', '!=', '')->get()
            ->keyBy(fn ($a) => preg_replace('/\s+/', '', strtoupper($a->nomor_polisi)));

        // samakan dengan daftar di form create/edit dan DAFTAR_JENIS di app
        $jenisMap = collect(['Sampah Domestik', 'Sampah Komersial', 'Sampah B3', 'Limbah Organik', 'Residu'])
            ->keyBy(fn ($j) => strtolower($j));

        $imported  = 0;
        $duplikat  = 0;
        $skipped   = [];
        $urut      = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delim)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // baris kosong
            }

            $row  = array_pad($row, count($header), null);
            $data = array_combine($header, array_slice($row, 0, count($header)));

            // waktu_masuk: "tanggal" atau "tanggal jam"
            $wTeks   = trim((string) ($data['waktu_masuk'] ?? ''));
            $tgl     = null;
            $jam     = null;
            $jamTeks = '';
            if ($wTeks !== '') {
                $bagian  = preg_split('/[\sT]+/', $wTeks, 2);
                $tgl     = $this->parseTanggal($bagian[0]);
                $jamTeks = trim($bagian[1] ?? '');
                $jam     = $jamTeks !== '' ? $this->parseJam($jamTeks) : null;
            }

            $armada  = $armadaMap[preg_replace('/\s+/', '', strtoupper(trim((string) ($data['nomor_polisi'] ?? ''))))] ?? null;

            $jenisTeks = trim((string) ($data['jenis_muatan'] ?? ''));
            $jenis     = $jenisTeks === ''
                ? self::JENIS_DEFAULT
                : ($jenisMap[strtolower($jenisTeks)] ?? null);

            $volTeks = trim((string) ($data['volume_ton'] ?? ''));
            $vol     = $volTeks === '' ? self::VOLUME_DEFAULT : str_replace(',', '.', $volTeks);

            $catatan = trim((string) ($data['catatan'] ?? '')) ?: null;
            $tipeCsv = trim((string) ($data['tipe_kendaraan'] ?? ''));

            $ritasiTeks = trim((string) ($data['ritasi'] ?? ''));
            $ritasi     = $ritasiTeks === '' ? 1 : (ctype_digit($ritasiTeks) ? (int) $ritasiTeks : null);

            $asal = strtolower(trim((string) ($data['asal_data'] ?? ''))) ?: 'impor_rekap';

            // urutan ritasi dihitung sebelum cek lain, supaya jam otomatis tetap sama
            // kalau baris yang salah diperbaiki lalu file diunggah ulang
            $urutan = 0;
            if ($tgl && $armada) {
                $k = $armada->id . '|' . $tgl->toDateString();
                $urutan = $urut[$k] = ($urut[$k] ?? 0) + 1;
            }

            $err = [];
            if (! $tgl)    $err[] = 'waktu_masuk tidak valid (pakai YYYY-MM-DD atau YYYY-MM-DD HH:MM)';
            if (! $armada) $err[] = 'nomor polisi tidak ditemukan atau armada tidak aktif';
            if (! $jenis)  $err[] = 'jenis muatan tidak dikenal';
            if (! is_numeric($vol) || $vol < 0 || $vol > 999.99) $err[] = 'volume harus angka 0 - 999.99';
            if ($jamTeks !== '' && ! $jam) $err[] = 'jam pada waktu_masuk tidak valid (pakai HH:MM)';
            if ($ritasi === null || $ritasi < 1 || $ritasi > 99) $err[] = 'ritasi harus bilangan bulat 1 - 99';
            if (! in_array($asal, ['aplikasi', 'impor_rekap'], true)) $err[] = "asal_data harus 'aplikasi' atau 'impor_rekap'";
            if (mb_strlen($tipeCsv) > 50) $err[] = 'tipe_kendaraan lebih dari 50 karakter';
            if ($catatan && mb_strlen($catatan) > 500) $err[] = 'catatan lebih dari 500 karakter';

            $waktu = null;
            if (! $err) {
                $waktu = $jam
                    ? $tgl->copy()->setTime($jam[0], $jam[1])
                    : $tgl->copy()->setTime(8, 0)->addMinutes($urutan - 1);
                if ($waktu->gt(now()->addMinutes(10))) {
                    $err[] = 'waktu masuk di masa depan';
                }
            }

            if ($err) {
                $skipped[] = "Baris {$rowNumber}: " . implode('; ', $err) . '.';
                continue;
            }

            $vol = round((float) $vol, 2);

            if (GatepassLog::where('armada_id', $armada->id)
                ->where('waktu_masuk', $waktu)->where('volume_ton', $vol)->exists()) {
                $duplikat++;
                continue;
            }

            $sumber = collect(explode('|', (string) ($data['sumber_sampah'] ?? '')))
                ->map(fn ($s) => trim($s))->filter()->implode('; ');

            // create() lewat Eloquent; GatepassLogObserver melewati invoice untuk asal_data 'impor_rekap'
            GatepassLog::create([
                'armada_id'      => $armada->id,
                'nomor_polisi'   => $armada->nomor_polisi,
                'tipe_kendaraan' => $tipeCsv !== '' ? $tipeCsv : ($armada->jenis_kendaraan ?: 'Lainnya'),
                'jenis_muatan'   => $jenis,
                'volume_ton'     => $vol,
                'waktu_masuk'    => $waktu,
                'ritasi'         => $ritasi,
                'asal_data'      => $asal,
                'sumber_sampah'  => $sumber ?: null,
                'catatan'        => $catatan,
            ]);

            $imported++;
        }

        fclose($handle);

        $message = "{$imported} ritasi berhasil diimport.";
        if ($duplikat) {
            $message .= " {$duplikat} baris dilewati karena sudah ada.";
        }
        if (! empty($skipped)) {
            $message .= ' ' . count($skipped) . ' baris salah dilewati (lihat detail di bawah).';
        }

        return redirect()->route('admin.gatepass.index')
            ->with($imported > 0 || $duplikat > 0 ? 'success' : 'error', $message)
            ->with('import_errors', $skipped);
    }

    private function parseTanggal(string $v): ?Carbon
    {
        $v = trim($v);

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $v, $m)) {
            [$y, $b, $t] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $v, $m)) {
            [$t, $b, $y] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } else {
            return null;
        }

        return checkdate($b, $t, $y) ? Carbon::create($y, $b, $t, 0, 0, 0) : null;
    }

    /** @return array{0:int,1:int}|null [jam, menit] */
    private function parseJam(string $v): ?array
    {
        if (preg_match('/^(\d{1,2})[:.](\d{2})(?::\d{2})?$/', trim($v), $m) && $m[1] < 24 && $m[2] < 60) {
            return [(int) $m[1], (int) $m[2]];
        }

        return null;
    }
}