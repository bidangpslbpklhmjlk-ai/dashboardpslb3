<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('gatepass_logs', 'volume_m3') && !Schema::hasColumn('gatepass_logs', 'volume_ton')) {
            Schema::table('gatepass_logs', function (Blueprint $table) {
                $table->renameColumn('volume_m3', 'volume_ton');
            });
        }

        if (Schema::hasColumn('invoices', 'volume_m3') && !Schema::hasColumn('invoices', 'volume_ton')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->renameColumn('volume_m3', 'volume_ton');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('gatepass_logs', 'volume_ton') && !Schema::hasColumn('gatepass_logs', 'volume_m3')) {
            Schema::table('gatepass_logs', function (Blueprint $table) {
                $table->renameColumn('volume_ton', 'volume_m3');
            });
        }

        if (Schema::hasColumn('invoices', 'volume_ton') && !Schema::hasColumn('invoices', 'volume_m3')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->renameColumn('volume_ton', 'volume_m3');
            });
        }
    }
};