<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perluas enum: lama + baru, agar data lama tetap valid
        DB::statement("ALTER TABLE armadas MODIFY tipe ENUM(
            'dinas','perusahaan',
            'dinas_lh','instansi_pemerintah','desa','bumn_bumd','swasta','lainnya'
        ) NOT NULL DEFAULT 'swasta'");

        // 2. Pindahkan data lama ke tipe baru
        DB::table('armadas')->where('tipe', 'dinas')->update(['tipe' => 'dinas_lh']);
        DB::table('armadas')->where('tipe', 'perusahaan')->update(['tipe' => 'swasta']);

        // 3. Rapikan enum: hanya nilai baru
        DB::statement("ALTER TABLE armadas MODIFY tipe ENUM(
            'dinas_lh','instansi_pemerintah','desa','bumn_bumd','swasta','lainnya'
        ) NOT NULL DEFAULT 'swasta'");
    }

    public function down(): void
    {
        // 1. Perluas enum lagi supaya nilai lama bisa ditulis
        DB::statement("ALTER TABLE armadas MODIFY tipe ENUM(
            'dinas','perusahaan',
            'dinas_lh','instansi_pemerintah','desa','bumn_bumd','swasta','lainnya'
        ) NOT NULL DEFAULT 'perusahaan'");

        // 2. Petakan kembali. Rollback ini lossy: tipe baru tidak punya padanan lama.
        //    Dinas LH & Instansi Pemerintah (gratis) -> dinas; sisanya -> perusahaan.
        DB::table('armadas')->whereIn('tipe', ['dinas_lh', 'instansi_pemerintah'])->update(['tipe' => 'dinas']);
        DB::table('armadas')->whereIn('tipe', ['desa', 'bumn_bumd', 'swasta', 'lainnya'])->update(['tipe' => 'perusahaan']);

        // 3. Kembalikan enum ke bentuk lama
        DB::statement("ALTER TABLE armadas MODIFY tipe ENUM('dinas','perusahaan') NOT NULL DEFAULT 'perusahaan'");
    }
};