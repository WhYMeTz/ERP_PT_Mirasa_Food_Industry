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
            return redirect()->route(Auth::user()->getDashboardRoute());
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

        return redirect()->route($user->getDashboardRoute())
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
}
