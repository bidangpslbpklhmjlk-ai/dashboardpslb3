@extends('layouts.admin')

@section('title', 'Tambah Rute Pengangkutan')

@section('content')
<div class="p-6 space-y-6 max-w-6xl">
    <div>
        <a href="{{ route('admin.rute-angkut.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">← Kembali ke daftar rute</a>
        <h1 class="mt-2 text-xl font-bold text-slate-900">Tambah Rute Pengangkutan</h1>
        <p class="text-sm text-slate-500">Pilih armada, hari, lalu susun lokasi pengambilan sesuai urutan jalan.</p>
    </div>

    @include('admin.rute-angkut._form', [
        'rute'   => null,
        'action' => route('admin.rute-angkut.store'),
        'method' => 'POST',
    ])
</div>
@endsection
