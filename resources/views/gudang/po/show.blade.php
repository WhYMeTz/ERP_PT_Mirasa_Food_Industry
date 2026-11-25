@extends('layouts.app')

@section('title', 'Detail Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-show.css') }}?v={{ file_exists(public_path('css/gudang/po/po-show.css')) ? filemtime(public_path('css/gudang/po/po-show.css')) : time() }}">
@endpush

@section('content')
<div class="po-show-wrapper">

    {{-- 1. BREADCRUMB (PERSIS CREATE & KAS KELUAR) --}}
    <nav class="po-breadcrumb no-print" aria-label="Breadcrumb">
        <a href="{{ route('gudang.po.index') }}">Pengadaan &amp; Logistik</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('gudang.po.index') }}">Purchase Order</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $po->po_no }}</span>
    </nav>

    {{-- 2. HEADER BAR (PERSIS CREATE & KAS KELUAR STANDARD) --}}
    <div class="po-header-bar no-print">
        <div class="po-header-info">
            <div class="po-header-title">
                <a href="{{ route('gudang.po.index') }}"
                   class="po-back-link"
                   aria-label="Kembali ke daftar purchase order"
                   title="Kembali ke Indeks Pengadaan">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>

                {{-- macOS Traffic Lights Accent --}}
                <div class="mac-traffic-dots" aria-label="macOS Window Controls">
                    <span class="mac-dot mac-dot-red" title="ERP PT Mirasa"></span>
                    <span class="mac-dot mac-dot-yellow" title="Purchase Order Detail"></span>
                    <span class="mac-dot mac-dot-green" title="Active Document"></span>
                </div>

                <h1>{{ $po->po_no }}</h1>

                {{-- Status Document Badge --}}
                @if ($po->status_cd == 'PENDING_APPROVAL')
                    <span class="po-badge po-badge-pending">Menunggu Approval Atasan</span>
                @elseif ($po->status_cd == 'REVISION')
                    <span class="po-badge po-badge-revision">Perlu Revisi Atasan</span>
                @elseif ($po->status_cd == 'REJECTED')
                    <span class="po-badge po-badge-rejected">Ditolak Atasan</span>
                @elseif ($po->status_cd == 'COMPLETED')
                    @if (!empty($po->closed_at))
                        <span class="po-badge po-badge-completed">Selesai (Batal Sisa {{ $po->persentase_terima }}%)</span>
                    @else
                        <span class="po-badge po-badge-completed">Selesai (100%)</span>
                    @endif
                @elseif ($po->status_cd == 'PARTIAL')
                    <span class="po-badge po-badge-partial">Sebagian Diterima ({{ $po->persentase_terima }}%)</span>
                @elseif ($po->status_cd == 'CLOSED')
                    <span class="po-badge po-badge-completed">Selesai (Batal Sisa {{ $po->persentase_terima }}%)</span>
                @elseif ($po->status_cd == 'APPROVED')
                    <span class="po-badge po-badge-approved">Disetujui</span>
                @elseif ($po->status_cd == 'DRAFT')
                    <span class="po-badge po-badge-draft">Draft</span>
                @else
                    <span class="po-badge po-badge-cancelled">Dibatalkan</span>
                @endif

                {{-- Tax Status Badges (Unified with DatPoHdr & Index Table) --}}
                @foreach ($po->getTaxBadges() as $tb)
                    <span class="po-badge po-badge-tax {{ str_contains($tb['type'], 'inc') ? 'po-badge-tax-inc' : '' }}" title="{{ $tb['title'] }}">
                        {{ $tb['label'] }}
                    </span>
                @endforeach

                {{-- QC Inspection Badge --}}
                @php $qcInfo = $po->qc_status_info; @endphp
                <span class="po-badge" style="background: {{ $qcInfo['bg'] }}; color: {{ $qcInfo['color'] }}; border: 1px solid {{ $qcInfo['border'] }};" title="{{ $qcInfo['summary'] }}">
                    <span>{{ $qcInfo['label'] }}</span>
                    @if ($qcInfo['tested'])
                        <span style="background: rgba(0,0,0,0.06); padding: 1px 6px; border-radius: 9999px; font-size: 0.7rem;">{{ $qcInfo['count'] }} QC</span>
                    @endif
                </span>

                @if ($po->isSentToSupplier())
                    <span class="po-badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;" title="Terkirim resmi ke email mitra supplier">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Terkirim Supplier</span>
                    </span>
                @endif

                @if ($po->is_ada_revisi)
                    <span class="po-badge" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;" title="Telah diedit {{ $po->total_revisi_count }}x (Audit Trail)">
                        Rev {{ $po->total_revisi_count }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Right: Smart Action Dropdown & Quick Export --}}
        <div class="po-header-actions">
            <a href="{{ route('gudang.po.index') }}" class="btn-pill-outline" title="Kembali ke Daftar PO">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Daftar PO</span>
            </a>

            <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" class="btn-pill-outline" title="Cetak Dokumen PDF Resmi HACCP">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak PDF</span>
            </a>

            @php
                $sendSupplierPayload = [
                    'po_id' => $po->po_id,
                    'po_no' => $po->po_no,
                    'supplier_nm' => $po->supplier?->supplier_nm ?? '-',
                    'supplier_email' => $po->supplier?->email ?? ($po->supplier_email_sent ?? ''),
                    'default_cc' => '',
                    'default_notes' => $po->supplier_email_notes ?? '',
                ];
            @endphp

            <div style="position: relative; display: inline-block;">
                <button type="button" class="btn-action-trigger" onclick="toggleShowActionMenu(event)">
                    <span>Menu Aksi PO</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div id="showActionMenuDropdown" class="show-action-menu-dropdown" style="display: none;">
                        @if ($po->status_cd == 'PENDING_APPROVAL')
                            <form action="{{ route('gudang.po.resend_approval', $po->po_id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="action-dropdown-item" title="Kirim surat permohonan persetujuan ke email Atasan">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span style="font-weight: 700; color: #b45309;">Kirim Email Approval</span>
                                </button>
                            </form>
                            <div class="action-dropdown-divider"></div>
                        @endif

                        @if ($po->isApproved() || in_array($po->status_cd, ['APPROVED', 'PARTIAL', 'COMPLETED']))
                            <button type="button" class="action-dropdown-item" onclick="toggleShowActionMenu(event); openKirimSupplierModal({{ json_encode($sendSupplierPayload) }})">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span style="font-weight: 700; color: #0284c7;">
                                    {{ $po->isSentToSupplier() ? 'Kirim Ulang ke Supplier' : 'Kirim ke Supplier via Email' }}
                                </span>
                            </button>
                            <div class="action-dropdown-divider"></div>
                        @endif

                        @if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $po->total_sisa_qty > 0)
                            <a href="{{ route('gudang.terima.create', ['po_id' => $po->po_id]) }}" class="action-dropdown-item">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Catat Terima Barang</span>
                            </a>
                        @endif

                        <button type="button" class="action-dropdown-item" onclick="openPoHaccpModal({{ $po->po_id }}); toggleShowActionMenu(event);">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Dokumen Form HACCP (Popup)</span>
                        </button>

                        <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" class="action-dropdown-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>Cetak PDF Resmi (HACCP)</span>
                        </a>

                        @if (!empty($po->closed_at))
                            <a href="{{ route('gudang.po.berita_acara', $po->po_id) }}" target="_blank" class="action-dropdown-item">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span style="font-weight: 700; color: #dc2626;">Berita Acara Pembatalan Sisa</span>
                            </a>
                        @endif

                        @php
                            $canEdit = (Auth::user()?->isSuperAdmin() || Auth::user()?->canEditPo()) && (!in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED']) || Auth::user()?->isSuperAdmin());
                        @endphp
                        @if ($canEdit)
                            <a href="{{ route('gudang.po.edit', $po->po_id) }}" class="action-dropdown-item">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit Purchase Order</span>
                            </a>
                        @endif

                        @if ($po->status_cd == 'PARTIAL' && $po->total_sisa_qty > 0)
                            <button type="button" class="action-dropdown-item danger-item" onclick="openBatalkanSisaModal({{ json_encode([
                                'po_id' => $po->po_id,
                                'po_no' => $po->po_no,
                                'supplier_nm' => $po->supplier?->supplier_nm ?? '-',
                                'total_pesan' => (float) $po->details->sum('pesan_qty'),
                                'total_terima' => (float) $po->details->sum('terima_qty'),
                                'total_sisa' => (float) $po->total_sisa_qty,
                                'url' => route('gudang.po.force_close', $po->po_id),
                            ]) }}); toggleShowActionMenu(event);">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span style="font-weight: 700; color: #dc2626;">Batalkan Sisa &amp; Selesaikan PO</span>
                            </button>
                        @endif

                        @if (in_array($po->status_cd, ['DRAFT', 'APPROVED', 'PENDING_APPROVAL']) && $po->details->sum('terima_qty') == 0)
                            <div class="action-dropdown-divider"></div>
                            <button type="button" class="action-dropdown-item danger-item" onclick="toggleShowActionMenu(event); openCancelPoModal();">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                <span>Batalkan PO</span>
                            </button>
                        @endif

                        @php
                            $canDelete = (Auth::user()?->isSuperAdmin() || Auth::user()?->canDeletePo()) && ($po->details->sum('terima_qty') == 0 || Auth::user()?->isSuperAdmin());
                        @endphp
                        @if ($canDelete)
                            <div class="action-dropdown-divider"></div>
                            <button type="button" class="action-dropdown-item danger-item" onclick="toggleShowActionMenu(event); openDeletePoModal();">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus PO</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    {{-- =====================================================================
         2. OPERATIONAL STATUS BANNERS (IF APPLICABLE)
         ===================================================================== --}}
    <div class="no-print">
        @if ($po->status_cd == 'CLOSED' || (!empty($po->closed_at) && $po->status_cd == 'COMPLETED'))
            <div class="po-alert-banner po-alert-success">
                <div>
                    <div style="font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 0.35rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Dokumen PO Telah Selesai (Sisa Kuota Dibatalkan Resmi)
                    </div>
                    <div style="font-size: 0.825rem; margin-top: 0.25rem;">
                        Diselesaikan pada: <strong>{{ $po->closed_at ? \Carbon\Carbon::parse($po->closed_at)->format('d/m/Y H:i') : '-' }}</strong> &bull; Oleh: <strong>{{ $po->closed_by ?? '-' }}</strong>
                    </div>
                    @if ($po->closed_reason)
                        <div style="margin-top: 0.35rem; font-size: 0.825rem; background: #ffffff; padding: 0.35rem 0.65rem; border-radius: 4px; border: 1px solid #bbf7d0;">
                            <strong>Alasan:</strong> {{ $po->closed_reason }}
                        </div>
                    @endif
                </div>
                <div>
                    <a href="{{ route('gudang.po.berita_acara', $po->po_id) }}" target="_blank" class="btn-pill-outline" style="color: #dc2626; border-color: #fca5a5;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Cetak Berita Acara</span>
                    </a>
                </div>
            </div>
        @elseif ($po->status_cd == 'PENDING_APPROVAL')
            <div class="po-alert-banner po-alert-warning">
                <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #92400e;">Menunggu Persetujuan Atasan (Approver)</h4>
                        <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: #78350f;">
                            Dokumen PO ini telah dialamatkan kepada <strong>{{ $po->approver?->name ?? 'Atasan Penyetuju' }}</strong>
                            @if ($po->approver?->email) ({{ $po->approver->email }}) @endif
                            untuk diverifikasi via email resmi sebelum dapat diteruskan ke supplier.
                        </p>
                    </div>
                </div>
                <form action="{{ route('gudang.po.resend_approval', $po->po_id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-pill-dark" style="background: #92400e; border-color: #92400e;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Kirim Email Approval</span>
                    </button>
                </form>
            </div>
        @elseif ($po->status_cd == 'REVISION')
            <div class="po-alert-banner po-alert-info">
                <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #075985;">Permintaan Revisi dari Atasan</h4>
                        <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: #0369a1;">
                            Atasan meminta penyesuaian dokumen. Silakan periksa catatan revisi dan perbarui data PO.
                        </p>
                    </div>
                </div>
                <a href="{{ route('gudang.po.edit', $po->po_id) }}" class="btn-pill-dark" style="background: #0284c7; border-color: #0284c7;">
                    <span>Edit PO Sekarang</span>
                </a>
            </div>
        @elseif ($po->status_cd == 'REJECTED')
            <div class="po-alert-banner po-alert-danger">
                <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #991b1b;">Dokumen PO Ditolak</h4>
                        <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: #b91c1c;">
                            Permohonan Purchase Order ditolak oleh Atasan. Dokumen tidak dapat diproses lebih lanjut.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- =====================================================================
             3. KPI METRICS CARDS (HARMONIZED 4 CARDS)
             ===================================================================== --}}
        @php
            $totalPesan = (float) $po->details->sum('pesan_qty');
            $totalTerima = (float) $po->details->sum('terima_qty');
            $pct = $totalPesan > 0 ? round(($totalTerima / $totalPesan) * 100, 1) : 0;
            $totalSisa = (float) $po->total_sisa_qty;

            $totalSubtotalItems = (float) $po->details->sum(function ($d) {
                $subTagihan = (float) ($d->subtotal_tagihan ?? 0);
                $subNominal = (float) ($d->subtotal_nominal ?? 0);
                $subNetto = (float) ($d->subtotal_netto ?? 0);
                $calc = (float) $d->pesan_qty * (float) $d->harga_nominal;
                return $subTagihan > 0 ? $subTagihan : ($subNominal > 0 ? $subNominal : ($subNetto > 0 ? $subNetto : $calc));
            });

            $totalTagihanHdr = (float) ($po->total_tagihan ?? 0);
            $totalNominalHdr = (float) ($po->total_nominal ?? 0);
            $displayTotalPO = $totalTagihanHdr > 0
                ? $totalTagihanHdr
                : ($totalNominalHdr > 0 ? $totalNominalHdr : $totalSubtotalItems);
        @endphp

        <div class="po-metrics-grid">
            {{-- KPI 1: Total Nilai Tagihan --}}
            <div class="po-metric-card">
                <div class="po-metric-top">
                    <div class="po-metric-header-left">
                        <span class="po-metric-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="po-metric-label">Total Nilai Tagihan</span>
                    </div>
                    <span class="po-metric-dots">&bull;&bull;&bull;</span>
                </div>
                <div class="po-metric-value">Rp {{ number_format($displayTotalPO, 0, ',', '.') }}</div>
                <div class="po-metric-footer">{{ $po->details->count() }} item bahan &bull; {{ number_format($totalPesan, 0) }} kuantitas</div>
            </div>

            {{-- KPI 2: Realisasi Penerimaan --}}
            <div class="po-metric-card">
                <div class="po-metric-top">
                    <div class="po-metric-header-left">
                        <span class="po-metric-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="po-metric-label">Realisasi Penerimaan</span>
                    </div>
                    <span class="badge" style="font-size: 0.725rem; font-weight: 700; background: {{ $pct >= 100 ? '#ecfdf5' : ($pct > 0 ? '#fffbeb' : '#f1f5f9') }}; color: {{ $pct >= 100 ? '#059669' : ($pct > 0 ? '#b45309' : '#64748b') }}; padding: 2px 7px; border-radius: 9999px;">{{ $pct }}%</span>
                </div>
                <div class="po-metric-value" style="color: {{ $pct >= 100 ? '#059669' : '#0f172a' }};">Rp {{ number_format((float) $po->realisasi_nominal, 0, ',', '.') }}</div>
                <div class="po-metric-footer">
                    <div class="po-progress-bar" style="margin-bottom: 4px;">
                        <div class="po-progress-fill" style="width: {{ min(100, $pct) }}%; background: {{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#0f172a' : '#cbd5e1') }};"></div>
                    </div>
                    <span>Diterima fisik: {{ number_format($totalTerima, 0) }} unit</span>
                </div>
            </div>

            {{-- KPI 3: Sisa Kuota Belum Tiba / Dibatalkan --}}
            <div class="po-metric-card">
                <div class="po-metric-top">
                    <div class="po-metric-header-left">
                        <span class="po-metric-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM4 17h1m4 0h6m4 0h1m-1-4V6a1 1 0 00-1-1H4a1 1 0 00-1 1v7m14 0h3l2 3v1a1 1 0 01-1 1h-1m-17 0H3a1 1 0 01-1-1v-1l2-3h12"/>
                            </svg>
                        </span>
                        <span class="po-metric-label">{{ !empty($po->closed_at) ? 'Sisa Batal Resmi' : 'Sisa Belum Tiba' }}</span>
                    </div>
                    <span class="po-metric-dots">&bull;&bull;&bull;</span>
                </div>
                <div class="po-metric-value" style="color: {{ !empty($po->closed_at) ? '#dc2626' : ($totalSisa > 0 ? '#b45309' : '#059669') }};">{{ number_format($totalSisa, 2) }}</div>
                <div class="po-metric-footer">
                    @if (!empty($po->closed_at))
                        <span style="color: #dc2626; font-weight: 600;">Arsip ditutup</span> &bull; Kuota dibatalkan
                    @elseif ($totalSisa > 0)
                        Menunggu pengiriman supplier
                    @else
                        Lengkap seluruhnya (100%)
                    @endif
                </div>
            </div>

            {{-- KPI 4: Tanggal PO & Estimasi Tiba --}}
            <div class="po-metric-card">
                <div class="po-metric-top">
                    <div class="po-metric-header-left">
                        <span class="po-metric-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <span class="po-metric-label">Jadwal &amp; ETA</span>
                    </div>
                    <span class="po-metric-dots">&bull;&bull;&bull;</span>
                </div>
                <div class="po-metric-value" style="font-size: 1.25rem;">{{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}</div>
                <div class="po-metric-footer">
                    Estimasi tiba:
                    @if ($po->tgl_estimasi_datang)
                        <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}</strong>
                    @else
                        <span>-</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- =====================================================================
             4. WORKSPACE GRID (2 COLUMNS: LEFT DETAILED CARDS, RIGHT SIDEBAR)
             ===================================================================== --}}
        <div class="po-workspace-grid">

            {{-- LEFT MAIN COLUMN --}}
            <div class="po-main-column">

                {{-- CARD 1: RINCIAN ITEM BARANG YANG DIPESAN --}}
                <div class="po-card">
                    <div class="po-card-header">
                        <h3 class="po-card-title">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Rincian Barang yang Dipesan</span>
                            <span class="po-card-subtitle">({{ $po->details->count() }} Item Bahan)</span>
                        </h3>
                    </div>

                    <div class="po-table-responsive">
                        <table class="po-table">
                            <thead>
                                <tr>
                                    <th style="width: 36px; text-align: center;">No</th>
                                    <th>Nama Komoditas / Bahan Baku</th>
                                    <th>Satuan</th>
                                    <th style="text-align: right;">Pesan</th>
                                    <th style="text-align: right;">Diterima</th>
                                    <th style="text-align: right;">{{ !empty($po->closed_at) ? 'Sisa Dibatalkan' : 'Sisa' }}</th>
                                    <th style="text-align: right;">Harga Satuan</th>
                                    <th style="text-align: right;">Diskon</th>
                                    <th style="text-align: right;">Potongan</th>
                                    <th style="text-align: center;">Pajak</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($po->details as $index => $item)
                                    @php
                                        $diskonPct = (float) ($item->diskon_persen ?? 0);
                                        $potNom = (float) ($item->potongan_nominal ?? 0);
                                        $isPpn = ($item->ppn_tipe ?? '') === 'PPN_11';

                                        $subTagihan = (float) ($item->subtotal_tagihan ?? 0);
                                        $subNominal = (float) ($item->subtotal_nominal ?? 0);
                                        $subNetto = (float) ($item->subtotal_netto ?? 0);
                                        $calcManual = (float) $item->pesan_qty * (float) $item->harga_nominal;

                                        $subtotalRow = $subTagihan > 0 ? $subTagihan : ($subNominal > 0 ? $subNominal : ($subNetto > 0 ? $subNetto : $calcManual));

                                        $taxLabel = $item->pajak?->pajak_nm ?? ($isPpn ? 'PPN 11%' : 'Non-Pajak');
                                        $taxCode = $item->pajak?->pajak_cd ?? ($item->ppn_tipe ?? ($isPpn ? 'PPN 11%' : 'Non-Pajak'));
                                        if ($item->pajak?->sifat_pajak === 'POTONG' || str_contains(strtoupper($taxCode), 'PPH')) {
                                            $taxCode = \App\Models\Gudang\DatPoHdr::formatShortPphName($taxCode);
                                        } elseif (strlen($taxCode) > 12) {
                                            $taxCode = substr($taxCode, 0, 12);
                                        }
                                        $taxTarif = $item->pajak ? (float) $item->pajak->tarif_persen : ($isPpn ? 11 : 0);
                                        $taxInc = (bool) ($item->is_harga_termasuk_pajak ?? $po->is_harga_termasuk_pajak);
                                    @endphp
                                    <tr>
                                        <td style="text-align: center; color: var(--po-slate-400);">{{ $index + 1 }}</td>
                                        <td>
                                            <strong style="color: var(--po-slate-900); display: block;">{{ $item->barang?->barang_nm }}</strong>
                                            <span style="font-size: 0.725rem; color: var(--po-slate-500); font-family: var(--po-font-mono);">{{ $item->barang?->barang_cd }}</span>
                                        </td>
                                        <td style="color: var(--po-slate-600);">
                                            {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                        </td>
                                        <td style="text-align: right; font-weight: 700;">
                                            {{ number_format((float) $item->pesan_qty, 2) }}
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: var(--po-emerald);">
                                            {{ number_format((float) $item->terima_qty, 2) }}
                                        </td>
                                        <td style="text-align: right; font-weight: 700;">
                                            @if (!empty($po->closed_at) && (float) $item->sisa_qty > 0)
                                                <span style="color: var(--po-rose);">{{ number_format((float) $item->sisa_qty, 2) }}</span>
                                                <span class="po-badge po-badge-rejected" style="display: block; margin-top: 2px;">Batal</span>
                                            @else
                                                <span style="color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                                                    {{ number_format((float) $item->sisa_qty, 2) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;" class="text-mono">
                                            Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                                        </td>
                                        <td style="text-align: right; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }};" class="text-mono">
                                            {{ $diskonPct > 0 ? number_format($diskonPct, 1, ',', '.') . '%' : '-' }}
                                        </td>
                                        <td style="text-align: right; color: {{ $potNom > 0 ? '#dc2626' : '#94a3b8' }};" class="text-mono">
                                            {{ $potNom > 0 ? 'Rp ' . number_format($potNom, 0, ',', '.') : '-' }}
                                        </td>
                                        <td style="text-align: center; white-space: nowrap;">
                                            @if ($taxTarif > 0)
                                                @php $isPotongRow = ($item->pajak?->sifat_pajak === 'POTONG'); @endphp
                                                <span class="po-badge po-badge-tax {{ $taxInc ? 'po-badge-tax-inc' : '' }}" title="{{ $item->pajak?->pajak_nm }}">
                                                    {{ $taxCode }}
                                                </span>
                                                <div style="font-size: 0.675rem; color: {{ $isPotongRow ? '#dc2626' : ($taxInc ? '#15803d' : '#64748b') }}; font-weight: 600; margin-top: 1px;">
                                                    {{ $isPotongRow ? "-{$taxTarif}% (Potong)" : "{$taxTarif}% (" . ($taxInc ? 'Inc' : 'Exc') . ")" }}
                                                </div>
                                            @else
                                                <span class="po-badge po-badge-draft">Non-Pajak</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: var(--po-slate-900);" class="text-mono">
                                            Rp {{ number_format($subtotalRow, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right; color: var(--po-slate-600);">
                                        Total:
                                    </td>
                                    <td style="text-align: right;">{{ number_format($totalPesan, 2) }}</td>
                                    <td style="text-align: right; color: var(--po-emerald);">{{ number_format($totalTerima, 2) }}</td>
                                    <td style="text-align: right; color: {{ !empty($po->closed_at) ? '#dc2626' : ($totalSisa > 0 ? '#b45309' : '#64748b') }};">
                                        {{ number_format($totalSisa, 2) }}
                                    </td>
                                    <td colspan="4"></td>
                                    <td style="text-align: right; font-size: 0.95rem;" class="text-mono">
                                        Rp {{ number_format($displayTotalPO, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- FINANCIAL BREAKDOWN SUMMARY --}}
                    @php
                        $firstTaxItem = $po->details->first(fn($d) => !empty($d->pajak) || !empty($d->ppn_tipe));
                        $taxSifatGlobal = $firstTaxItem?->pajak?->sifat_pajak ?? 'TAMBAH';
                        $isPotongGlobal = ($taxSifatGlobal === 'POTONG');
                        $taxCdGlobal = $firstTaxItem?->pajak?->pajak_cd ?? ($firstTaxItem?->ppn_tipe ?? 'Pajak');
                        if ($isPotongGlobal || str_contains(strtoupper($taxCdGlobal), 'PPH')) {
                            $taxCdGlobal = \App\Models\Gudang\DatPoHdr::formatShortPphName($taxCdGlobal);
                        }

                        $calcSubtotalBruto = (float) ($po->subtotal_bruto ?? 0) > 0
                            ? (float) $po->subtotal_bruto
                            : (float) $po->details->sum(fn($d) => (float) $d->pesan_qty * (float) $d->harga_nominal);

                        if ($calcSubtotalBruto <= 0 && $displayTotalPO > 0) {
                            $calcSubtotalBruto = $displayTotalPO;
                        }

                        $calcDiskonItem = (float) ($po->diskon_total ?? 0) > 0
                            ? (float) $po->diskon_total
                            : (float) $po->details->sum(fn($d) => (float) $d->pesan_qty * (float) ($d->diskon_nominal ?? 0));

                        $calcPotongan = (float) ($po->potongan_nominal ?? 0) > 0
                            ? (float) $po->potongan_nominal
                            : (float) $po->details->sum(fn($d) => (float) ($d->potongan_nominal ?? 0));

                        $calcDpp = (float) ($po->dpp_nominal ?? 0) > 0
                            ? (float) $po->dpp_nominal
                            : max(0, $calcSubtotalBruto - $calcDiskonItem - $calcPotongan);

                        $calcPpn = (float) ($po->ppn_nominal ?? 0) > 0
                            ? (float) $po->ppn_nominal
                            : (float) $po->details->sum(fn($d) => (float) ($d->ppn_nominal ?? 0));

                        $grandTotalTagihan = $totalTagihanHdr > 0
                            ? $totalTagihanHdr
                            : ($totalNominalHdr > 0 ? $totalNominalHdr : ($isPotongGlobal ? max(0, $calcDpp - $calcPpn) : ($calcDpp + $calcPpn)));

                        if ($grandTotalTagihan <= 0 && $displayTotalPO > 0) {
                            $grandTotalTagihan = $displayTotalPO;
                        }
                    @endphp

                    <div class="po-financial-breakdown">
                        <div class="po-breakdown-box">
                            <div class="po-breakdown-row">
                                <span>Subtotal Nilai Bruto:</span>
                                <strong>Rp {{ number_format($calcSubtotalBruto, 0, ',', '.') }}</strong>
                            </div>

                            @if ($calcDiskonItem > 0)
                                <div class="po-breakdown-row" style="color: #d97706;">
                                    <span>Akumulasi Diskon Item:</span>
                                    <strong>- Rp {{ number_format($calcDiskonItem, 0, ',', '.') }}</strong>
                                </div>
                            @endif

                            @if ($calcPotongan > 0)
                                <div class="po-breakdown-row" style="color: #dc2626;">
                                    <span>Potongan Harga Langsung:</span>
                                    <strong>- Rp {{ number_format($calcPotongan, 0, ',', '.') }}</strong>
                                </div>
                            @endif

                            <div class="po-breakdown-divider"></div>

                            <div class="po-breakdown-row">
                                <span>Dasar Pengenaan Pajak (DPP):</span>
                                <strong>Rp {{ number_format($calcDpp, 0, ',', '.') }}</strong>
                            </div>

                            @if ($calcPpn > 0)
                                @if ($isPotongGlobal)
                                    <div class="po-breakdown-row" style="color: #dc2626;">
                                        <span>Pemotongan Pajak ({{ $taxCdGlobal }}):</span>
                                        <strong>- Rp {{ number_format($calcPpn, 0, ',', '.') }}</strong>
                                    </div>
                                @elseif ($po->is_harga_termasuk_pajak)
                                    <div class="po-breakdown-row" style="color: #15803d;">
                                        <span>Pajak (Termasuk Gross):</span>
                                        <strong>Rp {{ number_format($calcPpn, 0, ',', '.') }}</strong>
                                    </div>
                                @else
                                    <div class="po-breakdown-row" style="color: #0284c7;">
                                        <span>Pajak Tambahan:</span>
                                        <strong>+ Rp {{ number_format($calcPpn, 0, ',', '.') }}</strong>
                                    </div>
                                @endif
                            @endif

                            <div class="po-breakdown-grand-total">
                                <span>Total Tagihan PO:</span>
                                <strong>Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: RIWAYAT KEDATANGAN BARANG (SURAT JALAN / GRN) --}}
                <div class="po-card">
                    <div class="po-card-header">
                        <h3 class="po-card-title">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Riwayat Kedatangan Barang Fisik</span>
                            <span class="po-card-subtitle">({{ $po->penerimaan->count() }} Surat Jalan / Termin)</span>
                        </h3>
                    </div>

                    @if ($po->penerimaan->isNotEmpty())
                        <div class="po-table-responsive">
                            <table class="po-table">
                                <thead>
                                    <tr>
                                        <th>No Penerimaan</th>
                                        <th>Tanggal Tiba</th>
                                        <th>Ketepatan Jadwal</th>
                                        <th>Gudang Masuk</th>
                                        <th>Status</th>
                                        <th style="text-align: right; width: 110px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($po->penerimaan as $terima)
                                        @php
                                            $tglTerima = \Carbon\Carbon::parse($terima->terima_tgl);
                                            $eta = $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang) : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;" class="text-mono">
                                                    {{ $terima->terima_no }}
                                                </a>
                                            </td>
                                            <td>{{ $tglTerima->format('d/m/Y') }}</td>
                                            <td>
                                                @if ($eta)
                                                    @if ($tglTerima->lt($eta))
                                                        <span class="po-badge po-badge-completed">Lebih Cepat</span>
                                                    @elseif ($tglTerima->equalTo($eta))
                                                        <span class="po-badge po-badge-approved">Tepat Waktu</span>
                                                    @else
                                                        <span class="po-badge po-badge-rejected">Terlambat</span>
                                                    @endif
                                                @else
                                                    <span style="color: #94a3b8;">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $terima->gudang?->gudang_nm ?? '-' }}</td>
                                            <td><span class="po-badge po-badge-completed">Diterima Fisik</span></td>
                                            <td style="text-align: right;">
                                                <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn-pill-outline" style="padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                                                    Lihat SJ
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 2.25rem 1rem; color: #94a3b8;">
                            <div style="width: 38px; height: 38px; margin: 0 auto 0.65rem; border-radius: 50%; background: #f8fafc; border: 1.5px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <strong style="color: #64748b; font-size: 0.875rem; display: block;">Belum ada riwayat kedatangan barang</strong>
                            <p style="font-size: 0.775rem; margin-top: 0.2rem; margin-bottom: 0;">
                                Barang belum pernah dicatat masuk. Saat armada supplier tiba di pabrik, catat penerimaan melalui menu Aksi.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- CARD 3: RIWAYAT INSPEKSI MUTU QC INBOUND --}}
                <div class="po-card">
                    <div class="po-card-header">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <h3 class="po-card-title">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Hasil Uji Mutu &amp; Inspeksi QC Inbound</span>
                                <span class="po-card-subtitle">({{ $po->qcInbound->count() }} Tiket QC)</span>
                            </h3>
                        </div>
                        <a href="{{ route('qc.inbound.index') }}" class="btn-pill-outline" style="padding: 0.25rem 0.65rem; font-size: 0.725rem;">
                            Antrean QC &rarr;
                        </a>
                    </div>

                    @if ($po->qcInbound->isNotEmpty())
                        <div class="po-table-responsive">
                            <table class="po-table">
                                <thead>
                                    <tr>
                                        <th>No. Tiket QC &amp; Tgl</th>
                                        <th>Armada &amp; Supir</th>
                                        <th>Komoditas</th>
                                        <th style="text-align: right;">Netto Lolos</th>
                                        <th style="text-align: center;">Keputusan Mutu</th>
                                        <th style="text-align: center;">Status GRN</th>
                                        <th style="text-align: right; width: 130px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($po->qcInbound as $qc)
                                        @php
                                            $firstDtl = $qc->details->first();
                                            $tglPeriksa = $qc->tgl_periksa ? \Carbon\Carbon::parse($qc->tgl_periksa) : null;
                                            $nettoTotal = (float) $qc->details->sum('qty_netto_lolos');
                                            $satuanNm = $firstDtl?->barang?->satuanDasar?->satuan_nm ?? 'kg';
                                            $terimaLinked = $qc->terima;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;" class="text-mono">
                                                    {{ $qc->qc_no }}
                                                </a>
                                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 1px;">
                                                    {{ $tglPeriksa ? $tglPeriksa->format('d/m/Y H:i') : '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <strong style="color: var(--po-slate-900);">{{ $qc->plat_nomor_truk ?: '-' }}</strong>
                                                <div style="font-size: 0.725rem; color: #64748b;">{{ $qc->sopir_nama ?: 'Sopir -' }}</div>
                                            </td>
                                            <td>
                                                <strong style="color: var(--po-slate-800);">{{ $firstDtl?->barang?->barang_nm ?: ($qc->nama_jenis ?: '-') }}</strong>
                                            </td>
                                            <td style="text-align: right; font-weight: 700; color: var(--po-emerald);" class="text-mono">
                                                {{ number_format($nettoTotal, 2, ',', '.') }} {{ $satuanNm }}
                                            </td>
                                            <td style="text-align: center;">
                                                @if ($qc->status_qc === 'DITOLAK_TOTAL' || $qc->status_qc === 'DITOLAK')
                                                    <span class="po-badge po-badge-rejected">Ditolak QC</span>
                                                @elseif ($qc->status_qc === 'DITERIMA_PARSIAL')
                                                    <span class="po-badge po-badge-pending">Lolos Bersyarat</span>
                                                @else
                                                    <span class="po-badge po-badge-completed">Lulus Uji Mutu</span>
                                                @endif
                                            </td>
                                            <td style="text-align: center;">
                                                @if ($terimaLinked)
                                                    <span class="po-badge po-badge-approved">Sudah GRN</span>
                                                @else
                                                    <span class="po-badge po-badge-draft">Menunggu GRN</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: flex; gap: 0.35rem; justify-content: flex-end;">
                                                    <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="btn-pill-outline" style="padding: 0.2rem 0.5rem; font-size: 0.725rem;">
                                                        Detail
                                                    </a>
                                                    <a href="{{ route('gudang.qc.haccp_cetak', $qc->qc_id) }}" target="_blank" class="btn-pill-outline" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; color: #0284c7; border-color: #bae6fd;">
                                                        HACCP
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 2.25rem 1rem; color: #94a3b8;">
                            <div style="width: 38px; height: 38px; margin: 0 auto 0.65rem; border-radius: 50%; background: #f8fafc; border: 1.5px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <strong style="color: #64748b; font-size: 0.875rem; display: block;">Belum ada inspeksi mutu QC</strong>
                            <p style="font-size: 0.775rem; margin-top: 0.2rem; margin-bottom: 0;">
                                Rekaman uji mutu dan refraksi kadar air akan otomatis muncul saat truk tiba dan diuji di lapangan.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- CARD 4: RIWAYAT PERUBAHAN & AUDIT TRAIL PO --}}
                <div class="po-card">
                    <div class="po-card-header">
                        <h3 class="po-card-title">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Riwayat Perubahan &amp; Audit Trail</span>
                            <span class="po-card-subtitle">({{ $po->revisiLog->count() }} Catatan Revisi)</span>
                        </h3>
                    </div>

                    @if ($po->revisiLog->isNotEmpty())
                        <div class="po-table-responsive">
                            <table class="po-table">
                                <thead>
                                    <tr>
                                        <th style="width: 120px;">Versi &amp; Waktu</th>
                                        <th style="width: 160px;">Diedit Oleh</th>
                                        <th style="width: 140px;">Kategori</th>
                                        <th>Poin Perubahan (Diffs)</th>
                                        <th style="text-align: right; width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($po->revisiLog as $log)
                                        @php
                                            $tglRev = $log->revisi_tgl ? \Carbon\Carbon::parse($log->revisi_tgl) : null;
                                            $diffs = $log->perubahan_json ?? [];
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="po-badge po-badge-approved text-mono">{{ $log->revisi_no ?: 'REV' }}</span>
                                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                                    {{ $tglRev ? $tglRev->format('d/m/Y H:i') : '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <strong style="color: var(--po-slate-900);">{{ $log->user_nama ?: 'SYSTEM' }}</strong>
                                                <div style="font-size: 0.7rem; color: #64748b;">{{ $log->user_role ?: 'Operator' }}</div>
                                            </td>
                                            <td>
                                                <span class="po-badge po-badge-draft" style="font-size: 0.68rem;">
                                                    {{ str_replace('_', ' ', $log->kategori_perubahan ?: 'KOMPREHENSIF') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if (!empty($diffs) && is_array($diffs))
                                                    <div style="display: flex; flex-direction: column; gap: 3px;">
                                                        @foreach (array_slice($diffs, 0, 2) as $df)
                                                            <div style="font-size: 0.75rem; color: #334155;">
                                                                <strong>{{ $df['label'] ?? ($df['field'] ?? '') }}:</strong>
                                                                <span style="color: #dc2626; text-decoration: line-through;">{{ $df['sebelum'] ?? '-' }}</span>
                                                                &rarr;
                                                                <span style="color: #166534; font-weight: 700;">{{ $df['sesudah'] ?? '-' }}</span>
                                                            </div>
                                                        @endforeach
                                                        @if (count($diffs) > 2)
                                                            <small style="color: #0284c7; font-weight: 600;">+ {{ count($diffs) - 2 }} perubahan lainnya...</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span style="font-size: 0.775rem; color: #64748b;">{{ $log->ringkasan_perubahan ?: '-' }}</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <button type="button" class="btn-pill-outline" onclick='openPoRevisiModal(@json($log))' style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                                                    Rincian
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 2.25rem 1rem; color: #94a3b8;">
                            <div style="width: 38px; height: 38px; margin: 0 auto 0.65rem; border-radius: 50%; background: #f8fafc; border: 1.5px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <strong style="color: #64748b; font-size: 0.875rem; display: block;">Dokumen Masih Versi Orisinal</strong>
                            <p style="font-size: 0.775rem; margin-top: 0.2rem; margin-bottom: 0;">
                                PO ini belum pernah mengalami pengubahan data sejak pertama kali diterbitkan.
                            </p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- RIGHT STICKY SIDEBAR --}}
            <div class="po-side-column">

                {{-- PANEL 1: MITRA SUPPLIER --}}
                <div class="po-card">
                    <div class="po-card-header">
                        <h4 class="po-card-title" style="font-size: 0.85rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Mitra Supplier</span>
                        </h4>
                    </div>
                    <div class="po-card-body" style="font-size: 0.825rem; line-height: 1.45;">
                        <strong style="font-size: 0.95rem; color: var(--po-slate-900); display: block;">
                            {{ $po->supplier?->supplier_nm ?? '-' }}
                        </strong>
                        <span style="font-size: 0.725rem; color: var(--po-slate-500); display: block; margin-top: 2px;">
                            Kode: {{ $po->supplier?->supplier_cd }}
                        </span>

                        <div style="margin-top: 0.75rem; display: flex; flex-direction: column; gap: 0.45rem;">
                            <div>
                                <span style="font-size: 0.725rem; color: var(--po-slate-500); display: block;">Alamat:</span>
                                <span>{{ $po->supplier?->alamat_txt ?? '-' }}</span>
                            </div>
                            <div>
                                <span style="font-size: 0.725rem; color: var(--po-slate-500); display: block;">Kontak:</span>
                                <strong>{{ $po->supplier?->kontak_no ?? '-' }}</strong>
                            </div>
                            <div>
                                <span style="font-size: 0.725rem; color: var(--po-slate-500); display: block;">Term of Payment (TOP):</span>
                                <strong style="color: #0f172a;">
                                    {{ $po->term_of_payment ?: ($po->supplier?->top_hari ? 'Tempo ' . $po->supplier->top_hari . ' Hari (Transfer)' : 'Transfer') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PANEL 2: GUDANG TUJUAN MASUK --}}
                <div class="po-card">
                    <div class="po-card-header">
                        <h4 class="po-card-title" style="font-size: 0.85rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                            <span>Gudang Tujuan Masuk</span>
                        </h4>
                    </div>
                    <div class="po-card-body" style="font-size: 0.825rem; line-height: 1.45;">
                        <strong style="font-size: 0.95rem; color: var(--po-slate-900); display: block;">
                            {{ $po->gudang?->gudang_nm ?? '-' }}
                        </strong>
                        <span style="font-size: 0.725rem; color: var(--po-slate-500); display: block; margin-top: 2px;">
                            Kode: {{ $po->gudang?->gudang_cd }}
                        </span>
                        <div style="margin-top: 0.5rem;">
                            <span style="font-size: 0.725rem; color: var(--po-slate-500); display: block;">Lokasi Pabrik:</span>
                            <span>{{ $po->gudang?->alamat_txt ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- PANEL 3: INSTRUKSI & CATATAN PO --}}
                <div class="po-card">
                    <div class="po-card-header">
                        <h4 class="po-card-title" style="font-size: 0.85rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                            <span>Instruksi &amp; Catatan PO</span>
                        </h4>
                    </div>
                    <div class="po-card-body" style="font-size: 0.825rem; line-height: 1.45; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <span style="font-size: 0.725rem; font-weight: 700; color: var(--po-slate-500); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">
                                Catatan Dokumen (Note)
                            </span>
                            @if ($po->catatan_txt)
                                <p style="margin: 0; white-space: pre-line; color: var(--po-slate-900); font-weight: 600;">
                                    {{ $po->catatan_txt }}
                                </p>
                            @elseif ($po->tgl_estimasi_datang)
                                <p style="margin: 0; color: var(--po-slate-900);">
                                    *Mohon dikirim tanggal {{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->locale('id')->translatedFormat('d F Y') }}
                                </p>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Tidak ada catatan khusus.</span>
                            @endif
                        </div>

                        <div style="border-top: 1px dashed var(--po-slate-200); padding-top: 0.5rem;">
                            <span style="font-size: 0.725rem; font-weight: 700; color: var(--po-slate-500); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px;">
                                Catatan Tambahan (Remark)
                            </span>
                            @if ($po->remark_txt)
                                <p style="margin: 0; white-space: pre-line; color: var(--po-slate-700);">
                                    {{ $po->remark_txt }}
                                </p>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">-</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div> {{-- End .no-print --}}

    {{-- =====================================================================
         5. PRINT-ONLY FORMAT ASLI HACCP (UNTUK CETAK BROWSER RESMI)
         ===================================================================== --}}
    <div class="print-only">
        <table style="width: 100%; border: 2px solid #000000; border-collapse: collapse; margin-bottom: 25px;">
            <tr>
                <td style="width: 110px; text-align: center; padding: 6px 4px; border: 1px solid #000000; vertical-align: middle;">
                    <img src="{{ asset('images/logo.png') }}" style="width: 58px; height: auto; display: inline-block; margin: 0 auto;" alt="Logo Cap Payung">
                    <div style="font-size: 6.5pt; font-weight: 800; color: #dc2626; margin-top: 2px; letter-spacing: 0.04em; text-align: center; white-space: nowrap;">
                        ENAK - GURIH - LEZAT
                    </div>
                </td>
                <td style="text-align: center; padding: 8px 10px; border: 1px solid #000000; vertical-align: middle;">
                    <div style="font-size: 11pt; font-weight: 800; color: #000000; letter-spacing: 0.03em;">PT. MIRASA FOOD INDUSTRY</div>
                    <div style="font-size: 10pt; font-weight: 600; margin-top: 6px;">Form</div>
                    <div style="font-size: 12pt; font-weight: 800; margin-top: 1px;">Permintaan Barang</div>
                </td>
                <td style="width: 250px; padding: 0; border: 1px solid #000000; vertical-align: middle;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                        <tr>
                            <td style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Nomor dokumen</td>
                            <td style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">:</td>
                            <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                        </tr>
                        <tr>
                            <td style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Terbitan/Tgl</td>
                            <td style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">:</td>
                            <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">19-08-2021</td>
                        </tr>
                        <tr>
                            <td style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Revisi</td>
                            <td style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">:</td>
                            <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">00</td>
                        </tr>
                        <tr>
                            <td style="width: 100px; border-right: 1px solid #000000; font-weight: 600; padding: 4px 6px;">Halaman</td>
                            <td style="width: 8px; text-align: center; padding: 4px 0;">:</td>
                            <td style="font-weight: 700; padding: 4px 6px;">1 dari 1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding: 0;">
                    <table style="border-collapse: collapse; font-size: 10pt;">
                        <tr>
                            <td style="width: 32px; font-weight: 700; vertical-align: top;">No.</td>
                            <td style="width: 8px; vertical-align: top;"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700; vertical-align: top;">PO</td>
                            <td style="font-weight: 700; vertical-align: top;">:</td>
                            <td style="font-weight: 800; font-family: monospace; font-size: 10.5pt; padding-left: 4px;">{{ $po->po_no }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 20px; vertical-align: top;">
                    <div style="font-size: 10pt; font-weight: 700; margin-bottom: 2px;">Kepada Yth:</div>
                    <div style="font-size: 10.5pt; font-weight: 800;">{{ $po->supplier?->supplier_nm ?? '-' }}</div>
                    @if ($po->supplier?->alamat_txt)
                        <div style="font-size: 9pt; color: #334155; margin-top: 2px;">{{ $po->supplier->alamat_txt }}</div>
                    @endif
                    @if ($po->supplier?->telepon_no && $po->supplier?->telepon_no !== '-')
                        <div style="font-size: 9pt; color: #334155;">Telp: {{ $po->supplier->telepon_no }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-bottom: 22px;">
            <div style="font-size: 12.5pt; font-weight: 800; text-decoration: underline; letter-spacing: 0.05em;">SURAT PERMINTAAN BARANG</div>
            <div style="font-size: 11pt; font-weight: 800; letter-spacing: 0.05em; margin-top: 3px;">PURCHASE ORDER</div>
        </div>

        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000; margin-bottom: 30px;">
            <thead>
                <tr>
                    <th style="width: 45px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">NO</th>
                    <th style="border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: left; padding-left: 10px; background: #ffffff;">NAMA BARANG</th>
                    <th style="width: 140px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">JUMLAH</th>
                    <th style="width: 180px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->details as $idx => $d)
                    <tr>
                        <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; text-align: center; font-weight: 700;">{{ $idx + 1 }}.</td>
                        <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; padding-left: 10px;">
                            <strong style="color: #000000;">{{ $d->barang?->barang_nm ?? '-' }}</strong>
                        </td>
                        <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; text-align: center; font-weight: 800;">
                            {{ number_format((float) $d->pesan_qty, 0, ',', '.') }} {{ $d->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                        </td>
                        <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9pt; padding-left: 8px;">
                            {{ $d->catatan_txt ?: '-' }}
                        </td>
                    </tr>
                @endforeach

                @php $emptyRows = max(0, 3 - count($po->details)); @endphp
                @for ($i = 0; $i < $emptyRows; $i++)
                    <tr>
                        <td style="border: 1px solid #000000; padding: 7px 8px; text-align: center; color: transparent;">&nbsp;</td>
                        <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                        <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                        <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="width: 60%; vertical-align: top; padding: 0;">
                    <div style="font-size: 8.5pt; line-height: 1.35; color: #000000; margin-top: 5px;">
                        <div style="font-weight: 800; font-size: 9pt; margin-bottom: 2px;">NB.</div>
                        <div>Barang di kirim ke alamat:</div>
                        <div style="font-weight: 800; font-size: 9pt;">PT. MIRASA FOOD INDUSTRY</div>
                        <div>Magelang</div>
                        <div style="margin-top: 5px; font-size: 8pt; color: #334155; line-height: 1.35;">
                            Faktur Pajak a.n. <strong>PT. MIRASA FOOD INDUSTRY</strong> (NPWP 07.635.700.4-152.000)<br>
                            Pembayaran via BCA <strong>104-9173332</strong> a.n. PT. MIRASA FOOD INDUSTRY
                        </div>
                    </div>
                </td>
                <td style="width: 45%; text-align: center; vertical-align: top; padding: 0;">
                    <div style="font-size: 10pt;">
                        Magelang, {{ $po->approved_at ? \Carbon\Carbon::parse($po->approved_at)->translatedFormat('d F Y') : ($po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') : now()->translatedFormat('d F Y')) }}<br>
                        <strong>Mengetahui / Hormat Kami,</strong>
                    </div>

                    @if ($po->isApproved() || in_array($po->status_cd, ['APPROVED', 'PARTIAL', 'COMPLETED']))
                        @php
                            $approverUser = $po->approver;
                            if (!$approverUser && !empty($po->approved_by)) {
                                $approverUser = \App\Models\User::where('name', $po->approved_by)->first();
                            }
                            $sigFilename = $approverUser?->signature_path ?: 'ttd_stempel_1.png';
                            $sigUrl = file_exists(public_path('images/signatures/' . $sigFilename))
                                ? asset('images/signatures/' . $sigFilename)
                                : asset('images/signatures/ttd_stempel_1.png');
                        @endphp
                        <div style="margin: 4px auto; text-align: center; height: 80px;">
                            <img src="{{ $sigUrl }}" alt="Tanda Tangan & Stempel" style="height: 78px; max-width: 145px; object-fit: contain; display: inline-block;">
                        </div>
                        <div style="font-size: 10pt; font-weight: 800; text-decoration: underline;">
                            {{ $po->approved_by ?? $po->approver?->name ?? 'Habieb Soleh Yoenanto' }}
                        </div>
                        <div style="font-size: 8pt; color: #475569;">
                            PT Mirasa Food Industry
                        </div>
                    @else
                        <div style="height: 80px;"></div>
                        <div style="font-size: 10pt; color: #94a3b8;">
                            (Menunggu Persetujuan Atasan)
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- =====================================================================
         6. MODALS PARTIALS
         ===================================================================== --}}
    @include('gudang.po.partials.modal-force-close')
    @include('gudang.po.partials.modal-haccp-po')
    @include('gudang.po.partials.modal-kirim-supplier')
    @include('gudang.po.partials.modal-revisi-po')
    @include('gudang.po.partials.modal-cancel-delete')

</div>
@endsection

@push('scripts')
    <script>
        window.poConfig = {
            baseUrl: "{{ url('gudang/po') }}"
        };
    </script>
    <script src="{{ asset('js/gudang/po/po-show.js') }}?v={{ file_exists(public_path('js/gudang/po/po-show.js')) ? filemtime(public_path('js/gudang/po/po-show.js')) : time() }}"></script>
@endpush
