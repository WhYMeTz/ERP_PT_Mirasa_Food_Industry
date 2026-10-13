@extends('layouts.qc-mobile')

@section('title', 'Detail QC Minyak: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak-detail.css') }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();

    // Deteksi Satuan: Liter vs Kg
    $satuanRaw = $firstDetail?->barang?->satuanDasar?->satuan_nm
        ?: ($firstDetail?->barang?->satuanDasar?->satuan_cd
        ?: ($qc->po?->details?->firstWhere('barang_id', $firstDetail?->barang_id)?->barang?->satuanDasar?->satuan_nm
        ?: ($qc->po?->details?->first()?->barang?->satuanDasar?->satuan_nm ?? 'kg')));

    $isLiter = (stripos($satuanRaw, 'liter') !== false || stripos($satuanRaw, 'ltr') !== false || strtoupper(trim($satuanRaw)) === 'L');
    if (!$isLiter) {
        $namaItem = strtoupper(($firstDetail?->barang?->barang_nm ?? '') . ' ' . ($qc->nama_jenis ?? ''));
        if (str_contains($namaItem, 'KELAPA') && !str_contains($namaItem, 'SAWIT')) {
            $isLiter = true;
        }
    }
    $satuanDtl = $isLiter ? 'Liter' : 'kg';
    $satuanDtlLower = strtolower($satuanDtl);

    // Evaluasi Parameter FFA
    $ffaCoa = $firstDetail?->ffa_coa !== null ? (float)$firstDetail->ffa_coa : null;
    $ffaQc = $firstDetail?->ffa_qc !== null ? (float)$firstDetail->ffa_qc : null;
    $ffaPass = $ffaQc !== null ? ($ffaQc <= 0.20) : true;

    // Evaluasi Wadah & Higiene
    $tipeWadah = strtoupper($firstDetail?->tipe_wadah_minyak ?? 'TANGKI');
    $kondisiWadahOk = ($firstDetail?->kondisi_tangki_jerigen ?? 'OK') === 'OK';
    $rawMaterialOk = ($firstDetail?->status_raw_material ?? 'OK') === 'OK';
    $jernihOk = (bool) ($firstDetail?->minyak_jernih_st ?? true);
    $tangkiBersihOk = (bool) ($firstDetail?->tangki_bersih_st ?? true);

    $sjKg = (float) ($qc->jumlah_surat_jalan ?? $totalGross);
    $pabrikKg = (float) ($qc->jumlah_di_pabrik ?? $totalGross);
    $selisihTimbang = $pabrikKg - $sjKg;
@endphp

