@extends('layouts.app')

@section('title', 'Bukti Kas Masuk (BKM) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_masuk/kas-masuk-index.css') }}?v={{ file_exists(public_path('css/keuangan/kas_masuk/kas-masuk-index.css')) ? filemtime(public_path('css/keuangan/kas_masuk/kas-masuk-index.css')) : time() }}">
@endpush

@section('content')
<div class="bkm-page-wrapper">

    {{-- 1. TOP HEADER SECTION (MENGIKUTI STANDAR PO) --}}
    <div class="bkm-top-header">
        <div>
            <div class="bkm-header-kicker">Keuangan &amp; Kasir</div>
            <h1 class="bkm-header-title">Bukti Kas Masuk (BKM)</h1>
            <p class="bkm-header-subtitle">
                Pencatatan penerimaan uang kas dan bank harian oleh kasir operasional pabrik.
            </p>
        </div>

        <div class="bkm-header-actions">
            <a href="{{ route('keuangan.kas_bank.index') }}" class="btn-pill-outline">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>Saldo Kas &amp; Bank</span>
            </a>

            @if (Auth::user()?->canCreateKasMasuk() ?? true)
                <a href="{{ route('keuangan.kas_masuk.create') }}" class="btn-pill-dark">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Catat Kas Masuk</span>
                </a>
            @endif
        </div>
    </div>

    {{-- ALERT NOTIFICATIONS --}}
    @if (session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.8125rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.8125rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- 2. 4 KARTU METRIK OPERASIONAL KASIR (STANDAR PO_DESIGN_SYSTEM.MD) --}}
    <div class="bkm-metrics-grid">
        {{-- Card 1: Penerimaan Hari Ini (Paling Penting untuk Kasir) --}}
        <div class="bkm-metric-card">
            <div class="bkm-metric-top">
                <div class="bkm-metric-header-left">
                    <span class="bkm-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <span class="bkm-metric-label">Penerimaan Hari Ini</span>
                </div>
                <span class="bkm-metric-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            </div>
            <div class="bkm-metric-value">Rp {{ number_format($kpi['total_hari_ini'] ?? 0, 0, ',', '.') }}</div>
            <div class="bkm-metric-footer">
                <span class="dot dot-emerald" aria-hidden="true"></span>
                <span>{{ $kpi['count_hari_ini'] ?? 0 }} transaksi kasir hari ini</span>
            </div>
        </div>

        {{-- Card 2: Total Kas Masuk Bulan Ini --}}
        <div class="bkm-metric-card">
            <div class="bkm-metric-top">
                <div class="bkm-metric-header-left">
                    <span class="bkm-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="bkm-metric-label">Kas Masuk Bulan Ini</span>
                </div>
                <span class="bkm-metric-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            </div>
            <div class="bkm-metric-value">Rp {{ number_format($kpi['total_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
            <div class="bkm-metric-footer">
                <span class="dot dot-slate" aria-hidden="true"></span>
                <span>{{ $kpi['count_bulan_ini'] ?? 0 }} transaksi bulan ini</span>
            </div>
        </div>

        {{-- Card 3: Menunggu Verifikasi Akuntan (DRAFT) --}}
        <div class="bkm-metric-card">
            <div class="bkm-metric-top">
                <div class="bkm-metric-header-left">
                    <span class="bkm-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="bkm-metric-label">Menunggu Verifikasi</span>
                </div>
                <span class="bkm-metric-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            </div>
            <div class="bkm-metric-value">{{ number_format($kpi['count_draft'] ?? 0) }} Dokumen</div>
            <div class="bkm-metric-footer">
                <span class="dot dot-amber" aria-hidden="true"></span>
                <span>Rp {{ number_format($kpi['total_draft'] ?? 0, 0, ',', '.') }} antrean draft akuntan</span>
            </div>
        </div>

        {{-- Card 4: Total Transaksi Terverifikasi --}}
        <div class="bkm-metric-card">
            <div class="bkm-metric-top">
                <div class="bkm-metric-header-left">
                    <span class="bkm-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="bkm-metric-label">Terverifikasi (POSTED)</span>
                </div>
                <span class="bkm-metric-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            </div>
            <div class="bkm-metric-value">{{ number_format($kpi['count_posted'] ?? 0) }} Transaksi</div>
            <div class="bkm-metric-footer">
                <span class="dot dot-emerald" aria-hidden="true"></span>
                <span>{{ $kpi['total_rekening_aktif'] ?? 0 }} rekening kas/bank aktif</span>
            </div>
        </div>
    </div>

    {{-- 3. MAIN CARD (PO STANDARD: CONTROL BAR + DATA TABLE + PAGINATION) --}}
    <div class="bkm-main-card">
        <div class="bkm-control-bar">
            {{-- Row 1: Segmented Status Tabs --}}
            <div class="bkm-tabs-row">
                <div class="bkm-status-pills" role="tablist" aria-label="Filter status transaksi BKM">
                    @php $activeStatus = $filters['status'] ?? 'ALL'; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'ALL', 'page' => 1]) }}"
                       class="bkm-pill-tab {{ $activeStatus === 'ALL' ? 'active' : '' }}">
                        <span>Semua</span>
                        <span class="badge-count">{{ $statusCounts['all'] ?? 0 }}</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'DRAFT', 'page' => 1]) }}"
                       class="bkm-pill-tab {{ $activeStatus === 'DRAFT' ? 'active' : '' }}">
                        <span>Menunggu Verifikasi</span>
                        <span class="badge-count">{{ $statusCounts['draft'] ?? 0 }}</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'POSTED', 'page' => 1]) }}"
                       class="bkm-pill-tab {{ $activeStatus === 'POSTED' ? 'active' : '' }}">
                        <span>Terverifikasi (POSTED)</span>
                        <span class="badge-count">{{ $statusCounts['posted'] ?? 0 }}</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'VOID', 'page' => 1]) }}"
                       class="bkm-pill-tab {{ $activeStatus === 'VOID' ? 'active' : '' }}">
                        <span>Dibatalkan (VOID)</span>
                        <span class="badge-count">{{ $statusCounts['void'] ?? 0 }}</span>
                    </a>
                </div>
            </div>

            {{-- Row 2: Filter Form --}}
            <form method="GET" action="{{ route('keuangan.kas_masuk.index') }}" class="bkm-filter-form">
                <input type="hidden" name="status" value="{{ $activeStatus }}">
                <input type="hidden" name="per_page" value="{{ $filters['per_page'] ?? 15 }}">

                <div class="bkm-search-wrap">
                    <span class="bkm-search-icon" aria-hidden="true">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           class="bkm-search-input" 
                           placeholder="Cari No BKM, Penyetor, Uraian...">
                </div>

                <select name="kategori" class="bkm-select-input" aria-label="Filter kategori">
                    <option value="">Semua Kategori</option>
                    <option value="PENJUALAN_LIMBAH" {{ ($filters['kategori'] ?? '') === 'PENJUALAN_LIMBAH' ? 'selected' : '' }}>Penjualan Limbah</option>
                    <option value="PENJUALAN_TUNAI" {{ ($filters['kategori'] ?? '') === 'PENJUALAN_TUNAI' ? 'selected' : '' }}>Penjualan Tunai</option>
                    <option value="SETORAN_MODAL" {{ ($filters['kategori'] ?? '') === 'SETORAN_MODAL' ? 'selected' : '' }}>Setoran Modal</option>
                    <option value="PENDAPATAN_LAIN" {{ ($filters['kategori'] ?? '') === 'PENDAPATAN_LAIN' ? 'selected' : '' }}>Pendapatan Lain-lain</option>
                </select>

                <select name="kas_bank_id" class="bkm-select-input" aria-label="Filter rekening kas bank">
                    <option value="">Semua Rekening Kas/Bank</option>
                    @foreach ($kasBanks as $kb)
                        <option value="{{ $kb->kas_bank_id }}" {{ ($filters['kas_bank_id'] ?? '') == $kb->kas_bank_id ? 'selected' : '' }}>
                            {{ $kb->nama_akun }} ({{ $kb->tipe_akun }})
                        </option>
                    @endforeach
                </select>

                <select name="metode_terima" class="bkm-select-input" aria-label="Filter metode bayar">
                    <option value="">Semua Metode</option>
                    <option value="TRANSFER" {{ ($filters['metode_terima'] ?? '') === 'TRANSFER' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="CASH" {{ ($filters['metode_terima'] ?? '') === 'CASH' ? 'selected' : '' }}>Tunai / Cash</option>
                    <option value="GIRO_CEK" {{ ($filters['metode_terima'] ?? '') === 'GIRO_CEK' ? 'selected' : '' }}>Cek / Giro</option>
                    <option value="EDC_QRIS" {{ ($filters['metode_terima'] ?? '') === 'EDC_QRIS' ? 'selected' : '' }}>QRIS / EDC</option>
                </select>

                <input type="date" name="tgl_mulai" value="{{ $filters['tgl_mulai'] ?? '' }}" class="bkm-date-input" aria-label="Tanggal mulai">
                <input type="date" name="tgl_selesai" value="{{ $filters['tgl_selesai'] ?? '' }}" class="bkm-date-input" aria-label="Tanggal selesai">

                <button type="submit" class="btn-bkm-filter">Filter</button>

                @if (!empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'ALL') || !empty($filters['kategori']) || !empty($filters['kas_bank_id']) || !empty($filters['metode_terima']) || !empty($filters['tgl_mulai']) || !empty($filters['tgl_selesai']))
                    <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn-bkm-reset" title="Reset filter">Reset</a>
                @endif

                <div class="bkm-total-badge">
                    Total: <strong>{{ $bkmList->total() }}</strong> Data
                </div>
            </form>
        </div>

        {{-- 4. ENTERPRISE DATA TABLE --}}
        <div class="bkm-table-wrapper">
            <table class="bkm-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 160px;">No. BKM &amp; Tanggal</th>
                        <th style="min-width: 170px;">Penyetor / Sumber</th>
                        <th style="min-width: 160px;">Rekening Tujuan</th>
                        <th style="min-width: 140px;">Kategori</th>
                        <th style="min-width: 130px;">Metode &amp; Ref</th>
                        <th style="min-width: 150px; text-align: right;">Total Diterima</th>
                        <th style="min-width: 150px; text-align: center;">Status</th>
                        <th style="width: 90px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bkmList as $index => $bkm)
                        @php
                            $menuId = 'actionMenuBkm_' . $bkm->bkm_id;
                            $formattedTotal = 'Rp ' . number_format($bkm->total_terima, 0, ',', '.');
                            $tglFmt = $bkm->tgl_terima ? \Carbon\Carbon::parse($bkm->tgl_terima)->format('d/m/Y') : '-';
                            $penyetorTxt = $bkm->penyetor_nama ?: '-';
                            $kasBankNm = $bkm->kasBank->nama_akun ?? 'Kas/Bank';
                            $isDraft = ($bkm->status_cd === 'DRAFT');
                            $isPosted = ($bkm->status_cd === 'POSTED');
                            $isVoid = ($bkm->status_cd === 'VOID');
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: 600;">
                                {{ $bkmList->firstItem() + $index }}
                            </td>

                            {{-- No BKM & Tanggal --}}
                            <td>
                                <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}" class="bkm-doc-code">
                                    {{ $bkm->bkm_no }}
                                </a>
                                <div class="bkm-meta-date">
                                    {{ $tglFmt }}
                                </div>
                            </td>

                            {{-- Penyetor / Sumber --}}
                            <td>
                                <div class="bkm-entity-title">{{ $penyetorTxt }}</div>
                                @if ($bkm->customer)
                                    <div class="bkm-entity-sub">{{ $bkm->customer->customer_nm }}</div>
                                @endif
                            </td>

                            {{-- Rekening Tujuan --}}
                            <td>
                                <div class="bkm-entity-title">{{ $kasBankNm }}</div>
                                <div class="bkm-entity-sub">
                                    {{ $bkm->kasBank && $bkm->kasBank->nomor_rekening ? 'Rek. ' . $bkm->kasBank->nomor_rekening : 'Kas Fisik Kantor' }}
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td>
                                @if ($bkm->kategori_penerimaan === 'PENJUALAN_LIMBAH')
                                    <span class="bkm-category-badge bkm-cat-limbah">Limbah Pabrik</span>
                                @elseif ($bkm->kategori_penerimaan === 'SETORAN_MODAL')
                                    <span class="bkm-category-badge bkm-cat-modal">Modal Owner</span>
                                @elseif ($bkm->kategori_penerimaan === 'PENJUALAN_TUNAI')
                                    <span class="bkm-category-badge bkm-cat-tunai">Penjualan Tunai</span>
                                @else
                                    <span class="bkm-category-badge bkm-cat-lain">Pendapatan Lain</span>
                                @endif
                            </td>

                            {{-- Metode & Ref --}}
                            <td>
                                <span class="bkm-method-badge">{{ $bkm->metode_terima }}</span>
                                @if ($bkm->no_referensi_bank)
                                    <div class="bkm-entity-sub" style="font-family: var(--erp-font-mono);">
                                        {{ $bkm->no_referensi_bank }}
                                    </div>
                                @endif
                            </td>

                            {{-- Total Diterima --}}
                            <td class="bkm-amount-cell">
                                {{ $formattedTotal }}
                            </td>

                            {{-- Status Dokumen (Standar PO & Kasir Operasional) --}}
                            <td style="text-align: center;">
                                @if ($isDraft)
                                    <span class="erp-status-indicator indicator-amber">
                                        <span class="dot dot-amber" aria-hidden="true"></span>
                                        <span>Menunggu Verifikasi Akuntan</span>
                                    </span>
                                @elseif ($isPosted)
                                    <span class="erp-status-indicator indicator-emerald">
                                        <span class="dot dot-emerald" aria-hidden="true"></span>
                                        <span>Terverifikasi (POSTED)</span>
                                    </span>
                                @else
                                    <span class="erp-status-indicator indicator-rose">
                                        <span class="dot dot-rose" aria-hidden="true"></span>
                                        <span>Dibatalkan</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Kolom Aksi (Smart Action Dropdown - Khusus Kasir Operasional) --}}
                            <td style="text-align: center; position: relative;">
                                <button type="button" 
                                        class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div id="{{ $menuId }}" class="action-dropdown-menu" role="menu">
                                    {{-- 1. Cetak Kuitansi / Bukti Masuk Langsung --}}
                                    <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}?print=true" target="_blank" class="action-dropdown-item" role="menuitem">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        <span>Cetak Kuitansi / Bukti Masuk</span>
                                    </a>

                                    {{-- 2. Lihat Voucher BKM --}}
                                    <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}" class="action-dropdown-item" role="menuitem">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Lihat Voucher BKM</span>
                                    </a>

                                    {{-- 3. Batalkan / Void BKM (Hanya jika belum VOID) --}}
                                    @if (!$isVoid && (Auth::user()?->canVoidKasMasuk() ?? true))
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" 
                                                class="action-dropdown-item danger-item" 
                                                role="menuitem"
                                                onclick="openModalVoidBkm({{ $bkm->bkm_id }}, '{{ $bkm->bkm_no }}', '{{ $formattedTotal }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span>Batalkan / Void</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">Tidak Ada Data Kas Masuk</div>
                                    <p style="font-size: 0.8125rem; color: #64748b; margin: 0; max-width: 380px; line-height: 1.4;">
                                        Tidak ditemukan transaksi Bukti Kas Masuk yang sesuai dengan filter saat ini.
                                    </p>
                                    @if (Auth::user()?->canCreateKasMasuk() ?? true)
                                        <a href="{{ route('keuangan.kas_masuk.create') }}" class="btn-pill-dark" style="margin-top: 0.35rem;">
                                            + Catat Bukti Kas Masuk Baru
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 5. PAGINATION SECTION --}}
        @if ($bkmList->hasPages() || $bkmList->total() > 0)
            <div class="bkm-pagination-bar">
                <div class="bkm-pagination-info">
                    Menampilkan <strong>{{ $bkmList->firstItem() ?? 0 }}</strong> s.d. <strong>{{ $bkmList->lastItem() ?? 0 }}</strong> dari <strong>{{ $bkmList->total() }}</strong> transaksi
                </div>
                <div>
                    {{ $bkmList->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- MODAL DIALOGS (HANYA MODAL VOID UNTUK KASIR) --}}
@include('keuangan.kas_masuk.partials.modal-void-bkm')

@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_masuk/kas-masuk-index.js') }}?v={{ file_exists(public_path('js/keuangan/kas_masuk/kas-masuk-index.js')) ? filemtime(public_path('js/keuangan/kas_masuk/kas-masuk-index.js')) : time() }}"></script>
@endpush
