@extends('layouts.qc-mobile')

@section('title', 'Detail Uji QC: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak.css') }}">
@endpush

@php
    $kat = strtoupper((string)($qc->kategori_barang ?: 'SINGKONG'));
    $firstDetail = $qc->details->first();
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalRefraksi = $qc->details->sum('qty_refraksi');
    $totalReject = $qc->details->sum('qty_reject');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();

    // Deteksi Satuan: Liter vs Kg
    $satuanRaw = $firstDetail?->barang?->satuanDasar?->satuan_nm
        ?: ($firstDetail?->barang?->satuanDasar?->satuan_cd
        ?: ($qc->po?->details?->firstWhere('barang_id', $firstDetail?->barang_id)?->barang?->satuanDasar?->satuan_nm
        ?: ($qc->po?->details?->first()?->barang?->satuanDasar?->satuan_nm ?? '')));

    $isLiter = false;
    if ($satuanRaw) {
        $isLiter = (stripos($satuanRaw, 'liter') !== false || stripos($satuanRaw, 'ltr') !== false || strtoupper(trim($satuanRaw)) === 'L');
    }
    if (!$isLiter && $kat === 'MINYAK') {
        $namaItem = strtoupper(($firstDetail?->barang?->barang_nm ?? '') . ' ' . ($qc->nama_jenis ?? ''));
        if (str_contains($namaItem, 'KELAPA') && !str_contains($namaItem, 'SAWIT')) {
            $isLiter = true;
        }
    }

    $satuanDtl = $isLiter ? 'Liter' : ($kat === 'SINGKONG' ? 'kg' : ($firstDetail?->barang?->satuanDasar?->satuan_cd ?? 'kg'));
    $satuanDtlLower = strtolower($satuanDtl);
@endphp

