@extends('layouts.admin')
@section('title', 'Manajemen Pengguna')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 p-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Pengguna</h1>
            <p class="text-slate-500 mt-1">Atur akun admin dan menu yang boleh diakses</p>
        </div>
        <a href="{{ route('admin.pengguna.create') }}"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('admin.pengguna.index') }}" method="GET" class="flex gap-3 items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email..."
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">🔍 Cari</button>
            <a href="{{ route('admin.pengguna.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Pengguna</th>
                    <th class="px-5 py-3.5">Peran</th>
                    <th class="px-5 py-3.5">Akses Menu</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-4">
                        <div class="font-medium text-slate-800">{{ $u->name }}
                            @if($u->id === auth()->id())<span class="text-xs text-slate-400">(Anda)</span>@endif
                        </div>
                        <div class="text-xs text-slate-400">{{ $u->email }}</div>
                        @if($u->role !== 'superadmin' && $u->pegawai?->jabatan)
                            <div class="text-xs text-emerald-700 mt-0.5">{{ $u->pegawai->jabatan->nama_jabatan }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($u->role === 'superadmin')
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Superadmin</span>
                        @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Admin</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-slate-600">
                        @if($u->role === 'superadmin')
                            Semua menu
                        @else
                            {{ count($u->menus ?? []) }} dari {{ $totalMenu }} menu
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($u->is_active)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                        @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-slate-200 text-slate-600">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.pengguna.edit', $u) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('admin.pengguna.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus pengguna {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">{{ $users->appends(request()->query())->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>
@endsection