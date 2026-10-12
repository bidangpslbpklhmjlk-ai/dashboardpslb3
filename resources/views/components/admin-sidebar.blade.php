{{-- resources/views/components/admin-sidebar.blade.php --}}
@props(['pages' => collect(), 'activePage' => null])

@php
    $user = auth()->user();

    // Ikon per menu (isi atribut d dari <path>)
    $icons = [
        'berita' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
        'akuntabilitas' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'armada' => ['M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0z'],
        'rute-angkut' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
        'gatepass' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
        'tarif' => 'M9 7h6m0 10v-3m-3 3v-6m-3 6v-1m9-9V4a1 1 0 00-1-1H6a1 1 0 00-1 1v16a1 1 0 001 1h12a1 1 0 001-1v-3',
        'lokasi' => ['M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
        'invoice' => 'M9 14l2 2 4-4m-5-9H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0014.586 3H10z',
        'penugasan' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'kerusakan' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'checkin' => ['M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z', 'M15 13a3 3 0 11-6 0 3 3 0 016 0z'],
        'object-retribusi' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-4 4 4 4 0 014-4zm6 6a4 4 0 10-3.5-2',
        'kategori-object-retribusi' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        'pungutan' => ['M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
        'pegawai' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'jabatan' => 'M20 13V7a2 2 0 00-2-2h-3V4a2 2 0 00-2-2h-2a2 2 0 00-2 2v1H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4m8-3h.01',
        'pengguna' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'neraca-sampah' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
        'industri' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'peran' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
        ];

    // Hanya menu yang boleh diakses pengguna ini, dikelompokkan sesuai config/menus.php
    $menuGroups = collect(config('menus'))
        ->filter(fn ($menu, $key) => $user->canAccessMenu($key))
        ->map(fn ($menu, $key) => $menu + ['key' => $key])
        ->groupBy('group');

    $initials = mb_strtoupper(
        collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('')
    );
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:h-screen shrink-0">

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Header Sidebar --}}
        <div class="h-20 flex items-center px-6 border-b border-slate-100 gap-3 shrink-0">
            <div class="w-9 h-9 bg-green-700 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-md shadow-green-700/20">M</div>
            <div>
                <h2 class="font-bold text-sm text-slate-900 tracking-tight leading-none">DLH Majalengka</h2>
                <span class="text-[10px] text-gray-400 font-medium tracking-wider uppercase">Admin Panel</span>
            </div>
        </div>

        <nav class="p-4 space-y-1.5">
            {{-- Dashboard (untuk semua pengguna) --}}
            <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Utama</span>

            <a href="#" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-700 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/></svg>
                <span>Dashboard</span>
            </a>

            @foreach($menuGroups as $grup => $items)
                {{-- Judul grup (grup "Utama" sudah punya judul di atas) --}}
                @if($grup !== 'Utama')
                    <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2 mt-4">{{ $grup }}</span>
                @endif

                @foreach($items as $item)
                    @php
                        $pola = str_replace('.index', '.*', $item['route']);
                        $aktif = request()->routeIs($pola);
                        $nama = $item['nav'] ?? $item['label'];
                    @endphp

                    @if($item['key'] === 'halaman')
                        {{-- Kelola Halaman (dengan submenu) --}}
                        <div x-data="{ openKelola: {{ request()->routeIs('admin.pages.*') ? 'true' : 'false' }} }" class="space-y-1">
                            <a href="{{ route('admin.pages.index') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all group {{ $aktif ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 {{ $aktif ? 'text-green-700' : 'text-gray-400 group-hover:text-green-700' }} transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                                    <span>{{ $nama }}</span>
                                </div>
                                @if($pages->isNotEmpty())
                                    <svg @click.prevent="openKelola = !openKelola" :class="openKelola ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 hover:text-slate-700 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                @endif
                            </a>

                            @if($pages->isNotEmpty())
                                <div x-show="openKelola" x-collapse class="pl-4 space-y-1 mt-1">
                                    @foreach($pages->groupBy('category') as $category => $catPages)
                                        @php $isCategoryActive = $activePage && strtolower($activePage->category) === strtolower($category); @endphp

                                        <div x-data="{ openCat: {{ $isCategoryActive ? 'true' : 'false' }} }" class="space-y-0.5">
                                            <button @click="openCat = !openCat"
                                                    class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-[11px] font-bold tracking-wider uppercase transition-all {{ $isCategoryActive ? 'text-green-700' : 'text-slate-400 hover:text-slate-700' }}">
                                                <span>{{ $category ?? 'Tanpa Kategori' }}</span>
                                                <svg :class="openCat ? 'rotate-180' : ''" class="w-3 h-3 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </button>

                                            <div x-show="openCat" x-collapse class="pl-2 border-l border-slate-200 ml-2 space-y-0.5">
                                                @foreach($catPages as $p)
                                                    <a href="{{ route('admin.pages.edit', $p->id) }}"
                                                       class="block px-3 py-1.5 rounded-lg text-xs font-medium transition-all truncate {{ $activePage && $activePage->id === $p->id ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                                        {{ $p->title }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all group {{ $aktif ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-5 h-5 {{ $aktif ? 'text-green-700' : 'text-gray-400 group-hover:text-green-700' }} transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                @foreach((array) ($icons[$item['key']] ?? []) as $d)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/>
                                @endforeach
                            </svg>
                            <span>{{ $nama }}</span>
                        </a>
                    @endif
                @endforeach
            @endforeach

            {{-- Pengaturan: hanya superadmin --}}
{{-- Pengaturan: hanya superadmin --}}
@if($user->isSuperadmin())
    @php
        $aktifPengguna = request()->routeIs('admin.pengguna.*');
        $aktifPeran = request()->routeIs('admin.peran.*');
    @endphp
    <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2 mt-4">Pengaturan</span>

    <a href="{{ route('admin.pengguna.index') }}"
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all group {{ $aktifPengguna ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 {{ $aktifPengguna ? 'text-green-700' : 'text-gray-400 group-hover:text-green-700' }} transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['pengguna'] }}"/>
        </svg>
        <span>Manajemen Pengguna</span>
    </a>

    <a href="{{ route('admin.peran.index') }}"
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all group {{ $aktifPeran ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 {{ $aktifPeran ? 'text-green-700' : 'text-gray-400 group-hover:text-green-700' }} transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['peran'] }}"/>
        </svg>
        <span>Peran</span>
    </a>
@endif
        </nav>
    </div>

    {{-- Footer Sidebar: data pengguna yang sedang login --}}
    <div class="p-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center font-semibold text-sm text-slate-700 shrink-0">{{ $initials }}</div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-800 truncate">{{ $user->name }}</p>
                <p class="text-[10px] text-gray-400 truncate">{{ $user->email }} · {{ $user->role === 'superadmin' ? 'Superadmin' : 'Admin' }}</p>
            </div>
        </div>
    </div>
</aside>