<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman form login
public function showLoginForm(Request $request)
{
    // Cek apakah ada query string ?key=rahasia-anda
    // Jika tidak ada, atau nilainya salah, tampilkan 404
    if ($request->query('kunci') !== 'majalengka2026') {
        abort(404);
    }

    return view('admin.auth.login');
}

    // Memproses data kiriman form login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Proses autentikasi (Remember me diset true agar login awet)
        $credentials['is_active'] = true;
        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();

        return redirect()->intended(route('admin.home'))
            ->with('success', 'Selamat datang kembali!');
        }

        // Jika gagal, kembalikan dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login', ['kunci' => 'majalengka2026'])
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}