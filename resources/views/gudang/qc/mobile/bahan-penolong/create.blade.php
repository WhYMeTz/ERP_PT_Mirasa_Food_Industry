@extends('layouts.qc-mobile')

@section('title', 'Sampling Mutu Bahan Penolong - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/bahan-penolong/bahan-penolong.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (str_contains($nm, 'MSG') || str_contains($nm, 'MONOSODIUM') || str_contains($nm, 'PENYEDAP') || str_starts_with($cd, 'BP-MS')) {
            return 'MSG';
        }
        if (str_contains($nm, 'GARAM') || str_contains($nm, 'SALT') || str_starts_with($cd, 'BP-GR')) {
            return 'GARAM';
        }
        if (str_contains($nm, 'PERENYAH') || str_starts_with($cd, 'BP-PR')) {
            return 'PERENYAH';
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

    $curKomoditas = strtoupper($initialKomoditas ?? request('kategori_barang', 'MSG'));
    if (!in_array($curKomoditas, ['MSG', 'GARAM', 'PERENYAH'])) {
        $curKomoditas = 'MSG';
    }

    $docNumbers = [
        'MSG'      => 'MFI/HACCP-04/FRM-03/032/VIII/2021',
        'GARAM'    => 'MFI/HACCP-04/FRM-03/033/VIII/2021',
        'PERENYAH' => 'MFI/HACCP-04/FRM-03/063/IX/2023',
    ];
    $docNo = $docNumbers[$curKomoditas] ?? 'MFI/HACCP-04/FRM-03/032/VIII/2021';

    // Filter PO khusus Bahan Penolong
    $bpPos = $pos->filter(function($p) use ($getPoCommodities) {
        $cats = $getPoCommodities($p);
        return count(array_intersect(['MSG', 'GARAM', 'PERENYAH'], $cats)) > 0;
    });
    if ($bpPos->isEmpty()) {
        $bpPos = $pos;
    }

    // Filter Supplier khusus Bahan Penolong (Non-Petani)
    $bpSuppliers = $suppliers->filter(function($s) {
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($bpSuppliers->isEmpty()) {
        $bpSuppliers = $suppliers;
    }
@endphp
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan Khusus Bahan Penolong --}}
    <div style="background: linear-gradient(135deg, #7c3aed 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.25rem 1.5rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 QC LAPANGAN &bull; INBOUND
                    </span>
                    <span id="badgeDocNo" style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        No. Dok: {{ $docNo }}
                    </span>
                </div>
                <h1 style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    🧂 Sampling Mutu Bahan Penolong ({{ $curKomoditas }})
                </h1>
                <p style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Pemeriksaan Fisik Kering/Gumpal, Kemasan Zak, CoA &amp; Higienitas (Formulir HACCP MFI)
                </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; font-weight: 700;" title="Kembali ke Web ERP Desktop">
                        🖥️ Ke Web ERP
                    </a>
                @endif
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => $curKomoditas]) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #7c3aed; border-radius: 8px; font-weight: 700;">
                    📋 Riwayat {{ $curKomoditas }}
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
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PLASTIK']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🛍️ Plastik
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'KARTON']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                📦 Karton
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'MSG']) }}" class="btn komoditas-btn {{ $curKomoditas === 'MSG' ? 'active-komoditas' : '' }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid {{ $curKomoditas === 'MSG' ? '#7c3aed' : '#cbd5e1' }}; background: {{ $curKomoditas === 'MSG' ? '#7c3aed' : '#ffffff' }}; color: {{ $curKomoditas === 'MSG' ? '#ffffff' : '#334155' }}; text-align: center; text-decoration: none;">
                🧂 MSG
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'GARAM']) }}" class="btn komoditas-btn {{ $curKomoditas === 'GARAM' ? 'active-komoditas' : '' }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid {{ $curKomoditas === 'GARAM' ? '#7c3aed' : '#cbd5e1' }}; background: {{ $curKomoditas === 'GARAM' ? '#7c3aed' : '#ffffff' }}; color: {{ $curKomoditas === 'GARAM' ? '#ffffff' : '#334155' }}; text-align: center; text-decoration: none;">
                🧂 Garam
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PERENYAH']) }}" class="btn komoditas-btn {{ $curKomoditas === 'PERENYAH' ? 'active-komoditas' : '' }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid {{ $curKomoditas === 'PERENYAH' ? '#7c3aed' : '#cbd5e1' }}; background: {{ $curKomoditas === 'PERENYAH' ? '#7c3aed' : '#ffffff' }}; color: {{ $curKomoditas === 'PERENYAH' ? '#ffffff' : '#334155' }}; text-align: center; text-decoration: none;">
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

    {{-- STEP / TAB SWITCHER KHUSUS BAHAN PENOLONG --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchBpTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #7c3aed; background: #7c3aed; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Dokumen &amp; Armada
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchBpTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🧂 2. Mutu Bahan &amp; Fisik
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcBpForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="form_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="{{ $curKomoditas }}">
        <input type="hidden" name="status_uji_goreng" value="SELESAI">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" class="card" style="border-radius: 12px; border-top: 5px solid #7c3aed; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 1: Laporan Kedatangan Bahan Penolong</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Verifikasi surat jalan, produsen &amp; audit halal transportasi</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="switchBpTab(2)" style="background: #7c3aed; border-color: #7c3aed; border-radius: 8px; font-weight: 700; padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.35rem; flex-shrink: 0;" title="Lanjut ke Pemeriksaan Mutu Bahan (Tahap 2)">
                    <span>Lanjut: Mutu Bahan</span> &rarr;
                </button>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.1rem;">
                {{-- PILIH DARI PO AKTIF DENGAN SMART FILTER --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                        <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                            📄 Referensi Purchase Order (PO) <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">(Opsional - auto-fill data)</span>
                        </label>
                        <span id="poFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #7c3aed; background: #ede9fe; padding: 2px 7px; border-radius: 6px;">
                            Filter: 🧂 Bahan Penolong
                        </span>
                    </div>

                    {{-- FILTER CHIPS & QUICK SEARCH UNTUK PO --}}
                    <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                        <div class="supplier-filter-chips" id="poFilterChips">
                            <button type="button" class="btn-supplier-chip active-chip" id="chipPo_BP" onclick="setPoCategoryFilter('BP')">
                                🧂 Khusus PO Bahan Penolong
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_ALL" onclick="setPoCategoryFilter('ALL')">
                                🌐 Semua PO ({{ $pos->count() }})
                            </button>
                        </div>
                        <div class="supplier-search-wrap">
                            <input type="text" id="poSearchInput" class="form-control supplier-search-input" placeholder="🔍 Cari nomor PO atau nama produsen..." oninput="onSearchPo(this.value)">
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
                                {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} ({{ $itemNames ?: 'Item Penolong' }} - Sisa: {{ number_format($totalSisa, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SUPPLIER & GUDANG TUJUAN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                            <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                                🏭 Vendor / Supplier Bahan <span style="color:red;">*</span>
                            </label>
                            <span id="supplierFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #7c3aed; background: #ede9fe; padding: 2px 7px; border-radius: 6px;">
                                Filter: 🧂 Supplier Bahan
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                            <div class="supplier-filter-chips">
                                <button type="button" class="btn-supplier-chip active-chip" id="chipSupplier_VENDOR" onclick="setSupplierCategoryFilter('VENDOR')">
                                    🧂 Vendor Bahan
                                </button>
                                <button type="button" class="btn-supplier-chip" id="chipSupplier_ALL" onclick="setSupplierCategoryFilter('ALL')">
                                    🌐 Semua Mitra ({{ $suppliers->count() }})
                                </button>
                            </div>
                            <div class="supplier-search-wrap">
                                <input type="text" id="supplierSearchInput" class="form-control supplier-search-input" placeholder="🔍 Ketik nama supplier bahan..." oninput="onSearchSupplier(this.value)">
                                <button type="button" id="btnClearSupplierSearch" class="supplier-search-clear" onclick="clearSupplierSearch()" style="display: none;">✕</button>
                            </div>
                        </div>

                        <select name="supplier_id" id="supplierSelect" class="form-control" required style="font-weight: 700;">
                            <option value="">-- Pilih Vendor Bahan Penolong --</option>
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
                        <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="Contoh: SJ-BP-4432" value="{{ old('surat_jalan_supplier') }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nomor Delivery Order (DO)</label>
                        <input type="text" name="nomor_do" class="form-control" placeholder="Contoh: DO-2026/099" value="{{ old('nomor_do') }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nama Pabrik / Produsen</label>
                        <input type="text" name="nama_produsen" id="inputNamaProdusen" class="form-control" placeholder="Contoh: PT Ajinomoto / Garam Beryodium" value="{{ old('nama_produsen') }}">
                    </div>
                </div>

                {{-- KUANTITAS SJ, PABRIK & SAMPLE --}}
                <div style="background: #faf5ff; border: 1.5px solid #e9d5ff; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #6b21a8; margin-bottom: 0.6rem;">
                        ⚖️ KUANTITAS PENGIRIMAN &amp; CONTOH SAMPLE
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.85rem;">
                        <div>
                            <label class="form-label" style="font-weight: 700;">Kuantitas di SJ (KG / Zak) <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="Contoh: 1000.00" value="{{ old('jumlah_surat_jalan') }}" required>
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700;">Jumlah Dihitung Pabrik (KG)</label>
                            <input type="number" step="0.01" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="0.00" value="{{ old('jumlah_di_pabrik') }}">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700;">Jumlah Sample Diperiksa (KG)</label>
                            <input type="number" step="0.01" min="0.01" name="jumlah_sample_kg" class="form-control" placeholder="1.00" value="{{ old('jumlah_sample_kg', 1) }}">
                        </div>
                    </div>
                </div>

                {{-- ARMADA TRANSPORTASI & KELAYAKAN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Plat Nomor Truk / Box</label>
                        <input type="text" name="plat_nomor_truk" class="form-control" placeholder="Contoh: B 9911 UTT" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nama Sopir / Pengemudi</label>
                        <input type="text" name="sopir_nama" class="form-control" placeholder="Nama sopir..." value="{{ old('sopir_nama') }}">
                    </div>
                </div>

                {{-- AUDIT HIGIENITAS ARMADA & HALAL --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
                        📋 AUDIT HIGIENITAS &amp; STATUS HALAL TRANSPORTASI (Standar HACCP MFI):
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', '1') ? 'checked' : '' }}>
                            Bebas Bau &amp; Cemaran Kimia / Basah
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="angkut_barang_haram_st" value="1" {{ old('angkut_barang_haram_st') ? 'checked' : '' }}>
                            Mengangkut Barang Najis / Non-Halal
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="terdaftar_lppom_st" value="1" {{ old('terdaftar_lppom_st', '1') ? 'checked' : '' }}>
                            Bahan Terdaftar LPPOM MUI / Memiliki Sertifikat Halal
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="ada_sertifikat_halal_st" value="1" {{ old('ada_sertifikat_halal_st', '1') ? 'checked' : '' }}>
                            Sertifikat Halal &amp; Dokumen CoA Asli Disertakan
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN KUALITAS BAHAN PENOLONG (FORM HACCP RESMI)             --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #7c3aed; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 2: Pengujian Mutu Fisik Bahan Penolong</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Analisa kondisi isi, kekeringan, kemasan zak &amp; toleransi reject</span>
                </div>
                <div style="display: flex; gap: 0.4rem;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="switchBpTab(1)" style="border-radius: 8px; font-weight: 700;">
                        &larr; Kembali: Tahap 1
                    </button>
                    <button type="button" class="btn btn-sm" id="btnTopSimpanCepat" onclick="submitBpForm()" style="background: #059669; color: #ffffff; border-radius: 8px; font-weight: 800;">
                        💾 Simpan QC
                    </button>
                </div>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- PILIH ITEM BARANG & SPESIFIKASI --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; color: #0f172a;">Item Bahan Penolong <span style="color:red;">*</span></label>
                        <select name="bp_barang_id" id="bpBarangSelect" class="form-control" style="font-weight: 700;" onchange="onBpBarangChanged(this)" required>
                            @foreach ($barangs as $b)
                                @php
                                    $nm = strtoupper($b->barang_nm);
                                    $cd = strtoupper($b->barang_cd);
                                    $isBp = str_contains($nm, 'MSG') || str_contains($nm, 'GARAM') || str_contains($nm, 'PERENYAH') || str_contains($nm, 'BUMBU') || str_contains($nm, 'SALT') || str_starts_with($cd, 'BP-');
                                @endphp
                                @if ($isBp)
                                    <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}">
                                        {{ $b->barang_cd }} &bull; {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'KG' }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; color: #0f172a;">Deskripsi / Merk Bahan</label>
                        <input type="text" name="nama_jenis" id="bpNamaJenisInput" class="form-control" placeholder="Contoh: Garam Beryodium Halus / MSG Plus" value="{{ old('nama_jenis', $curKomoditas) }}">
                    </div>
                </div>

                {{-- ISI RAW MATERIAL & KEMASAN STATUS --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="bp_status_raw_material" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="bp_status_raw_material" value="TDK_STD"> TDK STD
                            </label>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KEMASAN :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="bp_kemasan_kondisi" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="bp_kemasan_kondisi" value="TIDAK_STANDARD"> TIDAK STANDARD
                            </label>
                        </div>
                    </div>
                </div>

                {{-- KONDISI FISIK BAHAN & KEMASAN ZAK --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
                        <div>
                            <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.6rem;">
                                🧪 KONDISI FISIK BAHAN:
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #15803d; font-weight: 700;">
                                    <input type="checkbox" name="bp_isi_kering" value="1" checked> KERING
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #0284c7; font-weight: 600;">
                                    <input type="checkbox" name="bp_isi_basah" value="1"> BASAH
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #b45309; font-weight: 600;">
                                    <input type="checkbox" name="bp_isi_gumpal" value="1"> GUMPAL
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #475569; font-weight: 600;">
                                    <input type="checkbox" name="bp_isi_berminyak" value="1"> BERMINYAK
                                </label>
                            </div>
                        </div>

                        <div>
                            <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.6rem;">
                                📦 KONDISI KEMASAN / ZAK:
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer;">
                                    <input type="checkbox" name="bp_kemasan_kotor" value="1"> KOTOR
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer;">
                                    <input type="checkbox" name="bp_kemasan_apek" value="1"> APEK
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #b45309; font-weight: 700;">
                                    <input type="checkbox" name="bp_kemasan_jamur" value="1"> JAMUR
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                                    <input type="checkbox" name="bp_kemasan_sobek" value="1"> SOBEK
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KUANTITAS LOLOS & REJECT --}}
                <div style="background: #faf5ff; border: 1.5px solid #e9d5ff; border-radius: 10px; padding: 1.15rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #6b21a8;">⚖️ HASIL PEMERIKSAAN KUANTITAS</span>
                        <span id="labelBpNetto" style="font-size: 1rem; font-weight: 900; color: #7c3aed; background: #ffffff; padding: 2px 10px; border-radius: 6px; border: 1px solid #e9d5ff;">
                            0.00 KG
                        </span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.85rem;">
                        <div>
                            <label class="form-label" style="font-weight: 800; color: #0f172a;">Jumlah Lolos / Netto (KG) <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" min="0" name="bp_qty_gross" id="bpQtyGross" class="form-control" placeholder="0.00" value="{{ old('bp_qty_gross') }}" required>
                        </div>
                        <div>
                            <label class="form-label" style="color: #dc2626; font-weight: 800;">Qty Reject / Rusak (KG)</label>
                            <input type="number" step="0.01" min="0" name="bp_qty_reject" id="bpQtyReject" class="form-control" placeholder="0.00" value="{{ old('bp_qty_reject', 0) }}">
                        </div>
                    </div>
                </div>

                {{-- KOMENTAR & KESIMPULAN --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
                        <textarea name="bp_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji mutu bahan penolong...">{{ old('bp_komentar') }}</textarea>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                        <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
                        <div style="display: flex; gap: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                                <input type="radio" name="bp_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="bp_kesimpulan" value="TOLAK"> ❌ TOLAK
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM DOCK NAVIGATION --}}
        <div class="bottom-dock-nav">
            <button type="button" class="btn btn-secondary" id="dockBtnPrev" onclick="switchBpTab(1)" style="display: none; border-radius: 8px; font-weight: 700;">
                &larr; Tahap 1
            </button>
            <button type="button" class="btn btn-primary" id="dockBtnNext" onclick="switchBpTab(2)" style="background: #7c3aed; border-color: #7c3aed; border-radius: 8px; font-weight: 800; margin-left: auto;">
                <span id="dockBtnNextText">Lanjut: Mutu Bahan</span>
                <span id="dockBtnNextIcon">&rarr;</span>
            </button>
        </div>
    </form>
</div>

{{-- SUBMIT OVERLAY LOADING --}}
<div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); color: #ffffff;">
    <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center; max-width: 320px; width: 90%;">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem; animation: pulse 1.5s infinite;">🧂</div>
        <h3 id="overlayTitle" style="font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem;">Menyimpan QC Bahan Penolong...</h3>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Memvalidasi mutu &amp; memperbarui sistem.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            poDetailsUrl: "{{ url('gudang/qc/inbound/po') }}"
        };
    </script>
    <script src="{{ asset('js/gudang/qc/bahan-penolong/bahan-penolong.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/bahan-penolong/bahan-penolong-create.js') }}"></script>
@endpush
