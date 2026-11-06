@extends('layouts.admin')

@section('title', 'Bank Sampah')

@section('content')
@php
    $hasil = session('hasil_import');
    $fmt   = fn ($n) => number_format((float) $n, 0, ',', '.');
    $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $jml   = [
        'desa'  => $rekap->sum('jumlah_desa'),
        'bsu'   => $rekap->sum('jumlah_bsu'),
        'aktif' => $rekap->sum('desa_aktif'),
        'belum' => $rekap->sum('desa_belum'),
    ];
    $persen = $jml['desa'] ? (int) round($jml['aktif'] / $jml['desa'] * 100) : 0;
@endphp

<div class="p-6 lg:p-8 space-y-6">

    @include('admin.bank-sampah._tabs')

    {{-- Judul + pilih tahun --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Bank Sampah</h1>
            <p class="text-sm text-slate-500">Rekap penimbangan per kecamatan. Status desa dihitung dari ada atau tidaknya penimbangan.</p>
        </div>
<form method="GET" class="flex flex-wrap items-center gap-2">
    <label for="tahun" class="text-sm text-slate-600">Tahun</label>
    <input type="number" id="tahun" name="tahun" value="{{ $tahun }}" min="2000" max="2100"
           class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600">
    <select name="bulan" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600">
        <option value="">Setahun penuh</option>
        @foreach($namaBulan as $i => $nm)
            <option value="{{ $i + 1 }}" @selected($bulan === $i + 1)>{{ $nm }}</option>
        @endforeach
    </select>
    <button class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Tampilkan</button>
</form>
    </div>

    {{-- Pesan galat --}}
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Hasil impor terakhir --}}
    @if($hasil)
        <div class="rounded-xl border {{ $hasil['disimpan'] ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }} p-4 text-sm space-y-3">
            <p class="font-semibold {{ $hasil['disimpan'] ? 'text-green-800' : 'text-amber-800' }}">
                {{ $hasil['disimpan'] ? 'Impor selesai dan data tersimpan.' : 'Pratinjau saja, belum ada data yang disimpan.' }}
            </p>
            <div class="flex flex-wrap gap-x-6 gap-y-1 text-slate-700">
                <span>Baris dibaca: <b>{{ $hasil['baris_dibaca'] }}</b></span>
                <span>Bank sampah baru: <b>{{ $hasil['bank_baru'] }}</b></span>
                <span>Data timbangan: <b>{{ $hasil['timbangan_disimpan'] }}</b></span>
            </div>

            @if(!empty($hasil['perlu_dicek']))
                <details open>
                    <summary class="cursor-pointer font-medium text-slate-800">Perlu dicek ({{ count($hasil['perlu_dicek']) }})</summary>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5 text-slate-700">
                        @foreach($hasil['perlu_dicek'] as $c)<li>{{ $c }}</li>@endforeach
                    </ul>
                </details>
            @endif

            @if(!empty($hasil['dilewati']))
                <details>
                    <summary class="cursor-pointer font-medium text-slate-800">Baris dilewati ({{ count($hasil['dilewati']) }})</summary>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5 text-slate-600">
                        @foreach($hasil['dilewati'] as $c)<li>{{ $c }}</li>@endforeach
                    </ul>
                </details>
            @endif
        </div>
    @endif

    {{-- Form impor --}}
