<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NeracaSampahArea;

class NeracaSampahAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
    ['kantor',  'Area Kantor'],
    ['parkir',  'Area Tempat Parkir/Taman/Jalan'],
    ['tunggu',  'Area Ruang Tunggu'],
    ['makan',   'Area Tempat Makan'],
    ['kapal',   'Sampah Kapal (Khusus Pelabuhan)'],
    ['lain',    'Area Lain (Area Produksi & Gudang)'],
];
foreach ($areas as $i => [$kode, $nama]) {
    NeracaSampahArea::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'urutan' => $i + 1]);
}
    }
}
