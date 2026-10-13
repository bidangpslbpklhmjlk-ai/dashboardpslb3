<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gatepass_logs', function (Blueprint $t) {
            $t->string('asal_data', 20)->default('aplikasi')->after('photo'); // aplikasi | impor_rekap
            $t->text('sumber_sampah')->nullable()->after('asal_data');        // lokasi asal sampah
            $t->text('catatan')->nullable()->after('sumber_sampah');
            $t->index('asal_data');
        });
    }

    public function down(): void
    {
        Schema::table('gatepass_logs', function (Blueprint $t) {
            $t->dropIndex(['asal_data']);
            $t->dropColumn(['asal_data', 'sumber_sampah', 'catatan']);
        });
    }
};