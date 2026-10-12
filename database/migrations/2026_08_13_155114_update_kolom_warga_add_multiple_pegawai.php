<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // hapus kolom pegawai_id di warga (kalau sudah pernah ditambahkan)
        if (Schema::hasColumn('warga', 'pegawai_id')) {
            Schema::table('warga', function (Blueprint $table) {
                $table->dropConstrainedForeignId('pegawai_id');
            });
        }

        Schema::create('pegawai_warga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['warga_id', 'pegawai_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai_warga');

        Schema::table('warga', function (Blueprint $table) {
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
        });
    }
};