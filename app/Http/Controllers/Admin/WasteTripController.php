<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Armada;

class WasteTripController extends Controller
{
    /**
     * Daftar kecamatan Majalengka
     */
   protected array $kecamatanList = [
        'Argapura', 'Banjaran', 'Bantarujeg', 'Cigasong', 'Cikijing',
        'Cingambul', 'Dawuan', 'Jatitujuh', 'Jatiwangi', 'Kadipaten',
        'Kasokandel', 'Kertajati', 'Lemahsugih', 'Leuwimunding', 'Ligung',
        'Maja', 'Majalengka', 'Malausma', 'Palasah', 'Panyingkiran',
        'Rajagaluh', 'Sindang', 'Sindangwangi', 'Sukahaji', 'Sumberjaya',
        'Talaga'
    ];

    /**
     * Daftar desa per kecamatan Majalengka
     * Sumber: Data resmi Pemkab Majalengka
     */
      protected array $desaData = [
        'Argapura' => [
            'Argalingga', 'Argamukti', 'Cibunut', 'Cikaracak', 'Gunungwangi',
            'Haurseah', 'Heubeulisuk', 'Mekarwangi', 'Sadasari', 'Sagara',
            'Sukadana', 'Sukasari Kaler', 'Sukasari Kidul', 'Tejamulya'
        ],
        'Banjaran' => [
            'Banjaran', 'Cimeong', 'Darmalarang', 'Genteng', 'Girimulya',
            'Hegarmanah', 'Kagok', 'Kareo', 'Panyindangan', 'Sangiang',
            'Sindangpala', 'Sunia', 'Suniabaru'
        ],
        'Bantarujeg' => [
            'Babakansari', 'Bantarujeg', 'Cikidang', 'Cimangguhilir', 'Cinambo',
            'Cipeundeuy', 'Gununglarang', 'Haurgeulis', 'Salawangi', 'Silihwangi',
            'Sindanghurip', 'Sukamenak', 'Wadowetan'
        ],
        'Cigasong' => [
            'Baribis', 'Batujaya', 'Karayunan', 'Kawunghilir', 'Kutamanggu',
            'Tajur', 'Tenjolayar', 'Cicenang', 'Cigasong', 'Simpeureum'
        ],
        'Cikijing' => [
            'Bagjasari', 'Banjaransari', 'Cidulang', 'Cikijing', 'Cilancang',
            'Cipulus', 'Cisoka', 'Jagasari', 'Kancana', 'Kasturi',
            'Sindang', 'Sindangpanji', 'Sukamukti', 'Sukasari', 'Sunalari'
        ],
        'Cingambul' => [
            'Cidadap', 'Cikondang', 'Cimanggugirang', 'Cingambul', 'Cintaasih',
            'Ciranjeng', 'Kondangmekar', 'Maniis', 'Muktisari', 'Nagarakembang',
            'Rawa', 'Sedaraja', 'Wangkelang'
        ],
        'Dawuan' => [
            'Balida', 'Baturuyuk', 'Bojongcideres', 'Dawuan', 'Gandu',
            'Genteng', 'Karanganyar', 'Mandapa', 'Pasirmalati', 'Salawana',
            'Sinarjati'
        ],
        'Jatitujuh' => [
            'Babajurang', 'Biyawak', 'Jatiraga', 'Jatitengah', 'Jatitujuh',
            'Pangkalanpari', 'Panongan', 'Panyingkiran', 'Pasindangan', 'Pilangsari',
            'Putridalem', 'Randegan Kulon', 'Randegan Wetan', 'Sumber Kulon',
            'Sumber Wetan'
        ],
        'Jatiwangi' => [
            'Andir', 'Burujul Kulon', 'Burujul Wetan', 'Cibentar', 'Ciborelang',
            'Cicadas', 'Jatisura', 'Jatiwangi', 'Leuweunggede', 'Loji',
            'Mekarsari', 'Pinangraja', 'Sukaraja Kulon', 'Sukaraja Wetan',
            'Surawangi', 'Sutawangi'
        ],
        'Kadipaten' => [
            'Babakan Anyar', 'Cipaku', 'Heuleut', 'Kadipaten', 'Karangsambung',
            'Liangjulang', 'Pagandon'
        ],
        'Kasokandel' => [
            'Gandasari', 'Girimukti', 'Gunungsari', 'Jatimulya', 'Jatisawit',
            'Kasokandel', 'Leuwikidang', 'Ranji Kulon', 'Ranji Wetan', 'Wanajaya'
        ],
        'Kertajati' => [
            'Babakan', 'Bantarjati', 'Kertajati', 'Kertasari', 'Kertawinangun',
            'Mekarjaya', 'Mekarmulya', 'Pakubeureum', 'Palasah', 'Pasiripis',
            'Sahbandar', 'Sukakerta', 'Sukamulya', 'Sukawana'
        ],
        'Lemahsugih' => [
            'Bangbayang', 'Borogojol', 'Cibulan', 'Cigaleuh', 'Cipasung',
            'Kalapadua', 'Kepuh', 'Lemah Putih', 'Margajaya', 'Mekar Wangi',
            'Mekarmulya', 'Padarek', 'Sadawangi', 'Sinargalih', 'Sukajadi',
            'Sukamaju', 'Dayeuhwangi', 'Lemahsugih', 'Cisalak'
        ],
        'Leuwimunding' => [
            'Ciparay', 'Heuleut', 'Karangasem', 'Lame', 'Leuwikujang',
            'Leuwimunding', 'Mindi', 'Mirat', 'Nanggerang', 'Parakan',
            'Parungjaya', 'Patuanan', 'Rajawangi', 'Tanjungsari'
        ],
        'Ligung' => [
            'Ampel', 'Bantarwaru', 'Beber', 'Beusi', 'Buntu', 'Cibogor',
            'Gandawesi', 'Kedungkancana', 'Kedungsari', 'Kertasari', 'Kodasari',
            'Leuweunghapit', 'Leuwiliang Baru', 'Ligung', 'Ligung Lor',
            'Majasari', 'Sukawera', 'Tegalaren', 'Wanasalam'
        ],
        'Maja' => [
            'Anggrawati', 'Banjaran', 'Cengal', 'Cicalung', 'Cieurih', 'Cihaur',
            'Cipicung', 'Maja Selatan', 'Maja Utara', 'Nunuk Baru',
            'Kartabasuki', 'Malongpong', 'Paniis', 'Pasanggrahan', 'Pageraji',
            'Sindangkerta', 'Tegalsari', 'Wanahayu'
        ],
        'Majalengka' => [
            'Cibodas', 'Kawunggirang', 'Kulur', 'Sidamukti',
            'Babakan Jawa', 'Cicurug', 'Cijati', 'Cikasarung',
            'Majalengka Kulon', 'Majalengka Wetan', 'Munjul', 'Sindangkasih',
            'Tarikolot', 'Tonjong'
        ],
        'Malausma' => [
            'Banyusari', 'Buninagara', 'Cimuncang', 'Ciranca', 'Girimukti',
            'Jagamulya', 'Kramat Jaya', 'Lebakwangi', 'Malausma', 'Sukadana',
            'Werasari'
        ],
        'Palasah' => [
            'Buniwangi', 'Cisambeng', 'Enggalwangi', 'Majasuka', 'Palasah',
            'Pasir', 'Sindanghaji', 'Sindangwasa', 'Tarikolot', 'Trajaya',
            'Karamat', 'Waringin', 'Weragati'
        ],
        'Panyingkiran' => [
            'Bantrangsana', 'Bonang', 'Cijurey', 'Jatipamor', 'Jatiserang',
            'Karyamukti', 'Leuwiseeng', 'Pasirmuncang', 'Panyingkiran'
        ],
        'Rajagaluh' => [
            'Babakankareo', 'Cipinang', 'Cisetu', 'Kumbung', 'Pajajar',
            'Payung', 'Rajagaluh Kidul', 'Rajagaluh Lor', 'Rajagaluh',
            'Sadomas', 'Sindangpano', 'Singawada', 'Teja'
        ],
        'Sindang' => [
            'Bayureja', 'Garawastu', 'Gunungkuning', 'Indrakila', 'Pasirayu',
            'Sangkanhurip', 'Sindang'
        ],
        'Sindangwangi' => [
            'Balagedog', 'Bantar Agung', 'Buahkapas', 'Jerukleueut',
            'Lengkongkulon', 'Lengkongwetan', 'Leuwilaja', 'Padaherang',
            'Sindangwangi', 'Ujungberung'
        ],
        'Sukahaji' => [
            'Babakan Manjeti', 'Candrajaya', 'Cikalong', 'Cikeusik', 'Cikoneng',
            'Ciomas', 'Jayi', 'Nanggewer', 'Padahanten', 'Palabuan',
            'Salagedang', 'Sukahaji', 'Tanjungsari'
        ],
        'Sumberjaya' => [
            'Banjaran', 'Bongas Kulon', 'Bongas Wetan', 'Rancaputat', 'Cidenok',
            'Garawangi', 'Gelok Mulya', 'Lojikobong', 'Pancaksuji',
            'Panjalin Kidul', 'Panjalin Lor', 'Paningkiran', 'Parapatan',
            'Sepat', 'Sumberjaya'
        ],
        'Talaga' => [
            'Argasari', 'Campaga', 'Cibeureum', 'Cicanir', 'Cikeusal',
            'Ganeas', 'Gunungmanik', 'Jatipamor', 'Kertahayu', 'Lampuyang',
            'Margamukti', 'Mekarhurip', 'Mekarraharja', 'Salado', 'Sukaperna',
            'Talaga Kulon', 'Talaga Wetan'
        ],
    ];

public function index(Request $request)
{
    $query = WasteTrip::query();

    // === FILTER TANGGAL ===
    if ($request->filled('tanggal_mulai')) {
        $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
    }
    if ($request->filled('tanggal_selesai')) {
        $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
    }

    // === FILTER NOMOR POLISI ===
    if ($request->filled('nomor_polisi')) {
        $query->where('nomor_polisi', $request->nomor_polisi);
    }

    // Sorting default
    $query->orderBy('tanggal', 'desc');

    // === PAGINATION ===
    $perPage = $request->input('per_page', 10);
    $trips = $query->paginate($perPage)->withQueryString();

    // === HITUNG COVERAGE WILAYAH (dari SEMUA data) ===
    $allTrips = WasteTrip::all();
    $desaTercover = collect();
    $kecamatanTercover = collect();

    foreach ($allTrips as $trip) {
        if ($trip->start_desa && $trip->start_kecamatan) {
            $desaTercover->push($trip->start_kecamatan . '|' . $trip->start_desa);
            $kecamatanTercover->push($trip->start_kecamatan);
        }
        foreach ($trip->pickup_points ?? [] as $point) {
            if (($point['desa'] ?? null) && ($point['kecamatan'] ?? null)) {
                $desaTercover->push($point['kecamatan'] . '|' . $point['desa']);
                $kecamatanTercover->push($point['kecamatan']);
            }
        }
        if ($trip->end_desa && $trip->end_kecamatan) {
            $desaTercover->push($trip->end_kecamatan . '|' . $trip->end_desa);
            $kecamatanTercover->push($trip->end_kecamatan);
        }
    }

    $totalDesaTercover = $desaTercover->unique()->count();
    $totalKecamatanTercover = $kecamatanTercover->unique()->count();
    $totalDesaMajalengka = collect($this->desaData)->flatten()->count();
    $kecamatanTercoverList = $kecamatanTercover->unique()->sort()->values();

    // List nomor polisi untuk filter dropdown
    $truckListFilter = WasteTrip::select('nomor_polisi')->distinct()->orderBy('nomor_polisi')->pluck('nomor_polisi');

    // === DATA UNTUK MAP (dari SEMUA data) ===
    $truckColors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];
    $colorIndex = 0;

