@extends('layouts.qc-mobile')

@section('title', 'Sampling Mutu Minyak Goreng - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (str_contains($nm, 'MINYAK') || str_starts_with($cd, 'BP-MY') || str_starts_with($cd, 'MSW') || str_starts_with($cd, 'MKP')) {
            return 'MINYAK';
        }
        return 'LAINNYA';
    };

    $getPoCommodities = function ($p) use ($classifyBarang) {
        $cats = [];
        foreach ($p->details as $d) {
            $cat = $classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd);
            $cats[] = $cat;
        }
        return array_values(array_unique($cats));
    };

    // Filter PO KHUSUS Minyak Saja (Hanya PO yang memuat komoditas MINYAK)
    $minyakPos = $pos->filter(function($p) use ($getPoCommodities, $selectedPo) {
        if ($selectedPo && $selectedPo->po_id == $p->po_id) return true;
        $cats = $getPoCommodities($p);
        return in_array('MINYAK', $cats);
    });

    // Filter Supplier khusus Minyak (Vendor Non-Petani / Bahan Penolong)
    $minyakSuppliers = $suppliers->filter(function($s) use ($selectedPo) {
        if ($selectedPo && $selectedPo->supplier_id == $s->supplier_id) return true;
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($minyakSuppliers->isEmpty()) {
        $minyakSuppliers = $suppliers;
    }
@endphp

<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan Khusus Minyak Goreng (Sama dengan Singkong) --}}
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.25rem 1.5rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 QC LAPANGAN &bull; INBOUND
                    </span>
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        No. Dok: MFI/HACCP-04/FRM-03/029/VIII/2021
                    </span>
                </div>
                <h1 style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    Sampling Mutu Minyak Goreng
                </h1>
                <p style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Pemeriksaan FFA di COA, Kebersihan Tangki &amp; Audit Transportasi (Formulir HACCP MFI)
                </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; font-weight: 700;" title="Kembali ke Web ERP Desktop">
                        🖥️ Ke Web ERP
                    </a>
                @endif
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'MINYAK']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #0284c7; border-radius: 8px; font-weight: 700;">
                    📋 Riwayat Minyak
                </a>
            </div>
        </div>
    </div>

    {{-- SELECTOR 7 KOMODITAS (LINK NAVIGASI LANGSUNG ANTAR HALAMAN MODUL) --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-top: none; border-radius: 0 0 14px 14px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="font-size: 0.775rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🏷️</span> <span>PILIH JENIS BAHAN DATANG:</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(115px, 1fr)); gap: 0.45rem;">
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'SINGKONG']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🥔 Singkong
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'MINYAK']) }}" class="btn komoditas-btn active-komoditas" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid #0284c7; background: #0284c7; color: #ffffff; text-align: center; text-decoration: none;">
                🛢️ Minyak
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PLASTIK']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🛍️ Plastik
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'KARTON']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                📦 Karton
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'MSG']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🧂 MSG
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'GARAM']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🧂 Garam
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PERENYAH']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                ✨ Perenyah
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-error" style="margin-top: 0.5rem; margin-bottom: 1rem; border-radius: 10px;">
            <div style="font-weight: 700; margin-bottom: 0.35rem;">⚠️ Harap periksa isian formulir:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STEP / TAB SWITCHER KHUSUS MINYAK (2 TAB PERSIS SEPERTI SINGKONG) --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchMinyakTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #0284c7; background: #0284c7; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Dokumen &amp; Armada
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchMinyakTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🛢️ 2. Mutu Minyak &amp; FFA
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcMinyakForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="form_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="MINYAK">
        <input type="hidden" name="status_uji_goreng" value="SELESAI">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" style="display: flex; flex-direction: column; gap: 1rem;">
            {{-- WIDGET WAKTU INSPEKSI LAPANGAN (SESUAI GAMBAR 1) --}}
            <div class="minyak-inspection-bar">
                <div class="inspection-bar-left">
                    <div class="inspection-bar-icon">📅</div>
                    <div>
                        <div class="inspection-bar-label">Waktu Inspeksi Lapangan</div>
                        <div class="inspection-bar-val" id="textInspectionBarTime">{{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') }} WIB</div>
                    </div>
                </div>
                <span class="inspection-bar-badge">QC-RM-042</span>
            </div>

            {{-- CARD 1: INFORMASI BAHAN & PENGIRIMAN (SESUAI GAMBAR 1) --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="card-step-badge">1</span>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.25;">Informasi Bahan &amp; Pengiriman</h2>
                        </div>
                    </div>
                    <span class="card-step-tag">DO / SURAT JALAN</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    {{-- 1. REFERENSI PO & GUDANG BONGKAR (SEJAJAR & BERSIH TANPA TOMBOL/SEARCH TERPISAH) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Referensi PO Minyak <span style="font-size: 0.72rem; color: #64748b; font-weight: normal;">(Opsional)</span></label>
                            </div>
                            <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 700;">
                                <option value="">-- Tanpa PO / Kiriman Langsung Minyak --</option>
                                @foreach ($minyakPos as $p)
                                    @php
                                        $poCats = $getPoCommodities($p);
                                        $itemNames = $p->details->map(fn($d) => $d->barang?->barang_nm)->filter()->unique()->implode(', ');
                                        $totalSisa = $p->details->sum(fn($d) => max(0, (float)$d->pesan_qty - (float)$d->terima_qty));
                                    @endphp
                                    <option value="{{ $p->po_id }}"
                                            data-komoditas="{{ implode(',', $poCats) }}"
                                            data-supplier-id="{{ $p->supplier_id }}"
                                            data-gudang-id="{{ $p->gudang_id }}"
                                            data-search="{{ strtolower($p->po_no . ' ' . ($p->supplier?->supplier_nm ?? '') . ' ' . $itemNames) }}"
                                            {{ old('po_id', $selectedPo?->po_id) == $p->po_id ? 'selected' : '' }}>
                                        {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} ({{ $itemNames ?: 'Item Minyak' }} - Sisa: {{ number_format($totalSisa, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Gudang Bongkar <span style="color:#ef4444;">*</span></label>
                            </div>
                            <select name="gudang_id" id="gudangSelect" class="form-control" required style="font-size: 0.85rem; font-weight: 700;">
                                @foreach ($gudangs as $g)
                                    <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id ?? auth()->user()?->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                        {{ $g->display_name ?? $g->gudang_nm }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 2. NAMA BAHAN BAKU (MASTER BARANG DENGAN SATUAN KG / LITER) --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <div class="qc-field-header">
                            <label class="qc-field-label">Nama Bahan Baku <span style="color:#ef4444;">*</span></label>
                        </div>
                        <select name="minyak_barang_id" id="minyakBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this); onMinyakBarangChanged(this);">
                            @foreach ($barangs as $b)
                                @if (stripos($b->barang_nm, 'minyak') !== false)
                                    @php
                                        $satNm = $b->satuanDasar?->satuan_nm ?? ($b->satuanDasar?->satuan_cd ?? 'KG');
                                        $satCd = strtoupper($b->satuanDasar?->satuan_cd ?? 'KG');
                                    @endphp
                                    <option value="{{ $b->barang_id }}"
                                            data-nama="{{ $b->barang_nm }}"
                                            data-satuan="{{ $satNm }}"
                                            data-satuan-cd="{{ $satCd }}"
                                            {{ (stripos($b->barang_nm, 'sawit') !== false) ? 'selected' : '' }}>
                                        {{ $b->barang_nm }} ({{ $b->barang_cd }}) &bull; Satuan: {{ $satNm }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" name="minyak_satuan" id="inputMinyakSatuan" value="{{ old('minyak_satuan', 'KG') }}">
                    </div>

                    {{-- 3. MITRA VENDOR & NAMA PRODUSEN (DIDIKATKAN MENJADI SATU KELOMPOK & BISA DIEDIT LANGSUNG) --}}
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                    Mitra Vendor / Supplier Minyak <span style="color:#ef4444;">*</span>
                                </label>
                            </div>
                            <select name="supplier_id" id="supplierSelect" class="form-control" required onchange="onSupplierSelected(this)" style="font-weight: 700; font-size: 0.85rem;">
                                <option value="">-- Pilih Mitra Vendor / Supplier Minyak --</option>
                                @foreach ($minyakSuppliers as $s)
                                    @php
                                        $jenisCd = $s->jenisSupplier?->jenis_supplier_cd ?: (str_starts_with($s->supplier_cd, 'SKG-') ? 'RAW' : 'BP');
                                        $isPetani = ($jenisCd === 'RAW' || str_starts_with($s->supplier_cd, 'SKG-'));
                                    @endphp
                                    <option value="{{ $s->supplier_id }}"
                                            data-supplier-nm="{{ $s->supplier_nm }}"
                                            data-supplier-cd="{{ $s->supplier_cd }}"
                                            data-jenis-cd="{{ $jenisCd }}"
                                            data-is-petani="{{ $isPetani ? '1' : '0' }}"
                                            {{ old('supplier_id', $selectedPo?->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                                        {{ $s->supplier_nm }} ({{ $s->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nama Produsen <span style="font-size: 0.72rem; color: #0284c7; font-weight: normal;">(Bisa diedit/ketik)</span></label>
                                </div>
                                <input type="text" name="nama_produsen" id="namaProdusenInput" class="form-control" placeholder="PT Sari Agro Mandiri" value="{{ old('nama_produsen', 'PT Sari Agro Mandiri') }}" style="font-weight: 700;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Negara Produsen <span style="font-size: 0.72rem; color: #0284c7; font-weight: normal;">(Bisa diedit)</span></label>
                                </div>
                                <input type="text" name="negara_produsen" class="form-control" value="{{ old('negara_produsen', 'Indonesia') }}" style="font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    {{-- 4. NOMOR DO (SURAT JALAN) & TANGGAL DATANG (SEJAJAR & EDITABLE) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group">
                            <div class="qc-field-header">
                                <label class="qc-field-label">No. DO (Surat Jalan)</label>
                            </div>
                            <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="DO-SAM/001" value="{{ old('surat_jalan_supplier') }}" style="font-weight: 700;">
                        </div>
                        <div class="form-group">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Tanggal Datang</label>
                                <button type="button" onclick="setCurrentDateTime()" style="background: none; border: none; color: #0284c7; font-size: 0.72rem; font-weight: 800; cursor: pointer; padding: 0;">🕒 Sekarang</button>
                            </div>
                            <input type="datetime-local" name="tgl_periksa" id="inputTglDatang" class="form-control" data-has-old="{{ old('tgl_periksa') ? '1' : '0' }}" value="{{ old('tgl_periksa', now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" style="font-weight: 700;">
                        </div>
                    </div>

                    {{-- 5. SUBCARD: KUANTITAS & VERIFIKASI TIMBANGAN (SESUAI GAMBAR 1 DENGAN SATUAN DINAMIS) --}}
                    <div class="minyak-subcard">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                            <span class="minyak-subcard-title" style="margin-bottom: 0;">Kuantitas &amp; Verifikasi Timbangan</span>
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <div class="satuan-toggle-pills" id="satuanToggleGroup">
                                    <button type="button" class="btn-satuan-pill active" id="pillSatuan_KG" onclick="setMinyakSatuan('KG')">
                                        <span class="pill-icon">⚖️</span>
                                        <span class="pill-text">KG</span>
                                    </button>
                                    <button type="button" class="btn-satuan-pill" id="pillSatuan_LITER" onclick="setMinyakSatuan('Liter')">
                                        <span class="pill-icon">💧</span>
                                        <span class="pill-text">Liter</span>
                                    </button>
                                </div>
                                <span id="badgeSelisihTimbangan" style="display: none; font-size: 0.7rem; font-weight: 800; padding: 2px 7px; border-radius: 6px;"></span>
                            </div>
                        </div>
                        <div class="qc-grid-2col">
                            <div class="form-group">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah Surat Jalan (<span class="label-satuan-text">KG</span>)</label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="0.01" min="0" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="16000" value="{{ old('jumlah_surat_jalan') }}" oninput="syncMinyakQuantity('sj')" style="font-weight: 800;">
                                    <span class="input-suffix-text suffix-satuan-text">KG</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah di Pabrik (<span class="label-satuan-text">KG</span>)</label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="0.01" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="15985" value="{{ old('jumlah_di_pabrik') }}" oninput="syncMinyakQuantity('pabrik')" style="font-weight: 800;">
                                    <span class="input-suffix-text suffix-satuan-text">KG</span>
                                </div>
                            </div>
                        </div>

                        {{-- Jumlah Sampel QC Diambil & Netto Sementara --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.25rem; border-top: 1px dashed #e2e8f0; padding-top: 0.45rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.8rem; font-weight: 800; color: #1e293b;">Sampel QC Diambil:</span>
                                <div class="input-suffix-wrap" style="max-width: 120px;">
                                    <input type="number" step="1" name="jumlah_sample_gr" class="form-control" placeholder="500" value="{{ old('jumlah_sample_gr', 500) }}" style="text-align: right; font-weight: 900; color: #0284c7; height: 38px !important; min-height: 38px !important;">
                                    <span class="input-suffix-text">gr</span>
                                </div>
                            </div>
                            <span id="labelSubcardNetto" style="font-size: 0.75rem; font-weight: 800; color: #059669;">Netto: 0,00 KG</span>
                        </div>
                    </div>

                    {{-- 6. NAMA JENIS SPESIFIKASI & ARMADA --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; font-size: 0.8rem;">NAMA JENIS : (Spesifikasi Fraksi / Grade)</label>
                        <input type="text" name="nama_jenis" id="namaJenisInput" list="listMinyakJenisPage" class="form-control" placeholder="RBD Palm Olein CP8 / Curah Sawit" value="{{ old('nama_jenis', 'RBD Palm Olein / Curah Sawit') }}" oninput="if (document.getElementById('minyakNamaJenisInput')) document.getElementById('minyakNamaJenisInput').value = this.value;">
                        <datalist id="listMinyakJenisPage">
                            <option value="RBD Palm Olein (CP8)">
                            <option value="RBD Palm Olein (CP10)">
                            <option value="Minyak Curah Kelapa Sawit">
                            <option value="Minyak Kelapa (RBD CNO)">
                            <option value="Minyak Sawit Super (Filma)">
                        </datalist>
                    </div>

                    <div class="qc-grid-2col">
                        <div class="form-group">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Plat Truk / Tangki</label>
                            </div>
                            <input type="text" name="plat_nomor_truk" class="form-control" placeholder="B 9876 XYZ" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase; font-weight: 800;">
                        </div>
                        <div class="form-group">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Nama Sopir</label>
                            </div>
                            <input type="text" name="sopir_nama" class="form-control" placeholder="Nama pengemudi" value="{{ old('sopir_nama') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: HALAL & HIGIENE TRANSPORTASI (SESUAI GAMBAR 2) --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #bbf7d0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #f0fdf4; padding: 1rem 1.25rem; border-bottom: 1px solid #dcfce7; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="card-step-badge green">2</span>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 800; color: #166534; margin: 0; line-height: 1.25;">Halal &amp; Higiene Transportasi</h2>
                            <span style="font-size: 0.725rem; color: #15803d; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; CCP Titik Kritis</span>
                        </div>
                    </div>
                    <span style="font-size: 1.25rem;">🛡️</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                    {{-- ITEM 1: KONDISI TRANSPORTASI (TRUK TANGKI / KONTAINER) --}}
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin-bottom: 0.45rem;">
                            1. Kondisi Transportasi (Truk Tangki / Kontainer)
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            {{-- Option 1: Bebas Cemaran --}}
                            <label class="transport-radio-card active" id="cardTransportBebas">
                                <input type="radio" name="bebas_cemaran_st" value="1" checked onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #15803d;">Tidak ada cemaran, Najis / Kotoran</div>
                                    <div class="transport-card-desc">Truk bersih, segel utuh, tidak berbau asing</div>
                                </div>
                            </label>

                            {{-- Option 2: Ada Cemaran --}}
                            <label class="transport-radio-card is-danger" id="cardTransportCemar">
                                <input type="radio" name="bebas_cemaran_st" value="0" onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #dc2626;">Ada cemaran / Kotor / Najis</div>
                                    <div class="transport-card-desc">Ditemukan indikasi kontaminasi pada armada</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 2: DIANGKUT BERSAMA DENGAN BARANG HARAM? --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">2. Apakah barang tersebut diangkut bersama dengan barang haram?</div>
                                <div class="halal-qa-sub">Persyaratan mutlak integrasi logistik halal</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btnHaramTidak" onclick="setHalalToggle('angkut_barang_haram', 0)">Tidak</button>
                                <button type="button" class="segmented-toggle-btn" id="btnHaramYa" onclick="setHalalToggle('angkut_barang_haram', 1)">Ya</button>
                            </div>
                            <input type="hidden" name="angkut_barang_haram_st" id="input_angkut_barang_haram" value="{{ old('angkut_barang_haram_st', 0) }}">
                        </div>
                        <input type="text" name="komentar_transportasi" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_transportasi') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- ITEM 3: TERDAFTAR & DISETUJUI OLEH LPPOM MUI/BPJPH? --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">3. Apakah bahan tersebut terdaftar &amp; disetujui oleh LPPOM MUI/BPJPH?</div>
                                <div class="halal-qa-sub">Cek daftar bahan approved list</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn" id="btnLppomTidak" onclick="setHalalToggle('terdaftar_lppom', 0)">Tidak</button>
                                <button type="button" class="segmented-toggle-btn active ok" id="btnLppomYa" onclick="setHalalToggle('terdaftar_lppom', 1)">Ya</button>
                            </div>
                            <input type="hidden" name="terdaftar_lppom_st" id="input_terdaftar_lppom" value="{{ old('terdaftar_lppom_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_lppom" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_lppom') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- ITEM 4: MEMPUNYAI SERTIFIKAT HALAL? --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">4. Apakah barang tersebut mempunyai sertifikat halal?</div>
                                <div class="halal-qa-sub">Dokumen sertifikat halal resmi</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn" id="btnSertifikatTidak" onclick="setHalalToggle('ada_sertifikat', 0)">Tidak</button>
                                <button type="button" class="segmented-toggle-btn active ok" id="btnSertifikatYa" onclick="setHalalToggle('ada_sertifikat', 1)">Ya</button>
                            </div>
                            <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat" value="{{ old('ada_sertifikat_halal_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_sertifikat" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_sertifikat') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- ITEM 5: SERTIFIKAT HALAL MASIH BERLAKU? --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">5. Apakah sertifikat halal barang tersebut masih berlaku?</div>
                                <div class="halal-qa-sub">Validitas status aktif sertifikat</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn" id="btnBerlakuTidak" onclick="setHalalToggle('sertifikat_berlaku', 0)">Tidak</button>
                                <button type="button" class="segmented-toggle-btn active ok" id="btnBerlakuYa" onclick="setHalalToggle('sertifikat_berlaku', 1)">Ya</button>
                            </div>
                            <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_berlaku" value="{{ old('sertifikat_halal_berlaku_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_berlaku" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_berlaku') }}" style="font-size: 0.85rem;">
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN PARAMETER & MUTU KHUSUS MINYAK GORENG               --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" style="display: none; flex-direction: column; gap: 1rem;">
            @include('gudang.qc.partials.minyak.form-minyak')
        </div>

        {{-- FLOATING ACTION DOCK UNTUK MOBILE (DYNAMIC CAPSULE PILL) --}}
        <div class="qc-form-floating-dock" id="qcFloatingDock">
            <button type="button" class="btn-dock-back" id="dockBtnBack" style="display: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span id="dockBackText">Kembali</span>
            </button>
            <button type="button" class="btn-dock-next btn-primary-state" id="dockBtnNext">
                <span id="dockNextText">Lanjut ke Tahap 2</span>
                <svg id="dockNextIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>

        {{-- FULLSCREEN LOADING OVERLAY --}}
        <div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.72); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; padding: 1.75rem 2rem; border-radius: 16px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); max-width: 320px; margin: 1rem; width: 90%;">
                <div style="width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: qcSpin 0.8s linear infinite; margin: 0 auto 1.15rem;"></div>
                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.4rem;" id="overlayTitle">Menyimpan Data QC Minyak...</div>
                <div style="font-size: 0.8rem; color: #64748b; line-height: 1.45;">Sedang memproses inspeksi mutu minyak &amp; meneruskan ke gudang. Mohon tunggu.</div>
            </div>
        </div>
    </form>
</div>

@php
    $poListData = $minyakPos->mapWithKeys(function ($p) use ($getPoCommodities) {
        return [
            $p->po_id => [
                'po_id'       => $p->po_id,
                'po_no'       => $p->po_no,
                'supplier_id' => $p->supplier_id,
                'supplier_nm' => $p->supplier?->supplier_nm,
                'gudang_id'   => $p->gudang_id,
                'komoditas'   => $getPoCommodities($p),
                'items'       => $p->details->map(function ($d) {
                    return [
                        'podtl_id'  => $d->podtl_id,
                        'barang_id' => $d->barang_id,
                        'barang_nm' => $d->barang?->barang_nm,
                        'satuan'    => $d->barang?->satuanDasar?->satuan_nm ?? ($d->barang?->satuanDasar?->satuan_cd ?? 'KG'),
                        'satuan_cd' => strtoupper($d->barang?->satuanDasar?->satuan_cd ?? 'KG'),
                        'pesan_qty' => (float) $d->pesan_qty,
                        'terima_qty'=> (float) $d->terima_qty,
                        'sisa_qty'  => max(0, (float) $d->pesan_qty - (float) $d->terima_qty),
                    ];
                })->values(),
            ]
        ];
    });
@endphp

@push('scripts')
    <script>
        window.qcConfig = {
            poList: {!! json_encode($poListData) !!},
            initialKomoditas: 'MINYAK',
        };
    </script>
    <script src="{{ asset('js/gudang/qc/minyak/minyak.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/minyak/minyak-create.js') }}"></script>
@endpush
@endsection
