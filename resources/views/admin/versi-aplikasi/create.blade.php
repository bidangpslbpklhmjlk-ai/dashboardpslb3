@extends('layouts.admin')
@section('title', 'Tambah Versi Aplikasi')
@section('content')
<div class="max-w-3xl mx-auto space-y-6 p-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Tambah Versi Aplikasi</h1>
        <p class="text-slate-500 mt-1">Rilis baru yang akan diminta aplikasi untuk diperbarui</p>
    </div>

    <form action="{{ route('admin.versi-aplikasi.store') }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
        @csrf
        @include('admin.versi-aplikasi._form')
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.versi-aplikasi.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection