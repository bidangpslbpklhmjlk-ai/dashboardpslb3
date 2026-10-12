@extends('layouts.admin')

@section('title', 'Catat Kendaraan Masuk')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.gatepass.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Catat Kendaraan Masuk</h1>
            <p class="text-slate-500 mt-1">Waktu otomatis: {{ now()->format('H:i:s') }}</p>
        </div>
    </div>

    <form action="{{ route('admin.gatepass.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf

        {{-- Tanggal --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', now()->format('Y-m-d')) }}" required
                max="{{ now()->format('Y-m-d') }}"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            <p class="text-xs text-slate-400 mt-1">Default hari ini. Ubah kalau mencatat kendaraan yang masuk di tanggal lain.</p>
            @error('tanggal_masuk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Armada: nomor polisi, tipe kendaraan, perusahaan/dinas --}}
        @include('admin.gatepass._armada-picker', [
            'armadaOptions' => $armadaOptions,
            'selectedId'    => old('armada_id'),
        ])

        {{-- Jenis Muatan --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Muatan <span class="text-red-500">*</span></label>
            <select name="jenis_muatan" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white">
                <option value="">Pilih Jenis</option>
                @foreach(['Sampah Domestik','Sampah Komersial','Sampah B3','Limbah Organik','Residu'] as $jenis)
                    <option value="{{ $jenis }}" {{ old('jenis_muatan') === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                @endforeach
            </select>
            @error('jenis_muatan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Volume --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Volume (Ton) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="volume_ton" required placeholder="0.00" value="{{ old('volume_ton') }}"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('volume_ton')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Foto (Opsional) --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Foto Kendaraan <span class="text-slate-400 text-xs font-normal">(Opsional)</span>
            </label>
            <input type="file" name="photo" accept="image/*"
                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            <p class="text-xs text-slate-400 mt-1">Maksimal 2MB. Format: JPG, PNG, JPEG.</p>
            @error('photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Submit --}}
        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.gatepass.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                💾 Simpan Pencatatan
            </button>
        </div>
    </form>

</div>
@endsection