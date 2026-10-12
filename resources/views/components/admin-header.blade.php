{{-- resources/views/components/admin-header.blade.php --}}
@props(['title' => 'Admin Panel'])

<header class="h-20 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between shrink-0 sticky top-0 z-40 shadow-sm shadow-slate-100/40">
    {{-- Sisi Kiri: Tombol Mobile & Judul Halaman --}}
    <div class="flex items-center gap-4">
        {{-- Tombol Hamburger (Hanya Muncul di Layar Mobile/Tablet) --}}
        <button @click="sidebarOpen = !sidebarOpen" 
                class="p-2 -ml-2 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 lg:hidden transition-colors focus:outline-none focus:ring-2 focus:ring-green-700/10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        {{-- Judul Halaman Dinamis --}}
        <div>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-medium">
                <span>Dashboard</span>
                <svg class="w-2.5 h-2.5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                <span class="text-slate-500">{{ $title }}</span>
            </div>
        </div>
    </div>

    {{-- Sisi Kanan: Aksi & Informasi Akun Cepat --}}
    <div class="flex items-center gap-4">
        {{-- Tombol Lihat Website Utama (Opsional) --}}
        <a href="/" target="_blank" class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 hover:bg-slate-100 hover:text-slate-900 transition-all">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            <span>Lihat Situs</span>
        </a>

        <div class="w-px h-6 bg-slate-200 hidden sm:block"></div>

        {{-- Dropdown Profile Kecil --}}
        <div x-data="{ userMenuOpen: false }" class="relative">
            <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false" class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-50 transition-colors focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center font-bold text-xs text-green-700 border border-green-200/50">
                    AD
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-xs font-semibold text-slate-800 leading-tight">Admin DLH</p>
                    <p class="text-[10px] text-slate-400">Super Administrator</p>
                </div>
                <svg :class="userMenuOpen ? 'rotate-180' : ''" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {{-- Menu Dropdown Dropdown (Logout, dll) --}}
            <div x-show="userMenuOpen" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-2xl shadow-xl p-1.5 z-50 text-sm font-medium text-slate-600"
                 style="display: none;">
                
                <a href="#" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    <span>Pengaturan Akun</span>
                </a>
                
                <hr class="my-1.5 border-slate-100">
                
{{-- Tombol Logout yang benar --}}
<form action="{{ route('admin.logout') }}" method="POST" class="w-full">
    @csrf
    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50/50 transition-colors text-left font-semibold">
        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
        </svg>
        <span>Keluar Sistem</span>
    </button>
</form>
            </div>
        </div>
    </div>
</header>