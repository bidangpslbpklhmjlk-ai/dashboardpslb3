<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // Dropdown Profil
            ['title' => 'Tugas Pokok dan Fungsi', 'category' => 'profil'],
            ['title' => 'Struktur Organisasi', 'category' => 'profil'],
            ['title' => 'Maklumat Pelayanan', 'category' => 'profil'],
            ['title' => 'Sejarah Dinas', 'category' => 'profil'],
            ['title' => 'Bidang', 'category' => 'profil'],
            ['title' => 'Survey Kepuasan Masyarakat', 'category' => 'profil'],
            ['title' => 'PPID', 'category' => 'profil'],
            ['title' => 'Hubungi Kami', 'category' => 'profil'],

            // Dropdown Pelayanan
            ['title' => 'Persetujuan Lingkungan', 'category' => 'pelayanan'],
            ['title' => 'Penerbitan Surat Rekomendasi Izin Penebangan', 'category' => 'pelayanan'],
            ['title' => 'Pelayanan Jasa Penarikan atau Pengangkutan Sampah ke TPA', 'category' => 'pelayanan'],
            ['title' => 'Rekomendasi Arahan Penyimpanan Sementara TPS Limbah B3 (Rintek)', 'category' => 'pelayanan'],
            ['title' => 'Persetujuan Teknis Pemenuhan Baku Mutu Air Limbah dan Pembuangan Emisi', 'category' => 'pelayanan'],
            ['title' => 'Penerbitan Surat Kelayakan Operasional (SLO)', 'category' => 'pelayanan'],
            ['title' => 'Pengaduan Masyarakat di Bidang Lingkungan Hidup', 'category' => 'pelayanan'],

            // Dropdown Akuntabilitas
            ['title' => 'SAKIP', 'category' => 'akuntabilitas'],
            ['title' => 'Publikasi', 'category' => 'akuntabilitas'],
        ];

foreach ($menus as $menu) {
    \Illuminate\Support\Facades\DB::table('pages')->insert([
        'title' => $menu['title'],
        'slug' => \Illuminate\Support\Str::slug($menu['title']),
        'category' => $menu['category'],
        'content' => 'Konten untuk halaman ' . $menu['title'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
    }
}