<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah_kecamatan', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 10)->unique();   // contoh: 32.10.07
            $t->string('nama', 100);
        });

        Schema::create('wilayah_desa', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 15)->unique();   // contoh: 32.10.07.1007
            $t->string('kode_kecamatan', 10)->index();
            $t->string('nama', 100);
            $t->string('jenis', 10);            // desa | kelurahan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_desa');
        Schema::dropIfExists('wilayah_kecamatan');
    }
};