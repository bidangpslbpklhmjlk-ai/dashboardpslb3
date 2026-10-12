@extends('layouts.admin')

@section('title', 'Edit Rute Pengangkutan')

@section('content')
<div class="p-6 space-y-6 max-w-6xl">
    <div>
        <a href="{{ route('admin.rute-angkut.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">← Kembali ke daftar rute</a>
        <h1 class="mt-2 text-xl font-bold text-slate-900">Edit Rute Pengangkutan</h1>
        <p class="text-sm text-slate-500">
            {{ $rute->armada->nomor_polisi ?? '-' }} · {{ $rute->hari }}{{ $rute->nama ? ' · ' . $rute->nama : '' }}
        </p>
    </div>

    @include('admin.rute-angkut._form', [
        'rute'   => $rute,
        'action' => route('admin.rute-angkut.update', $rute),
        'method' => 'PUT',
    ])
</div>
@endsection
