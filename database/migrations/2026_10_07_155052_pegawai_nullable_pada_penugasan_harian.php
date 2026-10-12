<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penugasan_harian', function (Blueprint $t) {
            $t->unsignedBigInteger('pegawai_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Sengaja dikosongkan
    }
};