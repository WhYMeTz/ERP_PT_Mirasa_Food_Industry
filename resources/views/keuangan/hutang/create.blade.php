@extends('layouts.app')

@section('title', 'Catat Tagihan Hutang Pembelian (AP)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/hutang/hutang-create.css') }}?v={{ filemtime(public_path('css/keuangan/hutang/hutang-create.css')) }}">
@endpush

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.hutang.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                &larr; Riwayat Hutang
            </a>
            <span style="color: #cbd5e1;">/</span>
            <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                + Catat Tagihan Pembelian (Invoice Masuk)
            </h1>
        </div>
        <p style="font-size: 0.8125rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Pilih supplier pemasok dan centang Surat Jalan / GRN yang hendak ditagihkan.
        </p>
    </div>

    <div>
        <a href="{{ route('keuangan.hutang.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem;">
            Batal
        </a>
    </div>
</div>

<form action="{{ route('keuangan.hutang.store') }}" method="POST" id="formCreateHutang">
    @csrf

    <div class="hutang-form-grid">
        {{-- SISI KIRI: PEMILIHAN REKANAN, GRN & TABEL RINCIAN --}}
        <div>
            {{-- KARTU 1: IDENTITAS REKANAN & DOKUMEN --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 0.85rem 0; display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 0.4rem;">
                        <span>🏢</span> <span>Pemasok &amp; Dokumen Tagihan</span>
                    </span>
                    @php
                        $totalUnbilledAll = array_sum($unbilledSupplierSummary ?? []);
                        $suppliersWithGrn = $suppliers->filter(fn($s) => ($unbilledSupplierSummary[$s->supplier_id] ?? 0) > 0);
                    @endphp
                    <span style="font-size: 0.75rem; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                        {{ $totalUnbilledAll }} GRN Belum Ditagih di Seluruh Supplier
                    </span>
                </h3>

                {{-- REKAP CEPAT SUPPLIER YANG MEMILIKI GRN SIAP TAGIH --}}
                @if($suppliersWithGrn->isNotEmpty())
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.85rem; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; font-weight: 800; color: #334155; margin-bottom: 0.4rem; display: flex; align-items: center; justify-content: space-between;">
                            <span style="display: flex; align-items: center; gap: 5px;">
                                <span style="color: #0284c7;">⚡</span>
                                <span>Supplier dengan GRN Siap Ditagih (Pilihan Cepat):</span>
                            </span>
                            <button type="button" 
                                    onclick="openSupplierModal()" 
                                    style="background: transparent; border: none; color: #0284c7; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                                Lihat Semua Rekanan &rarr;
                            </button>
                        </div>
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;" id="supplierQuickBadges">
                            @foreach($suppliersWithGrn as $sg)
                                @php
                                    $cnt = $unbilledSupplierSummary[$sg->supplier_id] ?? 0;
                                    $isActive = (old('supplier_id', $selectedSupplierId) == $sg->supplier_id);
                                @endphp
                                <button type="button" 
                                        class="btn-supplier-pill {{ $isActive ? 'active' : '' }}"
                                        onclick="selectSupplierFromModal({{ $sg->supplier_id }}, '{{ addslashes($sg->supplier_nm) }}', '{{ $sg->supplier_cd }}', {{ $cnt }}, '{{ addslashes($sg->kontak_no ?? '-') }}', '{{ addslashes($sg->alamat_txt ?? '-') }}', {{ (int)($sg->top_hari ?? 14) }})"
                                        data-supplier-id="{{ $sg->supplier_id }}"
                                        style="background: {{ $isActive ? '#0284c7' : '#ffffff' }}; 
                                               color: {{ $isActive ? '#ffffff' : '#0f172a' }}; 
                                               border: 1.5px solid {{ $isActive ? '#0284c7' : '#cbd5e1' }}; 
                                               border-radius: 6px; 
                                               padding: 4px 10px; 
                                               font-size: 0.75rem; 
                                               font-weight: 700; 
                                               cursor: pointer; 
                                               display: inline-flex; 
                                               align-items: center; 
                                               gap: 6px; 
                                               transition: all 0.15s ease;">
                                    <span>{{ $sg->supplier_nm }}</span>
                                    <span style="background: {{ $isActive ? 'rgba(255,255,255,0.25)' : '#dbeafe' }}; 
                                                 color: {{ $isActive ? '#ffffff' : '#1e40af' }}; 
                                                 padding: 1px 6px; 
                                                 border-radius: 10px; 
                                                 font-size: 0.7rem; 
                                                 font-weight: 800;">
                                        {{ $cnt }} GRN
                                    </span>
                                    <span style="background: {{ $isActive ? 'rgba(255,255,255,0.2)' : '#f1f5f9' }}; 
                                                 color: {{ $isActive ? '#ffffff' : '#475569' }}; 
                                                 padding: 1px 5px; 
                                                 border-radius: 4px; 
                                                 font-size: 0.68rem; 
                                                 font-weight: 700;">
                                        TOP {{ $sg->top_hari ?? 14 }}h
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- WIDGET PEMILIHAN SUPPLIER (PLACEHOLDER / ACTIVE CARD) --}}
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Mitra Supplier / Petani Pemasok <span style="color: #dc2626;">*</span>
                    </label>

                    <input type="hidden" name="supplier_id" id="selectedSupplierIdInput" value="{{ old('supplier_id', $selectedSupplierId) }}" required>

                    @php
                        $initSupp = (old('supplier_id', $selectedSupplierId)) ? $suppliers->firstWhere('supplier_id', old('supplier_id', $selectedSupplierId)) : null;
                    @endphp

                    {{-- BOX PLACEHOLDER (JIKA BELUM DIPILIH) --}}
                    <div id="supplierPlaceholderBox" 
                         class="supplier-placeholder-box" 
                         onclick="openSupplierModal()" 
                         style="display: {{ $initSupp ? 'none' : 'flex' }};">
                        <div class="supplier-placeholder-icon">🔍</div>
                        <div class="supplier-placeholder-text">
                            <strong style="color: #0284c7; font-size: 0.925rem;">Klik di sini untuk Memilih Mitra Supplier</strong>
                            <span style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                Terdapat <strong>{{ $suppliersWithGrn->count() }} supplier</strong> dengan total <strong>{{ $totalUnbilledAll }} penerimaan barang (GRN)</strong> siap ditagihkan.
                            </span>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" style="font-weight: 700; font-size: 0.8rem; pointer-events: none;">
                            Buka Pop-up &rarr;
                        </button>
                    </div>

                    {{-- ACTIVE SUPPLIER CARD (JIKA SUDAH DIPILIH) --}}
                    <div id="supplierActiveCard" 
                         class="supplier-active-card" 
                         style="display: {{ $initSupp ? 'flex' : 'none' }};">
                        <div style="display: flex; align-items: center; gap: 0.85rem;">
                            <div style="font-size: 1.5rem; background: #eff6ff; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #bfdbfe; flex-shrink: 0;">
                                🏢
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <span id="activeSupplierCode" class="supplier-modal-code">{{ $initSupp?->supplier_cd ?? '-' }}</span>
                                    <strong id="activeSupplierName" style="font-size: 0.95rem; color: #0f172a;">{{ $initSupp?->supplier_nm ?? '-' }}</strong>
                                    <span id="activeSupplierBadge" class="badge-grn-count" style="display: {{ ($unbilledSupplierSummary[$initSupp?->supplier_id ?? 0] ?? 0) > 0 ? 'inline-block' : 'none' }};">
                                        ⚡ {{ $unbilledSupplierSummary[$initSupp?->supplier_id ?? 0] ?? 0 }} GRN Siap Tagih
                                    </span>
                                    <span id="activeSupplierTopBadge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.725rem; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                                        ⏱️ Default TOP: <span id="activeSupplierTopDays">{{ $initSupp?->top_hari ?? 14 }}</span> Hari
                                    </span>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 3px; display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <span id="activeSupplierContact">📞 {{ $initSupp?->kontak_no ?: 'Belum ada kontak' }}</span>
                                    <span id="activeSupplierAddress">📍 {{ \Illuminate\Support\Str::limit($initSupp?->alamat_txt ?: 'Alamat belum diatur', 45) }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary btn-sm" onclick="openSupplierModal()" style="font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Ganti Supplier</span>
                        </button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            No. Faktur / Nota Supplier
                        </label>
                        <input type="text" 
                               name="no_faktur_supplier" 
                               id="noFakturSupplierInput"
                               value="{{ old('no_faktur_supplier') }}" 
                               class="form-control" 
                               placeholder="Contoh: INV-SUP-2026/099" 
                               style="font-size: 0.85rem; height: 38px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Tagihan <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" 
                               id="tglTagihanInput"
                               name="tgl_tagihan" 
                               value="{{ old('tgl_tagihan', date('Y-m-d')) }}" 
                               class="form-control" 
                               style="font-size: 0.85rem; height: 38px;" 
                               required>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tempo Bayar (TOP) <span style="color: #dc2626;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="number" 
                                   id="topHariInput"
                                   name="top_hari" 
                                   value="{{ old('top_hari', $initSupp?->top_hari ?? 14) }}" 
                                   min="0" max="365"
                                   class="form-control" 
                                   style="font-size: 0.85rem; height: 38px; padding-right: 2.75rem; font-weight: 700; color: #0284c7;" 
                                   required>
                            <span style="position: absolute; right: 10px; top: 9px; font-size: 0.75rem; color: #64748b; font-weight: 700; pointer-events: none;">Hari</span>
                        </div>
                        <small id="topDefaultHint" style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 3px;">
                            Default: <strong id="topDefaultDaysText" style="color: #0369a1;">{{ $initSupp?->top_hari ?? 14 }} Hari</strong>
                        </small>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Jatuh Tempo <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" 
                               id="tglJatuhTempoInput"
                               name="tgl_jatuh_tempo" 
                               value="{{ old('tgl_jatuh_tempo', date('Y-m-d', strtotime('+' . ($initSupp?->top_hari ?? 14) . ' days'))) }}" 
                               class="form-control" 
                               style="font-size: 0.85rem; height: 38px; font-weight: 700; color: #b45309;" 
                               required>
                        <small id="jatuhTempoHelpText" style="color: #0369a1; font-size: 0.725rem; display: block; margin-top: 3px; font-weight: 600;">
                            ⏱️ Otomatis: Tgl Tagihan + <span id="helpTextTopHari">{{ $initSupp?->top_hari ?? 14 }}</span> Hari (TOP Supplier)
                        </small>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: PICKER SURAT JALAN / GRN YANG BELUM DITAGIH DENGAN TOOLBAR & TABEL RINGKAS --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                            <span>📦</span> <span>Pilih Penerimaan Barang (GRN) yang Ditagihkan</span>
                        </h3>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                            Centang Surat Jalan di bawah untuk memasukkan item barang secara otomatis
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.4rem; align-items: center;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="selectAllGrn()" style="font-size: 0.75rem; font-weight: 700;">
                            ☑️ Pilih Semua
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllGrn()" style="font-size: 0.75rem; font-weight: 700;">
                            ⬜ Batal Pilih
                        </button>
                    </div>
                </div>

                {{-- TOOLBAR PENCARIAN & FILTER GRN --}}
                <div class="grn-toolbar" id="grnToolbar" style="display: none;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 220px;">
                        <input type="text" 
                               id="grnSearchInput" 
                               class="form-control" 
                               placeholder="🔍 Cari No. GRN, Surat Jalan, atau Komoditas..." 
                               style="height: 32px; font-size: 0.775rem; border-radius: 6px;"
                               oninput="filterGrnList()">
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">Periode:</span>
                        <button type="button" class="grn-date-filter-btn active" data-filter="all" onclick="filterGrnByDate('all', this)">Semua</button>
                        <button type="button" class="grn-date-filter-btn" data-filter="7days" onclick="filterGrnByDate('7days', this)">7 Hari Terakhir</button>
                        <button type="button" class="grn-date-filter-btn" data-filter="month" onclick="filterGrnByDate('month', this)">Bulan Ini</button>
                    </div>
                </div>

                {{-- LOADING & EMPTY STATE --}}
                <div id="grnLoadingIndicator" style="display: none; text-align: center; padding: 2rem; color: #0284c7; font-weight: 700; font-size: 0.85rem;">
                    ⏳ Mengambil data penerimaan barang...
                </div>
                
                <div id="grnEmptyState" style="text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.85rem; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px;">
                    Silakan pilih mitra supplier di atas terlebih dahulu untuk menampilkan daftar GRN yang belum ditagihkan.
                </div>

                {{-- TABEL KOMPAK GRN SCROLLABLE --}}
                <div id="grnTableWrapper" class="grn-table-wrapper" style="display: none;">
                    <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8rem;">
                        <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 2; border-bottom: 1.5px solid #e2e8f0;">
                            <tr style="color: #475569; font-size: 0.725rem; text-transform: uppercase;">
                                <th style="padding: 0.6rem 0.75rem; width: 40px; text-align: center;">
                                    <input type="checkbox" id="headerMasterCheck" onchange="toggleMasterCheck(this)" style="cursor: pointer; accent-color: #0284c7;">
                                </th>
                                <th style="padding: 0.6rem 0.5rem; width: 180px;">No. Dokumen (GRN / SJ)</th>
                                <th style="padding: 0.6rem 0.5rem; width: 110px;">Tanggal Masuk</th>
                                <th style="padding: 0.6rem 0.5rem;">Komoditas &amp; Fisik</th>
                                <th style="padding: 0.6rem 0.75rem; text-align: right; width: 150px;">Estimasi Nilai</th>
                            </tr>
                        </thead>
                        <tbody id="grnTbodyContainer">
                            {{-- Di-render via JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- STATUS BAR SELEKSI GRN --}}
                <div id="grnStatusBar" class="grn-status-bar" style="display: none;">
                    <div>
                        Menampilkan <strong id="grnShownCount">0</strong> penerimaan barang
                    </div>
                    <div>
                        <strong id="grnSelectedCount" style="color: #0284c7;">0</strong> GRN dipilih &bull; Total Estimasi: <strong id="grnSelectedTotal" style="color: #16a34a;">Rp 0</strong>
                    </div>
                </div>
            </div>

            {{-- KARTU 3: RINCIAN ITEM BARANG YANG DITAGIHKAN --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 0.85rem 0; display: flex; align-items: center; gap: 0.4rem;">
                    <span>📝</span> <span>Rincian Komoditas &amp; Harga Satuan</span>
                </h3>

                <div style="overflow-x: auto;">
                    <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase;">
                                <th style="padding: 0.75rem 0.75rem;">Deskripsi / Komoditas</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right; width: 105px;">Kuantitas</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: center; width: 60px;">Satuan</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right; width: 125px;">Harga Satuan (Rp)</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right; width: 165px;">Diskon (% / Rp)</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right; width: 120px;">Potongan Mutu (Rp)</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: left; width: 175px;">
                                    <span>Perlakuan PPN</span>
                                    <span style="font-size: 0.68rem; color: #64748b; font-weight: 500; display: block; text-transform: none;">Non / +11% / Include</span>
                                </th>
                                <th style="padding: 0.75rem 0.75rem; text-align: right; width: 130px;">Netto (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr id="emptyItemsRow">
                                <td colspan="8" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.825rem;">
                                    Centang salah satu atau beberapa Surat Jalan / GRN di atas untuk memuat rincian tagihan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- KARTU 4: ONGKOS BONGKAR / ANGKUT (TONASE X TARIF) & UANG MUKA (DP) --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
                    {{-- BAGIAN A: ONGKOS ANGKUT / BONGKAR MUAT --}}
                    <div style="background: #faf5ff; border: 1.5px solid #e9d5ff; border-radius: 8px; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="font-size: 1.1rem;">🚚</span>
                                <strong style="font-size: 0.875rem; color: #581c87;">Ongkos Kirim / Upah Bongkar Muat</strong>
                            </div>
                            <span style="font-size: 0.7rem; background: #f3e8ff; color: #7e22ce; padding: 2px 7px; border-radius: 4px; font-weight: 800; border: 1px solid #d8b4fe;">
                                Akun 6112 Beban Angkut
                            </span>
                        </div>
                        <div style="font-size: 0.75rem; color: #6b21a8; margin-bottom: 0.85rem; line-height: 1.4;">
                            Hitung otomatis dari tonase barang yang diturunkan atau masukkan nominal borongan:
                        </div>

                        {{-- PILIHAN MODE KALKULASI --}}
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 4px; font-size: 0.75rem; font-weight: 700; color: #581c87; cursor: pointer; background: #ffffff; padding: 4px 8px; border-radius: 6px; border: 1px solid #d8b4fe;">
                                <input type="radio" name="ongkir_mode" id="ongkirModeTonase" value="tonase" checked onchange="toggleOngkirMode('tonase')">
                                <span>Hitung Tonase (KG) &times; Tarif</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; font-size: 0.75rem; font-weight: 700; color: #581c87; cursor: pointer; background: #ffffff; padding: 4px 8px; border-radius: 6px; border: 1px solid #d8b4fe;">
                                <input type="radio" name="ongkir_mode" id="ongkirModeManual" value="manual" onchange="toggleOngkirMode('manual')">
                                <span>Nominal Tetap / Flat</span>
                            </label>
                        </div>

                        {{-- MODE 1: TONASE X TARIF --}}
                        <div id="ongkirTonaseBox" style="display: block;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.65rem;">
                                <div>
                                    <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #6b21a8; margin-bottom: 0.25rem;">
                                        Total Tonase Netto (KG)
                                    </label>
                                    <div style="position: relative;">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               name="tonase_kg" 
                                               id="tonaseKgInput" 
                                               value="{{ old('tonase_kg', 0) }}" 
                                               class="form-control" 
                                               style="font-size: 0.825rem; height: 34px; padding-right: 2.25rem; font-weight: 700; color: #581c87; text-align: right;" 
                                               placeholder="0"
                                               oninput="calculateOngkirFromTonase()">
                                        <span style="position: absolute; right: 8px; top: 8px; font-size: 0.725rem; color: #7e22ce; font-weight: 700; pointer-events: none;">KG</span>
                                    </div>
                                    <small style="font-size: 0.68rem; color: #7e22ce; display: block; margin-top: 2px;">
                                        Akumulasi fisik lolos QC
                                    </small>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #6b21a8; margin-bottom: 0.25rem;">
                                        Tarif Upah / KG (Rp)
                                    </label>
                                    <div style="position: relative;">
                                        <input type="number" 
                                               step="any" 
                                               min="0" 
                                               name="ongkir_tarif_per_kg" 
                                               id="ongkirTarifPerKgInput" 
                                               value="{{ old('ongkir_tarif_per_kg', 0) }}" 
                                               class="form-control" 
                                               style="font-size: 0.825rem; height: 34px; padding-right: 2.75rem; font-weight: 700; color: #581c87; text-align: right;" 
                                               placeholder="150"
                                               oninput="calculateOngkirFromTonase()">
                                        <span style="position: absolute; right: 8px; top: 8px; font-size: 0.7rem; color: #7e22ce; font-weight: 700; pointer-events: none;">/ KG</span>
                                    </div>
                                    <small style="font-size: 0.68rem; color: #7e22ce; display: block; margin-top: 2px;">
                                        Contoh: Rp 150 atau Rp 200
                                    </small>
                                </div>
                            </div>

                            {{-- PREVIEW RUMUS DINAMIS --}}
                            <div style="background: #ffffff; border: 1px dashed #d8b4fe; border-radius: 6px; padding: 0.5rem 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem;">
                                <span style="color: #6b21a8; font-weight: 600;">Rumus Kalkulasi:</span>
                                <span id="ongkirFormulaText" style="font-weight: 800; color: #581c87;">
                                    0 KG &times; Rp 0 = Rp 0
                                </span>
                            </div>
                        </div>

                        {{-- INPUT NOMINAL ONGKIR (AKTIF BISA DIEDIT KEDUA MODE) --}}
                        <div style="margin-top: 0.65rem;">
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #6b21a8; margin-bottom: 0.25rem;">
                                Total Biaya Ongkos Angkut yang Ditagihkan (Rp)
                            </label>
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="biaya_ongkir" 
                                   id="biayaOngkirInput" 
                                   value="{{ old('biaya_ongkir', 0) }}" 
                                   class="form-control" 
                                   style="font-size: 0.875rem; height: 34px; text-align: right; font-weight: 800; color: #6b21a8; background: #ffffff;" 
                                   placeholder="0">
                        </div>
                    </div>

                    {{-- BAGIAN B: UANG MUKA / DOWN PAYMENT (DP) --}}
                    <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="font-size: 1.1rem;">💳</span>
                                <strong style="font-size: 0.875rem; color: #14532d;">Uang Muka / DP yang Sudah Dibayar</strong>
                            </div>
                            <span style="font-size: 0.7rem; background: #dcfce7; color: #15803d; padding: 2px 7px; border-radius: 4px; font-weight: 800; border: 1px solid #86efac;">
                                Potong Hutang (2100)
                            </span>
                        </div>
                        <div style="font-size: 0.75rem; color: #166534; margin-bottom: 0.85rem; line-height: 1.4;">
                            Jika pembelian ini sebelumnya telah dibayar uang muka (DP), masukkan nominal di bawah untuk langsung mengurangi sisa hutang:
                        </div>

                        <div style="margin-bottom: 0.75rem;">
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #14532d; margin-bottom: 0.25rem;">
                                Nominal Uang Muka / DP Terbayar (Rp)
                            </label>
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="uang_muka_nominal" 
                                   id="uangMukaNominalInput" 
                                   value="{{ old('uang_muka_nominal', 0) }}" 
                                   class="form-control" 
                                   style="font-size: 0.875rem; height: 34px; text-align: right; font-weight: 800; color: #15803d; background: #ffffff;" 
                                   placeholder="0"
                                   oninput="recalculateTotals()">
                        </div>

                        <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.5rem 0.75rem; font-size: 0.72rem; color: #166534; line-height: 1.4;">
                            💡 <strong>Informasi Jurnal:</strong> Pengeluaran kas DP dicatat saat kas keluar (Debet: <code>2100 Hutang Usaha</code>, Kredit: <code>1103 Bank BCA</code>). Tidak memerlukan COA baru sehingga tidak perlu izin perubahan bagan akun.
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU 5: CATATAN TAMBAHAN --}}
            <div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Catatan / Keterangan Tagihan
                </label>
                <textarea name="catatan" 
                          rows="2" 
                          class="form-control" 
                          placeholder="Catatan termin pembayaran, transfer bank supplier, dsb..." 
                          style="font-size: 0.8125rem;">{{ old('catatan') }}</textarea>
            </div>
        </div>

        {{-- SISI KANAN: STICKY SUMMARY & EKSEKUSI SIMPAN --}}
        <div>
            <div class="summary-sticky-card">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                    Ringkasan Tagihan
                </h3>

                <div class="summary-row">
                    <span>Total Bruto</span>
                    <strong id="summaryTotalBruto" style="color: #0f172a;">Rp 0</strong>
                </div>

                <div class="summary-row">
                    <span>Total Diskon</span>
                    <strong id="summaryTotalDiskon" style="color: #16a34a;">Rp 0</strong>
                </div>

                <div class="summary-row" id="rowSummaryPotonganItem" style="display: none; color: #b45309;">
                    <span>Total Potongan Mutu</span>
                    <strong id="summaryTotalPotonganItem" style="color: #b45309;">- Rp 0</strong>
                </div>

                <div class="summary-row">
                    <span>PPN</span>
                    <strong id="summaryTotalPpn" style="color: #0284c7;">Rp 0</strong>
                </div>

                <div style="padding: 0.75rem 0; border-bottom: 1px dashed #e2e8f0;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #b45309; margin-bottom: 0.25rem;">
                        Potongan Retur Barang Rusak (Rp)
                    </label>
                    <input type="number" 
                           step="any" 
                           min="0" 
                           name="total_potongan_retur" 
                           id="potonganReturInput" 
                           value="0" 
                           class="form-control" 
                           style="font-size: 0.85rem; height: 34px; text-align: right; font-weight: 700; color: #dc2626;" 
                           placeholder="0">
                    <div style="font-size: 0.68rem; color: #64748b; margin-top: 3px;">
                        Kredit nota jika ada bahan afkir/reject yang dipotongkan.
                    </div>
                </div>

                <div class="summary-row" id="rowSummaryOngkir" style="display: none; color: #7e22ce; padding: 0.5rem 0; border-bottom: 1px dashed #e9d5ff;">
                    <span>+ Ongkos Angkut (6112)</span>
                    <strong id="summaryBiayaOngkir" style="color: #7e22ce;">Rp 0</strong>
                </div>

                <div class="summary-row total-row" style="margin-top: 0.5rem;">
                    <span>Total Tagihan (Faktur)</span>
                    <strong id="summaryGrandTotal" style="color: #0284c7;">Rp 0</strong>
                </div>

                {{-- POTONGAN UANG MUKA / DP --}}
                <div class="summary-row" id="rowSummaryUangMuka" style="display: none; color: #15803d; padding: 0.45rem 0; border-bottom: 1px dashed #bbf7d0;">
                    <span>- Uang Muka / DP Dibayar</span>
                    <strong id="summaryUangMukaNominal" style="color: #15803d;">Rp 0</strong>
                </div>

                {{-- SISA HUTANG YANG HARUS DIBAYAR --}}
                <div id="boxSummarySisaHutang" style="margin-top: 0.75rem; padding: 0.75rem 0.85rem; background: #fef2f2; border-radius: 8px; border: 1.5px solid #fecaca; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.725rem; font-weight: 800; color: #991b1b; text-transform: uppercase;">
                            Sisa Hutang Wajib Bayar
                        </div>
                        <div style="font-size: 0.68rem; color: #b91c1c;">
                            Setelah dikurangi DP terbayar
                        </div>
                    </div>
                    <strong id="summarySisaHutang" style="font-size: 1.15rem; font-weight: 900; color: #dc2626;">
                        Rp 0
                    </strong>
                </div>

                <div style="margin-top: 1.25rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 800; font-size: 0.9rem; border-radius: 8px; box-shadow: 0 3px 6px rgba(2, 132, 199, 0.3);">
                        💾 Terbitkan Tagihan Hutang
                    </button>
                    <div style="font-size: 0.72rem; color: #64748b; text-align: center; margin-top: 0.5rem; line-height: 1.3;">
                        Tagihan akan masuk ke daftar hutang dan siap dijadwalkan pelunasan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- MODAL PEMILIH SUPPLIER --}}
@include('keuangan.hutang.partials.modal-supplier-picker')

@endsection

@push('scripts')
<script>
    window.appConfig = {
        baseUrl: '{{ url('') }}',
        initialTopHari: {{ (int)($initSupp?->top_hari ?? 14) }}
    };
</script>
<script src="{{ asset('js/keuangan/hutang/hutang-create.js') }}?v={{ filemtime(public_path('js/keuangan/hutang/hutang-create.js')) }}"></script>
@endpush
