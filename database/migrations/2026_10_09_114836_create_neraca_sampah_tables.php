<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('industri', function (Blueprint $t) {
        $t->id();
        $t->string('nama', 200);
        $t->string('alamat')->nullable();
        $t->string('kecamatan', 100)->nullable();
        $t->string('telepon', 20)->nullable();
        $t->boolean('aktif')->default(true);
        $t->timestamps();
    });

    Schema::create('neraca_sampah_area', function (Blueprint $t) {
        $t->id();
        $t->string('kode', 30)->unique();
        $t->string('nama', 150);
        $t->unsignedTinyInteger('urutan')->default(0);
        $t->boolean('aktif')->default(true);
        $t->timestamps();
    });

    Schema::create('neraca_sampah_laporan', function (Blueprint $t) {
        $t->id();
        $t->foreignId('industri_id')->constrained('industri')->restrictOnDelete();
        $t->date('periode_mulai');
        $t->date('periode_selesai');
        $t->string('status', 10)->default('draft'); // draft | final

        // Blok tanda tangan
        $t->string('kota_ttd', 100)->nullable();
        $t->date('tanggal_ttd')->nullable();
        $t->string('mengetahui_jabatan', 100)->nullable();
        $t->string('mengetahui_nama', 150)->nullable();
        $t->string('mengetahui_nip', 50)->nullable();
        $t->string('pembuat_jabatan', 100)->nullable();
        $t->string('pembuat_nama', 150)->nullable();
        $t->string('pembuat_nip', 50)->nullable();

        $t->text('catatan')->nullable();
        $t->timestamps();

        $t->unique(['industri_id', 'periode_mulai', 'periode_selesai'], 'neraca_laporan_unik');
    });

    Schema::create('neraca_sampah_harian', function (Blueprint $t) {
        $t->id();
        $t->foreignId('laporan_id')->constrained('neraca_sampah_laporan')->cascadeOnDelete();
        $t->foreignId('area_id')->constrained('neraca_sampah_area')->restrictOnDelete();
        $t->date('tanggal');
        $t->decimal('organik_kg', 12, 3)->default(0);
        $t->decimal('anorganik_kg', 12, 3)->default(0);
        $t->decimal('residu_kg', 12, 3)->default(0);
        $t->timestamps();

        $t->unique(['laporan_id', 'area_id', 'tanggal']);
        $t->index(['laporan_id', 'tanggal']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neraca_sampah_tables');
    }
};
