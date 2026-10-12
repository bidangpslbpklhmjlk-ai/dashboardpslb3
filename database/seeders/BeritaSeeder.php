<?php

namespace Database\Seeders;

use App\Models\Berita;
use Illuminate\Database\Seeder;

class BeritaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // MAU BIKIN BERAPA DATA? Cukup ganti angka di dalam count()
        // Contoh di bawah ini akan langsung membuat 20 berita acak sekaligus dalam sekejap!
        Berita::factory()->count(20)->create();
    }
}