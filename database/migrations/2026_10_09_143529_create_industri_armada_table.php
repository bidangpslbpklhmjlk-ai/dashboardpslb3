<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industri_armada', function (Blueprint $t) {
            $t->id();
            $t->foreignId('industri_id')->constrained('industri')->cascadeOnDelete();
            $t->foreignId('armada_id')->constrained('armadas')->cascadeOnDelete();
            $t->timestamps();

            // Satu armada hanya boleh terpasang ke satu industri
            $t->unique('armada_id', 'industri_armada_armada_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industri_armada');
    }
};