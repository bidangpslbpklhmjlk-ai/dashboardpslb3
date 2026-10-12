@extends('layouts.app')

@section('title', $page->title . ' – DLH Majalengka')

@section('content')
<section class="pt-32 pb-20 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-xs text-gray-400 uppercase tracking-wider mb-2">
            <a href="/" class="hover:text-forest-600">Beranda</a> / 
            <span class="text-gray-600">{{ $page->category }}</span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 font-display mb-8">
            {{ $page->title }}
        </h1>

        {{-- Gunakan {!! !!} langsung agar HTML (termasuk gambar) dirender oleh browser --}}
        {{-- Kita hapus nl2br dan e() agar tag <img> dan format lainnya terbaca --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 prose prose-slate max-w-none">
            {!! $page->content !!}
        </div>

    </div>
</section>
@endsection