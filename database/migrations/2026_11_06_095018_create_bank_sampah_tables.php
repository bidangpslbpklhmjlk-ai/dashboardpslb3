<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master unit bank sampah. Satu desa boleh punya banyak baris.
        Schema::create('bank_sampah', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 150);
            $t->string('kode_kecamatan', 10)->index();
            $t->string('kode_desa', 15)->nullable()->index(); // kosong untuk unit non-desa
            $t->string('jenis', 20)->default('desa');          // desa | sekolah | pkk | lainnya
            $t->boolean('aktif')->default(true);
            $t->timestamps();

            $t->unique(['nama', 'kode_kecamatan', 'kode_desa'], 'bank_sampah_unik');
        });

        // Berat per bank sampah per bulan. Bulan tanpa data tidak disimpan.
        Schema::create('bank_sampah_timbangan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bank_sampah_id')->constrained('bank_sampah')->cascadeOnDelete();
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('bulan');
            $t->decimal('berat_kg', 12, 2);
            $t->timestamps();

            $t->unique(['bank_sampah_id', 'tahun', 'bulan'], 'bank_sampah_timbangan_unik');
            $t->index(['tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_sampah_timbangan');
        Schema::dropIfExists('bank_sampah');
    }
};