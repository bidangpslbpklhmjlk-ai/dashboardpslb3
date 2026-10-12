@extends('layouts.app')

@section('title', 'Cek Keaslian Invoice')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4 shadow-lg shadow-emerald-600/20">M</div>
            <h1 class="text-2xl font-bold text-slate-900">Cek Keaslian Invoice</h1>
            <p class="text-slate-500 mt-1">DLH Majalengka</p>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form action="{{ route('invoice.verify') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor Invoice</label>
                    <input type="text" name="nomor_invoice" value="{{ old('nomor_invoice') }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm uppercase tracking-wider"
                        placeholder="INV-20260721-0001">
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-2 text-slate-400">atau</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Barcode / Kode Verifikasi</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-mono"
                        placeholder="Scan barcode atau ketik kode">
                </div>

                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition-colors shadow-sm">
                    🔍 Verifikasi Invoice
                </button>
            </form>

            @if(session('error'))
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm text-center">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; {{ date('Y') }} DLH Majalengka. Sistem Gatepass & Invoice.
        </p>

    </div>
</div>
@endsection