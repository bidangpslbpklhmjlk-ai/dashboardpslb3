<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Peran = template kumpulan menu (key dari config/menus.php)
        Schema::create('perans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('deskripsi')->nullable();
            $table->json('menus');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            // Nullable: pengguna tanpa peran memakai daftar menu kustom (users.menus).
            // restrictOnDelete: peran yang masih dipakai tidak bisa dihapus,
            // sehingga pengguna tidak tiba-tiba kehilangan semua menu.
            $table->foreignId('peran_id')->nullable()->after('role')
                ->constrained('perans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('peran_id');
        });

        Schema::dropIfExists('perans');
    }
};