@extends('layouts.app')

@section('title', 'Detail Invoice ' . $invoice->nomor_invoice)

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Status Badge --}}
        <div class="text-center mb-6">
            @if($invoice->status === 'paid')
                <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Invoice Terverifikasi & Lunas
                </div>
            @else
                <div class="inline-flex items-center gap-2 bg-amber-100 text-amber-700 px-4 py-2 rounded-full text-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Invoice Terverifikasi - Belum Dibayar
                </div>
            @endif
        </div>

        {{-- Card Detail --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Nomor Invoice</p>
                        <h1 class="text-xl font-bold text-slate-900 mt-1 font-mono">{{ $invoice->nomor_invoice }}</h1>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Tanggal</p>
                        <p class="text-sm text-slate-700 mt-1">{{ $invoice->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Perusahaan / Dinas</p>
                        <p class="text-sm font-medium text-slate-900 mt-1">{{ $invoice->perusahaan->nama }}</p>
                        <span class="inline-block mt-1 text-[10px] px-2 py-0.5 rounded-full {{ $invoice->perusahaan->tipe === 'dinas' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }} uppercase">
                            {{ $invoice->perusahaan->tipe }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Nomor Polisi</p>
                        <p class="text-sm font-medium text-slate-900 mt-1">{{ $invoice->gatepassLog->nomor_polisi }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Tipe Kendaraan</p>
                        <p class="text-sm text-slate-900 mt-1">{{ $invoice->gatepassLog->tipe_kendaraan }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Jenis Muatan</p>
                        <p class="text-sm text-slate-900 mt-1">{{ $invoice->gatepassLog->jenis_muatan }}</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Volume</p>
                            <p class="text-2xl font-bold text-slate-900">{{ number_format($invoice->volume_m3, 2) }} <span class="text-sm font-normal text-slate-500">m³</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Total</p>
                            <p class="text-3xl font-bold text-emerald-600">Rp {{ number_format($invoice->total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                @if($invoice->paid_at)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mt-4">
                        <p class="text-xs text-emerald-600 uppercase tracking-wider font-semibold">Dibayar Pada</p>
                        <p class="text-sm text-emerald-800 mt-1">{{ $invoice->paid_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
            </div>

            {{-- Barcode --}}
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500 mb-2">Kode Verifikasi</p>
                <p class="font-mono text-lg tracking-[0.3em] text-slate-700 bg-white px-4 py-2 rounded-lg inline-block border border-slate-200">
                    {{ $invoice->barcode }}
                </p>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="mt-6 text-center">
            <a href="{{ route('invoice.check') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Pencarian
            </a>
        </div>

    </div>
</div>
@endsection