{{-- resources/views/admin/jadwal-armada/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Jadwal Armada')

@section('content')
@php
    $inputCls = 'w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-white transition-shadow';
    $labelCls = 'block text-xs font-medium text-slate-600 mb-1.5 uppercase';

    // Ringkasan untuk kartu statistik
    $totalArmada = $armadas->count();
    $tanpaSopir  = $armadas->whereNull('pegawai_id')->count();
    $tanpaRute   = $armadas->filter(fn ($a) => collect($jadwal[$a->id])->flatten(1)->isEmpty())->count();
    $pekanBerjalan = $pekan === $pekanIni;
@endphp

<div class="max-w-7xl mx-auto space-y-5 sm:space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Jadwal Armada</h1>
            <p class="text-slate-500 text-sm mt-0.5">Jadwal mingguan armada Dinas LH per nomor polisi</p>
        </div>
        <a href="{{ route('admin.rute-angkut.index') }}"
           class="btn-press inline-flex items-center justify-center gap-2 border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-4 sm:px-5 py-2.5 rounded-lg font-medium text-sm whitespace-nowrap">
            Kelola di Rute Pengangkutan
        </a>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Armada Dinas LH Aktif</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">{{ number_format($totalArmada) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Belum Ada Sopir Default</p>
            <p class="text-2xl sm:text-3xl font-bold {{ $tanpaSopir ? 'text-amber-600' : 'text-emerald-600' }} mt-1">{{ number_format($tanpaSopir) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-slate-200">
            <p class="text-slate-500 text-sm font-medium">Belum Ada Jadwal (Minggu {{ $pekan }})</p>
            <p class="text-2xl sm:text-3xl font-bold {{ $tanpaRute ? 'text-amber-600' : 'text-emerald-600' }} mt-1">{{ number_format($tanpaRute) }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Filter Data</h3>

        <form action="{{ route('admin.jadwal-armada.index') }}" method="GET"
              class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 items-end">
            <input type="hidden" name="pekan" value="{{ $pekan }}">

            <div class="md:col-span-2">
                <label class="{{ $labelCls }}">Nomor Polisi / Nama Armada</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="D 1234 ABC"
                       class="{{ $inputCls }} uppercase">
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-2">
                <a href="{{ route('admin.jadwal-armada.index', ['pekan' => $pekan]) }}"
                   class="btn-press flex-1 text-center px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium text-sm hover:bg-slate-50">Reset</a>
                <button type="submit"
                        class="btn-press flex-1 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm shadow-sm hover:shadow-md">Cari</button>
            </div>
        </form>
    </div>

    {{-- Tabel jadwal --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center px-4 sm:px-5 py-4 border-b border-slate-200">
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-slate-700">Jadwal Minggu {{ $pekan }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $pekanBerjalan ? 'Pekan yang sedang berjalan' : 'Bukan pekan yang sedang berjalan' }}
                </p>
            </div>

            @if($siklus > 1)
                <div class="flex gap-1 bg-slate-100 p-1 rounded-lg self-start sm:self-auto">
                    @for($p = 1; $p <= $siklus; $p++)
                        <a href="{{ route('admin.jadwal-armada.index', array_filter(['pekan' => $p, 'search' => $search])) }}"
                           class="btn-press px-3 py-1.5 rounded-md text-xs font-medium {{ $p === $pekan ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500 hover:text-slate-700' }}">
                            Minggu {{ $p }}@if($p === $pekanIni) (ini)@endif
                        </a>
                    @endfor
                </div>
            @endif
        </div>

        <div class="overflow-x-auto min-h-[16rem]">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-3 sm:px-5 py-3.5 w-56 whitespace-nowrap">Nomor Polisi / Sopir</th>
                        @foreach($hari as $no => $nama)
                            <th class="px-3 py-3.5 min-w-[150px] whitespace-nowrap {{ ($pekanBerjalan && $no === $hariIni) ? 'bg-emerald-50 text-emerald-700' : '' }}">
                                {{ $nama }}
                                @if($pekanBerjalan && $no === $hariIni)
                                    <span class="ml-1 text-[10px] font-bold">hari ini</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($armadas as $a)
                        <tr class="align-top">
                            {{-- Identitas armada --}}
                            <td class="sticky left-0 z-10 bg-white px-3 sm:px-5 py-3 sm:py-4">
                                <p class="font-semibold text-slate-800 whitespace-nowrap">{{ $a->nomor_polisi ?: '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $a->nama }}</p>
                                <p class="text-xs mt-1 {{ $a->sopir ? 'text-slate-700' : 'text-amber-600' }}">
                                    {{ $a->sopir->nama_lengkap ?? 'Belum ada sopir default' }}
                                </p>
                                @if($a->kondisi && $a->kondisi !== \App\Models\Armada::KONDISI_BAIK)
                                    <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600">
                                        {{ $a->kondisi_label }}
                                    </span>
                                @endif
                            </td>

                            {{-- Senin sampai Minggu --}}
                            @foreach($hari as $no => $nama)
                                <td class="px-3 py-3 sm:py-4 {{ ($pekanBerjalan && $no === $hariIni) ? 'bg-emerald-50/40' : '' }}">
                                    @forelse($jadwal[$a->id][$no] as $e)
                                        <div class="mb-2 last:mb-0 rounded-lg border border-slate-200 bg-slate-50 p-2">
                                            <p class="text-xs font-semibold text-slate-800">{{ $e['rute'] }}</p>

                                            @if(count($e['lokasi']))
                                                <ul class="mt-1 space-y-0.5">
                                                    @foreach(array_slice($e['lokasi'], 0, 4) as $l)
                                                        <li class="text-[11px] text-slate-600 leading-snug">
                                                            {{ $l['nama'] }}@if($l['desa']) <span class="text-slate-400">({{ $l['desa'] }})</span>@endif
                                                            @if($l['pekan'])
                                                                <span class="ml-0.5 inline-flex items-center px-1 rounded text-[10px] font-bold bg-amber-100 text-amber-700">M{{ $l['pekan'] }}</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @if(count($e['lokasi']) > 4)
                                                    <p class="mt-1 text-[11px] text-slate-400"
                                                       title="{{ collect($e['lokasi'])->pluck('nama')->slice(4)->implode(', ') }}">
                                                        +{{ count($e['lokasi']) - 4 }} lokasi lainnya
                                                    </p>
                                                @endif
                                            @else
                                                <p class="mt-1 text-[11px] text-amber-600">Belum ada lokasi</p>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-slate-300 text-xs">-</span>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($hari) + 1 }}" class="px-5 py-12 text-center text-slate-500">
                                <p>Tidak ada armada Dinas LH aktif{{ $search !== '' ? ' untuk pencarian ini' : '' }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-5 py-3 border-t border-slate-200 bg-slate-50 text-xs text-slate-500">
            Label <span class="inline-flex items-center px-1 rounded text-[10px] font-bold bg-amber-100 text-amber-700">M1</span>
            dan <span class="inline-flex items-center px-1 rounded text-[10px] font-bold bg-amber-100 text-amber-700">M2</span>
            menandai lokasi yang hanya berlaku pada pekan itu. Lokasi tanpa label berlaku tiap minggu.
            Pengalihan karena armada rusak tidak tampil di halaman ini.
        </div>
    </div>

</div>
@endsection