<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkin_sampah', function (Blueprint $t) {
            $t->id();
            $t->foreignId('checkin_id')->constrained('checkins')->cascadeOnDelete();
            $t->foreignId('jenis_sampah_id')->constrained('jenis_sampah');
            $t->decimal('berat_kg', 10, 2);
            $t->timestamps();

            $t->unique(['checkin_id', 'jenis_sampah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_sampah');
    }
};