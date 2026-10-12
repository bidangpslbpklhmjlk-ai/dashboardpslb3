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
Schema::create('dokumen_akuntabilitas', function (Blueprint $table) {
    $table->id();

    $table->string('judul');
    $table->string('jenis');

    // SAKIP, LKjIP, Renstra, Renja, Publikasi, SOP, dll
    $table->integer('tahun');

    $table->string('file');

    $table->boolean('is_published')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_akuntabilitas');
    }
};
