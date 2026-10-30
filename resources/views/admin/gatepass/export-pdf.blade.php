<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Gatepass</title>
<style>
    @page { margin: 28px 30px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
    h1 { font-size: 14px; margin: 0 0 4px; }
    table.meta td { padding: 1px 8px 1px 0; font-size: 9px; }
    table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.data th { background: #059669; color: #fff; padding: 5px 4px; border: 1px solid #047857; }
    table.data td { padding: 4px; border: 1px solid #cbd5e1; vertical-align: top; }
    table.data tbody tr:nth-child(even) td { background: #f8fafc; }
    table.data tfoot td { background: #ecfdf5; font-weight: bold; }
    .c { text-align: center; } .r { text-align: right; }
    .note { margin-top: 6px; color: #b45309; font-style: italic; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
</style>
</head>
<body>
    <h1>Laporan Pencatatan Kendaraan Sampah (Gatepass TPA)</h1>
    <table class="meta">
        @foreach($filter as $k => $v)
            <tr><td><b>{{ $k }}</b></td><td>: {{ $v }}</td></tr>
        @endforeach
        <tr><td><b>Dicetak</b></td><td>: {{ now()->translatedFormat('d M Y H:i') }}</td></tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:8%">Tanggal</th>
                <th style="width:5%">Jam</th>
                <th style="width:10%">Nomor Polisi</th>
                <th style="width:9%">Tipe</th>
                <th style="width:10%">Muatan</th>
                <th style="width:17%">Perusahaan / Dinas</th>
                <th style="width:19%">Sumber Sampah</th>
                <th style="width:8%">Volume (Ton)</th>
                <th style="width:9%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                <tr>
                    <td class="c">{{ $r[0] }}</td>
                    <td class="c">{{ $r[1] }}</td>
                    <td class="c">{{ $r[2] }}</td>
                    <td>{{ $r[3] }}</td>
                    <td>{{ $r[4] }}</td>
                    <td>{{ $r[5] }}</td>
                    <td>{{ $r[6] }}</td>
                    <td>{{ $r[7] }}</td>
                    <td class="r">{{ number_format($r[8], 2) }}</td>
                    <td class="c">{{ $r[9] }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="c" style="padding:14px">Tidak ada data untuk filter ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">TOTAL ({{ $total['ritasi'] }} ritasi, {{ $total['kendaraan'] }} kendaraan)</td>
                <td class="r">{{ number_format($total['volume'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @if($total['estimasi'] > 0)
        <p class="note">Catatan: termasuk estimasi {{ number_format($total['estimasi'], 2) }} Ton dari data rekap.</p>
    @endif
</body>
</html>