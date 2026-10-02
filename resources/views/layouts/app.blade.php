<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP PT Mirasa Food Industry')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
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

        /* Click-Based Dropdown */
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

        /* 2-Column Mega Menu Styling for Master Data */
        .dropdown-mega {
            min-width: 450px !important;
            padding: 0.5rem !important;
        }
        .mega-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.4rem;
        }
        .mega-header {
            padding: 0.35rem 0.65rem;
            font-size: 0.675rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 0.25rem;
        }
        .mega-item {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 0.55rem !important;
            padding: 0.45rem 0.65rem !important;
            border-radius: 6px !important;
            color: #334155 !important;
            text-decoration: none !important;
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            border: none !important;
            border-bottom: none !important;
            transition: all 0.15s ease !important;
        }
        .mega-item:hover {
            background-color: #f1f5f9 !important;
            color: #0284c7 !important;
        }
        .mega-item.active {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
            font-weight: 700 !important;
        }
        .mega-item svg {
            color: #64748b;
            flex-shrink: 0;
            transition: color 0.15s ease;
        }
        .mega-item:hover svg, .mega-item.active svg {
            color: #0284c7;
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
    @stack('styles')
</head>
<body>
    <header>
        <div class="header-left">
            <a href="{{ Auth::check() ? route(Auth::user()->getDashboardRoute()) : url('/') }}" class="brand" style="text-decoration: none; display: flex; align-items: center; gap: 0.65rem;">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="Logo Cap Payung PT Mirasa" 
                     style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px; flex-shrink: 0; background: #ffffff; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); padding: 1px;">
                <div style="display: flex; flex-direction: column; line-height: 1.15;">
                    <span style="font-weight: 800; font-size: 0.95rem; color: #0f172a; letter-spacing: -0.01em;">MIRASA</span>
                    <span style="font-size: 0.65rem; color: #64748b; font-weight: 700; letter-spacing: 0.04em;">FOOD ERP</span>
                </div>
            </a>

            @auth
                <nav class="pill-nav">
                    {{-- 1. PENGADAAN & BAHAN MASUK (INBOUND) --}}
                    @if (Auth::user()->canAccessPo() || Auth::user()->canAccessQc() || Auth::user()->canAccessTerima() || Auth::user()->canAccessRetur())
                        @php
                            $isInboundActive = request()->routeIs('gudang.po.*') || 
                                              request()->routeIs('penjualan.so.*') || 
                                              request()->routeIs('qc.*') || 
                                              request()->routeIs('gudang.terima.*') || 
                                              request()->routeIs('gudang.retur.*');
                        @endphp
                        <div class="pill-dropdown" id="navDropdownInbound">
                            <button type="button" 
                                    class="pill-dropdown-btn {{ $isInboundActive ? 'active' : '' }}" 
                                    onclick="toggleNavDropdown('navDropdownInbound', event)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Pengadaan &amp; Inbound</span>
                                <span class="pill-dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu" style="min-width: 250px; padding: 0.45rem;">
                                @if (Auth::user()->canAccessPo())
                                    <a href="{{ route('gudang.po.index') }}" class="mega-item {{ request()->routeIs('gudang.po.*') ? 'active' : '' }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600;">Purchase Order (PO)</span>
                                            <span style="font-size: 0.6875rem; color: #64748b;">Pemesanan bahan ke supplier</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('penjualan.so.index') }}" class="mega-item {{ request()->routeIs('penjualan.so.*') ? 'active' : '' }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600;">PO Penjualan (SO)</span>
                                            <span style="font-size: 0.6875rem; color: #64748b;">Pesanan produk dari customer</span>
                                        </div>
                                    </a>
                                @endif

                                @if (Auth::user()->canAccessQc())
                                    <div style="height: 1px; background: #f1f5f9; margin: 0.3rem 0;"></div>
                                    <a href="{{ route('qc.inbound.index') }}" class="mega-item {{ request()->routeIs('qc.inbound.index') ? 'active' : '' }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600;">Pemeriksaan Mutu (QC)</span>
                                            <span style="font-size: 0.6875rem; color: #64748b;">Uji kadar air, kotoran &amp; sampling</span>
                                        </div>
                                    </a>
                                @endif

                                @if (Auth::user()->canAccessTerima())
                                    <div style="height: 1px; background: #f1f5f9; margin: 0.3rem 0;"></div>
                                    <a href="{{ route('gudang.terima.index') }}" class="mega-item {{ request()->routeIs('gudang.terima.*') ? 'active' : '' }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600;">Penerimaan Barang (GRN)</span>
                                            <span style="font-size: 0.6875rem; color: #64748b;">Bongkar muat fisik &amp; nomor batch</span>
                                        </div>
                                    </a>
                                @endif

                                @if (Auth::user()->canAccessRetur())
                                    <a href="{{ route('gudang.retur.index') }}" class="mega-item {{ request()->routeIs('gudang.retur.*') ? 'active' : '' }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600;">Retur Pembelian</span>
                                            <span style="font-size: 0.6875rem; color: #64748b;">Pengembalian barang reject ke vendor</span>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- 2. OPERASIONAL PRODUKSI PABRIK --}}
                    @if (Auth::user()->canAccessPemakaian() || Auth::user()->canAccessProduksi())
                        @php
                            $isProduksiActive = request()->routeIs('gudang.pemakaian.*') || request()->routeIs('produksi.*');
                        @endphp
                        <div class="pill-dropdown" id="navDropdownProduksi">
                            <button type="button" 
                                    class="pill-dropdown-btn {{ $isProduksiActive ? 'active' : '' }}" 
                                    onclick="toggleNavDropdown('navDropdownProduksi', event)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ea580c;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                <span>Produksi Pabrik</span>
                                <span class="pill-dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu" style="min-width: 250px; padding: 0.45rem;">
                                @if (Auth::user()->canAccessPemakaian())
                                    <a href="{{ route('gudang.pemakaian.index') }}" class="mega-item {{ request()->routeIs('gudang.pemakaian.*') ? 'active' : '' }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600;">Pemakaian Bahan (SPK)</span>
                                            <span style="font-size: 0.6875rem; color: #64748b;">Pengeluaran bahan baku ke penggorengan</span>
                                        </div>
                                    </a>
                                @endif

                                @if (Auth::user()->canAccessProduksi())
                                    <div style="height: 1px; background: #f1f5f9; margin: 0.3rem 0;"></div>
                                    <a href="{{ route('produksi.index') }}" class="mega-item {{ request()->routeIs('produksi.index') || request()->routeIs('produksi.rekap') ? 'active' : '' }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600;">Rekap Hasil &amp; HPP Harian</span>
                                            <span style="font-size: 0.6875rem; color: #64748b;">Timbangan keripik jadi &amp; susut HPP</span>
                                        </div>
                                    </a>
                                    @if (Auth::user()->canCreateProduksi())
                                        <a href="{{ route('produksi.create') }}" class="mega-item {{ request()->routeIs('produksi.create') ? 'active' : '' }}">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <div style="display: flex; flex-direction: column;">
                                                <span style="font-weight: 600;">+ Input Hasil Produksi</span>
                                                <span style="font-size: 0.6875rem; color: #64748b;">Catat hasil masak, borongan &amp; gas</span>
                                            </div>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- 3. GUDANG & STOK PERSEDIAAN --}}
                    @if (Auth::user()->canAccessStok())
                        <div class="pill-dropdown" id="navDropdownStok">
                            <button type="button" 
                                    class="pill-dropdown-btn {{ request()->routeIs('gudang.stok.*') ? 'active' : '' }}" 
                                    onclick="toggleNavDropdown('navDropdownStok', event)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7h16m-8 4v6"/></svg>
                                <span>Stok &amp; Gudang</span>
                                <span class="pill-dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu" style="min-width: 240px; padding: 0.45rem;">
                                <a href="{{ route('gudang.stok.index') }}" class="mega-item {{ request()->routeIs('gudang.stok.index') ? 'active' : '' }}">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;">Lacak Stok Komoditas</span>
                                        <span style="font-size: 0.6875rem; color: #64748b;">Saldo fisik &amp; status batch gudang</span>
                                    </div>
                                </a>
                                <a href="{{ route('gudang.stok.ledger') }}" class="mega-item {{ request()->routeIs('gudang.stok.ledger') ? 'active' : '' }}">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;">Buku Kartu Stok (Ledger)</span>
                                        <span style="font-size: 0.6875rem; color: #64748b;">Rekap mutasi masuk &amp; keluar</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 4. MASTER DATA (2-KOLOM MEGA MENU) --}}
                    @if (Auth::user()->canAccessMasterData())
                        <div class="pill-dropdown" id="navDropdownMaster">
                            <button type="button" 
                                    class="pill-dropdown-btn {{ request()->routeIs('master.*') ? 'active' : '' }}" 
                                    onclick="toggleNavDropdown('navDropdownMaster', event)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #7c3aed;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                <span>Master Data</span>
                                <span class="pill-dropdown-arrow">▼</span>
                            </button>
                            <div class="dropdown-menu dropdown-mega">
                                <div class="mega-grid">
                                    {{-- KOLOM 1: MATERIAL & RESEP PABRIK --}}
                                    <div style="display: flex; flex-direction: column;">
                                        <div class="mega-header" style="color: #0284c7;">
                                            Material &amp; Resep
                                        </div>
                                        <a href="{{ route('master.barang.index') }}" class="mega-item {{ request()->routeIs('master.barang.*') ? 'active' : '' }}">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Katalog Barang</span>
                                        </a>
                                        @if (Auth::user()->canAccessResep())
                                            <a href="{{ route('master.resep.index') }}" class="mega-item {{ request()->routeIs('master.resep.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                <span>Formula Resep (BOM)</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()->isSuperAdmin() || Auth::user()->isGudang())
                                            <a href="{{ route('master.satuan.index') }}" class="mega-item {{ request()->routeIs('master.satuan.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 18h12l3-18H3z"/></svg>
                                                <span>Satuan Ukur</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()->isSuperAdmin())
                                            <a href="{{ route('master.jenis.index') }}" class="mega-item {{ request()->routeIs('master.jenis.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                <span>Kategori / Jenis Barang</span>
                                            </a>
                                        @endif
                                    </div>

                                    {{-- KOLOM 2: RELASI & ENTITAS --}}
                                    <div style="display: flex; flex-direction: column;">
                                        <div class="mega-header" style="color: #059669;">
                                            Relasi &amp; Fasilitas
                                        </div>
                                        @if (Auth::user()->canAccessSupplier())
                                            <a href="{{ route('master.supplier.index') }}" class="mega-item {{ request()->routeIs('master.supplier.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>Mitra Supplier</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()->canAccessJenisSupplier())
                                            <a href="{{ route('master.jenis_supplier.index') }}" class="mega-item {{ request()->routeIs('master.jenis_supplier.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                <span>Jenis Supplier</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()->canAccessPerusahaan())
                                            <a href="{{ route('master.perusahaan.index') }}" class="mega-item {{ request()->routeIs('master.perusahaan.*', 'master.gudang.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                <span>Master Perusahaan</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()->canAccessCustomer())
                                            <a href="{{ route('master.customer.index') }}" class="mega-item {{ request()->routeIs('master.customer.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                                <span>Mitra Customer</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()->canAccessKaryawan())
                                            <a href="{{ route('master.karyawan.index') }}" class="mega-item {{ request()->routeIs('master.karyawan.*') ? 'active' : '' }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                                <span>Data Karyawan</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 5. HAK AKSES SISTEM (KHUSUS SUPERADMIN) --}}
                    @if (Auth::user()->canManageUsers())
                        <a href="{{ route('admin.users.index') }}" class="pill-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
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
    @stack('scripts')
</body>
</html>
