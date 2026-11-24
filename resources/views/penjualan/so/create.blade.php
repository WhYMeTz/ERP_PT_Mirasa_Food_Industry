@extends('layouts.app')

@section('title', 'PO Penjualan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-workstation.css') }}?v={{ filemtime(public_path('css/gudang/po/po-workstation.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/so/so-form.css') }}?v={{ filemtime(public_path('css/penjualan/so/so-form.css')) }}">
@endpush

@section('content')
<div class="so-form-container">
    {{-- PAGE HEADER & TOMBOL KEMBALI --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('penjualan.so.index') }}" class="so-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar PO Penjualan
            </a>
            <div class="so-title-row">
                <h1 class="so-page-title">PO Penjualan</h1>
            </div>
            <p class="so-page-subtitle">
                Penerbitan dokumen pesanan penjualan (Sales Order) komersial untuk mitra distributor dan customer.
            </p>
        </div>

        {{-- Live KPI Widget Ramping di Siku Kanan Atas --}}
        <div class="po-live-kpi-widget">
            <div>
                <div class="po-live-kpi-title">ESTIMASI TOTAL TAGIHAN:</div>
                <div class="po-live-kpi-val" id="topLiveGrandTotal">Rp 0</div>
            </div>
            <div style="height: 28px; width: 1.5px; background: #e2e8f0;"></div>
            <div style="font-size: 0.775rem; line-height: 1.35; color: #475569;">
                <div>Total Baris: <strong id="topLiveItemCount" style="color: #0f172a; font-family: monospace;">1 Item</strong></div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.7rem; color: #059669; font-weight: 700;">Mode Aktif</span>
                    <span id="badgeTopIndicator" class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.65rem;">Menunggu PPN</span>
                </div>
            </div>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 0.85rem; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terdapat kesalahan pengisian data pesanan:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penjualan.so.store') }}" id="formPo">
        @csrf

        {{-- 2. TOOLBAR PARAMETER PESANAN (UNIFIED COMPACT CARD - 4 KOLOM X 3 BARIS SIMETRIS) --}}
        <div class="po-toolbar-card">
            {{-- Baris Atas: Klasifikasi Pelanggan & Kategori Dokumen --}}
            <div class="po-mode-toolbar-wrap">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <span style="font-size: 0.725rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                        Klasifikasi Pelanggan:
                    </span>
                    <span id="badgeKlasifikasi" style="display: inline-block; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.725rem; padding: 2px 8px; border-radius: 4px;">
                        Belum Memilih Customer
                    </span>
                    <span id="textWilayah" style="font-size: 0.75rem; color: #64748b; font-weight: 600;">-</span>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.725rem; color: #64748b;">
                        Dokumen: <strong style="color: #0f172a;">Sales Order Resmi (SO)</strong>
                    </span>
                </div>
            </div>

            {{-- GRID UNIFIED 4 KOLOM X 3 BARIS (SIMETRIS, PRESISI & BERURUTAN SECARA ALUR KERJA OPERASIONAL) --}}
            <div class="po-toolbar-grid">
                {{-- [BARIS 1 - IDENTITAS DOKUMEN & MITRA PELANGGAN] --}}
                {{-- [R1-C1] Tanggal Pesanan --}}
                <div>
                    <label for="so_tgl" class="po-field-label">
                        Tanggal Pesanan <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="so_tgl" name="so_tgl" value="{{ old('so_tgl', date('Y-m-d')) }}" class="po-field-input" required>
                </div>

                {{-- [R1-C2] Nomor Pesanan (SO) --}}
                <div>
                    <label for="so_no" class="po-field-label">
                        Nomor Pesanan (SO) <span class="badge" style="background:#e0f2fe; color:#0284c7; font-size:0.625rem; margin-left:0.2rem;">Auto</span>
                    </label>
                    <input type="text" id="so_no" name="so_no" value="{{ old('so_no', $soNo) }}" readonly class="po-field-input" style="background: #f8fafc; font-family: monospace; color: #0284c7; font-weight: 700; cursor: not-allowed;" required>
                </div>

                {{-- [R1-C3] Pelanggan / Customer --}}
                <div>
                    <label for="customerSelect" class="po-field-label">
                        Pelanggan / Customer <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="customer_id" id="customerSelect" required class="po-field-select">
                        <option value="">-- Pilih Mitra Pelanggan --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->customer_id }}"
                                    data-code="{{ $cust->customer_cd }}"
                                    data-contact="{{ $cust->kontak_no }}"
                                    data-address="{{ $cust->alamat_txt }}"
                                    data-klasifikasi="{{ $cust->klasifikasi }}"
                                    data-wilayah="{{ $cust->wilayah }}"
                                    data-kode-pelanggan="{{ $cust->kode_pelanggan }}"
                                    data-top="{{ $cust->top_txt }}"
                                    data-default-ppn="{{ $cust->default_ppn }}"
                                    {{ old('customer_id') == $cust->customer_id ? 'selected' : '' }}>
                                {{ $cust->customer_nm }} ({{ $cust->customer_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R1-C4] Kode Pelanggan --}}
                <div>
                    <label for="customerCdInput" class="po-field-label">
                        Kode Pelanggan <span class="badge" style="background:#f1f5f9; color:#475569; font-size:0.625rem; margin-left:0.2rem;">Auto</span>
                    </label>
                    <input type="text" id="customerCdInput" readonly class="po-field-input" placeholder="Pilih customer..." style="background: #f8fafc; font-family: monospace; color: #0284c7; font-weight: 700; cursor: not-allowed;">
                </div>

                {{-- [BARIS 2 - KOMERSIAL & PERPAJAKAN FAKTUR] --}}
                {{-- [R2-C1] No. PO / Referensi Nota --}}
                <div>
                    <label for="customer_po_no" id="lblCustomerPoNo" class="po-field-label">
                        No. Referensi PO / Nota
                    </label>
                    <input type="text" id="customer_po_no" name="customer_po_no" value="{{ old('customer_po_no') }}" placeholder="Contoh: 450123 / J.02.03..." class="po-field-input" style="font-family: monospace;">
                </div>

                {{-- [R2-C2] Syarat Pembayaran (TOP) --}}
                @php
                    $initTopTxt = old('top_txt', '');
                    $initVal = 0;
                    $initUnit = 'HARI';
                    if (!empty($initTopTxt)) {
                        $upTop = strtoupper(trim($initTopTxt));
                        if (str_contains($upTop, 'NOTA')) {
                            preg_match('/\d+/', $upTop, $mTop);
                            $initVal = isset($mTop[0]) ? (int)$mTop[0] : 0;
                            $initUnit = 'NOTA';
                        } elseif (preg_match('/\d+/', $upTop, $mTop)) {
                            $initVal = (int)$mTop[0];
                            $initUnit = 'HARI';
                        }
                    }
                @endphp
                <div>
                    <label for="top_val" class="po-field-label">
                        Syarat Pembayaran (TOP)
                    </label>
                    <div style="display: flex; gap: 0.35rem; align-items: stretch;">
                        <input type="number" id="top_val" min="0" max="365" class="po-field-input" 
                               style="width: 75px; text-align: center; font-weight: 700; flex-shrink: 0;" 
                               placeholder="0" value="{{ $initVal }}">
                        <select id="top_unit" class="po-field-select" style="flex: 1;">
                            <option value="HARI" {{ $initUnit === 'HARI' ? 'selected' : '' }}>Hari</option>
                            <option value="NOTA" {{ $initUnit === 'NOTA' ? 'selected' : '' }}>Nota</option>
                        </select>
                    </div>
                    <input type="hidden" id="top_txt" name="top_txt" value="{{ old('top_txt', $initTopTxt ?: ($initVal . ' ' . ($initUnit === 'NOTA' ? 'Nota' : 'Hari'))) }}">
                </div>

                {{-- [R2-C3] Pajak Faktur (PPN) --}}
                <div>
                    <label for="ppnTipeHeader" class="po-field-label">
                        Pajak Faktur (PPN) <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="ppn_tipe_header" id="ppnTipeHeader" class="po-field-select" required>
                        <option value="" {{ old('ppn_tipe_header') ? '' : 'selected' }}>-- Pilih Status PPN --</option>
                        <option value="PPN_11" {{ old('ppn_tipe_header') === 'PPN_11' ? 'selected' : '' }}>Ber-PPN (11%)</option>
                        <option value="NON_PPN" {{ old('ppn_tipe_header') === 'NON_PPN' ? 'selected' : '' }}>Non-PPN</option>
                    </select>
                </div>

                {{-- [R2-C4] Nomor Faktur Penjualan --}}
                <div>
                    <label for="faktur_no" class="po-field-label">
                        <span>Nomor Faktur Penjualan</span>
                        <span id="badgeFakturFormat" class="badge" style="background:#fef3c7; color:#b45309; font-size:0.625rem; margin-left:0.2rem;">Pilih PPN Dulu</span>
                    </label>
                    <input type="text" id="faktur_no" name="faktur_no" value="{{ old('faktur_no', $fakturNo) }}" placeholder="Pilih status PPN terlebih dahulu..." class="po-field-input" style="font-family: monospace; font-weight: 700;" required>
                </div>

                {{-- [BARIS 3 - LOGISTIK & PENGIRIMAN ARMADA] --}}
                {{-- [R3-C1] Estimasi Pengiriman --}}
                <div>
                    <label for="tgl_kirim_estimasi" class="po-field-label">
                        Estimasi Pengiriman
                    </label>
                    <input type="date" id="tgl_kirim_estimasi" name="tgl_kirim_estimasi" value="{{ old('tgl_kirim_estimasi', date('Y-m-d', strtotime('+3 days'))) }}" class="po-field-input">
                </div>

                {{-- [R3-C2] No. Surat Jalan --}}
                <div>
                    <label for="surat_jalan_no" class="po-field-label">
                        No. Surat Jalan <span class="badge" style="background:#ecfdf5; color:#047857; font-size:0.625rem; margin-left:0.2rem;">Auto SJ</span>
                    </label>
                    <input type="text" id="surat_jalan_no" name="surat_jalan_no" value="{{ old('surat_jalan_no', $suratJalanNo ?? '') }}" readonly class="po-field-input" style="background: #f8fafc; font-family: monospace; font-weight: 700; color: #059669; cursor: not-allowed;">
                </div>

                {{-- [R3-C3] Nama Supir / Pengemudi --}}
                <div>
                    <label for="supir_nm" class="po-field-label">
                        Supir / Pengemudi <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.625rem; margin-left:0.2rem;">Opsional</span>
                    </label>
                    <input type="text" id="supir_nm" name="supir_nm" value="{{ old('supir_nm') }}" placeholder="Nama supir (jika sudah dijadwalkan)..." class="po-field-input">
                </div>

                {{-- [R3-C4] No. Polisi Armada Truk --}}
                <div>
                    <label for="nopol_kendaraan" class="po-field-label">
                        No. Polisi Truk <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.625rem; margin-left:0.2rem;">Opsional</span>
                    </label>
                    <input type="text" id="nopol_kendaraan" name="nopol_kendaraan" value="{{ old('nopol_kendaraan') }}" placeholder="Contoh: AD 8234 KL..." class="po-field-input" style="text-transform: uppercase; font-family: monospace;">
                </div>
            </div>

            {{-- Customer Info Preview Box (Alamat & Kontak) --}}
            <div id="customerInfoBox" class="so-customer-preview" style="display: none; margin-top: 0.85rem;">
                <div class="so-preview-grid">
                    <div>
                        <span class="so-preview-label">Alamat Pengiriman:</span>
                        <div class="so-preview-value" id="custAddressText">-</div>
                    </div>
                    <div>
                        <span class="so-preview-label">Kontak / Telepon:</span>
                        <div class="so-preview-value" id="custContactText">-</div>
                    </div>
                    <div>
                        <span class="so-preview-label">Ref. Mentor / Wilayah:</span>
                        <div class="so-preview-value" id="custRefText">-</div>
                    </div>
                </div>
            </div>

            {{-- DYNAMIC CUSTOMER TOP & CREDIT WARNING INTERLOCKING CARD --}}
            <div id="customerTopWarningCard" class="so-top-warning-card" style="display: none; margin-top: 0.85rem;"></div>
        </div>

        {{-- 3. KARTU TABEL WORKSTATION PRODUK (FULL-WIDTH 100% EXCEL GRID) --}}
        @php
            $fgCount = $barangs->filter(function($b) {
                $cd = strtoupper($b->jenisBarang?->jenis_barang_cd ?? $b->jenis_barang_cd ?? 'FG');
                return $cd === 'FG' || str_contains($cd, 'JADI') || !str_contains($cd, 'WIP');
            })->count();
            $wipCount = $barangs->filter(function($b) {
                $cd = strtoupper($b->jenisBarang?->jenis_barang_cd ?? $b->jenis_barang_cd ?? '');
                return $cd === 'WIP' || str_contains($cd, 'SETENGAH');
            })->count();
        @endphp
        <div class="po-workstation-table-card">
            {{-- Card Header: Filter Kategori & Hint Baris --}}
            <div style="background: #ffffff; padding: 0.55rem 0.85rem; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <strong style="color: #0f172a; font-size: 0.875rem; white-space: nowrap;">Item Produk Pesanan:</strong>
                    <div class="category-segmented-control" role="group" aria-label="Filter Kategori Produk">
                        <button type="button" class="category-segment-btn active" data-filter="ALL" onclick="filterProdukCategory('ALL', this)">
                            <span>Semua Produk</span>
                            <span class="category-segment-count">{{ $barangs->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-filter="FG" onclick="filterProdukCategory('FG', this)">
                            <span>Barang Jadi (FG)</span>
                            <span class="category-segment-count">{{ $fgCount }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-filter="WIP" onclick="filterProdukCategory('WIP', this)">
                            <span>Setengah Jadi (WIP)</span>
                            <span class="category-segment-count">{{ $wipCount }}</span>
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span style="font-size: 0.725rem; color: #64748b;">
                        Tekan <kbd style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd> untuk baris baru
                    </span>
                </div>
            </div>

            {{-- Tabel Excel Grid Utama --}}
            <div style="overflow-x: auto;">
                <table class="excel-grid-table" id="itemsTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">No</th>
                            <th style="min-width: 270px; text-align: left;">Produk / Barang Jadi <span style="color:#ef4444;">*</span></th>
                            <th style="width: 85px; text-align: right;">Jumlah <span style="color:#ef4444;">*</span></th>
                            <th style="width: 100px; text-align: center;">Satuan</th>
                            <th style="width: 120px; text-align: right;">Harga Satuan <span style="color:#ef4444;">*</span></th>
                            <th style="width: 160px; text-align: left;">Estimasi Margin (HPP)</th>
                            <th style="width: 75px; text-align: right;">Diskon (%)</th>
                            <th style="width: 95px; text-align: right;">Potongan (Rp)</th>
                            <th style="width: 85px; text-align: center;">PPN</th>
                            <th style="width: 125px; text-align: right;">Total Harga</th>
                            <th style="width: 40px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        {{-- Baris produk ditambahkan secara dinamis via JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Tombol Tambah Baris Lebar & Kosongkan Seluruh Baris --}}
            <div style="padding: 0.55rem 0.85rem; background: #ffffff; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap; border-top: 1px dashed #cbd5e1;">
                <button type="button" class="btn-add-row-wide" id="btnAddRow" style="max-width: 320px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>+ Tambah Baris Baru</span>
                </button>

                <button type="button" class="btn btn-sm" onclick="clearAllRows()" id="btnClearAllRows"
                    style="background: #fff1f2; border: 1.5px solid #fecdd3; color: #e11d48; font-weight: 700; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; border-radius: 6px; cursor: pointer; transition: all 0.15s ease;"
                    title="Kosongkan seluruh baris item pesanan">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Semua Baris</span>
                </button>
            </div>
        </div>

        {{-- 4. BOTTOM SECTION: CATATAN (KIRI) & FINANCIAL STACK (KANAN) --}}
        <div class="po-bottom-grid">
            {{-- Sisi Kiri: Catatan Pesanan & Panduan Alur FEFO --}}
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                <div class="po-notes-card">
                    <label for="catatan_txt" class="po-field-label" style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Catatan Pesanan &amp; Instruksi Khusus (NOTE)</span>
                        <small style="font-weight: normal; color: #64748b;">Tampil di Faktur &amp; Surat Jalan</small>
                    </label>
                    <textarea id="catatan_txt" name="catatan_txt" rows="3" class="form-control"
                        placeholder="Instruksi pengiriman, toleransi mutu, catatan tempo pembayaran, dll..."
                        style="border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.8125rem; padding: 0.5rem; width: 100%;">{{ old('catatan_txt') }}</textarea>
                </div>

                {{-- Panduan Ringkas Alur FEFO Gudang FG --}}
                <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.65rem 0.85rem; font-size: 0.725rem; color: #166534; line-height: 1.45; display: flex; align-items: flex-start; gap: 0.5rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <strong style="color: #14532d;">Sistem Terpadu Penjualan Pabrik:</strong> Pesanan Penjualan (SO) dapat dicatat bebas sesuai order mitra meskipun stok di gudang masih antre produksi. Alokasi pengeluaran fisik barang jadi akan diproses gudang secara otomatis berbasis FEFO saat pengiriman dijadwalkan.
                    </div>
                </div>
            </div>

            {{-- Sisi Kanan: Financial Stack Ringkasan Finansial Dokumen & Tombol Aksi --}}
            <div class="po-financial-card">
                <div style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.65rem;">
                    Ringkasan Finansial Dokumen
                </div>

                {{-- 1. Total Kuantitas --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Kuantitas Pesanan:</span>
                    <strong style="color: #0284c7; font-family: monospace; font-size: 0.95rem;">
                        <span id="sideTotalQty">0</span> Karton
                    </strong>
                </div>

                {{-- 2. Subtotal Bruto --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Subtotal Nilai Bruto:</span>
                    <span id="lblSubtotalBruto" style="font-weight: 700; font-family: monospace; font-size: 0.95rem; color: #1e293b;">Rp 0</span>
                </div>

                {{-- 3. Akumulasi Diskon (-) --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Akumulasi Diskon (-):</span>
                    <span id="lblTotalDiskon" style="font-weight: 700; font-family: monospace; color: #d97706;">- Rp 0</span>
                </div>

                {{-- 4. Potongan Faktur Langsung (-) --}}
                <div class="po-financial-row" style="align-items: center;">
                    <span style="color: #64748b; font-weight: 600;">Potongan Faktur (-):</span>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span style="font-size: 0.775rem; color: #64748b; font-weight: 700;">Rp</span>
                        <input type="number" step="any" min="0" name="potongan_nominal" id="headerPotonganInput" value="{{ old('potongan_nominal', 0) }}"
                            class="form-control" style="width: 110px; height: 28px; padding: 2px 8px; font-size: 0.8rem; text-align: right; font-weight: 700; border: 1.5px solid #cbd5e1; border-radius: 5px;">
                    </div>
                </div>

                {{-- 5. DPP (Netto) --}}
                <div class="po-financial-row" style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.45rem; margin-top: 0.2rem;">
                    <span style="color: #475569; font-weight: 700;">Dasar Pengenaan Pajak (DPP):</span>
                    <span id="lblDppNominal" style="font-weight: 700; font-family: monospace; color: #1e293b; font-size: 0.95rem;">Rp 0</span>
                </div>

                {{-- 6. PPN --}}
                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Pajak (PPN 11%):</span>
                    <span id="lblTotalPpn" style="font-weight: 700; font-family: monospace; color: #0284c7;">+ Rp 0</span>
                </div>

                {{-- 7. Grand Total Emphasized Box --}}
                <div class="po-grand-total-box">
                    <div>
                        <span style="font-size: 0.725rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #065f46; display: block;">
                            TOTAL TAGIHAN AKHIR:
                        </span>
                        <span style="font-size: 0.7rem; color: #059669;">Termasuk Pajak &amp; Potongan</span>
                    </div>
                    <div class="po-grand-total-val" id="lblTotalTagihan">
                        Rp 0
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="po-action-bar">
                    <a href="{{ route('penjualan.so.index') }}" class="btn btn-secondary"
                        style="padding: 0.5rem 1rem; font-size: 0.8125rem; font-weight: 700; border-radius: 5px; background: #f8fafc; border: 1.5px solid #cbd5e1; color: #475569; text-decoration: none;">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitPo" class="btn btn-primary"
                        style="padding: 0.55rem 1.35rem; font-size: 0.875rem; font-weight: 800; background: #059669; border: none; border-radius: 5px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.3); display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; color: #ffffff;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        <span>Simpan Dokumen PO Penjualan</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.soConfig = {
            barangs: @json($barangs),
            stockMap: @json($stockMap ?? []),
            existingItems: @json(old('items', [])),
            generateFakturUrl: '{{ route("penjualan.so.ajax.generate-faktur-no") }}',
            generateSoCodeUrl: '{{ route("ajax.generate_code") }}',
            topCheckUrl: '{{ route("penjualan.so.ajax.customer-top-check") }}'
        };
    </script>
    <script src="{{ asset('js/penjualan/so/so-form.js') }}?v={{ filemtime(public_path('js/penjualan/so/so-form.js')) }}"></script>
    <script>
        document.getElementById('so_tgl')?.addEventListener('change', function() {
            const dateVal = this.value;
            if (!dateVal) return;
            fetch(`{{ route('ajax.generate_code') }}?type=so&date=${dateVal}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.code) {
                        document.getElementById('so_no').value = data.code;
                    }
                })
                .catch(err => console.error('Error generating SO code:', err));
        });
    </script>
@endpush