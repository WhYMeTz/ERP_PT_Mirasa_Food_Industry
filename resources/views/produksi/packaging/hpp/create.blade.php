@extends('layouts.app')

@section('title', 'Kalkulasi & Lembar HPP Pengemasan (Tahap 2) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-hpp-create.css') }}?v={{ filemtime(public_path('css/produksi/packaging/packaging-hpp-create.css')) }}">
@endpush

@section('content')
<div class="hpp-container">
    {{-- Header & Breadcrumb Navigasi --}}
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('produksi.packaging.rekap') }}" 
           style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem; font-weight: 600; transition: color 0.15s ease;"
           onmouseover="this.style.color='#0f172a'"
           onmouseout="this.style.color='#64748b'"
           title="Kembali ke Buku Rekapitulasi HPP Kemas">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Buku Rekapitulasi HPP
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Kalkulasi &amp; Penetapan Lembar HPP Pengemasan (FG)
                </h1>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Penetapan nilai HPP bahan keripik curah WIP, biaya kemasan pabrik, dan pembebanan upah tenaga kerja kemas (BTKL).
                </p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert-danger-hpp" style="margin-bottom: 1.25rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form id="formHppPackaging" action="{{ route('produksi.packaging.hpp.store') }}" method="POST">
        @csrf

        {{-- CARD 1: DOKUMEN CATATAN PENGEMASAN & KONSUMSI BAHAN (WIP & KEMASAN) --}}
        <div class="hpp-card">
            <div class="hpp-card-header">
                <div class="hpp-step-badge">1</div>
                <div>
                    <h2 class="hpp-card-title">Dokumen Catatan Pengemasan &amp; Konsumsi Bahan Fisik</h2>
                    <p class="hpp-card-desc">Pilih nomor dokumen pengemasan fisik di lantai gudang untuk memuat rincian keripik curah WIP dan konsumsi bahan kemasan.</p>
                </div>
            </div>
            <div class="hpp-card-body">
                <div class="form-group-hpp">
                    <label for="pack_id" class="hpp-label">Nomor Dokumen Pengemasan Fisik Tersedia <span class="required">*</span></label>
                    <select name="pack_id" id="pack_id" class="hpp-select" required onchange="onPackagingSelectChange(this.value)">
                        <option value="">-- Pilih Dokumen Pengemasan Fisik --</option>
                        @forelse($packagingList as $p)
                            <option value="{{ $p->pack_id }}" 
                                    {{ (string) $p->pack_id === (string) old('pack_id', $selectedPackId) ? 'selected' : '' }}>
                                [{{ $p->pack_no }}] {{ \Carbon\Carbon::parse($p->pack_tgl)->format('d/m/Y') }} &mdash; {{ $p->barangJadi?->barang_nm ?? 'Produk Jadi' }} &bull; Shift {{ $p->shift ?? 'A' }} ({{ number_format((float)$p->hasil_karton_qty, 0, ',', '.') }} Karton / {{ number_format((float)$p->hasil_pcs_qty, 0, ',', '.') }} Pcs)
                            </option>
                        @empty
                            <option value="" disabled>(Semua dokumen pengemasan telah dikalkulasi &amp; berstatus Final)</option>
                        @endforelse
                    </select>

                    @if($packagingList->isEmpty())
                        <div class="spk-empty-alert" style="margin-top: 0.75rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Seluruh dokumen pengemasan pada sistem saat ini telah difinalkan HPP-nya. Untuk menghitung HPP baru, pastikan tim lapangan telah mengisi <a href="{{ route('produksi.packaging.create') }}" style="color: #0284c7; font-weight: 700;">Catatan Pengemasan Fisik</a> terlebih dahulu.</span>
                        </div>
                    @endif
                </div>

                {{-- Parameter Box Hasil Pengemasan Terpilih --}}
                <div id="packParamBox" style="display: none; margin-top: 1rem;">
                    <div class="spk-preview-box">
                        <div class="spk-param-item">
                            <span class="spk-param-label">Nomor Dokumen</span>
                            <span class="spk-param-value" id="pvPackNo" style="font-family: monospace; color: #0284c7;">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Tanggal Pelaksanaan</span>
                            <span class="spk-param-value" id="pvPackTgl">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Target Produk Jadi (FG)</span>
                            <span class="spk-param-value" id="pvBarangJadi" style="font-weight: 800; color: #0f172a;">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Gudang Penyimpanan FG</span>
                            <span class="spk-param-value" id="pvGudang">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">No. Batch FG</span>
                            <span class="spk-param-value font-mono" id="pvBatchNo" style="color: #0369a1;">-</span>
                        </div>
                        <div class="spk-param-item">
                            <span class="spk-param-label">Hasil Kemas Utama</span>
                            <span class="spk-param-value" id="pvHasilKarton" style="color: #059669; font-weight: 800;">0 Karton</span>
                        </div>
                    </div>
                </div>

                <div class="card-inner-divider" style="margin: 1.5rem 0 1.25rem 0; border-top: 1.5px dashed #e2e8f0;"></div>

                {{-- SUB-SECTION A: BAHAN KERIPIK CURAH WIP --}}
                <div style="margin-bottom: 1.5rem;">
                    {{-- Banner Dokumen SPK Dapur --}}
                    <div id="spkMasakBanner" class="pck-wip-callout" style="display: none;">
                        <div class="pck-wip-callout-title">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Dokumen SPK Masak Dapur Terkait: <strong id="lblSpkNo" style="font-family: monospace;">-</strong></span>
                        </div>
                        <div class="pck-wip-callout-meta">
                            <span>Tanggal Masak: <strong id="lblSpkTgl">-</strong></span>
                            <span>&bull;</span>
                            <span>HPP Curah WIP: <strong id="lblHppWipStatus">-</strong></span>
                        </div>
                    </div>

                    <div class="table-bppb-toolbar">
                        <div class="table-bppb-title">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>1. Bahan Keripik Curah (WIP Olahan Dapur)</span>
                        </div>
                    </div>

                    <div class="table-pemakaian-wrap">
                        <table class="table-pemakaian">
                            <thead>
                                <tr>
                                    <th style="width: 5%; text-align: center;">No</th>
                                    <th style="width: 35%; text-align: left;">Varian Keripik Curah (WIP)</th>
                                    <th style="width: 15%; text-align: right;">Qty Diambil</th>
                                    <th style="width: 15%; text-align: right;">HPP Curah (Rp/kg)</th>
                                    <th style="width: 15%; text-align: right;">Subtotal Nilai (Rp)</th>
                                    <th style="width: 15%; text-align: center;">Status Biaya</th>
                                </tr>
                            </thead>
                            <tbody id="wipTableBody">
                                <tr>
                                    <td colspan="6" style="padding: 2.5rem 1.5rem; text-align: center; background: #ffffff;">
                                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; max-width: 420px; margin: 0 auto; gap: 0.5rem;">
                                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; border: 1px solid #e2e8f0;">
                                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                            </div>
                                            <strong style="font-size: 0.875rem; color: #1e293b;">Menunggu Pemilihan Dokumen Pengemasan</strong>
                                            <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4;">
                                                Pilih nomor dokumen fisik pada bagian atas untuk memuat rincian keripik curah dan kalkulasi HPP dapur secara otomatis.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="wipTableFoot" style="display: none;">
                                <tr class="pemakaian-tfoot-row">
                                    <td colspan="4" style="text-align: right; font-weight: 700;">TOTAL BIAYA KERIPIK CURAH (WIP):</td>
                                    <td style="text-align: right; font-weight: 800; color: #0f172a;" id="lblSubtotalWip">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <input type="hidden" name="total_biaya_wip" id="total_biaya_wip" value="0">
                </div>

                <div class="card-inner-divider" style="margin: 1.5rem 0 1.25rem 0; border-top: 1.5px dashed #e2e8f0;"></div>

                {{-- SUB-SECTION B: BAHAN KEMASAN --}}
                <div>
                    <div class="table-bppb-toolbar">
                        <div class="table-bppb-title">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>2. Rincian Pemakaian Bahan Kemasan (Dus, Plastik, Lakban, Label)</span>
                        </div>
                    </div>

                    <div class="table-pemakaian-wrap">
                        <table class="table-pemakaian">
                            <thead>
                                <tr>
                                    <th style="width: 5%; text-align: center;">No</th>
                                    <th style="width: 35%; text-align: left;">Nama Bahan Kemasan &amp; Spesifikasi</th>
                                    <th style="width: 15%; text-align: right;">Qty Terpakai</th>
                                    <th style="width: 15%; text-align: right;">Harga Satuan (Rp)</th>
                                    <th style="width: 15%; text-align: right;">Subtotal Nilai (Rp)</th>
                                    <th style="width: 15%; text-align: center;">Kategori</th>
                                </tr>
                            </thead>
                            <tbody id="kemasanTableBody">
                                <tr>
                                    <td colspan="6" style="padding: 2.5rem 1.5rem; text-align: center; background: #ffffff;">
                                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; max-width: 420px; margin: 0 auto; gap: 0.5rem;">
                                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; border: 1px solid #e2e8f0;">
                                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                            <strong style="font-size: 0.875rem; color: #1e293b;">Menunggu Pemilihan Dokumen Pengemasan</strong>
                                            <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4;">
                                                Pilih dokumen pada bagian atas untuk memuat rincian dus karton, plastik roll, lakban, dan label.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="kemasanTableFoot" style="display: none;">
                                <tr class="pemakaian-tfoot-row">
                                    <td colspan="4" style="text-align: right; font-weight: 700;">TOTAL BIAYA BAHAN KEMASAN:</td>
                                    <td style="text-align: right; font-weight: 800; color: #0f172a;" id="lblSubtotalKemasan">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <input type="hidden" name="total_biaya_kemasan" id="total_biaya_kemasan" value="0">
                </div>
            </div>
        </div>

        {{-- CARD 2: TIM SHIFT KEMAS & BIAYA TENAGA KERJA LANGSUNG (BTKL) --}}
        <div class="hpp-card">
            <div class="hpp-card-header">
                <div class="hpp-step-badge">2</div>
                <div>
                    <h2 class="hpp-card-title">Tim Pelaksanaan &amp; Biaya Tenaga Kerja Langsung (BTKL Kemas)</h2>
                    <p class="hpp-card-desc">Informasi tim regu shift kemas di lantai gudang dan penetapan upah tenaga kerja yang otomatis sinkron dengan Modul Absensi.</p>
                </div>
            </div>
            <div class="hpp-card-body">
                <div class="cng-labor-split-grid">
                    {{-- KOLOM KIRI: INFO TIM SHIFT & MESIN KEMAS --}}
                    <div class="cng-subcard">
                        <div class="subcard-title-bar">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Informasi Operasional Lantai Kemas</span>
                        </div>

                        <div class="spk-param-item" style="margin-top: 0.25rem;">
                            <span class="spk-param-label">Shift Kerja Kemas</span>
                            <span class="spk-param-value" id="dispShiftKemas" style="font-weight: 700; color: #0284c7;">Shift A</span>
                        </div>

                        <div class="spk-param-item">
                            <span class="spk-param-label">Leader / Operator Kemas</span>
                            <span class="spk-param-value" id="dispOperatorKemas" style="font-weight: 700; color: #0f172a;">-</span>
                        </div>

                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-top: 0.5rem;">
                            <div style="font-size: 0.8rem; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.25rem;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Sinkronisasi Presensi Otomatis</span>
                            </div>
                            <p style="font-size: 0.775rem; color: #64748b; line-height: 1.45; margin: 0;">
                                Upah dihitung dari kehadiran karyawan departemen <strong>Langsung Packing</strong> pada tanggal dokumen kemas fisik.
                            </p>
                        </div>
                    </div>

                    {{-- KOLOM KANAN: BIAYA TENAGA KERJA LANGSUNG (BTKL) --}}
                    <div class="labor-subcard">
                        <div class="subcard-title-bar">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>Biaya Tenaga Kerja Langsung (BTKL)</span>
                            </div>
                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                <button type="button" class="btn-sync-tk" onclick="syncLaborFromKaryawan()" title="Tarik kehadiran dan upah riil dari Modul Absensi">
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
                            <div class="btkl-group-card">
                                <div class="btkl-group-header">
                                    <div class="btkl-badge-prod">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span>Karyawan Langsung Packing</span>
                                    </div>
                                </div>
                                <div class="btkl-group-inputs">
                                    <div class="btkl-input-item">
                                        <label for="tk_langsung_org" class="btkl-sublabel">Jumlah Karyawan <span class="required">*</span></label>
                                        <div class="input-suffix-wrap">
                                            <input type="number" step="1" min="0" name="jumlah_personil" id="tk_langsung_org" class="hpp-input" value="0" required oninput="calcLaborPackaging()">
                                            <span class="suffix-org">Orang</span>
                                        </div>
                                    </div>
                                    <div class="btkl-input-item">
                                        <label for="tk_langsung_nilai" class="btkl-sublabel">Total Upah Tenaga Kerja (Rp) <span class="required">*</span></label>
                                        <div class="input-rp-wrap">
                                            <span class="prefix-rp">Rp</span>
                                            <input type="number" step="any" min="0" name="total_biaya_tenaga_kerja" id="tk_langsung_nilai" class="hpp-input" value="0" required oninput="calcLaborPackaging()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <small class="helper-text" id="helpTkSync" style="margin-top: 0.25rem; display: block;">
                            Ditarik langsung dari kehadiran &amp; tarif upah riil Tenaga Kerja Langsung Packing di Modul Absensi.
                        </small>

                        {{-- Total BTKL Kemas Banner --}}
                        <div class="labor-result-banner" style="margin-top: 0.5rem;">
                            <div class="labor-result-info">
                                <span class="labor-res-label">TOTAL BIAYA TENAGA KERJA (BTKL)</span>
                                <span class="labor-res-formula" id="lblTkFormula">0 Orang Langsung Packing</span>
                            </div>
                            <div class="banner-total-value labor-value-text">
                                <span class="banner-val-currency">Rp</span>
                                <span class="banner-val-amount" id="disp_tk_total_nilai">0</span>
                            </div>
                        </div>

                        <div class="labor-notes-box">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Khusus tenaga kerja langsung pengemasan (packing meja sachet &amp; susun karton). Biaya upah olahan dapur dicatat terpisah.</span>
                        </div>
                    </div>
                </div>

                {{-- Hidden input compatibility tanpa FOH/Overhead --}}
                <input type="hidden" name="total_biaya_lain" id="total_biaya_lain" value="0">
            </div>
        </div>

        {{-- CARD 3: RINGKASAN BIAYA MANUFAKTUR & PENETAPAN HPP FINISH GOOD --}}
        <div class="hpp-card hpp-card-highlight">
            <div class="hpp-card-header">
                <div class="hpp-step-badge">3</div>
                <div>
                    <h2 class="hpp-card-title">Ringkasan Biaya Manufaktur &amp; Penetapan HPP Barang Jadi (FG)</h2>
                    <p class="hpp-card-desc">Total biaya akumulasi pengemasan, hasil jadi karton siap distribusi, dan penetapan nilai HPP persediaan barang jadi.</p>
                </div>
            </div>
            <div class="hpp-card-body">
                <div class="hpp-stat-summary-grid">
                    <div class="hpp-stat-box">
                        <span class="stat-label">Total Biaya WIP Curah</span>
                        <span class="stat-value" id="statWipNilai">Rp 0</span>
                        <span class="stat-meta">Konsumsi Bahan Keripik Dapur</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">Total Biaya Kemasan</span>
                        <span class="stat-value" id="statKemasanNilai">Rp 0</span>
                        <span class="stat-meta">Dus, Plastik, Lakban &amp; Label</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">Total Upah Packing</span>
                        <span class="stat-value" id="statTkNilai">Rp 0</span>
                        <span class="stat-meta" id="statTkMeta">0 Orang Kemas</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">TOTAL BIAYA PENGEMASAN</span>
                        <span class="stat-value" id="statTotalBiaya">Rp 0</span>
                        <span class="stat-meta">Akumulasi Biaya Kemas Siap Jual</span>
                    </div>

                    <div class="hpp-stat-box">
                        <span class="stat-label">TOTAL HASIL BARANG JADI</span>
                        <span class="stat-value" id="statTotalKarton">0 Karton</span>
                        <span class="stat-meta" id="statTotalPcsMeta">0 Pcs Finish Good</span>
                    </div>

                    <div class="hpp-stat-box pck-summary-highlight">
                        <span class="stat-label" style="color: #065f46; font-weight: 800;">HPP RIIL PER KARTON</span>
                        <span class="stat-value" id="statHppKarton">Rp 0</span>
                        <span class="stat-meta" id="statHppPcsMeta" style="color: #047857; font-weight: 700;">Setara Rp 0 / Pcs</span>
                    </div>
                </div>

                <input type="hidden" name="total_biaya_produksi" id="total_biaya_produksi" value="0">

                {{-- Action Bar --}}
                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem;">
                    <a href="{{ route('produksi.packaging.rekap') }}" class="btn-hpp-secondary">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitHpp" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 800; padding: 0.65rem 1.75rem; font-size: 0.925rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 2px 4px rgba(5,150,105,0.25);" disabled>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Finalkan Lembar HPP Kemas</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            urlPackagingData: "{{ route('produksi.packaging.hpp.packaging-data', ['id' => '__ID__']) }}",
            urlSyncKaryawan: "{{ route('produksi.sync-karyawan') }}"
        };
    </script>
    <script src="{{ asset('js/produksi/packaging/packaging-hpp-create.js') }}?v={{ filemtime(public_path('js/produksi/packaging/packaging-hpp-create.js')) }}"></script>
@endpush
