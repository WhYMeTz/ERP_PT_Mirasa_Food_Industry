@extends('layouts.app')

@section('title', 'Kalkulasi & Lembar HPP Produksi (Tahap 3)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/hpp/hpp-create.css') }}?v={{ filemtime(public_path('css/produksi/hpp/hpp-create.css')) }}">
@endpush

@section('content')
<div class="hpp-container">
    {{-- Breadcrumb & Header Navigasi (Identik dengan Formulir Hasil Produksi & Pengemasan) --}}
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('produksi.rekap') }}" 
           style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem; font-weight: 600; transition: color 0.15s ease;"
           onmouseover="this.style.color='#0f172a'"
           onmouseout="this.style.color='#64748b'"
           title="Kembali ke Buku Rekapitulasi HPP">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Buku Rekap HPP
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Kalkulasi &amp; Penetapan Lembar HPP Produksi
                </h1>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Penetapan nilai rupiah bahan baku, utilitas energi gas CNG (kurs JISDOR BI), biaya tenaga kerja, dan alokasi HPP per jenis produk WIP.
                </p>
            </div>
        </div>
    </div>

    @if(session('warning'))
        <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.85rem 1.25rem; color: #92400e; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: flex-start; gap: 0.75rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>{!! nl2br(e(session('warning'))) !!}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-danger-hpp">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form id="formHppProduksi" action="{{ route('produksi.hpp.store') }}" method="POST">
        @csrf

        {{-- CARD 1: DOKUMEN SPK & RINCIAN BAHAN BAKU GUDANG (BPPB) --}}
        <div class="hpp-card">
            <div class="hpp-card-header">
                <div class="hpp-step-badge">1</div>
                <div>
                    <h2 class="hpp-card-title">Dokumen SPK &amp; Pemakaian Bahan Baku Gudang (BPPB)</h2>
                    <p class="hpp-card-desc">Pilih nomor SPK produksi fisik dan verifikasi seluruh rincian pemakaian bahan baku dari dokumen pengeluaran gudang.</p>
                </div>
            </div>
            <div class="hpp-card-body">
                <div class="form-group-hpp">
                    <label for="produksi_id" class="hpp-label">Nomor Dokumen SPK Produksi Tersedia <span class="required">*</span></label>
                    <select name="produksi_id" id="produksi_id" class="hpp-select" required onchange="onProduksiSelectChange(this.value)">
                        <option value="">-- Pilih Dokumen Produksi Fisik --</option>
                        @forelse($produksiList as $p)
                            @php
                                $totalKg = (float) $p->outputs->sum('qty_kg');
                                if ($totalKg <= 0) $totalKg = (float) $p->total_wip_qty;
                            @endphp
                            <option value="{{ $p->produksi_id }}" 
                                    data-no="{{ $p->produksi_no }}"
                                    data-tgl="{{ \Carbon\Carbon::parse($p->produksi_tgl)->format('d/m/Y') }}"
                                    data-shift="{{ $p->shift_cd }}"
                                    data-lini="{{ $p->lini_produksi ?? 'Lini Manual' }}"
                                    {{ (string) $p->produksi_id === (string) old('produksi_id', $selectedProduksiId) ? 'selected' : '' }}>
                                [{{ $p->produksi_no }}] {{ \Carbon\Carbon::parse($p->produksi_tgl)->format('d/m/Y') }} &mdash; Shift {{ $p->shift_cd }} &bull; {{ $p->lini_produksi ?? 'Lini Manual' }} ({{ number_format($totalKg, 2, ',', '.') }} kg WIP)
                            </option>
                        @empty
                            <option value="" disabled>(Tidak ada dokumen fisik baru yang belum dihitung HPP)</option>
                        @endforelse
                    </select>

                    {{-- KOTAK MONITORING DOKUMEN LAIN PADA TANGGAL YANG SAMA (MENCEGAH DATA TERLEWAT) --}}
                    <div id="sameDayNoticeBox" style="display: none; margin-top: 0.85rem; background: #fffdf5; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.75rem 1rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.4rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="font-size: 0.9rem;">📅</span>
                                <strong id="sameDayTitle" style="font-size: 0.825rem; color: #92400e;">Daftar Dokumen Produksi pada Tanggal Ini:</strong>
                            </div>
                            <span id="sameDaySummaryBadge" style="font-size: 0.7rem; font-weight: 700; background: #fef3c7; color: #92400e; padding: 1px 7px; border-radius: 4px; border: 1px solid #fde68a;">
                                Multi-Shift
                            </span>
                        </div>
                        <div id="sameDayListContainer" style="display: flex; flex-direction: column; gap: 0.35rem;">
                            {{-- Diisi secara dinamis via JS loadProduksiData / renderSameDayNotice --}}
                        </div>
                    </div>

                    @if($produksiList->isEmpty())
                        <div class="spk-empty-alert">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Seluruh dokumen produksi fisik telah memiliki penetapan HPP. Untuk meninjau atau mencetak lembar HPP yang sudah tersimpan, silakan buka <a href="{{ route('produksi.rekap') }}" class="alert-link">Buku Rekap HPP</a>.</span>
                        </div>
                    @endif
                </div>

                {{-- NOTIFIKASI LOADING SAAT TARIK DATA SPK --}}
                <div id="spkLoadingNotice" class="spk-loading-box" style="display: none;">
                    <div class="spk-loading-spinner">
                        <svg class="spin-svg" width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="#cbd5e1" stroke-width="3"></circle>
                            <path fill="#059669" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="spk-loading-content">
                        <strong id="spkLoadingTitle">Menarik Data Dokumen Produksi Fisik...</strong>
                        <p id="spkLoadingSub">Menghubungkan dokumen pengeluaran gudang (BPPB), hasil timbangan WIP, utilitas gas, dan absensi tenaga kerja...</p>
                    </div>
                </div>

                {{-- PLACEHOLDER SEBELUM SPK DIPILIH --}}
                <div id="spkPlaceholderNotice" class="spk-placeholder-box">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Silakan pilih nomor SPK produksi fisik pada pilihan di atas untuk memuat data bahan dan rincian hasil olahan.</span>
                </div>

                {{-- RINGKASAN PARAMETER DOKUMEN SPK TERPILIH (STANDAR ERP MIRASA) --}}
                <div id="previewFisikSection" class="spk-param-box" style="display: none;">
                    <div class="spk-param-grid">
                        <div class="spk-param-item">
                            <span class="spk-param-label">No. Dokumen SPK</span>
                            <span class="spk-param-value font-mono" id="pvProduksiNo">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Tanggal Produksi</span>
                            <span class="spk-param-value" id="pvTgl">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Shift &amp; Lini Produksi</span>
                            <span class="spk-param-value" id="pvShiftLini">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Dokumen BPPB Bahan</span>
                            <span class="spk-param-value font-mono" id="pvBppb">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Singkong Masuk</span>
                            <span class="spk-param-value" id="pvSingkongQty">0 kg</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Total Hasil Olahan WIP</span>
                            <span class="spk-param-value" id="pvWipQty" style="color: #059669;">0 kg</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Rendemen Penggorengan</span>
                            <span class="spk-param-value" id="pvRendemen" style="color: #0284c7;">0,00 %</span>
                        </div>
                    </div>
                </div>

                <div class="card-inner-divider" style="margin: 1.35rem 0; border-top: 1.5px dashed #e2e8f0;"></div>

                {{-- INFO DOKUMEN BPPB --}}
                <div class="bppb-info-banner">
                    <div class="bppb-info-item">
                        <span class="bppb-info-lbl">No Dokumen BPPB:</span>
                        <strong class="bppb-info-val" id="lblBppbNo">-</strong>
                    </div>
                    <div class="bppb-info-item">
                        <span class="bppb-info-lbl">Tanggal Pengeluaran:</span>
                        <strong class="bppb-info-val" id="lblBppbTgl">-</strong>
                    </div>
                    <div class="bppb-info-item">
                        <span class="bppb-info-lbl">Gudang Asal Bahan:</span>
                        <strong class="bppb-info-val" id="lblBppbGudang">-</strong>
                    </div>
                    <div class="bppb-info-item" style="margin-left: auto;">
                        <span class="bppb-info-lbl">Total Nilai Pemakaian:</span>
                        <strong class="bppb-info-val highlight-bppb-val" id="lblBppbTotal">Rp 0</strong>
                    </div>
                </div>

                {{-- TOOLBAR & TABEL BARANG YANG DIGUNAKAN (DITARIK DARI BPPB) --}}
                <div class="table-bppb-toolbar">
                    <div class="table-bppb-title">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Rincian Barang Keluar dari Gudang (BPPB)</span>
                    </div>
                    <div class="table-bppb-actions">
                        <button type="button" id="btnResetBppb" class="btn-reset-bppb" style="display: none;" onclick="resetBppbToOriginal()" title="Kembalikan semua nilai ke data asli BPPB">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reset</span>
                        </button>
                        <button type="button" id="btnToggleKoreksiBppb" class="btn-koreksi-bppb" onclick="toggleKoreksiBppb()" title="Buka pengunci untuk menyesuaikan nilai bahan">
                            <svg id="svgLockBppb" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span id="txtBtnKoreksiBppb">Koreksi</span>
                        </button>
                    </div>
                </div>

                <div class="table-pemakaian-wrap">
                    <table class="table-pemakaian">
                        <thead>
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Nama Bahan &amp; Kode</th>
                                <th>Kategori Barang</th>
                                <th>Batch Bahan Masuk</th>
                                <th style="text-align: right;">Qty Keluar</th>
                                <th style="text-align: center; width: 70px;">Satuan</th>
                                <th style="text-align: right; width: 140px;">Harga Satuan</th>
                                <th style="text-align: right; width: 170px;">Subtotal Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="pemakaianTableBody">
                            <tr>
                                <td colspan="8" class="table-empty-row">
                                    <div class="empty-info-msg">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Pilih dokumen produksi fisik pada Card 1 untuk menarik rincian barang dari dokumen pengeluaran gudang (BPPB).</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="pemakaianTableFoot" style="display: none;">
                            <tr class="pemakaian-tfoot-row">
                                <td colspan="7" style="text-align: right; font-weight: 700;">TOTAL ESTIMASI BAHAN TERPAKAI:</td>
                                <td style="text-align: right; font-weight: 800; color: #0f172a;" id="lblSubtotalPemakaian">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- HIDDEN INPUTS AGAR FORM SUBMIT KE CONTROLLER TETAP 100% KOMPATIBEL --}}
                <input type="hidden" name="singkong_nilai" id="singkong_nilai" value="0">
                <input type="hidden" name="minyak_nilai" id="minyak_nilai" value="0">
                <input type="hidden" name="bumbu_nilai" id="bumbu_nilai" value="0">
                <input type="hidden" name="karton_baru_nilai" id="karton_baru_nilai" value="0">
                <input type="hidden" name="karton_bekas_nilai" id="karton_bekas_nilai" value="0">
                <input type="hidden" name="plastik_hd_nilai" id="plastik_hd_nilai" value="0">
                <input type="hidden" name="lakban_besar_nilai" id="lakban_besar_nilai" value="0">
                <input type="hidden" name="lakban_kecil_nilai" id="lakban_kecil_nilai" value="0">
                <input type="hidden" name="tali_rafia_nilai" id="tali_rafia_nilai" value="0">

                {{-- REKAPITULASI RINGKAS TOTAL BIAYA BAHAN (READ-ONLY BREAKDOWN) --}}
                <div class="bppb-summary-breakdown">
                    <div class="bppb-breakdown-card">
                        <div class="bppb-breakdown-lbl">1. Bahan Baku Utama (BBL)</div>
                        <div class="bppb-breakdown-val" id="dispSumBahanUtama">Rp 0</div>
                        <small class="bppb-breakdown-hint" id="hintSumBahanUtama">Singkong, Minyak Goreng, Bumbu Tabur</small>
                    </div>

                    <div class="bppb-breakdown-card">
                        <div class="bppb-breakdown-lbl">2. Kemasan Penunjang</div>
                        <div class="bppb-breakdown-val" id="dispSumKemasan">Rp 0</div>
                        <small class="bppb-breakdown-hint">Karton, Plastik HD, Lakban, Tali Rafia</small>
                    </div>

                    <div class="bppb-breakdown-card bppb-breakdown-total">
                        <div class="bppb-breakdown-lbl">Total Biaya Bahan Baku &amp; Kemasan</div>
                        <div class="bppb-breakdown-val" id="dispSumTotalBahan">Rp 0</div>
                        <span class="bppb-breakdown-status" id="dispSumBppbStatus">✓ Sesuai BPPB Gudang</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: BIAYA UTILITAS ENERGI GAS CNG & TENAGA KERJA LANGSUNG (BTKL) --}}
        <div class="hpp-card">
            <div class="hpp-card-header">
                <div class="hpp-step-badge">2</div>
                <div>
                    <h2 class="hpp-card-title">Biaya Utilitas Energi Gas CNG &amp; Tenaga Kerja Langsung (BTKL)</h2>
                    <p class="hpp-card-desc">Kalkulasi biaya energi boiler / fryer gas CNG (kurs JISDOR BI) dan upah harian regu kerja shift pengolahan.</p>
                </div>
            </div>
            <div class="hpp-card-body">
                <div class="cng-labor-split-grid">
                    {{-- KOLOM KIRI: UTILITAS ENERGI GAS CNG --}}
                    <div class="cng-subcard">
                        <div class="subcard-title-bar">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                            <span>Utilitas Energi Gas Alam (CNG)</span>
                        </div>

                        <div class="form-group-hpp">
                            <label for="cng_mmbtu" class="hpp-label">Meteran Fisik Gas CNG (MMBTU) <span class="required">*</span></label>
                            <input type="number" step="any" min="0" name="cng_mmbtu" id="cng_mmbtu" class="hpp-input" value="0" required oninput="calcCngFromMmbtu()">
                            <small class="helper-text">Ditarik otomatis dari pencatatan meteran shift pabrik.</small>
                        </div>

                        <div class="grid-2-sm">
                            <div class="form-group-hpp">
                                <label for="cng_tarif_usd" class="hpp-label-sm">Tarif Kontrak (USD / MMBTU)</label>
                                <div class="input-usd-wrap">
                                    <span class="prefix-usd">$</span>
                                    <input type="number" step="0.01" min="0" name="cng_tarif_usd" id="cng_tarif_usd" class="hpp-input-sm" value="{{ $defaultCngTarifUsd }}" oninput="calcCngFromTarif()">
                                </div>
                                <small class="helper-text">Standar vendor: $14.50</small>
                            </div>

                            <div class="form-group-hpp">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.35rem;">
                                    <label for="cng_kurs_jisdor" class="hpp-label-sm">Kurs JISDOR BI (Rp)</label>
                                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                                        <button type="button" class="btn-sync-kurs" onclick="fetchLiveJisdor(true)" title="Sinkronkan kurs JISDOR Bank Indonesia terkini">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Sync</span>
                                        </button>
                                        <a href="https://www.bi.go.id/id/statistik/informasi-kurs/jisdor/default.aspx" target="_blank" rel="noopener noreferrer" class="btn-link-bi" title="Buka Portal Resmi Bank Indonesia (JISDOR)">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            <span>Web BI</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="input-rp-wrap">
                                    <span class="prefix-rp">Rp</span>
                                    <input type="number" step="any" min="0" name="cng_kurs_jisdor" id="cng_kurs_jisdor" class="hpp-input-sm" value="{{ $defaultCngKursJisdor }}" oninput="calcCngFromTarif()">
                                </div>
                                <small class="helper-text" id="helpKursJisdor">Referensi resmi BI: <strong>Rp {{ number_format($defaultCngKursJisdor, 0, ',', '.') }}</strong> ({{ $latestKursDate ?? date('d/m/Y') }} &bull; <span style="color: #059669; font-weight: 600;">Kurs tanggal produksi</span>)</small>
                            </div>
                        </div>

                        <div class="form-group-hpp">
                            <label for="cng_tarif" class="hpp-label">Tarif Efektif Terkonversi (Rp / MMBTU)</label>
                            <div class="input-rp-wrap">
                                <span class="prefix-rp">Rp</span>
                                <input type="number" step="any" min="0" name="cng_tarif" id="cng_tarif" class="hpp-input" value="{{ $defaultCngTarif }}" oninput="calcCngFromMmbtu()">
                            </div>
                            <small class="helper-text">Hasil konversi: USD &times; Kurs JISDOR</small>
                        </div>

                        <div class="cng-result-banner">
                            <div class="cng-result-info">
                                <span class="cng-res-label">TOTAL BIAYA GAS CNG</span>
                                <span class="cng-res-formula" id="lblCngFormula">0 MMBTU &times; Rp 0</span>
                            </div>
                            <div class="banner-total-value cng-value-text">
                                <span class="banner-val-currency">Rp</span>
                                <span class="banner-val-amount" id="disp_cng_nilai">0</span>
                                <input type="hidden" name="cng_nilai" id="cng_nilai" value="0">
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM KANAN: TENAGA KERJA LANGSUNG (BTKL PRODUKSI) --}}
                    <div class="labor-subcard">
                        <div class="subcard-title-bar">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>Biaya Tenaga Kerja Langsung (BTKL)</span>
                            </div>
                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                <button type="button" class="btn-sync-tk" onclick="syncLaborFromKaryawan(true)" title="Sinkronkan presensi dari Modul Manajemen Karyawan">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Sync Presensi</span>
                                </button>
                                <a href="{{ route('manajemen.absensi.index') }}" target="_blank" rel="noopener noreferrer" class="btn-link-bi" title="Buka Modul Absensi Manajemen Karyawan">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <span>Data Absensi</span>
                                </a>
                            </div>
                        </div>

                        <div class="btkl-breakdown-container">
                            <!-- Karyawan Langsung (BTKL) -->
                            <div class="btkl-group-card">
                                <div class="btkl-group-header">
                                    <div class="btkl-badge-prod">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        <span id="lblLangsungGroup">Karyawan Langsung Manual</span>
                                    </div>
                                </div>
                                <div class="btkl-group-inputs">
                                    <div class="btkl-input-item">
                                        <label for="tk_langsung_org" class="btkl-sublabel">Jumlah Karyawan <span class="required">*</span></label>
                                        <div class="input-suffix-wrap">
                                            <input type="number" step="1" min="0" name="tk_langsung_org" id="tk_langsung_org" class="hpp-input" value="0" required oninput="calcLaborSplit()">
                                            <span class="suffix-org">Orang</span>
                                        </div>
                                    </div>
                                    <div class="btkl-input-item">
                                        <label for="tk_langsung_nilai" class="btkl-sublabel">Total Upah Tenaga Kerja (Rp) <span class="required">*</span></label>
                                        <div class="input-rp-wrap">
                                            <span class="prefix-rp">Rp</span>
                                            <input type="number" step="any" min="0" name="tk_langsung_nilai" id="tk_langsung_nilai" class="hpp-input" value="0" required oninput="calcLaborSplit()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <small class="helper-text" id="helpTkSync" style="margin-top: 0.35rem; display: block;">Ditarik langsung dari kehadiran &amp; tarif upah riil Tenaga Kerja Langsung di Modul Absensi.</small>

                        <!-- Total BTKL Banner -->
                        <div class="labor-result-banner">
                            <div class="labor-result-info">
                                <span class="labor-res-label">TOTAL BIAYA TENAGA KERJA (BTKL)</span>
                                <span class="labor-res-formula" id="lblTkFormula">0 Orang Produksi</span>
                            </div>
                            <div class="banner-total-value labor-value-text">
                                <span class="banner-val-currency">Rp</span>
                                <span class="banner-val-amount" id="disp_tk_total_nilai">0</span>
                                <input type="hidden" name="tk_total_nilai" id="tk_total_nilai" value="0">
                            </div>
                        </div>

                        <!-- Compatibility inputs for database schema -->
                        <input type="hidden" name="tk_jumlah_org" id="tk_jumlah_org" value="0">
                        <input type="hidden" name="tk_tarif_per_org" id="tk_tarif_per_org" value="0">
                        <input type="hidden" name="tk_tidak_langsung_org" id="tk_tidak_langsung_org" value="0">
                        <input type="hidden" name="tk_tidak_langsung_nilai" id="tk_tidak_langsung_nilai" value="0">

                        <div class="labor-notes-box">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Khusus tenaga kerja langsung pengolahan produksi keripik. Tenaga kerja packaging dan operasional tidak langsung dicatat terpisah.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 3: RINGKASAN BIAYA MANUFAKTUR & ALOKASI HPP PER PRODUK JADI --}}
        <div class="hpp-card hpp-card-highlight">
            <div class="hpp-card-header">
                <div class="hpp-step-badge">3</div>
                <div>
                    <h2 class="hpp-card-title">Ringkasan Biaya Manufaktur &amp; Alokasi HPP per Jenis Produk Jadi</h2>
                    <p class="hpp-card-desc">Alokasikan total biaya produksi ke masing-masing varian olahan WIP (Keripik Singkong Utama, Berko, dll) dan finalisasi lembar HPP.</p>
                </div>
            </div>
            <div class="hpp-card-body">
                {{-- KPI SUMMARY BOXES --}}
                <div class="hpp-stat-summary-grid">
                    <div class="hpp-stat-box">
                        <span class="stat-label">Total Biaya Bahan</span>
                        <span class="stat-value" id="statBahanNilai">Rp 0</span>
                        <span class="stat-meta">Singkong + Minyak + Bumbu + Kemasan</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">Total Energi Gas CNG</span>
                        <span class="stat-value" id="statCngNilai">Rp 0</span>
                        <span class="stat-meta" id="statCngMeta">0 MMBTU (JISDOR)</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">Total Upah Tenaga Kerja</span>
                        <span class="stat-value" id="statTkNilai">Rp 0</span>
                        <span class="stat-meta" id="statTkMeta">0 Orang Shift</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">TOTAL BIAYA MANUFAKTUR</span>
                        <span class="stat-value" id="statTotalBiaya">Rp 0</span>
                        <span class="stat-meta">Akumulasi Seluruh Biaya Shift</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">TOTAL OUTPUT WIP (KG)</span>
                        <span class="stat-value" id="statTotalWipKg">0.00 kg</span>
                        <span class="stat-meta" id="statRendemenMeta">Rendemen: 0.00%</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">HPP RATA-RATA RIIL (RP/KG)</span>
                        <span class="stat-value" id="statHppRataRata">Rp 0 / kg</span>
                        <span class="stat-meta">Total Biaya / Total Output WIP</span>
                    </div>
                </div>

                {{-- KONTROL PILIHAN METODE PEMBAGIAN HPP --}}
                <div class="alokasi-control-box">
                    <div class="alokasi-info-strip">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Petunjuk Alokasi:</strong> Nilai HPP pada tabel di bawah sudah terisi seimbang secara otomatis. Anda dapat langsung menyimpan, atau gunakan alat bantu di bawah ini jika ingin mengubah strategi pembagian biaya:</span>
                    </div>

                    <div class="alokasi-actions-grid">
                        <div class="alloc-quick-actions">
                            <button type="button" class="btn-alloc-mode btn-mode-rata" onclick="resetModeBagiRata()" title="Bagi rata total biaya ke seluruh produk berdasarkan perbandingan berat fisik">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                                <span>Bagi Rata Sesuai Berat</span>
                            </button>
                            <button type="button" class="btn-alloc-mode btn-mode-sisa" onclick="applyMetodePakaiSisa()" title="Pertahankan HPP produk KW 2 (termasuk jika diisi Rp 0), lalu bebankan sisa seluruh biaya ke Produk Utama agar seimbang">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Hitung Sisa ke Utama</span>
                            </button>
                        </div>

                        <div class="alloc-multiplier-action">
                            <span class="multiplier-formula-lbl">
                                Rasio HPP Produk Utama =
                            </span>
                            <input type="number" step="0.1" min="1" id="factorMultiplier" value="2.5" class="input-factor" title="Faktor rasio pengali HPP produk utama terhadap produk sampingan">
                            <span class="multiplier-formula-lbl">× dari harga</span>
                            <select id="targetMultiplierRow" class="select-target-baris" title="Pilih baris produk pembanding (acuan rasio)">
                                <option value="all_kw2">Semua KW 2 (Berko, Lengket, Remahan)</option>
                                <option value="berko_only">KS BERKO</option>
                                <option value="lengket_only">KS BERKO LENGKET</option>
                                <option value="remahan_only">KS REMAHAN</option>
                            </select>
                            <button type="button" class="btn-apply-diferensiasi" onclick="applyRasioProdukUtama()" title="Terapkan rasio perbandingan ke produk utama dan produk sampingan agar seimbang">
                                <span>Terapkan</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- TABEL ALOKASI HPP PER BARANG JADI --}}
                <div class="hpp-table-wrapper">
                    <table class="hpp-allocation-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Nama Produk WIP &amp; Kode</th>
                                <th>Grade / Kategori</th>
                                <th>Nomor Batch</th>
                                <th style="text-align: right;">Berat Fisik (kg)</th>
                                <th style="text-align: right; width: 220px;">HPP Satuan (Rp/kg)</th>
                                <th style="text-align: right; width: 190px;">Total Nilai Alokasi (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="allocationTableBody">
                            <tr>
                                <td colspan="7" class="table-empty-row">
                                    <div class="empty-info-msg">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Pilih dokumen produksi fisik pada Card 1 untuk memuat rincian alokasi per varian produk WIP.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="hpp-table-foot">
                                <td colspan="4" style="text-align: right; font-weight: 700;">TOTAL ALOKASI OUTPUT:</td>
                                <td style="text-align: right; font-weight: 700;" id="footTotalKg">0.00 kg</td>
                                <td style="text-align: right; font-size: 0.75rem; color: #64748b;" id="footStatusMatch">Balance</td>
                                <td style="text-align: right; font-weight: 800; font-size: 1rem; color: #0f172a;" id="footTotalNilai">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- BALANCE CHECKER NOTIFICATION --}}
                <div id="balanceAlertBox" class="balance-status-alert alert-matched" style="display: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="balanceAlertText">Total nilai alokasi output sesuai dengan Total Biaya Produksi shift ini.</span>
                </div>

                {{-- CATATAN TAMBAHAN FINANCE & TOMBOL FINALISASI (TERINTEGRASI DI CARD 3) --}}
                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1.5px dashed #cbd5e1;">
                    <div class="form-group-hpp">
                        <label for="catatan_hpp" class="hpp-label">Catatan Tambahan / Dasar Penetapan Biaya</label>
                        <textarea name="catatan_hpp" id="catatan_hpp" rows="2" class="hpp-textarea" placeholder="Contoh: Menggunakan kurs JISDOR penutupan BI hari ini. Alokasi HPP disesuaikan proporsi mutu hasil olahan."></textarea>
                    </div>

                    <div class="submit-action-row">
                        <a href="{{ route('produksi.rekap') }}" class="btn-cancel-hpp">Batal</a>
                        <button type="submit" class="btn-save-hpp" id="btnSubmitHpp">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan &amp; Tetapkan Lembar HPP Produksi</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- OVERLAY LOADING MODAL INTERAKTIF --}}
    <div id="hppLoadingOverlay" class="hpp-loading-backdrop" style="display: none;">
        <div class="hpp-loading-card">
            <div class="hpp-spinner-container">
                <svg class="spin-svg-lg" width="40" height="40" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="#e2e8f0" stroke-width="3"></circle>
                    <path fill="#059669" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="hpp-load-title" id="overlayLoadingTitle">Sinkronisasi Data Produksi</h3>
            <p class="hpp-load-msg" id="overlayLoadingMsg">Sedang menarik rincian pemakaian bahan gudang (BPPB), hasil timbang olahan WIP, energi gas, dan absensi...</p>
            <div class="hpp-load-progress-bar">
                <div class="hpp-load-progress-fill"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            produksiDataUrl: "{{ url('/produksi/hpp/produksi-data') }}",
            jisdorUrl: "{{ route('produksi.kurs-jisdor') }}",
            syncKaryawanUrl: "{{ route('produksi.sync-karyawan') }}",
            selectedProduksiId: {{ $selectedProduksiId ? (int)$selectedProduksiId : 'null' }},
            selectedProduksiTgl: "{{ $prodDateStr ?? '' }}",
            defaultCngTarifUsd: {{ $defaultCngTarifUsd }},
            defaultCngKursJisdor: {{ $defaultCngKursJisdor }},
            defaultCngTarif: {{ $defaultCngTarif }}
        };
    </script>
    <script src="{{ asset('js/produksi/hpp/hpp-create.js') }}?v={{ filemtime(public_path('js/produksi/hpp/hpp-create.js')) }}"></script>
@endpush