@section('content')
<div class="qc-detail-wrap">

    {{-- TOP NAV --}}
    <div class="qc-detail-top-nav">
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="qc-detail-back-btn">
            <span>&larr; Riwayat Tiket</span>
        </a>
        <div style="display: flex; align-items: center; gap: 0.45rem;">
            {{-- HANYA SUPER ADMIN YANG BISA KE WEB DAN LANGSUNG MENUJU KE TABEL QC WEB --}}
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn-qc-switch-desktop" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; background: #e0f2fe; border: 1.5px solid #bae6fd; padding: 0.35rem 0.65rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;" title="Kembali ke Tabel QC Web Desktop">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Tabel Web QC</span>
                </a>
            @endif

            {{-- TOMBOL EDIT CEPAT DI TOP HEADER --}}
            @if (Auth::user()?->canEditQc() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" style="font-size: 0.78rem; font-weight: 800; color: #ffffff; background: #0284c7; border: 1.5px solid #0284c7; padding: 0.35rem 0.75rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 4px rgba(2,132,199,0.25);">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>✏️ Edit Uji</span>
                </a>
            @endif
        </div>
    </div>

    {{-- HERO CARD --}}
    <div class="qc-detail-hero">
        <div class="qc-detail-hero-top">
            <div>
                <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                    <span class="badge-tag-commodity">
                        @if ($kat === 'SINGKONG') 🥔 @elseif ($kat === 'MINYAK') 🛢️ @elseif ($kat === 'PLASTIK') 🛍️ @elseif ($kat === 'KARTON') 📦 @else ✨ @endif
                        {{ $kat }}
                    </span>
                    @if ($kat === 'SINGKONG')
                        @php
                            $firstDtl = $qc->details->first();
                            $grade = $firstDtl?->grade_cd ?? 'A';
                        @endphp
                        <span style="font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 6px; {{ ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;' : 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' }}">
                            {{ ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '🍟 PENGUJIAN II (PRODUKSI)' : '🚛 PENGUJIAN I (KEDATANGAN)' }}
                        </span>
                        @if ($grade === 'B')
                            <span style="font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 6px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                🟡 GRADE B
                            </span>
                        @elseif ($grade === 'REJECT')
                            <span style="font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 6px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                                ❌ AFKIR
                            </span>
                        @else
                            <span style="font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 6px; background: #dcfce7; color: #15803d; border: 1px solid #86efac;">
                                🟢 GRADE A
                            </span>
                        @endif
                    @endif
                </div>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem;">{{ $qc->qc_no }}</h1>
            </div>
                @if ($qc->status_qc === 'SIAP_GUDANG')
                    <span class="badge-tag-status siap">⏳ Siap Gudang</span>
                @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $qc->details->sum('qty_reject') > 0))
                    <span class="badge-tag-status" style="background: #fff7ed; color: #c2410c; border: 1px solid #fdba74;">⚠️ Masuk Parsial</span>
                @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                    <span class="badge-tag-status diterima">✅ Masuk Penuh</span>
                @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                    <span class="badge-tag-status tolak">❌ Ditolak</span>
                @else
                    <span class="badge-tag-status siap">{{ $qc->status_qc }}</span>
                @endif
        </div>

        <div class="qc-detail-hero-meta">
            <div>📅 <strong>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong></div>
            <div>👤 Petugas QC: <strong>{{ $qc->petugas_qc_nama }}</strong></div>
            @if ($qc->terima)
                <div style="margin-top: 0.25rem; background: rgba(0,0,0,0.2); padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.75rem;">
                    📦 Sudah Masuk Gudang &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong>
                </div>
            @endif
            @if(!empty($qc->batch_no) || !empty($qc->parent_qc_id))
                <div style="margin-top: 0.35rem; background: rgba(147, 51, 234, 0.25); padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.775rem; border: 1px solid rgba(216, 180, 254, 0.4);">
                    🥔 Batch: <strong>{{ $qc->batch_no ?: '-' }}</strong>
                    @if($qc->parentQc)
                        &bull; QC Asal: <a href="{{ route('qc.inbound.show', [$qc->parentQc->qc_id, 'view' => 'mobile']) }}" style="color: #ffffff; text-decoration: underline; font-weight: 700;">#{{ $qc->parentQc->qc_no }}</a>
                    @endif
                </div>
            @endif
            @if($qc->pengujian2List && $qc->pengujian2List->count() > 0)
                <div style="margin-top: 0.35rem; background: rgba(220, 38, 38, 0.25); padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.775rem; border: 1px solid rgba(254, 202, 202, 0.4);">
                    ⚠️ Memiliki {{ $qc->pengujian2List->count() }} Laporan Pengujian II (Produksi):
                    @foreach($qc->pengujian2List as $p2)
                        <a href="{{ route('qc.inbound.show', [$p2->qc_id, 'view' => 'mobile']) }}" style="color: #ffffff; text-decoration: underline; font-weight: 700; margin-left: 0.35rem;">#{{ $p2->qc_no }}</a>
                    @endforeach
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
                <span class="qc-info-label">Nama Bahan (Master)</span>
                <span class="qc-info-value" style="font-weight: 800; color: #0284c7;">
                    {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: '-') }}
                </span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">NAMA JENIS :</span>
                <span class="qc-info-value" style="font-weight: 800; color: #0f172a;">
                    {{ $qc->nama_jenis ?: '-' }}
                </span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">Produsen &amp; Negara</span>
                <span class="qc-info-value">
                    {{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }} ({{ $qc->negara_produsen ?: 'Indonesia' }})
                </span>
            </div>
            <div class="qc-info-item">
                <span class="qc-info-label">Sampel Uji</span>
                <span class="qc-info-value" style="font-weight: 700;">
                    {{ $qc->jumlah_sample_gr ? $qc->jumlah_sample_gr . ' gr' : ($qc->jumlah_sample_kg ? $qc->jumlah_sample_kg . ' kg' : '250 gr') }}
                </span>
            </div>
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
        <div class="qc-metrics-grid" style="grid-template-columns: repeat({{ $kat === 'MINYAK' ? 3 : 4 }}, 1fr);">
            <div class="qc-metric-box">
                <div class="qc-metric-title">Gross (Bruto)</div>
                <div class="qc-metric-number">{{ number_format($totalGross, 0, ',', '.') }}</div>
                <div class="qc-metric-sub">{{ $satuanDtlLower }} timbang</div>
            </div>
            @if ($kat !== 'MINYAK')
            <div class="qc-metric-box">
                <div class="qc-metric-title">Refraksi</div>
                <div class="qc-metric-number" style="color: #d97706;">{{ number_format($totalRefraksi, 0, ',', '.') }}</div>
                <div class="qc-metric-sub">{{ number_format($firstDetail?->refraksi_persen ?? 0, 1) }}% potongan</div>
            </div>
            @endif
            <div class="qc-metric-box">
                <div class="qc-metric-title">Reject (Afkir)</div>
                <div class="qc-metric-number" style="color: #dc2626;">{{ number_format($totalReject, 0, ',', '.') }}</div>
                <div class="qc-metric-sub">{{ $satuanDtlLower }} ditolak</div>
            </div>
            <div class="qc-metric-box highlight">
                <div class="qc-metric-title" style="color: #059669;">Netto Lolos</div>
                <div class="qc-metric-number">{{ number_format($totalNetto, 0, ',', '.') }}</div>
                <div class="qc-metric-sub" style="color: #059669; font-weight: 700;">{{ $satuanDtlLower }} diterima pabrik</div>
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

            {{-- HASIL UJI CEPAT RASA FRYER DI DEPAN --}}
            <div style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.85rem; margin-top: 0.85rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.45rem;">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #d97706; text-transform: uppercase; display: flex; align-items: center; gap: 0.35rem;">
                        <span>🍟</span> <span>Hasil Uji Cepat Rasa Fryer (Di Depan)</span>
                    </div>

                    @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                        <span style="font-size: 0.72rem; font-weight: 800; background: #fef3c7; color: #b45309; padding: 0.2rem 0.6rem; border-radius: 20px; border: 1px solid #fde68a;">
                            ⏳ Menunggu Lab Fryer
                        </span>
                    @else
                        <span style="font-size: 0.72rem; font-weight: 800; background: #ecfdf5; color: #059669; padding: 0.2rem 0.6rem; border-radius: 20px; border: 1px solid #a7f3d0;">
                            ✔ Selesai Diuji Depan
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
                        <div style="font-weight: 800; color: #475569; margin-bottom: 0.3rem;">Persentase Cacat Goreng (Defect Frying):</div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(95px, 1fr)); gap: 0.35rem; font-weight: 700; color: #1e293b; font-size: 0.75rem;">
                            <span>Breakage: {{ $firstDetail?->defect_breakage_persen ?? 0 }}%</span>
                            <span>Cluster: {{ $firstDetail?->defect_cluster_persen ?? 0 }}%</span>
                            <span>Foldover: {{ $firstDetail?->defect_foldover_persen ?? 0 }}%</span>
                            <span>Oilsoaked: {{ $firstDetail?->defect_oilsoaked_persen ?? 0 }}%</span>
                            <span>Gambos: {{ $firstDetail?->defect_gambos_persen ?? 0 }}%</span>
                        </div>
                    </div>

                    <div style="margin-top: 0.6rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.725rem; color: #64748b;">
                        <span>Diuji oleh: <strong>{{ $qc->petugas_uji_goreng ?: 'Petugas Lab' }}</strong> ({{ $qc->tgl_uji_goreng ? \Carbon\Carbon::parse($qc->tgl_uji_goreng)->format('d/m/Y H:i') : '-' }})</span>
                        <button type="button" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $firstDetail?->qcdtl_id }}', { rasa: '{{ $firstDetail?->fryer_rasa }}', tekstur: '{{ $firstDetail?->fryer_tekstur }}', penampakan: '{{ $firstDetail?->fryer_penampakan }}', breakage: '{{ $firstDetail?->defect_breakage_persen }}', cluster: '{{ $firstDetail?->defect_cluster_persen }}', gambos: '{{ $firstDetail?->defect_gambos_persen }}' })" style="background: none; border: none; color: #0284c7; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            Koreksi Lab
                        </button>
                    </div>
                @endif

                {{-- PENGUJIAN II (PRODUKSI / WAFEL / WAJAN) --}}
                @if (($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_1')
                    <div style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.85rem; margin-top: 0.85rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.45rem;">
                            <div style="font-size: 0.82rem; font-weight: 800; color: #7e22ce; text-transform: uppercase; display: flex; align-items: center; gap: 0.35rem;">
                                <span>🍟</span> <span>QC Pengujian II (Lantai Produksi)</span>
                            </div>
                            @if ($qc->pengujian2List && $qc->pengujian2List->count() > 0)
                                <span style="font-size: 0.72rem; font-weight: 800; background: #ecfdf5; color: #059669; padding: 0.2rem 0.5rem; border-radius: 20px; border: 1px solid #a7f3d0;">
                                    ✔ Sudah Diuji II ({{ $qc->pengujian2List->count() }}x)
                                </span>
                            @else
                                <span style="font-size: 0.72rem; font-weight: 800; background: #f3e8ff; color: #7e22ce; padding: 0.2rem 0.5rem; border-radius: 20px; border: 1px solid #d8b4fe;">
                                    Siap Diuji II
                                </span>
                            @endif
                        </div>

                        <p style="font-size: 0.78rem; color: #64748b; margin: 0 0 0.65rem 0; line-height: 1.4;">
                            Pemeriksaan ulang fisik &amp; uji organoleptik wajan saat batch kedatangan ini ditarik ke proses produksi.
                        </p>

                        @if ($qc->pengujian2List && $qc->pengujian2List->count() > 0)
                            <div style="margin-bottom: 0.65rem; display: flex; flex-direction: column; gap: 0.4rem;">
                                @foreach ($qc->pengujian2List as $p2)
                                    <a href="{{ route('qc.inbound.show', [$p2->qc_id, 'view' => 'mobile']) }}" style="display: flex; align-items: center; justify-content: space-between; background: #faf5ff; border: 1px solid #d8b4fe; border-radius: 8px; padding: 0.5rem 0.75rem; text-decoration: none; color: #7e22ce; font-size: 0.8rem; font-weight: 700;">
                                        <span>🍟 {{ $p2->qc_no }} ({{ $p2->tgl_periksa ? $p2->tgl_periksa->format('d/m/Y H:i') : '-' }})</span>
                                        <span style="font-size: 0.75rem; color: #9333ea; font-weight: 800;">Buka Hasil &rarr;</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if (Auth::user()?->canCreateQc() && !$qc->isPengujian2Done())
                            @php
                                $batchVal = $qc->batch_no ?: ($qc->terima?->details?->first()?->batch_no ?: 'BATCH-' . $qc->qc_no);
                            @endphp
                            <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'view' => 'mobile']) }}" style="width: 100%; padding: 0.7rem; border-radius: 10px; background: #9333ea; color: #ffffff; font-weight: 800; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.4rem; box-shadow: 0 3px 6px -1px rgba(147, 51, 234, 0.3);">
                                <span>🍟</span> <span>Lakukan Pengujian 2 Sekarang &rarr;</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        {{-- JIKA KOMODITAS MINYAK --}}
        @elseif ($kat === 'MINYAK')
            <div class="qc-param-list">
                <div class="qc-param-row">
                    <span class="qc-param-name">Komoditas Minyak</span>
                    <span class="qc-param-val" style="font-weight: 800; color: #0284c7;">
                        {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: 'Minyak Goreng') }}
                    </span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">FFA di COA Supplier</span>
                    <span class="qc-param-val">{{ $firstDetail?->ffa_coa !== null ? number_format((float)$firstDetail->ffa_coa, 3) . '%' : '-' }}</span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">FFA Uji QC Mirasa (Max 0,200%)</span>
                    <span class="qc-param-val {{ (float)($firstDetail?->ffa_qc ?? 0) <= 0.20 ? 'pass' : 'fail' }}">
                        {{ $firstDetail?->ffa_qc !== null ? number_format((float)$firstDetail->ffa_qc, 3) . '%' : '-' }}
                        @if((float)($firstDetail?->ffa_qc ?? 0) > 0.20) ⚠️ Melebihi Batas @endif
                    </span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Tipe Wadah Minyak</span>
                    <span class="qc-param-val">
                        {{ $firstDetail?->tipe_wadah_minyak ?: 'TANGKI' }}
                    </span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Kondisi Wadah</span>
                    <span class="qc-param-val {{ ($firstDetail?->kondisi_tangki_jerigen ?? 'OK') === 'OK' ? 'pass' : 'fail' }}">
                        {{ ($firstDetail?->kondisi_tangki_jerigen ?? 'OK') === 'OK' ? '✔ OK (Standar)' : '✖ Tidak Standar' }}
                    </span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Isi Raw Material</span>
                    <span class="qc-param-val {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'pass' : 'fail' }}">
                        {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔ OK' : '✖ Tidak Standar' }}
                    </span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Kejernihan Minyak</span>
                    <span class="qc-param-val {{ $firstDetail?->minyak_jernih_st ? 'pass' : 'fail' }}">
                        {{ $firstDetail?->minyak_jernih_st ? '✔ Bebas Endapan / Jernih' : '✖ Keruh / Ada Endapan' }}
                    </span>
                </div>
                <div class="qc-param-row">
                    <span class="qc-param-name">Kebersihan Tangki</span>
                    <span class="qc-param-val {{ $firstDetail?->tangki_bersih_st ? 'pass' : 'fail' }}">
                        {{ $firstDetail?->tangki_bersih_st ? '✔ Bersih' : '✖ Tangki Kotor' }}
                    </span>
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
                    ✔ DITERIMA ({{ number_format($totalNetto, 0, ',', '.') }} {{ $satuanDtlLower }} Lolos)
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
                <div class="qc-sign-role">1. Petugas QC Pemeriksa</div>
                <div class="qc-sign-name">{{ $qc->petugas_qc_nama }}</div>
                <div class="qc-sign-status">✔ Terverifikasi (Depan)</div>
            </div>
            <div class="qc-sign-card">
                <div class="qc-sign-role">2. QC Supervisor</div>
                <div class="qc-sign-name">{{ $qc->qc_supervisor_nama ?: 'Kepala Direktur / Supervisor' }}</div>
                <div class="qc-sign-status">✔ Disetujui (ACC)</div>
            </div>
        </div>
    </div>

    {{-- KARTU AKSI CEPAT OPERASIONAL (SELALU TERLIHAT DI HALAMAN) --}}
    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 1.1rem 1.25rem; margin-top: 1.25rem; box-shadow: 0 2px 5px rgba(0,0,0,0.04); display: flex; flex-direction: column; gap: 0.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <span style="font-weight: 800; font-size: 0.85rem; color: #334155; text-transform: uppercase; letter-spacing: 0.03em;">Tindakan Operasional</span>
            @if ($qc->status_qc === 'SIAP_GUDANG')
                <span style="font-size: 0.75rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 0.2rem 0.6rem; border-radius: 6px;">⏳ Siap Ditarik Gudang</span>
            @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $qc->details->sum('qty_reject') > 0))
                <span style="font-size: 0.75rem; font-weight: 800; color: #c2410c; background: #fff7ed; border: 1px solid #fdba74; padding: 0.2rem 0.6rem; border-radius: 6px;">⚠️ Diterima Parsial (Reject)</span>
            @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                <span style="font-size: 0.75rem; font-weight: 800; color: #15803d; background: #dcfce7; padding: 0.2rem 0.6rem; border-radius: 6px;">✅ Masuk Gudang (Penuh)</span>
            @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                <span style="font-size: 0.75rem; font-weight: 800; color: #dc2626; background: #fee2e2; padding: 0.2rem 0.6rem; border-radius: 6px;">❌ Ditolak di Gerbang</span>
            @endif
        </div>

        @if (Auth::user()?->canEditQc() && !$isLocked)
            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #0284c7; color: #ffffff; font-size: 0.95rem; font-weight: 800; padding: 0.85rem 1.25rem; border-radius: 10px; text-decoration: none; box-shadow: 0 3px 6px rgba(2,132,199,0.3);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>✏️ Edit &amp; Koreksi Data Sampling QC</span>
            </a>
        @elseif ($isLocked)
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem; font-size: 0.8rem; font-weight: 700; color: #64748b; text-align: center;">
                🔒 Tiket terkunci karena barang sudah diproses masuk gudang (GRN)
            </div>
        @endif
    </div>

