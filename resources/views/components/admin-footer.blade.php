@php
    $user = auth()->user();
@endphp

<footer class="mt-8 mb-12 border-t border-slate-200 pt-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-3">
        <p class="text-xs text-slate-400">
            &copy; {{ date('Y') }} Dinas Lingkungan Hidup Kab. Majalengka
            @if ($user)
                · {{ $user->email }} · {{ $user->role === 'superadmin' ? 'Superadmin' : ($user->peran->nama ?? 'Admin') }}
            @endif
        </p>
    </div>
</footer>