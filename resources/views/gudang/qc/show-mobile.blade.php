@extends('layouts.qc-mobile')

@section('title', 'Detail Uji QC: ' . $qc->qc_no . ' - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-mobile-detail.css') }}">
@endpush

@php
    $kat = strtoupper((string)($qc->kategori_barang ?: 'SINGKONG'));
    $firstDetail = $qc->details->first();
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalRefraksi = $qc->details->sum('qty_refraksi');
    $totalReject = $qc->details->sum('qty_reject');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();
@endphp

@section('content')
<div class="qc-detail-wrap">

    {{-- TOP NAV --}}
    <div class="qc-detail-top-nav">
        <a href="{{ route('qc.inbound.index') }}" class="qc-detail-back-btn">
            <span>&larr; Riwayat Tiket</span>
        </a>
        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
            QC Lapangan &bull; Detail
        </span>
    </div>

    {{-- HERO CARD --}}
    <div class="qc-detail-hero">
        <div class="qc-detail-hero-top">
            <div>
                <span class="badge-tag-commodity">
                    @if ($kat === 'SINGKONG') 🥔 @elseif ($kat === 'MINYAK') 🛢️ @elseif ($kat === 'PLASTIK') 🛍️ @elseif ($kat === 'KARTON') 📦 @else ✨ @endif
                    {{ $kat }}
                </span>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem;">{{ $qc->qc_no }}</h1>
            </div>
            <div class="qc-detail-badges">
                @if ($qc->status_qc === 'SIAP_GUDANG')
                    <span class="badge-tag-status siap">⏳ Siap Gudang</span>
                @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                    <span class="badge-tag-status diterima">✅ Masuk Gudang</span>
                @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                    <span class="badge-tag-status tolak">❌ Ditolak</span>
                @else
                    <span class="badge-tag-status siap">{{ $qc->status_qc }}</span>
                @endif
            </div>
        </div>

        <div class="qc-detail-hero-meta">
            <div>📅 <strong>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong></div>
            <div>👤 Petugas QC: <strong>{{ $qc->petugas_qc_nama }}</strong></div>
            @if ($qc->terima)
                <div style="margin-top: 0.25rem; background: rgba(0,0,0,0.2); padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.75rem;">
                    📦 Sudah Masuk Gudang &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU 1: INFO PENGIRIMAN & ARMADA --}}
    <div class="qc-card-section">
        <h2 class="qc-card-title">
            <span>🚚</span> <span>Pengiriman &amp; Armada</span>
        </h2>
        <div class="qc-info-grid">
            <div class="qc-info-item">
                <span class="qc-info-label">Mitra Supplier</span>
                <span class="qc-info-value">{{ $qc->supplier?->supplier_nm ?? '-' }}</span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">No. Surat Jalan / DO</span>
                <span class="qc-info-value">{{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}</span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">No. Plat Truk</span>
                <span class="qc-info-value" style="color: #0284c7;">{{ $qc->plat_nomor_truk ?: '-' }}</span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">Nama Sopir</span>
                <span class="qc-info-value">{{ $qc->sopir_nama ?: '-' }}</span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">Kondisi Angkutan</span>
                <span class="qc-info-value" style="color: {{ $qc->bebas_cemaran_st ? '#16a34a' : '#dc2626' }};">
                    {{ $qc->bebas_cemaran_st ? '✔ Bersih & Bebas Cemaran' : '✖ Ada Cemaran' }}
                </span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">Audit Angkutan Halal</span>
                <span class="qc-info-value" style="color: {{ !$qc->angkut_barang_haram_st ? '#16a34a' : '#dc2626' }};">
                    {{ !$qc->angkut_barang_haram_st ? '✔ Bebas Barang Haram' : '✖ Tercampur Barang Haram' }}
                </span>
            </div>
        </div>
    </div>

    {{-- KARTU 2: RINGKASAN TIMBANGAN & TONASE --}}
    <div class="qc-card-section">
        <h2 class="qc-card-title">
            <span>⚖️</span> <span>Hasil Timbangan &amp; Tonase</span>
        </h2>
        <div class="qc-metrics-grid">
            <div class="qc-metric-box">
                <div class="qc-metric-title">Gross (Bruto)</div>
                <div class="qc-metric-number">{{ number_format($totalGross, 0, ',', '.') }}</div>
                <div class="qc-metric-sub">kg timbang</div>
            </div>
            <div class="qc-metric-box">
                <div class="qc-metric-title">Refraksi</div>
                <div class="qc-metric-number" style="color: #d97706;">{{ number_format($totalRefraksi, 0, ',', '.') }}</div>
                <div class="qc-metric-sub">{{ number_format($firstDetail?->refraksi_persen ?? 0, 1) }}% potongan</div>
            </div>
            <div class="qc-metric-box">
                <div class="qc-metric-title">Reject (Afkir)</div>
                <div class="qc-metric-number" style="color: #dc2626;">{{ number_format($totalReject, 0, ',', '.') }}</div>
                <div class="qc-metric-sub">kg ditolak</div>
            </div>
            <div class="qc-metric-box highlight">
                <div class="qc-metric-title" style="color: #059669;">Netto Lolos</div>
                <div class="qc-metric-number">{{ number_format($totalNetto, 0, ',', '.') }}</div>
                <div class="qc-metric-sub" style="color: #059669; font-weight: 700;">kg diterima pabrik</div    {{-- KARTU 3: CATATAN & KESIMPULAN --}}
    <div class="qc-card-section">
        <h2 class="qc-card-title">
            <span>💬</span> <span>Keputusan &amp; Catatan</span>
        </h2>

        <div style="margin-bottom: 0.75rem;">
            <span class="qc-info-label">Status Keputusan Akhir:</span>
            <div style="font-size: 1.05rem; font-weight: 900; margin-top: 0.2rem; color: {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '#16a34a' : '#dc2626' }};">
                @if ($qc->status_qc !== 'DITOLAK_TOTAL')
                    ✔ DITERIMA ({{ number_format($totalNetto, 0, ',', '.') }} kg Lolos)
                @else
                    ✖ DITOLAK TOTAL
                @endif
            </div>
        </div>

        @if ($firstDetail?->catatan_dtl ?: $qc->catatan_umum)
            <div class="qc-comment-box">
                "{{ $firstDetail?->catatan_dtl ?: $qc->catatan_umum }}"
            </div>
        @endif

        <div class="qc-sign-grid">
            <div class="qc-sign-card">
                <div class="qc-sign-role">Petugas QC</div>
                <div class="qc-sign-name">{{ $qc->petugas_qc_nama }}</div>
                <div class="qc-sign-status">✔ Terverifikasi</div>
            </div>
            <div class="qc-sign-card">
                <div class="qc-sign-role">Supervisor QC</div>
                <div class="qc-sign-name">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</div>
                <div class="qc-sign-status">✔ Disetujui</div>
            </div>
        </div>
    </div>

</div>

{{-- FIXED BOTTOM BAR MOBILE --}}
<div class="qc-mobile-bottom-bar">
    @if (Auth::user()?->canEditQc() && !$isLocked)
        <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile']) }}" class="qc-btn-mobile-edit" style="width: 100%; text-align: center;">
            <span>✏️ Edit Uji Mutu</span>
        </a>
    @else
        <button type="button" class="qc-btn-mobile-edit" style="width: 100%; background: #94a3b8; cursor: not-allowed;" disabled>
            <span>🔒 Tiket Terkunci</span>
        </button>
    @endif
</div>
@endsection
