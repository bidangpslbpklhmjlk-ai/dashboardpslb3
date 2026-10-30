{{-- resources/views/admin/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - DLH Majalengka</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased flex min-h-screen items-center justify-center p-4">

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
                <img src="{{ asset('images/logo.png') }}" alt="Logo DLH Majalengka"
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

                {{-- Input Password --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-700/5 rounded-xl text-sm transition-all text-slate-800 placeholder-slate-400 font-medium"
                           placeholder="••••••••">
                </div>

                {{-- Opsi Ingat Saya --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-green-700 border-slate-300 rounded focus:ring-green-600 focus:ring-offset-0 bg-slate-50">
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

</body>
</html>