@extends('layouts.qc-mobile')

@section('title', 'Detail QC ' . ($qc->kategori_barang ?? 'Bahan Penolong') . ': ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/bahan-penolong/bahan-penolong-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/bahan-penolong/bahan-penolong.css') }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();

    $curKomoditas = strtoupper($qc->kategori_barang ?? 'MSG');
    if (!in_array($curKomoditas, ['MSG', 'GARAM', 'PERENYAH', 'BUMBU'])) {
        $curKomoditas = 'BAHAN_PENOLONG';
    }

    $docCodes = [
        'MSG'            => 'QC-MSG-032',
        'GARAM'          => 'QC-GRM-033',
        'PERENYAH'       => 'QC-PRY-063',
        'BUMBU'          => 'QC-BMB-032',
        'BAHAN_PENOLONG' => 'QC-BP-032',
    ];
    $docCode = $docCodes[$curKomoditas] ?? 'QC-BP-032';

    $commodityLabels = [
        'MSG'            => '🧂 MSG (MONOSODIUM)',
        'GARAM'          => '🧂 GARAM INDUSTRI',
        'PERENYAH'       => '🥨 PERENYAH (STPP)',
        'BUMBU'          => '🌶️ BUMBU TABUR',
        'BAHAN_PENOLONG' => '🧂 BAHAN PENOLONG',
    ];
    $commodityLabel = $commodityLabels[$curKomoditas] ?? '🧂 BAHAN PENOLONG';

    // Evaluasi Parameter Mutu Bahan Penolong
    $rawMaterialOk = ($firstDetail?->status_raw_material ?? 'OK') === 'OK';
    $kemasanKondisiOk = ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK';

    // 4 Kondisi Isi
    $isKering = (bool) ($firstDetail?->isi_kering ?? true);
    $isBasah = (bool) ($firstDetail?->isi_basah ?? false);
    $isGumpal = (bool) ($firstDetail?->isi_gumpal ?? false);
    $isBerminyak = (bool) ($firstDetail?->isi_berminyak ?? false);

    // 4 Cacat Wadah Zak
    $isKotor = (bool) ($firstDetail?->kemasan_kotor ?? false);
    $isApek = (bool) ($firstDetail?->kemasan_apek ?? false);
    $isJamur = (bool) ($firstDetail?->kemasan_jamur ?? false);
    $isSobek = (bool) ($firstDetail?->kemasan_sobek ?? false);

    $hasCacat = $isBasah || $isGumpal || $isBerminyak || $isKotor || $isApek || $isJamur || $isSobek;

    $sampleGr = (float) ($firstDetail?->jumlah_sample_gr ?: ($qc->jumlah_sample_gr ?: 200));

    $sjQty = (float) ($qc->jumlah_surat_jalan ?? $totalGross);
    $pabrikQty = (float) ($qc->jumlah_di_pabrik ?? $totalGross);
    $selisihQty = $pabrikQty - $sjQty;
@endphp

