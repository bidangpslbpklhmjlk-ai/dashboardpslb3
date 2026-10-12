<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('armadas', function (Blueprint $table) {
            // Lepas foreign key lama ke tabel tarifs (nama default Laravel)
            $table->dropForeign(['tarif_id']);
        });

        // Data lama mengacu ke tabel tarifs, tidak cocok lagi: kosongkan
        \DB::table('armadas')->update(['tarif_id' => null]);

        Schema::table('armadas', function (Blueprint $table) {
            $table->renameColumn('tarif_id', 'kategori_object_retribusi_id');
        });

        Schema::table('armadas', function (Blueprint $table) {
            $table->foreign('kategori_object_retribusi_id')
                ->references('id')
                ->on('kategori_object_retribusi')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('armadas', function (Blueprint $table) {
            $table->dropForeign(['kategori_object_retribusi_id']);
            $table->renameColumn('kategori_object_retribusi_id', 'tarif_id');
        });
    }
};