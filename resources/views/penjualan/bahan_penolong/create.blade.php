@extends('layouts.app')

@section('title', 'Penjualan Bahan Penolong & Bumbu - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/bahan_penolong/bp-form.css') }}?v={{ filemtime(public_path('css/penjualan/bahan_penolong/bp-form.css')) }}">
@endpush

@section('content')
<div class="ds-create-container">
    {{-- HEADER ROW --}}
    <div class="ds-header-row">
        <div>
            <a href="{{ route('penjualan.bahan-penolong.index') }}" class="ds-back-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Penjualan Bahan Penolong
            </a>
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Penjualan Bahan Penolong &amp; Bumbu</h1>
            </div>
            <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
                Penjualan persediaan karton dus, kemasan, plastik, bumbu balado/seasoning curah. Sistem otomatis memotong stok gudang bahan/kemasan dan menerbitkan faktur lunas.
            </p>
        </div>
    </div>

    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; color: #991b1b; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; color: #991b1b; font-size: 0.85rem;">
            <div style="font-weight: 700; margin-bottom: 0.35rem;">Terdapat kesalahan pada input transaksi:</div>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penjualan.bahan-penolong.store') }}" id="bpForm">
        @csrf
        <input type="hidden" name="metode_bayar" id="metodeBayar" value="TUNAI">
        <input type="hidden" name="nopol_kendaraan" value="AMBIL SENDIRI">
        <input type="hidden" name="customer_id" value="{{ $defaultCashCustomer?->customer_id }}">

        {{-- 1. INFORMASI DOKUMEN & IDENTITAS TRANSAKSI --}}
        <div class="ds-card">
            <div class="ds-card-header">
                <h2 class="ds-card-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Informasi Pembeli &amp; Transaksi</span>
                </h2>
            </div>
            <div class="ds-card-body">
                <div class="ds-form-row">
                    <div class="ds-form-group">
                        <label class="ds-label" for="no_transaksi">No. Transaksi (Otomatis)</label>
                        <input type="text" id="no_transaksi" class="ds-ctrl" value="{{ $transaksiNo }}" readonly>
                    </div>
                    <div class="ds-form-group">
                        <label class="ds-label" for="tgl_transaksi">Tanggal Transaksi <span class="req" style="color:#ef4444;">*</span></label>
                        <input type="date" name="tgl_transaksi" id="tgl_transaksi" class="ds-ctrl" value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                    </div>
                    <div class="ds-form-group">
                        <label class="ds-label" for="gudang_id">Gudang Asal Barang <span class="req" style="color:#ef4444;">*</span></label>
                        <select name="gudang_id" id="gudang_id" class="ds-ctrl" required style="font-weight: 600;">
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $defaultGudangId) == $gdg->gudang_id) ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ds-form-group">
                        <label class="ds-label" for="nama_pembeli">Nama Pembeli (Opsional)</label>
                        <input type="text" name="nama_pembeli" id="nama_pembeli" class="ds-ctrl" placeholder="Contoh: Pak Slamet / Pengepul Karton..." value="{{ old('nama_pembeli') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. TABEL ITEM BAHAN PENOLONG & BUMBU (STANDAR ENTERPRISE) --}}
        <div class="ds-card">
            <div class="ds-card-header">
                <h2 class="ds-card-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Item Bahan Penolong, Kemasan &amp; Bumbu</span>
                </h2>
                <button type="button" class="btn" id="btnAddItem" style="background: #0f172a; color: #fff; font-size: 0.775rem; font-weight: 700; padding: 0.4rem 0.85rem; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Tambah Baris Bahan
                </button>
            </div>
            <div class="ds-card-body" style="padding: 0;">
                <div style="overflow-x: auto; width: 100%;">
                    <table class="ds-items-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="min-width: 260px;">Bahan Penolong / Bumbu / Kemasan</th>
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
                                        <option value="" data-price="0" data-stock="0">-- Pilih Bahan Penolong / Bumbu / Kemasan --</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->barang_id }}"
                                                data-price="{{ $item->harga_jual ?: ($item->harga_beli_standar ?? 0) }}"
                                                data-stock="{{ $item->ready_stock }}"
                                                data-stocks='@json($item->stocks_by_gudang)'
                                                data-name="{{ $item->barang_nm }}"
                                                data-code="{{ $item->barang_cd }}"
                                                data-unit="{{ $item->satuanDasar?->satuan_nm ?? '-' }}">
                                                {{ $item->barang_nm }} [{{ $item->barang_cd }}] - Stok: {{ number_format($item->ready_stock, 0) }} {{ $item->satuanDasar?->satuan_nm ?? '-' }}
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
                                    <span class="unit-display" style="font-size: 0.775rem; color: #64748b; font-weight: 600;">-</span>
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

        {{-- 3. BOTTOM SECTION: PEMBAYARAN & CATATAN (KIRI) vs RINGKASAN FINANSIAL (KANAN) --}}
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
                    <button type="button" class="ds-pay-tab-btn" id="tabTempo">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>TEMPO</span>
                    </button>
                </div>

                <div class="ds-form-group" style="margin-top: 1rem;">
                    <label class="ds-label" for="catatan_txt">Catatan Transaksi &amp; Keterangan Tambahan</label>
                    <textarea name="catatan_txt" id="catatan_txt" rows="3" class="ds-ctrl" style="height: auto; font-size: 0.8125rem; padding: 0.5rem;" placeholder="Catatan tambahan untuk nota/faktur penjualan, pihak pembeli, dll...">{{ old('catatan_txt') }}</textarea>
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
                    <a href="{{ route('penjualan.bahan-penolong.index') }}" class="ds-btn-cancel">
                        Batal
                    </a>
                    <button type="submit" class="ds-btn-submit" id="btnSubmitBp">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Proses Penjualan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- TEMPLATE BARIS ITEM TAMBAHAN --}}
<template id="itemRowTemplate">
    <tr class="item-row" data-index="__INDEX__">
        <td style="text-align: center; color: #64748b; font-weight: 700;" class="row-num">__NUM__</td>
        <td>
            <select name="items[__INDEX__][barang_id]" class="ds-ctrl product-select" required>
                <option value="" data-price="0" data-stock="0">-- Pilih Bahan Penolong / Bumbu / Kemasan --</option>
                @foreach ($items as $item)
                    <option value="{{ $item->barang_id }}"
                        data-price="{{ $item->harga_jual ?: ($item->harga_beli_standar ?? 0) }}"
                        data-stock="{{ $item->ready_stock }}"
                        data-stocks='@json($item->stocks_by_gudang)'
                        data-name="{{ $item->barang_nm }}"
                        data-code="{{ $item->barang_cd }}"
                        data-unit="{{ $item->satuanDasar?->satuan_nm ?? '-' }}">
                        {{ $item->barang_nm }} [{{ $item->barang_cd }}] - Stok: {{ number_format($item->ready_stock, 0) }} {{ $item->satuanDasar?->satuan_nm ?? '-' }}
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
            <span class="unit-display" style="font-size: 0.775rem; color: #64748b; font-weight: 600;">-</span>
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
    </tr>
</template>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/bahan_penolong/bp-form.js') }}?v={{ filemtime(public_path('js/penjualan/bahan_penolong/bp-form.js')) }}"></script>
@endpush
