<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industri', function (Blueprint $t) {
            $t->string('desa', 100)->nullable()->after('kecamatan');
        });
    }

    public function down(): void
    {
        Schema::table('industri', function (Blueprint $t) {
            $t->dropColumn('desa');
        });
    }
};