<details class="rounded-2xl border border-slate-200 bg-white shadow-sm" @if($errors->any()) open @endif>
<summary class="cursor-pointer px-5 py-3 text-sm font-bold uppercase tracking-wider text-slate-500">Impor timbangan dari Excel</summary>
<form method="POST" action="{{ route('admin.bank-sampah.import') }}" enctype="multipart/form-data" class="border-t border-slate-100 p-5">
        @csrf
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Impor dari Excel</h2>
            <a href="{{ route('admin.bank-sampah.template') }}"
               class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Unduh template</a>
        </div>
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="mb-1 block text-sm text-slate-600" for="file">File (sheet "Detail Data")</label>
                <input type="file" id="file" name="file" accept=".xlsx,.xls" required
                       class="block text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm text-slate-600" for="tahun_impor">Tahun data</label>
                <input type="number" id="tahun_impor" name="tahun" value="{{ old('tahun', $tahun) }}" min="2000" max="2100" required
                       class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-green-600">
            </div>
            <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                <input type="checkbox" name="pratinjau" value="1" checked class="rounded border-slate-300 text-green-700 focus:ring-green-600">
                Pratinjau saja (jangan simpan)
            </label>
            <button class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Proses</button>
        </div>
        <p class="mt-3 text-xs text-slate-500">Impor ulang aman: data bulan yang sama diperbarui, bukan digandakan. Bulan kosong atau "-" tidak disimpan.</p>
    </form>

    {{-- Ringkasan total --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Desa</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ $fmt($total->desa) }} <span class="text-sm font-medium text-slate-400">kg</span></p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Unit Non-desa (Kecamatan)</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ $fmt($total->unit_kec) }} <span class="text-sm font-medium text-slate-400">kg</span></p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat Kabupaten</p>
            <p class="mt-1 text-xl font-bold text-slate-800">{{ $fmt($total->kabupaten) }} <span class="text-sm font-medium text-slate-400">kg</span></p>
        </div>
        <div class="rounded-2xl border border-green-200 bg-green-50 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-green-700">Total {{ $bulan ? $namaBulan[$bulan - 1].' ' : '' }}{{ $tahun }}</p>
            <p class="mt-1 text-xl font-bold text-green-800">{{ $fmt($total->semua) }} <span class="text-sm font-medium text-green-600">kg</span></p>
            <p class="mt-1 text-xs text-green-700">{{ $bsuAktif }} BSU aktif, {{ $jml['aktif'] }} dari {{ $jml['desa'] }} desa menimbang ({{ $persen }}%)</p>
        </div>
    </div>

