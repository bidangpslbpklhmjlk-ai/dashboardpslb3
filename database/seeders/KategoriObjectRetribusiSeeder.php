<?php

namespace Database\Seeders;

use App\Models\KategoriObjectRetribusi;
use Illuminate\Database\Seeder;

class KategoriObjectRetribusiSeeder extends Seeder
{
    /**
     * Sumber: Pengumuman Tarif Retribusi Jasa Umum (Pelayanan Kebersihan),
     * Perda No. 7 Tahun 2023, dikeluarkan 2 Januari 2024.
     * Baris: [kode, nama, tarif, satuan]
     */
    private const DATA = [
        'Perusahaan Industri' => [
            ['1.a', 'Industri Besar', 350000, 'angkut'],
            ['1.b', 'Industri Menengah', 250000, 'angkut'],
            ['1.c', 'Industri Kecil', 200000, 'angkut'],
            ['1.d', 'Perusahaan Teknis Bengkel', 100000, 'bulan'],
            ['1.e', 'Perusahaan Angkutan', 100000, 'bulan'],
            ['1.f', 'Industri Rumah Tangga', 100000, 'bulan'],
        ],
        'Hotel / Penginapan' => [
            ['2.a', 'Melati / Losmen', 200000, 'bulan'],
            ['2.b', 'Bintang I', 100000, 'angkut'],
            ['2.c', 'Bintang II', 150000, 'angkut'],
            ['2.d', 'Bintang III', 200000, 'angkut'],
            ['2.e', 'Bintang IV', 250000, 'angkut'],
            ['2.f', 'Bintang V', 300000, 'angkut'],
        ],
        'Restoran, Rumah Makan, Cafe' => [
            ['3', 'Restoran, Rumah Makan, Cafe', 200000, 'bulan'],
        ],
        'Tempat Pariwisata' => [
            ['4', 'Tempat Pariwisata', 300000, 'angkut'],
        ],
        'Penyelenggaraan Keramaian' => [
            ['5', 'Penyelenggaraan Keramaian / Gedung Pertemuan', 200000, 'angkut'],
        ],
        'Perkantoran dan Perusahaan Jasa' => [
            ['6.a', 'Pemerintah', 50000, 'bulan'],
            ['6.b', 'Swasta / Perusahaan Umum', 75000, 'bulan'],
            ['6.c', 'Kantor Notaris', 75000, 'bulan'],
            ['6.d', 'Usaha Jasa / Kursus', 50000, 'bulan'],
            ['6.e', 'Perkantoran / Bank kelas I dan II', 75000, 'bulan'],
        ],
        'Tempat Pelayanan Kesehatan Pemerintah' => [
            ['7.a', 'Rumah Sakit', 200000, 'angkut'],
            ['7.b', 'Puskesmas', 200000, 'bulan'],
            ['7.c', 'Apotek', 100000, 'bulan'],
        ],
        'Tempat Pelayanan Kesehatan Swasta' => [
            ['8.a', 'Rumah Sakit', 25000, 'kamar_bulan'],
            ['8.b.1', 'Klinik Rawat Inap', 20000, 'kamar_bulan'],
            ['8.b.2', 'Klinik Rawat Jalan', 15000, 'kamar_bulan'],
            ['8.c', 'Balai Pengobatan', 150000, 'bulan'],
            ['8.d', 'Apotek', 50000, 'bulan'],
            ['8.e', 'Praktek Dokter', 100000, 'bulan'],
            ['8.f', 'Praktek Bidan', 50000, 'bulan'],
            ['8.g', 'Salon Kecantikan', 50000, 'bulan'],
        ],
        'Sarana Pendidikan' => [
            ['9.a', 'Sekolah, Lembaga Pendidikan, dan Perguruan Tinggi Pemerintah', 50000, 'bulan'],
            ['9.b', 'Sekolah, Lembaga Pendidikan, dan Perguruan Tinggi Swasta', 150000, 'bulan'],
            ['9.c', 'Gelanggang Olah Raga / Arena Olah Raga', 75000, 'bulan'],
            ['9.d', 'Lembaga Pendidikan Kursus', 50000, 'bulan'],
        ],
        'Sarana Perdagangan' => [
            ['10.a', 'Supermarket', 350000, 'angkut'],
            ['10.b', 'Toko Modern A, B dan C', 250000, 'bulan'],
            ['10.c', 'Mini Market', 250000, 'bulan'],
            ['10.d', 'Pertokoan / Toko Kelas I, II, III', 50000, 'bulan'],
            ['10.e', 'Ruko / Rumah Toko', 50000, 'bulan'],
            ['10.f', 'Kios / Warung (di Pasar)', 2000, 'hari'],
            ['10.g', 'Kaki Lima / Emperan', 2000, 'hari'],
            ['10.h', 'Pedagang di Lokasi Pasar, Terminal dan tempat lainnya', 2000, 'hari'],
        ],
        'Kendaraan Angkut Penumpang Umum' => [
            ['11', 'Kendaraan Angkut Penumpang Umum', 2000, 'hari'],
        ],
        'Rumah Tinggal' => [
            ['12', 'Rumah Tinggal', 5000, 'bulan'],
        ],
        'Pembuangan Sampah Langsung ke TPA' => [
            ['13.a', 'Truck', 100000, 'angkut'],
            ['13.b', 'Pick Up', 75000, 'angkut'],
            ['13.c', 'Cator / Roda Tiga', 50000, 'angkut'],
        ],
        'Pembuangan Sampah Langsung ke TPSS' => [
            ['14.a', 'Rumah Tinggal kelas I, II dan Kontrakan', 15000, 'bulan'],
            ['14.b', 'Ruko kelas I dan II', 60000, 'bulan'],
        ],
    ];

    public function run(): void
    {
        $urutan = 0;

        foreach (self::DATA as $kelompok => $rows) {
            foreach ($rows as [$kode, $nama, $tarif, $satuan]) {
                $urutan++;

                // firstOrCreate: tarif yang sudah diedit lewat menu tidak tertimpa kalau seeder dijalankan ulang
                KategoriObjectRetribusi::firstOrCreate(
                    ['kode' => $kode],
                    compact('kelompok', 'nama', 'tarif', 'satuan', 'urutan')
                );
            }
        }
    }
}