<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->json('menus')->nullable()->after('is_active');
        });

        // Semua pengguna yang sudah ada tetap punya akses penuh seperti sekarang,
        // supaya tidak ada yang terkunci. Superadmin bisa menurunkannya nanti.
        DB::table('users')->update(['role' => 'superadmin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'menus']);
        });
    }
};