<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rute_angkut_lokasi', function (Blueprint $table) {
            // null = tiap minggu, 1 = hanya minggu 1, 2 = hanya minggu 2
            $table->unsignedTinyInteger('pekan')->nullable()->after('urutan');
        });
    }

    public function down(): void
    {
        Schema::table('rute_angkut_lokasi', function (Blueprint $table) {
            $table->dropColumn('pekan');
        });
    }
};