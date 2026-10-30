@extends('layouts.admin')
@section('title', 'Versi Aplikasi')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 p-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Versi Aplikasi</h1>
            <p class="text-slate-500 mt-1">Atur versi Android yang wajib dipasang pengguna</p>
        </div>
        <a href="{{ route('admin.versi-aplikasi.create') }}"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Versi
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Versi</th>
                    <th class="px-5 py-3.5">Link Unduhan</th>
                    <th class="px-5 py-3.5">Catatan</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5">Dibuat</th>
                    <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($versi as $v)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-4">
                        <div class="font-medium text-slate-800">{{ $v->version_name }}</div>
                        <div class="text-xs text-slate-400">Version code {{ $v->version_code }}</div>
                    </td>
                    <td class="px-5 py-4 max-w-xs">
                        <a href="{{ $v->download_url }}" target="_blank" rel="noopener"
                           class="text-emerald-700 hover:underline truncate block" title="{{ $v->download_url }}">
                            {{ \Illuminate\Support\Str::limit($v->download_url, 45) }}
                        </a>
                    </td>
                    <td class="px-5 py-4 text-slate-600 max-w-xs">
                        {{ \Illuminate\Support\Str::limit($v->catatan, 60) ?: '-' }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if(!$v->aktif)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-slate-200 text-slate-600">Nonaktif</span>
                        @elseif($v->version_code === (int) $terbaru)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Dikirim ke aplikasi</span>
                        @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Aktif (lama)</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-slate-500 text-xs">
                        {{ $v->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.versi-aplikasi.edit', $v) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form action="{{ route('admin.versi-aplikasi.destroy', $v) }}" method="POST"
                                  onsubmit="return confirm('Hapus versi {{ $v->version_name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada versi. Aplikasi tidak akan meminta pembaruan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($versi->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">{{ $versi->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>
@endsection