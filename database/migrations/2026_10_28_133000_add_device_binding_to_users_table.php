<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('device_id', 64)->nullable()->after('is_active');
            $table->string('device_name', 100)->nullable()->after('device_id');
            $table->timestamp('device_bound_at')->nullable()->after('device_name');

            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['device_id']);
            $table->dropColumn(['device_id', 'device_name', 'device_bound_at']);
        });
    }
};