@extends('layouts.admin')
@section('content')
@php use App\Support\Label; $field = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600'; @endphp
<div class="p-6 max-w-xl space-y-5">
    <div>
        <a href="{{ route('admin.kerusakan.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Kembali</a>
        <h1 class="text-xl font-bold text-slate-900 mt-1">Tandai Armada Rusak</h1>
    </div>
    @include('admin.partials.konflik')
    <form method="POST" action="{{ route('admin.kerusakan.store') }}" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
        @csrf
        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Armada</label>
            <select name="armada_id" class="{{ $field }}" required><option value="">— pilih —</option>
                @foreach($armadaList as $a)<option value="{{ $a->id }}" @selected(old('armada_id') == $a->id)>{{ Label::of($a) }}</option>@endforeach</select></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs font-semibold text-slate-600 mb-1">Rusak sejak</label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', now()->toDateString()) }}" class="{{ $field }}" required></div>
            <div><label class="block text-xs font-semibold text-slate-600 mb-1">Perkiraan selesai <span class="font-normal text-slate-400">(opsional)</span></label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="{{ $field }}"></div>
        </div>
        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan</label>
            <input name="keterangan" value="{{ old('keterangan') }}" placeholder="mis. mesin overheat, ban pecah" class="{{ $field }}"></div>
        <div class="flex justify-end"><button class="px-5 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold">Simpan & lihat jadwal terdampak</button></div>
    </form>
</div>
@endsection
