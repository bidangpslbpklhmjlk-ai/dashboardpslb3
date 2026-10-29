<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceBound
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request); // auth:sanctum yang menangani
        }

        // Admin sudah mereset perangkat: token lama tidak berlaku lagi
        if (empty($user->device_id)) {
            $request->user()->currentAccessToken()?->delete();

            return response()->json([
                'code'    => 'DEVICE_RESET',
                'message' => 'Perangkat telah direset. Silakan login ulang.',
            ], 401);
        }

        $deviceId = (string) $request->header('X-Device-Id', '');

        if (! hash_equals($user->device_id, $deviceId)) {
            return response()->json([
                'code'    => 'DEVICE_MISMATCH',
                'message' => 'Akun terkunci di perangkat lain. Hubungi admin untuk reset.',
            ], 403);
        }

        // Perbarui "terakhir aktif" paling sering 1x per menit
        if (! $user->last_active_at || $user->last_active_at->lt(now()->subMinute())) {
            User::whereKey($user->id)->toBase()->update(['last_active_at' => now()]);
        }

        return $next($request);
    }
}