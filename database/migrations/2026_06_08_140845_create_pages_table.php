<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // <-- Pastikan ini ada
            $table->string('slug')->unique(); // <-- Pastikan ini ada
            $table->enum('category', ['profil', 'pelayanan', 'akuntabilitas', 'lainnya']); // <-- Pastikan ini ada
            $table->longText('content')->nullable(); // <-- Pastikan ini ada
            $table->boolean('is_active')->default(true); // <-- Pastikan ini ada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};