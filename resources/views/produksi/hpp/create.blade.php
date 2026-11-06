@extends('layouts.app')

@section('title', 'Kalkulasi & Lembar HPP Produksi (Tahap 3)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/hpp/hpp-create.css') }}">
@endpush

@section('content')
<div class="hpp-container">
    {{-- HEADER BREADCRUMB & JUDUL --}}
    <div class="hpp-header-area">
        <div class="hpp-breadcrumb">
            <a href="{{ route('produksi.index') }}">Produksi</a>
            <span class="separator">/</span>
            <a href="{{ route('produksi.rekap') }}">Buku Rekap HPP</a>
            <span class="separator">/</span>
            <span class="current">Kalkulasi Lembar HPP (Finance)</span>
        </div>
        <div class="hpp-header-title-row">
            <div>
                <h1 class="hpp-title">Kalkulasi &amp; Penetapan Lembar HPP Produksi</h1>
                <p class="hpp-subtitle">
                    Penetapan nilai rupiah bahan baku, utilitas energi gas CNG (kurs JISDOR BI), biaya tenaga kerja, dan alokasi HPP per jenis produk WIP.
                </p>
            </div>
            <div class="hpp-header-actions">
                <a href="{{ route('produksi.rekap') }}" class="btn-hpp-secondary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Buku Rekap HPP</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert-danger-hpp">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form id="formHppProduksi" action="{{ route('produksi.hpp.store') }}" method="POST">
        @csrf

        {{-- CARD 1: SELEKSI DOKUMEN PRODUKSI FISIK --}}
        <div class="hpp-card">
            <div class="hpp-card-header">
                <div class="hpp-step-badge">1</div>
                <div>
                    <h2 class="hpp-card-title">Pilih Dokumen Produksi Fisik</h2>
                    <p class="hpp-card-desc">Pilih nomor SPK produksi yang telah ditimbang dan diinput oleh operator produksi.</p>
                </div>
            </div>
            <div class="hpp-card-body">
                <div class="form-group-hpp">
                    <label for="produksi_id" class="hpp-label">Dokumen Produksi Tersedia <span class="required">*</span></label>
                    <select name="produksi_id" id="produksi_id" class="hpp-select" required onchange="onProduksiSelectChange(this.value)">
                        <option value="">-- Pilih Dokumen Produksi Fisik --</option>
                        @foreach($produksiList as $p)
                            @php
                                $totalKg = (float) $p->outputs->sum('qty_kg');
                                if ($totalKg <= 0) $totalKg = (float) $p->total_wip_qty;
                                $isCalculated = (float) $p->total_biaya_produksi > 0;
                            @endphp
                            <option value="{{ $p->produksi_id }}" 
                                    data-no="{{ $p->produksi_no }}"
                                    {{ (string) $p->produksi_id === (string) old('produksi_id', $selectedProduksiId) ? 'selected' : '' }}>
                                {{ $p->produksi_no }} | Tgl: {{ \Carbon\Carbon::parse($p->produksi_tgl)->format('d/m/Y') }} | Shift {{ $p->shift_cd }} | WIP: {{ number_format($totalKg, 2, ',', '.') }} kg | {{ $isCalculated ? 'HPP: Rp '.number_format($p->hpp_per_kg, 2, ',', '.') : '[HPP Belum Ditetapkan]' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PREVIEW DATA FISIK (READ ONLY) --}}
                <div id="previewFisikSection" class="preview-fisik-box" style="display: none;">
                    <div class="preview-fisik-header">
                        <span class="preview-fisik-title">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Rincian Hasil Produksi Fisik (SPK: <strong id="pvProduksiNo">-</strong>)
                        </span>
                        <span id="pvStatusHppBadge" class="badge-status-calc">Memuat...</span>
                    </div>

                    <div class="preview-grid">
                        <div class="pv-item">
                            <span class="pv-label">Tanggal Produksi</span>
                            <span class="pv-val" id="pvTgl">-</span>
                        </div>
                        <div class="pv-item">
                            <span class="pv-label">Shift &amp; Lini</span>
                            <span class="pv-val" id="pvShiftLini">-</span>
                        </div>
                        <div class="pv-item">
                            <span class="pv-label">Gudang Produksi</span>
                            <span class="pv-val" id="pvGudang">-</span>
                        </div>
                        <div class="pv-item">
                            <span class="pv-label">BPPB Pemakaian Gudang</span>
                            <span class="pv-val" id="pvBppb">-</span>
                        </div>
                        <div class="pv-item">
                            <span class="pv-label">Singkong Segar Masuk</span>
                            <span class="pv-val highlight-val" id="pvSingkongQty">0 kg</span>
                        </div>
                        <div class="pv-item">
                            <span class="pv-label">Hasil Fisik WIP Bersih</span>
                            <span class="pv-val highlight-val" id="pvWipQty">0 kg</span>
                        </div>
                        <div class="pv-item">
                            <span class="pv-label">Rendemen Aktual</span>
                            <span class="pv-val highlight-rendemen" id="pvRendemen">0.00 %</span>
                        </div>
                        <div class="pv-item">
                            <span class="pv-label">Nomor Batch WIP</span>
                            <span class="pv-val" id="pvBatch">-</span>
                        </div>
                    </div>

                    {{-- TABEL OUTPUT FISIK ASLI --}}
                    <div class="pv-table-wrap">
                        <table class="pv-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang / Varian</th>
                                    <th>Kategori &amp; Grade</th>
                                    <th>Batch Output</th>
                                    <th style="text-align: right;">Kuantitas Satuan</th>
                                    <th style="text-align: right;">Total Berat Fisik</th>
                                </tr>
                            </thead>
                            <tbody id="pvOutputsTableBody">
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #64748b; padding: 1rem;">Memuat daftar output fisik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM COSTING FINANCE (DITAMPILKAN JIKA DOKUMEN DIPILIH) --}}
        <div id="financeCostingContainer" style="display: none;">

            {{-- CARD 2: BIAYA BAHAN BAKU & KEMASAN --}}
            <div class="hpp-card">
                <div class="hpp-card-header">
                    <div class="hpp-step-badge">2</div>
                    <div>
                        <h2 class="hpp-card-title">Biaya Bahan Baku &amp; Bahan Kemasan (Rp)</h2>
                        <p class="hpp-card-desc">Nilai rupiah bahan baku ditarik otomatis dari BPPB gudang dan dapat disesuaikan jika terdapat nota koreksi timbangan/harga.</p>
                    </div>
                </div>
                <div class="hpp-card-body">
                    <div class="cost-grid-row">
                        <div class="cost-group">
                            <div class="cost-group-title">
                                <span>1. Singkong Segar</span>
                                <span class="cost-qty-badge" id="lblSingkongQty">0 kg</span>
                            </div>
                            <div class="input-rp-wrap">
                                <span class="prefix-rp">Rp</span>
                                <input type="number" step="any" min="0" name="singkong_nilai" id="singkong_nilai" class="hpp-input-rp" value="0" oninput="calcAllHpp()">
                            </div>
                            <small class="helper-text">Nilai pembelian singkong segar per SPK ini.</small>
                        </div>

                        <div class="cost-group">
                            <div class="cost-group-title">
                                <span>2. Minyak Goreng (Sawit &amp; Kelapa)</span>
                                <span class="cost-qty-badge" id="lblMinyakQty">0 kg</span>
                            </div>
                            <div class="input-rp-wrap">
                                <span class="prefix-rp">Rp</span>
                                <input type="number" step="any" min="0" name="minyak_nilai" id="minyak_nilai" class="hpp-input-rp" value="0" oninput="calcAllHpp()">
                            </div>
                            <small class="helper-text">Total nilai minyak terserap penggorengan.</small>
                        </div>

                        <div class="cost-group">
                            <div class="cost-group-title">
                                <span>3. Bumbu &amp; Flavour Tabur</span>
                                <span class="cost-qty-badge" id="lblBumbuQty">0 kg</span>
                            </div>
                            <div class="input-rp-wrap">
                                <span class="prefix-rp">Rp</span>
                                <input type="number" step="any" min="0" name="bumbu_nilai" id="bumbu_nilai" class="hpp-input-rp" value="0" oninput="calcAllHpp()">
                            </div>
                            <small class="helper-text">Total biaya bumbu tabur per batch ini.</small>
                        </div>
                    </div>

                    {{-- SUBSECTION KEMASAN --}}
                    <div class="kemasan-subcard">
                        <div class="kemasan-header-toggle" onclick="toggleKemasanSection()">
                            <span class="kemasan-title">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Rincian Biaya Kemasan Penunjang (Opsional)
                            </span>
                            <span class="badge-total-kemasan" id="badgeTotalKemasan">Rp 0</span>
                        </div>
                        <div id="kemasanInputsBody" class="kemasan-grid" style="display: none;">
                            <div class="form-group-hpp">
                                <label class="hpp-label-sm">Karton Baru (Rp)</label>
                                <input type="number" step="any" min="0" name="karton_baru_nilai" id="karton_baru_nilai" class="hpp-input-sm" value="0" oninput="calcAllHpp()">
                            </div>
                            <div class="form-group-hpp">
                                <label class="hpp-label-sm">Karton Bekas (Rp)</label>
                                <input type="number" step="any" min="0" name="karton_bekas_nilai" id="karton_bekas_nilai" class="hpp-input-sm" value="0" oninput="calcAllHpp()">
                            </div>
                            <div class="form-group-hpp">
                                <label class="hpp-label-sm">Plastik HD (Rp)</label>
                                <input type="number" step="any" min="0" name="plastik_hd_nilai" id="plastik_hd_nilai" class="hpp-input-sm" value="0" oninput="calcAllHpp()">
                            </div>
                            <div class="form-group-hpp">
                                <label class="hpp-label-sm">Lakban Besar (Rp)</label>
                                <input type="number" step="any" min="0" name="lakban_besar_nilai" id="lakban_besar_nilai" class="hpp-input-sm" value="0" oninput="calcAllHpp()">
                            </div>
                            <div class="form-group-hpp">
                                <label class="hpp-label-sm">Lakban Kecil (Rp)</label>
                                <input type="number" step="any" min="0" name="lakban_kecil_nilai" id="lakban_kecil_nilai" class="hpp-input-sm" value="0" oninput="calcAllHpp()">
                            </div>
                            <div class="form-group-hpp">
                                <label class="hpp-label-sm">Tali Rafia (Rp)</label>
                                <input type="number" step="any" min="0" name="tali_rafia_nilai" id="tali_rafia_nilai" class="hpp-input-sm" value="0" oninput="calcAllHpp()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3: UTILITAS ENERGI GAS ALAM (CNG) & KURS JISDOR --}}
            <div class="hpp-card">
                <div class="hpp-card-header">
                    <div class="hpp-step-badge">3</div>
                    <div>
                        <h2 class="hpp-card-title">Biaya Utilitas Energi Gas Alam (CNG)</h2>
                        <p class="hpp-card-desc">Kalkulasi biaya energi boiler &amp; penggorengan berdasarkan kontrak USD dan kurs JISDOR Bank Indonesia.</p>
                    </div>
                </div>
                <div class="hpp-card-body">
                    <div class="cng-calc-grid">
                        <div class="form-group-hpp">
                            <label for="cng_mmbtu" class="hpp-label">Meteran Fisik Gas CNG (MMBTU) <span class="required">*</span></label>
                            <input type="number" step="any" min="0" name="cng_mmbtu" id="cng_mmbtu" class="hpp-input" value="0" required oninput="calcCngFromMmbtu()">
                            <small class="helper-text">Ditarik dari pencatatan fisik meteran shift pabrik.</small>
                        </div>

                        <div class="form-group-hpp">
                            <label for="cng_tarif_usd" class="hpp-label">Tarif Kontrak CNG (USD / MMBTU)</label>
                            <div class="input-usd-wrap">
                                <span class="prefix-usd">$</span>
                                <input type="number" step="0.01" min="0" name="cng_tarif_usd" id="cng_tarif_usd" class="hpp-input" value="{{ $defaultCngTarifUsd }}" oninput="calcCngFromTarif()">
                            </div>
                            <small class="helper-text">Standar kontrak PGN / vendor: $14.50 / MMBTU.</small>
                        </div>

                        <div class="form-group-hpp">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label for="cng_kurs_jisdor" class="hpp-label">Kurs JISDOR BI (Rp / USD)</label>
                                <button type="button" class="btn-sync-kurs" onclick="fetchLiveJisdor()">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Sync JISDOR</span>
                                </button>
                            </div>
                            <div class="input-rp-wrap">
                                <span class="prefix-rp">Rp</span>
                                <input type="number" step="any" min="0" name="cng_kurs_jisdor" id="cng_kurs_jisdor" class="hpp-input" value="{{ $defaultCngKursJisdor }}" oninput="calcCngFromTarif()">
                            </div>
                            <small class="helper-text">
                                <a href="https://www.bi.go.id/id/statistik/informasi-kurs/jisdor/default.aspx" target="_blank" rel="noopener" style="color: #0284c7; text-decoration: underline;">Cek Kurs JISDOR Bank Indonesia ↗</a>
                            </small>
                        </div>

                        <div class="form-group-hpp">
                            <label for="cng_tarif" class="hpp-label">Tarif Hasil Konversi (Rp / MMBTU)</label>
                            <div class="input-rp-wrap">
                                <span class="prefix-rp">Rp</span>
                                <input type="number" step="any" min="0" name="cng_tarif" id="cng_tarif" class="hpp-input" value="{{ $defaultCngTarif }}" oninput="calcCngFromTarif()">
                            </div>
                            <small class="helper-text">Formula: Tarif USD × Kurs JISDOR BI.</small>
                        </div>
                    </div>

                    <div class="cng-result-banner">
                        <div class="cng-result-info">
                            <span class="cng-res-label">Total Biaya Pemakaian Gas Alam (CNG):</span>
                            <span class="cng-res-formula" id="lblCngFormula">0 MMBTU × Rp 0 / MMBTU</span>
                        </div>
                        <div class="input-rp-wrap" style="max-width: 280px;">
                            <span class="prefix-rp" style="background: #e0f2fe; color: #0369a1;">Rp</span>
                            <input type="number" step="any" min="0" name="cng_nilai" id="cng_nilai" class="hpp-input-result" value="0" required oninput="calcAllHpp()">
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 4: BIAYA TENAGA KERJA LANGSUNG --}}
            <div class="hpp-card">
                <div class="hpp-card-header">
                    <div class="hpp-step-badge">4</div>
                    <div>
                        <h2 class="hpp-card-title">Biaya Tenaga Kerja Langsung</h2>
                        <p class="hpp-card-desc">Beban upah tenaga kerja shift produksi penggorengan &amp; pengolahan singkong.</p>
                    </div>
                </div>
                <div class="hpp-card-body">
                    <div class="tk-grid-row">
                        <div class="form-group-hpp">
                            <label for="tk_jumlah_org" class="hpp-label">Jumlah Tenaga Kerja Hadir (Orang) <span class="required">*</span></label>
                            <input type="number" min="0" name="tk_jumlah_org" id="tk_jumlah_org" class="hpp-input" value="0" required oninput="calcTkFromOrg()">
                            <small class="helper-text">Sesuai catatan presensi fisik lini shift ini.</small>
                        </div>

                        <div class="form-group-hpp">
                            <label for="tk_total_nilai" class="hpp-label">Total Beban Upah Shift Ini (Rp) <span class="required">*</span></label>
                            <div class="input-rp-wrap">
                                <span class="prefix-rp">Rp</span>
                                <input type="number" step="any" min="0" name="tk_total_nilai" id="tk_total_nilai" class="hpp-input-rp" value="0" required oninput="calcAllHpp()">
                            </div>
                            <small class="helper-text">Total nominal upah tenaga kerja shift.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 5: RINGKASAN BIAYA & ALOKASI JOINT-COST HPP PER PRODUK --}}
            <div class="hpp-card hpp-card-highlight">
                <div class="hpp-card-header">
                    <div class="hpp-step-badge">5</div>
                    <div>
                        <h2 class="hpp-card-title">Ringkasan Biaya Produksi &amp; Alokasi HPP per Output</h2>
                        <p class="hpp-card-desc">Alokasikan biaya bersama (joint-cost) ke masing-masing varian hasil fisik (KW1 vs KW2 / BS / Reject).</p>
                    </div>
                </div>
                <div class="hpp-card-body">
                    {{-- EXECUTIVE STAT CARDS --}}
                    <div class="hpp-stat-summary-grid">
                        <div class="hpp-stat-box">
                            <span class="stat-label">Total Biaya Produksi</span>
                            <span class="stat-value text-emerald" id="lblTotalBiayaProduksi">Rp 0</span>
                            <span class="stat-meta">Bahan + Gas + TK + Kemasan</span>
                        </div>
                        <div class="hpp-stat-box">
                            <span class="stat-label">Total Berat Output Fisik</span>
                            <span class="stat-value text-indigo" id="lblTotalOutputKg">0.00 kg</span>
                            <span class="stat-meta">Seluruh varian yang dihasilkan</span>
                        </div>
                        <div class="hpp-stat-box">
                            <span class="stat-label">HPP Riil Rata-Rata</span>
                            <span class="stat-value text-amber" id="lblHppRataRata">Rp 0.00 / kg</span>
                            <span class="stat-meta">Total Biaya ÷ Total Output kg</span>
                        </div>
                    </div>

                    {{-- TOMBOL METODE ALOKASI --}}
                    <div class="alokasi-toolbar">
                        <span class="toolbar-title">Metode Alokasi Biaya Bersama:</span>
                        <div class="toolbar-buttons">
                            <button type="button" class="btn-alloc-mode btn-mode-rasio" onclick="applyStandarRasio()">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Alokasi Standar Rasio Mutu (Grade KW1 : KW2 = 2.5 : 1)</span>
                            </button>
                            <button type="button" class="btn-alloc-mode btn-mode-rata" onclick="resetModeBagiRata()">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                                <span>Bagi Rata Sesuai Berat (Weighted Average)</span>
                            </button>
                        </div>
                    </div>

                    {{-- TABEL ALOKASI HPP PER BARANG --}}
                    <div class="hpp-table-wrapper">
                        <table class="hpp-allocation-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Produk WIP</th>
                                    <th>Grade / Kategori</th>
                                    <th>Nomor Batch</th>
                                    <th style="text-align: right;">Berat Fisik (kg)</th>
                                    <th style="text-align: right; width: 190px;">HPP Satuan (Rp/kg)</th>
                                    <th style="text-align: right; width: 180px;">Total Nilai Alokasi (Rp)</th>
                                </tr>
                            </thead>
                            <tbody id="allocationTableBody">
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #64748b; padding: 1.5rem;">Pilih dokumen produksi fisik di Card 1 terlebih dahulu.</td>
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
                </div>
            </div>

            {{-- CARD 6: CATATAN FINANCE & SUBMIT --}}
            <div class="hpp-card">
                <div class="hpp-card-body">
                    <div class="form-group-hpp">
                        <label for="catatan_hpp" class="hpp-label">Catatan Tambahan Finance / Dasar Penetapan Biaya</label>
                        <textarea name="catatan_hpp" id="catatan_hpp" rows="2" class="hpp-textarea" placeholder="Contoh: Menggunakan kurs JISDOR penutupan BI tgl 05/11/2026. Biaya gas disesuaikan meteran flow boiler."></textarea>
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
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            produksiDataUrl: "{{ url('/produksi/hpp/produksi-data') }}",
            jisdorUrl: "{{ route('produksi.kurs-jisdor') }}",
            selectedProduksiId: {{ $selectedProduksiId ? (int)$selectedProduksiId : 'null' }},
            defaultCngTarifUsd: {{ $defaultCngTarifUsd }},
            defaultCngKursJisdor: {{ $defaultCngKursJisdor }},
            defaultCngTarif: {{ $defaultCngTarif }}
        };
    </script>
    <script src="{{ asset('js/produksi/hpp/hpp-create.js') }}"></script>
@endpush

