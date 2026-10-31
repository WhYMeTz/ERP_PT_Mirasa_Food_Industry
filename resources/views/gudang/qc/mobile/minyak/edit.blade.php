@extends('layouts.qc-mobile')

@section('title', 'Koreksi Sampling QC Minyak: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}?v={{ filemtime(public_path('css/gudang/qc/mobile/qc-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}?v={{ filemtime(public_path('css/gudang/qc/mobile/qc-mobile-detail.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak.css') }}?v={{ filemtime(public_path('css/gudang/qc/minyak/minyak.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak-detail.css') }}?v={{ filemtime(public_path('css/gudang/qc/minyak/minyak-detail.css')) }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;

    // Filter daftar PO yang memuat Minyak
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (str_contains($nm, 'MINYAK') || str_starts_with($cd, 'BP-MY') || str_starts_with($cd, 'MSW') || str_starts_with($cd, 'MKP')) {
            return 'MINYAK';
        }
        return 'LAINNYA';
    };

    $filteredPos = $pos->filter(function($p) use ($qc, $classifyBarang) {
        if ($p->po_id == $qc->po_id) return true;
        foreach ($p->details as $d) {
            if ($classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd) === 'MINYAK') {
                return true;
            }
        }
        return false;
    });

    if ($filteredPos->isEmpty() && $qc->po) {
        $filteredPos = collect([$qc->po]);
    }

    // Filter Supplier khusus Minyak (Non-Petani)
    $filteredSuppliers = $suppliers->filter(function($s) use ($qc) {
        if ($s->supplier_id == $qc->supplier_id) return true;
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($filteredSuppliers->isEmpty()) {
        $filteredSuppliers = $suppliers;
    }

    // Filter Master Barang khusus Minyak
    $filteredBarangs = $barangs->filter(function($b) {
        $nm = strtoupper($b->barang_nm);
        $cd = strtoupper($b->barang_cd);
        return str_contains($nm, 'MINYAK') || str_starts_with($cd, 'BP-MY') || str_starts_with($cd, 'MSW') || str_starts_with($cd, 'MKP');
    });
    if ($filteredBarangs->isEmpty()) {
        $filteredBarangs = $barangs;
    }

    $barangId = $firstDetail?->barang_id ?? ($filteredBarangs->first()?->barang_id);
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    $totalReject = (float) $qc->details->sum('qty_reject');

    // Satuan
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

    $ref = request('ref');
    if ($ref === 'detail') {
        $backUrl = route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']);
        $backLabel = 'Batal ke Detail';
    } else {
        $backUrl = route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'MINYAK']);
        $backLabel = 'Batal ke Riwayat';
    }
@endphp

@section('content')
<div class="qc-detail-wrap" style="max-width: 680px;">

    {{-- TOP APP NAV --}}
    <div class="qc-detail-top-nav">
        <a href="{{ $backUrl }}" class="qc-detail-back-btn" title="{{ $backLabel }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Batal &amp; Kembali</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn-qc-switch-desktop" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; background: #e0f2fe; border: 1.5px solid #bae6fd; padding: 0.35rem 0.65rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;" title="Kembali ke Tabel QC Web Desktop">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Tabel Web QC</span>
                </a>
            @endif
            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
                QC Minyak &bull; Edit
            </span>
        </div>
    </div>

    {{-- HEADER CARD MOBILE --}}
    <div class="qc-detail-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #b45309 100%);">
        <div class="qc-detail-hero-top">
            <div>
                <span class="badge-tag-commodity" style="background: rgba(245, 158, 11, 0.4); color: #fef3c7;">
                    🛢️ MINYAK GORENG
                </span>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem; color: #ffffff;">Edit Tiket #{{ $qc->qc_no }}</h1>
            </div>
            <span style="background: #fef08a; color: #854d0e; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 20px;">
                Mode Mobile
            </span>
        </div>
        <p style="font-size: 0.8rem; margin: 0.4rem 0 0; opacity: 0.85; line-height: 1.4;">
            Koreksi parameter sampling mutu minyak, uji FFA di COA/lab, kondisi wadah tangki, atau kuantitas kedatangan.
        </p>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-error" style="margin-bottom: 1rem; border-radius: 10px; background: #fef2f2; border: 1.5px solid #fecaca; padding: 0.75rem 1rem; color: #991b1b;">
            <div style="font-weight: 800; font-size: 0.85rem; margin-bottom: 0.25rem;">⚠️ Periksa isian berikut:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UTAMA MOBILE EDIT MINYAK --}}
    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditMinyakForm" style="display: flex; flex-direction: column; gap: 1rem;">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="ref" value="{{ $ref ?: 'index' }}">
        <input type="hidden" name="kategori_barang" value="MINYAK">
        <input type="hidden" name="batch_no" value="{{ old('batch_no', $qc->batch_no) }}">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">
        <input type="hidden" name="status_uji_goreng" value="SELESAI">
        <input type="hidden" name="items[{{ $qcdtlId }}][qcdtl_id]" value="{{ $qcdtlId }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][podtl_id]" value="{{ $firstDetail?->podtl_id }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][refraksi_persen]" value="0">

        {{-- KARTU 1: INFO PENGIRIMAN, PO & VENDOR MINYAK --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🚚</span> <span>1. Info Armada, PO &amp; Vendor Minyak</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                {{-- PILIHAN PO KHUSUS MINYAK --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <label class="qc-info-label" style="font-weight: 700; margin: 0;">Referensi Purchase Order (PO)</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                            PO Minyak ({{ $filteredPos->count() }} Tersedia)
                        </span>
                    </div>
                    <select name="po_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        <option value="">-- Tanpa PO / Kiriman Langsung Minyak --</option>
                        @foreach ($filteredPos as $po)
                            @php
                                $totPesan = (float) $po->details->sum('pesan_qty');
                                $terimaIni = ($po->po_id == $qc->po_id && $qc->terima) ? (float) $qc->terima->details->sum('terima_qty') : 0;
                                $sudahMsk = max(0, (float) $po->details->sum('terima_qty') - $terimaIni);
                                $sisaKta = max(0, $totPesan - $sudahMsk);
                            @endphp
                            <option value="{{ $po->po_id }}" {{ old('po_id', $qc->po_id) == $po->po_id ? 'selected' : '' }}>
                                {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} (Sisa: {{ number_format($sisaKta, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- MITRA VENDOR / SUPPLIER & GUDANG --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 700; margin: 0;">🏢 Mitra Vendor / Supplier Minyak *</label>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                                Vendor Minyak
                            </span>
                        </div>
                        <div style="margin-bottom: 0.4rem;">
                            <input type="text" id="editSupplierSearch" class="form-control" placeholder="🔍 Cari vendor supplier minyak..." oninput="filterEditSuppliers(this.value)" style="min-height: 38px; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 6px;">
                        </div>
                        <select name="supplier_id" id="editSupplierSelect" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required onchange="onEditSupplierChange(this)">
                            @foreach ($filteredSuppliers as $sup)
                                <option value="{{ $sup->supplier_id }}"
                                        data-supplier-nm="{{ $sup->supplier_nm }}"
                                        {{ old('supplier_id', $qc->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                    {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                        <div>
                            <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.25rem;">Pabrik Produsen</label>
                            <input type="text" name="nama_produsen" id="editNamaProdusen" value="{{ old('nama_produsen', $qc->nama_produsen ?? $qc->supplier?->supplier_nm) }}" class="form-control" placeholder="PT Sari Agro Mandiri" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        </div>
                        <div>
                            <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.25rem;">Negara Produsen</label>
                            <input type="text" name="negara_produsen" value="{{ old('negara_produsen', $qc->negara_produsen ?? 'Indonesia') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        </div>
                    </div>

                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.25rem;">🏢 Gudang / Tangki Penampungan *</label>
                        <select name="gudang_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} @if(!empty($g->tipe_gudang_cd) && $g->tipe_gudang_cd !== 'Pusat') ({{ $g->tipe_gudang_cd }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- PILIH MASTER BARANG & NAMA JENIS SPESIFIKASI --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Komoditas Minyak (Master Data) *</label>
                        <select name="items[{{ $qcdtlId }}][barang_id]" id="editBarangSelect" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #b45309;" required onchange="onEditBarangChanged(this); if(typeof onMinyakBarangChanged === 'function') onMinyakBarangChanged(this);">
                            @foreach ($filteredBarangs as $b)
                                <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}" {{ old('items.'.$qcdtlId.'.barang_id', $barangId) == $b->barang_id ? 'selected' : '' }}>
                                    {{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'kg' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 800; margin: 0;">NAMA JENIS : (Spesifikasi Fraksi / Grade)</label>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 1px 6px; border-radius: 4px;">Form HACCP MFI</span>
                        </div>
                        <input type="text" name="nama_jenis" id="editNamaJenis" list="listEditMinyakJenis" value="{{ old('nama_jenis', $qc->nama_jenis ?? 'RBD Palm Olein / Curah Sawit') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800;" placeholder="RBD Palm Olein (CP8) / Curah Sawit / Minyak Kelapa">
                        <datalist id="listEditMinyakJenis">
                            <option value="RBD Palm Olein (CP8)">
                            <option value="RBD Palm Olein (CP10)">
                            <option value="Minyak Curah Kelapa Sawit">
                            <option value="Minyak Kelapa (RBD CNO)">
                            <option value="Minyak Sawit Super (Filma)">
                            <option value="Minyak Sawit Jerigen Sania">
                        </datalist>
                    </div>
                </div>

                {{-- SURAT JALAN, PLAT NOMOR, SOPIR, WAKTU --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">No. DO (Surat Jalan)</label>
                        <input type="text" name="nomor_do" id="inputNomorDo" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" oninput="const sjEl = document.getElementById('inputSuratJalanSupplier'); if(sjEl) sjEl.value = this.value;">
                        <input type="hidden" name="surat_jalan_supplier" id="inputSuratJalanSupplier" value="{{ old('surat_jalan_supplier', $qc->surat_jalan_supplier ?? $qc->nomor_do) }}">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Plat Truk / Tangki</label>
                        <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; text-transform: uppercase;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Nama Sopir</label>
                        <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" style="width: 100%; border-radius: 8px;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Tanggal Datang</label>
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="form-control" style="width: 100%; border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
                    </div>
                </div>

                {{-- JUMLAH SAMPEL QC --}}
                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Sampel QC Diambil (Gram)</label>
                    <input type="number" step="any" name="jumlah_sample_gr" value="{{ old('jumlah_sample_gr', $qc->jumlah_sample_gr ?? 500) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #0284c7;" placeholder="500">
                </div>

            </div>
        </div>

        {{-- KARTU 2: HALAL & HIGIENE TRANSPORTASI (SISTEM JAMINAN HALAL / SJH) --}}
        <div class="qc-card-section" style="border: 1.5px solid #bbf7d0; background: #ffffff; border-radius: 12px; overflow: hidden; padding: 0;">
            <div style="background: #f0fdf4; padding: 0.85rem 1rem; border-bottom: 1px solid #dcfce7; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #166534; color: #ffffff; font-weight: 800; font-size: 0.82rem;">2</span>
                    <div>
                        <h2 style="font-size: 0.95rem; font-weight: 800; color: #166534; margin: 0; line-height: 1.25;">Halal &amp; Higiene Transportasi</h2>
                        <span style="font-size: 0.72rem; color: #15803d; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; CCP Titik Kritis</span>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🛡️</span>
            </div>

            <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- ITEM 1: KONDISI TRANSPORTASI (TRUK TANGKI / KONTAINER) --}}
                <div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 0.45rem;">
                        1. Kondisi Transportasi (Truk Tangki / Kontainer)
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        {{-- Option 1: Bebas Cemaran --}}
                        <label class="transport-radio-card {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'active' : '' }}" id="cardTransportBebas">
                            <input type="radio" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'checked' : '' }} onchange="updateTransportCard(this)">
                            <div class="transport-card-text">
                                <div class="transport-card-title" style="color: #15803d;">Tidak ada cemaran, Najis / Kotoran</div>
                                <div class="transport-card-desc">Truk bersih, segel utuh, tidak berbau asing</div>
                            </div>
                        </label>

                        {{-- Option 2: Ada Cemaran --}}
                        <label class="transport-radio-card is-danger {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 0 ? 'active' : '' }}" id="cardTransportCemar">
                            <input type="radio" name="bebas_cemaran_st" value="0" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 0 ? 'checked' : '' }} onchange="updateTransportCard(this)">
                            <div class="transport-card-text">
                                <div class="transport-card-title" style="color: #dc2626;">Ada cemaran / Kotor / Najis</div>
                                <div class="transport-card-desc">Ditemukan indikasi kontaminasi pada armada</div>
                            </div>
                        </label>
                    </div>
                </div>

                @php
                    $angkutHaramVal = (int) old('angkut_barang_haram_st', $qc->angkut_barang_haram_st ? 1 : 0);
                    $terdaftarLppomVal = (int) old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ?? 1);
                    $adaSertifikatVal = (int) old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ?? 1);
                    $sertifikatBerlakuVal = (int) old('sertifikat_halal_berlaku_st', $qc->sertifikat_halal_berlaku_st ?? 1);
                @endphp

                {{-- ITEM 2: DIANGKUT BERSAMA DENGAN BARANG HARAM? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">2. Apakah barang tersebut diangkut bersama dengan barang haram?</div>
                            <div class="halal-qa-sub">Persyaratan mutlak integrasi logistik halal</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $angkutHaramVal == 0 ? 'active ok' : '' }}" id="btnHaramTidak" onclick="setHalalToggle('angkut_barang_haram', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ $angkutHaramVal == 1 ? 'active danger' : '' }}" id="btnHaramYa" onclick="setHalalToggle('angkut_barang_haram', 1)">Ya</button>
                        </div>
                        <input type="hidden" name="angkut_barang_haram_st" id="input_angkut_barang_haram" value="{{ $angkutHaramVal }}">
                    </div>
                    <input type="text" name="komentar_transportasi" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_transportasi', $qc->komentar_transportasi) }}" style="font-size: 0.85rem;">
                </div>

                {{-- ITEM 3: TERDAFTAR & DISETUJUI OLEH LPPOM MUI/BPJPH? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">3. Apakah bahan tersebut terdaftar &amp; disetujui oleh LPPOM MUI/BPJPH?</div>
                            <div class="halal-qa-sub">Cek daftar bahan approved list</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $terdaftarLppomVal == 0 ? 'active danger' : '' }}" id="btnLppomTidak" onclick="setHalalToggle('terdaftar_lppom', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ $terdaftarLppomVal == 1 ? 'active ok' : '' }}" id="btnLppomYa" onclick="setHalalToggle('terdaftar_lppom', 1)">Ya</button>
                        </div>
                        <input type="hidden" name="terdaftar_lppom_st" id="input_terdaftar_lppom" value="{{ $terdaftarLppomVal }}">
                    </div>
                    <input type="text" name="komentar_lppom" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_lppom', $qc->komentar_lppom) }}" style="font-size: 0.85rem;">
                </div>

                {{-- ITEM 4: MEMPUNYAI SERTIFIKAT HALAL? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">4. Apakah barang tersebut mempunyai sertifikat halal?</div>
                            <div class="halal-qa-sub">Dokumen sertifikat halal resmi</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $adaSertifikatVal == 0 ? 'active danger' : '' }}" id="btnSertifikatTidak" onclick="setHalalToggle('ada_sertifikat', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ $adaSertifikatVal == 1 ? 'active ok' : '' }}" id="btnSertifikatYa" onclick="setHalalToggle('ada_sertifikat', 1)">Ya</button>
                        </div>
                        <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat" value="{{ $adaSertifikatVal }}">
                    </div>
                    <input type="text" name="komentar_sertifikat" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_sertifikat', $qc->komentar_sertifikat) }}" style="font-size: 0.85rem;">
                </div>

                {{-- ITEM 5: SERTIFIKAT HALAL MASIH BERLAKU? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">5. Apakah sertifikat halal barang tersebut masih berlaku?</div>
                            <div class="halal-qa-sub">Validitas status aktif sertifikat</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $sertifikatBerlakuVal == 0 ? 'active danger' : '' }}" id="btnBerlakuTidak" onclick="setHalalToggle('sertifikat_berlaku', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ $sertifikatBerlakuVal == 1 ? 'active ok' : '' }}" id="btnBerlakuYa" onclick="setHalalToggle('sertifikat_berlaku', 1)">Ya</button>
                        </div>
                        <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_berlaku" value="{{ $sertifikatBerlakuVal }}">
                    </div>
                    <input type="text" name="komentar_berlaku" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_berlaku', $qc->komentar_berlaku) }}" style="font-size: 0.85rem;">
                </div>
            </div>
        </div>

        {{-- KARTU 3: TIMBANGAN & KUANTITAS --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>⚖️</span> <span>3. Hasil Timbangan &amp; Kuantitas Minyak</span>
                <span class="sk-card-badge-right" style="background: #fef3c7; color: #92400e; font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; float: right;">Satuan: {{ $satuanDtl }}</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Jumlah Surat Jalan ({{ $satuanDtl }})</label>
                        <input type="number" step="any" name="jumlah_surat_jalan" id="inputJumlahSJ" value="{{ old('jumlah_surat_jalan', $qc->jumlah_surat_jalan ?? $firstDetail?->qty_timbang_gross) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" oninput="syncMinyakEditSJ(this.value)">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Jumlah di Pabrik ({{ $satuanDtl }})</label>
                        <input type="number" step="any" name="jumlah_di_pabrik" id="inputJumlahPabrik" value="{{ old('jumlah_di_pabrik', $qc->jumlah_di_pabrik ?? ($firstDetail?->qty_timbang_gross ?? $totalGross)) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; color: #b45309;" oninput="syncMinyakEditPabrik(this.value)">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Bruto / Gross Timbang ({{ $satuanDtl }}) *</label>
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="inputGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', $firstDetail?->qty_timbang_gross ?? ($qc->jumlah_di_pabrik ?? $totalGross)) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800;" oninput="syncMinyakEditGross(this.value)" required>
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem; color: #dc2626;">Reject / Tolak ({{ $satuanDtl }})</label>
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_reject]" id="inputReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', $firstDetail?->qty_reject ?? $totalReject) }}" class="form-control" style="width: 100%; border-radius: 8px; color: #dc2626; font-weight: 700;" oninput="recalcMinyakNetto()">
                    </div>
                </div>

                <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 10px; padding: 0.85rem 1rem; text-align: center; margin-top: 0.25rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #065f46; text-transform: uppercase;">Estimasi Netto Lolos Diterima Pabrik</div>
                    <div style="font-size: 1.45rem; font-weight: 900; color: #059669; margin-top: 0.2rem;" id="displayNettoText">
                        {{ number_format($totalNetto, 0, ',', '.') }} {{ $satuanDtl }}
                    </div>
                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputNettoLolos" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $firstDetail?->qty_netto_lolos ?? $totalNetto) }}">
                </div>
            </div>
        </div>

        {{-- KARTU 4: PARAMETER MUTU, WADAH & UJI LAB KIMIA (FFA) --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🧪</span> <span>4. Parameter Mutu Kimia &amp; Kondisi Wadah</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- STATUS RAW MATERIAL & KONDISI WADAH --}}
                <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 10px; padding: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.4rem;">
                        <span style="font-weight: 800; font-size: 0.82rem; color: #9a3412;">STATUS RAW MATERIAL MINYAK :</span>
                        <div style="display: flex; gap: 1rem; font-size: 0.82rem; font-weight: 700;">
                            <label style="display: flex; align-items: center; gap: 4px; color: #15803d; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="OK" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'checked' : '' }}> OK (Standar)
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="TDK_STD" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? 'checked' : '' }}> TDK STD (Menyimpang)
                            </label>
                        </div>
                    </div>

                    <div style="border-top: 1px dashed #fdba74; padding-top: 0.5rem; display: flex; flex-direction: column; gap: 0.4rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                            <span style="font-weight: 800; font-size: 0.8rem; color: #0f172a;">TIPE WADAH PENGANGKUT :</span>
                            <div style="display: flex; gap: 0.85rem; font-size: 0.78rem; font-weight: 700;">
                                <label style="cursor: pointer;"><input type="radio" name="items[{{ $qcdtlId }}][tipe_wadah_minyak]" value="TANGKI" {{ old('items.'.$qcdtlId.'.tipe_wadah_minyak', $firstDetail?->tipe_wadah_minyak ?? 'TANGKI') === 'TANGKI' ? 'checked' : '' }}> 🚚 TANGKI CURAH</label>
                                <label style="cursor: pointer;"><input type="radio" name="items[{{ $qcdtlId }}][tipe_wadah_minyak]" value="JERIGEN" {{ old('items.'.$qcdtlId.'.tipe_wadah_minyak', $firstDetail?->tipe_wadah_minyak ?? '') === 'JERIGEN' ? 'checked' : '' }}> 🛢️ JERIGEN</label>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; font-size: 0.8rem; font-weight: 700; margin-top: 0.2rem;">
                            <label style="display: flex; align-items: center; gap: 4px; color: #15803d; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][kondisi_tangki_jerigen]" value="OK" {{ old('items.'.$qcdtlId.'.kondisi_tangki_jerigen', $firstDetail?->kondisi_tangki_jerigen ?? 'OK') === 'OK' ? 'checked' : '' }}> ✔ Wadah OK (Standar)
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][kondisi_tangki_jerigen]" value="TIDAK_STANDARD" {{ old('items.'.$qcdtlId.'.kondisi_tangki_jerigen', $firstDetail?->kondisi_tangki_jerigen ?? '') === 'TIDAK_STANDARD' ? 'checked' : '' }}> ✖ Wadah Tidak Standar
                            </label>
                        </div>
                    </div>
                </div>

                {{-- FFA COA & FFA QC LAB --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem;">
                        <span style="font-size: 0.82rem; font-weight: 800; color: #0284c7;">
                            🔬 ASAM LEMAK BEBAS (FFA - FREE FATTY ACID):
                        </span>
                        <span style="font-size: 0.7rem; font-weight: 800; color: #b45309; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                            Std Max: ≤ 0.20%
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                        <div>
                            <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">FFA di COA Vendor (%)</label>
                            <input type="number" step="0.001" min="0" max="100" name="items[{{ $qcdtlId }}][ffa_coa]" id="inputMinyakFfaCoa" value="{{ old('items.'.$qcdtlId.'.ffa_coa', $firstDetail?->ffa_coa ?? '') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" placeholder="0.080">
                        </div>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <label class="qc-info-label" style="font-weight: 700; margin: 0; color: #0284c7;">FFA Cek QC Mirasa (%) *</label>
                                <span style="font-size: 0.68rem; font-weight: 700; color: #9a3412; background: #ffedd5; padding: 1px 5px; border-radius: 4px;">Max 0.20%</span>
                            </div>
                            <input type="number" step="0.001" min="0" max="100" name="items[{{ $qcdtlId }}][ffa_qc]" id="inputMinyakFfaQc" value="{{ old('items.'.$qcdtlId.'.ffa_qc', $firstDetail?->ffa_qc ?? '') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #0f172a; border-color: #0284c7;" placeholder="0.085" oninput="checkMinyakFfaAlert(this.value)">
                        </div>
                    </div>

                    {{-- ALERT FFA MELEBIHI BATAS --}}
                    <div id="bannerFfaAlert" style="display: {{ ((float)($firstDetail?->ffa_qc ?? 0) > 0.20) ? 'block' : 'none' }}; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 0.65rem 0.85rem; color: #991b1b; font-size: 0.78rem; margin-top: 0.65rem; line-height: 1.4;">
                        <strong style="display: flex; align-items: center; gap: 4px; font-size: 0.82rem; margin-bottom: 2px;">
                            <span>⚠️</span> <span>PERINGATAN: FFA MELEBIHI AMBANG BATAS (0.20%)</span>
                        </strong>
                        Kadar FFA yang tinggi menandakan minyak mengalami hidrolisis / oksidasi. Disarankan untuk menolak penerimaan minyak ini.
                    </div>

                    {{-- CHECKLIST JERNIH & TANGKI BERSIH --}}
                    <div style="display: flex; gap: 1.25rem; margin-top: 0.75rem; padding-top: 0.65rem; border-top: 1px dashed #cbd5e1; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: #15803d; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][minyak_jernih_st]" value="1" {{ old('items.'.$qcdtlId.'.minyak_jernih_st', $firstDetail?->minyak_jernih_st ? '1' : ($firstDetail === null ? '1' : '0')) == '1' ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span>MINYAK JERNIH (BEBAS ENDAPAN)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: #15803d; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][tangki_bersih_st]" value="1" {{ old('items.'.$qcdtlId.'.tangki_bersih_st', $firstDetail?->tangki_bersih_st ? '1' : ($firstDetail === null ? '1' : '0')) == '1' ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span>TANGKI BAGIAN DALAM BERSIH / SANITER</span>
                        </label>
                    </div>
                </div>

                {{-- CATATAN SPESIFIKASI MUTU FISIK --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    <input type="hidden" name="items[{{ $qcdtlId }}][grade_cd]" id="selectGradeCd" value="{{ old('items.'.$qcdtlId.'.grade_cd', $firstDetail?->grade_cd ?? 'A') }}">
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem; font-size: 0.8rem; color: #0f172a;">Catatan Spesifikasi Mutu Fisik</label>
                    <input type="text" name="items[{{ $qcdtlId }}][catatan_dtl]" value="{{ old('items.'.$qcdtlId.'.catatan_dtl', $firstDetail?->catatan_dtl) }}" class="form-control" placeholder="Contoh: Minyak jernih kuning keemasan, bau khas segar normal..." style="width: 100%; border-radius: 6px; font-size: 0.85rem;">
                </div>
            </div>
        </div>

        {{-- KARTU 5: KEPUTUSAN KESIMPULAN & CATATAN --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>💬</span> <span>5. Keputusan &amp; Catatan QC Minyak</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Keputusan Akhir QC:</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                        <label id="lblRadioTerima" style="display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 2px solid #86efac; border-radius: 8px; padding: 0.65rem 0.85rem; cursor: pointer;">
                            <input type="radio" name="kesimpulan_qc" id="radioTerima" value="TERIMA" {{ old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : '') === 'TERIMA' ? 'checked' : '' }} style="width: 20px; height: 20px;">
                            <span style="font-weight: 900; color: #15803d; font-size: 0.95rem;">✔ TERIMA</span>
                        </label>
                        <label id="lblRadioTolak" style="display: flex; align-items: center; gap: 8px; background: #fef2f2; border: 2px solid #fca5a5; border-radius: 8px; padding: 0.65rem 0.85rem; cursor: pointer;">
                            <input type="radio" name="kesimpulan_qc" id="radioTolak" value="TOLAK" {{ old('kesimpulan_qc', $qc->status_qc === 'DITOLAK_TOTAL' ? 'TOLAK' : '') === 'TOLAK' ? 'checked' : '' }} style="width: 20px; height: 20px;">
                            <span style="font-weight: 900; color: #b91c1c; font-size: 0.95rem;">✖ TOLAK</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Catatan Petugas QC</label>
                    <textarea name="catatan_umum" id="inputCatatanUmum" rows="2" class="form-control" style="width: 100%; border-radius: 8px;" placeholder="Kondisi segel tangki utuh, warna jernih, FFA normal...">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Petugas QC</label>
                        <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">QC Supervisor</label>
                        <input type="text" name="qc_supervisor_nama" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama) }}" class="form-control" style="width: 100%; border-radius: 8px;" placeholder="Supervisor QC">
                    </div>
                </div>
            </div>
        </div>

        {{-- TOMBOL SUBMIT FIXED BOTTOM --}}
        <div class="qc-mobile-bottom-bar" style="display: flex; gap: 0.65rem; align-items: center;">
            <a href="{{ $backUrl }}" style="flex: 1; padding: 0.8rem 0.5rem; font-size: 0.88rem; font-weight: 700; border-radius: 10px; text-align: center; text-decoration: none; background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; min-height: 44px;">
                ✕ Batal
            </a>
            <button type="submit" id="btnEditSubmit" class="qc-btn-mobile-edit" style="flex: 2; font-size: 0.92rem; padding: 0.8rem 1rem; background: #d97706;">
                <span>💾 Simpan Perubahan</span>
            </button>
        </div>

        {{-- FULLSCREEN LOADING OVERLAY --}}
        <div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.72); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; padding: 1.75rem 2rem; border-radius: 16px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); max-width: 320px; margin: 1rem; width: 90%;">
                <div style="width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #d97706; border-radius: 50%; animation: qcSpin 0.8s linear infinite; margin: 0 auto 1.15rem;"></div>
                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.4rem;">Memperbarui Tiket QC Minyak...</div>
                <div style="font-size: 0.8rem; color: #64748b; line-height: 1.45;">Sedang menyimpan koreksi uji mutu minyak. Mohon tidak menekan tombol lagi.</div>
            </div>
        </div>
        <style>
            @keyframes qcSpin {
                to { transform: rotate(360deg); }
            }
        </style>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        // Segmented Toggle Halal (Tidak / Ya)
        window.setHalalToggle = function (field, value) {
            const hidden = document.getElementById(`input_${field}`);
            if (hidden) hidden.value = value;

            if (field === 'angkut_barang_haram') {
                const btnTidak = document.getElementById('btnHaramTidak');
                const btnYa = document.getElementById('btnHaramYa');
                if (value == 0) {
                    btnTidak?.classList.add('active', 'ok');
                    btnTidak?.classList.remove('danger');
                    btnYa?.classList.remove('active', 'ok', 'danger');
                } else {
                    btnYa?.classList.add('active', 'danger');
                    btnYa?.classList.remove('ok');
                    btnTidak?.classList.remove('active', 'ok', 'danger');
                }
            } else if (field === 'terdaftar_lppom') {
                const btnTidak = document.getElementById('btnLppomTidak');
                const btnYa = document.getElementById('btnLppomYa');
                if (value == 1) {
                    btnYa?.classList.add('active', 'ok');
                    btnYa?.classList.remove('danger');
                    btnTidak?.classList.remove('active', 'ok', 'danger');
                } else {
                    btnTidak?.classList.add('active', 'danger');
                    btnTidak?.classList.remove('ok');
                    btnYa?.classList.remove('active', 'ok', 'danger');
                }
            } else if (field === 'ada_sertifikat') {
                const btnTidak = document.getElementById('btnSertifikatTidak');
                const btnYa = document.getElementById('btnSertifikatYa');
                if (value == 1) {
                    btnYa?.classList.add('active', 'ok');
                    btnYa?.classList.remove('danger');
                    btnTidak?.classList.remove('active', 'ok', 'danger');
                } else {
                    btnTidak?.classList.add('active', 'danger');
                    btnTidak?.classList.remove('ok');
                    btnYa?.classList.remove('active', 'ok', 'danger');
                }
            } else if (field === 'sertifikat_berlaku') {
                const btnTidak = document.getElementById('btnBerlakuTidak');
                const btnYa = document.getElementById('btnBerlakuYa');
                if (value == 1) {
                    btnYa?.classList.add('active', 'ok');
                    btnYa?.classList.remove('danger');
                    btnTidak?.classList.remove('active', 'ok', 'danger');
                } else {
                    btnTidak?.classList.add('active', 'danger');
                    btnTidak?.classList.remove('ok');
                    btnYa?.classList.remove('active', 'ok', 'danger');
                }
            }
        };

        // Transport Radio Card (Bebas Cemaran vs Ada Cemaran)
        window.updateTransportCard = function (radio) {
            const cardBebas = document.getElementById('cardTransportBebas');
            const cardCemar = document.getElementById('cardTransportCemar');
            if (radio.value === '1') {
                cardBebas?.classList.add('active');
                cardCemar?.classList.remove('active');
            } else {
                cardCemar?.classList.add('active');
                cardBebas?.classList.remove('active');
            }
        };

        function syncMinyakEditSJ(val) {
            const pabrikEl = document.getElementById('inputJumlahPabrik');
            if (pabrikEl && (!pabrikEl.value || parseFloat(pabrikEl.value) === 0)) {
                pabrikEl.value = val;
                syncMinyakEditPabrik(val);
            }
        }

        function syncMinyakEditPabrik(val) {
            const grossEl = document.getElementById('inputGross');
            if (grossEl) {
                grossEl.value = val;
            }
            recalcMinyakNetto();
        }

        function syncMinyakEditGross(val) {
            const pabrikEl = document.getElementById('inputJumlahPabrik');
            if (pabrikEl) {
                pabrikEl.value = val;
            }
            recalcMinyakNetto();
        }

        function recalcMinyakNetto() {
            const gross = parseFloat(document.getElementById('inputGross')?.value) || 0;
            const reject = parseFloat(document.getElementById('inputReject')?.value) || 0;
            const netto = Math.max(0, gross - reject);

            const displayEl = document.getElementById('displayNettoText');
            const hiddenEl = document.getElementById('inputNettoLolos');

            const satuan = "{{ $satuanDtl }}";
            if (displayEl) {
                displayEl.innerText = (netto % 1 === 0 ? netto : netto.toFixed(2)).toLocaleString('id-ID') + ' ' + satuan;
            }
            if (hiddenEl) {
                hiddenEl.value = (netto % 1 === 0 ? netto : netto.toFixed(2));
            }
        }

        function checkMinyakFfaAlert(val) {
            const ffaVal = parseFloat(val) || 0;
            const banner = document.getElementById('bannerFfaAlert');
            const radioTolak = document.getElementById('radioTolak');
            const selectGrade = document.getElementById('selectGradeCd');

            if (ffaVal > 0.20) {
                if (banner) banner.style.display = 'block';
                if (radioTolak) radioTolak.checked = true;
                if (selectGrade) selectGrade.value = 'REJECT';
            } else {
                if (banner) banner.style.display = 'none';
            }
        }

        function filterEditSuppliers(query) {
            const q = (query || '').toLowerCase().trim();
            const select = document.getElementById('editSupplierSelect');
            if (!select) return;

            for (let i = 0; i < select.options.length; i++) {
                const opt = select.options[i];
                const text = (opt.text || '').toLowerCase();
                if (!q || text.includes(q)) {
                    opt.style.display = '';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            }
        }

        function onEditSupplierChange(select) {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.value) {
                const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split('(')[0].trim();
                const produsenInput = document.getElementById('editNamaProdusen');
                if (produsenInput) produsenInput.value = supNm;
            }
        }

        function onEditBarangChanged(select) {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.value) {
                const bNm = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
                const namaEl = document.getElementById('editNamaJenis');
                if (namaEl) {
                    const upper = bNm.toUpperCase();
                    if (upper.includes('KELAPA') && !upper.includes('SAWIT')) {
                        namaEl.value = 'Minyak Kelapa (RBD CNO)';
                    } else {
                        namaEl.value = 'RBD Palm Olein / Curah Sawit';
                    }
                }
            }
        }

        // Overlay submit handler
        document.getElementById('qcEditMinyakForm')?.addEventListener('submit', function() {
            const overlay = document.getElementById('qcSubmitOverlay');
            if (overlay) overlay.style.display = 'flex';
        });
    </script>
@endpush
