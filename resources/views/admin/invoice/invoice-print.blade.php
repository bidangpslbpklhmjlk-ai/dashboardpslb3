<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $gatepass->invoice->nomor_invoice }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; padding: 40px; }
        .invoice-box { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0; }
        .logo { font-size: 24px; font-weight: bold; color: #059669; }
        .invoice-info { text-align: right; }
        .invoice-info h2 { color: #1e293b; font-size: 28px; margin-bottom: 5px; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-unpaid { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f8fafc; padding: 15px; border-radius: 8px; }
        .info-box h4 { font-size: 11px; text-transform: uppercase; color: #64748b; margin-bottom: 8px; letter-spacing: 0.05em; }
        .info-box p { color: #1e293b; font-size: 14px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f1f5f9; text-align: left; padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .total-row { font-size: 18px; font-weight: bold; color: #059669; }
        .barcode-section { text-align: center; margin-top: 30px; padding-top: 30px; border-top: 2px dashed #e2e8f0; }
        .barcode { font-family: 'Courier New', monospace; font-size: 14px; letter-spacing: 4px; background: #f1f5f9; padding: 10px 20px; border-radius: 6px; display: inline-block; margin: 10px 0; }
        .qr-placeholder { width: 120px; height: 120px; background: #f1f5f9; border-radius: 8px; margin: 10px auto; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 12px; }
        .footer { text-align: center; margin-top: 30px; color: #94a3b8; font-size: 12px; }
        @media print { body { background: white; padding: 0; } .invoice-box { box-shadow: none; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="invoice-box">
        {{-- Header --}}
        <div class="header">
            <div>
                <div class="logo">DLH Majalengka</div>
                <p style="color: #64748b; font-size: 13px; margin-top: 5px;">Dinas Lingkungan Hidup Kab. Majalengka</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p style="color: #64748b; font-size: 14px;">{{ $gatepass->invoice->nomor_invoice }}</p>
                <span class="status status-{{ $gatepass->invoice->status }}">{{ $gatepass->invoice->status }}</span>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="info-grid">
            <div class="info-box">
                <h4>Ditagihkan Kepada</h4>
                <p style="font-weight: 600;">{{ $gatepass->armada->nama }}</p>
                <p>{{ $gatepass->armada->tipe === 'dinas' ? 'Dinas' : 'Perusahaan' }}</p>
                @if($gatepass->armada->alamat)<p>{{ $gatepass->armada->alamat }}</p>@endif
            </div>
            <div class="info-box">
                <h4>Detail Invoice</h4>
                <p><strong>Tanggal:</strong> {{ $gatepass->waktu_masuk->format('d M Y') }}</p>
                <p><strong>Waktu Masuk:</strong> {{ $gatepass->waktu_masuk->format('H:i') }}</p>
                <p><strong>Nomor Polisi:</strong> {{ $gatepass->nomor_polisi }}</p>
            </div>
        </div>

        {{-- Tabel --}}
        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Volume (Ton)</th>
                    <th>Tarif</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $gatepass->tipe_kendaraan }}</strong><br>
                        <span style="color: #94a3b8; font-size: 13px;">{{ $gatepass->jenis_muatan }}</span>
                    </td>
                    <td>{{ number_format($gatepass->volume_ton, 2) }}</td>
                    <td>Rp {{ number_format($gatepass->invoice->tarif_total, 0, ',', '.') }} <span style="color: #94a3b8;">(flat)</span></td>
                    <td style="text-align: right;"><strong>Rp {{ number_format($gatepass->invoice->total, 0, ',', '.') }}</strong></td>
                </tr>
                <tr style="background: #f8fafc;">
                    <td colspan="3" style="text-align: right; font-weight: 600;">TOTAL</td>
                    <td class="total-row" style="text-align: right;">Rp {{ number_format($gatepass->invoice->total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

{{-- Barcode & QR --}}
<div class="barcode-section">
    <p style="color: #64748b; font-size: 12px; margin-bottom: 10px;">Scan untuk verifikasi keaslian</p>

    @if($gatepass->invoice)
        <div style="margin: 10px auto; width: fit-content;">
            <img src="{{ Storage::url($gatepass->invoice->qrcode_path) }}" alt="QR Code" width="120" height="120">
        </div>
        <div class="barcode">{{ $gatepass->invoice->barcode }}</div>
        <p style="color: #94a3b8; font-size: 11px; margin-top: 10px;">
            Verifikasi online: {{ route('invoice.show', $gatepass->invoice->barcode) }}
        </p>
    @else
        <p style="color:#94a3b8; font-size: 13px;">Kendaraan dinas — tidak dikenakan retribusi.</p>
    @endif
</div>

        <div class="footer">
            <p>Invoice ini sah dan diproses secara elektronik.</p>
            <p>DLH Majalengka &copy; {{ date('Y') }}</p>
        </div>
    </div>

    {{-- Tombol Cetak --}}
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 24px; background: #059669; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
            🖨️ Cetak Invoice
        </button>
<a href="{{ url()->previous() }}" style="margin-left: 10px; padding: 10px 24px; background: #e2e8f0; color: #475569; border: none; border-radius: 8px; text-decoration: none; font-weight: 600;">
    Kembali
</a>
    </div>
</body>
</html>