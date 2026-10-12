<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industri;
use App\Models\NeracaSampahLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class IndustriController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $industris = Industri::query()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('alamat', 'like', "%{$q}%")
                  ->orWhere('kecamatan', 'like', "%{$q}%");
            }))
            ->orderByDesc('aktif')
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        // Armada yang sudah terpasang ke industri (semua industri, bukan hanya halaman ini)
        $armadaMap = [];     // industri_id => [armada_id, ...]
        $armadaText = [];    // industri_id => [label, ...]
        $armadaTerpakai = []; // armada_id => industri_id
        $terpasang = DB::table('industri_armada as ia')
            ->join('armadas as a', 'a.id', '=', 'ia.armada_id')
            ->orderBy('a.nama')
            ->get(['ia.industri_id', 'a.id', 'a.nama', 'a.nomor_polisi', 'a.jenis_kendaraan']);

        foreach ($terpasang as $a) {
            $armadaMap[$a->industri_id][] = $a->id;
            $armadaText[$a->industri_id][] = $this->labelArmada($a);
            $armadaTerpakai[$a->id] = $a->industri_id;
        }

        return view('admin.industri.index', [
            'industris'      => $industris,
            'q'              => $q,
            'wilayah'        => $this->wilayah(),
            'armadaList'     => $this->armadaTersedia()
                ->map(fn ($a) => ['id' => $a->id, 'label' => $this->labelArmada($a)])
                ->values(),
            'armadaMap'      => $armadaMap,
            'armadaText'     => $armadaText,
            'armadaTerpakai' => $armadaTerpakai,
        ]);
    }

    /**
     * Daftar wilayah dari master (php artisan wilayah:import): ['Kecamatan A' => ['Desa 1', ...], ...]
     * Kalau master belum diisi, memakai data Lokasi Angkut sebagai cadangan.
     */
    private function wilayah(): array
    {
        if (!Schema::hasTable('wilayah_kecamatan') || !DB::table('wilayah_kecamatan')->exists()) {
            return $this->wilayahDariLokasi();
        }

        $desa = DB::table('wilayah_desa')->orderBy('nama')->get(['kode_kecamatan', 'nama'])->groupBy('kode_kecamatan');
        $out = [];

        foreach (DB::table('wilayah_kecamatan')->orderBy('nama')->get(['kode', 'nama']) as $k) {
            $out[$k->nama] = isset($desa[$k->kode]) ? $desa[$k->kode]->pluck('nama')->all() : [];
        }

        return $out;
    }

    /** Cadangan: wilayah dari data Lokasi Angkut */
    private function wilayahDariLokasi(): array
    {
        $out = [];

        $rows = DB::table('lokasi_angkuts')
            ->whereNotNull('kecamatan')
            ->where('kecamatan', '!=', '')
            ->select('kecamatan', 'desa')
            ->distinct()
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();

        foreach ($rows as $r) {
            $out[$r->kecamatan] ??= [];
            if ($r->desa !== null && $r->desa !== '') {
                $out[$r->kecamatan][] = $r->desa;
            }
        }

        return $out;
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $armadaIds = $this->validatedArmada($request, null);

        DB::transaction(function () use ($data, $armadaIds) {
            $industri = Industri::create($data);
            $this->syncArmada($industri->id, $armadaIds);
        });

        return redirect()->route('admin.industri.index')->with('success', 'Industri ditambahkan');
    }

    public function update(Request $request, Industri $industri)
    {
        $data = $this->validated($request);
        $armadaIds = $this->validatedArmada($request, $industri->id);

        DB::transaction(function () use ($industri, $data, $armadaIds) {
            $industri->update($data);
            $this->syncArmada($industri->id, $armadaIds);
        });

        return redirect()->route('admin.industri.index')->with('success', 'Industri diperbarui');
    }

    public function destroy(Industri $industri)
    {
        // Industri yang sudah punya laporan tidak boleh dihapus, cukup dinonaktifkan
        if (NeracaSampahLaporan::where('industri_id', $industri->id)->exists()) {
            return redirect()->route('admin.industri.index')
                ->with('error', 'Industri sudah punya laporan dan tidak bisa dihapus. Nonaktifkan lewat tombol Ubah.');
        }

        $industri->delete();

        return redirect()->route('admin.industri.index')->with('success', 'Industri dihapus');
    }

    /**
     * Armada yang boleh dikaitkan ke industri: aktif dan bukan armada dinas (tipe dinas_lh, mis. "Kantor DLH").
     */
    private function armadaTersedia()
    {
        return DB::table('armadas')
            ->where('aktif', 1)
            ->where(fn ($q) => $q->whereNull('tipe')->orWhere('tipe', '!=', 'dinas_lh'))
            ->orderBy('nama')
            ->orderBy('nomor_polisi')
            ->get(['id', 'nama', 'nomor_polisi', 'jenis_kendaraan']);
    }

    private function labelArmada($a): string
    {
        $label = $a->nama . ' · ' . ($a->nomor_polisi ?: 'tanpa nopol');

        return $a->jenis_kendaraan ? $label . ' · ' . $a->jenis_kendaraan : $label;
    }

    /** Validasi ulang di server: bukan armada dinas, dan belum dipakai industri lain */
    private function validatedArmada(Request $request, ?int $industriId): array
    {
        $request->validate([
            'armada_ids'   => 'nullable|array',
            'armada_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('armadas', 'id')->where(fn ($q) => $q
                    ->where('aktif', 1)
                    ->where(fn ($w) => $w->whereNull('tipe')->orWhere('tipe', '!=', 'dinas_lh'))),
                Rule::unique('industri_armada', 'armada_id')->where(fn ($q) => $q
                    ->when($industriId, fn ($w) => $w->where('industri_id', '!=', $industriId))),
            ],
        ], [
            'armada_ids.*.exists'   => 'Armada yang dipilih tidak valid.',
            'armada_ids.*.unique'   => 'Ada armada yang sudah terpasang ke industri lain.',
            'armada_ids.*.distinct' => 'Armada dipilih lebih dari sekali.',
        ]);

        return array_map('intval', $request->input('armada_ids', []));
    }

    private function syncArmada(int $industriId, array $armadaIds): void
    {
        DB::table('industri_armada')->where('industri_id', $industriId)->delete();

        if ($armadaIds) {
            $now = now();
            DB::table('industri_armada')->insert(array_map(fn ($id) => [
                'industri_id' => $industriId,
                'armada_id'   => $id,
                'created_at'  => $now,
                'updated_at'  => $now,
            ], $armadaIds));
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:200',
            'alamat'    => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:100',
            'desa'      => 'nullable|string|max:100',
            'telepon'   => 'nullable|string|max:20',
            'aktif'     => 'nullable|boolean',
        ]);

        // Checkbox yang tidak dicentang tidak terkirim
        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}