{{-- Tren bulanan: klik batang untuk memfilter bulan --}}
@php $maks = max($tren->max(), 1); @endphp
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Tren bulanan {{ $tahun }}</h2>
    <div class="mt-4 flex h-44 items-end gap-2">
        @foreach($tren as $i => $kg)
            <a href="{{ request()->fullUrlWithQuery(['bulan' => $bulan === $i + 1 ? null : $i + 1]) }}"
               title="{{ $namaBulan[$i] }}: {{ $fmt($kg) }} kg"
               class="flex flex-1 flex-col items-center justify-end gap-1">
                <span class="text-[10px] text-slate-500">{{ $kg ? $fmt($kg) : '' }}</span>
                <div class="w-full rounded-t {{ $bulan === $i + 1 ? 'bg-green-700' : 'bg-green-300 hover:bg-green-400' }}"
                     style="height: {{ max(2, (int) round($kg / $maks * 110)) }}px"></div>
                <span class="text-[10px] text-slate-500">{{ mb_substr($namaBulan[$i], 0, 3) }}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- Rekap per kecamatan: klik baris kecamatan untuk membuka desa dan BSU --}}
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3 text-right">Desa</th>
                    <th class="px-4 py-3 text-right">BSU</th>
                    <th class="px-4 py-3 text-right">Aktif</th>
                    <th class="px-4 py-3 text-right">Belum</th>
                    <th class="px-4 py-3 text-right">% Aktif</th>
                    <th class="px-4 py-3 text-right">Desa (kg)</th>
                    <th class="px-4 py-3 text-right">Non-desa (kg)</th>
                    <th class="px-4 py-3 text-right">Total (kg)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($rekap as $k)
                    <tr class="cursor-pointer hover:bg-slate-50" data-toggle="{{ $k->kode }}">
                        <td class="px-4 py-2.5 font-medium text-slate-800">
                            <span class="panah mr-1 inline-block text-slate-400 transition-transform">▸</span>{{ $k->nama }}
                        </td>
                        <td class="px-4 py-2.5 text-right">{{ $k->jumlah_desa }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $k->jumlah_bsu }}</td>
                        <td class="px-4 py-2.5 text-right text-green-700">{{ $k->desa_aktif }}</td>
                        <td class="px-4 py-2.5 text-right {{ $k->desa_belum ? 'text-red-600' : 'text-slate-400' }}">{{ $k->desa_belum }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $k->persen }}%</td>
                        <td class="px-4 py-2.5 text-right">{{ $fmt($k->kg_desa) }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-500">{{ $k->kg_unit ? $fmt($k->kg_unit) : '-' }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-800">{{ $fmt($k->kg_total) }}</td>
                    </tr>

                    @foreach($k->desa as $d)
                        <tr class="hidden bg-slate-50/60" data-parent="{{ $k->kode }}">
                            <td class="py-2 pl-10 pr-4 text-slate-700">{{ $d->nama }}</td>
                            <td class="px-4 py-2"></td>
                            <td class="px-4 py-2 text-right text-slate-500">{{ $d->jumlah_bsu ?: '-' }}</td>
                            <td class="px-4 py-2 text-xs {{ $d->aktif ? 'text-green-700' : 'text-red-600' }}" colspan="3">
                                {{ $d->aktif ? 'Sudah menimbang' : 'Belum menimbang' }}
                            </td>
                            <td class="px-4 py-2 text-right">{{ $d->aktif ? $fmt($d->kg) : '-' }}</td>
                            <td class="px-4 py-2"></td>
                            <td class="px-4 py-2 text-right text-slate-600">{{ $d->aktif ? $fmt($d->kg) : '-' }}</td>
                        </tr>
                        @foreach($d->bsu as $b)
                            <tr class="hidden bg-slate-50/60 text-xs text-slate-500" data-parent="{{ $k->kode }}">
                                <td class="py-1.5 pl-16 pr-4" colspan="3">
                                    {{ $b->nama }}
                                    @unless($b->aktif)<span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">nonaktif</span>@endunless
                                </td>
                                <td class="px-4 py-1.5" colspan="3"></td>
                                <td class="px-4 py-1.5 text-right">{{ $b->kg ? $fmt($b->kg) : '-' }}</td>
                                <td class="px-4 py-1.5" colspan="2"></td>
                            </tr>
                        @endforeach
                    @endforeach

                    @foreach($k->unit as $u)
                        <tr class="hidden bg-slate-50/60 text-xs text-slate-500" data-parent="{{ $k->kode }}">
                            <td class="py-1.5 pl-10 pr-4" colspan="3">{{ $u->nama }} <span class="text-slate-400">(unit non-desa)</span></td>
                            <td class="px-4 py-1.5" colspan="3"></td>
                            <td class="px-4 py-1.5"></td>
                            <td class="px-4 py-1.5 text-right">{{ $u->kg ? $fmt($u->kg) : '-' }}</td>
                            <td class="px-4 py-1.5"></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 font-semibold text-slate-800">
                <tr>
                    <td class="px-4 py-3">Jumlah Kecamatan</td>
                    <td class="px-4 py-3 text-right">{{ $jml['desa'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $jml['bsu'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $jml['aktif'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $jml['belum'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $persen }}%</td>
                    <td class="px-4 py-3 text-right">{{ $fmt($total->desa) }}</td>
                    <td class="px-4 py-3 text-right">{{ $fmt($total->unit_kec) }}</td>
                    <td class="px-4 py-3 text-right">{{ $fmt($total->desa + $total->unit_kec) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('[data-toggle]').forEach(function (tr) {
    tr.addEventListener('click', function () {
        document.querySelectorAll('[data-parent="' + tr.dataset.toggle + '"]').forEach(function (r) {
            r.classList.toggle('hidden');
        });
        tr.querySelector('.panah').classList.toggle('rotate-90');
    });
});
</script>

    {{-- Unit tingkat kabupaten (PKK Kabupaten, dll.) --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Unit Tingkat Kabupaten</h2>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3 text-right">Total (kg)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($kabupaten as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $u->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $u->jenis }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $fmt($u->kg) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-4 text-center text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
            @if($kabupaten->isNotEmpty())
                <tfoot class="bg-slate-50 font-semibold text-slate-800">
                    <tr>
                        <td class="px-4 py-3" colspan="2">Jumlah Tingkat Kabupaten</td>
                        <td class="px-4 py-3 text-right">{{ $fmt($total->kabupaten) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection