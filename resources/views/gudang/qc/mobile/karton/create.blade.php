@extends('layouts.qc-mobile')

@section('title', 'Sampling Mutu Karton Box - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/karton/karton.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($d) {
        $barang = $d?->barang;
        if (!$barang) return 'LAINNYA';

        $nm = strtoupper($barang->barang_nm ?? '');
        $cd = strtoupper($barang->barang_cd ?? '');
        $jnsCd = strtoupper($barang->jenisBarang?->jenis_barang_cd ?? '');

        // Abaikan komoditas non-karton
        if (
            str_contains($nm, 'MINYAK') || str_contains($nm, 'SINGKONG') || str_contains($nm, 'PUYUR') ||
            str_contains($nm, 'OPAK') || str_contains($nm, 'UBI') || str_contains($nm, 'BUMBU') ||
            str_contains($nm, 'MSG') || str_contains($nm, 'GARAM') || str_contains($nm, 'PERENYAH') ||
            str_contains($nm, 'PLASTIK') || str_contains($nm, 'KEMASAN') || str_contains($nm, 'OPP') ||
            str_contains($nm, 'LAKBAN') || str_contains($nm, 'RAFIA') || str_contains($nm, 'SARUNG TANGAN')
        ) {
            return 'LAINNYA';
        }

        // Kategori Karton Box
        if (
            str_contains($nm, 'KARTON') || 
            str_contains($nm, 'BOX') || 
            str_contains($nm, 'DUS') || 
            str_starts_with($cd, 'BP-KT') || 
            str_starts_with($cd, 'BP-BX') ||
            $jnsCd === 'BOX'
        ) {
            return 'KARTON';
        }

        return 'LAINNYA';
    };

    $getPoCommodities = function ($p) use ($classifyBarang) {
        $cats = [];
        foreach ($p->details as $d) {
            $cat = $classifyBarang($d);
            $cats[] = $cat;
        }
        return array_values(array_unique($cats));
    };

    // Filter PO KHUSUS Karton Box Saja (Hanya PO yang memuat komoditas KARTON)
    $kartonPos = $pos->filter(function($p) use ($getPoCommodities) {
        $cats = $getPoCommodities($p);
        return in_array('KARTON', $cats);
    });

    // Mapping Data PO Lengkap ke Format JSON untuk Auto-Fill Instan Tanpa Delay
    $poListData = $kartonPos->mapWithKeys(function ($p) use ($getPoCommodities, $classifyBarang) {
        return [
            $p->po_id => [
                'po_id'       => $p->po_id,
                'po_no'       => $p->po_no,
                'supplier_id' => $p->supplier_id,
                'supplier_nm' => $p->supplier?->supplier_nm,
                'gudang_id'   => $p->gudang_id,
                'komoditas'   => $getPoCommodities($p),
                'items'       => $p->details->filter(function ($d) use ($classifyBarang) {
                    return $classifyBarang($d) === 'KARTON';
                })->map(function ($d) {
                    return [
                        'podtl_id'  => $d->podtl_id,
                        'barang_id' => $d->barang_id,
                        'barang_nm' => $d->barang?->barang_nm,
                        'satuan'    => $d->barang?->satuanDasar?->satuan_nm ?? ($d->barang?->satuanDasar?->satuan_cd ?? 'PCS'),
                        'satuan_cd' => strtoupper($d->barang?->satuanDasar?->satuan_cd ?? 'PCS'),
                        'pesan_qty' => (float) $d->pesan_qty,
                        'terima_qty'=> (float) $d->terima_qty,
                        'sisa_qty'  => max(0, (float) $d->pesan_qty - (float) $d->terima_qty),
                    ];
                })->values(),
            ]
        ];
    });

    // Filter Supplier khusus Karton (Vendor Box / Non-Petani)
    $kartonSuppliers = $suppliers->filter(function($s) use ($selectedPo, $kartonPos) {
        if ($selectedPo && $selectedPo->supplier_id == $s->supplier_id) return true;
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($kartonSuppliers->isEmpty()) {
        $kartonSuppliers = $suppliers;
    }
@endphp
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan Khusus Karton Box (Aksen Warm Amber) --}}
    <div style="background: linear-gradient(135deg, #d97706 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.25rem 1.5rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 QC LAPANGAN &bull; INBOUND
                    </span>
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        No. Dok: MFI/HACCP-04/FRM-03/031/VIII/2021
                    </span>
                </div>
                <h1 style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    📦 Sampling Mutu Karton Box
                </h1>
                <p style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Pemeriksaan Dimensi P x L x T, Gramatur, Cacat Kemasan &amp; Kebersihan (Formulir HACCP MFI)
                </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; font-weight: 700;" title="Kembali ke Web ERP Desktop">
                        🖥️ Ke Web ERP
                    </a>
                @endif
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'KARTON']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #d97706; border-radius: 8px; font-weight: 700;">
                    📋 Riwayat Karton
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
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'KARTON']) }}" class="btn komoditas-btn active-komoditas" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid #d97706; background: #d97706; color: #ffffff; text-align: center; text-decoration: none;">
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
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'BUMBU']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🌶️ Bumbu
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

    {{-- STEP / TAB SWITCHER KHUSUS KARTON --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchKartonTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #d97706; background: #d97706; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Dokumen &amp; Armada
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchKartonTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            📦 2. Mutu Karton Box
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcKartonForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="form_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="KARTON">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" style="display: flex; flex-direction: column; gap: 1rem;">
            {{-- WIDGET WAKTU INSPEKSI LAPANGAN --}}
            <div class="karton-inspection-bar">
                <div class="inspection-bar-left">
                    <div class="inspection-bar-icon">📅</div>
                    <div>
                        <div class="inspection-bar-label">Waktu Inspeksi Lapangan</div>
                        <div class="inspection-bar-val" id="textInspectionBarTime">{{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') }} WIB</div>
                    </div>
                </div>
                <span class="inspection-bar-badge">QC-RM-031</span>
            </div>

            {{-- CARD 1: INFORMASI BAHAN & PENGIRIMAN --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="card-step-badge amber">1</span>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.25;">Informasi Bahan &amp; Pengiriman</h2>
                        </div>
                    </div>
                    <span class="card-step-tag">DO / SURAT JALAN</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    {{-- 1. REFERENSI PO & GUDANG BONGKAR (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Referensi PO Karton Box <span style="font-size: 0.72rem; color: #d97706; font-weight: normal;">(Opsional)</span></label>
                            </div>
                            <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 700;">
                                @if ($kartonPos->isEmpty())
                                    <option value="">-- Tidak Ada PO Karton Aktif / Kiriman Langsung --</option>
                                @else
                                    <option value="">-- Tanpa PO / Kiriman Langsung Karton --</option>
                                    @foreach ($kartonPos as $p)
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
                                            {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} ({{ $itemNames ?: 'Item Box' }} - Sisa: {{ number_format($totalSisa, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                @endif
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

                    {{-- 2. NO. DO (SURAT JALAN) & TANGGAL DATANG (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">No. DO / Surat Jalan</label>
                            </div>
                            <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="Contoh: SJ-BX-1029" value="{{ old('surat_jalan_supplier') }}" style="font-weight: 700;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Tanggal Datang</label>
                                <button type="button" onclick="setCurrentDateTime()" style="background: none; border: none; color: #d97706; font-size: 0.72rem; font-weight: 800; cursor: pointer; padding: 0;">🕒 Sekarang</button>
                            </div>
                            <input type="datetime-local" name="tgl_periksa" id="inputTglDatang" class="form-control" value="{{ old('tgl_periksa', now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" style="font-weight: 700;">
                        </div>
                    </div>

                    {{-- 3. MITRA VENDOR & NAMA PRODUSEN (SUBCARD KELOMPOK) --}}
                    <div class="karton-subcard">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                    Mitra Vendor / Supplier Karton Box <span style="color:#ef4444;">*</span>
                                </label>
                            </div>
                            <select name="supplier_id" id="supplierSelect" class="form-control" required onchange="onSupplierSelected(this)" style="font-weight: 700; font-size: 0.85rem;">
                                <option value="">-- Pilih Mitra Vendor Karton --</option>
                                @foreach ($kartonSuppliers as $s)
                                    <option value="{{ $s->supplier_id }}"
                                            data-supplier-cd="{{ $s->supplier_cd }}"
                                            data-supplier-nm="{{ $s->supplier_nm }}"
                                            {{ old('supplier_id', $selectedPo?->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                                        {{ $s->supplier_nm }} ({{ $s->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SELECTOR ITEM BARANG DARI PO (JIKA PO MEMILIKI MULTI-ITEM) --}}
                        <input type="hidden" name="podtl_id" id="inputSelectedPodtlId" value="{{ old('podtl_id') }}">
                        <div id="boxPoItemsSelector" style="display: none; background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 0.85rem 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.25rem;">
                                <label style="font-size: 0.8rem; font-weight: 800; color: #92400e; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>📦</span> <span>Item Barang dalam PO Ini (<span id="countPoItemsBadge">0</span> Item):</span>
                                </label>
                                <span style="font-size: 0.7rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 2px 7px; border-radius: 6px;">
                                    1 Dokumen QC per Item
                                </span>
                            </div>
                            <p style="font-size: 0.725rem; color: #78350f; margin: 0 0 0.65rem 0; line-height: 1.35;">
                                Silakan pilih item yang sedang dibongkar/diperiksa. Item lainnya dapat dibuatkan dokumen QC tersendiri setelah ini.
                            </p>
                            <div id="poItemsCardsList" class="po-items-list-grid">
                                {{-- Di-render secara dinamis oleh JS saat PO dipilih --}}
                            </div>
                        </div>

                        {{-- IDENTITAS BAHAN BAKU & NAMA JENIS SPESIFIKASI KARTON BOX --}}
                        <div class="qc-grid-2col">
                            {{-- NAMA BAHAN BAKU (MASTER BARANG KARTON BOX) --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                        Nama Bahan Baku Box <span style="color:#ef4444;">*</span>
                                    </label>
                                </div>
                                <select name="karton_barang_id" id="kartonBarangSelect" class="form-control" style="font-weight: 700;" onchange="onKartonBarangChanged(this)" required>
                                    @foreach ($barangs as $b)
                                        @if (stripos($b->barang_nm, 'karton') !== false || stripos($b->barang_nm, 'box') !== false || stripos($b->barang_nm, 'dus') !== false || stripos($b->barang_cd, 'BP-KT') !== false || stripos($b->barang_cd, 'BP-BX') !== false)
                                            <option value="{{ $b->barang_id }}"
                                                    data-nama="{{ $b->barang_nm }}"
                                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? 'PCS' }}"
                                                    data-satuan-cd="{{ strtoupper($b->satuanDasar?->satuan_cd ?? 'PCS') }}"
                                                    {{ $loop->first ? 'selected' : '' }}>
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) &bull; Satuan: {{ $b->satuanDasar?->satuan_nm ?? 'PCS' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- NAMA JENIS (SPESIFIKASI / VARIAN KARTON BOX) --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label" style="font-weight: 800; color: #92400e;">
                                        Nama Jenis (Spesifikasi Box) <span style="font-size: 0.72rem; color: #d97706; font-weight: normal;">(Bisa diedit/ketik)</span>
                                    </label>
                                </div>
                                <input type="text" name="nama_jenis" id="kartonNamaJenisInput" list="listKartonJenis" class="form-control" placeholder="Contoh: Karton Box Mirasa 250g (K150/M125)" value="{{ old('nama_jenis', 'Karton Box Mirasa 250g (K150/M125)') }}" style="font-weight: 700;">
                                <datalist id="listKartonJenis">
                                    <option value="Karton Box Mirasa 250g (K150/M125)">
                                    <option value="Karton Box Mirasa 500g (K150/M125/K150)">
                                    <option value="Karton Box Curah Cap Payung">
                                    <option value="Karton Box Single Wall 3 Ply">
                                    <option value="Karton Box Double Wall 5 Ply">
                                    <option value="Karton Box Corrugated C-Flute">
                                </datalist>
                            </div>
                        </div>

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nama Produsen <span style="font-size: 0.72rem; color: #d97706; font-weight: normal;">(Bisa diketik)</span></label>
                                </div>
                                <input type="text" name="nama_produsen" id="namaProdusenInput" class="form-control" placeholder="Contoh: PT Fajar Surya Box" value="{{ old('nama_produsen') }}" style="font-weight: 700;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nomor Delivery Order (DO)</label>
                                </div>
                                <input type="text" name="nomor_do" class="form-control" placeholder="Contoh: DO-BOX-2026/04" value="{{ old('nomor_do') }}" style="font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    {{-- 4. PLAT TRUK / KENDARAAN & NAMA SOPIR (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Plat Truk / Mobil Box</label>
                            </div>
                            <input type="text" name="plat_nomor_truk" class="form-control" placeholder="AD 8234 QP" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase; font-weight: 800;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Nama Sopir</label>
                            </div>
                            <input type="text" name="sopir_nama" class="form-control" placeholder="Nama pengemudi" value="{{ old('sopir_nama') }}">
                        </div>
                    </div>

                    {{-- 5. SUBCARD: KUANTITAS & VERIFIKASI PENGIRIMAN (AMBER THEME - DENGAN PILIHAN PCS & DUS) --}}
                    <div class="karton-subcard amber-accent">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                            <span class="karton-subcard-title" style="margin-bottom: 0;">
                                <span>⚖️</span> <span>KUANTITAS &amp; VERIFIKASI PENGIRIMAN</span>
                            </span>
                            <div class="satuan-toggle-pills" id="satuanToggleGroupKarton">
                                <button type="button" class="btn-satuan-pill active" id="pillSatuan_PCS" onclick="setKartonSatuan('PCS')">
                                    <span class="pill-icon">📦</span>
                                    <span class="pill-text">PCS</span>
                                </button>
                                <button type="button" class="btn-satuan-pill" id="pillSatuan_DUS" onclick="setKartonSatuan('DUS')">
                                    <span class="pill-icon">🗃️</span>
                                    <span class="pill-text">DUS</span>
                                </button>
                            </div>
                            <input type="hidden" name="satuan" id="inputKartonSatuan" value="{{ old('satuan', 'PCS') }}">
                        </div>

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah Surat Jalan (<span class="label-satuan-karton">PCS</span>) <span style="color:#ef4444;">*</span></label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="1" min="0.01" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="10000" value="{{ old('jumlah_surat_jalan') }}" style="font-weight: 800;" required>
                                    <span class="input-suffix-text suffix-satuan-karton">PCS</span>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah di Pabrik (<span class="label-satuan-karton">PCS</span>)</label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="1" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="10000" value="{{ old('jumlah_di_pabrik') }}" style="font-weight: 800;">
                                    <span class="input-suffix-text suffix-satuan-karton">PCS</span>
                                </div>
                            </div>
                        </div>

                        {{-- Sampel QC Diambil & Netto Real-Time --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.25rem; border-top: 1px dashed #fde68a; padding-top: 0.55rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.8rem; font-weight: 800; color: #0f172a;">Sampel QC Diperiksa:</span>
                                <div class="input-suffix-wrap" style="max-width: 130px;">
                                    <input type="number" step="1" min="1" name="jumlah_sample_pcs" id="inputJumlahSamplePcs" class="form-control" placeholder="10" value="{{ old('jumlah_sample_pcs', 10) }}" style="text-align: right; font-weight: 900; color: #d97706; height: 38px !important; min-height: 38px !important;">
                                    <span class="input-suffix-text">PCS</span>
                                </div>
                            </div>
                            <span id="labelSubcardNetto" style="font-size: 0.775rem; font-weight: 800; color: #b45309; background: #ffffff; padding: 3px 8px; border-radius: 6px; border: 1px solid #fde68a;">
                                Netto Lolos: 0 Pcs
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: STANDAR TRANSPORTASI & JAMINAN HALAL --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #fde68a; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="card-step-badge amber" style="width: 28px; height: 28px; font-size: 0.85rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">2</span>
                        <div>
                            <h2 style="font-size: 1.1rem; font-weight: 800; color: #92400e; margin: 0; line-height: 1.25;">Standar Transportasi &amp; Jaminan Halal</h2>
                            <span style="font-size: 0.75rem; color: #b45309; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; Kebersihan Armada Pengangkut</span>
                        </div>
                    </div>
                    <span style="font-size: 1.35rem;">🛡️</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    {{-- ITEM 1: KONDISI TRANSPORTASI (TRUK / BOX KARTON) --}}
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin-bottom: 0.55rem;">
                            1. Kondisi Transportasi (Truk Pengangkut Karton Box) <span style="color:#ef4444;">*</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                            <label class="transport-radio-card active" id="cardTransportClean">
                                <input type="radio" name="bebas_cemaran_st" value="1" checked style="display:none;" onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #15803d;">✔ Bersih &amp; Kering</div>
                                    <div class="transport-card-desc">Bebas dari bau menyengat, air, lumpur, oli &amp; cemaran kimia berbahaya</div>
                                </div>
                            </label>
                            <label class="transport-radio-card" id="cardTransportDirty">
                                <input type="radio" name="bebas_cemaran_st" value="0" style="display:none;" onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #dc2626;">✖ Tidak Bersih / Tercemar</div>
                                    <div class="transport-card-desc">Truk basah, berbau tajam, berdebu tebal, atau berceceran minyak/oli</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 2: 4 PERTANYAAN JAMINAN HALAL STANDAR HACCP MFI --}}
                    {{-- 1. Mengangkut barang haram / najis --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">2. Apakah armada pengangkut bebas dari mengangkut barang najis / non-halal?</div>
                                <div class="halal-qa-sub">Armada tidak pernah digunakan untuk memuat babi, bangkai, atau bahan turunan non-halal</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_angkut_haram_no" onclick="setHalalToggle('angkut_barang_haram', 0)">
                                    <span>✔</span> <span>Tidak</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_angkut_haram_yes" onclick="setHalalToggle('angkut_barang_haram', 1)">
                                    <span>✖</span> <span>Ya (Tercemar)</span>
                                </button>
                            </div>
                            <input type="hidden" name="angkut_barang_haram_st" id="input_angkut_barang_haram" value="{{ old('angkut_barang_haram_st', 0) }}">
                        </div>
                        <input type="text" name="komentar_transportasi" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_transportasi') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- 2. Logo Halal & Terdaftar LPPOM --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">3. Apakah karton box terdaftar LPPOM / sertifikasi jaminan mutu?</div>
                                <div class="halal-qa-sub">Karton box bersertifikat atau memenuhi standar jaminan kemasan pangan halal</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_lppom_yes" onclick="setHalalToggle('terdaftar_lppom', 1)">
                                    <span>✔</span> <span>Ya</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_lppom_no" onclick="setHalalToggle('terdaftar_lppom', 0)">
                                    <span>✖</span> <span>Tidak</span>
                                </button>
                            </div>
                            <input type="hidden" name="terdaftar_lppom_st" id="input_terdaftar_lppom" value="{{ old('terdaftar_lppom_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_lppom" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_lppom') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- 3. Sertifikat Disertakan --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">4. Apakah spesifikasi mutu box / sertifikat jaminan disertakan?</div>
                                <div class="halal-qa-sub">Salinan Certificate of Analysis (CoA) atau sertifikasi disertakan dalam pengiriman</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_sertifikat_yes" onclick="setHalalToggle('ada_sertifikat_halal', 1)">
                                    <span>✔</span> <span>Ada</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_sertifikat_no" onclick="setHalalToggle('ada_sertifikat_halal', 0)">
                                    <span>✖</span> <span>Tidak</span>
                                </button>
                            </div>
                            <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat_halal" value="{{ old('ada_sertifikat_halal_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_sertifikat" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_sertifikat') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- 4. Masa Berlaku Sertifikat --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">5. Apakah masa berlaku dokumen / sertifikasi masih aktif?</div>
                                <div class="halal-qa-sub">Masa berlaku dokumen legalitas vendor belum kadaluarsa saat kedatangan</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_berlaku_yes" onclick="setHalalToggle('sertifikat_halal_berlaku', 1)">
                                    <span>✔</span> <span>Berlaku</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_berlaku_no" onclick="setHalalToggle('sertifikat_halal_berlaku', 0)">
                                    <span>✖</span> <span>Kadaluarsa</span>
                                </button>
                            </div>
                            <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_halal_berlaku" value="{{ old('sertifikat_halal_berlaku_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_berlaku" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_berlaku') }}" style="font-size: 0.85rem;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN KUALITAS KARTON BOX (FORM HACCP RESMI)                 --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border: 1.5px solid #fde68a; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="card-step-badge amber" style="width: 28px; height: 28px; font-size: 0.85rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">2</span>
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 800; color: #92400e; margin: 0; line-height: 1.25;">Tahap 2: Pengujian Mutu Karton Box</h2>
                        <span style="font-size: 0.75rem; color: #b45309; font-weight: 700;">Analisa dimensi P x L x T, gramatur, cacat kemasan &amp; toleransi reject</span>
                    </div>
                </div>
            </div>

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.15rem;">
                {{-- INFO STRIP ITEM & SPESIFIKASI JENIS --}}
                <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 0.65rem 0.95rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                        <span style="font-weight: 800; color: #92400e;">📦 Bahan Baku:</span>
                        <span id="tab2InfoBarang" style="font-weight: 700; color: #1e293b;">-</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                        <span style="font-weight: 800; color: #b45309;">🏷️ Nama Jenis:</span>
                        <span id="tab2InfoJenis" style="font-weight: 800; color: #b45309;">-</span>
                    </div>
                </div>

                {{-- SUBCARD 1: ISI RAW MATERIAL & KEMASAN STATUS (MODERN CHOICE BUTTONS) --}}
                <div class="karton-subcard">
                    <span class="karton-subcard-title">
                        <span>🔍</span> <span>2. ISI RAW MATERIAL &amp; WADAH KEMASAN</span>
                    </span>
                    <div class="qc-grid-2col">
                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.45rem;">2. ISI RAW MATERIAL :</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn active ok" id="btnRawOk" onclick="setRawMaterialStatus('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn" id="btnRawTdkStd" onclick="setRawMaterialStatus('TDK_STD')">
                                    <span>✖</span> <span>TDK STD</span>
                                </button>
                            </div>
                            <input type="hidden" name="karton_status_raw_material" id="inputRawMaterial" value="OK">
                        </div>

                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.45rem;">KEMASAN :</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn active ok" id="btnKemasanOk" onclick="setKemasanKondisi('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn" id="btnKemasanTdkStd" onclick="setKemasanKondisi('TIDAK_STANDARD')">
                                    <span>✖</span> <span>TIDAK STANDARD</span>
                                </button>
                            </div>
                            <input type="hidden" name="karton_kemasan_kondisi" id="inputKemasanKondisi" value="OK">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 2: CHECKLIST CACAT KEMASAN (MODERN CHECKLIST CARDS) --}}
                <div class="karton-subcard">
                    <span class="karton-subcard-title" style="color: #475569;">
                        <span>⚠️</span> <span>KONDISI KEMASAN (Ketuk jika ditemukan cacat):</span>
                    </span>
                    <div class="modern-checklist-grid">
                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatKotor')">
                            <div class="modern-check-label">
                                <span>🧼</span> <span>KOTOR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatKotor">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="karton_kemasan_kotor" id="checkCacatKotor" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatApek')">
                            <div class="modern-check-label">
                                <span>👃</span> <span>APEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatApek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="karton_kemasan_apek" id="checkCacatApek" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatBasah')">
                            <div class="modern-check-label">
                                <span>💧</span> <span>BASAH</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatBasah">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="karton_kemasan_basah" id="checkCacatBasah" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatJamur')">
                            <div class="modern-check-label">
                                <span>🍄</span> <span>JAMUR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatJamur">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="karton_kemasan_jamur" id="checkCacatJamur" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatSobek')">
                            <div class="modern-check-label">
                                <span>✂️</span> <span>SOBEK/PENYOK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatSobek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="karton_kemasan_sobek" id="checkCacatSobek" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatBerminyak')">
                            <div class="modern-check-label">
                                <span>🛢️</span> <span>BERMINYAK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatBerminyak">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="karton_kemasan_berminyak" id="checkCacatBerminyak" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatBerdebu')">
                            <div class="modern-check-label">
                                <span>💨</span> <span>BERDEBU</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatBerdebu">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="karton_kemasan_berdebu" id="checkCacatBerdebu" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 3: ANALISA PARAMETER DIMENSI P x L x T & SPESIFIKASI (SPACIOUS MOBILE CARDS) --}}
                <div class="karton-subcard">
                    <span class="karton-subcard-title" style="color: #b45309; margin-bottom: 0.75rem;">
                        <span>🔬</span> <span>ANALISA PARAMETER DIMENSI P x L x T &amp; SPESIFIKASI</span>
                    </span>

                    <div class="param-analysis-wrap">
                        {{-- 1. DIMENSI PANJANG --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>📏</span> PANJANG (cm)
                                </span>
                                <div class="param-std-badge">
                                    <span>Standar:</span>
                                    <input type="text" name="karton_panjang_standar" id="inputPanjangStandar" value="{{ old('karton_panjang_standar', '40 cm') }}" style="width: 75px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;" title="Standar Panjang">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '40 cm')">40 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '39.5 cm')">39.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '40.5 cm')">40.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputPanjangAnalisa', '41 cm')">41 cm</button>
                            </div>
                            <input type="text" name="karton_panjang_analisa" id="inputPanjangAnalisa" class="form-control param-input-spacious" placeholder="Ketik hasil analisa panjang (Contoh: 40 cm)..." value="{{ old('karton_panjang_analisa') }}">
                        </div>

                        {{-- 2. DIMENSI LEBAR --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>📐</span> LEBAR (cm)
                                </span>
                                <div class="param-std-badge">
                                    <span>Standar:</span>
                                    <input type="text" name="karton_lebar_standar" id="inputLebarStandar" value="{{ old('karton_lebar_standar', '30 cm') }}" style="width: 75px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;" title="Standar Lebar">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '30 cm')">30 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '29.5 cm')">29.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '30.5 cm')">30.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputLebarAnalisa', '31 cm')">31 cm</button>
                            </div>
                            <input type="text" name="karton_lebar_analisa" id="inputLebarAnalisa" class="form-control param-input-spacious" placeholder="Ketik hasil analisa lebar (Contoh: 30 cm)..." value="{{ old('karton_lebar_analisa') }}">
                        </div>

                        {{-- 3. DIMENSI TINGGI --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>📦</span> TINGGI (cm)
                                </span>
                                <div class="param-std-badge">
                                    <span>Standar:</span>
                                    <input type="text" name="karton_tinggi_standar" id="inputTinggiStandar" value="{{ old('karton_tinggi_standar', '25 cm') }}" style="width: 75px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;" title="Standar Tinggi">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '25 cm')">25 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '24.5 cm')">24.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '25.5 cm')">25.5 cm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputTinggiAnalisa', '26 cm')">26 cm</button>
                            </div>
                            <input type="text" name="karton_tinggi_analisa" id="inputTinggiAnalisa" class="form-control param-input-spacious" placeholder="Ketik hasil analisa tinggi (Contoh: 25 cm)..." value="{{ old('karton_tinggi_analisa') }}">
                        </div>

                        {{-- 4. SPESIFIKASI / GRAMATUR --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>⚖️</span> SPESIFIKASI / GRAMATUR
                                </span>
                                <div class="param-std-badge">
                                    <span>Standar:</span>
                                    <input type="text" name="karton_spesifikasi_standar" id="inputSpesifikasiStandar" value="{{ old('karton_spesifikasi_standar', 'Sesuai Spesifikasi MFI') }}" readonly style="width: 140px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #b45309; text-align: right; padding: 0;" title="Standar Spesifikasi">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputSpesifikasiAnalisa', 'K150/M125/K150 B/F')">K150/M125/K150 B/F</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputSpesifikasiAnalisa', 'Double Wall 5 Ply')">Double Wall 5 Ply</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputSpesifikasiAnalisa', 'Single Wall 3 Ply')">Single Wall 3 Ply</button>
                            </div>
                            <input type="text" name="karton_spesifikasi_analisa" id="inputSpesifikasiAnalisa" class="form-control param-input-spacious" placeholder="Ketik spesifikasi / gramatur box (Contoh: K150/M125/K150 B/F)..." value="{{ old('karton_spesifikasi_analisa', 'K150/M125/K150 B/F') }}">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 4: HASIL PEMERIKSAAN KUANTITAS --}}
                <div class="karton-subcard amber-accent">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                        <span class="karton-subcard-title">
                            <span>⚖️</span> <span>HASIL PEMERIKSAAN KUANTITAS</span>
                        </span>
                        <span id="labelKartonNetto" style="font-size: 0.95rem; font-weight: 900; color: #b45309; background: #ffffff; padding: 4px 12px; border-radius: 8px; border: 1.5px solid #fde68a;">
                            0 Pcs
                        </span>
                    </div>

                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 800; color: #0f172a;">Jumlah Lolos (<span class="label-satuan-karton">PCS</span>) <span style="color:#ef4444;">*</span></label>
                            <div class="input-suffix-wrap">
                                <input type="number" step="1" min="0" name="karton_qty_gross" id="kartonQtyGross" class="form-control" placeholder="0" value="{{ old('karton_qty_gross') }}" style="font-weight: 800;" required>
                                <span class="input-suffix-text suffix-satuan-karton">PCS</span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="color: #dc2626; font-weight: 800;">Qty Reject / Rusak (<span class="label-satuan-karton">PCS</span>)</label>
                            <div class="input-suffix-wrap">
                                <input type="number" step="1" min="0" name="karton_qty_reject" id="kartonQtyReject" class="form-control" placeholder="0" value="{{ old('karton_qty_reject', 0) }}" style="font-weight: 800;">
                                <span class="input-suffix-text suffix-satuan-karton">PCS</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 5: KESIMPULAN & KOMENTAR PEMERIKSAAN (SESUAI GAMBAR STANDAR QC) --}}
                <div class="karton-subcard">
                    <input type="hidden" name="karton_kesimpulan" id="inputKartonKesimpulan" value="{{ old('karton_kesimpulan', 'TERIMA') }}">
                    <input type="hidden" name="kesimpulan_qc" id="inputKesimpulanQc" value="{{ old('karton_kesimpulan', 'TERIMA') }}">

                    <div class="qc-disposisi-grid">
                        <!-- KARTU TERIMA -->
                        <div class="qc-disposisi-card active terima" id="cardDisposisiTerima" onclick="setKesimpulanKarton('TERIMA')">
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
                                    <input type="number" step="1" min="0" name="qty_terima" id="inputQtyTerima" class="qc-disposisi-input" value="0" readonly>
                                    <span class="qc-disposisi-unit unit-label-disposisi-karton">pcs</span>
                                </div>
                            </div>
                        </div>

                        <!-- KARTU TOLAK -->
                        <div class="qc-disposisi-card tolak" id="cardDisposisiTolak" onclick="setKesimpulanKarton('TOLAK')">
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
                                    <input type="number" step="1" min="0" name="qty_tolak" id="inputQtyTolak" class="qc-disposisi-input" value="0" readonly>
                                    <span class="qc-disposisi-unit unit-label-disposisi-karton">pcs</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CATATAN / KOMENTAR PENGUJIAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.35rem; display: block;">
                            Catatan / Komentar Pengujian:
                        </label>
                        <textarea name="karton_komentar" id="inputKartonKomentar" rows="3" class="form-control" placeholder="Kondisi kualitas mutu karton box baik, dimensi presisi, kemasan bersih." style="font-size: 0.85rem; line-height: 1.45;">{{ old('karton_komentar') }}</textarea>
                    </div>
                </div>

                {{-- SUBCARD 6: OTORISASI & PEMERIKSA (BISA DITAMBAHKAN/DIEDIT LANGSUNG) --}}
                <div class="karton-subcard" style="padding: 0; background: transparent; border: none; box-shadow: none;">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        {{-- PETUGAS QC RAW MATERIAL --}}
                        <div class="qc-auth-card">
                            <div class="qc-auth-header">
                                <div>
                                    <div class="qc-auth-title">Petugas QC Raw Material</div>
                                    <div class="qc-auth-sub">Pemeriksa kedatangan langsung</div>
                                </div>
                                <span class="qc-auth-badge verified">Terverifikasi ID</span>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nama Petugas QC (Bisa Diedit/Ketik Langsung)</label>
                                </div>
                                <input type="text" name="petugas_qc_nama" id="inputPetugasQc" class="form-control" placeholder="Nama Petugas QC" value="{{ old('petugas_qc_nama', auth()->user()?->name ?? 'Petugas QC') }}" style="font-weight: 800; color: #0f172a;">
                            </div>
                        </div>

                        {{-- QC SUPERVISOR / LAB HEAD --}}
                        <div class="qc-auth-card">
                            <div class="qc-auth-header">
                                <div>
                                    <div class="qc-auth-title">QC Supervisor / Lab Head</div>
                                    <div class="qc-auth-sub">Peninjau &amp; Persetujuan Rilis Bahan</div>
                                </div>
                                <span class="qc-auth-badge pending">Menunggu Review</span>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Tugaskan Supervisor Peninjau (Bisa Diedit/Ketik Langsung)</label>
                                </div>
                                <input type="text" name="qc_supervisor_nama" id="inputSupervisorQc" list="listSupervisorQc" class="form-control" placeholder="Pilih atau ketik nama Supervisor..." value="{{ old('qc_supervisor_nama', 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" style="font-weight: 800; color: #0f172a;">
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
            </div>
        </div>

        {{-- FLOATING ACTION DOCK UNTUK MOBILE (DYNAMIC CAPSULE PILL PERSIS PLASTIK & SINGKONG) --}}
        <div class="qc-form-floating-dock" id="qcFloatingDock">
            <button type="button" class="btn-dock-back" id="dockBtnBack" style="display: none;" onclick="switchKartonTab(1)">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span id="dockBackText">Kembali</span>
            </button>
            <button type="button" class="btn-dock-next btn-primary-state" id="dockBtnNext" onclick="switchKartonTab(2)">
                <span id="dockNextText">Lanjut: Mutu Karton</span>
                <svg id="dockNextIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>
</div>

{{-- SUBMIT OVERLAY LOADING --}}
<div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); color: #ffffff;">
    <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center; max-width: 320px; width: 90%;">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem; animation: pulse 1.5s infinite;">📦</div>
        <h3 id="overlayTitle" style="font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem;">Menyimpan QC Karton Box...</h3>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Memvalidasi dimensi &amp; memperbarui status gudang.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            poDetailsUrl: "{{ url('gudang/qc/inbound/po') }}",
            poListData: @json($poListData)
        };
    </script>
    <script src="{{ asset('js/gudang/qc/karton/karton.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/karton/karton-create.js') }}"></script>
@endpush
