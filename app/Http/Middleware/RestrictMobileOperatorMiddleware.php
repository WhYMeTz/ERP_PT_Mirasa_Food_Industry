<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictMobileOperatorMiddleware
{
    /**
     * Pastikan akun ber-peran Operator Kemas Lapangan (OPERATOR_PACKAGING)
     * HANYA dapat mengakses modul Mobile Kemas.
     * Mencegah akses ke seluruh halaman Web Office Desktop (Master Data, Keuangan, HPP Office, Gudang, dll).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isOperatorPackaging()) {
                // Daftar whitelist pola URL yang boleh diakses operator lapangan
                $allowedPatterns = [
                    'produksi/packaging/mobile*',
                    'mobile/*',
                    'logout',
                    'mobile/logout',
                    'ajax/*',
                ];

                $isAllowed = false;
                foreach ($allowedPatterns as $pattern) {
                    if ($request->is($pattern)) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (!$isAllowed) {
                    return redirect()->route('produksi.packaging.mobile')
                        ->with('error', 'Akses Terbatas: Akun Operator Kemas Lapangan khusus untuk penginputan laporan melalui portal mobile.');
                }
            }
        }

        return $next($request);
    }
}
