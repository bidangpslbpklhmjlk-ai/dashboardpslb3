<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BankSampahImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BankSampahController extends Controller
{
/** Rekap per kecamatan, bisa dibuka sampai desa dan BSU. Filter tahun dan bulan (opsional). */
public function index(Request $request)
{
    $tahun = (int) $request->input('tahun', 2026);
    $bulan = $request->filled('bulan') ? max(1, min(12, (int) $request->input('bulan'))) : null;

    // Berat per BSU (id => kg) untuk periode terpilih
    $timbangan = DB::table('bank_sampah_timbangan as t')
        ->where('t.tahun', $tahun)
        ->when($bulan, fn ($q) => $q->where('t.bulan', $bulan))
        ->groupBy('t.bank_sampah_id')
        ->selectRaw('t.bank_sampah_id, SUM(t.berat_kg) as kg')
        ->pluck('kg', 'bank_sampah_id');

    $bsu = DB::table('bank_sampah')->orderBy('nama')
        ->get(['id', 'nama', 'jenis', 'aktif', 'kode_kecamatan', 'kode_desa'])
        ->map(function ($b) use ($timbangan) {
            $b->kg = (float) ($timbangan[$b->id] ?? 0);
            return $b;
        });

    $bsuPerDesa = $bsu->whereNotNull('kode_desa')->groupBy('kode_desa');
    $unitPerKec = $bsu->whereNull('kode_desa')->whereNotNull('kode_kecamatan')->groupBy('kode_kecamatan');
    $desaPerKec = DB::table('wilayah_desa')->orderBy('nama')
        ->get(['kode', 'kode_kecamatan', 'nama'])->groupBy('kode_kecamatan');

    $rekap = DB::table('wilayah_kecamatan')->orderBy('nama')->get()
        ->map(function ($k) use ($desaPerKec, $bsuPerDesa, $unitPerKec) {
            $desa = ($desaPerKec[$k->kode] ?? collect())->map(function ($d) use ($bsuPerDesa) {
                $daftar = $bsuPerDesa[$d->kode] ?? collect();
                $kg     = (float) $daftar->sum('kg');
                return (object) [
                    'kode'       => $d->kode,
                    'nama'       => $d->nama,
                    'bsu'        => $daftar->values(),
                    'jumlah_bsu' => $daftar->where('aktif', true)->count(),
                    'kg'         => $kg,
                    'aktif'      => $kg > 0,
                ];
            })->values();

            $unit   = ($unitPerKec[$k->kode] ?? collect())->values();
            $aktif  = $desa->where('aktif', true)->count();
            $kgDesa = (float) $desa->sum('kg');
            $kgUnit = (float) $unit->sum('kg');

            return (object) [
                'kode'        => $k->kode,
                'nama'        => $k->nama,
                'desa'        => $desa,
                'unit'        => $unit,
                'jumlah_desa' => $desa->count(),
                'jumlah_bsu'  => $desa->sum('jumlah_bsu') + $unit->where('aktif', true)->count(),
                'desa_aktif'  => $aktif,
                'desa_belum'  => $desa->count() - $aktif,
                'persen'      => $desa->count() ? (int) round($aktif / $desa->count() * 100) : 0,
                'kg_desa'     => $kgDesa,
                'kg_unit'     => $kgUnit,
                'kg_total'    => $kgDesa + $kgUnit,
            ];
        });

    // Unit tingkat kabupaten (tanpa kecamatan dan desa), mis. PKK Kabupaten
    $kabupaten = $bsu->whereNull('kode_kecamatan')->whereNull('kode_desa')->sortBy('nama')->values();

    $total = (object) [
        'desa'      => (float) $rekap->sum('kg_desa'),
        'unit_kec'  => (float) $rekap->sum('kg_unit'),
        'kabupaten' => (float) $kabupaten->sum('kg'),
    ];
    $total->semua = $total->desa + $total->unit_kec + $total->kabupaten;

    $bsuAktif = $bsu->where('aktif', true)->count();

    // Tren 12 bulan untuk tahun terpilih (tidak terpengaruh filter bulan)
    $perBulan = DB::table('bank_sampah_timbangan')->where('tahun', $tahun)
        ->groupBy('bulan')->selectRaw('bulan, SUM(berat_kg) as kg')->pluck('kg', 'bulan');
    $tren = collect(range(1, 12))->map(fn ($m) => (float) ($perBulan[$m] ?? 0));

    return view('admin.bank-sampah.index', compact('rekap', 'tahun', 'bulan', 'total', 'kabupaten', 'bsuAktif', 'tren'));
}

    /** Unduh template Excel untuk impor. Sheet "Detail Data" yang dibaca importer. */
    public function template()
    {
        $book = new Spreadsheet();

        // --- Detail Data ---
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Detail Data');
        $header = ['Kecamatan', 'Desa', 'Nama Bank Sampah',
                   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $sheet->fromArray($header, null, 'A1');
        $sheet->getStyle('A1:O1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:O1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15803D');
        $sheet->freezePane('A2');
        foreach (range('A', 'C') as $c) $sheet->getColumnDimension($c)->setWidth($c === 'C' ? 34 : 22);
        foreach (range('D', 'O') as $c) $sheet->getColumnDimension($c)->setWidth(12);
        $sheet->getStyle('D2:O1000')->getNumberFormat()->setFormatCode('#,##0.##');

        // --- Petunjuk ---
        $info = $book->createSheet();
        $info->setTitle('Petunjuk');
        $baris = [
            ['Petunjuk pengisian'],
            ['1. Isi sheet "Detail Data". Sheet lain tidak dibaca saat impor.'],
            ['2. Satu baris = satu bank sampah. Satu desa boleh punya beberapa baris.'],
            ['3. Kecamatan dan Desa harus sama dengan sheet "Daftar Desa". Kecamatan boleh dikosongkan pada baris di bawahnya jika sama dengan baris sebelumnya.'],
            ['4. Nama Bank Sampah boleh dikosongkan; sistem memakai nama desa.'],
            ['5. Berat dalam kg, tanpa satuan. Bulan tanpa data dikosongkan atau diisi "-" (bukan 0).'],
            ['6. Jangan menambah baris Jumlah/Total atau subtotal. Total dihitung sistem.'],
            ['7. Unit non-desa (sekolah, PKK) ditulis di kolom Desa; akan diimpor tanpa desa dan muncul di daftar "Perlu dicek".'],
            ['8. Tahun diisi di form impor, bukan di file ini.'],
        ];
        $info->fromArray($baris, null, 'A1');
        $info->getStyle('A1')->getFont()->setBold(true);
        $info->getColumnDimension('A')->setWidth(120);

        // --- Daftar Desa (acuan ejaan, dari tabel wilayah) ---
        $ref = $book->createSheet();
        $ref->setTitle('Daftar Desa');
        $ref->fromArray(['Kecamatan', 'Desa'], null, 'A1');
        $ref->getStyle('A1:B1')->getFont()->setBold(true);
        $rows = DB::table('wilayah_desa as d')
            ->join('wilayah_kecamatan as k', 'k.kode', '=', 'd.kode_kecamatan')
            ->orderBy('k.nama')->orderBy('d.nama')
            ->get(['k.nama as kecamatan', 'd.nama as desa'])
            ->map(fn ($r) => [$r->kecamatan, $r->desa])->all();
        $ref->fromArray($rows, null, 'A2');
        $ref->getColumnDimension('A')->setWidth(22);
        $ref->getColumnDimension('B')->setWidth(26);

        $book->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 'template-bank-sampah.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, BankSampahImporter $importer)
    {
        $request->validate([
            'file'  => 'required|file|mimes:xlsx,xls|max:10240',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        // pratinjau=1 -> hitung dan laporkan saja, tanpa menyimpan
        try {
            $hasil = $importer->import(
                $request->file('file')->getRealPath(),
                (int) $request->tahun,
                !$request->boolean('pratinjau')
            );
        } catch (\RuntimeException $e) {    
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('hasil_import', $hasil);
    }
}