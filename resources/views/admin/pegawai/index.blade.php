{{-- resources/views/admin/pegawai/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Data Pegawai')

@section('content')
<div class="p-6 space-y-6">

    <div x-data="{ showImportModal: false }" class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Data Pegawai</h1>
            <p class="text-sm text-slate-500">Data pegawai bersumber dari Manajemen Pengguna. Pegawai baru ditambahkan lewat menu Pengguna.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pegawai.template') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                Download Template
            </a>

            <button type="button" @click="showImportModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/></svg>
                Upload CSV
            </button>

            @if(auth()->user()->isSuperadmin())
                <a href="{{ route('admin.pengguna.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-green-700/20 hover:bg-green-800 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Pegawai
                </a>
            @endif
        </div>

        {{-- Modal Upload CSV --}}
        <div x-show="showImportModal" x-cloak
             class="fixed inset-0 z-[60] flex items-center justify-center p-4"
             style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="showImportModal = false"></div>

            <div class="relative bg-white rounded-2xl border border-slate-200/80 shadow-xl w-full max-w-md p-6 space-y-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Upload CSV Data Pegawai</h3>
                        <p class="text-xs text-slate-500 mt-1">Gunakan format sesuai template. Kolom wajib: <span class="font-semibold">nama_lengkap</span> &amp; <span class="font-semibold">nama_jabatan</span>.</p>
                    </div>
                    <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.pegawai.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <input type="file" name="csv_file" accept=".csv,.txt" required
                               class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 file:text-sm file:font-semibold hover:file:bg-green-100">
                        <p class="text-[11px] text-slate-400 mt-1.5">Nama jabatan pada CSV harus sudah terdaftar di menu Jabatan. Maks. 2MB.</p>
                        <p class="text-[11px] text-amber-600 mt-1">Pegawai hasil import belum memiliki akun. Tautkan lewat form Pengguna.</p>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="submit" class="px-4 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800">
                            Import Sekarang
                        </button>
                        <a href="{{ route('admin.pegawai.template') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 underline">
                            Belum punya template?
                        </a>
                    </div>
                </form>
            </div>
        </div>
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
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="px-4 py-3 rounded-xl bg-amber-50 text-amber-700 text-sm border border-amber-100">
            <p class="font-semibold mb-1.5">Baris yang dilewati saat import:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIP..."
               class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">

        <select name="jabatan_id" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
            <option value="">Semua Jabatan</option>
            @foreach($jabatans as $jabatan)
                <option value="{{ $jabatan->id }}" @selected(request('jabatan_id') == $jabatan->id)>{{ $jabatan->nama_jabatan }}</option>
            @endforeach
        </select>

        <button type="submit" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">Filter</button>
        <a href="{{ route('admin.pegawai.index') }}" class="px-4 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-800">Reset</a>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold">Nama</th>
                    <th class="text-left px-5 py-3 font-semibold">NIP</th>
                    <th class="text-left px-5 py-3 font-semibold">Jabatan</th>
                    <th class="text-left px-5 py-3 font-semibold">No. Telepon</th>
                    <th class="text-left px-5 py-3 font-semibold">Akun</th>
                    <th class="text-left px-5 py-3 font-semibold">Status</th>
                    <th class="text-right px-5 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pegawais as $pegawai)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $pegawai->nama_lengkap }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $pegawai->nip ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $pegawai->no_telepon ?? '-' }}</td>
                        <td class="px-5 py-3">
@if($pegawai->user)
    <span class="text-slate-600">{{ $pegawai->user->email }}</span>
    <span class="block text-[11px] text-slate-400">
        {{ $pegawai->user->peran->nama ?? 'Menu kustom' }}
    </span>
@else
    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700">Belum punya akun</span>
@endif
                        </td>
                        <td class="px-5 py-3">
                            @if($pegawai->status === 'aktif')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-50 text-green-700">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if($pegawai->user_id)
                                    {{-- Pegawai dengan akun: data diatur dari Manajemen Pengguna --}}
                                @if(auth()->user()->isSuperadmin())
                                    <a href="{{ route('admin.pegawai.edit', $pegawai->id) }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100">Ubah Peran</a>
                                @else
                                    <span class="text-xs text-slate-400">Diatur di Pengguna</span>
                                @endif
                                @else
                                    {{-- Data lama tanpa akun --}}
                                    <a href="{{ route('admin.pegawai.edit', $pegawai->id) }}"
                                       class="px-3 py-1.5 rounded-lg text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100">Edit</a>
                                    <form action="{{ route('admin.pegawai.destroy', $pegawai->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus data pegawai ini?');">
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
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada data pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $pegawais->links() }}
    </div>

</div>
@endsection