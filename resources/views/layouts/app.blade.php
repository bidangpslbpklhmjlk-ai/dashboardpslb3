<!DOCTYPE html>
<html lang="id" x-data="{ mobileOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 40">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Dinas Lingkungan Hidup – Kabupaten Majalengka')</title>

  {{-- Google Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet" />

  {{-- Memanggil Tailwind & Alpine via Vite (Bawaan Laravel 12) --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  @stack('styles')
</head>

<body class="bg-gray-50 text-slate-800 antialiased overflow-x-hidden">

    {{-- Panggil Komponen Navbar --}}
    <x-navbar />

    {{-- Konten Dinamis Halaman --}}
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>