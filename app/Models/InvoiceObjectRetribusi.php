<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceObjectRetribusi extends Model
{
    protected $table = 'invoice_object_retribusi';
    protected $fillable = ['kunjungan_id', 'object_retribusi_id', 'nomor_invoice', 'total', 'status', 'paid_at', 'printed_at', 'barcode', 'qrcode_path'];
    protected $casts = ['total' => 'decimal:2', 'paid_at' => 'datetime', 'printed_at' => 'datetime'];

    public function kunjungan() { return $this->belongsTo(Kunjungan::class); }
    public function objectRetribusi() { return $this->belongsTo(ObjectRetribusi::class); }

    public function isPaid() { return $this->status === 'paid'; }
    public function markAsPaid() { $this->update(['status' => 'paid', 'paid_at' => now()]); }
    public function markAsUnpaid() { $this->update(['status' => 'unpaid', 'paid_at' => null]); }
    public function markAsCancelled() { $this->update(['status' => 'cancelled']); }
    public function markAsPrinted() { if (!$this->printed_at) $this->update(['printed_at' => now()]); }
}
