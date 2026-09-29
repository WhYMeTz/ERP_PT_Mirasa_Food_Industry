@extends('layouts.app')

@section('title', 'Catat Penerimaan Barang Fisik - ERP PT Mirasa')

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
        .order-station-grid { grid-template-columns: 1fr; }
        .sticky-action-sidebar { position: static !important; top: auto !important; }
    }

    .sup-filter-chip, .barang-filter-chip {
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
    .sup-filter-chip:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .sup-filter-chip.active {
        background: #059669;
        border-color: #059669;
        color: #ffffff;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3);
    }

    .category-segmented-control {
        display: inline-flex;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 6px;
        gap: 2px;
        border: 1px solid #cbd5e1;
    }
    .category-segment-btn {
        background: transparent;
        border: none;
        padding: 0.32rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.15s ease;
        line-height: 1.25;
    }
    .category-segment-btn:hover:not(.active) {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.7);
    }
    .category-segment-btn.active {
        background: #ffffff;
        color: #059669;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    .category-segment-count {
        font-size: 0.675rem;
        padding: 0.1rem 0.45rem;
        border-radius: 9999px;
        background: #e2e8f0;
        color: #475569;
        font-weight: 700;
        transition: all 0.15s ease;
    }
    .category-segment-btn.active .category-segment-count {
        background: #dcfce7;
        color: #15803d;
    }

    .sup-option-item {
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.1s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sup-option-item:hover, .sup-option-item.highlighted {
        background: #f0fdf4;
    }
    .sup-option-item:last-child {
        border-bottom: none;
    }

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
        background: #059669;
        border-color: #059669;
        color: #ffffff;
        box-shadow: 0 1px 2px rgba(5, 150, 105, 0.25);
    }
    .sup-modal-row {
        cursor: pointer;
        transition: background 0.08s ease;
    }
    .sup-modal-row:hover td {
        background: #f0fdf4 !important;
    }

    /* Excel Table Styling */
    .excel-grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }
    .excel-grid-table th {
        background: #0f172a;
        color: #f8fafc;
        font-weight: 600;
        padding: 0.55rem 0.45rem;
        border: 1px solid #334155;
        font-size: 0.775rem;
        letter-spacing: 0.02em;
    }
    .excel-grid-table td {
        padding: 0.3rem 0.4rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        vertical-align: middle;
    }
    .excel-grid-table tr:nth-child(even) td {
        background: #f8fafc;
    }
    .excel-grid-table tr:hover td {
        background: #f0fdf4;
    }
    .excel-grid-table .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 0.35rem 0.45rem !important;
        font-size: 0.825rem !important;
        height: 32px;
        box-sizing: border-box;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .excel-grid-table .form-control:focus {
        border-color: #059669 !important;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25) !important;
        background: #ffffff !important;
    }
</style>

<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Penerimaan
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Catat Penerimaan Barang Fisik (Goods Receipt / GRN)</h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">Barang yang dicatat di formulir ini akan langsung menambah saldo fisik gudang, membuat nomor batch baru, dan dicatat pada Kartu Stok.</p>
</div>

