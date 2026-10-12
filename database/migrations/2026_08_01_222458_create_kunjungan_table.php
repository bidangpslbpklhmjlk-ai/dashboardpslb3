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
Schema::create('kunjungan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('warga_id')->constrained('warga');
    $table->foreignId('petugas_id')->constrained('users');
    $table->timestamp('waktu_kunjungan');
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->string('foto')->nullable();
    $table->text('catatan')->nullable();
    $table->enum('status', ['selesai', 'batal'])->default('selesai');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
