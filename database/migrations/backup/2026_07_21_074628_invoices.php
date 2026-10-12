<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Ganti tarif_per_m3 jadi tarif_total
            if (Schema::hasColumn('invoices', 'tarif_per_m3')) {
                $table->dropColumn('tarif_per_m3');
            }
            $table->decimal('tarif_total', 12, 2)->default(0)->after('volume_m3');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('tarif_total');
            $table->decimal('tarif_per_m3', 10, 2)->default(0);
        });
    }
};