    $trucksForMap = $allTrips->groupBy('nomor_polisi')->map(function($group) use ($truckColors, &$colorIndex) {
        $first = $group->first();
        $color = $truckColors[$colorIndex % count($truckColors)];
        $colorIndex++;

        return [
            'id' => str_replace(' ', '', $first->nomor_polisi),
            'nopol' => $first->nomor_polisi,
            'color' => $color,
            'start' => [(float)$first->start_lat, (float)$first->start_lng],
            'start_nama' => $first->start_nama ?? 'Titik Awal',
            'start_desa' => $first->start_desa,
            'start_kecamatan' => $first->start_kecamatan,
            'points' => collect($first->pickup_points)->map(function($p, $i) {
                return [
                    'lat' => (float)$p['lat'],
                    'lng' => (float)$p['lng'],
                    'name' => $p['nama'] ?? 'Titik ' . ($i + 1),
                    'desa' => $p['desa'] ?? null,
                    'kecamatan' => $p['kecamatan'] ?? null,
                ];
            })->values()->toArray(),
            'end' => $first->end_lat && $first->end_lng ? [(float)$first->end_lat, (float)$first->end_lng] : null,
            'end_nama' => $first->end_nama ?? 'Titik Akhir',
            'end_desa' => $first->end_desa,
            'end_kecamatan' => $first->end_kecamatan,
            'route_geometry' => $first->route_geometry,
        ];
    })->values();