</div>

{{-- FIXED BOTTOM BAR MOBILE --}}
<div class="qc-mobile-bottom-bar" style="display: flex; gap: 0.65rem; align-items: center;">
    @if ($qc->status_qc === 'SIAP_GUDANG')
        <div style="flex: 2; background: #e0f2fe; border: 1.5px solid #7dd3fc; border-radius: 10px; padding: 0.65rem 0.85rem; font-size: 0.8rem; font-weight: 800; color: #0369a1; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
            <span>⏳</span> <span>Siap Ditarik Gudang (GRN)</span>
        </div>
    @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $qc->details->sum('qty_reject') > 0))
        <div style="flex: 2; background: #fff7ed; border: 1.5px solid #fdba74; border-radius: 10px; padding: 0.65rem 0.85rem; font-size: 0.8rem; font-weight: 800; color: #c2410c; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
            <span>⚠️</span> <span>Masuk Parsial (Ada Reject)</span>
        </div>
    @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
        <div style="flex: 2; background: #dcfce7; border: 1.5px solid #86efac; border-radius: 10px; padding: 0.65rem 0.85rem; font-size: 0.8rem; font-weight: 800; color: #15803d; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
            <span>✅</span> <span>Selesai (Masuk Penuh)</span>
        </div>
    @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
        <div style="flex: 2; background: #fee2e2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 0.65rem 0.85rem; font-size: 0.8rem; font-weight: 800; color: #b91c1c; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
            <span>❌</span> <span>Ditolak di Gerbang (Truk Pulang)</span>
        </div>
    @endif

    @if (Auth::user()?->canEditQc() && !$isLocked)
        <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'detail']) }}" class="qc-btn-mobile-edit" style="flex: 1; text-align: center;">
            <span>✏️ Edit Uji</span>
        </a>
    @else
        <button type="button" class="qc-btn-mobile-edit" style="flex: 1; background: #94a3b8; cursor: not-allowed;" disabled>
            <span>🔒 Terkunci</span>
        </button>
    @endif
</div>

{{-- INCLUDE MODAL UJI GORENG FRYER --}}
@include('gudang.qc.partials.singkong.modal-uji-fryer')

@endsection
