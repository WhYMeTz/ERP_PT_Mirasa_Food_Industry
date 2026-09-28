@extends('layouts.app')

@section('title', 'Buat Purchase Order Baru - ERP PT Mirasa')

@section('content')
<style>
    .order-station-grid {
        display: grid;
        grid-template-columns: 2.3fr 1fr;
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 2rem;
    }
    @media (max-width: 1100px) {
        .order-station-grid {
            grid-template-columns: 1fr;
        }
        .sticky-action-sidebar {
            position: static !important;
            top: auto !important;
        }
    }
    .excel-grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }
    .excel-grid-table th {
        background: #f8fafc;
        color: #334155;
        font-weight: 700;
        padding: 0.6rem 0.5rem;
        border: 1px solid #cbd5e1;
        font-size: 0.775rem;
        letter-spacing: 0.02em;
    }
    .excel-grid-table td {
        padding: 0.35rem 0.45rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        vertical-align: middle;
    }
    .excel-grid-table tr:nth-child(even) td {
        background: #fafafa;
    }
    .excel-grid-table tr:hover td {
        background: #f0f9ff;
    }
    .excel-grid-table .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 0.35rem 0.5rem !important;
        font-size: 0.825rem !important;
        height: 32px;
        box-sizing: border-box;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .excel-grid-table .form-control:focus {
        border-color: #0284c7 !important;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25) !important;
        background: #ffffff !important;
    }

    /* Filter Chips Barang */
    .barang-filter-chip {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 0.725rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        line-height: 1.2;
    }
    .barang-filter-chip:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .barang-filter-chip.active {
        background: #0284c7;
        border-color: #0284c7;
        color: #ffffff;
        box-shadow: 0 1px 3px rgba(2, 132, 199, 0.3);
    }

    /* Supplier Modal Tabs (Clean Enterprise Style) */
    .sup-modal-tab {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.3rem 0.65rem;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.12s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }
    .sup-modal-tab:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .sup-modal-tab.active {
        background: #0284c7;
        border-color: #0284c7;
        color: #ffffff;
        box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);
    }

    /* Supplier Dropdown Items */
    .sup-option-item {
        padding: 0.65rem 0.95rem;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.1s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sup-option-item:hover, .sup-option-item.highlighted {
        background: #f0f9ff;
    }
    .sup-option-item:last-child {
        border-bottom: none;
    }

    /* Supplier Modal Table Row (Excel Table Standard) */
    .sup-modal-row {
        cursor: pointer;
        transition: background 0.08s ease;
    }
    .sup-modal-row:hover td {
        background: #f0f9ff !important;
    }

    /* PO Mode Switcher (Konsep 1: Auto-Split PO) */
    .po-mode-btn {
        background: transparent;
        border: none;
        padding: 0.4rem 0.95rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 5px;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .po-mode-btn.active {
        background: #ffffff;
        color: #0f172a;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .po-mode-btn:hover:not(.active) {
        color: #1e293b;
    }
</style>

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
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.6rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.65rem;">
                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.03em; margin-right: 0.25rem;">
                            Filter Kategori:
                        </span>
                        <button type="button" class="barang-filter-chip active" data-category="ALL" onclick="filterBarangCategory('ALL', this)">
                            Semua (36 Bahan / 60 Rekanan)
                        </button>
                        <button type="button" class="barang-filter-chip" data-category="BB" onclick="filterBarangCategory('BB', this)">
                            🌾 Bahan Baku ({{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']))->count() }})
                        </button>
                        <button type="button" class="barang-filter-chip" data-category="BUMBU" onclick="filterBarangCategory('BUMBU', this)">
                            🧂 Bumbu &amp; Penolong ({{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))->count() }})
                        </button>
                        <button type="button" class="barang-filter-chip" data-category="PACK" onclick="filterBarangCategory('PACK', this)">
                            📦 Kemasan &amp; Packaging ({{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)))->count() }})
                        </button>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        {{-- Quick Search Supplier Khusus Tabel (Aktif pada mode multi-supplier) --}}
                        <div id="table_supplier_search_box" style="display: none; align-items: center; gap: 0.35rem;">
                            <div style="position: relative;">
                                <input type="text" id="table_sup_search_input" oninput="filterTableSuppliersBySearch(this.value)" placeholder="🔍 Filter supplier di tabel..." class="form-control" style="height: 28px; font-size: 0.75rem; padding: 0.2rem 0.5rem 0.2rem 1.6rem !important; width: 190px; border-radius: 4px; border: 1px solid #cbd5e1;">
                                <span style="position: absolute; left: 0.45rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.725rem;">🔍</span>
                            </div>
                            <button type="button" onclick="clearTableSupSearch()" class="btn btn-sm" style="padding: 0.15rem 0.5rem; font-size: 0.7rem; background: #e2e8f0; color: #475569; border: none; border-radius: 4px; height: 28px; cursor: pointer; font-weight: 600;">
                                Reset
                            </button>
                        </div>

                        <div style="font-size: 0.725rem; color: #0284c7; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Barang &amp; Supplier otomatis tersaring sesuai kategori</span>
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
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="min-width: 260px; text-align: left;">Nama Komoditas / Bahan Baku <span style="color:#ef4444;">*</span></th>
                                <th class="col-supplier" style="display: none; min-width: 220px; text-align: left;">
                                    Supplier Mitra <span style="color:#ef4444;">*</span>
                                </th>
                                <th style="width: 90px; text-align: center;">Satuan</th>
                                <th style="width: 120px; text-align: right;">Kuantitas <span style="color:#ef4444;">*</span></th>
                                <th style="width: 150px; text-align: right;">Harga Satuan (Rp)</th>
                                <th style="width: 150px; text-align: right;">Subtotal (Rp)</th>
                                <th style="width: 45px; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            {{-- Row pertama bawaan --}}
                            <tr class="item-row" data-index="0">
                                <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">1</td>
                                <td>
                                    <select name="items[0][barang_id]" class="form-control item-barang" onchange="updateRowSatuan(this)" required>
                                        <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                                        
                                        <optgroup label="🌾 BAHAN BAKU (KOMODITAS UTAMA)" class="grp-bb">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                                                <option value="{{ $b->barang_id }}" data-category="BB" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="🧂 BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                                                <option value="{{ $b->barang_id }}" data-category="BUMBU" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="📦 BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                                            @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                                                <option value="{{ $b->barang_id }}" data-category="PACK" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
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
                                    <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" class="form-control item-harga" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem;" class="row-subtotal">
                                    Rp 0
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="padding: 0.15rem 0.4rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                                <td id="tfootQtyColspan" colspan="3" style="text-align: right; padding: 0.75rem 1rem; color: #334155; font-size: 0.85rem;">Total Kuantitas:</td>
                                <td id="totalQtyDisplay" style="padding: 0.75rem 0.5rem; text-align: right; color: #0284c7; font-size: 0.95rem;">1.00</td>
                                <td style="text-align: right; padding: 0.75rem 1rem; color: #334155; font-size: 0.85rem;">Grand Total Pesanan:</td>
                                <td id="grandTotalDisplay" style="text-align: right; padding: 0.75rem 0.5rem; font-size: 1.05rem; color: #0284c7;">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
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
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Total Pembelian:</span>
                        <div id="sideGrandTotal" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem; font-family: monospace; letter-spacing: -0.02em;">
                            Rp 0
                        </div>
                    </div>

                    {{-- STATISTIK ITEM & KUANTITAS --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Jumlah Item:</span>
                            <strong style="color: #0f172a;"><span id="sideTotalItems">1</span> Jenis Bahan</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 0.35rem; border-top: 1px dashed #e2e8f0;">
                            <span style="color: #64748b;">Total Volume Kuantitas:</span>
                            <strong style="color: #0284c7;"><span id="sideTotalQty">1.00</span> Unit</strong>
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
{{-- ========================================================================= --}}
{{-- MODAL PILIH MASTER SUPPLIER (STANDAR TABEL EXCEL ERP)                     --}}
{{-- ========================================================================= --}}
<div id="modalPilihSupplier" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 960px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem;">
            <div>
                <h2 class="modal-title" style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Daftar Supplier</h2>
                <p style="color: #64748b; font-size: 0.8rem; margin: 0.2rem 0 0 0;">Pilih supplier rekanan untuk dokumen pesanan pembelian (PO).</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalPilihSupplier')">&times;</button>
        </div>

        {{-- Toolbar Pencarian & Filter Tab Kategori --}}
        <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div style="position: relative; margin-bottom: 0.6rem;">
                <input type="text" id="modal_supplier_search" class="form-control" placeholder="Cari berdasarkan nama supplier, kode, no telepon, atau alamat..." style="padding-left: 2.1rem !important; height: 36px; font-size: 0.85rem;" oninput="modalCurrentPage = 1; renderSupplierModalTable();">
                <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            {{-- Tabs Filter Kategori (Standar ERP Bersih) --}}
            <div style="display: flex; gap: 0.35rem; overflow-x: auto; padding-bottom: 0.15rem;">
                <button type="button" class="sup-modal-tab active" data-tab="ALL" onclick="filterSupplierModalTab('ALL', this)">
                    Semua Supplier ({{ $supplierList->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="RAW" onclick="filterSupplierModalTab('RAW', this)">
                    Bahan Baku ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'RAW')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="BP" onclick="filterSupplierModalTab('BP', this)">
                    Penolong Industri ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BP')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="BUMBU" onclick="filterSupplierModalTab('BUMBU', this)">
                    Bumbu &amp; Perasa ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BUMBU')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="KEMASAN" onclick="filterSupplierModalTab('KEMASAN', this)">
                    Kemasan &amp; Karton ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'KEMASAN')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="SPAREPART" onclick="filterSupplierModalTab('SPAREPART', this)">
                    Suku Cadang ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'SPAREPART')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="UMUM" onclick="filterSupplierModalTab('UMUM', this)">
                    Umum &amp; Ekspedisi ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'UMUM')->count() }})
                </button>
            </div>
        </div>

        {{-- Tabel Standar Excel Grid --}}
        <div style="flex: 1; overflow-y: auto; padding: 0.75rem 1.25rem; min-height: 250px; max-height: 52vh;">
            <table class="excel-grid-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center; position: sticky; top: 0; z-index: 5;">No</th>
                        <th style="width: 110px; text-align: left; position: sticky; top: 0; z-index: 5;">Kode Supplier</th>
                        <th style="text-align: left; position: sticky; top: 0; z-index: 5;">Nama Supplier</th>
                        <th style="width: 140px; text-align: left; position: sticky; top: 0; z-index: 5;">Jenis Kategori</th>
                        <th style="width: 130px; text-align: left; position: sticky; top: 0; z-index: 5;">No. Kontak / Telp</th>
                        <th style="text-align: left; position: sticky; top: 0; z-index: 5;">Alamat / Lokasi</th>
                        <th style="width: 75px; text-align: center; position: sticky; top: 0; z-index: 5;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="supplier_modal_tbody">
                    {{-- Diisi secara dinamis via JS --}}
                </tbody>
            </table>
        </div>

        {{-- Footer Modal dengan Navigasi Halaman (Pagination) --}}
        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.65rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <span id="modal_supplier_count_text" style="font-size: 0.8rem; color: #64748b;">Menampilkan 0 supplier</span>
            
            <div id="modal_supplier_pagination" style="display: flex; align-items: center; gap: 0.35rem;">
                <button type="button" id="modal_prev_btn" class="btn btn-secondary btn-sm" onclick="changeSupplierModalPage(-1)" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                    &larr; Sebelumnya
                </button>
                <span id="modal_page_info" style="font-size: 0.75rem; font-weight: 600; color: #334155; padding: 0 0.35rem;">
                    Hal 1 dari 1
                </span>
                <button type="button" id="modal_next_btn" class="btn btn-secondary btn-sm" onclick="changeSupplierModalPage(1)" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                    Selanjutnya &rarr;
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalPilihSupplier')">Tutup</button>
        </div>
    </div>
