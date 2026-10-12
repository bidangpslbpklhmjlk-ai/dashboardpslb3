<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $t) {
            $t->id();
            $t->foreignId('penugasan_id')->constrained('penugasan_harian')->cascadeOnDelete();
            $t->unsignedSmallInteger('ritasi_ke')->default(1);
            $t->foreignId('lokasi_angkut_id')->constrained('lokasi_angkuts');
            $t->dateTime('waktu_kejadian');
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->float('akurasi_gps')->nullable();
            $t->string('sumber', 10)->default('app'); // app|admin
            $t->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->string('alasan_manual')->nullable();
            $t->json('flag')->nullable();
            $t->uuid('client_uuid')->unique();
            $t->text('catatan')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['penugasan_id', 'ritasi_ke']);
            $t->index(['lokasi_angkut_id', 'waktu_kejadian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};