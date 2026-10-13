@extends('layouts.qc-mobile')

@section('title', 'Koreksi QC Plastik: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik-detail.css') }}">
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

    // Filter Supplier khusus Kemasan / Plastik
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

    $barangId = $firstDetail?->barang_id ?? $filteredBarangs->first()?->barang_id;
    $ref = request('ref', 'index');

    // Satuan
    $satuanRaw = $firstDetail?->barang?->satuanDasar?->satuan_nm
        ?: ($firstDetail?->barang?->satuanDasar?->satuan_cd ?: 'PCS');
    $isKg = strtoupper(trim($satuanRaw)) === 'KG';
    $satuanDtl = $isKg ? 'KG' : (strtoupper(trim($satuanRaw)) === 'LEMBAR' ? 'Lembar' : 'Pcs');

    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
@endphp

@section('content')
<div class="qc-form-container" style="max-width: 680px; margin: 0 auto; padding-bottom: 5.5rem;">

    {{-- TOP APP BAR --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.85rem;">
        <a href="{{ $ref === 'detail' ? route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) : route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']) }}" class="btn-back-header">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Batal</span>
        </a>

        <div style="text-align: center; flex: 1;">
            <div style="font-size: 0.72rem; font-weight: 800; color: #0d9488; text-transform: uppercase; letter-spacing: 0.05em;">Koreksi Inspeksi</div>
            <div style="font-size: 0.95rem; font-weight: 900; color: #0f172a; line-height: 1.2;">{{ $qc->qc_no }}</div>
        </div>

        <button type="submit" form="qcEditPlastikForm" class="btn-top-save" style="background: #0d9488; border-color: #0d9488;">
            <span>Simpan</span>
        </button>
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

        {{-- KARTU 1: INFO PENGIRIMAN, PO & VENDOR KEMASAN --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🚚</span> <span>1. Info Armada, PO &amp; Vendor Kemasan</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                {{-- PILIHAN PO KHUSUS PLASTIK --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <label class="qc-info-label" style="font-weight: 700; margin: 0;">Referensi Purchase Order (PO)</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #0f766e; background: #ccfbf1; padding: 2px 6px; border-radius: 4px;">
                            PO Kemasan ({{ $filteredPos->count() }} Tersedia)
                        </span>
                    </div>
                    <select name="po_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        <option value="">-- Tanpa PO / Kiriman Langsung Plastik --</option>
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

                {{-- MITRA VENDOR & GUDANG --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 700; margin: 0;">🏢 Mitra Vendor / Supplier Kemasan *</label>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #0f766e; background: #ccfbf1; padding: 2px 6px; border-radius: 4px;">
                                Vendor Kemasan
                            </span>
                        </div>
                        <div style="margin-bottom: 0.4rem;">
                            <input type="text" id="editSupplierSearch" class="form-control" placeholder="🔍 Cari vendor supplier kemasan..." oninput="filterEditSuppliers(this.value)" style="min-height: 38px; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 6px;">
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
                            <input type="text" name="nama_produsen" id="editNamaProdusen" value="{{ old('nama_produsen', $qc->nama_produsen ?? $qc->supplier?->supplier_nm) }}" class="form-control" placeholder="PT Polychem Pack" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        </div>
                        <div>
                            <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.25rem;">Negara Produsen</label>
                            <input type="text" name="negara_produsen" value="{{ old('negara_produsen', $qc->negara_produsen ?? 'Indonesia') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        </div>
                    </div>

                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.25rem;">🏢 Gudang Tujuan Bongkar *</label>
                        <select name="gudang_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} @if(!empty($g->tipe_gudang_cd) && $g->tipe_gudang_cd !== 'Pusat') ({{ $g->tipe_gudang_cd }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- PILIH MASTER BARANG & NAMA JENIS SPESIFIKASI KEMASAN --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Komoditas Kemasan (Master Data) *</label>
                        <select name="items[{{ $qcdtlId }}][barang_id]" id="editBarangSelect" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #0f766e;" required onchange="onEditBarangChanged(this)">
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

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 700; margin: 0;">Spesifikasi / Nama Jenis Kemasan</label>
                            <span style="font-size: 0.68rem; color: #64748b;">Bisa diedit</span>
                        </div>
                        <input type="text" name="nama_jenis" id="editNamaJenis" list="listPlastikJenisEdit" value="{{ old('nama_jenis', $qc->nama_jenis ?? 'Plastik Kemasan Keripik Cap Payung 250g') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #0f766e;" placeholder="Contoh: Plastik OPP Roll 250g (Tebal 0.08mm)">
                        <datalist id="listPlastikJenisEdit">
                            <option value="Plastik Kemasan Keripik Cap Payung 250g">
                            <option value="Plastik OPP Roll 250g (Tebal 0.08mm)">
                            <option value="Plastik OPP Roll 500g">
                            <option value="Standing Pouch Cap Payung 250g">
                            <option value="Standing Pouch Zipper 500g Metalized">
                            <option value="Plastik PP Tebal 0.08mm Seal Rapi">
                        </datalist>
                    </div>
                </div>

                {{-- SURAT JALAN, ARMADA & TANGGAL --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">No. DO / Surat Jalan</label>
                        <input type="text" name="nomor_do" id="inputNomorDo" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" oninput="const sjEl = document.getElementById('inputSuratJalanSupplier'); if(sjEl) sjEl.value = this.value;">
                        <input type="hidden" name="surat_jalan_supplier" id="inputSuratJalanSupplier" value="{{ old('surat_jalan_supplier', $qc->surat_jalan_supplier ?? $qc->nomor_do) }}">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Plat Truk / Mobil Box</label>
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
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Sampel QC Diperiksa (Pcs)</label>
                    <input type="number" step="1" min="1" name="jumlah_sample_pcs" value="{{ old('jumlah_sample_pcs', $qc->jumlah_sample_pcs ?? 10) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #0d9488;" placeholder="10">
                </div>
            </div>
        </div>

        {{-- KARTU 2: HALAL & HIGIENE TRANSPORTASI (SISTEM JAMINAN HALAL / SJH & CCP) --}}
        <div class="qc-card-section" style="border: 1.5px solid #99f6e4; background: #ffffff; border-radius: 12px; overflow: hidden; padding: 0;">
            <div style="background: #f0fdfa; padding: 0.85rem 1rem; border-bottom: 1px solid #ccfbf1; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #0f766e; color: #ffffff; font-weight: 800; font-size: 0.82rem;">2</span>
                    <div>
                        <h2 style="font-size: 0.95rem; font-weight: 800; color: #0f766e; margin: 0; line-height: 1.25;">Standar Transportasi &amp; Jaminan Halal</h2>
                        <span style="font-size: 0.72rem; color: #0d9488; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; CCP Kebersihan Armada</span>
                    </div>
                </div>
                <span style="font-size: 1.25rem;">🛡️</span>
            </div>

            <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- ITEM 1: KONDISI TRANSPORTASI (TRUK / BOX KEMASAN) --}}
                <div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 0.45rem;">
                        1. Kondisi Transportasi (Truk / Box Kemasan)
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        {{-- Option 1: Bebas Cemaran --}}
                        <label class="transport-radio-card {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'active' : '' }}" id="cardTransportBebas">
                            <input type="radio" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) == 1 ? 'checked' : '' }} onchange="updateTransportCard(this)">
                            <div class="transport-card-text">
                                <div class="transport-card-title" style="color: #0d9488;">Tidak ada cemaran, Najis / Kotoran</div>
                                <div class="transport-card-desc">Bak mobil box bersih, kering, bebas bau asing &amp; bebas bahan kimia</div>
                            </div>
                        </label>

                        {{-- Option 2: Ada Cemaran --}}
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

        {{-- KARTU 3: HASIL KUANTITAS & VERIFIKASI PENERIMAAN --}}
        <div class="qc-card-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.4rem;">
                <h2 class="qc-card-title" style="margin: 0;">
                    <span>⚖️</span> <span>3. Hasil Kuantitas &amp; Kedatangan Pabrik</span>
                </h2>
                <div class="satuan-toggle-pills" id="satuanToggleGroupPlastik">
                    <button type="button" class="btn-satuan-pill {{ $satuanDtl !== 'KG' ? 'active' : '' }}" id="pillSatuan_PCS" onclick="setPlastikEditSatuan('PCS')">
                        <span class="pill-icon">🛍️</span>
                        <span class="pill-text">PCS</span>
                    </button>
                    <button type="button" class="btn-satuan-pill {{ $satuanDtl === 'KG' ? 'active' : '' }}" id="pillSatuan_KG" onclick="setPlastikEditSatuan('KG')">
                        <span class="pill-icon">⚖️</span>
                        <span class="pill-text">KG</span>
                    </button>
                </div>
                <input type="hidden" name="satuan" id="inputPlastikSatuan" value="{{ $satuanDtl }}">
                <input type="hidden" name="items[{{ $qcdtlId }}][satuan]" id="inputPlastikItemSatuan" value="{{ $satuanDtl }}">
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Jumlah Surat Jalan (<span class="label-satuan-text">{{ $satuanDtl }}</span>)</label>
                        <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="jumlah_surat_jalan" id="inputJumlahSJ" value="{{ old('jumlah_surat_jalan', $qc->jumlah_surat_jalan ?? $firstDetail?->qty_timbang_gross) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" oninput="syncPlastikEditSJ(this.value)">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Jumlah di Pabrik (<span class="label-satuan-text">{{ $satuanDtl }}</span>)</label>
                        <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="jumlah_di_pabrik" id="inputJumlahPabrik" value="{{ old('jumlah_di_pabrik', $qc->jumlah_di_pabrik ?? ($firstDetail?->qty_timbang_gross ?? $totalGross)) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; color: #0f766e;" oninput="syncPlastikEditPabrik(this.value)">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Gross Kedatangan (<span class="label-satuan-text">{{ $satuanDtl }}</span>) *</label>
                        <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="inputGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', $firstDetail?->qty_timbang_gross ?? ($qc->jumlah_di_pabrik ?? $totalGross)) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800;" oninput="syncPlastikEditGross(this.value)" required>
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem; color: #dc2626;">Reject / Cacat (<span class="label-satuan-text">{{ $satuanDtl }}</span>)</label>
                        <input type="number" step="{{ $satuanDtl === 'KG' ? '0.01' : '1' }}" name="items[{{ $qcdtlId }}][qty_reject]" id="inputReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', $firstDetail?->qty_reject ?? $totalReject) }}" class="form-control" style="width: 100%; border-radius: 8px; color: #dc2626; font-weight: 700;" oninput="recalcPlastikNetto()">
                    </div>
                </div>

                <div style="background: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 10px; padding: 0.85rem 1rem; text-align: center; margin-top: 0.25rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #0f766e; text-transform: uppercase;">Estimasi Netto Lolos Diterima Pabrik</div>
                    <div style="font-size: 1.45rem; font-weight: 900; color: #0d9488; margin-top: 0.2rem;" id="displayNettoText">
                        {{ number_format($totalNetto, 0, ',', '.') }} {{ $satuanDtl }}
                    </div>
                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputNettoLolos" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $firstDetail?->qty_netto_lolos ?? $totalNetto) }}">
                </div>
            </div>
        </div>

        {{-- KARTU 4: PARAMETER MUTU, CACAT KEMASAN & UJI MIKROMETER --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🔬</span> <span>4. Parameter Mutu Fisik &amp; Analisa Kemasan</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- STATUS RAW MATERIAL & KONDISI WADAH KEMASAN --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.4rem;">
                        <span style="font-weight: 800; font-size: 0.82rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
                        <div style="display: flex; gap: 1rem; font-size: 0.82rem; font-weight: 700;">
                            <label style="display: flex; align-items: center; gap: 4px; color: #15803d; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="OK" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'checked' : '' }}> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="TDK_STD" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? 'checked' : '' }}> TDK STD
                            </label>
                        </div>
                    </div>

                    <div style="border-top: 1px dashed #e2e8f0; padding-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                        <span style="font-weight: 800; font-size: 0.82rem; color: #0f172a;">KEMASAN :</span>
                        <div style="display: flex; gap: 1rem; font-size: 0.82rem; font-weight: 700;">
                            <label style="display: flex; align-items: center; gap: 4px; color: #15803d; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][kemasan_kondisi]" value="OK" {{ old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? 'checked' : '' }}> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="items[{{ $qcdtlId }}][kemasan_kondisi]" value="TIDAK_STANDARD" {{ old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi ?? '') === 'TIDAK_STANDARD' ? 'checked' : '' }}> TIDAK STANDARD
                            </label>
                        </div>
                    </div>
                </div>

                {{-- CHECKLIST 4 CACAT KEMASAN --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    <div style="font-weight: 800; font-size: 0.82rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚠️ KONDISI KEMASAN (Centang jika ditemukan cacat):
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; font-size: 0.825rem; font-weight: 700;">
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_kotor]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_kotor', $firstDetail?->kemasan_kotor) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span>🧼 KOTOR</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_apek]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_apek', $firstDetail?->kemasan_apek) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span>👃 APEK</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_basah]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_basah', $firstDetail?->kemasan_basah) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span>💧 BASAH</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; color: #dc2626;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_sobek]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_sobek', $firstDetail?->kemasan_sobek) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span>✂️ SOBEK</span>
                        </label>
                    </div>
                </div>

                {{-- ANALISA PARAMETER KETEBALAN & KEUTUHAN --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="font-size: 0.82rem; font-weight: 800; color: #0f766e;">
                        🔬 ANALISA PARAMETER KETEBALAN &amp; KEUTUHAN:
                    </div>

                    {{-- 1. KETEBALAN --}}
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 700; margin: 0;">Ketebalan Mikrometer</label>
                            <div style="display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #475569;">
                                <span>Std:</span>
                                <input type="text" name="items[{{ $qcdtlId }}][ketebalan_standar]" value="{{ old('items.'.$qcdtlId.'.ketebalan_standar', $firstDetail?->ketebalan_standar ?? '0.08 mm') }}" style="width: 70px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.72rem; padding: 1px 4px; font-weight: 700;">
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.35rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                            <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.08 mm')">0.08 mm</button>
                            <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.085 mm')">0.085 mm</button>
                            <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.075 mm')">0.075 mm</button>
                            <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.09 mm')">0.09 mm</button>
                        </div>
                        <input type="text" name="items[{{ $qcdtlId }}][ketebalan_analisa]" id="inputKetebalanAnalisa" value="{{ old('items.'.$qcdtlId.'.ketebalan_analisa', $firstDetail?->ketebalan_analisa) }}" class="form-control" placeholder="Contoh: 0.08 mm" style="width: 100%; border-radius: 8px; font-weight: 700;">
                    </div>

                    {{-- 2. KEUTUHAN --}}
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 700; margin: 0;">Keutuhan Seal &amp; Fisik</label>
                            <div style="display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #475569;">
                                <span>Std:</span>
                                <input type="text" name="items[{{ $qcdtlId }}][keutuhan_standar]" value="{{ old('items.'.$qcdtlId.'.keutuhan_standar', $firstDetail?->keutuhan_standar ?? 'Tidak Sobek') }}" readonly style="width: 80px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.72rem; padding: 1px 4px; font-weight: 700; background: #f8fafc;">
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.35rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                            <button type="button" class="param-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Tidak Sobek')">✔ Tidak Sobek</button>
                            <button type="button" class="param-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Sobek')">✖ Sobek</button>
                            <button type="button" class="param-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Seal Kurang Rapat')">⚠️ Seal Kurang Rapat</button>
                        </div>
                        <input type="text" name="items[{{ $qcdtlId }}][keutuhan_analisa]" id="inputKeutuhanAnalisa" value="{{ old('items.'.$qcdtlId.'.keutuhan_analisa', $firstDetail?->keutuhan_analisa ?? 'Tidak Sobek') }}" class="form-control" placeholder="Contoh: Tidak Sobek" style="width: 100%; border-radius: 8px; font-weight: 700;">
                    </div>
                </div>

                {{-- CATATAN SPESIFIKASI MUTU FISIK --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    <input type="hidden" name="items[{{ $qcdtlId }}][grade_cd]" id="selectGradeCd" value="{{ old('items.'.$qcdtlId.'.grade_cd', $firstDetail?->grade_cd ?? 'A') }}">
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem; font-size: 0.8rem; color: #0f172a;">Catatan Spesifikasi Mutu Fisik</label>
                    <input type="text" name="items[{{ $qcdtlId }}][catatan_dtl]" value="{{ old('items.'.$qcdtlId.'.catatan_dtl', $firstDetail?->catatan_dtl) }}" class="form-control" placeholder="Contoh: Ketebalan rata 0.08mm, hasil seal rapat kuat, bebas bercak noda..." style="width: 100%; border-radius: 6px; font-size: 0.85rem;">
                </div>
            </div>
        </div>

        {{-- KARTU 5: KEPUTUSAN KESIMPULAN & CATATAN --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>💬</span> <span>5. Keputusan &amp; Catatan QC Plastik</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Keputusan Akhir QC:</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                        <label id="lblRadioTerima" style="display: flex; align-items: center; gap: 8px; background: #f0fdfa; border: 2px solid #99f6e4; border-radius: 8px; padding: 0.65rem 0.85rem; cursor: pointer;">
                            <input type="radio" name="kesimpulan_qc" id="radioTerima" value="TERIMA" {{ old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : '') === 'TERIMA' ? 'checked' : '' }} style="width: 20px; height: 20px;">
                            <span style="font-weight: 900; color: #0f766e; font-size: 0.95rem;">✔ TERIMA</span>
                        </label>
                        <label id="lblRadioTolak" style="display: flex; align-items: center; gap: 8px; background: #fef2f2; border: 2px solid #fca5a5; border-radius: 8px; padding: 0.65rem 0.85rem; cursor: pointer;">
                            <input type="radio" name="kesimpulan_qc" id="radioTolak" value="TOLAK" {{ old('kesimpulan_qc', $qc->status_qc === 'DITOLAK_TOTAL' ? 'TOLAK' : '') === 'TOLAK' ? 'checked' : '' }} style="width: 20px; height: 20px;">
                            <span style="font-weight: 900; color: #dc2626; font-size: 0.95rem;">✖ TOLAK</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Catatan Umum QC</label>
                    <textarea name="catatan_umum" rows="2" class="form-control" placeholder="Catatan kesimpulan inspeksi kedatangan plastik kemasan..." style="width: 100%; border-radius: 8px; font-size: 0.8rem;">{{ old('catatan_umum', $qc->catatan_umum) }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Petugas QC Pemeriksa</label>
                        <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama ?? auth()->user()?->name) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">QC Supervisor Peninjau</label>
                        <input type="text" name="qc_supervisor_nama" list="listSpvPlastik" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama ?? 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
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

        {{-- FLOATING ACTION DOCK UNTUK MOBILE --}}
        <div class="qc-form-floating-dock" id="qcFloatingDock">
            <a href="{{ $ref === 'detail' ? route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) : route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']) }}" class="btn-dock-back" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Batal</span>
            </a>
            <button type="submit" class="btn-dock-next btn-primary-state" id="dockBtnSubmit" style="background: #0d9488; border-color: #0d9488;">
                <span>Simpan Koreksi QC Plastik</span>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </button>
        </div>

        {{-- OVERLAY PROSES SUBMIT --}}
        <div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); color: #ffffff;">
            <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center; max-width: 320px; width: 90%;">
                <div style="font-size: 2.5rem; margin-bottom: 0.75rem; animation: spin-slow 2s linear infinite;">🛍️</div>
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem; color: #0f766e;">Menyimpan Koreksi Plastik...</h3>
                <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Memperbarui lembar inspeksi mutu QC.</p>
            </div>
        </div>

        <style>
            @keyframes spin-slow {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        // Satuan
        let currentSatuan = "{{ $satuanDtl }}";

        window.setPlastikEditSatuan = function (sat) {
            currentSatuan = sat;
            const inputSatuan = document.getElementById('inputPlastikSatuan');
            const inputItemSatuan = document.getElementById('inputPlastikItemSatuan');
            if (inputSatuan) inputSatuan.value = sat;
            if (inputItemSatuan) inputItemSatuan.value = sat;

            const pillPcs = document.getElementById('pillSatuan_PCS');
            const pillKg = document.getElementById('pillSatuan_KG');
            if (sat === 'KG') {
                pillKg?.classList.add('active');
                pillPcs?.classList.remove('active');
            } else {
                pillPcs?.classList.add('active');
                pillKg?.classList.remove('active');
            }

            document.querySelectorAll('.label-satuan-text').forEach(el => {
                el.innerText = sat;
            });

            const stepVal = (sat === 'KG') ? '0.01' : '1';
            ['inputJumlahSJ', 'inputJumlahPabrik', 'inputGross', 'inputReject'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.step = stepVal;
            });

            recalcPlastikNetto();
        };

        // Quick chip setter
        window.setParamValue = function (targetId, val) {
            const el = document.getElementById(targetId);
            if (el) el.value = val;
        };

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
            } else if (field === 'ada_sertifikat_halal') {
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
            } else if (field === 'sertifikat_halal_berlaku') {
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

        function syncPlastikEditSJ(val) {
            const pabrikEl = document.getElementById('inputJumlahPabrik');
            if (pabrikEl && (!pabrikEl.value || parseFloat(pabrikEl.value) === 0)) {
                pabrikEl.value = val;
                syncPlastikEditPabrik(val);
            }
        }

        function syncPlastikEditPabrik(val) {
            const grossEl = document.getElementById('inputGross');
            if (grossEl) {
                grossEl.value = val;
            }
            recalcPlastikNetto();
        }

        function syncPlastikEditGross(val) {
            const pabrikEl = document.getElementById('inputJumlahPabrik');
            if (pabrikEl) {
                pabrikEl.value = val;
            }
            recalcPlastikNetto();
        }

        function recalcPlastikNetto() {
            const gross = parseFloat(document.getElementById('inputGross')?.value) || 0;
            const reject = parseFloat(document.getElementById('inputReject')?.value) || 0;
            const netto = Math.max(0, gross - reject);

            const displayEl = document.getElementById('displayNettoText');
            const hiddenEl = document.getElementById('inputNettoLolos');

            if (displayEl) {
                displayEl.innerText = (netto % 1 === 0 ? netto : netto.toFixed(2)).toLocaleString('id-ID') + ' ' + currentSatuan;
            }
            if (hiddenEl) {
                hiddenEl.value = (netto % 1 === 0 ? netto : netto.toFixed(2));
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
                if (namaEl && !namaEl.value) {
                    namaEl.value = bNm;
                }
                const satCd = opt.getAttribute('data-satuan-cd') || opt.getAttribute('data-satuan');
                if (satCd) {
                    window.setPlastikEditSatuan(satCd);
                }
            }
        }

        // Overlay submit handler
        document.getElementById('qcEditPlastikForm')?.addEventListener('submit', function() {
            const overlay = document.getElementById('qcSubmitOverlay');
            if (overlay) overlay.style.display = 'flex';
        });
    </script>
@endpush
