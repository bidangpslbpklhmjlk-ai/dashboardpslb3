<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'nomor_invoice', 'gatepass_log_id', 'armada_id',
        'volume_ton', 'tarif_total', 'total',
        'status', 'printed_at', 'paid_at',
        'barcode', 'qrcode_path',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
        'paid_at' => 'datetime',
        'volume_ton' => 'decimal:2',
        'tarif_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // ==================== RELASI ====================
    public function gatepassLog()
    {
        return $this->belongsTo(GatepassLog::class);
    }

    public function armada()
    {
        return $this->belongsTo(Armada::class);
    }

    // ==================== FACTORY ====================
    public static function createForGatepass(GatepassLog $gatepass, Armada $armada): self
    {
        $tarifTotal = 100000;

        return static::create([
            'gatepass_log_id' => $gatepass->id,
            'armada_id'       => $armada->id,
            'volume_ton'      => $gatepass->volume_ton,
            'tarif_total'     => $tarifTotal,
            'total'           => $tarifTotal,
            'status'          => 'unpaid',
            // nomor_invoice & barcode otomatis di-generate oleh booted() di bawah
        ]);
    }

    // ==================== HELPER STATUS ====================
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid', 'paid_at' => now()]);
    }

    public function markAsUnpaid(): void
    {
        $this->update(['status' => 'unpaid', 'paid_at' => null]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function markAsPrinted(): void
    {
        if (!$this->printed_at) {
            $this->update(['printed_at' => now()]);
        }
    }

    // ==================== GENERATE NOMOR & BARCODE ====================
    public static function generateNomorInvoice(): string
    {
        $tanggal = now()->format('Ymd');
        $prefix = "INV-{$tanggal}-";

        $lastNumber = static::where('nomor_invoice', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('nomor_invoice');

        $urutan = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return $prefix . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    public static function generateBarcode(): string
    {
        do {
            $barcode = strtoupper(Str::random(24));
        } while (static::where('barcode', $barcode)->exists());

        return $barcode;
    }

    protected static function booted()
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->nomor_invoice)) {
                $invoice->nomor_invoice = static::generateNomorInvoice();
            }
            if (empty($invoice->barcode)) {
                $invoice->barcode = static::generateBarcode();
            }
        });
    }
}