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
        Schema::create('waste_trips', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nomor_polisi', 20);
            $table->string('jenis_kendaraan', 50);
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            // Titik Awal
            $table->decimal('start_lat', 15, 12);
            $table->decimal('start_lng', 15, 12);
            $table->string('start_nama', 100)->nullable();
            $table->string('start_desa', 100)->nullable();
            $table->string('start_kecamatan', 50)->nullable();
            $table->string('start_kabupaten', 50)->nullable()->default('Majalengka');
            $table->string('start_provinsi', 50)->nullable()->default('Jawa Barat');

            // Titik Pengangkutan
            $table->json('pickup_points');

            // Rute
            $table->json('route_geometry')->nullable();

            // Titik Akhir
            $table->decimal('end_lat', 15, 12)->nullable();
            $table->decimal('end_lng', 15, 12)->nullable();
            $table->string('end_nama', 100)->nullable();
            $table->string('end_desa', 100)->nullable();
            $table->string('end_kecamatan', 50)->nullable();
            $table->string('end_kabupaten', 50)->nullable()->default('Majalengka');
            $table->string('end_provinsi', 50)->nullable()->default('Jawa Barat');

            $table->decimal('total_jarak', 8, 2)->default(0);
            $table->timestamps();

            $table->index('tanggal');
            $table->index('nomor_polisi');
            $table->index('start_kecamatan');
            $table->index('end_kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_trips');
    }
};
