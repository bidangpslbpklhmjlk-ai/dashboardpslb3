<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            // Tambah kolom yang belum ada
            if (!Schema::hasColumn('perusahaans', 'pembayaran')) {
                $table->enum('pembayaran', ['gratis', 'bayar'])->default('gratis')->after('tipe');
            }
            if (!Schema::hasColumn('perusahaans', 'tarif_total')) {
                $table->decimal('tarif_total', 12, 2)->default(0)->after('pembayaran');
            }
            // Hapus tarif_per_m3 kalau masih ada dari versi lama
            if (Schema::hasColumn('perusahaans', 'tarif_per_m3')) {
                $table->dropColumn('tarif_per_m3');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            if (Schema::hasColumn('perusahaans', 'pembayaran')) {
                $table->dropColumn('pembayaran');
            }
            if (Schema::hasColumn('perusahaans', 'tarif_total')) {
                $table->dropColumn('tarif_total');
            }
        });
    }
};