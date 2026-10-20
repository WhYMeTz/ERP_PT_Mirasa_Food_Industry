@extends('layouts.qc-mobile')

@section('title', 'Koreksi QC Karton: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/karton/karton.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/karton/karton-detail.css') }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;

    // Filter daftar PO yang memuat Karton
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (
            str_contains($nm, 'MINYAK') || str_contains($nm, 'SINGKONG') || str_contains($nm, 'PUYUR') ||
            str_contains($nm, 'OPAK') || str_contains($nm, 'UBI') || str_contains($nm, 'BUMBU') ||
            str_contains($nm, 'MSG') || str_contains($nm, 'GARAM') || str_contains($nm, 'PERENYAH') ||
            str_contains($nm, 'PLASTIK') || str_contains($nm, 'KEMASAN') || str_contains($nm, 'OPP')
        ) {
            return 'LAINNYA';
        }
        if (
            str_contains($nm, 'KARTON') || str_contains($nm, 'BOX') || str_contains($nm, 'DUS') ||
            str_starts_with($cd, 'BP-KT') || str_starts_with($cd, 'BP-BX')
        ) {
            return 'KARTON';
        }
        return 'LAINNYA';
    };

    $filteredPos = $pos->filter(function($p) use ($qc, $classifyBarang) {
        if ($p->po_id == $qc->po_id) return true;
        foreach ($p->details as $d) {
            if ($classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd) === 'KARTON') {
                return true;
            }
        }
        return false;
    });

    if ($filteredPos->isEmpty() && $qc->po) {
        $filteredPos = collect([$qc->po]);
    }

    // Filter Supplier khusus Karton / Non-Petani
    $filteredSuppliers = $suppliers->filter(function($s) use ($qc) {
        if ($s->supplier_id == $qc->supplier_id) return true;
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($filteredSuppliers->isEmpty()) {
        $filteredSuppliers = $suppliers;
    }

    // Filter Master Barang Karton Box
    $filteredBarangs = $barangs->filter(function($b) use ($classifyBarang, $firstDetail) {
        if ($firstDetail && $b->barang_id == $firstDetail->barang_id) return true;
        return $classifyBarang($b->barang_nm, $b->barang_cd) === 'KARTON';
    });
    if ($filteredBarangs->isEmpty()) {
        $filteredBarangs = $barangs;
    }

    $barangId = $firstDetail?->barang_id ?? $filteredBarangs->first()?->barang_id;
    $ref = request('ref', 'index');
    if ($ref === 'detail') {
        $backUrl = route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']);
        $backLabel = 'Batal ke Detail';
    } else {
        $backUrl = route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'KARTON']);
        $backLabel = 'Batal ke Riwayat';
    }

    // Satuan
    $satuanRaw = $firstDetail?->barang?->satuanDasar?->satuan_nm
        ?: ($firstDetail?->barang?->satuanDasar?->satuan_cd ?: 'PCS');
    $satuanDtl = strtoupper(trim($satuanRaw)) === 'BOX' ? 'BOX' : 'PCS';

    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
@endphp

