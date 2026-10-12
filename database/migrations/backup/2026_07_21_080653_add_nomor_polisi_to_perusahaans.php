<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            if (!Schema::hasColumn('perusahaans', 'nomor_polisi')) {
                $table->string('nomor_polisi', 20)->nullable()->after('tarif_total');
            }
            if (!Schema::hasColumn('perusahaans', 'jenis_kendaraan')) {
                $table->string('jenis_kendaraan', 50)->nullable()->after('nomor_polisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            if (Schema::hasColumn('perusahaans', 'nomor_polisi')) {
                $table->dropColumn('nomor_polisi');
            }
            if (Schema::hasColumn('perusahaans', 'jenis_kendaraan')) {
                $table->dropColumn('jenis_kendaraan');
            }
        });
    }
};