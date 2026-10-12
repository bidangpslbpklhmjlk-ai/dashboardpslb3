@extends('layouts.admin')

@section('title', 'Edit Kategori Object Retribusi')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.kategori-object-retribusi.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Kategori Object Retribusi</h1>
            <p class="text-slate-500 mt-1">{{ $kategoriObjectRetribusi->nama }}</p>
        </div>
    </div>

    <form action="{{ route('admin.kategori-object-retribusi.update', $kategoriObjectRetribusi) }}" method="POST"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        @include('admin.kategori-object-retribusi._form', ['kategori' => $kategoriObjectRetribusi])

        <p class="text-xs text-slate-400 -mt-2">Perubahan tarif hanya berlaku untuk invoice baru, invoice lama tidak berubah.</p>

        @php $dipakai = $kategoriObjectRetribusi->objectRetribusi()->count(); @endphp
        @if($dipakai > 0)
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-500">
                Kategori ini sedang dipakai oleh <span class="font-semibold text-slate-700">{{ $dipakai }} object retribusi</span> terdaftar.
            </div>
        @endif

        <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
            <a href="{{ route('admin.kategori-object-retribusi.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                💾 Perbarui
            </button>
        </div>
    </form>
</div>
@endsection