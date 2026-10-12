@extends('layouts.admin')

@section('title', 'Tambah Pegawai')

@section('content')
<div class="p-6 max-w-2xl space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900">Tambah Pegawai</h1>
        <p class="text-sm text-slate-500">Lengkapi data pegawai baru.</p>
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

    <form action="{{ route('admin.pegawai.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">NIP (opsional)</label>
                <input type="text" name="nip" value="{{ old('nip') }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jabatan</label>
                <select name="jabatan_id"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach($jabatans as $jabatan)
                        <option value="{{ $jabatan->id }}" @selected(old('jabatan_id') == $jabatan->id)>{{ $jabatan->nama_jabatan }}</option>
                    @endforeach
                </select>
                @if($jabatans->isEmpty())
                    <p class="text-xs text-amber-600 mt-1.5">Belum ada jabatan. <a href="{{ route('admin.jabatan.create') }}" class="underline">Tambah jabatan dulu</a>.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telepon</label>
                <input type="text" name="no_telepon" value="{{ old('no_telepon') }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                <select name="status"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
                    <option value="aktif" @selected(old('status', 'aktif') == 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status') == 'nonaktif')>Nonaktif</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat (opsional)</label>
            <textarea name="alamat" rows="3"
                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">{{ old('alamat') }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800">
                Simpan
            </button>
            <a href="{{ route('admin.pegawai.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                Batal
            </a>
        </div>
    </form>

</div>
@endsection
