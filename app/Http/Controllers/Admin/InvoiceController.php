<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceObjectRetribusi;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $sumber = $request->get('sumber', 'armada');

        if ($sumber === 'pungutan') {
            $query = InvoiceObjectRetribusi::with(['objectRetribusi', 'kunjungan.petugas']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_invoice', 'like', "%{$search}%")
                      ->orWhereHas('objectRetribusi', fn($q2) => $q2->where('nama', 'like', "%{$search}%"));
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $invoices = $query->latest()->paginate(15)->withQueryString();

            $summary = [
                'total' => InvoiceObjectRetribusi::count(),
                'paid' => InvoiceObjectRetribusi::where('status', 'paid')->count(),
                'unpaid' => InvoiceObjectRetribusi::where('status', 'unpaid')->count(),
                'cancelled' => InvoiceObjectRetribusi::where('status', 'cancelled')->count(),
                'total_pendapatan' => InvoiceObjectRetribusi::where('status', 'paid')->sum('total'),
            ];
        } else {
            $query = Invoice::with(['armada', 'gatepassLog']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_invoice', 'like', "%{$search}%")
                      ->orWhereHas('armada', fn($q2) => $q2->where('nama', 'like', "%{$search}%"))
                      ->orWhereHas('gatepassLog', fn($q2) => $q2->where('nomor_polisi', 'like', "%{$search}%"));
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $invoices = $query->latest()->paginate(15)->withQueryString();

            $summary = [
                'total' => Invoice::count(),
                'paid' => Invoice::where('status', 'paid')->count(),
                'unpaid' => Invoice::where('status', 'unpaid')->count(),
                'cancelled' => Invoice::where('status', 'cancelled')->count(),
                'total_pendapatan' => Invoice::where('status', 'paid')->sum('total'),
            ];
        }

        return view('admin.invoice.index', compact('invoices', 'summary', 'sumber'));
    }

    public function print(Invoice $invoice)
    {
        if (!$invoice->qrcode_path) {
            $filename = 'qrcodes/' . $invoice->barcode . '.svg';
            Storage::disk('public')->put($filename, QrCode::size(120)->generate(route('invoice.show', $invoice->barcode)));
            $invoice->update(['qrcode_path' => $filename]);
        }
        $invoice->markAsPrinted();
        $gatepass = $invoice->gatepassLog;
        return view('admin.invoice.invoice-print', compact('invoice', 'gatepass'));
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:unpaid,paid,cancelled']);
        match ($request->status) {
            'paid' => $invoice->markAsPaid(),
            'unpaid' => $invoice->markAsUnpaid(),
            'cancelled' => $invoice->markAsCancelled(),
        };
        return response()->json(['success' => true, 'status' => $invoice->status]);
    }

    public function printPungutan(InvoiceObjectRetribusi $invoiceObjectRetribusi)
    {
        if (!$invoiceObjectRetribusi->qrcode_path) {
            $filename = 'qrcodes/' . $invoiceObjectRetribusi->barcode . '.svg';
            Storage::disk('public')->put($filename, QrCode::size(120)->generate(route('invoice.show', $invoiceObjectRetribusi->barcode)));
            $invoiceObjectRetribusi->update(['qrcode_path' => $filename]);
        }
        $invoiceObjectRetribusi->markAsPrinted();
        return view('admin.invoice.invoice-pungutan-print', compact('invoiceObjectRetribusi'));
    }

    public function updateStatusPungutan(Request $request, InvoiceObjectRetribusi $invoiceObjectRetribusi)
    {
        $request->validate(['status' => 'required|in:unpaid,paid,cancelled']);
        match ($request->status) {
            'paid' => $invoiceObjectRetribusi->markAsPaid(),
            'unpaid' => $invoiceObjectRetribusi->markAsUnpaid(),
            'cancelled' => $invoiceObjectRetribusi->markAsCancelled(),
        };
        return response()->json(['success' => true, 'status' => $invoiceObjectRetribusi->status]);
    }
}