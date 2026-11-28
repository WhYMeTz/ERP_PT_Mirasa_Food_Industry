@extends('layouts.app')

@section('title', 'Input Bukti Kas Masuk (BKM) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_masuk/kas-masuk-form.css') }}?v={{ file_exists(public_path('css/keuangan/kas_masuk/kas-masuk-form.css')) ? filemtime(public_path('css/keuangan/kas_masuk/kas-masuk-form.css')) : time() }}">
@endpush

@section('content')
<div class="bkm-full-container">

    {{-- BREADCRUMB (PO & KAS KELUAR STANDARD) --}}
    <nav class="bkm-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('keuangan.kas_masuk.index') }}">Keuangan</a>
        <span class="breadcrumb-separator" aria-hidden="true">/</span>
        <a href="{{ route('keuangan.kas_masuk.index') }}">Kas Masuk</a>
        <span class="breadcrumb-separator" aria-hidden="true">/</span>
        <span class="breadcrumb-current">Baru</span>
    </nav>

    {{-- HEADER BAR --}}
    <div class="bkm-header-bar">
        <div class="bkm-header-info">
            <div class="bkm-header-title">
                <a href="{{ route('keuangan.kas_masuk.index') }}"
                   class="bkm-back-link"
                   aria-label="Kembali ke daftar kas masuk">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1>Input Bukti Kas Masuk</h1>
                <span class="bkm-pill-badge">BKM Kasir</span>
                <span class="bkm-pill-badge bkm-pill-code" title="Nomor BKM Baru">{{ $suggestedBkmNo }}</span>
            </div>
        </div>

        <div class="bkm-header-actions">
            <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn-header-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Daftar Kas Masuk</span>
            </a>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if (isset($errors) && $errors->any())
        <div role="alert" class="bkm-alert-banner">
            <div class="bkm-alert-heading">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Perhatian: Formulir belum lengkap</span>
            </div>
            <ul class="bkm-alert-list">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- NOTICE KAS MASUK PIUTANG (AR) --}}
    <div class="bkm-ar-notice">
        <div class="bkm-ar-notice-left">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Untuk pelunasan tagihan faktur pelanggan / piutang produk jadi, gunakan formulir <strong>Kas Masuk Piutang (AR)</strong>.</span>
        </div>
        <a href="{{ route('keuangan.bayar_piutang.create') }}" class="bkm-ar-notice-link">
            <span>Buka Kas Masuk Piutang &rarr;</span>
        </a>
    </div>

    <form method="POST" action="{{ route('keuangan.kas_masuk.store') }}" id="formKasMasuk" autocomplete="off">
        @csrf
        <input type="hidden" name="submit_action" id="submitActionInput" value="save_and_print">

        {{-- CARD 1: SUMBER & REKENING TUJUAN --}}
        <div class="bkm-card">
            <div class="bkm-card-header">
                <h2 class="bkm-card-title">1. Sumber &amp; Rekening Tujuan</h2>
            </div>

            <div class="bkm-card-body">
                {{-- 4 KARTU KATEGORI (ERGONOMIS ORANG TUA - NOL EMOJI - MONOKROMATIK SLATE) --}}
                @php 
                    $kategoriVal = old('kategori_penerimaan', $defaultKategori);
                    $isSingleMode = in_array($kategoriVal, ['SETORAN_MODAL', 'PENDAPATAN_LAIN']);
                @endphp
                <div class="category-selection-grid">
                    {{-- 1. Penjualan Limbah Pabrik --}}
                    <label class="category-card-option {{ $kategoriVal === 'PENJUALAN_LIMBAH' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="PENJUALAN_LIMBAH" {{ $kategoriVal === 'PENJUALAN_LIMBAH' ? 'checked' : '' }}>
                        <div class="category-card-icon-box" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="category-card-content">
                            <div class="category-card-title">
                                <span>Penjualan Limbah Pabrik</span>
                                <div class="category-check-indicator" aria-hidden="true">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="category-card-desc">
                                Minyak jelantah, kulit singkong &amp; by-product produksi.
                            </div>
                        </div>
                    </label>

                    {{-- 2. Penjualan Tunai Pabrik --}}
                    <label class="category-card-option {{ $kategoriVal === 'PENJUALAN_TUNAI' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="PENJUALAN_TUNAI" {{ $kategoriVal === 'PENJUALAN_TUNAI' ? 'checked' : '' }}>
                        <div class="category-card-icon-box" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="category-card-content">
                            <div class="category-card-title">
                                <span>Penjualan Tunai Pabrik</span>
                                <div class="category-check-indicator" aria-hidden="true">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="category-card-desc">
                                Keripik eceran, penjualan afkir &amp; remah keripik di pabrik.
                            </div>
                        </div>
                    </label>

                    {{-- 3. Setoran Modal Owner --}}
                    <label class="category-card-option {{ $kategoriVal === 'SETORAN_MODAL' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="SETORAN_MODAL" {{ $kategoriVal === 'SETORAN_MODAL' ? 'checked' : '' }}>
                        <div class="category-card-icon-box" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div class="category-card-content">
                            <div class="category-card-title">
                                <span>Setoran Modal Owner</span>
                                <div class="category-check-indicator" aria-hidden="true">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="category-card-desc">
                                Injeksi tambahan modal kerja operasional dari pemilik pabrik.
                            </div>
                        </div>
                    </label>

                    {{-- 4. Penerimaan Lain-lain --}}
                    <label class="category-card-option {{ $kategoriVal === 'PENDAPATAN_LAIN' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="PENDAPATAN_LAIN" {{ $kategoriVal === 'PENDAPATAN_LAIN' ? 'checked' : '' }}>
                        <div class="category-card-icon-box" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="category-card-content">
                            <div class="category-card-title">
                                <span>Penerimaan Lain-lain</span>
                                <div class="category-check-indicator" aria-hidden="true">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="category-card-desc">
                                Bunga simpanan bank, pengembalian biaya operasional.
                            </div>
                        </div>
                    </label>
                </div>

                {{-- PARAMETER GRID 4 KOLOM SIMETRIS --}}
                <div class="parameter-grid">
                    {{-- 1. Rekening Kas / Bank Tujuan --}}
                    <div class="form-group">
                        <label for="kas_bank_id" class="form-label">
                            <span>Rekening Kas / Bank Tujuan <span class="req-star">*</span></span>
                        </label>
                        <select name="kas_bank_id" id="kas_bank_id" class="form-control" required>
                            <option value="">-- Pilih Rekening Kas / Bank --</option>
                            @foreach ($kasBanks as $kb)
                                <option value="{{ $kb->kas_bank_id }}"
                                        data-saldo="{{ $kb->saldo_berjalan }}"
                                        data-tipe="{{ $kb->tipe_akun }}"
                                        {{ old('kas_bank_id') == $kb->kas_bank_id ? 'selected' : '' }}>
                                    {{ $kb->nama_akun }} ({{ $kb->tipe_akun }})
                                </option>
                            @endforeach
                        </select>
                        <div class="live-saldo-badge">
                            <span class="live-saldo-caption">Saldo Aktif:</span>
                            <span id="liveSaldoDisplay" class="live-saldo-value">Rp 0</span>
                        </div>
                    </div>

                    {{-- 2. Diterima Dari (Penyetor / Sumber) --}}
                    <div class="form-group">
                        <label for="penyetor_nama" class="form-label">
                            <span>Diterima Dari (Penyetor) <span class="req-star">*</span></span>
                        </label>
                        <input type="text"
                               name="penyetor_nama"
                               id="penyetor_nama"
                               class="form-control"
                               value="{{ old('penyetor_nama') }}"
                               placeholder="Nama pembeli, pengepul, atau owner..."
                               required>
                        <span class="form-hint">Pihak luar / sumber yang menyerahkan dana</span>
                    </div>

                    {{-- 3. Tanggal & Metode (Dual Inline) --}}
                    <div class="form-group">
                        <label for="tgl_terima" class="form-label">
                            <span>Tanggal &amp; Metode <span class="req-star">*</span></span>
                        </label>
                        <div class="dual-input-row">
                            <input type="date"
                                   name="tgl_terima"
                                   id="tgl_terima"
                                   class="form-control"
                                   value="{{ old('tgl_terima', now()->toDateString()) }}"
                                   required>
                            <select name="metode_terima" id="metode_terima" class="form-control" aria-label="Metode terima">
                                <option value="TRANSFER" {{ old('metode_terima', 'TRANSFER') === 'TRANSFER' ? 'selected' : '' }}>TRANSFER</option>
                                <option value="CASH" {{ old('metode_terima') === 'CASH' ? 'selected' : '' }}>TUNAI</option>
                                <option value="GIRO_CEK" {{ old('metode_terima') === 'GIRO_CEK' ? 'selected' : '' }}>GIRO / CEK</option>
                                <option value="EDC_QRIS" {{ old('metode_terima') === 'EDC_QRIS' ? 'selected' : '' }}>QRIS / EDC</option>
                            </select>
                        </div>
                        <span class="form-hint">Waktu transaksi fisik dan instrumen bayar</span>
                    </div>

                    {{-- 4. No Referensi Bank / Bukti Transfer --}}
                    <div class="form-group">
                        <label for="no_referensi_bank" class="form-label">
                            <span>No. Ref / Bukti Transfer (Opsional)</span>
                        </label>
                        <input type="text"
                               name="no_referensi_bank"
                               id="no_referensi_bank"
                               class="form-control"
                               value="{{ old('no_referensi_bank') }}"
                               placeholder="TRF-BCA-12345 / No Slip">
                        <span class="form-hint">Nomor mutasi bank atau nomor struk setor</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: AREA RINCIAN PENERIMAAN KAS (SMART DYNAMIC WORKSTATION) --}}
        <div class="bkm-card card-table-workstation">

            {{-- ========================================================
                 WADAH 1: SINGLE DIRECT ENTRY (MODAL OWNER / LAIN-LAIN)
                 ======================================================== --}}
            @php
                $defaultSingleDesc = ($kategoriVal === 'SETORAN_MODAL') 
                    ? 'Injeksi modal kerja operasional pemilik' 
                    : (($kategoriVal === 'PENDAPATAN_LAIN') ? 'Penerimaan bunga bank / pendapatan lain' : 'Penerimaan dana kas masuk');
                $singleDescVal = old('items.0.deskripsi', old('items.0.uraian', $defaultSingleDesc));
                $singleNominalVal = old('items.0.nominal', old('items.0.subtotal', ''));
            @endphp
            <div id="singleEntryWorkstation" class="bkm-workstation-panel" style="{{ $isSingleMode ? 'display: block;' : 'display: none;' }}">
                <div class="bkm-card-header">
                    <div>
                        <h2 class="bkm-card-title">2. Rincian Penerimaan Kas Langsung</h2>
                        <span class="bkm-card-subtitle">Penerimaan dana segar non-komoditas langsung tercatat ke rekening kas/bank</span>
                    </div>
                    <div>
                        <span class="bkm-mode-badge">Mode Penerimaan Langsung</span>
                    </div>
                </div>

                <div class="bkm-single-entry-body">
                    <div class="bkm-single-entry-grid">
                        {{-- Field 1: Nominal Dana Diterima (Besar, Monospace) --}}
                        <div class="bkm-single-field bkm-single-field-amount">
                            <label for="single_nominal" class="bkm-single-label">
                                <span>Nominal Dana Diterima (Rp) <span class="req-star">*</span></span>
                            </label>
                            <div class="bkm-single-amount-wrap">
                                <span class="bkm-single-prefix" aria-hidden="true">Rp</span>
                                <input type="text"
                                       id="single_nominal"
                                       class="bkm-single-amount-input"
                                       value="{{ $singleNominalVal ? number_format((float)str_replace('.', '', $singleNominalVal), 0, ',', '.') : '' }}"
                                       placeholder="0"
                                       inputmode="numeric"
                                       autocomplete="off">
                            </div>
                            <span class="form-hint">Ketik nominal transfer/setoran. Otomatis diformat pemisah ribuan.</span>
                        </div>

                        {{-- Field 2: Keterangan / Keperluan Penerimaan --}}
                        <div class="bkm-single-field">
                            <label for="single_deskripsi" class="bkm-single-label">
                                <span>Keterangan / Keperluan Penerimaan <span class="req-star">*</span></span>
                            </label>
                            <input type="text"
                                   id="single_deskripsi"
                                   class="form-control bkm-single-text-input"
                                   value="{{ $singleDescVal }}"
                                   placeholder="Contoh: Injeksi modal kerja operasional pemilik...">
                            <span class="form-hint">Penjelasan rincian peruntukan penerimaan kas masuk</span>
                        </div>
                    </div>

                    {{-- Hidden inputs Mode 1 yang dikirimkan ke Laravel Controller --}}
                    <fieldset id="mode1Fieldset" style="border: 0; padding: 0; margin: 0;" {{ $isSingleMode ? '' : 'disabled' }}>
                        <input type="hidden" name="items[0][deskripsi]" id="mode1_deskripsi_input" value="{{ $singleDescVal }}">
                        <input type="hidden" name="items[0][nominal]" id="mode1_nominal_input" value="{{ $singleNominalVal }}">
                        <input type="hidden" name="items[0][qty]" value="1">
                        <input type="hidden" name="items[0][satuan]" value="transaksi">
                    </fieldset>
                </div>
            </div>

            {{-- ========================================================
                 WADAH 2: MULTI-ITEM TABLE (LIMBAH PABRIK & PENJUALAN TUNAI)
                 ======================================================== --}}
            <div id="multiItemWorkstation" class="bkm-workstation-panel" style="{{ !$isSingleMode ? 'display: block;' : 'display: none;' }}">
                <fieldset id="mode2Fieldset" style="border: 0; padding: 0; margin: 0;" {{ !$isSingleMode ? '' : 'disabled' }}>
                    <div class="bkm-card-header">
                        <div class="bkm-workstation-header-left">
                            <h2 class="bkm-card-title">2. Rincian Barang / Komoditas Penerimaan</h2>
                            <span class="bkm-card-subtitle">Input satu atau lebih baris komoditas limbah / eceran pabrik</span>
                        </div>
                        <div class="bkm-quick-presets-bar">
                            <span class="quick-presets-label">Preset Cepat:</span>
                            <div class="quick-presets-pills">
                                <button type="button" class="btn-preset-pill" onclick="applyItemPreset('Penjualan Minyak Jelantah', 'kg')">
                                    + Minyak Jelantah
                                </button>
                                <button type="button" class="btn-preset-pill" onclick="applyItemPreset('Penjualan Kulit Singkong', 'kg')">
                                    + Kulit Singkong
                                </button>
                                <button type="button" class="btn-preset-pill" onclick="applyItemPreset('Penjualan Remah &amp; Afkir Keripik', 'kg')">
                                    + Remah Keripik
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bkm-items-header-bar">
                        <div class="bkm-col-num-hdr">No</div>
                        <div class="bkm-col-uraian-hdr">Uraian / Keterangan Penerimaan <span class="req-star">*</span></div>
                        <div class="bkm-col-nominal-hdr">Nominal (Rp) <span class="req-star">*</span></div>
                        <div class="bkm-col-action-hdr">Aksi</div>
                    </div>

                    <div class="bkm-items-list" id="focusItemsList">
                        @php
                            $oldItems = old('items', [
                                ['deskripsi' => 'Penjualan Minyak Jelantah', 'qty' => 1, 'satuan' => 'kg', 'harga_satuan' => '', 'nominal' => '']
                            ]);
                            // Jika Mode 1 sempat dipakai dan kembali ke mode 2, pastikan baris valid
                            if (empty($oldItems) || (count($oldItems) === 1 && empty($oldItems[0]['deskripsi']))) {
                                $oldItems = [
                                    ['deskripsi' => 'Penjualan Minyak Jelantah', 'qty' => 1, 'satuan' => 'kg', 'harga_satuan' => '', 'nominal' => '']
                                ];
                            }
                        @endphp

                        @foreach ($oldItems as $idx => $item)
                            @php
                                $uraianVal = $item['deskripsi'] ?? $item['uraian'] ?? '';
                                $qtyVal = (float) ($item['qty'] ?? 1);
                                $satuanVal = $item['satuan'] ?? 'kg';
                                $hargaVal = $item['harga_satuan'] ?? '';
                                $nominalVal = $item['nominal'] ?? $item['subtotal'] ?? '';
                                $hasQtyDetails = (!empty($hargaVal) || $qtyVal > 1);
                            @endphp
                            <div class="bkm-item-card" data-row-index="{{ $idx }}" id="itemCard_{{ $idx }}">
                                <div class="bkm-item-main-row">
                                    <div class="bkm-item-num">{{ $idx + 1 }}</div>

                                    {{-- Kolom Uraian --}}
                                    <div class="bkm-item-col-uraian">
                                        <label class="bkm-mobile-label" for="item_uraian_{{ $idx }}">Uraian <span class="req-star">*</span></label>
                                        <input type="text"
                                               id="item_uraian_{{ $idx }}"
                                               name="items[{{ $idx }}][deskripsi]"
                                               class="form-control-item item-uraian"
                                               value="{{ $uraianVal }}"
                                               placeholder="Ketik keterangan barang (misal: Minyak Jelantah / Remah Keripik)..."
                                               required>

                                        {{-- Progressive Disclosure: Rincian Timbangan Limbah / Afkir --}}
                                        <div class="bkm-item-sub-actions">
                                            <button type="button" class="btn-toggle-qty" onclick="toggleQtyPanel({{ $idx }})" aria-expanded="{{ $hasQtyDetails ? 'true' : 'false' }}">
                                                <svg class="toggle-icon {{ $hasQtyDetails ? 'is-rotated' : '' }}" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                                <span>Rincian Timbangan (Kg x Harga)</span>
                                            </button>
                                        </div>

                                        <div class="bkm-item-expandable-panel" id="qtyPanel_{{ $idx }}" style="{{ $hasQtyDetails ? '' : 'display: none;' }}">
                                            <div class="bkm-details-grid">
                                                <div class="detail-field">
                                                    <label class="detail-label" for="item_qty_{{ $idx }}">Kuantitas (Qty)</label>
                                                    <input type="number"
                                                           step="any"
                                                           min="0.01"
                                                           id="item_qty_{{ $idx }}"
                                                           name="items[{{ $idx }}][qty]"
                                                           class="form-control-detail item-qty"
                                                           value="{{ $qtyVal }}"
                                                           oninput="recalcRowTotal({{ $idx }})">
                                                </div>
                                                <div class="detail-field">
                                                    <label class="detail-label" for="item_satuan_{{ $idx }}">Satuan</label>
                                                    <input type="text"
                                                           id="item_satuan_{{ $idx }}"
                                                           name="items[{{ $idx }}][satuan]"
                                                           class="form-control-detail item-satuan"
                                                           value="{{ $satuanVal }}"
                                                           placeholder="kg/kaleng">
                                                </div>
                                                <div class="detail-field">
                                                    <label class="detail-label" for="item_harga_{{ $idx }}">Harga Satuan (Rp)</label>
                                                    <input type="number"
                                                           step="any"
                                                           min="0"
                                                           id="item_harga_{{ $idx }}"
                                                           name="items[{{ $idx }}][harga_satuan]"
                                                           class="form-control-detail item-harga"
                                                           value="{{ $hargaVal }}"
                                                           placeholder="0"
                                                           oninput="recalcRowTotal({{ $idx }})">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kolom Nominal (Auto Formatted, Monospace Slate Besar) --}}
                                    <div class="bkm-item-col-nominal">
                                        <label class="bkm-mobile-label" for="item_nominal_{{ $idx }}">Nominal (Rp) <span class="req-star">*</span></label>
                                        <div class="bkm-nominal-input-wrap">
                                            <span class="nominal-prefix" aria-hidden="true">Rp</span>
                                            <input type="text"
                                                   id="item_nominal_{{ $idx }}"
                                                   name="items[{{ $idx }}][subtotal]"
                                                   class="form-control-nominal item-nominal"
                                                   value="{{ $nominalVal ? number_format((float)str_replace('.', '', $nominalVal), 0, ',', '.') : '' }}"
                                                   placeholder="0"
                                                   required
                                                   oninput="formatNominalInput(this); updateGrandTotal();">
                                        </div>
                                    </div>

                                    {{-- Kolom Aksi Hapus --}}
                                    <div class="bkm-item-col-action">
                                        <button type="button"
                                                class="btn-delete-row"
                                                onclick="removeFocusRow({{ $idx }})"
                                                title="Hapus baris ini"
                                                aria-label="Hapus baris {{ $idx + 1 }}">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="bkm-add-row-bar">
                        <button type="button" class="btn-add-item-large" onclick="addItemRow()">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Tambah Baris Penerimaan</span>
                        </button>
                    </div>
                </fieldset>
            </div>

            {{-- CATATAN UMUM TRANSAKSI BKM --}}
            <div class="bkm-notes-row">
                <span class="notes-icon" aria-hidden="true">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </span>
                <div class="notes-input-wrapper">
                    <input type="text"
                           name="catatan"
                           id="catatan"
                           class="form-control notes-input"
                           value="{{ old('catatan') }}"
                           placeholder="Catatan / Memo transaksi kas masuk (opsional)...">
                </div>
            </div>
        </div>

        {{-- STICKY DOCK FOOTER (STANDAR PERSIS PO & KAS KELUAR - MONOKROMATIK SLATE) --}}
        <div class="bkm-dock-footer">
            {{-- Kiri: Monitoring Rekening & Status Kasir --}}
            <div class="dock-section">
                <div class="dock-info-box">
                    <div class="dock-label">
                        <span class="dock-indicator-dot" aria-hidden="true"></span>
                        <span>Rekening Tujuan:</span>
                    </div>
                    <div id="dockAccountDisplay" class="dock-info-content">
                        Belum memilih kas/bank
                    </div>
                    <div class="dock-cashier-status">
                        Kasir Operasional: Saldo fisik bertambah seketika
                    </div>
                </div>
            </div>

            {{-- Tengah: Grand Total Raksasa Monospace Slate & Terbilang Otomatis --}}
            <div class="dock-total-wrapper">
                <div class="dock-total-label">TOTAL DITERIMA:</div>
                <div id="dockTotalAmount" class="dock-total-amount">
                    Rp 0
                </div>
                <div id="dockTerbilang" class="dock-terbilang" title="Kalimat terbilang nilai uang">
                    # Nol Rupiah #
                </div>
            </div>

            {{-- Kanan: Aksi Batal, Simpan Draft & Simpan Cetak --}}
            <div class="dock-actions">
                <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn-dock-cancel">
                    Batal
                </a>
                <button type="submit" name="submit_action" value="save" class="btn-dock-secondary" id="btnSubmitDraft" title="Simpan sebagai draft BKM">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span>Simpan Draft BKM</span>
                </button>
                <div class="dock-btn-group">
                    <button type="submit" name="submit_action" value="save_and_print" class="btn-dock-submit" id="btnSubmitPrint" title="Simpan BKM dan cetak kuitansi">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Simpan &amp; Cetak Kuitansi</span>
                    </button>
                    <div class="dock-shortcut-hint">
                        Shortcut: <kbd>Ctrl</kbd> + <kbd>Enter</kbd>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_masuk/kas-masuk-create.js') }}?v={{ file_exists(public_path('js/keuangan/kas_masuk/kas-masuk-create.js')) ? filemtime(public_path('js/keuangan/kas_masuk/kas-masuk-create.js')) : time() }}"></script>
@endpush
