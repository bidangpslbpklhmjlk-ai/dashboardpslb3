<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rute_angkuts', function (Blueprint $table) {
            $table->string('hari', 10)->change();
        });

        if (Schema::hasColumn('rute_angkuts', 'nama_rute')) {
            Schema::table('rute_angkuts', function (Blueprint $table) {
                $table->renameColumn('nama_rute', 'nama');
            });
        }
    }

    public function down(): void
    {
        // Sengaja dikosongkan
    }
};