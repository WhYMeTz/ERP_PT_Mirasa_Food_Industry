@extends('layouts.qc-mobile')

@section('title', 'QC Mobile - Penerimaan Karton Balikan')
@section('hide_bottom_nav', true)

{{-- NAVBAR ATAS: CLEAN, SINGLE LINE & TIDAK CROWDED --}}
@section('top_brand_url', route('gudang.kemasan-sirkular.index'))
@section('top_brand_badge')
    <span class="qc-brand-badge-logistik">LOGISTIK</span>
@endsection
@section('top_brand_sub', 'Karton Balikan • Logistik')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/kemasan_sirkular/sirkular-mobile.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="sirkular-mobile-wrap">
    {{-- 1. SUB-NAVIGASI HALAMAN (MODEL ENTERPRISE QC MOBILE) --}}
    <div class="sirkular-subnav">
        <a href="{{ route('gudang.kemasan-sirkular.index') }}" class="btn-subnav-back" title="Lihat Rekap Surat Jalan Balikan">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Riwayat Karton Balikan</span>
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
            <div class="mobile-kpi-card-label">Total Kirim (DO)</div>
            <div class="mobile-kpi-card-val" id="m_stat_kirim">0</div>
        </div>
        <div class="mobile-kpi-card" style="background: #f0fdf4; border-color: #86efac;">
            <div class="mobile-kpi-card-label" style="color: #166534;">Fisik Kembali</div>
            <div class="mobile-kpi-card-val" id="m_stat_kembali" style="color: #15803d;">0</div>
        </div>
        <div class="mobile-kpi-card">
            <div class="mobile-kpi-card-label">Selisih DO</div>
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
                    <h2 class="mobile-card-title-text">1. Surat Jalan &amp; Info Kendaraan</h2>
                </div>
                <span style="font-size: 0.725rem; font-weight: 700; color: #475569; font-family: monospace; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.15rem 0.5rem; border-radius: 4px;">{{ $sirkularNo }}</span>
            </div>

            <div class="mobile-form-card-body">
                {{-- PILIH REFERENSI SURAT JALAN DO --}}
                <div class="mobile-form-group">
                    <label for="m_pengeluaran_id" class="mobile-form-label" style="color: #0284c7;">
                        No. Surat Jalan Pengiriman
                    </label>
                    <select name="pengeluaran_id" id="m_pengeluaran_id" class="mobile-form-control" style="border-color: #38bdf8;">
                        <option value="">-- Tanpa Surat Jalan (Internal Pabrik / Bebas) --</option>
                        @foreach($pengeluaranList as $p)
                            <option value="{{ $p->pengeluaran_id }}" {{ old('pengeluaran_id') == $p->pengeluaran_id ? 'selected' : '' }}>
                                [{{ $p->pengeluaran_no }}] - {{ $p->customer?->customer_nm }} ({{ number_format($p->total_qty, 0, ',', '.') }} Box)
                            </option>
                        @endforeach
                    </select>
                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.35rem;">
                        Pilih surat jalan dari supir truk Indofood, atau biarkan kosong jika kardus dari dalam pabrik sendiri.
                    </div>
                </div>

                {{-- CUSTOMER & GUDANG --}}
                <div class="mobile-form-row">
                    <div>
                        <label for="m_customer_id" class="mobile-form-label">Nama Customer / Tujuan</label>
                        <select name="customer_id" id="m_customer_id" class="mobile-form-control">
                            <option value="">-- Internal Pabrik / Bebas --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->customer_id }}" {{ (old('customer_id') == $c->customer_id || str_contains(strtoupper($c->customer_nm), 'INDOFOOD')) ? 'selected' : '' }}>
                                    {{ $c->customer_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="m_gudang_id" class="mobile-form-label">Disimpan ke Gudang <span style="color: #ef4444;">*</span></label>
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
                        <label for="m_supir_nm" class="mobile-form-label">Nama Sopir / Pembawa</label>
                        <input type="text" name="supir_nm" id="m_supir_nm" value="{{ old('supir_nm') }}" class="mobile-form-control" placeholder="Nama pengemudi armada / petugas">
                    </div>
                    <div>
                        <label for="m_nopol_kendaraan" class="mobile-form-label">Plat Nomor Truk / Mobil</label>
                        <input type="text" name="nopol_kendaraan" id="m_nopol_kendaraan" value="{{ old('nopol_kendaraan') }}" class="mobile-form-control" placeholder="Contoh: B 9123 KPS" style="font-weight: 700;">
                    </div>
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
                    <h2 class="mobile-card-title-text">2. Hasil Hitung Kardus Balikan</h2>
                </div>
                <span class="commodity-tag">Karton IFM 6 Kg</span>
            </div>

            {{-- TOOLBAR AUTO-FILL & RESET --}}
            <div class="sortir-toolbar">
                <button type="button" class="btn-tool-secondary" onclick="setAllFromDo()" title="Klik jika seluruh kardus kembali utuh sesuai surat jalan">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Sesuai Surat Jalan (<span id="chip_kirim_target">0</span> Box)</span>
                </button>
                <button type="button" class="btn-tool-ghost" onclick="resetAllSortir()">
                    Kosongkan
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

                {{-- ITEM 1: KARDUS BAGUS / LAYAK PAKAI --}}
                <div class="sortir-item-row">
                    <div class="sortir-item-header">
                        <div>
                            <div class="sortir-item-title">1. Kardus Bagus / Layak Pakai</div>
                            <div class="sortir-item-sub">Kondisi bersih &amp; siap dipakai produksi lagi</div>
                        </div>
                        <span class="sortir-sku-badge">{{ $barangKartonPuteran?->barang_cd ?? 'KIF00G-PUT' }}</span>
                    </div>
                    <div class="sortir-input-group">
                        <input type="number" inputmode="numeric" step="1" min="0" name="items[0][mulus_qty]" id="m_mulus_qty" value="{{ old('items.0.mulus_qty', 0) }}" class="sortir-num-input" placeholder="0" oninput="calcMobileSortir()" onfocus="this.select()">
                        <span class="sortir-unit-addon">Pcs</span>
                    </div>
                </div>

                {{-- ITEM 2: KARDUS RUSAK / AFKIR --}}
                <div class="sortir-item-row" style="border-bottom: none;">
                    <div class="sortir-item-header">
                        <div>
                            <div class="sortir-item-title">2. Kardus Rusak / Afkir</div>
                            <div class="sortir-item-sub">Kondisi sobek, basah, atau tidak layak</div>
                        </div>
                        <span class="sortir-sku-badge sku-reject">{{ $barangKartonReject?->barang_cd ?? 'KMS-KTN-REJ' }}</span>
                    </div>
                    <div class="sortir-input-group">
                        <input type="number" inputmode="numeric" step="1" min="0" name="items[0][rusak_qty]" id="m_rusak_qty" value="{{ old('items.0.rusak_qty', 0) }}" class="sortir-num-input" placeholder="0" oninput="calcMobileSortir()" onfocus="this.select()">
                        <span class="sortir-unit-addon">Pcs</span>
                    </div>
                </div>
            </div>

            {{-- SUMMARY TALLY FOOTER --}}
            <div class="sortir-tally-footer">
                <div class="tally-footer-item">
                    <span class="tally-footer-label">Total Diterima:</span>
                    <strong class="tally-footer-val" id="textFooterKembali">0 Pcs</strong>
                </div>
                <div class="tally-footer-item">
                    <span class="tally-footer-label">Selisih Kurang:</span>
                    <strong class="tally-footer-val" id="textFooterSelisih" style="color: #059669;">0 Pcs</strong>
                </div>
            </div>
        </div>

        {{-- CATATAN TAMBAHAN CHECKER LAPANGAN --}}
        <div class="mobile-form-card" style="margin-top: 1rem;">
            <div class="mobile-form-card-body" style="padding: 1rem;">
                <label for="m_catatan_txt" class="mobile-form-label">
                    Catatan Tambahan (Opsional)
                </label>
                <textarea name="catatan_txt" id="m_catatan_txt" rows="2" class="mobile-form-control" style="height: auto; font-size: 0.825rem;" placeholder="Tuliskan kondisi fisik muatan, lakban sobek, kardus basah, atau keterangan selisih jika ada...">{{ old('catatan_txt') }}</textarea>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 3. FIXED BOTTOM FLOATING ACTION BAR                                       --}}
        {{-- ========================================================================= --}}
        <div class="mobile-fixed-bottom-bar">
            <div class="mobile-bottom-bar-inner">
                <button type="submit" id="btnBottomSubmit" class="btn-mobile-submit-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Penerimaan Karton Balikan</span>
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
