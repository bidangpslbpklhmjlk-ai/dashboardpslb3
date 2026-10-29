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
    Schema::create('login_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()
              ->constrained()->nullOnDelete();
        $table->string('email')->nullable();
        $table->string('status', 20);
        $table->string('device_id', 64)->nullable();
        $table->string('device_name', 100)->nullable();
        $table->string('ip_address', 45)->nullable(); // cukup untuk IPv6
        $table->timestamp('created_at')->useCurrent();

        $table->index(['user_id', 'created_at']);
        $table->index('created_at');
    });
}

public function down(): void
{
    Schema::dropIfExists('login_logs');
}
};
