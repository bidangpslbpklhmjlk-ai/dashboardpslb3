<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BeritaFactory extends Factory
{
    /**
     * Tentukan pola data acak untuk model Berita.
     */
    public function definition(): array
    {
        return [
            // Membuat judul acak berisi 5-8 kata
            'title' => $this->faker->sentence(rand(5, 8)), 
            
            // Membuat isi berita acak sebanyak 4 paragraf
            'content' => $this->faker->paragraphs(4, true), 
            
            // Memilih kategori secara acak dari list yang ditentukan
            'category' => $this->faker->randomElement(['Berita Utama', 'Kegiatan', 'Pengumuman', 'Aksi', 'Prestasi']), 
            
            // Mengosongkan gambar atau diisi path default (karena gambar bisa di-upload nanti)
            'image' => null, 
        ];
    }
}