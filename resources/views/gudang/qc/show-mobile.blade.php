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
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="qc-detail-back-btn">
            <span>&larr; Riwayat Tiket</span>
        </a>
        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="btn-qc-switch-desktop" style="font-size: 0.72rem; font-weight: 700; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 0.25rem 0.5rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;" title="Buka Dokumen Cetak HACCP Desktop">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Ke Web ERP</span>
                </a>
            @endif
            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
                QC Lapangan &bull; Detail
            </span>
        </div>
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
                <div class="qc-metric-sub" style="color: #059669; font-weight: 700;">kg diterima pabrik</div>
            </div>
        </div>
    </div>

    {{-- KARTU 3: HASIL PENGUJIAN MUTU LENGKAP --}}
    <div class="qc-card-section">
        <h2 class="qc-card-title">
            <span>🔬</span> <span>Hasil Pengujian Mutu ({{ $kat }})</span>
        </h2>

        {{-- JIKA KOMODITAS SINGKONG --}}
        @if ($kat === 'SINGKONG')
            {{-- PENGUJIAN I: FISIK & DIAMETER --}}
            <div style="margin-bottom: 0.85rem;">
                <div style="font-size: 0.8rem; font-weight: 800; color: #0284c7; text-transform: uppercase; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.35rem;">
                    <span>📏</span> <span>Pengujian I &bull; Fisik &amp; Diameter Umbi</span>
                </div>
                <div class="qc-param-list">
                    <div class="qc-param-row">
                        <span class="qc-param-name">Diameter &lt; 4 cm (Standar Max 5%)</span>
                        <span class="qc-param-val {{ (float)($firstDetail?->diameter_kurang_4cm_persen ?? 0) <= 5 ? 'pass' : 'fail' }}">
                            {{ number_format((float)($firstDetail?->diameter_kurang_4cm_persen ?? 0), 1) }}%
                        </span>
                    </div>
                    <div class="qc-param-row">
                        <span class="qc-param-name">Diameter &ge; 4 cm (Standar Min 95%)</span>
                        <span class="qc-param-val {{ (float)($firstDetail?->diameter_lebih_4cm_persen ?? 0) >= 95 ? 'pass' : 'fail' }}">
                            {{ number_format((float)($firstDetail?->diameter_lebih_4cm_persen ?? 0), 1) }}%
                        </span>
                    </div>
                </div>

                <div style="margin-top: 0.65rem;">
                    <span class="qc-info-label">Kondisi Kesegaran Umbi:</span>
                    <div class="qc-tag-chips" style="margin-top: 0.3rem;">
                        @if ($firstDetail?->kondisi_segar) <span class="qc-chip-item active-success">✔ Segar</span> @endif
                        @if ($firstDetail?->kondisi_busuk) <span class="qc-chip-item active-danger">✖ Busuk</span> @endif
                        @if ($firstDetail?->kondisi_layu) <span class="qc-chip-item active-danger">✖ Layu</span> @endif
                        @if ($firstDetail?->kondisi_berjamur) <span class="qc-chip-item active-danger">✖ Berjamur</span> @endif
                        @if ($firstDetail?->kondisi_basah) <span class="qc-chip-item active-danger">✖ Basah</span> @endif
                        @if ($firstDetail?->kondisi_lembek) <span class="qc-chip-item active-danger">✖ Lembek</span> @endif
                        @if ($firstDetail?->kondisi_terkelupas) <span class="qc-chip-item">Terkelupas</span> @endif
                    </div>
                </div>
            </div>

            {{-- PENGUJIAN II: UJI GORENG (LAB FRYER) --}}
            <div style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.85rem; margin-top: 0.85rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.45rem;">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #d97706; text-transform: uppercase; display: flex; align-items: center; gap: 0.35rem;">
                        <span>🍟</span> <span>Pengujian II &bull; Uji Goreng (Fryer)</span>
                    </div>

                    @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                        <span style="font-size: 0.72rem; font-weight: 800; background: #fef3c7; color: #b45309; padding: 0.2rem 0.6rem; border-radius: 20px; border: 1px solid #fde68a;">
                            ⏳ Menunggu Lab Fryer
                        </span>
                    @else
                        <span style="font-size: 0.72rem; font-weight: 800; background: #ecfdf5; color: #059669; padding: 0.2rem 0.6rem; border-radius: 20px; border: 1px solid #a7f3d0;">
                            ✔ Selesai Diuji
                        </span>
                    @endif
                </div>

                {{-- JIKA MASIH MENUNGGU LAB (SETENGAH PROSES) --}}
                @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                    <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 0.9rem; text-align: center;">
                        <div style="font-size: 1.6rem; margin-bottom: 0.25rem;">⏳🍟</div>
                        <div style="font-weight: 800; font-size: 0.9rem; color: #92400e;">
                            Pengujian II (Fryer) Belum Diisi
                        </div>
                        <div style="font-size: 0.78rem; color: #b45309; margin-top: 0.2rem; line-height: 1.4;">
                            Truk telah selesai diuji timbangan fisik awal. Silakan lengkapi hasil uji rasa, tekstur, dan defect goreng sekarang.
                        </div>
                        <button type="button" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $firstDetail?->qcdtl_id }}')" style="margin-top: 0.75rem; width: 100%; padding: 0.75rem; border: none; border-radius: 10px; background: #d97706; color: #ffffff; font-weight: 800; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.3);">
                            <span>🍟</span> <span>Lanjutkan Uji Fryer Sekarang</span>
                        </button>
                    </div>
                @else
                    {{-- JIKA SUDAH SELESAI --}}
                    <div class="qc-param-list">
                        <div class="qc-param-row">
                            <span class="qc-param-name">Rasa Keripik Singkong</span>
                            <span class="qc-param-val {{ $firstDetail?->fryer_rasa === 'PAHIT' ? 'fail' : 'pass' }}">
                                {{ $firstDetail?->fryer_rasa === 'PAHIT' ? '✖ Pahit' : '✔ Tidak Pahit (Gurih)' }}
                            </span>
                        </div>
                        <div class="qc-param-row">
                            <span class="qc-param-name">Tekstur Keripik</span>
                            <span class="qc-param-val {{ in_array($firstDetail?->fryer_tekstur, ['ALOT', 'LEMBEK']) ? 'fail' : 'pass' }}">
                                {{ $firstDetail?->fryer_tekstur ?: 'Renyah' }}
                            </span>
                        </div>
                        <div class="qc-param-row">
                            <span class="qc-param-name">Penampakan Minyak</span>
                            <span class="qc-param-val {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? 'fail' : 'pass' }}">
                                {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? '✖ Oilsoaked' : '✔ Normal' }}
                            </span>
                        </div>
                    </div>

                    <div style="margin-top: 0.65rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.75rem; font-size: 0.78rem;">
                        <div style="font-weight: 800; color: #475569; margin-bottom: 0.3rem;">Persentase Cacat Goreng (Defect):</div>
                        <div style="display: flex; justify-content: space-between; font-weight: 700; color: #1e293b;">
                            <span>Breakage: {{ $firstDetail?->defect_breakage_persen ?? 0 }}%</span>
                            <span>Cluster: {{ $firstDetail?->defect_cluster_persen ?? 0 }}%</span>
                            <span>Gambos: {{ $firstDetail?->defect_gambos_persen ?? 0 }}%</span>
                        </div>
                    </div>

                    <div style="margin-top: 0.6rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.725rem; color: #64748b;">
                        <span>Diuji oleh: <strong>{{ $qc->petugas_uji_goreng ?: 'Petugas Lab' }}</strong> ({{ $qc->tgl_uji_goreng ? $qc->tgl_uji_goreng->format('d/m/Y H:i') : '-' }})</span>
                        <button type="button" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $firstDetail?->qcdtl_id }}', { rasa: '{{ $firstDetail?->fryer_rasa }}', tekstur: '{{ $firstDetail?->fryer_tekstur }}', penampakan: '{{ $firstDetail?->fryer_penampakan }}', breakage: '{{ $firstDetail?->defect_breakage_persen }}', cluster: '{{ $firstDetail?->defect_cluster_persen }}', gambos: '{{ $firstDetail?->defect_gambos_persen }}' })" style="background: none; border: none; color: #0284c7; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            Koreksi Lab
                        </button>
                    </div>
                @endif
            </div>

        {{-- JIKA KOMODITAS MINYAK --}}
        @elseif ($kat === 'MINYAK')
            <div class="qc-param-list">
                <div class="qc-param-row">
                    <span class="qc-param-name">FFA di COA Supplier</span>
                    <span class="qc-param-val">{{ $firstDetail?->ffa_coa ? number_format($firstDetail->ffa_coa, 3) : '-' }}</span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">FFA Uji QC Mirasa (Max 0,100%)</span>
                    <span class="qc-param-val {{ (float)($firstDetail?->ffa_qc ?? 0) <= 0.1 ? 'pass' : 'fail' }}">
                        {{ $firstDetail?->ffa_qc ? number_format($firstDetail->ffa_qc, 3) : '-' }}
                    </span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Kejernihan Minyak</span>
                    <span class="qc-param-val pass">✔ Bebas Endapan / Jernih</span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Kebersihan Tangki</span>
                    <span class="qc-param-val pass">✔ Bersih</span>
                </div>
            </div>

        {{-- JIKA PLASTIK & KARTON --}}
        @else
            <div class="qc-param-list">
                <div class="qc-param-row">
                    <span class="qc-param-name">Kondisi Kemasan / Fisik</span>
                    <span class="qc-param-val pass">✔ Bersih, Kering &amp; Utuh</span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Spesifikasi Ukuran / Mutu</span>
                    <span class="qc-param-val pass">✔ Sesuai Standar Pabrik</span>
                </div>
            </div>
        @endif
    </div>

    {{-- KARTU 4: CATATAN & KESIMPULAN --}}
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
    @if ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
        <button type="button" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $firstDetail?->qcdtl_id }}')" class="qc-btn-mobile-edit" style="flex: 2; background: #d97706; text-align: center; border-color: #b45309;">
            <span>🍟 Lanjutkan Uji Fryer</span>
        </button>
    @endif

    @if (Auth::user()?->canEditQc() && !$isLocked)
        <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile']) }}" class="qc-btn-mobile-edit" style="flex: 1; text-align: center;">
            <span>✏️ Edit Uji</span>
        </a>
    @else
        <button type="button" class="qc-btn-mobile-edit" style="flex: 1; background: #94a3b8; cursor: not-allowed;" disabled>
            <span>🔒 Terkunci</span>
        </button>
    @endif
</div>

{{-- INCLUDE MODAL UJI GORENG FRYER --}}
@include('gudang.qc.partials.modal-uji-fryer')

@endsection
