<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gatepass_logs', function (Blueprint $table) {
            $table->unsignedInteger('ritasi')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('gatepass_logs', function (Blueprint $table) {
            $table->unsignedInteger('ritasi')->default(null)->change();
        });
    }
};