@extends('layouts.admin')
@section('title', 'Pungutan')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pungutan</h1>
            <p class="text-slate-500 mt-1">Riwayat kunjungan petugas ke object retribusi</p>
        </div>
        <a href="{{ route('admin.pungutan.create') }}"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Kunjungan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Kunjungan Hari Ini</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['hari_ini'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Selesai</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['selesai'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Batal</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $stats['batal'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.pungutan.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Cari Object Retribusi</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama object retribusi..."
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Status</label>
                <select name="status" class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">🔍 Filter</button>
                <a href="{{ route('admin.pungutan.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Waktu</th>
                    <th class="px-5 py-3.5">Object Retribusi</th>
                    <th class="px-5 py-3.5">Petugas</th>
                    <th class="px-5 py-3.5">Invoice</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($kunjungan as $k)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-4 text-slate-600">{{ $k->waktu_kunjungan->format('d M Y H:i') }}</td>
                    <td class="px-5 py-4">
                        <div class="font-medium text-slate-800">{{ $k->objectRetribusi->nama ?? '-' }}</div>
                        <div class="text-xs text-slate-400">{{ $k->objectRetribusi->kategori->nama ?? '-' }}</div>
                    </td>
                    <td class="px-5 py-4 text-slate-600">{{ $k->petugas->name ?? '-' }}</td>
                    <td class="px-5 py-4">
                        @if($k->invoice)
                            <span class="font-mono text-xs text-slate-600">{{ $k->invoice->nomor_invoice }}</span>
                            @if($k->invoice->status === 'paid')
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Lunas</span>
                            @elseif($k->invoice->status === 'cancelled')
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Batal</span>
                            @else
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Belum Bayar</span>
                            @endif
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($k->status === 'selesai')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Selesai</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Batal</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
<div class="flex items-center justify-center gap-1">
    @if($k->invoice)
        <a href="{{ route('admin.invoice.pungutan.print', $k->invoice) }}" target="_blank"
           class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Cetak Invoice">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        </a>
    @endif
    <a href="{{ route('admin.pungutan.show', $k) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Detail">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
    </a>
    <form action="{{ route('admin.pungutan.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus data kunjungan ini?')">
        @csrf @method('DELETE')
        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </form>
</div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada data kunjungan.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($kunjungan->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">{{ $kunjungan->appends(request()->query())->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>
@endsection
