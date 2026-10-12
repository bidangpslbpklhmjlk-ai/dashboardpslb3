@php
    $tot = [];
    foreach ($area['areas'] as $a) {
        $tot[$a->id] = ['organik' => 0, 'anorganik' => 0, 'residu' => 0, 'timbulan' => 0];
    }
    foreach ($area['rows'] as $r) {
        foreach ($area['areas'] as $a) {
            foreach (['organik', 'anorganik', 'residu', 'timbulan'] as $k) {
                $tot[$a->id][$k] += $r['area'][$a->id][$k];
            }
        }
    }
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-800">Rekap Data Pengelolaan Sampah per Lokasi Area (ton/bulan)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-600 font-semibold">
                <tr class="border-b border-slate-200">
                    <th rowspan="2" class="px-4 py-3 text-left sticky left-0 bg-slate-50">Bulan</th>
                    @foreach($area['areas'] as $a)
                        <th colspan="4" class="px-4 py-2 text-center border-l border-slate-200">{{ $a->nama }}</th>
                    @endforeach
                </tr>
                <tr class="border-b border-slate-200 text-xs">
                    @foreach($area['areas'] as $a)
                        <th class="px-3 py-2 text-right border-l border-slate-200">Organik</th>
                        <th class="px-3 py-2 text-right">Anorganik</th>
                        <th class="px-3 py-2 text-right">Residu</th>
                        <th class="px-3 py-2 text-right">Timbulan</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($area['rows'] as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-medium text-slate-800 sticky left-0 bg-white">{{ $r['bulan']->translatedFormat('F Y') }}</td>
                    @foreach($area['areas'] as $a)
                        @php $c = $r['area'][$a->id]; @endphp
                        <td class="px-3 py-2.5 text-right border-l border-slate-100">{{ $ton($c['organik']) }}</td>
                        <td class="px-3 py-2.5 text-right">{{ $ton($c['anorganik']) }}</td>
                        <td class="px-3 py-2.5 text-right">{{ $ton($c['residu']) }}</td>
                        <td class="px-3 py-2.5 text-right font-semibold">{{ $ton($c['timbulan']) }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 font-bold text-slate-800 border-t-2 border-slate-300">
                <tr>
                    <td class="px-4 py-3 sticky left-0 bg-slate-50">Total (ton/tahun)</td>
                    @foreach($area['areas'] as $a)
                        <td class="px-3 py-3 text-right border-l border-slate-200">{{ $ton($tot[$a->id]['organik']) }}</td>
                        <td class="px-3 py-3 text-right">{{ $ton($tot[$a->id]['anorganik']) }}</td>
                        <td class="px-3 py-3 text-right">{{ $ton($tot[$a->id]['residu']) }}</td>
                        <td class="px-3 py-3 text-right">{{ $ton($tot[$a->id]['timbulan']) }}</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
    @include('admin.neraca-sampah.partials.ttd')
</div>