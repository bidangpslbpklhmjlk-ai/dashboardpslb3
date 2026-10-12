<?php

namespace App\Services;

use App\Models\NeracaSampahArea;
use App\Models\NeracaSampahLaporan;

class NeracaSampahService
{
    /** Matriks kg: $m[area_id]['2025-07'] = [organik, anorganik, residu] (hanya data dalam periode laporan) */
    public function matrix(NeracaSampahLaporan $laporan): array
    {
        $m = [];
        $rows = $laporan->harian()
            ->whereBetween('tanggal', [
                $laporan->periode_mulai->toDateString(),
                $laporan->periode_selesai->toDateString(),
            ])
            ->get(['area_id', 'tanggal', 'organik_kg', 'anorganik_kg', 'residu_kg']);

        foreach ($rows as $r) {
            $ym = $r->tanggal->format('Y-m');
            $m[$r->area_id][$ym] ??= ['organik' => 0, 'anorganik' => 0, 'residu' => 0];
            $m[$r->area_id][$ym]['organik']   += $r->organik_kg;
            $m[$r->area_id][$ym]['anorganik'] += $r->anorganik_kg;
            $m[$r->area_id][$ym]['residu']    += $r->residu_kg;
        }
        return $m;
    }

    /** Hitung turunan (ton) dari tiga angka kg */
    public function hitung(float $organikKg, float $anorganikKg, float $residuKg): array
    {
        $org = $organikKg / 1000;
        $ang = $anorganikKg / 1000;
        $res = $residuKg / 1000;
        $timbulan  = $org + $ang + $res;
        $terkelola = $org + $ang;

        return [
            'organik'          => $org,
            'anorganik'        => $ang,
            'terkelola'        => $terkelola,
            'residu'           => $res,
            'timbulan'         => $timbulan,
            'persen_terkelola' => $timbulan > 0 ? $terkelola / $timbulan : null, // null = tampil "-"
            'persen_residu'    => $timbulan > 0 ? $res / $timbulan : null,
        ];
    }

    /** Sheet "Rekap Neraca": per area, satu periode penuh */
    public function neraca(NeracaSampahLaporan $laporan): array
    {
        $m = $this->matrix($laporan);
        $rows = [];
        $tot = ['organik' => 0, 'anorganik' => 0, 'residu' => 0];

        foreach (NeracaSampahArea::where('aktif', true)->orderBy('urutan')->get() as $area) {
            $s = ['organik' => 0, 'anorganik' => 0, 'residu' => 0];
            foreach ($m[$area->id] ?? [] as $bulan) {
                foreach ($s as $k => $_) $s[$k] += $bulan[$k];
            }
            foreach ($s as $k => $v) $tot[$k] += $v;

            $rows[] = ['area' => $area->nama] + $this->hitung($s['organik'], $s['anorganik'], $s['residu']);
        }

        return [
            'rows'  => $rows,
            'total' => $this->hitung($tot['organik'], $tot['anorganik'], $tot['residu']),
        ];
    }

    /** Sheet "Rekap Sampah terkelola": per bulan, semua area aktif */
    public function rekapBulanan(NeracaSampahLaporan $laporan): array
    {
        $m = $this->matrix($laporan);
        $aktifIds = NeracaSampahArea::where('aktif', true)->pluck('id')->all();
        $rows = [];

        foreach ($laporan->bulanList() as $ym => $tgl) {
            $s = ['organik' => 0, 'anorganik' => 0, 'residu' => 0];
            foreach ($m as $areaId => $perArea) {
                if (!in_array($areaId, $aktifIds)) continue; // samakan dengan tab lain: area nonaktif tidak dihitung
                foreach ($perArea[$ym] ?? [] as $k => $v) $s[$k] += $v;
            }
            $rows[] = ['bulan' => $tgl] + $this->hitung($s['organik'], $s['anorganik'], $s['residu']);
        }
        return $rows;
    }

    /** Sheet "Rekap area": per bulan × area */
    public function rekapArea(NeracaSampahLaporan $laporan): array
    {
        $m = $this->matrix($laporan);
        $areas = NeracaSampahArea::where('aktif', true)->orderBy('urutan')->get();
        $rows = [];

        foreach ($laporan->bulanList() as $ym => $tgl) {
            $row = ['bulan' => $tgl, 'area' => []];
            foreach ($areas as $a) {
                $c = $m[$a->id][$ym] ?? ['organik' => 0, 'anorganik' => 0, 'residu' => 0];
                $row['area'][$a->id] = $this->hitung($c['organik'], $c['anorganik'], $c['residu']);
            }
            $rows[] = $row;
        }
        return ['areas' => $areas, 'rows' => $rows];
    }
}