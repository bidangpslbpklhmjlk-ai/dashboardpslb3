<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perusahaans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('tipe', 50)->default('perusahaan');
            $table->enum('pembayaran', ['gratis', 'bayar'])->default('gratis');
            $table->decimal('tarif_total', 12, 2)->default(0);
            $table->string('alamat', 255)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perusahaans');
    }
};