<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkin_pengecualian', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal');
            $t->foreignId('armada_id')->constrained('armadas')->cascadeOnDelete();
            $t->foreignId('lokasi_angkut_id')->constrained('lokasi_angkuts')->cascadeOnDelete();
            $t->enum('status', ['tidak_diambil', 'diambil_armada_lain']);
            $t->foreignId('armada_pengganti_id')->nullable()->constrained('armadas')->nullOnDelete();
            $t->string('alasan', 255);
            $t->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->unique(['tanggal', 'armada_id', 'lokasi_angkut_id'], 'pengecualian_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_pengecualian');
    }
};