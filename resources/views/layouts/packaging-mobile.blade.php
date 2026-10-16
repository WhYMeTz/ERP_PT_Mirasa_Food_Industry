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
                @if(Auth::check() && (Auth::user()->isSuperAdmin() || Auth::user()->isProduksi()))
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

    @stack('scripts')
</body>
</html>
