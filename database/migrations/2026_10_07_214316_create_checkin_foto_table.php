<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkin_foto', function (Blueprint $t) {
            $t->id();
            $t->foreignId('checkin_id')->constrained('checkins')->cascadeOnDelete();
            $t->string('path');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_foto');
    }
};