@extends('layouts.app')

@section('title', ($activeTab === 'bkm' ? 'Verifikasi Jurnal Kas Masuk' : 'Verifikasi Jurnal Kas Keluar') . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_jurnal/verifikasi-jurnal.css') }}?v={{ filemtime(public_path('css/keuangan/verifikasi_jurnal/verifikasi-jurnal.css')) }}">
@endpush

@section('content')
<div class="vj-page-wrapper">
    {{-- 1. HEADER HALAMAN & ACTIONS (WaveDash / Linear Standard) --}}
    <div class="vj-header-banner">
        <div class="vj-header-left">
            <span class="vj-kicker">AKUNTANSI &amp; PEMBUKUAN</span>
            <div class="vj-title-row">
                <h1 class="vj-title">
                    {{ $activeTab === 'bkm' ? 'Verifikasi Jurnal Kas Masuk' : 'Verifikasi Jurnal Kas Keluar' }}
                </h1>
                @if (($summaryMetrics['total_dokumen'] ?? 0) > 0)
                    <span class="vj-pending-pill">
                        <span class="vj-status-dot-pulse"></span>
                        {{ $summaryMetrics['total_dokumen'] }} Menunggu Tindakan
                    </span>
                @else
                    <span class="vj-badge-live">
                        Semua Transaksi Telah Dijurnal
                    </span>
                @endif
            </div>
        </div>

        <div class="vj-header-actions">
            <a href="{{ route('keuangan.verifikasi_jurnal.index', ['tab' => $activeTab]) }}" class="vj-btn vj-btn-secondary" title="Segarkan Antrean">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Segarkan</span>
            </a>

            @if (Auth::user()->canAccessJurnal())
                <a href="{{ route('keuangan.jurnal.index') }}" class="vj-btn vj-btn-secondary" title="Buka Jurnal Umum">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Jurnal Umum</span>
                </a>
            @endif

            @if (Auth::user()->canAccessJurnal() || Auth::user()->canAccessCoa() || Auth::user()->canAccessLaporanKeuangan())
                <a href="{{ route('keuangan.buku_besar.index') }}" class="vj-btn vj-btn-secondary" title="Buka Buku Besar">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Buku Besar</span>
                </a>
            @endif

            @if ($activeTab === 'bkm')
                @if (Auth::user()->canAccessKasMasuk())
                    <a href="{{ route('keuangan.kas_masuk.index') }}" class="vj-btn vj-btn-secondary" title="Buku Kas Masuk">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Buku Kas Masuk</span>
                    </a>
                @endif
            @else
                @if (Auth::user()->canAccessBayarHutang())
                    <a href="{{ route('keuangan.kas_keluar.index') }}" class="vj-btn vj-btn-secondary" title="Buku Kas Keluar">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Buku Kas Keluar</span>
                    </a>
                @endif
            @endif
        </div>
    </div>

    {{-- 2. SEGMENTED TAB SWITCHER (2 TAB SENTRAL: BKK & BKM) --}}
    <div class="vj-segmented-container">
        <div class="vj-segmented-tabs" role="tablist" aria-label="Pilih Jenis Transaksi Kas">
            <a href="{{ route('keuangan.verifikasi_jurnal.index', array_merge(request()->except(['page_bkk', 'page_bkm']), ['tab' => 'bkk'])) }}"
               class="vj-segment-btn {{ $activeTab === 'bkk' ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ $activeTab === 'bkk' ? 'true' : 'false' }}">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Kas Keluar (BKK)</span>
                @if ($summaryMetricsBkk['total_dokumen'] > 0)
                    <span class="vj-tab-badge vj-badge-bkk">{{ $summaryMetricsBkk['total_dokumen'] }}</span>
                @else
                    <span class="vj-tab-badge vj-badge-zero">0</span>
                @endif
            </a>

            <a href="{{ route('keuangan.verifikasi_jurnal.index', array_merge(request()->except(['page_bkk', 'page_bkm']), ['tab' => 'bkm'])) }}"
               class="vj-segment-btn {{ $activeTab === 'bkm' ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ $activeTab === 'bkm' ? 'true' : 'false' }}">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Kas Masuk (BKM)</span>
                @if ($summaryMetricsBkm['total_dokumen'] > 0)
                    <span class="vj-tab-badge vj-badge-bkm">{{ $summaryMetricsBkm['total_dokumen'] }}</span>
                @else
                    <span class="vj-tab-badge vj-badge-zero">0</span>
                @endif
            </a>
        </div>
    </div>

    {{-- 3. METRIK RINGKASAN BERSIH (3 KARTU TABULAR) --}}
    <section class="vj-metrics-grid" aria-label="Metrik Ringkasan Antrean">
        <div class="vj-metric-card">
            <div class="vj-metric-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div class="vj-metric-info">
                <span class="vj-metric-label">Antrean Menunggu Tindakan</span>
                <span class="vj-metric-val">
                    {{ number_format($summaryMetrics['total_dokumen'], 0, ',', '.') }} Dokumen
                </span>
                <span class="vj-metric-desc">
                    {{ $activeTab === 'bkm' ? 'BKM berstatus DRAFT belum dijurnal' : 'BKK berstatus DRAFT belum dijurnal' }}
                </span>
            </div>
        </div>

        <div class="vj-metric-card">
            <div class="vj-metric-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if ($activeTab === 'bkm')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    @endif
                </svg>
            </div>
            <div class="vj-metric-info">
                <span class="vj-metric-label">
                    {{ $activeTab === 'bkm' ? 'Total Nilai Penerimaan' : 'Total Nilai Pengeluaran' }}
                </span>
                <span class="vj-metric-val {{ $activeTab === 'bkm' ? 'vj-val-emerald' : 'vj-val-highlight' }}">
                    Rp {{ number_format($summaryMetrics['total_nominal'], 0, ',', '.') }}
                </span>
                <span class="vj-metric-desc">
                    {{ $activeTab === 'bkm' ? 'Total dana masuk ke kas/bank fisik' : 'Total dana keluar dari kasir operasional' }}
                </span>
            </div>
        </div>

        <div class="vj-metric-card">
            <div class="vj-metric-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
            </div>
            <div class="vj-metric-info">
                <span class="vj-metric-label">Rekening Kas Terlibat</span>
                <span class="vj-metric-val">
                    {{ $summaryMetrics['total_kas_bank'] }} Rekening
                </span>
                <span class="vj-metric-desc">
                    {{ $activeTab === 'bkm' ? 'Rekening penampung penerimaan kas' : 'Sumber pengeluaran kas operasional' }}
                </span>
            </div>
        </div>
    </section>

    {{-- 4. FILTER TOOLBAR --}}
    <div class="vj-filter-card">
        <form method="GET" action="{{ route('keuangan.verifikasi_jurnal.index') }}" id="vjFilterForm" class="vj-filter-form">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="vj-filter-grid">
                {{-- Pencarian --}}
                <div class="vj-filter-field vj-filter-search">
                    <label for="filterSearch" class="vj-filter-label">
                        {{ $activeTab === 'bkm' ? 'Cari No. BKM / Penyetor / Uraian' : 'Cari No. BKK / Penerima / Uraian' }}
                    </label>
                    <div class="vj-input-with-icon">
                        <svg class="vj-input-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text"
                               id="filterSearch"
                               name="search"
                               value="{{ $filters['search'] }}"
                               placeholder="{{ $activeTab === 'bkm' ? 'Ketik nomor BKM, penyetor, uraian...' : 'Ketik nomor BKK, penerima, uraian...' }}"
                               class="vj-filter-input">
                    </div>
                </div>

                {{-- Kas / Bank --}}
                <div class="vj-filter-field">
                    <label for="filterKasBank" class="vj-filter-label">
                        {{ $activeTab === 'bkm' ? 'Rekening Tujuan Kas / Bank' : 'Sumber Kas / Bank' }}
                    </label>
                    <select id="filterKasBank" name="kas_bank_id" class="vj-filter-select">
                        <option value="">Semua Kas &amp; Bank</option>
                        @foreach ($kasBanks as $kb)
                            <option value="{{ $kb->kas_bank_id }}" {{ (string) $filters['kas_bank_id'] === (string) $kb->kas_bank_id ? 'selected' : '' }}>
                                {{ $kb->nama_akun }} ({{ $kb->nomor_rekening ?: 'Kas Fisik' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori (Hanya relevan di Kas Keluar) --}}
                @if ($activeTab === 'bkk')
                    <div class="vj-filter-field">
                        <label for="filterKategori" class="vj-filter-label">Kategori Pengeluaran</label>
                        <select id="filterKategori" name="kategori" class="vj-filter-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoriList as $key => $lbl)
                                <option value="{{ $key }}" {{ $filters['kategori'] === $key ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Tanggal Mulai --}}
                <div class="vj-filter-field vj-field-date">
                    <label for="filterStartDate" class="vj-filter-label">Tgl Mulai</label>
                    <input type="date"
                           id="filterStartDate"
                           name="start_date"
                           value="{{ $filters['start_date'] }}"
                           class="vj-filter-input">
                </div>

                {{-- Tanggal Selesai --}}
                <div class="vj-filter-field vj-field-date">
                    <label for="filterEndDate" class="vj-filter-label">Tgl Selesai</label>
                    <input type="date"
                           id="filterEndDate"
                           name="end_date"
                           value="{{ $filters['end_date'] }}"
                           class="vj-filter-input">
                </div>

                {{-- Tombol Aksi Filter --}}
                <div class="vj-filter-actions">
                    <button type="submit" class="vj-btn-filter">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cari</span>
                    </button>
                    @if (!empty($filters['search']) || !empty($filters['kas_bank_id']) || !empty($filters['kategori']) || !empty($filters['start_date']) || !empty($filters['end_date']))
                        <a href="{{ route('keuangan.verifikasi_jurnal.index', ['tab' => $activeTab]) }}" class="vj-btn-reset" title="Hapus Filter">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ====================================================================== --}}
    {{-- KONTEN TAB 1: KAS KELUAR (BKK) --}}
    {{-- ====================================================================== --}}
    @if ($activeTab === 'bkk')
        {{-- BATCH ACTION TOOLBAR BKK --}}
        @if ($bkkList->count() > 0)
            <div class="vj-batch-toolbar">
                <div class="vj-batch-left">
                    <label class="vj-checkbox-container" title="Pilih seluruh BKK pada halaman ini yang telah siap dipetakan">
                        <input type="checkbox" id="checkAllReady" class="vj-checkbox" onchange="toggleSelectAll(this)">
                        <span class="vj-checkbox-mark"></span>
                        <span class="vj-checkbox-text">Pilih Semua yang Siap Jurnal</span>
                    </label>
                    <div class="vj-batch-indicators">
                        <span id="selectedCountBadge" class="vj-batch-badge">0 BKK Terpilih</span>
                        <span id="selectedTotalBadge" class="vj-batch-total" style="display: none;">(Rp 0)</span>
                    </div>
                </div>

                <div class="vj-batch-center">
                    <div class="vj-quick-filter-group" role="tablist">
                        <button type="button" class="vj-quick-tab active" data-filter="all" onclick="applyClientFilter('all', this)">
                            <span>Semua</span>
                            <span class="vj-tab-count" id="tabCountAll">{{ $bkkList->count() }}</span>
                        </button>
                        <button type="button" class="vj-quick-tab" data-filter="ready" onclick="applyClientFilter('ready', this)">
                            <span>Siap Jurnal</span>
                            <span class="vj-tab-count vj-tab-count-ready" id="tabCountReady">0</span>
                        </button>
                        <button type="button" class="vj-quick-tab" data-filter="unmapped" onclick="applyClientFilter('unmapped', this)">
                            <span>Belum Dipetakan</span>
                            <span class="vj-tab-count vj-tab-count-unmapped" id="tabCountUnmapped">0</span>
                        </button>
                    </div>
                </div>

                <div class="vj-batch-right">
                    @if (Auth::user()->canCreateJurnal() || Auth::user()->canManageCoa())
                        <button type="button" id="btnBatchVerify" class="vj-btn-batch-primary" disabled onclick="openBatchConfirmModal()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Verifikasi Masal Terpilih</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- TABEL BKK --}}
        <div class="vj-table-card">
            @if ($bkkList->count() > 0)
                <div class="vj-table-scroll">
                    <table class="vj-table" id="vjMainTable">
                        <thead>
                            <tr>
                                <th style="width: 175px;">No. BKK &amp; Tanggal</th>
                                <th style="min-width: 250px;">Penerima &amp; Keperluan</th>
                                <th style="width: 200px;">Rekening Kas &amp; Total Keluar</th>
                                <th style="min-width: 360px;">Pos Akun Beban (COA Debit)</th>
                                <th style="width: 170px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bkkList as $bkk)
                                @php
                                    $defaultTglJurnal = $bkk->tgl_keluar
                                        ? \Carbon\Carbon::parse($bkk->tgl_keluar)->format('Y-m-d')
                                        : date('Y-m-d');
                                    $defaultCoaId = $bkk->coa_id ?? $bkk->kategoriPengeluaran?->coa_id_default;
                                    $suggestedKode = $bkk->kategoriPengeluaran?->coaDefault?->kode_akun ?? match ($bkk->kategori_pengeluaran) {
                                        'LISTRIK_AIR'           => '6102',
                                        'BBM_TRANSPORT'         => '6107',
                                        'PEMELIHARAAN_MESIN'    => '6106',
                                        'PERLENGKAPAN_ATK'      => '6100',
                                        'KONSUMSI_RUMAH_TANGGA' => '6105',
                                        'RISET_QC'              => '6110',
                                        'PENGIRIMAN_EKSPEDISI'  => '6111',
                                        'BIAYA_OPERASIONAL'     => '6110',
                                        'LAINNYA'               => '6110',
                                        default                 => null,
                                    };

                                    $resolvedCoaId = null;
                                    if ($defaultCoaId) {
                                        $resolvedCoaId = (int) $defaultCoaId;
                                    } elseif ($suggestedKode) {
                                        $matched = $expenseCoas->firstWhere('kode_akun', $suggestedKode);
                                        if ($matched) {
                                            $resolvedCoaId = (int) $matched->coa_id;
                                        }
                                    }

                                    $isReady = !empty($resolvedCoaId);
                                    $menuId = 'menu-bkk-' . $bkk->bkk_id;
                                    $itemDetailsData = $bkk->details->map(function ($d) {
                                        return [
                                            'uraian'       => $d->uraian,
                                            'qty'          => (float) $d->qty,
                                            'satuan'       => $d->satuan ?: 'Pcs',
                                            'harga_satuan' => (float) $d->harga_satuan,
                                            'nominal'      => (float) $d->nominal,
                                        ];
                                    });
                                @endphp
                                <tr class="vj-table-row {{ $isReady ? 'row-ready' : 'row-unmapped' }}"
                                    id="row-bkk-{{ $bkk->bkk_id }}"
                                    data-bkk-id="{{ $bkk->bkk_id }}"
                                    data-bkk-no="{{ $bkk->bkk_no }}"
                                    data-tgl="{{ \Carbon\Carbon::parse($bkk->tgl_keluar)->translatedFormat('d M Y') }}"
                                    data-tgl-raw="{{ $defaultTglJurnal }}"
                                    data-nominal="{{ (float) $bkk->total_keluar }}"
                                    data-nominal-formatted="Rp {{ number_format($bkk->total_keluar, 0, ',', '.') }}"
                                    data-kas-bank="{{ $bkk->kasBank?->nama_akun ?? 'Kas Fisik' }}"
                                    data-penerima="{{ $bkk->penerima_nama }}"
                                    data-pemohon="{{ $bkk->pemohon?->karyawan_nm ?? ($bkk->departemen_pemohon ?: '-') }}"
                                    data-metode="{{ $bkk->metode_bayar . ($bkk->no_referensi_bank ? ' (Ref: ' . $bkk->no_referensi_bank . ')' : '') }}"
                                    data-catatan="{{ $bkk->catatan ?? '' }}"
                                    data-items="{{ json_encode($itemDetailsData) }}"
                                    data-ready="{{ $isReady ? '1' : '0' }}">
                                    
                                    {{-- KOLOM 1: Checkbox & No. BKK & Tanggal --}}
                                    <td>
                                        <div class="vj-col-bkk">
                                            <div class="vj-bkk-select-wrap">
                                                <label class="vj-checkbox-container" title="Pilih BKK {{ $bkk->bkk_no }}">
                                                    <input type="checkbox"
                                                           class="vj-checkbox vj-row-checkbox"
                                                           value="{{ $bkk->bkk_id }}"
                                                           onchange="onRowCheckboxChange(this)">
                                                    <span class="vj-checkbox-mark"></span>
                                                </label>
                                                <div class="vj-bkk-info">
                                                    @if (Auth::user()->canAccessBayarHutang())
                                                        <a href="{{ route('keuangan.kas_keluar.show', $bkk->bkk_id) }}" class="vj-bkk-no" title="Buka detail BKK">
                                                            {{ $bkk->bkk_no }}
                                                        </a>
                                                    @else
                                                        <span class="vj-bkk-no">{{ $bkk->bkk_no }}</span>
                                                    @endif
                                                    <span class="vj-bkk-date">
                                                        {{ \Carbon\Carbon::parse($bkk->tgl_keluar)->translatedFormat('d M Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="vj-bkk-creator">
                                                Kasir: <strong>{{ $bkk->created_by ?: 'SYSTEM' }}</strong>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM 2: Penerima & Keperluan --}}
                                    <td>
                                        <div class="vj-col-recipient">
                                            <div class="vj-recipient-header">
                                                <span class="vj-recipient-name">{{ $bkk->penerima_nama }}</span>
                                                @if ($bkk->tipe_penerima === 'KARYAWAN')
                                                    <span class="vj-pill-tag">Karyawan</span>
                                                @endif
                                            </div>

                                            @if ($bkk->pemohon || $bkk->departemen_pemohon)
                                                <div class="vj-pic-sub">
                                                    PIC: <strong>{{ $bkk->pemohon?->karyawan_nm ?? 'Karyawan' }}</strong>
                                                    @if ($bkk->departemen_pemohon || $bkk->pemohon?->departemen_cd)
                                                        <span class="text-slate-500">({{ $bkk->departemen_pemohon ?: $bkk->pemohon->departemen_cd }})</span>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="vj-purpose-preview">
                                                @php
                                                    $firstItem = $bkk->details->first();
                                                    $detailCount = $bkk->details->count();
                                                @endphp
                                                @if ($firstItem)
                                                    <span class="vj-purpose-text">{{ $firstItem->uraian }}</span>
                                                @else
                                                    <span class="vj-purpose-text text-slate-400">Pengeluaran kas operasional</span>
                                                @endif

                                                <button type="button"
                                                        class="vj-btn-open-detail"
                                                        onclick="openDetailModal({{ $bkk->bkk_id }})"
                                                        title="Lihat rincian seluruh item pengeluaran">
                                                    [Lihat Rincian Item ({{ $detailCount }})]
                                                </button>
                                            </div>

                                            @if ($bkk->catatan)
                                                <div class="vj-row-note" title="{{ $bkk->catatan }}">
                                                    Catatan: "{{ \Illuminate\Support\Str::limit($bkk->catatan, 45) }}"
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- KOLOM 3: Rekening Kas & Total Keluar --}}
                                    <td>
                                        <div class="vj-col-account">
                                            <div class="vj-source-name">
                                                {{ $bkk->kasBank?->nama_akun ?? 'Kas Fisik' }}
                                            </div>
                                            <div class="vj-source-method">
                                                {{ $bkk->metode_bayar }}
                                                @if ($bkk->no_referensi_bank)
                                                    • Ref: {{ $bkk->no_referensi_bank }}
                                                @endif
                                            </div>
                                            <div class="vj-total-nominal">
                                                Rp {{ number_format($bkk->total_keluar, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM 4: Pos Akun Beban (COA Debit) --}}
                                    <td>
                                        <div class="vj-col-coa">
                                            <div class="vj-coa-select-row">
                                                <select class="vj-coa-select"
                                                        id="coa_select_{{ $bkk->bkk_id }}"
                                                        onchange="onCoaSelectChange({{ $bkk->bkk_id }})"
                                                        aria-label="Pilih Pos Akun Beban untuk {{ $bkk->bkk_no }}">
                                                    <option value="">-- Pilih Akun Beban --</option>
                                                    @foreach ($expenseCoas as $coa)
                                                        <option value="{{ $coa->coa_id }}"
                                                                data-kode="{{ $coa->kode_akun }}"
                                                                data-nama="{{ $coa->nama_akun }}"
                                                                {{ (int) $resolvedCoaId === (int) $coa->coa_id ? 'selected' : '' }}>
                                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="vj-coa-meta-row">
                                                <div class="vj-coa-badge-wrap" id="coa_badge_{{ $bkk->bkk_id }}">
                                                    @if ($isReady)
                                                        <span class="vj-status-pill vj-status-ready">
                                                            <span class="vj-status-dot"></span> Sesuai Kategori
                                                        </span>
                                                    @else
                                                        <span class="vj-status-pill vj-status-unmapped">
                                                            <span class="vj-status-dot"></span> Perlu Dipilih
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="vj-date-compact-wrap">
                                                    <span class="vj-date-label">Tgl Jurnal:</span>
                                                    <input type="date"
                                                           class="vj-row-date"
                                                           id="tgl_jurnal_{{ $bkk->bkk_id }}"
                                                           value="{{ $defaultTglJurnal }}"
                                                           title="Tanggal Penerbitan Jurnal">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM 5: Aksi (Single Verify & Smart Action Dropdown) --}}
                                    <td>
                                        <div class="vj-col-actions">
                                            @if (Auth::user()->canCreateJurnal() || Auth::user()->canManageCoa())
                                                <button type="button"
                                                        class="vj-btn-single-verify"
                                                        id="btnVerifySingle_{{ $bkk->bkk_id }}"
                                                        onclick="verifySingleBkk({{ $bkk->bkk_id }})"
                                                        title="Verifikasi BKK ini dan terbitkan jurnal sekarang">
                                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Verifikasi</span>
                                                </button>
                                            @endif

                                            {{-- SMART ACTION DROPDOWN (Standard AGENTS.md) --}}
                                            <button type="button"
                                                    class="btn-action-trigger"
                                                    aria-haspopup="true"
                                                    aria-label="Menu aksi {{ $bkk->bkk_no }}"
                                                    onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                                <span>Aksi</span>
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            <div id="{{ $menuId }}" class="action-dropdown-menu" role="menu">
                                                @if (Auth::user()->canAccessBayarHutang())
                                                    <a href="{{ route('keuangan.kas_keluar.show', $bkk->bkk_id) }}" class="action-dropdown-item" role="menuitem">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        <span>Lihat Slip BKK</span>
                                                    </a>
                                                @endif

                                                <button type="button" class="action-dropdown-item" role="menuitem" onclick="openDetailModal({{ $bkk->bkk_id }})">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    <span>Rincian Item</span>
                                                </button>

                                                @if (Auth::user()->canAccessBayarHutang())
                                                    <button type="button" class="action-dropdown-item danger-item" role="menuitem" onclick="openVoidModal({{ $bkk->bkk_id }}, '{{ $bkk->bkk_no }}')">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                        <span>Batalkan / Void</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION BKK --}}
                @if ($bkkList->hasPages())
                    <div class="vj-pagination">
                        <div class="vj-pagination-info">
                            Menampilkan {{ $bkkList->firstItem() }} sampai {{ $bkkList->lastItem() }} dari total {{ $bkkList->total() }} transaksi
                        </div>
                        <div>
                            {{ $bkkList->links() }}
                        </div>
                    </div>
                @endif
            @else
                {{-- EMPTY STATE BKK --}}
                <div class="vj-empty-state">
                    <div class="vj-empty-icon-wrap" aria-hidden="true">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="vj-empty-title">Semua Kas Keluar Sudah Diverifikasi</h3>
                    <p class="vj-empty-text">
                        Tidak ada antrean pengeluaran kas operasional (BKK) yang menunggu verifikasi jurnal. Seluruh bukti pengeluaran kasir telah dipetakan pos akun bebannya dan tercatat di Jurnal Umum.
                    </p>
                    <div class="vj-empty-actions">
                        @if (Auth::user()->canAccessJurnal())
                            <a href="{{ route('keuangan.jurnal.index') }}" class="vj-btn vj-btn-secondary">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Buka Jurnal Umum</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessBayarHutang())
                            <a href="{{ route('keuangan.kas_keluar.index') }}" class="vj-btn vj-btn-secondary">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Lihat Riwayat Kas Keluar</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ====================================================================== --}}
    {{-- KONTEN TAB 2: KAS MASUK (BKM) --}}
    {{-- ====================================================================== --}}
    @if ($activeTab === 'bkm')
        {{-- BATCH ACTION TOOLBAR BKM --}}
        @if ($bkmList->count() > 0)
            <div class="vj-batch-toolbar">
                <div class="vj-batch-left">
                    <label class="vj-checkbox-container" title="Pilih seluruh BKM pada halaman ini yang telah siap dipetakan">
                        <input type="checkbox" id="checkAllReadyBkm" class="vj-checkbox" onchange="toggleSelectAllBkm(this)">
                        <span class="vj-checkbox-mark"></span>
                        <span class="vj-checkbox-text">Pilih Semua yang Siap Jurnal</span>
                    </label>
                    <div class="vj-batch-indicators">
                        <span id="selectedCountBadgeBkm" class="vj-batch-badge">0 BKM Terpilih</span>
                        <span id="selectedTotalBadgeBkm" class="vj-batch-total text-emerald-700" style="display: none;">(Rp 0)</span>
                    </div>
                </div>

                <div class="vj-batch-center">
                    <div class="vj-quick-filter-group" role="tablist">
                        <button type="button" class="vj-quick-tab active" data-filter="all" onclick="applyClientFilterBkm('all', this)">
                            <span>Semua</span>
                            <span class="vj-tab-count" id="tabCountAllBkm">{{ $bkmList->count() }}</span>
                        </button>
                        <button type="button" class="vj-quick-tab" data-filter="ready" onclick="applyClientFilterBkm('ready', this)">
                            <span>Siap Jurnal</span>
                            <span class="vj-tab-count vj-tab-count-ready" id="tabCountReadyBkm">0</span>
                        </button>
                        <button type="button" class="vj-quick-tab" data-filter="unmapped" onclick="applyClientFilterBkm('unmapped', this)">
                            <span>Belum Dipetakan</span>
                            <span class="vj-tab-count vj-tab-count-unmapped" id="tabCountUnmappedBkm">0</span>
                        </button>
                    </div>
                </div>

                <div class="vj-batch-right">
                    @if (Auth::user()->canCreateJurnal() || Auth::user()->canManageCoa())
                        <button type="button" id="btnBatchVerifyBkm" class="vj-btn-batch-primary" disabled onclick="openBatchConfirmModalBkm()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Verifikasi Masal Terpilih</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- TABEL BKM --}}
        <div class="vj-table-card">
            @if ($bkmList->count() > 0)
                <div class="vj-table-scroll">
                    <table class="vj-table" id="vjMainTableBkm">
                        <thead>
                            <tr>
                                <th style="width: 175px;">No. BKM &amp; Tanggal</th>
                                <th style="min-width: 240px;">Penyetor &amp; Uraian Kasir</th>
                                <th style="width: 200px;">Rekening Kas &amp; Total Terima</th>
                                <th style="min-width: 440px;">Jurnal Akuntan (Double-Entry GL)</th>
                                <th style="width: 175px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bkmList as $bkm)
                                @php
                                    $defaultTglJurnalBkm = $bkm->tgl_terima
                                        ? \Carbon\Carbon::parse($bkm->tgl_terima)->format('Y-m-d')
                                        : date('Y-m-d');
                                    $penyetorName = $bkm->penyetor_nama ?: ($bkm->customer?->customer_nm ?: 'Penyetor');
                                    $firstDetailUraian = $bkm->details->first()?->uraian ?: 'Penerimaan Kas/Bank';
                                    $defaultKeteranganJurnal = "BKM {$bkm->bkm_no} - {$firstDetailUraian} ({$penyetorName})";
                                    $menuBkmId = 'menu-bkm-' . $bkm->bkm_id;

                                    // Item details data untuk modal
                                    $itemDetailsDataBkm = $bkm->details->map(function ($d) {
                                        return [
                                            'uraian'       => $d->uraian,
                                            'qty'          => (float) $d->qty,
                                            'satuan'       => $d->satuan ?: 'Pcs',
                                            'harga_satuan' => (float) $d->harga_satuan,
                                            'nominal'      => (float) $d->nominal,
                                        ];
                                    });

                                    // Check jika COA lawan sudah ada di DB
                                    $preselectedCoaId = $bkm->coa_id ? (int) $bkm->coa_id : null;
                                    $preselectedCoa = $preselectedCoaId ? $creditCoas->firstWhere('coa_id', $preselectedCoaId) : null;
                                    $isReadyBkm = !empty($preselectedCoaId);
                                @endphp
                                <tr class="vj-table-row {{ $isReadyBkm ? 'row-ready' : 'row-unmapped' }}"
                                    id="row-bkm-{{ $bkm->bkm_id }}"
                                    data-bkm-id="{{ $bkm->bkm_id }}"
                                    data-bkm-no="{{ $bkm->bkm_no }}"
                                    data-tgl="{{ \Carbon\Carbon::parse($bkm->tgl_terima)->translatedFormat('d M Y') }}"
                                    data-tgl-raw="{{ $defaultTglJurnalBkm }}"
                                    data-nominal="{{ (float) $bkm->total_terima }}"
                                    data-nominal-formatted="Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}"
                                    data-kas-bank="{{ $bkm->kasBank?->nama_akun ?? 'Kas Fisik' }}"
                                    data-penyetor="{{ $penyetorName }}"
                                    data-kasir="{{ $bkm->penerima_kasir ?: ($bkm->created_by ?: 'SYSTEM') }}"
                                    data-metode="{{ $bkm->metode_terima . ($bkm->no_referensi_bank ? ' (Ref: ' . $bkm->no_referensi_bank . ')' : '') }}"
                                    data-catatan="{{ $bkm->catatan ?? '' }}"
                                    data-items="{{ json_encode($itemDetailsDataBkm) }}"
                                    data-ready="{{ $isReadyBkm ? '1' : '0' }}">
                                    
                                    {{-- KOLOM 1: Checkbox & No. BKM & Tanggal --}}
                                    <td>
                                        <div class="vj-col-bkk">
                                            <div class="vj-bkk-select-wrap">
                                                <label class="vj-checkbox-container" title="Pilih BKM {{ $bkm->bkm_no }}">
                                                    <input type="checkbox"
                                                           class="vj-checkbox vj-row-checkbox-bkm"
                                                           value="{{ $bkm->bkm_id }}"
                                                           onchange="onRowCheckboxChangeBkm(this)">
                                                    <span class="vj-checkbox-mark"></span>
                                                </label>
                                                <div class="vj-bkk-info">
                                                    @if (Auth::user()->canAccessKasMasuk())
                                                        <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}" class="vj-bkk-no vj-bkm-no-link" title="Buka detail BKM">
                                                            {{ $bkm->bkm_no }}
                                                        </a>
                                                    @else
                                                        <span class="vj-bkk-no">{{ $bkm->bkm_no }}</span>
                                                    @endif
                                                    <span class="vj-bkk-date">
                                                        {{ \Carbon\Carbon::parse($bkm->tgl_terima)->translatedFormat('d M Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="vj-bkk-creator">
                                                Kasir: <strong>{{ $bkm->penerima_kasir ?: ($bkm->created_by ?: 'SYSTEM') }}</strong>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM 2: Penyetor & Uraian Asli Kasir --}}
                                    <td>
                                        <div class="vj-col-recipient">
                                            <div class="vj-recipient-header">
                                                <span class="vj-recipient-name">{{ $penyetorName }}</span>
                                                @if ($bkm->tipe_penyetor === 'CUSTOMER')
                                                    <span class="vj-pill-tag vj-pill-tag-emerald">Customer</span>
                                                @elseif ($bkm->tipe_penyetor === 'KARYAWAN')
                                                    <span class="vj-pill-tag">Karyawan</span>
                                                @endif
                                            </div>

                                            <div class="vj-purpose-preview">
                                                @php
                                                    $detailCountBkm = $bkm->details->count();
                                                @endphp
                                                <span class="vj-purpose-text">{{ $firstDetailUraian }}</span>

                                                <button type="button"
                                                        class="vj-btn-open-detail"
                                                        onclick="openDetailModalBkm({{ $bkm->bkm_id }})"
                                                        title="Lihat rincian seluruh item penerimaan">
                                                    [Lihat Rincian Item ({{ $detailCountBkm }})]
                                                </button>
                                            </div>

                                            @if ($bkm->catatan)
                                                <div class="vj-row-note" title="{{ $bkm->catatan }}">
                                                    Catatan: "{{ \Illuminate\Support\Str::limit($bkm->catatan, 45) }}"
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- KOLOM 3: Rekening Kas & Total Terima --}}
                                    <td>
                                        <div class="vj-col-account">
                                            <div class="vj-source-name font-semibold text-emerald-800">
                                                {{ $bkm->kasBank?->nama_akun ?? 'Kas Fisik' }}
                                            </div>
                                            <div class="vj-source-method">
                                                {{ $bkm->metode_terima }}
                                                @if ($bkm->no_referensi_bank)
                                                    • Ref: {{ $bkm->no_referensi_bank }}
                                                @endif
                                            </div>
                                            <div class="vj-total-nominal text-emerald-700">
                                                Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM 4: Jurnal Akuntan (Double-Entry GL) --}}
                                    <td>
                                        <div class="vj-gl-entry-box">
                                            {{-- Baris 1: Akun Debet Kas/Bank Fisik (Read-Only / Locked) --}}
                                            <div class="vj-gl-row vj-gl-debet-row">
                                                <span class="vj-gl-type-pill vj-pill-debet">DEBET</span>
                                                <div class="vj-gl-locked-account" title="Akun Debet otomatis terkunci ke rekening penampung kasir">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b; flex-shrink: 0;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    <span class="vj-gl-locked-text">
                                                        {{ $bkm->kasBank?->nama_akun ?? 'Kas Fisik' }}
                                                        <span class="text-slate-400 font-mono">({{ $bkm->kasBank?->kode_akun ?? '1100' }})</span>
                                                    </span>
                                                </div>
                                                <span class="vj-gl-amount text-slate-800 font-mono">
                                                    Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}
                                                </span>
                                            </div>

                                            {{-- Baris 2: Akun Kredit Lawan (Searchable COA Picker) --}}
                                            <div class="vj-gl-row vj-gl-kredit-row">
                                                <span class="vj-gl-type-pill vj-pill-kredit">KREDIT</span>
                                                
                                                {{-- Searchable COA Picker Component --}}
                                                <div class="vj-coa-picker-wrapper" id="coa_picker_wrap_{{ $bkm->bkm_id }}">
                                                    <div class="vj-coa-search-box">
                                                        <svg class="vj-coa-picker-icon" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                        <input type="text"
                                                               class="vj-coa-picker-input"
                                                               id="coa_search_{{ $bkm->bkm_id }}"
                                                               data-bkm-id="{{ $bkm->bkm_id }}"
                                                               placeholder="Cari kode atau nama akun kredit..."
                                                               value="{{ $preselectedCoa ? $preselectedCoa->kode_akun . ' - ' . $preselectedCoa->nama_akun : '' }}"
                                                               autocomplete="off"
                                                               onfocus="openCoaDropdown({{ $bkm->bkm_id }})"
                                                               oninput="filterCoaOptions({{ $bkm->bkm_id }}, this.value)"
                                                               onkeydown="handleCoaPickerKey(event, {{ $bkm->bkm_id }})">
                                                        <input type="hidden"
                                                               name="coa_id"
                                                               id="bkm_coa_id_{{ $bkm->bkm_id }}"
                                                               value="{{ $preselectedCoaId ?? '' }}"
                                                               class="vj-bkm-selected-coa">
                                                        <button type="button"
                                                                class="vj-coa-clear-btn"
                                                                id="coa_clear_{{ $bkm->bkm_id }}"
                                                                style="{{ $isReadyBkm ? 'display: flex;' : 'display: none;' }}"
                                                                onclick="clearCoaSelection({{ $bkm->bkm_id }})"
                                                                title="Hapus pilihan COA">
                                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    
                                                    {{-- Autocomplete Dropdown List --}}
                                                    <div class="vj-coa-dropdown" id="coa_dropdown_{{ $bkm->bkm_id }}" style="display: none;">
                                                        <div class="vj-coa-list-scroll">
                                                            @foreach ($creditCoas as $coa)
                                                                <div class="vj-coa-option {{ $preselectedCoaId === $coa->coa_id ? 'selected' : '' }}"
                                                                     data-id="{{ $coa->coa_id }}"
                                                                     data-kode="{{ $coa->kode_akun }}"
                                                                     data-nama="{{ $coa->nama_akun }}"
                                                                     data-kategori="{{ $coa->kategori_akun }}"
                                                                     onclick="selectCoaOption({{ $bkm->bkm_id }}, {{ $coa->coa_id }}, '{{ $coa->kode_akun }}', '{{ addslashes($coa->nama_akun) }}')">
                                                                    <span class="vj-coa-code">{{ $coa->kode_akun }}</span>
                                                                    <span class="vj-coa-name">{{ $coa->nama_akun }}</span>
                                                                    <span class="vj-coa-cat-badge">{{ $coa->kategori_akun }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="vj-coa-empty-msg" style="display: none;">Tidak ada akun COA yang cocok.</div>
                                                    </div>
                                                </div>

                                                <span class="vj-gl-amount text-slate-800 font-mono">
                                                    Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}
                                                </span>
                                            </div>

                                            {{-- Baris 3: Keterangan Jurnal Bersama (Sinkronisasi 2 Akun Jurnal) --}}
                                            <div class="vj-gl-keterangan-row">
                                                <label for="bkm_keterangan_{{ $bkm->bkm_id }}" class="vj-gl-desc-label">Keterangan Jurnal (Debet &amp; Kredit):</label>
                                                <input type="text"
                                                       class="vj-gl-desc-input"
                                                       id="bkm_keterangan_{{ $bkm->bkm_id }}"
                                                       value="{{ $defaultKeteranganJurnal }}"
                                                       placeholder="Keterangan transaksi jurnal umum..."
                                                       title="Keterangan ini disinkronkan secara identik ke baris Debet dan Kredit">
                                            </div>

                                            {{-- Baris 4: Footer Meta (Balance Checker & Tgl Jurnal) --}}
                                            <div class="vj-gl-meta-row">
                                                <div class="vj-balance-checker" id="balance_checker_{{ $bkm->bkm_id }}" title="Total Debet = Total Kredit (Selisih Rp 0)">
                                                    <span class="vj-balance-dot"></span>
                                                    <span class="vj-balance-text">SEIMBANG (Rp 0)</span>
                                                </div>

                                                <div class="vj-date-compact-wrap">
                                                    <span class="vj-date-label">Tgl Jurnal:</span>
                                                    <input type="date"
                                                           class="vj-row-date"
                                                           id="tgl_jurnal_bkm_{{ $bkm->bkm_id }}"
                                                           value="{{ $defaultTglJurnalBkm }}"
                                                           title="Tanggal Penerbitan Jurnal">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM 5: Aksi (Single Verify & Smart Action Dropdown) --}}
                                    <td>
                                        <div class="vj-col-actions">
                                            @if (Auth::user()->canCreateJurnal() || Auth::user()->canManageCoa())
                                                <button type="button"
                                                        class="vj-btn-single-verify vj-btn-verify-bkm"
                                                        id="btnVerifySingleBkm_{{ $bkm->bkm_id }}"
                                                        {{ $isReadyBkm ? '' : 'disabled' }}
                                                        onclick="verifySingleBkm({{ $bkm->bkm_id }})"
                                                        title="Verifikasi BKM ini dan terbitkan jurnal umum">
                                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Terbitkan Jurnal</span>
                                                </button>
                                            @endif

                                            {{-- SMART ACTION DROPDOWN (Standard AGENTS.md) --}}
                                            <button type="button"
                                                    class="btn-action-trigger"
                                                    aria-haspopup="true"
                                                    aria-label="Menu aksi {{ $bkm->bkm_no }}"
                                                    onclick="toggleSmartActionDropdown(this, event, '{{ $menuBkmId }}')">
                                                <span>Aksi</span>
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            <div id="{{ $menuBkmId }}" class="action-dropdown-menu" role="menu">
                                                @if (Auth::user()->canAccessKasMasuk())
                                                    <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}" class="action-dropdown-item" role="menuitem">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        <span>Lihat Slip BKM</span>
                                                    </a>
                                                @endif

                                                <button type="button" class="action-dropdown-item" role="menuitem" onclick="openDetailModalBkm({{ $bkm->bkm_id }})">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    <span>Rincian Item</span>
                                                </button>

                                                @if (Auth::user()->canAccessKasMasuk())
                                                    <button type="button" class="action-dropdown-item danger-item" role="menuitem" onclick="openVoidModalBkm({{ $bkm->bkm_id }}, '{{ $bkm->bkm_no }}')">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                        <span>Batalkan / Void</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION BKM --}}
                @if ($bkmList->hasPages())
                    <div class="vj-pagination">
                        <div class="vj-pagination-info">
                            Menampilkan {{ $bkmList->firstItem() }} sampai {{ $bkmList->lastItem() }} dari total {{ $bkmList->total() }} transaksi
                        </div>
                        <div>
                            {{ $bkmList->links() }}
                        </div>
                    </div>
                @endif
            @else
                {{-- EMPTY STATE BKM --}}
                <div class="vj-empty-state">
                    <div class="vj-empty-icon-wrap" aria-hidden="true">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="vj-empty-title">Semua Kas Masuk Sudah Diverifikasi</h3>
                    <p class="vj-empty-text">
                        Tidak ada antrean penerimaan kas operasional (BKM) yang menunggu verifikasi jurnal. Seluruh bukti penerimaan kasir telah dipetakan pos akun kreditnya dan tercatat di Jurnal Umum.
                    </p>
                    <div class="vj-empty-actions">
                        @if (Auth::user()->canAccessJurnal())
                            <a href="{{ route('keuangan.jurnal.index') }}" class="vj-btn vj-btn-secondary">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Buka Jurnal Umum</span>
                            </a>
                        @endif
                        @if (Auth::user()->canAccessKasMasuk())
                            <a href="{{ route('keuangan.kas_masuk.index') }}" class="vj-btn vj-btn-secondary">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Lihat Riwayat Kas Masuk</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- MODALS PARTIALS BKK --}}
