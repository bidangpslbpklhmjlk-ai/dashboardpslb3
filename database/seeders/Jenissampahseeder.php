<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use Illuminate\Database\Seeder;

class JenisSampahSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Organik', 'Anorganik', 'B3', 'Residu'] as $nama) {
            JenisSampah::firstOrCreate(['nama' => $nama], ['aktif' => true]);
        }
    }
}