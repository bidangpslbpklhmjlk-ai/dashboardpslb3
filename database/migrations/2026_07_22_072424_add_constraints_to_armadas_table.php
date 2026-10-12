<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('armadas', function (Blueprint $table) {
            // Tambah unique (pastikan tidak ada duplikat dulu!)
            $table->unique('nama');
            $table->unique('nomor_polisi');

            // Tambah composite index
            $table->index(['tipe', 'aktif']);
            $table->index(['pembayaran', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::table('armadas', function (Blueprint $table) {
            $table->dropUnique(['nama']);
            $table->dropUnique(['nomor_polisi']);
            $table->dropIndex(['tipe', 'aktif']);
            $table->dropIndex(['pembayaran', 'aktif']);
        });
    }
};