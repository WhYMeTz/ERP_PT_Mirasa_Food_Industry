<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ERP PT Mirasa Food Industry</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #334155;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 460px;
            padding: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            text-decoration: none;
        }
        .brand-badge {
            background: #0284c7;
            color: white;
            font-weight: 800;
            font-size: 0.875rem;
            padding: 0.4rem 0.65rem;
            border-radius: 8px;
            letter-spacing: 0.05em;
        }
        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        .brand-sub {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.35rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.9375rem;
            transition: all 0.2s;
            outline: none;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            background: #0284c7;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        .btn-submit:hover {
            background: #0369a1;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            margin-bottom: 1.25rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .demo-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px dashed #e2e8f0;
        }
        .demo-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        .demo-btn {
            display: flex;
            flex-direction: column;
            text-align: left;
            padding: 0.6rem 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.2s;
        }
        .demo-btn:hover {
            background: #f0f9ff;
            border-color: #0284c7;
            transform: translateY(-2px);
        }
        .demo-btn-role {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .demo-btn-name {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0.15rem;
        }
        .demo-btn-loc {
            font-size: 0.7rem;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">
        <span class="brand-badge">ERP</span>
        <div>
            <div class="brand-text">PT Mirasa Food Industry</div>
            <div class="brand-sub">Sistem Manajemen Produksi & Distribusi Makanan</div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('login.attempt') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email" class="form-label">Email Pengguna</label>
            <input type="email" id="email" name="email" value="{{ old('email', 'produksi.mgl@mirasa.co.id') }}" class="form-control" required placeholder="nama@mirasa.co.id">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi (Password)</label>
            <input type="password" id="password" name="password" value="password123" class="form-control" required placeholder="Masukkan kata sandi...">
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <label style="font-size: 0.8125rem; color: #475569; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                <input type="checkbox" name="remember" value="1" checked> Ingat saya
            </label>
            <span style="font-size: 0.75rem; color: #64748b;">Default: password123</span>
        </div>

        <button type="submit" class="btn-submit">Masuk ke Sistem &rarr;</button>
    </form>

    {{-- QUICK DEMO SWITCHER --}}
    @if (!empty($demoUsers) && $demoUsers->count() > 0)
        <div class="demo-section">
            <div class="demo-title">
                <span>⚡</span>
                <span>Masuk Cepat 1-Klik (Uji Coba Role & Gudang):</span>
            </div>
            <div class="demo-grid">
                @foreach ($demoUsers as $u)
                    <a href="{{ route('quick.login', $u->id) }}" class="demo-btn">
                        @php
                            $roleColor = '#0284c7';
                            if ($u->role_cd === 'SUPERADMIN') $roleColor = '#dc2626';
                            elseif ($u->role_cd === 'STAFF_PRODUKSI') $roleColor = '#ea580c';
                            elseif ($u->role_cd === 'ADMIN_GUDANG') $roleColor = '#0284c7';
                            elseif ($u->role_cd === 'PURCHASING') $roleColor = '#d97706';
                        @endphp
                        <span class="demo-btn-role" style="color: {{ $roleColor }};">{{ $u->role_cd }}</span>
                        <span class="demo-btn-name">{{ $u->name }}</span>
                        <span class="demo-btn-loc">
                            @if ($u->gudang)
                                🔒 {{ $u->gudang->gudang_nm }}
                            @else
                                🌐 Seluruh Gudang
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

</body>
</html>
