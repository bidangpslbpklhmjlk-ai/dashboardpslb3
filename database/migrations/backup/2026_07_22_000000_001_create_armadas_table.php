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
        Schema::create('armadas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->enum('tipe', ['perusahaan', 'dinas'])->default('perusahaan');
            $table->enum('pembayaran', ['gratis', 'bayar'])->default('gratis');
            $table->string('nomor_polisi', 20)->nullable();
            $table->string('jenis_kendaraan', 50)->nullable();
            $table->year('tahun_pengadaan')->nullable();
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_beroperasi'])->default('baik');
            $table->string('foto', 255)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('tipe');
            $table->index('pembayaran');
            $table->index('aktif');
            $table->index('nomor_polisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('armadas');
    }
};
