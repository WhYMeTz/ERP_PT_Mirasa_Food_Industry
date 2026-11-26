@extends('layouts.app')

@section('title', 'Penerimaan Barang (GRN) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-index.css') }}?v={{ file_exists(public_path('css/gudang/terima/terima-index.css')) ? filemtime(public_path('css/gudang/terima/terima-index.css')) : time() }}">
@endpush

@section('content')
<div class="terima-page-wrapper">
    {{-- 1. TOP HEADER (STANDAR PURCHASE ORDER ENTERPRISE) --}}
    <div class="terima-top-header">
        <div>
            <div class="terima-header-kicker">LOGISTIK &amp; INBOUND WAREHOUSE</div>
            <h1 class="terima-header-title">Penerimaan Barang (GRN)</h1>
            <p class="terima-header-subtitle">
                Pencatatan fisik bongkar muat barang masuk ke gudang, nomor batch, dan kedaluwarsa (Inbound FIFO).
            </p>
        </div>
        <div class="terima-header-actions">
            <a href="{{ route('gudang.terima.export-rekap-pdf', request()->query()) }}" target="_blank" class="btn-pill-outline" title="Buka &amp; Cetak Laporan Rekapitulasi Dokumen PDF Resmi">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Cetak Rekap PDF</span>
            </a>
            <a href="{{ route('gudang.terima.export-excel', request()->query()) }}" class="btn-pill-outline" title="Download Rekap Barang Masuk format Excel (.xlsx)">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>
            @if (Auth::user()?->canCreateTerima())
                <button type="button" onclick="document.getElementById('modal-import-excel').style.display='flex'" class="btn-pill-outline" title="Import data GRN dari file Excel">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Excel</span>
                </button>
                <a href="{{ route('gudang.terima.create') }}" class="btn-pill-dark">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Catat Penerimaan Barang</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 2. 4 KARTU METRIK OPERASIONAL (KPI CARDS MONOKROMATIK SESUAI PO) --}}
    <div class="terima-metrics-grid">
        {{-- CARD 1: SEMUA PENERIMAAN --}}
        <div class="terima-metric-card">
            <div class="terima-metric-top">
                <div class="terima-metric-header-left">
                    <span class="terima-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span class="terima-metric-label">Semua Dokumen GRN</span>
                </div>
                <span class="terima-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="terima-metric-value">{{ number_format($kpiCounts['total'] ?? 0, 0, ',', '.') }}</div>
            <div class="terima-metric-footer">Total fisik bongkar muat tercatat</div>
        </div>

        {{-- CARD 2: PENERIMAAN HARI INI --}}
        <div class="terima-metric-card">
            <div class="terima-metric-top">
                <div class="terima-metric-header-left">
                    <span class="terima-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="terima-metric-label">Penerimaan Hari Ini</span>
                </div>
                <span class="terima-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="terima-metric-value">{{ number_format($kpiCounts['today'] ?? 0, 0, ',', '.') }}</div>
            <div class="terima-metric-footer">Armada / truk tiba hari ini</div>
        </div>

        {{-- CARD 3: VIA PO RESMI --}}
        <div class="terima-metric-card">
            <div class="terima-metric-top">
                <div class="terima-metric-header-left">
                    <span class="terima-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="terima-metric-label">Penerimaan via PO</span>
                </div>
                <span class="terima-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="terima-metric-value">{{ number_format($kpiCounts['po'] ?? 0, 0, ',', '.') }}</div>
            <div class="terima-metric-footer">Realisasi pesanan resmi pabrik</div>
        </div>

        {{-- CARD 4: NON-PO / LANGSUNG --}}
        <div class="terima-metric-card">
            <div class="terima-metric-top">
                <div class="terima-metric-header-left">
                    <span class="terima-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span class="terima-metric-label">Penerimaan Non-PO</span>
                </div>
                <span class="terima-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="terima-metric-value">{{ number_format($kpiCounts['direct'] ?? 0, 0, ',', '.') }}</div>
            <div class="terima-metric-footer">Pembelian langsung / darurat</div>
        </div>
    </div>

    {{-- 3. MAIN CARD & CONTROL BAR --}}
    <div class="terima-main-card">
        <div class="terima-control-bar">
            {{-- Row 1: View Switcher Tabs & Total Counter --}}
            <div class="terima-tabs-row">
                <div class="terima-switcher-pills">
                    <a href="{{ route('gudang.terima.index', array_merge(request()->query(), ['view' => 'header'])) }}" 
                       class="terima-switcher-tab {{ $viewType === 'header' ? 'active' : '' }}">
                        Daftar Dokumen GRN
                    </a>
                    <a href="{{ route('gudang.terima.index', array_merge(request()->query(), ['view' => 'item'])) }}" 
                       class="terima-switcher-tab {{ $viewType === 'item' ? 'active' : '' }}">
                        Buku Rekap Bahan (Per Item)
                    </a>
                </div>

                <div class="terima-total-badge">
                    Total: <strong>{{ number_format($dataList->total(), 0, ',', '.') }}</strong> {{ $viewType === 'item' ? 'baris item' : 'dokumen' }}
                </div>
            </div>

            {{-- Row 2: Search & Filter Form --}}
            <form action="{{ route('gudang.terima.index') }}" method="GET" class="terima-filter-form">
                <input type="hidden" name="view" value="{{ $viewType }}">
                <input type="hidden" name="per_page" value="{{ $perPage ?? 20 }}">
                
                <div class="terima-search-wrapper">
                    <span class="terima-search-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari No GRN, SJ, supplier, batch, atau bahan..." class="terima-search-input">
                </div>

                <select name="gudang_id" class="terima-select-control" onchange="this.form.submit()">
                    <option value="">-- Semua Gudang --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->display_name }}
                        </option>
                    @endforeach
                </select>

                <label class="filter-reject-label" title="Tampilkan hanya penerimaan dengan barang reject">
                    <input type="checkbox" name="has_reject" value="1" {{ !empty($hasReject) ? 'checked' : '' }} onchange="this.form.submit()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Ada Reject</span>
                </label>

                <button type="submit" class="btn-terima-filter">Filter</button>
                @if(!empty($search) || !empty($gudangId) || !empty($hasReject))
                    <a href="{{ route('gudang.terima.index', ['view' => $viewType, 'per_page' => $perPage ?? 20]) }}" class="btn-terima-reset">Reset</a>
                @endif
            </form>
        </div>

        @if ($viewType === 'item')
            {{-- VIEW 1: BUKU REKAP BAHAN MASUK (PER ITEM) --}}
            <div style="overflow-x: auto;">
                <table class="terima-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="min-width: 140px;">Tgl &amp; No. GRN</th>
                            <th style="min-width: 170px;">Supplier &amp; PO</th>
                            <th style="min-width: 190px;">Bahan Masuk</th>
                            <th style="min-width: 140px;">No. Batch &amp; Exp</th>
                            <th style="min-width: 110px; text-align: right;">Qty Masuk</th>
                            <th style="min-width: 130px;">Gudang Simpan</th>
                            <th style="min-width: 120px; text-align: right;">@Harga Satuan</th>
                            <th style="width: 100px; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataList as $index => $row)
                            @php
                                $subtotal = (float) ($row->subtotal_netto ?: ((float) $row->terima_qty * (float) $row->harga_nominal));
                                $dtlReject = (float) ($row->reject_qty ?? 0);
                                $qcDtlReject = 0;
                                if ($row->header?->qc) {
                                    $matchedQcDtl = $row->header->qc->details->firstWhere('barang_id', $row->barang_id);
                                    $qcDtlReject = (float) ($matchedQcDtl?->qty_reject ?? 0);
                                }
                                $totalItemReject = $dtlReject > 0 ? $dtlReject : $qcDtlReject;
                                $satuan = $row->barang?->satuanDasar?->satuan_nm ?? '-';
                                $qcId = $row->header?->qc_id;
                                $qcNo = $row->header?->qc?->qc_no ?? $row->header?->terima_no;
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                    {{ $dataList->firstItem() + $index }}
                                </td>
                                <td>
                                    <div class="terima-date-text">
                                        {{ \Carbon\Carbon::parse($row->header?->terima_tgl)->format('d/m/Y') }}
                                    </div>
                                    <a href="{{ route('gudang.terima.show', $row->header?->terima_id) }}" class="terima-link-no">
                                        {{ $row->header?->terima_no ?? '-' }}
                                    </a>
                                </td>
                                <td>
                                    <div class="terima-supplier-name">
                                        {{ $row->header?->supplier?->supplier_nm ?? '-' }}
                                    </div>
                                    @if ($row->header?->po)
                                        <a href="{{ route('gudang.po.show', $row->header->po_id) }}" class="terima-po-capsule" style="margin-top: 0.2rem;" title="Buka Detail PO">
                                            PO: {{ $row->header->po->po_no }}
                                        </a>
                                    @else
                                        <span class="terima-non-po">Non-PO</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                        <span class="terima-item-name">
                                            {{ $row->barang?->barang_nm ?? '-' }}
                                        </span>
                                        @php
                                            $rowGrade = strtoupper(trim((string)$row->grade_cd));
                                            $isNonGrade = in_array($rowGrade, ['NON-GRADE', 'NON_GRADE', 'NON GRADE']) || ($row->barang && !$row->barang->hasGrade());
                                        @endphp
                                        @if ($isNonGrade)
                                            <span class="terima-grade-pill terima-grade-none">Non-Grade</span>
                                        @elseif ($rowGrade === 'B')
                                            <span class="terima-grade-pill terima-grade-b">Grade B</span>
                                        @elseif ($rowGrade === 'REJECT')
                                            <span class="terima-grade-pill terima-grade-reject">Afkir</span>
                                        @elseif ($rowGrade)
                                            <span class="terima-grade-pill terima-grade-a">Grade {{ $rowGrade }}</span>
                                        @endif
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                        <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.725rem; color: #64748b;">
                                            {{ $row->barang?->barang_cd ?? '-' }}
                                        </span>
                                        <span style="background: #f1f5f9; color: #475569; font-size: 0.6875rem; padding: 1px 5px; border-radius: 4px;">
                                            {{ $row->barang?->jenisBarang?->jenis_barang_nm ?? ($row->barang?->jenisBarang?->jenis_barang_cd ?? '-') }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.8125rem; background: #f8fafc; color: #1e293b; border: 1px solid #e2e8f0; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; display: inline-block;">
                                        {{ $row->batch_no }}
                                    </span>
                                    @if ($row->expired_tgl)
                                        <small style="display: block; font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">
                                            Exp: {{ \Carbon\Carbon::parse($row->expired_tgl)->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <span style="font-weight: 700; color: #0f172a; font-size: 0.9375rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                        {{ number_format((float) $row->terima_qty, 2, ',', '.') }}
                                    </span>
                                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.2rem;">
                                        {{ $satuan }}
                                    </span>
                                    @if ($totalItemReject > 0)
                                        <div style="margin-top: 0.25rem;">
                                            @if ($qcId)
                                                <button type="button" 
                                                        onclick="openBeritaAcaraModal('{{ $qcId }}', '{{ $qcNo }}')"
                                                        class="badge-reject-btn"
                                                        title="Buka Berita Acara Penolakan">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>Reject: {{ number_format($totalItemReject, 2, ',', '.') }} {{ $satuan }}</span>
                                                </button>
                                            @else
                                                <span class="badge-reject-tag" title="Ada penolakan barang">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>Reject: {{ number_format($totalItemReject, 2, ',', '.') }} {{ $satuan }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: #334155;">
                                        {{ $row->header?->gudang?->gudang_nm ?? '-' }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;">
                                        Rp {{ number_format((float) ($row->harga_netto ?: $row->harga_nominal), 0, ',', '.') }}
                                    </div>
                                    <small style="color: #64748b; font-size: 0.725rem;">
                                        / {{ $satuan }}
                                    </small>
                                </td>
                                <td style="text-align: right; position: relative;">
                                    <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, 'dropdown-item-{{ $row->terimadtl_id }}')">
                                        <span>Aksi</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div id="dropdown-item-{{ $row->terimadtl_id }}" class="action-dropdown-menu">
                                        <a href="{{ route('gudang.terima.show', $row->header?->terima_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Surat Jalan / GRN</span>
                                        </a>
                                        <a href="{{ route('gudang.terima.export-pdf', $row->header?->terima_id) }}" target="_blank" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span>Cetak PDF (GRN)</span>
                                        </a>
                                        @if ($totalItemReject > 0 && $qcId)
                                            <button type="button" class="action-dropdown-item warning-item" onclick="openBeritaAcaraModal('{{ $qcId }}', '{{ $qcNo }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span style="font-weight: 700; color: #dc2626;">Berita Acara Penolakan</span>
                                            </button>
                                        @endif
                                        @if ($row->header?->po_id)
                                            <a href="{{ route('gudang.po.show', $row->header->po_id) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                <span>Lihat PO Terkait</span>
                                            </a>
                                        @endif
                                        @if ($row->header?->gudang_id)
                                            <a href="{{ route('gudang.stok.index', ['gudang_id' => $row->header->gudang_id, 'search' => $row->batch_no, 'view' => 'batch']) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                <span>Cek Stok Batch di Gudang</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()?->canEditTerima())
                                            @php
                                                $isItemLocked = $row->header?->isLockedByStockUsage();
                                            @endphp
                                            @if ($isItemLocked)
                                                <a href="{{ route('gudang.terima.show', $row->header?->terima_id) }}#locked" class="action-dropdown-item" style="color: #64748b;" title="Dokumen terkunci: Sebagian/seluruh bahan baku telah digunakan di lini produksi">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    <span>Edit (Terkunci)</span>
                                                </a>
                                            @else
                                                <a href="{{ route('gudang.terima.edit', $row->header?->terima_id) }}" class="action-dropdown-item">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit Penerimaan</span>
                                                </a>
                                            @endif
                                        @endif
                                        @if (Auth::user()?->canDeleteTerima())
                                            <div class="action-dropdown-divider"></div>
                                            <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteTerimaModal({{ $row->header?->terima_id }}, '{{ $row->header?->terima_no }}', '{{ addslashes($row->header?->supplier?->supplier_nm ?? '') }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Hapus Dokumen</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                    Belum ada riwayat penerimaan barang fisik. Klik tombol <strong>"+ Catat Penerimaan Barang"</strong> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            {{-- VIEW 2: DAFTAR DOKUMEN GRN (HEADER VIEW) --}}
            <div style="overflow-x: auto;">
                <table class="terima-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="min-width: 160px;">Nomor Penerimaan</th>
                            <th style="min-width: 120px;">Tanggal Masuk</th>
                            <th style="min-width: 130px;">Ref. PO</th>
                            <th style="min-width: 180px;">Supplier Pengirim</th>
                            <th style="min-width: 140px;">Gudang Simpan</th>
                            <th style="min-width: 220px;">Bahan &amp; Grade Diterima</th>
                            <th style="min-width: 130px; text-align: right;">Total Fisik Netto</th>
                            <th style="width: 100px; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataList as $index => $item)
                            @php
                                $dtlReject = (float) $item->details->sum('reject_qty');
                                $qcReject = (float) ($item->qc?->details->sum('qty_reject') ?? 0);
                                $totalHdrReject = $dtlReject > 0 ? $dtlReject : $qcReject;
                                $qcId = $item->qc_id;
                                $qcNo = $item->qc?->qc_no ?? $item->terima_no;
                                $satuanUtama = $item->details->first()?->barang?->satuanDasar?->satuan_nm ?? 'KG';
                                $itemCount = $item->details->count();
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                    {{ $dataList->firstItem() + $index }}
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                                        <a href="{{ route('gudang.terima.show', $item->terima_id) }}" class="terima-link-no">
                                            {{ $item->terima_no }}
                                        </a>
                                        @if ($totalHdrReject > 0)
                                            @if ($qcId)
                                                <button type="button" 
                                                        onclick="openBeritaAcaraModal('{{ $qcId }}', '{{ $qcNo }}')"
                                                        class="badge-reject-btn"
                                                        title="Buka Berita Acara Penolakan">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>Reject ({{ number_format($totalHdrReject, 0, ',', '.') }})</span>
                                                </button>
                                            @else
                                                <span class="badge-reject-tag" title="Ada penolakan barang">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>Reject ({{ number_format($totalHdrReject, 0, ',', '.') }})</span>
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="terima-sj-text">
                                        SJ: {{ $item->suratjalan_no ?: '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="terima-date-text">
                                        {{ \Carbon\Carbon::parse($item->terima_tgl)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>
                                    @if ($item->po)
                                        <a href="{{ route('gudang.po.show', $item->po_id) }}" class="terima-po-capsule" title="Buka Detail Purchase Order">
                                            {{ $item->po->po_no }}
                                        </a>
                                    @else
                                        <span class="terima-non-po">Non-PO</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="terima-supplier-name">{{ $item->supplier?->supplier_nm ?? '-' }}</div>
                                    <div class="terima-supplier-sub">
                                        {{ $item->supplier?->jenisSupplier?->jenis_supplier_nm ?? ($item->supplier?->supplier_cd ?? 'Supplier') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="terima-gudang-cell">
                                        <span class="terima-gudang-name">{{ $item->gudang?->gudang_nm ?? '-' }}</span>
                                        @if ($item->gudang?->gudang_cd)
                                            <span class="terima-gudang-code-pill">{{ $item->gudang->gudang_cd }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($itemCount === 1)
                                        @php
                                            $singleD = $item->details->first();
                                            $sgGrade = strtoupper(trim((string)$singleD->grade_cd));
                                            $sgIsNon = in_array($sgGrade, ['NON-GRADE', 'NON_GRADE', 'NON GRADE']) || ($singleD->barang && !$singleD->barang->hasGrade());
                                            $sgNetto = (float) $singleD->terima_qty - (float) ($singleD->reject_qty ?? 0);
                                        @endphp
                                        <div class="terima-single-item">
                                            <span class="terima-item-name">{{ $singleD->barang?->barang_nm ?? '-' }}</span>
                                            @if ($sgIsNon)
                                                <span class="terima-grade-pill terima-grade-none">Non-Grade</span>
                                            @elseif ($sgGrade === 'B')
                                                <span class="terima-grade-pill terima-grade-b">Grade B</span>
                                            @elseif ($sgGrade === 'REJECT')
                                                <span class="terima-grade-pill terima-grade-reject">Afkir</span>
                                            @elseif ($sgGrade)
                                                <span class="terima-grade-pill terima-grade-a">Grade {{ $sgGrade }}</span>
                                            @else
                                                <span class="terima-grade-pill terima-grade-none">Non-Grade</span>
                                            @endif
                                            <span class="terima-item-qty-tag">({{ number_format($sgNetto, 0, ',', '.') }} {{ $singleD->barang?->satuanDasar?->satuan_nm ?? 'KG' }})</span>
                                        </div>
                                    @elseif ($itemCount > 1)
                                        <div class="terima-multi-items">
                                            <span class="terima-count-badge">{{ $itemCount }} Komoditas</span>
                                            <div class="terima-compact-list">
                                                @foreach ($item->details->take(2) as $d)
                                                    @php
                                                        $dGrade = strtoupper(trim((string)$d->grade_cd));
                                                        $dIsNon = in_array($dGrade, ['NON-GRADE', 'NON_GRADE', 'NON GRADE']) || ($d->barang && !$d->barang->hasGrade());
                                                        $dNetto = (float)$d->terima_qty - (float)($d->reject_qty ?? 0);
                                                    @endphp
                                                    <div class="terima-compact-row">
                                                        <span class="terima-bullet">&bull;</span>
                                                        <span class="terima-compact-name">{{ $d->barang?->barang_nm ?? '-' }}</span>
                                                        @if ($dIsNon)
                                                            <span class="terima-grade-pill terima-grade-none terima-grade-pill-mini">Non-Grade</span>
                                                        @elseif ($dGrade === 'B')
                                                            <span class="terima-grade-pill terima-grade-b terima-grade-pill-mini">Grade B</span>
                                                        @elseif ($dGrade)
                                                            <span class="terima-grade-pill terima-grade-a terima-grade-pill-mini">{{ $dGrade }}</span>
                                                        @endif
                                                        <span class="terima-compact-qty">({{ number_format($dNetto, 0, ',', '.') }})</span>
                                                    </div>
                                                @endforeach
                                                @if ($itemCount > 2)
                                                    <div class="terima-more-items">+{{ $itemCount - 2 }} komoditas lainnya</div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="terima-non-po">-</span>
                                    @endif
                                </td>
                                <td class="terima-netto-cell">
                                    @php
                                        $hdrSumBruto = (float) $item->details->sum('terima_qty');
                                        $hdrSumAfkir = (float) $item->details->sum('reject_qty');
                                        $hdrNetto = max(0, $hdrSumBruto - $hdrSumAfkir);
                                    @endphp
                                    <div class="terima-netto-val">
                                        {{ number_format($hdrNetto, 2, ',', '.') }}
                                    </div>
                                    <div class="terima-netto-sub">
                                        {{ $satuanUtama }} Netto Bersih
                                    </div>
                                    @if ($totalHdrReject > 0)
                                        <span class="terima-afkir-pill">
                                            Afkir: {{ number_format($totalHdrReject, 0, ',', '.') }} {{ $satuanUtama }}
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: right; position: relative;">
                                    <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, 'dropdown-hdr-{{ $item->terima_id }}')">
                                        <span>Aksi</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div id="dropdown-hdr-{{ $item->terima_id }}" class="action-dropdown-menu">
                                        <a href="{{ route('gudang.terima.show', $item->terima_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Detail Dokumen</span>
                                        </a>
                                        <a href="{{ route('gudang.terima.export-pdf', $item->terima_id) }}" target="_blank" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span>Cetak PDF (GRN)</span>
                                        </a>
                                        @if ($totalHdrReject > 0 && $qcId)
                                            <button type="button" class="action-dropdown-item warning-item" onclick="openBeritaAcaraModal('{{ $qcId }}', '{{ $qcNo }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span style="font-weight: 700; color: #dc2626;">Berita Acara Penolakan</span>
                                            </button>
                                        @endif
                                        @if ($item->po_id)
                                            <a href="{{ route('gudang.po.show', $item->po_id) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                <span>Lihat PO Terkait</span>
                                            </a>
                                        @endif
                                        @if ($item->gudang_id)
                                            <a href="{{ route('gudang.stok.index', ['gudang_id' => $item->gudang_id]) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                <span>Lihat Stok di Gudang Ini</span>
                                            </a>
                                        @endif
                                        @if (Auth::user()?->canEditTerima())
                                            @php
                                                $isHdrLocked = $item->isLockedByStockUsage();
                                            @endphp
                                            @if ($isHdrLocked)
                                                <a href="{{ route('gudang.terima.show', $item->terima_id) }}#locked" class="action-dropdown-item" style="color: #64748b;" title="Dokumen terkunci: Sebagian/seluruh bahan baku telah digunakan di lini produksi">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    <span>Edit (Terkunci)</span>
                                                </a>
                                            @else
                                                <a href="{{ route('gudang.terima.edit', $item->terima_id) }}" class="action-dropdown-item">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit Penerimaan</span>
                                                </a>
                                            @endif
                                        @endif
                                        @if (Auth::user()?->canDeleteTerima())
                                            <div class="action-dropdown-divider"></div>
                                            <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteTerimaModal({{ $item->terima_id }}, '{{ $item->terima_no }}', '{{ addslashes($item->supplier?->supplier_nm ?? '') }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Hapus Dokumen</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                    Belum ada riwayat dokumen penerimaan barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- PAGINATION MODERN --}}
        @include('gudang.terima.partials.pagination-modern')
    </div>
</div>
@endsection

@include('gudang.terima.partials.modal-import-excel')
@include('gudang.terima.partials.modal-delete-confirm')
@include('gudang.qc.partials.common.modal-preview-berita-acara')

@push('scripts')
<script src="{{ asset('js/gudang/terima/terima-index.js') }}?v={{ file_exists(public_path('js/gudang/terima/terima-index.js')) ? filemtime(public_path('js/gudang/terima/terima-index.js')) : time() }}"></script>
@endpush
