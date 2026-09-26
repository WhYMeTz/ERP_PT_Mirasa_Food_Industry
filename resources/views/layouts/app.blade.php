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
            padding: 0.65rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            gap: 1.5rem;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            color: #0f172a;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }
        .brand-badge {
            background: #0284c7;
            color: #ffffff;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-weight: 800;
            letter-spacing: 0.05em;
        }

        /* Segmented Pill Navigation Container */
        .pill-nav {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            padding: 0.25rem;
            border-radius: 10px;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
        .pill-item, .pill-dropdown-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            font-size: 0.825rem;
            padding: 0.4rem 0.8rem;
            border-radius: 7px;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            line-height: 1.2;
            user-select: none;
        }
        .pill-item:hover, .pill-dropdown-btn:hover {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.75);
        }
        .pill-item.active, .pill-dropdown-btn.active, .pill-dropdown.active .pill-dropdown-btn {
            color: #0284c7;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            font-weight: 700;
        }
        .pill-dropdown-arrow {
            display: inline-block;
            font-size: 0.6rem;
            color: #94a3b8;
            margin-left: 3px;
            transition: transform 0.2s ease, color 0.2s ease;
        }
        .pill-dropdown.active .pill-dropdown-arrow {
            transform: rotate(180deg);
            color: #0284c7;
        }
        .pill-badge {
            font-size: 0.6875rem;
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .pill-badge-po { background: #e0f2fe; color: #0369a1; }
        .pill-badge-gr { background: #dcfce7; color: #15803d; }
        .pill-badge-out { background: #fee2e2; color: #b91c1c; }

        /* Click-Based Dropdown (Tidak hilang saat kursor bergerak) */
        .pill-dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            top: 100%;
            margin-top: 0.45rem;
            background-color: #ffffff;
            min-width: 240px;
            box-shadow: 0 15px 30px -5px rgba(15, 23, 42, 0.18), 0 4px 6px -2px rgba(15, 23, 42, 0.05);
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            z-index: 1000;
            overflow: hidden;
            animation: menuFadeIn 0.15s ease-out;
        }
        @keyframes menuFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-right {
            left: auto;
            right: 0;
        }
        .pill-dropdown.active .dropdown-menu {
            display: block !important;
        }
        .dropdown-menu a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1rem;
            color: #334155;
            text-decoration: none;
            font-size: 0.825rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }
        .dropdown-menu a:last-child {
            border-bottom: none;
        }
        .dropdown-menu a:hover {
            background-color: #f0f9ff;
            color: #0284c7;
        }
        .dropdown-item-title {
            font-weight: 600;
            display: block;
            line-height: 1.25;
            color: #0f172a;
        }
        .dropdown-item-desc {
            font-size: 0.7rem;
            color: #64748b;
            display: block;
            margin-top: 0.15rem;
        }

        /* Header Right / User Chip */
        .header-right {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .user-chip-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.35rem 0.75rem;
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.15s;
            text-align: left;
        }
        .user-chip-btn:hover, .pill-dropdown.active .user-chip-btn {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        main {
            flex: 1;
            width: 100%;
            max-width: 100%;
            margin: 1.25rem 0;
            padding: 0 1.75rem;
        }

        @media (max-width: 768px) {
            header {
                padding: 0.65rem 1rem;
            }
            main {
                padding: 0 1rem;
                margin: 1rem 0;
            }
        }
        .table-compact th, .table-compact td {
            padding: 0.5rem 0.45rem !important;
            vertical-align: middle;
        }
        .table-compact .form-control {
            padding: 0.4rem 0.5rem !important;
            font-size: 0.825rem !important;
            border-radius: 6px !important;
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

        /* Laravel & Bootstrap Pagination Controls Styling */
        nav[role="navigation"] {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        nav[role="navigation"] svg,
        .pagination svg,
        nav svg {
            width: 1.15rem !important;
            height: 1.15rem !important;
            max-width: 1.15rem !important;
            max-height: 1.15rem !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        nav[role="navigation"] div, nav[role="navigation"] span, nav[role="navigation"] a {
            font-size: 0.825rem !important;
        }
        .pagination {
            display: flex;
            list-style: none;
            gap: 0.25rem;
            align-items: center;
            padding: 0;
            margin: 0;
        }
        .page-item .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 2rem;
            height: 2rem;
        }
        .page-item.active .page-link {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }
        .page-item.disabled .page-link {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
            cursor: not-allowed;
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
        <div class="header-left">
            <a href="{{ Auth::check() ? route(Auth::user()->getDashboardRoute()) : url('/') }}" class="brand">
                <span class="brand-badge">ERP</span>
                <span>PT Mirasa Food Industry</span>
            </a>

            @auth
                <nav class="pill-nav">
                    {{-- PURCHASE ORDER --}}
                    @if (Auth::user()->canAccessPo())
                        <a href="{{ route('gudang.po.index') }}" class="pill-item {{ request()->routeIs('gudang.po.*') ? 'active' : '' }}">
                            <span class="pill-badge pill-badge-po">PO</span>
                            <span>Purchase Order</span>
                        </a>
                    @endif

                    {{-- BARANG MASUK (GRN) --}}
                    @if (Auth::user()->canAccessTerima())
                        <a href="{{ route('gudang.terima.index') }}" class="pill-item {{ request()->routeIs('gudang.terima.*') ? 'active' : '' }}">
                            <span class="pill-badge pill-badge-gr">GR</span>
                            <span>Barang Masuk</span>
                        </a>
                    @endif

                    {{-- BARANG KELUAR (OUT) --}}
                    @if (Auth::user()->canAccessPemakaian())
                        <a href="{{ route('gudang.pemakaian.index') }}" class="pill-item {{ request()->routeIs('gudang.pemakaian.*') ? 'active' : '' }}">
                            <span class="pill-badge pill-badge-out">OUT</span>
                            <span>Barang Keluar</span>
                        </a>
                    @endif

                    {{-- STOK PERSEDIAAN (CLICK-BASED DROPDOWN) --}}
                    @if (Auth::user()->canAccessStok())
                        <div class="pill-dropdown" id="navDropdownStok">
                            <button type="button" 
                                    class="pill-dropdown-btn {{ request()->routeIs('gudang.stok.*') ? 'active' : '' }}" 
                                    onclick="toggleNavDropdown('navDropdownStok', event)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <span>Stok Persediaan</span>
                                <span class="pill-dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu">
                                <a href="{{ route('gudang.stok.index') }}" style="{{ request()->routeIs('gudang.stok.index') ? 'background:#f0f9ff; font-weight:700;' : '' }}">
                                    <div>
                                        <span class="dropdown-item-title">📊 Lacak Stok (Batch Monitoring)</span>
                                        <span class="dropdown-item-desc">Status Tersedia/Habis, Qty Awal & Sisa Nilai</span>
                                    </div>
                                </a>
                                <a href="{{ route('gudang.stok.ledger') }}" style="{{ request()->routeIs('gudang.stok.ledger') ? 'background:#f0f9ff; font-weight:700;' : '' }}">
                                    <div>
                                        <span class="dropdown-item-title">📑 Kartu Stok (Buku Mutasi / Ledger)</span>
                                        <span class="dropdown-item-desc">Riwayat mutasi IN / OUT & saldo akhir</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- MASTER DATA (CLICK-BASED DROPDOWN) --}}
                    @if (Auth::user()->canAccessMasterData())
                        <div class="pill-dropdown" id="navDropdownMaster">
                            <button type="button" 
                                    class="pill-dropdown-btn {{ request()->routeIs('master.*') ? 'active' : '' }}" 
                                    onclick="toggleNavDropdown('navDropdownMaster', event)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                <span>Master Data</span>
                                <span class="pill-dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu">
                                <a href="{{ route('master.barang.index') }}">
                                    <div>
                                        <span class="dropdown-item-title">Katalog Barang</span>
                                        <span class="dropdown-item-desc">Singkong, Bumbu, Minyak, Kemasan</span>
                                    </div>
                                </a>
                                @if (Auth::user()->isSuperAdmin() || Auth::user()->isGudang())
                                    <a href="{{ route('master.satuan.index') }}">
                                        <div>
                                            <span class="dropdown-item-title">Satuan Ukur</span>
                                            <span class="dropdown-item-desc">Kg, Sak, Pcs, Bal</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('master.gudang.index') }}">
                                        <div>
                                            <span class="dropdown-item-title">Daftar Gudang</span>
                                            <span class="dropdown-item-desc">Gudang Magelang, Bahan Baku, FG</span>
                                        </div>
                                    </a>
                                @endif
                                @if (Auth::user()->isSuperAdmin() || Auth::user()->isPurchasing())
                                    <a href="{{ route('master.supplier.index') }}">
                                        <div>
                                            <span class="dropdown-item-title">Mitra Supplier</span>
                                            <span class="dropdown-item-desc">Petani Singkong, Supplier Bahan</span>
                                        </div>
                                    </a>
                                @endif
                                @if (Auth::user()->isSuperAdmin())
                                    <a href="{{ route('master.jenis.index') }}">
                                        <div>
                                            <span class="dropdown-item-title">Jenis Barang</span>
                                            <span class="dropdown-item-desc">Kategori Bahan & WIP</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('master.customer.index') }}">
                                        <div>
                                            <span class="dropdown-item-title">Mitra Customer</span>
                                            <span class="dropdown-item-desc">Indofood, Toko Distributor</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('master.karyawan.index') }}">
                                        <div>
                                            <span class="dropdown-item-title">Data Karyawan</span>
                                            <span class="dropdown-item-desc">Mandor, Staf & Operator</span>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- HAK AKSES SISTEM (KHUSUS SUPERADMIN) --}}
                    @if (Auth::user()->canManageUsers())
                        <a href="{{ route('admin.users.index') }}" class="pill-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Hak Akses User</span>
                        </a>
                    @endif
                </nav>
            @endauth
        </div>

        {{-- HEADER RIGHT (PROFIL PENGGUNA & LOGOUT) --}}
        <div class="header-right">
            @auth
                @php
                    $roleBg = '#0284c7';
                    if (Auth::user()->isSuperAdmin()) $roleBg = '#dc2626';
                    elseif (Auth::user()->isProduksi()) $roleBg = '#ea580c';
                    elseif (Auth::user()->isPurchasing()) $roleBg = '#d97706';
                @endphp
                <div class="pill-dropdown" id="navDropdownUser">
                    <button type="button" class="user-chip-btn" onclick="toggleNavDropdown('navDropdownUser', event)" title="Klik untuk ganti peran">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $roleBg }}; display: inline-block;"></span>
                        <div>
                            <div style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; line-height: 1.2;">
                                {{ Auth::user()->name }}
                            </div>
                            <div style="font-size: 0.6875rem; color: #64748b;">
                                <strong style="color: {{ $roleBg }};">{{ Auth::user()->role_cd }}</strong>
                                @if (Auth::user()->gudang)
                                    • {{ Auth::user()->gudang->gudang_nm }}
                                @endif
                            </div>
                        </div>
                        <span class="pill-dropdown-arrow">▼</span>
                    </button>

                    <div class="dropdown-menu dropdown-right" style="min-width: 250px;">
                        <div style="padding: 0.6rem 0.95rem; background: #f8fafc; font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">
                            ⚡ Ganti Peran Cepat (Testing)
                        </div>
                        @php
                            $switchUsers = \App\Models\User::where('active_st', true)->where('deleted_st', false)->orderBy('id')->get();
                        @endphp
                        @foreach ($switchUsers as $su)
                            <a href="{{ route('quick.login', $su->id) }}" style="padding: 0.6rem 0.95rem; {{ $su->id === Auth::id() ? 'background: #f0fdf4; font-weight: 700;' : '' }}">
                                <div>
                                    <div style="font-size: 0.8125rem; color: #0f172a;">{{ $su->name }}</div>
                                    <div style="font-size: 0.6875rem; color: #64748b;">
                                        Role: <strong>{{ $su->role_cd }}</strong>
                                        @if ($su->gudang) ({{ $su->gudang->gudang_cd }}) @endif
                                    </div>
                                </div>
                                @if ($su->id === Auth::id())
                                    <span style="color: #16a34a; font-size: 0.75rem;">✓ Aktif</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Logout Button --}}
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.65rem; color: #ef4444; border-radius: 8px;" title="Keluar / Logout">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm" style="padding: 0.45rem 0.85rem;">
                    Masuk / Login &rarr;
                </a>
            @endauth
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#065f46;font-size:1.1rem;">&times;</button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-error" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.35rem;">
                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                    <strong>Silakan periksa kembali input formulir Anda:</strong>
                    <button onclick="this.closest('.alert').remove()" style="background:none;border:none;cursor:pointer;color:#991b1b;font-size:1.1rem;">&times;</button>
                </div>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
        &copy; {{ date('Y') }} ERP PT Mirasa Food Industry &bull; Pabrik Pengolahan F&B Singkong
    </footer>

    <script>
        // Nav Dropdown Click Toggle (Hanya terbuka saat diklik, TIDAK hilang saat kursor bergerak)
        function toggleNavDropdown(id, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            const target = document.getElementById(id);
            if (!target) return;
            const wasActive = target.classList.contains('active');
            
            // Tutup semua dropdown navigasi lain
            document.querySelectorAll('.pill-dropdown').forEach(d => {
                if (d !== target) d.classList.remove('active');
            });
            
            // Toggle dropdown yang ditarget
            if (wasActive) {
                target.classList.remove('active');
            } else {
                target.classList.add('active');
            }
        }

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

        // Close dropdown & modal saat klik di luar
        window.addEventListener('click', function(e) {
            // Tutup pill-dropdown jika klik di luar elemen dropdown
            if (!e.target.closest('.pill-dropdown')) {
                document.querySelectorAll('.pill-dropdown').forEach(d => d.classList.remove('active'));
            }

            // Tutup modal backdrop jika diklik
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.classList.remove('show');
            }
        });

        // Close dropdown & modal saat tombol ESC ditekan
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.pill-dropdown').forEach(d => d.classList.remove('active'));
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
