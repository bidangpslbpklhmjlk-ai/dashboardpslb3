<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Email atau kata sandi salah.'], 422);
        }
        if (! $user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }
        if (empty($user->menuApp())) {
            return response()->json(['message' => 'Akun ini tidak punya akses aplikasi.'], 403);
        }

        return response()->json([
            'token' => $user->createToken($data['device_name'] ?? 'android')->plainTextToken,
            'user'  => $this->profil($user),
        ]);
    }

    /** Profil dan menu terbaru, dipanggil app saat dibuka. */
    public function me(Request $request)
    {
        return response()->json($this->profil($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'OK']);
    }

    private function profil(User $u): array
    {
        return [
            'id'      => $u->id,
            'name'    => $u->name,
            'email'   => $u->email,
            'jabatan' => $u->peran->nama ?? $u->role,   // sesuaikan nama kolom di tabel peran
            'menu'    => $u->menuApp(),
        ];
    }
}