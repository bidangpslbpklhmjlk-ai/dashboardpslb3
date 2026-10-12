<?php
// database/migrations/2026_10_05_000001_add_wilayah_to_lokasi_angkuts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lokasi_angkuts', function (Blueprint $table) {
            $table->string('kecamatan', 50)->nullable()->after('alamat');
            $table->string('desa', 100)->nullable()->after('kecamatan');
            $table->index('kecamatan');
        });
    }

    public function down(): void
    {
        Schema::table('lokasi_angkuts', function (Blueprint $table) {
            $table->dropIndex(['kecamatan']);
            $table->dropColumn(['kecamatan', 'desa']);
        });
    }
};