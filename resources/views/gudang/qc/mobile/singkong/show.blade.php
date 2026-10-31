@extends('layouts.qc-mobile')

@section('title', 'Detail QC Singkong: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/singkong/singkong-detail.css') }}?v={{ filemtime(public_path('css/gudang/qc/singkong/singkong-detail.css')) }}">
@endpush

@php
    // Identifikasi apakah dibuka dari tiket Pengujian 1 atau Pengujian 2
    $isCurrentP2 = ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2';

    // Model Pengujian 1 (Kedatangan Induk) dan Pengujian 2 (Lantai Produksi)
    $p1 = $isCurrentP2 ? ($qc->parentQc ?: $qc) : $qc;
    $p2 = $isCurrentP2 ? $qc : ($qc->pengujian2List->first() ?? null);

    // Detail pertama dari masing-masing pengujian
    $p1Detail = $p1->details->first();
    $p2Detail = $p2?->details?->first();

    // Data Metrik Pengujian 1
    $u1Gross = (float) ($p1Detail?->qty_timbang_gross ?: $p1->jumlah_di_pabrik ?: 0);
    $u1Refraksi = (float) ($p1Detail?->qty_refraksi ?: 0);
    $u1RefraksiPersen = (float) ($p1Detail?->refraksi_persen ?: 0);
    $u1Reject = (float) ($p1Detail?->qty_reject ?: 0);
    $u1Netto = (float) ($p1Detail?->qty_netto_lolos ?: 0);
    $u1Grade = $p1Detail?->grade_cd ?? 'A';
    $u1KadarAir = $p1Detail?->kadar_air_persen;
    $u1RawMat = $p1Detail?->status_raw_material ?? 'OK';
    $u1DiamKurang = (float) ($p1Detail?->diameter_kurang_4cm_persen ?? 0);
    $u1DiamLebih = (float) ($p1Detail?->diameter_lebih_4cm_persen ?? 0);
    $u1DiamKurangPass = $u1DiamKurang <= 5;
    $u1DiamLebihPass = $u1DiamLebih >= 95;

    // Data Metrik Pengujian 2 (jika sudah ada)
    $hasP2 = !empty($p2);
    $u2Gross = (float) ($p2Detail?->qty_timbang_gross ?: ($p2?->jumlah_di_pabrik ?: 0));
    $u2Refraksi = (float) ($p2Detail?->qty_refraksi ?: 0);
    $u2RefraksiPersen = (float) ($p2Detail?->refraksi_persen ?: 0);
    $u2Reject = (float) ($p2Detail?->qty_reject ?: 0);
    $u2Netto = (float) ($p2Detail?->qty_netto_lolos ?: 0);
    $u2Grade = $p2Detail?->grade_cd ?? ($hasP2 ? 'A' : null);
    $u2KadarAir = $p2Detail?->kadar_air_persen;

    // Akumulasi Total 1 Truk (Uji 1 + Uji 2)
    $sjQty = (float) ($p1->jumlah_surat_jalan ?: ($u1Gross ?: 0));
    $totalGrossTruk = $u1Gross + ($hasP2 ? $u2Gross : 0);
    $totalRefraksiTruk = $u1Refraksi + ($hasP2 ? $u2Refraksi : 0);
    $totalRejectTruk = $u1Reject + ($hasP2 ? $u2Reject : 0);
    $totalNettoTruk = $u1Netto + ($hasP2 ? $u2Netto : 0);
    $avgRefraksiPersen = $totalGrossTruk > 0 ? ($totalRefraksiTruk / $totalGrossTruk) * 100 : $u1RefraksiPersen;
    $sisaBakTruk = max(0, $sjQty - $totalGrossTruk);

    // Penentuan Grade Gabungan Truk
    if ($u1Grade === 'REJECT' || ($hasP2 && $u2Grade === 'REJECT')) {
        $gradeGabungan = 'REJECT';
    } elseif ($u1Grade === 'B' || ($hasP2 && $u2Grade === 'B')) {
        $gradeGabungan = 'B';
    } else {
        $gradeGabungan = 'A';
    }

    $isLocked = (!empty($p1->terima) || !empty($p2?->terima)) && !Auth::user()?->isSuperAdmin();
@endphp

