@php $tot = ['organik' => 0, 'anorganik' => 0, 'residu' => 0, 'timbulan' => 0]; @endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-800">Rekap Jumlah Sampah Terkelola per Bulan (ton/bulan)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Bulan</th>
                    <th class="px-4 py-3 text-left">Tahun</th>
                    <th class="px-4 py-3 text-right">Organik Terpilah</th>
                    <th class="px-4 py-3 text-right">Anorganik Terpilah</th>
                    <th class="px-4 py-3 text-right">Lainnya / Residu</th>
                    <th class="px-4 py-3 text-right">Timbulan Sampah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($bulanan as $i => $r)
                    @php
                        foreach (['organik', 'anorganik', 'residu', 'timbulan'] as $k) $tot[$k] += $r[$k];
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $r['bulan']->translatedFormat('F') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $r['bulan']->format('Y') }}</td>
                        <td class="px-4 py-3 text-right">{{ $ton($r['organik']) }}</td>
                        <td class="px-4 py-3 text-right">{{ $ton($r['anorganik']) }}</td>
                        <td class="px-4 py-3 text-right">{{ $ton($r['residu']) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $ton($r['timbulan']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 font-bold text-slate-800 border-t-2 border-slate-300">
                <tr>
                    <td colspan="3" class="px-4 py-3">Total (ton/tahun)</td>
                    <td class="px-4 py-3 text-right">{{ $ton($tot['organik']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($tot['anorganik']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($tot['residu']) }}</td>
                    <td class="px-4 py-3 text-right">{{ $ton($tot['timbulan']) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @include('admin.neraca-sampah.partials.ttd')
</div>