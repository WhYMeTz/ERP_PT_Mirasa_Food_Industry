<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Masuk ke Sistem - ERP PT Mirasa Food Industry</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}?v={{ time() }}">
</head>

<body class="auth-body">

    {{-- Background dengan Gambar Pabrik --}}
    <div class="auth-page-bg">
        <img src="{{ asset('images/pabrik-mirasa-1.jpg') }}" alt="Pabrik PT Mirasa Food Industry" class="page-bg-img">
        <div class="page-bg-overlay"></div>
    </div>

    <div class="auth-wrapper">
        {{-- SISI KIRI: SHOWCASE FOTO PABRIK --}}
        <div class="auth-visual-pane">
            {{-- Slideshow Foto Pabrik --}}
            <div class="slideshow-track">
                <img src="{{ asset('images/pabrik-mirasa-1.jpg') }}" alt="Kompleks Pabrik PT Mirasa"
                    class="slide-img active">
                <img src="{{ asset('images/pabrik-mirasa-2.jpg') }}" alt="Gedung Utama PT Mirasa" class="slide-img">
            </div>

            <div class="visual-overlay"></div>

            {{-- Header Visual: Logo & Status --}}
            <div class="visual-header">
                <div class="glass-logo-badge">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo PT Mirasa" class="badge-logo-img">
                    <div class="badge-text-group">
                        <span class="badge-title">PT Mirasa Food Industry</span>
                        <span class="badge-sub">Enterprise System</span>
                    </div>
                </div>

                <div class="plant-pill">
                    <span class="pulsing-dot"></span>
                    <span>Pabrik Magelang &bull; Beroperasi</span>
                </div>
            </div>

            {{-- Footer Visual: Quote & Indikator --}}
            <div class="visual-footer">
                <div class="quote-box">
                    <span class="quote-icon">&ldquo;</span>
                    <h2 class="quote-text">Sistem terpadu untuk efisiensi rantai pasok dan mutu pangan berstandar
                        tinggi.</h2>
                    <p class="quote-author">
                        <span>PT Mirasa Food Industry</span>
                        <span class="quote-author-tag">&bull; Standar HACCP & BPOM</span>
                    </p>
                </div>

                <div class="slide-indicators">
                    <button type="button" class="slide-dot active" aria-label="Slide 1"></button>
                    <button type="button" class="slide-dot" aria-label="Slide 2"></button>
                </div>
            </div>
        </div>

        {{-- SISI KANAN: FORM LOGIN --}}
        <div class="auth-form-pane">
            <div class="form-content-inner">
                {{-- Header Form --}}
                <div class="form-header">
                    <div class="form-badge-pill">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Portal Manajemen ERP</span>
                    </div>
                    <h1 class="form-title">Selamat Datang</h1>
                    <p class="form-subtitle">Silakan masukkan akun Anda untuk mengakses sistem operasional pabrik.</p>
                </div>

                {{-- Alert Pesan --}}
                @if (isset($errors) && $errors->any())
                    <div class="auth-alert alert-danger">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="auth-alert alert-success">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                {{-- Form Login --}}
                <form action="{{ route('login.attempt') }}" method="POST" id="mainLoginForm">
                    @csrf

                    {{-- Input Email --}}
                    <div class="form-group">
                        <label for="emailInput" class="form-label">Email atau Nama Pengguna</label>
                        <div class="input-container">
                            <span class="input-icon-left">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input type="text" id="emailInput" name="email" class="form-input"
                                placeholder="contoh: admin@mirasa.co.id" value="{{ old('email') }}" required
                                autocomplete="username">
                        </div>
                    </div>

                    {{-- Input Password --}}
                    <div class="form-group">
                        <label for="passwordInput" class="form-label">Kata Sandi</label>
                        <div class="input-container">
                            <span class="input-icon-left">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="passwordInput" name="password" class="form-input"
                                placeholder="Masukkan kata sandi..." required autocomplete="current-password">
                            <button type="button" id="togglePasswordBtn" class="btn-toggle-pwd"
                                title="Lihat/Sembunyikan Sandi">
                                <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Opsi --}}
                    <div class="form-options-row">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" value="1" checked>
                            <span>Ingat sesi saya</span>
                        </label>
                        <a href="javascript:void(0)" class="link-help"
                            onclick="alert('Untuk reset kata sandi, silakan hubungi Administrator IT PT Mirasa Food Industry.');">Lupa
                            kata sandi?</a>
                    </div>

                    {{-- Tombol Submit --}}
                    <button type="submit" class="btn-auth-submit">
                        <span>Masuk ke Sistem</span>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                {{-- Quick Login Demo (Format Kartu Modern Seimbang & Anti-Lonjong) --}}
                @if (isset($demoUsers) && $demoUsers->count() > 0)
                    <div class="demo-accounts-card">
                        <button type="button" id="demoToggleTrigger" class="demo-toggle-trigger">
                            <span class="demo-trigger-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>Pilih Akun Demo Uji Coba (1-Click Switch)</span>
                            </span>
                            <span class="demo-toggle-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>

                        <div id="demoGridWrapper" class="demo-grid-wrapper">
                            <div class="demo-grid">
                                @foreach ($demoUsers as $u)
                                    @php
                                        $roleColor = '#0284c7';
                                        $roleLabel = $u->role_cd;
                                        if ($u->role_cd === 'DIREKSI') {
                                            $roleColor = '#dc2626';
                                            $roleLabel = 'DIREKSI';
                                        } elseif ($u->role_cd === 'MANAJER') {
                                            $roleColor = '#0284c7';
                                            $roleLabel = 'MANAJER';
                                        } elseif ($u->role_cd === 'SUPERADMIN') {
                                            $roleColor = '#991b1b';
                                            $roleLabel = 'SUPERADMIN';
                                        } elseif ($u->role_cd === 'QC') {
                                            $roleColor = '#059669';
                                            $roleLabel = 'QC INBOUND';
                                        } elseif ($u->role_cd === 'STAFF_PRODUKSI') {
                                            $roleColor = '#ea580c';
                                            $roleLabel = 'PRODUKSI';
                                        } elseif ($u->role_cd === 'ADMIN_GUDANG') {
                                            $roleColor = '#0284c7';
                                            $roleLabel = 'GUDANG RAW';
                                        } elseif ($u->role_cd === 'PURCHASING') {
                                            $roleColor = '#d97706';
                                            $roleLabel = 'PURCHASING';
                                        } elseif ($u->role_cd === 'OPERATOR_PACKAGING') {
                                            $roleColor = '#e11d48';
                                            $roleLabel = 'OPERATOR KEMAS';
                                        }
                                    @endphp
                                    <a href="{{ route('quick.login', $u->id) }}" class="demo-quick-btn">
                                        <div class="demo-role-tag" style="color: {{ $roleColor }};">
                                            <span>{{ $roleLabel }}</span>
                                            <span class="demo-card-arrow">&rarr;</span>
                                        </div>
                                        <span class="demo-user-name">{{ $u->name }}</span>
                                        <span class="demo-user-loc">
                                            @if ($u->gudang)
                                                <span>🔒 {{ $u->gudang->gudang_nm }}</span>
                                            @else
                                                <span>🌐 Seluruh Gudang</span>
                                            @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Mobile Portal --}}
            <div class="form-footer">
                <a href="{{ route('mobile.login') }}" class="link-mobile-portal">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Petugas QC & Operator Kemas? Masuk ke Portal Lapangan (HP)</span>
                </a>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/auth/login.js') }}?v={{ time() }}"></script>
</body>

</html>