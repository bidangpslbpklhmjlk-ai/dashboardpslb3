<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiAngkut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LokasiAngkutController extends Controller
{
    public function index(Request $request)
{
    $query = LokasiAngkut::query();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('alamat', 'like', "%{$search}%")
              ->orWhere('kecamatan', 'like', "%{$search}%")
              ->orWhere('desa', 'like', "%{$search}%");
        });
    }

    if ($request->filled('kecamatan')) {
        $query->where('kecamatan', $request->kecamatan);
    }

    if ($request->has('aktif') && $request->aktif !== '') {
        $query->where('aktif', $request->aktif);
    }

    // Semua titik hasil filter untuk peta (tanpa paginasi)
    $titik = (clone $query)
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderBy('nama')
        ->get()
        ->map(fn ($l) => [
            'id'        => $l->id,
            'nama'      => $l->nama,
            'alamat'    => $l->alamat,
            'desa'      => $l->desa,
            'kecamatan' => $l->kecamatan,
            'lat'       => (float) $l->latitude,
            'lng'       => (float) $l->longitude,
            'aktif'     => (bool) $l->aktif,
            'edit_url'  => route('admin.lokasi.edit', $l),
        ])
        ->values();

    $lokasis = $query->orderBy('nama')->paginate(25)->withQueryString();

    return view('admin.lokasi.index', [
        'lokasis'       => $lokasis,
        'titik'         => $titik,
        'kecamatanList' => $this->kecamatanList(),
    ]);
}

    public function create()
    {
        return view('admin.lokasi.create', $this->wilayahViewData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request), $this->messages());

        $validated['aktif'] = $request->boolean('aktif');

        LokasiAngkut::create($validated);

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi angkut berhasil ditambahkan');
    }

    public function edit(LokasiAngkut $lokasi)
    {
        return view('admin.lokasi.edit', array_merge(
            ['lokasi' => $lokasi],
            $this->wilayahViewData()
        ));
    }

    public function update(Request $request, LokasiAngkut $lokasi)
    {
        $validated = $request->validate($this->rules($request), $this->messages());

        $validated['aktif'] = $request->boolean('aktif');

        $lokasi->update($validated);

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi angkut berhasil diperbarui');
    }

    public function destroy(LokasiAngkut $lokasi)
    {
        $lokasi->delete();

        return redirect()->route('admin.lokasi.index')
            ->with('success', 'Lokasi angkut berhasil dihapus');
    }

    /**
     * Download template CSV untuk import bulk data
     */
    public function template()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_lokasi_angkut.csv"',
        ];

        $columns = ['nama', 'alamat', 'kecamatan', 'desa', 'latitude', 'longitude', 'keterangan', 'aktif'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            // BOM agar Excel membaca UTF-8 dengan benar
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            fputcsv($file, [
                'TPS Pasar Majalengka',
                'Jl. KH. Abdul Halim, Majalengka',
                'Majalengka',
                'Majalengka Wetan',
                '-6.8366',
                '108.2274',
                'Titik pengangkutan utama',
                '1',
            ]);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Import data lokasi dari file CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');

        // Lewati BOM jika ada
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau format tidak valid.');
        }

        $header = array_map('strtolower', array_map('trim', $header));
        $required = ['nama', 'kecamatan', 'desa', 'latitude', 'longitude'];

        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                fclose($handle);
                return back()->with('error', "Kolom wajib '{$col}' tidak ditemukan di CSV.");
            }
        }

        $inserted = 0;
        $failed = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;

            if (count($row) < count($header)) {
                $failed[] = "Baris {$rowNumber}: jumlah kolom tidak sesuai.";
                continue;
            }

            $data = array_combine($header, array_slice($row, 0, count($header)));

            // Samakan penulisan kecamatan/desa dengan data resmi (abaikan huruf besar/kecil)
            [$data['kecamatan'], $data['desa']] = $this->canonicalWilayah(
                $data['kecamatan'] ?? '',
                $data['desa'] ?? ''
            );

            $validator = Validator::make($data, [
                'nama'       => 'required|string|max:150',
                'alamat'     => 'nullable|string|max:255',
                'kecamatan'  => ['required', Rule::in($this->kecamatanList())],
                'desa'       => ['required', Rule::in($this->desaList($data['kecamatan']))],
                'latitude'   => 'required|numeric|between:-90,90',
                'longitude'  => 'required|numeric|between:-180,180',
                'keterangan' => 'nullable|string|max:500',
                'aktif'      => 'nullable|in:0,1,true,false,yes,no',
            ], $this->messages());

            if ($validator->fails()) {
                $failed[] = "Baris {$rowNumber}: " . implode(', ', $validator->errors()->all());
                continue;
            }

            $aktif = in_array(strtolower($data['aktif'] ?? ''), ['1', 'true', 'yes']);

            LokasiAngkut::create([
                'nama'       => trim($data['nama']),
                'alamat'     => trim($data['alamat'] ?? ''),
                'kecamatan'  => $data['kecamatan'],
                'desa'       => $data['desa'],
                'latitude'   => $data['latitude'],
                'longitude'  => $data['longitude'],
                'keterangan' => trim($data['keterangan'] ?? ''),
                'aktif'      => $aktif,
            ]);

            $inserted++;
        }

        fclose($handle);

        $message = "{$inserted} data berhasil diimport.";
        if (count($failed) > 0) {
            $message .= ' ' . count($failed) . ' baris gagal.';
            return redirect()->route('admin.lokasi.index')
                ->with('success', $message)
                ->with('import_errors', $failed);
        }

        return redirect()->route('admin.lokasi.index')->with('success', $message);
    }

    // === HELPER WILAYAH ===

    private function rules(Request $request): array
    {
        return [
            'nama'       => 'required|string|max:150',
            'alamat'     => 'nullable|string|max:255',
            'kecamatan'  => ['required', 'string', 'max:50', Rule::in($this->kecamatanList())],
            'desa'       => ['required', 'string', 'max:100', Rule::in($this->desaList($request->input('kecamatan')))],
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    private function messages(): array
    {
        return [
            'kecamatan.required' => 'Kecamatan wajib dipilih.',
            'kecamatan.in'       => 'Kecamatan tidak dikenali.',
            'desa.required'      => 'Desa/Kelurahan wajib dipilih.',
            'desa.in'            => 'Desa/Kelurahan tidak ada di kecamatan yang dipilih.',
        ];
    }

    private function wilayahViewData(): array
    {
        $desaData = config('wilayah.desa', []);
        ksort($desaData);
        foreach ($desaData as &$list) {
            sort($list);
        }
        unset($list);

        return [
            'kecamatanList' => $this->kecamatanList(),
            'desaData'      => $desaData,
        ];
    }

    private function kecamatanList(): array
    {
        $list = config('wilayah.kecamatan', []);
        sort($list);

        return $list;
    }

    private function desaList(?string $kecamatan): array
    {
        if (blank($kecamatan)) {
            return [];
        }

        return config('wilayah.desa.' . $kecamatan, []);
    }

    /**
     * Cocokkan teks kecamatan/desa dari CSV ke penulisan resmi di config.
     * Kalau tidak cocok, nilai dikembalikan apa adanya supaya gagal di validasi.
     */
    private function canonicalWilayah(string $kecamatan, string $desa): array
    {
        $kecamatan = trim($kecamatan);
        $desa = trim($desa);

        foreach (config('wilayah.kecamatan', []) as $kec) {
            if (strcasecmp($kec, $kecamatan) === 0) {
                $kecamatan = $kec;
                break;
            }
        }

        foreach ($this->desaList($kecamatan) as $d) {
            if (strcasecmp($d, $desa) === 0) {
                $desa = $d;
                break;
            }
        }

        return [$kecamatan, $desa];
    }
}