@include('keuangan.verifikasi_jurnal.partials.modal-detail-bkk')
@include('keuangan.verifikasi_jurnal.partials.modal-confirm-batch')
@include('keuangan.verifikasi_jurnal.partials.modal-void-bkk')

{{-- MODALS PARTIALS BKM --}}
@include('keuangan.verifikasi_jurnal.partials.modal-detail-bkm')
@include('keuangan.verifikasi_jurnal.partials.modal-confirm-batch-bkm')
@include('keuangan.verifikasi_jurnal.partials.modal-void-bkm')

@endsection

@push('scripts')
    <script>
        window.vjConfig = {
            activeTab: "{{ $activeTab }}",
            batchUrl: "{{ route('keuangan.verifikasi_jurnal.batch_proses') }}",
            batchBkmUrl: "{{ route('keuangan.verifikasi_jurnal.batch_proses_bkm') }}",
            singleBaseUrl: "{{ url('keuangan/verifikasi-jurnal/bkk') }}",
            singleBkmBaseUrl: "{{ url('keuangan/verifikasi-jurnal/bkm') }}",
            voidBaseUrl: "{{ url('keuangan/kas-keluar') }}",
            voidBkmBaseUrl: "{{ url('keuangan/kas-masuk') }}",
            csrfToken: "{{ csrf_token() }}",
            showBaseUrl: "{{ url('keuangan/kas-keluar') }}",
            showBkmBaseUrl: "{{ url('keuangan/kas-masuk') }}"
        };
    </script>
    <script src="{{ asset('js/keuangan/verifikasi_jurnal/verifikasi-jurnal.js') }}?v={{ filemtime(public_path('js/keuangan/verifikasi_jurnal/verifikasi-jurnal.js')) }}"></script>
@endpush
