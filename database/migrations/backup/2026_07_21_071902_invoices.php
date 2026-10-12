<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice', 50)->unique(); // INV-20260721-0001
            $table->foreignId('gatepass_log_id')->constrained('gatepass_logs')->onDelete('cascade');
            $table->foreignId('perusahaan_id')->constrained('perusahaans')->onDelete('restrict');
            $table->decimal('volume_m3', 6, 2);
            $table->decimal('tarif_per_m3', 10, 2);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->string('barcode', 100)->unique();
            $table->string('qrcode_path', 255)->nullable();
            $table->timestamps();

            $table->index('nomor_invoice');
            $table->index('barcode');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};