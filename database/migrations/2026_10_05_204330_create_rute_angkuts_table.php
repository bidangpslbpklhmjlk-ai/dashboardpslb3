<?php
// database/migrations/2026_10_05_000002_create_rute_angkuts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rute_angkuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('armada_id')->constrained('armadas')->cascadeOnDelete();
            $table->unsignedTinyInteger('hari'); // 1=Senin ... 7=Minggu
            $table->string('nama_rute', 100)->nullable();
            $table->decimal('total_jarak', 8, 2)->default(0);
            $table->json('route_geometry')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['armada_id', 'hari']);
        });

        Schema::create('rute_angkut_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rute_angkut_id')->constrained('rute_angkuts')->cascadeOnDelete();
            $table->foreignId('lokasi_angkut_id')->constrained('lokasi_angkuts')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan');
            $table->unique(['rute_angkut_id', 'lokasi_angkut_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rute_angkut_lokasi');
        Schema::dropIfExists('rute_angkuts');
    }
};