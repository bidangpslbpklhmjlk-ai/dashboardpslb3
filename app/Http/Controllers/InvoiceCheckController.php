<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceObjectRetribusi;
use Illuminate\Http\Request;

class InvoiceCheckController extends Controller
{
    public function index()
    {
        return view('invoice.check');
    }

    public function verify(Request $request)
    {
        $request->validate(['barcode' => 'required|string|max:100']);

        return redirect()->route('invoice.show', trim($request->barcode));
    }

    public function show(string $barcode)
    {
        $invoice = Invoice::with(['armada', 'gatepassLog'])
            ->where('barcode', $barcode)
            ->first();

        $invoiceObjectRetribusi = null;
        if (!$invoice) {
            $invoiceObjectRetribusi = InvoiceObjectRetribusi::with(['objectRetribusi', 'kunjungan.petugas'])
                ->where('barcode', $barcode)
                ->first();
        }

        abort_if(!$invoice && !$invoiceObjectRetribusi, 404);

        return view('invoice.show', compact('invoice', 'invoiceObjectRetribusi'));
    }
}