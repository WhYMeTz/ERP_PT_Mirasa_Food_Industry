<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route(Auth::user()->getDashboardRoute()));
        }

        // Simpan halaman terakhir ke intended session agar saat relog tetap berada di halaman tersebut
        $prev = url()->previous();
        if (!session()->has('url.intended') && $prev && !str_contains($prev, '/login')) {
            session(['url.intended' => $prev]);
        }

        $demoUsers = User::with(['karyawan', 'gudang'])
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->get();

        return view('auth.login', compact('demoUsers'));
    }

    /**
     * Proses autentikasi login pengguna
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            return redirect()->intended(route($user->getDashboardRoute()))
                ->with('success', "Selamat datang, {$user->name}! Login berhasil sebagai {$user->role_cd}.");
        }

        return back()->withInput($request->only('email', 'remember'))->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Fitur Login Cepat (1-Click Switcher) untuk memudahkan pengujian berbagai role
     */
    public function quickLogin(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->getDashboardRoute()))
            ->with('success', "Beralih akun berhasil! Anda sekarang masuk sebagai {$user->name} ({$user->role_cd}).");
    }

    /**
     * Keluar dari sistem (Logout)
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Tampilkan formulir login khusus Mobile (QC Lapangan & Operator Kemas)
     */
    public function showMobileLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            return redirect()->intended(route($user->getDashboardRoute()));
        }

        // Ambil akun untuk fast-switcher: QC Lapangan, Operator Kemas, Staff Produksi, Superadmin
        $operatorUsers = User::with(['karyawan', 'gudang'])
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->where(function ($q) {
                $q->where('role_cd', 'QC')
                  ->orWhere('role_cd', 'OPERATOR_PACKAGING')
                  ->orWhere('role_cd', 'STAFF_PRODUKSI')
                  ->orWhere('role_cd', 'SUPERADMIN');
            })
            ->orderByRaw("CASE 
                WHEN role_cd = 'QC' THEN 1 
                WHEN role_cd = 'OPERATOR_PACKAGING' THEN 2 
                WHEN role_cd = 'STAFF_PRODUKSI' THEN 3 
                ELSE 4 
            END")
            ->get();

        return view('auth.mobile-login', compact('operatorUsers'));
    }

    /**
     * Proses autentikasi login khusus Mobile (QC Lapangan & Operator Kemas)
     */
    public function mobileLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Akun / Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $input = $request->input('email');
        $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $credentials = [
            $field     => $input,
            'password' => $request->input('password'),
            'active_st' => true,
            'deleted_st' => false,
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            $targetRoute = $user->getDashboardRoute();
            $portalTitle = $user->isQc() ? 'Portal QC Lapangan' : ($user->isOperatorPackaging() ? 'Portal Kemas' : 'Portal Mobile');

            return redirect()->intended(route($targetRoute))
                ->with('success', "Selamat bertugas, {$user->name}! Anda berhasil masuk ke {$portalTitle}.");
        }

        return back()->withInput($request->only('email', 'remember'))->withErrors([
            'email' => 'Akun atau kata sandi yang Anda masukkan salah.',
        ]);
    }

    /**
     * Keluar dari sesi Mobile (QC Lapangan & Operator Kemas)
     */
    public function mobileLogout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.login')
            ->with('success', 'Sesi kerja lapangan Anda telah diakhiri. Silakan login kembali saat memulai tugas baru.');
    }
}

