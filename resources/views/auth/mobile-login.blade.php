<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Login Operator Kemas - PT Mirasa Food Industry</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth/mobile-login.css') }}">
</head>
<body class="pkg-login-body">
    <div class="pkg-login-container">
        {{-- BRANDING KHUSUS OPERATOR LAPANGAN --}}
        <div class="pkg-login-header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo PT Mirasa" class="pkg-login-logo" onerror="this.style.display='none'">
            <div>
                <span class="pkg-login-badge">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Portal Shopfloor Kemas</span>
                </span>
            </div>
            <h1 class="pkg-login-title">PT Mirasa Food Industry</h1>
            <p class="pkg-login-subtitle">Silakan login untuk mencatat laporan kemas harian</p>
        </div>

        {{-- KARTU FORM LOGIN MOBILE (MODERN RED & WHITE) --}}
        <div class="pkg-login-card">
            @if (isset($errors) && $errors->any())
                <div class="pkg-alert-error">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @if (session('success'))
                <div class="pkg-alert-success">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form action="{{ route('mobile.login.attempt') }}" method="POST" id="mobileLoginForm">
                @csrf

                {{-- Input Akun / Email / Username --}}
                <div class="pkg-form-group">
                    <label for="emailInput" class="pkg-form-label">Akun / Email Operator</label>
                    <div class="pkg-input-wrapper">
                        <span class="pkg-input-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input type="text" id="emailInput" name="email" class="pkg-input-field" placeholder="contoh: produksi.mgl@mirasa.co.id" value="{{ old('email') }}" required autocomplete="username">
                    </div>
                </div>

                {{-- Input Kata Sandi --}}
                <div class="pkg-form-group">
                    <label for="passwordInput" class="pkg-form-label">Kata Sandi</label>
                    <div class="pkg-input-wrapper">
                        <span class="pkg-input-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" id="passwordInput" name="password" class="pkg-input-field" placeholder="Masukkan kata sandi..." required autocomplete="current-password">
                        <button type="button" id="togglePasswordBtn" class="pkg-toggle-pwd" title="Lihat/Sembunyikan Sandi">
                            <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Ingat Saya --}}
                <div class="pkg-remember-wrapper">
                    <label class="pkg-checkbox-label">
                        <input type="checkbox" name="remember" value="1" checked>
                        <span>Ingat saya di HP ini</span>
                    </label>
                </div>

                {{-- Tombol Masuk --}}
                <button type="submit" class="pkg-btn-submit">
                    <span>Masuk ke Sistem Kemas</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            {{-- 1-Click Fast Switcher Untuk Kemudahan Pengujian (Dev / Demo) --}}
            @if(isset($operatorUsers) && $operatorUsers->count() > 0)
                <div class="pkg-quick-login-section">
                    <div class="pkg-quick-title">
                        <span>Pilih Akun Demo / Uji Coba:</span>
                    </div>
                    <div class="pkg-quick-grid">
                        @foreach($operatorUsers as $op)
                            <button type="button" class="pkg-quick-user-btn" onclick="selectQuickOperator('{{ $op->email }}', 'password123')">
                                <div>
                                    <span class="pkg-quick-name">{{ $op->name }}</span>
                                    <span class="pkg-quick-meta">{{ $op->email }} ({{ $op->role_cd }})</span>
                                </div>
                                <span class="pkg-quick-arrow">Login &rarr;</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- LINK MENUJU WEB OFFICE --}}
        <div class="pkg-login-footer">
            <a href="{{ route('login') }}" class="pkg-link-office">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Bukan operator? Masuk ke Web Office ERP</span>
            </a>
        </div>
    </div>

    <script src="{{ asset('js/auth/mobile-login.js') }}"></script>
</body>
</html>
