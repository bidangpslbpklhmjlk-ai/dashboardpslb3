@extends('layouts.admin')

@section('title', $bsu->exists ? 'Ubah BSU' : 'Tambah BSU')

@section('content')
@php
    $nilai = fn ($k) => old($k, $bsu->$k);
    $input = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600';
@endphp

<div class="p-6 lg:p-8 space-y-6 max-w-3xl">
    @include('admin.bank-sampah._tabs')

    <h1 class="text-xl font-bold text-slate-800">{{ $bsu->exists ? 'Ubah BSU' : 'Tambah BSU' }}</h1>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $bsu->exists ? route('admin.bank-sampah.bsu.update', $bsu->id) : route('admin.bank-sampah.bsu.store') }}"
          x-data="{ kec: '{{ $nilai('kode_kecamatan') }}', desa: '{{ $nilai('kode_desa') }}', semua: @js($desaPerKec) }"
          class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($bsu->exists) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm text-slate-600">Nama BSU</label>
                <input type="text" name="nama" value="{{ $nilai('nama') }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="mb-1 block text-sm text-slate-600">Kecamatan</label>
                <select name="kode_kecamatan" x-model="kec" @change="desa = ''" class="{{ $input }}">
                    <option value="">Tingkat kabupaten (tanpa kecamatan)</option>
                    @foreach($kecamatan as $k)<option value="{{ $k->kode }}">{{ $k->nama }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm text-slate-600">Desa</label>
                <select name="kode_desa" x-model="desa" class="{{ $input }}">
                    <option value="">Tidak terhubung ke desa tertentu</option>
                    <template x-for="d in (semua[kec] || [])" :key="d.kode">
                        <option :value="d.kode" x-text="d.nama" :selected="d.kode === desa"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm text-slate-600">Jenis</label>
                <select name="jenis" class="{{ $input }}">
                    @foreach(['desa' => 'Desa', 'sekolah' => 'Sekolah', 'pkk' => 'PKK', 'lainnya' => 'Lainnya'] as $v => $l)
                        <option value="{{ $v }}" @selected($nilai('jenis') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 pt-6 text-sm text-slate-700">
                <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $bsu->aktif)) class="rounded border-slate-300 text-green-700 focus:ring-green-600">
                BSU aktif
            </label>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="mb-1 block text-sm text-slate-600">Ketua</label>
                <input type="text" name="ketua" value="{{ $nilai('ketua') }}" class="{{ $input }}"></div>
            <div><label class="mb-1 block text-sm text-slate-600">No HP</label>
                <input type="text" name="no_hp" value="{{ $nilai('no_hp') }}" class="{{ $input }}"></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm text-slate-600">Email</label>
                <input type="email" name="email" value="{{ $nilai('email') }}" class="{{ $input }}"></div>
            <div><label class="mb-1 block text-sm text-slate-600">Tenaga kerja pria</label>
                <input type="number" min="0" name="tenaga_pria" value="{{ $nilai('tenaga_pria') }}" class="{{ $input }}"></div>
            <div><label class="mb-1 block text-sm text-slate-600">Tenaga kerja wanita</label>
                <input type="number" min="0" name="tenaga_wanita" value="{{ $nilai('tenaga_wanita') }}" class="{{ $input }}"></div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="mb-1 block text-sm text-slate-600">Jenis legalitas</label>
                <input type="text" name="legalitas_jenis" value="{{ $nilai('legalitas_jenis') }}" class="{{ $input }}"></div>
            <div><label class="mb-1 block text-sm text-slate-600">Tanggal terbit</label>
                <input type="date" name="legalitas_tanggal"
                       value="{{ old('legalitas_tanggal', optional($bsu->legalitas_tanggal)->format('Y-m-d')) }}" class="{{ $input }}"></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm text-slate-600">Nomor dan penerbit legalitas</label>
                <textarea name="legalitas_keterangan" rows="2" class="{{ $input }}">{{ $nilai('legalitas_keterangan') }}</textarea></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm text-slate-600">Catatan</label>
                <textarea name="catatan" rows="2" class="{{ $input }}">{{ $nilai('catatan') }}</textarea></div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button class="rounded-lg bg-green-700 px-5 py-2 text-sm font-semibold text-white hover:bg-green-800">Simpan</button>
            <a href="{{ route('admin.bank-sampah.bsu.index') }}" class="text-sm text-slate-600 hover:underline">Batal</a>
        </div>
    </form>
</div>
@endsection