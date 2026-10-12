@extends('layouts.admin')

@section('title', 'Tarif')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Tarif</h1>
        <p class="text-slate-500 mt-1">Daftar tarif pembuangan sampah langsung</p>
    </div>

    @forelse($kelompoks as $kelompok => $items)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">{{ $kelompok }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 w-24">Kode</th>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3 text-right">Tarif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-mono text-slate-500">{{ $item->kode }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $item->nama }}</td>
                            <td class="px-5 py-3 text-right font-bold text-emerald-600">
                                Rp {{ number_format($item->tarif, 0, ',', '.') }}
                                <span class="text-xs text-slate-400 font-normal">{{ $item->satuan_label }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-slate-200 px-5 py-12 text-center text-slate-500">
            Belum ada kategori untuk Pembuangan Sampah Langsung ke TPA / TPSS.
        </div>
    @endforelse
</div>
@endsection