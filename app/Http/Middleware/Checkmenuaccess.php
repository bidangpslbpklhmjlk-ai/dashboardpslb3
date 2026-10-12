<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian di route:
 *   ->middleware('menu:berita')            -> butuh menu "berita"
 *   ->middleware('menu:invoice,pungutan')  -> butuh salah satu dari dua menu
 *   ->middleware('menu:pengguna')          -> hanya superadmin (key ini tidak bisa di-assign)
 */
class CheckMenuAccess
{
    public function handle(Request $request, Closure $next, string ...$menus): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login', ['kunci' => 'majalengka2026'])
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi superadmin.']);
        }

        abort_unless($user->canAccessMenu(...$menus), 403, 'Anda tidak memiliki akses ke menu ini.');

        return $next($request);
    }
}