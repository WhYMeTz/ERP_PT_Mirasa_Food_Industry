@extends('layouts.app')

@section('title', 'Input Kas Keluar - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_keluar/kas-keluar-form.css') }}?v={{ filemtime(public_path('css/keuangan/kas_keluar/kas-keluar-form.css')) }}">
@endpush

@section('content')
@php
    $selectedKat = null;
    if (old('kategori_id')) {
        $selectedKat = $kategoriMasterList->firstWhere('kategori_id', old('kategori_id'));
    } elseif (old('kategori_pengeluaran')) {
        $selectedKat = $kategoriMasterList->firstWhere('kode_kategori', old('kategori_pengeluaran'));
    } else {
        $selectedKat = $kategoriMasterList->firstWhere('kode_kategori', $defaultKategori) ?? $kategoriMasterList->first();
    }
    $initialKatId = $selectedKat?->kategori_id ?? '';
    $initialKatKode = $selectedKat?->kode_kategori ?? $defaultKategori;
    $initialKatNama = $selectedKat?->nama_kategori ?? '';
    $initialCoaText = $selectedKat?->coaDefault 
        ? ($selectedKat->coaDefault->kode_akun . ' - ' . $selectedKat->coaDefault->nama_akun) 
        : 'Auto COA Akuntansi';
@endphp

<div class="bkk-full-container">
    {{-- BREADCRUMB --}}
    <nav class="bkk-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('keuangan.kas_keluar.index') }}">Keuangan</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('keuangan.kas_keluar.index') }}">Kas Keluar</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Baru</span>
    </nav>

    {{-- HEADER BAR --}}
    <div class="bkk-header-bar">
        <div class="bkk-header-info">
            <div class="bkk-header-title">
                <a href="{{ route('keuangan.kas_keluar.index') }}"
                   class="bkk-back-link"
                   aria-label="Kembali ke daftar kas keluar">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1>Input Kas Keluar</h1>
                <span class="bkk-pill-badge">BKK Operasional</span>
                <span class="bkk-pill-badge bkk-pill-code" title="Nomor BKK Tersedia">{{ $suggestedBkkNo }}</span>
            </div>
        </div>

        <div class="bkk-header-actions">
            <a href="{{ route('keuangan.kas_keluar.index') }}" class="btn-header-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Daftar Kas Keluar</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div role="alert" class="bkk-alert-banner">
            <div class="bkk-alert-heading">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Mohon periksa kembali formulir berikut:</span>
            </div>
            <ul class="bkk-alert-list">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('keuangan.kas_keluar.store') }}" id="formKasKeluar" autocomplete="off">
        @csrf

        {{-- CARD 1: PARAMETER PENGELUARAN --}}
        <div class="bkk-card">
            <div class="bkk-card-header">
                <h2 class="bkk-card-title">Parameter Pengeluaran</h2>
            </div>

            <div class="bkk-card-body">
                <div class="parameter-grid">
                    {{-- 1. SUMBER KAS / BANK --}}
                    <div class="form-group">
                        <label for="kas_bank_id" class="form-label">
                            <span>Sumber Kas / Bank <span class="req-star">*</span></span>
                        </label>
                        <select name="kas_bank_id" id="kas_bank_id" class="form-control" required>
                            <option value="">-- Pilih Akun Kas / Bank --</option>
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

                    {{-- 2. KATEGORI PENGELUARAN (SMART SEARCHABLE AUTOCOMPLETE MIRIP KARYAWAN) --}}
                    <div class="form-group">
                        <label for="kategori_input" class="form-label">
                            <span>Kategori Pengeluaran <span class="req-star">*</span></span>
                            <span id="kategoriSelectedBadge" class="recipient-pill-badge badge-neutral">
                                {{ $selectedKat ? 'Kategori Terpilih' : 'Pilih Kategori' }}
                            </span>
                        </label>
                        
                        <div class="smart-autocomplete-wrapper" id="smartKategoriContainer">
                            <div class="input-with-icon">
                                <span class="input-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </span>
                                <input type="text"
                                       name="kategori_nama_display"
                                       id="kategori_input"
                                       class="form-control with-icon-input"
                                       value="{{ $initialKatNama }}"
                                       placeholder="Ketik untuk mencari kategori pengeluaran..."
                                       required
                                       autocomplete="off">
                                <button type="button" id="btnClearKategori" class="btn-clear-input" title="Reset kategori" style="{{ $initialKatNama ? 'display: inline-flex;' : 'display: none;' }}">
                                    &times;
                                </button>
                            </div>

                            <input type="hidden" name="kategori_id" id="kategori_id" value="{{ $initialKatId }}">
                            <input type="hidden" name="kategori_pengeluaran" id="kategori_pengeluaran" value="{{ $initialKatKode }}">

                            <div class="autocomplete-dropdown" id="kategoriDropdown" style="display: none;">
                                <div class="autocomplete-section-title">Kategori Pengeluaran Operasional:</div>
                                <div class="autocomplete-list" id="kategoriSuggestionsList">
                                    {{-- Diisi secara dinamis oleh JavaScript --}}
                                </div>
                            </div>
                        </div>

                        <div class="live-kategori-badge">
                            <span class="live-kategori-caption">Default Beban:</span>
                            <span id="liveKategoriCoa" class="live-kategori-value">{{ $initialCoaText }}</span>
                        </div>
                    </div>

                    {{-- 3. DIBAYARKAN / DISERAHKAN KEPADA --}}
                    <div class="form-group">
                        <label for="penerima_nama" class="form-label">
                            <span>Dibayarkan / Diserahkan <span class="req-star">*</span></span>
                            <span id="recipientTypeBadge" class="recipient-pill-badge badge-neutral">Karyawan / Vendor</span>
                        </label>
                        
                        <div class="smart-autocomplete-wrapper" id="smartRecipientContainer">
                            <div class="input-with-icon">
                                <span class="input-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <input type="text"
                                       name="penerima_nama"
                                       id="penerima_nama"
                                       class="form-control with-icon-input"
                                       value="{{ old('penerima_nama') }}"
                                       placeholder="Ketik nama karyawan atau vendor..."
                                       required
                                       autocomplete="off">
                                <button type="button" id="btnClearRecipient" class="btn-clear-input" title="Reset penerima" style="display: none;">
                                    &times;
                                </button>
                            </div>

                            <input type="hidden" name="pemohon_karyawan_id" id="pemohon_karyawan_id" value="{{ old('pemohon_karyawan_id') }}">
                            <input type="hidden" name="departemen_pemohon" id="departemen_pemohon" value="{{ old('departemen_pemohon') }}">
                            <input type="hidden" name="tipe_penerima" id="tipe_penerima" value="{{ old('tipe_penerima', 'PIHAK_KETIGA') }}">

                            <div class="autocomplete-dropdown" id="recipientDropdown" style="display: none;">
                                <div class="autocomplete-section-title">Karyawan Mirasa atau Vendor:</div>
                                <div class="autocomplete-list" id="recipientSuggestionsList">
                                    {{-- Diisi secara dinamis oleh JavaScript --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. TANGGAL, METODE & NO. REFERENSI --}}
                    <div class="form-group">
                        <div class="dual-input-row">
                            <div>
                                <label for="tgl_keluar" class="form-label">
                                    <span>Tgl Keluar <span class="req-star">*</span></span>
                                </label>
                                <input type="date"
                                       name="tgl_keluar"
                                       id="tgl_keluar"
                                       class="form-control"
                                       value="{{ old('tgl_keluar', date('Y-m-d')) }}"
                                       required>
                            </div>
                            <div>
                                <label for="metode_bayar" class="form-label">
                                    <span>Metode</span>
                                </label>
                                <select name="metode_bayar" id="metode_bayar" class="form-control">
                                    <option value="CASH" {{ old('metode_bayar') === 'CASH' ? 'selected' : '' }}>TUNAI</option>
                                    <option value="TRANSFER" {{ old('metode_bayar') === 'TRANSFER' ? 'selected' : '' }}>TRANSFER</option>
                                    <option value="GIRO" {{ old('metode_bayar') === 'GIRO' ? 'selected' : '' }}>GIRO</option>
                                </select>
                            </div>
                        </div>
                        <div class="ref-input-subrow">
                            <input type="text"
                                   name="no_referensi_bank"
                                   id="no_referensi_bank"
                                   class="form-control form-control-sm"
                                   value="{{ old('no_referensi_bank') }}"
                                   placeholder="No. Nota / Struk / Ref (Opsional)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: RINCIAN PENGELUARAN (DUAL-COLUMN CLEAN FOCUS) --}}
        <div class="bkk-card card-table-workstation">
            <div class="bkk-card-header">
                <div class="bkk-card-title-group">
                    <h2 class="bkk-card-title">Rincian Pengeluaran</h2>
                    <span class="bkk-card-subtitle">Format ringkas & lega. Cukup isi uraian dan nominal, atau buka rincian Qty bila diperlukan.</span>
                </div>

                <div class="card-header-actions">
                    <button type="button" class="btn-add-row" id="btnAddRow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Baris</span>
                    </button>
                </div>
            </div>

            <div id="bkkRowNotice" role="status" aria-live="polite" class="bkk-row-notice" style="display: none;"></div>

            {{-- HEADER KOLOM WORKSTATION (DESKTOP) --}}
            <div class="bkk-items-header-bar">
                <div class="bkk-col-num-hdr">No</div>
                <div class="bkk-col-uraian-hdr">Uraian / Keperluan Pengeluaran <span class="req-star">*</span></div>
                <div class="bkk-col-nominal-hdr">Nominal Keluar (Rp) <span class="req-star">*</span></div>
                <div class="bkk-col-action-hdr">Aksi</div>
            </div>

            {{-- DAFTAR BARIS RINCIAN (DUAL-COLUMN CLEAN FOCUS) --}}
            @php
                $oldItems = old('items', [
                    ['uraian' => '', 'qty' => 1, 'satuan' => 'LS', 'harga_satuan' => '', 'nominal' => '']
                ]);
            @endphp

            <div class="bkk-items-list" id="bkkItemsList">
                @foreach ($oldItems as $idx => $item)
                    @php
                        $hasDetails = !empty($item['harga_satuan']) || (!empty($item['qty']) && (float)$item['qty'] != 1) || (!empty($item['satuan']) && $item['satuan'] !== 'LS');
                    @endphp
                    <div class="bkk-item-card" data-row-index="{{ $idx }}">
                        <div class="bkk-item-main-row">
                            <div class="bkk-item-num" title="Baris {{ $loop->iteration }}">
                                <span class="row-num-cell">{{ $loop->iteration }}</span>
                            </div>

                            <div class="bkk-item-col-uraian">
                                <label class="bkk-mobile-label" for="item_uraian_{{ $idx }}">
                                    Uraian / Keperluan Pengeluaran <span class="req-star">*</span>
                                </label>
                                <input type="text"
                                       name="items[{{ $idx }}][uraian]"
                                       id="item_uraian_{{ $idx }}"
                                       class="form-control-item item-uraian"
                                       value="{{ $item['uraian'] ?? '' }}"
                                       placeholder="Ketik keperluan pengeluaran (contoh: Biaya operasional servis, bensin, konsumsi)..."
                                       required
                                       autocomplete="off">

                                <div class="bkk-item-sub-actions">
                                    <button type="button" class="btn-toggle-qty" aria-expanded="{{ $hasDetails ? 'true' : 'false' }}">
                                        <svg class="toggle-icon {{ $hasDetails ? 'is-rotated' : '' }}" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                        <span class="toggle-text">{{ $hasDetails ? 'Sembunyikan Rincian Qty' : '+ Rincian Qty & Satuan (Opsional)' }}</span>
                                    </button>
                                </div>

                                <div class="bkk-item-expandable-panel" style="{{ $hasDetails ? 'display: block;' : 'display: none;' }}">
                                    <div class="bkk-details-grid">
                                        <div class="detail-field">
                                            <label class="detail-label" for="item_qty_{{ $idx }}">Qty / Jumlah</label>
                                            <input type="number"
                                                   step="any"
                                                   name="items[{{ $idx }}][qty]"
                                                   id="item_qty_{{ $idx }}"
                                                   class="form-control-detail item-qty"
                                                   value="{{ $item['qty'] ?? 1 }}"
                                                   min="0.01"
                                                   placeholder="1">
                                        </div>
                                        <div class="detail-field">
                                            <label class="detail-label" for="item_satuan_{{ $idx }}">Satuan</label>
                                            <input type="text"
                                                   name="items[{{ $idx }}][satuan]"
                                                   id="item_satuan_{{ $idx }}"
                                                   class="form-control-detail item-satuan"
                                                   value="{{ $item['satuan'] ?? 'LS' }}"
                                                   placeholder="LS / Pcs / Liter">
                                        </div>
                                        <div class="detail-field">
                                            <label class="detail-label" for="item_harga_{{ $idx }}">Harga Satuan (Rp)</label>
                                            <div class="detail-input-prefix">
                                                <span class="prefix-text">Rp</span>
                                                <input type="number"
                                                       step="any"
                                                       name="items[{{ $idx }}][harga_satuan]"
                                                       id="item_harga_{{ $idx }}"
                                                       class="form-control-detail item-harga with-prefix"
                                                       value="{{ $item['harga_satuan'] ?? '' }}"
                                                       placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bkk-item-col-nominal">
                                <label class="bkk-mobile-label" for="item_nominal_{{ $idx }}">
                                    Nominal Keluar (Rp) <span class="req-star">*</span>
                                </label>
                                <div class="bkk-nominal-input-wrap">
                                    <span class="nominal-prefix">Rp</span>
                                    <input type="number"
                                           step="any"
                                           name="items[{{ $idx }}][nominal]"
                                           id="item_nominal_{{ $idx }}"
                                           class="form-control-nominal item-nominal"
                                           value="{{ $item['nominal'] ?? '' }}"
                                           placeholder="0"
                                           required
                                           autocomplete="off">
                                </div>
                            </div>

                            <div class="bkk-item-col-action">
                                <button type="button" class="btn-delete-row" title="Hapus baris pengeluaran" aria-label="Hapus baris {{ $loop->iteration }}">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- TOMBOL TAMBAH BARIS BESAR DI BAWAH DAFTAR --}}
            <div class="bkk-add-row-bar">
                <button type="button" class="btn-add-item-large" id="btnAddRowLarge">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Tambah Baris Pengeluaran</span>
                </button>
            </div>

            {{-- CATATAN TAMBAHAN --}}
            <div class="bkk-notes-row">
                <div class="notes-icon">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="notes-input-wrapper">
                    <input type="text"
                           name="catatan"
                           id="catatan"
                           class="form-control notes-input"
                           value="{{ old('catatan') }}"
                           placeholder="Catatan tambahan untuk bagian Akuntansi / Finance (opsional)...">
                </div>
            </div>

            {{-- DOCK FOOTER BERKELAS --}}
            <div class="bkk-dock-footer">
                <div class="dock-section dock-saldo">
                    <div class="dock-label">
                        <span class="dock-indicator-dot"></span>
                        <span>Monitoring Saldo</span>
                    </div>
                    <div class="dock-saldo-content" id="sisaSaldoDisplay">
                        Pilih akun Kas/Bank untuk estimasi sisa saldo
                    </div>
                </div>

                <div class="dock-section dock-total">
                    <div class="dock-label">Total Pengeluaran Kas (BKK)</div>
                    <div class="dock-total-wrapper">
                        <div class="dock-total-amount" id="bkkTotalDisplay">Rp 0</div>
                        <div class="dock-terbilang" id="bkkTerbilangDisplay"># Nol Rupiah #</div>
                    </div>
                </div>

                <div class="dock-section dock-actions">
                    <a href="{{ route('keuangan.kas_keluar.index') }}" class="btn-dock-cancel">
                        Batal
                    </a>
                    <button type="submit" class="btn-dock-submit" id="btnSubmitBkk">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Kas Keluar</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- MODAL PERINGATAN SALDO FISIK KURANG (AGENTS.MD COMPLIANT) --}}
    <div id="modalSaldoMinus" class="bkk-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalSaldoMinusTitle" style="display: none;">
        <div class="bkk-modal-card">
            <div class="bkk-modal-header">
                <div class="bkk-modal-title" id="modalSaldoMinusTitle">
                    <svg width="18" height="18" fill="none" stroke="#dc2626" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Konfirmasi Saldo Fisik Kas</span>
                </div>
                <button type="button" class="btn-modal-close" id="btnCloseSaldoMinus" aria-label="Tutup modal">&times;</button>
            </div>
            <div class="bkk-modal-body">
                <p id="modalSaldoMinusMsg" class="modal-body-text">
                    Saldo kas fisik saat ini lebih kecil dari total pengeluaran.
                </p>
                <div class="modal-notice-box">
                    Transaksi ini tetap dapat disimpan bila kasir telah memperoleh persetujuan pengeluaran talangan.
                </div>
            </div>
            <div class="bkk-modal-footer">
                <button type="button" class="btn-modal-cancel" id="btnBatalSaldoMinus">Periksa Kembali</button>
                <button type="button" class="btn-modal-confirm" id="btnLanjutSaldoMinus">Tetap Simpan Transaksi</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            suggestedBkkNo: @json($suggestedBkkNo),
            karyawanList: @json($karyawanList),
            kategoriMasterList: @json($kategoriMasterList)
        };
    </script>
    <script src="{{ asset('js/keuangan/kas_keluar/kas-keluar-create.js') }}?v={{ filemtime(public_path('js/keuangan/kas_keluar/kas-keluar-create.js')) }}"></script>
@endpush