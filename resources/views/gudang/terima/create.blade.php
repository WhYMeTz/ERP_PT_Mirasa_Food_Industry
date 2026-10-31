@extends('layouts.app')

@section('title', 'Catat Penerimaan Barang Fisik - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-workstation.css') }}?v={{ filemtime(public_path('css/gudang/po/po-workstation.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-form.css') }}?v={{ filemtime(public_path('css/gudang/terima/terima-form.css')) }}">
@endpush

@section('content')
    {{-- 1. CHEVRON BREADCRUMB NAVIGATION (GAYA AKUNTANSI ENTERPRISE) & LIVE KPI WIDGET --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.5rem;">
        <nav class="erp-chevron-bar" aria-label="Breadcrumb">
            <a href="{{ route('gudang.terima.index') }}" class="erp-chevron-item">
                <span>Pergudangan</span>
            </a>
            <span class="erp-chevron-sep">
                <svg width="8" height="20" viewBox="0 0 8 20" fill="none"><path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" /></svg>
            </span>
            <a href="{{ route('gudang.terima.index') }}" class="erp-chevron-item">
                <span>Penerimaan Barang</span>
            </a>
            <span class="erp-chevron-sep">
                <svg width="8" height="20" viewBox="0 0 8 20" fill="none"><path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" /></svg>
            </span>
            <span class="erp-chevron-item active">
                <span>Add Penerimaan (GRN)</span>
            </span>
            <span class="erp-chevron-end">
                <svg width="10" height="32" viewBox="0 0 10 32" fill="none"><path d="M1 1L8 16L1 31" stroke="#dce4ec" stroke-width="1.5" /></svg>
            </span>
        </nav>

        {{-- Live KPI Widget Ramping di Siku Kanan Atas --}}
        <div class="po-live-kpi-widget">
            <div>
                <div class="po-live-kpi-title">TOTAL NETTO MASUK:</div>
                <div class="po-live-kpi-val" id="topLiveGrandTotal">0,00 KG</div>
            </div>
            <div style="height: 28px; width: 1.5px; background: #e2e8f0;"></div>
            <div style="font-size: 0.775rem; line-height: 1.35; color: #475569;">
                <div>Baris: <strong id="topLiveItemCount" style="color: #0f172a; font-family: monospace;">1 Item</strong></div>
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <span style="font-size: 0.7rem; color: #059669; font-weight: 700;">🟢 Mode Fisik</span>
                    <span id="qcStatusPill" style="font-size: 0.675rem; color: #0284c7; font-weight: 700; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1px 6px; border-radius: 10px; display: none;">🔬 QC Terhubung</span>
                    <span id="draftSaveIndicator" style="display: none; font-size: 0.675rem; color: #0284c7; font-weight: 700; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1px 6px; border-radius: 10px;" title="Data form otomatis disimpan ke memori browser">💾 Draft Tersimpan</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('gudang.terima.store') }}" method="POST" id="formTerima">
        @csrf
        <input type="hidden" name="qc_id" id="input_qc_id" value="{{ old('qc_id', request('qc_id')) }}">
        @if ($selectedPo)
            <input type="hidden" name="redirect_to" value="po">
        @endif

        {{-- 2. TOOLBAR CARD RAMPING: SEMUA FIELD LANGSUNG TAMPIL 2 BARIS (NO ACCORDION, ZERO EMPTY SPACE) --}}
        <div class="po-toolbar-card">
            {{-- Header Toolbar Bar: QC Synchronizer & Status --}}
            <div class="po-mode-toolbar-wrap" style="align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0; flex-wrap: wrap;">
                    <div style="width: 28px; height: 28px; border-radius: 6px; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;">
                        🔬
                    </div>
                    <div style="min-width: 0;">
                        <span style="font-size: 0.8rem; font-weight: 800; color: #0369a1;" id="qcBannerTitle">
                            Sinkronisasi Inspeksi Mutu QC Lapangan
                        </span>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;" id="qcBannerSubtitle">
                            Tarik sampling kadar air, refraksi kotoran, dan timbangan truk yang diverifikasi QC.
                        </span>
                    </div>
                </div>

                {{-- Action buttons for QC --}}
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <button type="button" onclick="openQcModal()" class="btn btn-sm"
                        style="background: #0284c7; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <span>⚡ Tarik Tiket QC</span>
                    </button>
                    <button type="button" id="btnDetachQc" onclick="detachQcTicket()"
                        style="display: none; background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.3rem 0.65rem; border-radius: 5px; font-size: 0.725rem; font-weight: 700; cursor: pointer;">
                        ✕ Lepas QC
                    </button>
                </div>
            </div>

            {{-- GRID UNIFIED 4 KOLOM X 2 BARIS (SIMETRIS, PRESISI, TIDAK MEPET & TIDAK BERONGGA) --}}
            <div class="po-toolbar-grid">
                {{-- [R1-C1] Tanggal Masuk Fisik --}}
                <div>
                    <label for="terima_tgl" class="po-field-label">
                        Tanggal Masuk <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="date" id="terima_tgl" name="terima_tgl" value="{{ old('terima_tgl', date('Y-m-d')) }}"
                        class="po-field-input" required>
                </div>

                {{-- [R1-C2] Nomor Penerimaan (GRN) --}}
                <div>
                    <label for="terima_no" class="po-field-label">
                        Nomor GRN <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="terima_no" name="terima_no" value="{{ old('terima_no', $nextTerimaNo ?? '') }}"
                        class="po-field-input" style="font-family: monospace; font-weight: 700;" required>
                    <small id="terima_no_help" style="display: none; color: #64748b; font-size: 0.725rem; margin-top: 2px;">Nomor urut otomatis Goods Receipt Note (GRN).</small>
                </div>

                {{-- [R1-C3] Supplier Pengirim (Smart Autocomplete / Picker) --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                        <label id="lblSupplierHeader" class="po-field-label" style="margin-bottom: 0;">
                            Supplier Pengirim <span style="color:#ef4444;">*</span>
                        </label>
                        <a href="javascript:void(0)" onclick="openSupplierModal()"
                            style="font-size: 0.7rem; color: #0284c7; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;"
                            title="Buka Daftar Master Supplier">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Daftar ({{ $supplierList->count() }})
                        </a>
                    </div>

                    {{-- Selected Supplier Card --}}
                    <div id="selected_supplier_card"
                        style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.25rem 0.65rem; justify-content: space-between; align-items: center; gap: 0.4rem; height: 36px; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; min-width: 0;">
                            <span style="background: #22c55e; color: #fff; width: 17px; height: 17px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; flex-shrink: 0;">✓</span>
                            <div style="min-width: 0; display: flex; align-items: center; gap: 0.3rem;">
                                <strong id="disp_sup_name" style="color: #14532d; font-size: 0.775rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></strong>
                                <span id="disp_sup_code" class="badge" style="background: #dcfce7; color: #166534; font-size: 0.65rem; font-weight: 700; font-family: monospace;"></span>
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
                            <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </span>
                            <span style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.6rem;">
                                ▼
                            </span>
                        </div>
                        <div id="supplier_dropdown_list"
                            style="display: none; position: absolute; top: calc(100% + 3px); left: 0; right: 0; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.06); max-height: 280px; overflow-y: auto; z-index: 1050;">
                        </div>
                    </div>

                    {{-- Hidden select synchronized for POST --}}
                    <select id="supplier_id" name="supplier_id" style="display: none;" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($supplierList as $sup)
                            <option value="{{ $sup->supplier_id }}"
                                data-code="{{ $sup->supplier_cd }}"
                                data-name="{{ $sup->supplier_nm }}"
                                data-category="{{ $sup->jenisSupplier?->jenis_supplier_cd ?? 'OTHER' }}"
                                data-category-name="{{ $sup->jenisSupplier?->jenis_supplier_nm ?? 'Lainnya' }}"
                                data-contact="{{ $sup->kontak_no ?? '' }}"
                                data-address="{{ $sup->alamat_txt ?? '' }}"
                                {{ (old('supplier_id', $selectedPo?->supplier_id) == $sup->supplier_id) ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R1-C4] Perusahaan / Lokasi Penerima --}}
                <div>
                    <label for="gudang_id" class="po-field-label">
                        Gudang Lokasi Penerima <span style="color:#ef4444;">*</span>
                    </label>
                    <select id="gudang_id" name="gudang_id" class="po-field-select" required>
                        <option value="">-- Pilih Gudang Masuk --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $selectedPo?->gudang_id) == $gdg->gudang_id) ? 'selected' : '' }}>
                                {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R2-C1] Referensi Purchase Order --}}
                <div>
                    <label for="po_id" class="po-field-label">
                        Referensi PO (Opsional)
                    </label>
                    <select id="po_id" name="po_id" class="po-field-select" onchange="onPoSelected(this.value)">
                        <option value="">-- Non-PO / Pembelian Langsung --</option>
                        @foreach ($openPoList as $po)
                            <option value="{{ $po->po_id }}" 
                                data-supplier="{{ $po->supplier_id }}" 
                                data-gudang="{{ $po->gudang_id }}"
                                {{ (old('po_id', $selectedPo?->po_id) == $po->po_id) ? 'selected' : '' }}>
                                {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} ({{ $po->status_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R2-C2] No. Surat Jalan Supplier --}}
                <div>
                    <label for="suratjalan_no" class="po-field-label">
                        No. Surat Jalan (SJ) Supplier
                    </label>
                    <input type="text" id="suratjalan_no" name="suratjalan_no" value="{{ old('suratjalan_no') }}"
                        class="po-field-input" placeholder="Contoh: SJ-2026/09/88">
                </div>

                {{-- [R2-C3] Jatuh Tempo Surat Jalan --}}
                <div>
                    <label for="tgl_jatuh_tempo" class="po-field-label">
                        Jatuh Tempo SJ (TOP)
                    </label>
                    <input type="date" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo" value="{{ old('tgl_jatuh_tempo') }}"
                        class="po-field-input">
                </div>

                {{-- [R2-C4] Catatan Singkat Armada / Lapangan --}}
                <div>
                    <label for="catatan_txt" class="po-field-label">
                        Keterangan Armada / Plat Truk
                    </label>
                    <input type="text" id="catatan_txt" name="catatan_txt" value="{{ old('catatan_txt') }}"
                        class="po-field-input" placeholder="Contoh: Plat AA 2382 KL (Sopir: Kumar)">
                </div>
            </div>
        </div>

        {{-- BARIS INFO PO AKTIF JIKA DIPILIH --}}
        @if ($selectedPo)
            <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.55rem 0.85rem; margin-bottom: 0.85rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="font-size: 0.8125rem; color: #334155; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <span style="font-weight: 700; color: #0f172a;">PO Terpilih:</span>
                    <span style="font-family: monospace; font-weight: 700; color: #0284c7;">{{ $selectedPo->po_no }}</span>
                    <span>&bull;</span>
                    <span>Supplier: <strong>{{ $selectedPo->supplier?->supplier_nm }}</strong></span>
                    @if ($selectedPo->status_cd == 'PARTIAL')
                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size: 0.7rem; padding: 2px 6px;">Sebagian Diterima</span>
                    @else
                        <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:700; font-size: 0.7rem; padding: 2px 6px;">Terbuka Penuh</span>
                    @endif
                </div>
                <div style="display: flex; gap: 0.4rem; align-items: center;">
                    <button type="button" onclick="copyAllRemainingPoQty()" class="btn btn-sm"
                        style="background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.65rem; border-radius: 5px; border: none; display: inline-flex; align-items: center; gap: 0.3rem;"
                        title="Isi kuantitas terima dengan seluruh sisa PO">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        Salin Sisa PO
                    </button>
                    <button type="button" onclick="clearAllTerimaQty()" class="btn btn-sm"
                        style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 600; font-size: 0.75rem; padding: 0.25rem 0.55rem; border-radius: 5px; display: inline-flex; align-items: center; gap: 0.3rem;"
                        title="Kosongkan kuantitas agar bisa diketik manual">
                        Kosongkan Qty
                    </button>
                </div>
            </div>
        @endif

        {{-- BANNER PERINGATAN OVER PO TERPADU --}}
        <div id="bannerTerimaOverPo" style="display: none; background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 8px; padding: 0.65rem 0.85rem; margin-bottom: 0.85rem; align-items: flex-start; gap: 0.65rem; box-shadow: 0 1px 3px rgba(245, 158, 11, 0.1);">
            <div style="width: 24px; height: 24px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; font-weight: 700;">
                ⚠️
            </div>
            <div style="flex: 1; font-size: 0.8rem; color: #92400e;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <strong style="color: #78350f; font-size: 0.825rem;">Peringatan: Kuantitas Fisik Melebihi Sisa Pesanan PO (Over-Delivery)</strong>
                    <span id="badgeOverPoCount" class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.675rem; font-weight: 700;">1 Item Over</span>
                </div>
                <div id="bannerTerimaOverPoText" style="margin-top: 2px; line-height: 1.35;">
                    Terdapat barang dengan kuantitas terima fisik melebihi sisa pesanan PO. Stok fisik tetap akan dicatat 100% akurat sesuai barang riil.
                </div>
            </div>
        </div>

        {{-- 3. KARTU TABEL WORKSTATION FULL-WIDTH (100% LEBAR TANPA TERJEPIT) --}}
        <div class="po-workstation-table-card">
            {{-- Card Header: Filter Kategori (Kiri) & Keyboard Shortcut (Kanan) --}}
            <div style="background: #ffffff; padding: 0.55rem 0.85rem; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                
                {{-- Sisi Kiri: Filter Kategori Barang --}}
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <strong style="color: #0f172a; font-size: 0.875rem; white-space: nowrap;">Fisik Barang Diterima:</strong>

                    <div class="category-segmented-control" role="group" aria-label="Filter Kategori Barang" style="padding: 2px;">
                        <button type="button" class="category-segment-btn active" data-category="ALL" onclick="filterBarangCategory('ALL', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Semua</span>
                            <span class="category-segment-count">{{ $barangList->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="BB" onclick="filterBarangCategory('BB', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Bahan Baku</span>
                            <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']))->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="BUMBU" onclick="filterBarangCategory('BUMBU', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Bumbu &amp; Penolong</span>
                            <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="PACK" onclick="filterBarangCategory('PACK', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Kemasan</span>
                            <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)))->count() }}</span>
                        </button>
                    </div>
                </div>

                {{-- Sisi Kanan: Hint --}}
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <span style="font-size: 0.725rem; color: #64748b;">
                        💡 Tekan <kbd style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd> untuk baris baru
                    </span>
                </div>
            </div>

            {{-- Table Excel Grid --}}
            <div style="overflow-x: auto;">
                <table class="excel-grid-table" id="tableTerimaItems" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 32px; text-align: center;">#</th>
                            <th style="min-width: 250px;">Nama Komoditas / Barang <span style="color:#ef4444;">*</span></th>
                            <th style="width: 155px;">Nomor Batch Fisik <span style="color:#ef4444;">*</span></th>
                            <th style="width: 120px; text-align: center;">Tgl Expired</th>
                            <th style="width: 115px; text-align: center;">Grade</th>
                            <th style="width: 110px; text-align: right;">Qty (Bruto) <span style="color:#ef4444;">*</span></th>
                            <th style="width: 90px; text-align: right;">Afkir</th>
                            <th style="width: 110px; text-align: right; background: #064e3b; color: #ffffff;">Netto</th>
                            <th style="width: 70px; text-align: center;">Satuan</th>
                            <th style="width: 130px; text-align: right;">Harga Satuan (Rp)</th>
                            <th style="width: 135px; text-align: right;">Total (Rp)</th>
                            <th style="width: 40px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="terimaItemsContainer">
                        @if ($selectedPo && $selectedPo->details->isNotEmpty())
                            {{-- Prefilled items from selected PO --}}
                            @foreach ($selectedPo->details as $idx => $pdtl)
                                @if ((float) $pdtl->sisa_qty > 0)
                                    @php
                                        $acronym = app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym(
                                            $pdtl->barang?->barang_nm, 
                                            $pdtl->barang?->barang_cd
                                        );
                                        $batchPrefix = ($acronym ?: 'BRG') . '-';
                                    @endphp
                                    <tr class="terima-row" data-index="{{ $idx }}" data-sisa="{{ (float) $pdtl->sisa_qty }}" data-pesan="{{ (float) $pdtl->pesan_qty }}" data-barang-nm="{{ $pdtl->barang?->barang_nm }}">
                                        <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">{{ $idx + 1 }}</td>
                                        <td>
                                            <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $pdtl->podtl_id }}">
                                            <input type="hidden" name="items[{{ $idx }}][barang_id]" value="{{ $pdtl->barang_id }}" class="item-barang-id">
                                            <strong style="color: #0f172a; display: block; font-size: 0.825rem;" class="item-barang-name">{{ $pdtl->barang?->barang_nm }}</strong>
                                            <span style="font-size: 0.725rem; color: #64748b;">
                                                Pesanan: {{ number_format((float) $pdtl->pesan_qty, 2) }} | <strong>Sisa: {{ number_format((float) $pdtl->sisa_qty, 2) }}</strong>
                                            </span>
                                            <div class="row-over-po-badge" style="display: none; margin-top: 3px;">
                                                <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 700; padding: 1px 5px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                    ⚠️ Over PO (<span class="over-diff-text">+0</span>)
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ old("items.{$idx}.batch_no", $batchPrefix) }}" placeholder="{{ $batchPrefix }}... (isi nomor batch)" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%;" required>
                                        </td>
                                        <td>
                                            <input type="date" name="items[{{ $idx }}][expired_tgl]" value="{{ old("items.{$idx}.expired_tgl") }}" class="form-control" style="font-size: 0.8rem;">
                                        </td>
                                        <td>
                                            <select name="items[{{ $idx }}][grade_cd]" class="form-control po-grade-select">
                                                <option value="A" selected>Grade A</option>
                                                <option value="B">Grade B</option>
                                                <option value="C">Grade C</option>
                                                <option value="REJECT">Reject</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][terima_qty]" value="{{ (float) $pdtl->sisa_qty }}" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                                            {{ number_format((float) $pdtl->sisa_qty, 2, ',', '.') }}
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="row-satuan" style="font-weight: 600; color: #475569; font-size: 0.8rem;">{{ $pdtl->barang?->satuanDasar?->satuan_nm ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="items[{{ $idx }}][harga_nominal]" value="{{ (float) $pdtl->harga_nominal }}" class="form-control item-harga" placeholder="0" style="text-align: right; font-weight: 600;" oninput="calculateTotalTerima()">
                                            {{-- Nilai Finansial/Pajak Disimpan di Hidden Input untuk Finance --}}
                                            <input type="hidden" name="items[{{ $idx }}][diskon_persen]" value="{{ old("items.{$idx}.diskon_persen", (float)($pdtl->diskon_persen ?? 0)) }}" class="item-diskon">
                                            <input type="hidden" name="items[{{ $idx }}][potongan_nominal]" value="{{ old("items.{$idx}.potongan_nominal", (float)($pdtl->potongan_nominal ?? 0)) }}" class="item-potongan">
                                            <input type="hidden" name="items[{{ $idx }}][ppn_tipe]" value="{{ old("items.{$idx}.ppn_tipe", $pdtl->ppn_tipe ?? 'NON_PPN') }}" class="item-ppn-tipe">
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;" class="row-subtotal">
                                            Rp {{ number_format((float)$pdtl->sisa_qty * (float)$pdtl->harga_nominal, 0, ',', '.') }}
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            {{-- Row Kosong Default --}}
                            <tr class="terima-row" data-index="0" data-sisa="0">
                                <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">1</td>
                                <td>
                                    <select name="items[0][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
                                        <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                                        
                                        <optgroup label="BAHAN BAKU (RAW MATERIAL)" class="grp-bb">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                                                <option value="{{ $b->barang_id }}" 
                                                        data-category="BB"
                                                        data-cd="{{ $b->barang_cd }}" 
                                                        data-nm="{{ $b->barang_nm }}"
                                                        data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                        data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                        data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                                                <option value="{{ $b->barang_id }}" 
                                                        data-category="BUMBU"
                                                        data-cd="{{ $b->barang_cd }}" 
                                                        data-nm="{{ $b->barang_nm }}"
                                                        data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                        data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                        data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                                                <option value="{{ $b->barang_id }}" 
                                                        data-category="PACK"
                                                        data-cd="{{ $b->barang_cd }}" 
                                                        data-nm="{{ $b->barang_nm }}"
                                                        data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                        data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                        data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[0][batch_no]" id="batch_0" value="" placeholder="Contoh: BC-... (isi nomor batch)" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%;" required>
                                </td>
                                <td>
                                    <input type="date" name="items[0][expired_tgl]" class="form-control" style="font-size: 0.8rem;">
                                </td>
                                <td>
                                    <select name="items[0][grade_cd]" class="form-control po-grade-select">
                                        <option value="A" selected>Grade A</option>
                                        <option value="B">Grade B</option>
                                        <option value="C">Grade C</option>
                                        <option value="REJECT">Reject</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0" name="items[0][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0" name="items[0][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                                    1,00
                                </td>
                                <td style="text-align: center;">
                                    <span class="row-satuan" style="font-weight: 600; color: #475569; font-size: 0.8rem;">-</span>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right; font-weight: 600;" oninput="calculateTotalTerima()">
                                    {{-- Nilai Finansial/Pajak Disimpan di Hidden Input untuk Finance --}}
                                    <input type="hidden" name="items[0][diskon_persen]" value="0" class="item-diskon">
                                    <input type="hidden" name="items[0][potongan_nominal]" value="0" class="item-potongan">
                                    <input type="hidden" name="items[0][ppn_tipe]" value="NON_PPN" class="item-ppn-tipe">
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;" class="row-subtotal">
                                    Rp 0
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                            <td colspan="5" style="padding: 0.55rem 0.75rem; text-align: right; color: #475569; font-size: 0.8rem; text-transform: uppercase;">
                                Akumulasi Total Fisik:
                            </td>
                            <td id="totalBrutoQtyDisplay" style="text-align: right; font-family: monospace; font-size: 0.9rem; color: #0284c7; padding-right: 0.5rem;">0,00</td>
                            <td id="totalRejectQtyDisplay" style="text-align: right; font-family: monospace; font-size: 0.9rem; color: #dc2626; padding-right: 0.5rem;">0,00</td>
                            <td id="totalNettoQtyDisplay" style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #047857; background: #dcfce7; padding-right: 0.5rem;">0,00</td>
                            <td style="text-align: center; color: #64748b; font-size: 0.75rem;">Total</td>
                            <td></td>
                            <td id="totalTerimaNilaiDisplay" style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #0f172a; padding-right: 0.5rem;">Rp 0</td>
                            <td style="display: none;" id="totalTerimaDiskonDisplay">-</td>
                            <td style="display: none;" id="totalTerimaPotonganDisplay">-</td>
                            <td style="display: none;" id="totalTerimaPpnDisplay">-</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                @include('gudang.terima.partials.item-row-template')
            </div>

            {{-- Tombol Tambah Baris Baru --}}
            <div style="padding: 0.55rem 0.85rem; background: #ffffff; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn-add-row-wide" onclick="addTerimaRow(true)" style="max-width: 320px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>+ Tambah Baris Baru</span>
                </button>
            </div>
        </div>

        {{-- 4. BOTTOM SECTION: CATATAN LAPANGAN (KIRI) & FINANCIAL/PHYSICAL STACK (KANAN) --}}
        <div class="po-bottom-grid">
            {{-- Sisi Kiri: Catatan & Panduan --}}
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                <div class="po-notes-card">
                    <label for="catatan_lengkap" class="po-field-label" style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Catatan Fisik Penerimaan &amp; Berita Acara</span>
                        <small style="font-weight: normal; color: #64748b;">Kondisi muatan &amp; armada</small>
                    </label>
                    <textarea id="catatan_lengkap" name="catatan_lengkap_display" rows="3" class="form-control"
                        placeholder="Tuliskan catatan kondisi muatan, kadar air, basah karena hujan, hasil sortir, atau catatan operasional lainnya..."
                        style="border: 1.5px solid #cbd5e1; border-radius: 5px; font-size: 0.8125rem; padding: 0.5rem;"
                        oninput="document.getElementById('catatan_txt').value = this.value">{{ old('catatan_txt') }}</textarea>
                </div>

                {{-- Panduan Ringkas Operasional --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.85rem; font-size: 0.725rem; color: #64748b; line-height: 1.4;">
                    <strong style="color: #334155;">💡 Tips Penerimaan Fisik:</strong>
                    Barang yang disimpan di formulir ini akan langsung menambah saldo fisik gudang, membuat nomor batch baru, dan dicatat pada Kartu Stok. Pastikan nomor batch fisik dan tanggal kedaluwarsa telah sesuai dengan surat jalan supplier.
                </div>
            </div>

            {{-- Sisi Kanan: Financial & Physical Summary Stack --}}
            <div class="po-financial-card">
                <div style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.65rem;">
                    Ringkasan Fisik &amp; Nilai GRN
                </div>

                {{-- 1. Total Bruto --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Bruto Timbangan:</span>
                    <strong style="color: #0284c7; font-family: monospace; font-size: 0.95rem;">
                        <span id="sideTotalBruto">0,00 KG</span>
                    </strong>
                </div>

                {{-- 2. Total Afkir / Reject --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Afkir / Reject:</span>
                    <strong style="color: #dc2626; font-family: monospace; font-size: 0.95rem;">
                        <span id="sideTotalAfkir">0,00 KG</span>
                    </strong>
                </div>

                {{-- 3. Total Netto Masuk --}}
                <div class="po-financial-row">
                    <span style="color: #14532d; font-weight: 700;">Total Netto Masuk (Stok):</span>
                    <strong style="color: #047857; font-family: monospace; font-size: 1.05rem;">
                        <span id="sideTotalNetto">0,00 KG</span>
                    </strong>
                </div>

                {{-- 4. DPP Nilai Barang Bersih --}}
                <div class="po-financial-row" style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.45rem; margin-top: 0.2rem;">
                    <span style="color: #475569; font-weight: 700;">DPP Nilai Netto:</span>
                    <span id="sideTotalDpp" style="font-weight: 700; font-family: monospace; color: #1e293b; font-size: 0.95rem;">Rp 0</span>
                </div>

                {{-- 5. Grand Total Box --}}
                <div class="po-grand-total-box">
                    <div>
                        <span style="font-size: 0.725rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #065f46; display: block;">
                            TOTAL NILAI PENERIMAAN:
                        </span>
                        <span style="font-size: 0.7rem; color: #059669;">Berdasarkan Kuantitas Netto Masuk</span>
                    </div>
                    <div class="po-grand-total-val" id="sideGrandTotal">
                        Rp 0
                    </div>
                </div>

                {{-- Hidden elements for backward JS compatibility --}}
                <span id="barTotalNetto" style="display: none;">0,00</span>
                <span id="barTotalReject" style="display: none;">0,00</span>
                <span id="barTotalItems" style="display: none;">0</span>
                <span id="barGrandTotal" style="display: none;">Rp 0</span>
                <div id="barTaxSummaryLine" style="display: none;"></div>
                <input type="hidden" name="potongan_nominal" id="global_potongan" value="{{ old('potongan_nominal', 0) }}">

                {{-- Action Bar --}}
                <div class="po-action-bar">
                    <a href="{{ route('gudang.terima.index') }}" class="btn btn-secondary"
                        style="padding: 0.5rem 1rem; font-size: 0.8125rem; font-weight: 700; border-radius: 5px; background: #f8fafc; border: 1.5px solid #cbd5e1; color: #475569; text-decoration: none;">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitTerima" class="btn btn-primary"
                        style="padding: 0.55rem 1.35rem; font-size: 0.875rem; font-weight: 800; background: #059669; border: none; border-radius: 5px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.3); display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        <span id="btnSubmitTerimaText">Simpan Penerimaan Fisik</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @include('gudang.terima.partials.modal-supplier-picker')
    @include('gudang.terima.partials.modal-qc-picker')
    @include('gudang.terima.partials.modal-confirm-over-po')
    @include('gudang.terima.partials.modal-confirm-previous-month')

    @php
        $suppliersJson = $supplierList->map(function($sup) {
            return [
                'id' => $sup->supplier_id,
                'code' => $sup->supplier_cd,
                'name' => $sup->supplier_nm,
                'category' => $sup->jenisSupplier?->jenis_supplier_cd ?? 'OTHER',
                'categoryName' => $sup->jenisSupplier?->jenis_supplier_nm ?? 'Lainnya',
                'contact' => $sup->kontak_no ?? '-',
                'address' => $sup->alamat_txt ?? '',
                'top_hari' => (int)($sup->top_hari ?? 14),
            ];
        })->values();
    @endphp

    @push('scripts')
    <script>
        window.suppliersData = @json($suppliersJson);
        window.qcSiapGudangUrl = "{{ route('qc.inbound.siap_gudang') }}";
        window.qcTicketDataBaseUrl = "{{ url('qc/inbound/ticket-data') }}";
        window.terimaCreateUrl = "{{ route('gudang.terima.create') }}";
        window.selectedPoDetailsCount = {{ $selectedPo ? count($selectedPo->details) : 1 }};
        window.currentServerYearMonth = "{{ date('Y-m') }}";
        window.hasOldServerInput = {{ ($errors->any() || old('terima_no') || old('supplier_id')) ? 'true' : 'false' }};
        @if(old('supplier_id', $selectedPo?->supplier_id))
            window.oldSupplierId = {{ old('supplier_id', $selectedPo?->supplier_id) }};
        @endif
        @if(old('qc_id', request('qc_id')))
            window.initialQcId = {{ (int) old('qc_id', request('qc_id')) }};
        @endif
    </script>
    <script src="{{ asset('js/gudang/terima/terima-create.js') }}?v={{ filemtime(public_path('js/gudang/terima/terima-create.js')) }}"></script>
    <script>
        if (window.oldSupplierId && !window.initialQcId) {
            selectSupplier(window.oldSupplierId);
        }
        calculateTotalTerima();
    </script>
    @endpush
@endsection
