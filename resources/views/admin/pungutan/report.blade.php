@extends('layouts.admin')
@section('title', 'Laporan Rekap Pungutan')
@section('content')
<div class="max-w-full mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Laporan Rekap Pungutan</h1>
            <p class="text-slate-500 mt-1">Status bayar tiap object retribusi per tanggal dalam rentang waktu tertentu</p>
        </div>
        <a href="{{ route('admin.pungutan.index') }}" class="px-4 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">
            Kembali ke Pungutan
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.pungutan.report') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai->format('Y-m-d') }}"
                    class="px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Sampai Tanggal</label>
                <input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir->format('Y-m-d') }}"
                    class="px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">
                    🔍 Tampilkan
                </button>
            </div>
            <div class="flex gap-2 ml-auto">
                <a href="{{ route('admin.pungutan.report.export.excel', request()->query()) }}"
                    class="px-4 py-2.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-medium text-sm flex items-center gap-2">
                    📊 Export Excel
                </a>
                <a href="{{ route('admin.pungutan.report.export.pdf', request()->query()) }}"
                    class="px-4 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-medium text-sm flex items-center gap-2">
                    📄 Export PDF
                </a>
            </div>
        </form>
        <p class="text-xs text-slate-400 mt-3">Rentang maksimal 90 hari sekali tampil, supaya tabel tidak terlalu lebar.</p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Total Lunas</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $totalLunas }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Total Belum Lunas</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $totalBelum }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Total Batal</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $totalBatal }}</p>
        </div>
    </div>

    {{-- Matrix --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 sticky left-0 bg-slate-50 z-10 min-w-[180px] border-r border-slate-200">Object Retribusi</th>
                        <th class="px-3 py-3 min-w-[120px] border-r border-slate-200">Kategori</th>
                        @foreach($tanggalRange as $tanggal)
                            <th class="px-2 py-3 text-center min-w-[70px] border-r border-slate-100">{{ $tanggal->format('d/m') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($matrix as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 sticky left-0 bg-white z-10 font-medium text-slate-800 border-r border-slate-200 whitespace-nowrap">
                            {{ $row['objectRetribusi']->nama }}
                        </td>
                        <td class="px-3 py-2.5 text-slate-500 border-r border-slate-200 whitespace-nowrap">
                            {{ $row['objectRetribusi']->kategori->nama ?? '-' }}
                        </td>
                        @foreach($row['cells'] as $cell)
                            <td class="px-2 py-2.5 text-center border-r border-slate-100">
                                @if($cell['kode'] === 'lunas')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-800">Lunas</span>
                                @elseif($cell['kode'] === 'belum')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800">Belum</span>
                                @elseif($cell['kode'] === 'batal')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-700">Batal</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr><td colspan="{{ 2 + $tanggalRange->count() }}" class="px-5 py-12 text-center text-slate-500">Tidak ada data object retribusi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection