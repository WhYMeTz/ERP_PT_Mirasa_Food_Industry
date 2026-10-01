@extends('layouts.app')

@section('title', 'Buat Purchase Order Baru - ERP PT Mirasa')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-form.css') }}">
@endpush

@php
    // Siapkan daftar option supplier untuk digunakan di baris tabel (Mode Multi-Supplier / Auto-Split)
    $supplierOptionsHtml = '<option value="">-- Pilih Supplier Mitra --</option>';
    if (isset($jenisSupplierList) && $jenisSupplierList->count() > 0) {
        foreach ($jenisSupplierList as $js) {
            $subs = $supplierList->filter(fn($s) => $s->jenis_supplier_id == $js->jenis_supplier_id);
            if ($subs->count() > 0) {
                $icon = match($js->jenis_supplier_cd) {
                    'RAW', 'BB' => '🌾 ',
                    'BUMBU'     => '🧂 ',
                    'KEMASAN', 'PACK' => '📦 ',
                    'BP'        => '🏭 ',
                    default     => ''
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

<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('gudang.po.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar PO
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Buat Purchase Order (PO) Baru</h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">Formulir pemesanan material &amp; bahan baku singkong/bumbu/kemasan ke mitra supplier.</p>
</div>

<form action="{{ route('gudang.po.store') }}" method="POST" id="formPo">
    @csrf

    <div class="order-station-grid">
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            {{-- KARTU 1: INFORMASI UTAMA DOKUMEN --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Supplier</strong>
                </div>

                {{-- SEGMENTED MODE SWITCHER: KONSEP 1 (AUTO-SPLIT PO) --}}
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <div style="font-size: 0.725rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.35rem;">
                            Mode Alur Pemesanan:
                        </div>
                        <div style="display: inline-flex; background: #e2e8f0; padding: 3px; border-radius: 6px; gap: 3px;">
                            <button type="button" id="btnModeSingle" onclick="setPoMode('single')" class="po-mode-btn active">
                                🏢 Satu Supplier (Standar 1 PO)
                            </button>
                            <button type="button" id="btnModeMulti" onclick="setPoMode('multi')" class="po-mode-btn">
                                ⚡ Multi-Supplier (Auto-Split PO)
                            </button>
                        </div>
                    </div>
                    <div id="modeDescription" style="font-size: 0.8rem; color: #64748b; max-width: 480px; line-height: 1.4;">
                        Mode standar: Seluruh barang dalam formulir ini dipesan ke 1 supplier utama di bawah (diterbitkan sebagai 1 dokumen PO resmi).
                    </div>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    {{-- BARIS 1: NOMOR PO, TANGGAL PO, ESTIMASI TIBA --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="po_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Nomor PO <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="po_no" name="po_no" value="{{ old('po_no', $nextPoNo ?? '') }}" class="form-control" style="background: #f8fafc; font-weight: 600;" required>
                            <small id="po_no_help" style="color: #64748b; font-size: 0.725rem;">Nomor urut otomatis sistem pengadaan.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="po_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal PO <span style="color:#ef4444;">*</span></label>
                            <input type="date" id="po_tgl" name="po_tgl" value="{{ old('po_tgl', date('Y-m-d')) }}" class="form-control" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="tgl_estimasi_datang" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Estimasi Tanggal Tiba</label>
                            <input type="date" id="tgl_estimasi_datang" name="tgl_estimasi_datang" value="{{ old('tgl_estimasi_datang') }}" class="form-control">
                            <small style="color: #64748b; font-size: 0.725rem;">Target kedatangan armada pengiriman di pabrik.</small>
                        </div>
                    </div>

                    {{-- BARIS 2: SUPPLIER MITRA (AUTOCOMPLETE CEPAT) & GUDANG TUJUAN MASUK --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; align-items: start;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <label id="lblSupplierHeader" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0;">
                                    Supplier Mitra <span id="reqSupplierHeader" style="color:#ef4444;">*</span>
                                </label>
                                <a href="javascript:void(0)" onclick="openSupplierModal()" style="font-size: 0.75rem; color: #0284c7; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;" title="Buka Daftar Supplier Lengkap (Tabel Excel Grid)">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Daftar Supplier ({{ $supplierList->count() }})
                                </a>
                            </div>

                            {{-- Card Tampilan Supplier Terpilih --}}
                            <div id="selected_supplier_card" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.45rem 0.75rem; justify-content: space-between; align-items: center; gap: 0.75rem; min-height: 38px; box-sizing: border-box;">
                                <div style="display: flex; align-items: center; gap: 0.55rem; min-width: 0;">
                                    <div style="background: #22c55e; color: #fff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; flex-shrink: 0; font-weight: 700;">✓</div>
                                    <div style="min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                            <strong id="disp_sup_name" style="color: #14532d; font-size: 0.85rem;"></strong>
                                            <span id="disp_sup_code" class="badge" style="background: #dcfce7; color: #166534; font-size: 0.7rem; font-weight: 700; font-family: monospace;"></span>
                                            <span id="disp_sup_cat_badge"></span>
                                        </div>
                                        <div id="disp_sup_contact" style="font-size: 0.7rem; color: #15803d; margin-top: 0.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelectedSupplier()" class="btn btn-sm" style="background: #ffffff; border: 1px solid #86efac; color: #15803d; font-size: 0.725rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 4px; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
                                    Ganti
                                </button>
                            </div>

                            {{-- Search Box Tunggal Bersih (Smart Autocomplete) --}}
                            <div id="supplier_search_wrapper" style="position: relative;">
                                <div style="position: relative; width: 100%;">
                                    <input type="text" id="supplier_search_input" class="form-control" placeholder="Cari nama atau kode supplier (misal: Sawit, Tani, SUP-01)..." autocomplete="off" style="padding-left: 2.2rem !important; padding-right: 2rem !important; height: 38px; font-size: 0.85rem; border-radius: 6px; width: 100%; box-sizing: border-box;">
                                    <span style="position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.65rem;">
                                        ▼
                                    </span>
                                </div>

                                {{-- Dropdown Container Autocomplete --}}
                                <div id="supplier_dropdown_list" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.06); max-height: 320px; overflow-y: auto; z-index: 1050;">
                                    {{-- Diisi dinamis via JS --}}
                                </div>
                            </div>

                            {{-- Hidden select yang disinkronkan untuk form POST --}}
                            <select id="supplier_id" name="supplier_id" style="display: none;" required>
                                <option value="">-- Pilih Supplier Mitra --</option>
                                @foreach ($supplierList as $sup)
                                    <option value="{{ $sup->supplier_id }}"
                                        {{ old('supplier_id') == $sup->supplier_id ? 'selected' : '' }}>
                                        {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Ketik untuk memilih cepat, atau klik <strong>Daftar Supplier</strong> di atas untuk melihat tabel lengkap.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem; display: flex; align-items: center; justify-content: space-between;">
                                <span>Gudang Tujuan Masuk <span style="color:#ef4444;">*</span></span>
                                @if (!empty($isGudangLocked))
                                    <span class="badge" style="background:#dcfce7; color:#15803d; font-size:0.7rem;">
                                        Terkunci (Lokasi Anda)
                                    </span>
                                @endif
                            </label>
                            @if (!empty($isGudangLocked))
                                <input type="hidden" name="gudang_id" value="{{ $assignedGudangId }}">
                                <select id="gudang_id" class="form-control" disabled style="background: #f8fafc; color: #1e293b; font-weight: 600; cursor: not-allowed; height: 38px; border-radius: 6px; font-size: 0.85rem;">
                                    @foreach ($gudangList as $gdg)
                                        <option value="{{ $gdg->gudang_id }}" {{ $assignedGudangId == $gdg->gudang_id ? 'selected' : '' }}>
                                            {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <select id="gudang_id" name="gudang_id" class="form-control" required style="height: 38px; border-radius: 6px; font-size: 0.85rem; padding-right: 2rem;">
                                    <option value="">-- Pilih Gudang Tujuan Masuk --</option>
                                    @foreach ($gudangList as $gdg)
                                        <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $assignedGudangId ?? '') == $gdg->gudang_id ? 'selected' : '' }}>
                                            {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Lokasi gudang fisik tempat komoditas akan dibongkar.</small>
                        </div>
                    </div>

                    {{-- BARIS 3: CATATAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Instruksi / Catatan Pengadaan</label>
                        <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control" placeholder="Contoh: Estimasi pengiriman hari Jumat jam 08.00 pagi, singkong kadar air standar pabrik.">{{ old('catatan_txt') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: RINCIAN ITEM BARANG (DETAIL TABLE - EXCEL STYLE ELEGAN DENGAN FILTER BAHAN BAKU / PENOLONG) --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden;">
                <div class="card-header" style="background: #ffffff; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">2. Rincian Barang yang Dipesan</strong>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                            Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> pada kuantitas/harga untuk tambah baris
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        @if (!empty($belowMinimumList) && $belowMinimumList->count() > 0)
                            <button type="button" onclick="toggleSafetyStockDrawer()" id="btnToggleSafetyStock" class="btn btn-sm" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; font-size: 0.775rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.65rem; border-radius: 5px; cursor: pointer;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span id="safetyStockCountText">Peringatan Stok Kritis ({{ $belowMinimumList->count() }})</span>
                                <svg id="chevronSafetyStock" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        @endif
                        <button type="button" onclick="addRow(true)" class="btn btn-primary btn-sm" style="font-weight: 600; background: #0284c7; padding: 0.35rem 0.85rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Baris
                        </button>
                    </div>
                </div>

                {{-- FILTER BAR CEPAT UNTUK KATEGORI BARANG & REKANAN SUPPLIER --}}
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.55rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.65rem;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                        <span style="font-size: 0.725rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                            Kategori:
                        </span>
                        <div class="category-segmented-control" role="group" aria-label="Filter Kategori Barang">
                            <button type="button" class="category-segment-btn active" data-category="ALL" onclick="filterBarangCategory('ALL', this)">
                                <span>Semua</span>
                                <span class="category-segment-count">{{ $barangList->count() }}</span>
                            </button>
                            <button type="button" class="category-segment-btn" data-category="BB" onclick="filterBarangCategory('BB', this)">
                                <span>Bahan Baku</span>
                                <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']))->count() }}</span>
                            </button>
                            <button type="button" class="category-segment-btn" data-category="BUMBU" onclick="filterBarangCategory('BUMBU', this)">
                                <span>Bumbu &amp; Penolong</span>
                                <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))->count() }}</span>
                            </button>
                            <button type="button" class="category-segment-btn" data-category="PACK" onclick="filterBarangCategory('PACK', this)">
                                <span>Kemasan</span>
                                <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)))->count() }}</span>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        {{-- Quick Search Supplier Khusus Tabel (Aktif pada mode multi-supplier) --}}
                        <div id="table_supplier_search_box" style="display: none; align-items: center; gap: 0.35rem;">
                            <div style="position: relative;">
                                <input type="text" id="table_sup_search_input" oninput="filterTableSuppliersBySearch(this.value)" placeholder="Cari supplier di tabel..." class="form-control" style="height: 28px; font-size: 0.75rem; padding: 0.2rem 0.5rem 0.2rem 1.6rem !important; width: 190px; border-radius: 4px; border: 1px solid #cbd5e1;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position: absolute; left: 0.45rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <button type="button" onclick="clearTableSupSearch()" class="btn btn-sm" style="padding: 0.15rem 0.5rem; font-size: 0.7rem; background: #e2e8f0; color: #475569; border: none; border-radius: 4px; height: 28px; cursor: pointer; font-weight: 600;">
                                Reset
                            </button>
                        </div>

                        <div style="font-size: 0.725rem; color: #0284c7; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Barang &amp; Supplier tersaring otomatis</span>
                        </div>
                    </div>
                </div>

                {{-- COLLAPSIBLE DRAWER: REKOMENDASI SAFETY STOCK --}}
                @if (!empty($belowMinimumList) && $belowMinimumList->count() > 0)
                    <div id="drawerSafetyStock" style="display: none; background: #fffdf5; border-bottom: 2px dashed #fde68a; padding: 0.85rem 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <strong style="font-size: 0.825rem; color: #92400e; display: flex; align-items: center; gap: 0.35rem;">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Daftar Komoditas Menipis di Bawah Batas Safety Stock Pabrik
                                </strong>
                                <span style="font-size: 0.75rem; color: #b45309;">
                                    Klik tombol <span style="font-weight: 700;">+ Masukkan</span> untuk langsung menambahkan barang &amp; defisit kuantitas ke tabel pesanan:
                                </span>
                            </div>
                            <button type="button" id="btnAddAllBelowMinimum" onclick="addAllBelowMinimumItems()" class="btn btn-sm" style="background: #d97706; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 0.25rem 0.65rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Masukkan Semua (<span id="safetyStockAddAllCount">{{ $belowMinimumList->count() }}</span> Bahan)</span>
                            </button>
                        </div>

                        <div style="background: #ffffff; border: 1px solid #fed7aa; border-radius: 6px; overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                <thead>
                                    <tr style="background: #fff7ed; border-bottom: 1px solid #fed7aa; color: #9a3412; font-weight: 700; font-size: 0.75rem;">
                                        <th style="padding: 0.45rem 0.75rem; text-align: left;">Nama Komoditas / Bahan Baku</th>
                                        <th style="padding: 0.45rem 0.75rem; text-align: right;">Stok Fisik Gudang</th>
                                        <th style="padding: 0.45rem 0.75rem; text-align: right;">Batas Safety Stock</th>
                                        <th style="padding: 0.45rem 0.75rem; text-align: right; color: #c2410c;">Defisit Kebutuhan</th>
                                        <th style="padding: 0.45rem 0.75rem; text-align: center; width: 120px;">Aksi Cepat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($belowMinimumList as $bm)
                                        @php
                                            $cat = 'OTHER';
                                            $jenisCd = $bm->jenisBarang?->jenis_barang_cd ?? '';
                                            if (in_array($jenisCd, ['RAW', 'BB'])) {
                                                $cat = 'BB';
                                            } elseif (in_array($jenisCd, ['PACK']) || (in_array($jenisCd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $bm->barang_nm))) {
                                                $cat = 'PACK';
                                            } elseif (in_array($jenisCd, ['SUPP', 'BUMBU', 'BP'])) {
                                                $cat = 'BUMBU';
                                            }
                                            $deficit = max(1, (float)($bm->batas_minimum_qty - $bm->current_stock));
                                        @endphp
                                        <tr class="safety-stock-row" data-category="{{ $cat }}" style="border-bottom: 1px solid #ffedd5;">
                                            <td style="padding: 0.45rem 0.75rem; font-weight: 600; color: #1e293b;">
                                                {{ $bm->barang_nm }}
                                                <span style="color: #64748b; font-size: 0.725rem; font-weight: normal;">({{ $bm->barang_cd }})</span>
                                                @if($cat === 'BB')
                                                    <span class="badge" style="background:#dcfce7; color:#166534; font-size:0.65rem; margin-left:0.25rem;">Bahan Baku</span>
                                                @elseif($cat === 'BUMBU')
                                                    <span class="badge" style="background:#fef3c7; color:#92400e; font-size:0.65rem; margin-left:0.25rem;">Bumbu &amp; Penolong</span>
                                                @elseif($cat === 'PACK')
                                                    <span class="badge" style="background:#f1f5f9; color:#475569; font-size:0.65rem; margin-left:0.25rem;">Kemasan</span>
                                                @endif
                                            </td>
                                            <td style="padding: 0.45rem 0.75rem; text-align: right; color: #64748b;">
                                                {{ number_format($bm->current_stock, 0) }} {{ $bm->satuanDasar?->satuan_cd }}
                                            </td>
                                            <td style="padding: 0.45rem 0.75rem; text-align: right; color: #64748b;">
                                                {{ number_format($bm->batas_minimum_qty, 0) }} {{ $bm->satuanDasar?->satuan_cd }}
                                            </td>
                                            <td style="padding: 0.45rem 0.75rem; text-align: right; font-weight: 700; color: #b45309;">
                                                {{ number_format($deficit, 0) }} {{ $bm->satuanDasar?->satuan_cd }}
                                            </td>
                                            <td style="padding: 0.45rem 0.75rem; text-align: center;">
                                                <button type="button" 
                                                        onclick="addBelowMinimumItem({{ $bm->barang_id }}, '{{ addslashes($bm->barang_nm) }}', '{{ $bm->satuanDasar?->satuan_nm ?? '-' }}', {{ (float)($bm->harga_beli_standar ?? 0) }}, {{ $deficit }})"
                                                        class="btn btn-sm btn-add-safety"
                                                        style="background: #f59e0b; color: #ffffff; border: none; padding: 0.2rem 0.55rem; font-size: 0.725rem; font-weight: 700; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    + Masukkan
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div style="overflow-x: auto;">
                    <table class="excel-grid-table" id="tableItems">
                        <thead>
                            <tr>
                                <th style="width: 32px; text-align: center;">No</th>
                                <th style="min-width: 230px; text-align: left;">Nama Komoditas / Bahan Baku <span style="color:#f87171;">*</span></th>
                                <th class="col-supplier" style="display: none; min-width: 200px; text-align: left;">
                                    Supplier Mitra <span style="color:#f87171;">*</span>
                                </th>
                                <th style="width: 70px; text-align: center;">Satuan</th>
                                <th style="width: 95px; text-align: right;">Kuantitas <span style="color:#f87171;">*</span></th>
                                <th style="width: 110px; text-align: right;">@Harga Satuan</th>
                                <th style="width: 65px; text-align: right;" title="Diskon dagang per item (%)">Disc (%)</th>
                                <th style="width: 90px; text-align: right;" title="Potongan nominal item (Rp)">Potongan</th>
                                <th style="width: 85px; text-align: center;" title="Pajak Pertambahan Nilai">PPN</th>
                                <th style="width: 115px; text-align: right; background: #0f172a; color: #ffffff;" title="Subtotal bersih setelah diskon, potongan & PPN">Subtotal</th>
                                <th style="width: 35px; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            {{-- Row pertama bawaan --}}
                            <tr class="item-row" data-index="0">
                                <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">1</td>
                                <td>
                                    <select name="items[0][barang_id]" class="form-control item-barang" onchange="updateRowSatuan(this)" required>
                                        <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                                        
                                        <optgroup label="BAHAN BAKU (RAW MATERIAL)" class="grp-bb">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                                                <option value="{{ $b->barang_id }}" data-category="BB" data-code="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                                                <option value="{{ $b->barang_id }}" data-category="BUMBU" data-code="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                                                <option value="{{ $b->barang_id }}" data-category="PACK" data-code="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <div class="row-barang-hint" style="font-size: 0.68rem; margin-top: 2px; color: #64748b; display: none;"></div>
                                </td>
                                <td class="col-supplier" style="display: none;">
                                    <select name="items[0][supplier_id]" class="form-control item-supplier" onchange="calculateGrandTotal()">
                                        {!! $supplierOptionsHtml !!}
                                    </select>
                                    <div class="row-supplier-hint" style="font-size: 0.68rem; margin-top: 2px; display: none;"></div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="row-satuan" style="font-weight: 600; color: #475569;">-</span>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0.0001" name="items[0][pesan_qty]" class="form-control item-qty" value="1" oninput="calculateSubtotal(this)" style="text-align: right; font-weight: 700;" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" class="form-control item-harga" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right; font-weight: 600;">
                                </td>
                                <td>
                                    <input type="number" step="0.1" min="0" max="100" name="items[0][diskon_persen]" class="form-control item-diskon" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[0][potongan_nominal]" class="form-control item-potongan" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
                                </td>
                                <td>
                                    <select name="items[0][ppn_tipe]" class="form-control item-ppn-tipe" onchange="calculateSubtotal(this)" style="font-size: 0.775rem; font-weight: 600;">
                                        <option value="NON_PPN">Non (0%)</option>
                                        <option value="PPN_11">PPN 11%</option>
                                    </select>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.85rem; font-family: monospace;" class="row-subtotal">
                                    Rp 0
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="padding: 0.15rem 0.4rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                                <td id="tfootQtyColspan" colspan="3" style="text-align: right; padding: 0.65rem 0.75rem; color: #334155; font-size: 0.8rem;">Total:</td>
                                <td id="totalQtyDisplay" style="padding: 0.65rem 0.5rem; text-align: right; color: #0284c7; font-size: 0.875rem; font-weight: 700;">1.00</td>
                                <td></td>
                                <td id="totalDiskonDisplay" style="padding: 0.65rem 0.35rem; color: #d97706; font-size: 0.8rem; text-align: right; font-family: monospace;" title="Akumulasi Diskon Item">-</td>
                                <td id="totalPotonganDisplay" style="padding: 0.65rem 0.35rem; color: #dc2626; font-size: 0.8rem; text-align: right; font-family: monospace;" title="Akumulasi Potongan">-</td>
                                <td id="totalPpnDisplay" style="padding: 0.65rem 0.35rem; color: #0369a1; font-size: 0.8rem; text-align: center; font-family: monospace;" title="Akumulasi PPN 11%">-</td>
                                <td id="grandTotalDisplay" style="text-align: right; padding: 0.65rem 0.5rem; font-size: 0.95rem; color: #0f172a; font-family: monospace;">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('gudang.po.partials.item-row-template')
                </div>
            </div>

        </div>

        <div class="sticky-action-sidebar" style="position: sticky; top: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
            
            {{-- KARTU SUMMARY & ACTION UTAMA --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); overflow: hidden;">
                <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.875rem 1.25rem;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #94a3b8;">
                        Ringkasan Dokumen
                    </div>
                    <strong style="color: #ffffff; font-size: 1.05rem;">Estimasi Nilai PO</strong>
                </div>

                <div style="padding: 1.25rem;">
                    {{-- TOTAL NOMINAL DISPLAY BESAR --}}
                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Total Estimasi PO:</span>
                        <div id="sideGrandTotal" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem; font-family: monospace; letter-spacing: -0.02em;">
                            Rp 0
                        </div>
                    </div>

                    {{-- STATISTIK ITEM & KUANTITAS & BREAKDOWN HARGA --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.45rem; font-size: 0.825rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Jumlah Item:</span>
                            <strong style="color: #0f172a;"><span id="sideTotalItems">1</span> Jenis Bahan</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 0.35rem; border-top: 1px dashed #e2e8f0;">
                            <span style="color: #64748b;">Total Kuantitas:</span>
                            <strong style="color: #0284c7;"><span id="sideTotalQty">1.00</span> Unit</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 0.35rem; border-top: 1px dashed #e2e8f0;">
                            <span style="color: #64748b;">Subtotal Bruto:</span>
                            <span id="sideSubtotalBruto" style="font-weight: 600; font-family: monospace; color: #334155;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Diskon Item:</span>
                            <span id="sideTotalDiskon" style="font-weight: 600; font-family: monospace; color: #d97706;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Potongan Harga:</span>
                            <span id="sideTotalPotongan" style="font-weight: 600; font-family: monospace; color: #dc2626;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">PPN (11%):</span>
                            <span id="sideTotalPpn" style="font-weight: 600; font-family: monospace; color: #0284c7;">Rp 0</span>
                        </div>
                        <div id="sideAutoSplitBox" style="display: none; padding-top: 0.5rem; border-top: 1px dashed #cbd5e1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <span style="color: #64748b; font-size: 0.75rem;">Estimasi Penerbitan:</span>
                                <span id="sidePoCountBadge" class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 0.725rem;">1 Dokumen PO</span>
                            </div>
                            <div id="sidePoSupplierList" style="font-size: 0.725rem; color: #334155; line-height: 1.4; max-height: 120px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL UTAMA: SIMPAN & TERBITKAN PO (TIDAK PERNAH TENGGELAM) --}}
                    <button type="submit" id="btnSubmitPo" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 700; background: #059669; justify-content: center; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.25); display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="btnSubmitPoText">Simpan &amp; Terbitkan PO</span>
                    </button>

                    <a href="{{ route('gudang.po.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 0.65rem; font-size: 0.85rem; padding: 0.5rem;">
                        Batal &amp; Kembali ke Daftar
                    </a>
                </div>
            </div>

            {{-- KARTU PANDUAN CEPAT OPERATOR --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.85rem;">Fitur Pengadaan Cepat</strong>
                </div>
                <div style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #0284c7; font-weight: 700;">&bull;</span>
                        <span><strong>Cari Supplier Cepat:</strong> Ketik nama/kode supplier atau klik tombol <em>Daftar Supplier</em> untuk membuka tabel Excel master supplier.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #0284c7; font-weight: 700;">&bull;</span>
                        <span><strong>Filter Barang Otomatis:</strong> Pilih kategori <em>Bahan Baku Mentah</em>, <em>Bumbu</em>, atau <em>Kemasan</em> untuk menyaring item barang pesanan.</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="color: #0284c7; font-weight: 700;">&bull;</span>
                        <span>Tekan <kbd style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd> pada kuantitas/harga untuk langsung membuat baris baru.</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
@include('gudang.po.partials.modal-supplier-picker')

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
        ];
    })->values();
@endphp

@push('scripts')
<script>
    window.suppliersData = @json($suppliersJson);
    @if(old('supplier_id'))
        window.oldSupplierId = {{ old('supplier_id') }};
    @endif
    @if(old('items.0.supplier_id') || old('items.1.supplier_id'))
        window.oldHasMultiSupplier = true;
    @endif
</script>
<script src="{{ asset('js/gudang/po/po-create.js') }}"></script>
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