</div>

<script>
    // =========================================================================
    // 1. DATA SUPPLIER & FILTER PENCARIAN
    // =========================================================================
    const suppliersData = [
        @foreach ($supplierList as $sup)
            {
                id: {{ $sup->supplier_id }},
                code: {!! json_encode($sup->supplier_cd) !!},
                name: {!! json_encode($sup->supplier_nm) !!},
                category: {!! json_encode($sup->jenisSupplier?->jenis_supplier_cd ?? 'OTHER') !!},
                categoryName: {!! json_encode($sup->jenisSupplier?->jenis_supplier_nm ?? 'Lainnya') !!},
                contact: {!! json_encode($sup->kontak_no ?? '-') !!},
                address: {!! json_encode($sup->alamat_txt ?? '') !!}
            },
        @endforeach
    ];

    let activeModalSupplierTab = 'ALL';
    let highlightedSupIndex = -1;

    const supSearchInput = document.getElementById('supplier_search_input');
    const supDropdownList = document.getElementById('supplier_dropdown_list');
    const supHiddenSelect = document.getElementById('supplier_id');
    const selectedSupCard = document.getElementById('selected_supplier_card');
    const supSearchWrapper = document.getElementById('supplier_search_wrapper');
    const modalSearchInput = document.getElementById('modal_supplier_search');
    const modalTbody = document.getElementById('supplier_modal_tbody');
    const modalCountText = document.getElementById('modal_supplier_count_text');

    function getCategoryBadge(category, categoryName) {
        let catBg = '#f1f5f9';
        let catColor = '#334155';
        let label = categoryName || 'Lainnya';

        if (category === 'RAW') {
            catBg = '#dcfce7';
            catColor = '#166534';
            label = 'Bahan Baku';
        } else if (category === 'BP') {
            catBg = '#e0e7ff';
            catColor = '#3730a3';
            label = 'Penolong Industri';
        } else if (category === 'BUMBU') {
            catBg = '#fef3c7';
            catColor = '#92400e';
            label = 'Bumbu & Perasa';
        } else if (category === 'KEMASAN') {
            catBg = '#f3e8ff';
            catColor = '#6b21a8';
            label = 'Kemasan & Karton';
        } else if (category === 'SPAREPART') {
            catBg = '#ffedd5';
            catColor = '#9a3412';
            label = 'Suku Cadang';
        } else if (category === 'UMUM') {
            catBg = '#f1f5f9';
            catColor = '#334155';
            label = 'Umum & Jasa';
        }

        return {
            bg: catBg,
            color: catColor,
            label: label,
            html: `<span class="badge" style="background:${catBg}; color:${catColor}; font-size:0.725rem; font-weight:600; padding: 0.15rem 0.45rem; border-radius: 4px; border: 1px solid rgba(0,0,0,0.06); white-space:nowrap;">${escapeHtml(label)}</span>`
        };
    }

    let modalCurrentPage = 1;
    const modalPerPage = 8;

    function changeSupplierModalPage(delta) {
        modalCurrentPage += delta;
        renderSupplierModalTable();
    }

    // Modal Handlers
    function openSupplierModal() {
        if (supDropdownList) supDropdownList.style.display = 'none';
        openModal('modalPilihSupplier');
        // Teruskan teks yang sudah diketik ke modal agar sinkron
        const currentTyped = supSearchInput ? supSearchInput.value.trim() : '';
        modalSearchInput.value = currentTyped;
        modalCurrentPage = 1;
        renderSupplierModalTable();
        setTimeout(() => {
            modalSearchInput.focus();
            modalSearchInput.select();
        }, 100);
    }

    function closeSupplierModal() {
        closeModal('modalPilihSupplier');
    }

    function filterSupplierModalTab(tab, btn) {
        activeModalSupplierTab = tab;
        modalCurrentPage = 1;
        document.querySelectorAll('.sup-modal-tab').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        renderSupplierModalTable();
    }

    function renderSupplierModalTable() {
        const query = modalSearchInput.value.trim().toLowerCase();
        const filtered = suppliersData.filter(s => {
            const matchesTab = (activeModalSupplierTab === 'ALL' || s.category === activeModalSupplierTab);
            const matchesText = !query || 
                (s.name && s.name.toLowerCase().includes(query)) || 
                (s.code && s.code.toLowerCase().includes(query)) || 
                (s.contact && s.contact.toLowerCase().includes(query)) || 
                (s.address && s.address.toLowerCase().includes(query)) ||
                (s.categoryName && s.categoryName.toLowerCase().includes(query));
            return matchesTab && matchesText;
        });

        const totalItems = filtered.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / modalPerPage));
        if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;
        if (modalCurrentPage < 1) modalCurrentPage = 1;

        const startIdx = (modalCurrentPage - 1) * modalPerPage;
        const endIdx = Math.min(startIdx + modalPerPage, totalItems);
        const pageItems = filtered.slice(startIdx, endIdx);

        modalCountText.innerText = totalItems > 0 
            ? `Menampilkan ${startIdx + 1}–${endIdx} dari ${totalItems} supplier`
            : `Menampilkan 0 supplier`;

        const paginationDiv = document.getElementById('modal_supplier_pagination');
        if (paginationDiv) {
            paginationDiv.style.display = totalPages > 1 ? 'flex' : 'none';
            document.getElementById('modal_page_info').innerText = `Hal ${modalCurrentPage} dari ${totalPages}`;
            const prevBtn = document.getElementById('modal_prev_btn');
            const nextBtn = document.getElementById('modal_next_btn');
            prevBtn.disabled = (modalCurrentPage <= 1);
            nextBtn.disabled = (modalCurrentPage >= totalPages);
            prevBtn.style.opacity = (modalCurrentPage <= 1) ? '0.4' : '1';
            prevBtn.style.cursor = (modalCurrentPage <= 1) ? 'not-allowed' : 'pointer';
            nextBtn.style.opacity = (modalCurrentPage >= totalPages) ? '0.4' : '1';
            nextBtn.style.cursor = (modalCurrentPage >= totalPages) ? 'not-allowed' : 'pointer';
        }

        if (totalItems === 0) {
            modalTbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                        Tidak ditemukan data supplier dengan kata kunci "<strong>${escapeHtml(query)}</strong>".
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        pageItems.forEach((s, idx) => {
            const rowNo = startIdx + idx + 1;
            const badge = getCategoryBadge(s.category, s.categoryName);
            html += `
                <tr class="sup-modal-row" onclick="selectSupplier(${s.id}); closeSupplierModal();">
                    <td style="text-align: center; color: #64748b; font-size: 0.775rem;">${rowNo}</td>
                    <td>
                        <strong style="color: #0284c7; font-family: monospace; font-size: 0.8rem;">${escapeHtml(s.code)}</strong>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.825rem;">${escapeHtml(s.name)}</strong>
                    </td>
                    <td>${badge.html}</td>
                    <td style="font-size: 0.8rem; color: #334155;">
                        ${s.contact && s.contact !== '-' ? escapeHtml(s.contact) : '-'}
                    </td>
                    <td style="font-size: 0.8rem; color: #475569;">
                        ${s.address ? escapeHtml(s.address) : '-'}
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; font-weight: 600;" onclick="event.stopPropagation(); selectSupplier(${s.id}); closeSupplierModal();">
                            Pilih
                        </button>
                    </td>
                </tr>
            `;
        });

        modalTbody.innerHTML = html;
    }

    // Autocomplete Dropdown Popover
    function renderSupplierDropdown(query = '') {
        const q = query.trim().toLowerCase();
        let filtered = suppliersData.filter(s => {
            if (!q) return true;
            return (s.name && s.name.toLowerCase().includes(q)) || 
                   (s.code && s.code.toLowerCase().includes(q)) ||
                   (s.contact && s.contact.toLowerCase().includes(q)) ||
                   (s.address && s.address.toLowerCase().includes(q)) ||
                   (s.categoryName && s.categoryName.toLowerCase().includes(q));
        });

        if (filtered.length === 0) {
            supDropdownList.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                    Tidak ditemukan supplier untuk "<strong>${escapeHtml(query)}</strong>"
                </div>
            `;
            return;
        }

        const displayLimit = 7;
        const visibleItems = filtered.slice(0, displayLimit);

        let html = `
            <div style="padding: 0.4rem 0.85rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; font-size: 0.7rem; color: #64748b; font-weight: 600;">
                <span>${filtered.length} supplier ditemukan</span>
            </div>
        `;

        visibleItems.forEach((s, idx) => {
            const badge = getCategoryBadge(s.category, s.categoryName);
            html += `
                <div class="sup-option-item" data-id="${s.id}" data-idx="${idx}" onclick="selectSupplier(${s.id})">
                    <div style="min-width: 0;">
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${escapeHtml(s.name)}
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.15rem; font-size: 0.725rem; color: #64748b;">
                            <span style="font-family: monospace; font-weight: 700; color: #0284c7;">${escapeHtml(s.code)}</span>
                            ${s.contact && s.contact !== '-' ? `<span>&bull; Telp: ${escapeHtml(s.contact)}</span>` : ''}
                            ${s.address ? `<span>&bull; ${escapeHtml(s.address)}</span>` : ''}
                        </div>
                    </div>
                    <div style="flex-shrink: 0; margin-left: 0.5rem;">
                        ${badge.html}
                    </div>
                </div>
            `;
        });

        supDropdownList.innerHTML = html;
        highlightedSupIndex = -1;
    }

    function selectSupplier(supplierId) {
        const sup = suppliersData.find(s => s.id == supplierId);
        if (!sup) return;

        // Set value hidden select
        supHiddenSelect.value = sup.id;

        // Tampilkan info kartu supplier terpilih
        const badge = getCategoryBadge(sup.category, sup.categoryName);
        document.getElementById('disp_sup_name').innerText = sup.name;
        document.getElementById('disp_sup_code').innerText = sup.code;
        document.getElementById('disp_sup_cat_badge').innerHTML = badge.html;
        document.getElementById('disp_sup_contact').innerText = 
            (sup.contact && sup.contact !== '-' ? 'Telp: ' + sup.contact : '') + 
            (sup.address ? (sup.contact && sup.contact !== '-' ? ' | ' : '') + 'Alamat: ' + sup.address : '');

        selectedSupCard.style.display = 'flex';
        supSearchWrapper.style.display = 'none';
        supDropdownList.style.display = 'none';

        // Jika dalam mode multi-supplier, otomatis isi baris item yang suppliernya masih kosong
        if (typeof currentPoMode !== 'undefined' && currentPoMode === 'multi') {
            document.querySelectorAll('.item-supplier').forEach(sel => {
                if (!sel.value) {
                    sel.value = sup.id;
                }
            });
        }
        calculateGrandTotal();

        // Auto filter barang kategori sesuai supplier jika relevan
        if (sup.category === 'RAW') {
            const bbChip = document.querySelector('.barang-filter-chip[data-category="BB"]');
            if (bbChip) filterBarangCategory('BB', bbChip);
        } else if (sup.category === 'BUMBU') {
            const bumbuChip = document.querySelector('.barang-filter-chip[data-category="BUMBU"]');
            if (bumbuChip) filterBarangCategory('BUMBU', bumbuChip);
        } else if (sup.category === 'KEMASAN') {
            const packChip = document.querySelector('.barang-filter-chip[data-category="PACK"]');
            if (packChip) filterBarangCategory('PACK', packChip);
        }
    }

    function clearSelectedSupplier() {
        supHiddenSelect.value = '';
        selectedSupCard.style.display = 'none';
        supSearchWrapper.style.display = 'block';
        supSearchInput.value = '';
        renderSupplierDropdown('');
        supDropdownList.style.display = 'none';
        supSearchInput.focus();
        calculateGrandTotal();
    }

    // Event listener search input
    supSearchInput.addEventListener('input', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    supSearchInput.addEventListener('focus', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    // Keyboard navigation pada pencarian supplier
    supSearchInput.addEventListener('keydown', function(e) {
        const items = supDropdownList.querySelectorAll('.sup-option-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedSupIndex = (highlightedSupIndex + 1) % items.length;
            updateHighlightedSupItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedSupIndex = (highlightedSupIndex - 1 + items.length) % items.length;
            updateHighlightedSupItem(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedSupIndex >= 0 && items[highlightedSupIndex]) {
                const id = items[highlightedSupIndex].dataset.id;
                selectSupplier(id);
            } else if (items.length > 0) {
                // Pilih item pertama jika belum di-highlight
                const id = items[0].dataset.id;
                selectSupplier(id);
            }
        } else if (e.key === 'Escape') {
            supDropdownList.style.display = 'none';
        }
    });

    function updateHighlightedSupItem(items) {
        items.forEach((item, idx) => {
            if (idx === highlightedSupIndex) {
                item.classList.add('highlighted');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('highlighted');
            }
        });
    }

    // Tutup dropdown jika klik di luar
    document.addEventListener('click', function(e) {
        if (!supSearchWrapper.contains(e.target)) {
            supDropdownList.style.display = 'none';
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Cek supplier bawaan (misal saat validasi gagal kembali dengan old('supplier_id'))
    @if(old('supplier_id'))
        selectSupplier({{ old('supplier_id') }});
    @endif


    // =========================================================================
    // 2. FILTER KATEGORI BARANG & SUPPLIER PADA TABEL PO
    // =========================================================================
    let activeBarangCategory = 'ALL';
    let currentTableSupSearch = '';

    /**
     * Menghasilkan string HTML <option> dan <optgroup> supplier berdasarkan kategori & kata kunci pencarian.
     */
    function generateSupplierOptionsHtml(category = 'ALL', selectedId = '', searchQuery = '') {
        const q = (searchQuery || '').trim().toLowerCase();

        const groups = [
            { key: 'RAW', label: '🌾 BAHAN BAKU / PETANI SINGKONG' },
            { key: 'BUMBU', label: '🧂 BUMBU & BAHAN PENOLONG' },
            { key: 'KEMASAN', label: '📦 KEMASAN & PACKAGING' },
            { key: 'BP', label: '🏭 PENOLONG INDUSTRI' },
            { key: 'OTHER', label: 'LAINNYA' }
        ];

        let html = '<option value="">-- Pilih Supplier Mitra --</option>';
        let totalCount = 0;

        groups.forEach(grp => {
            let isAllowed = false;
            if (category === 'ALL') {
                isAllowed = true;
            } else if (category === 'BB' || category === 'RAW') {
                isAllowed = (grp.key === 'RAW');
            } else if (category === 'BUMBU') {
                isAllowed = (grp.key === 'BUMBU' || grp.key === 'BP');
            } else if (category === 'PACK' || category === 'KEMASAN') {
                isAllowed = (grp.key === 'KEMASAN');
            }

            if (isAllowed) {
                const list = suppliersData.filter(s => {
                    const matchCat = (s.category === grp.key);
                    const matchText = !q || s.name.toLowerCase().includes(q) || s.code.toLowerCase().includes(q);
                    return matchCat && matchText;
                });

                if (list.length > 0) {
                    totalCount += list.length;
                    html += `<optgroup label="${grp.label} (${list.length})">`;
                    list.forEach(s => {
                        const isSelected = (s.id == selectedId) ? 'selected' : '';
                        html += `<option value="${s.id}" data-category="${s.category}" ${isSelected}>${escapeHtml(s.name)} (${escapeHtml(s.code)})</option>`;
                    });
                    html += `</optgroup>`;
                }
            }
        });

        if (totalCount === 0) {
            html = `<option value="">-- Tidak ada supplier yang cocok (${escapeHtml(searchQuery || category)}) --</option>`;
        }

        return { html, totalCount };
    }

    /**
     * Merender dropdown supplier dan badge keterangan filter pada baris tabel tertentu.
     */
    function renderSupplierSelectOptions(selectElem, category = 'ALL', searchQuery = '') {
        if (!selectElem) return;
        const currentVal = selectElem.value;
        const { html, totalCount } = generateSupplierOptionsHtml(category, currentVal, searchQuery);
        selectElem.innerHTML = html;

        // Pertahankan nilai supplier yang terpilih jika masih tersedia di hasil filter
        if (currentVal) {
            selectElem.value = currentVal;
        }

        // Tampilkan badge indikator di bawah select
        const row = selectElem.closest('tr');
        if (row) {
            const hint = row.querySelector('.row-supplier-hint');
            if (hint) {
                if (category === 'BB' || category === 'RAW') {
                    hint.innerHTML = `<span style="color:#15803d; font-weight:600;">🌾 ${totalCount} Petani Bahan Baku</span>`;
                    hint.style.display = 'block';
                } else if (category === 'BUMBU') {
                    hint.innerHTML = `<span style="color:#b45309; font-weight:600;">🧂 ${totalCount} Supplier Bumbu &amp; Penolong</span>`;
                    hint.style.display = 'block';
                } else if (category === 'PACK') {
                    hint.innerHTML = `<span style="color:#7c3aed; font-weight:600;">📦 ${totalCount} Vendor Kemasan</span>`;
                    hint.style.display = 'block';
                } else if (searchQuery) {
                    hint.innerHTML = `<span style="color:#0284c7; font-weight:600;">🔍 ${totalCount} Supplier Cocok</span>`;
                    hint.style.display = 'block';
                } else {
                    hint.style.display = 'none';
                }
            }
        }
    }

    function filterBarangCategory(category, btn) {
        activeBarangCategory = category;
        document.querySelectorAll('.barang-filter-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');

        // Update semua baris di tabel: filter barang dan filter supplier
        document.querySelectorAll('.item-row').forEach(row => {
            const barangSelect = row.querySelector('.item-barang');
            applyBarangCategoryFilterToSelect(barangSelect, category);

            const supSelect = row.querySelector('.item-supplier');
            if (supSelect) {
                const selectedBarangOpt = barangSelect ? barangSelect.options[barangSelect.selectedIndex] : null;
                const targetCat = (selectedBarangOpt && selectedBarangOpt.value) ? selectedBarangOpt.dataset.category : category;
                renderSupplierSelectOptions(supSelect, targetCat, currentTableSupSearch);
            }
        });

        // Filter daftar barang di drawer safety stock jika ada
        document.querySelectorAll('.safety-stock-row').forEach(row => {
            const rowCat = row.dataset.category;
            if (category === 'ALL' || rowCat === category) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function applyBarangCategoryFilterToSelect(selectElem, category) {
        if (!selectElem) return;
        const grpBb = selectElem.querySelector('.grp-bb');
        const grpBumbu = selectElem.querySelector('.grp-bumbu');
        const grpPack = selectElem.querySelector('.grp-pack');

        if (category === 'ALL') {
            if (grpBb) grpBb.style.display = '';
            if (grpBumbu) grpBumbu.style.display = '';
            if (grpPack) grpPack.style.display = '';
        } else if (category === 'BB') {
            if (grpBb) grpBb.style.display = '';
            if (grpBumbu) grpBumbu.style.display = 'none';
            if (grpPack) grpPack.style.display = 'none';
        } else if (category === 'BUMBU') {
            if (grpBb) grpBb.style.display = 'none';
            if (grpBumbu) grpBumbu.style.display = '';
            if (grpPack) grpPack.style.display = 'none';
        } else if (category === 'PACK') {
            if (grpBb) grpBb.style.display = 'none';
            if (grpBumbu) grpBumbu.style.display = 'none';
            if (grpPack) grpPack.style.display = '';
        }
    }

    /**
     * Pencarian cepat supplier di tabel (saat mode multi-supplier aktif).
     */
    function filterTableSuppliersBySearch(query) {
        currentTableSupSearch = query;
        document.querySelectorAll('.item-row').forEach(row => {
            const barangSelect = row.querySelector('.item-barang');
            const selectedBarangOpt = barangSelect ? barangSelect.options[barangSelect.selectedIndex] : null;
            const targetCat = (selectedBarangOpt && selectedBarangOpt.value) ? selectedBarangOpt.dataset.category : activeBarangCategory;

            const supSelect = row.querySelector('.item-supplier');
            if (supSelect) {
                renderSupplierSelectOptions(supSelect, targetCat, query);
            }
        });
    }

    function clearTableSupSearch() {
        const inp = document.getElementById('table_sup_search_input');
        if (inp) inp.value = '';
        filterTableSuppliersBySearch('');
    }

    // =========================================================================
    // 3. LOGIKA MODE PEMESANAN (KONSEP 1: AUTO-SPLIT PO VS SINGLE SUPPLIER)
    // =========================================================================
    let currentPoMode = 'single'; // 'single' atau 'multi'

    function setPoMode(mode) {
        currentPoMode = mode;
        const btnSingle = document.getElementById('btnModeSingle');
        const btnMulti = document.getElementById('btnModeMulti');
        const modeDesc = document.getElementById('modeDescription');
        const colSuppliers = document.querySelectorAll('.col-supplier');
        const tfootQty = document.getElementById('tfootQtyColspan');
        const supHeaderLabel = document.getElementById('lblSupplierHeader');
        const supHeaderRequired = document.getElementById('reqSupplierHeader');
        const supHeaderSelect = document.getElementById('supplier_id');
        const sideAutoSplitBox = document.getElementById('sideAutoSplitBox');
        const poNoHelp = document.getElementById('po_no_help');
        const tblSupSearchBox = document.getElementById('table_supplier_search_box');

        if (mode === 'multi') {
            btnSingle.classList.remove('active');
            btnMulti.classList.add('active');
            modeDesc.innerHTML = `<span style="color:#0284c7; font-weight:700;">⚡ Konsep 1 Aktif:</span> Anda dapat menentukan supplier mitra berbeda pada setiap baris barang. Sistem akan memecah secara otomatis menjadi beberapa dokumen PO resmi terpisah (1 PO per supplier).`;

            if (poNoHelp) {
                poNoHelp.innerText = 'Prefix / nomor urut dasar untuk pemecahan PO otomatis per-supplier.';
            }

            if (supHeaderRequired) supHeaderRequired.style.display = 'none';
            if (supHeaderLabel) {
                supHeaderLabel.innerHTML = 'Supplier Utama <span style="font-size:0.75rem; color:#64748b; font-weight:normal;">(Opsional / Default Baris)</span>';
            }
            if (supHeaderSelect) supHeaderSelect.removeAttribute('required');

            colSuppliers.forEach(el => el.style.display = '');
            if (tfootQty) tfootQty.setAttribute('colspan', '4');
            if (tblSupSearchBox) tblSupSearchBox.style.display = 'flex';

            document.querySelectorAll('.item-row').forEach(row => {
                const barangSelect = row.querySelector('.item-barang');
                const selectedBarangOpt = barangSelect ? barangSelect.options[barangSelect.selectedIndex] : null;
                const targetCat = (selectedBarangOpt && selectedBarangOpt.value) ? selectedBarangOpt.dataset.category : activeBarangCategory;

                const supSelect = row.querySelector('.item-supplier');
                if (supSelect) {
                    supSelect.setAttribute('required', 'required');
                    renderSupplierSelectOptions(supSelect, targetCat, currentTableSupSearch);
                    if (!supSelect.value && supHiddenSelect.value) {
                        supSelect.value = supHiddenSelect.value;
                    }
                }
            });

            if (sideAutoSplitBox) sideAutoSplitBox.style.display = 'block';
        } else {
            btnSingle.classList.add('active');
            btnMulti.classList.remove('active');
            modeDesc.innerHTML = `Mode standar: Seluruh barang dalam formulir ini dipesan ke 1 supplier utama di bawah (diterbitkan sebagai 1 dokumen PO resmi).`;

            if (poNoHelp) {
                poNoHelp.innerText = 'Nomor urut otomatis sistem pengadaan.';
            }

            if (supHeaderRequired) supHeaderRequired.style.display = 'inline';
            if (supHeaderLabel) {
                supHeaderLabel.innerHTML = 'Supplier Mitra <span style="color:#ef4444;">*</span>';
            }
            if (supHeaderSelect) supHeaderSelect.setAttribute('required', 'required');

            colSuppliers.forEach(el => el.style.display = 'none');
            if (tfootQty) tfootQty.setAttribute('colspan', '3');
            if (tblSupSearchBox) tblSupSearchBox.style.display = 'none';

            document.querySelectorAll('.item-supplier').forEach(sel => {
                sel.removeAttribute('required');
            });

            if (sideAutoSplitBox) sideAutoSplitBox.style.display = 'none';
        }

        calculateGrandTotal();
    }

    // =========================================================================
    // 4. LOGIKA BARIS TABEL PO, KALKULASI & KEYBOARD EXCEL
    // =========================================================================
    let rowIndex = 1;

    function addRow(focusNew = false) {
        const container = document.getElementById('itemsContainer');
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = rowIndex;

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">${rowIndex + 1}</td>
            <td>
                <select name="items[${rowIndex}][barang_id]" class="form-control item-barang" onchange="updateRowSatuan(this)" required>
                    <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                    
                    <optgroup label="🌾 BAHAN BAKU (KOMODITAS UTAMA)" class="grp-bb">
                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                            <option value="{{ $b->barang_id }}" data-category="BB" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="🧂 BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                            <option value="{{ $b->barang_id }}" data-category="BUMBU" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="📦 BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                            <option value="{{ $b->barang_id }}" data-category="PACK" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </td>
            <td class="col-supplier" style="${currentPoMode === 'multi' ? '' : 'display: none;'}">
                <select name="items[${rowIndex}][supplier_id]" class="form-control item-supplier" onchange="calculateGrandTotal()" ${currentPoMode === 'multi' ? 'required' : ''}>
                    ${generateSupplierOptionsHtml(activeBarangCategory, '', currentTableSupSearch).html}
                </select>
                <div class="row-supplier-hint" style="font-size: 0.68rem; margin-top: 2px; display: none;"></div>
            </td>
            <td style="text-align: center;">
                <span class="row-satuan" style="font-weight: 600; color: #475569;">-</span>
            </td>
            <td>
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][pesan_qty]" class="form-control item-qty" value="1" oninput="calculateSubtotal(this)" style="text-align: right; font-weight: 700;" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_nominal]" class="form-control item-harga" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
            </td>
            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem;" class="row-subtotal">
                Rp 0
            </td>
            <td style="text-align: center;">
                <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="padding: 0.15rem 0.4rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
            </td>
        `;

        container.appendChild(tr);
        rowIndex++;
        updateRowNumbers();
        attachExcelKeyboardEvents(tr);

        // Jika dalam mode multi dan sudah ada supplier default header terpilih, isikan ke baris baru
        const supSelect = tr.querySelector('.item-supplier');
        if (supSelect) {
            renderSupplierSelectOptions(supSelect, activeBarangCategory, currentTableSupSearch);
            if (currentPoMode === 'multi' && supHiddenSelect.value) {
                supSelect.value = supHiddenSelect.value;
            }
        }

        // Terapkan filter kategori yang sedang aktif ke select baris baru
        const select = tr.querySelector('.item-barang');
        applyBarangCategoryFilterToSelect(select, activeBarangCategory);

        if (focusNew) {
            select.focus();
        }
        calculateGrandTotal();
        return tr;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 baris item barang dalam Purchase Order.');
            return;
        }
        btn.closest('tr').remove();
        updateRowNumbers();
        calculateGrandTotal();
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, idx) => {
            row.querySelector('.row-num').innerText = idx + 1;
        });
        document.getElementById('sideTotalItems').innerText = rows.length;
    }

    function updateRowSatuan(selectElem) {
        const row = selectElem.closest('tr');
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const satuan = selectedOption ? (selectedOption.dataset.satuan || '-') : '-';
        const defaultHarga = parseFloat(selectedOption ? (selectedOption.dataset.harga || 0) : 0);
        const barangCategory = selectedOption ? (selectedOption.dataset.category || 'ALL') : 'ALL';

        row.querySelector('.row-satuan').innerText = satuan;

        const hargaInput = row.querySelector('.item-harga');
        if (defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
            hargaInput.value = defaultHarga;
            calculateSubtotal(hargaInput);
        }

        // Auto-filter supplier di baris ini sesuai kategori barang terpilih!
        const supSelect = row.querySelector('.item-supplier');
        if (supSelect) {
            renderSupplierSelectOptions(supSelect, barangCategory, currentTableSupSearch);
            calculateGrandTotal();
        }
    }

    function calculateSubtotal(inputElem) {
        const row = inputElem.closest('tr');
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const harga = parseFloat(row.querySelector('.item-harga').value) || 0;
        const subtotal = qty * harga;

        row.querySelector('.row-subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let totalQty = 0;
        let grandTotal = 0;
        const supplierItemCount = {};

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const harga = parseFloat(row.querySelector('.item-harga').value) || 0;
            totalQty += qty;
            grandTotal += (qty * harga);

            if (currentPoMode === 'multi') {
                const supSelect = row.querySelector('.item-supplier');
                let supId = supSelect ? supSelect.value : '';
                if (!supId && supHiddenSelect.value) {
                    supId = supHiddenSelect.value;
                }
                if (supId) {
                    supplierItemCount[supId] = (supplierItemCount[supId] || 0) + 1;
                }
            }
        });

        const formattedGrandTotal = 'Rp ' + grandTotal.toLocaleString('id-ID');
        document.getElementById('totalQtyDisplay').innerText = totalQty.toFixed(2);
        document.getElementById('grandTotalDisplay').innerText = formattedGrandTotal;

        // Update di sticky sidebar kanan
        document.getElementById('sideGrandTotal').innerText = formattedGrandTotal;
        document.getElementById('sideTotalQty').innerText = totalQty.toFixed(2);

        const submitBtnText = document.getElementById('btnSubmitPoText');
        const sidePoCountBadge = document.getElementById('sidePoCountBadge');
        const sidePoSupplierList = document.getElementById('sidePoSupplierList');

        if (currentPoMode === 'multi') {
            const uniqueSupIds = Object.keys(supplierItemCount);
            const splitCount = Math.max(1, uniqueSupIds.length);

            if (sidePoCountBadge) {
                sidePoCountBadge.innerText = `${splitCount} Dokumen PO`;
                sidePoCountBadge.style.background = splitCount > 1 ? '#dcfce7' : '#e0f2fe';
                sidePoCountBadge.style.color = splitCount > 1 ? '#15803d' : '#0284c7';
            }

            if (submitBtnText) {
                if (splitCount > 1) {
                    submitBtnText.innerText = `⚡ Simpan & Pecah Jadi ${splitCount} PO`;
                } else {
                    submitBtnText.innerText = `Simpan & Terbitkan PO`;
                }
            }

            if (sidePoSupplierList) {
                if (uniqueSupIds.length === 0) {
                    sidePoSupplierList.innerHTML = `<span style="color:#94a3b8; font-style:italic;">Pilih supplier pada setiap baris item...</span>`;
                } else {
                    let listHtml = '<ul style="margin: 0; padding-left: 1.15rem; list-style-type: disc;">';
                    uniqueSupIds.forEach(id => {
                        const sup = suppliersData.find(s => s.id == id);
                        const supName = sup ? sup.name : `Supplier #${id}`;
                        const count = supplierItemCount[id];
                        listHtml += `<li style="margin-bottom: 0.2rem;"><strong>${escapeHtml(supName)}</strong>: ${count} item</li>`;
                    });
                    listHtml += '</ul>';
                    sidePoSupplierList.innerHTML = listHtml;
                }
            }
        } else {
            if (submitBtnText) {
                submitBtnText.innerText = 'Simpan & Terbitkan PO';
            }
        }
    }

    // Toggle Collapsible Drawer Safety Stock
    function toggleSafetyStockDrawer() {
        const drawer = document.getElementById('drawerSafetyStock');
        const chevron = document.getElementById('chevronSafetyStock');
        if (!drawer) return;

        if (drawer.style.display === 'none' || drawer.style.display === '') {
            drawer.style.display = 'block';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            drawer.style.display = 'none';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    // 1-Click Reorder untuk bahan baku yang menipis
    function addBelowMinimumItem(barangId, barangNm, satuanNm, defaultHarga, deficitQty) {
        const rows = document.querySelectorAll('.item-row');
        let targetRow = null;

        // Cek apakah baris pertama masih kosong belum dipilih barang
        if (rows.length === 1 && !rows[0].querySelector('.item-barang').value) {
            targetRow = rows[0];
        } else {
            // Cek apakah barang sudah ada di tabel
            for (let r of rows) {
                if (r.querySelector('.item-barang').value == barangId) {
                    const qtyInput = r.querySelector('.item-qty');
                    qtyInput.value = parseFloat(qtyInput.value) + deficitQty;
                    calculateSubtotal(qtyInput);
                    qtyInput.focus();
                    return;
                }
            }
            targetRow = addRow(false);
        }

        const select = targetRow.querySelector('.item-barang');
        select.value = barangId;
        targetRow.querySelector('.row-satuan').innerText = satuanNm;
        targetRow.querySelector('.item-qty').value = deficitQty;
        targetRow.querySelector('.item-harga').value = defaultHarga;

        if (currentPoMode === 'multi' && supHiddenSelect.value) {
            const rowSup = targetRow.querySelector('.item-supplier');
            if (rowSup && !rowSup.value) rowSup.value = supHiddenSelect.value;
        }

        calculateSubtotal(targetRow.querySelector('.item-qty'));
    }

    function addAllBelowMinimumItems() {
        document.querySelectorAll('.safety-stock-row').forEach(row => {
            if (row.style.display !== 'none') {
                const btn = row.querySelector('.btn-add-safety');
                if (btn) btn.click();
            }
        });
    }

    // Excel Keyboard Navigation (Enter to move or create new row)
    function attachExcelKeyboardEvents(rowElement) {
        const inputs = rowElement.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const allRows = Array.from(document.querySelectorAll('.item-row'));
                    const currentRowIdx = allRows.indexOf(rowElement);

                    if (input.classList.contains('item-harga') || input.classList.contains('item-qty')) {
                        if (currentRowIdx === allRows.length - 1) {
                            // Di baris terakhir -> buat baris baru dan langsung fokus
                            addRow(true);
                        } else {
                            // Pindah ke input kolom yang sama di baris bawahnya
                            const nextRow = allRows[currentRowIdx + 1];
                            const target = nextRow.querySelector('.' + input.classList[1]);
                            if (target) target.focus();
                        }
                    }
                }
            });
        });
    }

    // Inisialisasi awal
    document.querySelectorAll('.item-row').forEach(r => attachExcelKeyboardEvents(r));
    updateRowNumbers();
    calculateGrandTotal();

    // Auto-detect jika ada old items dengan supplier_id
    @if(old('items.0.supplier_id') || old('items.1.supplier_id'))
        setPoMode('multi');
    @endif
</script>
@endsection
