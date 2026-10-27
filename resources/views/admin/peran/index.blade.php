{{-- resources/views/admin/peran/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Peran')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Peran</h1>
            <p class="text-sm text-slate-500">Template menu untuk pengguna. Pilih satu peran saat membuat pengguna, tidak perlu mencentang menu satu per satu.</p>
        </div>

        <a href="{{ route('admin.peran.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-green-700/20 hover:bg-green-800 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Peran
        </a>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm font-medium border border-green-100">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold">Nama Peran</th>
                    <th class="text-left px-5 py-3 font-semibold">Deskripsi</th>
                    <th class="text-left px-5 py-3 font-semibold">Menu</th>
                    <th class="text-left px-5 py-3 font-semibold">Pengguna</th>
                    <th class="text-right px-5 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($perans as $peran)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $peran->nama }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $peran->deskripsi ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ count($peran->menuValid()) }} menu</td>
                        <td class="px-5 py-3 text-slate-500">{{ $peran->users_count }} pengguna</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.peran.edit', $peran) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100">Edit</a>

                                @if($peran->users_count > 0)
                                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 bg-slate-50 cursor-not-allowed"
                                          title="Masih dipakai pengguna">Dipakai</span>
                                @else
                                    <form action="{{ route('admin.peran.destroy', $peran) }}" method="POST"
                                          onsubmit="return confirm('Hapus peran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada peran. Klik "Tambah Peran" untuk membuat template pertama.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection