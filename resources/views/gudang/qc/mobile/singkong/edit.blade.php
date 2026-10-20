@extends('layouts.qc-mobile')

@section('title', 'Koreksi Sampling QC Singkong: ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/singkong/singkong.css') }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;
    $isPengujian2 = ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2';

    // Filter daftar PO yang memuat Singkong
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR') || str_starts_with($cd, 'BB-OP') || str_starts_with($cd, 'BB-PY') || str_starts_with($cd, 'BB-')) {
            return 'SINGKONG';
        }
        return 'LAINNYA';
    };

    $filteredPos = $pos->filter(function($p) use ($qc, $classifyBarang) {
        if ($p->po_id == $qc->po_id) return true;
        foreach ($p->details as $d) {
            if ($classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd) === 'SINGKONG') {
                return true;
            }
        }
        return false;
    });

    if ($filteredPos->isEmpty() && $qc->po) {
        $filteredPos = collect([$qc->po]);
    }

    $filteredBarangs = $barangs->filter(function($b) {
        $nm = strtoupper($b->barang_nm);
        $cd = strtoupper($b->barang_cd);
        return str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR');
    });

    if ($filteredBarangs->isEmpty()) {
        $filteredBarangs = $barangs;
    }

    $barangId = $firstDetail?->barang_id ?? ($filteredBarangs->firstWhere('barang_nm', 'like', '%Singkong%')?->barang_id ?? $filteredBarangs->first()?->barang_id);
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    $totalReject = (float) $qc->details->sum('qty_reject');

    $ref = request('ref');
    if ($ref === 'detail') {
        $backUrl = route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']);
        $backLabel = 'Batal ke Detail';
    } else {
        $backUrl = route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'SINGKONG']);
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
                QC Singkong &bull; Edit
            </span>
        </div>
    </div>

    {{-- HEADER CARD MOBILE --}}
    <div class="qc-detail-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);">
        <div class="qc-detail-hero-top">
            <div>
                <span class="badge-tag-commodity" style="background: rgba(2, 132, 199, 0.4); color: #38bdf8;">
                    🥔 SINGKONG
                </span>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem; color: #ffffff;">Edit Tiket #{{ $qc->qc_no }}</h1>
            </div>
            <span style="background: #fef08a; color: #854d0e; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 20px;">
                Mode Mobile
            </span>
        </div>
        <p style="font-size: 0.8rem; margin: 0.4rem 0 0; opacity: 0.85; line-height: 1.4;">
            Koreksi parameter sampling, hasil uji lab fryer, sortir diameter, atau tonase timbangan kedatangan singkong.
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

    {{-- FORM UTAMA MOBILE EDIT SINGKONG --}}
    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditMobileForm" style="display: flex; flex-direction: column; gap: 1rem;">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="ref" value="{{ $ref ?: 'index' }}">
        <input type="hidden" name="kategori_barang" value="SINGKONG">
        <input type="hidden" name="batch_no" value="{{ old('batch_no', $qc->batch_no) }}">
        <input type="hidden" name="tahap_uji" id="inputTahapUji" value="{{ old('tahap_uji', $qc->tahap_uji ?? 'PENGUJIAN_1') }}">
        <input type="hidden" name="parent_qc_id" value="{{ old('parent_qc_id', $qc->parent_qc_id) }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][qcdtl_id]" value="{{ $qcdtlId }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][podtl_id]" value="{{ $firstDetail?->podtl_id }}">

        {{-- KARTU TAHAP PENGUJIAN SINGKONG (TERKUNCI / NON-SWITCHABLE SAAT EDIT) --}}
        <div class="qc-tahap-card" style="background: {{ $isPengujian2 ? '#faf5ff' : '#f0f9ff' }}; border: 1.5px solid {{ $isPengujian2 ? '#d8b4fe' : '#bae6fd' }}; border-radius: 12px; padding: 0.85rem 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                <div style="font-size: 0.85rem; font-weight: 800; color: {{ $isPengujian2 ? '#6b21a8' : '#0369a1' }}; display: flex; align-items: center; gap: 0.4rem;">
                    <span>{{ $isPengujian2 ? '🍟' : '🚛' }}</span>
                    <span>{{ $isPengujian2 ? 'Dokumen QC: Pengujian 2 (Lanjutan Sisa Bak Produksi)' : 'Dokumen QC: Pengujian 1 (Awal Kedatangan Truk Gerbang)' }}</span>
                </div>
                <span style="font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; {{ $isPengujian2 ? 'background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;' : 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' }}">
                    {{ $isPengujian2 ? 'Tahap II' : 'Tahap I' }}
                </span>
            </div>
            <div style="font-size: 0.75rem; color: {{ $isPengujian2 ? '#7e22ce' : '#0284c7' }}; margin-top: 0.35rem; line-height: 1.4;">
                @if ($isPengujian2)
                    Form koreksi data pengujian sensori &amp; fisik sisa muatan bak yang dibongkar pada lini produksi.
                    @if ($qc->parentQc)
                        <div style="margin-top: 0.45rem; font-size: 0.75rem; color: #581c87; background: #ffffff; border: 1px solid #e9d5ff; border-radius: 6px; padding: 0.4rem 0.6rem;">
                            🔗 <strong>Truk Induk Kedatangan:</strong> #{{ $qc->parentQc->qc_no }} &bull; Plat: <strong>{{ $qc->parentQc->plat_nomor_truk ?: '-' }}</strong> &bull; Sopir: <strong>{{ $qc->parentQc->sopir_nama ?: '-' }}</strong> &bull; Netto Uji 1: <strong style="color: #15803d;">{{ number_format($qc->parentQc->details->sum('qty_netto_lolos'), 0, ',', '.') }} kg</strong>
                        </div>
                    @endif
                @else
                    Form koreksi data inspeksi kedatangan truk awal saat tiba di gerbang pos penerimaan pabrik.
                @endif
            </div>
        </div>

        {{-- KARTU 1: INFO PENGIRIMAN, PO & KOMODITAS --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🚚</span> <span>1. Info Armada, PO &amp; Komoditas Singkong</span>
                <span style="font-size: 0.72rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; float: right; {{ $isPengujian2 ? 'background: #f3e8ff; color: #7e22ce;' : 'background: #e0f2fe; color: #0369a1;' }}">
                    {{ $isPengujian2 ? '🍟 Pengujian II' : '🚛 Pengujian I' }}
                </span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                {{-- PILIHAN PO KHUSUS SINGKONG --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <label class="qc-info-label" style="font-weight: 700; margin: 0;">Referensi Purchase Order (PO)</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">
                            PO Singkong ({{ $filteredPos->count() }} Tersedia)
                        </span>
                    </div>
                    <select name="po_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        <option value="">-- Tanpa PO / Non-PO (Pembelian Langsung) --</option>
                        @foreach ($filteredPos as $po)
                            @php
                                $totPesan = (float) $po->details->sum('pesan_qty');
                                $terimaIni = ($po->po_id == $qc->po_id && $qc->terima) ? (float) $qc->terima->details->sum('terima_qty') : 0;
                                $sudahMsk = max(0, (float) $po->details->sum('terima_qty') - $terimaIni);
                                $sisaKta = max(0, $totPesan - $sudahMsk);
                            @endphp
                            <option value="{{ $po->po_id }}" {{ old('po_id', $qc->po_id) == $po->po_id ? 'selected' : '' }}>
                                {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} (Sisa: {{ number_format($sisaKta, 0, ',', '.') }} kg)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SUPPLIER & GUDANG --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 700; margin: 0;">🏢 Mitra Supplier / Petani *</label>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">
                                Petani Singkong
                            </span>
                        </div>
                        <div style="margin-bottom: 0.4rem;">
                            <input type="text" id="editSupplierSearch" class="form-control" placeholder="🔍 Cari nama petani / supplier..." oninput="filterEditSuppliers(this.value)" style="min-height: 38px; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 6px;">
                        </div>
                        <select name="supplier_id" id="editSupplierSelect" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required onchange="onEditSupplierChange(this)">
                            @foreach ($suppliers as $sup)
                                @php
                                    $isPetani = str_starts_with($sup->supplier_cd, 'SKG-') || ($sup->jenisSupplier?->jenis_supplier_cd === 'RAW');
                                @endphp
                                <option value="{{ $sup->supplier_id }}"
                                        data-supplier-nm="{{ $sup->supplier_nm }}"
                                        {{ old('supplier_id', $qc->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                    {{ $sup->supplier_nm }} ({{ $isPetani ? 'Petani Singkong' : ($sup->jenisSupplier?->jenis_supplier_nm ?? 'Vendor') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.25rem;">🏢 Perusahaan / Gudang Bongkar *</label>
                        <select name="gudang_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} @if(!empty($g->tipe_gudang_cd) && $g->tipe_gudang_cd !== 'Pusat') ({{ $g->tipe_gudang_cd }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- PILIH BARANG & NAMA JENIS --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Komoditas Bahan (Master Data) *</label>
                        <select name="items[{{ $qcdtlId }}][barang_id]" id="editBarangSelect" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; color: #0284c7;" required onchange="onEditBarangChanged(this)">
                            @foreach ($filteredBarangs as $b)
                                <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}" {{ old('items.'.$qcdtlId.'.barang_id', $barangId) == $b->barang_id ? 'selected' : '' }}>
                                    {{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'kg' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="qc-info-label" style="font-weight: 800; margin: 0;">NAMA JENIS :</label>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 1px 6px; border-radius: 4px;">Form HACCP MFI</span>
                        </div>
                        <input type="text" name="nama_jenis" id="editNamaJenis" list="listEditNamaJenis" value="{{ old('nama_jenis', $qc->nama_jenis) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800;" placeholder="Singkong Basah Curah / Singkong Ketan / Singkong Gajah">
                        <datalist id="listEditNamaJenis">
                            <option value="Singkong Basah Curah">
                            <option value="Singkong Ketan">
                            <option value="Singkong Gajah">
                            <option value="Singkong Manggu">
                            <option value="Singkong Mentega">
                        </datalist>
                    </div>
                </div>

                {{-- SURAT JALAN, PLAT NOMOR, SOPIR, WAKTU --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">No. Surat Jalan / DO</label>
                        <input type="text" name="nomor_do" id="inputNomorDo" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" oninput="const sjEl = document.getElementById('inputSuratJalanSupplier'); if(sjEl) sjEl.value = this.value;">
                        <input type="hidden" name="surat_jalan_supplier" id="inputSuratJalanSupplier" value="{{ old('surat_jalan_supplier', $qc->surat_jalan_supplier ?? $qc->nomor_do) }}">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">No. Plat Truk</label>
                        <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; text-transform: uppercase;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Nama Sopir</label>
                        <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" style="width: 100%; border-radius: 8px;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Waktu Periksa</label>
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="form-control" style="width: 100%; border-radius: 8px; font-size: 0.8rem;">
                    </div>
                </div>

                {{-- INFO KEBUN & PANEN SINGKONG --}}
                <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.85rem;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: #334155; margin-bottom: 0.4rem;">INFO PANEN &amp; SAMPEL SINGKONG:</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.4rem;">
                        <div>
                            <label class="qc-info-label" style="font-size: 0.72rem;">Lokasi Panen</label>
                            <input type="text" name="lokasi_panen" value="{{ old('lokasi_panen', $qc->lokasi_panen) }}" placeholder="Kecamatan / Desa" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                        </div>
                        <div>
                            <label class="qc-info-label" style="font-size: 0.72rem;">Umur Singkong (Bulan)</label>
                            <input type="number" step="0.1" name="umur_singkong_bln" value="{{ old('umur_singkong_bln', $qc->umur_singkong_bln ?? 9.0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div>
                            <label class="qc-info-label" style="font-size: 0.72rem;">Tanggal Panen</label>
                            <input type="date" name="tgl_panen" value="{{ old('tgl_panen', $qc->tgl_panen ? $qc->tgl_panen->format('Y-m-d') : '') }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                        </div>
                        <div>
                            <label class="qc-info-label" id="lblSampleUji" style="font-size: 0.72rem;">
                                {{ $isPengujian2 ? 'Sampel Uji Sisa Bak (kg)' : 'Jumlah Sampel Uji (kg)' }}
                            </label>
                            <input type="number" step="0.1" name="jumlah_sample_kg" value="{{ old('jumlah_sample_kg', $qc->jumlah_sample_kg ?? 7.0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                        </div>
                    </div>
                </div>

                {{-- AUDIT HALAL & KEBERSIHAN --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.85rem; margin-top: 0.25rem;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: #475569; margin-bottom: 0.4rem;">AUDIT TRANSPORTASI &amp; JAMINAN HALAL:</div>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.8rem;">
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span style="font-weight: 700;">Truk Bersih (Bebas Najis / Kotoran / Cemaran)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="angkut_barang_haram_st" value="1" {{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span style="font-weight: 700; color: #dc2626;">Truk Membawa Barang Haram (Centang jika ada)</span>
                        </label>
                        <div style="margin-top: 0.25rem;">
                            <input type="text" name="komentar_transportasi" value="{{ old('komentar_transportasi', $qc->komentar_transportasi) }}" placeholder="Catatan kebersihan armada / audit halal..." class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: TIMBANGAN & TONASE --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>⚖️</span> <span>2. Hasil Timbangan &amp; Tonase</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">
                            {{ $isPengujian2 ? 'Jumlah SJ (kg) (Auto Uji 1)' : 'Jumlah Surat Jalan (kg)' }}
                        </label>
                        <input type="number" step="any" name="jumlah_surat_jalan" id="inputJumlahSJ" value="{{ old('jumlah_surat_jalan', $qc->jumlah_surat_jalan ?? $firstDetail?->qty_timbang_gross) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" oninput="syncMobileGrossFromSJ(this.value)">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">
                            {{ $isPengujian2 ? 'Muatan Uji 2 / Sisa Bak (kg)' : 'Jumlah di Pabrik (kg)' }}
                        </label>
                        <input type="number" step="any" name="jumlah_di_pabrik" id="inputJumlahPabrik" value="{{ old('jumlah_di_pabrik', $qc->jumlah_di_pabrik ?? ($firstDetail?->qty_timbang_gross ?? $totalGross)) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; color: #0284c7;" oninput="syncMobileGrossFromPabrik(this.value)">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Bruto (kg) *</label>
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="inputGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', $firstDetail?->qty_timbang_gross ?? ($qc->jumlah_di_pabrik ?? $totalGross)) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800;" oninput="syncMobilePabrikFromGross(this.value)" required>
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Refraksi (%)</label>
                        <input type="number" step="0.1" name="items[{{ $qcdtlId }}][refraksi_persen]" id="inputRefraksiPersen" value="{{ old('items.'.$qcdtlId.'.refraksi_persen', $firstDetail?->refraksi_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 8px; color: #d97706; font-weight: 700;" oninput="recalcMobileNetto()">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Reject (kg)</label>
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_reject]" id="inputReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', $firstDetail?->qty_reject ?? $totalReject) }}" class="form-control" style="width: 100%; border-radius: 8px; color: #dc2626; font-weight: 700;" oninput="recalcMobileNetto()">
                    </div>
                </div>

                <div style="margin-top: 0.15rem;">
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Kadar Air Singkong (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][kadar_air_persen]" value="{{ old('items.'.$qcdtlId.'.kadar_air_persen', $firstDetail?->kadar_air_persen ?? 12.0) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" placeholder="12.0">
                </div>

                <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 10px; padding: 0.85rem 1rem; text-align: center; margin-top: 0.25rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #065f46; text-transform: uppercase;">Estimasi Netto Lolos Diterima Pabrik</div>
                    <div style="font-size: 1.45rem; font-weight: 900; color: #059669; margin-top: 0.2rem;" id="displayNettoText">
                        {{ number_format($totalNetto, 0, ',', '.') }} kg
                    </div>
                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputNettoLolos" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $firstDetail?->qty_netto_lolos ?? $totalNetto) }}">
                </div>
            </div>
        </div>

        {{-- KARTU 3: PARAMETER MUTU, FISIK & HASIL FRYER LENGKAP --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🔬</span> <span>3. Parameter Fisik &amp; Uji Goreng Singkong</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- STATUS RAW MATERIAL SINGKONG --}}
                <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 10px; padding: 0.75rem 0.85rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                    <span style="font-weight: 800; font-size: 0.82rem; color: #9a3412;">STATUS RAW MATERIAL SINGKONG :</span>
                    <div style="display: flex; gap: 1rem; font-size: 0.82rem; font-weight: 700;">
                        <label style="display: flex; align-items: center; gap: 4px; color: #15803d; cursor: pointer;">
                            <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="OK" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'checked' : '' }}> OK (Standar)
                        </label>
                        <label style="display: flex; align-items: center; gap: 4px; color: #dc2626; cursor: pointer;">
                            <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="TDK_STD" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? 'checked' : '' }}> TDK STD (Menyimpang)
                        </label>
                    </div>
                </div>

                {{-- KONDISI FISIK & KESEGARAN UMBI --}}
                <div>
                    <div style="font-size: 0.78rem; font-weight: 800; color: #059669; margin-bottom: 0.35rem;">
                        {{ $isPengujian2 ? 'PENGUJIAN II • PEMERIKSAAN FISIK LAPISAN DALAM BAK (SISA MUATAN):' : 'PENGUJIAN I • KONDISI FISIK & KESEGARAN UMBI (AWAL):' }}
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.4rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.65rem;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_segar]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_segar', $firstDetail?->kondisi_segar ?? 1) ? 'checked' : '' }}>
                            <span style="font-weight: 700; color: #16a34a;">✔ Segar</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_layu]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_layu', $firstDetail?->kondisi_layu) ? 'checked' : '' }}>
                            <span style="font-weight: 700; color: #b45309;">✖ Layu</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_busuk]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_busuk', $firstDetail?->kondisi_busuk) ? 'checked' : '' }}>
                            <span style="font-weight: 700; color: #dc2626;">✖ Busuk</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_berjamur]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_berjamur', $firstDetail?->kondisi_berjamur) ? 'checked' : '' }}>
                            <span style="font-weight: 700; color: #dc2626;">✖ Berjamur</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_basah]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_basah', $firstDetail?->kondisi_basah) ? 'checked' : '' }}>
                            <span style="font-weight: 700; color: #0284c7;">✖ Basah</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_lembek]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_lembek', $firstDetail?->kondisi_lembek) ? 'checked' : '' }}>
                            <span style="font-weight: 700; color: #dc2626;">✖ Lembek</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_terkelupas]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_terkelupas', $firstDetail?->kondisi_terkelupas) ? 'checked' : '' }}>
                            <span style="font-weight: 700; color: #d97706;">Terkelupas</span>
                        </label>
                    </div>
                </div>

                {{-- PROPORSI DIAMETER UMBI & DEFECT SORTIR FISIK --}}
                <div>
                    <div style="font-size: 0.78rem; font-weight: 800; color: #0284c7; margin-bottom: 0.35rem;">PROPORSI DIAMETER &amp; DEFECT SORTIR FISIK:</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; margin-bottom: 0.5rem;">
                        <div>
                            <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Diameter &lt; 4 cm (% Max 5%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][diameter_kurang_4cm_persen]" value="{{ old('items.'.$qcdtlId.'.diameter_kurang_4cm_persen', $firstDetail?->diameter_kurang_4cm_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 8px;">
                        </div>
                        <div>
                            <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Diameter &ge; 4 cm (% Min 95%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][diameter_lebih_4cm_persen]" value="{{ old('items.'.$qcdtlId.'.diameter_lebih_4cm_persen', $firstDetail?->diameter_lebih_4cm_persen ?? 100) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                        <div>
                            <label class="qc-info-label" style="font-size: 0.72rem;">Defect Gambos (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_gambos_persen]" value="{{ old('items.'.$qcdtlId.'.defect_gambos_persen', $firstDetail?->defect_gambos_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700; color: #dc2626;">
                        </div>
                        <div>
                            <label class="qc-info-label" style="font-size: 0.72rem;">Patah/Remuk (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_breakage_persen]" value="{{ old('items.'.$qcdtlId.'.defect_breakage_persen', $firstDetail?->defect_breakage_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                        </div>
                        <div>
                            <label class="qc-info-label" style="font-size: 0.72rem;">Kulit/Cluster (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_cluster_persen]" value="{{ old('items.'.$qcdtlId.'.defect_cluster_persen', $firstDetail?->defect_cluster_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                        </div>
                    </div>
                </div>

                {{-- HASIL UJI GORENG (LAB FRYER) & CACAT GORENG --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.35rem;">
                        <div style="font-size: 0.78rem; font-weight: 800; color: #d97706;">
                            {{ $isPengujian2 ? 'PENGUJIAN II • UJI GORENG SISA BAK (TITIK KRITIS PRODUKSI):' : 'PENGUJIAN I • HASIL UJI GORENG LAB FRYER (AWAL):' }}
                        </div>
                        <span style="font-size: 0.7rem; font-weight: 800; color: #b45309; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                            Kriteria Sensori
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 0.65rem;">
                        <div>
                            <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.2rem;">Rasa *</label>
                            <select name="items[{{ $qcdtlId }}][fryer_rasa]" id="selectFryerRasa" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 800;" onchange="onRasaGorengChanged(this.value)">
                                <option value="TIDAK_PAHIT" {{ old('items.'.$qcdtlId.'.fryer_rasa', $firstDetail?->fryer_rasa ?? 'TIDAK_PAHIT') === 'TIDAK_PAHIT' ? 'selected' : '' }}>😋 Tidak Pahit</option>
                                <option value="PAHIT" {{ old('items.'.$qcdtlId.'.fryer_rasa', $firstDetail?->fryer_rasa ?? '') === 'PAHIT' ? 'selected' : '' }} style="color: #dc2626; font-weight: 900;">🤢 PAHIT</option>
                            </select>
                        </div>
                        <div>
                            <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.2rem;">Tekstur</label>
                            <select name="items[{{ $qcdtlId }}][fryer_tekstur]" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                <option value="RENYAH" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur ?? 'RENYAH') === 'RENYAH' ? 'selected' : '' }}>✨ Renyah</option>
                                <option value="EMPUK" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur) === 'EMPUK' ? 'selected' : '' }}>🥟 Empuk</option>
                                <option value="ALOT" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur) === 'ALOT' ? 'selected' : '' }}>Alot</option>
                                <option value="LEMBEK" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur) === 'LEMBEK' ? 'selected' : '' }}>Lembek</option>
                                <option value="KERAS" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur) === 'KERAS' ? 'selected' : '' }}>🧱 Keras</option>
                            </select>
                        </div>
                        <div>
                            <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.2rem;">Penampakan</label>
                            <select name="items[{{ $qcdtlId }}][fryer_penampakan]" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                <option value="TIDAK_OILSOAKED" {{ old('items.'.$qcdtlId.'.fryer_penampakan', $firstDetail?->fryer_penampakan ?? 'TIDAK_OILSOAKED') === 'TIDAK_OILSOAKED' ? 'selected' : '' }}>☀️ Normal</option>
                                <option value="OILSOAKED" {{ old('items.'.$qcdtlId.'.fryer_penampakan', $firstDetail?->fryer_penampakan ?? '') === 'OILSOAKED' ? 'selected' : '' }}>💧 Oilsoaked</option>
                            </select>
                        </div>
                    </div>

                    {{-- ALERT BAHAYA RASA PAHIT --}}
                    <div id="bannerPahitWarning" style="display: {{ old('items.'.$qcdtlId.'.fryer_rasa', $firstDetail?->fryer_rasa) === 'PAHIT' ? 'block' : 'none' }}; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 0.65rem 0.85rem; color: #991b1b; font-size: 0.78rem; margin-bottom: 0.65rem; line-height: 1.4;">
                        <strong style="display: flex; align-items: center; gap: 4px; font-size: 0.82rem; margin-bottom: 2px;">
                            <span>⚠️</span> <span>RASA PAHIT TERDETEKSI (AFKIR / RACUN SIANIDA)</span>
                        </strong>
                        Singkong dengan rasa pahit tidak layak konsumsi pabrik! Disposisi otomatis diubah menjadi <strong>TOLAK TOTAL</strong> dan Grade Mutu menjadi <strong>REJECT</strong>.
                    </div>

                    {{-- CACAT GORENG LANJUTAN --}}
                    <div style="font-size: 0.725rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem;">CACAT PENGGORENGAN TAMBAHAN (%):</div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                        <div>
                            <label class="qc-info-label">Foldover (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_foldover_persen]" value="{{ old('items.'.$qcdtlId.'.defect_foldover_persen', $firstDetail?->defect_foldover_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                        </div>
                        <div>
                            <label class="qc-info-label">Oilsoaked (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_oilsoaked_persen]" value="{{ old('items.'.$qcdtlId.'.defect_oilsoaked_persen', $firstDetail?->defect_oilsoaked_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                        </div>
                    </div>
                </div>

                {{-- PENETAPAN GRADE MUTU SINGKONG --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label class="qc-info-label" style="font-weight: 800; margin: 0; color: #0f172a;">PENETAPAN GRADE MUTU SINGKONG *</label>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">
                            Grade Kedatangan
                        </span>
                    </div>
                    <select name="items[{{ $qcdtlId }}][grade_cd]" id="selectGradeCd" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800; font-size: 0.9rem;" required onchange="onGradeChanged(this.value)">
                        <option value="A" {{ old('items.'.$qcdtlId.'.grade_cd', $firstDetail?->grade_cd ?? 'A') === 'A' ? 'selected' : '' }} style="color: #047857; font-weight: 800;">
                            🟢 Grade A (Super - Renyah & Diameter Memenuhi)
                        </option>
                        <option value="B" {{ old('items.'.$qcdtlId.'.grade_cd', $firstDetail?->grade_cd) === 'B' ? 'selected' : '' }} style="color: #b45309; font-weight: 800;">
                            🟡 Grade B (Standar - Ada Ukuran Kecil / Refraksi Tanah)
                        </option>
                        <option value="C" {{ old('items.'.$qcdtlId.'.grade_cd', $firstDetail?->grade_cd) === 'C' ? 'selected' : '' }} style="color: #64748b; font-weight: 800;">
                            ⚪ Grade C (Campur / Sortir Lanjut)
                        </option>
                        <option value="REJECT" {{ old('items.'.$qcdtlId.'.grade_cd', $firstDetail?->grade_cd) === 'REJECT' ? 'selected' : '' }} style="color: #dc2626; font-weight: 900;">
                            🔴 REJECT (Afkir / Tolak Total)
                        </option>
                    </select>

                    <div style="margin-top: 0.5rem;">
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.2rem; font-size: 0.75rem;">Catatan Spesifikasi Mutu Fisik</label>
                        <input type="text" name="items[{{ $qcdtlId }}][catatan_dtl]" value="{{ old('items.'.$qcdtlId.'.catatan_dtl', $firstDetail?->catatan_dtl) }}" class="form-control" placeholder="Contoh: Singkong segar, aroma khas tanah normal, rasa gurih renyah..." style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 4: KEPUTUSAN KESIMPULAN & CATATAN --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>💬</span> <span>4. Keputusan &amp; Catatan QC</span>
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
                    <textarea name="catatan_umum" id="inputCatatanUmum" rows="2" class="form-control" style="width: 100%; border-radius: 8px;" placeholder="Catatan hasil sampling...">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
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
            <button type="submit" id="btnEditSubmit" class="qc-btn-mobile-edit" style="flex: 2; font-size: 0.92rem; padding: 0.8rem 1rem;">
                <span>💾 Simpan Perubahan</span>
            </button>
        </div>

        {{-- FULLSCREEN LOADING OVERLAY --}}
        <div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.72); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; padding: 1.75rem 2rem; border-radius: 16px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); max-width: 320px; margin: 1rem; width: 90%;">
                <div style="width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: qcSpin 0.8s linear infinite; margin: 0 auto 1.15rem;"></div>
                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.4rem;">Memperbarui Tiket QC Singkong...</div>
                <div style="font-size: 0.8rem; color: #64748b; line-height: 1.45;">Sedang menyimpan koreksi uji mutu singkong. Mohon tidak menekan tombol lagi.</div>
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
    <script src="{{ asset('js/gudang/qc/mobile/qc-edit.js') }}"></script>
@endpush
