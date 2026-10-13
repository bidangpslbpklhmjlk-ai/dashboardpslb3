<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\Industri;
use App\Models\Invoice;
use App\Models\InvoiceObjectRetribusi;
use App\Models\Kunjungan;
use App\Models\NeracaSampahHarian;
use App\Models\NeracaSampahLaporan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $u = auth()->user();

        return view('admin.dashboard', [
            'armada'   => $u->canAccessMenu('armada', 'kerusakan') ? $this->aman(fn () => $this->armada()) : null,
            'rute'     => $u->canAccessMenu('rute-angkut', 'lokasi', 'penugasan') ? $this->aman(fn () => $this->rute()) : null,
            'tpa'      => $u->canAccessMenu('gatepass', 'invoice') ? $this->aman(fn () => $this->tpa()) : null,
            'pungutan' => $u->canAccessMenu('pungutan', 'invoice') ? $this->aman(fn () => $this->pungutan()) : null,
            'industri' => $u->canAccessMenu('industri', 'neraca-sampah') ? $this->aman(fn () => $this->industri()) : null,
            'neraca'   => $u->canAccessMenu('neraca-sampah') ? $this->aman(fn () => $this->neraca()) : null,
        ]);
    }

    /** Satu bagian yang error tidak merusak seluruh dashboard. */
    private function aman(\Closure $fn): ?array
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /** ['2026-05' => 'May 26', ...] enam bulan terakhir */
    private function enamBulan(): array
    {
        $out = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i);
            $out[$m->format('Y-m')] = $m->format('M y');
        }
        return $out;
    }

    private function armada(): array
    {
        $aktif = Armada::where('aktif', true);

        $kondisi = (clone $aktif)
            ->selectRaw("coalesce(kondisi, 'belum_diisi') as k, count(*) as n")
            ->groupBy('k')->pluck('n', 'k')->all();

        $tipe = (clone $aktif)->selectRaw('tipe, count(*) as n')->groupBy('tipe')->pluck('n', 'tipe')->all();

        // ASUMSI: model App\Models\KerusakanArmada, kolom status (selesai = sudah diperbaiki)
        $rusakAktif = \App\Models\KerusakanArmada::where('status', '!=', 'selesai')->count();

        return [
            'total'      => Armada::count(),
            'aktif'      => (clone $aktif)->count(),
            'kondisi'    => $kondisi,
            'tipe'       => $tipe,
            'rusakAktif' => $rusakAktif,
        ];
    }

    private function rute(): array
    {
        return [
            // ASUMSI: model RuteAngkut dan LokasiAngkut ada
            'rute'            => \App\Models\RuteAngkut::count(),
            'lokasi'          => \App\Models\LokasiAngkut::count(),
            // Jadwal bawaan: armada yang sudah punya lokasi (tabel armada_lokasi_angkut)
            'armadaTerjadwal' => DB::table('armada_lokasi_angkut')->distinct()->count('armada_id'),
            'lokasiTercakup'  => DB::table('armada_lokasi_angkut')->distinct()->count('lokasi_angkut_id'),
            // ASUMSI: model Checkin dengan created_at
            'checkinHariIni'  => \App\Models\Checkin::whereDate('created_at', today())->count(),
        ];
    }

    private function tpa(): array
    {
        $bulan = $this->enamBulan();
        $awal  = now()->startOfMonth()->subMonths(5);

        // ASUMSI: gatepass_logs punya created_at (volume_ton sudah dipastikan dari migration)
        $log = DB::table('gatepass_logs')
            ->where('created_at', '>=', $awal)
            ->selectRaw('substr(created_at, 1, 7) as ym, count(*) as trip, sum(volume_ton) as ton')
            ->groupBy('ym')->get()->keyBy('ym');

        $uang = Invoice::where('created_at', '>=', $awal)->where('status', 'paid')
            ->selectRaw('substr(created_at, 1, 7) as ym, sum(total) as total')
            ->groupBy('ym')->pluck('total', 'ym');

        $trip = $ton = $retribusi = [];
        foreach ($bulan as $ym => $label) {
            $trip[]      = (int) ($log[$ym]->trip ?? 0);
            $ton[]       = round((float) ($log[$ym]->ton ?? 0), 2);
            $retribusi[] = (float) ($uang[$ym] ?? 0);
        }

        return [
            'labels'     => array_values($bulan),
            'trip'       => $trip,
            'ton'        => $ton,
            'retribusi'  => $retribusi,
            'unpaidJml'  => Invoice::where('status', 'unpaid')->count(),
            'unpaidNom'  => (float) Invoice::where('status', 'unpaid')->sum('total'),
            'paidBulan'  => (float) Invoice::where('status', 'paid')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
        ];
    }

    private function pungutan(): array
    {
        $bulan = $this->enamBulan();
        $awal  = now()->startOfMonth()->subMonths(5);

        $uang = InvoiceObjectRetribusi::where('created_at', '>=', $awal)->where('status', 'paid')
            ->selectRaw('substr(created_at, 1, 7) as ym, sum(total) as total')
            ->groupBy('ym')->pluck('total', 'ym');

        $nilai = [];
        foreach ($bulan as $ym => $label) {
            $nilai[] = (float) ($uang[$ym] ?? 0);
        }

        $bulanIni = [now()->startOfMonth(), now()->endOfMonth()];

        return [
            'labels'      => array_values($bulan),
            'nilai'       => $nilai,
            'hariIni'     => Kunjungan::whereDate('waktu_kunjungan', today())->count(),
            'selesaiBln'  => Kunjungan::where('status', 'selesai')->whereBetween('waktu_kunjungan', $bulanIni)->count(),
            'batalBln'    => Kunjungan::where('status', 'batal')->whereBetween('waktu_kunjungan', $bulanIni)->count(),
            'belumLunas'  => InvoiceObjectRetribusi::where('status', 'unpaid')->count(),
            'nomBelumLunas' => (float) InvoiceObjectRetribusi::where('status', 'unpaid')->sum('total'),
        ];
    }

    private function industri(): array
    {
        $tahun = now()->year;

        return [
            'total'         => Industri::count(),
            'aktif'         => Industri::where('aktif', true)->count(),
            'punyaLaporan'  => NeracaSampahLaporan::whereYear('periode_mulai', '<=', $tahun)
                ->whereYear('periode_selesai', '>=', $tahun)->distinct()->count('industri_id'),
        ];
    }

    private function neraca(): array
    {
        $bulan = $this->enamBulan();
        $awal  = now()->startOfMonth()->subMonths(5);

        $rows = NeracaSampahHarian::where('tanggal', '>=', $awal->toDateString())
            ->selectRaw('substr(tanggal, 1, 7) as ym, sum(organik_kg) as o, sum(anorganik_kg) as a, sum(residu_kg) as r')
            ->groupBy('ym')->get()->keyBy('ym');

        $o = $a = $r = [];
        foreach ($bulan as $ym => $label) {
            $o[] = round((float) ($rows[$ym]->o ?? 0), 1);
            $a[] = round((float) ($rows[$ym]->a ?? 0), 1);
            $r[] = round((float) ($rows[$ym]->r ?? 0), 1);
        }

        // 5 industri dengan timbulan terbesar tahun ini
        $perLaporan = NeracaSampahHarian::whereBetween('tanggal', [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()])
            ->selectRaw('laporan_id, sum(organik_kg + anorganik_kg + residu_kg) as kg')
            ->groupBy('laporan_id')->pluck('kg', 'laporan_id');

        $top = NeracaSampahLaporan::with('industri')->whereIn('id', $perLaporan->keys())->get()
            ->groupBy('industri_id')
            ->map(fn ($g) => [
                'nama' => $g->first()->industri->nama ?? '-',
                'kg'   => round((float) $g->sum(fn ($l) => $perLaporan[$l->id] ?? 0), 1),
            ])
            ->sortByDesc('kg')->take(5)->values()->all();

        $totalSemua = array_sum($o) + array_sum($a) + array_sum($r);

        return [
            'labels'   => array_values($bulan),
            'organik'  => $o,
            'anorganik'=> $a,
            'residu'   => $r,
            'top'      => $top,
            'pctResidu'=> $totalSemua > 0 ? round(array_sum($r) / $totalSemua * 100, 1) : null,
            'pctOlah'  => $totalSemua > 0 ? round((array_sum($o) + array_sum($a)) / $totalSemua * 100, 1) : null,
            'totalKg'  => round($totalSemua, 1),
        ];
    }
}