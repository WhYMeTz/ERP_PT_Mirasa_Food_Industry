@extends('layouts.app')

@section('title', 'Detail Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-show.css') }}">
@endpush

@section('content')
    {{-- TOP COMMAND HEADER --}}
    <div class="no-print"
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('gudang.po.index') }}"
                style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar PO
            </a>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $po->po_no }}</h1>
                @if ($po->status_cd == 'PENDING_APPROVAL')
                    <span class="badge"
                        style="background:#fef3c7; color:#b45309; border: 1px solid #fde68a; font-weight:700;">Menunggu Approval
                        Atasan</span>
                @elseif ($po->status_cd == 'REVISION')
                    <span class="badge"
                        style="background:#eff6ff; color:#1d4ed8; border: 1px solid #bfdbfe; font-weight:700;">🔄 Perlu Revisi
                        Atasan</span>
                @elseif ($po->status_cd == 'REJECTED')
                    <span class="badge"
                        style="background:#fee2e2; color:#b91c1c; border: 1px solid #fecaca; font-weight:700;">Ditolak
                        Atasan</span>
                @elseif ($po->status_cd == 'COMPLETED')
                    @if (!empty($po->closed_at))
                        <span class="badge"
                            style="background:#ecfdf5; color:#047857; border: 1px solid #a7f3d0; font-weight:700;">Selesai (Batal
                            Sisa {{ $po->persentase_terima }}%)</span>
                    @else
                        <span class="badge badge-success">Selesai (100%)</span>
                    @endif
                @elseif ($po->status_cd == 'PARTIAL')
                    <span class="badge badge-info">Sebagian Diterima ({{ $po->persentase_terima }}%)</span>
                @elseif ($po->status_cd == 'CLOSED')
                    <span class="badge"
                        style="background:#ecfdf5; color:#047857; border: 1px solid #a7f3d0; font-weight:700;">Selesai (Batal
                        Sisa {{ $po->persentase_terima }}%)</span>
                @elseif ($po->status_cd == 'APPROVED')
                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">Disetujui</span>
                @elseif ($po->status_cd == 'DRAFT')
                    <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
                @else
                    <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
                @endif

                @if ($po->isSentToSupplier())
                    <span class="badge"
                        style="background:#dbeafe; color:#1e40af; border: 1px solid #bfdbfe; font-weight:700; display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Terkirim ke Supplier
                    </span>
                @endif

                @if($po->is_harga_termasuk_pajak)
                    <span class="badge"
                        style="background:#dcfce7; color:#15803d; border: 1px solid #86efac; font-weight:700;">Harga Termasuk
                        PPN (Gross)</span>
                @else
                    <span class="badge" style="background:#f1f5f9; color:#475569; border: 1px solid #cbd5e1;">Belum Termasuk PPN
                        (Net)</span>
                @endif
            </div>
        </div>

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

        {{-- SINGLE UNIFIED ACTION DROPDOWN --}}
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <div style="position: relative; display: inline-block;">
                <button type="button" class="btn btn-secondary show-dropdown-trigger" onclick="toggleShowActionMenu(event)"
                    style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #1e293b; font-weight: 700; font-size: 0.85rem; padding: 0.5rem 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    <span>Menu Aksi PO</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- DROPDOWN MENU ITEMS --}}
                <div id="showActionMenuDropdown" class="show-action-menu-dropdown" style="display: none;">

                    {{-- AKSI JIKA MENUNGGU VERIFIKASI: KIRIM ULANG EMAIL --}}
                    @if ($po->status_cd == 'PENDING_APPROVAL')
                        <form action="{{ route('gudang.po.resend_approval', $po->po_id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="action-dropdown-item"
                                title="Kirim ulang surat permohonan persetujuan ke email Atasan">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span style="font-weight: 700; color: #b45309;">Kirim Ulang Email Approval</span>
                            </button>
                        </form>
                        <div class="action-dropdown-divider"></div>
                    @endif

                    {{-- AKSI KIRIM KE SUPPLIER (JIKA SUDAH APPROVED) --}}
                    @if ($po->isApproved() || in_array($po->status_cd, ['APPROVED', 'PARTIAL', 'COMPLETED']))
                        <button type="button" class="action-dropdown-item"
                            onclick="toggleShowActionMenu(event); openKirimSupplierModal({{ json_encode($sendSupplierPayload) }})">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            <span style="font-weight: 700; color: #0284c7;">
                                {{ $po->isSentToSupplier() ? 'Kirim Ulang ke Supplier' : 'Kirim ke Supplier via Email' }}
                            </span>
                        </button>
                        <div class="action-dropdown-divider"></div>
                    @endif

                    {{-- 1. TERIMA BARANG (JIKA STATUS MEMUNGKINKAN: ARAHKAN KE FORM INBOUND RESMI) --}}
                    @if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']) && $po->total_sisa_qty > 0)
                        <a href="{{ route('gudang.terima.create', ['po_id' => $po->po_id]) }}" class="action-dropdown-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Catat Terima Barang</span>
                        </a>
                    @endif

                    {{-- 2. DOKUMEN FORM HACCP (POPUP) --}}
                    <button type="button" class="action-dropdown-item"
                        onclick="openPoHaccpModal({{ $po->po_id }}); toggleShowActionMenu(event);">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Dokumen Form HACCP (Popup)</span>
                    </button>

                    {{-- 3. CETAK PDF RESMI (HACCP) --}}
                    <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" class="action-dropdown-item">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Cetak PDF Resmi (HACCP)</span>
                    </a>

                    {{-- 3.1 CETAK BERITA ACARA PEMBATALAN SISA (JIKA ADA PEMBATALAN SISA) --}}
                    @if (!empty($po->closed_at))
                        <a href="{{ route('gudang.po.berita_acara', $po->po_id) }}" target="_blank"
                            class="action-dropdown-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span style="font-weight: 700; color: #dc2626;">Berita Acara Pembatalan Sisa</span>
                        </a>
                    @endif

                    {{-- 4. EDIT PO --}}
                    @php
                        $canEdit = (Auth::user()?->isSuperAdmin() || Auth::user()?->canEditPo()) && (!in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED']) || Auth::user()?->isSuperAdmin());
                    @endphp
                    @if ($canEdit)
                        <a href="{{ route('gudang.po.edit', $po->po_id) }}" class="action-dropdown-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Edit Purchase Order</span>
                        </a>
                    @endif

                    {{-- 5. BATALKAN SISA & SELESAIKAN PO --}}
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
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span style="font-weight: 700; color: #dc2626;">Batalkan Sisa &amp; Selesaikan PO</span>
                                    </button>
                    @endif

                    {{-- 6. BATALKAN PO --}}
                    @if (in_array($po->status_cd, ['DRAFT', 'APPROVED', 'PENDING_APPROVAL']) && $po->details->sum('terima_qty') == 0)
                        <div class="action-dropdown-divider"></div>
                        <form action="{{ route('gudang.po.cancel', $po->po_id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Purchase Order ini?')"
                            style="margin: 0;">
                            @csrf
                            <button type="submit" class="action-dropdown-item danger-item">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <span>Batalkan PO</span>
                            </button>
                        </form>
                    @endif

                    {{-- 7. HAPUS PO --}}
                    @php
                        $canDelete = (Auth::user()?->isSuperAdmin() || Auth::user()?->canDeletePo()) && ($po->details->sum('terima_qty') == 0 || Auth::user()?->isSuperAdmin());
                    @endphp
                    @if ($canDelete)
                        <div class="action-dropdown-divider"></div>
                        <form action="{{ route('gudang.po.destroy', $po->po_id) }}" method="POST"
                            onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS dokumen Purchase Order {{ $po->po_no }}? Tindakan ini tidak dapat dibatalkan.')"
                            style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-dropdown-item danger-item">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Hapus PO</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- BUNGKUS KONTEN DASHBOARD WEB DENGAN NO-PRINT AGAR TIDAK MUNCUL SAAT CETAK BROWSER --}}
        <div class="no-print">

            {{-- ALERT JIKA PO SUDAH DITUTUP / SELESAI BATAL SISA --}}
            @if ($po->status_cd == 'CLOSED' || (!empty($po->closed_at) && $po->status_cd == 'COMPLETED'))
                <div class="alert"
                    style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; color: #166534; margin-bottom: 1.25rem; padding: 0.875rem 1.25rem; border-radius: 6px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <div
                                style="font-weight: 700; color: #14532d; font-size: 0.95rem; display: flex; align-items: center; gap: 0.35rem;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Dokumen PO Telah Selesai (Sisa Kuota Dibatalkan Resmi)
                            </div>
                            <div style="font-size: 0.85rem; color: #166534; margin-top: 0.25rem;">
                                Diselesaikan pada:
                                <strong>{{ $po->closed_at ? \Carbon\Carbon::parse($po->closed_at)->format('d/m/Y H:i') : '-' }}</strong>
                                &bull; Oleh: <strong>{{ $po->closed_by ?? '-' }}</strong>
                            </div>
                            @if ($po->closed_reason)
                                <div
                                    style="margin-top: 0.35rem; font-size: 0.85rem; color: #166534; background: #ffffff; padding: 0.4rem 0.65rem; border-radius: 4px; border: 1px solid #bbf7d0;">
                                    <strong>Alasan Pembatalan Sisa:</strong> {{ $po->closed_reason }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('gudang.po.berita_acara', $po->po_id) }}" target="_blank" class="btn btn-sm"
                                style="background: #ffffff; color: #dc2626; border: 1.5px solid #fca5a5; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); text-decoration: none;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Cetak Berita Acara (A4)</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- KARTU STATUS APPROVAL & DISPATCH SUPPLIER --}}
            @if ($po->status_cd == 'PENDING_APPROVAL')
                <div
                    style="background: #fffbeb; border: 1.5px solid #fef3c7; border-left: 5px solid #f59e0b; border-radius: 8px; padding: 1.15rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                            <div
                                style="width: 40px; height: 40px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #92400e;">Menunggu Persetujuan
                                    Atasan (Approver)</h3>
                                <p style="margin: 0.25rem 0 0; font-size: 0.85rem; color: #78350f;">
                                    Dokumen PO ini telah dialamatkan kepada
                                    <strong>{{ $po->approver?->name ?? 'Atasan Penyetuju' }}</strong>
                                    @if ($po->approver?->email)
                                        ({{ $po->approver->email }})
                                    @endif
                                    untuk diverifikasi via email resmi sebelum dapat diteruskan ke supplier.
                                </p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                            <form action="{{ route('gudang.po.resend_approval', $po->po_id) }}" method="POST"
                                style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm"
                                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; font-size: 0.825rem;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>Kirim Ulang Email Approval</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif ($po->status_cd == 'REVISION')
                <div
                    style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-left: 5px solid #2563eb; border-radius: 8px; padding: 1.15rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                            <div
                                style="width: 40px; height: 40px; border-radius: 8px; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e40af;">Dokumen PO
                                        Memerlukan Revisi dari Atasan</h3>
                                    <span class="badge"
                                        style="background: #2563eb; color: #ffffff; font-size: 0.7rem; font-weight: 700;">PERLU
                                        PENYESUAIAN</span>
                                </div>
                                <p style="margin: 0.25rem 0 0; font-size: 0.85rem; color: #1d4ed8;">
                                    Instruksi dari:
                                    <strong>{{ $po->rejected_by ?? ($po->approver?->name ?? 'Atasan Peninjau') }}</strong>
                                    pada
                                    {{ $po->rejected_at ? \Carbon\Carbon::parse($po->rejected_at)->translatedFormat('d F Y H:i') : '-' }}.
                                </p>
                                <div
                                    style="margin-top: 0.5rem; font-size: 0.875rem; background: #ffffff; padding: 0.65rem 0.85rem; border-radius: 6px; border: 1px solid #bfdbfe; color: #1e3a8a;">
                                    <strong>Catatan / Poin Koreksi Atasan:</strong><br>
                                    <span
                                        style="font-weight: 600; display: inline-block; margin-top: 2px;">&ldquo;{{ $po->reject_reason ?? 'Mohon periksa kembali kuantitas dan harga satuan barang yang diajukan.' }}&rdquo;</span>
                                </div>
                            </div>
                        </div>
                        @if (Auth::user()?->isSuperAdmin() || Auth::user()?->canEditPo())
                            <a href="{{ route('gudang.po.edit', $po->po_id) }}" class="btn btn-primary"
                                style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1rem; font-size: 0.85rem; background: #2563eb; border-color: #1d4ed8; box-shadow: 0 1px 3px rgba(37,99,235,0.3);">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Perbaiki &amp; Edit PO Sekarang</span>
                            </a>
                        @endif
                    </div>
                </div>
            @elseif ($po->status_cd == 'REJECTED')
                <div
                    style="background: #fef2f2; border: 1.5px solid #fee2e2; border-left: 5px solid #ef4444; border-radius: 8px; padding: 1.15rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                            <div
                                style="width: 40px; height: 40px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #991b1b;">Dokumen PO Ditolak
                                    oleh Atasan</h3>
                                <p style="margin: 0.25rem 0 0; font-size: 0.85rem; color: #7f1d1d;">
                                    Ditolak oleh: <strong>{{ $po->rejected_by ?? ($po->approver?->name ?? 'Atasan') }}</strong>
                                    pada
                                    {{ $po->rejected_at ? \Carbon\Carbon::parse($po->rejected_at)->format('d F Y H:i') : '-' }}.
                                </p>
                                <div
                                    style="margin-top: 0.4rem; font-size: 0.85rem; background: #ffffff; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fecaca; color: #991b1b;">
                                    <strong>Catatan Alasan:</strong>
                                    {{ $po->reject_reason ?? 'Tidak ada keterangan tambahan.' }}
                                </div>
                            </div>
                        </div>
                        @if (Auth::user()?->isSuperAdmin() || Auth::user()?->canEditPo())
                            <a href="{{ route('gudang.po.edit', $po->po_id) }}" class="btn btn-primary btn-sm"
                                style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; font-size: 0.825rem;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Revisi &amp; Edit Dokumen PO</span>
                            </a>
                        @endif
                    </div>
                </div>
            @elseif ($po->isApproved() || in_array($po->status_cd, ['APPROVED', 'PARTIAL', 'COMPLETED']))
                <div
                    style="background: #f0fdf4; border: 1.5px solid #dcfce7; border-left: 5px solid #10b981; border-radius: 8px; padding: 1.15rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                            <div
                                style="width: 40px; height: 40px; border-radius: 8px; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #065f46;">Dokumen PO Telah
                                        Disetujui Resmi (APPROVED)</h3>
                                    <span class="badge"
                                        style="background: #10b981; color: #ffffff; font-size: 0.7rem; font-weight: 700;">SAH
                                        DIVERIFIKASI</span>
                                </div>
                                <p style="margin: 0.25rem 0 0; font-size: 0.85rem; color: #047857;">
                                    Disetujui oleh:
                                    <strong>{{ $po->approved_by ?? ($po->approver?->name ?? 'Atasan Penyetuju') }}</strong>
                                    pada
                                    {{ $po->approved_at ? \Carbon\Carbon::parse($po->approved_at)->format('d F Y H:i') : '-' }}.
                                    @if ($po->approval_notes)
                                        &bull; <em>"{{ $po->approval_notes }}"</em>
                                    @endif
                                </p>

                                {{-- STATUS EMAIL KE SUPPLIER --}}
                                <div style="margin-top: 0.5rem; font-size: 0.8125rem;">
                                    @if ($po->isSentToSupplier())
                                        <span
                                            style="color: #1e40af; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; background: #dbeafe; padding: 2px 8px; border-radius: 4px;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Surat PO telah dikirim ke supplier ({{ $po->supplier_email_sent }}) pada
                                            {{ \Carbon\Carbon::parse($po->sent_to_supplier_at)->format('d/m/Y H:i') }}
                                        </span>
                                    @else
                                        <span style="color: #64748b; display: inline-flex; align-items: center; gap: 0.35rem;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            PO siap dikirimkan ke mitra supplier via email.
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                            @if ($po->approval_token)
                                <a href="{{ route('po.verify.qr', $po->approval_token) }}" target="_blank"
                                    class="btn btn-secondary btn-sm"
                                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; font-size: 0.825rem;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    <span>Sertifikat Verifikasi QR</span>
                                </a>
                            @endif
                            <button type="button" onclick="openKirimSupplierModal({{ json_encode($sendSupplierPayload) }})"
                                class="btn btn-primary btn-sm"
                                style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; font-size: 0.825rem; background: #0284c7; border-color: #0284c7;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span>{{ $po->isSentToSupplier() ? 'Kirim Ulang ke Supplier' : 'Kirim ke Supplier via Email' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 4 KARTU METRIK OPERASIONAL PO --}}
            @php
                $pct = $po->persentase_terima;
                $totalPesan = (float) $po->details->sum('pesan_qty');
                $totalTerima = (float) $po->details->sum('terima_qty');
                $totalSisa = (float) $po->total_sisa_qty;

                $totalSubtotalItems = (float) $po->details->sum(function ($d) {
                    $subTagihan = (float) ($d->subtotal_tagihan ?? 0);
                    $subNominal = (float) ($d->subtotal_nominal ?? 0);
                    $subNetto = (float) ($d->subtotal_netto ?? 0);
                    $calc = (float) $d->pesan_qty * (float) $d->harga_nominal;
                    if ($subTagihan > 0)
                        return $subTagihan;
                    if ($subNominal > 0)
                        return $subNominal;
                    if ($subNetto > 0)
                        return $subNetto;
                    return $calc;
                });

                $totalTagihanHdr = (float) ($po->total_tagihan ?? 0);
                $totalNominalHdr = (float) ($po->total_nominal ?? 0);
                $displayTotalPO = $totalTagihanHdr > 0
                    ? $totalTagihanHdr
                    : ($totalNominalHdr > 0 ? $totalNominalHdr : $totalSubtotalItems);
            @endphp
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                {{-- TOTAL NOMINAL PO --}}
                <div class="card"
                    style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <span
                        style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                        Total Nilai Pesanan
                    </span>
                    <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                        Rp {{ number_format($displayTotalPO, 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
                        {{ $po->details->count() }} item bahan baku &bull; {{ number_format($totalPesan, 0) }} total
                        kuantitas
                    </div>
                </div>

                {{-- REALISASI NOMINAL DITERIMA --}}
                <div class="card"
                    style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <span
                            style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                            Realisasi Penerimaan Barang
                        </span>
                        <span
                            style="font-size: 0.8rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : ($pct > 0 ? '#d97706' : '#64748b') }};">
                            {{ $pct }}%
                        </span>
                    </div>
                    <div
                        style="font-size: 1.35rem; font-weight: 700; color: {{ $pct >= 100 ? '#059669' : '#0f172a' }}; margin-top: 0.25rem;">
                        Rp {{ number_format((float) $po->realisasi_nominal, 0, ',', '.') }}
                    </div>
                    {{-- Progress Bar --}}
                    <div
                        style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.45rem;">
                        <div
                            style="width: {{ min(100, $pct) }}%; height: 100%; background: {{ $pct >= 100 ? '#10b981' : ($pct > 0 ? '#f59e0b' : '#cbd5e1') }};">
                        </div>
                    </div>
                </div>

                {{-- TOTAL SISA FISIK BELUM DATANG / DIBATALKAN --}}
                <div class="card"
                    style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <span
                        style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                        @if (!empty($po->closed_at))
                            Sisa Kuota Dibatalkan
                        @else
                            Sisa Fisik Belum Tiba
                        @endif
                    </span>
                    <div
                        style="font-size: 1.35rem; font-weight: 700; color: {{ !empty($po->closed_at) ? '#dc2626' : ($totalSisa > 0 ? '#b45309' : '#059669') }}; margin-top: 0.25rem;">
                        {{ number_format($totalSisa, 2) }}
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
                        @if (!empty($po->closed_at))
                            <strong style="color: #dc2626;">Dibatalkan resmi</strong> &bull; Sisa kuota tidak dapat dikirim lagi
                        @elseif ($totalSisa > 0)
                            Menunggu kiriman supplier berikutnya
                        @else
                            Seluruh barang telah diterima lengkap
                        @endif
                    </div>
                </div>

                {{-- JADWAL PO & ESTIMASI TIBA --}}
                <div class="card"
                    style="padding: 1rem 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <span
                        style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                        Jadwal &amp; Estimasi Tiba
                    </span>
                    <div style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                        {{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.35rem;">
                        Estimasi Tiba:
                        @if ($po->tgl_estimasi_datang)
                            <strong
                                style="color: #0284c7;">{{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}</strong>
                        @else
                            <span>-</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- MASTER-DETAIL WORKSPACE: 2 KOLOM (KIRI: RINCIAN & HISTORI, KANAN: SIDEBAR INFO MITRA) --}}
            <div
                style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 1.5rem; align-items: start; margin-bottom: 2rem;">
                {{-- KOLOM KIRI (KONTEN UTAMA) --}}
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                    {{-- KARTU 1: RINCIAN ITEM BARANG YANG DIPESAN --}}
                    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div class="card-header"
                            style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #0f172a; font-size: 0.95rem;">Rincian Barang yang Dipesan</strong>
                                <span
                                    style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">({{ $po->details->count() }}
                                    Item Bahan)</span>
                            </div>
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                        <th style="width: 40px; text-align: center;">No</th>
                                        <th>Nama Komoditas / Bahan Baku</th>
                                        <th>Satuan</th>
                                        <th style="text-align: right;">Pesan</th>
                                        <th style="text-align: right;">Diterima</th>
                                        <th style="text-align: right;">
                                            {{ !empty($po->closed_at) ? 'Sisa Dibatalkan' : 'Sisa' }}</th>
                                        <th style="text-align: right;">Harga Satuan</th>
                                        <th style="text-align: right;">Diskon</th>
                                        <th style="text-align: right;">Potongan</th>
                                        <th style="text-align: center;">Pajak (PPN)</th>
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

                                            if ($subTagihan > 0) {
                                                $subtotalRow = $subTagihan;
                                            } elseif ($subNominal > 0) {
                                                $subtotalRow = $subNominal;
                                            } elseif ($subNetto > 0) {
                                                $subtotalRow = $subNetto;
                                            } else {
                                                $subtotalRow = $calcManual;
                                            }

                                            $taxLabel = $item->pajak?->pajak_nm ?? ($isPpn ? 'PPN 11%' : 'Non-Pajak');
                                            $taxCode = $item->pajak?->pajak_cd ?? ($item->ppn_tipe ?? ($isPpn ? 'PPN 11%' : 'Non-Pajak'));
                                            if ($taxCode === 'PPH-22-KOMODITAS') {
                                                $taxCode = 'PPh 22';
                                            } elseif (strlen($taxCode) > 12) {
                                                $taxCode = substr($taxCode, 0, 12);
                                            }
                                            $taxTarif = $item->pajak ? (float) $item->pajak->tarif_persen : ($isPpn ? 11 : 0);
                                            $taxInc = (bool) ($item->is_harga_termasuk_pajak ?? $po->is_harga_termasuk_pajak);
                                        @endphp
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $index + 1 }}
                                            </td>
                                            <td>
                                                <strong
                                                    style="color: #0f172a; font-size: 0.875rem;">{{ $item->barang?->barang_nm }}</strong>
                                                <span
                                                    style="display: block; font-size: 0.725rem; color: #64748b; font-family: monospace;">{{ $item->barang?->barang_cd }}</span>
                                            </td>
                                            <td style="color: #475569; font-size: 0.85rem;">
                                                {{ $item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-') }}
                                            </td>
                                            <td style="text-align: right; font-weight: 600; font-size: 0.875rem;">
                                                {{ number_format((float) $item->pesan_qty, 2) }}
                                            </td>
                                            <td
                                                style="text-align: right; font-weight: 600; color: #059669; font-size: 0.875rem;">
                                                {{ number_format((float) $item->terima_qty, 2) }}
                                            </td>
                                            <td style="text-align: right; font-weight: 600; font-size: 0.875rem;">
                                                @if (!empty($po->closed_at) && (float) $item->sisa_qty > 0)
                                                    <span
                                                        style="color: #dc2626; font-weight: 700;">{{ number_format((float) $item->sisa_qty, 2) }}</span>
                                                    <span class="badge"
                                                        style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-size: 0.65rem; display: block; margin-top: 2px;">Batal
                                                        Sisa</span>
                                                @else
                                                    <span style="color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                                                        {{ number_format((float) $item->sisa_qty, 2) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td
                                                style="text-align: right; font-size: 0.85rem; color: #475569; font-family: monospace;">
                                                Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                                            </td>
                                            <td
                                                style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }};">
                                                {{ $diskonPct > 0 ? number_format($diskonPct, 1, ',', '.') . '%' : '-' }}
                                            </td>
                                            <td
                                                style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $potNom > 0 ? '#dc2626' : '#94a3b8' }};">
                                                {{ $potNom > 0 ? 'Rp ' . number_format($potNom, 0, ',', '.') : '-' }}
                                            </td>
                                            <td style="text-align: center; white-space: nowrap;">
                                                @if ($taxTarif > 0)
                                                    <span class="badge"
                                                        style="background:#eff6ff; color:#0284c7; border: 1px solid #bfdbfe; font-size:0.75rem; font-weight:800; font-family: monospace;"
                                                        title="{{ $item->pajak?->pajak_nm }}">
                                                        {{ $taxCode }}
                                                    </span>
                                                    <div style="font-size: 0.675rem; color: {{ $taxInc ? '#1e40af' : '#15803d' }}; font-weight: 600; margin-top: 1px;">
                                                        {{ $taxTarif }}% ({{ $taxInc ? 'Inc' : 'Exc' }})
                                                    </div>
                                                    @if ((float) ($item->ppn_nominal ?? 0) > 0)
                                                        <div
                                                            style="font-size: 0.7rem; color: #64748b; font-family: monospace; margin-top: 2px;">
                                                            Rp {{ number_format((float) $item->ppn_nominal, 0, ',', '.') }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="badge"
                                                        style="background:#f1f5f9; color:#64748b; font-size:0.7rem;">Non-Pajak</span>
                                                @endif
                                            </td>
                                            <td
                                                style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;">
                                                Rp {{ number_format($subtotalRow, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                                    <tr>
                                        <td colspan="3"
                                            style="text-align: right; padding: 0.75rem 1rem; color: #475569; font-size: 0.85rem;">
                                            Total Kuantitas &amp; Subtotal Keseluruhan:
                                        </td>
                                        <td
                                            style="text-align: right; padding: 0.75rem 0.5rem; color: #0f172a; font-size: 0.875rem;">
                                            {{ number_format($totalPesan, 2) }}
                                        </td>
                                        <td
                                            style="text-align: right; padding: 0.75rem 0.5rem; color: #059669; font-size: 0.875rem;">
                                            {{ number_format($totalTerima, 2) }}
                                        </td>
                                        <td
                                            style="text-align: right; padding: 0.75rem 0.5rem; color: {{ !empty($po->closed_at) ? '#dc2626' : ($totalSisa > 0 ? '#b45309' : '#64748b') }}; font-size: 0.875rem;">
                                            {{ number_format($totalSisa, 2) }}
                                            @if (!empty($po->closed_at) && $totalSisa > 0)
                                                <div style="font-size: 0.65rem; color: #dc2626; font-weight: 700;">(Batal Sisa)
                                                </div>
                                            @endif
                                        </td>
                                        <td colspan="4"></td>
                                        <td
                                            style="text-align: right; padding: 0.75rem 1rem; color: #0f172a; font-size: 1rem; font-family: monospace;">
                                            Rp {{ number_format($displayTotalPO, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- SUMMARY BREAKDOWN KEUANGAN PO: DISKON, POTONGAN, DPP & PPN --}}
                        @php
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
                                : ($totalNominalHdr > 0 ? $totalNominalHdr : ($calcDpp + $calcPpn));

                            if ($grandTotalTagihan <= 0 && $displayTotalPO > 0) {
                                $grandTotalTagihan = $displayTotalPO;
                            }
                        @endphp

                        <div
                            style="border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 1.25rem; display: flex; justify-content: flex-end;">
                            <div
                                style="width: 100%; max-width: 440px; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; color: #64748b;">
                                    <span>Subtotal Nilai Bruto:</span>
                                    <strong style="color: #0f172a; font-family: monospace;">Rp
                                        {{ number_format($calcSubtotalBruto, 0, ',', '.') }}</strong>
                                </div>

                                @if ($calcDiskonItem > 0)
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; color: #d97706;">
                                        <span>Akumulasi Diskon Item:</span>
                                        <strong style="font-family: monospace;">- Rp
                                            {{ number_format($calcDiskonItem, 0, ',', '.') }}</strong>
                                    </div>
                                @endif

                                @if ($calcPotongan > 0)
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; color: #dc2626;">
                                        <span>Potongan Harga Langsung:</span>
                                        <strong style="font-family: monospace;">- Rp
                                            {{ number_format($calcPotongan, 0, ',', '.') }}</strong>
                                    </div>
                                @endif

                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; color: #475569; padding-top: 0.35rem; border-top: 1px dashed #cbd5e1;">
                                    <span>Dasar Pengenaan Pajak (DPP):</span>
                                    <strong style="color: #0f172a; font-family: monospace;">Rp
                                        {{ number_format($calcDpp, 0, ',', '.') }}</strong>
                                </div>

                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; color: #0284c7;">
                                    <span>Pajak (PPN):</span>
                                    <strong style="font-family: monospace;">+ Rp
                                        {{ number_format($calcPpn, 0, ',', '.') }}</strong>
                                </div>

                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.65rem; border-top: 2px solid #0f172a; font-size: 1.05rem;">
                                    <span style="font-weight: 700; color: #0f172a;">Total Nilai PO Resmi:</span>
                                    <strong style="color: #0284c7; font-size: 1.2rem; font-family: monospace;">Rp
                                        {{ number_format($grandTotalTagihan, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU 2: RIWAYAT KEDATANGAN BARANG (SURAT JALAN / GOOD RECEIPTS) --}}
                    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div class="card-header"
                            style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #0f172a; font-size: 0.95rem;">Riwayat Kedatangan Barang Fisik</strong>
                                <span
                                    style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">({{ $po->penerimaan->count() }}
                                    Surat Jalan / Termin)</span>
                            </div>
                        </div>

                        @if ($po->penerimaan->isNotEmpty())
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                            <th>No Penerimaan</th>
                                            <th>Tanggal Tiba</th>
                                            <th>Ketepatan Jadwal</th>
                                            <th>Gudang Masuk</th>
                                            <th>Status</th>
                                            <th style="text-align: right; width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($po->penerimaan as $terima)
                                            @php
                                                $tglTerima = \Carbon\Carbon::parse($terima->terima_tgl);
                                                $eta = $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang) : null;
                                            @endphp
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td>
                                                    <a href="{{ route('gudang.terima.show', $terima->terima_id) }}"
                                                        style="font-weight: 700; color: #0284c7; text-decoration: none; font-size: 0.85rem;">
                                                        {{ $terima->terima_no }}
                                                    </a>
                                                </td>
                                                <td style="font-size: 0.85rem; color: #334155;">{{ $tglTerima->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    @if ($eta)
                                                        @if ($tglTerima->lt($eta))
                                                            <span class="badge"
                                                                style="background:#f0fdf4; color:#166534; font-size:0.75rem;">
                                                                Lebih Cepat
                                                            </span>
                                                        @elseif ($tglTerima->equalTo($eta))
                                                            <span class="badge"
                                                                style="background:#f0f9ff; color:#0369a1; font-size:0.75rem;">
                                                                Tepat Waktu
                                                            </span>
                                                        @else
                                                            <span class="badge"
                                                                style="background:#fef2f2; color:#991b1b; font-size:0.75rem;">
                                                                Terlambat
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span style="color:#94a3b8; font-size:0.8rem;">-</span>
                                                    @endif
                                                </td>
                                                <td style="font-size: 0.85rem; color: #475569;">{{ $terima->gudang?->gudang_nm }}
                                                </td>
                                                <td><span class="badge badge-success">Diterima Fisik</span></td>
                                                <td style="text-align: right;">
                                                    <a href="{{ route('gudang.terima.show', $terima->terima_id) }}"
                                                        class="btn btn-secondary btn-sm no-print"
                                                        style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                                                        Lihat Surat Jalan
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <div
                                    style="width: 42px; height: 42px; margin: 0 auto 0.75rem; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <strong style="color: #64748b; font-size: 0.9rem; display: block;">Belum ada riwayat kedatangan
                                    barang</strong>
                                <p style="font-size: 0.8rem; margin-top: 0.25rem; margin-bottom: 0;">
                                    Barang belum pernah dicatat masuk. Saat armada supplier tiba di pabrik, gunakan tombol hijau
                                    <strong>"Terima Barang"</strong> di bagian atas.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- KOLOM KANAN (SIDEBAR RINGKAS STICKY) --}}
                <div style="display: flex; flex-direction: column; gap: 1.25rem; position: sticky; top: 1rem;">

                    {{-- PANEL 1: MITRA SUPPLIER --}}
                    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div class="card-header"
                            style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a; font-size: 0.9rem;">Mitra Supplier</strong>
                        </div>
                        <div style="padding: 1rem 1.25rem;">
                            <strong
                                style="font-size: 1rem; color: #0f172a; display: block;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                            <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">Kode:
                                {{ $po->supplier?->supplier_cd }}</span>

                            <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569; line-height: 1.4;">
                                <div style="margin-bottom: 0.35rem;">
                                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Alamat:</span>
                                    {{ $po->supplier?->alamat_txt ?? '-' }}
                                </div>
                                <div>
                                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Telepon /
                                        Kontak:</span>
                                    <strong>{{ $po->supplier?->kontak_no ?? '-' }}</strong>
                                </div>
                                <div style="margin-top: 0.35rem;">
                                    <span style="color: #64748b; display: block; font-size: 0.75rem;">Term of Payment
                                        (Syarat Bayar):</span>
                                    <strong style="color: #0284c7;">
                                        {{ $po->term_of_payment ?: ($po->supplier?->top_hari ? 'Tempo ' . $po->supplier->top_hari . ' Hari (Transfer)' : 'Transfer') }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PANEL 2: GUDANG TUJUAN PENERIMAAN --}}
                    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div class="card-header"
                            style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a; font-size: 0.9rem;">Gudang Tujuan Masuk</strong>
                        </div>
                        <div style="padding: 1rem 1.25rem;">
                            <strong
                                style="font-size: 1rem; color: #0f172a; display: block;">{{ $po->gudang?->gudang_nm ?? '-' }}</strong>
                            <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">Kode:
                                {{ $po->gudang?->gudang_cd }}</span>

                            <div style="margin-top: 0.75rem; font-size: 0.825rem; color: #475569;">
                                <span style="color: #64748b; display: block; font-size: 0.75rem;">Lokasi Pabrik:</span>
                                {{ $po->gudang?->alamat_txt ?? '-' }}
                            </div>
                        </div>
                    </div>

                    {{-- PANEL 3: CATATAN DOKUMEN (NOTE) & REMARK --}}
                    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div class="card-header"
                            style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a; font-size: 0.9rem;">Instruksi &amp; Catatan PO</strong>
                        </div>
                        <div
                            style="padding: 1rem 1.25rem; font-size: 0.85rem; color: #334155; line-height: 1.4; display: flex; flex-direction: column; gap: 0.75rem;">
                            <div>
                                <span
                                    style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.25rem;">
                                    📌 Catatan Dokumen (NOTE)
                                </span>
                                @if ($po->catatan_txt)
                                    <p style="margin: 0; white-space: pre-line; color: #0f172a; font-weight: 600;">
                                        {{ $po->catatan_txt }}</p>
                                @elseif ($po->tgl_estimasi_datang)
                                    <p style="margin: 0; color: #0f172a;">*Mohon dikirim tanggal
                                        {{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->locale('id')->translatedFormat('d F Y') }}
                                    </p>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Tidak ada catatan khusus.</span>
                                @endif
                            </div>

                            <div style="border-top: 1px dashed #e2e8f0; padding-top: 0.5rem;">
                                <span
                                    style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.25rem;">
                                    📝 Catatan Tambahan (REMARK)
                                </span>
                                @if ($po->remark_txt)
                                    <p style="margin: 0; white-space: pre-line; color: #0f172a;">{{ $po->remark_txt }}</p>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div> {{-- AKHIR DARI .no-print --}}

        {{-- DOKUMEN CETAK RESMI FORMAT ASLI HACCP (HANYA MUNCUL SAAT PRINT BROWSER) --}}
        <div class="print-only">
            {{-- KOP TABEL STANDAR HACCP --}}
            <table style="width: 100%; border: 2px solid #000000; border-collapse: collapse; margin-bottom: 25px;">
                <tr>
                    <td
                        style="width: 110px; text-align: center; padding: 6px 4px; border: 1px solid #000000; vertical-align: middle;">
                        <img src="{{ asset('images/logo.png') }}" style="width: 58px; height: auto; display: inline-block; margin: 0 auto;" alt="Logo Cap Payung">
                        <div
                            style="font-size: 6.5pt; font-weight: 800; color: #dc2626; margin-top: 2px; letter-spacing: 0.04em; text-align: center; white-space: nowrap;">
                            ENAK - GURIH - LEZAT
                        </div>
                    </td>
                    <td style="text-align: center; padding: 8px 10px; border: 1px solid #000000; vertical-align: middle;">
                        <div style="font-size: 11pt; font-weight: 800; color: #000000; letter-spacing: 0.03em;">PT. MIRASA
                            FOOD INDUSTRY</div>
                        <div style="font-size: 10pt; font-weight: 600; margin-top: 6px;">Form</div>
                        <div style="font-size: 12pt; font-weight: 800; margin-top: 1px;">Permintaan Barang</div>
                    </td>
                    <td style="width: 250px; padding: 0; border: 1px solid #000000; vertical-align: middle;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                            <tr>
                                <td
                                    style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">
                                    Nomor dokumen</td>
                                <td
                                    style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">
                                    :</td>
                                <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">
                                    MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                            </tr>
                            <tr>
                                <td
                                    style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">
                                    Terbitan/Tgl</td>
                                <td
                                    style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">
                                    :</td>
                                <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">19-08-2021
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="width: 100px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-weight: 600; padding: 4px 6px;">
                                    Revisi</td>
                                <td
                                    style="width: 8px; text-align: center; border-bottom: 1px solid #000000; padding: 4px 0;">
                                    :</td>
                                <td style="font-weight: 700; padding: 4px 6px; border-bottom: 1px solid #000000;">00</td>
                            </tr>
                            <tr>
                                <td
                                    style="width: 100px; border-right: 1px solid #000000; font-weight: 600; padding: 4px 6px;">
                                    Halaman</td>
                                <td style="width: 8px; text-align: center; padding: 4px 0;">:</td>
                                <td style="font-weight: 700; padding: 4px 6px;">1 dari 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- NO PO & KEPADA YTH --}}
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
                                <td style="font-weight: 800; font-family: monospace; font-size: 10.5pt; padding-left: 4px;">
                                    {{ $po->po_no }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; padding-left: 20px; vertical-align: top;">
                        <div style="font-size: 10pt; font-weight: 700; margin-bottom: 2px;">
                            Kepada Yth:
                        </div>
                        <div style="font-size: 10.5pt; font-weight: 800;">
                            {{ $po->supplier?->supplier_nm ?? '-' }}
                        </div>
                        @if ($po->supplier?->alamat_txt)
                            <div style="font-size: 9pt; color: #334155; margin-top: 2px;">
                                {{ $po->supplier->alamat_txt }}
                            </div>
                        @endif
                        @if ($po->supplier?->telepon_no && $po->supplier?->telepon_no !== '-')
                            <div style="font-size: 9pt; color: #334155;">
                                Telp: {{ $po->supplier->telepon_no }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- JUDUL DOKUMEN --}}
            <div style="text-align: center; margin-bottom: 22px;">
                <div style="font-size: 12.5pt; font-weight: 800; text-decoration: underline; letter-spacing: 0.05em;">SURAT
                    PERMINTAAN BARANG</div>
                <div style="font-size: 11pt; font-weight: 800; letter-spacing: 0.05em; margin-top: 3px;">PURCHASE ORDER
                </div>
            </div>

            {{-- TABEL BARANG --}}
            <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000; margin-bottom: 30px;">
                <thead>
                    <tr>
                        <th
                            style="width: 45px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">
                            NO</th>
                        <th
                            style="border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: left; padding-left: 10px; background: #ffffff;">
                            NAMA BARANG</th>
                        <th
                            style="width: 140px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">
                            JUMLAH</th>
                        <th
                            style="width: 180px; border: 1px solid #000000; border-bottom: 2px solid #000000; padding: 6px 6px; font-weight: 800; font-size: 9.5pt; text-align: center; background: #ffffff;">
                            KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($po->details as $idx => $d)
                        <tr>
                            <td
                                style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; text-align: center; font-weight: 700;">
                                {{ $idx + 1 }}.</td>
                            <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; padding-left: 10px;">
                                <strong style="color: #000000;">{{ $d->barang?->barang_nm ?? '-' }}</strong>
                            </td>
                            <td
                                style="border: 1px solid #000000; padding: 7px 8px; font-size: 9.5pt; text-align: center; font-weight: 800;">
                                {{ number_format((float) $d->pesan_qty, 0, ',', '.') }}
                                {{ $d->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                            </td>
                            <td style="border: 1px solid #000000; padding: 7px 8px; font-size: 9pt; padding-left: 8px;">
                                {{ $d->catatan_txt ?: '-' }}
                            </td>
                        </tr>
                    @endforeach

                    @php $emptyRows = max(0, 3 - count($po->details)); @endphp
                    @for ($i = 0; $i < $emptyRows; $i++)
                        <tr>
                            <td style="border: 1px solid #000000; padding: 7px 8px; text-align: center; color: transparent;">
                                &nbsp;</td>
                            <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                            <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                            <td style="border: 1px solid #000000; padding: 7px 8px;">&nbsp;</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- TANDA TANGAN & CATATAN PENGIRIMAN --}}
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
                            Magelang,
                            {{ $po->approved_at ? \Carbon\Carbon::parse($po->approved_at)->translatedFormat('d F Y') : ($po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') : now()->translatedFormat('d F Y')) }}<br>
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
                                <img src="{{ $sigUrl }}" alt="Tanda Tangan & Stempel"
                                    style="height: 78px; max-width: 145px; object-fit: contain; display: inline-block;">
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

        {{-- MODALS PARTIALS --}}
        @include('gudang.po.partials.modal-force-close')
        @include('gudang.po.partials.modal-haccp-po')
        @include('gudang.po.partials.modal-kirim-supplier')

@endsection

    @push('scripts')
        <script>
            window.poConfig = {
                baseUrl: "{{ url('gudang/po') }}"
            };
        </script>
        <script
            src="{{ asset('js/gudang/po/po-show.js') }}?v={{ file_exists(public_path('js/gudang/po/po-show.js')) ? filemtime(public_path('js/gudang/po/po-show.js')) : time() }}"></script>
    @endpush