@extends('layouts.admin')

@section('title', 'Daftar BSU')

@section('content')
@php $hasil = session('hasil_bsu'); @endphp

<div class="p-6 lg:p-8 space-y-6">

    @include('admin.bank-sampah._tabs')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Daftar BSU</h1>
            <p class="text-sm text-slate-500">Daftar induk bank sampah ({{ $total }} unit). Timbangan dicatat per BSU.</p>
        </div>
        <a href="{{ route('admin.bank-sampah.bsu.create') }}"
           class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Tambah BSU</a>
    </div>

    @if(session('sukses'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('sukses') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($hasil)
        <div class="rounded-xl border {{ $hasil['disimpan'] ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }} p-4 text-sm space-y-3">
            <p class="font-semibold {{ $hasil['disimpan'] ? 'text-green-800' : 'text-amber-800' }}">
                {{ $hasil['disimpan'] ? 'Impor selesai dan data tersimpan.' : 'Pratinjau saja, belum ada data yang disimpan.' }}
            </p>
            <div class="flex flex-wrap gap-x-6 gap-y-1 text-slate-700">
                <span>Sheet dibaca: <b>{{ $hasil['sheet_dibaca'] }}</b></span>
                <span>BSU baru: <b>{{ $hasil['bsu_baru'] }}</b></span>
                <span>BSU diperbarui: <b>{{ $hasil['bsu_diperbarui'] }}</b></span>
            </div>
            @if(!empty($hasil['perlu_dicek']))
                <details open>
                    <summary class="cursor-pointer font-medium text-slate-800">Perlu dicek ({{ count($hasil['perlu_dicek']) }})</summary>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5 text-slate-700">@foreach($hasil['perlu_dicek'] as $c)<li>{{ $c }}</li>@endforeach</ul>
                </details>
            @endif
            @if(!empty($hasil['dilewati']))
                <details>
                    <summary class="cursor-pointer font-medium text-slate-800">Dilewati ({{ count($hasil['dilewati']) }})</summary>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5 text-slate-600">@foreach($hasil['dilewati'] as $c)<li>{{ $c }}</li>@endforeach</ul>
                </details>
            @endif
        </div>
    @endif

    {{-- Impor daftar induk --}}
    <form method="POST" action="{{ route('admin.bank-sampah.bsu.import') }}" enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <div class="mb-3 flex items-center justify-between gap-3">
    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Impor daftar BSU dari Excel</h2>
    <a href="{{ route('admin.bank-sampah.bsu.template') }}"
       class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Unduh template</a>
</div>
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="mb-1 block text-sm text-slate-600" for="file">File (satu sheet per kecamatan)</label>
                <input type="file" id="file" name="file" accept=".xlsx,.xls" required
                       class="block text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-slate-200">
            </div>
            <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                <input type="checkbox" name="pratinjau" value="1" checked class="rounded border-slate-300 text-green-700 focus:ring-green-600">
                Pratinjau saja (jangan simpan)
            </label>
            <button class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Proses</button>
        </div>
        <p class="mt-3 text-xs text-slate-500">NIK tidak diimpor. Impor ulang memperbarui BSU yang sama, tidak menggandakan.</p>
    </form>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="kecamatan" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600">
            <option value="">Semua kecamatan</option>
            @foreach($kecamatan as $k)
                <option value="{{ $k->kode }}" @selected(request('kecamatan') === $k->kode)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama BSU, ketua, atau desa"
               class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600">
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Cari</button>
    </form>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kecamatan</th>
                        <th class="px-4 py-3">Desa</th>
                        <th class="px-4 py-3">Nama BSU</th>
                        <th class="px-4 py-3">Ketua</th>
                        <th class="px-4 py-3">No HP</th>
                        <th class="px-4 py-3 text-right">Tenaga (P/W)</th>
                        <th class="px-4 py-3">Legalitas</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftar as $b)
                        <tr class="hover:bg-slate-50 {{ $b->aktif ? '' : 'opacity-60' }}">
                            <td class="px-4 py-2.5">{{ $b->kecamatan_nama ?? 'Tingkat Kabupaten' }}</td>
                            <td class="px-4 py-2.5">{{ $b->desa_nama ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-800">
                                {{ $b->nama }}
                                @unless($b->aktif)<span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">nonaktif</span>@endunless
                            </td>
                            <td class="px-4 py-2.5">{{ $b->ketua ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $b->no_hp ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $b->tenaga_pria ?? '-' }} / {{ $b->tenaga_wanita ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-slate-600">
                                {{ $b->legalitas_jenis ?? '-' }}
                                @if($b->legalitas_tanggal)<span class="block text-xs text-slate-400">{{ $b->legalitas_tanggal->translatedFormat('j F Y') }}</span>@endif
                            </td>
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.bank-sampah.bsu.edit', $b->id) }}" class="text-green-700 hover:underline">Ubah</a>
                                <form method="POST" action="{{ route('admin.bank-sampah.bsu.destroy', $b->id) }}" class="inline"
                                      onsubmit="return confirm('Hapus BSU ini?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-3 text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada BSU. Impor file Excel di atas atau klik Tambah BSU.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($daftar->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">{{ $daftar->links() }}</div>
        @endif
    </div>
</div>
@endsection