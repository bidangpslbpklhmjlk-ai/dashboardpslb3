<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            // Pilihan: kolektif (RW/Pasar/Terminal) atau warga individu
            $table->boolean('is_kolektif')->default(false)->after('status');

            // Wajib diisi HANYA jika is_kolektif = true (angka saja: jumlah rumah tangga/pedagang/kendaraan)
            $table->unsignedInteger('jumlah_unit')->nullable()->after('is_kolektif');
        });
    }

    public function down(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            $table->dropColumn(['is_kolektif', 'jumlah_unit']);
        });
    }
};