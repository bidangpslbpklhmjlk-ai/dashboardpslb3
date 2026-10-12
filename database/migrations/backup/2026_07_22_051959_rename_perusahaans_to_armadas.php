<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename tabel
        Schema::rename('perusahaans', 'armadas');

        // Tambah kolom baru untuk dinas
        Schema::table('armadas', function (Blueprint $table) {
            $table->year('tahun_pengadaan')->nullable()->after('jenis_kendaraan');
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_beroperasi'])->default('baik')->after('tahun_pengadaan');
            $table->string('foto', 255)->nullable()->after('kondisi');
        });

        // Update foreign key di gatepass_logs
        Schema::table('gatepass_logs', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->renameColumn('perusahaan_id', 'armada_id');
            $table->foreign('armada_id')->references('id')->on('armadas')->onDelete('restrict');
        });

        // Update foreign key di invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->renameColumn('perusahaan_id', 'armada_id');
            $table->foreign('armada_id')->references('id')->on('armadas')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Revert foreign key
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['armada_id']);
            $table->renameColumn('armada_id', 'perusahaan_id');
            $table->foreign('perusahaan_id')->references('id')->on('perusahaans')->onDelete('restrict');
        });

        Schema::table('gatepass_logs', function (Blueprint $table) {
            $table->dropForeign(['armada_id']);
            $table->renameColumn('armada_id', 'perusahaan_id');
            $table->foreign('perusahaan_id')->references('id')->on('perusahaans')->onDelete('restrict');
        });

        // Hapus kolom
        Schema::table('armadas', function (Blueprint $table) {
            $table->dropColumn(['tahun_pengadaan', 'kondisi', 'foto']);
        });

        // Rename balik
        Schema::rename('armadas', 'perusahaans');
    }
};