<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankSampah;
use App\Services\BsuImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/** Daftar induk BSU (identitas bank sampah). Timbangan ada di BankSampahController. */
class BsuController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->input('q'));

        $daftar = BankSampah::query()
            ->leftJoin('wilayah_kecamatan as k', 'k.kode', '=', 'bank_sampah.kode_kecamatan')
            ->leftJoin('wilayah_desa as d', 'd.kode', '=', 'bank_sampah.kode_desa')
            ->select('bank_sampah.*', 'k.nama as kecamatan_nama', 'd.nama as desa_nama')
            ->when($request->filled('kecamatan'), fn ($q) => $q->where('bank_sampah.kode_kecamatan', $request->kecamatan))
            ->when($cari !== '', fn ($q) => $q->where(function ($w) use ($cari) {
                $w->where('bank_sampah.nama', 'like', "%$cari%")
                  ->orWhere('bank_sampah.ketua', 'like', "%$cari%")
                  ->orWhere('d.nama', 'like', "%$cari%");
            }))
            ->orderByRaw('k.nama is null, k.nama')
            ->orderBy('d.nama')
            ->orderBy('bank_sampah.nama')
            ->paginate(50)
            ->withQueryString();

        return view('admin.bank-sampah.bsu.index', [
            'daftar'    => $daftar,
            'kecamatan' => DB::table('wilayah_kecamatan')->orderBy('nama')->get(),
            'total'     => BankSampah::count(),
        ]);
    }

    public function create()
    {
        return view('admin.bank-sampah.bsu.form', $this->dataForm(new BankSampah(['jenis' => 'desa', 'aktif' => true])));
    }

    public function store(Request $request)
    {
        BankSampah::create($this->siapkan($request));
        return redirect()->route('admin.bank-sampah.bsu.index')->with('sukses', 'BSU ditambahkan.');
    }

    public function edit(BankSampah $bsu)
    {
        return view('admin.bank-sampah.bsu.form', $this->dataForm($bsu));
    }

    public function update(Request $request, BankSampah $bsu)
    {
        $bsu->update($this->siapkan($request, $bsu));
        return redirect()->route('admin.bank-sampah.bsu.index')->with('sukses', 'BSU diperbarui.');
    }

    public function destroy(BankSampah $bsu)
    {
        if ($bsu->timbangan()->exists()) {
            return back()->withErrors(['hapus' => "BSU '{$bsu->nama}' sudah punya data timbangan. Nonaktifkan saja, jangan dihapus."]);
        }
        $bsu->delete();
        return back()->with('sukses', 'BSU dihapus.');
    }

    /** Template impor daftar BSU: satu sheet per kecamatan, format sama dengan yang dibaca BsuImporter. */
