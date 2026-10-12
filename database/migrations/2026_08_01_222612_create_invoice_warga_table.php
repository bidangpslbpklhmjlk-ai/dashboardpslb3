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
Schema::create('invoice_warga', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kunjungan_id')->unique()->constrained('kunjungan');
    $table->foreignId('warga_id')->constrained('warga');
    $table->string('nomor_invoice', 50)->unique();
    $table->decimal('total', 12, 2);
    $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('printed_at')->nullable();
    $table->string('barcode', 100)->unique();
    $table->string('qrcode_path')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_warga');
    }
};
