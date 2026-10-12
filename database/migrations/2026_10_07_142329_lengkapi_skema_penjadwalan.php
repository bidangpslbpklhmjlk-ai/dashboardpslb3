<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi penugasan_harian agar mendukung:
 *  - armada rusak berbasis rentang tanggal
 *  - pengalihan per rute ATAU per lokasi (+ flag)
 *  - jam opsional di baris harian
 *
 * Aman dijalankan ulang (setiap langkah dicek dulu).
 * Jalankan SETELAH migration penugasan_harian, checkins, checkin_sampah.
 */
return new class extends Migration {
    public function up(): void
    {
        // 1) Riwayat armada rusak
        if (! Schema::hasTable('armada_kerusakan')) {
            Schema::create('armada_kerusakan', function (Blueprint $t) {
                $t->id();
                $t->foreignId('armada_id')->constrained('armadas')->cascadeOnDelete();
                $t->date('tanggal_mulai');
                $t->date('tanggal_selesai')->nullable();       // null = belum tahu kapan selesai
                $t->string('status', 10)->default('rusak');    // rusak | selesai | batal
                $t->string('keterangan')->nullable();
                $t->timestamps();
                $t->index(['armada_id', 'status']);
            });
        }

        // 2) penugasan_harian: jam, sumber, tautan kerusakan, info pengalihan
        Schema::table('penugasan_harian', function (Blueprint $t) {
            if (! Schema::hasColumn('penugasan_harian', 'jam_mulai')) {
                $t->time('jam_mulai')->nullable()->after('tanggal');
            }
            if (! Schema::hasColumn('penugasan_harian', 'jam_selesai')) {
                $t->time('jam_selesai')->nullable()->after('jam_mulai');
            }
            if (! Schema::hasColumn('penugasan_harian', 'sumber')) {
                $t->string('sumber', 10)->default('manual')->after('status'); // manual | alih
            }
            if (! Schema::hasColumn('penugasan_harian', 'kerusakan_id')) {
                $t->foreignId('kerusakan_id')->nullable()->after('armada_asal_id')
                  ->constrained('armada_kerusakan')->nullOnDelete();
            }
            if (! Schema::hasColumn('penugasan_harian', 'alih_sampai')) {
                $t->date('alih_sampai')->nullable()->after('dialihkan');
            }
        });

        // 3) Pembatasan lokasi per baris harian.
        //    Tanpa baris pivot = seluruh rute; ada baris pivot = hanya lokasi tsb.
        if (! Schema::hasTable('penugasan_harian_lokasi')) {
            Schema::create('penugasan_harian_lokasi', function (Blueprint $t) {
                $t->id();
                $t->foreignId('penugasan_id')->constrained('penugasan_harian')->cascadeOnDelete();
                $t->foreignId('lokasi_angkut_id')->constrained('lokasi_angkuts');
                $t->unique(['penugasan_id', 'lokasi_angkut_id']);
            });
        }

        // 4) Lindungi riwayat check-in: penugasan yang punya check-in tidak boleh terhapus diam-diam.
        try {
            Schema::table('checkins', function (Blueprint $t) {
                $t->dropForeign(['penugasan_id']);
            });
        } catch (\Throwable $e) {
            // FK belum ada / sudah dilepas -> lanjut
        }
        Schema::table('checkins', function (Blueprint $t) {
            $t->foreign('penugasan_id')->references('id')->on('penugasan_harian')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        try {
            Schema::table('checkins', function (Blueprint $t) {
                $t->dropForeign(['penugasan_id']);
            });
            Schema::table('checkins', function (Blueprint $t) {
                $t->foreign('penugasan_id')->references('id')->on('penugasan_harian')->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
        }

        Schema::dropIfExists('penugasan_harian_lokasi');

        if (Schema::hasColumn('penugasan_harian', 'kerusakan_id')) {
            Schema::table('penugasan_harian', function (Blueprint $t) {
                $t->dropForeign(['kerusakan_id']);
            });
        }
        Schema::table('penugasan_harian', function (Blueprint $t) {
            $drop = array_values(array_filter(
                ['jam_mulai', 'jam_selesai', 'sumber', 'kerusakan_id', 'alih_sampai'],
                fn ($c) => Schema::hasColumn('penugasan_harian', $c)
            ));
            if ($drop) {
                $t->dropColumn($drop);
            }
        });

        Schema::dropIfExists('armada_kerusakan');
    }
};