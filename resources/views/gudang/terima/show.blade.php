@extends('layouts.app')

@section('title', 'Detail Penerimaan Barang ' . $terima->terima_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-show.css') }}?v={{ file_exists(public_path('css/gudang/terima/terima-show.css')) ? filemtime(public_path('css/gudang/terima/terima-show.css')) : time() }}">
@endpush

@section('content')
<div class="po-show-wrapper">

    {{-- PRINT-ONLY HEADER (SLIP RESMI) --}}
    <div class="print-only" style="margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
                <p style="font-size: 0.75rem; color: #475569; margin: 0.2rem 0 0 0;">
                    Pabrik Pengolahan F&B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
                </p>
            </div>
            <div style="text-align: right;">
                <strong style="font-size: 1.1rem; color: #047857; display: block;">BUKTI PENERIMAAN BARANG & SLIP TIMBANG</strong>
                <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $terima->terima_no }}</span>
            </div>
        </div>
    </div>

    {{-- 1. BREADCRUMB (OPERATIONAL NAVIGATION) --}}
    <nav class="po-breadcrumb no-print" aria-label="Breadcrumb">
        <a href="{{ route('gudang.terima.index') }}">Pergudangan</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('gudang.terima.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $terima->terima_no }}</span>
    </nav>

    {{-- 2. HEADER BAR (HARMONIZED WITH PO SHOW) --}}
    @php
        $firstHutangDtl = $terima->hutangDetails->first();
        $hutangHdr = $firstHutangDtl?->header;
        $totalRejectQty = (float) $terima->details->sum('reject_qty');
        $hasQcRejection = $terima->qc_id && ($totalRejectQty > 0 || ($terima->qc && ($terima->qc->status_qc === 'DITOLAK_TOTAL' || $terima->qc->details->sum('qty_reject') > 0)));
        $sumBruto = (float) $terima->details->sum('terima_qty');
        $sumAfkir = (float) $terima->details->sum('reject_qty');
        $sumNetto = max(0, $sumBruto - $sumAfkir);
        $satuanUtama = $terima->details->first()?->barang?->satuanDasar?->satuan_nm ?? 'KG';
    @endphp

    <div class="po-header-bar no-print">
        <div class="po-header-info">
            <div class="po-header-title">
                <a href="{{ route('gudang.terima.index') }}"
                   class="po-back-link"
                   aria-label="Kembali ke daftar penerimaan"
                   title="Kembali ke Indeks Penerimaan">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>

                {{-- macOS Traffic Lights Accent --}}
                <div class="mac-traffic-dots" aria-label="macOS Window Controls">
                    <span class="mac-dot mac-dot-red" title="ERP PT Mirasa"></span>
                    <span class="mac-dot mac-dot-yellow" title="Penerimaan Barang Fisik"></span>
                    <span class="mac-dot mac-dot-green" title="Dokumen GRN Aktif"></span>
                </div>

                <h1>{{ $terima->terima_no }}</h1>

                {{-- Status Badges --}}
                <span class="po-badge po-badge-completed">Stok Masuk Gudang</span>

                @if ($hutangHdr)
                    <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" class="po-badge po-badge-approved" style="text-decoration: none;" title="Buka Dokumen Tagihan Hutang Pembelian">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Ditagihkan: {{ $hutangHdr->hutang_no }}</span>
                    </a>
                @else
                    <span class="po-badge po-badge-draft">Belum Ditagihkan</span>
                @endif

                @if ($terima->po)
                    <a href="{{ route('gudang.po.show', $terima->po_id) }}" class="po-badge po-badge-tax" style="text-decoration: none;" title="Buka Detail PO Terkait">
                        PO: {{ $terima->po->po_no }}
                    </a>
                    @php
                        $hasOverPo = false;
                        $hasUnderPo = false;
                        foreach ($terima->details as $d) {
                            if ($d->podtl_id && $d->poDetail) {
                                $pQty = (float) $d->poDetail->pesan_qty;
                                $nQty = max(0, (float) $d->terima_qty - (float) ($d->reject_qty ?? 0));
                                if ($pQty > 0) {
                                    if ($nQty > $pQty + 0.001) $hasOverPo = true;
                                    elseif ($nQty < $pQty - 0.001) $hasUnderPo = true;
                                }
                            }
                        }
                    @endphp
                    @if ($hasOverPo)
                        <span class="po-badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;" title="Terdapat barang yang diterima melebihi kuantitas PO">
                            Status PO: Over PO (+Delivery)
                        </span>
                    @elseif ($hasUnderPo)
                        <span class="po-badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;" title="Penerimaan belum memenuhi seluruh kuantitas PO (Parsial)">
                            Status PO: Parsial / Belum Lengkap
                        </span>
                    @else
                        <span class="po-badge" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;" title="Kuantitas fisik tepat sesuai kuantitas PO">
                            Status PO: Sesuai PO (100%)
                        </span>
                    @endif
                @else
                    <span class="po-badge po-badge-draft">Non-PO</span>
                @endif

                @if ($terima->qc_id)
                    <a href="{{ route('qc.inbound.show', $terima->qc_id) }}" class="po-badge po-badge-qc" style="text-decoration: none;" title="Buka Tiket Inspeksi QC Inbound">
                        <span>QC Lolos: {{ $terima->qc?->qc_no ?? ('#' . $terima->qc_id) }}</span>
                    </a>
                @endif

                @if ($terima->is_ada_revisi)
                    <span class="po-badge" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;" title="Telah diedit {{ $terima->total_revisi_count }}x (Audit Trail)">
                        REV-{{ $terima->total_revisi_count }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Right: Header Quick Actions --}}
        <div class="po-header-actions">
            <a href="{{ route('gudang.terima.index') }}" class="btn-pill-outline" title="Kembali ke Daftar Penerimaan">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Daftar Penerimaan</span>
            </a>

            <a href="{{ route('gudang.terima.export-pdf', $terima->terima_id) }}" target="_blank" class="btn-pill-outline" title="Cetak Dokumen PDF Bukti Penerimaan Barang">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak PDF (GRN)</span>
            </a>

            @if ($hasQcRejection)
                <button type="button" 
                        onclick="openBeritaAcaraModal('{{ $terima->qc_id }}', '{{ $terima->qc?->qc_no ?? $terima->terima_no }}')" 
                        class="btn-pill-outline" 
                        style="border-color: #fecaca; color: #dc2626; background: #fef2f2;" 
                        title="Buka Berita Acara Penolakan HACCP">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Berita Acara Penolakan</span>
                </button>
            @endif

            {{-- Dropdown Aksi Show --}}
            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, 'dropdown-show-terima')">
                <span>Menu Aksi Dokumen</span>
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div id="dropdown-show-terima" class="action-dropdown-menu">
                <a href="{{ route('gudang.stok.index', ['gudang_id' => $terima->gudang_id]) }}" class="action-dropdown-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Lihat Stok di Gudang Ini</span>
                </a>
                @if ($terima->po_id)
                    <a href="{{ route('gudang.po.show', $terima->po_id) }}" class="action-dropdown-item">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Buka Purchase Order Terkait</span>
                    </a>
                @endif
                @if ($terima->qc_id)
                    <a href="{{ route('qc.inbound.show', $terima->qc_id) }}" class="action-dropdown-item">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Buka Tiket QC Inbound Terkait</span>
                    </a>
                    @if ($hasQcRejection)
                        <button type="button" 
                                onclick="openBeritaAcaraModal('{{ $terima->qc_id }}', '{{ $terima->qc?->qc_no ?? $terima->terima_no }}')" 
                                class="action-dropdown-item warning-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Cetak Berita Acara Penolakan</span>
                        </button>
                    @endif
                @endif
                @if ($hutangHdr)
                    <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" class="action-dropdown-item">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Buka Invoice Keuangan ({{ $hutangHdr->hutang_no }})</span>
                    </a>
                @endif
                @if (Auth::user()?->canEditTerima())
                    @if (isset($stockUsage) && !$stockUsage['can_be_edited'])
                        <button type="button" class="action-dropdown-item" style="color: #64748b;" onclick="openStockLockedModal('edit')" title="Dokumen terkunci: Sebagian/seluruh bahan baku telah digunakan di lini produksi">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Edit Penerimaan (Terkunci)</span>
                        </button>
                    @else
                        <a href="{{ route('gudang.terima.edit', $terima->terima_id) }}" class="action-dropdown-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit Penerimaan Ini</span>
                        </a>
                    @endif
                @endif
                @if (Auth::user()?->canDeleteTerima())
                    <div class="action-dropdown-divider"></div>
                    @if (isset($stockUsage) && !$stockUsage['can_be_deleted'])
                        <button type="button" class="action-dropdown-item danger-item" style="opacity: 0.6;" onclick="openStockLockedModal('delete')" title="Dokumen terkunci: Bahan baku telah digunakan atau tagihan telah dibayar">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Hapus Dokumen (Terkunci)</span>
                        </button>
                    @else
                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteTerimaModal({{ $terima->terima_id }}, '{{ $terima->terima_no }}', '{{ addslashes($terima->supplier?->supplier_nm ?? '') }}')">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus Dokumen Ini</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- 3. 4 KARTU METRIK KPI (MONOCHROMATIC SLATE GRID 4 KOLOM PERSIS PO SHOW) --}}
    <div class="po-metrics-grid no-print">
        {{-- CARD 1: TOTAL NETTO MASUK --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span class="po-metric-label">Total Netto Masuk</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">+ {{ number_format($sumNetto, 2, ',', '.') }} {{ $satuanUtama }}</div>
            <div class="po-metric-footer">Fisik tersimpan di gudang</div>
        </div>

        {{-- CARD 2: TOTAL BRUTO TIMBANG --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </span>
                    <span class="po-metric-label">Total Bruto Timbang</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">{{ number_format($sumBruto, 2, ',', '.') }} {{ $satuanUtama }}</div>
            <div class="po-metric-footer">Timbangan kotor muatan armada</div>
        </div>

        {{-- CARD 3: TOTAL AFKIR / REJECT --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <span class="po-metric-label">Total Afkir / Reject</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value {{ $sumAfkir > 0 ? 'text-danger' : '' }}">
                - {{ number_format($sumAfkir, 2, ',', '.') }} {{ $satuanUtama }}
            </div>
            <div class="po-metric-footer">Ditolak / tidak masuk stok</div>
        </div>

        {{-- CARD 4: STATUS KEUANGAN (AP) --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span class="po-metric-label">Status Keuangan (AP)</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">
                @if ($hutangHdr)
                    <span style="font-size: 1.15rem; color: {{ $hutangHdr->status_pembayaran === 'LUNAS' ? '#047857' : ($hutangHdr->status_pembayaran === 'SEBAGIAN' ? '#b45309' : '#b91c1c') }};">
                        {{ str_replace('_', ' ', $hutangHdr->status_pembayaran) }}
                    </span>
                @else
                    <span style="font-size: 1.15rem; color: #64748b;">BELUM DITAGIH</span>
                @endif
            </div>
            <div class="po-metric-footer">
                {{ $hutangHdr ? 'No. Faktur: ' . $hutangHdr->hutang_no : 'Belum ada faktur AP terbit' }}
            </div>
        </div>
    </div>

    {{-- 4. WORKSPACE GRID (2 COLUMNS: LEFT MAIN 2.3fr, RIGHT SIDEBAR 1fr) --}}
    <div class="po-workspace-grid">

        {{-- LEFT MAIN COLUMN --}}
        <div class="po-main-column">

            {{-- ALERT BANNER STATUS PEMAKAIAN STOK (JIKA STOK TERPAKAI / TERKUNCI) --}}
            @if (isset($stockUsage) && $stockUsage['is_used'])
                <div class="stock-usage-alert-box {{ !$stockUsage['can_be_edited'] ? 'locked' : '' }} no-print">
                    <div class="stock-usage-header">
                        <div class="stock-usage-badge-pill">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>{{ !$stockUsage['can_be_edited'] ? 'DOKUMEN TERKUNCI • STOK TERPAKAI' : 'STATUS STOK • TELAH DIGUNAKAN SEBAGIAN' }}</span>
                        </div>
                        <span class="stock-usage-tag">Proteksi Integritas Stok &amp; HPP</span>
                    </div>
                    <div class="stock-usage-body">
                        <p class="stock-usage-desc">
                            @if (!$stockUsage['can_be_edited'])
                                Sebagian atau seluruh komoditas dari penerimaan ini telah digunakan pada proses produksi atau mutasi keluar gudang. 
                                Dokumen ini <strong>dikunci dari pengeditan atau pembatalan langsung</strong> guna mencegah saldo minus dan menjaga keakuratan laporan mutasi.
                            @else
                                Dokumen ini memiliki riwayat pemakaian parsial di lini produksi, namun saldo sisa di gudang saat ini masih mencukupi untuk penyesuaian.
                            @endif
                        </p>
                        <div class="stock-usage-items-table">
                            <table style="width: 100%; font-size: 0.8125rem; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #cbd5e1; background: #f8fafc; text-align: left; color: #475569;">
                                        <th style="padding: 0.5rem 0.65rem;">Komoditas &amp; Batch</th>
                                        <th style="padding: 0.5rem 0.65rem; text-align: right;">Masuk GRN</th>
                                        <th style="padding: 0.5rem 0.65rem; text-align: right;">Terpakai (OUT)</th>
                                        <th style="padding: 0.5rem 0.65rem; text-align: right;">Sisa di Gudang</th>
                                        <th style="padding: 0.5rem 0.65rem; text-align: center;">Status Kunci</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockUsage['items'] as $uItem)
                                        @if ($uItem['used_qty'] > 0 || $uItem['is_locked'])
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.5rem 0.65rem;">
                                                    <div style="font-weight: 600; color: #0f172a;">{{ $uItem['barang_nm'] }}</div>
                                                    <div style="font-family: var(--po-font-mono); font-size: 0.725rem; color: #64748b;">
                                                        Batch: {{ $uItem['batch_no'] }} &bull; Grade: {{ $uItem['grade_cd'] }}
                                                    </div>
                                                </td>
                                                <td style="padding: 0.5rem 0.65rem; text-align: right; font-family: var(--po-font-mono); color: #0f172a;">
                                                    {{ number_format($uItem['terima_netto'], 2, ',', '.') }} {{ $uItem['satuan_nm'] }}
                                                </td>
                                                <td style="padding: 0.5rem 0.65rem; text-align: right; font-family: var(--po-font-mono); font-weight: 700; color: #dc2626;">
                                                    - {{ number_format($uItem['used_qty'], 2, ',', '.') }} {{ $uItem['satuan_nm'] }}
                                                </td>
                                                <td style="padding: 0.5rem 0.65rem; text-align: right; font-family: var(--po-font-mono); font-weight: 700; color: #0f172a;">
                                                    {{ number_format($uItem['sisa_qty'], 2, ',', '.') }} {{ $uItem['satuan_nm'] }}
                                                </td>
                                                <td style="padding: 0.5rem 0.65rem; text-align: center;">
                                                    @if ($uItem['is_locked'])
                                                        <span style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 4px; font-size: 0.6875rem; font-weight: 700;" title="Stok pada batch ini sudah terpakai">Terkunci</span>
                                                    @else
                                                        <span style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; padding: 2px 7px; border-radius: 4px; font-size: 0.6875rem;">Sisa Cukup</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="stock-usage-footer-action">
                            <span>SOP Rekonsiliasi: Apabila terdapat selisih fisik riil, gunakan menu <strong>Penyesuaian Stok (Stock Adjustment)</strong>.</span>
                            <a href="{{ route('gudang.adjustment.create') }}" class="btn-adjustment-link">
                                <span>Buka Form Penyesuaian Stok &rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ALERT BANNER AP INVOICE (JIKA ADA DOKUMEN HUTANG) --}}
            @if ($hutangHdr)
                @php
                    $grnDiskonTotal = (float) $terima->details->sum(function($d) {
                        $sub = (float)$d->terima_qty * (float)$d->harga_nominal;
                        if ((float)$d->diskon_persen > 0) return round($sub * ((float)$d->diskon_persen / 100), 2);
                        return (float)$d->terima_qty * (float)($d->diskon_nominal ?? 0);
                    });
                    $grnPotonganTotal = (float) $terima->details->sum('potongan_nominal');

                    $apDiskonTotal = (float) $terima->hutangDetails->sum('diskon_nominal');
                    $apPotonganTotal = (float) $terima->hutangDetails->sum('potongan_nominal');

                    $selisihDiskon = round($apDiskonTotal - $grnDiskonTotal, 2);
                    $selisihPotongan = round($apPotonganTotal - $grnPotonganTotal, 2);
                    $adaPenyesuaian = (abs($selisihDiskon) > 0.01 || abs($selisihPotongan) > 0.01);
                @endphp

                <div class="po-alert-banner {{ $adaPenyesuaian ? 'po-alert-warning' : 'po-alert-info' }} no-print">
                    <div class="po-alert-left">
                        <div class="po-alert-icon-box">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="po-alert-body">
                            <h4 class="po-alert-title">
                                {{ $adaPenyesuaian ? 'Penyesuaian & Rekonsiliasi Tagihan Keuangan' : 'Dokumen Tagihan Hutang Pembelian (AP Invoice Terbit)' }}
                            </h4>
                            <p class="po-alert-desc">
                                Terkait Invoice AP: <strong>{{ $hutangHdr->hutang_no }}</strong>
                                @if ($hutangHdr->no_faktur_supplier)
                                    &bull; Faktur Supplier: <strong>{{ $hutangHdr->no_faktur_supplier }}</strong>
                                @endif
                                &bull; Tgl Tagihan: {{ \Carbon\Carbon::parse($hutangHdr->tgl_tagihan)->format('d/m/Y') }}
                                &bull; Status: <strong>{{ str_replace('_', ' ', $hutangHdr->status_pembayaran) }}</strong>
                            </p>
                            @if ($adaPenyesuaian)
                                <ul class="po-alert-list">
                                    @if (abs($selisihDiskon) > 0.01)
                                        <li>
                                            <strong>Diskon Faktur:</strong> Di AP dicatat <strong>Rp {{ number_format($apDiskonTotal, 0, ',', '.') }}</strong> 
                                            (GRN awal: Rp {{ number_format($grnDiskonTotal, 0, ',', '.') }} &bull; 
                                            <span style="font-weight: 700;">
                                                {{ $selisihDiskon > 0 ? '+ Tambahan Diskon Rp ' . number_format($selisihDiskon, 0, ',', '.') : '- Pengurangan Diskon Rp ' . number_format(abs($selisihDiskon), 0, ',', '.') }}
                                            </span>)
                                        </li>
                                    @endif
                                    @if (abs($selisihPotongan) > 0.01)
                                        <li>
                                            <strong>Potongan Mutu:</strong> Di AP dicatat <strong>Rp {{ number_format($apPotonganTotal, 0, ',', '.') }}</strong> 
                                            (GRN awal: Rp {{ number_format($grnPotonganTotal, 0, ',', '.') }} &bull; 
                                            <span style="font-weight: 700;">
                                                {{ $selisihPotongan > 0 ? '+ Penyesuaian Potongan Rp ' . number_format($selisihPotongan, 0, ',', '.') : '- Pengurangan Potongan Rp ' . number_format(abs($selisihPotongan), 0, ',', '.') }}
                                            </span>)
                                        </li>
                                    @endif
                                </ul>
                            @endif
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" class="btn-pill-outline" style="font-size: 0.8rem;">
                            <span>Buka Invoice Keuangan &rarr;</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- ALERT BANNER PENOLAKAN BAHAN BAKU --}}
            @if ($hasQcRejection)
                @php
                    $displayReject = $totalRejectQty > 0 ? $totalRejectQty : (float) $terima->qc->details->sum('qty_reject');
                @endphp
                <div class="po-alert-banner po-alert-danger no-print">
                    <div class="po-alert-left">
                        <div class="po-alert-icon-box">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="po-alert-body">
                            <h4 class="po-alert-title">
                                Penolakan Muatan Bahan Baku ({{ number_format($displayReject, 2, ',', '.') }} {{ $satuanUtama }} Ditolak)
                            </h4>
                            <p class="po-alert-desc">
                                Kuantitas yang ditolak (Grade B / cacat mutu) tidak masuk stok gudang dan tidak memotong tagihan PO. Berita Acara Penolakan HACCP resmi telah diterbitkan.
                            </p>
                        </div>
                    </div>
                    @if ($terima->qc_id)
                        <div>
                            <button type="button" 
                                    onclick="openBeritaAcaraModal('{{ $terima->qc_id }}', '{{ $terima->qc?->qc_no ?? $terima->terima_no }}')" 
                                    class="btn-pill-dark" 
                                    style="background: #dc2626; border-color: #dc2626; color: #ffffff;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Buka Berita Acara Penolakan &rarr;</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- KARTU UTAMA: RINCIAN BARANG & ALOKASI NOMOR BATCH --}}
            <div class="po-card">
                <div class="po-card-header">
                    <h3 class="po-card-title">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Rincian Barang &amp; Alokasi Nomor Batch</span>
                        <span class="po-card-subtitle">({{ $terima->details->count() }} Jenis Komoditas)</span>
                    </h3>
                </div>

                <div class="po-table-responsive">
                    <table class="po-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="min-width: 170px;">Komoditas &amp; Kode</th>
                                <th style="min-width: 130px;">Batch / Lot</th>
                                <th style="min-width: 95px; text-align: center;">Expired</th>
                                <th style="min-width: 90px; text-align: center;">Grade</th>
                                <th style="min-width: 130px; text-align: right;">Qty Netto Masuk</th>
                                <th style="min-width: 70px; text-align: center;">Satuan</th>
                                <th style="width: 90px; text-align: center;" class="no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($terima->details as $index => $item)
                                @php
                                    $hargaAwal = (float) ($item->harga_nominal ?? 0);
                                    $itemNetto = max(0, (float) $item->terima_qty - (float) ($item->reject_qty ?? 0));
                                    $showGrade = strtoupper(trim((string)$item->grade_cd));
                                    $showIsNonGrade = in_array($showGrade, ['NON-GRADE', 'NON_GRADE', 'NON GRADE']) || ($item->barang && !$item->barang->hasGrade());
                                @endphp
                                <tr>
                                    <td style="text-align: center; color: var(--po-slate-500); font-weight: 600;">
                                        {{ $index + 1 }}
                                    </td>
                                    <td>
                                        <strong style="color: var(--po-slate-900); font-size: 0.85rem; display: block;">
                                            {{ $item->barang?->barang_nm ?? '-' }}
                                        </strong>
                                        <span style="font-family: var(--po-font-mono); font-size: 0.725rem; color: var(--po-slate-500); margin-top: 1px; display: block;">
                                            {{ $item->barang?->barang_cd ?? '-' }}
                                        </span>
                                        @if ($item->podtl_id && $item->poDetail)
                                            @php
                                                $poPesan = (float) $item->poDetail->pesan_qty;
                                                $diffPo = $itemNetto - $poPesan;
                                                $satuanNm = $item->barang?->satuanDasar?->satuan_nm ?? '';
                                            @endphp
                                            @if ($poPesan > 0)
                                                <div style="margin-top: 4px;">
                                                    @if ($diffPo > 0.001)
                                                        <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Kuantitas fisik diterima melebihi pesanan PO">
                                                            Over PO (+{{ number_format($diffPo, 2, ',', '.') }} {{ $satuanNm }}) &bull; PO: {{ number_format($poPesan, 2, ',', '.') }}
                                                        </span>
                                                    @elseif ($diffPo < -0.001)
                                                        <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Penerimaan belum memenuhi seluruh pesanan PO (Parsial)">
                                                            Kurang / Parsial (-{{ number_format(abs($diffPo), 2, ',', '.') }} {{ $satuanNm }}) &bull; PO: {{ number_format($poPesan, 2, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="badge" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Kuantitas fisik tepat sesuai pesanan PO">
                                                            Sesuai PO (100%) &bull; PO: {{ number_format($poPesan, 2, ',', '.') }} {{ $satuanNm }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <span class="terima-batch-capsule">
                                            {{ $item->batch_no }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        @if ($item->expired_tgl)
                                            <span style="color: var(--po-slate-800); font-weight: 600; font-size: 0.8125rem;">
                                                {{ \Carbon\Carbon::parse($item->expired_tgl)->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span style="color: var(--po-slate-400);">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @if ($showIsNonGrade)
                                            <span class="grade-pill-show grade-none">Non-Grade</span>
                                        @elseif ($showGrade === 'B')
                                            <span class="grade-pill-show grade-b">Grade B</span>
                                        @elseif ($showGrade === 'REJECT')
                                            <span class="grade-pill-show grade-reject">Afkir</span>
                                        @elseif ($showGrade)
                                            <span class="grade-pill-show grade-a">Grade {{ $showGrade }}</span>
                                        @else
                                            <span class="grade-pill-show grade-none">Non-Grade</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="font-weight: 700; font-size: 0.9375rem; color: var(--po-slate-900); font-family: var(--po-font-mono);">
                                            + {{ number_format($itemNetto, 2, ',', '.') }}
                                        </div>
                                        @if ((float) $item->reject_qty > 0)
                                            <div style="font-size: 0.7rem; color: var(--po-rose); font-weight: 700; margin-top: 2px;">
                                                Afkir: {{ number_format((float) $item->reject_qty, 2, ',', '.') }} (Bruto: {{ number_format((float) $item->terima_qty, 2, ',', '.') }})
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: center; color: var(--po-slate-600); font-size: 0.8rem;">
                                        {{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}
                                    </td>
                                    <td style="text-align: center;" class="no-print">
                                        <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $terima->gudang_id]) }}" 
                                           class="btn-pill-outline" 
                                           style="padding: 0.25rem 0.55rem; font-size: 0.75rem;" 
                                           title="Buka Kartu Stok Buku Besar">
                                            <span>Kartu Stok &rarr;</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ELEGANT SUMMARY FOOTER BAR --}}
                <div class="po-summary-bar">
                    <div class="po-summary-note">
                        Pencatatan fisik barang masuk gudang. Faktur tagihan hutang dan rekonsiliasi pembayaran dikelola oleh modul <strong>Keuangan (AP)</strong>.
                    </div>
                    <div class="po-summary-metrics">
                        <div class="po-summary-metric-box">
                            <span class="po-summary-metric-label">Total Qty Bruto</span>
                            <span class="po-summary-metric-val">{{ number_format($sumBruto, 2, ',', '.') }} {{ $satuanUtama }}</span>
                        </div>
                        @if ($sumAfkir > 0)
                            <div class="po-summary-metric-box danger-box">
                                <span class="po-summary-metric-label">Total Afkir / Reject</span>
                                <span class="po-summary-metric-val">- {{ number_format($sumAfkir, 2, ',', '.') }} {{ $satuanUtama }}</span>
                            </div>
                        @endif
                        <div class="po-summary-metric-box primary-box">
                            <span class="po-summary-metric-label">Total Netto Masuk Gudang</span>
                            <span class="po-summary-metric-val">+ {{ number_format($sumNetto, 2, ',', '.') }} {{ $satuanUtama }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU TANDA TANGAN & OTORISASI SLIP TIMBANG --}}
            <div class="po-card">
                <div class="po-card-header">
                    <h3 class="po-card-title">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Otorisasi &amp; Verifikasi Slip Timbang Fisik</span>
                    </h3>
                </div>
                <div class="slip-signatures-container">
                    <div class="slip-sign-box">
                        <span class="slip-sign-role">Diserahkan Oleh:</span>
                        <strong class="slip-sign-entity">Sopir / Petani Pemasok</strong>
                        <div class="slip-sign-space"></div>
                        <div class="slip-sign-line"></div>
                        <span class="slip-sign-meta">Nama &amp; Tanggal</span>
                    </div>

                    <div class="slip-sign-box">
                        <span class="slip-sign-role">Diterima &amp; Ditimbang:</span>
                        <strong class="slip-sign-entity">Petugas Timbang Gudang</strong>
                        <div class="slip-sign-space"></div>
                        <div class="slip-sign-line"></div>
                        <span class="slip-sign-meta">Nama &amp; Tanggal</span>
                    </div>

                    <div class="slip-sign-box">
                        <span class="slip-sign-role">Diperiksa &amp; Disetujui:</span>
                        <strong class="slip-sign-entity">QC / Kepala Gudang</strong>
                        <div class="slip-sign-space"></div>
                        <div class="slip-sign-line"></div>
                        <span class="slip-sign-meta">Nama &amp; Tanda Tangan</span>
                    </div>
                </div>
            </div>

            {{-- KARTU RIWAYAT PERUBAHAN & AUDIT TRAIL DOKUMEN PENERIMAAN --}}
            <div class="po-card no-print">
                <div class="po-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 class="po-card-title">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Riwayat Perubahan &amp; Audit Trail</span>
                        <span class="po-card-subtitle">({{ $terima->revisiLog->count() }} Catatan Revisi)</span>
                    </h3>

                    @if ($terima->is_ada_revisi)
                        <div style="font-size: 0.725rem; color: #64748b; display: flex; align-items: center; gap: 0.45rem;">
                            <span>Terakhir diedit oleh:</span>
                            <strong style="color: #0f172a;">{{ $terima->terakhir_direvisi_by ?? 'Petugas Gudang' }}</strong>
                            <span>({{ $terima->terakhir_direvisi_at ? $terima->terakhir_direvisi_at->format('d/m/Y H:i') : '-' }} WIB)</span>
                        </div>
                    @endif
                </div>

                @if ($terima->revisiLog->isNotEmpty())
                    <div class="po-table-responsive">
                        <table class="po-table">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Versi &amp; Waktu</th>
                                    <th style="width: 170px;">Diedit Oleh</th>
                                    <th style="width: 150px;">Kategori</th>
                                    <th>Rincian Perubahan (Sebelum &rarr; Sesudah)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($terima->revisiLog as $log)
                                    @php
                                        $tglRev = $log->revisi_tgl ? \Carbon\Carbon::parse($log->revisi_tgl) : null;
                                        $rawDiffs = $log->perubahan_json ?? [];
                                        $diffs = is_array($rawDiffs) ? array_filter($rawDiffs, function($df) {
                                            $f = strtolower($df['field'] ?? '');
                                            $l = strtolower($df['label'] ?? '');
                                            return !str_contains($f, 'tagihan') && !str_contains($f, 'harga') && !str_contains($l, 'tagihan') && !str_contains($l, 'harga');
                                        }) : [];
                                    @endphp
                                    <tr>
                                        <td style="vertical-align: top;">
                                            <span class="po-badge po-badge-approved" style="font-family: monospace; font-weight: 700;">{{ $log->revisi_no ?: 'REV' }}</span>
                                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 3px;">
                                                {{ $tglRev ? $tglRev->format('d/m/Y H:i') : '-' }} WIB
                                            </div>
                                        </td>
                                        <td style="vertical-align: top;">
                                            <strong style="color: var(--po-slate-900);">{{ $log->user_nama ?: 'SYSTEM' }}</strong>
                                            <div style="font-size: 0.7rem; color: #64748b;">{{ $log->user_role ?: 'Operator Gudang' }}</div>
                                        </td>
                                        <td style="vertical-align: top;">
                                            <span class="po-badge po-badge-draft" style="font-size: 0.68rem;">
                                                {{ str_replace('_', ' ', $log->kategori_perubahan ?: 'KOMPREHENSIF') }}
                                            </span>
                                        </td>
                                        <td style="vertical-align: top;">
                                            @if (!empty($log->alasan_edit))
                                                <div style="font-size: 0.75rem; color: #64748b; font-style: italic; margin-bottom: 6px;">
                                                    Alasan: &ldquo;{{ $log->alasan_edit }}&rdquo;
                                                </div>
                                            @endif

                                            @if (!empty($diffs) && is_array($diffs))
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    @foreach ($diffs as $df)
                                                        <div style="font-size: 0.775rem; color: #1e293b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 8px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                                                            <strong style="color: #475569;">{{ $df['label'] ?? ($df['field'] ?? 'Field') }}:</strong>
                                                            <div style="font-family: monospace; font-size: 0.75rem;">
                                                                <span style="color: #dc2626; text-decoration: line-through; background: #fef2f2; padding: 1px 4px; border-radius: 4px;">{{ $df['sebelum'] ?? '-' }}</span>
                                                                <span style="color: #64748b; margin: 0 4px;">&rarr;</span>
                                                                <span style="color: #15803d; font-weight: 700; background: #f0fdf4; padding: 1px 4px; border-radius: 4px;">{{ $df['sesudah'] ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span style="font-size: 0.775rem; color: #64748b;">{{ $log->ringkasan_perubahan ?: '-' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 2.25rem 1rem; color: #94a3b8;">
                        <div style="width: 38px; height: 38px; margin: 0 auto 0.65rem; border-radius: 50%; background: #f8fafc; border: 1.5px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <strong style="color: #64748b; font-size: 0.875rem; display: block;">Dokumen Masuk Gudang Versi Orisinal (Belum pernah mengalami revisi)</strong>
                        <p style="font-size: 0.775rem; margin-top: 0.2rem; margin-bottom: 0;">
                            Penerimaan barang ini belum pernah mengalami perubahan data sejak pertama kali dicatat masuk ke gudang.
                        </p>
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT SIDEBAR INSPECTOR (STICKY) --}}
        <div class="po-side-column no-print">

            {{-- KARTU INFORMASI DOKUMEN & PENGIRIM --}}
            <div class="po-card">
                <div class="po-card-header">
                    <h4 class="po-card-title">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Informasi Supplier &amp; Gudang</span>
                    </h4>
                </div>
                <div class="po-card-body" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <span class="inspector-entity-sub">Mitra Supplier Pengirim</span>
                        <strong class="inspector-entity-title">{{ $terima->supplier?->supplier_nm ?? '-' }}</strong>
                        <div class="inspector-entity-desc">
                            {{ $terima->supplier?->alamat_txt ?? '-' }}<br>
                            Kontak: {{ $terima->supplier?->kontak_no ?? '-' }}
                        </div>
                    </div>
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.85rem;">
                        <span class="inspector-entity-sub">Gudang Penyimpanan Fisik</span>
                        <strong class="inspector-entity-title">{{ $terima->gudang?->gudang_nm ?? '-' }}</strong>
                        <div class="inspector-entity-desc">
                            Kode Gudang: <span style="font-family: var(--po-font-mono); font-weight: 700;">{{ $terima->gudang?->gudang_cd ?? '-' }}</span><br>
                            Lokasi: {{ $terima->gudang?->alamat_txt ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU REFERENSI DOKUMEN & LOGISTIK --}}
            <div class="po-card">
                <div class="po-card-header">
                    <h4 class="po-card-title">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Referensi Dokumen Logistik</span>
                    </h4>
                </div>
                <div class="po-card-body">
                    <div class="inspector-kv-row">
                        <span class="inspector-kv-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Tanggal Masuk</span>
                        </span>
                        <span class="inspector-kv-val">{{ \Carbon\Carbon::parse($terima->terima_tgl)->format('d F Y') }}</span>
                    </div>

                    <div class="inspector-kv-row">
                        <span class="inspector-kv-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>Surat Jalan</span>
                        </span>
                        <span class="inspector-kv-val" style="font-family: var(--po-font-mono);">{{ $terima->suratjalan_no ?: '-' }}</span>
                    </div>

                    <div class="inspector-kv-row">
                        <span class="inspector-kv-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Referensi PO</span>
                        </span>
                        <span class="inspector-kv-val">
                            @if ($terima->po)
                                <a href="{{ route('gudang.po.show', $terima->po_id) }}" class="inspector-link-sky">
                                    {{ $terima->po->po_no }} &rarr;
                                </a>
                            @else
                                <span style="color: var(--po-slate-400); font-style: italic;">Non-PO</span>
                            @endif
                        </span>
                    </div>

                    @if ($terima->qc_id)
                        <div class="inspector-kv-row">
                            <span class="inspector-kv-label">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Tiket QC Inbound</span>
                            </span>
                            <span class="inspector-kv-val">
                                <a href="{{ route('qc.inbound.show', $terima->qc_id) }}" class="inspector-link-purple">
                                    {{ $terima->qc?->qc_no ?? ('#' . $terima->qc_id) }} &rarr;
                                </a>
                            </span>
                        </div>
                    @endif

                    <div class="inspector-kv-row">
                        <span class="inspector-kv-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Status Dokumen</span>
                        </span>
                        <span class="inspector-kv-val">
                            @if ($terima->is_ada_revisi)
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.725rem; font-weight: 700; border: 1px solid #cbd5e1; padding: 2px 6px; border-radius: 4px;">
                                    Revisi {{ $terima->total_revisi_count }}x
                                </span>
                            @else
                                <span class="badge" style="background: #f0fdf4; color: #166534; font-size: 0.725rem; font-weight: 700; border: 1px solid #bbf7d0; padding: 2px 6px; border-radius: 4px;">
                                    Versi Orisinal
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- KARTU CATATAN FISIK PENERIMAAN (JIKA ADA) --}}
            @if ($terima->catatan_txt)
                <div class="po-card">
                    <div class="po-card-header">
                        <h4 class="po-card-title">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                            <span>Catatan Fisik Penerimaan</span>
                        </h4>
                    </div>
                    <div class="po-card-body">
                        <p style="margin: 0; font-size: 0.825rem; color: var(--po-slate-700); line-height: 1.5;">
                            {{ $terima->catatan_txt }}
                        </p>
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>

@include('gudang.terima.partials.modal-delete-confirm')
@include('gudang.terima.partials.modal-stock-locked')
@include('gudang.qc.partials.common.modal-preview-berita-acara')

@push('scripts')
<script src="{{ asset('js/gudang/terima/terima-show.js') }}?v={{ file_exists(public_path('js/gudang/terima/terima-show.js')) ? filemtime(public_path('js/gudang/terima/terima-show.js')) : time() }}"></script>
@endpush
@endsection