@section('content')
<div class="my-detail-wrap">

    {{-- TOP APP BAR --}}
    <div class="my-top-bar">
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'MINYAK']) }}" class="my-back-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Riwayat Minyak</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="my-back-btn" style="color: #0284c7; border-color: #bae6fd; background: #f0f9ff;" title="Ke Tabel Web QC">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Web ERP</span>
                </a>
            @endif

            @if (Auth::user()?->canEditQcMobile() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="my-action-btn-edit">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Uji</span>
                </a>
            @endif
        </div>
    </div>

    {{-- HERO CARD --}}
    <div class="my-hero">
        <div class="my-hero-badges">
            <span class="my-pill my-pill-commodity">🛢️ MINYAK GORENG</span>

            <span class="my-pill my-pill-wadah">
                {{ $tipeWadah === 'JERIGEN' ? '🛢️ WADAH JERIGEN' : '🚚 TRUK TANGKI CURAH' }}
            </span>
        </div>

        <div class="my-hero-title-row">
            <h1 class="my-hero-ticket-no">{{ $qc->qc_no }}</h1>

            @if ($qc->status_qc === 'SIAP_GUDANG')
                <span class="my-hero-status siap">⏳ Siap Gudang</span>
            @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
                <span class="my-hero-status parsial">⚠️ Masuk Parsial</span>
            @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                <span class="my-hero-status diterima">✅ Masuk Penuh</span>
            @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                <span class="my-hero-status tolak">❌ Ditolak</span>
            @else
                <span class="my-hero-status siap">{{ $qc->status_qc }}</span>
            @endif
        </div>

        <div class="my-hero-meta-grid">
            <div class="my-hero-meta-item">
                <span>📅</span>
                <span>Periksa: <strong>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong></span>
            </div>
            <div class="my-hero-meta-item">
                <span>👤</span>
                <span>Petugas: <strong>{{ $qc->petugas_qc_nama ?: '-' }}</strong></span>
            </div>
            <div class="my-hero-meta-item">
                <span>🏭</span>
                <span>Gudang: <strong>{{ $qc->gudang?->gudang_nm ?: '-' }}</strong></span>
            </div>
            @if(!empty($qc->batch_no))
                <div class="my-hero-meta-item">
                    <span>🏷️</span>
                    <span>Batch: <strong>{{ $qc->batch_no }}</strong></span>
                </div>
            @endif
        </div>

        {{-- INFO JIKA SUDAH DIPROSES KE GRN GUDANG --}}
        @if ($qc->terima)
            <div class="my-hero-grn-box">
                <span>📦 Ditarik ke Gudang &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong></span>
                <span style="font-size: 0.72rem; color: #dcfce7;">{{ $qc->terima->terima_tgl ? \Carbon\Carbon::parse($qc->terima->terima_tgl)->format('d/m/Y') : '' }}</span>
            </div>
        @endif
    </div>

    {{-- KARTU 1: HASIL TIMBANGAN & TONASE --}}
    <div class="my-card">
        <div class="my-card-header">
            <h2 class="my-card-title">
                <span>⚖️</span> <span>Hasil Timbangan &amp; Kuantitas</span>
            </h2>
            <span class="my-card-badge-right">Satuan: {{ $satuanDtl }}</span>
        </div>

        <div class="my-metrics-row">
            <div class="my-metric-card">
                <div class="my-metric-title">Gross (Bruto) Datang</div>
                <div class="my-metric-val">{{ number_format($totalGross, 0, ',', '.') }}</div>
                <div class="my-metric-sub">{{ $satuanDtlLower }} timbang pabrik</div>
            </div>

            <div class="my-metric-card">
                <div class="my-metric-title">Reject (Afkir)</div>
                <div class="my-metric-val" style="color: var(--my-danger);">{{ number_format($totalReject, 0, ',', '.') }}</div>
                <div class="my-metric-sub">{{ $satuanDtlLower }} ditolak</div>
            </div>

            <div class="my-metric-card netto-hero">
                <div class="my-metric-title">Netto Lolos Diterima</div>
                <div class="my-metric-val">{{ number_format($totalNetto, 0, ',', '.') }}</div>
                <div class="my-metric-sub">{{ $satuanDtlLower }} masuk gudang</div>
            </div>
        </div>

        {{-- PERBANDINGAN SURAT JALAN VS TIMBANGAN PABRIK --}}
        <div style="margin-top: 0.85rem; padding-top: 0.65rem; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; font-size: 0.775rem;">
            <div>
                <span style="color: var(--my-text-muted);">Surat Jalan:</span>
                <strong style="color: #0f172a; margin-left: 0.25rem;">{{ number_format($sjKg, 0, ',', '.') }} {{ $satuanDtlLower }}</strong>
                <span style="color: var(--my-text-muted); margin-left: 0.5rem;">Pabrik:</span>
                <strong style="color: #0284c7; margin-left: 0.25rem;">{{ number_format($pabrikKg, 0, ',', '.') }} {{ $satuanDtlLower }}</strong>
            </div>

            @if ($selisihTimbang != 0)
                <span style="font-size: 0.72rem; font-weight: 800; padding: 2px 7px; border-radius: 6px; {{ $selisihTimbang < 0 ? 'background: #fef2f2; color: #dc2626;' : 'background: #f0fdf4; color: #16a34a;' }}">
                    Selisih: {{ $selisihTimbang > 0 ? '+' : '' }}{{ number_format($selisihTimbang, 0, ',', '.') }} {{ $satuanDtlLower }}
                </span>
            @else
                <span style="font-size: 0.72rem; font-weight: 800; color: #16a34a; background: #dcfce7; padding: 2px 7px; border-radius: 6px;">
                    ✔ Timbangan Sesuai
                </span>
            @endif
        </div>
    </div>

    {{-- KARTU 2: UJI KIMIA LABORATORIUM (FFA - FREE FATTY ACID) --}}
    <div class="my-card">
        <div class="my-card-header">
            <h2 class="my-card-title">
                <span>🧪</span> <span>Uji Laboratorium &bull; Parameter FFA</span>
            </h2>
            <span class="my-card-badge-right" style="background: #e0f2fe; color: #0369a1;">Titik Kritis Mutu</span>
        </div>

        <div class="my-ffa-box">
            <div class="my-ffa-header">
                <span class="my-ffa-title">
                    <span>🔬</span> <span>Asam Lemak Bebas (Free Fatty Acid)</span>
                </span>
                <span class="my-ffa-standard-badge">Standar Maksimal: ≤ 0,200%</span>
            </div>

            <div class="my-ffa-grid">
                <div class="my-ffa-card">
                    <div class="my-ffa-label">FFA di COA Supplier</div>
                    <div class="my-ffa-value">
                        {{ $ffaCoa !== null ? number_format($ffaCoa, 3) . '%' : '-' }}
                    </div>
                </div>

                <div class="my-ffa-card" style="border-color: {{ $ffaPass ? '#86efac' : '#fca5a5' }};">
                    <div class="my-ffa-label" style="color: #0284c7;">FFA Uji QC Mirasa *</div>
                    <div class="my-ffa-value {{ $ffaPass ? 'pass' : 'fail' }}">
                        {{ $ffaQc !== null ? number_format($ffaQc, 3) . '%' : '-' }}
                    </div>
                </div>
            </div>

            <div class="my-ffa-status-badge {{ $ffaPass ? 'pass' : 'fail' }}">
                @if ($ffaPass)
                    <span>✔</span>
                    <span>Lolos Standar Mutu Kimia (FFA ≤ 0,200%) &bull; Layak Produksi</span>
                @else
                    <span>⚠️</span>
                    <span>Melebihi Batas Standar (FFA > 0,200%) &bull; Waspada Bau Tengik / Mutu Menurun</span>
                @endif
            </div>
        </div>
    </div>

    {{-- KARTU 3: KONDISI WADAH, KEJERNIHAN & SANITASI TANGKI --}}
    <div class="my-card">
        <div class="my-card-header">
            <h2 class="my-card-title">
                <span>🛢️</span> <span>Kondisi Wadah &amp; Pemeriksaan Fisik</span>
            </h2>
            <span class="my-card-badge-right">Sanitasi</span>
        </div>

        <div class="my-hygiene-grid">
            <div class="my-hygiene-card">
                <div class="my-hygiene-icon">📦</div>
                <div>
                    <div class="my-hygiene-title">Kondisi Wadah ({{ $tipeWadah }})</div>
                    <div class="my-hygiene-val {{ $kondisiWadahOk ? 'pass' : 'fail' }}">
                        {{ $kondisiWadahOk ? '✔ OK (Standar)' : '✖ Tidak Standar' }}
                    </div>
                </div>
            </div>

            <div class="my-hygiene-card">
                <div class="my-hygiene-icon">🌿</div>
                <div>
                    <div class="my-hygiene-title">Status Raw Material</div>
                    <div class="my-hygiene-val {{ $rawMaterialOk ? 'pass' : 'fail' }}">
                        {{ $rawMaterialOk ? '✔ OK (Sesuai)' : '✖ Menyimpang' }}
                    </div>
                </div>
            </div>

            <div class="my-hygiene-card">
                <div class="my-hygiene-icon">💧</div>
                <div>
                    <div class="my-hygiene-title">Kejernihan Minyak</div>
                    <div class="my-hygiene-val {{ $jernihOk ? 'pass' : 'fail' }}">
                        {{ $jernihOk ? '✔ Jernih / Bebas Endapan' : '✖ Keruh / Ada Endapan' }}
                    </div>
                </div>
            </div>

            <div class="my-hygiene-card">
                <div class="my-hygiene-icon">🧼</div>
                <div>
                    <div class="my-hygiene-title">Bagian Dalam Tangki</div>
                    <div class="my-hygiene-val {{ $tangkiBersihOk ? 'pass' : 'fail' }}">
                        {{ $tangkiBersihOk ? '✔ Bersih &amp; Saniter' : '✖ Kotor / Ada Residu' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 4: INFO PENGIRIMAN, VENDOR & ARMADA --}}
    <div class="my-card">
        <div class="my-card-header">
            <h2 class="my-card-title">
                <span>🚚</span> <span>Pengiriman, Vendor &amp; Armada</span>
            </h2>
            <span class="my-card-badge-right">Logistik</span>
        </div>

        <div class="my-grid-2">
            <div class="my-cell">
                <span class="my-cell-label">Nama Bahan (Master)</span>
                <span class="my-cell-value highlight-amber">
                    {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: 'Minyak Goreng') }}
                </span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Spesifikasi Fraksi / Jenis</span>
                <span class="my-cell-value" style="font-weight: 800; color: #0f172a;">
                    {{ $qc->nama_jenis ?: '-' }}
                </span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Mitra Vendor / Supplier</span>
                <span class="my-cell-value">{{ $qc->supplier?->supplier_nm ?? '-' }}</span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Pabrik Produsen</span>
                <span class="my-cell-value">
                    {{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }} ({{ $qc->negara_produsen ?: 'Indonesia' }})
                </span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Sampel QC Diambil</span>
                <span class="my-cell-value highlight-blue">
                    {{ $qc->jumlah_sample_gr ? number_format((float)$qc->jumlah_sample_gr, 0, ',', '.') . ' gr' : ($qc->jumlah_sample_kg ? number_format((float)$qc->jumlah_sample_kg, 1) . ' kg' : '500 gr') }}
                </span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Referensi PO</span>
                <span class="my-cell-value">
                    @if ($qc->po)
                        <span style="color: #0284c7; font-weight: 800;">{{ $qc->po->po_no }}</span>
                    @else
                        <span style="color: #64748b;">Non-PO (Kiriman Langsung)</span>
                    @endif
                </span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">No. Surat Jalan / DO</span>
                <span class="my-cell-value">{{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}</span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Plat Truk / Tangki</span>
                <span class="my-cell-value highlight-amber">{{ $qc->plat_nomor_truk ?: '-' }}</span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Nama Sopir</span>
                <span class="my-cell-value">{{ $qc->sopir_nama ?: '-' }}</span>
            </div>

            <div class="my-cell">
                <span class="my-cell-label">Waktu Kedatangan</span>
                <span class="my-cell-value">{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</span>
            </div>
        </div>
    </div>

    {{-- KARTU 5: AUDIT HALAL & HIGIENE TRANSPORTASI (SJH) --}}
    <div class="my-card">
        <div class="my-card-header">
            <h2 class="my-card-title">
                <span>🛡️</span> <span>Audit Halal &amp; Higiene Transportasi</span>
            </h2>
            <span class="my-card-badge-right" style="background: #dcfce7; color: #166534;">Sistem Jaminan Halal</span>
        </div>

        <div class="my-halal-list">
            <div class="my-halal-item">
                <div class="my-halal-left">
                    <span class="my-halal-title">1. Kebersihan Armada &amp; Kontainer</span>
                    <span class="my-halal-sub">Bebas dari najis, kotoran, atau bau asing</span>
                </div>
                <span class="my-halal-badge {{ $qc->bebas_cemaran_st ? 'pass' : 'fail' }}">
                    {{ $qc->bebas_cemaran_st ? '✔ Bebas Cemaran' : '✖ Ada Cemaran' }}
                </span>
            </div>

            <div class="my-halal-item">
                <div class="my-halal-left">
                    <span class="my-halal-title">2. Pemisahan Logistik Barang Haram</span>
                    <span class="my-halal-sub">Tidak diangkut bersamaan dengan komoditas haram</span>
                </div>
                <span class="my-halal-badge {{ !$qc->angkut_barang_haram_st ? 'pass' : 'fail' }}">
                    {{ !$qc->angkut_barang_haram_st ? '✔ Bebas Barang Haram' : '✖ Tercampur Barang Haram' }}
                </span>
            </div>

            <div class="my-halal-item">
                <div class="my-halal-left">
                    <span class="my-halal-title">3. Pendaftaran di LPPOM MUI / BPJPH</span>
                    <span class="my-halal-sub">Terdaftar di daftar bahan approved list</span>
                </div>
                <span class="my-halal-badge {{ $qc->terdaftar_lppom_st ? 'pass' : 'fail' }}">
                    {{ $qc->terdaftar_lppom_st ? '✔ Terdaftar' : '✖ Belum Terdaftar' }}
                </span>
            </div>

            <div class="my-halal-item">
                <div class="my-halal-left">
                    <span class="my-halal-title">4. Kepemilikan Sertifikat Halal</span>
                    <span class="my-halal-sub">Memiliki dokumen sertifikat halal produsen resmi</span>
                </div>
                <span class="my-halal-badge {{ $qc->ada_sertifikat_halal_st ? 'pass' : 'fail' }}">
                    {{ $qc->ada_sertifikat_halal_st ? '✔ Ada Sertifikat' : '✖ Tidak Ada' }}
                </span>
            </div>

            <div class="my-halal-item">
                <div class="my-halal-left">
                    <span class="my-halal-title">5. Masa Berlaku Sertifikat Halal</span>
                    <span class="my-halal-sub">Sertifikat halal masih aktif dan berlaku</span>
                </div>
                <span class="my-halal-badge {{ $qc->sertifikat_halal_berlaku_st ? 'pass' : 'fail' }}">
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

    {{-- KARTU 6: KEPUTUSAN, CATATAN & TANDA TANGAN --}}
    <div class="my-card">
        <div class="my-card-header">
            <h2 class="my-card-title">
                <span>💬</span> <span>Keputusan &amp; Catatan QC Minyak</span>
            </h2>
            <span class="my-card-badge-right" style="background: #f0fdf4; color: #166534;">ACC Valid</span>
        </div>

        <div style="margin-bottom: 0.75rem;">
            <div class="my-cell-label">Status Keputusan Akhir:</div>
            <div style="font-size: 1.05rem; font-weight: 900; margin-top: 0.2rem; color: {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '#16a34a' : '#dc2626' }};">
                @if ($qc->status_qc !== 'DITOLAK_TOTAL')
                    ✔ DITERIMA ({{ number_format($totalNetto, 0, ',', '.') }} {{ $satuanDtlLower }} Lolos)
                @else
                    ✖ DITOLAK TOTAL (TIDAK DIBONGKAR)
                @endif
            </div>
        </div>

        @if ($firstDetail?->catatan_dtl ?: $qc->catatan_umum)
            <div style="background: #f8fafc; border-left: 3px solid #d97706; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-style: italic; color: #334155; margin-bottom: 0.75rem;">
                "{{ $firstDetail?->catatan_dtl ?: $qc->catatan_umum }}"
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.7rem 0.85rem; text-align: center;">
                <div style="font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase;">1. Petugas QC Pemeriksa</div>
                <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">{{ $qc->petugas_qc_nama ?: '-' }}</div>
                <div style="font-size: 0.68rem; font-weight: 800; color: #16a34a; margin-top: 0.25rem;">✔ Terverifikasi Lapangan</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.7rem 0.85rem; text-align: center;">
                <div style="font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase;">2. QC Supervisor</div>
                <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</div>
                <div style="font-size: 0.68rem; font-weight: 800; color: #16a34a; margin-top: 0.25rem;">✔ Disetujui (ACC)</div>
            </div>
        </div>
    </div>

</div>

{{-- FIXED BOTTOM DOCK (MOBILE ACTIONS) --}}
<div class="my-bottom-dock">
    <div class="my-bottom-dock-inner">
        @if ($qc->status_qc === 'SIAP_GUDANG')
            <div class="my-dock-status" style="background: #e0f2fe; color: #0369a1; border: 1.5px solid #7dd3fc;">
                <span>⏳</span> <span>Siap Ditarik Gudang (GRN)</span>
            </div>
        @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
            <div class="my-dock-status" style="background: #fff7ed; color: #c2410c; border: 1.5px solid #fdba74;">
                <span>⚠️</span> <span>Masuk Parsial (Ada Reject)</span>
            </div>
        @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
            <div class="my-dock-status" style="background: #dcfce7; color: #15803d; border: 1.5px solid #86efac;">
                <span>✅</span> <span>Selesai (Masuk Penuh)</span>
            </div>
        @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
            <div class="my-dock-status" style="background: #fee2e2; color: #b91c1c; border: 1.5px solid #fca5a5;">
                <span>❌</span> <span>Ditolak di Gerbang</span>
            </div>
        @else
            <div class="my-dock-status" style="background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1;">
                <span>📋</span> <span>{{ $qc->status_qc }}</span>
            </div>
        @endif

        @if (Auth::user()?->canEditQcMobile() && !$isLocked)
            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="my-dock-btn-edit">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Uji</span>
            </a>
        @else
            <button type="button" class="my-dock-btn-locked" disabled>
                <span>🔒 Terkunci</span>
            </button>
        @endif
    </div>
</div>

@endsection
