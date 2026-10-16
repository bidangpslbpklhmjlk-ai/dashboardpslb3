<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $nama = 'rute_angkut_lokasi_rute_angkut_id_lokasi_angkut_id_unique';

    public function up(): void
    {
        $ada = collect(Schema::getIndexes('rute_angkut_lokasi'))->contains(fn ($i) => $i['name'] === $this->nama);
        if (! $ada) {
            return; // lokal: indeks memang sudah tidak ada
        }

        // Foreign key rute_angkut_id mungkin memakai indeks ini, jadi sediakan pengganti dulu.
        Schema::table('rute_angkut_lokasi', function (Blueprint $t) {
            $t->index(['rute_angkut_id', 'urutan'], 'rute_lokasi_rute_urutan_index');
        });
        Schema::table('rute_angkut_lokasi', function (Blueprint $t) {
            $t->dropUnique($this->nama);
        });
    }

    public function down(): void
    {
        // sengaja kosong: indeks unik ini menghalangi lokasi yang sama dua kali dalam satu rute
    }
};