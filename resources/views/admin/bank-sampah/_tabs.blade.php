@php
    $tabRekap = request()->routeIs('admin.bank-sampah.index');
    $tabBsu   = request()->routeIs('admin.bank-sampah.bsu.*');
    $kelas    = fn ($aktif) => $aktif
        ? 'border-green-700 text-green-700'
        : 'border-transparent text-slate-500 hover:text-slate-800';
@endphp
<div class="flex gap-6 border-b border-slate-200">
    <a href="{{ route('admin.bank-sampah.index') }}" class="-mb-px border-b-2 px-1 pb-2 text-sm font-semibold {{ $kelas($tabRekap) }}">Rekap Timbangan</a>
    <a href="{{ route('admin.bank-sampah.bsu.index') }}" class="-mb-px border-b-2 px-1 pb-2 text-sm font-semibold {{ $kelas($tabBsu) }}">Daftar BSU</a>
</div>