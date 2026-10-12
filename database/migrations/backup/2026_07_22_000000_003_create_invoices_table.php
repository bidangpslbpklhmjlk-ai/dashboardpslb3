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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice', 50)->unique();
            $table->foreignId('gatepass_log_id')->constrained('gatepass_logs')->onDelete('cascade');
            $table->foreignId('armada_id')->constrained('armadas')->onDelete('restrict');
            $table->decimal('volume_m3', 6, 2);
            $table->decimal('tarif_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->string('barcode', 100)->unique();
            $table->string('qrcode_path', 255)->nullable();
            $table->timestamps();

            $table->index('nomor_invoice');
            $table->index('barcode');
            $table->index('status');
            $table->index('armada_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