public function template()
{
    $header = ['No.', 'Kecamatan', 'Desa', 'Nama BSU', 'Legalitas BSU', 'Tanggal Terbit',
               'Ketua BSU', 'No HP', 'Email', 'Tenaga Kerja Pria', 'Tenaga Kerja Wanita'];
    $lebar  = [6, 20, 22, 30, 30, 18, 26, 18, 28, 12, 12];

    $book = new Spreadsheet();

    // Petunjuk (sheet pertama; importer melewatinya karena tanpa header "No.")
    $info = $book->getActiveSheet();
    $info->setTitle('Petunjuk');
    $info->fromArray([
        ['Petunjuk pengisian'],
        ['1. Isi di sheet kecamatan masing-masing. Satu baris bernomor = satu BSU.'],
        ['2. Kolom No. wajib diisi (1, 2, 3, dst.). Baris tanpa nomor dianggap lanjutan BSU di atasnya.'],
        ['3. Nama atau legalitas yang panjang boleh disambung ke baris di bawahnya, kolom yang sama, tanpa nomor.'],
        ['4. Kecamatan boleh dikosongkan, sistem memakai nama sheet. Desa harus sama dengan sheet "Daftar Desa"; salah ketik kecil dicocokkan otomatis.'],
        ['5. Nama BSU kosong diganti sementara "BSU <nama desa>" dan muncul di daftar "Perlu dicek".'],
        ['6. Tanggal Terbit ditulis seperti 9 Februari 2026 atau 2026-02-09.'],
        ['7. Kolom No HP dan Tanggal Terbit sudah berformat teks. Jangan diubah, supaya angka 0 di depan nomor HP tidak hilang.'],
        ['8. Jangan isi NIK. Kolom itu tidak dibaca dan tidak disimpan.'],
        ['9. Kecamatan yang tidak punya BSU dibiarkan kosong. Impor ulang memperbarui BSU yang sama.'],
    ], null, 'A1');
    $info->getStyle('A1')->getFont()->setBold(true);
    $info->getColumnDimension('A')->setWidth(130);

    // Satu sheet per kecamatan
    $kec = DB::table('wilayah_kecamatan')->orderBy('nama')->get();
    foreach ($kec as $k) {
        $s = $book->createSheet();
        $s->setTitle(mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $k->nama), 0, 31));
        $s->fromArray($header, null, 'A1');
        $s->getStyle('A1:K1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $s->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15803D');
        $s->freezePane('A2');
        foreach ($lebar as $i => $w) $s->getColumnDimensionByColumn($i + 1)->setWidth($w);
        // Teks, agar 0 di depan nomor HP dan tanggal tidak diubah Excel
        $s->getStyle('F2:F500')->getNumberFormat()->setFormatCode('@');
        $s->getStyle('H2:H500')->getNumberFormat()->setFormatCode('@');
    }

    // Acuan ejaan desa
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
    }, 'template-daftar-bsu.xlsx', [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

    public function import(Request $request, BsuImporter $importer)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);

        try {
            $hasil = $importer->import($request->file('file')->getRealPath(), !$request->boolean('pratinjau'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
        return back()->with('hasil_bsu', $hasil);
    }

    // ------------------------------------------------------------------

    private function dataForm(BankSampah $bsu): array
    {
        $desaPerKec = DB::table('wilayah_desa')->orderBy('nama')->get(['kode', 'kode_kecamatan', 'nama'])
            ->groupBy('kode_kecamatan')
            ->map(fn ($g) => $g->map(fn ($d) => ['kode' => $d->kode, 'nama' => $d->nama])->values());

        return [
            'bsu'        => $bsu,
            'kecamatan'  => DB::table('wilayah_kecamatan')->orderBy('nama')->get(),
            'desaPerKec' => $desaPerKec,
        ];
    }

    private function siapkan(Request $request, ?BankSampah $bsu = null): array
    {
        $data = $request->validate([
            'nama'                 => 'required|string|max:150',
            'jenis'                => 'required|in:desa,sekolah,pkk,lainnya',
            'kode_kecamatan'       => 'nullable|exists:wilayah_kecamatan,kode',
            'kode_desa'            => 'nullable|exists:wilayah_desa,kode',
            'ketua'                => 'nullable|string|max:120',
            'no_hp'                => 'nullable|string|max:30',
            'email'                => 'nullable|email|max:150',
            'tenaga_pria'          => 'nullable|integer|min:0|max:65000',
            'tenaga_wanita'        => 'nullable|integer|min:0|max:65000',
            'legalitas_jenis'      => 'nullable|string|max:100',
            'legalitas_keterangan' => 'nullable|string',
            'legalitas_tanggal'    => 'nullable|date',
            'catatan'              => 'nullable|string',
        ]);
        $data['aktif'] = $request->boolean('aktif');

        // Desa menentukan kecamatan
        if (!empty($data['kode_desa'])) {
            $data['kode_kecamatan'] = DB::table('wilayah_desa')->where('kode', $data['kode_desa'])->value('kode_kecamatan');
        }
        $data['kode_kecamatan'] = $data['kode_kecamatan'] ?? null;
        $data['kode_desa']      = $data['kode_desa'] ?? null;

        $kembar = BankSampah::where('nama', $data['nama'])
            ->where('kode_kecamatan', $data['kode_kecamatan'])
            ->where('kode_desa', $data['kode_desa'])
            ->when($bsu, fn ($q) => $q->where('id', '!=', $bsu->id))
            ->exists();
        if ($kembar) {
            throw ValidationException::withMessages(['nama' => 'BSU dengan nama itu sudah ada di desa/kecamatan yang sama.']);
        }
        return $data;
    }
}