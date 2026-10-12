<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_trips', function (Blueprint $table) {
            $table->json('route_geometry')->nullable()->after('pickup_points');
        });
    }

    public function down(): void
    {
        Schema::table('waste_trips', function (Blueprint $table) {
            $table->dropColumn('route_geometry');
        });
    }
};