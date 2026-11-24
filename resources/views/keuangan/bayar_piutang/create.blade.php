@extends('layouts.app')

@section('title', 'Terima Pembayaran Customer (BKM AR) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar_piutang/bayar-piutang-form.css') }}?v={{ file_exists(public_path('css/keuangan/bayar_piutang/bayar-piutang-form.css')) ? filemtime(public_path('css/keuangan/bayar_piutang/bayar-piutang-form.css')) : time() }}">
@endpush

@section('content')
<div class="ar-form-container">

    {{-- 1. BREADCRUMB --}}
    <nav class="ar-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('keuangan.bayar_piutang.index') }}">Keuangan</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('keuangan.bayar_piutang.index') }}">Terima Pembayaran Customer</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Baru</span>
    </nav>

    {{-- 2. HEADER BAR --}}
    <div class="ar-header-bar">
        <div class="ar-header-info">
            <div class="ar-header-title">
                <a href="{{ route('keuangan.bayar_piutang.index') }}"
                   class="ar-back-link"
                   aria-label="Kembali ke daftar pembayaran">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1>Terima Pembayaran Customer</h1>
                <span class="ar-pill-badge">Pelunasan Faktur Penjualan</span>
                <span class="ar-pill-badge" style="font-family: monospace; font-weight: 700;">BKM AR</span>
            </div>
            <p class="ar-header-subtitle">
                Pencatatan pembayaran tagihan faktur toko &amp; distributor secara penuh maupun cicilan bertahap.
            </p>
        </div>

        <div class="ar-header-actions">
            <a href="{{ route('keuangan.bayar_piutang.index') }}" class="btn-header-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Daftar Pembayaran</span>
            </a>
        </div>
    </div>

    {{-- Alert Validasi Error --}}
    @if (isset($errors) && $errors->any())
        <div role="alert" class="ar-alert-banner">
            <div class="ar-alert-heading">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Mohon periksa kembali isian formulir:</span>
            </div>
            <ul class="ar-alert-list">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="ar-alert-banner">
            <div class="ar-alert-heading">
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <form id="formBayarPiutang" method="POST" action="{{ route('keuangan.bayar_piutang.store') }}" autocomplete="off">
        @csrf

        {{-- CARD 1: PARAMETER PENERIMAAN --}}
        <div class="ar-card">
            <div class="ar-card-header">
                <h2 class="ar-card-title">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>1. Parameter Penerimaan Kas / Bank</span>
                </h2>
                <span id="customerSelectedBadge" class="recipient-pill-badge">
                    Pilih Pelanggan
                </span>
            </div>

            <div class="ar-card-body">
                {{-- Chips Akses Cepat Customer dengan Tagihan Terbuka --}}
                @if ($unpaidCounts->isNotEmpty())
                    <div class="chips-section-title">Pelanggan dengan Tagihan Faktur Terbuka:</div>
                    <div class="customer-chips-scroll">
                        @foreach ($customers as $c)
                            @if (isset($unpaidCounts[$c->customer_id]))
                                @php
                                    $info = $unpaidCounts[$c->customer_id];
                                    $isActiveChip = ($customerId == $c->customer_id);
                                @endphp
                                <button type="button"
                                        class="customer-chip {{ $isActiveChip ? 'active' : '' }}"
                                        data-customer-id="{{ $c->customer_id }}">
                                    <div class="customer-chip-name">{{ $c->customer_nm }}</div>
                                    <div class="customer-chip-sub">
                                        {{ $info->count_faktur }} Faktur &bull; Rp {{ number_format($info->total_sisa, 0, ',', '.') }}
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="parameter-grid">
                    {{-- 1. CUSTOMER AUTOCOMPLETE --}}
                    <div class="form-group" id="customerAutocompleteContainer">
                        <label for="customer_input" class="form-label">
                            <span>Pelanggan (Customer) <span class="req-star">*</span></span>
                        </label>
                        <div class="autocomplete-container">
                            <div class="autocomplete-input-wrap">
                                <span class="autocomplete-search-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text"
                                       id="customer_input"
                                       class="form-control-large autocomplete-input"
                                       placeholder="Ketik nama pelanggan / toko..."
                                       autocomplete="off">
                                <button type="button" id="btnClearCustomer" class="btn-clear-autocomplete" aria-label="Hapus pilihan customer">&times;</button>
                            </div>
                            <div id="customerDropdown" class="autocomplete-dropdown">
                                <div id="customerSuggestionsList"></div>
                            </div>
                        </div>

                        {{-- Hidden Native Select for standard form submit --}}
                        <select name="customer_id" id="selectCustomerId" style="display: none;" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach ($customers as $cust)
                                <option value="{{ $cust->customer_id }}"
                                        data-name="{{ $cust->customer_nm }}"
                                        data-code="{{ $cust->customer_cd ?? 'CUST' }}"
                                        {{ old('customer_id', $customerId) == $cust->customer_id ? 'selected' : '' }}>
                                    {{ $cust->customer_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. REKENING KAS / BANK --}}
                    <div class="form-group">
                        <label for="selectKasBankId" class="form-label">
                            <span>Akun Kas / Bank <span class="req-star">*</span></span>
                        </label>
                        <select name="kas_bank_id" id="selectKasBankId" class="form-control-large" required>
                            <option value="">-- Rekening Penerima --</option>
                            @foreach ($kasBanks as $kb)
                                <option value="{{ $kb->kas_bank_id }}"
                                        data-saldo="{{ $kb->saldo_berjalan }}"
                                        data-tipe="{{ $kb->tipe_akun }}"
                                        {{ old('kas_bank_id') == $kb->kas_bank_id ? 'selected' : '' }}>
                                    {{ $kb->nama_akun }} ({{ $kb->tipe_akun }})
                                </option>
                            @endforeach
                        </select>
                        <div class="live-saldo-badge">
                            <span class="live-saldo-caption">Saldo Berjalan:</span>
                            <span id="liveSaldoDisplay" class="live-saldo-value">Rp 0</span>
                        </div>
                    </div>

                    {{-- 3. TANGGAL & METODE PENERIMAAN --}}
                    <div class="form-group">
                        <label for="inputTglBayar" class="form-label">
                            <span>Tanggal Pembayaran <span class="req-star">*</span></span>
                        </label>
                        <input type="date"
                               id="inputTglBayar"
                               name="tgl_bayar"
                               class="form-control-large"
                               value="{{ old('tgl_bayar', now()->toDateString()) }}"
                               required>
                        <div style="margin-top: 0.35rem;">
                            <select name="metode_bayar" id="selectMetodeBayar" class="form-control-large" style="height: 38px; font-size: 0.825rem;" required>
                                <option value="TRANSFER" {{ old('metode_bayar') === 'TRANSFER' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="CASH" {{ old('metode_bayar') === 'CASH' ? 'selected' : '' }}>Tunai / Kas Langsung</option>
                                <option value="GIRO" {{ old('metode_bayar') === 'GIRO' ? 'selected' : '' }}>Bilyet Giro / Cek</option>
                                <option value="LAINNYA" {{ old('metode_bayar') === 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                    </div>

                    {{-- 4. DITERIMA DARI & NO REFERENSI BANK --}}
                    <div class="form-group">
                        <label for="inputDiterimaDari" class="form-label">
                            <span>Diterima Dari / Penyetor <span class="req-star">*</span></span>
                        </label>
                        <input type="text"
                               id="inputDiterimaDari"
                               name="diterima_dari"
                               class="form-control-large"
                               placeholder="Nama penyetor / pihak toko"
                               value="{{ old('diterima_dari') }}"
                               required>
                        <div style="margin-top: 0.35rem;">
                            <input type="text"
                                   id="inputNoRefBank"
                                   name="no_referensi_bank"
                                   class="form-control-large"
                                   placeholder="No Ref Bank / Giro (opsional)"
                                   value="{{ old('no_referensi_bank') }}"
                                   style="height: 38px; font-size: 0.825rem;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: DAFTAR FAKTUR TERBUKA YANG LAPANG & TIDAK BERDESAKAN --}}
        <div class="ar-card">
            <div class="ar-card-header">
                <h2 class="ar-card-title">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>2. Alokasi Faktur Penjualan Belum Lunas</span>
                </h2>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button type="button" class="btn-table-action-fast" onclick="lunasiSemuaFaktur()">
                        Lunasi Semua Faktur
                    </button>
                    <button type="button" class="btn-table-action-fast" onclick="kosongkanSemuaFaktur()">
                        Kosongkan
                    </button>
                </div>
            </div>

            <div class="ar-card-body" style="padding: 0;">
                {{-- Loading Notice --}}
                <div id="loadingFakturNotice" class="faktur-loading-box" style="display: none;">
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">Memuat daftar tagihan faktur...</div>
                    <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 4px;">Mengambil data piutang customer secara real-time</div>
                </div>

                {{-- Empty Notice --}}
                <div id="emptyFakturNotice" class="faktur-empty-box" style="margin: 1.5rem;">
                    <div class="faktur-empty-title">Silakan pilih pelanggan di atas</div>
                    <div class="faktur-empty-sub">Faktur penjualan yang belum lunas akan dimuat secara otomatis di sini.</div>
                </div>

                {{-- Table Area --}}
                <div class="ar-table-faktur-wrap">
                    <table class="ar-table-faktur">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">
                                    <input type="checkbox" id="checkAllFaktur" aria-label="Pilih Semua Faktur" style="width: 17px; height: 17px; cursor: pointer;">
                                </th>
                                <th style="min-width: 160px;">No. Faktur &amp; PO</th>
                                <th style="min-width: 130px;">Tgl Faktur &amp; TOP</th>
                                <th style="min-width: 150px;">Jatuh Tempo</th>
                                <th style="min-width: 150px; text-align: right;">Total Nilai Faktur</th>
                                <th style="min-width: 150px; text-align: right;">Sisa Piutang</th>
                                <th style="min-width: 110px; text-align: center;">Status</th>
                                <th style="min-width: 260px; text-align: right;">Alokasi Pembayaran (Nominal Bayar)</th>
                            </tr>
                        </thead>
                        <tbody id="fakturTableBody">
                            {{-- Di-render secara dinamis via JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CATATAN TAMBAHAN --}}
        <div class="ar-notes-card">
            <div class="notes-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <input type="text"
                   name="catatan"
                   id="catatan"
                   class="notes-input"
                   value="{{ old('catatan') }}"
                   placeholder="Catatan tambahan untuk bagian Finance &amp; Akuntansi (opsional)...">
        </div>

        {{-- WAVEDASH STICKY DOCK FOOTER --}}
        <div class="ar-dock-footer">
            {{-- Section 1: Monitoring Alokasi --}}
            <div class="dock-section dock-saldo">
                <div class="dock-label">
                    <span class="dock-indicator-dot"></span>
                    <span id="dockCountSelected">0 Faktur Terpilih</span>
                </div>
                <div class="dock-saldo-content" id="sisaSaldoDisplay">
                    Pilih akun Kas/Bank untuk estimasi mutasi saldo
                </div>
                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;" id="dockSisaTagihan">
                    Sisa Tagihan Belum Terbayar: Rp 0
                </div>
            </div>

            {{-- Section 2: Total Kas Diterima (Rp Besar + Terbilang) --}}
            <div class="dock-section dock-total">
                <div class="dock-label">TOTAL KAS DITERIMA (BKM)</div>
                <div class="dock-total-wrapper">
                    <div class="dock-total-amount" id="dockTotalBayar">Rp 0</div>
                    <div class="dock-terbilang" id="dockTerbilang"># Nol Rupiah #</div>
                </div>
            </div>

            {{-- Section 3: Action Buttons --}}
            <div class="dock-section dock-actions">
                <a href="{{ route('keuangan.bayar_piutang.index') }}" class="btn-dock-cancel">
                    Batal
                </a>
                <button type="submit" class="btn-dock-submit" id="btnSubmitBayar">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan &amp; Terbitkan BKM</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            baseUrl: '{{ url('') }}',
            selectedCustomerId: '{{ old('customer_id', $customerId ?? '') }}',
            preselectedFakturId: '{{ $selectedFaktur ? $selectedFaktur->faktur_id : '' }}',
            customerList: @json($customerListData ?? [])
        };
    </script>
    <script src="{{ asset('js/keuangan/bayar_piutang/bayar-piutang-create.js') }}?v={{ file_exists(public_path('js/keuangan/bayar_piutang/bayar-piutang-create.js')) ? filemtime(public_path('js/keuangan/bayar_piutang/bayar-piutang-create.js')) : time() }}"></script>
@endpush
