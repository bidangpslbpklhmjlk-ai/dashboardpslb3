<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('berita_images', function (Blueprint $table) {
        $table->id();
        // foreignId ini menghubungkan gambar ke berita
        // constrained('beritas') merujuk pada tabel berita
        $table->foreignId('berita_id')->constrained('beritas')->onDelete('cascade');
        $table->string('path');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_images');
    }
};
