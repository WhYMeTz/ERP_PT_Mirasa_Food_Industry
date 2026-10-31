<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#dc2626">
    <title>@yield('title', 'Executive Portal - PT Mirasa Food Industry')</title>

    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Font: Plus Jakarta Sans & SF Style -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Zero-Flicker Early Script (Runs before First Paint) -->
    <script>
        (function() {
            try {
                if (localStorage.getItem('mirasa_exec_sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch(e) {}
        })();
    </script>

    <!-- Executive Portal CSS -->
    <link rel="stylesheet" href="{{ asset('css/approval/executive-dashboard.css') }}?v={{ filemtime(public_path('css/approval/executive-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-review.css') }}?v={{ filemtime(public_path('css/gudang/po/po-review.css')) }}">
    @stack('styles')
</head>
<body class="executive-body">

    @php
        $user = Auth::user();
        $pendingCount = \App\Models\Gudang\DatPoHdr::where('deleted_st', false)
            ->where('status_cd', 'PENDING_APPROVAL')
            ->count();
        $revisionCount = \App\Models\Gudang\DatPoHdr::where('deleted_st', false)
            ->where('status_cd', 'REVISION')
            ->count();
        $activeView = request('view', 'dashboard');
        $activeStatus = request('status');

        // Referensi Atasan: Stok Menipis & Jatuh Tempo AP
        $subBatch = \App\Models\Gudang\DatStokBatch::where('deleted_st', false)
            ->where('sisa_qty', '>', 0)
            ->selectRaw('barang_id, SUM(sisa_qty) as total_sisa')
            ->groupBy('barang_id');
        $lowStockCount = \App\Models\MasterData\MstBarang::active()
            ->leftJoinSub($subBatch, 's', 'mst_barang.barang_id', '=', 's.barang_id')
            ->whereRaw('COALESCE(s.total_sisa, 0) > 0 AND COALESCE(s.total_sisa, 0) <= mst_barang.batas_minimum_qty')
            ->count();

        $jatuhTempoCount = \App\Models\Keuangan\DatHutangHdr::where('deleted_st', false)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->where('tgl_jatuh_tempo', '<', now()->startOfDay())
            ->count();
        
        $recentPos = \App\Models\Gudang\DatPoHdr::where('deleted_st', false)
            ->latest('created_at')
            ->limit(5)
            ->get();
    @endphp

    <div class="exec-layout-shell">

        {{-- MACOS SIDEBAR (MATCHING USER REFERENCE DESIGN EXACTLY) --}}
        <aside class="exec-sidebar" id="execSidebar">
            
            {{-- 1. Window Controls Header (Traffic Lights + Sidebar Toggle) --}}
            <div class="macos-window-header">
                <div class="macos-traffic-lights" title="macOS Controls">
                    <span class="traffic-dot dot-close" title="Close"></span>
                    <span class="traffic-dot dot-minimize" id="macMinimizeBtn" title="Minimize / Expand Sidebar"></span>
                    <span class="traffic-dot dot-expand" title="Expand"></span>
                </div>
                <button type="button" class="btn-macos-toggle" id="execSidebarToggle" title="Toggle Sidebar (Ctrl/Cmd + B)">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="3" ry="3"></rect>
                        <line x1="9" y1="3" x2="9" y2="21"></line>
                    </svg>
                </button>
            </div>

            {{-- 2. Date Meta & Hand-Drawn Sunrise Sketch (Collapsible) --}}
            <div class="macos-header-card sidebar-hide-on-collapse">
                <div class="macos-date-col">
                    <div class="macos-day-title">{{ now()->translatedFormat('l') }}</div>
                    <div class="macos-full-date">{{ now()->translatedFormat('M d, Y') }}</div>
                    <div class="macos-weather-tag">
                        <span>Pabrik Mirasa</span>
                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <span>28°C</span>
                    </div>
                </div>

                {{-- Sketch Sunrise Illustration (Hand-drawn style matching screenshot) --}}
                <div class="macos-sketch-col" title="Selamat Pagi dari Mirasa">
                    <svg width="68" height="42" viewBox="0 0 100 60" fill="none" stroke="#27272a" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Birds in sky -->
                        <path d="M12 12 Q16 9 20 12 Q24 9 28 12" />
                        <path d="M74 8 Q78 5 82 8 Q86 5 90 8" />
                        <path d="M82 14 Q85 12 88 14 Q91 12 94 14" />
                        <!-- Sun Rays -->
                        <line x1="28" y1="28" x2="18" y2="24" stroke-dasharray="1 2.5" />
                        <line x1="34" y1="20" x2="26" y2="12" stroke-dasharray="1 2.5" />
                        <line x1="43" y1="15" x2="39" y2="6" stroke-dasharray="1 2.5" />
                        <line x1="53" y1="13" x2="53" y2="4" stroke-dasharray="1 2.5" />
                        <line x1="63" y1="15" x2="67" y2="6" stroke-dasharray="1 2.5" />
                        <line x1="72" y1="20" x2="80" y2="12" stroke-dasharray="1 2.5" />
                        <line x1="78" y1="28" x2="88" y2="24" stroke-dasharray="1 2.5" />
                        <!-- Sun Half Circle with fine hatch -->
                        <path d="M35 34 A 18 18 0 0 1 71 34" fill="#27272a" fill-opacity="0.12" />
                        <path d="M33 34 A 20 20 0 0 1 73 34" stroke-width="1.3" />
                        <!-- Ocean Horizon & Ripples -->
                        <line x1="8" y1="34" x2="96" y2="34" stroke-width="1.5" />
                        <line x1="16" y1="38" x2="90" y2="38" stroke-dasharray="4 3" />
                        <line x1="24" y1="42" x2="82" y2="42" stroke-dasharray="5 4" />
                        <line x1="32" y1="46" x2="74" y2="46" stroke-dasharray="3 3" />
                        <line x1="42" y1="50" x2="64" y2="50" stroke-dasharray="2 3" />
                    </svg>
                </div>
            </div>

            {{-- 3. Primary Action Button (Black Pill with subtle shadow) --}}
            <div class="macos-action-btn-wrap">
                <a href="{{ route('gudang.po.approval_center', ['status' => 'PENDING_APPROVAL']) }}" 
                   class="macos-primary-btn" 
                   data-tooltip="Tinjau Dokumen Urgent">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="sidebar-hide-on-collapse">Tinjau PO Urgent</span>
                    @if($pendingCount > 0)
                        <span class="macos-circle-badge red sidebar-hide-on-collapse" style="margin-left: 6px;">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>

            {{-- Scrollable Nav Container --}}
            <div class="macos-nav-scroll-area">
                
                {{-- SECTION 1: PUSAT PERSETUJUAN (APPROVAL HUB) --}}
                <div class="macos-section-header sidebar-hide-on-collapse">
                    <span>Pusat Persetujuan</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <ul class="macos-nav-group">
                    {{-- 1. Purchase Order (PO Beli) --}}
                    <li>
                        <a href="{{ route('gudang.po.approval_center') }}" 
                           data-nav="po"
                           class="macos-nav-item {{ (request()->routeIs('gudang.po.approval_center') || request()->routeIs('gudang.po.review')) ? 'active' : '' }}"
                           data-tooltip="Purchase Order (PO)">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="sidebar-hide-on-collapse">Purchase Order (PO)</span>
                            @if($pendingCount > 0)
                                <span class="macos-circle-badge red">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- 2. Pengeluaran Kas & Hutang --}}
                    <li>
                        <a href="{{ route('keuangan.hutang.index') }}" 
                           data-nav="hutang"
                           class="macos-nav-item {{ request()->routeIs('keuangan.hutang.*') && request('status_pembayaran') !== 'JATUH_TEMPO' ? 'active' : '' }}"
                           data-tooltip="Pengeluaran & Hutang">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="sidebar-hide-on-collapse">Pengeluaran &amp; Hutang</span>
                            @if($jatuhTempoCount > 0)
                                <span class="macos-circle-badge red">{{ $jatuhTempoCount }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- 3. Sales Order (SO Jual) --}}
                    <li>
                        <a href="{{ route('penjualan.so.index') }}" 
                           data-nav="so"
                           class="macos-nav-item {{ request()->routeIs('penjualan.so.*') ? 'active' : '' }}"
                           data-tooltip="Sales Order (SO)">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="sidebar-hide-on-collapse">Sales Order (SO Jual)</span>
                        </a>
                    </li>

                    {{-- 4. Penyesuaian Stok (Opname) --}}
                    <li>
                        <a href="{{ route('gudang.stok.index') }}" 
                           data-nav="stok"
                           class="macos-nav-item {{ (request()->routeIs('gudang.stok.*') && request('status') !== 'menipis') ? 'active' : '' }}"
                           data-tooltip="Penyesuaian Stok">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span class="sidebar-hide-on-collapse">Penyesuaian Stok</span>
                        </a>
                    </li>
                </ul>

                {{-- SECTION 2: MONITORING & AUDIT --}}
                <div class="macos-section-header sidebar-hide-on-collapse">
                    <span>Monitoring &amp; Audit</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <ul class="macos-nav-group">
                    <li>
                        <a href="{{ route('gudang.stok.index', ['status' => 'menipis']) }}" 
                           data-nav="stok_menipis"
                           class="macos-nav-item {{ (request()->routeIs('gudang.stok.*') && request('status') === 'menipis') ? 'active' : '' }}" 
                           data-tooltip="Stok Kritis Gudang">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="sidebar-hide-on-collapse">Stok Kritis Gudang</span>
                            @if($lowStockCount > 0)
                                <span class="macos-circle-badge amber">{{ $lowStockCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('keuangan.hutang.index', ['status_pembayaran' => 'JATUH_TEMPO']) }}" 
                           data-nav="hutang_jatuh_tempo"
                           class="macos-nav-item {{ (request()->routeIs('keuangan.hutang.*') && request('status_pembayaran') === 'JATUH_TEMPO') ? 'active' : '' }}" 
                           data-tooltip="Jatuh Tempo Hutang Dagang">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="sidebar-hide-on-collapse">Jatuh Tempo Hutang</span>
                            @if($jatuhTempoCount > 0)
                                <span class="macos-circle-badge red">{{ $jatuhTempoCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" 
                           data-nav="summary"
                           class="macos-nav-item" 
                           data-tooltip="Ringkasan Eksekutif (Segera Hadir)"
                           onclick="alert('Modul Ringkasan Eksekutif & BI Dashboard sedang dalam tahap integrasi metrik pabrik.')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span class="sidebar-hide-on-collapse">Ringkasan Eksekutif</span>
                            <span class="macos-circle-badge sidebar-hide-on-collapse" style="background: #e4e4e7; color: #71717a; font-size: 9px; padding: 2px 5px; border-radius: 4px; width: auto; height: auto;">Segera</span>
                        </a>
                    </li>
                </ul>

                {{-- SECTION 3: RECENT NOTES / PO TERBARU (With Relative Timestamps) --}}
                <div class="macos-section-header sidebar-hide-on-collapse">
                    <span>PO Terbaru</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <ul class="macos-nav-group sidebar-hide-on-collapse">
                    @forelse($recentPos as $rPo)
                        @php
                            $diffStr = $rPo->created_at ? $rPo->created_at->diffForHumans(null, true, true) : '1d';
                        @endphp
                        <li>
                            <a href="{{ route('gudang.po.review', $rPo->po_id) }}" 
                               class="macos-nav-item {{ (request()->routeIs('gudang.po.review') && request('id') == $rPo->po_id) ? 'active' : '' }}"
                               data-tooltip="{{ $rPo->po_no }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="macos-recent-title">{{ $rPo->po_no }}</span>
                                <span class="macos-recent-time">{{ $diffStr }}</span>
                            </a>
                        </li>
                    @empty
                        <li style="padding: 0.35rem 0.65rem; font-size: 0.75rem; color: #a1a1aa;">
                            Belum ada riwayat PO
                        </li>
                    @endforelse
                </ul>

            </div>

            {{-- 4. macOS Bottom Utility Bar (Sliders, Sync, Exit) --}}
            <div class="macos-bottom-bar">
                <div class="macos-bottom-left">
                    <button type="button" class="macos-bar-icon-btn" title="Refresh Sync Indeks" onclick="window.location.reload();">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                    <button type="button" class="macos-bar-icon-btn" title="Filter &amp; Pengaturan">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </button>
                </div>

                <div class="macos-bottom-right">
                    <a href="{{ route('gudang.po.index') }}" class="macos-bar-icon-btn" title="Kembali ke ERP Biasa">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </a>
                </div>
            </div>

        </aside>

        {{-- MAIN CANVAS (RIGHT) --}}
        <main class="exec-main-wrapper" id="execMainWrapper">
            
            {{-- macOS Safari-style Top Progress Bar --}}
            <div id="macosProgressBar" class="macos-top-progress-bar"></div>

            <div id="execContentArea">
                {{-- TOP NAV SEARCH BAR (MATCHING SCREENSHOT) --}}
                <div class="top-nav-search-bar">
                    <div class="search-box-wrap">
                        <svg width="16" height="16" fill="none" stroke="#71717a" viewBox="0 0 24 24" class="search-icon-pos"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" 
                               id="execPoSearch" 
                               class="search-input-macos" 
                               placeholder="Search file, supplier, or PO number...">
                    </div>

                    <div class="top-nav-right-actions">
                        <button type="button" class="btn-macos-bell" title="Notifikasi Persetujuan">
                            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @if($pendingCount > 0)
                                <span class="notif-bubble-dot"></span>
                            @endif
                        </button>

                        <a href="{{ route('gudang.po.create') }}" class="btn-top-invite-dark">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Buat PO Baru</span>
                        </a>
                    </div>
                </div>

                {{-- YIELD CONTENT --}}
                @yield('content')
            </div>

        </main>

    </div>

    <!-- Executive JavaScript -->
    <script src="{{ asset('js/approval/executive-dashboard.js') }}?v={{ filemtime(public_path('js/approval/executive-dashboard.js')) }}"></script>
    @stack('scripts')
</body>
</html>
