<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Identitas BSU dari daftar induk. NIK sengaja tidak disimpan.
    public function up(): void
    {
        Schema::table('bank_sampah', function (Blueprint $t) {
            $t->string('ketua', 120)->nullable()->after('jenis');
            $t->string('no_hp', 30)->nullable()->after('ketua');
            $t->string('email', 150)->nullable()->after('no_hp');
            $t->unsignedSmallInteger('tenaga_pria')->nullable()->after('email');
            $t->unsignedSmallInteger('tenaga_wanita')->nullable()->after('tenaga_pria');
            $t->string('legalitas_jenis', 100)->nullable()->after('tenaga_wanita');
            $t->text('legalitas_keterangan')->nullable()->after('legalitas_jenis');
            $t->date('legalitas_tanggal')->nullable()->after('legalitas_keterangan');
            $t->text('catatan')->nullable()->after('legalitas_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('bank_sampah', function (Blueprint $t) {
            $t->dropColumn(['ketua', 'no_hp', 'email', 'tenaga_pria', 'tenaga_wanita',
                'legalitas_jenis', 'legalitas_keterangan', 'legalitas_tanggal', 'catatan']);
        });
    }
};