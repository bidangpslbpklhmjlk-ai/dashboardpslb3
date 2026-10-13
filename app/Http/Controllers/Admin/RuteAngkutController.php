<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\LokasiAngkut;
use App\Models\RuteAngkut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RuteAngkutController extends Controller
{
    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    private const COLORS = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];

    public function index(Request $request)
    {
        $query = RuteAngkut::query()->with(['armada', 'lokasis']);

        if ($request->filled('armada_id')) {
            $query->where('armada_id', $request->armada_id);
        }
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        $rutes = $query->get()
            ->each(fn ($r) => $r->setRelation('lokasis', $r->lokasis->sortBy('pivot.urutan')->values()))
            ->sortBy([
                fn ($a, $b) => strcmp($a->armada->nomor_polisi ?? '', $b->armada->nomor_polisi ?? ''),
                fn ($a, $b) => array_search($a->hari, self::HARI) <=> array_search($b->hari, self::HARI),
            ])
            ->values();

        // Hanya rute aktif yang dihitung untuk peta dan coverage
        $aktif = $rutes->where('aktif', true)->values();

        $desa = collect();
        $kecamatan = collect();
        foreach ($aktif as $rute) {
            foreach ($rute->lokasis as $lokasi) {
                if (($lokasi->jenis ?? 'pengambilan') !== 'pengambilan') continue;
                if ($lokasi->kecamatan) {
                    $kecamatan->push($lokasi->kecamatan);
                    if ($lokasi->desa) {
                        $desa->push($lokasi->kecamatan . '|' . $lokasi->desa);
                    }
                }
            }
        }

        // Satu warna per armada
        $colorByArmada = [];
        foreach ($aktif->pluck('armada_id')->unique()->values() as $i => $armadaId) {
            $colorByArmada[$armadaId] = self::COLORS[$i % count(self::COLORS)];
        }

        $legend = [];
        $routesForMap = $aktif->map(function ($rute) use ($colorByArmada, &$legend) {
            $color = $colorByArmada[$rute->armada_id];
            $nopol = $rute->armada->nomor_polisi ?? '-';
            $legend[$nopol] = $color;

            $geometry = $rute->route_geometry;
            if (is_string($geometry)) {
                $geometry = json_decode($geometry, true);
            }

            return [
                'id'       => $rute->id,
                'nama'     => $rute->nama ?: 'Rute ' . $rute->hari,
                'nopol'    => $nopol,
                'jenis'    => $rute->armada->jenis_kendaraan ?? null,
                'hari'     => $rute->hari,
                'color'    => $color,
                'jarak'    => (float) $rute->total_jarak,
                'geometry' => $geometry,
                'points'   => $rute->lokasis->map(fn ($l) => [
                    'id'        => $l->id,
                    'lat'       => (float) $l->latitude,
                    'lng'       => (float) $l->longitude,
                    'name'      => $l->nama,
                    'desa'      => $l->desa,
                    'kecamatan' => $l->kecamatan,
                ])->values(),
            ];
        })->values();

        return view('admin.rute-angkut.index', [
            'rutes'                 => $rutes,
            'routesForMap'          => $routesForMap,
            'legend'                => $legend,
            'armadas' => Armada::query()
    ->whereNotNull('nomor_polisi')
    ->where(fn ($q) => $q
        ->where('tipe', 'dinas_lh')
        ->orWhereIn('id', RuteAngkut::select('armada_id'))
    )
    ->orderBy('nomor_polisi')
    ->get(['id', 'nomor_polisi', 'jenis_kendaraan']),
            'hariList'              => self::HARI,
            'totalJarak'            => round($aktif->sum('total_jarak'), 2),
            'totalDesaTercover'     => $desa->unique()->count(),
            'totalKecamatanTercover' => $kecamatan->unique()->count(),
            'totalDesaMajalengka'   => collect(config('wilayah.desa', []))->flatten()->count(),
            'totalKecamatan'        => count(config('wilayah.kecamatan', [])),
            'kecamatanTercoverList' => $kecamatan->unique()->sort()->values(),
        ]);
    }

    public function create()
    {
        return view('admin.rute-angkut.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $ids = array_map('intval', $validated['lokasi_ids']);
        $route = $this->buildRoute($ids);

        DB::transaction(function () use ($validated, $request, $ids, $route) {
            $rute = RuteAngkut::create([
                'armada_id'      => $validated['armada_id'],
                'nama'           => $validated['nama'] ?? null,
                'hari'           => $validated['hari'],
                'aktif'          => $request->boolean('aktif', true),
                'total_jarak'    => $route['distance'],
                'route_geometry' => $route['geometry'],
            ]);
            $this->syncLokasi($rute, $ids);
        });

        return redirect()->route('admin.rute-angkut.index')
            ->with('success', 'Rute pengangkutan berhasil ditambahkan');
    }

    public function edit(RuteAngkut $ruteAngkut)
    {
        return view('admin.rute-angkut.edit', array_merge(
            ['rute' => $ruteAngkut],
            $this->formData($ruteAngkut)
        ));
    }

    public function update(Request $request, RuteAngkut $ruteAngkut)
    {
        $validated = $this->validated($request, $ruteAngkut->armada_id);
        $ids = array_map('intval', $validated['lokasi_ids']);
        $route = $this->buildRoute($ids);

        DB::transaction(function () use ($validated, $request, $ids, $route, $ruteAngkut) {
            $ruteAngkut->update([
                'armada_id'      => $validated['armada_id'],
                'nama'           => $validated['nama'] ?? null,
                'hari'           => $validated['hari'],
                'aktif'          => $request->boolean('aktif'),
                'total_jarak'    => $route['distance'],
                'route_geometry' => $route['geometry'],
            ]);
            $this->syncLokasi($ruteAngkut, $ids);
        });

        return redirect()->route('admin.rute-angkut.index')
            ->with('success', 'Rute pengangkutan berhasil diperbarui');
    }

    public function destroy(RuteAngkut $ruteAngkut)
    {
        DB::transaction(function () use ($ruteAngkut) {
            $ruteAngkut->lokasis()->detach();
            $ruteAngkut->delete();
        });

        return redirect()->route('admin.rute-angkut.index')
            ->with('success', 'Rute pengangkutan berhasil dihapus');
    }

    // === HELPER ===

private function validated(Request $request, ?int $currentArmadaId = null): array
{
    return $request->validate([
        'armada_id'      => ['required', Rule::exists('armadas', 'id')->where(
            fn ($q) => $q->where('tipe', 'dinas_lh')->orWhere('id', $currentArmadaId)
        )],
        'nama'           => ['nullable', 'string', 'max:100'],
        'hari'           => ['required', Rule::in(self::HARI)],
        'lokasi_ids'     => ['required', 'array', 'min:1'],
        'lokasi_ids.*'   => ['integer', 'exists:lokasi_angkuts,id'],
    ], [
        'armada_id.exists'    => 'Armada harus bertipe Dinas LH.',
        'lokasi_ids.required' => 'Pilih minimal satu lokasi pengambilan.',
        'lokasi_ids.min'      => 'Pilih minimal satu lokasi pengambilan.',
        'lokasi_ids.*.exists' => 'Ada lokasi yang tidak ditemukan. Muat ulang halaman lalu coba lagi.',
    ]);
}

    private function formData(?RuteAngkut $rute = null): array
    {
        if ($rute) {
            $rute->load('lokasis');
        }

        $selectedIds = collect(old('lokasi_ids', $rute
            ? $rute->lokasis->sortBy('pivot.urutan')->pluck('id')->all()
            : []))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();


$lokasiData = LokasiAngkut::query()
    ->where(fn ($q) => $q->where('aktif', true)->orWhereIn('id', $selectedIds))
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->orderBy('nama')
    ->get()
    ->map(fn ($l) => [
        'id'        => $l->id,
        'nama'      => $l->nama,
        'jenis'     => $l->jenis, 
        'desa'      => $l->desa,
        'kecamatan' => $l->kecamatan,
        'lat'       => (float) $l->latitude,
        'lng'       => (float) $l->longitude,
    ])
    ->values();

        $kecamatanList = config('wilayah.kecamatan', []);
        sort($kecamatanList);

        return [
            'armadas'       => $this->armadaOptions($rute?->armada_id),
            'lokasiData'    => $lokasiData,
            'selectedIds'   => $selectedIds,
            'hariList'      => self::HARI,
            'kecamatanList' => $kecamatanList,
        ];
    }

private function armadaOptions(?int $includeId = null)
{
    return Armada::query()
        ->whereNotNull('nomor_polisi')
        ->where(function ($q) use ($includeId) {
            $q->where(fn ($d) => $d->where('tipe', 'dinas_lh')->where('aktif', true));
            // saat edit, armada rute ini tetap tampil walau bukan Dinas LH
            if ($includeId) {
                $q->orWhere('id', $includeId);
            }
        })
        ->orderBy('nomor_polisi')
        ->get(['id', 'nama', 'nomor_polisi', 'jenis_kendaraan']);
}

private function lokasiDipakai(?int $exceptRuteId = null): array
{
    $rel   = (new RuteAngkut)->lokasis();
    $rutes = (new RuteAngkut)->getTable();

    return DB::table($rel->getTable())
        ->join($rutes, "{$rutes}.id", '=', $rel->getQualifiedForeignPivotKeyName())
        ->where("{$rutes}.aktif", true)
        ->when($exceptRuteId, fn ($q) => $q->where("{$rutes}.id", '!=', $exceptRuteId))
        ->pluck($rel->getQualifiedRelatedPivotKeyName())
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();
}

private function syncLokasi(RuteAngkut $rute, array $ids): void
{
    $rute->lokasis()->detach();

    foreach (array_values($ids) as $i => $id) {
        $rute->lokasis()->attach($id, ['urutan' => $i + 1]);
    }
}

    /**
     * Hitung jarak dan garis rute lewat OSRM sesuai urutan lokasi.
     */
    private function buildRoute(array $ids): array
    {
        $lokasis = LokasiAngkut::whereIn('id', $ids)->get()->keyBy('id');

        $waypoints = [];
        foreach ($ids as $id) {
            if ($l = $lokasis->get($id)) {
                $waypoints[] = [(float) $l->latitude, (float) $l->longitude];
            }
        }

        if (count($waypoints) < 2) {
            return ['distance' => 0, 'geometry' => null];
        }

        $coords = collect($waypoints)->map(fn ($p) => "{$p[1]},{$p[0]}")->join(';');
        $url = "https://router.project-osrm.org/route/v1/driving/{$coords}?overview=full&geometries=geojson";

        try {
            $data = Http::timeout(10)->get($url)->json();

            if (($data['code'] ?? '') === 'Ok' && !empty($data['routes'])) {
                $route = $data['routes'][0];

                return [
                    'distance' => round($route['distance'] / 1000, 2),
                    'geometry' => collect($route['geometry']['coordinates'])
                        ->map(fn ($c) => [(float) $c[1], (float) $c[0]])
                        ->toArray(),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('OSRM gagal: ' . $e->getMessage());
        }

        // Cadangan: jarak garis lurus (haversine), tanpa geometri
        $total = 0;
        for ($i = 0; $i < count($waypoints) - 1; $i++) {
            $total += $this->haversine($waypoints[$i], $waypoints[$i + 1]);
        }

        return ['distance' => round($total, 2), 'geometry' => null];
    }

    private function haversine(array $a, array $b): float
    {
        $dLat = deg2rad($b[0] - $a[0]);
        $dLng = deg2rad($b[1] - $a[1]);
        $h = sin($dLat / 2) ** 2 + cos(deg2rad($a[0])) * cos(deg2rad($b[0])) * sin($dLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($h), sqrt(1 - $h));
    }
}