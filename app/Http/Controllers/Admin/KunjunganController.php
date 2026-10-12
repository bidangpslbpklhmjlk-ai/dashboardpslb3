<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\ObjectRetribusi;
use App\Models\InvoiceObjectRetribusi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exports\PungutanReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class KunjunganController extends Controller
{
    public function index(Request $request)
    {
        $query = Kunjungan::with(['objectRetribusi.kategori', 'petugas', 'invoice']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('objectRetribusi', fn($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('waktu_kunjungan', $request->tanggal);
        }

        $kunjungan = $query->latest('waktu_kunjungan')->paginate(15)->withQueryString();

        $stats = [
            'hari_ini' => Kunjungan::whereDate('waktu_kunjungan', today())->count(),
            'selesai' => Kunjungan::where('status', 'selesai')->count(),
            'batal' => Kunjungan::where('status', 'batal')->count(),
        ];

        return view('admin.pungutan.index', compact('kunjungan', 'stats'));
    }

    public function create()
    {
        $objectRetribusiList = ObjectRetribusi::with('kategori')->where('status', 'aktif')->orderBy('nama')->get();
        return view('admin.pungutan.create', compact('objectRetribusiList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'object_retribusi_id' => 'required|exists:object_retribusi,id',
            'tanggal_kunjungan' => 'required|date|before_or_equal:today',
            'foto' => 'nullable|image|max:2048',
            'catatan' => 'nullable|string|max:500',
            'status' => 'required|in:selesai,batal',
            'status_pembayaran' => 'required_if:status,selesai|nullable|in:lunas,belum_lunas',
            'nominal' => 'required_if:status,selesai|nullable|integer|min:0',
        ]);

        $objectRetribusi = ObjectRetribusi::with('kategori')->findOrFail($validated['object_retribusi_id']);

            $tagihan = (int) $objectRetribusi->total_tagihan;

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('pungutan', 'public');
        }

        $pesanInvoice = '';

        DB::transaction(function () use ($validated, $objectRetribusi, $tagihan, &$pesanInvoice) {
            $kunjungan = Kunjungan::create([
                'object_retribusi_id' => $objectRetribusi->id,
                'petugas_id' => auth()->id(),
                'waktu_kunjungan' => Carbon::parse($validated['tanggal_kunjungan'])->setTimeFromTimeString(now()->format('H:i:s')),
                'foto' => $validated['foto'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'status' => $validated['status'],
            ]);

            if ($kunjungan->status === 'selesai') {
                $nomorInvoice = 'INV-W-' . now()->format('Ymd') . '-' . str_pad(
                    InvoiceObjectRetribusi::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
                );

                $isLunas = ($validated['status_pembayaran'] ?? null) === 'lunas';

                InvoiceObjectRetribusi::create([
                    'kunjungan_id' => $kunjungan->id,
                    'object_retribusi_id' => $objectRetribusi->id,
                    'nomor_invoice' => $nomorInvoice,
                    'total' => $validated['nominal'] ?? $tagihan,
                    'status' => $isLunas ? 'paid' : 'unpaid',
                    'paid_at' => $isLunas ? now() : null,
                    'barcode' => Str::random(20),
                ]);

                $pesanInvoice = $isLunas ? ' dan invoice diterbitkan (lunas)' : ' dan invoice diterbitkan';
            }
        });

        return redirect()->route('admin.pungutan.index')
            ->with('success', 'Kunjungan berhasil dicatat' . $pesanInvoice);
    }

    public function show(Kunjungan $kunjungan)
    {
        $kunjungan->load(['objectRetribusi.kategori', 'petugas', 'invoice']);
        return view('admin.pungutan.show', compact('kunjungan'));
    }

    public function destroy(Kunjungan $kunjungan)
    {
        if ($kunjungan->foto && Storage::disk('public')->exists($kunjungan->foto)) {
            Storage::disk('public')->delete($kunjungan->foto);
        }

        if ($kunjungan->invoice) {
            $kunjungan->invoice->delete();
        }

        $kunjungan->delete();

        return redirect()->route('admin.pungutan.index')
            ->with('success', 'Kunjungan berhasil dihapus');
    }

    public function report(Request $request)
    {
        $data = $this->buildMatrixData($request);
        return view('admin.pungutan.report', $data);
    }

    public function exportReportExcel(Request $request)
    {
        $data = $this->buildMatrixData($request);
        $filename = 'rekap-pungutan-' . $data['tanggalMulai']->format('Ymd') . '-' . $data['tanggalAkhir']->format('Ymd') . '.xlsx';
        return Excel::download(new PungutanReportExport($data), $filename);
    }

    public function exportReportPdf(Request $request)
    {
        $data = $this->buildMatrixData($request);
        $pdf = Pdf::loadView('admin.pungutan.report-pdf', $data)->setPaper('a4', 'landscape');
        $filename = 'rekap-pungutan-' . $data['tanggalMulai']->format('Ymd') . '-' . $data['tanggalAkhir']->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Bangun data matrix: baris = object retribusi, kolom = tiap tanggal dalam rentang,
     * isi = status kunjungan/pembayaran pada tanggal tsb.
     */
    private function buildMatrixData(Request $request): array
    {
        $tanggalMulai = $request->filled('tanggal_mulai')
            ? Carbon::parse($request->tanggal_mulai)->startOfDay()
            : now()->startOfMonth();

        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : now()->endOfMonth();

        // Batas wajar supaya matrix tidak meledak (maks 90 hari)
        if ($tanggalMulai->diffInDays($tanggalAkhir) > 90) {
            $tanggalAkhir = $tanggalMulai->copy()->addDays(90);
        }

        $objectRetribusiList = ObjectRetribusi::with('kategori')->orderBy('nama')->get();

        $kunjunganList = Kunjungan::with('invoice')
            ->whereBetween('waktu_kunjungan', [$tanggalMulai, $tanggalAkhir])
            ->get()
            ->groupBy(fn ($k) => $k->object_retribusi_id . '_' . $k->waktu_kunjungan->format('Y-m-d'));

        $tanggalRange = collect(CarbonPeriod::create($tanggalMulai->copy()->startOfDay(), $tanggalAkhir->copy()->startOfDay()));

        $matrix = [];
        foreach ($objectRetribusiList as $objectRetribusi) {
            $cells = [];
            foreach ($tanggalRange as $tanggal) {
                $key = $objectRetribusi->id . '_' . $tanggal->format('Y-m-d');
                $kunjunganHariIni = $kunjunganList->get($key)?->first();
                $cells[$tanggal->format('Y-m-d')] = $this->statusCell($kunjunganHariIni);
            }
            $matrix[] = ['objectRetribusi' => $objectRetribusi, 'cells' => $cells];
        }

        // Ringkasan total per status untuk seluruh periode
        $totalLunas = 0;
        $totalBelum = 0;
        $totalBatal = 0;
        foreach ($matrix as $row) {
            foreach ($row['cells'] as $cell) {
                match ($cell['kode']) {
                    'lunas' => $totalLunas++,
                    'belum' => $totalBelum++,
                    'batal' => $totalBatal++,
                    default => null,
                };
            }
        }

        return compact('objectRetribusiList', 'tanggalRange', 'matrix', 'tanggalMulai', 'tanggalAkhir', 'totalLunas', 'totalBelum', 'totalBatal');
    }

    private function statusCell(?Kunjungan $kunjungan): array
    {
        if (!$kunjungan) {
            return ['label' => '-', 'kode' => 'kosong'];
        }

        if ($kunjungan->status === 'batal') {
            return ['label' => 'Batal', 'kode' => 'batal'];
        }

        if (!$kunjungan->invoice) {
            return ['label' => '-', 'kode' => 'kosong'];
        }

        return match ($kunjungan->invoice->status) {
            'paid'      => ['label' => 'Lunas', 'kode' => 'lunas'],
            'cancelled' => ['label' => 'Batal', 'kode' => 'batal'],
            default     => ['label' => 'Belum', 'kode' => 'belum'],
        };
    }
}