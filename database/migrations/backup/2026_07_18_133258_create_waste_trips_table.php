<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('waste_trips', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nomor_polisi');
            $table->string('jenis_kendaraan');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            
            // Titik Awal - presisi tinggi (15,12)
            $table->decimal('start_lat', 15, 12);
            $table->decimal('start_lng', 15, 12);
            $table->string('start_nama')->nullable();
            
            // Titik Pengangkutan
            $table->json('pickup_points'); // [{lat, lng, jarak, nama}, ...]
            
            // Titik Akhir - presisi tinggi (15,12)
            $table->decimal('end_lat', 15, 12)->nullable();
            $table->decimal('end_lng', 15, 12)->nullable();
            $table->string('end_nama')->nullable();
            
            $table->decimal('total_jarak', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('waste_trips');
    }
};