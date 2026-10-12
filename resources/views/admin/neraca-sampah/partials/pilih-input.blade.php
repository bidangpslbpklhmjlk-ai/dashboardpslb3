@php
    // Hari terisi dan total kg per bulan per area: [ym][area_id] => ['hari' => n, 'kg' => x]
    if (empty($terisi)) {
        $terisi = [];
        $rowsTerisi = \Illuminate\Support\Facades\DB::table('neraca_sampah_harian')
            ->where('laporan_id', $laporan->id)
            ->selectRaw('substr(tanggal, 1, 7) as ym, area_id, count(*) as hari, sum(organik_kg + anorganik_kg + residu_kg) as kg')
            ->groupBy('ym', 'area_id')
            ->get();

        foreach ($rowsTerisi as $r) {
            $terisi[$r->ym][$r->area_id] = ['hari' => (int) $r->hari, 'kg' => (float) $r->kg];
        }
    }
@endphp

{{-- Import dari Excel --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
    <h2 class="font-semibold text-slate-800">Import dari Excel</h2>
    <p class="text-xs text-slate-500 mt-1">
        Unggah file "Log Book dan Neraca Pengelolaan Sampah" apa adanya. Centang bulan yang ingin diimpor,
        hanya sheet bulan itu yang dibaca. Data pada tanggal dan area yang sama akan <strong>ditimpa</strong>.
    </p>

    @error('excel_file')
        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
    @enderror

    <form action="{{ route('admin.neraca-sampah.import', $laporan) }}" method="POST" enctype="multipart/form-data"
          onsubmit="if (!this.querySelectorAll('input[type=checkbox]:checked').length) { alert('Pilih minimal satu bulan yang akan diimpor.'); return false; } return confirm('Data pada tanggal dan area yang sama akan ditimpa. Lanjutkan?')"
          class="mt-3 flex flex-wrap items-center gap-3">
        @csrf
        <div class="flex flex-wrap gap-x-4 gap-y-1 w-full">
            @foreach($laporan->bulanList() as $ym => $tgl)
                <label class="inline-flex items-center gap-1.5 text-sm text-slate-700">
                    <input type="checkbox" name="bulan[]" value="{{ $ym }}" class="rounded border-slate-300">
                    {{ $tgl->translatedFormat('F Y') }}
                </label>
            @endforeach
        </div>
        <input type="file" name="excel_file" accept=".xlsx,.xlsm" required
               class="text-sm text-slate-600 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200">
        <button type="submit" class="px-5 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Import</button>
    </form>

    @if(session('import_summary'))
        <div class="mt-4 overflow-x-auto">
            <table class="text-sm w-full max-w-md">
                <thead class="text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-2 text-left">Bulan</th>
                        <th class="py-2 text-right">Baris</th>
                        <th class="py-2 text-right">Total (kg)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach(session('import_summary') as $bulan => $s)
                        <tr>
                            <td class="py-1.5">{{ $bulan }}</td>
                            <td class="py-1.5 text-right">{{ $s['baris'] }}</td>
                            <td class="py-1.5 text-right">{{ number_format($s['total_kg'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="mt-4 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
            <p class="font-medium mb-1">Catatan import:</p>
            <ul class="list-disc list-inside space-y-0.5 max-h-48 overflow-y-auto">
                @foreach(array_slice(session('import_errors'), 0, 50) as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            @if(count(session('import_errors')) > 50)
                <p class="mt-1 text-xs">… dan {{ count(session('import_errors')) - 50 }} catatan lainnya.</p>
            @endif
        </div>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-800">Input Data Harian</h2>
        <p class="text-xs text-slate-500 mt-1">
            Pilih bulan dan area yang akan diisi (satuan kg/hari).
            <span class="text-emerald-700 font-medium">Hijau</span> = semua hari terisi,
            <span class="text-amber-700 font-medium">kuning</span> = sebagian, abu-abu = belum ada data.
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">Bulan</th>
                    @foreach($areas as $a)
                        <th class="px-4 py-3 text-center text-xs">{{ $a->nama }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right">Total (kg)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($laporan->bulanList() as $ym => $tgl)
                    @php
                        // Jumlah hari yang seharusnya terisi: irisan bulan dengan periode laporan
                        $mulaiBulan = $tgl->copy()->max($laporan->periode_mulai->copy()->startOfDay());
                        $akhirBulan = $tgl->copy()->endOfMonth()->startOfDay()->min($laporan->periode_selesai->copy()->startOfDay());
                        $hariBulan  = (int) $mulaiBulan->diffInDays($akhirBulan, true) + 1;
                        $totalBulan = collect($terisi[$ym] ?? [])->sum('kg');
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $tgl->translatedFormat('F Y') }}</td>
                        @foreach($areas as $a)
                            @php
                                $t = $terisi[$ym][$a->id] ?? null;
                                $kelas = !$t
                                    ? 'text-slate-600 bg-slate-100 hover:bg-slate-200'
                                    : ($t['hari'] >= $hariBulan ? 'text-emerald-800 bg-emerald-100 hover:bg-emerald-200' : 'text-amber-800 bg-amber-100 hover:bg-amber-200');
                            @endphp
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.neraca-sampah.harian', [$laporan, $ym, $a]) }}"
                                   class="inline-block px-3 py-1.5 rounded-lg text-xs font-medium leading-tight {{ $kelas }}">
                                    @if($t)
                                        <span class="block font-semibold">{{ $t['hari'] }} / {{ $hariBulan }} hari</span>
                                        <span class="block text-[11px] opacity-80">{{ number_format($t['kg'], 1, ',', '.') }} kg</span>
                                    @else
                                        Isi
                                    @endif
                                </a>
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right font-semibold {{ $totalBulan > 0 ? 'text-slate-800' : 'text-slate-300' }}">{{ number_format($totalBulan, 1, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>