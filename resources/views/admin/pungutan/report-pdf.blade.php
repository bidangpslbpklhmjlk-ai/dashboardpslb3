<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Pungutan</title>
    <style>
        @page { margin: 12px 16px; }
        body { font-family: sans-serif; font-size: 8px; color: #1e293b; }
        h1 { font-size: 14px; margin-bottom: 2px; }
        p.subtitle { font-size: 10px; color: #64748b; margin-top: 0; margin-bottom: 4px; }
        p.page-info { font-size: 9px; color: #94a3b8; margin-top: 0; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 2px 3px; text-align: center; overflow: hidden; }
        th { background-color: #f1f5f9; font-weight: bold; }
        td.nama { text-align: left; white-space: nowrap; font-weight: 600; overflow: hidden; text-overflow: ellipsis; }
        td.kategori { text-align: left; white-space: nowrap; color: #64748b; overflow: hidden; text-overflow: ellipsis; }
        col.nama-col { width: 90px; }
        col.kategori-col { width: 55px; }
        .lunas { background-color: #d1fae5; color: #065f46; }
        .belum { background-color: #fef3c7; color: #92400e; }
        .batal { background-color: #fee2e2; color: #991b1b; }
        .kosong { color: #cbd5e1; }
        .summary { margin-bottom: 10px; font-size: 10px; }
        .summary span { margin-right: 16px; }
        .blok { page-break-before: always; }
        .legenda { font-size: 8px; color: #64748b; margin-top: 6px; }
    </style>
</head>
<body>
    <h1>Rekap Pungutan Retribusi</h1>
    <p class="subtitle">Periode {{ $tanggalMulai->format('d M Y') }} &mdash; {{ $tanggalAkhir->format('d M Y') }}</p>

    <div class="summary">
        <span><strong>Lunas:</strong> {{ $totalLunas }}</span>
        <span><strong>Belum Lunas:</strong> {{ $totalBelum }}</span>
        <span><strong>Batal:</strong> {{ $totalBatal }}</span>
    </div>

    @php
        $kolomPerHalaman = 15;
        $tanggalChunks = $tanggalRange->chunk($kolomPerHalaman);
        $totalHalaman = $tanggalChunks->count();
    @endphp

    @foreach($tanggalChunks as $i => $chunk)
    <div class="{{ $i > 0 ? 'blok' : '' }}">
        <p class="page-info">
            Bagian {{ $i + 1 }} dari {{ $totalHalaman }} &mdash;
            Tanggal {{ $chunk->first()->format('d M') }} s/d {{ $chunk->last()->format('d M Y') }}
        </p>
        <table>
            <colgroup>
                <col class="nama-col">
                <col class="kategori-col">
                @foreach($chunk as $tanggal)
                    <col>
                @endforeach
            </colgroup>
            <thead>
                <tr>
                    <th style="text-align:left;">Nama Object Retribusi</th>
                    <th style="text-align:left;">Kategori</th>
                    @foreach($chunk as $tanggal)
                        <th>{{ $tanggal->format('d/m') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($matrix as $row)
                <tr>
                    <td class="nama">{{ $row['objectRetribusi']->nama }}</td>
                    <td class="kategori">{{ $row['objectRetribusi']->kategori->nama ?? '-' }}</td>
                    @foreach($chunk as $tanggal)
                        @php $cell = $row['cells'][$tanggal->format('Y-m-d')]; @endphp
                        <td class="{{ $cell['kode'] }}">
                            @if($cell['kode'] === 'lunas') L
                            @elseif($cell['kode'] === 'belum') B
                            @elseif($cell['kode'] === 'batal') X
                            @else -
                            @endif
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($loop->last)
        <p class="legenda">Keterangan: L = Lunas, B = Belum Lunas, X = Batal, - = Tidak ada kunjungan</p>
        @endif
    </div>
    @endforeach
</body>
</html>