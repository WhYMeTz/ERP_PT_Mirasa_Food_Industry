@extends('layouts.executive')

@section('title', 'Tinjauan PO ' . $po->po_no . ' - Mirasa Executive')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-review.css') }}?v={{ filemtime(public_path('css/gudang/po/po-review.css')) }}">
@endpush

@php
    $subtotal = 0;
    foreach ($po->details as $d) {
        $qty = (float) $d->pesan_qty;
        $price = (float) ($d->harga_nominal ?? 0);
        $lineTotal = (float) ($d->subtotal_nominal ?? ($qty * $price));
        $subtotal += $lineTotal;
    }

    $ppn = (float) ($po->ppn_nominal ?? 0);
    if ($ppn == 0 && ($po->is_harga_termasuk_pajak || str_contains(strtoupper($po->catatan_txt ?? ''), 'PPN'))) {
        $ppn = round($subtotal * 0.11);
    }
    $grandTotal = (float) ($po->total_tagihan ?? ($subtotal + $ppn));

    $tglPo = $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->format('d M Y') : date('d M Y');
    $tglEta = $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang)->locale('id')->translatedFormat('d F Y') : null;
    $isApprovedState = $po->isApproved() || in_array($po->status_cd, ['APPROVED', 'COMPLETED', 'PARTIAL', 'ORDERED']);

    // Cari akun atasan/approver yang ditugaskan atau yang menyetujui dokumen PO ini
    $approverUser = null;
    if ($isApprovedState && !empty($po->approved_by)) {
        $approverUser = \App\Models\User::where('name', $po->approved_by)->first();
    }
    if (!$approverUser) {
        $approverUser = $po->approver;
    }
    if (!$approverUser && !empty($po->approver_id)) {
        $approverUser = \App\Models\User::find($po->approver_id);
    }
    $approverDisplayName = $isApprovedState 
        ? ($po->approved_by ?? $approverUser?->name ?? 'Bpk. Ahmad Kurniawan (Direktur)')
        : ($approverUser?->name ?? 'Bpk. Ahmad Kurniawan (Direktur)');
    $sigFilename = $approverUser?->signature_path ?: 'ttd_stempel_1.png';
@endphp

