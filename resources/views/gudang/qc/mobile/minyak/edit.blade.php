@extends('layouts.qc-mobile')

@section('title', 'Koreksi Sampling QC Minyak: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}?v={{ filemtime(public_path('css/gudang/qc/mobile/qc-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}?v={{ filemtime(public_path('css/gudang/qc/mobile/qc-mobile-detail.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak.css') }}?v={{ filemtime(public_path('css/gudang/qc/minyak/minyak.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak-detail.css') }}?v={{ filemtime(public_path('css/gudang/qc/minyak/minyak-detail.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak-edit.css') }}?v={{ filemtime(public_path('css/gudang/qc/minyak/minyak-edit.css')) }}">
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

    // Format Nilai Numerik agar Rapi & Bersih (Tanpa Nol Berlebih)
    $sjVal = (float) ($qc->jumlah_surat_jalan ?? $firstDetail?->qty_timbang_gross ?? 0);
    $pabrikVal = (float) ($qc->jumlah_di_pabrik ?? ($firstDetail?->qty_timbang_gross ?? $totalGross));
    $grossVal = (float) ($firstDetail?->qty_timbang_gross ?? ($qc->jumlah_di_pabrik ?? $totalGross));
    $rejectVal = (float) ($firstDetail?->qty_reject ?? 0);
    $nettoVal = (float) ($firstDetail?->qty_netto_lolos ?? $totalNetto);
    $sampleVal = (float) ($qc->jumlah_sample_gr ?? 500);

    $ffaCoaVal = $firstDetail?->ffa_coa !== null ? (float) $firstDetail->ffa_coa : '';
    $ffaQcVal = $firstDetail?->ffa_qc !== null ? (float) $firstDetail->ffa_qc : '';

    $statusRawVal = old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK');
    $tipeWadahVal = old('items.'.$qcdtlId.'.tipe_wadah_minyak', $firstDetail?->tipe_wadah_minyak ?? 'TANGKI');
    $kondisiWadahVal = old('items.'.$qcdtlId.'.kondisi_tangki_jerigen', $firstDetail?->kondisi_tangki_jerigen ?? 'OK');
    $kesimpulanVal = old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : 'TOLAK');

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
<div class="minyak-edit-wrap">

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

    {{-- HEADER CARD HERO MOBILE --}}
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
    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditMinyakForm">
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

        {{-- ========================================================================= --}}
        {{-- KARTU 1: INFO PENGIRIMAN, PO & VENDOR MINYAK                             --}}
        {{-- ========================================================================= --}}
        <div class="minyak-edit-card">
            <div class="minyak-edit-card-header theme-blue">
                <div class="minyak-edit-card-header-left">
                    <span class="minyak-step-badge">1</span>
                    <div>
                        <h2 class="minyak-edit-card-title">Info Armada, PO &amp; Vendor Minyak</h2>
                        <div class="minyak-edit-card-subtitle">Identitas Surat Jalan, Nomor PO &amp; Pihak Pemasok</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🚚</span>
            </div>

            <div class="minyak-edit-card-body">
                {{-- PILIHAN PO KHUSUS MINYAK --}}
                <div class="minyak-field-group">
                    <div class="minyak-field-header">
                        <label class="minyak-field-label">Referensi Purchase Order (PO)</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 2px 7px; border-radius: 5px;">
                            PO Minyak ({{ $filteredPos->count() }} Tersedia)
                        </span>
                    </div>
                    <select name="po_id" id="editPoSelect" class="form-control" style="font-weight: 700;" onchange="onEditPoChanged(this)">
                        <option value="">-- Tanpa PO / Kiriman Langsung Minyak --</option>
                        @foreach ($filteredPos as $po)
                            @php
                                $totPesan = (float) $po->details->sum('pesan_qty');
                                $terimaIni = ($po->po_id == $qc->po_id && $qc->terima) ? (float) $qc->terima->details->sum('terima_qty') : 0;
                                $sudahMsk = max(0, (float) $po->details->sum('terima_qty') - $terimaIni);
                                $sisaKta = max(0, $totPesan - $sudahMsk);
                                $poDetailMatch = $po->details?->firstWhere('barang_id', $firstDetail?->barang_id) ?: $po->details?->first();
                                $poBarangId = $poDetailMatch?->barang_id;
                            @endphp
                            <option value="{{ $po->po_id }}"
                                    data-supplier-id="{{ $po->supplier_id }}"
                                    data-supplier-nm="{{ $po->supplier?->supplier_nm }}"
                                    data-gudang-id="{{ $po->gudang_id }}"
                                    data-barang-id="{{ $poBarangId }}"
                                    {{ old('po_id', $qc->po_id) == $po->po_id ? 'selected' : '' }}>
                                {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} (Sisa: {{ number_format($sisaKta, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- MITRA VENDOR / SUPPLIER --}}
                <div class="minyak-field-group">
                    <div class="minyak-field-header">
                        <label class="minyak-field-label">Mitra Supplier / Produsen Minyak <span class="required-star">*</span></label>
                        <span id="badgeEditLockSupplier" class="badge-po-locked" style="{{ $qc->po_id ? 'display: inline-flex;' : 'display: none;' }} font-size: 0.7rem; font-weight: 800; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">🔒 Terkunci ({{ $qc->supplier?->supplier_nm }})</span>
                    </div>
                    <div id="wrapEditSupplierSearch" style="margin-bottom: 0.25rem; {{ $qc->po_id ? 'display: none;' : '' }}">
                        <input type="text" id="editSupplierSearch" class="form-control" placeholder="🔍 Cari vendor supplier minyak..." oninput="filterEditSuppliers(this.value)" style="min-height: 38px; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 8px;">
                    </div>
                    <select name="supplier_id" id="editSupplierSelect" class="form-control" style="font-weight: 700; {{ $qc->po_id ? 'pointer-events: none; background-color: #f1f5f9; border-color: #cbd5e1;' : '' }}" required onchange="onEditSupplierChange(this)" tabindex="{{ $qc->po_id ? '-1' : '0' }}">
                        @foreach ($filteredSuppliers as $sup)
                            <option value="{{ $sup->supplier_id }}"
                                    data-supplier-nm="{{ $sup->supplier_nm }}"
                                    {{ old('supplier_id', $qc->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GUDANG PENAMPUNGAN --}}
                <div class="minyak-field-group">
                    <div class="minyak-field-header">
                        <label class="minyak-field-label">Gudang / Tangki Penampungan <span class="required-star">*</span></label>
                        <span id="badgeEditLockGudang" class="badge-po-locked" style="{{ $qc->po_id ? 'display: inline-flex;' : 'display: none;' }} font-size: 0.7rem; font-weight: 800; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">🔒 Terkunci dari PO</span>
                    </div>
                    <select name="gudang_id" id="editGudangSelect" class="form-control" style="font-weight: 700; {{ $qc->po_id ? 'pointer-events: none; background-color: #f1f5f9; border-color: #cbd5e1;' : '' }}" required tabindex="{{ $qc->po_id ? '-1' : '0' }}">
                        @foreach ($gudangs as $g)
                            <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                {{ $g->display_name ?? $g->gudang_nm }} @if(!empty($g->tipe_gudang_cd) && $g->tipe_gudang_cd !== 'Pusat') ({{ $g->tipe_gudang_cd }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- MASTER BARANG MINYAK --}}
                <div class="minyak-field-group">
                    <div class="minyak-field-header">
                        <label class="minyak-field-label">Komoditas Minyak (Master Data) <span class="required-star">*</span></label>
                        <span id="badgeEditLockBarang" class="badge-po-locked" style="{{ $qc->po_id ? 'display: inline-flex;' : 'display: none;' }} font-size: 0.7rem; font-weight: 800; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">🔒 Terkunci dari PO</span>
                    </div>
                    <select name="items[{{ $qcdtlId }}][barang_id]" id="editBarangSelect" class="form-control" style="font-weight: 800; color: #b45309; {{ $qc->po_id ? 'pointer-events: none; background-color: #f1f5f9; border-color: #cbd5e1;' : '' }}" required onchange="onEditBarangChanged(this)" tabindex="{{ $qc->po_id ? '-1' : '0' }}">
                        @foreach ($filteredBarangs as $b)
                            <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}" {{ old('items.'.$qcdtlId.'.barang_id', $barangId) == $b->barang_id ? 'selected' : '' }}>
                                {{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'kg' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- NAMA JENIS SPESIFIKASI --}}
                <div class="minyak-field-group">
                    <div class="minyak-field-header">
                        <label class="minyak-field-label">NAMA JENIS : (Spesifikasi Fraksi / Grade)</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 1px 7px; border-radius: 4px;">Form HACCP MFI</span>
                    </div>
                    <input type="text" name="nama_jenis" id="editNamaJenis" list="listEditMinyakJenis" value="{{ old('nama_jenis', $qc->nama_jenis ?? 'RBD Palm Olein / Curah Sawit') }}" class="form-control" style="font-weight: 800;" placeholder="RBD Palm Olein (CP8) / Curah Sawit / Minyak Kelapa">
                    <datalist id="listEditMinyakJenis">
                        <option value="RBD Palm Olein (CP8)">
                        <option value="RBD Palm Olein (CP10)">
                        <option value="Minyak Curah Kelapa Sawit">
                        <option value="Minyak Kelapa (RBD CNO)">
                        <option value="Minyak Sawit Super (Filma)">
                        <option value="Minyak Sawit Jerigen Sania">
                    </datalist>
                </div>

                {{-- NEGARA PRODUSEN --}}
                <div class="minyak-field-group">
                    <label class="minyak-field-label">Negara Produsen</label>
                    <input type="text" name="negara_produsen" value="{{ old('negara_produsen', $qc->negara_produsen ?: 'Indonesia') }}" class="form-control" placeholder="Indonesia" style="font-weight: 700;">
                </div>
                <input type="hidden" name="nama_produsen" id="editNamaProdusen" value="{{ old('nama_produsen', $qc->nama_produsen ?: $qc->supplier?->supplier_nm) }}">

                {{-- SURAT JALAN & PLAT TRUK --}}
                <div class="minyak-grid-2col stack-on-mobile">
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">No. DO (Surat Jalan)</label>
                        <input type="text" name="nomor_do" id="inputNomorDo" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" class="form-control" style="font-weight: 700;" oninput="const sjEl = document.getElementById('inputSuratJalanSupplier'); if(sjEl) sjEl.value = this.value;">
                        <input type="hidden" name="surat_jalan_supplier" id="inputSuratJalanSupplier" value="{{ old('surat_jalan_supplier', $qc->surat_jalan_supplier ?? $qc->nomor_do) }}">
                    </div>
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">Plat Truk / Tangki</label>
                        <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" style="font-weight: 800; text-transform: uppercase;">
                    </div>
                </div>

                {{-- NAMA SOPIR & TANGGAL DATANG --}}
                <div class="minyak-grid-2col stack-on-mobile">
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">Nama Sopir</label>
                        <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" placeholder="Nama pengemudi">
                    </div>
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">Tanggal Datang</label>
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="form-control" style="font-size: 0.85rem; font-weight: 700;">
                    </div>
                </div>

                {{-- JUMLAH SAMPEL QC --}}
                <div class="minyak-field-group">
                    <label class="minyak-field-label">Sampel QC Diambil</label>
                    <div class="minyak-suffix-wrap">
                        <input type="number" step="any" name="jumlah_sample_gr" value="{{ old('jumlah_sample_gr', $sampleVal) }}" class="form-control" style="font-weight: 800; color: #0284c7;" placeholder="500">
                        <span class="minyak-suffix-label">Gram</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KARTU 2: HALAL & HIGIENE TRANSPORTASI (SJH & CCP TITIK KRITIS)            --}}
        {{-- ========================================================================= --}}
        <div class="minyak-edit-card" style="border-color: #bbf7d0;">
            <div class="minyak-edit-card-header theme-green">
                <div class="minyak-edit-card-header-left">
                    <span class="minyak-step-badge green">2</span>
                    <div>
                        <h2 class="minyak-edit-card-title" style="color: #166534;">Halal &amp; Higiene Transportasi</h2>
                        <div class="minyak-edit-card-subtitle" style="color: #15803d;">Sistem Jaminan Halal (SJH) &amp; CCP Titik Kritis</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🛡️</span>
            </div>

            <div class="minyak-edit-card-body">
                {{-- ITEM 1: KONDISI TRANSPORTASI --}}
                <div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 0.45rem;">
                        1. Kondisi Transportasi (Truk Tangki / Kontainer)
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label class="transport-radio-card {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'active' : '' }}" id="cardTransportBebas">
                            <input type="radio" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'checked' : '' }} onchange="updateTransportCard(this)">
                            <div class="transport-card-text">
                                <div class="transport-card-title" style="color: #15803d;">Tidak ada cemaran, Najis / Kotoran</div>
                                <div class="transport-card-desc">Truk bersih, segel utuh, tidak berbau asing</div>
                            </div>
                        </label>

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

                {{-- ITEM 2: DIANGKUT BERSAMA BARANG HARAM? --}}
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

                {{-- ITEM 3: TERDAFTAR LPPOM / BPJPH? --}}
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

        {{-- ========================================================================= --}}
        {{-- KARTU 3: HASIL TIMBANGAN & KUANTITAS MINYAK                              --}}
        {{-- ========================================================================= --}}
        <div class="minyak-edit-card">
            <div class="minyak-edit-card-header theme-amber">
                <div class="minyak-edit-card-header-left">
                    <span class="minyak-step-badge amber">3</span>
                    <div>
                        <h2 class="minyak-edit-card-title">Hasil Timbangan &amp; Kuantitas Minyak</h2>
                        <div class="minyak-edit-card-subtitle">Verifikasi Dokumen Surat Jalan vs Timbangan Real Pabrik</div>
                    </div>
                </div>
                <span style="background: #fef3c7; color: #92400e; font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; border: 1px solid #fde68a;">
                    Satuan: {{ $satuanDtl }}
                </span>
            </div>

            <div class="minyak-edit-card-body">
                <div class="minyak-grid-2col stack-on-mobile">
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">Jumlah Surat Jalan</label>
                        <div class="minyak-suffix-wrap">
                            <input type="number" step="any" name="jumlah_surat_jalan" id="inputJumlahSJ" value="{{ old('jumlah_surat_jalan', $sjVal) }}" class="form-control" style="font-weight: 800;" oninput="syncMinyakEditSJ(this.value)">
                            <span class="minyak-suffix-label">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">Jumlah di Pabrik</label>
                        <div class="minyak-suffix-wrap">
                            <input type="number" step="any" name="jumlah_di_pabrik" id="inputJumlahPabrik" value="{{ old('jumlah_di_pabrik', $pabrikVal) }}" class="form-control" style="font-weight: 800; color: #b45309;" oninput="syncMinyakEditPabrik(this.value)">
                            <span class="minyak-suffix-label">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                </div>

                <div class="minyak-grid-2col stack-on-mobile">
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">Bruto / Gross Timbang <span class="required-star">*</span></label>
                        <div class="minyak-suffix-wrap">
                            <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="inputGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', $grossVal) }}" class="form-control" style="font-weight: 900; color: #0f172a;" oninput="syncMinyakEditGross(this.value)" required>
                            <span class="minyak-suffix-label">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                    <div class="minyak-field-group">
                        <label class="minyak-field-label" style="color: #dc2626;">Reject / Tolak</label>
                        <div class="minyak-suffix-wrap">
                            <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_reject]" id="inputReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', $rejectVal) }}" class="form-control" style="font-weight: 800; color: #dc2626;" oninput="recalcMinyakNetto()">
                            <span class="minyak-suffix-label" style="color: #ef4444;">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                </div>

                {{-- ESTIMASI NETTO LOLOS DITERIMA PABRIK --}}
                <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 12px; padding: 1rem; text-align: center; margin-top: 0.15rem;">
                    <div style="font-size: 0.72rem; font-weight: 800; color: #065f46; letter-spacing: 0.04em; text-transform: uppercase;">
                        Estimasi Netto Lolos Diterima Pabrik
                    </div>
                    <div style="font-size: 1.65rem; font-weight: 900; color: #059669; margin-top: 0.25rem;" id="displayNettoText">
                        {{ number_format($nettoVal, 0, ',', '.') }} {{ $satuanDtl }}
                    </div>
                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputNettoLolos" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $nettoVal) }}">
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KARTU 4: PARAMETER MUTU KIMIA & KONDISI WADAH                            --}}
        {{-- ========================================================================= --}}
        <div class="minyak-edit-card">
            <div class="minyak-edit-card-header theme-amber">
                <div class="minyak-edit-card-header-left">
                    <span class="minyak-step-badge amber">4</span>
                    <div>
                        <h2 class="minyak-edit-card-title">Parameter Mutu Kimia &amp; Kondisi Wadah</h2>
                        <div class="minyak-edit-card-subtitle">Uji FFA COA/Lab, Integritas Wadah Tangki &amp; Kejernihan</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🧪</span>
            </div>

            <div class="minyak-edit-card-body">
                {{-- 1. STATUS RAW MATERIAL MINYAK --}}
                <div class="minyak-subcard-box theme-amber">
                    <div class="minyak-subcard-header">
                        <span class="minyak-subcard-title">
                            <span>📋</span> <span>Status Raw Material Minyak</span>
                        </span>
                        <span class="minyak-subcard-badge" style="background: #fef3c7; color: #b45309;">Kondisi Fisik</span>
                    </div>
                    <div class="minyak-pill-group">
                        <label class="minyak-pill-btn {{ $statusRawVal === 'OK' ? 'is-active-ok' : '' }}" onclick="selectMinyakPill('items[{{ $qcdtlId }}][status_raw_material]', 'OK')">
                            <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="OK" {{ $statusRawVal === 'OK' ? 'checked' : '' }}>
                            <span class="pill-dot"></span>
                            <span>✔ OK (Standar)</span>
                        </label>
                        <label class="minyak-pill-btn {{ $statusRawVal === 'TDK_STD' ? 'is-active-danger' : '' }}" onclick="selectMinyakPill('items[{{ $qcdtlId }}][status_raw_material]', 'TDK_STD')">
                            <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="TDK_STD" {{ $statusRawVal === 'TDK_STD' ? 'checked' : '' }}>
                            <span class="pill-dot"></span>
                            <span>✖ TDK STD (Menyimpang)</span>
                        </label>
                    </div>
                </div>

                {{-- 2. WADAH PENGANGKUT (TIPE & KONDISI WADAH) --}}
                <div class="minyak-subcard-box">
                    <div class="minyak-subcard-header">
                        <span class="minyak-subcard-title">
                            <span>🚚</span> <span>Wadah Pengangkut (Truk Tangki / Jerigen)</span>
                        </span>
                        <span class="minyak-subcard-badge" style="background: #e2e8f0; color: #334155;">Higienitas</span>
                    </div>

                    {{-- Baris A: Tipe Wadah Pengangkut --}}
                    <div class="minyak-field-group">
                        <label class="minyak-field-label" style="font-size: 0.78rem; color: #475569;">Tipe Wadah:</label>
                        <div class="minyak-pill-group">
                            <label class="minyak-pill-btn {{ $tipeWadahVal === 'TANGKI' ? 'is-active-blue' : '' }}" onclick="selectMinyakPill('items[{{ $qcdtlId }}][tipe_wadah_minyak]', 'TANGKI')">
                                <input type="radio" name="items[{{ $qcdtlId }}][tipe_wadah_minyak]" value="TANGKI" {{ $tipeWadahVal === 'TANGKI' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>🚚 Tangki Curah</span>
                            </label>
                            <label class="minyak-pill-btn {{ $tipeWadahVal === 'JERIGEN' ? 'is-active-blue' : '' }}" onclick="selectMinyakPill('items[{{ $qcdtlId }}][tipe_wadah_minyak]', 'JERIGEN')">
                                <input type="radio" name="items[{{ $qcdtlId }}][tipe_wadah_minyak]" value="JERIGEN" {{ $tipeWadahVal === 'JERIGEN' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>🛢️ Jerigen</span>
                            </label>
                        </div>
                    </div>

                    {{-- Baris B: Kondisi Tangki / Jerigen --}}
                    <div class="minyak-field-group">
                        <label class="minyak-field-label" style="font-size: 0.78rem; color: #475569;">Kondisi Kebersihan Wadah:</label>
                        <div class="minyak-pill-group">
                            <label class="minyak-pill-btn {{ $kondisiWadahVal === 'OK' ? 'is-active-ok' : '' }}" onclick="selectMinyakPill('items[{{ $qcdtlId }}][kondisi_tangki_jerigen]', 'OK')">
                                <input type="radio" name="items[{{ $qcdtlId }}][kondisi_tangki_jerigen]" value="OK" {{ $kondisiWadahVal === 'OK' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>✔ Wadah Bersih / OK</span>
                            </label>
                            <label class="minyak-pill-btn {{ $kondisiWadahVal === 'TIDAK_STANDARD' ? 'is-active-danger' : '' }}" onclick="selectMinyakPill('items[{{ $qcdtlId }}][kondisi_tangki_jerigen]', 'TIDAK_STANDARD')">
                                <input type="radio" name="items[{{ $qcdtlId }}][kondisi_tangki_jerigen]" value="TIDAK_STANDARD" {{ $kondisiWadahVal === 'TIDAK_STANDARD' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>✖ Wadah Tidak Standar</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- 3. UJI KIMIA LABORATORIUM: ASAM LEMAK BEBAS (FFA) --}}
                <div class="minyak-ffa-box">
                    <div class="minyak-ffa-header">
                        <div class="minyak-ffa-title">
                            <span>🔬</span> <span>Asam Lemak Bebas (FFA)</span>
                        </div>
                        <span class="minyak-ffa-limit-badge">Std Max: ≤ 0.20%</span>
                    </div>

                    <div class="minyak-ffa-grid">
                        <div class="minyak-field-group">
                            <label class="minyak-field-label" style="color: #475569;">FFA COA Vendor</label>
                            <div class="minyak-suffix-wrap">
                                <input type="number" step="0.001" min="0" max="100" name="items[{{ $qcdtlId }}][ffa_coa]" id="inputMinyakFfaCoa" value="{{ old('items.'.$qcdtlId.'.ffa_coa', $ffaCoaVal) }}" class="form-control" style="font-weight: 800;" placeholder="0.080">
                                <span class="minyak-suffix-label">%</span>
                            </div>
                        </div>
                        <div class="minyak-field-group">
                            <label class="minyak-field-label" style="color: #0369a1;">FFA Cek QC Mirasa <span class="required-star">*</span></label>
                            <div class="minyak-suffix-wrap">
                                <input type="number" step="0.001" min="0" max="100" name="items[{{ $qcdtlId }}][ffa_qc]" id="inputMinyakFfaQc" value="{{ old('items.'.$qcdtlId.'.ffa_qc', $ffaQcVal) }}" class="form-control" style="font-weight: 900; color: #0f172a; border-color: #0284c7;" placeholder="0.085" oninput="checkMinyakFfaAlert(this.value)">
                                <span class="minyak-suffix-label" style="color: #0284c7;">%</span>
                            </div>
                        </div>
                    </div>

                    {{-- ALERT FFA MELEBIHI BATAS --}}
                    <div id="bannerFfaAlert" style="display: {{ ((float)($firstDetail?->ffa_qc ?? 0) > 0.20) ? 'block' : 'none' }}; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 0.75rem 0.95rem; color: #991b1b; font-size: 0.8rem; line-height: 1.45;">
                        <strong style="display: flex; align-items: center; gap: 5px; font-size: 0.85rem; margin-bottom: 2px;">
                            <span>⚠️</span> <span>PERINGATAN: FFA MELEBIHI AMBANG BATAS (0.20%)</span>
                        </strong>
                        Kadar FFA yang tinggi menandakan minyak mengalami hidrolisis / oksidasi. Keputusan disarankan dialihkan ke <strong>TOLAK</strong>.
                    </div>
                </div>

                {{-- 4. VERIFIKASI KEJERNIHAN & SANITASI TANGKI --}}
                <div class="minyak-check-card-row">
                    @php
                        $jernihChecked = old('items.'.$qcdtlId.'.minyak_jernih_st', $firstDetail?->minyak_jernih_st ? '1' : ($firstDetail === null ? '1' : '0')) == '1';
                        $tangkiChecked = old('items.'.$qcdtlId.'.tangki_bersih_st', $firstDetail?->tangki_bersih_st ? '1' : ($firstDetail === null ? '1' : '0')) == '1';
                    @endphp

                    <label class="minyak-check-card {{ $jernihChecked ? 'is-checked' : '' }}">
                        <div class="minyak-check-left">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][minyak_jernih_st]" value="1" {{ $jernihChecked ? 'checked' : '' }} onchange="toggleMinyakCheckCard(this)">
                            <span class="minyak-check-title">Minyak Jernih</span>
                        </div>
                        <span class="minyak-check-badge">Bebas Endapan</span>
                    </label>

                    <label class="minyak-check-card {{ $tangkiChecked ? 'is-checked' : '' }}">
                        <div class="minyak-check-left">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][tangki_bersih_st]" value="1" {{ $tangkiChecked ? 'checked' : '' }} onchange="toggleMinyakCheckCard(this)">
                            <span class="minyak-check-title">Tangki Bersih</span>
                        </div>
                        <span class="minyak-check-badge">Saniter</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KARTU 5: KEPUTUSAN KESIMPULAN & CATATAN QC MINYAK                        --}}
        {{-- ========================================================================= --}}
        <div class="minyak-edit-card">
            <div class="minyak-edit-card-header theme-green">
                <div class="minyak-edit-card-header-left">
                    <span class="minyak-step-badge green">5</span>
                    <div>
                        <h2 class="minyak-edit-card-title">Keputusan &amp; Catatan QC Minyak</h2>
                        <div class="minyak-edit-card-subtitle">Kesimpulan Akhir Inspeksi, Catatan Lapangan &amp; Otorisasi</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">💬</span>
            </div>

            <div class="minyak-edit-card-body">
                <div class="minyak-field-group">
                    <label class="minyak-field-label">Keputusan Akhir QC <span class="required-star">*</span></label>
                    <div class="minyak-pill-group">
                        <label class="minyak-pill-btn {{ $kesimpulanVal === 'TERIMA' ? 'is-active-ok' : '' }}" onclick="selectMinyakPill('kesimpulan_qc', 'TERIMA')">
                            <input type="radio" name="kesimpulan_qc" id="radioTerima" value="TERIMA" {{ $kesimpulanVal === 'TERIMA' ? 'checked' : '' }}>
                            <span class="pill-dot"></span>
                            <span style="font-size: 0.95rem; font-weight: 800;">✔ TERIMA</span>
                        </label>
                        <label class="minyak-pill-btn {{ $kesimpulanVal === 'TOLAK' ? 'is-active-danger' : '' }}" onclick="selectMinyakPill('kesimpulan_qc', 'TOLAK')">
                            <input type="radio" name="kesimpulan_qc" id="radioTolak" value="TOLAK" {{ $kesimpulanVal === 'TOLAK' ? 'checked' : '' }}>
                            <span class="pill-dot"></span>
                            <span style="font-size: 0.95rem; font-weight: 800;">✖ TOLAK</span>
                        </label>
                    </div>
                </div>

                <div class="minyak-field-group">
                    <label class="minyak-field-label">Catatan Petugas QC</label>
                    <textarea name="catatan_umum" id="inputCatatanUmum" rows="3" class="form-control" placeholder="Tuliskan keterangan inspeksi minyak, kondisi segel, warna, atau catatan lab..." style="line-height: 1.45;">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
                </div>

                <div class="minyak-grid-2col stack-on-mobile">
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">Petugas QC <span class="required-star">*</span></label>
                        <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama) }}" class="form-control" style="font-weight: 700;" required>
                    </div>
                    <div class="minyak-field-group">
                        <label class="minyak-field-label">QC Supervisor</label>
                        <input type="text" name="qc_supervisor_nama" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama) }}" class="form-control" placeholder="Nama Supervisor QC">
                    </div>
                </div>
            </div>
        </div>

        {{-- FLOATING BOTTOM BAR --}}
        <div class="minyak-mobile-bottom-bar">
            <a href="{{ $backUrl }}" style="flex: 1; padding: 0.8rem 0.5rem; font-size: 0.88rem; font-weight: 700; border-radius: 10px; text-align: center; text-decoration: none; background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; min-height: 46px;">
                ✕ Batal
            </a>
            <button type="submit" id="btnEditSubmit" class="qc-btn-mobile-edit" style="flex: 2; font-size: 0.92rem; padding: 0.8rem 1rem; background: #d97706; min-height: 46px; border-radius: 10px; font-weight: 800; border: none; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 10px rgba(217, 119, 6, 0.35);">
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
    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.minyakSatuan = "{{ $satuanDtl }}";
    </script>
    <script src="{{ asset('js/gudang/qc/minyak/minyak-edit.js') }}?v={{ filemtime(public_path('js/gudang/qc/minyak/minyak-edit.js')) }}"></script>
@endpush
