<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifikatorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Pastikan pengguna sudah login
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized - Please login first.'], 401);
        }

        $user = Auth::user();

        // Jika pengguna adalah SuperAdmin atau Verifikator, izinkan akses tanpa batasan
        if ($user->hasRole('Admin') || $user->hasRole('Verifikator')) {
            return $next($request);
        }

        // Periksa apakah pengguna memiliki salah satu peran yang diperlukan
        if (!$user->hasAnyRole($roles)) {
            return response()->json(['error' => 'Unauthorized - You do not have the necessary role.'], 403);
        }

        return $next($request);
    }
}
