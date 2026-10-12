<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PungutanReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        $headings = ['Nama Object Retribusi', 'Kategori'];
        foreach ($this->data['tanggalRange'] as $tanggal) {
            $headings[] = $tanggal->format('d/m/Y');
        }
        return $headings;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data['matrix'] as $row) {
            $line = [
                $row['objectRetribusi']->nama,
                $row['objectRetribusi']->kategori->nama ?? '-',
            ];
            foreach ($row['cells'] as $cell) {
                $line[] = $cell['label'];
            }
            $rows[] = $line;
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Rekap Pungutan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}