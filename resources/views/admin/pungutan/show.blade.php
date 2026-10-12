@extends('layouts.admin')
@section('title', 'Detail Kunjungan')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.pungutan.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Kunjungan</h1>
            <p class="text-slate-500 mt-1">{{ $kunjungan->waktu_kunjungan->format('d M Y H:i') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Info Kunjungan --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="flex justify-between items-start">
            <h2 class="font-semibold text-slate-800">Informasi Kunjungan</h2>
            @if($kunjungan->status === 'selesai')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Selesai</span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Batal</span>
            @endif
        </div>

        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-slate-500">Object Retribusi</dt>
                <dd class="font-medium text-slate-800 mt-0.5">{{ $kunjungan->objectRetribusi->nama ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Kategori</dt>
                <dd class="font-medium text-slate-800 mt-0.5">{{ $kunjungan->objectRetribusi->kategori->nama ?? '-' }}</dd>
            </div>
            <div class="col-span-2">
                <dt class="text-slate-500">Alamat</dt>
                <dd class="font-medium text-slate-800 mt-0.5">{{ $kunjungan->objectRetribusi->alamat ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Petugas</dt>
                <dd class="font-medium text-slate-800 mt-0.5">{{ $kunjungan->petugas->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Waktu Kunjungan</dt>
                <dd class="font-medium text-slate-800 mt-0.5">{{ $kunjungan->waktu_kunjungan->format('d M Y H:i') }}</dd>
            </div>
            @if($kunjungan->objectRetribusi->latitude && $kunjungan->objectRetribusi->longitude)
            <div class="col-span-2">
                <dt class="text-slate-500">Titik Koordinat Rumah</dt>
                <dd class="font-medium text-slate-800 mt-0.5">
                    {{ $kunjungan->objectRetribusi->latitude }}, {{ $kunjungan->objectRetribusi->longitude }}
                    <a href="https://www.google.com/maps?q={{ $kunjungan->objectRetribusi->latitude }},{{ $kunjungan->objectRetribusi->longitude }}" target="_blank" class="text-emerald-600 hover:underline text-xs ml-2">Lihat di Maps ↗</a>
                </dd>
            </div>
            @endif
            @if($kunjungan->catatan)
            <div class="col-span-2">
                <dt class="text-slate-500">Catatan</dt>
                <dd class="font-medium text-slate-800 mt-0.5">{{ $kunjungan->catatan }}</dd>
            </div>
            @endif
        </dl>

        @if($kunjungan->foto)
        <div>
            <dt class="text-slate-500 text-sm mb-1.5">Foto Bukti</dt>
            <img src="{{ Storage::url($kunjungan->foto) }}" alt="Foto kunjungan" class="rounded-lg border border-slate-200 max-h-80 object-cover">
        </div>
        @endif
    </div>

    {{-- Info Invoice --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
        <h2 class="font-semibold text-slate-800">Invoice</h2>

        @if($kunjungan->invoice)
            @php
                $statusStyles = [
                    'unpaid' => 'bg-amber-100 text-amber-800',
                    'paid' => 'bg-emerald-100 text-emerald-800',
                    'cancelled' => 'bg-red-100 text-red-800',
                ];
                $statusLabels = [
                    'unpaid' => 'Belum Bayar',
                    'paid' => 'Lunas',
                    'cancelled' => 'Dibatalkan',
                ];
            @endphp
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">Nomor Invoice</dt>
                    <dd class="font-mono font-medium text-slate-800 mt-0.5">{{ $kunjungan->invoice->nomor_invoice }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd class="mt-0.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusStyles[$kunjungan->invoice->status] }}">
                            {{ $statusLabels[$kunjungan->invoice->status] }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Total</dt>
                    <dd class="font-semibold text-slate-900 mt-0.5">Rp {{ number_format($kunjungan->invoice->total, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Dibuat</dt>
                    <dd class="font-medium text-slate-800 mt-0.5">{{ $kunjungan->invoice->created_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>
            <div class="pt-2">
                <a href="{{ route('admin.invoice.pungutan.print', $kunjungan->invoice) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                    🖨️ Cetak Invoice
                </a>
            </div>
        @else
            <p class="text-slate-500 text-sm">Tidak ada invoice untuk kunjungan ini (status kunjungan bukan "Selesai").</p>
        @endif
    </div>

    <div class="flex justify-between items-center">
        <form action="{{ route('admin.pungutan.destroy', $kunjungan) }}" method="POST" onsubmit="return confirm('Hapus data kunjungan ini beserta invoicenya?')">
            @csrf @method('DELETE')
            <button type="submit" class="px-5 py-2.5 border border-red-300 text-red-600 rounded-lg font-medium hover:bg-red-50 transition-colors text-sm">
                Hapus Kunjungan
            </button>
        </form>
        <a href="{{ route('admin.pungutan.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-medium hover:bg-slate-50 text-sm">
            Kembali
        </a>
    </div>
</div>
@endsection