<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\KategoriObjectRetribusi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArmadaController extends Controller
{
    /** Kelompok kategori yang boleh dipilih sebagai tarif armada */
    private const KELOMPOK_TARIF = [
        'Pembuangan Sampah Langsung ke TPA',
        'Pembuangan Sampah Langsung ke TPSS',
    ];

    private function validationRules(?Armada $armada = null): array
    {
        $uniqueNopol = $armada
            ? 'unique:armadas,nomor_polisi,' . $armada->id
            : 'unique:armadas,nomor_polisi';

        return [
            'nama' => 'required|string|max:150',
            'tipe' => 'required|in:' . implode(',', array_keys(Armada::TIPE)),
            'pembayaran' => 'required|in:gratis,bayar',
            'kategori_object_retribusi_id' => 'nullable|required_if:pembayaran,bayar|exists:kategori_object_retribusi,id',
            'nomor_polisi' => 'nullable|string|max:20|' . $uniqueNopol,
            'jenis_kendaraan' => 'nullable|string|max:50',
            'tahun_pengadaan' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'kondisi' => 'nullable|in:baik,rusak_ringan,rusak_berat,tidak_beroperasi',
            'foto' => 'nullable|image|max:2048',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
        ];
    }

    private function normalizeData(array $data): array
    {
        $cfg = Armada::TIPE[$data['tipe'] ?? ''] ?? [];

        // Tipe gratis otomatis tanpa pembayaran
        if (!empty($cfg['gratis'])) {
            $data['pembayaran'] = 'gratis';
        }

        // Tahun & kondisi hanya untuk tipe aset (Dinas LH)
        if (empty($cfg['aset'])) {
            $data['tahun_pengadaan'] = null;
            $data['kondisi'] = null;
        }

        // Tarif hanya relevan kalau statusnya "bayar"
        if (($data['pembayaran'] ?? '') === 'gratis') {
            $data['kategori_object_retribusi_id'] = null;
        }

        if (!empty($data['nomor_polisi'])) {
            $data['nomor_polisi'] = strtoupper(trim($data['nomor_polisi']));
        }

        return $data;
    }

    private function handleFotoUpload(Request $request, ?Armada $armada = null): ?string
    {
        if (!$request->hasFile('foto')) return null;

        if ($armada && $armada->foto && Storage::disk('public')->exists($armada->foto)) {
            Storage::disk('public')->delete($armada->foto);
        }
        return $request->file('foto')->store('armada', 'public');
    }

    private function jsonResponse(bool $success, string $message, $data = null, int $status = 200)
    {
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) $response['data'] = $data;
        return response()->json($response, $status);
    }

    private function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    public function index(Request $request)
    {
        $query = Armada::with(['kategori']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nomor_polisi', 'like', "%{$search}%")
                  ->orWhere('jenis_kendaraan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipe')) $query->where('tipe', $request->tipe);
        if ($request->filled('pembayaran')) $query->where('pembayaran', $request->pembayaran);
        if ($request->has('aktif') && $request->aktif !== '') $query->where('aktif', $request->aktif);
        if ($request->filled('kondisi')) $query->where('kondisi', $request->kondisi);

        $armadas = $query->orderBy('nama')->paginate(25)->withQueryString();

        // Statistik dari SELURUH data, bukan hanya halaman aktif
        $total = Armada::count();
        $dinasLh = Armada::where('tipe', Armada::TIPE_DINAS_LH)->count();

        $stats = [
            'total'     => $total,
            'aktif'     => Armada::where('aktif', true)->count(),
            'dinas_lh'  => $dinasLh,
            'non_dinas' => $total - $dinasLh,
        ];

        // Pilihan tarif: kategori TPA & TPSS, dikelompokkan untuk <optgroup>
        $tarifList = KategoriObjectRetribusi::whereIn('kelompok', self::KELOMPOK_TARIF)
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get()
            ->groupBy('kelompok');

        return view('admin.armada.index', compact('armadas', 'tarifList', 'stats'));
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->validationRules());

            $validated['aktif'] = true;
            $validated = $this->normalizeData($validated);

            $fotoPath = $this->handleFotoUpload($request);
            if ($fotoPath) $validated['foto'] = $fotoPath;

            $armada = Armada::create($validated);

            if ($this->isAjax($request)) {
                return $this->jsonResponse(true, 'Armada berhasil ditambahkan!', $armada);
            }

            return redirect()->route('admin.armada.index')->with('success', 'Armada berhasil ditambahkan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($this->isAjax($request)) {
                return $this->jsonResponse(false, 'Validasi gagal', $e->errors(), 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            if ($this->isAjax($request)) {
                return $this->jsonResponse(false, 'Gagal menyimpan: ' . $e->getMessage(), null, 500);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Armada $armada)
    {
        $armada->loadCount(['gatepassLogs', 'invoices']);
        $armada->load('kategori');

        return response()->json([
            'success' => true,
            'data' => $armada,
        ]);
    }

    public function update(Request $request, Armada $armada)
    {
        try {
            $rules = $this->validationRules($armada);
            $rules['aktif'] = 'sometimes|boolean';

            $validated = $request->validate($rules);

            $validated['aktif'] = $request->boolean('aktif');
            $validated = $this->normalizeData($validated);

            $fotoPath = $this->handleFotoUpload($request, $armada);
            if ($fotoPath) $validated['foto'] = $fotoPath;

            $armada->update($validated);

            if ($this->isAjax($request)) {
                return $this->jsonResponse(true, 'Data berhasil diperbarui!', $armada->fresh());
            }
            return redirect()->route('admin.armada.index')->with('success', 'Data berhasil diperbarui');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($this->isAjax($request)) {
                return $this->jsonResponse(false, 'Validasi gagal', $e->errors(), 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            if ($this->isAjax($request)) {
                return $this->jsonResponse(false, 'Gagal memperbarui: ' . $e->getMessage(), null, 500);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, Armada $armada)
    {
        try {
            if ($armada->gatepassLogs()->count() > 0) {
                $message = 'Tidak dapat menghapus. Armada masih terdaftar di gatepass.';
                if ($this->isAjax($request)) return $this->jsonResponse(false, $message, null, 422);
                return redirect()->back()->with('error', $message);
            }

            if ($armada->invoices()->count() > 0) {
                $message = 'Tidak dapat menghapus. Armada masih memiliki invoice.';
                if ($this->isAjax($request)) return $this->jsonResponse(false, $message, null, 422);
                return redirect()->back()->with('error', $message);
            }

            if ($armada->foto && Storage::disk('public')->exists($armada->foto)) {
                Storage::disk('public')->delete($armada->foto);
            }

            $armada->delete();

            if ($this->isAjax($request)) {
                return $this->jsonResponse(true, 'Data berhasil dihapus');
            }
            return redirect()->route('admin.armada.index')->with('success', 'Data berhasil dihapus');

        } catch (\Exception $e) {
            if ($this->isAjax($request)) {
                return $this->jsonResponse(false, 'Gagal menghapus: ' . $e->getMessage(), null, 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}