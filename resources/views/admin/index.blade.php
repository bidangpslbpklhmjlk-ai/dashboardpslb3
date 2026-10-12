{{-- resources/views/admin/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
{{-- Panel Utama (Hanya isinya saja, struktur luar & sidebar sudah ditangani oleh layouts.admin) --}}
<div class="flex flex-col min-w-0 h-screen bg-slate-50/60 text-slate-800" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    
    {{-- Header --}}
    <header class="h-20 bg-white border-b border-slate-200/80 flex items-center px-6 lg:px-8 shadow-sm shrink-0">
        <h2 class="text-lg font-bold text-slate-700 capitalize">
            Dashboard: {{ ($mode ?? '') == 'berita' ? 'Manajemen Berita' : 'Manajemen Halaman' }}
        </h2>
    </header>

    {{-- Bagian Konten --}}
    <main class="p-4 sm:p-6 lg:p-8 max-w-4xl w-full mx-auto flex-1 space-y-6 overflow-y-auto">
        
        @if(($mode ?? '') == 'berita')
            @include('admin.partials.form-berita')
        @elseif(($mode ?? '') == 'pages')
            @include('admin.partials.form-pages')
        @else
            <div class="p-8 text-center text-slate-400">
                Pilih menu di sidebar untuk mulai mengelola konten.
            </div>
        @endif

    </main>
</div>
@endsection