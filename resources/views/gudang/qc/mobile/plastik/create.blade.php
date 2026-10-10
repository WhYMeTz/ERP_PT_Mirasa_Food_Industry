@extends('layouts.qc-mobile')

@section('title', 'Sampling Mutu Plastik Kemasan - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (str_contains($nm, 'PLASTIK') || str_contains($nm, 'KEMASAN') || str_contains($nm, 'OPP') || str_contains($nm, 'ROLL') || str_starts_with($cd, 'BP-PL') || str_starts_with($cd, 'BP-KM')) {
            return 'PLASTIK';
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

    // Filter PO khusus Plastik
    $plastikPos = $pos->filter(function($p) use ($getPoCommodities) {
        $cats = $getPoCommodities($p);
        return in_array('PLASTIK', $cats);
    });
    if ($plastikPos->isEmpty()) {
        $plastikPos = $pos;
    }

    // Filter Supplier khusus Plastik (Vendor Kemasan / Non-Petani)
    $plastikSuppliers = $suppliers->filter(function($s) {
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($plastikSuppliers->isEmpty()) {
        $plastikSuppliers = $suppliers;
    }
@endphp
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan Khusus Plastik Kemasan --}}
    <div style="background: linear-gradient(135deg, #0d9488 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.25rem 1.5rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 QC LAPANGAN &bull; INBOUND
                    </span>
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        No. Dok: MFI/HACCP-04/FRM-03/030/VIII/2021
                    </span>
                </div>
                <h1 style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    🛍️ Sampling Mutu Plastik Kemasan
                </h1>
                <p style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Pemeriksaan Ketebalan, Kekuatan Sealing, Cacat Cetak &amp; Kebersihan (Formulir HACCP MFI)
                </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; font-weight: 700;" title="Kembali ke Web ERP Desktop">
                        🖥️ Ke Web ERP
                    </a>
                @endif
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #0d9488; border-radius: 8px; font-weight: 700;">
                    📋 Riwayat Plastik
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
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'MINYAK']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🛢️ Minyak
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PLASTIK']) }}" class="btn komoditas-btn active-komoditas" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid #0d9488; background: #0d9488; color: #ffffff; text-align: center; text-decoration: none;">
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

    {{-- STEP / TAB SWITCHER KHUSUS PLASTIK --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchPlastikTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #0d9488; background: #0d9488; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Dokumen &amp; Armada
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchPlastikTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🛍️ 2. Mutu Plastik Kemasan
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcPlastikForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="form_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="PLASTIK">
        <input type="hidden" name="status_uji_goreng" value="SELESAI">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" class="card" style="border-radius: 12px; border-top: 5px solid #0d9488; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 1: Laporan Kedatangan Plastik Kemasan</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Verifikasi surat jalan, nomor DO, vendor &amp; audit higienitas armada</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="switchPlastikTab(2)" style="background: #0d9488; border-color: #0d9488; border-radius: 8px; font-weight: 700; padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.35rem; flex-shrink: 0;" title="Lanjut ke Pemeriksaan Mutu Plastik (Tahap 2)">
                    <span>Lanjut: Mutu Plastik</span> &rarr;
                </button>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.1rem;">
                {{-- PILIH DARI PO AKTIF DENGAN SMART FILTER --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                        <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                            📄 Referensi Purchase Order (PO) <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">(Opsional - auto-fill data)</span>
                        </label>
                        <span id="poFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #0d9488; background: #ccfbf1; padding: 2px 7px; border-radius: 6px;">
                            Filter: 🛍️ Plastik Kemasan
                        </span>
                    </div>

                    {{-- FILTER CHIPS & QUICK SEARCH UNTUK PO --}}
                    <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                        <div class="supplier-filter-chips" id="poFilterChips">
                            <button type="button" class="btn-supplier-chip active-chip" id="chipPo_PLASTIK" onclick="setPoCategoryFilter('PLASTIK')">
                                🛍️ Khusus PO Plastik
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_ALL" onclick="setPoCategoryFilter('ALL')">
                                🌐 Semua PO ({{ $pos->count() }})
                            </button>
                        </div>
                        <div class="supplier-search-wrap">
                            <input type="text" id="poSearchInput" class="form-control supplier-search-input" placeholder="🔍 Cari nomor PO Plastik atau nama vendor..." oninput="onSearchPo(this.value)">
                            <button type="button" id="btnClearPoSearch" class="supplier-search-clear" onclick="clearPoSearch()" style="display: none;">✕</button>
                        </div>
                    </div>

                    <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 700;">
                        <option value="">-- Tanpa PO / Kiriman Langsung --</option>
                        @foreach ($pos as $p)
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
                                {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} ({{ $itemNames ?: 'Item Kemasan' }} - Sisa: {{ number_format($totalSisa, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SUPPLIER & GUDANG TUJUAN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                            <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                                🏭 Vendor / Supplier Plastik <span style="color:red;">*</span>
                            </label>
                            <span id="supplierFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #0d9488; background: #ccfbf1; padding: 2px 7px; border-radius: 6px;">
                                Filter: 🛍️ Supplier Plastik
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                            <div class="supplier-filter-chips">
                                <button type="button" class="btn-supplier-chip active-chip" id="chipSupplier_VENDOR" onclick="setSupplierCategoryFilter('VENDOR')">
                                    🛍️ Vendor Kemasan
                                </button>
                                <button type="button" class="btn-supplier-chip" id="chipSupplier_ALL" onclick="setSupplierCategoryFilter('ALL')">
                                    🌐 Semua Mitra ({{ $suppliers->count() }})
                                </button>
                            </div>
                            <div class="supplier-search-wrap">
                                <input type="text" id="supplierSearchInput" class="form-control supplier-search-input" placeholder="🔍 Ketik nama vendor plastik..." oninput="onSearchSupplier(this.value)">
                                <button type="button" id="btnClearSupplierSearch" class="supplier-search-clear" onclick="clearSupplierSearch()" style="display: none;">✕</button>
                            </div>
                        </div>

                        <select name="supplier_id" id="supplierSelect" class="form-control" required style="font-weight: 700;">
                            <option value="">-- Pilih Vendor Plastik Kemasan --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->supplier_id }}"
                                        data-jenis-cd="{{ $s->jenisSupplier?->jenis_supplier_cd ?? '' }}"
                                        data-is-petani="{{ str_starts_with(strtoupper($s->supplier_cd ?? ''), 'SKG-') ? '1' : '0' }}"
                                        data-supplier-nm="{{ $s->supplier_nm }}"
                                        data-supplier-cd="{{ $s->supplier_cd }}"
                                        {{ old('supplier_id', $selectedPo?->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                                    {{ $s->supplier_cd }} &bull; {{ $s->supplier_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; color: #0f172a;">Gudang / Lokasi Simpan <span style="color:red;">*</span></label>
                        <select name="gudang_id" id="gudangSelect" class="form-control" required style="font-weight: 700;">
                            <option value="">-- Pilih Gudang Bongkar --</option>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id ?? 1) == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} ({{ $g->lokasi ?? 'Pabrik' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SURAT JALAN & NOMOR DO --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nomor Surat Jalan (SJ)</label>
                        <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="Contoh: SJ-PL-9988" value="{{ old('surat_jalan_supplier') }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nomor Delivery Order (DO)</label>
                        <input type="text" name="nomor_do" class="form-control" placeholder="Contoh: DO-2026/089" value="{{ old('nomor_do') }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nama Pabrik / Produsen</label>
                        <input type="text" name="nama_produsen" id="inputNamaProdusen" class="form-control" placeholder="Contoh: PT Polychem Pack" value="{{ old('nama_produsen') }}">
                    </div>
                </div>

                {{-- KUANTITAS SJ, PABRIK & SAMPLE --}}
                <div style="background: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #115e59; margin-bottom: 0.6rem;">
                        ⚖️ KUANTITAS PENGIRIMAN &amp; CONTOH SAMPLE
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.85rem;">
                        <div>
                            <label class="form-label" style="font-weight: 700;">Kuantitas di SJ (Pcs / Roll) <span style="color:red;">*</span></label>
                            <input type="number" step="1" min="1" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="Contoh: 50000" value="{{ old('jumlah_surat_jalan') }}" required>
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700;">Jumlah Dihitung Pabrik (Pcs)</label>
                            <input type="number" step="1" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="0" value="{{ old('jumlah_di_pabrik') }}">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700;">Jumlah Sample Diperiksa (Pcs)</label>
                            <input type="number" step="1" min="1" name="jumlah_sample_pcs" class="form-control" placeholder="10" value="{{ old('jumlah_sample_pcs', 10) }}">
                        </div>
                    </div>
                </div>

                {{-- ARMADA TRANSPORTASI & KELAYAKAN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Plat Nomor Truk / Box</label>
                        <input type="text" name="plat_nomor_truk" class="form-control" placeholder="Contoh: B 9123 KDA" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nama Sopir / Pengemudi</label>
                        <input type="text" name="sopir_nama" class="form-control" placeholder="Nama sopir..." value="{{ old('sopir_nama') }}">
                    </div>
                </div>

                {{-- AUDIT HIGIENITAS & TRANSPORTASI HALAL --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
                        📋 AUDIT HIGIENITAS ARMADA PENGANGKUT (Standar HACCP MFI):
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', '1') ? 'checked' : '' }}>
                            Bebas Bau &amp; Cemaran Kimia
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="angkut_barang_haram_st" value="1" {{ old('angkut_barang_haram_st') ? 'checked' : '' }}>
                            Mengangkut Barang Najis / Non-Halal
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="terdaftar_lppom_st" value="1" {{ old('terdaftar_lppom_st', '1') ? 'checked' : '' }}>
                            Produsen Memiliki Izin Edar / Sertifikat Halal
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="ada_sertifikat_halal_st" value="1" {{ old('ada_sertifikat_halal_st', '1') ? 'checked' : '' }}>
                            Dokumen CoA / Spesifikasi Kemasan Disertakan
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN KUALITAS PLASTIK KEMASAN (FORM HACCP RESMI)           --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #0d9488; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 2: Pengujian Mutu Plastik Kemasan</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Analisa mikrometer ketebalan, keutuhan seal, cacat cetak &amp; toleransi reject</span>
                </div>
                <div style="display: flex; gap: 0.4rem;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="switchPlastikTab(1)" style="border-radius: 8px; font-weight: 700;">
                        &larr; Kembali: Tahap 1
                    </button>
                    <button type="button" class="btn btn-sm" id="btnTopSimpanCepat" onclick="submitPlastikForm()" style="background: #059669; color: #ffffff; border-radius: 8px; font-weight: 800;">
                        💾 Simpan QC
                    </button>
                </div>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- PILIH ITEM BARANG PLASTIK & SPESIFIKASI --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; color: #0f172a;">Item Plastik Kemasan <span style="color:red;">*</span></label>
                        <select name="plastik_barang_id" id="plastikBarangSelect" class="form-control" style="font-weight: 700;" onchange="onPlastikBarangChanged(this)" required>
                            @foreach ($barangs as $b)
                                @if (stripos($b->barang_nm, 'plastik') !== false || stripos($b->barang_nm, 'kemasan') !== false || stripos($b->barang_nm, 'opp') !== false || stripos($b->barang_nm, 'pp') !== false || stripos($b->barang_nm, 'roll') !== false || stripos($b->barang_cd, 'BP-PL') !== false)
                                    <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}">
                                        {{ $b->barang_cd }} &bull; {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; color: #0f172a;">Spesifikasi / Jenis Kemasan</label>
                        <input type="text" name="nama_jenis" id="plastikNamaJenisInput" class="form-control" placeholder="Contoh: Plastik OPP Roll 250g / Standing Pouch" value="{{ old('nama_jenis', 'Plastik Kemasan Keripik') }}">
                    </div>
                </div>

                {{-- ISI RAW MATERIAL & KEMASAN STATUS --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="plastik_status_raw_material" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="plastik_status_raw_material" value="TDK_STD"> TDK STD
                            </label>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KEMASAN :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="plastik_kemasan_kondisi" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="plastik_kemasan_kondisi" value="TIDAK_STANDARD"> TIDAK STANDARD
                            </label>
                        </div>
                    </div>
                </div>

                {{-- KONDISI KEMASAN / CACAT --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚠️ KONDISI KEMASAN (Centang jika ditemukan cacat):
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="plastik_kemasan_kotor" value="1"> KOTOR
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="plastik_kemasan_apek" value="1"> APEK
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="plastik_kemasan_basah" value="1"> BASAH
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                            <input type="checkbox" name="plastik_kemasan_sobek" value="1"> SOBEK
                        </label>
                    </div>
                </div>

                {{-- ANALISA PARAMETER KETEBALAN & KEUTUHAN --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.75rem;">
                        🔬 ANALISA PARAMETER KETEBALAN &amp; KEUTUHAN
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: #f1f5f9; border-bottom: 1.5px solid #cbd5e1;">
                                <th style="padding: 0.5rem; text-align: left; width: 140px;">Parameter</th>
                                <th style="padding: 0.5rem; text-align: left;">Hasil Analisa</th>
                                <th style="padding: 0.5rem; text-align: left; width: 220px;">Standard</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.5rem; font-weight: 700;">KETEBALAN</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_ketebalan_analisa" class="form-control" placeholder="Contoh: 0.08 mm" value="{{ old('plastik_ketebalan_analisa') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_ketebalan_standar" class="form-control" placeholder="Contoh: 0.08 mm" value="{{ old('plastik_ketebalan_standar', '0.08 mm') }}">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem; font-weight: 700;">KEUTUHAN</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_keutuhan_analisa" class="form-control" placeholder="Contoh: Tidak Sobek" value="{{ old('plastik_keutuhan_analisa', 'Tidak Sobek') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_keutuhan_standar" class="form-control" value="{{ old('plastik_keutuhan_standar', 'Tidak Sobek') }}">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- KUANTITAS LOLOS & REJECT --}}
                <div style="background: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 10px; padding: 1.15rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #115e59;">⚖️ HASIL PEMERIKSAAN KUANTITAS</span>
                        <span id="labelPlastikNetto" style="font-size: 1rem; font-weight: 900; color: #0d9488; background: #ffffff; padding: 2px 10px; border-radius: 6px; border: 1px solid #99f6e4;">
                            0 Pcs
                        </span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.85rem;">
                        <div>
                            <label class="form-label" style="font-weight: 800; color: #0f172a;">Jumlah Lolos (Pcs / Unit) <span style="color:red;">*</span></label>
                            <input type="number" step="1" min="0" name="plastik_qty_gross" id="plastikQtyGross" class="form-control" placeholder="0" value="{{ old('plastik_qty_gross') }}" required>
                        </div>
                        <div>
                            <label class="form-label" style="color: #dc2626; font-weight: 800;">Qty Reject / Cacat (Pcs)</label>
                            <input type="number" step="1" min="0" name="plastik_qty_reject" id="plastikQtyReject" class="form-control" placeholder="0" value="{{ old('plastik_qty_reject', 0) }}">
                        </div>
                    </div>
                </div>

                {{-- KOMENTAR & KESIMPULAN --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
                        <textarea name="plastik_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji mutu plastik kemasan...">{{ old('plastik_komentar') }}</textarea>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                        <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
                        <div style="display: flex; gap: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                                <input type="radio" name="plastik_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="plastik_kesimpulan" value="TOLAK"> ❌ TOLAK
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM DOCK NAVIGATION --}}
        <div class="bottom-dock-nav">
            <button type="button" class="btn btn-secondary" id="dockBtnPrev" onclick="switchPlastikTab(1)" style="display: none; border-radius: 8px; font-weight: 700;">
                &larr; Tahap 1
            </button>
            <button type="button" class="btn btn-primary" id="dockBtnNext" onclick="switchPlastikTab(2)" style="background: #0d9488; border-color: #0d9488; border-radius: 8px; font-weight: 800; margin-left: auto;">
                <span id="dockBtnNextText">Lanjut: Mutu Plastik</span>
                <span id="dockBtnNextIcon">&rarr;</span>
            </button>
        </div>
    </form>
</div>

{{-- SUBMIT OVERLAY LOADING --}}
<div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); color: #ffffff;">
    <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center; max-width: 320px; width: 90%;">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem; animation: pulse 1.5s infinite;">🛍️</div>
        <h3 id="overlayTitle" style="font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem;">Menyimpan QC Plastik...</h3>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Memvalidasi parameter mutu &amp; memperbarui sistem.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            poDetailsUrl: "{{ url('gudang/qc/inbound/po') }}"
        };
    </script>
    <script src="{{ asset('js/gudang/qc/plastik/plastik.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/plastik/plastik-create.js') }}"></script>
@endpush
