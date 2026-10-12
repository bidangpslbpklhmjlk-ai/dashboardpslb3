<header
  x-data="{ 
    scrolled: false, 
    mobileOpen: false,
    dropdown: { profil: false, pelayanan: false, akuntabilitas: false },
    closeAll() { this.dropdown.profil = false; this.dropdown.pelayanan = false; this.dropdown.akuntabilitas = false; }
  }"
  @scroll.window="scrolled = (window.pageYOffset > 20) ? true : false"
  @click.away="closeAll()"
  class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 backdrop-blur-md"
  :class="scrolled ? 'bg-white/95 shadow-md border-b border-gray-100' : 'bg-white/85 border-b border-gray-200/50'"
>
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 lg:h-20">

      <a href="/" class="flex items-center gap-3 flex-shrink-0">
        <div class="w-10 h-10 rounded-full bg-forest-600 flex items-center justify-center shadow-md">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
          </svg>
        </div>
        <div class="leading-tight">
          <p class="text-xs font-semibold text-forest-600 uppercase tracking-widest leading-none">Kab. Majalengka</p>
          <p class="text-sm font-bold text-slate-800 leading-tight">Dinas Lingkungan Hidup</p>
        </div>
      </a>

      <div class="hidden lg:flex items-center gap-2">
        <a href="/" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-forest-600 transition-colors">Beranda</a>

        <div class="relative">
          <button @click="closeAll(); dropdown.profil = !dropdown.profil" class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-forest-600 transition-colors focus:outline-none">
            Profil
            <svg class="w-4 h-4 transition-transform duration-200" :class="dropdown.profil ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="dropdown.profil" x-transition class="absolute left-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
            <a href="/page/profil/tugas-pokok-dan-fungsi" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">Tugas Pokok dan Fungsi</a>
            <a href="/page/profil/struktur-organisasi" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">Struktur Organisasi</a>
            <a href="/page/profil/maklumat-pelayanan" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">Maklumat Pelayanan</a>
            <a href="/page/profil/sejarah-dinas" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">Sejarah Dinas</a>
            <a href="/page/profil/bidang" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">Bidang</a>
            <a href="/page/profil/survey-kepuasan-masyarakat" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">Survey Kepuasan Masyarakat</a>
            <a href="/page/profil/ppid" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">PPID</a>
            <a href="/page/profil/hubungi-kami" class="block px-4 py-2 text-sm text-rose-600 font-medium hover:bg-rose-50">Hubungi Kami</a>
          </div>
        </div>

        <div class="relative">
          <button @click="closeAll(); dropdown.pelayanan = !dropdown.pelayanan" class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-forest-600 transition-colors focus:outline-none">
            Pelayanan
            <svg class="w-4 h-4 transition-transform duration-200" :class="dropdown.pelayanan ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="dropdown.pelayanan" x-transition class="absolute left-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 max-h-[400px] overflow-y-auto">
            <a href="/page/pelayanan/persetujuan-lingkungan" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600 whitespace-normal">Persetujuan Lingkungan</a>
            <a href="/page/pelayanan/penerbitan-surat-rekomendasi-izin-penebangan" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600 whitespace-normal">Penerbitan Surat Rekomendasi Izin Penebangan</a>
            <a href="/page/pelayanan/pelayanan-jasa-penarikan-atau-pengangkutan-sampah-ke-tpa" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600 whitespace-normal">Pelayanan Jasa Penarikan/Pengangkutan Sampah ke TPA</a>
            <a href="/page/pelayanan/rekomendasi-arahan-penyimpanan-sementara-tps-limbah-b3-rintek" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600 whitespace-normal">Rekomendasi Arahan Penyimpanan Sementara TPS Limbah B3</a>
            <a href="/page/pelayanan/persetujuan-teknis-pemenuhan-baku-mutu-air-limbah-dan-pembuangan-emisi" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600 whitespace-normal">Persetujuan Teknis Pemenuhan Baku Mutu Air Limbah</a>
            <a href="/page/pelayanan/penerbitan-surat-kelayakan-operasional-slo" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600 whitespace-normal">Penerbitan Surat Kelayakan Operasional (SLO)</a>
            <a href="/page/pelayanan/pengaduan-masyarakat-di-bidang-lingkungan-hidup" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600 whitespace-normal">Pengaduan Masyarakat di Bidang Lingkungan Hidup</a>
          </div>
        </div>

        <div class="relative">
          <button @click="closeAll(); dropdown.akuntabilitas = !dropdown.akuntabilitas" class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-forest-600 transition-colors focus:outline-none">
            Akuntabilitas
            <svg class="w-4 h-4 transition-transform duration-200" :class="dropdown.akuntabilitas ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="dropdown.akuntabilitas" x-transition class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
            <a href="/page/akuntabilitas/sakip" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">SAKIP</a>
            <a href="/page/akuntabilitas/publikasi" class="block px-4 py-2 text-sm text-slate-700 hover:bg-forest-50 hover:text-forest-600">Publikasi</a>
          </div>
        </div>

        <a href="/page/pelayanan/pengaduan-masyarakat-di-bidang-lingkungan-hidup" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-forest-600 transition-colors">Pengaduan</a>
      </div>

      <div class="hidden lg:flex items-center gap-3">
        <a href="#portal" class="px-4 py-2 rounded-full border border-rose-500 text-rose-500 font-medium text-sm hover:bg-rose-50 transition-all duration-200">
          Portal DLH
        </a>
      </div>

      <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg text-slate-700 hover:bg-gray-100">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div x-show="mobileOpen" x-transition class="lg:hidden bg-white border border-gray-100 rounded-2xl p-4 mb-4 space-y-2 shadow-lg">
      <p class="text-xs font-bold text-gray-400 uppercase px-3">Menu Utama</p>
      <a href="/" class="block px-3 py-2 rounded-lg text-slate-700 font-medium text-sm hover:bg-gray-50">Beranda</a>
      
      <div x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex justify-between px-3 py-2 text-slate-700 font-medium text-sm hover:bg-gray-50 rounded-lg">
          Profil <span>+</span>
        </button>
        <div x-show="open" class="pl-4 bg-gray-50 rounded-lg py-1 mt-1 text-xs space-y-1">
          <a href="/page/profil/tugas-pokok-dan-fungsi" class="block p-2 text-slate-600">Tugas Pokok & Fungsi</a>
          <a href="/page/profil/struktur-organisasi" class="block p-2 text-slate-600">Struktur Organisasi</a>
          <a href="/page/profil/sejarah-dinas" class="block p-2 text-slate-600">Sejarah Dinas</a>
        </div>
      </div>

      <div x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex justify-between px-3 py-2 text-slate-700 font-medium text-sm hover:bg-gray-50 rounded-lg">
          Pelayanan <span>+</span>
        </button>
        <div x-show="open" class="pl-4 bg-gray-50 rounded-lg py-1 mt-1 text-xs space-y-1">
          <a href="/page/pelayanan/persetujuan-lingkungan" class="block p-2 text-slate-600">Persetujuan Lingkungan</a>
          <a href="/page/pelayanan/penerbitan-surat-rekomendasi-izin-penebangan" class="block p-2 text-slate-600">Izin Penebangan</a>
        </div>
      </div>

      <a href="#portal" class="block text-center mt-4 py-2 rounded-xl bg-rose-500 text-white font-semibold text-sm">Portal DLH</a>
    </div>
  </nav>
</header>