@extends('layouts.admin')

@section('title', 'Edit Jabatan')

@section('content')
<div class="p-6 max-w-2xl space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900">Edit Jabatan</h1>
        <p class="text-sm text-slate-500">Perbarui data jabatan.</p>
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

    <form action="{{ route('admin.jabatan.update', $jabatan->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Jabatan</label>
            <input type="text" name="nama_jabatan" value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan (opsional)</label>
            <textarea name="keterangan" rows="3"
                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-700/20 focus:border-green-700">{{ old('keterangan', $jabatan->keterangan) }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.jabatan.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                Batal
            </a>
        </div>
    </form>

</div>
@endsection
