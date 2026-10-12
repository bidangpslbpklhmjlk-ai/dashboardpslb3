@extends('layouts.app')

@section('title', 'Dinas Lingkungan Hidup – Kabupaten Majalengka')

@section('content')
    {{-- Section 1: Hero (Ditampilkan hanya jika ada berita) --}}
    @if($berita && $berita->count() > 0)
        <x-hero :berita="$berita" />
    @else
        <div class="pt-32 pb-10 text-center">
            <h2 class="text-xl text-gray-400">Selamat Datang di Portal DLH Majalengka</h2>
        </div>
    @endif

    {{-- Section 2: Live Environment Data Widget --}}
    <x-env-data-widget />

    {{-- Section 3: Layanan Publik Grid --}}
{{-- Section 3: Layanan Publik Grid --}}
<section id="layanan" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <p class="text-xs font-semibold text-forest-600 uppercase tracking-widest mb-2">Apa yang Kami Layani</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 font-display mb-4">Layanan Publik</h2>
            <p class="text-gray-500 max-w-xl mx-auto text-sm">Berbagai layanan lingkungan hidup tersedia untuk warga Kabupaten Majalengka. Klik untuk informasi lebih lanjut.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Layanan 1: Perizinan --}}
            <a href="#" class="group p-6 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-lg transition duration-300">
                <div class="w-10 h-10 bg-forest-50 text-forest-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-forest-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Perizinan</h3>
                <p class="text-sm text-gray-500">Pengurusan dokumen AMDAL, UKL-UPL dan lainnya.</p>
            </a>

            {{-- Layanan 2: Persampahan --}}
            <a href="#" class="group p-6 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-lg transition duration-300">
                <div class="w-10 h-10 bg-sky-50 text-sky-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-sky-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Persampahan</h3>
                <p class="text-sm text-gray-500">Layanan pengangkutan dan pengelolaan sampah warga.</p>
            </a>

            {{-- Layanan 3: Uji Lab --}}
            <a href="#" class="group p-6 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-lg transition duration-300">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.883M8 12a4 4 0 118 0 4 4 0 01-8 0z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Laboratorium</h3>
                <p class="text-sm text-gray-500">Pengujian kualitas air, udara, dan tanah.</p>
            </a>

            {{-- Layanan 4: Pengaduan --}}
            <a href="#" class="group p-6 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-lg transition duration-300">
                <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-rose-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">Pengaduan</h3>
                <p class="text-sm text-gray-500">Laporkan masalah lingkungan di sekitar Anda.</p>
            </a>
        </div>
    </div>
</section>
@endsection