@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-index.css') }}?v={{ file_exists(public_path('css/gudang/po/po-index.css')) ? filemtime(public_path('css/gudang/po/po-index.css')) : time() }}">
@endpush

@section('content')
<div class="po-page-wrapper">

    {{-- 1. TOP HEADER SECTION (PERSIS KAS KELUAR) --}}
    <div class="po-top-header">
        <div>
            <div class="po-header-kicker">Pengadaan &amp; Logistik</div>
            <h1 class="po-header-title">Purchase Order (PO)</h1>
            <p class="po-header-subtitle">
                Kelola dokumen pemesanan bahan baku singkong, minyak, bumbu, dan kemasan ke mitra supplier.
            </p>
        </div>

        <div class="po-header-actions">
            <a href="{{ route('gudang.terima.index') }}" class="btn-pill-outline">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Penerimaan (Inbound)</span>
            </a>

            <a href="{{ route('gudang.po.create') }}" class="btn-pill-dark">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat PO Baru</span>
            </a>
        </div>
    </div>

    {{-- 2. METRICS CARDS (GAYA PERSIS KAS KELUAR - 5 KOLOM) --}}
    @php
        $currentStatus = $status ?? '';
    @endphp
    <div class="po-metrics-grid">
        {{-- Card 1: Semua PO --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <span class="po-metric-label">Semua Dokumen PO</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">{{ number_format($statusCounts['all'] ?? 0) }}</div>
            <div class="po-metric-footer">Total transaksi pengadaan aktif</div>
        </div>

        {{-- Card 2: Menunggu Approval --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="po-metric-label">Menunggu Approval</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">{{ number_format($statusCounts['pending_approval'] ?? 0) }}</div>
            <div class="po-metric-footer">Menunggu verifikasi atasan</div>
        </div>

        {{-- Card 3: Menunggu Pengiriman --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="po-metric-label">Menunggu Kirim</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">{{ number_format($statusCounts['approved'] ?? 0) }}</div>
            <div class="po-metric-footer">PO disetujui &bull; Siap kirim</div>
        </div>

        {{-- Card 4: Masuk Sebagian --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM4 17h1m4 0h6m4 0h1m-1-4V6a1 1 0 00-1-1H4a1 1 0 00-1 1v7m14 0h3l2 3v1a1 1 0 01-1 1h-1m-17 0H3a1 1 0 01-1-1v-1l2-3h12" />
                        </svg>
                    </span>
                    <span class="po-metric-label">Masuk Sebagian</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">{{ number_format($statusCounts['partial'] ?? 0) }}</div>
            <div class="po-metric-footer">Truk tiba &bull; Ada sisa kuota</div>
        </div>

        {{-- Card 5: Selesai & Arsip --}}
        <div class="po-metric-card">
            <div class="po-metric-top">
                <div class="po-metric-header-left">
                    <span class="po-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span class="po-metric-label">Selesai &amp; Arsip</span>
                </div>
                <span class="po-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="po-metric-value">{{ number_format($statusCounts['completed'] ?? 0) }}</div>
            <div class="po-metric-footer">Pengadaan selesai &bull; Arsip tuntas</div>
        </div>
    </div>

    {{-- 3. MAIN DATA CARD (TABLE & CONTROLS) --}}
    <div class="po-main-card">
        {{-- Control Bar --}}
        <div class="po-control-bar">
            {{-- Tabs Status Row --}}
            <div class="po-tabs-row">
                <div class="po-status-pills">
                    <a href="{{ route('gudang.po.index') }}"
                       class="po-status-tab {{ empty($currentStatus) ? 'active' : '' }}">
                        <span>Semua</span>
                        <span class="badge-count">{{ $statusCounts['all'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('gudang.po.index', ['status' => 'PENDING_APPROVAL']) }}"
                       class="po-status-tab {{ $currentStatus === 'PENDING_APPROVAL' ? 'active' : '' }}">
                        <span>Menunggu Approval</span>
                        <span class="badge-count">{{ $statusCounts['pending_approval'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('gudang.po.index', ['status' => 'APPROVED']) }}"
                       class="po-status-tab {{ $currentStatus === 'APPROVED' ? 'active' : '' }}">
                        <span>Disetujui</span>
                        <span class="badge-count">{{ $statusCounts['approved'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('gudang.po.index', ['status' => 'PARTIAL']) }}"
                       class="po-status-tab {{ $currentStatus === 'PARTIAL' ? 'active' : '' }}">
                        <span>Masuk Sebagian</span>
                        <span class="badge-count">{{ $statusCounts['partial'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('gudang.po.index', ['status' => 'COMPLETED']) }}"
                       class="po-status-tab {{ in_array($currentStatus, ['COMPLETED', 'COMPLETED_CLOSED', 'CLOSED']) ? 'active' : '' }}">
                        <span>Selesai</span>
                        <span class="badge-count">{{ $statusCounts['completed'] ?? 0 }}</span>
                    </a>
                </div>
            </div>

            {{-- Filter Form Row --}}
            <form action="{{ route('gudang.po.index') }}" method="GET" class="po-filter-form">
                <div class="po-search-wrapper">
                    <span class="po-search-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Cari nomor PO atau nama supplier mitra..."
                           class="po-search-input">
                </div>

                <select name="status" class="po-select-control" onchange="this.form.submit()">
                    <option value="">-- Status Spesifik --</option>
                    <option value="PENDING_APPROVAL" {{ ($currentStatus == 'PENDING_APPROVAL') ? 'selected' : '' }}>Menunggu Approval</option>
                    <option value="REVISION" {{ ($currentStatus == 'REVISION') ? 'selected' : '' }}>Perlu Revisi Atasan</option>
                    <option value="APPROVED" {{ ($currentStatus == 'APPROVED') ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="PARTIAL" {{ ($currentStatus == 'PARTIAL') ? 'selected' : '' }}>Sebagian Diterima</option>
                    <option value="COMPLETED_CLOSED" {{ ($currentStatus == 'COMPLETED_CLOSED') ? 'selected' : '' }}>Selesai / Ditutup</option>
                    <option value="COMPLETED" {{ ($currentStatus == 'COMPLETED') ? 'selected' : '' }}>Selesai (Completed)</option>
                    <option value="CLOSED" {{ ($currentStatus == 'CLOSED') ? 'selected' : '' }}>Ditutup (Closed)</option>
                    <option value="REJECTED" {{ ($currentStatus == 'REJECTED') ? 'selected' : '' }}>Ditolak Atasan (Rejected)</option>
                    <option value="DRAFT" {{ ($currentStatus == 'DRAFT') ? 'selected' : '' }}>Draft</option>
                    <option value="CANCELLED" {{ ($currentStatus == 'CANCELLED') ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <button type="submit" class="btn-po-filter">Filter</button>
                @if(!empty($search) || !empty($currentStatus))
                    <a href="{{ route('gudang.po.index') }}" class="btn-po-reset">Reset</a>
                @endif

                <div class="po-total-badge">
                    Total PO: <strong>{{ $poList->total() }}</strong> dokumen
                </div>
            </form>
        </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 180px;">Nomor PO</th>
                    <th style="min-width: 120px;">Tanggal</th>
                    <th style="min-width: 180px;">Supplier Mitra</th>
                    <th style="min-width: 140px;">Gudang Tujuan</th>
                    <th style="min-width: 200px;">Status &amp; Realisasi</th>
                    <th style="min-width: 130px; text-align: right;">Total Nominal</th>
                    <th style="width: 160px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($poList as $index => $po)
                    @php
                        $pct = $po->persentase_terima;
                        $totalPesan = (float) $po->details->sum('pesan_qty');
                        $totalTerima = (float) $po->details->sum('terima_qty');
                        $totalSisa = (float) $po->total_sisa_qty;
                        $canReceive = in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $totalSisa > 0;

                        // Siapkan payload JSON aman untuk Quick Receive Modal
                        $poPayload = [
                            'po_id' => $po->po_id,
                            'po_no' => $po->po_no,
                            'supplier_id' => $po->supplier_id,
                            'supplier_nm' => $po->supplier?->supplier_nm ?? '-',
                            'gudang_id' => $po->gudang_id,
                            'gudang_nm' => $po->gudang?->gudang_nm ?? '-',
                            'items' => $po->details->map(function ($dtl) {
                                $acronym = app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym(
                                    $dtl->barang?->barang_nm,
                                    $dtl->barang?->barang_cd
                                );
                                return [
                                    'podtl_id'      => $dtl->podtl_id,
                                    'barang_id'     => $dtl->barang_id,
                                    'barang_nm'     => $dtl->barang?->barang_nm ?? '-',
                                    'batch_prefix'  => ($acronym ?: 'BRG') . '-',
                                    'satuan_nm'     => $dtl->barang?->satuanDasar?->satuan_nm ?? ($dtl->barang?->satuanDasar?->satuan_cd ?? '-'),
                                    'pesan_qty'     => (float) $dtl->pesan_qty,
                                    'terima_qty'    => (float) $dtl->terima_qty,
                                    'sisa_qty'      => (float) $dtl->sisa_qty,
                                    'harga_nominal' => (float) $dtl->harga_nominal,
                                ];
                            })->values(),
                        ];
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            {{ $poList->firstItem() + $index }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                {{-- Tombol expand preview item tanpa pindah halaman --}}
                                <button type="button" 
                                        onclick="togglePoRow('row-items-{{ $po->po_id }}', this)" 
                                        title="Intip rincian barang"
                                        style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; padding: 0; flex-shrink: 0;">
                                    <svg class="chevron-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.15s ease;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                        <a href="javascript:void(0)" 
                                           onclick="openPoDetailModal({{ $po->po_id }})" 
                                           class="po-table-no" 
                                           title="Klik untuk melihat detail dokumen PO">
                                            {{ $po->po_no }}
                                        </a>
                                        @if ($po->is_ada_revisi)
                                            <span class="po-badge-revision" title="Dokumen ini telah direvisi {{ $po->total_revisi_count }}x (Terakhir oleh: {{ $po->terakhir_direvisi_by }})">
                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Rev {{ $po->total_revisi_count }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <span class="po-table-subtext" style="display: block;">
                                        {{ $po->details->count() }} item bahan baku
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="po-table-date">
                                {{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}
                            </div>
                            @if ($po->tgl_estimasi_datang)
                                <span class="po-table-eta">
                                    ETA: {{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                            <span class="po-table-subtext" style="display: block;">{{ $po->supplier?->supplier_cd }}</span>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: #334155;">{{ $po->gudang?->gudang_nm ?? '-' }}</span>
                        </td>
                        <td>
                            {{-- BADGE STATUS & PROGRESS BAR REALISASI FISIK --}}
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <div>
                                    @if ($po->status_cd == 'COMPLETED')
                                        <span class="po-status-indicator po-status-completed">
                                            <span class="po-status-dot"></span>
                                            <span>{{ !empty($po->closed_at) ? 'Selesai (Batal Sisa)' : 'Selesai (100%)' }}</span>
                                        </span>
                                    @elseif ($po->status_cd == 'CLOSED')
                                        <span class="po-status-indicator po-status-closed" title="Selesai dengan pembatalan sisa kuota: {{ $po->closed_reason }}">
                                            <span class="po-status-dot"></span>
                                            <span>Selesai (Batal Sisa)</span>
                                        </span>
                                    @elseif ($po->status_cd == 'APPROVED')
                                        <span class="po-status-indicator po-status-approved">
                                            <span class="po-status-dot"></span>
                                            <span>Disetujui</span>
                                        </span>
                                    @elseif ($po->status_cd == 'PARTIAL')
                                        <span class="po-status-indicator po-status-partial">
                                            <span class="po-status-dot"></span>
                                            <span>Masuk Sebagian ({{ $pct }}%)</span>
                                        </span>
                                    @elseif ($po->status_cd == 'PENDING_APPROVAL')
                                        <span class="po-status-indicator po-status-pending-approval">
                                            <span class="po-status-dot"></span>
                                            <span>Menunggu Approval</span>
                                        </span>
                                    @elseif ($po->status_cd == 'REVISION')
                                        <span class="po-status-indicator po-status-revision">
                                            <span class="po-status-dot"></span>
                                            <span>Perlu Revisi Atasan</span>
                                        </span>
                                    @elseif ($po->status_cd == 'REJECTED')
                                        <span class="po-status-indicator po-status-rejected">
                                            <span class="po-status-dot"></span>
                                            <span>Ditolak Atasan</span>
                                        </span>
                                    @elseif ($po->status_cd == 'DRAFT')
                                        <span class="po-status-indicator po-status-draft">
                                            <span class="po-status-dot"></span>
                                            <span>Draft</span>
                                        </span>
                                    @else
                                        <span class="po-status-indicator po-status-cancelled">
                                            <span class="po-status-dot"></span>
                                            <span>Dibatalkan</span>
                                        </span>
                                    @endif
                                </div>

                                {{-- Memo Revisi / Catatan Tolak dari Atasan --}}
                                @if ($po->status_cd == 'REVISION' && !empty($po->reject_reason))
                                    <div class="po-memo-box po-memo-revision" title="{{ $po->reject_reason }}">
                                        <strong>Memo Revisi:</strong> {{ \Illuminate\Support\Str::limit($po->reject_reason, 40) }}
                                    </div>
                                @elseif ($po->status_cd == 'REJECTED' && !empty($po->reject_reason))
                                    <div class="po-memo-box po-memo-rejected" title="{{ $po->reject_reason }}">
                                        <strong>Alasan Tolak:</strong> {{ \Illuminate\Support\Str::limit($po->reject_reason, 40) }}
                                    </div>
                                @endif

                                {{-- Progress Bar Ramping & Tenang --}}
                                <div class="po-progress-track">
                                    <div class="po-progress-bar {{ in_array($po->status_cd, ['COMPLETED', 'CLOSED']) ? 'is-completed' : '' }}" style="width: {{ min(100, $pct) }}%;"></div>
                                </div>
                                <div class="po-table-subtext">
                                    @if ($po->status_cd == 'PENDING_APPROVAL')
                                        Approver: <strong style="color: #475569;">{{ $po->approver?->name ?? 'Belum ditentukan' }}</strong>
                                    @elseif ($po->status_cd == 'REJECTED')
                                        <span title="{{ $po->reject_reason }}">Alasan: {{ \Illuminate\Support\Str::limit($po->reject_reason ?? 'Tidak ada catatan', 30) }}</span>
                                    @elseif ($po->status_cd == 'COMPLETED')
                                        @if (!empty($po->closed_at))
                                            Realisasi {{ number_format($totalTerima, 0) }} / {{ number_format($totalPesan, 0) }} &bull; Sisa dibatalkan
                                        @else
                                            Tuntas diterima
                                        @endif
                                    @elseif ($po->status_cd == 'PARTIAL')
                                        Masuk {{ number_format($totalTerima, 0) }} / {{ number_format($totalPesan, 0) }}
                                    @elseif ($po->status_cd == 'APPROVED')
                                        Menunggu kedatangan truk
                                    @elseif ($po->status_cd == 'CLOSED')
                                        Realisasi {{ number_format($totalTerima, 0) }} / {{ number_format($totalPesan, 0) }} &bull; Sisa dibatalkan
                                    @else
                                        -
                                    @endif
                                </div>

                                {{-- BADGE STATUS EMAIL KE SUPPLIER --}}
                                @if ($po->isSentToSupplier())
                                    <div style="margin-top: 2px;">
                                        <span class="po-badge-email" title="Terkirim ke {{ $po->supplier_email_sent }} pada {{ \Carbon\Carbon::parse($po->sent_to_supplier_at)->format('d/m/Y H:i') }}">
                                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span>PO Terkirim</span>
                                        </span>
                                    </div>
                                @endif

                                {{-- BADGE STATUS QC INBOUND --}}
                                @php $qcInfo = $po->qc_status_info; @endphp
                                <div style="margin-top: 3px;">
                                    @if ($qcInfo['tested'])
                                        @php
                                            $qcDotColor = '#16a34a'; // lolos
                                            if ($qcInfo['status'] === 'DITOLAK') {
                                                $qcDotColor = '#dc2626';
                                            } elseif ($qcInfo['status'] === 'PARSIAL') {
                                                $qcDotColor = '#d97706';
                                            } elseif ($qcInfo['status'] === 'DRAFT') {
                                                $qcDotColor = '#64748b';
                                            }
                                        @endphp
                                        <span class="po-qc-pill" title="{{ $qcInfo['label'] }} ({{ $qcInfo['count'] }} Tiket Uji QC Inbound)">
                                            <span class="po-qc-dot" style="background: {{ $qcDotColor }};"></span>
                                            <span>{{ $qcInfo['short_label'] }} ({{ $qcInfo['count'] }})</span>
                                        </span>
                                    @elseif (in_array($po->status_cd, ['APPROVED', 'PARTIAL']))
                                        <span class="po-qc-pill" title="Belum ada catatan inspeksi kedatangan oleh tim QC Lapangan">
                                            <span class="po-qc-dot" style="background: #94a3b8;"></span>
                                            <span>Belum Diuji QC</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @php
                            $nominalRow = (float) $po->total_tagihan > 0 
                                ? (float) $po->total_tagihan 
                                : ((float) $po->total_nominal > 0 
                                    ? (float) $po->total_nominal 
                                    : (float) $po->details->sum(fn($d) => (float)$d->subtotal_tagihan > 0 ? (float)$d->subtotal_tagihan : ((float)$d->subtotal_nominal > 0 ? (float)$d->subtotal_nominal : (float)$d->pesan_qty * (float)$d->harga_nominal)));
                        @endphp
                        <td style="text-align: right;">
                            <div class="po-nominal-value">Rp {{ number_format($nominalRow, 0, ',', '.') }}</div>
                            <div style="display: flex; gap: 4px; justify-content: flex-end; align-items: center; margin-top: 3px; flex-wrap: wrap;">
                                @foreach ($po->getTaxBadges() as $tb)
                                    <span class="po-tax-capsule" title="{{ $tb['title'] }}">{{ $tb['label'] }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align: right; position: relative;">
                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'poDropdown-{{ $po->po_id }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            @php
                                $sendSupplierPayload = [
                                    'po_id'          => $po->po_id,
                                    'po_no'          => $po->po_no,
                                    'supplier_nm'    => $po->supplier?->supplier_nm ?? '-',
                                    'supplier_email' => $po->supplier?->email ?? ($po->supplier_email_sent ?? ''),
                                    'default_cc'     => '',
                                    'default_notes'  => $po->supplier_email_notes ?? '',
                                ];
                            @endphp

                            {{-- DROPDOWN MENU ITEMS --}}
                            <div id="poDropdown-{{ $po->po_id }}" class="action-dropdown-menu">
                                
                                {{-- 1. KELOMPOK DOKUMEN: LIHAT & CETAK --}}
                                <button type="button" 
                                        class="action-dropdown-item" 
                                        onclick="closeAllActionDropdowns(); openPoDetailModal({{ $po->po_id }})" 
                                        title="Buka pratinjau cepat rincian dokumen PO">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span style="font-weight: 600;">Lihat Detail PO</span>
                                </button>

                                <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span>Cetak PDF Resmi</span>
                                </a>

                                {{-- JIKA ADA PEMBATALAN SISA: CETAK BERITA ACARA --}}
                                @if (!empty($po->closed_at))
                                    <a href="{{ route('gudang.po.berita_acara', $po->po_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span style="font-weight: 700; color: #dc2626;">Berita Acara Pembatalan</span>
                                    </a>
                                @endif

                                {{-- 2. KELOMPOK OPERASIONAL: APPROVAL, EMAIL SUPPLIER, TERIMA BARANG, BATAL SISA --}}
                                @php
                                    $hasOperationalAction = ($po->status_cd == 'PENDING_APPROVAL')
                                        || ($po->isApproved() || in_array($po->status_cd, ['APPROVED', 'PARTIAL', 'COMPLETED']))
                                        || $canReceive
                                        || ($po->status_cd == 'PARTIAL' && $totalSisa > 0);
                                @endphp

                                @if ($hasOperationalAction)
                                    <div class="action-dropdown-divider"></div>

                                    {{-- JIKA PENDING APPROVAL --}}
                                    @if ($po->status_cd == 'PENDING_APPROVAL')
                                        <form action="{{ route('gudang.po.resend_approval', $po->po_id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="action-dropdown-item" title="Kirim surat permohonan persetujuan ke email Atasan">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <span style="font-weight: 600; color: #b45309;">Kirim Email Approval</span>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- JIKA PO APPROVED: KIRIM KE SUPPLIER --}}
                                    @if ($po->isApproved() || in_array($po->status_cd, ['APPROVED', 'PARTIAL', 'COMPLETED']))
                                        <button type="button" 
                                                class="action-dropdown-item" 
                                                onclick="closeAllActionDropdowns(); openKirimSupplierModal({{ json_encode($sendSupplierPayload) }})">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            <span style="font-weight: 600; color: #0284c7;">
                                                {{ $po->isSentToSupplier() ? 'Kirim Ulang ke Supplier' : 'Kirim ke Supplier via Email' }}
                                            </span>
                                        </button>
                                    @endif

                                    {{-- TERIMA BARANG (JIKA DAPAT DITERIMA: ARAHKAN KE FORM RESMI PENERIMAAN) --}}
                                    @if ($canReceive)
                                        <a href="{{ route('gudang.terima.create', ['po_id' => $po->po_id]) }}" 
                                           class="action-dropdown-item" 
                                           title="Catat penerimaan barang resmi di modul Inbound Gudang">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span style="font-weight: 600; color: #0f172a;">Catat Terima Barang</span>
                                        </a>
                                    @endif

                                    {{-- BATALKAN SISA & SELESAIKAN (JIKA PARTIAL & ADA SISA) --}}
                                    @if ($po->status_cd == 'PARTIAL' && $totalSisa > 0)
                                        @php
                                            $forceClosePayload = [
                                                'po_id'        => $po->po_id,
                                                'po_no'        => $po->po_no,
                                                'supplier_nm'  => $po->supplier?->supplier_nm ?? '-',
                                                'total_pesan'  => $totalPesan,
                                                'total_terima' => $totalTerima,
                                                'total_sisa'   => $totalSisa,
                                                'url'          => route('gudang.po.force_close', $po->po_id),
                                            ];
                                        @endphp
                                        <button type="button" 
                                                class="action-dropdown-item danger-item" 
                                                onclick="closeAllActionDropdowns(); openBatalkanSisaModal({{ json_encode($forceClosePayload) }})">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span style="font-weight: 700; color: #dc2626;">Batalkan Sisa Pesanan</span>
                                        </button>
                                    @endif
                                @endif

                                {{-- 3. KELOMPOK MANAJEMEN: EDIT & HAPUS --}}
                                @php
                                    $canEdit = (Auth::user()?->isSuperAdmin() || Auth::user()?->canEditPo()) && (!in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED']) || Auth::user()?->isSuperAdmin());
                                    $canDelete = (Auth::user()?->isSuperAdmin() || Auth::user()?->canDeletePo()) && ($totalTerima == 0 || Auth::user()?->isSuperAdmin());
                                @endphp

                                @if ($canEdit || $canDelete)
                                    <div class="action-dropdown-divider"></div>

                                    @if ($canEdit)
                                        <a href="{{ route('gudang.po.edit', $po->po_id) }}" class="action-dropdown-item" style="{{ $po->status_cd == 'REVISION' ? 'color: #1d4ed8; font-weight: 700;' : '' }}">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>{{ $po->status_cd == 'REVISION' ? 'Perbaiki / Revisi PO' : 'Edit PO' }}</span>
                                        </a>
                                    @endif

                                    @if ($canDelete)
                                        <form action="{{ route('gudang.po.destroy', $po->po_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS dokumen Purchase Order {{ $po->po_no }}? Tindakan ini tidak dapat dibatalkan.')" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-dropdown-item danger-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Hapus PO</span>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- EXPANDABLE SUB-ROW: RINCIAN ITEM BARANG TANPA PINDAH HALAMAN --}}
                    <tr id="row-items-{{ $po->po_id }}" style="display: none; background: #f8fafc;">
                        <td colspan="8" style="padding: 0.75rem 1.25rem; border-bottom: 2px solid #e2e8f0;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <strong style="font-size: 0.825rem; color: #334155;">
                                        Rincian Item Pesanan ({{ $po->po_no }}):
                                    </strong>
                                    <a href="{{ route('gudang.po.show', $po->po_id) }}" style="font-size: 0.75rem; color: #0284c7; text-decoration: none;">
                                        Buka halaman detail penuh &rarr;
                                    </a>
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Nama Bahan Baku</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: left;">Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Pesan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Sudah Masuk</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Sisa Belum Tiba</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Harga Satuan</th>
                                            <th style="padding: 0.35rem 0.5rem; text-align: right;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($po->details as $item)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.4rem 0.5rem;">
                                                    <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                                                    <span style="display: block; font-size: 0.7rem; color: #64748b;">{{ $item->barang?->barang_cd }}</span>
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; color: #475569;">
                                                    {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600;">
                                                    {{ number_format((float) $item->pesan_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: #059669;">
                                                    {{ number_format((float) $item->terima_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                                                    {{ number_format((float) $item->sisa_qty, 2) }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; color: #475569;">
                                                    Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.5rem; text-align: right; font-weight: 600; color: #0f172a;">
                                                    Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada transaksi Purchase Order. Klik tombol <strong>"Buat PO Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($poList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $poList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL DETAIL DOKUMEN PO (QUICK VIEW POPUP) --}}
@include('gudang.po.partials.modal-detail-po')

{{-- MODAL DOKUMEN FORM HACCP RESMI --}}
@include('gudang.po.partials.modal-haccp-po')

{{-- MODAL KIRIM DOKUMEN KE SUPPLIER --}}
@include('gudang.po.partials.modal-kirim-supplier')

{{-- MODAL BATALKAN SISA & SELESAIKAN PO --}}
@include('gudang.po.partials.modal-force-close')

</div>
@endsection

@push('scripts')
<script>
    window.poConfig = {
        baseUrl: "{{ url('gudang/po') }}"
    };
    window.quickTerimaCreateRoute = "{{ route('gudang.terima.create') }}";
</script>
<script src="{{ asset('js/gudang/po/po-index.js') }}?v={{ file_exists(public_path('js/gudang/po/po-index.js')) ? filemtime(public_path('js/gudang/po/po-index.js')) : time() }}"></script>
<script>
    // Fallback registration jika browser mencache script lama
    if (typeof window.openPoDetailModal !== 'function') {
        window.openPoDetailModal = function(poId) {
            const modal = document.getElementById('modalDetailPo');
            const spinner = document.getElementById('poDetailLoadingSpinner');
            const content = document.getElementById('poDetailContentBody');

            if (!modal) return;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            if (spinner) spinner.style.display = 'block';
            if (content) content.style.display = 'none';

            fetch('{{ url("gudang/po") }}/' + poId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(result => {
                if (result.status === 'success' && result.data && typeof renderPoDetail === 'function') {
                    renderPoDetail(result.data);
                } else if (result.status === 'success' && result.data) {
                    location.reload(true);
                }
            })
            .catch(err => {
                alert('Gagal memuat detail dokumen PO: ' + err.message);
                if (modal) modal.style.display = 'none';
                document.body.style.overflow = '';
            });
        };
    }

    if (typeof window.openPoHaccpModal !== 'function') {
        window.openPoHaccpModal = function(poId) {
            const modal = document.getElementById('modalHaccpPo');
            const spinner = document.getElementById('haccpLoadingSpinner');
            const sheet = document.getElementById('haccpPaperSheet');

            if (!modal) return;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            if (spinner) spinner.style.display = 'block';
            if (sheet) sheet.style.display = 'none';

            fetch('{{ url("gudang/po") }}/' + poId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(result => {
                if (result.status === 'success' && result.data && typeof renderPoHaccpSheet === 'function') {
                    renderPoHaccpSheet(result.data);
                } else if (result.status === 'success' && result.data) {
                    location.reload(true);
                }
            })
            .catch(err => {
                alert('Gagal memuat dokumen form HACCP: ' + err.message);
                if (modal) modal.style.display = 'none';
                document.body.style.overflow = '';
            });
        };
    }
</script>
@endpush
