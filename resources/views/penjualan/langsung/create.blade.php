@extends('layouts.app')

@section('title', 'Kasir Penjualan Langsung - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/direct-sales-create.css') }}">
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
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Kasir Cepat &amp; Penjualan Langsung</h1>
                <span class="stock-tag in-stock" style="font-size: 0.75rem; padding: 3px 8px;">
                    ⚡ 1-Klik: Selesai + Potong Stok FIFO
                </span>
            </div>
            <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
                Penjualan langsung/tunai tanpa PO customer. Sistem otomatis memotong stok Gudang FG, mencatat pengeluaran barang, dan menerbitkan faktur lunas.
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

        <div class="ds-cashier-grid">
            {{-- KOLOM KIRI: FORM & ITEM KERANJANG --}}
            <div>
                {{-- KARTU 1: INFORMASI TRANSAKSI --}}
                <div class="ds-card">
                    <div class="ds-card-header">
                        <h2 class="ds-card-title">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Informasi Dokumen &amp; Pelanggan
                        </h2>
                    </div>
                    <div class="ds-card-body">
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

                        <div class="ds-form-row">
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
                            <div class="ds-form-group">
                                <label class="ds-label" for="nama_pembeli">Nama Pembeli / Toko</label>
                                <input type="text" name="nama_pembeli" id="nama_pembeli" class="ds-ctrl" placeholder="Contoh: Toko Berkah / Bpk. Rudi" value="{{ old('nama_pembeli') }}">
                            </div>
                            <div class="ds-form-group">
                                <label class="ds-label" for="nopol_kendaraan">No. Polisi / Kendaraan</label>
                                <input type="text" name="nopol_kendaraan" id="nopol_kendaraan" class="ds-ctrl" placeholder="Contoh: AA 1234 XY" value="{{ old('nopol_kendaraan') }}">
                            </div>
                        </div>

                        <div class="ds-form-group">
                            <label class="ds-label" for="catatan_txt">Catatan Transaksi</label>
                            <input type="text" name="catatan_txt" id="catatan_txt" class="ds-ctrl" placeholder="Catatan tambahan untuk nota penjualan..." value="{{ old('catatan_txt') }}">
                        </div>
                    </div>
                </div>

                {{-- KARTU 2: DAFTAR PRODUK (KERANJANG PENJUALAN) --}}
                <div class="ds-card">
                    <div class="ds-card-header">
                        <h2 class="ds-card-title">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Item Produk Siap Jual (Gudang FG)
                        </h2>
                        <button type="button" class="btn" id="btnAddItem" style="background: #0f172a; color: #fff; font-size: 0.775rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Baris Produk
                        </button>
                    </div>
                    <div class="ds-card-body" style="padding: 0;">
                        <div style="overflow-x: auto;">
                            <table class="ds-items-table">
                                <thead>
                                    <tr>
                                        <th style="min-width: 250px;">Produk Siap Jual</th>
                                        <th style="width: 100px; text-align: center;">Qty</th>
                                        <th style="width: 140px; text-align: right;">Harga Satuan</th>
                                        <th style="width: 90px; text-align: center;">Diskon (%)</th>
                                        <th style="width: 140px; text-align: right;">Subtotal</th>
                                        <th style="width: 50px; text-align: center;">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    {{-- BARIS AWAL 1 --}}
                                    <tr class="item-row" data-index="0">
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
                                            <div class="stock-info" style="margin-top: 4px;"></div>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][qty]" class="ds-ctrl qty-input" value="1" min="0.0001" step="any" style="text-align: center; font-weight: 700;" required>
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
            </div>

            {{-- KOLOM KANAN: RINGKASAN PEMBAYARAN & KASIR (STICKY) --}}
            <div class="ds-billing-sticky">
                {{-- RINGKASAN HARGA --}}
                <div class="ds-billing-summary">
                    <div style="font-size: 0.825rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.75rem;">
                        Ringkasan Pembayaran
                    </div>

                    <div class="ds-summary-row">
                        <span>Subtotal Belanja</span>
                        <span id="displaySubtotalBruto" style="font-weight: 700; color: #0f172a;">Rp 0</span>
                    </div>

                    <div class="ds-summary-row" style="align-items: flex-start;">
                        <span style="font-size: 0.8rem; margin-top: 4px;">Potongan Diskon (Rp)</span>
                        <div style="width: 140px;">
                            <input type="number" name="potongan_nominal" id="potonganGlobal" class="ds-ctrl" value="0" min="0" step="any" style="text-align: right; height: 32px; font-size: 0.8rem;">
                        </div>
                    </div>

                    <div class="ds-summary-row" style="margin-top: 0.25rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: #475569; cursor: pointer;">
                            <input type="checkbox" name="is_ppn" id="isPpnCheck" value="1">
                            <span>Kenakan PPN (11%)</span>
                        </label>
                    </div>

                    <div class="ds-summary-row" id="rowDpp" style="display: none;">
                        <span>Dasar Pengenaan Pajak (DPP)</span>
                        <span id="displayDpp" style="font-weight: 600;">Rp 0</span>
                    </div>

                    <div class="ds-summary-row" id="rowPpn" style="display: none;">
                        <span>PPN 11%</span>
                        <span id="displayPpn" style="font-weight: 600; color: #7c3aed;">Rp 0</span>
                    </div>

                    <div class="ds-summary-row total-box">
                        <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">TOTAL TAGIHAN</span>
                        <span id="displayGrandTotal" class="ds-grand-total" data-grand-total="0">Rp 0</span>
                    </div>
                </div>

                {{-- KOTAK KASIR TENDER --}}
                <div class="ds-tender-card">
                    <div style="font-size: 0.825rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.35rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Metode Pembayaran
                    </div>

                    <div class="ds-pay-tabs">
                        <button type="button" class="ds-pay-tab-btn active" id="tabTunai">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            TUNAI
                        </button>
                        <button type="button" class="ds-pay-tab-btn" id="tabTransfer">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            TRANSFER
                        </button>
                    </div>

                    <div id="cashTenderSection">
                        <label class="ds-label" for="uangDiterima">Nominal Uang Diterima (Rp)</label>
                        <input type="number" name="uang_diterima" id="uangDiterima" class="ds-ctrl" placeholder="0" min="0" step="any" style="font-size: 1.15rem; font-weight: 700; color: #0f172a; text-align: right; height: 42px;">

                        <div class="ds-quick-cash-row">
                            <button type="button" class="ds-quick-btn" data-val="exact">Uang Pas</button>
                            <button type="button" class="ds-quick-btn" data-val="50000">50.000</button>
                            <button type="button" class="ds-quick-btn" data-val="100000">100.000</button>
                            <button type="button" class="ds-quick-btn" data-val="200000">200.000</button>
                            <button type="button" class="ds-quick-btn" data-val="500000">500.000</button>
                        </div>

                        <div class="ds-kembalian-box" id="kembalianBox">
                            <span style="font-size: 0.8rem; font-weight: 600; color: #475569;">Kembalian</span>
                            <span id="displayKembalian" class="ds-kembalian-val">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" class="ds-submit-btn" id="btnSubmitCashier">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Selesaikan Transaksi</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- TEMPLATE BARIS PRODUK --}}
<template id="itemRowTemplate">
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
        <div class="stock-info" style="margin-top: 4px;"></div>
    </td>
    <td>
        <input type="number" name="items[__INDEX__][qty]" class="ds-ctrl qty-input" value="1" min="0.0001" step="any" style="text-align: center; font-weight: 700;" required>
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
    <script src="{{ asset('js/penjualan/direct-sales-create.js') }}"></script>
@endpush
