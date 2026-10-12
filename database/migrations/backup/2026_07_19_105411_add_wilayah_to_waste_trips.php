<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_trips', function (Blueprint $table) {
            // Start point wilayah
            $table->string('start_desa')->nullable()->after('start_nama');
            $table->string('start_kecamatan')->nullable()->after('start_desa');
            $table->string('start_kabupaten')->nullable()->after('start_kecamatan');
            $table->string('start_provinsi')->nullable()->after('start_kabupaten');

            // End point wilayah
            $table->string('end_desa')->nullable()->after('end_nama');
            $table->string('end_kecamatan')->nullable()->after('end_desa');
            $table->string('end_kabupaten')->nullable()->after('end_kecamatan');
            $table->string('end_provinsi')->nullable()->after('end_kabupaten');
        });
    }

    public function down(): void
    {
        Schema::table('waste_trips', function (Blueprint $table) {
            $table->dropColumn([
                'start_desa', 'start_kecamatan', 'start_kabupaten', 'start_provinsi',
                'end_desa', 'end_kecamatan', 'end_kabupaten', 'end_provinsi'
            ]);
        });
    }
};