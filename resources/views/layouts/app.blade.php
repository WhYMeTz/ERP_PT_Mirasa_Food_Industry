<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP PT Mirasa Food Industry')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.875rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #0f172a;
            font-weight: 700;
            font-size: 1.15rem;
        }
        .brand-badge {
            background: #0284c7;
            color: #ffffff;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
        }
        nav a {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.2s;
        }
        nav a:hover, nav a.active {
            color: #0284c7;
            background: #f0f9ff;
            font-weight: 600;
        }
        main {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.925rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.125rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        .btn-danger {
            background: #fee2e2;
            color: #b91c1c;
        }
        .btn-danger:hover {
            background: #fecaca;
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-align: left;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        tr:hover td {
            background: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-info {
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-success {
            background: #ecfdf5;
            color: #065f46;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.375rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: #334155;
        }
        .form-control {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.925rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2,132,199,0.15);
        }
        .form-error {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* Modal Popup Styling */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .modal-backdrop.show {
            display: flex;
        }
        .modal-dialog {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 620px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            animation: modalSlideIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-16px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #94a3b8;
            line-height: 1;
            transition: color 0.15s;
        }
        .modal-close:hover {
            color: #334155;
        }
        .modal-body {
            padding: 1.5rem;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background: #f8fafc;
        }

        footer {
            text-align: center;
            padding: 1.5rem;
            color: #94a3b8;
            font-size: 0.85rem;
            border-top: 1px solid #e2e8f0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header>
        <a href="{{ route('master.barang.index') }}" class="brand">
            <span class="brand-badge">ERP</span>
            PT Mirasa Food Industry
        </a>
        <nav style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
            {{-- Navigasi Master Data --}}
            <a href="{{ route('master.barang.index') }}" class="{{ request()->routeIs('master.barang.*') ? 'active' : '' }}">Barang</a>
            <a href="{{ route('master.satuan.index') }}" class="{{ request()->routeIs('master.satuan.*') ? 'active' : '' }}">Satuan</a>
            <a href="{{ route('master.jenis.index') }}" class="{{ request()->routeIs('master.jenis.*') ? 'active' : '' }}">Jenis</a>
            <a href="{{ route('master.gudang.index') }}" class="{{ request()->routeIs('master.gudang.*') ? 'active' : '' }}">Gudang</a>
            <a href="{{ route('master.supplier.index') }}" class="{{ request()->routeIs('master.supplier.*') || request()->routeIs('master.jenis_supplier.*') ? 'active' : '' }}">Supplier</a>
            <a href="{{ route('master.customer.index') }}" class="{{ request()->routeIs('master.customer.*') ? 'active' : '' }}">Customer</a>
            <a href="{{ route('master.karyawan.index') }}" class="{{ request()->routeIs('master.karyawan.*') ? 'active' : '' }}">Karyawan</a>

            <span style="color: #cbd5e1; margin: 0 0.35rem;">|</span>

            {{-- Navigasi Transaksi Gudang & Inventory (Sesuai 4 Sheet Operasional Mirasa) --}}
            <a href="{{ route('gudang.po.index') }}" class="{{ request()->routeIs('gudang.po.*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                <span style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700;">PO</span>
                Purchase Order
            </a>
            <a href="{{ route('gudang.terima.index') }}" class="{{ request()->routeIs('gudang.terima.*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                <span style="font-size: 0.75rem; background: #ecfdf5; color: #065f46; padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700;">GR</span>
                Barang Masuk
            </a>
            <a href="{{ route('gudang.pemakaian.index') }}" class="{{ request()->routeIs('gudang.pemakaian.*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                <span style="font-size: 0.75rem; background: #fee2e2; color: #991b1b; padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700;">OUT</span>
                Barang Keluar
            </a>
            <a href="{{ route('gudang.stok.index') }}" class="{{ request()->routeIs('gudang.stok.index') ? 'active' : '' }}">Lacak Stok</a>
            <a href="{{ route('gudang.stok.ledger') }}" class="{{ request()->routeIs('gudang.stok.ledger') ? 'active' : '' }}">Kartu Stok</a>

            <span style="color: #cbd5e1; margin: 0 0.35rem;">|</span>

            {{-- Navigasi Hak Akses & Pengguna --}}
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" style="display: inline-flex; align-items: center; gap: 0.3rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Hak Akses User
            </a>

            <span style="color: #cbd5e1; margin: 0 0.35rem;">|</span>

            {{-- Info Pengguna yang Sedang Login --}}
            @auth
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.25rem 0.65rem; border-radius: 8px;">
                    <div style="text-align: left;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; display: block; line-height: 1.2;">
                            {{ Auth::user()->name }}
                        </span>
                        <span style="font-size: 0.7rem; color: #64748b; display: block;">
                            {{ Auth::user()->role_cd }}
                            @if (Auth::user()->gudang)
                                • <strong style="color: #059669;">🔒 {{ Auth::user()->gudang->gudang_nm }}</strong>
                            @else
                                • <strong style="color: #0284c7;">🌐 Pusat</strong>
                            @endif
                        </span>
                    </div>

                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Beralih ke akun lain">
                        Ganti Akun
                    </a>

                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.4rem; color: #ef4444;" title="Keluar">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm" style="padding: 0.35rem 0.75rem;">
                    Masuk / Login &rarr;
                </a>
            @endauth
        </nav>
    </header>

    <main>
        @if (session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#065f46;font-size:1.1rem;">&times;</button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-error">
                <span>Silakan periksa kembali input formulir Anda.</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#991b1b;font-size:1.1rem;">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
        &copy; {{ date('Y') }} ERP PT Mirasa Food Industry &bull; Pabrik Pengolahan F&B Singkong
    </footer>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
            }
        }

        // Close when clicking outside modal dialog
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.classList.remove('show');
            }
        });

        // Close on ESC key
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
            }
        });

        // AJAX Helper untuk auto-generate kode master
        async function fetchNextCode(type, targetInputId, extraParams = {}) {
            const input = document.getElementById(targetInputId);
            if (!input) return;

            let url = '{{ route("ajax.generate_code") }}?type=' + encodeURIComponent(type);
            for (const [key, value] of Object.entries(extraParams)) {
                if (value) url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(value);
            }

            try {
                const btn = document.querySelector(`[data-target="${targetInputId}"]`);
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }

                const res = await fetch(url);
                const data = await res.json();
                if (data.status === 'success' && data.code) {
                    input.value = data.code;
                }

                if (btn) {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            } catch (err) {
                console.error('Gagal mengambil kode otomatis:', err);
            }
        }

        // Debounce helper untuk meng-generate kode dari nama saat user selesai mengetik
        let debounceTimers = {};
        function debounceCodeFromName(type, nameInputId, codeInputId, extraParams = {}) {
            clearTimeout(debounceTimers[codeInputId]);
            debounceTimers[codeInputId] = setTimeout(() => {
                const nameInput = document.getElementById(nameInputId);
                const codeInput = document.getElementById(codeInputId);
                const nameVal = nameInput ? nameInput.value.trim() : '';

                if (nameVal && nameVal.length >= 3 && codeInput) {
                    fetchNextCode(type, codeInputId, { ...extraParams, name: nameVal });
                }
            }, 500);
        }
    </script>
</body>
</html>
