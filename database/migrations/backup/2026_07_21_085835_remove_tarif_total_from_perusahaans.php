<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            if (Schema::hasColumn('perusahaans', 'tarif_total')) {
                $table->dropColumn('tarif_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->decimal('tarif_total', 12, 2)->default(0)->after('pembayaran');
        });
    }
};