@section('content')
<div class="bp-detail-wrap">

    {{-- TOP APP BAR --}}
    <div class="bp-top-bar">
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => $curKomoditas]) }}" class="bp-back-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Riwayat {{ $curKomoditas }}</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="bp-back-btn" style="color: #0284c7; border-color: #bae6fd; background: #f0f9ff;" title="Ke Tabel Web QC">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Web ERP</span>
                </a>
            @endif

            @if (Auth::user()?->canEditQcMobile() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="bp-action-btn-edit">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Uji</span>
                </a>
            @endif
        </div>
    </div>

    {{-- HERO CARD --}}
    <div class="bp-hero">
        <div class="bp-hero-badges">
            <span class="bp-pill bp-pill-commodity">{{ $commodityLabel }}</span>
            <span class="bp-pill bp-pill-doc">{{ $docCode }}</span>
        </div>

        <div class="bp-hero-title-row">
            <h1 class="bp-hero-ticket-no">{{ $qc->qc_no }}</h1>

            @if ($qc->status_qc === 'SIAP_GUDANG')
                <span class="bp-hero-status siap">⏳ Siap Gudang</span>
            @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
                <span class="bp-hero-status parsial">⚠️ Masuk Parsial</span>
            @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                <span class="bp-hero-status diterima">✅ Masuk Penuh</span>
            @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                <span class="bp-hero-status tolak">❌ Ditolak</span>
            @else
                <span class="bp-hero-status siap">{{ $qc->status_qc }}</span>
            @endif
        </div>

        <div class="bp-hero-meta-grid">
            <div class="bp-hero-meta-item">
                <span>📅</span>
                <span>Periksa: <strong>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong></span>
            </div>
            <div class="bp-hero-meta-item">
                <span>👤</span>
                <span>Petugas: <strong>{{ $qc->petugas_qc_nama ?: '-' }}</strong></span>
            </div>
            <div class="bp-hero-meta-item">
                <span>🏭</span>
                <span>Gudang: <strong>{{ $qc->gudang?->gudang_nm ?: '-' }}</strong></span>
            </div>
            @if(!empty($qc->batch_no))
                <div class="bp-hero-meta-item">
                    <span>🏷️</span>
                    <span>Batch: <strong>{{ $qc->batch_no }}</strong></span>
                </div>
            @endif
        </div>

        {{-- INFO JIKA SUDAH DIPROSES KE GRN GUDANG --}}
        @if ($qc->terima)
            <div class="bp-hero-grn-box">
                <span>📦 Ditarik ke Gudang &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong></span>
                <span style="font-size: 0.72rem; color: #ede9fe;">{{ $qc->terima->terima_tgl ? \Carbon\Carbon::parse($qc->terima->terima_tgl)->format('d/m/Y') : '' }}</span>
            </div>
        @endif
    </div>

    {{-- KARTU 1: HASIL PEMERIKSAAN KUANTITAS --}}
    <div class="bp-card">
        <div class="bp-card-header">
            <h2 class="bp-card-title">
                <span>⚖️</span> <span>Hasil Kuantitas &amp; Penerimaan</span>
            </h2>
            <span class="bp-card-badge-right">Satuan: KG</span>
        </div>

        <div class="bp-metrics-row">
            <div class="bp-metric-card">
                <div class="bp-metric-title">Gross Kedatangan</div>
                <div class="bp-metric-val">{{ number_format($totalGross, 2, ',', '.') }}</div>
                <div class="bp-metric-sub">kg di pabrik</div>
            </div>

            <div class="bp-metric-card">
                <div class="bp-metric-title">Reject (Rusak/Afkir)</div>
                <div class="bp-metric-val danger">{{ number_format($totalReject, 2, ',', '.') }}</div>
                <div class="bp-metric-sub">kg ditolak</div>
            </div>

            <div class="bp-metric-card highlight">
                <div class="bp-metric-title" style="color: #6d28d9;">Netto Lolos</div>
                <div class="bp-metric-val violet">{{ number_format($totalNetto, 2, ',', '.') }}</div>
                <div class="bp-metric-sub" style="color: #7c3aed;">kg lolos QC</div>
            </div>
        </div>

        {{-- PERBANDINGAN SURAT JALAN VS PABRIK --}}
        <div style="background: #f8fafc; border-radius: 10px; padding: 0.75rem 0.95rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; flex-wrap: wrap; gap: 0.4rem;">
            <div>
                <span style="color: #64748b;">Surat Jalan:</span> <strong>{{ number_format($sjQty, 2, ',', '.') }} KG</strong>
                &bull; <span style="color: #64748b;">Pabrik:</span> <strong>{{ number_format($pabrikQty, 2, ',', '.') }} KG</strong>
            </div>
            <div>
                @if ($selisihQty < 0)
                    <span style="color: #dc2626; font-weight: 800;">Selisih: {{ number_format($selisihQty, 2, ',', '.') }} KG (Susut)</span>
                @elseif ($selisihQty > 0)
                    <span style="color: #16a34a; font-weight: 800;">Selisih: +{{ number_format($selisihQty, 2, ',', '.') }} KG</span>
                @else
                    <span style="color: #7c3aed; font-weight: 800;">Selisih: 0 KG (Sesuai)</span>
                @endif
            </div>
        </div>
    </div>

    {{-- KARTU 2: PARAMETER MUTU FISIK & KONDISI BAHAN --}}
    <div class="bp-card">
        <div class="bp-card-header">
            <h2 class="bp-card-title">
                <span>🔬</span> <span>Pengujian Mutu Fisik Bahan Penolong</span>
            </h2>
            <span class="bp-card-badge-right" style="background: #ede9fe; color: #6d28d9;">Standar HACCP</span>
        </div>

        {{-- STATUS RAW MATERIAL & KONDISI WADAH KEMASAN --}}
        <div class="bp-status-row">
            <div class="bp-status-box {{ $rawMaterialOk ? 'pass' : 'fail' }}">
                <span class="bp-status-title">2. Isi Raw Material</span>
                <span class="bp-status-badge {{ $rawMaterialOk ? 'pass' : 'fail' }}">
                    {{ $rawMaterialOk ? '✔ OK (Standar)' : '✖ TDK STD' }}
                </span>
            </div>

            <div class="bp-status-box {{ $kemasanKondisiOk ? 'pass' : 'fail' }}">
                <span class="bp-status-title">Kondisi Kemasan Zak</span>
                <span class="bp-status-badge {{ $kemasanKondisiOk ? 'pass' : 'fail' }}">
                    {{ $kemasanKondisiOk ? '✔ OK (Standar)' : '✖ TIDAK STANDARD' }}
                </span>
            </div>
        </div>

        {{-- 4 KONDISI FISIK ISI --}}
        <div style="margin-top: 0.75rem;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #6d28d9; text-transform: uppercase; margin-bottom: 0.4rem; display: flex; justify-content: space-between; align-items: center;">
                <span>Kondisi Fisik Isi (Raw Material):</span>
                <span style="color: #64748b; font-size: 0.7rem; font-weight: 600;">Sampel: {{ number_format($sampleGr, 0) }} gr</span>
            </div>

            <div class="bp-defect-grid">
                <div class="bp-defect-item {{ $isKering ? 'clean' : '' }}">
                    <div>🌾 KERING</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isKering ? '✔ Standar OK' : '✖ Tidak' }}</div>
                </div>

                <div class="bp-defect-item {{ $isBasah ? 'detected' : 'clean' }}">
                    <div>💧 BASAH</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isBasah ? '✖ Lembap' : '✔ Normal' }}</div>
                </div>

                <div class="bp-defect-item {{ $isGumpal ? 'detected' : 'clean' }}">
                    <div>🧱 GUMPAL</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isGumpal ? '✖ Menggumpal' : '✔ Halus' }}</div>
                </div>

                <div class="bp-defect-item {{ $isBerminyak ? 'detected' : 'clean' }}">
                    <div>🛢️ BERMINYAK</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isBerminyak ? '✖ Lengket' : '✔ Normal' }}</div>
                </div>
            </div>
        </div>

        {{-- 4 KONDISI KEMASAN / WADAH ZAK --}}
        <div style="margin-top: 0.85rem;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 0.4rem; display: flex; justify-content: space-between; align-items: center;">
                <span>Kondisi Kemasan / Wadah Zak:</span>
                @if ($hasCacat)
                    <span style="color: #dc2626; font-size: 0.72rem; font-weight: 800;">⚠️ Ada Catatan Mutu</span>
                @else
                    <span style="color: #16a34a; font-size: 0.72rem; font-weight: 800;">✔ Bersih Tanpa Cacat</span>
                @endif
            </div>

            <div class="bp-defect-grid">
                <div class="bp-defect-item {{ $isKotor ? 'detected' : 'clean' }}">
                    <div>🧼 KOTOR</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isKotor ? '✖ Ditemukan' : '✔ Bersih' }}</div>
                </div>

                <div class="bp-defect-item {{ $isApek ? 'detected' : 'clean' }}">
                    <div>👃 APEK</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isApek ? '✖ Berbau' : '✔ Segar' }}</div>
                </div>

                <div class="bp-defect-item {{ $isJamur ? 'detected' : 'clean' }}">
                    <div>🍄 JAMUR</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isJamur ? '✖ Berjamur' : '✔ Aman' }}</div>
                </div>

                <div class="bp-defect-item {{ $isSobek ? 'detected' : 'clean' }}">
                    <div>✂️ SOBEK</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isSobek ? '✖ Rusak' : '✔ Utuh' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 3: PENGIRIMAN, VENDOR & LOGISTIK --}}
    <div class="bp-card">
        <div class="bp-card-header">
            <h2 class="bp-card-title">
                <span>🚚</span> <span>Pengiriman, Vendor &amp; Transportasi</span>
            </h2>
            <span class="bp-card-badge-right">Logistik</span>
        </div>

        <div class="bp-grid-2">
            <div class="bp-cell">
                <span class="bp-cell-label">Nama Bahan (Master)</span>
                <span class="bp-cell-value highlight-violet">
                    {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: $curKomoditas) }}
                </span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Nama Jenis / Komoditas</span>
                <span class="bp-cell-value" style="font-weight: 800; color: #0f172a;">
                    {{ $qc->nama_jenis ?: $curKomoditas }}
                </span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Mitra Vendor Supplier</span>
                <span class="bp-cell-value">{{ $qc->supplier?->supplier_nm ?? '-' }}</span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Pabrik Produsen</span>
                <span class="bp-cell-value">
                    {{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }} ({{ $qc->negara_produsen ?: 'Indonesia' }})
                </span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Sampel Diperiksa</span>
                <span class="bp-cell-value highlight-blue">
                    {{ number_format($sampleGr, 0) }} Gram
                </span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Referensi PO</span>
                <span class="bp-cell-value">
                    @if ($qc->po)
                        <span style="color: #7c3aed; font-weight: 800;">{{ $qc->po->po_no }}</span>
                    @else
                        <span style="color: #64748b;">Non-PO (Kiriman Langsung)</span>
                    @endif
                </span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">No. Surat Jalan / DO</span>
                <span class="bp-cell-value">{{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}</span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Plat Nomor Truk</span>
                <span class="bp-cell-value highlight-violet">{{ $qc->plat_nomor_truk ?: '-' }}</span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Nama Sopir</span>
                <span class="bp-cell-value">{{ $qc->sopir_nama ?: '-' }}</span>
            </div>

            <div class="bp-cell">
                <span class="bp-cell-label">Waktu Kedatangan</span>
                <span class="bp-cell-value">{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</span>
            </div>
        </div>
    </div>

    {{-- KARTU 4: AUDIT HALAL & HIGIENE TRANSPORTASI (SJH) --}}
    <div class="bp-card">
        <div class="bp-card-header">
            <h2 class="bp-card-title">
                <span>🛡️</span> <span>Standar Transportasi &amp; Jaminan Halal</span>
            </h2>
            <span class="bp-card-badge-right" style="background: #dcfce7; color: #166534;">Sistem Jaminan Halal</span>
        </div>

        <div class="bp-halal-list">
            <div class="bp-halal-item">
                <div class="bp-halal-left">
                    <span class="bp-halal-title">1. Kebersihan Armada Truk</span>
                    <span class="bp-halal-sub">Bebas kotoran, najis, cairan kimia atau bau menyengat</span>
                </div>
                <span class="bp-halal-badge {{ $qc->bebas_cemaran_st ? 'pass' : 'fail' }}">
                    {{ $qc->bebas_cemaran_st ? '✔ Bebas Cemaran' : '✖ Ada Cemaran' }}
                </span>
            </div>

            <div class="bp-halal-item">
                <div class="bp-halal-left">
                    <span class="bp-halal-title">2. Pemisahan Logistik Barang Haram</span>
                    <span class="bp-halal-sub">Tidak diangkut bersamaan dengan komoditas non-halal</span>
                </div>
                <span class="bp-halal-badge {{ !$qc->angkut_barang_haram_st ? 'pass' : 'fail' }}">
                    {{ !$qc->angkut_barang_haram_st ? '✔ Bebas Barang Haram' : '✖ Tercampur Barang Haram' }}
                </span>
            </div>

            <div class="bp-halal-item">
                <div class="bp-halal-left">
                    <span class="bp-halal-title">3. Pendaftaran di LPPOM MUI / BPJPH</span>
                    <span class="bp-halal-sub">Bahan penolong terdaftar dalam daftar halal approved</span>
                </div>
                <span class="bp-halal-badge {{ $qc->terdaftar_lppom_st ? 'pass' : 'fail' }}">
                    {{ $qc->terdaftar_lppom_st ? '✔ Terdaftar' : '✖ Belum Terdaftar' }}
                </span>
            </div>

            <div class="bp-halal-item">
                <div class="bp-halal-left">
                    <span class="bp-halal-title">4. Kepemilikan Sertifikat Halal</span>
                    <span class="bp-halal-sub">Memiliki dokumen sertifikat halal produsen resmi</span>
                </div>
                <span class="bp-halal-badge {{ $qc->ada_sertifikat_halal_st ? 'pass' : 'fail' }}">
                    {{ $qc->ada_sertifikat_halal_st ? '✔ Ada Sertifikat' : '✖ Tidak Ada' }}
                </span>
            </div>

            <div class="bp-halal-item">
                <div class="bp-halal-left">
                    <span class="bp-halal-title">5. Masa Berlaku Sertifikat Halal</span>
                    <span class="bp-halal-sub">Status sertifikat halal aktif dan tidak kedaluwarsa</span>
                </div>
                <span class="bp-halal-badge {{ $qc->sertifikat_halal_berlaku_st ? 'pass' : 'fail' }}">
                    {{ $qc->sertifikat_halal_berlaku_st ? '✔ Masih Berlaku' : '✖ Kadaluarsa' }}
                </span>
            </div>
        </div>

        @if ($qc->komentar_transportasi || $qc->komentar_lppom || $qc->komentar_sertifikat || $qc->komentar_berlaku)
            <div style="margin-top: 0.65rem; padding: 0.5rem 0.65rem; background: #f8fafc; border-radius: 8px; font-size: 0.75rem; color: #475569; display: flex; flex-direction: column; gap: 0.2rem;">
                @if($qc->komentar_transportasi) <div>• Catatan Transportasi: <em>{{ $qc->komentar_transportasi }}</em></div> @endif
                @if($qc->komentar_lppom) <div>• Catatan LPPOM: <em>{{ $qc->komentar_lppom }}</em></div> @endif
                @if($qc->komentar_sertifikat) <div>• Catatan Sertifikat: <em>{{ $qc->komentar_sertifikat }}</em></div> @endif
                @if($qc->komentar_berlaku) <div>• Catatan Validitas: <em>{{ $qc->komentar_berlaku }}</em></div> @endif
            </div>
        @endif
    </div>

    {{-- KARTU 5: KEPUTUSAN & CATATAN QC --}}
    <div class="bp-card">
        <div class="bp-card-header">
            <h2 class="bp-card-title">
                <span>💬</span> <span>Keputusan &amp; Otorisasi QC {{ $curKomoditas }}</span>
            </h2>
            <span class="bp-card-badge-right" style="background: #f0fdf4; color: #166534;">ACC Valid</span>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <div class="bp-cell-label">Status Keputusan Akhir:</div>
            <div style="font-size: 1.05rem; font-weight: 900; margin-top: 0.2rem; color: {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '#16a34a' : '#dc2626' }};">
                @if ($qc->status_qc !== 'DITOLAK_TOTAL')
                    ✔ DITERIMA ({{ number_format($totalNetto, 2, ',', '.') }} kg Lolos Gudang)
                @else
                    ✖ DITOLAK TOTAL (TIDAK DIBONGKAR / KUALITAS TIDAK SESUAI)
                @endif
            </div>
        </div>

        <div class="bp-grid-2" style="border-top: 1px dashed #e2e8f0; padding-top: 0.65rem; margin-top: 0.65rem;">
            <div class="bp-cell">
                <span class="bp-cell-label">Petugas QC Pemeriksa:</span>
                <span class="bp-cell-value" style="font-weight: 800;">{{ $qc->petugas_qc_nama ?: 'Petugas QC Lapangan' }}</span>
            </div>
            <div class="bp-cell">
                <span class="bp-cell-label">Supervisor Peninjau:</span>
                <span class="bp-cell-value" style="font-weight: 800;">{{ $qc->qc_supervisor_nama ?: '-' }}</span>
            </div>
        </div>

        @if ($qc->catatan_umum || $firstDetail?->catatan_dtl)
            <div style="margin-top: 0.75rem; padding: 0.65rem 0.85rem; background: #faf5ff; border-left: 3px solid #7c3aed; border-radius: 4px; font-size: 0.825rem; color: #3b0764;">
                <div style="font-weight: 800; margin-bottom: 0.2rem; color: #6d28d9;">Catatan Pengujian Bahan Penolong:</div>
                {{ $firstDetail?->catatan_dtl ?: $qc->catatan_umum }}
            </div>
        @endif
    </div>

    {{-- FLOATING BOTTOM FIXED BAR (PERSIS PLASTIK, MINYAK & KARTON) --}}
    <div class="bp-bottom-bar">
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => $curKomoditas]) }}" class="bp-bottom-btn btn-back">
            &larr; Riwayat
        </a>

        @if (Auth::user()?->canEditQcMobile() && !$isLocked)
            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="bp-bottom-btn btn-edit">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Pemeriksaan</span>
            </a>
        @endif
    </div>

</div>
@endsection
