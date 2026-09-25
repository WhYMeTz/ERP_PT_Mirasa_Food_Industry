<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan login terlebih dahulu untuk mengakses sistem.']);
        }

        $user = Auth::user();

        // Superadmin selalu memiliki akses penuh ke seluruh modul
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Jika tidak ada batasan role khusus, izinkan
        if (empty($roles)) {
            return $next($request);
        }

        // Cek apakah user memiliki izin dinamis atau role yang diizinkan
        foreach ($roles as $item) {
            $item = trim($item);
            if ($user->canDo($item) || $user->hasRole($item)) {
                return $next($request);
            }
        }

        // Jika akses ditolak, kembalikan ke dashboard default dengan notifikasi ramah
        $defaultRoute = route($user->getDashboardRoute());
        
        return redirect($defaultRoute)->with('error', "Akses Ditolak: Peran akun Anda '{$user->role_cd}' saat ini belum memiliki izin untuk membuka fitur tersebut. Pengaturan hak akses dapat disesuaikan oleh Super Administrator.");
    }
}
