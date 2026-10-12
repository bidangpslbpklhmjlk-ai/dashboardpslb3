<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industri;
use App\Models\NeracaSampahArea;
use App\Models\NeracaSampahHarian;
use App\Models\NeracaSampahLaporan;
use App\Services\NeracaSampahImporter;
use App\Services\NeracaSampahService;
use App\Services\NeracaSampahTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Services\NeracaSampahAnalisis;

class NeracaSampahController extends Controller
{
    public function __construct(private NeracaSampahService $service) {}

    /** Daftar industri yang punya laporan (satu baris per industri) */
    public function index()
    {
        $grup = NeracaSampahLaporan::with('industri')
            ->orderByDesc('periode_mulai')
            ->get()
            ->groupBy('industri_id')
            ->sortBy(fn ($items) => mb_strtolower($items->first()->industri->nama));

        return view('admin.neraca-sampah.index', [
            'grup'         => $grup,
            'industriList' => Industri::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    /** Halaman satu industri: tahun-tahun laporannya, lengkap dengan bulan di dalamnya */
    public function industri(Industri $industri, NeracaSampahAnalisis $penganalisis)
    {
        $laporans = NeracaSampahLaporan::where('industri_id', $industri->id)
            ->orderByDesc('periode_mulai')
            ->get();

        // Jumlah tanggal yang sudah punya data, per laporan per bulan
        $hariTerisi = [];
        if ($laporans->isNotEmpty()) {
            $rows = DB::table('neraca_sampah_harian')
                ->whereIn('laporan_id', $laporans->pluck('id'))
                ->select('laporan_id', 'tanggal')
                ->distinct()
                ->get();

            foreach ($rows as $r) {
                $ym = substr((string) $r->tanggal, 0, 7);
                $hariTerisi[$r->laporan_id][$ym] = ($hariTerisi[$r->laporan_id][$ym] ?? 0) + 1;
            }
        }

                        // Tahun yang dipilih lewat ?laporan=ID. Bawaan: laporan terbaru
        $laporan  = $laporans->firstWhere('id', (int) request('laporan')) ?? $laporans->first();
        $tampil   = $laporan ? collect([$laporan]) : collect();
        $analisis = $penganalisis->analisa($tampil);

        return view('admin.neraca-sampah.industri', compact('industri', 'laporans', 'laporan', 'tampil', 'hariTerisi', 'analisis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'industri_id'     => 'required|exists:industri,id',
            'tahun'           => 'nullable|integer|min:2000|max:2100',
            'periode_mulai'   => 'nullable|date',
            'periode_selesai' => 'nullable|date|after_or_equal:periode_mulai',
        ]);

        if ($request->filled('periode_mulai') && $request->filled('periode_selesai')) {
            $mulai = Carbon::parse($request->periode_mulai)->startOfDay();
            $selesai = Carbon::parse($request->periode_selesai)->startOfDay();
        } elseif ($request->filled('tahun')) {
            $mulai = Carbon::create((int) $request->tahun, 1, 1)->startOfDay();
            $selesai = Carbon::create((int) $request->tahun, 12, 31)->startOfDay();
        } else {
            throw ValidationException::withMessages([
                'tahun' => 'Isi tahun, atau isi periode mulai dan periode selesai.',
            ]);
        }

        $this->periksaPeriode((int) $request->industri_id, $mulai, $selesai, null);

        $laporan = NeracaSampahLaporan::create([
            'industri_id'     => $request->industri_id,
            'periode_mulai'   => $mulai->toDateString(),
            'periode_selesai' => $selesai->toDateString(),
            'status'          => 'draft',
        ]);

         return redirect()->route('admin.neraca-sampah.industri', [$laporan->industri_id, 'laporan' => $laporan->id])
            ->with('success', 'Laporan dibuat. Klik Buka untuk mengisi data harian atau Import untuk memasukkan file Excel.');
    }

        public function show(Request $request, NeracaSampahLaporan $laporan, NeracaSampahAnalisis $analisis)
    {
        $tab = $request->query('tab', 'neraca'); // neraca | bulanan | area | input | pengaturan
        $laporan->load('industri');
        $bulanAktif = $request->query('bulan');
        if (!isset($laporan->bulanList()[$bulanAktif])) {
            $bulanAktif = null;
        }

        return view('admin.neraca-sampah.show', [
            'laporan'      => $laporan,
            'tab'          => $tab,
            'bulanAktif'   => $bulanAktif,
            'terisi'       => $tab === 'input' ? $this->ringkasInput($laporan) : [],
            'neraca'       => $tab === 'neraca'
                ? ($bulanAktif ? $analisis->neracaBulan($laporan, $bulanAktif) : $this->service->neraca($laporan))
                : null,
            'bulanan'      => $tab === 'bulanan' ? $this->service->rekapBulanan($laporan) : null,
            'area'         => $tab === 'area'    ? $this->service->rekapArea($laporan) : null,
            'areas'        => NeracaSampahArea::where('aktif', true)->orderBy('urutan')->get(),
            'industriList' => Industri::where('aktif', true)
                ->orWhere('id', $laporan->industri_id)
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function update(Request $request, NeracaSampahLaporan $laporan)
    {
        $data = $request->validate($this->rules());

        $this->periksaPeriode(
            (int) $data['industri_id'],
            Carbon::parse($data['periode_mulai'])->startOfDay(),
            Carbon::parse($data['periode_selesai'])->startOfDay(),
            $laporan->id
        );

        $laporan->update($data);

        return back()->with('success', 'Laporan diperbarui');
    }

    public function destroy(NeracaSampahLaporan $laporan)
    {
        $industriId = $laporan->industri_id;
        $laporan->delete(); // data harian ikut terhapus (cascade)

        $masihAda = NeracaSampahLaporan::where('industri_id', $industriId)->exists();

        return redirect()
            ->route($masihAda ? 'admin.neraca-sampah.industri' : 'admin.neraca-sampah.index', $masihAda ? $industriId : [])
            ->with('success', 'Laporan dihapus');
    }

    /** Form input harian: 1 bulan × 1 area (tanggal 1..31 × 3 kolom) */
    public function harian(NeracaSampahLaporan $laporan, string $bulan, NeracaSampahArea $area)
    {
        abort_unless(isset($laporan->bulanList()[$bulan]), 404);
        $awal = Carbon::createFromFormat('!Y-m', $bulan)->startOfMonth();

        $existing = $laporan->harian()
            ->where('area_id', $area->id)
            ->whereBetween('tanggal', [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()])
            ->get()->keyBy(fn ($r) => $r->tanggal->format('Y-m-d'));

        return view('admin.neraca-sampah.partials.input-harian', [
            'laporan'  => $laporan->load('industri'),
            'area'     => $area,
            'awal'     => $awal,
            'existing' => $existing,
            'areas'    => NeracaSampahArea::where('aktif', true)->orderBy('urutan')->get(),
        ]);
    }

    public function simpanHarian(Request $request, NeracaSampahLaporan $laporan, string $bulan, NeracaSampahArea $area)
    {
        $request->validate([
            'data'                => 'required|array',
            'data.*.organik_kg'   => 'nullable|numeric|min:0',
            'data.*.anorganik_kg' => 'nullable|numeric|min:0',
            'data.*.residu_kg'    => 'nullable|numeric|min:0',
        ]);

        foreach ($request->data as $tanggal => $v) {
            // Kunci tanggal harus Y-m-d yang valid
            try {
                $tgl = Carbon::createFromFormat('Y-m-d', (string) $tanggal)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }
            if ($tgl->format('Y-m-d') !== $tanggal) {
                continue;
            }

            // Hanya terima tanggal yang berada di bulan dan periode laporan
            if ($tgl->format('Y-m') !== $bulan
                || $tgl->lt($laporan->periode_mulai)
                || $tgl->gt($laporan->periode_selesai)) {
                continue;
            }

            $kunci = ['laporan_id' => $laporan->id, 'area_id' => $area->id, 'tanggal' => $tgl->toDateString()];

            // Baris yang dikosongkan = tidak ada data (bukan angka 0)
            $kosong = ($v['organik_kg'] ?? '') === ''
                && ($v['anorganik_kg'] ?? '') === ''
                && ($v['residu_kg'] ?? '') === '';

            if ($kosong) {
                NeracaSampahHarian::where($kunci)->delete();
                continue;
            }

            NeracaSampahHarian::updateOrCreate($kunci, [
                'organik_kg'   => $v['organik_kg'] ?: 0,
                'anorganik_kg' => $v['anorganik_kg'] ?: 0,
                'residu_kg'    => $v['residu_kg'] ?: 0,
            ]);
        }

        return back()->with('success', 'Data harian tersimpan');
    }

    public function cetak(NeracaSampahLaporan $laporan, string $jenis)
    {
        // TODO fase 2: PDF/Excel, ikuti pola KunjunganController::exportReportPdf / Excel
        abort_unless(in_array($jenis, ['neraca', 'bulanan', 'area']), 404);
    }

    public function import(Request $request, NeracaSampahLaporan $laporan, NeracaSampahImporter $importer)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xlsm|max:5120',
            'bulan'      => 'nullable|array',
            'bulan.*'    => 'date_format:Y-m',
        ], [
            'excel_file.required' => 'Pilih file Excel terlebih dahulu.',
            'excel_file.mimes'    => 'File harus berformat .xlsx atau .xlsm.',
            'excel_file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

                $bulan = array_values(array_unique($request->input('bulan', [])));
        if (!$bulan) {
            return back()->with('error', 'Pilih minimal satu bulan yang akan diimpor.');
        }

        try {
            $hasil = $importer->import($laporan, $request->file('excel_file')->getRealPath(), $bulan);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'File tidak dapat dibaca. Pastikan file asli dari format Log Book dan Neraca.');
        }

        $pesan = "{$hasil['total_baris']} data harian berhasil diimport.";
        if ($hasil['warnings']) {
            $pesan .= ' ' . count($hasil['warnings']) . ' catatan perlu dicek.';
        }

        return redirect()->route('admin.neraca-sampah.show', [$laporan, 'tab' => 'input'])
            ->with('success', $pesan)
            ->with('import_summary', $hasil['bulan'])
            ->with('import_errors', $hasil['warnings']);
    }

    public function template(NeracaSampahLaporan $laporan, NeracaSampahTemplate $template)
    {
        $laporan->load('industri');
        $spreadsheet = $template->buat($laporan);
        $nama = 'Template Neraca Sampah - ' . Str::slug($laporan->industri->nama) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $nama, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

        /** Hari terisi dan total kg per bulan per area: [ym][area_id] => ['hari' => n, 'kg' => x] */
    private function ringkasInput(NeracaSampahLaporan $laporan): array
    {
        $rows = DB::table('neraca_sampah_harian')
            ->where('laporan_id', $laporan->id)
            ->selectRaw('substr(tanggal, 1, 7) as ym, area_id, count(*) as hari, sum(organik_kg + anorganik_kg + residu_kg) as kg')
            ->groupBy('ym', 'area_id')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[$r->ym][$r->area_id] = ['hari' => (int) $r->hari, 'kg' => (float) $r->kg];
        }

        return $out;
    }
    
    /** Periode maksimal 12 bulan dan tidak boleh tumpang tindih dengan laporan lain milik industri yang sama */
    private function periksaPeriode(int $industriId, Carbon $mulai, Carbon $selesai, ?int $abaikanId): void
    {
        $jumlahBulan = ($selesai->year * 12 + $selesai->month) - ($mulai->year * 12 + $mulai->month) + 1;
        if ($jumlahBulan > 12) {
            throw ValidationException::withMessages(['periode_mulai' => 'Periode maksimal 12 bulan.']);
        }

        $bentrok = NeracaSampahLaporan::where('industri_id', $industriId)
            ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId))
            ->where('periode_mulai', '<=', $selesai->toDateString())
            ->where('periode_selesai', '>=', $mulai->toDateString())
            ->first();

        if ($bentrok) {
            throw ValidationException::withMessages([
                'periode_mulai' => 'Periode bertabrakan dengan laporan yang sudah ada untuk industri ini ('
                    . $bentrok->periode_mulai->translatedFormat('F Y') . ' – '
                    . $bentrok->periode_selesai->translatedFormat('F Y') . ').',
            ]);
        }
    }

    private function rules(): array
    {
        return [
            'industri_id'        => 'required|exists:industri,id',
            'periode_mulai'      => 'required|date',
            'periode_selesai'    => 'required|date|after_or_equal:periode_mulai',
            'status'             => 'nullable|in:draft,final',
            'kota_ttd'           => 'nullable|string|max:100',
            'tanggal_ttd'        => 'nullable|date',
            'mengetahui_jabatan' => 'nullable|string|max:100',
            'mengetahui_nama'    => 'nullable|string|max:150',
            'mengetahui_nip'     => 'nullable|string|max:50',
            'pembuat_jabatan'    => 'nullable|string|max:100',
            'pembuat_nama'       => 'nullable|string|max:150',
            'pembuat_nip'        => 'nullable|string|max:50',
            'catatan'            => 'nullable|string',
        ];
    }
}