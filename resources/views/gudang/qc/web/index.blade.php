@extends('layouts.app')

@section('title', 'Pemeriksaan Mutu (QC) Inbound & HACCP - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/web/qc-admin-index.css') }}?v={{ file_exists(public_path('css/gudang/qc/web/qc-admin-index.css')) ? filemtime(public_path('css/gudang/qc/web/qc-admin-index.css')) : time() }}">
@endpush

@section('content')
<div class="qc-page-wrapper">

    {{-- 1. TOP HEADER SECTION (PERSIS STANDAR PO) --}}
    <div class="qc-top-header">
        <div>
            <div class="qc-header-kicker">PENJAMINAN MUTU &amp; LOGISTIK</div>
            <h1 class="qc-header-title">Pemeriksaan Mutu (QC) Inbound &amp; HACCP</h1>
            <p class="qc-header-subtitle">
                Monitoring hasil sampling kedatangan bahan baku, arsip lembar mutu HACCP, serta serah terima penarikan ke Gudang (GRN).
            </p>
        </div>

        <div class="qc-header-actions">
            <a href="{{ route('qc.inbound.index', ['status_qc' => 'SIAP_GUDANG']) }}" class="btn-pill-outline" title="Lihat antrean bahan baku yang lolos uji dan siap ditarik ke GRN">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Antrean Siap Gudang</span>
            </a>

            @if (Auth::user()?->canCreateQcMobile() || Auth::user()?->canCreateQc())
                <a href="{{ route('qc.inbound.create') }}" class="btn-pill-dark" title="Buka form inspeksi penerimaan bahan di lapangan">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Rekam QC Lapangan</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 2. 5 KARTU METRIK OPERASIONAL (GAYA STANDAR PO 5 KOLOM) --}}
    @php
        $curStatus = $filters['status_qc'] ?? '';
    @endphp
    <div class="qc-metrics-grid">
        {{-- Card 1: Semua QC (Aktif Default jika filter status kosong) --}}
        <div class="qc-metric-card {{ empty($curStatus) ? 'is-active' : '' }}">
            <div class="qc-metric-top">
                <div class="qc-metric-header-left">
                    <span class="qc-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <span class="qc-metric-label">Semua Dokumen</span>
                </div>
                <span class="qc-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="qc-metric-value">{{ number_format($inspeksiList->total() ?? 0) }}</div>
            <div class="qc-metric-footer">Total tiket sampling tercatat</div>
        </div>

        {{-- Card 2: Siap Ditarik GRN --}}
        <div class="qc-metric-card {{ $curStatus === 'SIAP_GUDANG' ? 'is-active' : '' }}">
            <div class="qc-metric-top">
                <div class="qc-metric-header-left">
                    <span class="qc-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="qc-metric-label">Siap Ditarik GRN</span>
                </div>
                <span class="qc-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="qc-metric-value">{{ number_format($countSiap ?? 0) }}</div>
            <div class="qc-metric-footer">Lolos sampling &bull; Siap bongkar</div>
        </div>

        {{-- Card 3: Menunggu Pengujian 2 --}}
        <div class="qc-metric-card {{ $curStatus === 'MENUNGGU_UJI_2' ? 'is-active' : '' }}">
            <div class="qc-metric-top">
                <div class="qc-metric-header-left">
                    <span class="qc-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </span>
                    <span class="qc-metric-label">Menunggu Uji 2</span>
                </div>
                <span class="qc-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="qc-metric-value">{{ number_format($countMenungguUji2 ?? 0) }}</div>
            <div class="qc-metric-footer">Truk singkong siap uji sisa bak</div>
        </div>

        {{-- Card 4: Masuk Penuh (GRN) --}}
        <div class="qc-metric-card {{ $curStatus === 'DITERIMA_GUDANG' ? 'is-active' : '' }}">
            <div class="qc-metric-top">
                <div class="qc-metric-header-left">
                    <span class="qc-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="qc-metric-label">Masuk Penuh (GRN)</span>
                </div>
                <span class="qc-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="qc-metric-value">{{ number_format($countSelesai ?? 0) }}</div>
            <div class="qc-metric-footer">Telah terbit bukti terima (GRN)</div>
        </div>

        {{-- Card 5: Ditolak / Masuk Parsial (Bebas Emoji, Ikon SVG Alert Monokromatik) --}}
        <div class="qc-metric-card {{ in_array($curStatus, ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL', 'DITOLAK_TOTAL']) ? 'is-active' : '' }}">
            <div class="qc-metric-top">
                <div class="qc-metric-header-left">
                    <span class="qc-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                    <span class="qc-metric-label">Ditolak / Parsial</span>
                </div>
                <span class="qc-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="qc-metric-value">{{ number_format(($countParsial ?? 0) + ($countReject ?? 0)) }}</div>
            <div class="qc-metric-footer">Ada muatan ditolak / Berita Acara</div>
        </div>
    </div>

    {{-- 3. MAIN DATA CARD (TABLE & CONTROLS STANDAR PO) --}}
    <div class="qc-main-card">
        {{-- Control Bar --}}
        <div class="qc-control-bar">
            {{-- Tabs Status Row --}}
            <div class="qc-tabs-row">
                <div class="qc-status-pills">
                    <a href="{{ route('qc.inbound.index') }}"
                       class="qc-status-tab {{ empty($curStatus) ? 'active' : '' }}">
                        <span>Semua</span>
                        <span class="badge-count">{{ $inspeksiList->total() }}</span>
                    </a>
                    <a href="{{ route('qc.inbound.index', ['status_qc' => 'SIAP_GUDANG']) }}"
                       class="qc-status-tab {{ $curStatus === 'SIAP_GUDANG' ? 'active' : '' }}">
                        <span>Siap Ditarik GRN</span>
                        <span class="badge-count">{{ $countSiap ?? 0 }}</span>
                    </a>
                    <a href="{{ route('qc.inbound.index', ['status_qc' => 'MENUNGGU_UJI_2']) }}"
                       class="qc-status-tab {{ $curStatus === 'MENUNGGU_UJI_2' ? 'active' : '' }}">
                        <span>Menunggu Uji 2</span>
                        <span class="badge-count">{{ $countMenungguUji2 ?? 0 }}</span>
                    </a>
                    <a href="{{ route('qc.inbound.index', ['status_qc' => 'DITERIMA_GUDANG']) }}"
                       class="qc-status-tab {{ $curStatus === 'DITERIMA_GUDANG' ? 'active' : '' }}">
                        <span>Selesai (GRN Penuh)</span>
                        <span class="badge-count">{{ $countSelesai ?? 0 }}</span>
                    </a>
                    <a href="{{ route('qc.inbound.index', ['status_qc' => 'DITERIMA_PARSIAL']) }}"
                       class="qc-status-tab {{ in_array($curStatus, ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL', 'PARSIAL', 'DITOLAK_TOTAL']) ? 'active' : '' }}">
                        <span>Ditolak / Parsial</span>
                        <span class="badge-count">{{ ($countParsial ?? 0) + ($countReject ?? 0) }}</span>
                    </a>
                </div>
            </div>

            {{-- Row 2: Search & Filter Form --}}
            <form action="{{ route('qc.inbound.index') }}" method="GET" class="qc-filter-form">
                @if(!empty($curStatus))
                    <input type="hidden" name="status_qc" value="{{ $curStatus }}">
                @endif
                @if(!empty($perPage))
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                @endif

                <div class="qc-search-wrapper">
                    <span class="qc-search-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="Cari nomor QC, plat truk, supplier, sopir..." 
                           class="qc-search-input">
                </div>

                <select name="kategori_barang" class="qc-select-control" onchange="this.form.submit()">
                    <option value="">-- Semua Komoditas --</option>
                    <optgroup label="Bahan Baku (2 Tahap)">
                        <option value="SINGKONG" {{ ($filters['kategori_barang'] ?? '') === 'SINGKONG' ? 'selected' : '' }}>Singkong</option>
                    </optgroup>
                    <optgroup label="Bahan Penolong (1 Tahap)">
                        <option value="MINYAK" {{ ($filters['kategori_barang'] ?? '') === 'MINYAK' ? 'selected' : '' }}>Minyak Goreng</option>
                        <option value="PLASTIK" {{ ($filters['kategori_barang'] ?? '') === 'PLASTIK' ? 'selected' : '' }}>Plastik Kemasan</option>
                        <option value="KARTON" {{ ($filters['kategori_barang'] ?? '') === 'KARTON' ? 'selected' : '' }}>Karton Box</option>
                        <option value="MSG" {{ ($filters['kategori_barang'] ?? '') === 'MSG' ? 'selected' : '' }}>MSG</option>
                        <option value="GARAM" {{ ($filters['kategori_barang'] ?? '') === 'GARAM' ? 'selected' : '' }}>Garam</option>
                        <option value="PERENYAH" {{ ($filters['kategori_barang'] ?? '') === 'PERENYAH' ? 'selected' : '' }}>Perenyah</option>
                    </optgroup>
                </select>

                <select name="status_qc" class="qc-select-control" onchange="this.form.submit()">
                    <option value="">-- Status Spesifik --</option>
                    <option value="SIAP_GUDANG" {{ ($filters['status_qc'] ?? '') === 'SIAP_GUDANG' ? 'selected' : '' }}>Siap Gudang</option>
                    <option value="MENUNGGU_UJI_2" {{ ($filters['status_qc'] ?? '') === 'MENUNGGU_UJI_2' ? 'selected' : '' }}>Menunggu Uji 2</option>
                    <option value="DITERIMA_GUDANG" {{ ($filters['status_qc'] ?? '') === 'DITERIMA_GUDANG' ? 'selected' : '' }}>Selesai Masuk (GRN Penuh)</option>
                    <option value="DITERIMA_PARSIAL" {{ in_array(($filters['status_qc'] ?? ''), ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL', 'PARSIAL']) ? 'selected' : '' }}>Ditolak / Masuk Parsial</option>
                    <option value="DITOLAK_TOTAL" {{ ($filters['status_qc'] ?? '') === 'DITOLAK_TOTAL' ? 'selected' : '' }}>Ditolak Total (Reject)</option>
                </select>

                <select name="supplier_id" class="qc-select-control" onchange="this.form.submit()">
                    <option value="">-- Semua Supplier --</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->supplier_id }}" {{ ($filters['supplier_id'] ?? '') == $s->supplier_id ? 'selected' : '' }}>
                            {{ $s->supplier_nm }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-qc-filter">Filter</button>
                @if(!empty($filters['search']) || !empty($filters['kategori_barang']) || !empty($filters['status_qc']) || !empty($filters['supplier_id']))
                    <a href="{{ route('qc.inbound.index') }}" class="btn-qc-reset" title="Reset Filter">Reset</a>
                @endif

                <div class="qc-total-badge">
                    <button type="button" 
                            onclick="toggleAllDrawers()" 
                            class="btn-pill-outline" 
                            style="padding: 0.35rem 0.75rem; font-size: 0.775rem;"
                            title="Buka atau tutup seluruh panel rincian audit secara serentak">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        <span id="btnToggleAllTextLabel">Buka Semua Rincian</span>
                    </button>
                    <span>Total QC: <strong>{{ $inspeksiList->total() }}</strong> dokumen</span>
                </div>
            </form>
        </div>

        {{-- Tabel Data Bersih Tanpa Emoji --}}
        <div class="qc-table-responsive">
            <table class="qc-table-clean">
                <thead>
                    <tr>
                        <th class="qc-col-no">No.</th>
                        <th class="qc-col-drawer">
                            <button type="button" 
                                    id="btnToggleAllDrawersHeader"
                                    class="btn-drawer-toggle" 
                                    onclick="toggleAllDrawers()" 
                                    title="Buka Semua Rincian Audit" 
                                    style="width: 24px; height: 24px;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </th>
                        <th class="qc-col-dokumen">Dokumen QC &amp; Tanggal</th>
                        <th class="qc-col-armada">Armada &amp; Rekanan</th>
                        <th class="qc-col-muatan">Muatan SJ</th>
                        <th class="qc-col-uji1">Pengujian 1</th>
                        <th class="qc-col-uji2">Pengujian 2</th>
                        <th class="qc-col-netto">Total Netto</th>
                        <th class="qc-col-status">Status Gudang</th>
                        <th class="qc-col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspeksiList as $qc)
                        @php
                            $kat = strtoupper((string) ($qc->kategori_barang ?: 'SINGKONG'));
                            $isSingkong = in_array($kat, ['SINGKONG', 'UBI', 'TALAS']) || str_starts_with($kat, 'BB-') || str_contains($kat, 'SINGKONG');
                            $isBahanBaku = $isSingkong;
                            $isBahanPenolong = !$isBahanBaku;

                            // Detail item pertama & relasi master barang
                            $p1FirstDtl = $qc->details->first();
                            $barangObj = $p1FirstDtl?->barang;
                            $satuanCd = $barangObj?->satuanDasar?->satuan_cd ?: ($barangObj?->satuanDasar?->satuan_nm ?: ($isSingkong ? 'kg' : 'Pcs'));
                            $barangNm = $barangObj?->barang_nm ?: ($qc->nama_jenis ?: $kat);

                            // Pengujian 1 Data
                            $p1Gross = (float) $qc->details->sum('qty_timbang_gross');
                            $p1Refraksi = (float) $qc->details->sum('qty_refraksi');
                            $p1Reject = (float) $qc->details->sum('qty_reject');
                            $p1Netto = (float) $qc->details->sum('qty_netto_lolos');
                            $p1Grade = $p1FirstDtl?->grade_cd ?? 'A';
                            $p1RefPersen = (float) ($p1FirstDtl?->refraksi_persen ?? 0);

                            // Pengujian 2 Data (child)
                            $p2 = $qc->pengujian2List->first();
                            $p2Gross = $p2 ? (float) $p2->details->sum('qty_timbang_gross') : 0;
                            $p2Refraksi = $p2 ? (float) $p2->details->sum('qty_refraksi') : 0;
                            $p2Reject = $p2 ? (float) $p2->details->sum('qty_reject') : 0;
                            $p2Netto = $p2 ? (float) $p2->details->sum('qty_netto_lolos') : 0;
                            $p2FirstDtl = $p2 ? $p2->details->first() : null;
                            $p2Grade = $p2FirstDtl?->grade_cd ?? 'A';
                            $p2RefPersen = $p2FirstDtl ? (float) ($p2FirstDtl->refraksi_persen ?? 0) : 0;

                            // Combined Truck Totals
                            $totalGross = $isBahanBaku ? ($p1Gross + $p2Gross) : $p1Gross;
                            $totalRefraksi = $isBahanBaku ? ($p1Refraksi + $p2Refraksi) : $p1Refraksi;
                            $totalReject = $isBahanBaku ? ($p1Reject + $p2Reject) : $p1Reject;
                            $totalNetto = $isBahanBaku ? ($p1Netto + $p2Netto) : $p1Netto;

                            // Terima GRN
                            $terimaObj = $qc->terima ?: ($p2?->terima);
                            $isLocked = !empty($terimaObj) && !Auth::user()?->isSuperAdmin() && !Auth::user()?->isGudang();

                            // Cek Over-Delivery terhadap PO
                            $poObj = $qc->po;
                            $poTotalPesan = $poObj ? (float) $poObj->details->sum('pesan_qty') : 0;
                            $isOverPo = false;
                            $selisihLebihPo = 0;
                            $persenLebihPo = 0;
                            if ($poObj && $poTotalPesan > 0) {
                                $muatanAcuan = max((float)$qc->jumlah_surat_jalan, (float)$totalGross);
                                if ($muatanAcuan > $poTotalPesan) {
                                    $isOverPo = true;
                                    $selisihLebihPo = $muatanAcuan - $poTotalPesan;
                                    $persenLebihPo = round(($selisihLebihPo / $poTotalPesan) * 100, 1);
                                }
                            }
                        @endphp

                        {{-- BARIS UTAMA TRUK (BERSIH, LEGA, & TERATUR) --}}
                        <tr id="row-{{ $qc->qc_id }}" class="qc-main-row">
                            {{-- 0. NOMOR URUT --}}
                            <td class="qc-col-no" style="text-align: center; color: #64748b; font-size: 0.8125rem; font-weight: 600;">
                                {{ $inspeksiList->firstItem() ? ($inspeksiList->firstItem() + $loop->index) : $loop->iteration }}
                            </td>

                            {{-- 1. TOMBOL ACCORDION DRAWER --}}
                            <td class="qc-col-drawer" style="text-align: center;">
                                <button type="button" 
                                        class="btn-drawer-toggle" 
                                        onclick="toggleRowDrawer('{{ $qc->qc_id }}', this)" 
                                        title="Klik untuk melihat rincian teknis, refraksi &amp; audit truk ini">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </td>

                            {{-- 2. DOKUMEN QC & TANGGAL (BEBAS EMOJI) --}}
                            <td class="qc-col-dokumen">
                                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                    <a href="javascript:void(0)" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" style="font-weight: 700; color: #0284c7; text-decoration: none; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.85rem;" title="Klik untuk membuka dokumen HACCP">
                                        {{ $qc->qc_no }}
                                    </a>
                                    @if ($isBahanBaku)
                                        <span class="qc-badge-clean" style="background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0;">
                                            Bahan Baku &bull; {{ $kat }}
                                        </span>
                                    @else
                                        <span class="qc-badge-clean" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                                            Bahan Penolong &bull; {{ $kat }}
                                        </span>
                                    @endif
                                </div>
                                @if (!$isBahanBaku && $barangNm)
                                    <div style="font-size: 0.75rem; font-weight: 600; color: #0f172a; margin-top: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="{{ $barangNm }}">
                                        {{ $barangNm }}
                                    </div>
                                @endif
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.2rem; display: flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                            </td>

                            {{-- 3. ARMADA & REKANAN (BEBAS EMOJI) --}}
                            <td class="qc-col-armada">
                                <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        {{ $qc->plat_nomor_truk ?: '-' }}
                                    </span>
                                    @if ($isOverPo)
                                        <span class="qc-badge-clean warning" title="Kuantitas muatan melebihi pesanan PO #{{ $poObj?->po_no }} (PO: {{ number_format($poTotalPesan, 0, ',', '.') }} kg, Selisih Lebih: +{{ number_format($selisihLebihPo, 0, ',', '.') }} kg)">
                                            Over PO (+{{ number_format($selisihLebihPo, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }})
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 0.75rem; color: #475569; margin-top: 0.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="{{ $qc->supplier?->supplier_nm ?: '-' }}">
                                    {{ $qc->supplier?->supplier_nm ?: '-' }}
                                </div>
                                @if ($poObj)
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">
                                        PO: <strong style="color: #0284c7; font-family: ui-monospace, monospace;">{{ $poObj->po_no }}</strong>
                                        <span style="color: #94a3b8;">({{ number_format($poTotalPesan, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }})</span>
                                    </div>
                                @endif
                            </td>

                            {{-- 4. MUATAN SURAT JALAN --}}
                            <td class="qc-col-muatan" style="text-align: right;">
                                <div style="font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                                    {{ number_format((float)$qc->jumlah_surat_jalan, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}
                                </div>
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.1rem;">
                                    SJ: {{ $qc->surat_jalan_supplier ?: '-' }}
                                </div>
                            </td>

                            {{-- 5. PENGUJIAN 1 (BEBAS EMOJI) --}}
                            <td class="qc-col-uji1">
                                @if ($isBahanBaku)
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        <span style="font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">{{ number_format($p1Netto, 0, ',', '.') }} kg</span>
                                        @if ($p1Grade === 'B')
                                            <span class="qc-badge-clean grade-b">Gr. B</span>
                                        @elseif ($p1Grade === 'REJECT')
                                            <span class="qc-badge-clean grade-reject">Afkir</span>
                                        @else
                                            <span class="qc-badge-clean grade-a">Gr. A</span>
                                        @endif
                                    </div>
                                    @if ($p1Refraksi > 0)
                                        <div style="font-size: 0.6875rem; color: #d97706; margin-top: 0.1rem;">
                                            Ref: -{{ number_format($p1Refraksi, 0, ',', '.') }} kg ({{ number_format($p1RefPersen, 1) }}%)
                                        </div>
                                    @endif
                                @else
                                    {{-- BAHAN PENOLONG (PENGUJIAN 1 TAHAP) --}}
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        <span style="font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">{{ number_format($p1Netto, 0, ',', '.') }} {{ $satuanCd }}</span>
                                        @if ($kat === 'KARTON')
                                            @php
                                                $rowGA = $qc->details->firstWhere('grade_cd', 'A');
                                                $rowGB = $qc->details->firstWhere('grade_cd', 'B');
                                            @endphp
                                            @if ($rowGA && $rowGB && (float)$rowGA->qty_netto_lolos > 0 && (float)$rowGB->qty_netto_lolos > 0)
                                                <span class="qc-badge-clean grade-a">A: {{ number_format((float)$rowGA->qty_netto_lolos, 0, ',', '.') }}</span>
                                                <span class="qc-badge-clean grade-b">B: {{ number_format((float)$rowGB->qty_netto_lolos, 0, ',', '.') }}</span>
                                            @elseif ($p1Grade === 'B' || ($rowGB && (float)$rowGB->qty_netto_lolos > 0))
                                                <span class="qc-badge-clean grade-b">Gr. B</span>
                                            @elseif ($p1Grade === 'REJECT')
                                                <span class="qc-badge-clean grade-reject">Afkir</span>
                                            @else
                                                <span class="qc-badge-clean grade-a">Gr. A</span>
                                            @endif
                                        @else
                                            @if ($qc->status_qc === 'DITOLAK_TOTAL' || $p1Netto <= 0)
                                                <span class="qc-badge-clean grade-reject">Ditolak</span>
                                            @elseif ($p1Reject > 0)
                                                <span class="qc-badge-clean warning">Parsial</span>
                                            @else
                                                <span class="qc-badge-clean grade-a">Lolos Uji</span>
                                            @endif
                                        @endif
                                    </div>
                                    @if ($p1Reject > 0)
                                        <div style="font-size: 0.6875rem; color: #dc2626; font-weight: 700; margin-top: 0.1rem;">
                                            Reject: -{{ number_format($p1Reject, 0, ',', '.') }} {{ $satuanCd }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- 6. PENGUJIAN 2 (BEBAS EMOJI) --}}
                            <td class="qc-col-uji2">
                                @if ($isSingkong)
                                    @if ($p2)
                                        @if ($p2->status_qc === 'DITOLAK_TOTAL' || $p2Netto <= 0)
                                            <span class="qc-badge-clean grade-reject" title="Uji 2 Ditolak Total: muatan tidak masuk gudang">
                                                Ditolak Total ({{ number_format($p2Gross ?: $p2Reject, 0, ',', '.') }} kg)
                                            </span>
                                        @else
                                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                <span style="font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">{{ number_format($p2Netto, 0, ',', '.') }} kg</span>
                                                @if ($p2Grade === 'B')
                                                    <span class="qc-badge-clean grade-b">Gr. B</span>
                                                @elseif ($p2Grade === 'REJECT')
                                                    <span class="qc-badge-clean grade-reject">Afkir</span>
                                                @else
                                                    <span class="qc-badge-clean grade-a">Gr. A</span>
                                                @endif
                                            </div>
                                            @if ($p2Reject > 0)
                                                <div style="font-size: 0.6875rem; color: #dc2626; font-weight: 700; margin-top: 0.1rem;">
                                                    Ditolak: -{{ number_format($p2Reject, 0, ',', '.') }} kg
                                                </div>
                                            @elseif ($p2Refraksi > 0)
                                                <div style="font-size: 0.6875rem; color: #d97706; margin-top: 0.1rem;">
                                                    Ref: -{{ number_format($p2Refraksi, 0, ',', '.') }} kg ({{ number_format($p2RefPersen, 1) }}%)
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        @if ($qc->status_qc === 'DITOLAK_TOTAL')
                                            <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">&mdash; Pulang</span>
                                        @else
                                            <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: nowrap;">
                                                <span class="qc-badge-waiting">Menunggu Uji 2</span>
                                                @if (Auth::user()?->canCreateQcMobile() || Auth::user()?->canCreateQc())
                                                    <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id]) }}" 
                                                       class="qc-btn-uji2-pill" 
                                                       title="Catat Pengujian 2 (Sisa Bak)">
                                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                        <span>Uji 2</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                @else
                                    {{-- BAHAN PENOLONG: TIDAK ADA UJI 2 (HANYA 1 TAHAP) --}}
                                    <span style="color: #94a3b8; font-size: 0.725rem; font-style: italic; background: #f8fafc; padding: 2px 8px; border-radius: 9999px; border: 1px dashed #cbd5e1; display: inline-block; white-space: nowrap;">
                                        &mdash; (1 Tahap)
                                    </span>
                                @endif
                            </td>

                            {{-- 7. TOTAL NETTO LOLOS --}}
                            <td class="qc-col-netto" style="text-align: right;">
                                <span style="font-size: 0.95rem; font-weight: 800; color: #047857; font-variant-numeric: tabular-nums;">
                                    {{ number_format($totalNetto, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}
                                </span>
                                @if ($totalReject > 0)
                                    <div style="font-size: 0.6875rem; color: #dc2626; font-weight: 700; margin-top: 1px;" title="Kuantitas ditolak (tidak masuk stok)">
                                        -{{ number_format($totalReject, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }} Reject
                                    </div>
                                @endif
                            </td>

                            {{-- 8. STATUS GUDANG (WAVEDASH / LINEAR MODERN INDICATOR) --}}
                            <td class="qc-col-status" style="text-align: center;">
                                @if ($terimaObj)
                                    @if ($qc->status_qc === 'DITERIMA_PARSIAL' || $totalReject > 0 || ($p2 && $p2->status_qc === 'DITOLAK_TOTAL'))
                                        <div class="qc-status-stack">
                                            <a href="{{ route('gudang.terima.show', $terimaObj->terima_id) }}" 
                                               class="qc-status-indicator qc-status-partial" 
                                               title="Diterima sebagian di gudang">
                                                <span class="qc-status-dot is-partial"></span>
                                                <span>GRN Parsial</span>
                                            </a>
                                            <span class="qc-status-reject-subtext">Reject {{ number_format($totalReject, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</span>
                                        </div>
                                    @else
                                        <div class="qc-status-stack">
                                            <a href="{{ route('gudang.terima.show', $terimaObj->terima_id) }}" 
                                               class="qc-status-indicator qc-status-grn" 
                                               title="Telah terbit dokumen penerimaan gudang (GRN)">
                                                <span class="qc-status-dot is-completed"></span>
                                                <span>Masuk Gudang</span>
                                            </a>
                                            <span class="qc-status-ref">#{{ $terimaObj->terima_no }}</span>
                                        </div>
                                    @endif
                                @elseif ($qc->status_qc === 'DITOLAK_TOTAL' && (!$p2 || $p2->status_qc === 'DITOLAK_TOTAL'))
                                    <div class="qc-status-stack">
                                        <span class="qc-status-indicator qc-status-rejected" title="Ditolak di gerbang">
                                            <span class="qc-status-dot is-rejected"></span>
                                            <span>Ditolak</span>
                                        </span>
                                    </div>
                                @elseif ($isSingkong && !$p2 && $qc->status_qc !== 'DITOLAK_TOTAL')
                                    <div class="qc-status-stack">
                                        <span class="qc-status-indicator qc-status-waiting" title="Bongkar setengah bak, menunggu uji tahap 2">
                                            <span class="qc-status-dot is-waiting"></span>
                                            <span>Bongkar 1/2</span>
                                        </span>
                                    </div>
                                @elseif ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG') || in_array($qc->status_qc, ['PASSED', 'DITERIMA_GUDANG']))
                                    <div class="qc-status-stack">
                                        <span class="qc-status-indicator qc-status-ready" title="Lolos pengujian, siap ditarik ke GRN">
                                            <span class="qc-status-dot is-ready"></span>
                                            <span>Siap GRN</span>
                                        </span>
                                    </div>
                                @else
                                    <div class="qc-status-stack">
                                        <span class="qc-status-indicator" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;">
                                            <span class="qc-status-dot" style="background: #94a3b8;"></span>
                                            <span>{{ $qc->status_qc }}</span>
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- 9. SMART ACTION DROPDOWN --}}
                            <td class="qc-col-aksi">
                                <button type="button" 
                                        class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, 'qcDropdown-{{ $qc->qc_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="qcDropdown-{{ $qc->qc_id }}" class="action-dropdown-menu">
                                    {{-- 0. LIHAT RINCIAN AUDIT --}}
                                    <button type="button" class="action-dropdown-item" onclick="toggleRowDrawer('{{ $qc->qc_id }}', document.querySelector('#row-{{ $qc->qc_id }} .btn-drawer-toggle'))">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span style="font-weight: 700; color: #0284c7;">Buka Rincian Truk</span>
                                    </button>

                                    <div class="action-dropdown-divider"></div>

                                    {{-- 1. LIHAT DOKUMEN HACCP --}}
                                    <button type="button" class="action-dropdown-item" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>{{ $isSingkong ? 'HACCP Uji 1 (#' . $qc->qc_no . ')' : 'Dokumen HACCP (#' . $qc->qc_no . ')' }}</span>
                                    </button>
                                    @if ($p2)
                                        <button type="button" class="action-dropdown-item" onclick="openHaccpModal('{{ $p2->qc_id }}', '{{ $p2->qc_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span style="color: #7e22ce; font-weight: 700;">HACCP Uji 2 (#{{ $p2->qc_no }})</span>
                                        </button>
                                    @endif

                                    {{-- 2. CETAK LEMBAR HACCP A4 --}}
                                    <button type="button" class="action-dropdown-item" onclick="directPrintHaccp('{{ $qc->qc_id }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>{{ $isSingkong ? 'Cetak HACCP Uji 1 (A4)' : 'Cetak Dokumen HACCP (A4)' }}</span>
                                    </button>
                                    @if ($p2)
                                        <button type="button" class="action-dropdown-item" onclick="directPrintHaccp('{{ $p2->qc_id }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span style="color: #7e22ce;">Cetak HACCP Uji 2 (A4)</span>
                                        </button>
                                    @endif

                                    {{-- 3. CETAK BERITA ACARA PENOLAKAN --}}
                                    @if ($qc->status_qc === 'DITOLAK_TOTAL' || $qc->details->sum('qty_reject') > 0)
                                        <button type="button" class="action-dropdown-item" style="color: #dc2626; font-weight: 700;" onclick="openBeritaAcaraModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Berita Acara Penolakan</span>
                                        </button>
                                    @endif
                                    @if ($p2 && ($p2->status_qc === 'DITOLAK_TOTAL' || $p2->details->sum('qty_reject') > 0))
                                        <button type="button" class="action-dropdown-item" style="color: #dc2626; font-weight: 700;" onclick="openBeritaAcaraModal('{{ $p2->qc_id }}', '{{ $p2->qc_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Berita Acara Penolakan (Uji 2)</span>
                                        </button>
                                    @endif

                                    {{-- 4. TARIK KE GRN GUDANG --}}
                                    @if (!$terimaObj && ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG') || in_array($qc->status_qc, ['PASSED', 'DITERIMA_GUDANG'])) && Auth::user()?->canAccessTerima())
                                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Tarik ke GRN Gudang</span>
                                        </a>
                                    @endif

                                    {{-- 5. CATAT PENGUJIAN II (KHUSUS SINGKONG) - BEBAS EMOJI --}}
                                    @if ($isSingkong && !$p2 && $qc->status_qc !== 'DITOLAK_TOTAL' && (Auth::user()?->canCreateQcMobile() || Auth::user()?->canCreateQc()))
                                        <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id]) }}" class="action-dropdown-item" style="color: #7e22ce; font-weight: 700;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Catat Pengujian II</span>
                                        </a>
                                    @endif

                                    <div class="action-dropdown-divider"></div>

                                    {{-- 6. EDIT DOKUMEN QC --}}
                                    @if (Auth::user()?->canEditQcWeb())
                                        @if (!$isLocked)
                                            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'ref' => 'index']) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>{{ $isSingkong ? 'Edit QC Uji 1' : 'Edit Dokumen QC' }}</span>
                                            </a>
                                            @if ($p2)
                                                <a href="{{ route('qc.inbound.edit', [$p2->qc_id, 'ref' => 'index']) }}" class="action-dropdown-item">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit QC Uji 2</span>
                                                </a>
                                            @endif
                                        @else
                                            <span class="action-dropdown-item" style="color: #94a3b8; cursor: not-allowed;" title="Terkunci karena sudah terbit GRN Gudang">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span>Terkunci (GRN Ada)</span>
                                            </span>
                                        @endif
                                    @endif

                                    {{-- 7. BATALKAN / HAPUS TIKET QC --}}
                                    @if (Auth::user()?->canDeleteQcWeb() && !$isLocked)
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteQcModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Batalkan Tiket QC</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- SUB-ROW ACCORDION DRAWER DETAIL AUDIT TRUK --}}
                        @include('gudang.qc.partials.common.drawer-detail')

                    @empty
                        <tr>
                            <td colspan="10" style="padding: 3.5rem 1.5rem; text-align: center; color: #64748b;">
                                <div style="display: flex; justify-content: center; margin-bottom: 0.75rem; color: #94a3b8;">
                                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                                <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">Belum Ada Tiket QC Inbound</div>
                                <div style="font-size: 0.8125rem; margin-top: 0.25rem;">
                                    Belum ada data kedatangan bahan baku yang dicatat atau sesuai kriteria filter di atas.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. PAGINATION MODERN TERPADU (STANDAR PO) --}}
        @include('gudang.qc.partials.pagination-modern')
    </div>

</div>

{{-- MODAL KONFIRMASI HAPUS --}}
@include('gudang.qc.partials.common.modal-delete')

{{-- MODAL POPUP PREVIEW DOKUMEN HACCP --}}
@include('gudang.qc.partials.common.modal-preview-haccp')

{{-- MODAL POPUP PREVIEW BERITA ACARA PENOLAKAN --}}
@include('gudang.qc.partials.common.modal-preview-berita-acara')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/web/qc-admin-index.js') }}?v={{ file_exists(public_path('js/gudang/qc/web/qc-admin-index.js')) ? filemtime(public_path('js/gudang/qc/web/qc-admin-index.js')) : time() }}"></script>
    @if (request('direct_print'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    if (typeof directPrintHaccp === 'function') {
                        directPrintHaccp('{{ request('direct_print') }}');
                    }
                }, 400);
            });
        </script>
    @endif
@endpush
