@extends('layouts.app')

@section('title', 'Tagihan Gas Utilitas - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_keluar/kas-keluar-form.css') }}?v={{ filemtime(public_path('css/keuangan/kas_keluar/kas-keluar-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/keuangan/hutang/tagihan-gas.css') }}?v={{ file_exists(public_path('css/keuangan/hutang/tagihan-gas.css')) ? filemtime(public_path('css/keuangan/hutang/tagihan-gas.css')) : time() }}">
@endpush

@section('content')
<div class="bkk-full-container">

    {{-- BREADCRUMB STANDAR --}}
    <nav class="bkk-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('keuangan.hutang.index') }}">Keuangan</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('keuangan.hutang.index') }}">Hutang Usaha</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Baru</span>
    </nav>

    {{-- HEADER BAR STANDAR KAS KELUAR --}}
    <div class="bkk-header-bar">
        <div class="bkk-header-info">
            <div class="bkk-header-title">
                <a href="{{ route('keuangan.hutang.index') }}"
                   class="bkk-back-link"
                   aria-label="Kembali ke riwayat hutang">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1>Tagihan Gas Utilitas</h1>
                <span class="bkk-pill-badge">Konsinyasi Pabrik</span>
                <span class="bkk-pill-badge bkk-pill-code">Periode: {{ $periode }}</span>
            </div>
        </div>

        <div class="bkk-header-actions">
            <a href="{{ route('keuangan.hutang.index') }}" class="btn-header-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Riwayat Hutang</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div role="alert" class="bkk-alert-banner">
            <div class="bkk-alert-heading">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Mohon periksa kembali formulir berikut:</span>
            </div>
            <ul class="bkk-alert-list">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UTAMA TAGIHAN GAS --}}
    <form action="{{ route('keuangan.hutang.store_gas') }}" method="POST" id="formTagihanGas" autocomplete="off">
        @csrf

        {{-- Hidden defaults untuk pemenuhan kalkulasi service backend --}}
        <input type="hidden" name="cng_nm3_vendor" id="inputNm3Vendor" value="{{ old('cng_nm3_vendor', $gasSummary['total_cng_nm3']) }}">
        <input type="hidden" name="kurs_jisdor" id="inputKursJisdor" value="{{ old('kurs_jisdor', $gasSummary['avg_kurs_jisdor'] > 0 ? round($gasSummary['avg_kurs_jisdor']) : 16000) }}">

        {{-- ========================================================
             KARTU 1: INFORMASI REKANAN & FAKTUR VENDOR
             ======================================================== --}}
        <div class="bkk-card">
            <div class="bkk-card-header">
                <div>
                    <h2 class="bkk-card-title">Informasi Rekanan &amp; Faktur Vendor</h2>
                    <p class="bkk-card-subtitle">Pemasok gas utilitas, periode bulan pemakaian, nomor invoice fisik, dan termin jatuh tempo pembayaran.</p>
                </div>
            </div>

            <div class="bkk-card-body">
                {{-- Baris 1: Grid 3 Kolom Simetris --}}
                <div class="gas-grid-3col">
                    {{-- Kolom 1: Vendor Gas Pemasok --}}
                    <div class="form-group">
                        <label for="selectSupplier" class="form-label form-label-senior">
                            <span>Vendor Gas Pemasok <span class="req-star">*</span></span>
                        </label>
                        <select name="supplier_id" id="selectSupplier" class="form-control form-control-senior" required>
                            <option value="">-- Pilih Rekanan Vendor Gas --</option>
                            @foreach ($suppliers as $supp)
                                <option value="{{ $supp->supplier_id }}" {{ old('supplier_id') == $supp->supplier_id ? 'selected' : '' }}>
                                    {{ $supp->supplier_nm }} ({{ $supp->supplier_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kolom 2: Bulan Pemakaian --}}
                    <div class="form-group">
                        <label for="inputPeriodeBulan" class="form-label form-label-senior">
                            <span>Bulan Pemakaian <span class="req-star">*</span></span>
                        </label>
                        <input type="month"
                               name="periode_bulan"
                               id="inputPeriodeBulan"
                               value="{{ old('periode_bulan', $periode) }}"
                               class="form-control form-control-senior font-mono"
                               onchange="window.location.href='{{ route('keuangan.hutang.create_gas') }}?periode=' + this.value"
                               required>
                    </div>

                    {{-- Kolom 3: Nomor Faktur / Invoice Vendor --}}
                    <div class="form-group">
                        <label for="inputNoFaktur" class="form-label form-label-senior">
                            <span>Nomor Faktur / Invoice Vendor <span class="req-star">*</span></span>
                        </label>
                        <input type="text"
                               name="no_faktur_supplier"
                               id="inputNoFaktur"
                               value="{{ old('no_faktur_supplier') }}"
                               placeholder="Contoh: INV/PGN/2026/XI/0091"
                               class="form-control form-control-senior font-mono"
                               autocomplete="off"
                               required>
                    </div>
                </div>

                {{-- Baris 2: Grid 3 Kolom Simetris --}}
                <div class="gas-grid-3col" style="margin-top: 1rem;">
                    {{-- Kolom 1: Tanggal Tagihan --}}
                    <div class="form-group">
                        <label for="inputTglTagihan" class="form-label form-label-senior">
                            <span>Tanggal Tagihan <span class="req-star">*</span></span>
                        </label>
                        <input type="date"
                               name="tgl_tagihan"
                               id="inputTglTagihan"
                               value="{{ old('tgl_tagihan', date('Y-m-d')) }}"
                               class="form-control form-control-senior font-mono"
                               required>
                    </div>

                    {{-- Kolom 2: Tempo Pembayaran (TOP) --}}
                    <div class="form-group">
                        <label for="inputTopHari" class="form-label form-label-senior">
                            <span>Tempo Pembayaran (TOP)</span>
                        </label>
                        <div class="input-suffix-wrapper">
                            <input type="number"
                                   id="inputTopHari"
                                   name="top_hari"
                                   min="0"
                                   max="180"
                                   value="{{ old('top_hari', 14) }}"
                                   class="form-control form-control-senior font-mono text-center"
                                   placeholder="14">
                            <span class="input-suffix-tag">Hari</span>
                        </div>
                    </div>

                    {{-- Kolom 3: Tanggal Jatuh Tempo --}}
                    <div class="form-group">
                        <label for="inputTglJatuhTempo" class="form-label form-label-senior">
                            <span>Tanggal Jatuh Tempo <span class="req-star">*</span></span>
                        </label>
                        <input type="date"
                               name="tgl_jatuh_tempo"
                               id="inputTglJatuhTempo"
                               value="{{ old('tgl_jatuh_tempo', date('Y-m-d', strtotime('+14 days'))) }}"
                               class="form-control form-control-senior font-mono"
                               required>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================
             KARTU 2: RINCIAN NILAI TAGIHAN & PEMAKAIAN
             ======================================================== --}}
        <div class="bkk-card">
            <div class="bkk-card-header">
                <div>
                    <h2 class="bkk-card-title">Rincian Nilai Tagihan &amp; Pemakaian</h2>
                    <p class="bkk-card-subtitle">Masukkan nilai pemakaian gas dari faktur vendor. Sistem otomatis menghitung PPN dan total tagihan.</p>
                </div>
            </div>

            <div class="bkk-card-body">
                {{-- Baris 1: Grid 3 Kolom Simetris & Sejajar --}}
                <div class="gas-grid-3col">
                    {{-- Kolom 1: Pemakaian Gas Vendor (MMBTU) --}}
                    <div class="form-group">
                        <label for="inputMmbtuVendor" class="form-label form-label-senior">
                            <span>Pemakaian Gas Vendor (MMBTU) <span class="req-star">*</span></span>
                        </label>
                        <div class="input-suffix-wrapper">
                            <input type="number"
                                   step="0.0001"
                                   min="0"
                                   name="cng_mmbtu_vendor"
                                   id="inputMmbtuVendor"
                                   value="{{ old('cng_mmbtu_vendor', $gasSummary['total_cng_mmbtu']) }}"
                                   class="form-control form-control-senior font-mono text-right"
                                   placeholder="0.0000"
                                   required>
                            <span class="input-suffix-tag">MMBTU</span>
                        </div>
                        <div class="form-sub-badge-row">
                            <span class="badge-coa-item text-slate">
                                Acuan Pabrik: <strong>{{ number_format($gasSummary['total_cng_mmbtu'], 4) }} MMBTU</strong>
                                <span id="noteSelisihMmbtu" class="helper-diff-tag">(Cocok)</span>
                            </span>
                        </div>
                    </div>

                    {{-- Kolom 2: Nilai Tagihan DPP (Sebelum PPN) --}}
                    <div class="form-group">
                        <label for="inputDppDisplay" class="form-label form-label-senior">
                            <span>Nilai Tagihan DPP (Sebelum PPN) <span class="req-star">*</span></span>
                        </label>
                        <div class="input-money-container">
                            <span class="money-prefix">Rp</span>
                            <input type="text"
                                   id="inputDppDisplay"
                                   class="form-control form-control-senior form-control-money font-mono"
                                   placeholder="0"
                                   required>
                            <input type="hidden"
                                   name="dpp_vendor"
                                   id="inputDppVendor"
                                   value="{{ old('dpp_vendor', round($gasSummary['estimasi_biaya'])) }}">
                        </div>
                        <div class="form-sub-badge-row">
                            <button type="button"
                                    class="btn-copy-dpp-link"
                                    id="btnSalinPabrik"
                                    onclick="useFactoryEstimate()"
                                    title="Salin estimasi biaya dari meteran dapur pabrik">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                </svg>
                                <span>Gunakan Nilai Pabrik: Rp {{ number_format($gasSummary['estimasi_biaya'], 0, ',', '.') }}</span>
                            </button>
                            <span class="badge-coa-tag">
                                Akun Beban (D): <strong>5102 - Pemakaian CNG</strong>
                            </span>
                        </div>
                    </div>

                    {{-- Kolom 3: Skema Pajak PPN --}}
                    <div class="form-group">
                        <label for="selectPpnMode" class="form-label form-label-senior">
                            <span>Skema Pajak PPN <span class="req-star">*</span></span>
                        </label>
                        <select id="selectPpnMode" class="form-control form-control-senior" onchange="setPpnMode(this.value)">
                            <option value="11" selected>PPN 11% (Standar)</option>
                            <option value="0">Bebas PPN (0%)</option>
                            <option value="manual">Input Manual</option>
                        </select>
                        <div class="form-sub-badge-row">
                            <span class="badge-coa-item text-slate" id="ppnModeSubBadge">
                                Tarif Pajak Masukan 11%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Baris 2: Grid 3 Kolom Simetris & Sejajar --}}
                <div class="gas-grid-3col" style="margin-top: 1.15rem;">
                    {{-- Kolom 1: Nominal Pajak PPN --}}
                    <div class="form-group">
                        <label for="inputPpnDisplay" class="form-label form-label-senior">
                            <span>Nominal Pajak PPN</span>
                        </label>
                        <div class="input-money-container">
                            <span class="money-prefix">Rp</span>
                            <input type="text"
                                   id="inputPpnDisplay"
                                   class="form-control form-control-senior form-control-money font-mono"
                                   readonly>
                            <input type="hidden"
                                   name="total_ppn"
                                   id="inputTotalPpn"
                                   value="{{ old('total_ppn', 0) }}">
                        </div>
                        <div class="form-sub-badge-row">
                            <span class="badge-coa-tag">
                                Akun Pajak (D): <strong>1130 - PPN Masukan</strong>
                            </span>
                        </div>
                    </div>

                    {{-- Kolom 2: Total Tagihan Yang Harus Dibayar --}}
                    <div class="form-group">
                        <label for="inputTotalTagihanDisplay" class="form-label form-label-senior">
                            <span>Total Tagihan Yang Harus Dibayar <span class="req-star">*</span></span>
                        </label>
                        <div class="input-money-container">
                            <span class="money-prefix">Rp</span>
                            <input type="text"
                                   id="inputTotalTagihanDisplay"
                                   class="form-control form-control-senior form-control-money font-mono font-bold gas-total-highlight-input"
                                   readonly>
                            <input type="hidden"
                                   name="total_tagihan"
                                   id="inputTotalTagihan"
                                   value="{{ old('total_tagihan', round($gasSummary['estimasi_biaya'])) }}">
                        </div>
                        <div class="form-sub-badge-row">
                            <span class="badge-coa-tag">
                                Akun Hutang (K): <strong>2100 - Hutang Usaha</strong>
                            </span>
                        </div>
                    </div>

                    {{-- Kolom 3: Status Rekonsiliasi Meteran --}}
                    <div class="form-group">
                        <label class="form-label form-label-senior">
                            <span>Status Rekonsiliasi Meteran</span>
                        </label>
                        <div class="gas-recon-status-box recon-match" id="reconStatusBox">
                            <div class="gas-recon-status-content" id="totalStatusNote">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Cocok dengan fisik dapur (Selisih Rp 0)</span>
                            </div>
                        </div>
                        <div class="form-sub-badge-row">
                            <span class="badge-coa-item text-slate" id="reconSummarySubNote">
                                Komparasi Faktur vs Estimasi Dapur Pabrik
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Baris 3: Catatan / Keterangan Tagihan --}}
                <div class="form-group" style="margin-top: 1.15rem;">
                    <label for="inputCatatan" class="form-label form-label-senior">
                        <span>Catatan / Keterangan Tagihan (Opsional)</span>
                    </label>
                    <input type="text"
                           name="catatan"
                           id="inputCatatan"
                           class="form-control form-control-senior"
                           value="{{ old('catatan', "Tagihan Utilitas Gas Periode {$periode}") }}"
                           placeholder="Catatan termin pembayaran, transfer rekening vendor, keterangan meteran...">
                </div>

                {{-- Strip Baris Ringkas: Jurnal Umum Otomatis (Gaya Kas Keluar) --}}
                <div class="live-jurnal-badge-bar">
                    <div class="jurnal-badge-title">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Jurnal Umum Otomatis:</span>
                    </div>
                    <div class="jurnal-badge-entries">
                        <span class="jurnal-coa-pill pill-debet"><strong>(D) 5102</strong> - Pemakaian CNG</span>
                        <span class="jurnal-badge-sep">&bull;</span>
                        <span class="jurnal-coa-pill pill-debet"><strong>(D) 1130</strong> - PPN Masukan</span>
                        <span class="jurnal-badge-sep">&bull;</span>
                        <span class="jurnal-coa-pill pill-kredit"><strong>(K) 2100</strong> - Hutang Usaha Vendor</span>
                    </div>
                </div>

                {{-- Garis Pemisah & Baris 4: Info Meteran Pabrik 1 Baris di Bagian Bawah --}}
                <div class="gas-meter-bottom-summary">
                    <div class="gas-meter-summary-text">
                        Catatan Dapur Pabrik: <strong>{{ number_format($gasSummary['total_cng_mmbtu'], 4) }} MMBTU</strong> ({{ number_format($gasSummary['total_cng_nm3'], 2) }} Nm³) &bull; Estimasi: <strong>Rp {{ number_format($gasSummary['estimasi_biaya'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="gas-meter-summary-action">
                        <button type="button" class="btn-header-secondary btn-sm-rincian" onclick="openModalRincianHarian()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span>Lihat Rincian Harian ({{ count($gasSummary['daily_details']) }} Data)</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========================================================
             BOTTOM ACTION BAR
             ======================================================== --}}
        <div class="gas-bottom-action-bar">
            <a href="{{ route('keuangan.hutang.index') }}" class="btn-dock-cancel">
                Batal
            </a>
            <button type="button" class="btn-dock-submit" onclick="openModalConfirmGas()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Simpan Tagihan Gas</span>
            </button>
        </div>

    </form>
</div>

{{-- MODALS PARTIALS --}}
@include('keuangan.hutang.partials.modal-rincian-harian-gas')
@include('keuangan.hutang.partials.modal-confirm-gas')

@endsection

@push('scripts')
    <script>
        window.gasConfig = {
            totalMmbtuPabrik: {{ (float) ($gasSummary['total_cng_mmbtu'] ?? 0) }},
            totalNm3Pabrik: {{ (float) ($gasSummary['total_cng_nm3'] ?? 0) }},
            totalEstimasiAkrual: {{ (float) ($gasSummary['estimasi_biaya'] ?? 0) }},
            avgKursJisdor: {{ (float) ($gasSummary['avg_kurs_jisdor'] ?? 0) }},
            avgTarifUsd: {{ (float) ($gasSummary['avg_tarif_usd'] ?? 0) }},
            periodeBulan: '{{ $periode }}',
            baseUrl: '{{ url('') }}'
        };
    </script>
    <script src="{{ asset('js/keuangan/hutang/tagihan-gas.js') }}?v={{ file_exists(public_path('js/keuangan/hutang/tagihan-gas.js')) ? filemtime(public_path('js/keuangan/hutang/tagihan-gas.js')) : time() }}"></script>
@endpush
