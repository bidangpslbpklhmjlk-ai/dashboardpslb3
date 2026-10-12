<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Nama tabel (foreign key di tabel lain ikut menyesuaikan otomatis)
        Schema::rename('kategori_warga', 'kategori_object_retribusi');
        Schema::rename('warga', 'object_retribusi');
        Schema::rename('invoice_warga', 'invoice_object_retribusi');
        Schema::rename('pegawai_warga', 'pegawai_object_retribusi');

        // 2. Nama kolom
        Schema::table('object_retribusi', function (Blueprint $table) {
            $table->renameColumn('kategori_warga_id', 'kategori_object_retribusi_id');
        });

        foreach (['kunjungan', 'invoice_object_retribusi', 'pegawai_object_retribusi'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->renameColumn('warga_id', 'object_retribusi_id');
            });
        }

        // 3. Kolom baru kategori (sesuai pengumuman tarif)
        Schema::table('kategori_object_retribusi', function (Blueprint $table) {
            $table->string('kelompok')->default('Lainnya')->after('id');
            $table->string('kode', 20)->nullable()->unique()->after('kelompok');
            // data lama berarti "per kunjungan" = per angkut
            $table->string('satuan', 20)->default('angkut')->after('tarif');
            // baris lama tampil setelah data dari pengumuman (urutan 1-50)
            $table->unsignedSmallInteger('urutan')->default(1000)->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_object_retribusi', function (Blueprint $table) {
            $table->dropUnique(['kode']);
            $table->dropColumn(['kelompok', 'kode', 'satuan', 'urutan']);
        });

        foreach (['kunjungan', 'invoice_object_retribusi', 'pegawai_object_retribusi'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->renameColumn('object_retribusi_id', 'warga_id');
            });
        }

        Schema::table('object_retribusi', function (Blueprint $table) {
            $table->renameColumn('kategori_object_retribusi_id', 'kategori_warga_id');
        });

        Schema::rename('pegawai_object_retribusi', 'pegawai_warga');
        Schema::rename('invoice_object_retribusi', 'invoice_warga');
        Schema::rename('object_retribusi', 'warga');
        Schema::rename('kategori_object_retribusi', 'kategori_warga');
    }
};