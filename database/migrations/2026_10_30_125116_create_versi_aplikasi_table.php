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
    Schema::create('versi_aplikasi', function (Blueprint $t) {
        $t->id();
        $t->string('platform', 20)->default('android');
        $t->unsignedInteger('version_code');
        $t->string('version_name', 30);
        $t->string('download_url', 500);
        $t->text('catatan')->nullable();
        $t->boolean('aktif')->default(true);
        $t->timestamps();
        $t->unique(['platform', 'version_code']);
    });
}

public function down(): void
{
    Schema::dropIfExists('versi_aplikasi');
}
};
