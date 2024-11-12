<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Pastikan pengguna sudah login
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized - Please login first.'], 401);
        }

        $user = Auth::user();

        // Jika pengguna adalah SuperAdmin, izinkan akses tanpa batasan
        if ($user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        // Periksa apakah pengguna memiliki salah satu peran yang diperlukan
        if (!$user->hasAnyRole($roles)) {
            return response()->json(['error' => 'Unauthorized - You do not have the necessary role.'], 403);
        }
        return $next($request);
    }
}
