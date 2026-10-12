@extends('layouts.admin')

@section('title', 'Neraca Sampah - ' . $industri->nama)

@section('content')
@php
    $tahunLabel = fn ($l) => $l->periode_mulai->year === $l->periode_selesai->year
        ? (string) $l->periode_mulai->year
        : $l->periode_mulai->year . '/' . $l->periode_selesai->year;
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex justify-between items-start">
        <div>
            <a href="{{ route('admin.neraca-sampah.index') }}" class="text-xs text-slate-500 hover:text-slate-700">&larr; Daftar industri</a>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $industri->nama }}</h1>
            <p class="text-slate-500 mt-1">
                {{ collect([$industri->alamat, $industri->desa, $industri->kecamatan])->filter()->implode(', ') ?: 'Neraca sampah per tahun' }}
            </p>
        </div>
        <a href="{{ route('admin.neraca-sampah.index', ['industri' => $industri->id]) }}"
           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Tahun
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif
    @error('excel_file')
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ $message }}</div>
    @enderror
           @if($laporans->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Tahun</span>
            @foreach($laporans->sortBy('periode_mulai') as $opsi)
                <a href="{{ route('admin.neraca-sampah.industri', [$industri, 'laporan' => $opsi->id]) }}"
                   class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $laporan && $opsi->id === $laporan->id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $tahunLabel($opsi) }}
                </a>
            @endforeach
        </div>
    @endif

    @if($laporan)
        @include('admin.neraca-sampah.partials.analisis-industri')
    @endif

        @forelse($tampil as $l)
    @php $bulanImport = collect($l->bulanList())->map(fn ($t, $ym) => ['ym' => $ym, 'label' => $t->translatedFormat('F Y')])->values(); @endphp
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap justify-between items-center gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-slate-900">Tahun {{ $tahunLabel($l) }}</h2>
                    @if($l->status === 'final')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Final</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Draft</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $l->periode_mulai->translatedFormat('F Y') }} – {{ $l->periode_selesai->translatedFormat('F Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.neraca-sampah.show', $l) }}"
                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Buka</a>
                <button type="button"
                        onclick="openImport('{{ route('admin.neraca-sampah.import', $l) }}', @js($industri->nama . ' · Tahun ' . $tahunLabel($l)), @js(Route::has('admin.neraca-sampah.template') ? route('admin.neraca-sampah.template', $l) : null))"
                        class="px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">Import</button>
                <form action="{{ route('admin.neraca-sampah.destroy', $l) }}" method="POST"
                      onsubmit="return confirm('Hapus laporan tahun {{ $tahunLabel($l) }} beserta seluruh data hariannya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">Hapus</button>
                </form>
            </div>
        </div>

        {{-- Bulan di dalam tahun --}}
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($l->bulanList() as $ym => $tgl)
                @php
                    $hari = $hariTerisi[$l->id][$ym] ?? 0;
                    $penuh = $hari >= $tgl->daysInMonth;
                @endphp
                <a href="{{ route('admin.neraca-sampah.show', [$l, 'tab' => 'input']) }}"
                   class="block rounded-lg border px-3 py-2.5 transition-colors
                          {{ $hari === 0 ? 'border-slate-200 bg-slate-50 hover:bg-slate-100' : ($penuh ? 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100' : 'border-amber-200 bg-amber-50 hover:bg-amber-100') }}">
                    <p class="text-sm font-semibold text-slate-800">{{ $tgl->translatedFormat('F') }}</p>
                    <p class="text-xs mt-0.5 {{ $hari === 0 ? 'text-slate-400' : ($penuh ? 'text-emerald-700' : 'text-amber-700') }}">
                        {{ $hari === 0 ? 'Belum ada data' : $hari . ' dari ' . $tgl->daysInMonth . ' hari' }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 px-5 py-12 text-center text-slate-500">
        Industri ini belum punya laporan.
    </div>
    @endforelse
</div>

{{-- Modal: Import Excel --}}
<div id="modalImport" class="hidden fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-900">Import dari Excel</h2>
            <button type="button" onclick="closeImport()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>
        <form id="formImport" method="POST" enctype="multipart/form-data" class="p-6 space-y-4"
              onsubmit="return confirm('Data pada tanggal dan area yang sama akan ditimpa. Lanjutkan?')">
            @csrf
            <p class="text-sm text-slate-600">Laporan: <strong id="importNama"></strong></p>
            <p class="text-xs text-slate-500">
                Unggah file "Log Book dan Neraca Pengelolaan Sampah" apa adanya (.xlsx / .xlsm, maks 5 MB).
                Sheet bulan yang ada di file akan dibaca. Bulan lain tidak berubah.
                Belum punya file? <a id="importTemplate" href="#" class="text-emerald-700 underline hidden">Unduh template kosong</a>.
            </p>
                        <div>
                <p class="text-xs font-medium text-slate-600 mb-1.5 uppercase tracking-wide">Bulan yang diimpor</p>
                <div id="importBulan" class="flex flex-wrap gap-x-4 gap-y-1"></div>
                <p class="text-xs text-slate-400 mt-1">Hanya sheet bulan yang dicentang yang dibaca. Sheet bulan lain di file diabaikan.</p>
            </div>
            <input type="file" name="excel_file" accept=".xlsx,.xlsm" required
                   class="text-sm text-slate-600 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeImport()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Import</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openImport(url, nama, urlTemplate, bulan) {
        document.getElementById('formImport').action = url;
        document.getElementById('importNama').textContent = nama;

        const t = document.getElementById('importTemplate');
        if (urlTemplate) { t.href = urlTemplate; t.classList.remove('hidden'); }
        else { t.classList.add('hidden'); }

        const box = document.getElementById('importBulan');
        box.replaceChildren();
        (bulan || []).forEach(function (b) {
            const label = document.createElement('label');
            label.className = 'inline-flex items-center gap-1.5 text-sm text-slate-700';
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.name = 'bulan[]';
            cb.value = b.ym;
            label.append(cb, document.createTextNode(b.label));
            box.append(label);
        });

        document.getElementById('modalImport').classList.remove('hidden');
    }
    function closeImport() { document.getElementById('modalImport').classList.add('hidden'); }
</script>
@endsection