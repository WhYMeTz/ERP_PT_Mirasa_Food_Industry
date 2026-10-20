@extends('layouts.qc-mobile')

@section('title', 'Detail QC Singkong: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/singkong/singkong-detail.css') }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalRefraksi = (float) $qc->details->sum('qty_refraksi');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();

    $grade = $firstDetail?->grade_cd ?? 'A';
    $isPengujian2 = ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2';

    $diamKurang = (float) ($firstDetail?->diameter_kurang_4cm_persen ?? 0);
    $diamLebih = (float) ($firstDetail?->diameter_lebih_4cm_persen ?? 0);
    $diamKurangPass = $diamKurang <= 5;
    $diamLebihPass = $diamLebih >= 95;
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

    {{-- HERO CARD --}}
    <div class="sk-hero">
        <div class="sk-hero-badges">
            <span class="sk-pill sk-pill-commodity">🥔 SINGKONG</span>

            <span class="sk-pill {{ $isPengujian2 ? 'sk-pill-tahap2' : 'sk-pill-tahap1' }}">
                {{ $isPengujian2 ? '🍟 PENGUJIAN II (PRODUKSI)' : '🚛 PENGUJIAN I (KEDATANGAN)' }}
            </span>

            @if ($grade === 'B')
                <span class="sk-pill sk-pill-grade-b">🟡 GRADE B</span>
            @elseif ($grade === 'REJECT')
                <span class="sk-pill sk-pill-grade-reject">❌ REJECT / AFKIR</span>
            @else
                <span class="sk-pill sk-pill-grade-a">🟢 GRADE A</span>
            @endif
        </div>

        <div class="sk-hero-title-row">
            <h1 class="sk-hero-ticket-no">{{ $qc->qc_no }}</h1>

            @if ($qc->status_qc === 'SIAP_GUDANG')
                <span class="sk-hero-status siap">⏳ Siap Gudang</span>
            @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
                <span class="sk-hero-status parsial">⚠️ Masuk Parsial</span>
            @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                <span class="sk-hero-status diterima">✅ Masuk Penuh</span>
            @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                <span class="sk-hero-status tolak">❌ Ditolak</span>
            @else
                <span class="sk-hero-status siap">{{ $qc->status_qc }}</span>
            @endif
        </div>

        <div class="sk-hero-meta-grid">
            <div class="sk-hero-meta-item">
                <span>📅</span>
                <span>Periksa: <strong>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong></span>
            </div>
            <div class="sk-hero-meta-item">
                <span>👤</span>
                <span>Petugas: <strong>{{ $qc->petugas_qc_nama ?: '-' }}</strong></span>
            </div>
            <div class="sk-hero-meta-item">
                <span>🏭</span>
                <span>Gudang: <strong>{{ $qc->gudang?->gudang_nm ?: '-' }}</strong></span>
            </div>
            @if(!empty($qc->batch_no))
                <div class="sk-hero-meta-item">
                    <span>🏷️</span>
                    <span>Batch: <strong>{{ $qc->batch_no }}</strong></span>
                </div>
            @endif
        </div>

        {{-- INFO JIKA SUDAH DIPROSES GRN GUDANG --}}
        @if ($qc->terima)
            <div class="sk-hero-relation-box" style="background: rgba(22, 163, 74, 0.25); border-color: rgba(134, 239, 172, 0.4);">
                <span>📦 Ditarik ke Gudang &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong></span>
                <span style="font-size: 0.72rem; color: #dcfce7;">{{ $qc->terima->terima_tgl ? \Carbon\Carbon::parse($qc->terima->terima_tgl)->format('d/m/Y') : '' }}</span>
            </div>
        @endif

        {{-- JIKA INI PENGUJIAN 2: LINK KE TRUK INDUK --}}
        @if ($isPengujian2 && $qc->parentQc)
            <div class="sk-hero-relation-box">
                <span>🔗 Truk Induk Kedatangan: <a href="{{ route('qc.inbound.show', [$qc->parentQc->qc_id, 'view' => 'mobile']) }}">#{{ $qc->parentQc->qc_no }}</a></span>
                <span>Plat: <strong>{{ $qc->parentQc->plat_nomor_truk ?: '-' }}</strong></span>
            </div>
        @endif

        {{-- JIKA INI PENGUJIAN 1 DAN ADA LAPORAN PENGUJIAN 2 --}}
        @if (!$isPengujian2 && $qc->pengujian2List && $qc->pengujian2List->count() > 0)
            <div class="sk-hero-relation-box" style="background: rgba(147, 51, 234, 0.25); border-color: rgba(216, 180, 254, 0.4);">
                <span>🍟 Ada {{ $qc->pengujian2List->count() }} Laporan Pengujian II (Produksi):</span>
                <div>
                    @foreach ($qc->pengujian2List as $p2)
                        <a href="{{ route('qc.inbound.show', [$p2->qc_id, 'view' => 'mobile']) }}" style="margin-left: 0.35rem;">#{{ $p2->qc_no }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- KARTU 1: TIMBANGAN & TONASE KEDATANGAN --}}
    <div class="sk-card">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>⚖️</span> <span>Hasil Timbangan &amp; Tonase</span>
            </h2>
            <span class="sk-card-badge-right">Satuan: kg</span>
        </div>

        <div class="sk-metrics-row">
            <div class="sk-metric-card">
                <div class="sk-metric-title">Gross (Bruto)</div>
                <div class="sk-metric-val">{{ number_format($totalGross, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">kg timbang</div>
            </div>

            <div class="sk-metric-card">
                <div class="sk-metric-title">Refraksi</div>
                <div class="sk-metric-val" style="color: var(--sk-warning);">{{ number_format($totalRefraksi, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">{{ number_format($firstDetail?->refraksi_persen ?? 0, 1) }}% potongan</div>
            </div>

            <div class="sk-metric-card">
                <div class="sk-metric-title">Reject (Afkir)</div>
                <div class="sk-metric-val" style="color: var(--sk-danger);">{{ number_format($totalReject, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">kg ditolak</div>
            </div>

            <div class="sk-metric-card netto-hero">
                <div class="sk-metric-title">Netto Lolos</div>
                <div class="sk-metric-val">{{ number_format($totalNetto, 0, ',', '.') }}</div>
                <div class="sk-metric-sub">kg diterima pabrik</div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.85rem; padding-top: 0.65rem; border-top: 1px dashed #e2e8f0; font-size: 0.775rem;">
            <span style="color: var(--sk-text-muted);">Kadar Air Singkong:</span>
            <strong style="color: #0f172a; font-size: 0.85rem;">{{ $firstDetail?->kadar_air_persen !== null ? number_format((float)$firstDetail->kadar_air_persen, 1) . '%' : '-' }}</strong>
        </div>
    </div>

    {{-- KARTU 2: INFO PANEN, SUPPLIER & ARMADA --}}
    <div class="sk-card">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>🚚</span> <span>Panen, Supplier &amp; Armada</span>
            </h2>
            <span class="sk-card-badge-right">HACCP Inbound</span>
        </div>

        <div class="sk-grid-2">
            <div class="sk-cell">
                <span class="sk-cell-label">Nama Barang (Master)</span>
                <span class="sk-cell-value highlight-blue">
                    {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: 'Singkong Segar') }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Mitra Supplier / Petani</span>
                <span class="sk-cell-value">{{ $qc->supplier?->supplier_nm ?? '-' }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Lokasi Asal Panen</span>
                <span class="sk-cell-value">{{ $qc->lokasi_panen ?: ($firstDetail?->lokasi_panen ?: '-') }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Umur Singkong</span>
                <span class="sk-cell-value">
                    {{ $qc->umur_singkong_bln ? $qc->umur_singkong_bln . ' Bulan' : ($firstDetail?->umur_singkong_bulan ? $firstDetail->umur_singkong_bulan . ' Bulan' : '-') }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Tanggal Panen</span>
                <span class="sk-cell-value">
                    {{ $qc->tgl_panen ? \Carbon\Carbon::parse($qc->tgl_panen)->format('d/m/Y') : '-' }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Sampel Uji Diambil</span>
                <span class="sk-cell-value highlight-amber">
                    {{ $qc->jumlah_sample_kg ? number_format((float)$qc->jumlah_sample_kg, 1) . ' kg' : ($firstDetail?->sample_kg ? number_format((float)$firstDetail->sample_kg, 1) . ' kg' : '25.0 kg') }}
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">No. Surat Jalan / DO</span>
                <span class="sk-cell-value">{{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Referensi PO</span>
                <span class="sk-cell-value">
                    @if ($qc->po)
                        <span style="color: #0284c7; font-weight: 800;">{{ $qc->po->po_no }}</span>
                    @else
                        <span style="color: #64748b;">Non-PO (Pembelian Langsung)</span>
                    @endif
                </span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Plat Nomor Truk</span>
                <span class="sk-cell-value highlight-blue">{{ $qc->plat_nomor_truk ?: '-' }}</span>
            </div>

            <div class="sk-cell">
                <span class="sk-cell-label">Nama Sopir</span>
                <span class="sk-cell-value">{{ $qc->sopir_nama ?: '-' }}</span>
            </div>
        </div>

        {{-- VERIFIKASI KEBERSIHAN & AUDIT HALAL ARMADA --}}
        <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px dashed #e2e8f0; display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.65rem;">
            <div style="background: {{ $qc->bebas_cemaran_st ? '#f0fdf4' : '#fef2f2' }}; border: 1px solid {{ $qc->bebas_cemaran_st ? '#86efac' : '#fca5a5' }}; border-radius: 8px; padding: 0.5rem 0.65rem;">
                <div style="font-size: 0.68rem; font-weight: 800; color: {{ $qc->bebas_cemaran_st ? '#166534' : '#991b1b' }}; text-transform: uppercase;">Kebersihan Armada</div>
                <div style="font-size: 0.775rem; font-weight: 700; color: {{ $qc->bebas_cemaran_st ? '#15803d' : '#dc2626' }}; margin-top: 2px;">
                    {{ $qc->bebas_cemaran_st ? '✔ Bebas Cemaran' : '✖ Ada Kontaminasi' }}
                </div>
            </div>

            <div style="background: {{ !$qc->angkut_barang_haram_st ? '#f0fdf4' : '#fef2f2' }}; border: 1px solid {{ !$qc->angkut_barang_haram_st ? '#86efac' : '#fca5a5' }}; border-radius: 8px; padding: 0.5rem 0.65rem;">
                <div style="font-size: 0.68rem; font-weight: 800; color: {{ !$qc->angkut_barang_haram_st ? '#166534' : '#991b1b' }}; text-transform: uppercase;">Audit Angkutan Halal</div>
                <div style="font-size: 0.775rem; font-weight: 700; color: {{ !$qc->angkut_barang_haram_st ? '#15803d' : '#dc2626' }}; margin-top: 2px;">
                    {{ !$qc->angkut_barang_haram_st ? '✔ Bebas Barang Haram' : '✖ Ada Barang Haram' }}
                </div>
            </div>
        </div>

        @if ($qc->komentar_transportasi)
            <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #64748b; background: #f8fafc; border-radius: 6px; padding: 0.4rem 0.6rem;">
                Catatan Armada: <em>"{{ $qc->komentar_transportasi }}"</em>
            </div>
        @endif
    </div>

    {{-- KARTU 3: PARAMETER FISIK & SORTIR DIAMETER --}}
    <div class="sk-card">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>📏</span> <span>Parameter Fisik &amp; Sortir Umbi</span>
            </h2>
            <span class="sk-card-badge-right">Standar Mutu</span>
        </div>

        {{-- STATUS RAW MATERIAL --}}
        <div style="display: flex; align-items: center; justify-content: space-between; background: {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '#f0fdf4' : '#fef2f2' }}; border: 1.5px solid {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '#86efac' : '#fca5a5' }}; border-radius: 10px; padding: 0.65rem 0.85rem; margin-bottom: 0.85rem;">
            <div>
                <span style="font-size: 0.7rem; font-weight: 800; color: {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '#166534' : '#991b1b' }}; text-transform: uppercase;">Status Raw Material</span>
                <div style="font-size: 0.95rem; font-weight: 900; color: {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '#15803d' : '#dc2626' }};">
                    {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔ OK (STANDAR PABRIK)' : '✖ TIDAK STANDAR' }}
                </div>
            </div>
            <span style="font-size: 1.35rem;">
                {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '🌿' : '⚠️' }}
            </span>
        </div>

        {{-- SORTIR DIAMETER --}}
        <div class="sk-diameter-card">
            <div class="sk-diameter-header">
                <span style="color: #334155;">Diameter &lt; 4 cm (Standar Max 5%)</span>
                <span class="{{ $diamKurangPass ? 'highlight-green' : 'highlight-red' }}" style="font-size: 0.875rem; font-weight: 800;">
                    {{ number_format($diamKurang, 1) }}% {{ $diamKurangPass ? '✔ Lolos' : '⚠️ Melebihi Batas' }}
                </span>
            </div>
            <div class="sk-progress-bar-wrap">
                <div class="sk-progress-bar-fill {{ $diamKurangPass ? 'pass' : 'fail' }}" style="width: {{ min(100, $diamKurang * 10) }}%;"></div>
            </div>
        </div>

        <div class="sk-diameter-card">
            <div class="sk-diameter-header">
                <span style="color: #334155;">Diameter &ge; 4 cm (Standar Min 95%)</span>
                <span class="{{ $diamLebihPass ? 'highlight-green' : 'highlight-red' }}" style="font-size: 0.875rem; font-weight: 800;">
                    {{ number_format($diamLebih, 1) }}% {{ $diamLebihPass ? '✔ Lolos' : '⚠️ Kurang dari 95%' }}
                </span>
            </div>
            <div class="sk-progress-bar-wrap">
                <div class="sk-progress-bar-fill {{ $diamLebihPass ? 'pass' : 'fail' }}" style="width: {{ min(100, $diamLebih) }}%;"></div>
            </div>
        </div>

        {{-- KONDISI KESEGARAN UMBI --}}
        <div style="margin-top: 0.85rem;">
            <div class="sk-cell-label">Kondisi Kesegaran Umbi Singkong:</div>
            <div class="sk-chips-wrap">
                @if ($firstDetail?->kondisi_segar)
                    <span class="sk-chip sk-chip-success">✔ Segar</span>
                @endif
                @if ($firstDetail?->kondisi_busuk)
                    <span class="sk-chip sk-chip-danger">✖ Busuk</span>
                @endif
                @if ($firstDetail?->kondisi_layu)
                    <span class="sk-chip sk-chip-danger">✖ Layu</span>
                @endif
                @if ($firstDetail?->kondisi_berjamur)
                    <span class="sk-chip sk-chip-danger">✖ Berjamur</span>
                @endif
                @if ($firstDetail?->kondisi_basah)
                    <span class="sk-chip sk-chip-warning">⚠️ Basah</span>
                @endif
                @if ($firstDetail?->kondisi_lembek)
                    <span class="sk-chip sk-chip-danger">✖ Lembek</span>
                @endif
                @if ($firstDetail?->kondisi_terkelupas)
                    <span class="sk-chip sk-chip-warning">Kulit Terkelupas</span>
                @endif

                @if (!$firstDetail?->kondisi_segar && !$firstDetail?->kondisi_busuk && !$firstDetail?->kondisi_layu && !$firstDetail?->kondisi_berjamur && !$firstDetail?->kondisi_basah && !$firstDetail?->kondisi_lembek && !$firstDetail?->kondisi_terkelupas)
                    <span class="sk-chip sk-chip-success">✔ Segar (Normal)</span>
                @endif
            </div>
        </div>
    </div>

    {{-- KARTU 4: HASIL PENGUJIAN UJI GORENG (LAB FRYER) --}}
    <div class="sk-card">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>🍟</span> <span>Hasil Lab Uji Goreng (Fryer)</span>
            </h2>

            @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                <span class="sk-pill" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                    ⏳ Menunggu Lab
                </span>
            @else
                <span class="sk-pill" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                    ✔ Selesai Uji Fryer
                </span>
            @endif
        </div>

        @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
            {{-- BANNER MENUNGGU LAB FRYER --}}
            <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 1.1rem; text-align: center;">
                <div style="font-size: 1.8rem; margin-bottom: 0.25rem;">🍟⏳</div>
                <div style="font-weight: 800; font-size: 0.95rem; color: #92400e;">
                    Pengujian II / Lab Fryer Belum Lengkap
                </div>
                <div style="font-size: 0.8rem; color: #b45309; margin: 0.35rem 0 0.85rem; line-height: 1.4;">
                    Pemeriksaan rasa keripik, kerenyahan tekstur, dan defect penggorengan wajan belum diisi.
                </div>
                <button type="button" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $firstDetail?->qcdtl_id }}')" style="width: 100%; padding: 0.75rem; border: none; border-radius: 10px; background: #d97706; color: #ffffff; font-weight: 800; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.45rem; box-shadow: 0 3px 8px rgba(217, 119, 6, 0.3);">
                    <span>🍟</span> <span>Lanjutkan Uji Fryer Sekarang</span>
                </button>
            </div>
        @else
            {{-- JIKA SUDAH SELESAI UJI GORENG --}}
            @if ($firstDetail?->fryer_rasa === 'PAHIT')
                <div class="sk-fryer-alert-pahit">
                    <span style="font-size: 1.25rem;">⚠️</span>
                    <div>
                        <strong>PERINGATAN BAHAN BAKU: RASA PAHIT!</strong>
                        <div style="font-size: 0.75rem; margin-top: 1px;">Singkong ini mengandung asam sianida / rasa pahit. Batch harus ditolak atau diafkir untuk keamanan pangan.</div>
                    </div>
                </div>
            @endif

            <div class="sk-grid-3">
                <div class="sk-cell" style="background: #f8fafc; border-radius: 8px; padding: 0.5rem 0.65rem;">
                    <span class="sk-cell-label">Rasa Keripik</span>
                    <span class="sk-cell-value {{ $firstDetail?->fryer_rasa === 'PAHIT' ? 'highlight-red' : 'highlight-green' }}">
                        {{ $firstDetail?->fryer_rasa === 'PAHIT' ? '✖ PAHIT' : '✔ TIDAK PAHIT (GURIH)' }}
                    </span>
                </div>

                <div class="sk-cell" style="background: #f8fafc; border-radius: 8px; padding: 0.5rem 0.65rem;">
                    <span class="sk-cell-label">Tekstur Keripik</span>
                    <span class="sk-cell-value {{ in_array($firstDetail?->fryer_tekstur, ['ALOT', 'LEMBEK']) ? 'highlight-red' : 'highlight-green' }}">
                        {{ $firstDetail?->fryer_tekstur ?: 'RENYAH' }}
                    </span>
                </div>

                <div class="sk-cell" style="background: #f8fafc; border-radius: 8px; padding: 0.5rem 0.65rem;">
                    <span class="sk-cell-label">Penampakan Minyak</span>
                    <span class="sk-cell-value {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? 'highlight-red' : 'highlight-green' }}">
                        {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? '✖ OILSOAKED' : '✔ NORMAL' }}
                    </span>
                </div>
            </div>

            {{-- DEFECT FRYING GRID --}}
            <div style="margin-top: 0.75rem;">
                <span class="sk-cell-label">Persentase Cacat Penggorengan (Defect Frying):</span>
                <div class="sk-defect-grid">
                    <div class="sk-defect-item">
                        <span class="sk-defect-label">Breakage</span>
                        <span class="sk-defect-val">{{ number_format((float)($firstDetail?->defect_breakage_persen ?? 0), 1) }}%</span>
                    </div>
                    <div class="sk-defect-item">
                        <span class="sk-defect-label">Cluster</span>
                        <span class="sk-defect-val">{{ number_format((float)($firstDetail?->defect_cluster_persen ?? 0), 1) }}%</span>
                    </div>
                    <div class="sk-defect-item">
                        <span class="sk-defect-label">Foldover</span>
                        <span class="sk-defect-val">{{ number_format((float)($firstDetail?->defect_foldover_persen ?? 0), 1) }}%</span>
                    </div>
                    <div class="sk-defect-item">
                        <span class="sk-defect-label">Oilsoaked</span>
                        <span class="sk-defect-val">{{ number_format((float)($firstDetail?->defect_oilsoaked_persen ?? 0), 1) }}%</span>
                    </div>
                    <div class="sk-defect-item">
                        <span class="sk-defect-label">Gambos</span>
                        <span class="sk-defect-val">{{ number_format((float)($firstDetail?->defect_gambos_persen ?? 0), 1) }}%</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.65rem; padding-top: 0.5rem; border-top: 1px dashed #e2e8f0; font-size: 0.725rem; color: #64748b;">
                <span>Diuji oleh: <strong>{{ $qc->petugas_uji_goreng ?: 'Petugas Lab' }}</strong> ({{ $qc->tgl_uji_goreng ? \Carbon\Carbon::parse($qc->tgl_uji_goreng)->format('d/m/Y H:i') : '-' }})</span>
                @if (Auth::user()?->canEditQcMobile() && !$isLocked)
                    <button type="button" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $firstDetail?->qcdtl_id }}', { rasa: '{{ $firstDetail?->fryer_rasa }}', tekstur: '{{ $firstDetail?->fryer_tekstur }}', penampakan: '{{ $firstDetail?->fryer_penampakan }}', breakage: '{{ $firstDetail?->defect_breakage_persen }}', cluster: '{{ $firstDetail?->defect_cluster_persen }}', gambos: '{{ $firstDetail?->defect_gambos_persen }}' })" style="background: none; border: none; color: #0284c7; font-weight: 700; cursor: pointer; text-decoration: underline; font-size: 0.75rem;">
                        Koreksi Lab Fryer
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- KARTU 5: ALUR PENGUJIAN 2 (LANTAI PRODUKSI / SISA BAK) --}}
    @if (!$isPengujian2)
        <div class="sk-card">
            <div class="sk-card-header">
                <h2 class="sk-card-title">
                    <span>🍟</span> <span>QC Pengujian II (Lantai Produksi)</span>
                </h2>
                @if ($qc->isPengujian2Done())
                    <span class="sk-pill" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                        ✔ Selesai ({{ $qc->pengujian2List->count() }}x)
                    </span>
                @else
                    <span class="sk-pill" style="background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                        Siap Diuji II
                    </span>
                @endif
            </div>

            <p style="font-size: 0.78rem; color: #64748b; margin: 0 0 0.65rem 0; line-height: 1.45;">
                Pemeriksaan fisik organoleptik wajan saat sisa muatan bak truk dibongkar pada proses produksi wafel.
            </p>

            @if ($qc->pengujian2List && $qc->pengujian2List->count() > 0)
                <div style="margin-bottom: 0.65rem; display: flex; flex-direction: column; gap: 0.4rem;">
                    @foreach ($qc->pengujian2List as $p2)
                        <a href="{{ route('qc.inbound.show', [$p2->qc_id, 'view' => 'mobile']) }}" style="display: flex; align-items: center; justify-content: space-between; background: #faf5ff; border: 1px solid #d8b4fe; border-radius: 8px; padding: 0.55rem 0.85rem; text-decoration: none; color: #7e22ce; font-size: 0.8rem; font-weight: 700;">
                            <span>🍟 {{ $p2->qc_no }} ({{ $p2->tgl_periksa ? $p2->tgl_periksa->format('d/m/Y H:i') : '-' }})</span>
                            <span style="font-size: 0.75rem; color: #9333ea; font-weight: 800;">Buka Hasil &rarr;</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if (Auth::user()?->canCreateQc() && !$qc->isPengujian2Done())
                <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'view' => 'mobile']) }}" style="width: 100%; padding: 0.75rem; border-radius: 10px; background: #9333ea; color: #ffffff; font-weight: 800; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.45rem; box-shadow: 0 3px 6px -1px rgba(147, 51, 234, 0.35);">
                    <span>🍟</span> <span>Lakukan Pengujian 2 Sekarang &rarr;</span>
                </a>
            @endif
        </div>
    @endif

    {{-- KARTU 6: KEPUTUSAN, CATATAN & TANDA TANGAN --}}
    <div class="sk-card">
        <div class="sk-card-header">
            <h2 class="sk-card-title">
                <span>💬</span> <span>Keputusan &amp; Catatan QC</span>
            </h2>
            <span class="sk-card-badge-right">ACC Valid</span>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <div class="sk-cell-label">Status Keputusan Akhir:</div>
            <div style="font-size: 1.05rem; font-weight: 900; margin-top: 0.2rem; color: {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '#16a34a' : '#dc2626' }};">
                @if ($qc->status_qc !== 'DITOLAK_TOTAL')
                    ✔ DITERIMA PABRIK ({{ number_format($totalNetto, 0, ',', '.') }} kg Lolos)
                @else
                    ✖ DITOLAK TOTAL DI GERBANG
                @endif
            </div>
        </div>

        @if ($firstDetail?->catatan_dtl ?: $qc->catatan_umum)
            <div style="background: #f8fafc; border-left: 3px solid #0284c7; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-style: italic; color: #334155; margin-bottom: 0.75rem;">
                "{{ $firstDetail?->catatan_dtl ?: $qc->catatan_umum }}"
            </div>
        @endif

        <div class="sk-sign-grid">
            <div class="sk-sign-box">
                <div class="sk-sign-role">1. Petugas QC Pemeriksa</div>
                <div class="sk-sign-name">{{ $qc->petugas_qc_nama ?: '-' }}</div>
                <div class="sk-sign-badge">✔ Terverifikasi Lapangan</div>
            </div>
            <div class="sk-sign-box">
                <div class="sk-sign-role">2. QC Supervisor</div>
                <div class="sk-sign-name">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC / Kepala Gudang' }}</div>
                <div class="sk-sign-badge">✔ Disetujui (ACC)</div>
            </div>
        </div>
    </div>

</div>

{{-- FIXED BOTTOM DOCK (MOBILE ACTIONS) --}}
<div class="sk-bottom-dock">
    <div class="sk-bottom-dock-inner">
        @if ($qc->status_qc === 'SIAP_GUDANG')
            <div class="sk-dock-status" style="background: #e0f2fe; color: #0369a1; border: 1.5px solid #7dd3fc;">
                <span>⏳</span> <span>Siap Ditarik Gudang (GRN)</span>
            </div>
        @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
            <div class="sk-dock-status" style="background: #fff7ed; color: #c2410c; border: 1.5px solid #fdba74;">
                <span>⚠️</span> <span>Masuk Parsial (Ada Reject)</span>
            </div>
        @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
            <div class="sk-dock-status" style="background: #dcfce7; color: #15803d; border: 1.5px solid #86efac;">
                <span>✅</span> <span>Selesai (Masuk Penuh)</span>
            </div>
        @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
            <div class="sk-dock-status" style="background: #fee2e2; color: #b91c1c; border: 1.5px solid #fca5a5;">
                <span>❌</span> <span>Ditolak di Gerbang</span>
            </div>
        @else
            <div class="sk-dock-status" style="background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1;">
                <span>📋</span> <span>{{ $qc->status_qc }}</span>
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
