<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
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
            'device_id'   => 'required|string|max:64',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->catat($request, $data, $user, LoginLog::WRONG_PASSWORD);
            return response()->json(['message' => 'Email atau kata sandi salah.'], 422);
        }
        if (! $user->is_active) {
            $this->catat($request, $data, $user, LoginLog::INACTIVE);
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }
        if (empty($user->menuApp())) {
            $this->catat($request, $data, $user, LoginLog::NO_ACCESS);
            return response()->json(['message' => 'Akun ini tidak punya akses aplikasi.'], 403);
        }
        if (! $this->ikatPerangkat($user, $data)) {
            $this->catat($request, $data, $user, LoginLog::DEVICE_MISMATCH);
            return response()->json([
                'code'    => 'DEVICE_MISMATCH',
                'message' => 'Akun terkunci di perangkat lain. Hubungi admin untuk reset.',
            ], 403);
        }

        $user->last_login_at  = now();
        $user->last_active_at = now();
        $user->save();

        $user->tokens()->delete();
        $this->catat($request, $data, $user, LoginLog::SUCCESS);

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

    /** true kalau HP ini boleh dipakai: belum ada ikatan (lalu diikat) atau sama dengan yang terikat. */
    private function ikatPerangkat(User $user, array $data): bool
    {
        if (empty($user->device_id)) {
            $terikat = User::whereKey($user->id)->whereNull('device_id')->update([
                'device_id'       => $data['device_id'],
                'device_name'     => $data['device_name'] ?? null,
                'device_bound_at' => now(),
            ]);
            if ($terikat) {
                return true;
            }
            $user->refresh(); // keduluan HP lain, cek ulang
        }

        return $user->device_id === $data['device_id'];
    }

    private function catat(Request $request, array $data, ?User $user, string $status): void
    {
        LoginLog::create([
            'user_id'     => $user?->id,
            'email'       => $data['email'],
            'status'      => $status,
            'device_id'   => $data['device_id'],
            'device_name' => $data['device_name'] ?? null,
            'ip_address'  => $request->ip(),
        ]);
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