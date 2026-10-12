@extends('layouts.admin')

@section('title', 'Neraca Sampah')

@section('content')
@php
    $tahunLabel = fn ($l) => $l->periode_mulai->year === $l->periode_selesai->year
        ? (string) $l->periode_mulai->year
        : $l->periode_mulai->year . '/' . $l->periode_selesai->year;
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Neraca Sampah Industri</h1>
            <p class="text-slate-500 mt-1">Satu baris per industri. Klik Buka untuk melihat tahun dan bulannya.</p>
        </div>
        <button type="button" onclick="openModal()"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Laporan
        </button>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Tabel: satu baris per industri --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Industri</th>
                        <th class="px-5 py-3.5">Tahun</th>
                        <th class="px-5 py-3.5 text-center">Laporan</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($grup as $industriId => $items)
                    @php $industri = $items->first()->industri; @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $industri->nama }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($items->map($tahunLabel)->unique() as $t)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">{{ $t }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center text-slate-600">{{ $items->count() }}</td>
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('admin.neraca-sampah.industri', $industriId) }}"
                               class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Buka</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                            Belum ada laporan. Klik "Buat Laporan" untuk membuat yang pertama.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Buat Laporan --}}
<div id="modalLaporan" class="hidden fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-900">Buat Laporan Neraca Sampah</h2>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('admin.neraca-sampah.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Industri <span class="text-red-500">*</span></label>
                <select name="industri_id" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Pilih Industri</option>
                    @foreach($industriList as $i)
                        <option value="{{ $i->id }}" @selected(old('industri_id', request('industri')) == $i->id)>{{ $i->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Tahun</label>
                <input type="number" name="tahun" min="2000" max="2100" value="{{ old('tahun', now()->year) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <p class="text-xs text-slate-400 mt-1">Laporan dibuat satu kali untuk satu tahun (Januari sampai Desember). Bulan diisi atau diimport di dalamnya.</p>
            </div>

            <details class="text-sm" @if(old('periode_mulai') || old('periode_selesai')) open @endif>
                <summary class="cursor-pointer text-slate-600 font-medium">Periode bukan tahun kalender (mis. Juli sampai Juni)</summary>
                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Periode Mulai</label>
                        <input type="date" name="periode_mulai" value="{{ old('periode_mulai') }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Periode Selesai</label>
                        <input type="date" name="periode_selesai" value="{{ old('periode_selesai') }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-2">Jika kedua tanggal ini diisi, isian Tahun diabaikan. Maksimal 12 bulan.</p>
            </details>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal()  { document.getElementById('modalLaporan').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modalLaporan').classList.add('hidden'); }
    @if($errors->any() || request('industri')) openModal(); @endif
</script>
@endsection