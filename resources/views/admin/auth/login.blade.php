{{-- resources/views/admin/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - DLH Majalengka</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}?v={{ filemtime(public_path('images/favicon.png')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased flex min-h-screen items-center justify-center p-4">

    @php
        // Tercentang saat pertama dibuka; setelah gagal login, ikuti pilihan terakhir pengguna
        $ingat = count(session()->getOldInput()) ? old('remember') !== null : true;
    @endphp

    <div class="w-full max-w-md">
        {{-- Flash Message Logout/Sukses --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-2xl text-xs font-medium text-center">
                {{ session('success') }}
            </div>
        @endif

        {{-- Kartu Form Login --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-100/50 p-8 space-y-6">

            {{-- Header Logo --}}
            <div class="text-center space-y-3">
                <img src="{{ asset('images/logo.png') }}?v={{ filemtime(public_path('images/logo.png')) }}" alt="Logo DLH Majalengka"
                     class="h-14 w-auto max-w-full object-contain mx-auto">
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">Selamat Datang</h2>
                    <p class="text-xs text-slate-400 font-medium">Masuk ke Ruang Kerja Admin DLH Majalengka</p>
                </div>
            </div>

            {{-- Menampilkan Error Validasi Global --}}
            @if($errors->any())
                <div class="p-3.5 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl text-xs font-semibold">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form Aksi --}}
            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Input Email --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-700/5 rounded-xl text-sm transition-all text-slate-800 placeholder-slate-400 font-medium"
                           placeholder="admin@dlh.com">
                </div>

                {{-- Input Password + tombol lihat --}}
                <div>
                    <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full pl-4 pr-12 py-3 bg-slate-50 border border-slate-200 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-700/5 rounded-xl text-sm transition-all text-slate-800 placeholder-slate-400 font-medium"
                               placeholder="••••••••">
                        <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-green-700 transition-colors"
                                aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi">
                            {{-- Ikon mata (sandi tersembunyi) --}}
                            <svg id="iconMata" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{-- Ikon mata dicoret (sandi terlihat) --}}
                            <svg id="iconMataCoret" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Opsi Ingat Saya (langsung tercentang) --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" @checked($ingat)
                               class="w-4 h-4 text-green-700 border-slate-300 rounded focus:ring-green-600 focus:ring-offset-0 bg-slate-50">
                        <span class="text-xs text-slate-500 font-medium">Ingat perangkat ini</span>
                    </label>
                </div>

                {{-- Tombol Submit --}}
                <button type="submit" class="w-full py-3 px-4 bg-green-700 text-white font-bold text-sm rounded-xl hover:bg-green-800 shadow-lg shadow-green-700/10 active:scale-[0.98] transition-all">
                    Masuk Sekarang
                </button>
            </form>
        </div>

        {{-- Footer teks kecil --}}
        <p class="text-center text-[11px] text-slate-400 mt-6 font-medium">
            &copy; 2026 Dinas Lingkungan Hidup Kabupaten Majalengka.
        </p>
    </div>

    <script>
        (function () {
            var input = document.getElementById('password');
            var tombol = document.getElementById('togglePassword');
            var mata = document.getElementById('iconMata');
            var coret = document.getElementById('iconMataCoret');

            tombol.addEventListener('click', function () {
                var tampil = input.type === 'password';
                input.type = tampil ? 'text' : 'password';
                mata.classList.toggle('hidden', tampil);
                coret.classList.toggle('hidden', !tampil);
                var label = tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
                tombol.setAttribute('aria-label', label);
                tombol.setAttribute('title', label);
                input.focus();
            });
        })();
    </script>

</body>
</html>