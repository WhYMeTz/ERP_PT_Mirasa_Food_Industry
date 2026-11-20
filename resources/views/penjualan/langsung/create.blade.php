@extends('layouts.app')

@section('title', 'Penjualan Langsung Pabrik - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/direct-sales-create.css') }}?v={{ filemtime(public_path('css/penjualan/direct-sales-create.css')) }}">
@endpush

@section('content')
<div class="ds-create-container">
    {{-- HEADER ROW --}}
    <div class="ds-header-row">
        <div>
            <a href="{{ route('penjualan.langsung.index') }}" class="ds-back-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Penjualan Langsung
            </a>
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Penjualan Langsung Pabrik</h1>
                <span class="stock-tag in-stock" style="font-size: 0.75rem; padding: 3px 8px;">
                    ⚡ Potong Stok FG Otomatis + Faktur Lunas
                </span>
            </div>
            <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
                Penjualan langsung/tunai tanpa dokumen PO customer (sales kanvaser, mitra ambil di pabrik, atau order spot). Sistem otomatis memotong stok Gudang FG, mencatat pengeluaran barang, dan menerbitkan faktur lunas.
            </p>
        </div>
    </div>

    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; color: #991b1b; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; color: #991b1b; font-size: 0.85rem;">
            <div style="font-weight: 700; margin-bottom: 0.35rem;">Terdapat kesalahan pada input transaksi:</div>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penjualan.langsung.store') }}" id="directSalesForm">
        @csrf
        <input type="hidden" name="metode_bayar" id="metodeBayar" value="TUNAI">

        {{-- 1. PILIHAN KATEGORI TRANSAKSI PENJUALAN (UMUM vs SALES KELILING) --}}
        <div class="ds-category-selector-card">
            <div class="ds-category-header">
                <div class="ds-category-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Pilih Kategori Transaksi Penjualan
                </div>
                <span class="ds-category-badge" id="activeCategoryBadge">Kategori: UMUM PABRIK</span>
            </div>

            <div class="ds-category-options">
                {{-- KARTU 1: PENJUALAN UMUM PABRIK --}}
                <label class="ds-category-option {{ old('kategori_penjualan', 'UMUM') === 'UMUM' ? 'active' : '' }}" id="labelKategoriUmum">
                    <input type="radio" name="kategori_penjualan" value="UMUM" {{ old('kategori_penjualan', 'UMUM') === 'UMUM' ? 'checked' : '' }} class="ds-category-radio">
                    <div class="ds-category-content">
                        <div>
                            <div class="ds-category-name">Penjualan Umum (Pabrik)</div>
                            <div class="ds-category-desc">Pembeli / pedagang datang langsung ke gudang pabrik membawa kendaraan sendiri</div>
                        </div>
                    </div>
                </label>

                {{-- KARTU 2: PENJUALAN SALES --}}
                <label class="ds-category-option {{ old('kategori_penjualan') === 'SALES' ? 'active' : '' }}" id="labelKategoriSales">
                    <input type="radio" name="kategori_penjualan" value="SALES" {{ old('kategori_penjualan') === 'SALES' ? 'checked' : '' }} class="ds-category-radio">
                    <div class="ds-category-content">
                        <div>
                            <div class="ds-category-name">Penjualan Sales</div>
                            <div class="ds-category-desc">Setoran rekap penjualan armada kanvaser / sales motoris rute toko</div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- 2. INFORMASI DOKUMEN & IDENTITAS TRANSAKSI --}}
        <div class="ds-card">
            <div class="ds-card-header">
                <h2 class="ds-card-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span id="titleInfoDokumen">Informasi Pembeli Umum Pabrik</span>
                </h2>
            </div>
            <div class="ds-card-body">
                {{-- IDENTITAS DOKUMEN BERSAMA --}}
                <div class="ds-form-row">
                    <div class="ds-form-group">
                        <label class="ds-label" for="no_transaksi">No. Transaksi (Otomatis)</label>
                        <input type="text" id="no_transaksi" class="ds-ctrl" value="{{ $directSaleNo }}" readonly>
                    </div>
                    <div class="ds-form-group">
                        <label class="ds-label" for="tgl_transaksi">Tanggal Transaksi <span class="req">*</span></label>
                        <input type="date" name="tgl_transaksi" id="tgl_transaksi" class="ds-ctrl" value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                    </div>
                </div>

                {{-- SECTION KATEGORI 1: UMUM PABRIK --}}
                <div id="sectionKategoriUmum" style="{{ old('kategori_penjualan', 'UMUM') === 'SALES' ? 'display: none;' : '' }}">
                    <div class="ds-form-row" style="margin-top: 0.85rem;">
                        <div class="ds-form-group" style="flex: 1.2;">
                            <label class="ds-label" for="customer_id">Akun Pelanggan</label>
                            <select name="customer_id" id="customer_id" class="ds-ctrl">
                                @if ($defaultCashCustomer)
                                    <option value="{{ $defaultCashCustomer->customer_id }}" selected>
                                        {{ $defaultCashCustomer->customer_nm }} (Default Tunai)
                                    </option>
                                @endif
                                @foreach ($customers as $c)
                                    @if ($defaultCashCustomer && $c->customer_id == $defaultCashCustomer->customer_id)
                                        @continue
                                    @endif
                                    <option value="{{ $c->customer_id }}" {{ old('customer_id') == $c->customer_id ? 'selected' : '' }}>
                                        {{ $c->customer_nm }} [{{ $c->customer_cd }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ds-form-group" style="flex: 1.5;">
                            <label class="ds-label" for="nama_pembeli">Nama Pembeli / Pemesan (Opsional)</label>
                            <input type="text" name="nama_pembeli" id="nama_pembeli" class="ds-ctrl" placeholder="Dapat dikosongkan untuk pembeli umum atau isi nama pembeli..." value="{{ old('nama_pembeli') }}">
                        </div>
                    </div>
                </div>

                {{-- SECTION KATEGORI 2: SALES KELILING --}}
                <div id="sectionKategoriSales" style="{{ old('kategori_penjualan') === 'SALES' ? '' : 'display: none;' }}">
                    <div class="ds-form-row" style="margin-top: 0.85rem;">
                        <div class="ds-form-group" style="flex: 1.3;">
                            <label class="ds-label" for="sales_nm">Nama Sales / Kanvaser <span class="req">*</span></label>
                            <input type="text" name="sales_nm" id="sales_nm" list="karyawanList" class="ds-ctrl" placeholder="Ketik nama sales atau pilih karyawan..." value="{{ old('sales_nm') }}" autocomplete="off">
                            <datalist id="karyawanList">
                                @foreach ($karyawans as $k)
                                    <option value="{{ $k->karyawan_nm }}">{{ $k->karyawan_nm }} (NIK: {{ $k->nik }} - {{ $k->jabatan_nm ?? 'Karyawan' }})</option>
                                @endforeach
                            </datalist>
                            <small style="color: #64748b; font-size: 0.72rem; margin-top: 3px; display: block;">Bisa ketik nama baru atau pilih dari daftar karyawan pabrik.</small>
                        </div>
                        <div class="ds-form-group">
                            <label class="ds-label" for="rute_wilayah">Rute / Wilayah Kunjungan</label>
                            <input type="text" name="rute_wilayah" id="rute_wilayah" class="ds-ctrl" placeholder="Contoh: Solo Kota / Boyolali / Klaten" value="{{ old('rute_wilayah') }}">
                        </div>
                        <div class="ds-form-group">
                            <label class="ds-label" for="armada_sales">Armada Mobil / Motor Sales</label>
                            <input type="text" name="armada_sales" id="armada_sales" class="ds-ctrl" placeholder="Contoh: AD 9876 KL (Grand Max Box)" value="{{ old('armada_sales') }}">
                        </div>
                    </div>

                    <div class="ds-form-row" style="margin-top: 0.85rem;">
                        <div class="ds-form-group" style="flex: 1.3;">
                            <label class="ds-label" for="customer_id_sales">Akun Pelanggan Mitra (Opsional)</label>
                            <select name="customer_id_sales" id="customer_id_sales" class="ds-ctrl">
                                @if ($defaultCashCustomer)
                                    <option value="{{ $defaultCashCustomer->customer_id }}" selected>
                                        {{ $defaultCashCustomer->customer_nm }} (Default Kanvaser)
                                    </option>
                                @endif
                                @foreach ($customers as $c)
                                    @if ($defaultCashCustomer && $c->customer_id == $defaultCashCustomer->customer_id)
                                        @continue
                                    @endif
                                    <option value="{{ $c->customer_id }}" {{ old('customer_id_sales') == $c->customer_id ? 'selected' : '' }}>
                                        {{ $c->customer_nm }} [{{ $c->customer_cd }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ds-form-group">
                            <label class="ds-label" for="toko_kanvas_nm">Nama Toko / Keterangan Penjualan</label>
                            <input type="text" name="toko_kanvas_nm" id="toko_kanvas_nm" class="ds-ctrl" placeholder="Contoh: Toko Barokah / Rekap 5 Toko Rute Solo" value="{{ old('toko_kanvas_nm') }}">
                        </div>
                        <div class="ds-form-group">
                            <label class="ds-label" for="nota_manual_no">No. Nota Manual / Buku Sales</label>
                            <input type="text" name="nota_manual_no" id="nota_manual_no" class="ds-ctrl" placeholder="Contoh: BUKU-04 / NOTA-112" value="{{ old('nota_manual_no') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. ITEM PRODUK JADI SIAP JUAL (GUDANG FG) --}}
        <div class="ds-card">
            <div class="ds-card-header">
                <h2 class="ds-card-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Item Produk Siap Jual (Gudang FG)
                </h2>
                <button type="button" class="btn" id="btnAddItem" style="background: #0f172a; color: #fff; font-size: 0.775rem; font-weight: 700; padding: 0.4rem 0.85rem; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    + Tambah Baris Produk
                </button>
            </div>
            <div class="ds-card-body" style="padding: 0;">
                <div style="overflow-x: auto; width: 100%;">
                    <table class="ds-items-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="min-width: 260px;">Produk Siap Jual</th>
                                <th style="width: 140px; text-align: center;">Ketersediaan Stok</th>
                                <th style="width: 100px; text-align: center;">Qty</th>
                                <th style="width: 80px; text-align: center;">Satuan</th>
                                <th style="width: 135px; text-align: right;">Harga Satuan (Rp)</th>
                                <th style="width: 90px; text-align: center;">Diskon (%)</th>
                                <th style="width: 145px; text-align: right;">Subtotal (Rp)</th>
                                <th style="width: 50px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            {{-- BARIS AWAL 1 --}}
                            <tr class="item-row" data-index="0">
                                <td style="text-align: center; color: #64748b; font-weight: 700;" class="row-num">1</td>
                                <td>
                                    <select name="items[0][barang_id]" class="ds-ctrl product-select" required>
                                        <option value="" data-price="0" data-stock="0">-- Pilih Produk Jadi --</option>
                                        @foreach ($products as $prod)
                                            <option value="{{ $prod->barang_id }}"
                                                data-price="{{ $prod->harga_jual ?? 0 }}"
                                                data-stock="{{ $prod->ready_stock }}"
                                                data-unit="{{ $prod->satuanDasar?->satuan_nm ?? 'Karton' }}">
                                                {{ $prod->barang_nm }} [{{ $prod->barang_cd }}] - Stok: {{ number_format($prod->ready_stock, 0) }} {{ $prod->satuanDasar?->satuan_nm ?? 'Karton' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="text-align: center;">
                                    <div class="stock-info">-</div>
                                </td>
                                <td>
                                    <input type="number" name="items[0][qty]" class="ds-ctrl qty-input" value="1" min="0.0001" step="any" style="text-align: center; font-weight: 700;" required>
                                </td>
                                <td style="text-align: center;">
                                    <span class="unit-display" style="font-size: 0.775rem; color: #64748b; font-weight: 600;">Karton</span>
                                </td>
                                <td>
                                    <input type="number" name="items[0][harga_satuan]" class="ds-ctrl price-input" value="0" min="0" step="any" style="text-align: right; font-weight: 600;" required>
                                </td>
                                <td>
                                    <input type="number" name="items[0][diskon_persen]" class="ds-ctrl disc-input" value="0" min="0" max="100" step="0.1" style="text-align: center;">
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">
                                    <span class="subtotal-display" data-value="0">Rp 0</span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-remove-row" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px;" title="Hapus Baris">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. BOTTOM SECTION: PEMBAYARAN & CATATAN (KIRI) vs RINGKASAN FINANSIAL (KANAN) --}}
        <div class="ds-bottom-grid">
            {{-- Sisi Kiri: Metode Pembayaran & Catatan --}}
            <div class="ds-payment-card">
                <div class="ds-pay-methods-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Metode Pembayaran
                </div>
                <div class="ds-pay-tabs">
                    <button type="button" class="ds-pay-tab-btn active" id="tabTunai">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>TUNAI / CASH</span>
                    </button>
                    <button type="button" class="ds-pay-tab-btn" id="tabTransfer">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>TRANSFER BANK</span>
                    </button>
                </div>

                <div class="ds-form-group" style="margin-top: 1rem;">
                    <label class="ds-label" for="catatan_txt">Catatan Transaksi &amp; Instruksi Khusus</label>
                    <textarea name="catatan_txt" id="catatan_txt" rows="3" class="ds-ctrl" style="height: auto; font-size: 0.8125rem; padding: 0.5rem;" placeholder="Catatan tambahan untuk nota/faktur penjualan, instruksi pengiriman, dll...">{{ old('catatan_txt') }}</textarea>
                </div>
            </div>

            {{-- Sisi Kanan: Ringkasan Finansial --}}
            <div class="ds-financial-card">
                <div class="ds-financial-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Ringkasan Finansial Transaksi
                </div>

                <div class="ds-financial-row">
                    <span style="font-weight: 600;">Subtotal Nilai Bruto</span>
                    <strong id="displaySubtotalBruto" style="color: #0f172a; font-family: monospace; font-size: 0.95rem;">Rp 0</strong>
                </div>

                <div class="ds-financial-row" style="align-items: center;">
                    <span style="font-weight: 600;">Potongan Diskon Langsung (-)</span>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="font-size: 0.775rem; color: #64748b; font-weight: 700;">Rp</span>
                        <input type="number" name="potongan_nominal" id="potonganGlobal" value="0" min="0" step="any"
                            style="width: 120px; height: 30px; padding: 2px 8px; font-size: 0.8125rem; text-align: right; font-weight: 700; border: 1.5px solid #cbd5e1; border-radius: 5px;">
                    </div>
                </div>

                <div class="ds-financial-row">
                    <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.8125rem; color: #334155; cursor: pointer; font-weight: 600; margin: 0;">
                        <input type="checkbox" name="is_ppn" id="isPpnCheck" value="1" style="accent-color: #059669; width: 16px; height: 16px;">
                        <span>Kenakan PPN (11%)</span>
                    </label>
                    <span id="displayPpnStatus" style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Non-PPN</span>
                </div>

                <div class="ds-financial-row" id="rowDpp" style="display: none;">
                    <span style="font-weight: 700; color: #334155;">Dasar Pengenaan Pajak (DPP)</span>
                    <span id="displayDpp" style="font-weight: 700; font-family: monospace; color: #0f172a; font-size: 0.95rem;">Rp 0</span>
                </div>

                <div class="ds-financial-row" id="rowPpn" style="display: none;">
                    <span style="font-weight: 600; color: #7c3aed;">Pajak Pertambahan Nilai (PPN 11%)</span>
                    <span id="displayPpn" style="font-weight: 700; font-family: monospace; color: #7c3aed; font-size: 0.95rem;">+ Rp 0</span>
                </div>

                {{-- Grand Total Box --}}
                <div class="ds-grand-total-box">
                    <div>
                        <span class="ds-grand-total-label">TOTAL TAGIHAN AKHIR</span>
                        <span class="ds-grand-total-sub">Otomatis Terbit Faktur Lunas</span>
                    </div>
                    <div class="ds-grand-total-val" id="displayGrandTotal" data-grand-total="0">
                        Rp 0
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="ds-action-bar">
                    <a href="{{ route('penjualan.langsung.index') }}" class="ds-btn-cancel">
                        Batal
                    </a>
                    <button type="submit" class="ds-btn-submit" id="btnSubmitDirectSale">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Selesaikan Penjualan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- TEMPLATE BARIS PRODUK --}}
<template id="itemRowTemplate">
    <td style="text-align: center; color: #64748b; font-weight: 700;" class="row-num">__INDEX__</td>
    <td>
        <select name="items[__INDEX__][barang_id]" class="ds-ctrl product-select" required>
            <option value="" data-price="0" data-stock="0">-- Pilih Produk Jadi --</option>
            @foreach ($products as $prod)
                <option value="{{ $prod->barang_id }}"
                    data-price="{{ $prod->harga_jual ?? 0 }}"
                    data-stock="{{ $prod->ready_stock }}"
                    data-unit="{{ $prod->satuanDasar?->satuan_nm ?? 'Karton' }}">
                    {{ $prod->barang_nm }} [{{ $prod->barang_cd }}] - Stok: {{ number_format($prod->ready_stock, 0) }} {{ $prod->satuanDasar?->satuan_nm ?? 'Karton' }}
                </option>
            @endforeach
        </select>
    </td>
    <td style="text-align: center;">
        <div class="stock-info">-</div>
    </td>
    <td>
        <input type="number" name="items[__INDEX__][qty]" class="ds-ctrl qty-input" value="1" min="0.0001" step="any" style="text-align: center; font-weight: 700;" required>
    </td>
    <td style="text-align: center;">
        <span class="unit-display" style="font-size: 0.775rem; color: #64748b; font-weight: 600;">Karton</span>
    </td>
    <td>
        <input type="number" name="items[__INDEX__][harga_satuan]" class="ds-ctrl price-input" value="0" min="0" step="any" style="text-align: right; font-weight: 600;" required>
    </td>
    <td>
        <input type="number" name="items[__INDEX__][diskon_persen]" class="ds-ctrl disc-input" value="0" min="0" max="100" step="0.1" style="text-align: center;">
    </td>
    <td style="text-align: right; font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">
        <span class="subtotal-display" data-value="0">Rp 0</span>
    </td>
    <td style="text-align: center;">
        <button type="button" class="btn-remove-row" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px;" title="Hapus Baris">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </td>
</template>
@endsection

@push('scripts')
    <script>
        window.directSalesConfig = {
            generateCodeUrl: '{{ route("ajax.generate_code") }}'
        };
    </script>
    <script src="{{ asset('js/penjualan/direct-sales-create.js') }}?v={{ filemtime(public_path('js/penjualan/direct-sales-create.js')) }}"></script>
@endpush