<form action="{{ route('gudang.terima.store') }}" method="POST" id="formTerima">
    @csrf
    @if ($selectedPo)
        <input type="hidden" name="redirect_to" value="po">
    @endif

    <div class="order-station-grid">
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            {{-- KARTU 1: INFORMASI DOKUMEN & PENGIRIM --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Pengirim</strong>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    {{-- BARIS 1: NOMOR PENERIMAAN, TANGGAL MASUK, REFERENSI PO, NO SURAT JALAN --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="terima_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Nomor Penerimaan <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="terima_no" name="terima_no" value="{{ old('terima_no', $nextTerimaNo ?? '') }}" class="form-control" style="background: #f8fafc; font-weight: 600; height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor urut Good Receipt Note (GRN).</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="terima_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal Masuk Fisik <span style="color:#ef4444;">*</span></label>
                            <input type="date" id="terima_tgl" name="terima_tgl" value="{{ old('terima_tgl', date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Waktu kedatangan armada di pabrik.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="po_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Referensi Purchase Order</label>
                            <select id="po_id" name="po_id" class="form-control" onchange="onPoSelected(this.value)" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
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
                            <small style="color: #64748b; font-size: 0.725rem;">Pilih PO untuk otomatis memuat barang.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="suratjalan_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No Surat Jalan Supplier</label>
                            <input type="text" id="suratjalan_no" name="suratjalan_no" value="{{ old('suratjalan_no') }}" class="form-control" placeholder="Contoh: SJ-2026/09/8812" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor surat jalan supir vendor.</small>
                        </div>
                    </div>

                    {{-- BARIS 2: SUPPLIER PENGIRIM & GUDANG TUJUAN MASUK (KEMBAR PERSIS DENGAN PO) --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; align-items: start;">
                        
                        {{-- Kolom Supplier Pengirim --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <label id="lblSupplierHeader" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0;">
                                    Supplier Pengirim <span style="color:#ef4444;">*</span>
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

                            <div id="supplier_search_wrapper" style="position: relative;">
                                <div style="position: relative; width: 100%;">
                                    <input type="text" id="supplier_search_input" class="form-control" placeholder="Cari nama atau kode supplier (misal: Sawit, Singkong, SUP-01)..." autocomplete="off" style="padding-left: 2.2rem !important; padding-right: 2rem !important; height: 38px; font-size: 0.85rem; border-radius: 6px; width: 100%; box-sizing: border-box;">
                                    <span style="position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.65rem;">
                                        ▼
                                    </span>
                                </div>

                                <div id="supplier_dropdown_list" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.06); max-height: 280px; overflow-y: auto; z-index: 1050;">
                                    {{-- Diisi dinamis via JS --}}
                                </div>
                            </div>

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
                            <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Ketik untuk memilih cepat, atau klik <strong>Daftar Supplier</strong> di atas untuk tabel lengkap.</small>
                        </div>

                        {{-- Kolom Gudang Penyimpanan --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem;">
                                Gudang Penyimpanan <span style="color:#ef4444;">*</span>
                            </label>
                            <select id="gudang_id" name="gudang_id" class="form-control" required style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                                <option value="">-- Pilih Gudang Penyimpanan --</option>
                                @foreach ($gudangList as $gdg)
                                    <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $selectedPo?->gudang_id) == $gdg->gudang_id) ? 'selected' : '' }}>
                                        {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Lokasi fisik gudang tempat komoditas disimpan.</small>
                        </div>
                    </div>

                    {{-- BARIS 3: CATATAN TAMBAHAN PENERIMAAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem;">
                            Catatan Tambahan Penerimaan
                        </label>
                        <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control" style="border-radius: 6px; font-size: 0.85rem;" placeholder="Contoh: Diterima dalam kondisi baik, kadar air singkong 18%, plat truk AB-1234-CD.">{{ old('catatan_txt') }}</textarea>
                    </div>

                </div>
            </div>

    @if ($selectedPo)
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.875rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="font-size: 0.875rem; color: #334155;">
                <strong style="color: #0f172a;">PO Terpilih:</strong> <span style="font-family: monospace; font-weight: 700; color: #0284c7;">{{ $selectedPo->po_no }}</span> &bull; 
                Supplier: <strong>{{ $selectedPo->supplier?->supplier_nm }}</strong>
                @if ($selectedPo->status_cd == 'PARTIAL')
                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; margin-left: 0.35rem; font-size: 0.75rem;">Sebagian Diterima</span>
                @else
                    <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:700; margin-left: 0.35rem; font-size: 0.75rem;">Terbuka Penuh</span>
                @endif
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="button" onclick="copyAllRemainingPoQty()" class="btn btn-sm" style="background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.775rem; padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;" title="Isi kuantitas terima dengan seluruh sisa PO">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                    Salin Sisa PO
                </button>
                <button type="button" onclick="clearAllTerimaQty()" class="btn btn-secondary btn-sm" style="font-weight: 600; font-size: 0.775rem; padding: 0.35rem 0.65rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Kosongkan kuantitas agar bisa diketik manual">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Kosongkan Qty
                </button>
            </div>
        </div>
    @endif

    {{-- KARTU 2: DETAIL BARANG & NOMOR BATCH (INVENTORY ENGINE - EXCEL STYLE) --}}
    <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="card-header" style="background: #ffffff; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e1; padding: 0.875rem 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <strong style="color: #0f172a; font-size: 0.95rem;">2. Fisik Barang Diterima &amp; Alokasi Batch</strong>
                <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                    💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk berpindah baris layaknya Excel
                </span>
            </div>
            <button type="button" onclick="addTerimaRow(true)" class="btn btn-primary btn-sm" style="font-weight: 600; background: #059669; padding: 0.4rem 0.85rem;">
                + Tambah Baris
            </button>
        </div>

        {{-- FILTER BAR CEPAT UNTUK KATEGORI BARANG --}}
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
            <div style="font-size: 0.725rem; color: #059669; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Hanya bahan masuk supplier (WIP &amp; FG tidak ditampilkan)</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="excel-grid-table" id="tableTerimaItems" style="width: 100%; min-width: 1050px;">
                <thead>
                    <tr>
                        <th style="width: 32px; text-align: center;">No</th>
                        <th style="min-width: 220px; text-align: left;">Nama Komoditas / Barang <span style="color:#ef4444;">*</span></th>
                        <th style="width: 160px; text-align: left;">Nomor Batch Fisik <span style="color:#ef4444;">*</span></th>
                        <th style="width: 120px; text-align: center;">Tgl Expired</th>
                        <th style="width: 95px; text-align: center;">Grade Mutu</th>
                        <th style="width: 110px; text-align: right;">Qty Masuk (Bruto) <span style="color:#ef4444;">*</span></th>
                        <th style="width: 95px; text-align: right;">Afkir (Reject)</th>
                        <th style="width: 105px; text-align: right; background: #064e3b;">Netto Bersih</th>
                        <th style="width: 60px; text-align: center;">Satuan</th>
                        <th style="width: 115px; text-align: right;">Harga Satuan (Rp)</th>
                        <th style="width: 40px; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="terimaItemsContainer">
                    @if ($selectedPo && $selectedPo->details->isNotEmpty())
                        {{-- Prefilled items from selected PO --}}
                        @php $prefilledBatches = []; @endphp
                        @foreach ($selectedPo->details as $idx => $pdtl)
                            @if ((float) $pdtl->sisa_qty > 0)
                                @php
                                    $batchVal = app(\App\Services\Common\CodeGeneratorService::class)->generateBatchNo(
                                        $pdtl->barang?->barang_cd, 
                                        old('terima_tgl', date('Y-m-d')), 
                                        $pdtl->barang?->barang_nm,
                                        $prefilledBatches
                                    );
                                    $prefilledBatches[] = $batchVal;
                                @endphp
                                <tr class="terima-row" data-index="{{ $idx }}" data-sisa="{{ (float) $pdtl->sisa_qty }}">
                                    <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">{{ $idx + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $pdtl->podtl_id }}">
                                        <input type="hidden" name="items[{{ $idx }}][barang_id]" value="{{ $pdtl->barang_id }}" class="item-barang-id">
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">{{ $pdtl->barang?->barang_nm }}</strong>
                                        <span style="font-size: 0.725rem; color: #64748b;">
                                            Pesanan: {{ number_format((float) $pdtl->pesan_qty, 2) }} | <strong>Sisa: {{ number_format((float) $pdtl->sisa_qty, 2) }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ old("items.{$idx}.batch_no", $batchVal) }}" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7;" required>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $idx }}][expired_tgl]" class="form-control">
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][grade_cd]" class="form-control">
                                            <option value="A" selected>Grade A Super</option>
                                            <option value="B">Grade B Standar</option>
                                            <option value="REJECT">Reject / Afkir</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" max="{{ $pdtl->sisa_qty }}" name="items[{{ $idx }}][terima_qty]" value="{{ (float) $pdtl->sisa_qty }}" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                                        {{ number_format((float) $pdtl->sisa_qty, 2, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-weight: 700; color: #475569; font-size: 0.8rem;">{{ $pdtl->barang?->satuanDasar?->satuan_nm ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][harga_nominal]" value="{{ (float) $pdtl->harga_nominal }}" class="form-control item-harga" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
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
                            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">1</td>
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
                                <input type="text" name="items[0][batch_no]" id="batch_0" value="" placeholder="Otomatis saat barang dipilih" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7;" required>
                            </td>
                            <td>
                                <input type="date" name="items[0][expired_tgl]" class="form-control">
                            </td>
                            <td>
                                <select name="items[0][grade_cd]" class="form-control">
                                    <option value="A" selected>Grade A Super</option>
                                    <option value="B">Grade B Standar</option>
                                    <option value="REJECT">Reject / Afkir</option>
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
                                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">-</span>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                            </td>
                            <td style="text-align: center;">
                                <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                        <td colspan="5" style="text-align: right; padding: 0.65rem 0.75rem; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            Total Timbangan Fisik:
                        </td>
                        <td id="totalBrutoQtyDisplay" style="padding: 0.65rem 0.45rem; color: #0284c7; font-size: 0.9rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Bruto Timbangan">
                            0.00
                        </td>
                        <td id="totalRejectQtyDisplay" style="padding: 0.65rem 0.45rem; color: #dc2626; font-size: 0.9rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Afkir / Reject">
                            0.00
                        </td>
                        <td id="totalNettoQtyDisplay" style="padding: 0.65rem 0.45rem; color: #047857; font-size: 0.95rem; text-align: right; background: #dcfce7; font-family: monospace; font-weight: 800;" title="Total Netto Bersih">
                            0.00
                        </td>
                        <td style="text-align: center; color: #64748b; font-size: 0.725rem;">Subtotal:</td>
                        <td id="totalTerimaNilaiDisplay" style="text-align: right; padding: 0.65rem 0.45rem; font-size: 0.9rem; color: #0f172a; font-family: monospace; font-weight: 700;">
                            Rp 0
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

        </div> {{-- End Kolom Kiri (.order-station-grid left) --}}

        {{-- KOLOM KANAN: STICKY ACTION SIDEBAR --}}
        <div class="sticky-action-sidebar" style="position: sticky; top: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
            
            {{-- KARTU SUMMARY & ACTION UTAMA --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); overflow: hidden;">
                <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.875rem 1.25rem;">
                    <div style="font-size: 0.725rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #94a3b8;">
                        Ringkasan Penerimaan
                    </div>
                    <strong style="color: #ffffff; font-size: 1.05rem;">Estimasi Nilai HPP Masuk</strong>
                </div>

                <div style="padding: 1.25rem;">
                    {{-- TOTAL NOMINAL DISPLAY BESAR --}}
                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.725rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Total Nilai Masuk:</span>
                        <div id="sideGrandTotal" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem; font-family: monospace; letter-spacing: -0.02em;">
                            Rp 0
                        </div>
                    </div>

                    {{-- STATISTIK DAMPAK KARTU STOK --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Jumlah Komoditas:</span>
                            <strong style="color: #0f172a;"><span id="sideTotalItems">0</span> Jenis Bahan</strong>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.4rem; border-top: 1px dashed #e2e8f0;">
                            <span style="color: #64748b;">Gudang Tujuan:</span>
                            <strong id="sideGudangName" style="color: #0f172a; font-size: 0.8rem; max-width: 150px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</strong>
                        </div>

                        {{-- HIGHLIGHT NETTO BERSIH MASUK STOK --}}
                        <div style="padding: 0.55rem 0.65rem; background: #dcfce7; border: 1px solid #86efac; border-radius: 5px; margin-top: 0.2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #166534; font-size: 0.775rem; font-weight: 700; text-transform: uppercase;">Netto Masuk Stok:</span>
                                <strong style="color: #15803d; font-size: 1.05rem; font-family: monospace;"><span id="sideTotalNetto">0.00</span></strong>
                            </div>
                            <div id="sideRejectNotice" style="font-size: 0.7rem; color: #166534; margin-top: 0.2rem; display: flex; justify-content: space-between;">
                                <span>Afkir (Reject):</span>
                                <span id="sideTotalRejectText" style="font-weight: 700; color: #dc2626;">0.00 (Tidak Masuk)</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.25rem; font-size: 0.75rem; color: #64748b; line-height: 1.4; display: flex; gap: 0.35rem;">
                        <svg width="15" height="15" fill="none" stroke="#059669" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Saldo gudang bertambah otomatis sesuai <strong>Netto Bersih</strong> dan tercatat pada Kartu Stok saat disimpan.</span>
                    </div>

                    {{-- TOMBOL UTAMA: SIMPAN & MASUKKAN KE STOK GUDANG --}}
                    <button type="submit" id="btnSubmitTerima" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 700; background: #059669; justify-content: center; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.25); display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Masukkan ke Stok</span>
                    </button>

                    <a href="{{ route('gudang.terima.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 0.65rem; font-size: 0.85rem; padding: 0.5rem;">
                        Batal &amp; Kembali ke Daftar
                    </a>
                </div>
            </div>

            {{-- KARTU PANDUAN CEPAT OPERATOR --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.85rem;">Panduan Penerimaan Fisik</strong>
                </div>
                <div style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #059669; font-weight: 700;">&bull;</span>
                        <span><strong>Nomor Batch Terisolasi:</strong> Format kode batch dibuat otomatis per komoditas &amp; tanggal kedatangan (contoh: <code>BC-28092026-01</code>).</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #059669; font-weight: 700;">&bull;</span>
                        <span><strong>Hitung Timbangan Fisik:</strong> Input kuantitas bruto timbangan &amp; jumlah reject/afkir untuk mendapatkan kuantitas <strong>Netto Bersih</strong>.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #059669; font-weight: 700;">&bull;</span>
                        <span><strong>Filter Kategori Cepat:</strong> Gunakan tombol chip di atas tabel untuk memfilter bahan baku mentah, bumbu, atau kemasan.</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="color: #059669; font-weight: 700;">&bull;</span>
                        <span>Tekan <kbd style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd> pada kuantitas/harga untuk langsung menambah baris baru.</span>
                    </div>
                </div>
            </div>

        </div> {{-- End Kolom Kanan (.sticky-action-sidebar) --}}
    </div> {{-- End .order-station-grid --}}
</form>

{{-- ========================================================================= --}}
{{-- MODAL PILIH MASTER SUPPLIER (STANDAR TABEL EXCEL ERP PERSIS PO)           --}}
{{-- ========================================================================= --}}
<div id="modalPilihSupplier" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 960px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem;">
            <div>
                <h2 class="modal-title" style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Daftar Supplier</h2>
                <p style="color: #64748b; font-size: 0.8rem; margin: 0.2rem 0 0 0;">Pilih supplier rekanan untuk penerimaan fisik barang masuk.</p>
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
                <button type="button" class="sup-modal-tab" data-tab="BUMBU" onclick="filterSupplierModalTab('BUMBU', this)">
                    Bumbu &amp; Penolong ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BUMBU')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="KEMASAN" onclick="filterSupplierModalTab('KEMASAN', this)">
                    Kemasan &amp; Karton ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'KEMASAN')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="BP" onclick="filterSupplierModalTab('BP', this)">
                    Penolong Industri ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BP')->count() }})
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
        } else if (category === 'BUMBU') {
            catBg = '#fef3c7';
            catColor = '#92400e';
            label = 'Bumbu & Penolong';
        } else if (category === 'KEMASAN') {
            catBg = '#f3e8ff';
            catColor = '#6b21a8';
            label = 'Kemasan & Karton';
        } else if (category === 'BP') {
            catBg = '#ffedd5';
            catColor = '#9a3412';
            label = 'Penolong Industri';
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

    function openSupplierModal() {
        if (supDropdownList) supDropdownList.style.display = 'none';
        openModal('modalPilihSupplier');
        const currentTyped = supSearchInput ? supSearchInput.value.trim() : '';
        if (modalSearchInput) modalSearchInput.value = currentTyped;
        modalCurrentPage = 1;
        renderSupplierModalTable();
        setTimeout(() => {
            if (modalSearchInput) {
                modalSearchInput.focus();
                modalSearchInput.select();
            }
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
        if (!modalTbody || !modalSearchInput) return;
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

        if (modalCountText) {
            modalCountText.innerText = totalItems > 0 
                ? `Menampilkan ${startIdx + 1}–${endIdx} dari ${totalItems} supplier`
                : `Menampilkan 0 supplier`;
        }

        const paginationDiv = document.getElementById('modal_supplier_pagination');
        if (paginationDiv) {
            paginationDiv.style.display = totalPages > 1 ? 'flex' : 'none';
            document.getElementById('modal_page_info').innerText = `Hal ${modalCurrentPage} dari ${totalPages}`;
            const prevBtn = document.getElementById('modal_prev_btn');
            const nextBtn = document.getElementById('modal_next_btn');
            if (prevBtn) {
                prevBtn.disabled = (modalCurrentPage <= 1);
                prevBtn.style.opacity = (modalCurrentPage <= 1) ? '0.4' : '1';
                prevBtn.style.cursor = (modalCurrentPage <= 1) ? 'not-allowed' : 'pointer';
            }
            if (nextBtn) {
                nextBtn.disabled = (modalCurrentPage >= totalPages);
                nextBtn.style.opacity = (modalCurrentPage >= totalPages) ? '0.4' : '1';
                nextBtn.style.cursor = (modalCurrentPage >= totalPages) ? 'not-allowed' : 'pointer';
            }
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
                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; font-weight: 600; background: #059669;" onclick="event.stopPropagation(); selectSupplier(${s.id}); closeSupplierModal();">
                            Pilih
                        </button>
                    </td>
                </tr>
            `;
        });

        modalTbody.innerHTML = html;
    }

    function renderSupplierDropdown(query = '') {
        const q = query.trim().toLowerCase();
        let filtered = suppliersData.filter(s => {
            if (!q) return true;
            return (s.name && s.name.toLowerCase().includes(q)) || 
                   (s.code && s.code.toLowerCase().includes(q)) ||
                   (s.contact && s.contact.toLowerCase().includes(q));
        });

        const badgeCount = document.getElementById('supplier_count_badge');
        if (badgeCount) badgeCount.innerText = `Total: ${suppliersData.length} supplier`;

        if (filtered.length === 0) {
            supDropdownList.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                    Tidak ditemukan supplier dengan kata kunci "<strong>${escapeHtml(query)}</strong>".
                    <div style="margin-top: 0.4rem;">
                        <a href="javascript:void(0)" onclick="openSupplierModal()" style="color: #0284c7; font-weight: 600; font-size: 0.75rem;">Buka Tabel Daftar Supplier</a>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.slice(0, 15).forEach((s, idx) => {
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

        supHiddenSelect.value = sup.id;

        const badge = getCategoryBadge(sup.category, sup.categoryName);
        document.getElementById('disp_sup_name').innerText = sup.name;
        document.getElementById('disp_sup_code').innerText = sup.code;
        const catBadgeSpan = document.getElementById('disp_sup_cat_badge');
        if (catBadgeSpan) catBadgeSpan.innerHTML = badge.html;
        const legacyCatSpan = document.getElementById('disp_sup_cat');
        if (legacyCatSpan) legacyCatSpan.innerText = sup.categoryName;
        document.getElementById('disp_sup_contact').innerText = 
            (sup.contact && sup.contact !== '-' ? 'Telp: ' + sup.contact : '') + 
            (sup.address ? (sup.contact && sup.contact !== '-' ? ' | ' : '') + 'Alamat: ' + sup.address : '');

        selectedSupCard.style.display = 'flex';
        supSearchWrapper.style.display = 'none';
        supDropdownList.style.display = 'none';

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
        supDropdownList.style.display = 'block';
        supSearchInput.focus();
    }

    // Search input listeners
    supSearchInput.addEventListener('input', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    supSearchInput.addEventListener('focus', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    // Keyboard navigation
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

    document.addEventListener('click', function(e) {
        if (!supSearchWrapper.contains(e.target) && !e.target.classList.contains('sup-filter-chip')) {
            supDropdownList.style.display = 'none';
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Prefill selected supplier if available (from old input or selected PO)
    @php
        $initSupplierId = old('supplier_id', $selectedPo?->supplier_id);
    @endphp
    @if($initSupplierId)
        selectSupplier({{ $initSupplierId }});
    @endif


    // =========================================================================
    // 2. FILTER KATEGORI BARANG PADA TABEL GRN
    // =========================================================================
    let activeBarangCategory = 'ALL';

    function filterBarangCategory(category, btn) {
        activeBarangCategory = category;
        document.querySelectorAll('.category-segment-btn, .barang-filter-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');

        document.querySelectorAll('.item-barang').forEach(select => {
            applyBarangCategoryFilterToSelect(select, category);
        });
    }

    function applyBarangCategoryFilterToSelect(selectElem, category) {
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


    // =========================================================================
    // 3. LOGIKA BARIS PENERIMAAN BARANG, BATCH GENERATOR & KALKULASI
    // =========================================================================
    let terimaRowIndex = {{ $selectedPo ? count($selectedPo->details) : 1 }};

    function onPoSelected(poId) {
        if (!poId) {
            window.location.href = "{{ route('gudang.terima.create') }}";
            return;
        }
        window.location.href = "{{ route('gudang.terima.create') }}?po_id=" + poId;
    }

    // ⚡ Salin Semua Sisa PO langsung ke kolom Kuantitas Terima
    function copyAllRemainingPoQty() {
        document.querySelectorAll('.terima-row').forEach(row => {
            const sisa = parseFloat(row.dataset.sisa || 0);
            if (sisa > 0) {
                const qtyInput = row.querySelector('.item-terima-qty');
                if (qtyInput) qtyInput.value = sisa;
            }
        });
        calculateTotalTerima();
    }

    // 🧹 Kosongkan Semua Kuantitas agar bisa diketik manual sesuai fisik yang datang
    function clearAllTerimaQty() {
        document.querySelectorAll('.item-terima-qty').forEach(input => {
            input.value = 0;
        });
        calculateTotalTerima();
    }

    function addTerimaRow(focusNew = false) {
        const container = document.getElementById('terimaItemsContainer');
        const tr = document.createElement('tr');
        tr.className = 'terima-row';
        tr.dataset.index = terimaRowIndex;
        tr.dataset.sisa = 0;

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">${terimaRowIndex + 1}</td>
            <td>
                <select name="items[${terimaRowIndex}][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
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
                <input type="text" name="items[${terimaRowIndex}][batch_no]" value="" placeholder="Otomatis saat barang dipilih" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7;" required>
            </td>
            <td>
                <input type="date" name="items[${terimaRowIndex}][expired_tgl]" class="form-control">
            </td>
            <td>
                <select name="items[${terimaRowIndex}][grade_cd]" class="form-control">
                    <option value="A" selected>Grade A Super</option>
                    <option value="B">Grade B Standar</option>
                    <option value="REJECT">Reject / Afkir</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.0001" min="0" name="items[${terimaRowIndex}][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
            </td>
            <td>
                <input type="number" step="0.0001" min="0" name="items[${terimaRowIndex}][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
            </td>
            <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                1,00
            </td>
            <td style="text-align: center;">
                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">-</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${terimaRowIndex}][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
            </td>
            <td style="text-align: center;">
                <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
            </td>
        `;

        container.appendChild(tr);
        terimaRowIndex++;
        updateTerimaRowNumbers();
        attachExcelKeyboardEventsTerima(tr);
        calculateTotalTerima();

        // Terapkan filter kategori barang yang aktif
        const select = tr.querySelector('.item-barang');
        applyBarangCategoryFilterToSelect(select, activeBarangCategory);

        if (focusNew) {
            select.focus();
        }
    }

    function removeTerimaRow(btn) {
        const rows = document.querySelectorAll('.terima-row');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 baris item barang yang diterima.');
            return;
        }
        btn.closest('tr').remove();
        updateTerimaRowNumbers();
        calculateTotalTerima();
    }

    function updateTerimaRowNumbers() {
        document.querySelectorAll('.terima-row').forEach((row, idx) => {
            row.querySelector('.row-num').innerText = idx + 1;
        });
    }

    async function updateTerimaSatuanAndBatch(selectElem) {
        const row = selectElem.closest('tr');
        const selectedOption = selectElem.options ? selectElem.options[selectElem.selectedIndex] : null;
        const barangId = selectElem.value;
        if (!barangId) {
            const batchInput = row.querySelector('.item-batch');
            if (batchInput) {
                batchInput.value = '';
                batchInput.placeholder = 'Otomatis saat barang dipilih';
            }
            const satuanSpan = row.querySelector('.row-satuan');
            if (satuanSpan) satuanSpan.innerText = '-';
            const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');
            if (hargaInput) hargaInput.value = 0;
            calculateTotalTerima();
            return;
        }

        const satuan = selectedOption?.dataset?.satuan || '-';
        const defaultHarga = parseFloat(selectedOption?.dataset?.harga || 0);

        const satuanSpan = row.querySelector('.row-satuan');
        if (satuanSpan && satuan !== '-') satuanSpan.innerText = satuan;

        // Ambil tanggal dari input terima_tgl
        const tglInput = document.getElementById('terima_tgl');
        const tglMasuk = tglInput?.value || '';

        // Otomatis generate nomor batch via AJAX
        const batchInput = row.querySelector('.item-batch');
        if (batchInput) {
            batchInput.value = 'Membuat batch...';
            try {
                // Kumpulkan batch_no yang sudah ada di baris-baris lain agar tidak kembar nomor urutnya
                const existingBatches = [];
                document.querySelectorAll('.item-batch').forEach(inp => {
                    if (inp !== batchInput && inp.value && !inp.value.includes('...')) {
                        existingBatches.push(inp.value.trim());
                    }
                });

                const excludeParam = existingBatches.length > 0 ? `&exclude=${encodeURIComponent(existingBatches.join(','))}` : '';
                const url = `{{ route('ajax.generate_code') }}?type=batch_no&barang_id=${barangId}&date=${tglMasuk}${excludeParam}`;
                const resp = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await resp.json();
                if (data.status === 'success' && data.code) {
                    batchInput.value = data.code;
                } else {
                    // Fallback generator client-side
                    const acronym = selectedOption?.dataset?.acronym || 'BRG';
                    const dateParts = tglMasuk ? tglMasuk.split('-') : [];
                    const formattedDate = dateParts.length === 3 ? `${dateParts[2]}${dateParts[1]}${dateParts[0]}` : '01012026';
                    const prefix = `${acronym}-${formattedDate}-`;
                    let maxNum = 0;
                    existingBatches.forEach(b => {
                        if (b.startsWith(prefix)) {
                            const n = parseInt(b.substring(prefix.length), 10);
                            if (!isNaN(n) && n > maxNum) maxNum = n;
                        }
                    });
                    batchInput.value = `${prefix}${String(maxNum + 1).padStart(2, '0')}`;
                }
            } catch (err) {
                console.error('Gagal mengambil nomor batch berikutnya:', err);
                const acronym = selectedOption?.dataset?.acronym || 'BRG';
                const dateParts = tglMasuk ? tglMasuk.split('-') : [];
                const formattedDate = dateParts.length === 3 ? `${dateParts[2]}${dateParts[1]}${dateParts[0]}` : '01012026';
                const prefix = `${acronym}-${formattedDate}-`;
                let maxNum = 0;
                existingBatches.forEach(b => {
                    if (b.startsWith(prefix)) {
                        const n = parseInt(b.substring(prefix.length), 10);
                        if (!isNaN(n) && n > maxNum) maxNum = n;
                    }
                });
                batchInput.value = `${prefix}${String(maxNum + 1).padStart(2, '0')}`;
            }
        }

        const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');
        if (hargaInput && defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
            hargaInput.value = defaultHarga;
        }
        calculateTotalTerima();
    }

    // Ketika tanggal penerimaan diubah, sinkronkan otomatis seluruh nomor batch di tabel
    document.getElementById('terima_tgl')?.addEventListener('change', async function() {
        const rows = document.querySelectorAll('.terima-row');
        for (const row of rows) {
            const selectElem = row.querySelector('.item-barang') || row.querySelector('.item-barang-id');
            if (selectElem && selectElem.value) {
                await updateTerimaSatuanAndBatch(selectElem);
            }
        }
    });

    // Inisialisasi awal saat halaman selesai dimuat: jika ada baris yang sudah terpilih barangnya, ambil batch berikutnya
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.terima-row').forEach(row => {
            const selectElem = row.querySelector('.item-barang');
            if (selectElem && selectElem.value) {
                updateTerimaSatuanAndBatch(selectElem);
            }
        });
    });

    // Kalkulasi Lengkap Timbangan Pabrik: Bruto, Reject/Afkir, Netto Bersih, dan Nilai Fisik
    function calculateTotalTerima() {
        let totalBruto = 0;
        let totalReject = 0;
        let totalNilai = 0;
        let activeItemCount = 0;

        document.querySelectorAll('.terima-row').forEach(row => {
            const selectElem = row.querySelector('.item-barang') || row.querySelector('.item-barang-id');
            const terimaInput = row.querySelector('.item-terima-qty');
            const rejectInput = row.querySelector('.item-reject-qty');
            const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');

            if (selectElem && selectElem.value) {
                activeItemCount++;
            }

            const terimaQty = parseFloat(terimaInput?.value) || 0;
            const rejectQty = parseFloat(rejectInput?.value) || 0;
            const hargaNominal = parseFloat(hargaInput?.value) || 0;

            const netto = Math.max(0, terimaQty - rejectQty);

            const nettoSpan = row.querySelector('.row-netto');
            if (nettoSpan) {
                nettoSpan.innerText = netto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            totalBruto += terimaQty;
            totalReject += rejectQty;
            totalNilai += (terimaQty * hargaNominal);
        });

        const totalNetto = Math.max(0, totalBruto - totalReject);

        // Update Tabel Footer
        const brutoDisplay = document.getElementById('totalBrutoQtyDisplay');
        const rejectDisplay = document.getElementById('totalRejectQtyDisplay');
        const nettoDisplay = document.getElementById('totalNettoQtyDisplay');
        const nilaiDisplay = document.getElementById('totalTerimaNilaiDisplay');

        if (brutoDisplay) brutoDisplay.innerText = totalBruto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (rejectDisplay) rejectDisplay.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (nettoDisplay) nettoDisplay.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (nilaiDisplay) nilaiDisplay.innerText = 'Rp ' + totalNilai.toLocaleString('id-ID');

        // Update Sticky Action Sidebar
        const sideGrandTotal = document.getElementById('sideGrandTotal');
        const sideTotalItems = document.getElementById('sideTotalItems');
        const sideTotalNetto = document.getElementById('sideTotalNetto');
        const sideTotalRejectText = document.getElementById('sideTotalRejectText');

        if (sideGrandTotal) sideGrandTotal.innerText = 'Rp ' + totalNilai.toLocaleString('id-ID');
        if (sideTotalItems) sideTotalItems.innerText = activeItemCount;
        if (sideTotalNetto) sideTotalNetto.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (sideTotalRejectText) {
            sideTotalRejectText.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Unit (Tidak Masuk)';
        }
    }

    // Sinkronisasi Nama Gudang Tujuan ke Sidebar Kanan
    function updateSideGudangDisplay() {
        const gdgSelect = document.getElementById('gudang_id');
        const sideGdg = document.getElementById('sideGudangName');
        if (!sideGdg || !gdgSelect) return;
        const opt = gdgSelect.options[gdgSelect.selectedIndex];
        sideGdg.innerText = (opt && opt.value) ? opt.text : '- Belum Dipilih -';
    }

    document.getElementById('gudang_id')?.addEventListener('change', updateSideGudangDisplay);
    updateSideGudangDisplay();

    // Excel Keyboard Navigation untuk Penerimaan Barang
    function attachExcelKeyboardEventsTerima(rowElement) {
        const inputs = rowElement.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const allRows = Array.from(document.querySelectorAll('.terima-row'));
                    const currentRowIdx = allRows.indexOf(rowElement);

                    if (input.classList.contains('item-terima-qty') || input.classList.contains('item-reject-qty') || input.classList.contains('item-harga')) {
                        if (currentRowIdx === allRows.length - 1) {
                            addTerimaRow(true);
                        } else {
                            const nextRow = allRows[currentRowIdx + 1];
                            const targetClass = input.classList[1] || input.classList[0];
                            const target = nextRow.querySelector('.' + targetClass);
                            if (target) target.focus();
                        }
                    }
                }
            });
        });
    }

    document.querySelectorAll('.terima-row').forEach(r => attachExcelKeyboardEventsTerima(r));
    calculateTotalTerima();

    function fillAllSisaCreate() {
        copyAllRemainingPoQty();
    }

    function clearAllInputsCreate() {
        clearAllTerimaQty();
    }
</script>
@endsection
