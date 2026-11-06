@extends('layouts.app')

@section('title', 'Catat Tagihan Hutang Pembelian (AP) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/hutang/hutang-create.css') }}?v={{ filemtime(public_path('css/keuangan/hutang/hutang-create.css')) }}">
@endpush

@php
    $totalUnbilledAll = array_sum($unbilledSupplierSummary ?? []);
    $suppliersWithGrn = $suppliers->filter(fn($s) => ($unbilledSupplierSummary[$s->supplier_id] ?? 0) > 0);
    $initSupp = (old('supplier_id', $selectedSupplierId)) ? $suppliers->firstWhere('supplier_id', old('supplier_id', $selectedSupplierId)) : null;
@endphp

@section('content')
{{-- 1. CHEVRON BREADCRUMB NAVIGATION & LIVE KPI WIDGET --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
    <nav class="erp-chevron-bar" aria-label="Breadcrumb">
        <a href="{{ route('keuangan.hutang.index') }}" class="erp-chevron-item">
            <span>Keuangan</span>
        </a>
        <span class="erp-chevron-sep">
            <svg width="8" height="20" viewBox="0 0 8 20" fill="none">
                <path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </span>
        <a href="{{ route('keuangan.hutang.index') }}" class="erp-chevron-item">
            <span>Hutang Usaha</span>
        </a>
        <span class="erp-chevron-sep">
            <svg width="8" height="20" viewBox="0 0 8 20" fill="none">
                <path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </span>
        <span class="erp-chevron-item active">
            <span>+ Catat Tagihan Pembelian (Invoice Masuk)</span>
        </span>
        <span class="erp-chevron-end">
            <svg width="10" height="32" viewBox="0 0 10 32" fill="none">
                <path d="M1 1L8 16L1 31" stroke="#dce4ec" stroke-width="1.5" />
            </svg>
        </span>
    </nav>

    {{-- Live KPI Widget Ramping di Siku Kanan Atas (Identik PO Pembelian) --}}
    <div class="po-live-kpi-widget">
        <div>
            <div class="po-live-kpi-title">ESTIMASI GRAND TOTAL:</div>
            <div class="po-live-kpi-val" id="topLiveGrandTotal">Rp 0</div>
        </div>
        <div style="height: 28px; width: 1.5px; background: #e2e8f0;"></div>
        <div style="font-size: 0.775rem; line-height: 1.35; color: #475569;">
            <div>Item: <strong id="topLiveItemCount" style="color: #0f172a; font-family: monospace;">0 Item</strong></div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.7rem; color: #059669; font-weight: 700;">🟢 Mode Aktif</span>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('keuangan.hutang.store') }}" method="POST" id="formCreateHutang">
    @csrf

    {{-- 2. TOOLBAR CARD RAMPING: 4 KOLOM PRESISI & KONTROL PAJAK --}}
    <div class="po-toolbar-card">
        {{-- Mode Toolbar Wrap: Judul Dokumen (Kiri) & Skema Pajak PPN (Kanan) --}}
        <div class="po-mode-toolbar-wrap">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                    <span>🏢</span> <span>Dokumen Faktur &amp; Rekanan Supplier</span>
                </span>
                @if($totalUnbilledAll > 0)
                    <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.725rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        ⚡ {{ $totalUnbilledAll }} GRN Siap Tagih
                    </span>
                @endif
            </div>

            {{-- Skema Pajak PPN & Perlakuan Harga --}}
            <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.45rem; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.25rem 0.65rem;">
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; margin-bottom: 0; user-select: none;">
                        <input type="hidden" name="is_harga_termasuk_pajak" value="0">
                        <input type="checkbox" id="is_harga_termasuk_pajak" name="is_harga_termasuk_pajak" value="1" {{ old('is_harga_termasuk_pajak') ? 'checked' : '' }} onchange="onHeaderTaxInclusiveToggle(this)" style="width: 15px; height: 15px; accent-color: #059669; cursor: pointer;">
                        <span style="color: #0f172a; font-size: 0.775rem; font-weight: 700;">Harga Termasuk PPN</span>
                        <span class="badge" id="badgeTaxMode" style="background: #e2e8f0; color: #475569; font-size: 0.65rem; font-weight: 700; margin-left: 0.2rem; padding: 1px 5px; border-radius: 3px;">Net</span>
                    </label>
                </div>

                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <span style="font-size: 0.725rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Tarif Global:</span>
                    <select id="global_pajak_id" class="form-control" style="width: 145px; height: 32px; padding: 2px 6px; font-size: 0.75rem; font-weight: 700;" onchange="applyGlobalTaxToAllRows(this)">
                        <option value="NON_PPN" data-tarif="0" data-sifat="TAMBAH">Non-PPN (0%)</option>
                        @foreach ($pajakList as $pjk)
                            <option value="{{ $pjk->pajak_id }}" data-code="{{ $pjk->pajak_cd }}" data-tarif="{{ (float)$pjk->tarif_persen }}" data-sifat="{{ $pjk->sifat_pajak }}" {{ ($pjk->is_default || (float)$pjk->tarif_persen == 11) ? 'selected' : '' }}>
                                {{ $pjk->pajak_cd }} ({{ (float)$pjk->tarif_persen }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- GRID UNIFIED 4 KOLOM (SIMETRIS, PRESISI, IDENTIK PO WORKSTATION) --}}
        <div class="po-toolbar-grid">
            {{-- [C1] Mitra Supplier --}}
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                    <label class="po-field-label" style="margin-bottom: 0;">
                        Mitra Supplier <span style="color:#ef4444;">*</span>
                    </label>
                    <a href="javascript:void(0)" onclick="openSupplierModal()" style="font-size: 0.7rem; color: #0284c7; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Daftar Rekanan ({{ $suppliers->count() }})
                    </a>
                </div>

                <input type="hidden" name="supplier_id" id="selectedSupplierIdInput" value="{{ old('supplier_id', $selectedSupplierId) }}" required>

                {{-- Box Placeholder Belum Dipilih --}}
                <div id="supplierPlaceholderBox" class="supplier-placeholder-box" onclick="openSupplierModal()" style="display: {{ $initSupp ? 'none' : 'flex' }};">
                    <div style="display: flex; align-items: center; gap: 0.4rem; color: #64748b; font-size: 0.8rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span style="font-weight: 600;">Pilih Mitra Supplier Pemasok...</span>
                    </div>
                    <span style="font-size: 0.7rem; background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; font-weight: 700;">
                        Cari &rarr;
                    </span>
                </div>

                {{-- Card Supplier Aktif --}}
                <div id="supplierActiveCard" class="supplier-active-card" style="display: {{ $initSupp ? 'flex' : 'none' }};">
                    <div style="display: flex; align-items: center; gap: 0.4rem; min-width: 0; overflow: hidden;">
                        <span style="background: #22c55e; color: #fff; width: 17px; height: 17px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; flex-shrink: 0;">✓</span>
                        <div style="min-width: 0; display: flex; align-items: center; gap: 0.35rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <span id="activeSupplierCode" class="supplier-modal-code">{{ $initSupp?->supplier_cd ?? '-' }}</span>
                            <strong id="activeSupplierName" style="color: #14532d; font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis;">{{ $initSupp?->supplier_nm ?? '-' }}</strong>
                            <span id="activeSupplierBadge" class="badge-grn-count" style="display: {{ ($unbilledSupplierSummary[$initSupp?->supplier_id ?? 0] ?? 0) > 0 ? 'inline-block' : 'none' }};">
                                ⚡ {{ $unbilledSupplierSummary[$initSupp?->supplier_id ?? 0] ?? 0 }} GRN
                            </span>
                        </div>
                    </div>
                    <button type="button" onclick="openSupplierModal()" class="btn btn-sm" style="background: #ffffff; border: 1px solid #86efac; color: #15803d; font-size: 0.675rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 4px; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
                        Ganti
                    </button>

                    {{-- Data Tersembunyi untuk Interaksi JS --}}
                    <span id="activeSupplierContact" style="display: none;">{{ $initSupp?->kontak_no ?: '-' }}</span>
                    <span id="activeSupplierAddress" style="display: none;">{{ $initSupp?->alamat_txt ?: '-' }}</span>
                    <span id="activeSupplierTopBadge" style="display: none;">TOP: <span id="activeSupplierTopDays">{{ $initSupp?->top_hari ?? 14 }}</span></span>
                    <span id="topDefaultDaysText" style="display: none;">{{ $initSupp?->top_hari ?? 14 }} Hari</span>
                </div>
            </div>

            {{-- [C2] No. Faktur / Nota Supplier --}}
            <div>
                <label for="noFakturSupplierInput" class="po-field-label">
                    No. Faktur / Nota Supplier
                </label>
                <input type="text" 
                       name="no_faktur_supplier" 
                       id="noFakturSupplierInput"
                       value="{{ old('no_faktur_supplier') }}" 
                       class="po-field-input" 
                       placeholder="Contoh: INV-SUP-2026/099" 
                       style="font-family: monospace; font-weight: 700;">
            </div>

            {{-- [C3] Tanggal Tagihan --}}
            <div>
                <label for="tglTagihanInput" class="po-field-label">
                    Tanggal Tagihan <span style="color:#ef4444;">*</span>
                </label>
                <input type="date" 
                       id="tglTagihanInput"
                       name="tgl_tagihan" 
                       value="{{ old('tgl_tagihan', date('Y-m-d')) }}" 
                       class="po-field-input" 
                       required>
            </div>

            {{-- [C4] Tempo Bayar (TOP) & Jatuh Tempo --}}
            <div>
                <label class="po-field-label">
                    Tempo Bayar &amp; Jatuh Tempo <span style="color:#ef4444;">*</span>
                </label>
                <div style="display: flex; gap: 0.4rem; align-items: center;">
                    <div style="position: relative; width: 85px; flex-shrink: 0;">
                        <input type="number" 
                               id="topHariInput"
                               name="top_hari" 
                               value="{{ old('top_hari', $initSupp?->top_hari ?? 14) }}" 
                               min="0" max="365"
                               class="po-field-input" 
                               style="padding-right: 2rem !important; font-weight: 700; color: #0284c7; text-align: center;" 
                               required>
                        <span style="position: absolute; right: 6px; top: 9px; font-size: 0.68rem; color: #64748b; font-weight: 700; pointer-events: none;">hr</span>
                    </div>

                    <input type="date" 
                           id="tglJatuhTempoInput"
                           name="tgl_jatuh_tempo" 
                           value="{{ old('tgl_jatuh_tempo', date('Y-m-d', strtotime('+' . ($initSupp?->top_hari ?? 14) . ' days'))) }}" 
                           class="po-field-input" 
                           style="flex: 1; font-weight: 700; color: #b45309;" 
                           required>
                </div>
                <small id="jatuhTempoHelpText" style="color: #0369a1; font-size: 0.68rem; display: block; margin-top: 2px; font-weight: 600;">
                    ⏱️ Tgl Tagihan + <span id="helpTextTopHari">{{ $initSupp?->top_hari ?? 14 }}</span> Hari (TOP)
                </small>
            </div>
        </div>

        {{-- Quick Badges Supplier dengan GRN Siap Tagih (Ramping 1 Baris Horizontal) --}}
        @if($suppliersWithGrn->isNotEmpty())
            <div style="margin-top: 0.65rem; padding-top: 0.5rem; border-top: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 0.5rem; overflow-x: auto; padding-bottom: 2px;">
                <span style="font-size: 0.7rem; font-weight: 800; color: #475569; white-space: nowrap; text-transform: uppercase;">
                    ⚡ Siap Ditagih:
                </span>
                <div style="display: flex; gap: 0.35rem; flex-wrap: nowrap;" id="supplierQuickBadges">
                    @foreach($suppliersWithGrn as $sg)
                        @php
                            $cnt = $unbilledSupplierSummary[$sg->supplier_id] ?? 0;
                            $isActive = (old('supplier_id', $selectedSupplierId) == $sg->supplier_id);
                        @endphp
                        <button type="button" 
                                class="btn-supplier-pill {{ $isActive ? 'active' : '' }}"
                                onclick="handleSupplierCardSelect(this)"
                                data-supplier-id="{{ $sg->supplier_id }}"
                                data-supplier-nm="{{ $sg->supplier_nm }}"
                                data-supplier-cd="{{ $sg->supplier_cd ?? '' }}"
                                data-grn-count="{{ $cnt }}"
                                data-contact="{{ $sg->kontak_no ?? '-' }}"
                                data-address="{{ $sg->alamat_txt ?? '-' }}"
                                data-top="{{ (int)($sg->top_hari ?? 14) }}"
                                style="padding: 2px 8px; font-size: 0.725rem; white-space: nowrap;">
                            <span>{{ $sg->supplier_nm }}</span>
                            <span style="background: {{ $isActive ? 'rgba(255,255,255,0.25)' : '#dbeafe' }}; color: {{ $isActive ? '#ffffff' : '#1e40af' }}; padding: 0 5px; border-radius: 8px; font-size: 0.65rem; font-weight: 800;">
                                {{ $cnt }} GRN
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- 3. DRAWER / KARTU SELEKSI SURAT JALAN / GRN YANG BELUM DITAGIH --}}
    <div class="grn-selection-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.4rem;">
            <div>
                <strong style="font-size: 0.85rem; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                    <span>📦</span> <span>Pilih Penerimaan Barang (GRN) yang Ditagihkan</span>
                </strong>
                <span style="font-size: 0.725rem; color: #64748b;">
                    Centang Surat Jalan di bawah untuk memuat rincian item komoditas secara otomatis
                </span>
            </div>

            <div style="display: flex; gap: 0.35rem; align-items: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="selectAllGrn()" style="font-size: 0.725rem; font-weight: 700; padding: 2px 8px;">
                    ☑️ Pilih Semua
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllGrn()" style="font-size: 0.725rem; font-weight: 700; padding: 2px 8px;">
                    ⬜ Batal Pilih
                </button>
            </div>
        </div>

        {{-- Toolbar Pencarian & Filter GRN --}}
        <div class="grn-toolbar" id="grnToolbar" style="display: none;">
            <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 200px;">
                <input type="text" 
                       id="grnSearchInput" 
                       class="form-control" 
                       placeholder="🔍 Cari No. GRN, Surat Jalan, atau Komoditas..." 
                       style="height: 30px; font-size: 0.75rem; border-radius: 5px;"
                       oninput="filterGrnList()">
            </div>

            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;">Periode:</span>
                <button type="button" class="grn-date-filter-btn active" data-filter="all" onclick="filterGrnByDate('all', this)">Semua</button>
                <button type="button" class="grn-date-filter-btn" data-filter="7days" onclick="filterGrnByDate('7days', this)">7 Hari</button>
                <button type="button" class="grn-date-filter-btn" data-filter="month" onclick="filterGrnByDate('month', this)">Bulan Ini</button>
            </div>
        </div>

        {{-- Loading & Empty States --}}
        <div id="grnLoadingIndicator" style="display: none; text-align: center; padding: 1.5rem; color: #0284c7; font-weight: 700; font-size: 0.8rem;">
            ⏳ Mengambil data penerimaan barang...
        </div>
        
        <div id="grnEmptyState" style="text-align: center; padding: 1.75rem 1rem; color: #94a3b8; font-size: 0.8125rem; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 6px;">
            Silakan pilih mitra supplier di atas terlebih dahulu untuk menampilkan daftar GRN yang belum ditagihkan.
        </div>

        {{-- Tabel Kompak GRN Scrollable --}}
        <div id="grnTableWrapper" class="grn-table-wrapper" style="display: none;">
            <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.775rem;">
                <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 2; border-bottom: 1.5px solid #e2e8f0;">
                    <tr style="color: #475569; font-size: 0.7rem; text-transform: uppercase;">
                        <th style="padding: 0.5rem 0.6rem; width: 35px; text-align: center;">
                            <input type="checkbox" id="headerMasterCheck" onchange="toggleMasterCheck(this)" style="cursor: pointer; accent-color: #0284c7;">
                        </th>
                        <th style="padding: 0.5rem 0.5rem; width: 180px;">No. Dokumen (GRN / SJ)</th>
                        <th style="padding: 0.5rem 0.5rem; width: 110px;">Tanggal Masuk</th>
                        <th style="padding: 0.5rem 0.5rem;">Komoditas &amp; Fisik</th>
                        <th style="padding: 0.5rem 0.6rem; text-align: right; width: 150px;">Estimasi Nilai</th>
                    </tr>
                </thead>
                <tbody id="grnTbodyContainer">
                    {{-- Di-render via JS --}}
                </tbody>
            </table>
        </div>

        {{-- Status Bar Seleksi GRN --}}
        <div id="grnStatusBar" class="grn-status-bar" style="display: none;">
            <div>
                Menampilkan <strong id="grnShownCount">0</strong> penerimaan barang
            </div>
            <div>
                <strong id="grnSelectedCount" style="color: #0284c7;">0</strong> GRN dipilih &bull; Total Estimasi: <strong id="grnSelectedTotal" style="color: #16a34a;">Rp 0</strong>
            </div>
        </div>
    </div>

    {{-- 4. KARTU TABEL WORKSTATION FULL-WIDTH (IDENTIK PO PEMBELIAN) --}}
    <div class="po-workstation-table-card">
        <div class="po-workstation-table-header">
            <strong style="color: #0f172a; font-size: 0.85rem; display: flex; align-items: center; gap: 0.35rem;">
                <span>📝</span> <span>Rincian Komoditas &amp; Harga Satuan Tagihan</span>
            </strong>
            <span style="font-size: 0.725rem; color: #64748b;">
                💡 Kuantitas, harga standar, diskon &amp; perlakuan pajak otomatis diambil dari PO pengadaan
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table class="po-workstation-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="padding: 0.6rem 0.75rem; text-align: left;">Deskripsi / Komoditas</th>
                        <th style="padding: 0.6rem 0.5rem; text-align: right; width: 95px;">Kuantitas</th>
                        <th style="padding: 0.6rem 0.5rem; text-align: center; width: 60px;">Satuan</th>
                        <th style="padding: 0.6rem 0.5rem; text-align: right; width: 120px;">Harga Satuan (Rp)</th>
                        <th style="padding: 0.6rem 0.5rem; text-align: right; width: 150px;">Diskon (% / Rp)</th>
                        <th style="padding: 0.6rem 0.5rem; text-align: right; width: 110px;">Potongan Mutu (Rp)</th>
                        <th style="padding: 0.6rem 0.5rem; text-align: left; width: 165px;">
                            <span>Perlakuan Pajak</span>
                            <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 500; display: block; text-transform: none;">PPN / PPh 22</span>
                        </th>
                        <th style="padding: 0.6rem 0.75rem; text-align: right; width: 130px;">Netto (Rp)</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    <tr id="emptyItemsRow">
                        <td colspan="8" style="padding: 2.25rem 1rem; text-align: center; color: #94a3b8; font-size: 0.825rem;">
                            Centang salah satu atau beberapa Surat Jalan / GRN di atas untuk memuat rincian tagihan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- 5. BOTTOM SECTION: PENYESUAIAN OPERASIONAL (KIRI) & FINANCIAL STACK (KANAN) --}}
    <div class="po-bottom-grid">
        {{-- Sisi Kiri: Ongkos Kirim / Upah Bongkar Muat & Catatan Tagihan --}}
        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
            {{-- KARTU ONGKOS KIRIM / BONGKAR MUAT (TONASE X TARIF) --}}
            <div class="po-notes-card" style="background: #faf5ff; border-color: #d8b4fe;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <strong style="font-size: 0.825rem; color: #581c87; display: flex; align-items: center; gap: 0.35rem;">
                        <span>🚚</span> <span>Ongkos Kirim / Upah Bongkar Muat</span>
                    </strong>
                    <span style="font-size: 0.68rem; background: #f3e8ff; color: #7e22ce; padding: 1px 6px; border-radius: 4px; font-weight: 800; border: 1px solid #d8b4fe;">
                        Akun 6112 Beban Angkut
                    </span>
                </div>

                {{-- Radio Mode Pilihan --}}
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.65rem;">
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 0.725rem; font-weight: 700; color: #581c87; cursor: pointer; background: #ffffff; padding: 3px 8px; border-radius: 4px; border: 1px solid #d8b4fe;">
                        <input type="radio" name="ongkir_mode" id="ongkirModeTonase" value="tonase" checked onchange="toggleOngkirMode('tonase')">
                        <span>Hitung Tonase (<span id="spanSatuanTonase">KG</span>) &times; Tarif</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 0.725rem; font-weight: 700; color: #581c87; cursor: pointer; background: #ffffff; padding: 3px 8px; border-radius: 4px; border: 1px solid #d8b4fe;">
                        <input type="radio" name="ongkir_mode" id="ongkirModeManual" value="manual" onchange="toggleOngkirMode('manual')">
                        <span>Nominal Tetap / Flat</span>
                    </label>
                </div>

                {{-- Mode Tonase x Tarif --}}
                <div id="ongkirTonaseBox" style="display: block;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; margin-bottom: 0.5rem;">
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #6b21a8; margin-bottom: 0.2rem;">
                                Kuantitas Netto (<span id="badgeSatuanTonase">KG</span>)
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   name="tonase_kg" 
                                   id="tonaseKgInput" 
                                   value="{{ old('tonase_kg', 0) }}" 
                                   class="form-control" 
                                   style="font-size: 0.8rem; height: 32px; font-weight: 700; color: #581c87; text-align: right;" 
                                   placeholder="0"
                                   oninput="calculateOngkirFromTonase()">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #6b21a8; margin-bottom: 0.2rem;">
                                Tarif Upah / <span id="spanSatuanTarif">KG</span> (Rp)
                            </label>
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="ongkir_tarif_per_kg" 
                                   id="ongkirTarifPerKgInput" 
                                   value="{{ old('ongkir_tarif_per_kg', 0) }}" 
                                   class="form-control" 
                                   style="font-size: 0.8rem; height: 32px; font-weight: 700; color: #581c87; text-align: right;" 
                                   placeholder="150"
                                   oninput="calculateOngkirFromTonase()">
                            <span id="badgeSatuanTarif" style="display: none;">/ KG</span>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px dashed #d8b4fe; border-radius: 5px; padding: 0.35rem 0.65rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; margin-bottom: 0.5rem;">
                        <span style="color: #6b21a8; font-weight: 600;">Kalkulasi:</span>
                        <span id="ongkirFormulaText" style="font-weight: 800; color: #581c87;">
                            0 KG &times; Rp 0 = Rp 0
                        </span>
                    </div>
                </div>

                {{-- Input Final Biaya Ongkos Angkut --}}
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                    <label style="font-size: 0.725rem; font-weight: 700; color: #6b21a8; margin: 0;">
                        Total Ongkos Angkut (Rp):
                    </label>
                    <input type="number" 
                           step="any" 
                           min="0" 
                           name="biaya_ongkir" 
                           id="biayaOngkirInput" 
                           value="{{ old('biaya_ongkir', 0) }}" 
                           class="form-control" 
                           style="width: 150px; font-size: 0.85rem; height: 32px; text-align: right; font-weight: 800; color: #6b21a8; background: #ffffff;" 
                           placeholder="0">
                </div>
            </div>

            {{-- KARTU INTEGRASI RETUR PEMBELIAN (CREDIT NOTE OTOMATIS DARI GUDANG) --}}
            <div id="cardReturCreditNote" class="po-notes-card" style="display: none; background: #fffdf5; border: 1.5px solid #fde68a;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <strong style="font-size: 0.825rem; color: #92400e; display: flex; align-items: center; gap: 0.35rem;">
                        <span>🏷️</span> <span>Kompensasi Retur Pembelian (Credit Note Gudang)</span>
                    </strong>
                    <span id="badgeReturCount" class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.68rem; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                        0 Dokumen Tersedia
                    </span>
                </div>
                <div style="font-size: 0.725rem; color: #78350f; margin-bottom: 0.5rem; line-height: 1.4;">
                    Ditemukan pengembalian barang rusak yang disetujui untuk <strong>Potong Tagihan</strong>. Centang untuk memotongkan saldo nota kredit:
                </div>
                <div id="returItemsList" style="display: flex; flex-direction: column; gap: 0.45rem;">
                    {{-- Di-render via JS saat supplier dipilih --}}
                </div>
            </div>

            {{-- KARTU CATATAN & INPUT MANUAL RETUR (OPSIONAL / TERSEMBUNYI) --}}
            <div class="po-notes-card">
                <div style="margin-bottom: 0.5rem;">
                    <label for="catatan" class="po-field-label">
                        Catatan &amp; Keterangan Faktur
                    </label>
                    <textarea name="catatan" 
                              id="catatan" 
                              rows="2" 
                              class="form-control" 
                              placeholder="Catatan termin pembayaran, transfer bank supplier, dsb..." 
                              style="font-size: 0.8rem; border-radius: 5px;">{{ old('catatan') }}</textarea>
                </div>

                {{-- Link Toggle & Box Input Manual Retur (Disembunyikan secara Default) --}}
                <div style="display: flex; justify-content: flex-end; padding-top: 0.35rem; border-top: 1px dashed #e2e8f0;">
                    <button type="button" onclick="toggleManualReturBox()" style="background: transparent; border: none; font-size: 0.7rem; color: #64748b; cursor: pointer; text-decoration: underline;">
                        + Input Potongan Retur Manual Lainnya (Opsional)
                    </button>
                </div>
                <div id="boxManualRetur" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: #fff1f2; border: 1px dashed #fecdd3; border-radius: 6px; justify-content: space-between; align-items: center; gap: 0.5rem;">
                    <div>
                        <span style="font-size: 0.725rem; color: #991b1b; font-weight: 700; display: block;">Nominal Potongan Manual:</span>
                        <small style="font-size: 0.65rem; color: #b91c1c;">Hanya jika ada potongan khusus di luar dokumen retur</small>
                    </div>
                    <input type="number" 
                           step="any" 
                           min="0" 
                           name="total_potongan_retur" 
                           id="potonganReturInput" 
                           value="{{ old('total_potongan_retur', 0) }}" 
                           class="form-control" 
                           style="width: 140px; font-size: 0.825rem; height: 32px; text-align: right; font-weight: 700; color: #dc2626;" 
                           placeholder="0"
                           oninput="recalculateTotals()">
                </div>
            </div>

            {{-- Info Box Jurnal Akuntansi --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.85rem; font-size: 0.725rem; color: #64748b; line-height: 1.4;">
                <strong style="color: #334155;">💡 Standar Akuntansi:</strong> Tagihan pembelian dicatat pada buku besar (Debet: <code>1300 Persediaan / Pembelian</code>, Kredit: <code>2100 Hutang Usaha</code>). DP memotong sisa hutang secara otomatis.
            </div>
        </div>

        {{-- Sisi Kanan: Financial Stack Ringkasan Kalkulasi & Tombol Eksekusi --}}
        <div class="po-financial-card">
            <div style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.65rem;">
                Ringkasan Finansial Tagihan
            </div>

            {{-- 1. Total Bruto --}}
            <div class="po-financial-row">
                <span style="color: #64748b; font-weight: 600;">Total Bruto:</span>
                <strong id="summaryTotalBruto" style="font-family: monospace; font-size: 0.95rem; color: #1e293b;">Rp 0</strong>
            </div>

            {{-- 2. Total Diskon --}}
            <div class="po-financial-row">
                <span style="color: #64748b; font-weight: 600;">Total Diskon:</span>
                <strong id="summaryTotalDiskon" style="font-family: monospace; font-size: 0.95rem; color: #16a34a;">Rp 0</strong>
            </div>

            {{-- 3. Total Potongan Mutu --}}
            <div class="po-financial-row" id="rowSummaryPotonganItem" style="display: none; color: #b45309;">
                <span style="font-weight: 600;">Total Potongan Mutu:</span>
                <strong id="summaryTotalPotonganItem" style="font-family: monospace; font-size: 0.95rem; color: #b45309;">- Rp 0</strong>
            </div>

            {{-- 4. DPP (Dasar Pengenaan Pajak) --}}
            <div class="po-financial-row" id="rowSummaryDpp" style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.45rem; margin-top: 0.2rem;">
                <span style="color: #475569; font-weight: 700;">DPP (Netto):</span>
                <strong id="summaryTotalDpp" style="font-family: monospace; color: #1e293b; font-size: 0.95rem;">Rp 0</strong>
            </div>

            {{-- 5. Pajak (PPN / PPh 22) --}}
            <div class="po-financial-row">
                <span style="color: #64748b; font-weight: 600;">Pajak (PPN / PPh):</span>
                <strong id="summaryTotalPpn" style="font-family: monospace; font-size: 0.95rem; color: #0284c7;">Rp 0</strong>
            </div>

            {{-- 6. Ongkos Angkut --}}
            <div class="po-financial-row" id="rowSummaryOngkir" style="display: none; color: #7e22ce;">
                <span style="font-weight: 600;">+ Ongkos Angkut (6112):</span>
                <strong id="summaryBiayaOngkir" style="font-family: monospace; font-size: 0.95rem; color: #7e22ce;">Rp 0</strong>
            </div>

            {{-- 6b. Potongan Retur (Credit Note) --}}
            <div class="po-financial-row" id="rowSummaryRetur" style="display: none; color: #dc2626;">
                <span style="font-weight: 600;">- Potongan Retur (Credit Note):</span>
                <strong id="summaryPotonganRetur" style="font-family: monospace; font-size: 0.95rem; color: #dc2626;">- Rp 0</strong>
            </div>

            {{-- 7. Grand Total (Faktur Resmi) --}}
            <div class="po-grand-total-box">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: #047857; text-transform: uppercase;">
                        TOTAL TAGIHAN RESMI (FAKTUR)
                    </div>
                    <div style="font-size: 0.65rem; color: #15803d;">
                        Sudah termasuk kalkulasi diskon &amp; pajak
                    </div>
                </div>
                <div class="po-grand-total-val" id="summaryGrandTotal">Rp 0</div>
            </div>

            {{-- 8. Pengurangan Uang Muka (DP) --}}
            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 6px; padding: 0.55rem 0.75rem; margin-top: 0.65rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <span style="font-size: 0.725rem; font-weight: 700; color: #14532d;">
                        Uang Muka / DP yang Sudah Dibayar:
                    </span>
                    <span style="font-size: 0.65rem; background: #dcfce7; color: #15803d; padding: 1px 5px; border-radius: 3px; font-weight: 800;">
                        Potong Hutang
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <input type="number" 
                           step="any" 
                           min="0" 
                           name="uang_muka_nominal" 
                           id="uangMukaNominalInput" 
                           value="{{ old('uang_muka_nominal', 0) }}" 
                           class="form-control" 
                           style="font-size: 0.85rem; height: 32px; text-align: right; font-weight: 800; color: #15803d; background: #ffffff;" 
                           placeholder="0"
                           oninput="recalculateTotals()">
                </div>
                <div class="po-financial-row" id="rowSummaryUangMuka" style="display: none; color: #15803d; margin-top: 0.35rem; padding-top: 0.35rem; border-top: 1px dashed #bbf7d0;">
                    <span>- DP Terbayar:</span>
                    <strong id="summaryUangMukaNominal" style="font-family: monospace;">Rp 0</strong>
                </div>
            </div>

            {{-- 9. Sisa Hutang Wajib Bayar --}}
            <div id="boxSummarySisaHutang" style="margin-top: 0.65rem; padding: 0.65rem 0.85rem; background: #fef2f2; border-radius: 6px; border: 1.5px solid #fecaca; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: #991b1b; text-transform: uppercase;">
                        SISA HUTANG WAJIB BAYAR
                    </div>
                    <div style="font-size: 0.65rem; color: #b91c1c;">
                        Setelah dikurangi DP terbayar
                    </div>
                </div>
                <strong id="summarySisaHutang" style="font-size: 1.25rem; font-weight: 900; color: #dc2626; font-family: monospace;">
                    Rp 0
                </strong>
            </div>

            {{-- Action Buttons --}}
            <div class="po-action-bar">
                <a href="{{ route('keuangan.hutang.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8rem; padding: 0.45rem 0.85rem;">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary" style="font-weight: 800; font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                    💾 Terbitkan Tagihan Hutang
                </button>
            </div>
        </div>
    </div>
</form>

{{-- MODAL POP-UP PEMILIH MITRA SUPPLIER --}}
@include('keuangan.hutang.partials.modal-supplier-picker')

@endsection

@push('scripts')
<script>
    window.appConfig = {
        baseUrl: '{{ url('') }}',
        initialTopHari: {{ (int)($initSupp?->top_hari ?? 14) }},
        pajakList: @json($pajakList),
        initialReturs: @json($uncreditedReturs ?? [])
    };
</script>
<script src="{{ asset('js/keuangan/hutang/hutang-create.js') }}?v={{ filemtime(public_path('js/keuangan/hutang/hutang-create.js')) }}"></script>
@endpush
