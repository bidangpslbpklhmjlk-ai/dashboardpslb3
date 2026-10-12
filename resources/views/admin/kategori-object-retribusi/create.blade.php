@extends('layouts.admin')
@section('title', 'Tambah Kategori Object Retribusi')
@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Tambah Kategori Object Retribusi</h1>
    </div>
    <form action="{{ route('admin.kategori-object-retribusi.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @include('admin.kategori-object-retribusi._form', ['kategori' => null])
        <div class="flex gap-3 pt-2">
            <a href="{{ route('admin.kategori-object-retribusi.index') }}" class="flex-1 text-center px-4 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50">Batal</a>
            <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium shadow-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection