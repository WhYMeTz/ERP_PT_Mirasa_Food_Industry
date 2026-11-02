@extends('layouts.qc-mobile')

@section('title', 'QC Mobile - Penerimaan Kemasan Sirkular')
@section('hide_bottom_nav', true)

{{-- NAVBAR ATAS: CLEAN, SINGLE LINE & TIDAK CROWDED --}}
@section('top_brand_url', route('gudang.kemasan-sirkular.index'))
@section('top_brand_badge')
    <span class="qc-brand-badge-logistik">LOGISTIK</span>
@endsection
@section('top_brand_sub', 'Sirkular Kemasan • Logistik')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/kemasan_sirkular/sirkular-mobile.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="sirkular-mobile-wrap">
    {{-- 1. SUB-NAVIGASI HALAMAN (MODEL ENTERPRISE QC MOBILE) --}}
    <div class="sirkular-subnav">
        <a href="{{ route('gudang.kemasan-sirkular.index') }}" class="btn-subnav-back" title="Lihat Rekap Surat Jalan Balikan">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Riwayat Penerimaan</span>
        </a>

        @if (Auth::user()?->isSuperAdmin())
            <a href="{{ route('gudang.kemasan-sirkular.create') }}" class="btn-subnav-office" title="Buka Versi Layar Lebar Desktop">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Versi Office</span>
            </a>
        @endif
    </div>

    {{-- 2. HEADER HALAMAN BERSIH & MODERN --}}
    <div class="sirkular-page-head">
        <div class="sirkular-head-top">
            <h1 class="sirkular-page-title">
                <span>Penerimaan Karton Balikan</span>
            </h1>
            <span class="sirkular-doc-badge">
                MFI/LOG-SIRKULAR/FRM-01
            </span>
        </div>
        <p class="sirkular-page-desc">
            Sortir fisik kardus balikan pengiriman customer di pintu bongkar muat sebelum masuk kartu stok puteran.
        </p>
    </div>

    {{-- 2. LIVE REKONSILIASI KPI BAR --}}
    <div class="mobile-kpi-bar">
        <div class="mobile-kpi-card">
            <div class="mobile-kpi-card-label">Terkirim (DO)</div>
            <div class="mobile-kpi-card-val" id="m_stat_kirim">0</div>
        </div>
        <div class="mobile-kpi-card" style="background: #f0fdf4; border-color: #86efac;">
            <div class="mobile-kpi-card-label" style="color: #166534;">Fisik Kembali</div>
            <div class="mobile-kpi-card-val" id="m_stat_kembali" style="color: #15803d;">0</div>
        </div>
        <div class="mobile-kpi-card">
            <div class="mobile-kpi-card-label">Selisih/Kurang</div>
            <div class="mobile-kpi-card-val" id="m_stat_selisih" style="color: #059669;">0</div>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1rem; color: #991b1b;">
            <div style="font-weight: 800; font-size: 0.85rem; margin-bottom: 0.25rem;">⚠️ Harap periksa isian formulir:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gudang.kemasan-sirkular.store') }}" method="POST" id="formMobileSirkular">
        @csrf
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="sirkular_no" value="{{ $sirkularNo }}">
        <input type="hidden" name="sirkular_tgl" value="{{ date('Y-m-d') }}">

        {{-- ========================================================================= --}}
        {{-- BAGIAN 1: DOKUMEN PENGIRIMAN & IDENTITAS ARMADA                           --}}
        {{-- ========================================================================= --}}
        <div class="mobile-form-card">
            <div class="mobile-form-card-header">
                <div class="mobile-card-title-wrap">
                    <span class="mobile-card-num-badge">1</span>
                    <h2 class="mobile-card-title-text">Dokumen Pengiriman &amp; Armada</h2>
                </div>
                <span style="font-size: 0.725rem; font-weight: 800; color: #64748b; font-family: monospace;">{{ $sirkularNo }}</span>
            </div>

            <div class="mobile-form-card-body">
                {{-- PILIH REFERENSI SURAT JALAN DO --}}
                <div class="mobile-form-group">
                    <label for="m_pengeluaran_id" class="mobile-form-label" style="color: #0284c7;">
                        Pilih Surat Jalan Pengiriman (DO)
                    </label>
                    <select name="pengeluaran_id" id="m_pengeluaran_id" class="mobile-form-control" style="border-color: #38bdf8;">
                        <option value="">-- Pilih DO Pengiriman (Auto-Fill) --</option>
                        @foreach($pengeluaranList as $p)
                            <option value="{{ $p->pengeluaran_id }}" {{ old('pengeluaran_id') == $p->pengeluaran_id ? 'selected' : '' }}>
                                [{{ $p->pengeluaran_no }}] - {{ $p->customer?->customer_nm }} ({{ number_format($p->total_qty, 0, ',', '.') }} Box)
                            </option>
                        @endforeach
                    </select>
                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.35rem;">
                        💡 Memilih DO akan otomatis mengisi customer, nama supir, nomor polisi, dan target kuantitas box.
                    </div>
                </div>

                {{-- CUSTOMER & GUDANG --}}
                <div class="mobile-form-row">
                    <div>
                        <label for="m_customer_id" class="mobile-form-label">Customer / Mitra</label>
                        <select name="customer_id" id="m_customer_id" class="mobile-form-control" required>
                            <option value="">-- Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->customer_id }}" {{ (old('customer_id') == $c->customer_id || str_contains(strtoupper($c->customer_nm), 'INDOFOOD')) ? 'selected' : '' }}>
                                    {{ $c->customer_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="m_gudang_id" class="mobile-form-label">Gudang Simpan</label>
                        <select name="gudang_id" id="m_gudang_id" class="mobile-form-control" required>
                            @foreach($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ (old('gudang_id') == $g->gudang_id || str_contains(strtoupper($g->gudang_nm), 'KEMASAN') || str_contains(strtoupper($g->gudang_nm), 'BAHAN')) ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ARMADA & SOPIR --}}
                <div class="mobile-form-row">
                    <div>
                        <label for="m_supir_nm" class="mobile-form-label">Nama Sopir</label>
                        <input type="text" name="supir_nm" id="m_supir_nm" value="{{ old('supir_nm') }}" class="mobile-form-control" placeholder="Pengemudi truk">
                    </div>
                    <div>
                        <label for="m_nopol_kendaraan" class="mobile-form-label">No. Polisi Truk</label>
                        <input type="text" name="nopol_kendaraan" id="m_nopol_kendaraan" value="{{ old('nopol_kendaraan') }}" class="mobile-form-control" placeholder="B 9123 KPS" style="font-weight: 700;">
                    </div>
                </div>

                {{-- SURAT JALAN BALIKAN / TANDA TERIMA --}}
                <div class="mobile-form-group" style="margin-bottom: 0;">
                    <label for="m_surat_jalan_customer_no" class="mobile-form-label">No. Surat Jalan Balikan / Tanda Terima Fisik (Opsional)</label>
                    <input type="text" name="surat_jalan_customer_no" id="m_surat_jalan_customer_no" value="{{ old('surat_jalan_customer_no') }}" class="mobile-form-control" placeholder="Contoh: SJ-IFM/RET/2026/10/248">
                </div>

                <input type="hidden" name="ekspedisi_nm" id="m_ekspedisi_nm" value="{{ old('ekspedisi_nm', 'Internal Mirasa') }}">
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- BAGIAN 2: HASIL SORTIR FISIK WADAH KARTON (DIRECT INPUT LAPANGAN)         --}}
        {{-- ========================================================================= --}}
        <div class="mobile-form-card" style="margin-top: 1rem;">
            <div class="mobile-form-card-header">
                <div class="mobile-card-title-wrap">
                    <span class="mobile-card-num-badge">2</span>
                    <h2 class="mobile-card-title-text">Hasil Sortir Fisik Wadah</h2>
                </div>
                <span class="qc-field-tag">2 KONDISI FISIK</span>
            </div>

            {{-- QUICK TOOLBAR (AUTO-FILL DARI DO & RESET) --}}
            <div class="sortir-toolbar">
                <button type="button" class="btn-toolbar-autofill" onclick="setAllFromDo()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Samakan dengan DO (<span id="chip_kirim_target">0</span> Box)</span>
                </button>
                <button type="button" class="btn-toolbar-reset" onclick="resetAllSortir()">
                    ↺ Kosongkan
                </button>
            </div>

            <div class="mobile-form-card-body" style="padding: 0;">
                {{-- HIDDEN MASTER ID & KIRIM QTY --}}
                <input type="hidden" name="items[0][barang_asal_id]" value="{{ $barangKartonBaru?->barang_id ?? 29 }}">
                <input type="hidden" name="items[0][barang_mulus_id]" value="{{ $barangKartonPuteran?->barang_id ?? 82 }}">
                <input type="hidden" name="items[0][barang_bmg_id]" value="{{ $barangKartonBmg?->barang_id ?? 83 }}">
                <input type="hidden" name="items[0][barang_rusak_id]" value="{{ $barangKartonReject?->barang_id ?? 84 }}">
                <input type="hidden" name="items[0][kirim_qty]" id="m_kirim_qty" value="{{ old('items.0.kirim_qty', 0) }}">
                <input type="hidden" name="items[0][kembali_qty]" id="m_kembali_qty" value="{{ old('items.0.kembali_qty', 0) }}">
                <input type="hidden" name="items[0][baru_qty]" id="m_baru_qty" value="0">
                <input type="hidden" name="items[0][bmg_qty]" id="m_bmg_qty" value="0">
                <input type="hidden" name="items[0][puteran_ke]" id="m_puteran_ke" value="{{ old('items.0.puteran_ke', 1) }}">
                <input type="hidden" name="items[0][afkir_siklus_qty]" id="m_afkir_siklus_qty" value="{{ old('items.0.afkir_siklus_qty', 0) }}">

                {{-- ITEM 1: KARTON PUTERAN / BMG (BEKAS MASIH BAGUS) --}}
                <div class="sortir-tally-row">
                    <div class="sortir-tally-meta">
                        <div class="sortir-tally-title">
                            <span>1. Karton Puteran (BMG / Layak Pakai)</span>
                            <span id="badgeCycleStatus" class="tally-pill pill-success">Putaran 1 (Layak IFM)</span>
                        </div>
                        <div class="sortir-tally-desc">
                            Kardus balikan mulus &bull; Bekas Masih Good (Maks. 5x Pakai) &bull; SKU: <span class="font-mono">KIF00G-PUT</span>
                        </div>
                    </div>

                    {{-- SELECTOR SIKLUS PEMAKAIAN KARTON 1 S/D 5 --}}
                    <div class="cycle-selector-container">
                        <div class="cycle-selector-label">
                            <span>Siklus Pemakaian Kardus:</span>
                            <span style="font-weight: 800; color: #059669;">Maks. 5x Pakai</span>
                        </div>
                        <div class="cycle-btn-group">
                            <button type="button" class="cycle-btn active" data-cycle="1" onclick="setCycle(1)">
                                <span class="cycle-btn-num">1x</span>
                                <span class="cycle-btn-sub">Puteran 1</span>
                            </button>
                            <button type="button" class="cycle-btn" data-cycle="2" onclick="setCycle(2)">
                                <span class="cycle-btn-num">2x</span>
                                <span class="cycle-btn-sub">Puteran 2</span>
                            </button>
                            <button type="button" class="cycle-btn" data-cycle="3" onclick="setCycle(3)">
                                <span class="cycle-btn-num">3x</span>
                                <span class="cycle-btn-sub">Puteran 3</span>
                            </button>
                            <button type="button" class="cycle-btn" data-cycle="4" onclick="setCycle(4)">
                                <span class="cycle-btn-num">4x</span>
                                <span class="cycle-btn-sub">Puteran 4</span>
                            </button>
                            <button type="button" class="cycle-btn cycle-max" data-cycle="5" onclick="setCycle(5)" title="Batas Maksimal Pemakaian (Auto-Reject)">
                                <span class="cycle-btn-num">5x</span>
                                <span class="cycle-btn-sub">Limit Max</span>
                            </button>
                        </div>

                        {{-- PERINGATAN LIMIT MAKSIMAL 5X --}}
                        <div id="cycleAlertBanner" class="cycle-alert-banner">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <strong>⚠️ Batas SOP 5x Pemakaian Tercapai!</strong><br>
                                Kardus putaran ke-5 tidak boleh dipakai lagi untuk pengiriman customer. Kuantitas ini otomatis dialihkan ke <strong>Karton Reject / Manual (KMS-KTN-REJ)</strong>.
                            </div>
                        </div>
                    </div>

                    <div class="direct-input-bar">
                        <label for="m_mulus_qty" class="direct-input-label">Jumlah Fisik Puteran / BMG Diterima:</label>
                        <div class="direct-input-suffix-wrap">
                            <input type="number" inputmode="numeric" step="1" min="0" name="items[0][mulus_qty]" id="m_mulus_qty" value="{{ old('items.0.mulus_qty', 0) }}" class="direct-input-field" placeholder="0" oninput="calcMobileSortir()" onfocus="this.select()">
                            <span class="direct-input-suffix">PCS</span>
                        </div>
                    </div>
                </div>

                {{-- ITEM 2: KARTON REJECT / RUSAK FISIK --}}
                <div class="sortir-tally-row" style="border-bottom: none;">
                    <div class="sortir-tally-meta">
                        <div class="sortir-tally-title">
                            <span>2. Karton Reject / Rusak Fisik</span>
                            <span class="tally-pill pill-danger">Lini Manual (Rp 0)</span>
                        </div>
                        <div class="sortir-tally-desc">
                            Karton sobek / basah / afkir lini manual &bull; SKU: <span class="font-mono">KMS-KTN-REJ</span>
                        </div>
                    </div>

                    <div class="direct-input-bar">
                        <label for="m_rusak_qty" class="direct-input-label">Jumlah Fisik Rusak / Sobek:</label>
                        <div class="direct-input-suffix-wrap">
                            <input type="number" inputmode="numeric" step="1" min="0" name="items[0][rusak_qty]" id="m_rusak_qty" value="{{ old('items.0.rusak_qty', 0) }}" class="direct-input-field" placeholder="0" oninput="calcMobileSortir()" onfocus="this.select()">
                            <span class="direct-input-suffix">PCS</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUMMARY TALLY FOOTER --}}
            <div class="sortir-tally-footer">
                <div class="tally-footer-item">
                    <span class="tally-footer-label">Total Fisik:</span>
                    <span class="tally-footer-val" id="textFooterKembali">0 Pcs</span>
                </div>
                <div class="tally-footer-item">
                    <span class="tally-footer-label">Status DO:</span>
                    <span class="tally-footer-badge badge-match" id="badgeFooterStatus">Belum Diisi</span>
                </div>
            </div>
        </div>

        {{-- CATATAN TAMBAHAN CHECKER LAPANGAN --}}
        <div class="mobile-form-card" style="margin-top: 1rem;">
            <div class="mobile-form-card-body" style="padding: 1rem;">
                <label for="m_catatan_txt" class="mobile-form-label">
                    Catatan Checker Lapangan (Opsional)
                </label>
                <textarea name="catatan_txt" id="m_catatan_txt" rows="2" class="mobile-form-control" style="height: auto; font-size: 0.825rem;" placeholder="Kondisi balikan, lakban terbuka, kardus basah, dll...">{{ old('catatan_txt') }}</textarea>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 3. FIXED BOTTOM FLOATING ACTION BAR                                       --}}
        {{-- ========================================================================= --}}
        <div class="mobile-fixed-bottom-bar">
            <div class="mobile-bottom-bar-inner">
                <button type="submit" id="btnBottomSubmit" class="btn-mobile-submit-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Penerimaan Fisik</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            ajaxPengeluaranUrl: "{{ route('gudang.kemasan-sirkular.ajax-pengeluaran-detail', ':id') }}"
        };
    </script>
    <script src="{{ asset('js/gudang/kemasan_sirkular/sirkular-mobile.js') }}?v={{ time() }}"></script>
@endpush
