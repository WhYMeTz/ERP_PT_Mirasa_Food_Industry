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
</style>

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
        {{-- ========================================================================= --}}
        {{-- KOLOM KIRI (70%): FORM DOKUMEN & TABEL INPUT BARANG PESANAN             --}}
        {{-- ========================================================================= --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            {{-- KARTU 1: INFORMASI UTAMA DOKUMEN --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Rekanan</strong>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    {{-- BARIS 1: NOMOR PO, TANGGAL PO, ESTIMASI TIBA --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="po_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Nomor PO <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="po_no" name="po_no" value="{{ old('po_no', $nextPoNo ?? '') }}" class="form-control" style="background: #f8fafc; font-weight: 600;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor urut otomatis sistem pengadaan.</small>
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

                    {{-- BARIS 2: SUPPLIER MITRA & GUDANG TUJUAN --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="supplier_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Supplier Mitra <span style="color:#ef4444;">*</span></label>
                            <select id="supplier_id" name="supplier_id" class="form-control" required>
                                <option value="">-- Pilih Supplier Mitra --</option>
                                @foreach ($supplierList as $sup)
                                    <option value="{{ $sup->supplier_id }}" {{ old('supplier_id') == $sup->supplier_id ? 'selected' : '' }}>
                                        {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Gudang Tujuan Masuk <span style="color:#ef4444;">*</span>
                                @if (!empty($isGudangLocked))
                                    <span class="badge" style="background:#dcfce7; color:#15803d; font-size:0.7rem; margin-left:0.25rem;">
                                        Terkunci (Lokasi Akun Anda)
                                    </span>
                                @endif
                            </label>
                            @if (!empty($isGudangLocked))
                                <input type="hidden" name="gudang_id" value="{{ $assignedGudangId }}">
                                <select id="gudang_id" class="form-control" disabled style="background: #f8fafc; color: #1e293b; font-weight: 600; cursor: not-allowed;">
                                    @foreach ($gudangList as $gdg)
                                        <option value="{{ $gdg->gudang_id }}" {{ $assignedGudangId == $gdg->gudang_id ? 'selected' : '' }}>
                                            {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <select id="gudang_id" name="gudang_id" class="form-control" required>
                                    <option value="">-- Pilih Lokasi / Perusahaan Masuk --</option>
                                    @foreach ($gudangList as $gdg)
                                        <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $assignedGudangId ?? '') == $gdg->gudang_id ? 'selected' : '' }}>
                                            {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    {{-- BARIS 3: CATATAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Instruksi / Catatan Pengadaan</label>
                        <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control" placeholder="Contoh: Estimasi pengiriman hari Jumat jam 08.00 pagi, singkong kadar air standar pabrik.">{{ old('catatan_txt') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: RINCIAN ITEM BARANG (DETAIL TABLE - EXCEL STYLE ELEGAN) --}}
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
                                <span>Peringatan Stok Kritis ({{ $belowMinimumList->count() }})</span>
                                <svg id="chevronSafetyStock" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        @endif
                        <button type="button" onclick="addRow(true)" class="btn btn-primary btn-sm" style="font-weight: 600; background: #0284c7; padding: 0.35rem 0.85rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Baris
                        </button>
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
                            <button type="button" onclick="addAllBelowMinimumItems()" class="btn btn-sm" style="background: #d97706; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 0.25rem 0.65rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Masukkan Semua ({{ $belowMinimumList->count() }} Bahan)
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
                                            $deficit = max(1, (float)($bm->batas_minimum_qty - $bm->current_stock));
                                        @endphp
                                        <tr style="border-bottom: 1px solid #ffedd5;">
                                            <td style="padding: 0.45rem 0.75rem; font-weight: 600; color: #1e293b;">
                                                {{ $bm->barang_nm }}
                                                <span style="color: #64748b; font-size: 0.725rem; font-weight: normal;">({{ $bm->barang_cd }})</span>
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
                                                        class="btn btn-sm"
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
                                <th style="min-width: 250px; text-align: left;">Nama Komoditas / Bahan Baku <span style="color:#ef4444;">*</span></th>
                                <th style="width: 100px; text-align: center;">Satuan</th>
                                <th style="width: 130px; text-align: right;">Kuantitas <span style="color:#ef4444;">*</span></th>
                                <th style="width: 160px; text-align: right;">Harga Satuan (Rp)</th>
                                <th style="width: 160px; text-align: right;">Subtotal (Rp)</th>
                                <th style="width: 45px; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            {{-- Row pertama bawaan --}}
                            <tr class="item-row" data-index="0">
                                <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">1</td>
                                <td>
                                    <select name="items[0][barang_id]" class="form-control item-barang" onchange="updateRowSatuan(this)" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach($barangList as $b)
                                            <option value="{{ $b->barang_id }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                            </option>
                                        @endforeach
                                    </select>
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
                                <td colspan="3" style="text-align: right; padding: 0.75rem 1rem; color: #334155; font-size: 0.85rem;">Total Kuantitas:</td>
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

        {{-- ========================================================================= --}}
        {{-- KOLOM KANAN (30%): STICKY ORDER COCKPIT & TOMBOL SIMPAN SELALU MELAYANG   --}}
        {{-- ========================================================================= --}}
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
                    </div>

                    {{-- TOMBOL UTAMA: SIMPAN & TERBITKAN PO (TIDAK PERNAH TENGGELAM) --}}
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 700; background: #059669; justify-content: center; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.25); display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan &amp; Terbitkan PO
                    </button>

                    <a href="{{ route('gudang.po.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 0.65rem; font-size: 0.85rem; padding: 0.5rem;">
                        Batal &amp; Kembali ke Daftar
                    </a>
                </div>
            </div>

            {{-- KARTU PANDUAN CEPAT OPERATOR --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.85rem;">Panduan Input Cepat</strong>
                </div>
                <div style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #0284c7; font-weight: 700;">&bull;</span>
                        <span>Tekan <kbd style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd> pada kolom kuantitas/harga untuk langsung membuat baris baru.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #0284c7; font-weight: 700;">&bull;</span>
                        <span>Harga satuan otomatis terisi sesuai harga standar di master bahan baku.</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="color: #0284c7; font-weight: 700;">&bull;</span>
                        <span>Nomor PO digenerate otomatis bulanan sesuai standar pengadaan pabrik.</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
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
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barangList as $b)
                        <option value="{{ $b->barang_id }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
                </select>
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

        if (focusNew) {
            tr.querySelector('.item-barang').focus();
        }
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
        const satuan = selectedOption.dataset.satuan || '-';
        const defaultHarga = parseFloat(selectedOption.dataset.harga || 0);

        row.querySelector('.row-satuan').innerText = satuan;

        const hargaInput = row.querySelector('.item-harga');
        if (defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
            hargaInput.value = defaultHarga;
            calculateSubtotal(hargaInput);
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

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const harga = parseFloat(row.querySelector('.item-harga').value) || 0;
            totalQty += qty;
            grandTotal += (qty * harga);
        });

        const formattedGrandTotal = 'Rp ' + grandTotal.toLocaleString('id-ID');
        document.getElementById('totalQtyDisplay').innerText = totalQty.toFixed(2);
        document.getElementById('grandTotalDisplay').innerText = formattedGrandTotal;

        // Update di sticky sidebar kanan
        document.getElementById('sideGrandTotal').innerText = formattedGrandTotal;
        document.getElementById('sideTotalQty').innerText = totalQty.toFixed(2);
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
        calculateSubtotal(targetRow.querySelector('.item-qty'));
    }

    function addAllBelowMinimumItems() {
        @if (!empty($belowMinimumList) && $belowMinimumList->count() > 0)
            @foreach ($belowMinimumList as $bm)
                addBelowMinimumItem(
                    {{ $bm->barang_id }}, 
                    '{{ addslashes($bm->barang_nm) }}', 
                    '{{ $bm->satuanDasar?->satuan_nm ?? '-' }}', 
                    {{ (float)($bm->harga_beli_standar ?? 0) }}, 
                    {{ max(1, (float)($bm->batas_minimum_qty - $bm->current_stock)) }}
                );
            @endforeach
        @endif
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
</script>
@endsection
