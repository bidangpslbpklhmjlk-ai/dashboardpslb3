{{-- resources/views/admin/pegawai/edit.blade.php --}}
@extends('layouts.admin')

@section('title', $pegawai->user_id ? 'Ubah Peran Pegawai' : 'Edit Pegawai')

@section('content')
<div class="p-6 max-w-2xl space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $pegawai->user_id ? 'Ubah Peran Pegawai' : 'Edit Pegawai' }}</h1>
        <p class="text-sm text-slate-500">
            {{ $pegawai->user_id
                ? 'Peran menentukan menu yang bisa dibuka. Perubahan di sini juga berlaku di Manajemen Pengguna.'
                : 'Perbarui data pegawai yang belum punya akun.' }}
        </p>
    </div>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm border border-red-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.pegawai.update', $pegawai->id) }}" method="POST"
          class="bg-white rounded-2xl border border-slate-200/80 p-6 space-y-5">
        @csrf
        @method('PUT')

        @if($pegawai->user_id)
            {{-- ===== Pegawai dengan akun: data hanya-baca, peran bisa diubah ===== --}}
            @php
                $peranSekarang = old('peran_id', $pegawai->user->peran_id);
            @endphp

            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama</dt>
                    <dd class="text-slate-800 font-medium">{{ $pegawai->nama_lengkap }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Akun</dt>
                    <dd class="text-slate-800">{{ $pegawai->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jabatan</dt>
                    <dd class="text-slate-800">{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</dt>
                    <dd class="text-slate-800">{{ $pegawai->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
            </dl>

            <div class="border-t border-slate-100 pt-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Peran <span class="text-red-500">*</span></label>
                <select name="peran_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
                    <option value="">-- Pilih Peran --</option>
                    @foreach($perans as $peran)
                        <option value="{{ $peran->id }}" @selected($peranSekarang == $peran->id)>
                            {{ $peran->nama }} ({{ count($peran->menuValid()) }} menu)
                        </option>
                    @endforeach
                </select>

                @if(!$pegawai->user->peran_id)
                    <p class="text-xs text-amber-600 mt-1.5">
                        Pegawai ini memakai menu kustom. Memilih peran akan mengganti menu kustom tersebut.
                    </p>
                @endif

                @if($perans->isEmpty())
                    <p class="text-xs text-amber-600 mt-1.5">Belum ada peran. Buat peran dulu di menu Peran.</p>
                @endif
            </div>

            <p class="text-xs text-slate-400">
                Untuk mengubah nama, status, jabatan, atau data lainnya, gunakan
                <a href="{{ route('admin.pengguna.edit', $pegawai->user_id) }}" class="underline hover:text-slate-600">Manajemen Pengguna</a>.
            </p>
        @else
            {{-- ===== Data lama tanpa akun ===== --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $pegawai->nama_lengkap) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">NIP (opsional)</label>
                    <input type="text" name="nip" value="{{ old('nip', $pegawai->nip) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jabatan</label>
                    <select name="jabatan_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}" @selected(old('jabatan_id', $pegawai->jabatan_id) == $jabatan->id)>{{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telepon</label>
                    <input type="text" name="no_telepon" value="{{ old('no_telepon', $pegawai->no_telepon) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
                        <option value="aktif" @selected(old('status', $pegawai->status) == 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status', $pegawai->status) == 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat (opsional)</label>
                <textarea name="alamat" rows="3"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">{{ old('alamat', $pegawai->alamat) }}</textarea>
            </div>

            <p class="text-xs text-slate-400">
                Pegawai ini belum punya akun, jadi belum bisa diberi peran.
                @if(auth()->user()->isSuperadmin())
                    <a href="{{ route('admin.pengguna.create', ['pegawai_id' => $pegawai->id]) }}" class="underline hover:text-slate-600">Buat akun sekarang</a>.
                @endif
            </p>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800">
                {{ $pegawai->user_id ? 'Simpan Peran' : 'Simpan Perubahan' }}
            </button>
            <a href="{{ route('admin.pegawai.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                Batal
            </a>
        </div>
    </form>

</div>
@endsection