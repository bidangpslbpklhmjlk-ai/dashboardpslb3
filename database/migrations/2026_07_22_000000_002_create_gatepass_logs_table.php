<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gatepass_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_kendaraan', 50);
            $table->string('nomor_polisi', 20);
            $table->string('jenis_muatan', 50);
            $table->foreignId('armada_id')->constrained('armadas')->onDelete('restrict');
            $table->decimal('volume_m3', 6, 2)->default(0);
            $table->dateTime('waktu_masuk')->useCurrent();
            $table->string('photo', 255)->nullable();
            $table->timestamps();

            $table->index('waktu_masuk');
            $table->index('nomor_polisi');
            $table->index('tipe_kendaraan');
            $table->index('armada_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gatepass_logs');
    }
};
