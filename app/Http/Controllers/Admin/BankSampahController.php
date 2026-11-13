<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BankSampahImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Services\BankSampahRekap;

class BankSampahController extends Controller
{
/** Rekap per kecamatan, bisa dibuka sampai desa dan BSU. Filter tahun dan bulan (opsional). */
public function index(Request $request, BankSampahRekap $rekap)
{
    return view('admin.bank-sampah.index', $rekap->susun($request));
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