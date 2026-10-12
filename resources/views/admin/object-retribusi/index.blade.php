@extends('layouts.admin')
@section('title', 'Wajib Retribusi (Object Retribusi)')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Wajib Retribusi</h1>
            <p class="text-slate-500 mt-1">Data object retribusi terdaftar & titik lokasi pungutan</p>
        </div>
        <a href="{{ route('admin.object-retribusi.create') }}"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Daftarkan Object Retribusi
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.object-retribusi.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau alamat..."
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Kategori</label>
                <select name="kategori_id" class="w-48 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
    <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Petugas</label>
    <select name="pegawai_id" class="w-48 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
        <option value="">Semua</option>
        @foreach($pegawaiList as $p)
            <option value="{{ $p->id }}" {{ request('pegawai_id') == $p->id ? 'selected' : '' }}>{{ $p->nama_lengkap }}</option>
        @endforeach
    </select>
</div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Status</label>
                <select name="status" class="w-40 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">🔍 Filter</button>
                <a href="{{ route('admin.object-retribusi.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Nama</th>
                    <th class="px-5 py-3.5">Alamat</th>
                    <th class="px-5 py-3.5">Kategori</th>
                    <th class="px-5 py-3.5">Telepon</th>
                    <th class="px-5 py-3.5">Petugas</th>
                    <th class="px-5 py-3.5 text-center">Kolektif</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($objectRetribusi as $w)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-4 font-medium text-slate-800">{{ $w->nama }}</td>
                    <td class="px-5 py-4 text-slate-600 max-w-xs truncate">{{ $w->alamat }}</td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">{{ $w->kategori->nama ?? '-' }}</span>
                    </td>
                    <td class="px-5 py-4 text-slate-600">{{ $w->telepon ?? '-' }}</td>
<td class="px-5 py-4 text-slate-600">
    @forelse($w->pegawai as $p)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700 mr-1 mb-1">{{ $p->nama_lengkap }}</span>
    @empty
        <span class="text-slate-400 text-xs italic">Belum ditugaskan</span>
    @endforelse
</td>
                    <td class="px-5 py-4 text-center">
                        @if($w->is_kolektif)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Kolektif · {{ $w->jumlah_unit }} unit
                            </span>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($w->status === 'aktif')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.object-retribusi.edit', $w) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('admin.object-retribusi.destroy', $w) }}" method="POST" onsubmit="return confirm('Hapus object retribusi ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-slate-500">Belum ada object retribusi terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($objectRetribusi->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">{{ $objectRetribusi->appends(request()->query())->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>
@endsection