<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penugasan_harian', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal');
            $t->foreignId('rute_angkut_id')->constrained('rute_angkuts');
            $t->foreignId('armada_id')->constrained('armadas');
            $t->foreignId('pegawai_id')->constrained('pegawais');
            $t->foreignId('armada_asal_id')->nullable()->constrained('armadas')->nullOnDelete();
            $t->string('status', 20)->default('direncanakan'); // direncanakan|berjalan|selesai|batal
            $t->boolean('dialihkan')->default(false);
            $t->string('alasan_alih')->nullable();
            $t->text('catatan')->nullable();
            $t->timestamps();

            $t->index(['armada_id', 'tanggal']);
            $t->index(['pegawai_id', 'tanggal']);
            $t->index(['rute_angkut_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_harian');
    }
};