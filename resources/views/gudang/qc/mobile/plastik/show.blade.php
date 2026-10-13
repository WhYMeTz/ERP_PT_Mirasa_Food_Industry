@extends('layouts.qc-mobile')

@section('title', 'Detail QC Plastik: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik.css') }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();

    // Deteksi Satuan: PCS / Lembar / KG
    $satuanRaw = $firstDetail?->barang?->satuanDasar?->satuan_nm
        ?: ($firstDetail?->barang?->satuanDasar?->satuan_cd
        ?: ($qc->po?->details?->firstWhere('barang_id', $firstDetail?->barang_id)?->barang?->satuanDasar?->satuan_nm
        ?: ($qc->po?->details?->first()?->barang?->satuanDasar?->satuan_nm ?? 'PCS')));

    $satuanDtl = strtoupper(trim($satuanRaw)) === 'KG' ? 'KG' : (strtoupper(trim($satuanRaw)) === 'LEMBAR' ? 'Lembar' : 'Pcs');
    $satuanDtlLower = strtolower($satuanDtl);

    // Evaluasi Parameter Mutu Plastik
    $rawMaterialOk = ($firstDetail?->status_raw_material ?? 'OK') === 'OK';
    $kemasanKondisiOk = ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK';

    $isKotor = (bool) ($firstDetail?->kemasan_kotor ?? false);
    $isApek = (bool) ($firstDetail?->kemasan_apek ?? false);
    $isBasah = (bool) ($firstDetail?->kemasan_basah ?? false);
    $isSobek = (bool) ($firstDetail?->kemasan_sobek ?? false);
    $hasCacat = $isKotor || $isApek || $isBasah || $isSobek;

    $ketebalanAnalisa = $firstDetail?->ketebalan_analisa ?: '-';
    $ketebalanStandar = $firstDetail?->ketebalan_standar ?: '0.08 mm';
    $keutuhanAnalisa = $firstDetail?->keutuhan_analisa ?: '-';
    $keutuhanStandar = $firstDetail?->keutuhan_standar ?: 'Tidak Sobek';

    $sjQty = (float) ($qc->jumlah_surat_jalan ?? $totalGross);
    $pabrikQty = (float) ($qc->jumlah_di_pabrik ?? $totalGross);
    $selisihQty = $pabrikQty - $sjQty;
@endphp

