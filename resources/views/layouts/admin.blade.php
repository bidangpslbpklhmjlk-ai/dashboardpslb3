<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - DLH Majalengka</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 antialiased font-sans">

    {{-- Container utama diikat Alpine.js --}}
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        
        {{-- SIDEBAR KIRI --}}
        <x-admin-sidebar />

        {{-- KONTEN KANAN --}}
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            
            {{-- SEKARANG HEADER ADA DI SINI: Menerima title dari yield halaman --}}
            <x-admin-header :title="View::yieldContent('title', 'Admin Dashboard')" />

            {{-- Area Konten Inti / Dinamis --}}
            <main class="flex-1 overflow-y-auto relative bg-slate-50/50">
                @yield('content')
            </main>
            
        </div>
        
    </div>

</body>
</html>