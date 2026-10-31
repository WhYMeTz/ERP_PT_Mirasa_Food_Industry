@extends('layouts.app')

@section('title', 'Buat Purchase Order Baru - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-form.css') }}?v={{ filemtime(public_path('css/gudang/po/po-form.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('css/gudang/po/po-workstation.css') }}?v={{ filemtime(public_path('css/gudang/po/po-workstation.css')) }}">
@endpush

@php
    // Siapkan daftar option supplier untuk digunakan di baris tabel (Mode Multi-Supplier / Auto-Split)
    $supplierOptionsHtml = '<option value="">-- Pilih Supplier Mitra --</option>';
    if (isset($jenisSupplierList) && $jenisSupplierList->count() > 0) {
        foreach ($jenisSupplierList as $js) {
            $subs = $supplierList->filter(fn($s) => $s->jenis_supplier_id == $js->jenis_supplier_id);
            if ($subs->count() > 0) {
                $icon = match ($js->jenis_supplier_cd) {
                    'RAW', 'BB' => '🌾 ',
                    'BUMBU' => '🧂 ',
                    'KEMASAN', 'PACK' => '📦 ',
                    'BP' => '🏭 ',
                    default => ''
                };
                $supplierOptionsHtml .= '<optgroup label="' . htmlspecialchars($icon . $js->jenis_supplier_nm) . '">';
                foreach ($subs as $s) {
                    $supplierOptionsHtml .= '<option value="' . $s->supplier_id . '" data-category="' . ($js->jenis_supplier_cd ?? 'OTHER') . '">' . htmlspecialchars($s->supplier_nm . ' (' . $s->supplier_cd . ')') . '</option>';
                }
                $supplierOptionsHtml .= '</optgroup>';
            }
        }
    }
    $others = $supplierList->whereNull('jenis_supplier_id');
    if ($others->count() > 0) {
        $supplierOptionsHtml .= '<optgroup label="Lainnya">';
        foreach ($others as $s) {
            $supplierOptionsHtml .= '<option value="' . $s->supplier_id . '" data-category="OTHER">' . htmlspecialchars($s->supplier_nm . ' (' . $s->supplier_cd . ')') . '</option>';
        }
        $supplierOptionsHtml .= '</optgroup>';
    }
@endphp

@section('content')
    {{-- 1. CHEVRON BREADCRUMB NAVIGATION (GAYA AKUNTANSI ENTERPRISE) --}}
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.5rem;">
        <nav class="erp-chevron-bar" aria-label="Breadcrumb">
            <a href="{{ route('gudang.po.index') }}" class="erp-chevron-item">
                <span>Pembelian</span>
            </a>
            <span class="erp-chevron-sep">
                <svg width="8" height="20" viewBox="0 0 8 20" fill="none">
                    <path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </span>
            <a href="{{ route('gudang.po.index') }}" class="erp-chevron-item">
                <span>Purchase Order</span>
            </a>
            <span class="erp-chevron-sep">
                <svg width="8" height="20" viewBox="0 0 8 20" fill="none">
                    <path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </span>
            <span class="erp-chevron-item active">
                <span>Add Purchase Order</span>
            </span>
            <span class="erp-chevron-end">
                <svg width="10" height="32" viewBox="0 0 10 32" fill="none">
                    <path d="M1 1L8 16L1 31" stroke="#dce4ec" stroke-width="1.5" />
                </svg>
            </span>
        </nav>

        {{-- Live KPI Widget Ramping di Siku Kanan Atas --}}
        <div class="po-live-kpi-widget">
            <div>
                <div class="po-live-kpi-title">ESTIMASI GRAND TOTAL:</div>
                <div class="po-live-kpi-val" id="topLiveGrandTotal">Rp 0</div>
            </div>
            <div style="height: 28px; width: 1.5px; background: #e2e8f0;"></div>
            <div style="font-size: 0.775rem; line-height: 1.35; color: #475569;">
                <div>Baris: <strong id="topLiveItemCount" style="color: #0f172a; font-family: monospace;">1 Item</strong>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.7rem; color: #059669; font-weight: 700;">🟢 Mode Aktif</span>
                    <span id="draftSaveIndicator" style="display: none; font-size: 0.675rem; color: #0284c7; font-weight: 700; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1px 6px; border-radius: 10px;">💾 Draft Tersimpan</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('gudang.po.store') }}" method="POST" id="formPo">
        @csrf

        {{-- 2. TOOLBAR CARD RAMPING: SEMUA FIELD LANGSUNG TAMPIL 2 BARIS (NO ACCORDION, ZERO EMPTY SPACE) --}}
        <div class="po-toolbar-card">
            {{-- Mode Switcher & Tax Setting di Bar Atas (Rapi, Seimbang & Tidak Memakan Grid) --}}
            <div class="po-mode-toolbar-wrap">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span
                        style="font-size: 0.725rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                        Mode Alur PO:
                    </span>
                    <div class="po-mode-pills">
                        <button type="button" id="btnModeSingle" onclick="setPoMode('single')" class="po-mode-btn active">
                            🏢 Satu Supplier (Standar 1 PO)
                        </button>
                        <button type="button" id="btnModeMulti" onclick="setPoMode('multi')" class="po-mode-btn">
                            ⚡ Multi-Supplier (Auto-Split PO)
                        </button>
                    </div>
                </div>

                {{-- Skema Pajak PPN (Dipindah ke Atas agar Grid 4 Kolom di Bawah Simetris & Tidak Sempit) --}}
                <div
                    style="display: flex; align-items: center; gap: 0.45rem; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.25rem 0.65rem;">
                    <label
                        style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; margin-bottom: 0; user-select: none;">
                        <input type="hidden" name="is_harga_termasuk_pajak" value="0">
                        <input type="checkbox" id="is_harga_termasuk_pajak" name="is_harga_termasuk_pajak" value="1" {{ old('is_harga_termasuk_pajak') ? 'checked' : '' }} onchange="onHeaderTaxInclusiveToggle(this)"
                            style="width: 15px; height: 15px; accent-color: #059669; cursor: pointer;">
                        <span style="color: #0f172a; font-size: 0.775rem; font-weight: 700;">Harga Termasuk PPN</span>
                        <span class="badge" id="badgeTaxMode"
                            style="background: #e2e8f0; color: #475569; font-size: 0.65rem; font-weight: 700; margin-left: 0.2rem;">Net</span>
                    </label>
                </div>
            </div>

            {{-- GRID UNIFIED 4 KOLOM X 2 BARIS (SIMETRIS, PRESISI, TIDAK MEPET & TIDAK BERONGGA) --}}
            <div class="po-toolbar-grid">
                {{-- [R1-C1] Tanggal PO --}}
                <div>
                    <label for="po_tgl" class="po-field-label">
                        Tanggal PO <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="date" id="po_tgl" name="po_tgl" value="{{ old('po_tgl', date('Y-m-d')) }}"
                        class="po-field-input" required>
                </div>

                {{-- [R1-C2] Nomor PO --}}
                <div>
                    <label for="po_no" class="po-field-label">
                        Nomor PO <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="po_no" name="po_no" value="{{ old('po_no', $nextPoNo ?? '') }}"
                        class="po-field-input" style="font-family: monospace; font-weight: 700;" required>
                    <small id="po_no_help" style="display: none;">Nomor urut otomatis sistem pengadaan.</small>
                </div>

                {{-- [R1-C3] Supplier Mitra (Smart Autocomplete / Picker) --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                        <label id="lblSupplierHeader" class="po-field-label" style="margin-bottom: 0;">
                            Supplier Mitra <span id="reqSupplierHeader" style="color:#ef4444;">*</span>
                        </label>
                        <a href="javascript:void(0)" onclick="openSupplierModal()"
                            style="font-size: 0.7rem; color: #0284c7; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;"
                            title="Buka Daftar Master Supplier">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Daftar ({{ $supplierList->count() }})
                        </a>
                    </div>

                    {{-- Card Supplier Terpilih --}}
                    <div id="selected_supplier_card"
                        style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.25rem 0.65rem; justify-content: space-between; align-items: center; gap: 0.4rem; height: 36px; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; min-width: 0;">
                            <span
                                style="background: #22c55e; color: #fff; width: 17px; height: 17px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; flex-shrink: 0;">✓</span>
                            <div style="min-width: 0; display: flex; align-items: center; gap: 0.3rem;">
                                <strong id="disp_sup_name"
                                    style="color: #14532d; font-size: 0.775rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></strong>
                                <span id="disp_sup_code" class="badge"
                                    style="background: #dcfce7; color: #166534; font-size: 0.65rem; font-weight: 700; font-family: monospace;"></span>
                                <span id="disp_sup_cat_badge" style="display: none;"></span>
                                <span id="disp_sup_contact" style="display: none;"></span>
                            </div>
                        </div>
                        <button type="button" onclick="clearSelectedSupplier()" class="btn btn-sm"
                            style="background: #ffffff; border: 1px solid #86efac; color: #15803d; font-size: 0.675rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 3px; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
                            Ganti
                        </button>
                    </div>

                    {{-- Search Box Autocomplete Supplier --}}
                    <div id="supplier_search_wrapper" style="position: relative;">
                        <div style="position: relative; width: 100%;">
                            <input type="text" id="supplier_search_input" class="po-field-input"
                                placeholder="Cari nama / kode supplier mitra..." autocomplete="off"
                                style="padding-left: 1.85rem !important; padding-right: 1.5rem !important;">
                            <span
                                style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <span
                                style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.6rem;">
                                ▼
                            </span>
                        </div>

                        {{-- Dropdown Container Autocomplete --}}
                        <div id="supplier_dropdown_list"
                            style="display: none; position: absolute; top: calc(100% + 3px); left: 0; right: 0; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.06); max-height: 280px; overflow-y: auto; z-index: 1050;">
                        </div>
                    </div>

                    {{-- Hidden select yang disinkronkan untuk form POST --}}
                    <select id="supplier_id" name="supplier_id" style="display: none;" required>
                        <option value="">-- Pilih Supplier Mitra --</option>
                        @foreach ($supplierList as $sup)
                            <option value="{{ $sup->supplier_id }}" {{ old('supplier_id') == $sup->supplier_id ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R1-C4] Gudang Tujuan Masuk --}}
                <div>
                    <label for="gudang_id" class="po-field-label">
                        Gudang Tujuan Masuk <span style="color:#ef4444;">*</span>
                        @if (!empty($isGudangLocked))
                            <span class="badge"
                                style="background:#dcfce7; color:#15803d; font-size:0.625rem; margin-left: 0.2rem;">Terkunci</span>
                        @endif
                    </label>
                    @if (!empty($isGudangLocked))
                        <input type="hidden" name="gudang_id" value="{{ $assignedGudangId }}">
                        <select id="gudang_id" class="po-field-select" disabled
                            style="background: #f8fafc; cursor: not-allowed;">
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ $assignedGudangId == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    @else
                        <select id="gudang_id" name="gudang_id" class="po-field-select" required>
                            <option value="">-- Pilih Gudang Masuk --</option>
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $assignedGudangId ?? '') == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- [R2-C1] Penyetuju PO (Approver) --}}
                <div>
                    <label for="approver_id" class="po-field-label">
                        👔 Penyetuju (Approver) <span style="color:#ef4444;">*</span>
                    </label>
                    <select id="approver_id" name="approver_id" class="po-field-select" required>
                        <option value="">-- Pilih Atasan Penyetuju --</option>
                        @foreach ($approverList as $appr)
                            <option value="{{ $appr->id }}" {{ old('approver_id') == $appr->id ? 'selected' : '' }}>
                                {{ $appr->name }} [{{ $appr->role_cd }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R2-C2] Term of Payment --}}
                <div>
                    <label for="term_of_payment" class="po-field-label">
                        Term of Payment (TOP)
                    </label>
                    <input type="text" id="term_of_payment" name="term_of_payment" class="po-field-input"
                        value="{{ old('term_of_payment', 'Transfer') }}" placeholder="Contoh: Transfer / Tempo 30 Hari">
                </div>

                {{-- [R2-C3] Catatan Khusus (Remark) --}}
                <div>
                    <label for="remark_txt" class="po-field-label">
                        Catatan Khusus (Remark)
                    </label>
                    <input type="text" id="remark_txt" name="remark_txt" class="po-field-input"
                        value="{{ old('remark_txt') }}" placeholder="Contoh: Kadar air standar pabrik, kemasan rapi">
                </div>

                {{-- [R2-C4] Estimasi Kedatangan --}}
                <div>
                    <label for="tgl_estimasi_datang" class="po-field-label">
                        Estimasi Kedatangan
                    </label>
                    <input type="date" id="tgl_estimasi_datang" name="tgl_estimasi_datang"
                        value="{{ old('tgl_estimasi_datang') }}" class="po-field-input">
                </div>
            </div>
        </div>

        {{-- 3. KARTU TABEL WORKSTATION FULL-WIDTH (100% LEBAR TANPA TERJEPIT) --}}
        <div class="po-workstation-table-card">
            {{-- Card Header: Filter Kategori (Kiri) & Safety Stock / Pintasan (Kanan) --}}
            <div
                style="background: #ffffff; padding: 0.55rem 0.85rem; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                
                {{-- Sisi Kiri: Filter Kategori Barang (Tepat di atas kolom Pilih Barang) --}}
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <strong style="color: #0f172a; font-size: 0.875rem; white-space: nowrap;">Item Pesanan:</strong>

                    {{-- Category Segmented Filter --}}
                    <div class="category-segmented-control" role="group" aria-label="Filter Kategori Barang"
                        style="padding: 2px;">
                        <button type="button" class="category-segment-btn active" data-category="ALL"
                            onclick="filterBarangCategory('ALL', this)"
                            style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Semua</span>
                            <span class="category-segment-count">{{ $barangList->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="BB"
                            onclick="filterBarangCategory('BB', this)"
                            style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Bahan Baku</span>
                            <span
                                class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']))->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="BUMBU"
                            onclick="filterBarangCategory('BUMBU', this)"
                            style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Bumbu &amp; Penolong</span>
                            <span
                                class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="PACK"
                            onclick="filterBarangCategory('PACK', this)"
                            style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Kemasan</span>
                            <span
                                class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)))->count() }}</span>
                        </button>
                    </div>
                </div>

                {{-- Sisi Kanan: Rekomendasi Stok Kritis & Keyboard Shortcut Hint --}}
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    {{-- Safety Stock Trigger Button (Modal Popup) --}}
                    @if (!empty($belowMinimumList) && $belowMinimumList->count() > 0)
                        <button type="button" onclick="openSafetyStockModal()" id="btnToggleSafetyStock" class="btn btn-sm"
                            style="background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; font-weight: 700; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.65rem; border-radius: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(245, 158, 11, 0.12);">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span id="safetyStockCountText">Stok Kritis ({{ $belowMinimumList->count() }})</span>
                        </button>
                    @endif

                    <span style="font-size: 0.725rem; color: #64748b;">
                        💡 Tekan <kbd
                            style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd>
                        untuk baris baru
                    </span>
                </div>
            </div>

            {{-- Tabel Utama Excel-Grid Modern --}}
            <div style="overflow-x: auto;">
                <table class="excel-grid-table" id="poItemsTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">#</th>
                            <th style="min-width: 340px;">Barang / Komoditas <span style="color:#ef4444;">*</span></th>
                            <th class="col-supplier" style="display: none; min-width: 220px;">
                                Supplier Mitra <span style="color:#ef4444;">*</span>
                            </th>
                            <th style="width: 85px; text-align: center;">Satuan</th>
                            <th style="width: 130px; text-align: right;">Kuantitas <span style="color:#ef4444;">*</span>
                            </th>
                            <th style="width: 160px; text-align: right;">Harga Satuan (Rp) <span
                                    style="color:#ef4444;">*</span></th>
                            <th style="width: 160px; text-align: right;">Total (Rp)</th>
                            <th style="width: 45px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        {{-- Row 1 Default --}}
                        <tr class="item-row" data-index="0">
                            <td class="row-num"
                                style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">1</td>
                            <td>
                                <select name="items[0][barang_id]" class="form-control item-barang"
                                    onchange="updateRowSatuan(this)" required>
                                    <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                                    <optgroup label="BAHAN BAKU (RAW MATERIAL)" class="grp-bb">
                                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                                            <option value="{{ $b->barang_id }}" data-category="BB"
                                                data-code="{{ $b->barang_cd }}"
                                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}"
                                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp
                                                {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                                            <option value="{{ $b->barang_id }}" data-category="BUMBU"
                                                data-code="{{ $b->barang_cd }}"
                                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}"
                                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp
                                                {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                                            <option value="{{ $b->barang_id }}" data-category="PACK"
                                                data-code="{{ $b->barang_cd }}"
                                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}"
                                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp
                                                {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <div class="row-barang-hint"
                                    style="font-size: 0.68rem; margin-top: 2px; color: #64748b; display: none;"></div>
                            </td>
                            <td class="col-supplier" style="display: none;">
                                <select name="items[0][supplier_id]" class="form-control item-supplier"
                                    onchange="calculateGrandTotal()">
                                    {!! $supplierOptionsHtml !!}
                                </select>
                                <div class="row-supplier-hint" style="font-size: 0.68rem; margin-top: 2px; display: none;">
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="row-satuan"
                                    style="font-weight: 600; color: #475569; font-size: 0.8rem;">-</span>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0.0001" name="items[0][pesan_qty]"
                                    class="form-control item-qty" value="1" oninput="calculateSubtotal(this)"
                                    style="text-align: right; font-weight: 700;" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[0][harga_nominal]"
                                    class="form-control item-harga" value="0" oninput="calculateSubtotal(this)"
                                    placeholder="0" style="text-align: right; font-weight: 600;">
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.9rem; font-family: monospace;"
                                class="row-subtotal">
                                Rp 0
                            </td>
                            <td style="text-align: center;">
                                <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"
                                    style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                            <td colspan="2"
                                style="padding: 0.55rem 0.75rem; text-align: right; color: #475569; font-size: 0.8rem; text-transform: uppercase;">
                                Akumulasi:
                            </td>
                            <td class="col-supplier" style="display: none;"></td>
                            <td></td>
                            <td id="totalQtyDisplay"
                                style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #047857; padding-right: 0.65rem;">
                                1.00</td>
                            <td></td>
                            <td id="totalSubtotalDisplay"
                                style="text-align: right; padding: 0.55rem 0.65rem; font-size: 0.95rem; color: #0f172a; font-family: monospace;">
                                Rp 0</td>
                            <td style="display: none;" id="grandTotalDisplay">Rp 0</td>
                            <td style="display: none;" id="totalDiskonDisplay">-</td>
                            <td style="display: none;" id="totalPotonganDisplay">-</td>
                            <td style="display: none;" id="totalPpnDisplay">-</td>
                            <td style="display: none;" id="totalIncBadge">-</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                @include('gudang.po.partials.item-row-template')
            </div>

            {{-- Tombol Tambah Baris Baru & Hapus Semua Baris --}}
            <div style="padding: 0.55rem 0.85rem; background: #ffffff; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn-add-row-wide" onclick="addRow(true)" style="max-width: 320px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Tambah Baris Baru</span>
                </button>

                <button type="button" class="btn btn-sm" onclick="openConfirmClearRowsModal()" id="btnOpenClearRows"
                    style="background: #fff1f2; border: 1.5px solid #fecdd3; color: #e11d48; font-weight: 700; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; border-radius: 6px; cursor: pointer; transition: all 0.15s ease;"
                    title="Kosongkan seluruh baris item pesanan">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Semua Baris</span>
                </button>
            </div>
        </div>

        {{-- 4. BOTTOM SECTION: CATATAN (KIRI) & FINANCIAL STACK (KANAN) --}}
        <div class="po-bottom-grid">
            {{-- Sisi Kiri: Catatan Pengadaan / Instruksi & Ringkasan Split --}}
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                <div class="po-notes-card">
                    <label for="catatan_txt" class="po-field-label"
                        style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Catatan &amp; Instruksi Pengadaan (NOTE)</span>
                        <small style="font-weight: normal; color: #64748b;">Tampil di dokumen PO resmi</small>
                    </label>
                    <textarea id="catatan_txt" name="catatan_txt" rows="3" class="form-control"
                        placeholder="Tuliskan catatan khusus pengiriman, instruksi armada bongkar muat, atau ketentuan kualitas..."
                        style="border: 1.5px solid #cbd5e1; border-radius: 5px; font-size: 0.8125rem; padding: 0.5rem;">{{ old('catatan_txt') }}</textarea>

                    {{-- Kotak Auto-Split Summary Jika Mode Multi-Supplier --}}
                    <div id="sideAutoSplitBox"
                        style="display: none; margin-top: 0.65rem; padding: 0.6rem 0.85rem; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 5px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <strong style="color: #14532d; font-size: 0.75rem;">Estimasi Penerbitan Dokumen:</strong>
                            <span id="sidePoCountBadge" class="badge"
                                style="background: #dcfce7; color: #166534; font-weight: 700; font-size: 0.7rem;">1 Dokumen
                                PO</span>
                        </div>
                        <div id="sidePoSupplierList" style="font-size: 0.725rem; color: #15803d; line-height: 1.4;"></div>
                    </div>
                </div>

                {{-- Panduan Ringkas Operasional --}}
                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.85rem; font-size: 0.725rem; color: #64748b; line-height: 1.4;">
                    <strong style="color: #334155;">💡 Tips Pengadaan:</strong> Tekan <kbd
                        style="background:#e2e8f0; padding:1px 3px; border-radius:3px; font-weight:700;">Enter</kbd> pada
                    kuantitas/harga untuk langsung baris baru. Gunakan mode <em>Multi-Supplier</em> untuk pesanan banyak
                    supplier sekaligus.
                </div>
            </div>

            {{-- Sisi Kanan: Financial Stack Ringkasan Kalkulasi & Tombol Aksi --}}
            <div class="po-financial-card">
                <div
                    style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.65rem;">
                    Ringkasan Finansial Dokumen
                </div>

                {{-- 1. Total Kuantitas --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Kuantitas:</span>
                    <strong style="color: #0284c7; font-family: monospace; font-size: 0.95rem;">
                        <span id="sideTotalQty">1.00</span> Unit
                    </strong>
                </div>

                {{-- 2. Subtotal Bruto --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Subtotal Bruto:</span>
                    <span id="sideSubtotalBruto"
                        style="font-weight: 700; font-family: monospace; font-size: 0.95rem; color: #1e293b;">Rp 0</span>
                </div>

                {{-- 3. Diskon Global (-) % dan Nominal --}}
                <div class="po-financial-row" style="align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: #64748b; font-weight: 600;">Diskon Global (-):</span>
                        <div style="display: inline-flex; align-items: center; gap: 2px;">
                            <input type="number" step="0.1" min="0" max="100" id="global_diskon_persen" name="diskon_persen"
                                value="{{ old('diskon_persen', 0) }}" oninput="calculateGrandTotal()" class="form-control"
                                style="width: 58px; height: 28px; padding: 2px 6px; font-size: 0.8rem; text-align: right; font-weight: 700;"
                                placeholder="0">
                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">%</span>
                        </div>
                    </div>
                    <span id="sideTotalDiskon" style="font-weight: 700; font-family: monospace; color: #d97706;">Rp 0</span>
                </div>

                {{-- 4. Potongan Harga (-) Nominal --}}
                <div class="po-financial-row" style="align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: #64748b; font-weight: 600;">Potongan (-):</span>
                        <input type="number" step="any" min="0" id="global_potongan_nominal" name="potongan_nominal"
                            value="{{ old('potongan_nominal', 0) }}" oninput="calculateGrandTotal()" class="form-control"
                            style="width: 105px; height: 28px; padding: 2px 8px; font-size: 0.8rem; text-align: right; font-weight: 700;"
                            placeholder="Rp 0">
                    </div>
                    <span id="sideTotalPotongan" style="font-weight: 700; font-family: monospace; color: #dc2626;">Rp
                        0</span>
                </div>

                {{-- 5. DPP (Netto) --}}
                <div class="po-financial-row"
                    style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.45rem; margin-top: 0.2rem;">
                    <span style="color: #475569; font-weight: 700;">DPP (Netto):</span>
                    <span id="sideTotalDpp"
                        style="font-weight: 700; font-family: monospace; color: #1e293b; font-size: 0.95rem;">Rp 0</span>
                </div>

                {{-- 6. Pajak (PPN) Selector & Nilai --}}
                <div class="po-financial-row" style="align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: #64748b; font-weight: 600;">Pajak (PPN):</span>
                        <select id="global_pajak_id" name="pajak_id" class="form-control"
                            style="width: 130px; height: 28px; padding: 2px 6px; font-size: 0.775rem; font-weight: 700;"
                            onchange="calculateGrandTotal()">
                            <option value="" data-tarif="0" data-sifat="TAMBAH">Non-PPN (0%)</option>
                            @foreach ($pajakList as $pjk)
                                <option value="{{ $pjk->pajak_id }}" data-tarif="{{ (float) $pjk->tarif_persen }}"
                                    data-sifat="{{ $pjk->sifat_pajak }}" {{ (old('pajak_id') == $pjk->pajak_id || (empty(old('pajak_id')) && $pjk->is_default)) ? 'selected' : '' }}>
                                    {{ $pjk->pajak_cd }} ({{ (float) $pjk->tarif_persen }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <span id="sideTotalPpn" style="font-weight: 700; font-family: monospace; color: #0284c7;">Rp 0</span>
                </div>

                {{-- 7. Grand Total Emphasized Box --}}
                <div class="po-grand-total-box">
                    <div>
                        <span
                            style="font-size: 0.725rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #065f46; display: block;">
                            TOTAL ESTIMASI PO:
                        </span>
                        <span style="font-size: 0.7rem; color: #059669;">Termasuk Pajak &amp; Diskon</span>
                    </div>
                    <div class="po-grand-total-val" id="sideGrandTotal">
                        Rp 0
                    </div>
                </div>

                {{-- Hidden sideTotalItems element for JS --}}
                <span id="sideTotalItems" style="display: none;">1</span>

                {{-- Action Buttons --}}
                <div class="po-action-bar">
                    <a href="{{ route('gudang.po.index') }}" class="btn btn-secondary"
                        style="padding: 0.5rem 1rem; font-size: 0.8125rem; font-weight: 700; border-radius: 5px; background: #f8fafc; border: 1.5px solid #cbd5e1; color: #475569; text-decoration: none;">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitPo" class="btn btn-primary"
                        style="padding: 0.55rem 1.35rem; font-size: 0.875rem; font-weight: 800; background: #059669; border: none; border-radius: 5px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.3); display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span id="btnSubmitPoText">Simpan &amp; Terbitkan PO</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @include('gudang.po.partials.modal-supplier-picker')

    @php
        $suppliersJson = $supplierList->map(function ($sup) {
            return [
                'id' => $sup->supplier_id,
                'code' => $sup->supplier_cd,
                'name' => $sup->supplier_nm,
                'category' => $sup->jenisSupplier?->jenis_supplier_cd ?? 'OTHER',
                'categoryName' => $sup->jenisSupplier?->jenis_supplier_nm ?? 'Lainnya',
                'contact' => $sup->kontak_no ?? '-',
                'address' => $sup->alamat_txt ?? '',
                'topHari' => $sup->top_hari ?? 0,
            ];
        })->values();
    @endphp

    @include('gudang.po.partials.modal-confirm-previous-month')

    @php
        $safetyStockJson = (!empty($belowMinimumList) ? $belowMinimumList : collect())->map(function ($bm) {
            $catCd = $bm->jenisBarang?->jenis_barang_cd ?? 'OTHER';
            $category = match (true) {
                in_array($catCd, ['RAW', 'BB']) => 'BB',
                in_array($catCd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $bm->barang_nm) => 'BUMBU',
                in_array($catCd, ['PACK']) || (in_array($catCd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $bm->barang_nm)) => 'PACK',
                default => 'OTHER'
            };
            return [
                'id' => (int) $bm->barang_id,
                'code' => $bm->barang_cd ?? '',
                'name' => $bm->barang_nm,
                'category' => $category,
                'categoryName' => $bm->jenisBarang?->jenis_barang_nm ?? 'Komoditas',
                'currentStock' => (float) ($bm->current_stock ?? 0),
                'minStock' => (float) ($bm->batas_minimum_qty ?? 0),
                'deficitQty' => (float) ($bm->selisih_kurang ?? max(0, (float) ($bm->batas_minimum_qty ?? 0) - (float) ($bm->current_stock ?? 0))),
                'defaultPrice' => (float) ($bm->harga_beli_standar ?? 0),
                'satuan' => $bm->satuanDasar?->satuan_nm ?? ($bm->satuanDasar?->satuan_cd ?? '-'),
                'satuanCd' => $bm->satuanDasar?->satuan_cd ?? '-',
            ];
        })->values();
    @endphp

    @include('gudang.po.partials.modal-safety-stock')
    @include('gudang.po.partials.modal-confirm-clear-rows')

    @push('scripts')
        <script>
            window.currentServerYearMonth = '{{ date('Y-m') }}';
            window.suppliersData = @json($suppliersJson);
            window.safetyStockData = @json($safetyStockJson);
            @if(old('supplier_id'))
                window.oldSupplierId = {{ old('supplier_id') }};
            @endif
            @if(old('items.0.supplier_id') || old('items.1.supplier_id'))
                window.oldHasMultiSupplier = true;
            @endif
        </script>
        <script
            src="{{ asset('js/gudang/po/po-create.js') }}?v={{ filemtime(public_path('js/gudang/po/po-create.js')) }}"></script>
        <script>
            if (window.oldSupplierId) {
                selectSupplier(window.oldSupplierId);
            }
            if (window.oldHasMultiSupplier) {
                setPoMode('multi');
            }
        </script>
    @endpush

@endsection