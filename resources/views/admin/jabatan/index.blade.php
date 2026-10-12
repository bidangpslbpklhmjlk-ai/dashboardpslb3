{{-- resources/views/admin/jabatan/index.blade.php --}}
{{-- NOTE: sesuaikan @extends() di bawah dengan nama layout admin yang sudah kamu pakai --}}
@extends('layouts.admin')

@section('title', 'Kelola Jabatan')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Kelola Jabatan</h1>
            <p class="text-sm text-slate-500">Daftar jabatan pegawai (mis. Pemungut, Petugas Lapangan, dll).</p>
        </div>
        <a href="{{ route('admin.jabatan.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-green-700/20 hover:bg-green-800 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Jabatan
        </a>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 rounded-xl bg-green-50 text-green-700 text-sm font-medium border border-green-100">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-100">
            {{ session('error') }}
        </div>
    @endif

    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama jabatan..."
               class="w-full max-w-xs px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
        <button type="submit" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">Cari</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold">Nama Jabatan</th>
                    <th class="text-left px-5 py-3 font-semibold">Keterangan</th>
                    <th class="text-left px-5 py-3 font-semibold">Jumlah Pegawai</th>
                    <th class="text-right px-5 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($jabatans as $jabatan)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $jabatan->nama_jabatan }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $jabatan->keterangan ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $jabatan->pegawais_count }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.jabatan.edit', $jabatan->id) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100">Edit</a>
                                <form action="{{ route('admin.jabatan.destroy', $jabatan->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus jabatan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-400">Belum ada data jabatan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $jabatans->links() }}
    </div>

</div>
@endsection