    return view('admin.waste-trips.index', compact(
        'trips',
        'trucksForMap',
        'totalDesaTercover',
        'totalKecamatanTercover',
        'totalDesaMajalengka',
        'kecamatanTercoverList',
        'truckListFilter',
        'perPage'
    ));
}
 public function create()
{
    return view('admin.waste-trips.create', [
        'kecamatanList' => $this->kecamatanList,
        'desaData'      => $this->desaData,
        'armadas'       => $this->armadaOptions(),
    ]);
}

    public function store(Request $request)
{
    $validated = $request->validate([
        'tanggal' => 'required|date',
        'nomor_polisi' => 'required|string|max:20|exists:armadas,nomor_polisi',
        'jenis_kendaraan' => 'required|string',
        'jam_mulai' => 'required',
        'jam_selesai' => 'required',
        'start_lat' => 'required|numeric',
        'start_lng' => 'required|numeric',
        'start_nama' => 'nullable|string|max:100',
        'start_desa' => 'required|string|max:100',
        'start_kecamatan' => 'required|string|max:50',
        'start_kabupaten' => 'nullable|string|max:50',
        'start_provinsi' => 'nullable|string|max:50',
        'pickup_points' => 'required|array|min:1',
        'pickup_points.*.lat' => 'required|numeric',
        'pickup_points.*.lng' => 'required|numeric',
        'pickup_points.*.nama' => 'nullable|string|max:100',
        'pickup_points.*.desa' => 'required|string|max:100',
        'pickup_points.*.kecamatan' => 'required|string|max:50',
        'pickup_points.*.kabupaten' => 'nullable|string|max:50',
        'pickup_points.*.provinsi' => 'nullable|string|max:50',
        'end_lat' => 'nullable|numeric',
        'end_lng' => 'nullable|numeric',
        'end_nama' => 'nullable|string|max:100',
        'end_desa' => 'required_with:end_lat,end_lng|string|max:100',
        'end_kecamatan' => 'required_with:end_lat,end_lng|string|max:50',
        'end_kabupaten' => 'nullable|string|max:50',
        'end_provinsi' => 'nullable|string|max:50',
    ]);

    $waypoints = [
        [(float)$validated['start_lat'], (float)$validated['start_lng']]
    ];
    foreach ($validated['pickup_points'] as $point) {
        $waypoints[] = [(float)$point['lat'], (float)$point['lng']];
    }
    if (!empty($validated['end_lat']) && !empty($validated['end_lng'])) {
        $waypoints[] = [(float)$validated['end_lat'], (float)$validated['end_lng']];
    }

    $routeData = $this->getRouteFromOSRM($waypoints);

    $pickupPoints = $this->enrichPickupPoints(
        $validated['start_lat'],
        $validated['start_lng'],
        $validated['pickup_points']
    );

    WasteTrip::create([
        'tanggal' => $validated['tanggal'],
        'nomor_polisi' => $validated['nomor_polisi'],
        'jenis_kendaraan' => $validated['jenis_kendaraan'],
        'jam_mulai' => $validated['jam_mulai'],
        'jam_selesai' => $validated['jam_selesai'],
        'start_lat' => $validated['start_lat'],
        'start_lng' => $validated['start_lng'],
        'start_nama' => $validated['start_nama'] ?? null,
        'start_desa' => $validated['start_desa'],
        'start_kecamatan' => $validated['start_kecamatan'],
        'start_kabupaten' => $validated['start_kabupaten'] ?? 'Majalengka',
        'start_provinsi' => $validated['start_provinsi'] ?? 'Jawa Barat',
        'pickup_points' => $pickupPoints,
        'end_lat' => $validated['end_lat'] ?? null,
        'end_lng' => $validated['end_lng'] ?? null,
        'end_nama' => $validated['end_nama'] ?? null,
        'end_desa' => $validated['end_desa'] ?? null,
        'end_kecamatan' => $validated['end_kecamatan'] ?? null,
        'end_kabupaten' => $validated['end_kabupaten'] ?? 'Majalengka',
        'end_provinsi' => $validated['end_provinsi'] ?? 'Jawa Barat',
        'total_jarak' => $routeData['distance'],
        'route_geometry' => $routeData['geometry'],
    ]);

    return redirect()->route('admin.waste-trips.index')
        ->with('success', 'Data perjalanan berhasil ditambahkan');
}

public function edit(WasteTrip $wasteTrip)
{
    $kecamatanList = $this->kecamatanList;
    sort($kecamatanList);

    $desaData = $this->desaData;
    foreach ($desaData as $kec => &$desaList) {
        sort($desaList);
    }
    unset($desaList);

    return view('admin.waste-trips.edit', [
        'wasteTrip'     => $wasteTrip,
        'kecamatanList' => $kecamatanList,
        'desaData'      => $desaData,
        'armadas'       => $this->armadaOptions($wasteTrip->nomor_polisi),
    ]);
}

public function update(Request $request, WasteTrip $wasteTrip)
{
    $validated = $request->validate([
        'tanggal' => 'required|date',
        'nomor_polisi' => [
            'required', 'string', 'max:20',
            function ($attribute, $value, $fail) use ($wasteTrip) {
                if (!Armada::where('nomor_polisi', $value)->exists() && $value !== $wasteTrip->nomor_polisi) {
                    $fail('Nomor polisi tidak terdaftar di data armada.');
                }
            },
        ],
        'jenis_kendaraan' => 'required|string',
        'jam_mulai' => 'required',
        'jam_selesai' => 'required',
        'start_lat' => 'required|numeric',
        'start_lng' => 'required|numeric',
        'start_nama' => 'nullable|string|max:100',
        'start_desa' => 'required|string|max:100',
        'start_kecamatan' => 'required|string|max:50',
        'start_kabupaten' => 'nullable|string|max:50',
        'start_provinsi' => 'nullable|string|max:50',
        'pickup_points' => 'required|array|min:1',
        'pickup_points.*.lat' => 'required|numeric',
        'pickup_points.*.lng' => 'required|numeric',
        'pickup_points.*.nama' => 'nullable|string|max:100',
        'pickup_points.*.desa' => 'required|string|max:100',
        'pickup_points.*.kecamatan' => 'required|string|max:50',
        'pickup_points.*.kabupaten' => 'nullable|string|max:50',
        'pickup_points.*.provinsi' => 'nullable|string|max:50',
        'end_lat' => 'nullable|numeric',
        'end_lng' => 'nullable|numeric',
        'end_nama' => 'nullable|string|max:100',
        'end_desa' => 'required_with:end_lat,end_lng|string|max:100',
        'end_kecamatan' => 'required_with:end_lat,end_lng|string|max:50',
        'end_kabupaten' => 'nullable|string|max:50',
        'end_provinsi' => 'nullable|string|max:50',
    ]);

    $waypoints = [
        [(float)$validated['start_lat'], (float)$validated['start_lng']]
    ];
    foreach ($validated['pickup_points'] as $point) {
        $waypoints[] = [(float)$point['lat'], (float)$point['lng']];
    }
    if (!empty($validated['end_lat']) && !empty($validated['end_lng'])) {
        $waypoints[] = [(float)$validated['end_lat'], (float)$validated['end_lng']];
    }

    $routeData = $this->getRouteFromOSRM($waypoints);

    $pickupPoints = $this->enrichPickupPoints(
        $validated['start_lat'],
        $validated['start_lng'],
        $validated['pickup_points']
    );

    $wasteTrip->update([
        'tanggal' => $validated['tanggal'],
        'nomor_polisi' => $validated['nomor_polisi'],
        'jenis_kendaraan' => $validated['jenis_kendaraan'],
        'jam_mulai' => $validated['jam_mulai'],
        'jam_selesai' => $validated['jam_selesai'],
        'start_lat' => $validated['start_lat'],
        'start_lng' => $validated['start_lng'],
        'start_nama' => $validated['start_nama'] ?? null,
        'start_desa' => $validated['start_desa'],
        'start_kecamatan' => $validated['start_kecamatan'],
        'start_kabupaten' => $validated['start_kabupaten'] ?? 'Majalengka',
        'start_provinsi' => $validated['start_provinsi'] ?? 'Jawa Barat',
        'pickup_points' => $pickupPoints,
        'end_lat' => $validated['end_lat'] ?? null,
        'end_lng' => $validated['end_lng'] ?? null,
        'end_nama' => $validated['end_nama'] ?? null,
        'end_desa' => $validated['end_desa'] ?? null,
        'end_kecamatan' => $validated['end_kecamatan'] ?? null,
        'end_kabupaten' => $validated['end_kabupaten'] ?? 'Majalengka',
        'end_provinsi' => $validated['end_provinsi'] ?? 'Jawa Barat',
        'total_jarak' => $routeData['distance'],
        'route_geometry' => $routeData['geometry'],
    ]);

    return redirect()->route('admin.waste-trips.index')
        ->with('success', 'Data perjalanan berhasil diperbarui');
}

    public function destroy(WasteTrip $wasteTrip)
    {
        $wasteTrip->delete();
        return redirect()->route('admin.waste-trips.index')
            ->with('success', 'Data perjalanan berhasil dihapus');
    }

    // === OSRM INTEGRATION ===
    private function getRouteFromOSRM(array $waypoints): array
    {
        $coords = collect($waypoints)
            ->map(fn($p) => "{$p[1]},{$p[0]}")
            ->join(';');

        $url = "https://router.project-osrm.org/route/v1/driving/{$coords}?overview=full&geometries=geojson";

        try {
            $response = Http::timeout(10)->get($url);
            $data = $response->json();

            if (($data['code'] ?? '') === 'Ok' && !empty($data['routes'])) {
                $route = $data['routes'][0];

                $geometry = collect($route['geometry']['coordinates'])
                    ->map(fn($coord) => [(float)$coord[1], (float)$coord[0]])
                    ->toArray();

                return [
                    'distance' => round($route['distance'] / 1000, 2),
                    'duration' => $route['duration'],
                    'geometry' => $geometry,
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('OSRM failed: ' . $e->getMessage());
        }

        return [
            'distance' => $this->calculateTotalDistanceFallback($waypoints),
            'duration' => 0,
            'geometry' => null,
        ];
    }

    private function calculateTotalDistanceFallback(array $waypoints): float
    {
        $total = 0;
        for ($i = 0; $i < count($waypoints) - 1; $i++) {
            $total += $this->calculateDistance(
                $waypoints[$i][0], $waypoints[$i][1],
                $waypoints[$i + 1][0], $waypoints[$i + 1][1]
            );
        }
        return round($total, 2);
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    private function enrichPickupPoints($startLat, $startLng, $pickupPoints)
    {
        $enriched = [];
        $prevLat = $startLat;
        $prevLng = $startLng;

        foreach ($pickupPoints as $point) {
            $jarak = $this->calculateDistance($prevLat, $prevLng, $point['lat'], $point['lng']);
            $enriched[] = [
                'lat' => $point['lat'],
                'lng' => $point['lng'],
                'nama' => $point['nama'] ?? null,
                'desa' => $point['desa'],
                'kecamatan' => $point['kecamatan'],
                'kabupaten' => $point['kabupaten'] ?? 'Majalengka',
                'provinsi' => $point['provinsi'] ?? 'Jawa Barat',
                'jarak' => round($jarak, 2)
            ];
            $prevLat = $point['lat'];
            $prevLng = $point['lng'];
        }

        return $enriched;
    }
    private function armadaOptions(?string $includeNopol = null)
{
    return Armada::query()
        ->whereNotNull('nomor_polisi')
        ->where(function ($q) use ($includeNopol) {
            $q->where('aktif', true);
            if ($includeNopol) {
                $q->orWhere('nomor_polisi', $includeNopol);
            }
        })
        ->orderBy('nomor_polisi')
        ->get(['id', 'nama', 'nomor_polisi', 'jenis_kendaraan']);
}
}   