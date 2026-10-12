<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('armadas', function (Blueprint $table) {
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_beroperasi'])->nullable()->default(null)->change();
            $table->year('tahun_pengadaan')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('armadas', function (Blueprint $table) {
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_beroperasi'])->default('baik')->change();
            $table->year('tahun_pengadaan')->nullable()->change();
        });
    }
};