@section('content')
<div class="sk-detail-wrap">

    {{-- TOP APP BAR --}}
    <div class="sk-top-bar">
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'SINGKONG']) }}" class="sk-back-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Riwayat Singkong</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="sk-back-btn" style="color: #0284c7; border-color: #bae6fd; background: #f0f9ff;" title="Ke Tabel Web QC">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Web ERP</span>
                </a>
            @endif

            @if (Auth::user()?->canEditQcMobile() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="sk-action-btn-edit">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Uji</span>
                </a>
            @endif
        </div>
    </div>

    {{-- HERO CARD (IDENTITAS TRUK & DOKUMEN) --}}
    <div class="sk-hero">
        <div class="sk-hero-badges">
            <span class="sk-pill sk-pill-commodity">🥔 SINGKONG</span>

            @if ($hasP2)
                <span class="sk-pill sk-pill-tahap2">
                    🍟 UJI I &amp; II LENGKAP
                </span>
            @else
                <span class="sk-pill sk-pill-tahap1">
                    🚛 UJI I KEDATANGAN
                </span>
            @endif

            @if ($gradeGabungan === 'B')
                <span class="sk-pill sk-pill-grade-b">🟡 GRADE B</span>
            @elseif ($gradeGabungan === 'REJECT')
                <span class="sk-pill sk-pill-grade-reject">❌ REJECT / AFKIR</span>
            @else
                <span class="sk-pill sk-pill-grade-a">🟢 GRADE A</span>
            @endif
        </div>

        <div class="sk-hero-title-row">
            <div>
                <h1 class="sk-hero-ticket-no">
                    <span>🚛 {{ $p1->plat_nomor_truk ?: 'Truk Singkong' }}</span>
                </h1>
                <div style="font-size: 0.8rem; color: #bae6fd; font-weight: 700; margin-top: 2px;">
                    Tiket Uji 1: <strong>#{{ $p1->qc_no }}</strong>
                    @if ($hasP2)
                        &bull; Uji 2: <strong>#{{ $p2->qc_no }}</strong>
                    @endif
                </div>
            </div>

            @if ($p1->status_qc === 'SIAP_GUDANG' || ($hasP2 && $p2->status_qc === 'SIAP_GUDANG'))
                <span class="sk-hero-status siap">⏳ Siap Gudang</span>
            @elseif ($p1->status_qc === 'DITERIMA_PARSIAL' || $totalRejectTruk > 0)
                <span class="sk-hero-status parsial">⚠️ Masuk Parsial</span>
            @elseif ($p1->status_qc === 'DITERIMA_GUDANG')
                <span class="sk-hero-status diterima">✅ Masuk Penuh</span>
            @elseif ($p1->status_qc === 'DITOLAK_TOTAL' || ($hasP2 && $p2->status_qc === 'DITOLAK_TOTAL'))
                <span class="sk-hero-status tolak">❌ Ditolak</span>
            @else
                <span class="sk-hero-status siap">{{ $p1->status_qc }}</span>
            @endif
        </div>

        <div class="sk-hero-meta-grid">
            <div class="sk-hero-meta-item">
                <span>🏭</span>
                <span>Supplier: <strong>{{ $p1->supplier?->supplier_nm ?? '-' }}</strong></span>
            </div>
            <div class="sk-hero-meta-item">
                <span>📅</span>
                <span>Uji 1: <strong>{{ $p1->tgl_periksa ? $p1->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong></span>
            </div>
            <div class="sk-hero-meta-item">
                <span>👤</span>
                <span>QC Kedatangan: <strong>{{ $p1->petugas_qc_nama ?: '-' }}</strong></span>
            </div>
            <div class="sk-hero-meta-item">
                <span>🏢</span>
                <span>Gudang: <strong>{{ $p1->gudang?->gudang_nm ?: '-' }}</strong></span>
            </div>
        </div>

        {{-- INFO JIKA SUDAH DIPROSES GRN GUDANG --}}
        @if ($p1->terima || ($hasP2 && $p2->terima))
            @php $grn = $p1->terima ?: $p2->terima; @endphp
            <div class="sk-hero-relation-box" style="background: rgba(22, 163, 74, 0.25); border-color: rgba(134, 239, 172, 0.4);">
                <span>📦 Ditarik ke Gudang &bull; GRN: <strong>#{{ $grn->terima_no }}</strong></span>
                <span style="font-size: 0.72rem; color: #dcfce7;">{{ $grn->terima_tgl ? \Carbon\Carbon::parse($grn->terima_tgl)->format('d/m/Y') : '' }}</span>
            </div>
        @endif
    </div>

    {{-- KARTU 1: REKAPITULASI AKUMULASI 1 TRUK (UJI 1 + UJI 2) --}}
    <div class="sk-card sk-card-rekap">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>⚖️</span> <span>Rekapitulasi Akumulasi 1 Truk</span>
            </h2>
            <span class="sk-card-badge-right" style="background: #dcfce7; color: #166534; font-weight: 800;">
                {{ $hasP2 ? 'Uji I + II Selesai' : 'Uji I Selesai' }}
            </span>
        </div>

        <div class="sk-rekap-tag-row">
            <div>
                <span style="color: #64748b;">Surat Jalan Supplier:</span>
                <strong style="color: #0f172a; font-size: 0.85rem;">{{ number_format($sjQty, 0, ',', '.') }} kg</strong>
            </div>
            <div>
                <span style="color: #64748b;">Truk Bongkar:</span>
                <strong style="color: #0f172a; font-size: 0.85rem;">{{ $p1->plat_nomor_truk ?: '-' }}</strong>
            </div>
        </div>

        <div class="sk-metrics-row">
            <div class="sk-metric-card">
                <div class="sk-metric-title">Total Bruto (Gross)</div>
                <div class="sk-metric-val">{{ number_format($totalGrossTruk, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">
                    @if ($hasP2)
                        U1: {{ number_format($u1Gross, 0, ',', '.') }} + U2: {{ number_format($u2Gross, 0, ',', '.') }} kg
                    @else
                        kg timbang gerbang
                    @endif
                </div>
            </div>

            <div class="sk-metric-card">
                <div class="sk-metric-title">Total Refraksi</div>
                <div class="sk-metric-val" style="color: var(--sk-warning);">{{ number_format($totalRefraksiTruk, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">{{ number_format($avgRefraksiPersen, 1) }}% potongan</div>
            </div>

            <div class="sk-metric-card">
                <div class="sk-metric-title">Total Reject</div>
                <div class="sk-metric-val" style="color: var(--sk-danger);">{{ number_format($totalRejectTruk, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">kg afkir / tolak</div>
            </div>

            <div class="sk-metric-card netto-hero">
                <div class="sk-metric-title">Netto Diterima</div>
                <div class="sk-metric-val">{{ number_format($totalNettoTruk, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">kg masuk pabrik</div>
            </div>
        </div>

        <div style="margin-top: 0.85rem; padding-top: 0.65rem; border-top: 1px dashed #cbd5e1; font-size: 0.775rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
            @if ($hasP2)
                <div style="color: #15803d; font-weight: 700;">
                    ✔ Seluruh muatan bak truk telah tuntas ditimbang &amp; diuji (Total {{ number_format($totalGrossTruk, 0, ',', '.') }} kg).
                </div>
            @else
                <div style="color: #b45309; font-weight: 700;">
                    ⏳ Sisa muatan bak truk belum diuji II: <strong>{{ number_format($sisaBakTruk, 0, ',', '.') }} kg</strong>
                </div>
                @if (Auth::user()?->canCreateQc())
                    <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $p1->qc_id, 'tahap' => 2, 'view' => 'mobile']) }}" style="color: #7e22ce; font-weight: 800; text-decoration: underline;">
                        + Lakukan Uji 2 Sekarang
                    </a>
                @endif
            @endif
        </div>
    </div>

    {{-- =========================================================================
         BAGIAN 1: RINCIAN PENGUJIAN 1 (KEDATANGAN & GERBANG TIMBANG)
         ========================================================================= --}}
    <div id="sectionUji1">
        <div class="sk-card sk-stage-card-p1">
            <div class="sk-stage-header-p1">
                <div class="sk-stage-title-p1">
                    <span>🚛</span> <span>Pengujian I &bull; Kedatangan Gerbang</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <span class="sk-pill sk-pill-tahap1">#{{ $p1->qc_no }}</span>
                    @if ($u1Grade === 'B')
                        <span class="sk-pill sk-pill-grade-b">GRADE B</span>
                    @elseif ($u1Grade === 'REJECT')
                        <span class="sk-pill sk-pill-grade-reject">REJECT</span>
                    @else
                        <span class="sk-pill sk-pill-grade-a">GRADE A</span>
                    @endif
                </div>
            </div>

            {{-- METRIK TIMBANGAN UJI 1 --}}
            <div class="sk-metrics-row">
                <div class="sk-metric-card">
                    <div class="sk-metric-title">Gross Uji I</div>
                    <div class="sk-metric-val">{{ number_format($u1Gross, 0, ',', '.') }}</div>
                    <div class="sk-metric-sub">kg timbang</div>
                </div>

                <div class="sk-metric-card">
                    <div class="sk-metric-title">Refraksi</div>
                    <div class="sk-metric-val" style="color: var(--sk-warning);">{{ number_format($u1Refraksi, 0, ',', '.') }}</div>
                    <div class="sk-metric-sub">{{ number_format($u1RefraksiPersen, 1) }}% potongan</div>
                </div>

                <div class="sk-metric-card">
                    <div class="sk-metric-title">Reject (Afkir)</div>
                    <div class="sk-metric-val" style="color: var(--sk-danger);">{{ number_format($u1Reject, 0, ',', '.') }}</div>
                    <div class="sk-metric-sub">kg afkir</div>
                </div>

                <div class="sk-metric-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <div class="sk-metric-title" style="color: #166534;">Netto Uji I</div>
                    <div class="sk-metric-val" style="color: #15803d;">{{ number_format($u1Netto, 0, ',', '.') }}</div>
                    <div class="sk-metric-sub" style="color: #15803d;">kg lolos</div>
                </div>
            </div>

            {{-- PARAMETER FISIK & SORTIR UJI 1 --}}
            <div class="sk-sub-detail-box">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                    <span style="font-size: 0.78rem; font-weight: 800; color: #0284c7; text-transform: uppercase;">
                        📏 Parameter Fisik &amp; Sortir Kedatangan
                    </span>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #475569;">
                        Kadar Air: <strong style="color: #0f172a;">{{ $u1KadarAir !== null ? number_format((float)$u1KadarAir, 1) . '%' : '-' }}</strong> (Std &le;14%)
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; background: {{ $u1RawMat === 'OK' ? '#f0fdf4' : '#fef2f2' }}; border: 1.5px solid {{ $u1RawMat === 'OK' ? '#86efac' : '#fca5a5' }}; border-radius: 8px; padding: 0.5rem 0.75rem; margin-bottom: 0.65rem;">
                    <div>
                        <span style="font-size: 0.68rem; font-weight: 800; color: {{ $u1RawMat === 'OK' ? '#166534' : '#991b1b' }}; text-transform: uppercase;">Status Raw Material</span>
                        <div style="font-size: 0.85rem; font-weight: 800; color: {{ $u1RawMat === 'OK' ? '#15803d' : '#dc2626' }};">
                            {{ $u1RawMat === 'OK' ? '✔ OK (STANDAR PABRIK)' : '✖ TIDAK STANDAR' }}
                        </div>
                    </div>
                    <span style="font-size: 1.15rem;">{{ $u1RawMat === 'OK' ? '🌿' : '⚠️' }}</span>
                </div>

                {{-- DIAMETER --}}
                <div class="sk-diameter-card">
                    <div class="sk-diameter-header">
                        <span style="color: #334155;">Diameter &lt; 4 cm (Max 5%)</span>
                        <span class="{{ $u1DiamKurangPass ? 'highlight-green' : 'highlight-red' }}" style="font-size: 0.825rem; font-weight: 800;">
                            {{ number_format($u1DiamKurang, 1) }}% {{ $u1DiamKurangPass ? '✔ Lolos' : '⚠️ Melebihi' }}
                        </span>
                    </div>
                    <div class="sk-progress-bar-wrap">
                        <div class="sk-progress-bar-fill {{ $u1DiamKurangPass ? 'pass' : 'fail' }}" style="width: {{ min(100, $u1DiamKurang * 10) }}%;"></div>
                    </div>
                </div>

                <div class="sk-diameter-card">
                    <div class="sk-diameter-header">
                        <span style="color: #334155;">Diameter &ge; 4 cm (Min 95%)</span>
                        <span class="{{ $u1DiamLebihPass ? 'highlight-green' : 'highlight-red' }}" style="font-size: 0.825rem; font-weight: 800;">
                            {{ number_format($u1DiamLebih, 1) }}% {{ $u1DiamLebihPass ? '✔ Lolos' : '⚠️ Kurang' }}
                        </span>
                    </div>
                    <div class="sk-progress-bar-wrap">
                        <div class="sk-progress-bar-fill {{ $u1DiamLebihPass ? 'pass' : 'fail' }}" style="width: {{ min(100, $u1DiamLebih) }}%;"></div>
                    </div>
                </div>

                {{-- KONDISI UMBI --}}
                <div style="margin-top: 0.5rem;">
                    <div class="sk-cell-label">Kondisi Kesegaran Umbi:</div>
                    <div class="sk-chips-wrap">
                        @if ($p1Detail?->kondisi_segar) <span class="sk-chip sk-chip-success">✔ Segar</span> @endif
                        @if ($p1Detail?->kondisi_busuk) <span class="sk-chip sk-chip-danger">✖ Busuk</span> @endif
                        @if ($p1Detail?->kondisi_layu) <span class="sk-chip sk-chip-danger">✖ Layu</span> @endif
                        @if ($p1Detail?->kondisi_berjamur) <span class="sk-chip sk-chip-danger">✖ Berjamur</span> @endif
                        @if ($p1Detail?->kondisi_basah) <span class="sk-chip sk-chip-warning">⚠️ Basah</span> @endif
                        @if ($p1Detail?->kondisi_lembek) <span class="sk-chip sk-chip-danger">✖ Lembek</span> @endif
                        @if ($p1Detail?->kondisi_terkelupas) <span class="sk-chip sk-chip-warning">Terkelupas</span> @endif
                        @if (!$p1Detail?->kondisi_segar && !$p1Detail?->kondisi_busuk && !$p1Detail?->kondisi_layu && !$p1Detail?->kondisi_berjamur && !$p1Detail?->kondisi_basah && !$p1Detail?->kondisi_lembek && !$p1Detail?->kondisi_terkelupas)
                            <span class="sk-chip sk-chip-success">✔ Segar (Normal)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- HASIL LAB UJI GORENG (FRYER UJI 1) --}}
            <div class="sk-sub-detail-box" style="border-color: #fed7aa; background: #fffcf8;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                    <span style="font-size: 0.78rem; font-weight: 800; color: #b45309; text-transform: uppercase;">
                        🍟 Lab Uji Goreng (Fryer Kedatangan)
                    </span>
                    @if ($p1->status_uji_goreng === 'MENUNGGU_LAB')
                        <span class="sk-pill" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">⏳ Menunggu Lab</span>
                    @else
                        <span class="sk-pill" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">✔ Selesai Fryer</span>
                    @endif
                </div>

                @if ($p1->status_uji_goreng === 'MENUNGGU_LAB')
                    <div style="text-align: center; padding: 0.85rem; background: #ffffff; border: 1px dashed #fde68a; border-radius: 8px;">
                        <div style="font-size: 0.825rem; font-weight: 700; color: #92400e; margin-bottom: 0.5rem;">
                            Uji rasa &amp; goreng fryer lab belum diisi
                        </div>
                        <button type="button" onclick="openModalUjiFryer('{{ $p1->qc_id }}', '{{ $p1->qc_no }}', '{{ $p1Detail?->qcdtl_id }}')" style="padding: 0.55rem 0.95rem; border: none; border-radius: 8px; background: #d97706; color: #ffffff; font-weight: 800; font-size: 0.8rem; cursor: pointer;">
                            🍟 Lanjutkan Uji Fryer Sekarang
                        </button>
                    </div>
                @else
                    @if ($p1Detail?->fryer_rasa === 'PAHIT')
                        <div class="sk-fryer-alert-pahit">
                            <span>⚠️</span>
                            <div><strong>PERINGATAN: RASA PAHIT!</strong> Batch terdeteksi pahit (mengandung racun sianida).</div>
                        </div>
                    @endif

                    <div class="sk-grid-3">
                        <div class="sk-cell" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.45rem 0.6rem;">
                            <span class="sk-cell-label">Rasa Keripik</span>
                            <span class="sk-cell-value {{ $p1Detail?->fryer_rasa === 'PAHIT' ? 'highlight-red' : 'highlight-green' }}">
                                {{ $p1Detail?->fryer_rasa === 'PAHIT' ? '✖ PAHIT' : '✔ TIDAK PAHIT (GURIH)' }}
                            </span>
                        </div>
                        <div class="sk-cell" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.45rem 0.6rem;">
                            <span class="sk-cell-label">Tekstur</span>
                            <span class="sk-cell-value {{ in_array($p1Detail?->fryer_tekstur, ['ALOT', 'LEMBEK']) ? 'highlight-red' : 'highlight-green' }}">
                                {{ $p1Detail?->fryer_tekstur ?: 'RENYAH' }}
                            </span>
                        </div>
                        <div class="sk-cell" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.45rem 0.6rem;">
                            <span class="sk-cell-label">Minyak</span>
                            <span class="sk-cell-value {{ $p1Detail?->fryer_penampakan === 'OILSOAKED' ? 'highlight-red' : 'highlight-green' }}">
                                {{ $p1Detail?->fryer_penampakan === 'OILSOAKED' ? '✖ OILSOAKED' : '✔ NORMAL' }}
                            </span>
                        </div>
                    </div>

                    <div style="margin-top: 0.6rem;">
                        <span class="sk-cell-label">Defect Frying Kedatangan:</span>
                        <div class="sk-defect-grid">
                            <div class="sk-defect-item"><span class="sk-defect-label">Breakage</span><span class="sk-defect-val">{{ number_format((float)($p1Detail?->defect_breakage_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Cluster</span><span class="sk-defect-val">{{ number_format((float)($p1Detail?->defect_cluster_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Foldover</span><span class="sk-defect-val">{{ number_format((float)($p1Detail?->defect_foldover_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Oilsoaked</span><span class="sk-defect-val">{{ number_format((float)($p1Detail?->defect_oilsoaked_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Gambos</span><span class="sk-defect-val">{{ number_format((float)($p1Detail?->defect_gambos_persen ?? 0), 1) }}%</span></div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- INSPECTOR META UJI 1 --}}
            <div class="sk-inspector-meta">
                <span>Petugas: <strong>{{ $p1->petugas_qc_nama ?: '-' }}</strong> &bull; {{ $p1->tgl_periksa ? $p1->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</span>
                @if (Auth::user()?->canEditQcMobile() && !$isLocked)
                    <a href="{{ route('qc.inbound.edit', [$p1->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" style="color: #0284c7; font-weight: 700; text-decoration: underline;">
                        Edit Uji I
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- =========================================================================
         BAGIAN 2: RINCIAN PENGUJIAN 2 (LANTAI PRODUKSI / PEMBONGKARAN)
         ========================================================================= --}}
    <div id="sectionUji2">
        <div class="sk-card sk-stage-card-p2">
            <div class="sk-stage-header-p2">
                <div class="sk-stage-title-p2">
                    <span>🍟</span> <span>Pengujian II &bull; Lantai Produksi (Wafel)</span>
                </div>
                @if ($hasP2)
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <span class="sk-pill sk-pill-tahap2">#{{ $p2->qc_no }}</span>
                        @if ($u2Grade === 'B')
                            <span class="sk-pill sk-pill-grade-b">GRADE B</span>
                        @elseif ($u2Grade === 'REJECT')
                            <span class="sk-pill sk-pill-grade-reject">REJECT</span>
                        @else
                            <span class="sk-pill sk-pill-grade-a">GRADE A</span>
                        @endif
                    </div>
                @else
                    <span class="sk-pill" style="background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                        Belum Diuji
                    </span>
                @endif
            </div>

            @if ($hasP2)
                {{-- METRIK TIMBANGAN UJI 2 --}}
                <div class="sk-metrics-row">
                    <div class="sk-metric-card">
                        <div class="sk-metric-title">Gross Uji II</div>
                        <div class="sk-metric-val">{{ number_format($u2Gross, 0, ',', '.') }}</div>
                        <div class="sk-metric-sub">kg timbang wafel</div>
                    </div>

                    <div class="sk-metric-card">
                        <div class="sk-metric-title">Refraksi</div>
                        <div class="sk-metric-val" style="color: var(--sk-warning);">{{ number_format($u2Refraksi, 0, ',', '.') }}</div>
                        <div class="sk-metric-sub">{{ number_format($u2RefraksiPersen, 1) }}% potongan</div>
                    </div>

                    <div class="sk-metric-card">
                        <div class="sk-metric-title">Reject (Afkir)</div>
                        <div class="sk-metric-val" style="color: var(--sk-danger);">{{ number_format($u2Reject, 0, ',', '.') }}</div>
                        <div class="sk-metric-sub">kg afkir</div>
                    </div>

                    <div class="sk-metric-card" style="background: #faf5ff; border-color: #e9d5ff;">
                        <div class="sk-metric-title" style="color: #7e22ce;">Netto Uji II</div>
                        <div class="sk-metric-val" style="color: #7e22ce;">{{ number_format($u2Netto, 0, ',', '.') }}</div>
                        <div class="sk-metric-sub" style="color: #7e22ce;">kg lolos produksi</div>
                    </div>
                </div>

                {{-- HASIL UJI GORENG WAJAN PRODUKSI (FRYER UJI 2) --}}
                <div class="sk-sub-detail-box" style="border-color: #d8b4fe; background: #fdfaff;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                        <span style="font-size: 0.78rem; font-weight: 800; color: #7e22ce; text-transform: uppercase;">
                            🍳 Hasil Uji Goreng Wajan Produksi
                        </span>
                        <span class="sk-pill" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                            ✔ Terverifikasi
                        </span>
                    </div>

                    @if ($p2Detail?->fryer_rasa === 'PAHIT')
                        <div class="sk-fryer-alert-pahit">
                            <span>⚠️</span>
                            <div><strong>PERINGATAN UJI 2: RASA PAHIT!</strong> Hentikan penggorengan wafel pada batch ini!</div>
                        </div>
                    @endif

                    <div class="sk-grid-3">
                        <div class="sk-cell" style="background: #ffffff; border: 1px solid #f3e8ff; border-radius: 6px; padding: 0.45rem 0.6rem;">
                            <span class="sk-cell-label">Rasa Produksi</span>
                            <span class="sk-cell-value {{ $p2Detail?->fryer_rasa === 'PAHIT' ? 'highlight-red' : 'highlight-green' }}">
                                {{ $p2Detail?->fryer_rasa === 'PAHIT' ? '✖ PAHIT' : '✔ TIDAK PAHIT (GURIH)' }}
                            </span>
                        </div>
                        <div class="sk-cell" style="background: #ffffff; border: 1px solid #f3e8ff; border-radius: 6px; padding: 0.45rem 0.6rem;">
                            <span class="sk-cell-label">Tekstur Keripik</span>
                            <span class="sk-cell-value {{ in_array($p2Detail?->fryer_tekstur, ['ALOT', 'LEMBEK']) ? 'highlight-red' : 'highlight-green' }}">
                                {{ $p2Detail?->fryer_tekstur ?: 'RENYAH' }}
                            </span>
                        </div>
                        <div class="sk-cell" style="background: #ffffff; border: 1px solid #f3e8ff; border-radius: 6px; padding: 0.45rem 0.6rem;">
                            <span class="sk-cell-label">Minyak</span>
                            <span class="sk-cell-value {{ $p2Detail?->fryer_penampakan === 'OILSOAKED' ? 'highlight-red' : 'highlight-green' }}">
                                {{ $p2Detail?->fryer_penampakan === 'OILSOAKED' ? '✖ OILSOAKED' : '✔ NORMAL' }}
                            </span>
                        </div>
                    </div>

                    <div style="margin-top: 0.6rem;">
                        <span class="sk-cell-label">Defect Frying Wajan Produksi:</span>
                        <div class="sk-defect-grid">
                            <div class="sk-defect-item"><span class="sk-defect-label">Breakage</span><span class="sk-defect-val">{{ number_format((float)($p2Detail?->defect_breakage_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Cluster</span><span class="sk-defect-val">{{ number_format((float)($p2Detail?->defect_cluster_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Foldover</span><span class="sk-defect-val">{{ number_format((float)($p2Detail?->defect_foldover_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Oilsoaked</span><span class="sk-defect-val">{{ number_format((float)($p2Detail?->defect_oilsoaked_persen ?? 0), 1) }}%</span></div>
                            <div class="sk-defect-item"><span class="sk-defect-label">Gambos</span><span class="sk-defect-val">{{ number_format((float)($p2Detail?->defect_gambos_persen ?? 0), 1) }}%</span></div>
                        </div>
                    </div>
                </div>

                {{-- INSPECTOR META UJI 2 --}}
                <div class="sk-inspector-meta">
                    <span>Petugas Produksi: <strong>{{ $p2->petugas_qc_nama ?: '-' }}</strong> &bull; {{ $p2->tgl_periksa ? $p2->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</span>
                    @if (Auth::user()?->canEditQcMobile() && !$isLocked)
                        <a href="{{ route('qc.inbound.edit', [$p2->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" style="color: #7e22ce; font-weight: 700; text-decoration: underline;">
                            Edit Uji II
                        </a>
                    @endif
                </div>

            @else
                {{-- BELUM ADA PENGUJIAN 2 --}}
                <div class="sk-p2-empty-state">
                    <div style="font-size: 1.85rem; margin-bottom: 0.25rem;">🍟⏳</div>
                    <div style="font-weight: 900; font-size: 0.95rem; color: #581c87;">
                        Pengujian II (Lantai Produksi) Belum Dicatat
                    </div>
                    <div style="font-size: 0.8rem; color: #7e22ce; margin: 0.4rem 0 0.85rem; line-height: 1.45;">
                        Sisa muatan bak truk yang belum dibongkar pada lini wafel: <strong>{{ number_format($sisaBakTruk, 0, ',', '.') }} kg</strong>. Lakukan uji kedua saat pembongkaran produksi keripik wafel berlangsung.
                    </div>

                    @if (Auth::user()?->canCreateQc())
                        <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $p1->qc_id, 'tahap' => 2, 'view' => 'mobile']) }}" class="sk-p2-empty-btn">
                            <span>🍟 Lakukan Pengujian II Sekarang &rarr;</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- =========================================================================
         KARTU 3: INFO PANEN, ARMADA & KEBERSIHAN HALAL
         ========================================================================= --}}
    <div class="sk-card">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>🚚</span> <span>Panen, Supplier &amp; Armada</span>
            </h2>
            <span class="sk-card-badge-right">HACCP Inbound</span>
        </div>

        <div class="sk-grid-2">
            <div class="sk-cell">
                <span class="sk-cell-label">Nama Komoditas</span>
                <span class="sk-cell-value highlight-blue">
                    {{ $p1Detail?->barang?->barang_nm ?: ($p1->nama_jenis ?: 'Singkong Segar') }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Mitra Supplier</span>
                <span class="sk-cell-value">{{ $p1->supplier?->supplier_nm ?? '-' }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Lokasi Asal Panen</span>
                <span class="sk-cell-value">{{ $p1->lokasi_panen ?: ($p1Detail?->lokasi_panen ?: '-') }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Umur Singkong</span>
                <span class="sk-cell-value">
                    {{ $p1->umur_singkong_bln ? $p1->umur_singkong_bln . ' Bulan' : ($p1Detail?->umur_singkong_bulan ? $p1Detail->umur_singkong_bulan . ' Bulan' : '-') }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Tanggal Panen</span>
                <span class="sk-cell-value">
                    {{ $p1->tgl_panen ? \Carbon\Carbon::parse($p1->tgl_panen)->format('d/m/Y') : '-' }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Sampel Uji Diambil</span>
                <span class="sk-cell-value highlight-amber">
                    {{ $p1->jumlah_sample_kg ? number_format((float)$p1->jumlah_sample_kg, 1) . ' kg' : '25.0 kg' }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">No. Surat Jalan / DO</span>
                <span class="sk-cell-value">{{ $p1->nomor_do ?: ($p1->surat_jalan_supplier ?: '-') }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Referensi PO</span>
                <span class="sk-cell-value">
                    @if ($p1->po)
                        <span style="color: #0284c7; font-weight: 800;">{{ $p1->po->po_no }}</span>
                    @else
                        <span style="color: #64748b;">Non-PO (Pembelian Langsung)</span>
                    @endif
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Plat Nomor Truk</span>
                <span class="sk-cell-value highlight-blue">{{ $p1->plat_nomor_truk ?: '-' }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Nama Sopir</span>
                <span class="sk-cell-value">{{ $p1->sopir_nama ?: '-' }}</span>
            </div>
        </div>

        {{-- VERIFIKASI KEBERSIHAN & AUDIT HALAL ARMADA --}}
        <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px dashed #e2e8f0; display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.65rem;">
            <div style="background: {{ $p1->bebas_cemaran_st ? '#f0fdf4' : '#fef2f2' }}; border: 1px solid {{ $p1->bebas_cemaran_st ? '#86efac' : '#fca5a5' }}; border-radius: 8px; padding: 0.5rem 0.65rem;">
                <div style="font-size: 0.68rem; font-weight: 800; color: {{ $p1->bebas_cemaran_st ? '#166534' : '#991b1b' }}; text-transform: uppercase;">Kebersihan Armada</div>
                <div style="font-size: 0.775rem; font-weight: 700; color: {{ $p1->bebas_cemaran_st ? '#15803d' : '#dc2626' }}; margin-top: 2px;">
                    {{ $p1->bebas_cemaran_st ? '✔ Bebas Cemaran' : '✖ Ada Kontaminasi' }}
                </div>
            </div>

            <div style="background: {{ !$p1->angkut_barang_haram_st ? '#f0fdf4' : '#fef2f2' }}; border: 1px solid {{ !$p1->angkut_barang_haram_st ? '#86efac' : '#fca5a5' }}; border-radius: 8px; padding: 0.5rem 0.65rem;">
                <div style="font-size: 0.68rem; font-weight: 800; color: {{ !$p1->angkut_barang_haram_st ? '#166534' : '#991b1b' }}; text-transform: uppercase;">Audit Angkutan Halal</div>
                <div style="font-size: 0.775rem; font-weight: 700; color: {{ !$p1->angkut_barang_haram_st ? '#15803d' : '#dc2626' }}; margin-top: 2px;">
                    {{ !$p1->angkut_barang_haram_st ? '✔ Bebas Barang Haram' : '✖ Ada Barang Haram' }}
                </div>
            </div>
        </div>

        @if ($p1->komentar_transportasi)
            <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #64748b; background: #f8fafc; border-radius: 6px; padding: 0.4rem 0.6rem;">
                Catatan Armada: <em>"{{ $p1->komentar_transportasi }}"</em>
            </div>
        @endif
    </div>

    {{-- =========================================================================
         KARTU 4: KEPUTUSAN & TANDA TANGAN HACCP
         ========================================================================= --}}
    <div class="sk-card">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>💬</span> <span>Keputusan &amp; Catatan QC</span>
            </h2>
            <span class="sk-card-badge-right">ACC Valid</span>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <div class="sk-cell-label">Status Keputusan Akhir Truk:</div>
            <div style="font-size: 1.05rem; font-weight: 900; margin-top: 0.2rem; color: {{ $p1->status_qc !== 'DITOLAK_TOTAL' ? '#16a34a' : '#dc2626' }};">
                @if ($p1->status_qc !== 'DITOLAK_TOTAL')
                    ✔ DITERIMA PABRIK (Total {{ number_format($totalNettoTruk, 0, ',', '.') }} kg Lolos)
                @else
                    ✖ DITOLAK TOTAL DI GERBANG
                @endif
            </div>
        </div>

        @if ($p1Detail?->catatan_dtl ?: ($p1->catatan_umum ?: ($hasP2 && $p2Detail?->catatan_dtl ? $p2Detail->catatan_dtl : null)))
            <div style="background: #f8fafc; border-left: 3px solid #0284c7; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-style: italic; color: #334155; margin-bottom: 0.75rem;">
                "{{ $p1Detail?->catatan_dtl ?: ($p1->catatan_umum ?: ($p2Detail?->catatan_dtl ?? '')) }}"
            </div>
        @endif

        <div class="sk-sign-grid">
            <div class="sk-sign-box">
                <div class="sk-sign-role">1. Petugas QC Pemeriksa</div>
                <div class="sk-sign-name">{{ $p1->petugas_qc_nama ?: '-' }}</div>
                <div class="sk-sign-badge">✔ Terverifikasi Lapangan</div>
            </div>
            <div class="sk-sign-box">
                <div class="sk-sign-role">2. QC Supervisor</div>
                <div class="sk-sign-name">{{ $p1->qc_supervisor_nama ?: 'Supervisor QC / Kepala Gudang' }}</div>
                <div class="sk-sign-badge">✔ Disetujui (ACC)</div>
            </div>
        </div>
    </div>

</div>

{{-- FIXED BOTTOM DOCK (MOBILE ACTIONS) --}}
<div class="sk-bottom-dock">
    <div class="sk-bottom-dock-inner">
        @if ($p1->status_qc === 'SIAP_GUDANG' || ($hasP2 && $p2->status_qc === 'SIAP_GUDANG'))
            <div class="sk-dock-status" style="background: #e0f2fe; color: #0369a1; border: 1.5px solid #7dd3fc;">
                <span>⏳</span> <span>Siap Ditarik Gudang (GRN)</span>
            </div>
        @elseif ($p1->status_qc === 'DITERIMA_PARSIAL' || $totalRejectTruk > 0)
            <div class="sk-dock-status" style="background: #fff7ed; color: #c2410c; border: 1.5px solid #fdba74;">
                <span>⚠️</span> <span>Masuk Parsial (Ada Reject)</span>
            </div>
        @elseif ($p1->status_qc === 'DITERIMA_GUDANG')
            <div class="sk-dock-status" style="background: #dcfce7; color: #15803d; border: 1.5px solid #86efac;">
                <span>✅</span> <span>Selesai (Masuk Penuh)</span>
            </div>
        @elseif ($p1->status_qc === 'DITOLAK_TOTAL')
            <div class="sk-dock-status" style="background: #fee2e2; color: #b91c1c; border: 1.5px solid #fca5a5;">
                <span>❌</span> <span>Ditolak di Gerbang</span>
            </div>
        @else
            <div class="sk-dock-status" style="background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1;">
                <span>📋</span> <span>{{ $p1->status_qc }}</span>
            </div>
        @endif

        @if (Auth::user()?->canEditQcMobile() && !$isLocked)
            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="sk-dock-btn-edit">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Uji</span>
            </a>
        @else
            <button type="button" class="sk-dock-btn-locked" disabled>
                <span>🔒 Terkunci</span>
            </button>
        @endif
    </div>
</div>

{{-- MODAL UJI GORENG FRYER --}}
@include('gudang.qc.partials.singkong.modal-uji-fryer')

@endsection