@section('content')
<div class="qc-detail-wrap" style="max-width: 680px; margin: 0 auto; padding-bottom: 5.5rem;">

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
                QC Karton &bull; Edit
            </span>
        </div>
    </div>

    {{-- HEADER CARD MOBILE --}}
    <div class="qc-detail-hero" style="background: linear-gradient(135deg, #0f172a 0%, #78350f 50%, #d97706 100%);">
        <div class="qc-detail-hero-top">
            <div>
                <span class="badge-tag-commodity" style="background: rgba(245, 158, 11, 0.4); color: #fef3c7;">
                    📦 KARTON BOX
                </span>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem; color: #ffffff;">Edit Tiket #{{ $qc->qc_no }}</h1>
            </div>
            <span style="background: #fef08a; color: #854d0e; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 20px;">
                Mode Mobile
            </span>
        </div>
        <p style="font-size: 0.8rem; margin: 0.4rem 0 0; opacity: 0.85; line-height: 1.4;">
            Koreksi parameter sampling mutu karton box, dimensi P x L x T, kondisi armada, atau kuantitas kedatangan.
        </p>
    </div>

    {{-- ALERT JIKA SUDAH DIPROSES KE GUDANG (GRN) --}}
    @if ($qc->terima)
        <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; gap: 0.65rem; align-items: flex-start;">
            <span style="font-size: 1.25rem; line-height: 1;">📦</span>
            <div style="font-size: 0.78rem; color: #92400e; line-height: 1.4;">
                <strong>Perhatian:</strong> Tiket ini sudah ditarik ke Penerimaan Gudang (GRN <strong>#{{ $qc->terima->terima_no }}</strong>).
                Setiap revisi netto atau kuantitas reject akan menyinkronkan stok gudang secara otomatis.
            </div>
        </div>
    @endif

    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditKartonForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="ref" value="{{ $ref ?: 'index' }}">
        <input type="hidden" name="kategori_barang" value="KARTON">
        <input type="hidden" name="batch_no" value="{{ old('batch_no', $qc->batch_no) }}">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">
        <input type="hidden" name="status_uji_goreng" value="SELESAI">
        <input type="hidden" name="items[{{ $qcdtlId }}][qcdtl_id]" value="{{ $qcdtlId }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][podtl_id]" value="{{ $firstDetail?->podtl_id }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][refraksi_persen]" value="0">

        {{-- KARTU 1: INFO PENGIRIMAN, PO & VENDOR KARTON --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🚚</span> <span>1. Info Armada, PO &amp; Vendor Karton</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                {{-- PILIHAN PO KHUSUS KARTON --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <label class="qc-info-label" style="font-weight: 700; margin: 0;">Referensi Purchase Order (PO)</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                            PO Karton ({{ $filteredPos->count() }} Tersedia)
                        </span>
                    </div>
                    <select name="po_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        <option value="">-- Tanpa PO / Kiriman Langsung Karton --</option>
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

                {{-- MITRA SUPPLIER DENGAN QUICK SEARCH --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <label class="qc-info-label" style="font-weight: 700; margin: 0;">Mitra Supplier Vendor *</label>
                        <input type="text" placeholder="Cari vendor..." oninput="filterKartonSuppliers(this.value)" style="width: 120px; font-size: 0.72rem; padding: 2px 6px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <select name="supplier_id" id="editSupplierSelect" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required onchange="onEditSupplierChange(this)">
                        @foreach ($filteredSuppliers as $sup)
                            <option value="{{ $sup->supplier_id }}" data-supplier-nm="{{ $sup->supplier_nm }}" {{ old('supplier_id', $qc->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GUDANG BONGKAR MUAT --}}
                <div>
                    <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.25rem; display: block;">Gudang Bongkar Muat *</label>
                    <select name="gudang_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                        @foreach ($gudangs as $g)
                            <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                {{ $g->gudang_nm }} ({{ $g->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PILIH BARANG (KOMODITAS KARTON BOX) --}}
                <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 0.75rem;">
                    <label class="qc-info-label" style="font-weight: 800; color: #92400e; margin-bottom: 0.35rem; display: block;">
                        Komoditas Karton Box yang Diperiksa *
                    </label>
                    <select name="items[{{ $qcdtlId }}][barang_id]" id="selectEditBarang" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #0f172a;" required onchange="onEditBarangChanged(this)">
                        @foreach ($filteredBarangs as $b)
                            <option value="{{ $b->barang_id }}"
                                    data-nama="{{ $b->barang_nm }}"
                                    {{ old('items.'.$qcdtlId.'.barang_id', $barangId) == $b->barang_id ? 'selected' : '' }}>
                                {{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})
                            </option>
                        @endforeach
                    </select>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.5rem;">
                        <div>
                            <label class="qc-info-label" style="font-size: 0.75rem; margin-bottom: 0.15rem; display: block;">Nama Jenis (Varian Box)</label>
                            <input type="text" name="nama_jenis" id="editNamaJenis" value="{{ old('nama_jenis', $qc->nama_jenis ?: $firstDetail?->barang?->barang_nm) }}" class="form-control" placeholder="Contoh: Karton Box Corrugated" style="font-size: 0.8rem; font-weight: 700;">
                        </div>
                        <div>
                            <label class="qc-info-label" style="font-size: 0.75rem; margin-bottom: 0.15rem; display: block;">Pabrik Produsen</label>
                            <input type="text" name="nama_produsen" id="editNamaProdusen" value="{{ old('nama_produsen', $qc->nama_produsen ?: $qc->supplier?->supplier_nm) }}" class="form-control" placeholder="Nama Pabrik Box" style="font-size: 0.8rem; font-weight: 700;">
                        </div>
                    </div>
                </div>

                {{-- LOGISTIK ARMADA PENGIRIMAN --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">No. Surat Jalan / DO</label>
                        <input type="text" name="nomor_do" value="{{ old('nomor_do', $qc->nomor_do ?: $qc->surat_jalan_supplier) }}" class="form-control" placeholder="Contoh: DO-10928" style="font-weight: 700;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">Plat Nomor Truk / Box</label>
                        <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" placeholder="Contoh: AA 2382 KK" style="font-weight: 800; text-transform: uppercase;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">Nama Pengemudi / Sopir</label>
                        <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" placeholder="Nama sopir" style="font-weight: 700;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">Waktu Kedatangan</label>
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" class="form-control" style="font-size: 0.8rem; font-weight: 700;">
                    </div>
                </div>

                {{-- JUMLAH SURAT JALAN VS FISIK PABRIK & SAMPEL --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="font-size: 0.72rem; margin-bottom: 0.15rem; display: block;">Qty Surat Jalan (PCS)</label>
                        <input type="number" step="1" min="0" name="jumlah_surat_jalan" value="{{ old('jumlah_surat_jalan', (int)($qc->jumlah_surat_jalan ?? $totalGross)) }}" class="form-control" placeholder="0" style="font-weight: 800; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="font-size: 0.72rem; margin-bottom: 0.15rem; display: block;">Qty Fisik Datang (PCS)</label>
                        <input type="number" step="1" min="0" name="jumlah_di_pabrik" value="{{ old('jumlah_di_pabrik', (int)($qc->jumlah_di_pabrik ?? $totalGross)) }}" class="form-control" placeholder="0" style="font-weight: 800; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="font-size: 0.72rem; margin-bottom: 0.15rem; display: block;">Sampel Diperiksa (Pcs)</label>
                        <input type="number" step="1" min="1" name="jumlah_sample_pcs" value="{{ old('jumlah_sample_pcs', (int)($qc->jumlah_sample_pcs ?: 10)) }}" class="form-control" placeholder="10" style="font-weight: 800; font-size: 0.85rem;">
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: AUDIT TRANSPORTASI & JAMINAN HALAL (HACCP) --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🛡️</span> <span>2. Audit Sanitasi Armada &amp; Jaminan Halal</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                {{-- ITEM 1: KONDISI TRANSPORTASI (TRUK / BOX KEMASAN) --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">1. Kebersihan Armada &amp; Kontainer Box</div>
                            <div class="halal-qa-sub">Bebas kotoran, oli, najis, rembesan air atau bau menyengat</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ?? 1) == 0 ? 'active danger' : '' }}" id="btnCemarAda" onclick="setHalalToggle('bebas_cemaran', 0)">Kotor</button>
                            <button type="button" class="segmented-toggle-btn {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ?? 1) == 1 ? 'active ok' : '' }}" id="btnCemarBebas" onclick="setHalalToggle('bebas_cemaran', 1)">Bebas Cemaran</button>
                        </div>
                        <input type="hidden" name="bebas_cemaran_st" id="input_bebas_cemaran" value="{{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ?? 1) }}">
                    </div>
                    <input type="text" name="komentar_transportasi" value="{{ old('komentar_transportasi', $qc->komentar_transportasi) }}" class="form-control" placeholder="Catatan Transportasi (Opsional)..." style="font-size: 0.8rem;">
                </div>

                {{-- ITEM 2: DIANGKUT BERSAMA DENGAN BARANG HARAM? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">2. Apakah diangkut bersama dengan barang haram?</div>
                            <div class="halal-qa-sub">Pemisahan logistik halal mutlak</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st ?? 0) == 0 ? 'active ok' : '' }}" id="btnHaramTidak" onclick="setHalalToggle('angkut_barang_haram', 0)">Tidak (Aman)</button>
                            <button type="button" class="segmented-toggle-btn {{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st ?? 0) == 1 ? 'active danger' : '' }}" id="btnHaramYa" onclick="setHalalToggle('angkut_barang_haram', 1)">Ya (Haram)</button>
                        </div>
                        <input type="hidden" name="angkut_barang_haram_st" id="input_angkut_barang_haram" value="{{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st ?? 0) }}">
                    </div>
                </div>

                {{-- ITEM 3: TERDAFTAR & DISETUJUI LPPOM MUI/BPJPH? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">3. Terdaftar &amp; disetujui LPPOM MUI / BPJPH?</div>
                            <div class="halal-qa-sub">Approved list bahan pembantu &amp; kemasan</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ?? 1) == 0 ? 'active danger' : '' }}" id="btnLppomTidak" onclick="setHalalToggle('terdaftar_lppom', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ?? 1) == 1 ? 'active ok' : '' }}" id="btnLppomYa" onclick="setHalalToggle('terdaftar_lppom', 1)">Ya (Terdaftar)</button>
                        </div>
                        <input type="hidden" name="terdaftar_lppom_st" id="input_terdaftar_lppom" value="{{ old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ?? 1) }}">
                    </div>
                    <input type="text" name="komentar_lppom" value="{{ old('komentar_lppom', $qc->komentar_lppom) }}" class="form-control" placeholder="Komentar LPPOM..." style="font-size: 0.8rem;">
                </div>

                {{-- ITEM 4: MEMPUNYAI SERTIFIKAT HALAL? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">4. Mempunyai dokumen sertifikat halal?</div>
                            <div class="halal-qa-sub">Dokumen sertifikasi resmi dari produsen</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ?? 1) == 0 ? 'active danger' : '' }}" id="btnSertifikatTidak" onclick="setHalalToggle('ada_sertifikat', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ?? 1) == 1 ? 'active ok' : '' }}" id="btnSertifikatYa" onclick="setHalalToggle('ada_sertifikat', 1)">Ya (Ada)</button>
                        </div>
                        <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat" value="{{ old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ?? 1) }}">
                    </div>
                    <input type="text" name="komentar_sertifikat" value="{{ old('komentar_sertifikat', $qc->komentar_sertifikat) }}" class="form-control" placeholder="Nomor / Catatan Sertifikat Halal..." style="font-size: 0.8rem;">
                </div>

                {{-- ITEM 5: SERTIFIKAT HALAL MASIH BERLAKU? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">5. Sertifikat halal masih aktif berlaku?</div>
                            <div class="halal-qa-sub">Validitas masa aktif dokumen</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ old('sertifikat_halal_berlaku_st', $qc->sertifikat_halal_berlaku_st ?? 1) == 0 ? 'active danger' : '' }}" id="btnBerlakuTidak" onclick="setHalalToggle('sertifikat_berlaku', 0)">Kadaluarsa</button>
                            <button type="button" class="segmented-toggle-btn {{ old('sertifikat_halal_berlaku_st', $qc->sertifikat_halal_berlaku_st ?? 1) == 1 ? 'active ok' : '' }}" id="btnBerlakuYa" onclick="setHalalToggle('sertifikat_berlaku', 1)">Ya (Aktif)</button>
                        </div>
                        <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_berlaku" value="{{ old('sertifikat_halal_berlaku_st', $qc->sertifikat_halal_berlaku_st ?? 1) }}">
                    </div>
                    <input type="text" name="komentar_berlaku" value="{{ old('komentar_berlaku', $qc->komentar_berlaku) }}" class="form-control" placeholder="Tanggal masa berlaku..." style="font-size: 0.8rem;">
                </div>
            </div>
        </div>

        {{-- KARTU 3: PENGUJIAN MUTU KARTON BOX (DIMENSI & CACAT) --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🔬</span> <span>3. Pengujian Mutu Karton Box &amp; Cacat Kemasan</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- STATUS RAW MATERIAL & KEMASAN --}}
                <div class="karton-subcard">
                    <span class="karton-subcard-title">
                        <span>🔍</span> <span>Kesesuaian Raw Material &amp; Wadah Kemasan</span>
                    </span>

                    <div class="qc-grid-2col">
                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.8rem; color: #0f172a; margin-bottom: 0.35rem;">2. Isi Raw Material:</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'active ok' : '' }}" id="btnRawOk" onclick="setRawMaterialStatus('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material) === 'TDK_STD' ? 'active danger' : '' }}" id="btnRawTdkStd" onclick="setRawMaterialStatus('TDK_STD')">
                                    <span>✖</span> <span>TDK STD</span>
                                </button>
                            </div>
                            <input type="hidden" name="items[{{ $qcdtlId }}][status_raw_material]" id="inputRawMaterial" value="{{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK') }}">
                        </div>

                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.8rem; color: #0f172a; margin-bottom: 0.35rem;">Wadah Kemasan Box:</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn {{ old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? 'active ok' : '' }}" id="btnKemasanOk" onclick="setKemasanKondisi('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn {{ old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi) === 'TIDAK_STANDARD' ? 'active danger' : '' }}" id="btnKemasanTdkStd" onclick="setKemasanKondisi('TIDAK_STANDARD')">
                                    <span>✖</span> <span>TDK STD</span>
                                </button>
                            </div>
                            <input type="hidden" name="items[{{ $qcdtlId }}][kemasan_kondisi]" id="inputKemasanKondisi" value="{{ old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi ?? 'OK') }}">
                        </div>
                    </div>
                </div>

                {{-- CHECKLIST 7 CACAT KEMASAN --}}
                <div class="karton-subcard">
                    <span class="karton-subcard-title" style="color: #475569;">
                        <span>⚠️</span> <span>Checklist 7 Titik Cacat Kemasan (Ketuk jika ditemukan cacat):</span>
                    </span>

                    <div class="modern-checklist-grid">
                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatKotor')">
                            <div class="modern-check-label">
                                <span>🧼</span> <span>KOTOR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatKotor">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_kotor]" id="checkCacatKotor" value="1" {{ old('items.'.$qcdtlId.'.kemasan_kotor', $firstDetail?->kemasan_kotor) ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatApek')">
                            <div class="modern-check-label">
                                <span>👃</span> <span>APEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatApek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_apek]" id="checkCacatApek" value="1" {{ old('items.'.$qcdtlId.'.kemasan_apek', $firstDetail?->kemasan_apek) ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatBasah')">
                            <div class="modern-check-label">
                                <span>💧</span> <span>BASAH</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatBasah">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_basah]" id="checkCacatBasah" value="1" {{ old('items.'.$qcdtlId.'.kemasan_basah', $firstDetail?->kemasan_basah) ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatJamur')">
                            <div class="modern-check-label">
                                <span>🍄</span> <span>JAMUR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatJamur">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_jamur]" id="checkCacatJamur" value="1" {{ old('items.'.$qcdtlId.'.kemasan_jamur', $firstDetail?->kemasan_jamur) ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatSobek')">
                            <div class="modern-check-label">
                                <span>✂️</span> <span>SOBEK/PENYOK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatSobek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_sobek]" id="checkCacatSobek" value="1" {{ old('items.'.$qcdtlId.'.kemasan_sobek', $firstDetail?->kemasan_sobek) ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatBerminyak')">
                            <div class="modern-check-label">
                                <span>🛢️</span> <span>BERMINYAK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatBerminyak">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_berminyak]" id="checkCacatBerminyak" value="1" {{ old('items.'.$qcdtlId.'.kemasan_berminyak', $firstDetail?->kemasan_berminyak) ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatBerdebu')">
                            <div class="modern-check-label">
                                <span>💨</span> <span>BERDEBU</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatBerdebu">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_berdebu]" id="checkCacatBerdebu" value="1" {{ old('items.'.$qcdtlId.'.kemasan_berdebu', $firstDetail?->kemasan_berdebu) ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>
                    </div>
                </div>

                {{-- ANALISA PARAMETER DIMENSI P x L x T & SPESIFIKASI --}}
                <div class="karton-subcard">
                    <span class="karton-subcard-title" style="color: #b45309; margin-bottom: 0.25rem;">
                        <span>🔬</span> <span>Analisa Dimensi P x L x T &amp; Spesifikasi Box</span>
                    </span>

                    <div class="param-analysis-wrap">
                        {{-- 1. DIMENSI PANJANG --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>📏</span> PANJANG (P)
                                </span>
                                <div class="param-std-badge">
                                    <span>Std:</span>
                                    <input type="text" name="items[{{ $qcdtlId }}][dimensi_panjang_standar]" value="{{ old('items.'.$qcdtlId.'.dimensi_panjang_standar', $firstDetail?->dimensi_panjang_standar ?: ($firstDetail?->panjang_standar ?: '40 cm')) }}" style="width: 75px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '40 cm')">40 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '39.5 cm')">39.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '40.5 cm')">40.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '41 cm')">41 cm</button>
                            </div>
                            <input type="text" name="items[{{ $qcdtlId }}][dimensi_panjang_analisa]" id="inputPanjangAnalisa" value="{{ old('items.'.$qcdtlId.'.dimensi_panjang_analisa', $firstDetail?->dimensi_panjang_analisa ?: ($firstDetail?->panjang_cm ? $firstDetail->panjang_cm . ' cm' : '')) }}" class="form-control" placeholder="Contoh: 40 cm" style="font-weight: 800;">
                        </div>

                        {{-- 2. DIMENSI LEBAR --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>📐</span> LEBAR (L)
                                </span>
                                <div class="param-std-badge">
                                    <span>Std:</span>
                                    <input type="text" name="items[{{ $qcdtlId }}][dimensi_lebar_standar]" value="{{ old('items.'.$qcdtlId.'.dimensi_lebar_standar', $firstDetail?->dimensi_lebar_standar ?: ($firstDetail?->lebar_standar ?: '30 cm')) }}" style="width: 75px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '30 cm')">30 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '29.5 cm')">29.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '30.5 cm')">30.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '31 cm')">31 cm</button>
                            </div>
                            <input type="text" name="items[{{ $qcdtlId }}][dimensi_lebar_analisa]" id="inputLebarAnalisa" value="{{ old('items.'.$qcdtlId.'.dimensi_lebar_analisa', $firstDetail?->dimensi_lebar_analisa ?: ($firstDetail?->lebar_cm ? $firstDetail->lebar_cm . ' cm' : '')) }}" class="form-control" placeholder="Contoh: 30 cm" style="font-weight: 800;">
                        </div>

                        {{-- 3. DIMENSI TINGGI --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>📦</span> TINGGI (T)
                                </span>
                                <div class="param-std-badge">
                                    <span>Std:</span>
                                    <input type="text" name="items[{{ $qcdtlId }}][dimensi_tinggi_standar]" value="{{ old('items.'.$qcdtlId.'.dimensi_tinggi_standar', $firstDetail?->dimensi_tinggi_standar ?: ($firstDetail?->tinggi_standar ?: '25 cm')) }}" style="width: 75px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '25 cm')">25 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '24.5 cm')">24.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '25.5 cm')">25.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '26 cm')">26 cm</button>
                            </div>
                            <input type="text" name="items[{{ $qcdtlId }}][dimensi_tinggi_analisa]" id="inputTinggiAnalisa" value="{{ old('items.'.$qcdtlId.'.dimensi_tinggi_analisa', $firstDetail?->dimensi_tinggi_analisa ?: ($firstDetail?->tinggi_cm ? $firstDetail->tinggi_cm . ' cm' : '')) }}" class="form-control" placeholder="Contoh: 25 cm" style="font-weight: 800;">
                        </div>

                        {{-- 4. SPESIFIKASI / GRAMATUR --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>⚖️</span> SPESIFIKASI / GRAMATUR
                                </span>
                                <div class="param-std-badge">
                                    <span>Std:</span>
                                    <input type="text" name="items[{{ $qcdtlId }}][spesifikasi_standar]" value="{{ old('items.'.$qcdtlId.'.spesifikasi_standar', $firstDetail?->spesifikasi_standar ?: 'Sesuai Spesifikasi MFI') }}" style="width: 130px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputSpesifikasiAnalisa', 'K150/M125/K150 B/F')">K150/M125/K150 B/F</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputSpesifikasiAnalisa', 'Double Wall 5 Ply')">Double Wall 5 Ply</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputSpesifikasiAnalisa', 'Single Wall 3 Ply')">Single Wall 3 Ply</button>
                            </div>
                            <input type="text" name="items[{{ $qcdtlId }}][spesifikasi_analisa]" id="inputSpesifikasiAnalisa" value="{{ old('items.'.$qcdtlId.'.spesifikasi_analisa', $firstDetail?->spesifikasi_analisa ?: ($firstDetail?->spesifikasi_gramatur ?: 'K150/M125/K150 B/F')) }}" class="form-control" placeholder="Contoh: K150/M125/K150 B/F" style="font-weight: 800;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 4: HASIL PEMERIKSAAN KUANTITAS & DISPOSISI GUDANG --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>⚖️</span> <span>4. Kuantitas Hasil Uji &amp; Disposisi Gudang</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- BANNER NETTO HASIL KALKULASI --}}
                <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.72rem; font-weight: 800; color: #92400e; text-transform: uppercase;">Netto Diterima Pabrik:</div>
                        <div style="font-size: 0.72rem; color: #b45309;">Gross dikurangi reject kerusakan</div>
                    </div>
                    <div id="labelKartonEditNetto" style="font-size: 1.25rem; font-weight: 900; color: #b45309;">
                        {{ number_format($totalNetto, 0, ',', '.') }} Pcs
                    </div>
                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputQtyNettoLolos" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $firstDetail?->qty_netto_lolos ?? $totalNetto) }}">
                </div>

                {{-- INPUT GROSS & REJECT --}}
                <div class="qc-grid-2col">
                    <div>
                        <label class="qc-info-label" style="font-weight: 800; color: #0f172a; margin-bottom: 0.25rem; display: block;">
                            Jumlah Datang / Lolos (PCS) *
                        </label>
                        <input type="number" step="1" min="0" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="editQtyGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', (int)($firstDetail?->qty_timbang_gross ?? $totalGross)) }}" class="form-control" style="font-size: 1rem; font-weight: 800;" required oninput="calcKartonEditNetto()">
                    </div>

                    <div>
                        <label class="qc-info-label" style="font-weight: 800; color: #dc2626; margin-bottom: 0.25rem; display: block;">
                            Qty Reject / Cacat (PCS)
                        </label>
                        <input type="number" step="1" min="0" name="items[{{ $qcdtlId }}][qty_reject]" id="editQtyReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', (int)($firstDetail?->qty_reject ?? 0)) }}" class="form-control" style="font-size: 1rem; font-weight: 800; color: #dc2626;" oninput="calcKartonEditNetto()">
                    </div>
                </div>

                {{-- PILIHAN DISPOSISI: TERIMA vs TOLAK --}}
                @php
                    $isTolakAwal = old('items.'.$qcdtlId.'.keputusan_qc', $firstDetail?->keputusan_qc ?? '') === 'REJECT_TOTAL' || $qc->status_qc === 'DITOLAK_TOTAL';
                @endphp
                <input type="hidden" name="karton_kesimpulan" id="inputKartonKesimpulan" value="{{ $isTolakAwal ? 'TOLAK' : 'TERIMA' }}">
                <input type="hidden" name="kesimpulan_qc" id="inputKesimpulanQc" value="{{ $isTolakAwal ? 'TOLAK' : 'TERIMA' }}">

                <div class="qc-disposisi-grid">
                    <!-- KARTU TERIMA -->
                    <div class="qc-disposisi-card {{ !$isTolakAwal ? 'active terima' : '' }}" id="cardDisposisiTerima" onclick="setKesimpulanKarton('TERIMA')">
                        <div class="qc-disposisi-head">
                            <div class="qc-disposisi-title-wrap">
                                <span class="qc-disposisi-radio-icon"></span>
                                <span class="qc-disposisi-title">TERIMA</span>
                            </div>
                            <span class="qc-disposisi-status-badge">✓</span>
                        </div>
                        <div class="qc-disposisi-qty-wrap">
                            <span class="qc-disposisi-qty-label">Kuantitas Diterima:</span>
                            <div class="qc-disposisi-input-box">
                                <input type="number" step="1" min="0" name="qty_terima" id="inputQtyTerima" class="qc-disposisi-input" value="{{ (int)$totalNetto }}" readonly>
                                <span class="qc-disposisi-unit">pcs</span>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU TOLAK -->
                    <div class="qc-disposisi-card {{ $isTolakAwal ? 'active tolak' : '' }}" id="cardDisposisiTolak" onclick="setKesimpulanKarton('TOLAK')">
                        <div class="qc-disposisi-head">
                            <div class="qc-disposisi-title-wrap">
                                <span class="qc-disposisi-radio-icon"></span>
                                <span class="qc-disposisi-title">TOLAK</span>
                            </div>
                            <span class="qc-disposisi-status-badge">✕</span>
                        </div>
                        <div class="qc-disposisi-qty-wrap">
                            <span class="qc-disposisi-qty-label">Kuantitas Ditolak:</span>
                            <div class="qc-disposisi-input-box">
                                <input type="number" step="1" min="0" name="qty_tolak" id="inputQtyTolak" class="qc-disposisi-input" value="{{ (int)$totalReject }}" readonly>
                                <span class="qc-disposisi-unit">pcs</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CATATAN SPESIFIKASI MUTU FISIK & HIDDEN GRADE --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    {{-- Hidden grade_cd agar integritas DB aman tanpa menampilkan Grade UI --}}
                    <input type="hidden" name="items[{{ $qcdtlId }}][grade_cd]" id="selectGradeCd" value="{{ old('items.'.$qcdtlId.'.grade_cd', $firstDetail?->grade_cd ?? 'A') }}">
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem; font-size: 0.8rem; color: #0f172a;">Catatan Spesifikasi Mutu Fisik Box</label>
                    <input type="text" name="items[{{ $qcdtlId }}][catatan_dtl]" value="{{ old('items.'.$qcdtlId.'.catatan_dtl', $firstDetail?->catatan_dtl) }}" class="form-control" placeholder="Contoh: Dimensi presisi, ketebalan corrugated sesuai standar, bersih..." style="width: 100%; border-radius: 6px; font-size: 0.85rem;">
                </div>
            </div>
        </div>

        {{-- KARTU 5: KEPUTUSAN KESIMPULAN & CATATAN OTORISASI --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>💬</span> <span>5. Keputusan &amp; Catatan QC Karton</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Keputusan Akhir QC:</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-weight: 800; cursor: pointer;">
                            <input type="radio" name="items[{{ $qcdtlId }}][keputusan_qc]" id="radioTerima" value="TERIMA" {{ old('items.'.$qcdtlId.'.keputusan_qc', $firstDetail?->keputusan_qc ?? 'TERIMA') !== 'REJECT_TOTAL' ? 'checked' : '' }} onchange="setKesimpulanKarton('TERIMA')">
                            <span style="color: #15803d;">Lolos Masuk Gudang (Diterima)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-weight: 800; cursor: pointer;">
                            <input type="radio" name="items[{{ $qcdtlId }}][keputusan_qc]" id="radioTolak" value="REJECT_TOTAL" {{ old('items.'.$qcdtlId.'.keputusan_qc', $firstDetail?->keputusan_qc) === 'REJECT_TOTAL' ? 'checked' : '' }} onchange="setKesimpulanKarton('TOLAK')">
                            <span style="color: #dc2626;">Tolak Total (Afkir / Pulangkan)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Catatan Pengujian &amp; Rekomendasi</label>
                    <textarea name="catatan_umum" rows="2" class="form-control" placeholder="Tuliskan catatan kondisi box, kerusakan atau rekomendasi...">{{ old('catatan_umum', $qc->catatan_umum) }}</textarea>
                </div>

                {{-- PETUGAS QC & SUPERVISOR PENINJAU --}}
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.65rem; display: flex; flex-direction: column; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">Petugas QC Raw Material *</label>
                        <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama ?: auth()->user()?->name) }}" class="form-control" style="font-weight: 800; color: #0f172a;" required>
                    </div>

                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">QC Supervisor / Lab Head Peninjau</label>
                        <input type="text" name="qc_supervisor_nama" list="listSupervisorQc" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama ?: 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" class="form-control" style="font-weight: 800; color: #0f172a;">
                        <datalist id="listSupervisorQc">
                            <option value="Hendrawan, S.TP (SPV QC Shift Pagi)">
                            <option value="Rina Wijaya, S.Si (Supervisor QC Lab)">
                            <option value="Dedi (QC Bahan Baku)">
                            <option value="Bambang (Produksi Magelang)">
                            <option value="Super Administrator">
                            <option value="Ahmad Farhan, S.T (Ka. Gudang)">
                        </datalist>
                    </div>
                </div>
            </div>
        </div>

        {{-- FLOATING ACTION DOCK BOTTOM --}}
        <div class="qc-form-floating-dock" id="qcFloatingDock">
            <a href="{{ $backUrl }}" class="btn-dock-back" style="display: inline-flex; text-decoration: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Batal</span>
            </a>

            <button type="submit" class="btn-dock-next btn-primary-state" style="background: #d97706; border-color: #d97706;">
                <span>Simpan Perubahan QC</span>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </button>
        </div>
    </form>
</div>

{{-- SUBMIT OVERLAY LOADING --}}
<div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); color: #ffffff;">
    <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center; max-width: 320px; width: 90%;">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📦</div>
        <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem;">Menyimpan Koreksi Karton...</h3>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Memperbarui data inspeksi karton box.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/karton/karton.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/karton/karton-edit.js') }}"></script>
@endpush