@section('content')
<div class="pl-detail-wrap">

    {{-- TOP APP BAR --}}
    <div class="pl-top-bar">
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']) }}" class="pl-back-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Riwayat Plastik</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="pl-back-btn" style="color: #0284c7; border-color: #bae6fd; background: #f0fdfa;" title="Ke Tabel Web QC">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Web ERP</span>
                </a>
            @endif

            @if (Auth::user()?->canEditQcMobile() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="pl-action-btn-edit">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Uji</span>
                </a>
            @endif
        </div>
    </div>

    {{-- HERO CARD --}}
    <div class="pl-hero">
        <div class="pl-hero-badges">
            <span class="pl-pill pl-pill-commodity">🛍️ PLASTIK KEMASAN</span>
            <span class="pl-pill pl-pill-doc">QC-RM-030</span>
        </div>

        <div class="pl-hero-title-row">
            <h1 class="pl-hero-ticket-no">{{ $qc->qc_no }}</h1>

            @if ($qc->status_qc === 'SIAP_GUDANG')
                <span class="pl-hero-status siap">⏳ Siap Gudang</span>
            @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
                <span class="pl-hero-status parsial">⚠️ Masuk Parsial</span>
            @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                <span class="pl-hero-status diterima">✅ Masuk Penuh</span>
            @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                <span class="pl-hero-status tolak">❌ Ditolak</span>
            @else
                <span class="pl-hero-status siap">{{ $qc->status_qc }}</span>
            @endif
        </div>

        <div class="pl-hero-meta-grid">
            <div class="pl-hero-meta-item">
                <span>📅</span>
                <span>Periksa: <strong>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong></span>
            </div>
            <div class="pl-hero-meta-item">
                <span>👤</span>
                <span>Petugas: <strong>{{ $qc->petugas_qc_nama ?: '-' }}</strong></span>
            </div>
            <div class="pl-hero-meta-item">
                <span>🏭</span>
                <span>Gudang: <strong>{{ $qc->gudang?->gudang_nm ?: '-' }}</strong></span>
            </div>
            @if(!empty($qc->batch_no))
                <div class="pl-hero-meta-item">
                    <span>🏷️</span>
                    <span>Batch: <strong>{{ $qc->batch_no }}</strong></span>
                </div>
            @endif
        </div>

        {{-- INFO JIKA SUDAH DIPROSES KE GRN GUDANG --}}
        @if ($qc->terima)
            <div class="pl-hero-grn-box">
                <span>📦 Ditarik ke Gudang &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong></span>
                <span style="font-size: 0.72rem; color: #ccfbf1;">{{ $qc->terima->terima_tgl ? \Carbon\Carbon::parse($qc->terima->terima_tgl)->format('d/m/Y') : '' }}</span>
            </div>
        @endif
    </div>

    {{-- KARTU 1: HASIL PEMERIKSAAN KUANTITAS --}}
    <div class="pl-card">
        <div class="pl-card-header">
            <h2 class="pl-card-title">
                <span>⚖️</span> <span>Hasil Kuantitas &amp; Penerimaan</span>
            </h2>
            <span class="pl-card-badge-right">Satuan: {{ $satuanDtl }}</span>
        </div>

        <div class="pl-metrics-row">
            <div class="pl-metric-card">
                <div class="pl-metric-title">Gross Kedatangan</div>
                <div class="pl-metric-val">{{ number_format($totalGross, 0, ',', '.') }}</div>
                <div class="pl-metric-sub">{{ $satuanDtlLower }} di pabrik</div>
            </div>

            <div class="pl-metric-card">
                <div class="pl-metric-title">Reject (Cacat)</div>
                <div class="pl-metric-val danger">{{ number_format($totalReject, 0, ',', '.') }}</div>
                <div class="pl-metric-sub">{{ $satuanDtlLower }} ditolak</div>
            </div>

            <div class="pl-metric-card highlight">
                <div class="pl-metric-title" style="color: #0f766e;">Netto Lolos</div>
                <div class="pl-metric-val teal">{{ number_format($totalNetto, 0, ',', '.') }}</div>
                <div class="pl-metric-sub" style="color: #0d9488;">{{ $satuanDtlLower }} lolos QC</div>
            </div>
        </div>

        {{-- PERBANDINGAN SURAT JALAN VS PABRIK --}}
        <div style="background: #f8fafc; border-radius: 10px; padding: 0.75rem 0.95rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; flex-wrap: wrap; gap: 0.4rem;">
            <div>
                <span style="color: #64748b;">Surat Jalan:</span> <strong>{{ number_format($sjQty, 0, ',', '.') }} {{ $satuanDtl }}</strong>
                &bull; <span style="color: #64748b;">Pabrik:</span> <strong>{{ number_format($pabrikQty, 0, ',', '.') }} {{ $satuanDtl }}</strong>
            </div>
            <div>
                @if ($selisihQty < 0)
                    <span style="color: #dc2626; font-weight: 800;">Selisih: {{ number_format($selisihQty, 0, ',', '.') }} {{ $satuanDtl }} (Susut)</span>
                @elseif ($selisihQty > 0)
                    <span style="color: #16a34a; font-weight: 800;">Selisih: +{{ number_format($selisihQty, 0, ',', '.') }} {{ $satuanDtl }}</span>
                @else
                    <span style="color: #0d9488; font-weight: 800;">Selisih: 0 {{ $satuanDtl }} (Sesuai)</span>
                @endif
            </div>
        </div>
    </div>

    {{-- KARTU 2: PARAMETER MUTU & ANALISA KEMASAN (HACCP) --}}
    <div class="pl-card">
        <div class="pl-card-header">
            <h2 class="pl-card-title">
                <span>🔬</span> <span>Uji Mutu Fisik &amp; Analisa Kemasan</span>
            </h2>
            <span class="pl-card-badge-right" style="background: #ccfbf1; color: #0f766e;">Mikrometer &amp; Visual</span>
        </div>

        {{-- PARAMETER KETEBALAN & KEUTUHAN --}}
        <div class="pl-param-grid">
            <div class="pl-param-card">
                <span class="pl-param-label">
                    <span>📏</span> Ketebalan Mikrometer
                </span>
                <span class="pl-param-value">{{ $ketebalanAnalisa }}</span>
                <span class="pl-param-std">Standar: {{ $ketebalanStandar }}</span>
            </div>

            <div class="pl-param-card">
                <span class="pl-param-label">
                    <span>🛡️</span> Keutuhan Seal &amp; Fisik
                </span>
                <span class="pl-param-value">{{ $keutuhanAnalisa }}</span>
                <span class="pl-param-std">Standar: {{ $keutuhanStandar }}</span>
            </div>
        </div>

        {{-- STATUS RAW MATERIAL & KONDISI WADAH KEMASAN --}}
        <div class="pl-status-row">
            <div class="pl-status-box {{ $rawMaterialOk ? 'pass' : 'fail' }}">
                <span class="pl-status-title">2. Isi Raw Material</span>
                <span class="pl-status-badge {{ $rawMaterialOk ? 'pass' : 'fail' }}">
                    {{ $rawMaterialOk ? '✔ OK (Standar)' : '✖ TDK STD' }}
                </span>
            </div>

            <div class="pl-status-box {{ $kemasanKondisiOk ? 'pass' : 'fail' }}">
                <span class="pl-status-title">Wadah Kemasan</span>
                <span class="pl-status-badge {{ $kemasanKondisiOk ? 'pass' : 'fail' }}">
                    {{ $kemasanKondisiOk ? '✔ OK (Standar)' : '✖ TDK STANDARD' }}
                </span>
            </div>
        </div>

        {{-- CHECKLIST 4 CACAT KEMASAN --}}
        <div style="margin-top: 0.5rem;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 0.4rem; display: flex; justify-content: space-between; align-items: center;">
                <span>Verifikasi 4 Titik Cacat Kemasan:</span>
                @if ($hasCacat)
                    <span style="color: #dc2626; font-size: 0.72rem; font-weight: 800;">⚠️ Ada Indikasi Cacat</span>
                @else
                    <span style="color: #16a34a; font-size: 0.72rem; font-weight: 800;">✔ Bersih Tanpa Cacat</span>
                @endif
            </div>

            <div class="pl-defect-grid">
                <div class="pl-defect-item {{ $isKotor ? 'detected' : 'clean' }}">
                    <div>🧼 KOTOR</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isKotor ? '✖ Ditemukan' : '✔ Bersih' }}</div>
                </div>

                <div class="pl-defect-item {{ $isApek ? 'detected' : 'clean' }}">
                    <div>👃 APEK</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isApek ? '✖ Berbau' : '✔ Segar' }}</div>
                </div>

                <div class="pl-defect-item {{ $isBasah ? 'detected' : 'clean' }}">
                    <div>💧 BASAH</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isBasah ? '✖ Lembap' : '✔ Kering' }}</div>
                </div>

                <div class="pl-defect-item {{ $isSobek ? 'detected' : 'clean' }}">
                    <div>✂️ SOBEK</div>
                    <div style="font-size: 0.68rem; margin-top: 2px;">{{ $isSobek ? '✖ Rusak' : '✔ Utuh' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 3: PENGIRIMAN, VENDOR & LOGISTIK --}}
    <div class="pl-card">
        <div class="pl-card-header">
            <h2 class="pl-card-title">
                <span>🚚</span> <span>Pengiriman, Vendor &amp; Mobil Box</span>
            </h2>
            <span class="pl-card-badge-right">Logistik</span>
        </div>

        <div class="pl-grid-2">
            <div class="pl-cell">
                <span class="pl-cell-label">Nama Bahan (Master)</span>
                <span class="pl-cell-value highlight-teal">
                    {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: 'Plastik Kemasan') }}
                </span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Spesifikasi Kemasan</span>
                <span class="pl-cell-value" style="font-weight: 800; color: #0f172a;">
                    {{ $qc->nama_jenis ?: '-' }}
                </span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Mitra Vendor Kemasan</span>
                <span class="pl-cell-value">{{ $qc->supplier?->supplier_nm ?? '-' }}</span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Pabrik Produsen</span>
                <span class="pl-cell-value">
                    {{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }} ({{ $qc->negara_produsen ?: 'Indonesia' }})
                </span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Sampel QC Diperiksa</span>
                <span class="pl-cell-value highlight-blue">
                    {{ $qc->jumlah_sample_pcs ? number_format((int)$qc->jumlah_sample_pcs, 0) . ' Pcs' : '10 Pcs' }}
                </span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Referensi PO</span>
                <span class="pl-cell-value">
                    @if ($qc->po)
                        <span style="color: #0d9488; font-weight: 800;">{{ $qc->po->po_no }}</span>
                    @else
                        <span style="color: #64748b;">Non-PO (Kiriman Langsung)</span>
                    @endif
                </span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">No. Surat Jalan / DO</span>
                <span class="pl-cell-value">{{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}</span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Plat Truk / Box</span>
                <span class="pl-cell-value highlight-teal">{{ $qc->plat_nomor_truk ?: '-' }}</span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Nama Sopir</span>
                <span class="pl-cell-value">{{ $qc->sopir_nama ?: '-' }}</span>
            </div>

            <div class="pl-cell">
                <span class="pl-cell-label">Waktu Kedatangan</span>
                <span class="pl-cell-value">{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</span>
            </div>
        </div>
    </div>

    {{-- KARTU 4: AUDIT HALAL & HIGIENE TRANSPORTASI (SJH) --}}
    <div class="pl-card">
        <div class="pl-card-header">
            <h2 class="pl-card-title">
                <span>🛡️</span> <span>Standar Transportasi &amp; Jaminan Halal</span>
            </h2>
            <span class="pl-card-badge-right" style="background: #dcfce7; color: #166534;">Sistem Jaminan Halal</span>
        </div>

        <div class="pl-halal-list">
            <div class="pl-halal-item">
                <div class="pl-halal-left">
                    <span class="pl-halal-title">1. Kebersihan Armada &amp; Kontainer Box</span>
                    <span class="pl-halal-sub">Bebas kotoran, najis, cairan kimia atau bau asing</span>
                </div>
                <span class="pl-halal-badge {{ $qc->bebas_cemaran_st ? 'pass' : 'fail' }}">
                    {{ $qc->bebas_cemaran_st ? '✔ Bebas Cemaran' : '✖ Ada Cemaran' }}
                </span>
            </div>

            <div class="pl-halal-item">
                <div class="pl-halal-left">
                    <span class="pl-halal-title">2. Pemisahan Logistik Barang Haram</span>
                    <span class="pl-halal-sub">Tidak diangkut bersamaan dengan komoditas non-halal</span>
                </div>
                <span class="pl-halal-badge {{ !$qc->angkut_barang_haram_st ? 'pass' : 'fail' }}">
                    {{ !$qc->angkut_barang_haram_st ? '✔ Bebas Barang Haram' : '✖ Tercampur Barang Haram' }}
                </span>
            </div>

            <div class="pl-halal-item">
                <div class="pl-halal-left">
                    <span class="pl-halal-title">3. Pendaftaran di LPPOM MUI / BPJPH</span>
                    <span class="pl-halal-sub">Terdaftar di daftar bahan approved list kemasan</span>
                </div>
                <span class="pl-halal-badge {{ $qc->terdaftar_lppom_st ? 'pass' : 'fail' }}">
                    {{ $qc->terdaftar_lppom_st ? '✔ Terdaftar' : '✖ Belum Terdaftar' }}
                </span>
            </div>

            <div class="pl-halal-item">
                <div class="pl-halal-left">
                    <span class="pl-halal-title">4. Kepemilikan Sertifikat Halal</span>
                    <span class="pl-halal-sub">Memiliki dokumen sertifikat halal produsen resmi</span>
                </div>
                <span class="pl-halal-badge {{ $qc->ada_sertifikat_halal_st ? 'pass' : 'fail' }}">
                    {{ $qc->ada_sertifikat_halal_st ? '✔ Ada Sertifikat' : '✖ Tidak Ada' }}
                </span>
            </div>

            <div class="pl-halal-item">
                <div class="pl-halal-left">
                    <span class="pl-halal-title">5. Masa Berlaku Sertifikat Halal</span>
                    <span class="pl-halal-sub">Status sertifikat halal aktif dan tidak kedaluwarsa</span>
                </div>
                <span class="pl-halal-badge {{ $qc->sertifikat_halal_berlaku_st ? 'pass' : 'fail' }}">
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
    <div class="pl-card">
        <div class="pl-card-header">
            <h2 class="pl-card-title">
                <span>💬</span> <span>Keputusan &amp; Otorisasi QC Plastik</span>
            </h2>
            <span class="pl-card-badge-right" style="background: #f0fdf4; color: #166534;">ACC Valid</span>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <div class="pl-cell-label">Status Keputusan Akhir:</div>
            <div style="font-size: 1.05rem; font-weight: 900; margin-top: 0.2rem; color: {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '#16a34a' : '#dc2626' }};">
                @if ($qc->status_qc !== 'DITOLAK_TOTAL')
                    ✔ DITERIMA ({{ number_format($totalNetto, 0, ',', '.') }} {{ $satuanDtlLower }} Lolos Gudang)
                @else
                    ✖ DITOLAK TOTAL (TIDAK DIBONGKAR / CACAT KRITIS)
                @endif
            </div>
        </div>

        <div class="pl-grid-2" style="border-top: 1px dashed #e2e8f0; padding-top: 0.65rem; margin-top: 0.65rem;">
            <div class="pl-cell">
                <span class="pl-cell-label">Petugas QC Pemeriksa:</span>
                <span class="pl-cell-value" style="font-weight: 800;">{{ $qc->petugas_qc_nama ?: 'Petugas QC Lapangan' }}</span>
            </div>
            <div class="pl-cell">
                <span class="pl-cell-label">Supervisor Peninjau:</span>
                <span class="pl-cell-value" style="font-weight: 800;">{{ $qc->qc_supervisor_nama ?: '-' }}</span>
            </div>
        </div>

        @if ($qc->catatan_umum || $firstDetail?->catatan_dtl)
            <div style="margin-top: 0.75rem; padding: 0.65rem 0.85rem; background: #f8fafc; border-left: 3px solid #0d9488; border-radius: 4px; font-size: 0.825rem; color: #334155;">
                <div style="font-weight: 800; margin-bottom: 0.2rem; color: #0f766e;">Catatan Pengujian QC:</div>
                {{ $firstDetail?->catatan_dtl ?: $qc->catatan_umum }}
            </div>
        @endif
    </div>

    {{-- FLOATING BOTTOM BAR --}}
    <div class="pl-bottom-bar">
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']) }}" class="pl-bottom-btn btn-back">
            &larr; Riwayat
        </a>

        @if (Auth::user()?->canEditQcMobile() && !$isLocked)
            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="pl-bottom-btn btn-edit">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Pemeriksaan</span>
            </a>
        @endif
    </div>

</div>
@endsection
