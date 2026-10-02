<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'QC Inbound Lapangan - PT Mirasa Food Industry')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-layout.css') }}">
    @stack('styles')
</head>
<body class="qc-mobile-body">
    {{-- TOP APP BAR KHUSUS OPERASIONAL QC HP --}}
    <header class="qc-top-appbar">
        <div class="qc-appbar-inner">
            <a href="{{ route('qc.inbound.create') }}" class="qc-brand-section">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT Mirasa" class="qc-brand-logo" onerror="this.style.display='none'">
                <div class="qc-brand-text">
                    <div class="qc-brand-title">
                        <span>PT Mirasa</span>
                        <span class="qc-brand-badge">QC Lapangan</span>
                    </div>
                    <span class="qc-brand-sub">{{ Auth::user()->name ?? 'Petugas QC' }} &bull; Inbound</span>
                </div>
            </a>

            <div class="qc-top-actions">
                {{-- JIKA USER BUKAN MURNI QC (MISAL SUPERADMIN ATAU ADMIN GUDANG), SEDIAKAN SHORTCUT KE ERP DESKTOP --}}
                @if (Auth::user()?->isSuperAdmin() || Auth::user()?->isGudang())
                    <a href="{{ route('gudang.stok.index') }}" class="qc-btn-desktop-switch" title="Kembali ke Tampilan Penuh ERP Desktop">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>ERP Desktop</span>
                    </a>
                @endif

                {{-- TOMBOL LOGOUT MINI --}}
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Keluar dari sesi akun QC?');">
                    @csrf
                    <button type="submit" class="qc-btn-logout-mini" title="Logout dari Sistem">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- KONTEN UTAMA --}}
    <main class="qc-main-content">
        @if (session('success'))
            <div class="alert-qc alert-qc-success">
                <span>✅</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-qc alert-qc-error">
                <span>⚠️</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- BOTTOM NAVIGATION BAR KHUSUS HP (RAMAH JEMPOL & TOUCH-FRIENDLY) --}}
    <nav class="qc-bottom-nav">
        <div class="qc-bottom-nav-inner">
            {{-- MENU 1: INPUT UJI (SAMPLING LAPANGAN) --}}
            <a href="{{ route('qc.inbound.create') }}" class="qc-nav-item {{ request()->routeIs('qc.inbound.create') ? 'active' : '' }}">
                <div class="qc-nav-highlight-bubble">
                    <svg class="qc-nav-icon" style="color: #ffffff; width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span style="font-weight: 800; margin-top: 2px;">Input QC</span>
            </a>

            {{-- MENU 2: RIWAYAT TIKET QC --}}
            <a href="{{ route('qc.inbound.index') }}" class="qc-nav-item {{ (request()->routeIs('qc.inbound.index') || request()->routeIs('qc.inbound.show') || request()->routeIs('qc.inbound.edit')) ? 'active' : '' }}">
                <svg class="qc-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Riwayat Tiket</span>
            </a>

            {{-- MENU 3: KELUAR / LOGOUT --}}
            <form action="{{ route('logout') }}" method="POST" id="formLogoutBottomNav" style="display: contents;">
                @csrf
                <button type="submit" class="qc-nav-item" onclick="return confirm('Keluar dari sesi akun QC?');">
                    <svg class="qc-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
