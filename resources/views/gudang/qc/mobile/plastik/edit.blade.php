@extends('layouts.qc-mobile')

@section('title', 'Koreksi QC Plastik: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}?v={{ filemtime(public_path('css/gudang/qc/mobile/qc-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}?v={{ filemtime(public_path('css/gudang/qc/mobile/qc-mobile-detail.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik.css') }}?v={{ filemtime(public_path('css/gudang/qc/plastik/plastik.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik-detail.css') }}?v={{ filemtime(public_path('css/gudang/qc/plastik/plastik-detail.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik-edit.css') }}?v={{ filemtime(public_path('css/gudang/qc/plastik/plastik-edit.css')) }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;

    // Filter daftar PO yang memuat Plastik
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (
            str_contains($nm, 'PLASTIK') || str_contains($nm, 'KEMASAN') || str_contains($nm, 'OPP') ||
            str_contains($nm, 'ROLL') || str_contains($nm, 'POUCH') || str_contains($nm, 'STANDING') ||
            str_starts_with($cd, 'BP-PL') || str_starts_with($cd, 'BP-KM') || str_starts_with($cd, 'PHD') ||
            str_starts_with($cd, 'PEA') || str_starts_with($cd, 'PEC') || str_starts_with($cd, 'PNG')
        ) {
            return 'PLASTIK';
        }
        return 'LAINNYA';
    };

    $filteredPos = $pos->filter(function($p) use ($qc, $classifyBarang) {
        if ($p->po_id == $qc->po_id) return true;
        foreach ($p->details as $d) {
            if ($classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd) === 'PLASTIK') {
                return true;
            }
        }
        return false;
    });

    if ($filteredPos->isEmpty() && $qc->po) {
        $filteredPos = collect([$qc->po]);
    }

    // Filter Supplier khusus Kemasan / Plastik (Non-Petani)
    $filteredSuppliers = $suppliers->filter(function($s) use ($qc) {
        if ($s->supplier_id == $qc->supplier_id) return true;
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($filteredSuppliers->isEmpty()) {
        $filteredSuppliers = $suppliers;
    }

    // Filter Master Barang Plastik
    $filteredBarangs = $barangs->filter(function($b) use ($classifyBarang, $firstDetail) {
        if ($firstDetail && $b->barang_id == $firstDetail->barang_id) return true;
        return $classifyBarang($b->barang_nm, $b->barang_cd) === 'PLASTIK';
    });
    if ($filteredBarangs->isEmpty()) {
        $filteredBarangs = $barangs;
    }

    $barangId = $firstDetail?->barang_id ?? ($filteredBarangs->first()?->barang_id);

    // Satuan
    $satuanRaw = $firstDetail?->barang?->satuanDasar?->satuan_nm
        ?: ($firstDetail?->barang?->satuanDasar?->satuan_cd ?: 'PCS');
    $isKg = strtoupper(trim($satuanRaw)) === 'KG';
    $satuanDtl = $isKg ? 'KG' : (strtoupper(trim($satuanRaw)) === 'LEMBAR' ? 'Lembar' : 'Pcs');

    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');

    // Format Numerik Bersih
    $sjVal = (float) ($qc->jumlah_surat_jalan ?? $firstDetail?->qty_timbang_gross ?? 0);
    $pabrikVal = (float) ($qc->jumlah_di_pabrik ?? ($firstDetail?->qty_timbang_gross ?? $totalGross));
    $grossVal = (float) ($firstDetail?->qty_timbang_gross ?? ($qc->jumlah_di_pabrik ?? $totalGross));
    $rejectVal = (float) ($firstDetail?->qty_reject ?? $totalReject);
    $nettoVal = (float) ($firstDetail?->qty_netto_lolos ?? $totalNetto);
    $sampleVal = (int) ($qc->jumlah_sample_pcs ?? 10);

    $statusRawVal = old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK');
    $kemasanKondisiVal = old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi ?? 'OK');
    $kesimpulanVal = old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : 'TOLAK');

    $ref = request('ref');
    if ($ref === 'detail') {
        $backUrl = route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']);
        $backLabel = 'Batal ke Detail';
    } else {
        $backUrl = route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']);
        $backLabel = 'Batal ke Riwayat';
    }
@endphp

@section('content')
<div class="plastik-edit-wrap">

    {{-- TOP APP NAV --}}
    <div class="qc-detail-top-nav">
        <a href="{{ $backUrl }}" class="qc-detail-back-btn" title="{{ $backLabel }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Batal &amp; Kembali</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn-qc-switch-desktop" style="font-size: 0.75rem; font-weight: 700; color: #0f766e; background: #ccfbf1; border: 1.5px solid #99f6e4; padding: 0.35rem 0.65rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;" title="Kembali ke Tabel QC Web Desktop">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Tabel Web QC</span>
                </a>
            @endif
            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
                QC Plastik &bull; Edit
            </span>
        </div>
    </div>

    {{-- HEADER CARD HERO MOBILE --}}
    <div class="qc-detail-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%);">
        <div class="qc-detail-hero-top">
            <div>
                <span class="badge-tag-commodity" style="background: rgba(15, 118, 110, 0.4); color: #ccfbf1;">
                    🛍️ PLASTIK KEMASAN
                </span>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem; color: #ffffff;">Edit Tiket #{{ $qc->qc_no }}</h1>
            </div>
            <span style="background: #ccfbf1; color: #0f766e; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 20px;">
                Mode Mobile
            </span>
        </div>
        <p style="font-size: 0.8rem; margin: 0.4rem 0 0; opacity: 0.85; line-height: 1.4;">
            Koreksi parameter sampling mutu plastik, ketebalan mikrometer, visual seal, atau kuantitas kedatangan.
        </p>
    </div>

    {{-- ALERT JIKA SUDAH DIPROSES KE GUDANG (GRN) --}}
    @if ($qc->terima)
        <div style="background: #eff6ff; border: 1.5px solid #93c5fd; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; gap: 0.65rem; align-items: flex-start;">
            <span style="font-size: 1.25rem; line-height: 1;">📦</span>
            <div style="font-size: 0.78rem; color: #1e40af; line-height: 1.4;">
                <strong>Perhatian:</strong> Tiket ini sudah ditarik ke Penerimaan Gudang (GRN <strong>#{{ $qc->terima->terima_no }}</strong>).
                Setiap revisi netto atau kuantitas reject akan menyinkronkan stok gudang secara otomatis.
            </div>
        </div>
    @endif

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

    {{-- FORM UTAMA MOBILE EDIT PLASTIK --}}
    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditPlastikForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="ref" value="{{ $ref ?: 'index' }}">
        <input type="hidden" name="kategori_barang" value="PLASTIK">
        <input type="hidden" name="batch_no" value="{{ old('batch_no', $qc->batch_no) }}">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">
        <input type="hidden" name="status_uji_goreng" value="SELESAI">
        <input type="hidden" name="items[{{ $qcdtlId }}][qcdtl_id]" value="{{ $qcdtlId }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][podtl_id]" value="{{ $firstDetail?->podtl_id }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][refraksi_persen]" value="0">

        {{-- ========================================================================= --}}
        {{-- KARTU 1: INFO PENGIRIMAN, PO & VENDOR KEMASAN                           --}}
        {{-- ========================================================================= --}}
        <div class="plastik-edit-card">
            <div class="plastik-edit-card-header theme-teal">
                <div class="plastik-edit-card-header-left">
                    <span class="plastik-step-badge">1</span>
                    <div>
                        <h2 class="plastik-edit-card-title">Info Armada, PO &amp; Vendor Kemasan</h2>
                        <div class="plastik-edit-card-subtitle">Identitas Surat Jalan, Nomor PO &amp; Pihak Pemasok</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🚚</span>
            </div>

            <div class="plastik-edit-card-body">
                {{-- PILIHAN PO KHUSUS PLASTIK --}}
                <div class="plastik-field-group">
                    <div class="plastik-field-header">
                        <label class="plastik-field-label">Referensi Purchase Order (PO)</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #0f766e; background: #ccfbf1; padding: 2px 7px; border-radius: 5px;">
                            PO Kemasan ({{ $filteredPos->count() }} Tersedia)
                        </span>
                    </div>
                    <select name="po_id" id="editPoSelect" class="form-control" style="font-weight: 700;" onchange="onEditPoChanged(this)">
                        <option value="">-- Tanpa PO / Kiriman Langsung Plastik --</option>
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
                <div class="plastik-field-group">
                    <div class="plastik-field-header">
                        <label class="plastik-field-label">Mitra Supplier / Produsen Kemasan <span class="required-star">*</span></label>
                        <span id="badgeEditLockSupplier" class="badge-po-locked" style="{{ $qc->po_id ? 'display: inline-flex;' : 'display: none;' }} font-size: 0.7rem; font-weight: 800; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">🔒 Terkunci ({{ $qc->supplier?->supplier_nm }})</span>
                    </div>
                    <div id="wrapEditSupplierSearch" style="margin-bottom: 0.25rem; {{ $qc->po_id ? 'display: none;' : '' }}">
                        <input type="text" id="editSupplierSearch" class="form-control" placeholder="🔍 Cari vendor supplier kemasan..." oninput="filterEditSuppliers(this.value)" style="min-height: 38px; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 8px;">
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
                <div class="plastik-field-group">
                    <div class="plastik-field-header">
                        <label class="plastik-field-label">Gudang Tujuan Bongkar <span class="required-star">*</span></label>
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

                {{-- MASTER BARANG PLASTIK --}}
                <div class="plastik-field-group">
                    <div class="plastik-field-header">
                        <label class="plastik-field-label">Komoditas Kemasan (Master Data) <span class="required-star">*</span></label>
                        <span id="badgeEditLockBarang" class="badge-po-locked" style="{{ $qc->po_id ? 'display: inline-flex;' : 'display: none;' }} font-size: 0.7rem; font-weight: 800; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">🔒 Terkunci dari PO</span>
                    </div>
                    <select name="items[{{ $qcdtlId }}][barang_id]" id="editBarangSelect" class="form-control" style="font-weight: 800; color: #0f766e; {{ $qc->po_id ? 'pointer-events: none; background-color: #f1f5f9; border-color: #cbd5e1;' : '' }}" required onchange="onEditBarangChanged(this)" tabindex="{{ $qc->po_id ? '-1' : '0' }}">
                        @foreach ($filteredBarangs as $b)
                            <option value="{{ $b->barang_id }}"
                                    data-nama="{{ $b->barang_nm }}"
                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? 'PCS' }}"
                                    data-satuan-cd="{{ strtoupper($b->satuanDasar?->satuan_cd ?? 'PCS') }}"
                                    {{ old('items.'.$qcdtlId.'.barang_id', $barangId) == $b->barang_id ? 'selected' : '' }}>
                                {{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- NAMA JENIS SPESIFIKASI KEMASAN --}}
                <div class="plastik-field-group">
                    <div class="plastik-field-header">
                        <label class="plastik-field-label">Spesifikasi / Nama Jenis Kemasan</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #0f766e; background: #ccfbf1; padding: 1px 7px; border-radius: 4px;">Form HACCP MFI</span>
                    </div>
                    <input type="text" name="nama_jenis" id="editNamaJenis" list="listPlastikJenisEdit" value="{{ old('nama_jenis', $qc->nama_jenis ?? 'Plastik Kemasan Keripik Cap Payung 250g') }}" class="form-control" style="font-weight: 800; color: #0f766e;" placeholder="Contoh: Plastik OPP Roll 250g (Tebal 0.08mm)">
                    <datalist id="listPlastikJenisEdit">
                        <option value="Plastik Kemasan Keripik Cap Payung 250g">
                        <option value="Plastik OPP Roll 250g (Tebal 0.08mm)">
                        <option value="Plastik OPP Roll 500g">
                        <option value="Standing Pouch Cap Payung 250g">
                        <option value="Standing Pouch Zipper 500g Metalized">
                        <option value="Plastik PP Tebal 0.08mm Seal Rapi">
                    </datalist>
                </div>

                {{-- NEGARA PRODUSEN --}}
                <div class="plastik-field-group">
                    <label class="plastik-field-label">Negara Produsen</label>
                    <input type="text" name="negara_produsen" value="{{ old('negara_produsen', $qc->negara_produsen ?: 'Indonesia') }}" class="form-control" placeholder="Indonesia" style="font-weight: 700;">
                </div>
                <input type="hidden" name="nama_produsen" id="editNamaProdusen" value="{{ old('nama_produsen', $qc->nama_produsen ?: $qc->supplier?->supplier_nm) }}">

                {{-- SURAT JALAN & PLAT TRUK --}}
                <div class="plastik-grid-2col stack-on-mobile">
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">No. DO (Surat Jalan)</label>
                        <input type="text" name="nomor_do" id="inputNomorDo" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" class="form-control" style="font-weight: 700;" oninput="const sjEl = document.getElementById('inputSuratJalanSupplier'); if(sjEl) sjEl.value = this.value;">
                        <input type="hidden" name="surat_jalan_supplier" id="inputSuratJalanSupplier" value="{{ old('surat_jalan_supplier', $qc->surat_jalan_supplier ?? $qc->nomor_do) }}">
                    </div>
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">Plat Truk / Mobil Box</label>
                        <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" style="font-weight: 800; text-transform: uppercase;">
                    </div>
                </div>

                {{-- NAMA SOPIR & TANGGAL DATANG --}}
                <div class="plastik-grid-2col stack-on-mobile">
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">Nama Sopir</label>
                        <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" placeholder="Nama pengemudi">
                    </div>
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">Tanggal Datang</label>
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="form-control" style="font-size: 0.85rem; font-weight: 700;">
                    </div>
                </div>

                {{-- JUMLAH SAMPEL QC --}}
                <div class="plastik-field-group">
                    <label class="plastik-field-label">Sampel QC Diperiksa</label>
                    <div class="plastik-suffix-wrap">
                        <input type="number" step="1" min="1" name="jumlah_sample_pcs" value="{{ old('jumlah_sample_pcs', $sampleVal) }}" class="form-control" style="font-weight: 800; color: #0d9488;" placeholder="10">
                        <span class="plastik-suffix-label label-satuan-text">{{ $satuanDtl }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KARTU 2: STANDAR TRANSPORTASI & JAMINAN HALAL (SJH & CCP)                 --}}
        {{-- ========================================================================= --}}
        <div class="plastik-edit-card" style="border-color: #99f6e4;">
            <div class="plastik-edit-card-header theme-green">
                <div class="plastik-edit-card-header-left">
                    <span class="plastik-step-badge green">2</span>
                    <div>
                        <h2 class="plastik-edit-card-title" style="color: #166534;">Standar Transportasi &amp; Jaminan Halal</h2>
                        <div class="plastik-edit-card-subtitle" style="color: #15803d;">Sistem Jaminan Halal (SJH) &amp; CCP Kebersihan Armada</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🛡️</span>
            </div>

            <div class="plastik-edit-card-body">
                {{-- ITEM 1: KONDISI TRANSPORTASI --}}
                <div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 0.45rem;">
                        1. Kondisi Transportasi (Truk / Box Kemasan)
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label class="transport-radio-card {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'active' : '' }}" id="cardTransportBebas">
                            <input type="radio" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'checked' : '' }} onchange="updateTransportCard(this)">
                            <div class="transport-card-text">
                                <div class="transport-card-title" style="color: #0d9488;">Tidak ada cemaran, Najis / Kotoran</div>
                                <div class="transport-card-desc">Bak mobil box bersih, kering, bebas bau asing &amp; bebas bahan kimia</div>
                            </div>
                        </label>

                        <label class="transport-radio-card is-danger {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 0 ? 'active' : '' }}" id="cardTransportCemar">
                            <input type="radio" name="bebas_cemaran_st" value="0" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 0 ? 'checked' : '' }} onchange="updateTransportCard(this)">
                            <div class="transport-card-text">
                                <div class="transport-card-title" style="color: #dc2626;">Ada cemaran / Kotor / Najis</div>
                                <div class="transport-card-desc">Ditemukan indikasi kontaminasi, basah kotoran, atau bau menyengat</div>
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
                            <div class="halal-qa-sub">Cek daftar bahan approved list kemasan</div>
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
                            <div class="halal-qa-sub">Dokumen sertifikat halal produsen resmi</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $adaSertifikatVal == 0 ? 'active danger' : '' }}" id="btnSertifikatTidak" onclick="setHalalToggle('ada_sertifikat_halal', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ $adaSertifikatVal == 1 ? 'active ok' : '' }}" id="btnSertifikatYa" onclick="setHalalToggle('ada_sertifikat_halal', 1)">Ya</button>
                        </div>
                        <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat_halal" value="{{ $adaSertifikatVal }}">
                    </div>
                    <input type="text" name="komentar_sertifikat" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_sertifikat', $qc->komentar_sertifikat) }}" style="font-size: 0.85rem;">
                </div>

                {{-- ITEM 5: SERTIFIKAT HALAL MASIH BERLAKU? --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">5. Apakah sertifikat halal barang tersebut masih berlaku?</div>
                            <div class="halal-qa-sub">Validitas status aktif sertifikat kemasan</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $sertifikatBerlakuVal == 0 ? 'active danger' : '' }}" id="btnBerlakuTidak" onclick="setHalalToggle('sertifikat_halal_berlaku', 0)">Tidak</button>
                            <button type="button" class="segmented-toggle-btn {{ $sertifikatBerlakuVal == 1 ? 'active ok' : '' }}" id="btnBerlakuYa" onclick="setHalalToggle('sertifikat_halal_berlaku', 1)">Ya</button>
                        </div>
                        <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_halal_berlaku" value="{{ $sertifikatBerlakuVal }}">
                    </div>
                    <input type="text" name="komentar_berlaku" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_berlaku', $qc->komentar_berlaku) }}" style="font-size: 0.85rem;">
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KARTU 3: HASIL KUANTITAS & KEDATANGAN PABRIK                             --}}
        {{-- ========================================================================= --}}
        <div class="plastik-edit-card">
            <div class="plastik-edit-card-header theme-teal">
                <div class="plastik-edit-card-header-left">
                    <span class="plastik-step-badge">3</span>
                    <div>
                        <h2 class="plastik-edit-card-title">Hasil Kuantitas &amp; Kedatangan Pabrik</h2>
                        <div class="plastik-edit-card-subtitle">Verifikasi Dokumen Surat Jalan vs Kedatangan Fisik</div>
                    </div>
                </div>

                {{-- SATUAN TOGGLE --}}
                <div class="plastik-satuan-toggle">
                    <button type="button" class="plastik-satuan-btn {{ $satuanDtl !== 'KG' ? 'active' : '' }}" id="pillSatuan_PCS" onclick="setPlastikEditSatuan('PCS')">
                        <span>🛍️</span> <span>PCS</span>
                    </button>
                    <button type="button" class="plastik-satuan-btn {{ $satuanDtl === 'KG' ? 'active' : '' }}" id="pillSatuan_KG" onclick="setPlastikEditSatuan('KG')">
                        <span>⚖️</span> <span>KG</span>
                    </button>
                </div>
                <input type="hidden" name="satuan" id="inputPlastikSatuan" value="{{ $satuanDtl }}">
                <input type="hidden" name="items[{{ $qcdtlId }}][satuan]" id="inputPlastikItemSatuan" value="{{ $satuanDtl }}">
            </div>

            <div class="plastik-edit-card-body">
                <div class="plastik-grid-2col stack-on-mobile">
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">Jumlah Surat Jalan</label>
                        <div class="plastik-suffix-wrap">
                            <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="jumlah_surat_jalan" id="inputJumlahSJ" value="{{ old('jumlah_surat_jalan', $sjVal) }}" class="form-control" style="font-weight: 800;" oninput="syncPlastikEditSJ(this.value)">
                            <span class="plastik-suffix-label label-satuan-text">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">Jumlah di Pabrik</label>
                        <div class="plastik-suffix-wrap">
                            <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="jumlah_di_pabrik" id="inputJumlahPabrik" value="{{ old('jumlah_di_pabrik', $pabrikVal) }}" class="form-control" style="font-weight: 800; color: #0f766e;" oninput="syncPlastikEditPabrik(this.value)">
                            <span class="plastik-suffix-label label-satuan-text">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                </div>

                <div class="plastik-grid-2col stack-on-mobile">
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">Gross Kedatangan <span class="required-star">*</span></label>
                        <div class="plastik-suffix-wrap">
                            <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="inputGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', $grossVal) }}" class="form-control" style="font-weight: 900; color: #0f172a;" oninput="syncPlastikEditGross(this.value)" required>
                            <span class="plastik-suffix-label label-satuan-text">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                    <div class="plastik-field-group">
                        <label class="plastik-field-label" style="color: #dc2626;">Reject / Cacat</label>
                        <div class="plastik-suffix-wrap">
                            <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="items[{{ $qcdtlId }}][qty_reject]" id="inputReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', $rejectVal) }}" class="form-control" style="font-weight: 800; color: #dc2626;" oninput="recalcPlastikNetto()">
                            <span class="plastik-suffix-label label-satuan-text" style="color: #ef4444;">{{ $satuanDtl }}</span>
                        </div>
                    </div>
                </div>

                {{-- ESTIMASI NETTO LOLOS DITERIMA PABRIK --}}
                <div style="background: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 12px; padding: 1rem; text-align: center; margin-top: 0.15rem;">
                    <div style="font-size: 0.72rem; font-weight: 800; color: #0f766e; letter-spacing: 0.04em; text-transform: uppercase;">
                        Estimasi Netto Lolos Diterima Pabrik
                    </div>
                    <div style="font-size: 1.65rem; font-weight: 900; color: #0d9488; margin-top: 0.25rem;" id="displayNettoText">
                        {{ number_format($nettoVal, 0, ',', '.') }} {{ $satuanDtl }}
                    </div>
                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputNettoLolos" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $nettoVal) }}">
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KARTU 4: PARAMETER MUTU FISIK & ANALISA KEMASAN                         --}}
        {{-- ========================================================================= --}}
        <div class="plastik-edit-card">
            <div class="plastik-edit-card-header theme-teal">
                <div class="plastik-edit-card-header-left">
                    <span class="plastik-step-badge">4</span>
                    <div>
                        <h2 class="plastik-edit-card-title">Parameter Mutu Fisik &amp; Analisa Kemasan</h2>
                        <div class="plastik-edit-card-subtitle">Pengujian Mikrometer, Keutuhan Seal &amp; Kondisi Fisik</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🔬</span>
            </div>

            <div class="plastik-edit-card-body">
                {{-- 1. STATUS RAW MATERIAL & KONDISI WADAH KEMASAN --}}
                <div class="plastik-subcard-box theme-teal">
                    <div class="plastik-subcard-header">
                        <span class="plastik-subcard-title">
                            <span>📋</span> <span>Status Raw Material &amp; Kemasan</span>
                        </span>
                        <span class="plastik-subcard-badge" style="background: #ccfbf1; color: #0f766e;">Kondisi Fisik</span>
                    </div>

                    {{-- Baris A: Status Raw Material --}}
                    <div class="plastik-field-group">
                        <label class="plastik-field-label" style="font-size: 0.78rem; color: #475569;">Status Raw Material:</label>
                        <div class="plastik-pill-group">
                            <label class="plastik-pill-btn {{ $statusRawVal === 'OK' ? 'is-active-ok' : '' }}" onclick="selectPlastikPill('items[{{ $qcdtlId }}][status_raw_material]', 'OK')">
                                <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="OK" {{ $statusRawVal === 'OK' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>✔ OK (Standar)</span>
                            </label>
                            <label class="plastik-pill-btn {{ $statusRawVal === 'TDK_STD' ? 'is-active-danger' : '' }}" onclick="selectPlastikPill('items[{{ $qcdtlId }}][status_raw_material]', 'TDK_STD')">
                                <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="TDK_STD" {{ $statusRawVal === 'TDK_STD' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>✖ TDK STD (Menyimpang)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Baris B: Kondisi Kemasan --}}
                    <div class="plastik-field-group">
                        <label class="plastik-field-label" style="font-size: 0.78rem; color: #475569;">Kondisi Kemasan Wadah:</label>
                        <div class="plastik-pill-group">
                            <label class="plastik-pill-btn {{ $kemasanKondisiVal === 'OK' ? 'is-active-ok' : '' }}" onclick="selectPlastikPill('items[{{ $qcdtlId }}][kemasan_kondisi]', 'OK')">
                                <input type="radio" name="items[{{ $qcdtlId }}][kemasan_kondisi]" value="OK" {{ $kemasanKondisiVal === 'OK' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>✔ OK (Standar)</span>
                            </label>
                            <label class="plastik-pill-btn {{ $kemasanKondisiVal === 'TIDAK_STANDARD' ? 'is-active-danger' : '' }}" onclick="selectPlastikPill('items[{{ $qcdtlId }}][kemasan_kondisi]', 'TIDAK_STANDARD')">
                                <input type="radio" name="items[{{ $qcdtlId }}][kemasan_kondisi]" value="TIDAK_STANDARD" {{ $kemasanKondisiVal === 'TIDAK_STANDARD' ? 'checked' : '' }}>
                                <span class="pill-dot"></span>
                                <span>✖ TIDAK STANDARD</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- 2. CACAT KEMASAN (CHECKLIST 4 CACAT) --}}
                <div class="plastik-subcard-box">
                    <div class="plastik-subcard-header">
                        <span class="plastik-subcard-title">
                            <span>⚠️</span> <span>Indikasi Cacat Kemasan</span>
                        </span>
                        <span class="plastik-subcard-badge" style="background: #fee2e2; color: #991b1b;">Centang jika cacat</span>
                    </div>

                    @php
                        $kotorChecked = old('items.'.$qcdtlId.'.kemasan_kotor', $firstDetail?->kemasan_kotor) == '1';
                        $apekChecked = old('items.'.$qcdtlId.'.kemasan_apek', $firstDetail?->kemasan_apek) == '1';
                        $basahChecked = old('items.'.$qcdtlId.'.kemasan_basah', $firstDetail?->kemasan_basah) == '1';
                        $sobekChecked = old('items.'.$qcdtlId.'.kemasan_sobek', $firstDetail?->kemasan_sobek) == '1';
                    @endphp

                    <div class="plastik-check-card-grid">
                        <label class="plastik-check-card {{ $kotorChecked ? 'is-checked' : '' }}">
                            <div class="plastik-check-left">
                                <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_kotor]" value="1" {{ $kotorChecked ? 'checked' : '' }} onchange="togglePlastikCheckCard(this)">
                                <span class="plastik-check-title">🧼 Kotor</span>
                            </div>
                            <span class="plastik-check-badge">Cacat Noda</span>
                        </label>

                        <label class="plastik-check-card {{ $apekChecked ? 'is-checked' : '' }}">
                            <div class="plastik-check-left">
                                <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_apek]" value="1" {{ $apekChecked ? 'checked' : '' }} onchange="togglePlastikCheckCard(this)">
                                <span class="plastik-check-title">👃 Apek</span>
                            </div>
                            <span class="plastik-check-badge">Bau Asing</span>
                        </label>

                        <label class="plastik-check-card {{ $basahChecked ? 'is-checked' : '' }}">
                            <div class="plastik-check-left">
                                <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_basah]" value="1" {{ $basahChecked ? 'checked' : '' }} onchange="togglePlastikCheckCard(this)">
                                <span class="plastik-check-title">💧 Basah</span>
                            </div>
                            <span class="plastik-check-badge">Lembab / Air</span>
                        </label>

                        <label class="plastik-check-card {{ $sobekChecked ? 'is-checked' : '' }}">
                            <div class="plastik-check-left">
                                <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_sobek]" value="1" {{ $sobekChecked ? 'checked' : '' }} onchange="togglePlastikCheckCard(this)">
                                <span class="plastik-check-title">✂️ Sobek</span>
                            </div>
                            <span class="plastik-check-badge" style="color: #ef4444;">Kritis</span>
                        </label>
                    </div>
                </div>

                {{-- 3. ANALISA PARAMETER KETEBALAN & KEUTUHAN --}}
                <div class="plastik-subcard-box">
                    <div class="plastik-subcard-header">
                        <span class="plastik-subcard-title">
                            <span>📐</span> <span>Analisa Ketebalan &amp; Keutuhan Seal</span>
                        </span>
                        <span class="plastik-subcard-badge" style="background: #e2e8f0; color: #334155;">Uji Fisik</span>
                    </div>

                    {{-- 3A: KETEBALAN MIKROMETER --}}
                    <div class="plastik-field-group">
                        <div class="plastik-field-header">
                            <label class="plastik-field-label" style="color: #0f766e;">Ketebalan Mikrometer</label>
                            <div style="display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #475569;">
                                <span>Std:</span>
                                <input type="text" name="items[{{ $qcdtlId }}][ketebalan_standar]" value="{{ old('items.'.$qcdtlId.'.ketebalan_standar', $firstDetail?->ketebalan_standar ?? '0.08 mm') }}" style="width: 75px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.72rem; padding: 1px 5px; font-weight: 700; background: #ffffff;">
                            </div>
                        </div>

                        <div class="plastik-chip-group">
                            <button type="button" class="plastik-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.08 mm')">0.08 mm</button>
                            <button type="button" class="plastik-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.085 mm')">0.085 mm</button>
                            <button type="button" class="plastik-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.075 mm')">0.075 mm</button>
                            <button type="button" class="plastik-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.09 mm')">0.09 mm</button>
                        </div>
                        <input type="text" name="items[{{ $qcdtlId }}][ketebalan_analisa]" id="inputKetebalanAnalisa" value="{{ old('items.'.$qcdtlId.'.ketebalan_analisa', $firstDetail?->ketebalan_analisa) }}" class="form-control" placeholder="Contoh: 0.08 mm" style="font-weight: 700;">
                    </div>

                    {{-- 3B: KEUTUHAN SEAL & FISIK --}}
                    <div class="plastik-field-group">
                        <div class="plastik-field-header">
                            <label class="plastik-field-label" style="color: #0f766e;">Keutuhan Seal &amp; Fisik</label>
                            <div style="display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #475569;">
                                <span>Std:</span>
                                <input type="text" name="items[{{ $qcdtlId }}][keutuhan_standar]" value="{{ old('items.'.$qcdtlId.'.keutuhan_standar', $firstDetail?->keutuhan_standar ?? 'Tidak Sobek') }}" readonly style="width: 85px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.72rem; padding: 1px 5px; font-weight: 700; background: #f8fafc;">
                            </div>
                        </div>

                        <div class="plastik-chip-group">
                            <button type="button" class="plastik-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Tidak Sobek')">✔ Tidak Sobek</button>
                            <button type="button" class="plastik-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Sobek')">✖ Sobek</button>
                            <button type="button" class="plastik-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Seal Kurang Rapat')">⚠️ Seal Kurang Rapat</button>
                        </div>
                        <input type="text" name="items[{{ $qcdtlId }}][keutuhan_analisa]" id="inputKeutuhanAnalisa" value="{{ old('items.'.$qcdtlId.'.keutuhan_analisa', $firstDetail?->keutuhan_analisa ?? 'Tidak Sobek') }}" class="form-control" placeholder="Contoh: Tidak Sobek" style="font-weight: 700;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KARTU 5: KEPUTUSAN KESIMPULAN & CATATAN QC PLASTIK                      --}}
        {{-- ========================================================================= --}}
        <div class="plastik-edit-card">
            <div class="plastik-edit-card-header theme-green">
                <div class="plastik-edit-card-header-left">
                    <span class="plastik-step-badge green">5</span>
                    <div>
                        <h2 class="plastik-edit-card-title">Keputusan &amp; Catatan QC Plastik</h2>
                        <div class="plastik-edit-card-subtitle">Kesimpulan Akhir Inspeksi, Catatan Lapangan &amp; Otorisasi</div>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">💬</span>
            </div>

            <div class="plastik-edit-card-body">
                <div class="plastik-field-group">
                    <label class="plastik-field-label">Keputusan Akhir QC <span class="required-star">*</span></label>
                    <div class="plastik-pill-group">
                        <label class="plastik-pill-btn {{ $kesimpulanVal === 'TERIMA' ? 'is-active-ok' : '' }}" onclick="selectPlastikPill('kesimpulan_qc', 'TERIMA')">
                            <input type="radio" name="kesimpulan_qc" id="radioTerima" value="TERIMA" {{ $kesimpulanVal === 'TERIMA' ? 'checked' : '' }}>
                            <span class="pill-dot"></span>
                            <span style="font-size: 0.95rem; font-weight: 800;">✔ TERIMA</span>
                        </label>
                        <label class="plastik-pill-btn {{ $kesimpulanVal === 'TOLAK' ? 'is-active-danger' : '' }}" onclick="selectPlastikPill('kesimpulan_qc', 'TOLAK')">
                            <input type="radio" name="kesimpulan_qc" id="radioTolak" value="TOLAK" {{ $kesimpulanVal === 'TOLAK' ? 'checked' : '' }}>
                            <span class="pill-dot"></span>
                            <span style="font-size: 0.95rem; font-weight: 800;">✖ TOLAK</span>
                        </label>
                    </div>
                </div>

                <div class="plastik-field-group">
                    <label class="plastik-field-label">Catatan Umum QC</label>
                    <textarea name="catatan_umum" id="inputCatatanUmum" rows="3" class="form-control" placeholder="Tuliskan keterangan inspeksi plastik kemasan, kondisi seal, ketebalan, atau catatan pabrik..." style="line-height: 1.45;">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
                </div>

                <div class="plastik-grid-2col stack-on-mobile">
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">Petugas QC <span class="required-star">*</span></label>
                        <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama ?? auth()->user()?->name) }}" class="form-control" style="font-weight: 700;" required>
                    </div>
                    <div class="plastik-field-group">
                        <label class="plastik-field-label">QC Supervisor</label>
                        <input type="text" name="qc_supervisor_nama" list="listSpvPlastik" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama ?? 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" class="form-control" placeholder="Nama Supervisor QC" style="font-weight: 700;">
                        <datalist id="listSpvPlastik">
                            <option value="Hendrawan, S.TP (SPV QC Shift Pagi)">
                            <option value="Rina Wijaya, S.Si (Supervisor QC Lab)">
                            <option value="Dedi (QC Bahan Baku)">
                            <option value="Bambang (Produksi Magelang)">
                            <option value="Super Administrator">
                        </datalist>
                    </div>
                </div>
            </div>
        </div>

        {{-- FLOATING BOTTOM BAR --}}
        <div class="plastik-mobile-bottom-bar">
            <a href="{{ $backUrl }}" style="flex: 1; padding: 0.8rem 0.5rem; font-size: 0.88rem; font-weight: 700; border-radius: 10px; text-align: center; text-decoration: none; background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; min-height: 46px;">
                ✕ Batal
            </a>
            <button type="submit" id="btnEditSubmit" class="qc-btn-mobile-edit" style="flex: 2; font-size: 0.92rem; padding: 0.8rem 1rem; background: #0d9488; min-height: 46px; border-radius: 10px; font-weight: 800; border: none; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 10px rgba(13, 148, 136, 0.35);">
                <span>💾 Simpan Perubahan</span>
            </button>
        </div>

        {{-- FULLSCREEN LOADING OVERLAY --}}
        <div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.72); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; padding: 1.75rem 2rem; border-radius: 16px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); max-width: 320px; margin: 1rem; width: 90%;">
                <div style="width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #0d9488; border-radius: 50%; animation: qcSpin 0.8s linear infinite; margin: 0 auto 1.15rem;"></div>
                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.4rem;">Memperbarui Tiket QC Plastik...</div>
                <div style="font-size: 0.8rem; color: #64748b; line-height: 1.45;">Sedang menyimpan koreksi uji mutu plastik. Mohon tidak menekan tombol lagi.</div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.plastikSatuan = "{{ $satuanDtl }}";
    </script>
    <script src="{{ asset('js/gudang/qc/plastik/plastik-edit.js') }}?v={{ filemtime(public_path('js/gudang/qc/plastik/plastik-edit.js')) }}"></script>
@endpush
