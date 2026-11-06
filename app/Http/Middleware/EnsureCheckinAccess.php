<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCheckinAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessMenu('checkin')) {
            return response()->json(['message' => 'Akun ini tidak punya akses Check-in.'], 403);
        }

        return $next($request);
    }
}