@section('content')
<div class="po-review-wrapper anim-fade-up">

    {{-- 1. Header Toolbar --}}
    <div class="mac-review-header">
        <div class="mac-header-left">
            <a href="{{ route('gudang.po.approval_center', ['status' => 'PENDING_APPROVAL']) }}" class="btn-mac-back">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Antrean PO</span>
            </a>

            <div class="mac-doc-title-block">
                <h1>
                    <span>PO #{{ $po->po_no }}</span>
                </h1>
            </div>

            <div>
                @if($po->status_cd == 'PENDING_APPROVAL')
                    <span class="mac-status-chip">
                        ● Menunggu Keputusan Atasan
                    </span>
                @elseif($isApprovedState)
                    <span class="mac-status-chip approved">
                        ✓ Telah Disahkan (Approved)
                    </span>
                @elseif($po->status_cd == 'REVISION')
                    <span class="mac-status-chip">
                        🔄 Dalam Arahan Revisi
                    </span>
                @elseif($po->status_cd == 'REJECTED')
                    <span class="mac-status-chip" style="color: #dc2626;">
                        ✕ Ditolak Manajemen
                    </span>
                @endif
            </div>
        </div>

        <div class="mac-header-right">
            {{-- Tombol Lihat Dokumen Pop-up (macOS Quick Look) --}}
            <button type="button" class="btn-mac-back" onclick="openDocumentModal()" style="background: #18181b; color: #ffffff; border-color: #18181b;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Lihat Lembar Dokumen</span>
            </button>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div style="background: #18181b; color: #ffffff; padding: 0.85rem 1.25rem; border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.65rem;">
            <svg width="18" height="18" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991c1c; padding: 0.85rem 1.25rem; border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.65rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- 2. Top 3 Minimal macOS KPI Cards (Monochrome) --}}
    <div class="mac-kpi-grid">
        {{-- Card 1: Total Anggaran Belanja (Solid Black) --}}
        <div class="mac-kpi-card kpi-dark-highlight">
            <div class="mac-kpi-card-header">
                <span class="mac-kpi-label">Total Anggaran Belanja PO</span>
                <span style="font-size: 0.725rem; color: #a1a1aa; font-weight: 600;">Tahap Pengesahan</span>
            </div>
            <div class="mac-kpi-value">
                Rp {{ number_format($grandTotal, 0, ',', '.') }}
            </div>
            <p class="mac-kpi-subtext">
                Termin: <strong>{{ $po->term_of_payment ?: ($po->supplier?->top_hari ? 'Tempo ' . $po->supplier->top_hari . ' Hari (Transfer)' : 'Transfer') }}</strong>
            </p>
        </div>

        {{-- Card 2: Kelayakan Stok Riil Gudang --}}
        <div class="mac-kpi-card">
            <div class="mac-kpi-card-header">
                <span class="mac-kpi-label">Kelayakan Stok Riil</span>
                <span style="font-size: 0.725rem; color: #71717a; font-weight: 600;">Audit Gudang</span>
            </div>
            <div class="mac-kpi-value">
                @if($hasCriticalStock)
                    Stok Kritis
                @elseif($hasOverstock)
                    Stok Cukup
                @else
                    Rasio Aman
                @endif
            </div>
            <p class="mac-kpi-subtext">
                @if($hasCriticalStock)
                    Pengadaan mendesak, stok fisik berada di bawah batas safety stock harian pabrik.
                @elseif($hasOverstock)
                    Gudang masih memiliki stok cadangan; pastikan kuantitas pesan sesuai batch.
                @else
                    Kuantitas proporsional sesuai tingkat konsumsi resep standar pabrik Mirasa.
                @endif
            </p>
        </div>

        {{-- Card 3: Kewajaran Harga Standar --}}
        <div class="mac-kpi-card">
            <div class="mac-kpi-card-header">
                <span class="mac-kpi-label">Audit Harga Pabrik</span>
                <span style="font-size: 0.725rem; color: #71717a; font-weight: 600;">Benchmark Std</span>
            </div>
            <div class="mac-kpi-value">
                @if($hasPriceIncrease)
                    Ada Kenaikan
                @else
                    Sesuai Standar
                @endif
            </div>
            <p class="mac-kpi-subtext">
                @if($hasPriceIncrease)
                    Terdapat komoditas dengan harga di atas acuan pembelian standar pabrik.
                @else
                    Seluruh harga satuan sama atau berada di bawah acuan standar pabrik.
                @endif
            </p>
        </div>
    </div>

    {{-- 3. Detail Grid Layout (Left: Vendor & Metadata, Right: Audit & Financial Breakdown) --}}
    <div class="mac-detail-grid">
        
        {{-- Card A: Metadata Mitra Vendor & Logistik --}}
        <div class="mac-card">
            <h3 class="mac-card-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Mitra Vendor &amp; Pengiriman
            </h3>

            <div class="mac-vendor-name">
                {{ $po->supplier?->nama_supplier ?? $po->supplier?->supplier_nm ?? 'Supplier Umum' }}
            </div>
            <div class="mac-vendor-address">
                {{ $po->supplier?->alamat ?? $po->supplier?->alamat_txt ?? 'Alamat vendor belum terdata' }}<br>
                Telp: {{ $po->supplier?->telepon ?? $po->supplier?->kontak_no ?? '-' }}
            </div>

            <div class="mac-key-value-list">
                <div class="mac-kv-row">
                    <span class="mac-kv-key">Tanggal Pengajuan</span>
                    <span class="mac-kv-val">{{ \Carbon\Carbon::parse($po->po_tgl ?? $po->po_date)->translatedFormat('d F Y') }}</span>
                </div>
                <div class="mac-kv-row">
                    <span class="mac-kv-key">Estimasi Tiba (ETA)</span>
                    <span class="mac-kv-val">{{ $tglEta ?? 'Sesuai Jadwal' }}</span>
                </div>
                <div class="mac-kv-row">
                    <span class="mac-kv-key">Atasan Penanggung Jawab</span>
                    <span class="mac-kv-val" style="color: var(--mac-black);">{{ $approverDisplayName }}</span>
                </div>
                <div class="mac-kv-row">
                    <span class="mac-kv-key">Gudang Penerima</span>
                    <span class="mac-kv-val">{{ $po->gudang?->gudang_nm ?? 'Gudang Mirasa' }}</span>
                </div>
                <div class="mac-kv-row">
                    <span class="mac-kv-key">Rekening Transfer</span>
                    <span class="mac-kv-val">BCA 104-9173332</span>
                </div>
            </div>
        </div>

        {{-- Card B: Catatan & Ringkasan Anggaran --}}
        <div class="mac-card">
            <h3 class="mac-card-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Catatan Pengadaan &amp; Rekap Finansial
            </h3>

            {{-- Memo Pengajuan Staf --}}
            <div class="mac-memo-box">
                <strong style="display: block; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em; color: #71717a; margin-bottom: 4px;">
                    Catatan dari Staf Pembuat:
                </strong>
                "{{ $po->catatan_txt ?: 'Pengadaan rutin bahan baku operasional pabrik PT Mirasa Food Industry.' }}"
            </div>

            @if(!empty($po->approval_notes))
                <div class="mac-memo-box" style="background: #18181b; color: #ffffff; margin-top: 0.65rem;">
                    <strong style="display: block; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em; color: #a1a1aa; margin-bottom: 4px;">
                        Catatan Persetujuan:
                    </strong>
                    "{{ $po->approval_notes }}"
                </div>
            @endif

            @if(!empty($po->reject_reason))
                <div class="mac-memo-box" style="background: #fef2f2; color: #991c1c; margin-top: 0.65rem; border: 1px solid #fecaca;">
                    <strong style="display: block; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        Arahan Revisi / Penolakan:
                    </strong>
                    "{{ $po->reject_reason }}"
                </div>
            @endif

            <div class="mac-key-value-list" style="margin-top: 1rem;">
                <div class="mac-kv-row">
                    <span class="mac-kv-key">Subtotal Bruto</span>
                    <span class="mac-kv-val">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="mac-kv-row">
                    <span class="mac-kv-key">PPN (11%)</span>
                    <span class="mac-kv-val">Rp {{ number_format($ppn, 0, ',', '.') }}</span>
                </div>
                <div class="mac-kv-row" style="padding-top: 0.5rem; border-top: 1px solid var(--mac-border); font-size: 0.95rem;">
                    <span class="mac-kv-key" style="font-weight: 700; color: var(--mac-black);">Grand Total Tagihan</span>
                    <span class="mac-kv-val" style="font-size: 1.15rem; font-weight: 800; color: var(--mac-black);">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- 4. Full Table: Lembar Verifikasi Komoditas Bahan Baku --}}
    <div class="mac-table-card">
        <div class="mac-table-header">
            <h3 class="mac-table-title">Lembar Verifikasi Kuantitas &amp; Harga Komoditas</h3>
            <span style="font-size: 0.75rem; color: #71717a; font-weight: 600; background: #f4f4f5; padding: 4px 10px; border-radius: 9999px;">
                Total {{ count($analyzedItems) }} Komoditas Terdaftar
            </span>
        </div>

        <div class="mac-table-responsive">
            <table class="mac-modern-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">#</th>
                        <th>Komoditas &amp; Spesifikasi</th>
                        <th style="text-align: right;">Kuantitas Pesan</th>
                        <th style="text-align: center;">Stok Gudang Saat Ini</th>
                        <th style="text-align: right;">Harga Satuan Pesan</th>
                        <th style="text-align: right;">Subtotal (Rp)</th>
                        <th style="text-align: center;">Kelayakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analyzedItems as $index => $item)
                        <tr>
                            <td style="text-align: center; color: #a1a1aa; font-weight: 700;">
                                {{ $index + 1 }}
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--mac-black); font-size: 0.9rem; display: block;">
                                    {{ $item['nama_barang'] }}
                                </span>
                                <span style="font-size: 0.75rem; color: #71717a;">
                                    Satuan: <strong>{{ $item['satuan'] }}</strong>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 800; color: var(--mac-black);">
                                {{ number_format($item['pesan_qty'], 2, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 700; color: var(--mac-black);">
                                    {{ number_format($item['stok_riil'], 2, ',', '.') }} {{ $item['satuan'] }}
                                </div>
                                <div style="font-size: 0.725rem; color: #71717a;">
                                    Batas Min: {{ number_format($item['batas_min'], 2, ',', '.') }}
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-weight: 700; color: var(--mac-black);">
                                    Rp {{ number_format($item['harga_pesan'], 0, ',', '.') }}
                                </div>
                                @if($item['harga_std'] > 0)
                                    <div style="font-size: 0.725rem; color: #a1a1aa;">
                                        Std: Rp {{ number_format($item['harga_std'], 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 800; color: var(--mac-black); font-size: 0.925rem;">
                                Rp {{ number_format($item['pesan_qty'] * $item['harga_pesan'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($item['stock_status'] == 'CRITICAL')
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 9999px; background: #fee2e2; color: #991c1c; font-size: 0.725rem; font-weight: 700;">
                                        Kritis
                                    </span>
                                @elseif($item['price_diff'] > 0)
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 9999px; background: #fef3c7; color: #92400e; font-size: 0.725rem; font-weight: 700;">
                                        Kenaikan
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 9999px; background: #f4f4f5; color: #27272a; font-size: 0.725rem; font-weight: 700;">
                                        Layak
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 5. Sticky Floating Decision Dock (macOS Monochrome Glass Style) --}}
    <div class="mac-decision-dock no-print">
        {{-- Tombol Lihat Dokumen --}}
        <button type="button" class="btn-dock-item btn-dock-glass" onclick="openDocumentModal()">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Lihat Dokumen PO</span>
        </button>

        @if($po->status_cd == 'PENDING_APPROVAL' || $po->status_cd == 'REVISION')
            @if($canApprove)
                {{-- Tombol Minta Revisi --}}
                <button type="button" class="btn-dock-item btn-dock-glass" onclick="openRevisionModal()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Minta Revisi</span>
                </button>

                {{-- Tombol Tolak --}}
                <button type="button" class="btn-dock-item btn-dock-danger" onclick="openRejectModal()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Tolak</span>
                </button>

                {{-- Tombol Utama Setujui PO --}}
                <button type="button" class="btn-dock-item btn-dock-primary-white" onclick="openApproveModal()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Setujui Dokumen (Approve)</span>
                </button>
            @endif
        @else
            <span style="font-size: 0.8125rem; color: #a1a1aa; padding: 0 0.5rem;">
                Status: <strong>{{ $po->status_cd }}</strong>
            </span>
        @endif
    </div>

</div>

{{-- Modals Partial Inclusions --}}
@include('approval.po.partials.modal-document-preview')
@include('approval.po.partials.modal-approve-confirm')
@include('approval.po.partials.modal-revision-request')
@include('approval.po.partials.modal-reject-confirm')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/po/po-review.js') }}?v={{ filemtime(public_path('js/gudang/po/po-review.js')) }}"></script>
@endpush
