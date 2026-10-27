<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGatepassAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessMenu('gatepass')) {
            return response()->json(['message' => 'Akun ini tidak punya akses Gatepass.'], 403);
        }

        return $next($request);
    }
}