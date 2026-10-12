@extends('layouts.admin')

@section('title', 'Kategori Object Retribusi')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Kategori Object Retribusi</h1>
            <p class="text-slate-500 mt-1">Tarif retribusi jasa umum pelayanan kebersihan (Perda No. 7 Tahun 2023)</p>
        </div>
        <a href="{{ route('admin.kategori-object-retribusi.create') }}"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5 w-20">Kode</th>
                    <th class="px-5 py-3.5">Nama Kategori</th>
                    <th class="px-5 py-3.5 text-right">Tarif</th>
                    <th class="px-5 py-3.5 text-center">Jumlah Object Retribusi</th>
                    <th class="px-5 py-3.5 text-center">Total Unit Tertagih</th>
                    <th class="px-5 py-3.5 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($kategoris as $kelompok => $items)
                    <tr class="bg-slate-50/70">
                        <td colspan="6" class="px-5 py-2.5 text-xs font-bold uppercase tracking-wide text-slate-500">{{ $kelompok }}</td>
                    </tr>
                    @foreach($items as $kategori)
                        @php
                            // total unit = object individu (1 unit/entri) + jumlah_unit dari entri kolektif
                            $totalUnit = $kategori->object_retribusi_individu_count + ($kategori->total_unit_kolektif ?? 0);
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ $kategori->kode ?? '-' }}</td>
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $kategori->nama }}</td>
                            <td class="px-5 py-4 text-right">
                                <span class="font-semibold text-emerald-600">Rp {{ number_format($kategori->tarif, 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-400 block">{{ $kategori->satuan_label }}</span>
                            </td>
                            <td class="px-5 py-4 text-center text-slate-600">{{ $kategori->object_retribusi_count }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-slate-800 font-medium">{{ $totalUnit }}</span>
                                <span class="text-xs text-slate-400 block">≈ Rp {{ number_format($totalUnit * $kategori->tarif, 0, ',', '.') }} {{ strtolower($kategori->satuan_label) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.kategori-object-retribusi.edit', $kategori) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.kategori-object-retribusi.destroy', $kategori) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection