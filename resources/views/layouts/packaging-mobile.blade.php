<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Laporan Pengemasan Lapangan - PT Mirasa')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging_operator/packaging-operator.css') }}">
    @stack('styles')
</head>
<body class="packaging-mobile-body">
    {{-- TOP APP BAR KHUSUS OPERATOR PACKAGING HP --}}
    <header class="pkg-top-appbar">
        <div class="pkg-appbar-inner">
            <a href="{{ route('produksi.packaging.mobile') }}" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT Mirasa" style="height: 32px; width: auto;" onerror="this.style.display='none'">
                <div>
                    <div class="pkg-brand-title">
                        <span>PT Mirasa</span>
                        <span class="pkg-brand-badge">Packaging Lapangan</span>
                    </div>
                    <span style="font-size: 0.725rem; color: #64748b;">
                        {{ Auth::user()->name ?? 'Operator Kemas' }} &bull; Shift Kerja
                    </span>
                </div>
            </a>

            <div style="display: flex; gap: 6px; align-items: center;">
                {{-- TOMBOL KE OFFICE HANYA MUNCUL UNTUK SUPERADMIN --}}
                @if(Auth::check() && Auth::user()->isSuperAdmin())
                    <a href="{{ route('produksi.packaging.index') }}" title="Masuk ke Web Office ERP" style="padding: 6px 9px; background: #f1f5f9; border-radius: 6px; font-size: 0.75rem; color: #475569; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Office</span>
                    </a>
                @endif

                <form action="{{ route('mobile.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" title="Keluar / Ganti Shift" style="padding: 6px 9px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 6px; font-size: 0.75rem; color: #dc2626; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- KONTEN UTAMA --}}
    <main class="pkg-mobile-container">
        @if (session('success'))
            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #166534; padding: 0.85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <span>✅</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 0.85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- BOTTOM NAVBAR KHUSUS HP (TOUCH-FRIENDLY & RAMAH JEMPOL) --}}
    <nav class="pkg-bottom-nav">
        <div class="pkg-bottom-nav-inner">
            <a href="{{ route('produksi.packaging.mobile') }}" class="pkg-bottom-item {{ request()->routeIs('produksi.packaging.mobile') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Form Input</span>
            </a>
            <a href="{{ route('produksi.packaging.mobile.index') }}" class="pkg-bottom-item {{ (request()->routeIs('produksi.packaging.mobile.index') || request()->routeIs('produksi.packaging.mobile.show')) ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Riwayat</span>
            </a>
            <form action="{{ route('mobile.logout') }}" method="POST" style="display: contents;">
                @csrf
                <button type="submit" class="pkg-bottom-item" title="Keluar / Ganti Shift" onclick="return confirm('Keluar dari sesi operator HP?');">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
