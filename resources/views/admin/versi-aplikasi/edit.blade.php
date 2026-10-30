@extends('layouts.admin')
@section('title', 'Edit Versi Aplikasi')
@section('content')
<div class="max-w-3xl mx-auto space-y-6 p-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Edit Versi {{ $versi->version_name }}</h1>
    </div>

    <form action="{{ route('admin.versi-aplikasi.update', $versi) }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
        @csrf @method('PUT')
        @include('admin.versi-aplikasi._form')
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.versi-aplikasi.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection