 <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceObjectRetribusi->nomor_invoice }}</title>
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
        .status-cancelled { background: #fee2e2; color: #991b1b; }
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
        .footer { text-align: center; margin-top: 30px; color: #94a3b8; font-size: 12px; }
        @media print { body { background: white; padding: 0; } .invoice-box { box-shadow: none; } .no-print { display: none; } }
    </style>
</head>
<body>
@php
    $inv = $invoiceObjectRetribusi;
    $obj = $inv->objectRetribusi;
    $kategori = $obj->kategori ?? null;
    $kunjungan = $inv->kunjungan;
    $kolektif = (bool) ($obj->is_kolektif ?? false);
    $unit = $kolektif ? max(1, (int) $obj->jumlah_unit) : 1;
    $tarif = (int) ($kategori->tarif ?? 0);
    $satuan = $kategori->satuan ?? '';
    $tagihan = $tarif * $unit;
    $statusLabel = ['unpaid' => 'Belum Bayar', 'paid' => 'Lunas', 'cancelled' => 'Dibatalkan'][$inv->status] ?? $inv->status;
@endphp
    <div class="invoice-box">
        {{-- Header --}}
        <div class="header">
            <div>
                <div class="logo">DLH Majalengka</div>
                <p style="color: #64748b; font-size: 13px; margin-top: 5px;">Dinas Lingkungan Hidup Kab. Majalengka</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p style="color: #64748b; font-size: 14px;">{{ $inv->nomor_invoice }}</p>
                <span class="status status-{{ $inv->status }}">{{ $statusLabel }}</span>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="info-grid">
            <div class="info-box">
                <h4>Ditagihkan Kepada</h4>
                <p style="font-weight: 600;">{{ $obj->nama ?? '-' }}</p>
                <p>{{ $kategori->nama ?? '-' }}</p>
                @if($obj?->alamat)<p>{{ $obj->alamat }}</p>@endif
                @if($obj?->telepon)<p>Telp: {{ $obj->telepon }}</p>@endif
            </div>
            <div class="info-box">
                <h4>Detail Invoice</h4>
                <p><strong>Tanggal:</strong> {{ $kunjungan?->waktu_kunjungan?->format('d M Y') ?? $inv->created_at->format('d M Y') }}</p>
                <p><strong>Waktu:</strong> {{ $kunjungan?->waktu_kunjungan?->format('H:i') ?? $inv->created_at->format('H:i') }}</p>
                <p><strong>Petugas:</strong> {{ $kunjungan?->petugas?->name ?? '-' }}</p>
                @if($inv->status === 'paid' && $inv->paid_at)
                    <p><strong>Dibayar:</strong> {{ $inv->paid_at->format('d M Y H:i') }}</p>
                @endif
            </div>
        </div>

        {{-- Tabel --}}
        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Jumlah Unit</th>
                    <th>Tarif {{ $satuan }}</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $kategori->nama ?? 'Retribusi' }}</strong>{{ $kolektif ? ' (kolektif)' : '' }}<br>
                        <span style="color: #94a3b8; font-size: 13px;">{{ $obj->nama ?? '-' }}</span>
                    </td>
                    <td>{{ number_format($unit, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($tarif, 0, ',', '.') }} <span style="color: #94a3b8;">{{ $satuan }}</span></td>
                    <td style="text-align: right;"><strong>Rp {{ number_format($tagihan, 0, ',', '.') }}</strong></td>
                </tr>
                @if((int) $inv->total !== (int) $tagihan)
                <tr>
                    <td colspan="3" style="text-align: right;">Selisih dengan nominal diterima</td>
                    <td style="text-align: right;">Rp {{ number_format($inv->total - $tagihan, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr style="background: #f8fafc;">
                    <td colspan="3" style="text-align: right; font-weight: 600;">TOTAL</td>
                    <td class="total-row" style="text-align: right;">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($kunjungan?->catatan)
            <p style="color:#475569; font-size: 13px; margin-bottom: 20px;"><strong>Catatan:</strong> {{ $kunjungan->catatan }}</p>
        @endif

        {{-- Barcode & QR --}}
        <div class="barcode-section">
            <p style="color: #64748b; font-size: 12px; margin-bottom: 10px;">Scan untuk verifikasi keaslian</p>
            @if($inv->qrcode_path)
                <div style="margin: 10px auto; width: fit-content;">
                    <img src="{{ Storage::url($inv->qrcode_path) }}" alt="QR Code" width="120" height="120">
                </div>
            @endif
            <div class="barcode">{{ $inv->barcode }}</div>
            <p style="color: #94a3b8; font-size: 11px; margin-top: 10px;">
                Verifikasi online: {{ route('invoice.show', $inv->barcode) }}
            </p>
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