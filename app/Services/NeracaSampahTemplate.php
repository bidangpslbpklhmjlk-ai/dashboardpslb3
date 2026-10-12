<?php

namespace App\Services;

use App\Models\NeracaSampahArea;
use App\Models\NeracaSampahLaporan;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Membuat workbook kosong yang susunannya persis dibaca NeracaSampahImporter.
 * Satu sheet per bulan dalam periode laporan, nama sheet = nama bulan.
 *
 * PENTING: AREA_KOLOM, BARIS_AWAL, BARIS_AKHIR dan sel B4 harus sama dengan NeracaSampahImporter.
 * Kalau susunan importer diubah, ubah juga di sini.
 */
class NeracaSampahTemplate
{
    private const AREA_KOLOM = [
        'kantor' => 3,   // C
        'parkir' => 7,   // G
        'tunggu' => 11,  // K
        'makan'  => 15,  // O
        'kapal'  => 19,  // S
        'lain'   => 23,  // W
    ];

    private const BARIS_AWAL  = 8;   // tanggal 1
    private const BARIS_AKHIR = 38;  // tanggal 31

    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function buat(NeracaSampahLaporan $laporan): Spreadsheet
    {
        $namaArea = NeracaSampahArea::whereIn('kode', array_keys(self::AREA_KOLOM))->pluck('nama', 'kode');

        $spreadsheet = new Spreadsheet();
        $pertama = true;

        foreach ($laporan->bulanList() as $awal) {
            $ws = $pertama ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $pertama = false;

            $ws->setTitle(self::BULAN[$awal->month]);
            $this->isiSheet($ws, $laporan, $awal, $namaArea);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function isiSheet(Worksheet $ws, NeracaSampahLaporan $laporan, Carbon $awal, $namaArea): void
    {
        $ws->setCellValue('B1', 'LOG BOOK PENGELOLAAN SAMPAH (kg/hari)');
        $ws->setCellValue('B2', $laporan->industri->nama . ' - ' . self::BULAN[$awal->month] . ' ' . $awal->year);
        $ws->getStyle('B1')->getFont()->setBold(true)->setSize(13);

        // Kolom tanggal. Sel B4 harus berisi "Tanggal" (dicek importer)
        $ws->setCellValue('B4', 'Tanggal');
        $ws->mergeCells('B4:B7');
        $ws->getColumnDimension('A')->setWidth(3);
        $ws->getColumnDimension('B')->setWidth(10);

        foreach (self::AREA_KOLOM as $kode => $kolom) {
            $c0 = Coordinate::stringFromColumnIndex($kolom);
            $c3 = Coordinate::stringFromColumnIndex($kolom + 3);

            $ws->setCellValue($c0 . '6', $namaArea[$kode] ?? $kode);
            $ws->mergeCells("{$c0}6:{$c3}6");

            // Judul kolom dicek importer: harus memuat organik, anorganik, residu
            $ws->setCellValue(Coordinate::stringFromColumnIndex($kolom) . '7', 'Organik Terpilah');
            $ws->setCellValue(Coordinate::stringFromColumnIndex($kolom + 1) . '7', 'Anorganik Terpilah');
            $ws->setCellValue(Coordinate::stringFromColumnIndex($kolom + 2) . '7', 'Residu');
            $ws->setCellValue(Coordinate::stringFromColumnIndex($kolom + 3) . '7', 'Total');

            for ($i = 0; $i < 4; $i++) {
                $ws->getColumnDimension(Coordinate::stringFromColumnIndex($kolom + $i))->setWidth(13);
            }

            for ($r = self::BARIS_AWAL; $r <= self::BARIS_AKHIR; $r++) {
                $hari = $r - self::BARIS_AWAL + 1;
                if (!checkdate($awal->month, $hari, $awal->year)) {
                    continue;
                }
                $a = Coordinate::stringFromColumnIndex($kolom) . $r;
                $b = Coordinate::stringFromColumnIndex($kolom + 2) . $r;
                $ws->setCellValue(Coordinate::stringFromColumnIndex($kolom + 3) . $r, "=SUM({$a}:{$b})");
            }

            // Baris total per kolom (di bawah area data, tidak dibaca importer)
            $barisTotal = self::BARIS_AKHIR + 1;
            for ($i = 0; $i < 4; $i++) {
                $col = Coordinate::stringFromColumnIndex($kolom + $i);
                $ws->setCellValue($col . $barisTotal, "=SUM({$col}" . self::BARIS_AWAL . ":{$col}" . self::BARIS_AKHIR . ')');
            }
        }

        // Nomor tanggal hanya untuk hari yang ada di bulan ini
        for ($r = self::BARIS_AWAL; $r <= self::BARIS_AKHIR; $r++) {
            $hari = $r - self::BARIS_AWAL + 1;
            if (checkdate($awal->month, $hari, $awal->year)) {
                $ws->setCellValue('B' . $r, $hari);
            }
        }
        $ws->setCellValue('B' . (self::BARIS_AKHIR + 1), 'Total');

        // Tampilan
        $kolomAkhir = Coordinate::stringFromColumnIndex(max(self::AREA_KOLOM) + 3);
        $header = $ws->getStyle("B4:{$kolomAkhir}7");
        $header->getFont()->setBold(true);
        $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        $header->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        $ws->getStyle("B4:{$kolomAkhir}" . (self::BARIS_AKHIR + 1))->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $ws->getStyle('B' . (self::BARIS_AKHIR + 1) . ":{$kolomAkhir}" . (self::BARIS_AKHIR + 1))->getFont()->setBold(true);
        $ws->getStyle('C' . self::BARIS_AWAL . ":{$kolomAkhir}" . (self::BARIS_AKHIR + 1))
            ->getNumberFormat()->setFormatCode('#,##0.###');

        $ws->freezePane('C' . self::BARIS_AWAL);
    }
}