<?php

namespace App\Observers;

use App\Models\GatepassLog;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class GatepassLogObserver
{
    public function created(GatepassLog $gatepassLog): void
    {
        if (!$gatepassLog->needsInvoice()) {
            return;
        }
            if (!$gatepassLog->needsInvoice()) {
        return;
        }

        $tarif = $gatepassLog->armada->tarif;

        if (!$tarif) {
            Log::warning("GatepassLog #{$gatepassLog->id}: armada berbayar tapi belum punya tarif, invoice tidak dibuat.");
            return;
        }

            if ($gatepassLog->asal_data === 'impor_rekap') {
        return;
    }

    if (!$gatepassLog->needsInvoice()) {
        return;
    }

        $volumeTon = $gatepassLog->volume_ton ?? 0;
        $ritasi = $gatepassLog->ritasi ?? 1;

        $total = $tarif->isPerRitasi()
            ? $tarif->nominal * $ritasi
            : $tarif->nominal * $volumeTon;

        Invoice::create([
            'gatepass_log_id' => $gatepassLog->id,
            'armada_id' => $gatepassLog->armada_id,
            'volume_ton' => $volumeTon,
            'tarif_total' => $tarif->nominal,
            'total' => $total,
            'status' => 'unpaid',
        ]);
    }
}