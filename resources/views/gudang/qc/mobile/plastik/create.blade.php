@extends('layouts.qc-mobile')

@section('title', 'Sampling Mutu Plastik Kemasan - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($d) {
        $barang = $d?->barang;
        if (!$barang) return 'LAINNYA';

        $nm = strtoupper($barang->barang_nm ?? '');
        $cd = strtoupper($barang->barang_cd ?? '');
        $jnsCd = strtoupper($barang->jenisBarang?->jenis_barang_cd ?? '');

        // Abaikan komoditas non-plastik secara tegas
        if (
            str_contains($nm, 'KARTON') || str_contains($nm, 'DUS') || str_contains($nm, 'BOX') ||
            str_contains($nm, 'MINYAK') || str_contains($nm, 'SINGKONG') || str_contains($nm, 'PUYUR') ||
            str_contains($nm, 'OPAK') || str_contains($nm, 'UBI') || str_contains($nm, 'BUMBU') ||
            str_contains($nm, 'MSG') || str_contains($nm, 'GARAM') || str_contains($nm, 'PERENYAH') ||
            str_contains($nm, 'LAKBAN') || str_contains($nm, 'RAFIA') || str_contains($nm, 'SARUNG TANGAN')
        ) {
            return 'LAINNYA';
        }

        // Kategori Plastik Kemasan & Roll Packing
        if (
            str_contains($nm, 'PLASTIK') || 
            str_contains($nm, 'KEMASAN') || 
            str_contains($nm, 'OPP') || 
            str_contains($nm, 'ROLL') || 
            str_contains($nm, 'POUCH') || 
            str_contains($nm, 'STANDING') ||
            str_starts_with($cd, 'BP-PL') || 
            str_starts_with($cd, 'BP-KM') ||
            str_starts_with($cd, 'PHD') ||
            str_starts_with($cd, 'PEA') ||
            str_starts_with($cd, 'PEC') ||
            str_starts_with($cd, 'PNG') ||
            str_starts_with($cd, 'RXX') ||
            str_starts_with($cd, 'RMX') ||
            $jnsCd === 'PACK'
        ) {
            return 'PLASTIK';
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

    // Filter PO KHUSUS Plastik Kemasan Saja (Hanya PO yang memuat komoditas PLASTIK)
    $plastikPos = $pos->filter(function($p) use ($getPoCommodities) {
        $cats = $getPoCommodities($p);
        return in_array('PLASTIK', $cats);
    });

    // Mapping Data PO Lengkap ke Format JSON untuk Auto-Fill Instan Tanpa Delay
    $poListData = $plastikPos->mapWithKeys(function ($p) use ($getPoCommodities) {
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

    // Filter Supplier khusus Plastik (Vendor Kemasan / Non-Petani)
    $plastikSuppliers = $suppliers->filter(function($s) use ($selectedPo, $plastikPos) {
        if ($selectedPo && $selectedPo->supplier_id == $s->supplier_id) return true;
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($plastikSuppliers->isEmpty()) {
        $plastikSuppliers = $suppliers;
    }
@endphp
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan Khusus Plastik Kemasan (Sama Desain dengan Singkong & Minyak - Aksen Teal) --}}
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
                    Pemeriksaan Ketebalan, Keutuhan, Cacat Cetak &amp; Kebersihan (Formulir HACCP MFI)
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

    {{-- STEP / TAB SWITCHER KHUSUS PLASTIK (2 TAB PERSIS SEPERTI SINGKONG & MINYAK) --}}
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
        <div id="qcSection1" style="display: flex; flex-direction: column; gap: 1rem;">
            {{-- WIDGET WAKTU INSPEKSI LAPANGAN (SAMA DENGAN SINGKONG & MINYAK) --}}
            <div class="plastik-inspection-bar">
                <div class="inspection-bar-left">
                    <div class="inspection-bar-icon">📅</div>
                    <div>
                        <div class="inspection-bar-label">Waktu Inspeksi Lapangan</div>
                        <div class="inspection-bar-val" id="textInspectionBarTime">{{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') }} WIB</div>
                    </div>
                </div>
                <span class="inspection-bar-badge">QC-RM-030</span>
            </div>

            {{-- CARD 1: INFORMASI BAHAN & PENGIRIMAN (PERSIS SEPERTI SINGKONG) --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="card-step-badge teal">1</span>
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
                                <label class="qc-field-label">Referensi PO Plastik Kemasan <span style="font-size: 0.72rem; color: #0d9488; font-weight: normal;">(Opsional)</span></label>
                            </div>
                            <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 700;">
                                @if ($plastikPos->isEmpty())
                                    <option value="">-- Tidak Ada PO Plastik Aktif / Kiriman Langsung --</option>
                                @else
                                    <option value="">-- Tanpa PO / Kiriman Langsung Plastik --</option>
                                    @foreach ($plastikPos as $p)
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
                            <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="SJ-PL-2026/099" value="{{ old('surat_jalan_supplier') }}" style="font-weight: 700;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Tanggal Datang</label>
                                <button type="button" onclick="setCurrentDateTime()" style="background: none; border: none; color: #0d9488; font-size: 0.72rem; font-weight: 800; cursor: pointer; padding: 0;">🕒 Sekarang</button>
                            </div>
                            <input type="datetime-local" name="tgl_periksa" id="inputTglDatang" class="form-control" value="{{ old('tgl_periksa', now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" style="font-weight: 700;">
                        </div>
                    </div>

                    {{-- 3. MITRA VENDOR & NAMA PRODUSEN (SUBCARD KELOMPOK DENGAN DESAIN PERSIS SINGKONG) --}}
                    <div class="plastik-subcard">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                    Mitra Vendor / Supplier Kemasan <span style="color:#ef4444;">*</span>
                                </label>
                            </div>
                            <select name="supplier_id" id="supplierSelect" class="form-control" required onchange="onSupplierSelected(this)" style="font-weight: 700; font-size: 0.85rem;">
                                <option value="">-- Pilih Mitra Vendor Kemasan --</option>
                                @foreach ($plastikSuppliers as $s)
                                    <option value="{{ $s->supplier_id }}"
                                            data-supplier-cd="{{ $s->supplier_cd }}"
                                            data-supplier-nm="{{ $s->supplier_nm }}"
                                            {{ old('supplier_id', $selectedPo?->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                                        {{ $s->supplier_nm }} ({{ $s->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- NAMA BAHAN BAKU (MASTER BARANG PLASTIK KEMASAN) --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                    Nama Bahan Baku Kemasan <span style="color:#ef4444;">*</span>
                                </label>
                            </div>
                            <select name="plastik_barang_id" id="plastikBarangSelect" class="form-control" style="font-weight: 700;" onchange="onPlastikBarangChanged(this)" required>
                                @foreach ($barangs as $b)
                                    @if (stripos($b->barang_nm, 'plastik') !== false || stripos($b->barang_nm, 'kemasan') !== false || stripos($b->barang_nm, 'opp') !== false || stripos($b->barang_nm, 'pp') !== false || stripos($b->barang_nm, 'roll') !== false || stripos($b->barang_cd, 'BP-PL') !== false)
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

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nama Produsen <span style="font-size: 0.72rem; color: #0d9488; font-weight: normal;">(Bisa diketik)</span></label>
                                </div>
                                <input type="text" name="nama_produsen" id="namaProdusenInput" class="form-control" placeholder="Contoh: PT Polychem Pack" value="{{ old('nama_produsen') }}" style="font-weight: 700;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Negara Produsen</label>
                                </div>
                                <input type="text" name="negara_produsen" class="form-control" value="{{ old('negara_produsen', 'Indonesia') }}" style="font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    {{-- 4. PLAT TRUK / KENDARAAN & NAMA SOPIR (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Plat Truk / Mobil Box</label>
                            </div>
                            <input type="text" name="plat_nomor_truk" class="form-control" placeholder="B 9123 KDA" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase; font-weight: 800;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Nama Sopir</label>
                            </div>
                            <input type="text" name="sopir_nama" class="form-control" placeholder="Nama pengemudi" value="{{ old('sopir_nama') }}">
                        </div>
                    </div>

                    {{-- 5. SUBCARD: KUANTITAS & VERIFIKASI PENGIRIMAN (TEAL THEME - DENGAN PILIHAN PCS & KG) --}}
                    <div class="plastik-subcard teal-accent">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                            <span class="plastik-subcard-title" style="margin-bottom: 0;">
                                <span>⚖️</span> <span>KUANTITAS &amp; VERIFIKASI PENGIRIMAN</span>
                            </span>
                            <div class="satuan-toggle-pills" id="satuanToggleGroupPlastik">
                                <button type="button" class="btn-satuan-pill active" id="pillSatuan_PCS" onclick="setPlastikSatuan('PCS')">
                                    <span class="pill-icon">🛍️</span>
                                    <span class="pill-text">PCS</span>
                                </button>
                                <button type="button" class="btn-satuan-pill" id="pillSatuan_KG" onclick="setPlastikSatuan('KG')">
                                    <span class="pill-icon">⚖️</span>
                                    <span class="pill-text">KG</span>
                                </button>
                            </div>
                            <input type="hidden" name="satuan" id="inputPlastikSatuan" value="{{ old('satuan', 'PCS') }}">
                        </div>

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah Surat Jalan (<span class="label-satuan-plastik">PCS</span>) <span style="color:#ef4444;">*</span></label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="1" min="0.01" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="50000" value="{{ old('jumlah_surat_jalan') }}" style="font-weight: 800;" required>
                                    <span class="input-suffix-text suffix-satuan-plastik">PCS</span>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah di Pabrik (<span class="label-satuan-plastik">PCS</span>)</label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="1" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="50000" value="{{ old('jumlah_di_pabrik') }}" style="font-weight: 800;">
                                    <span class="input-suffix-text suffix-satuan-plastik">PCS</span>
                                </div>
                            </div>
                        </div>

                        {{-- Sampel QC Diambil & Netto Real-Time --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.25rem; border-top: 1px dashed #99f6e4; padding-top: 0.55rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.8rem; font-weight: 800; color: #0f172a;">Sampel QC Diperiksa:</span>
                                <div class="input-suffix-wrap" style="max-width: 130px;">
                                    <input type="number" step="1" min="1" name="jumlah_sample_pcs" id="inputJumlahSamplePcs" class="form-control" placeholder="10" value="{{ old('jumlah_sample_pcs', 10) }}" style="text-align: right; font-weight: 900; color: #0d9488; height: 38px !important; min-height: 38px !important;">
                                    <span class="input-suffix-text">PCS</span>
                                </div>
                            </div>
                            <span id="labelSubcardNetto" style="font-size: 0.775rem; font-weight: 800; color: #0d9488; background: #ffffff; padding: 3px 8px; border-radius: 6px; border: 1px solid #99f6e4;">
                                Netto Lolos: 0 Pcs
                            </span>
                        </div>

                        {{-- NAMA JENIS : (Spesifikasi Fraksi / Jenis Kemasan) --}}
                        <div class="form-group" style="margin-top: 0.65rem; margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 800; font-size: 0.775rem; color: #0f766e;">NAMA JENIS : (Spesifikasi Kemasan)</label>
                            <input type="text" name="nama_jenis" id="plastikNamaJenisInput" list="listPlastikJenis" class="form-control" placeholder="Plastik Kemasan Keripik Cap Payung / Standing Pouch" value="{{ old('nama_jenis', 'Plastik Kemasan Keripik Cap Payung') }}" style="font-weight: 700;">
                            <datalist id="listPlastikJenis">
                                <option value="Plastik Kemasan Keripik Cap Payung">
                                <option value="Plastik OPP Roll 250g">
                                <option value="Plastik OPP Roll 500g">
                                <option value="Standing Pouch Cap Payung">
                                <option value="Plastik PP Tebal 0.08mm">
                            </datalist>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: STANDAR TRANSPORTASI & JAMINAN HALAL (PERSIS GAMBAR 2) --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #99f6e4; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="card-step-badge teal" style="width: 28px; height: 28px; font-size: 0.85rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">2</span>
                        <div>
                            <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f766e; margin: 0; line-height: 1.25;">Standar Transportasi &amp; Jaminan Halal</h2>
                            <span style="font-size: 0.75rem; color: #0d9488; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; Kebersihan Armada Pengangkut</span>
                        </div>
                    </div>
                    <span style="font-size: 1.35rem;">🛡️</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    {{-- ITEM 1: KONDISI TRANSPORTASI (TRUK / BOX KEMASAN) --}}
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin-bottom: 0.55rem;">
                            1. Kondisi Transportasi (Truk / Box Kemasan)
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.55rem;">
                            <label class="transport-radio-card active" id="cardTransportBebas">
                                <input type="radio" name="bebas_cemaran_st" value="1" checked onchange="updateTransportCard(this)" style="margin-top: 3px; accent-color: #0d9488; width: 18px; height: 18px;">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #0d9488;">Tidak ada cemaran, Najis / Kotoran</div>
                                    <div class="transport-card-desc">Bak mobil box bersih, kering, bebas bau asing &amp; bebas zat kimia</div>
                                </div>
                            </label>
                            <label class="transport-radio-card is-danger" id="cardTransportCemar">
                                <input type="radio" name="bebas_cemaran_st" value="0" onchange="updateTransportCard(this)" style="margin-top: 3px; accent-color: #ef4444; width: 18px; height: 18px;">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #dc2626;">Ada cemaran / Kotor / Najis</div>
                                    <div class="transport-card-desc">Ditemukan indikasi kontaminasi, basah kotoran, atau bau menyengat</div>
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
                                <button type="button" class="segmented-toggle-btn" id="btnSertifikatTidak" onclick="setHalalToggle('ada_sertifikat_halal', 0)">Tidak</button>
                                <button type="button" class="segmented-toggle-btn active ok" id="btnSertifikatYa" onclick="setHalalToggle('ada_sertifikat_halal', 1)">Ya</button>
                            </div>
                            <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat_halal" value="{{ old('ada_sertifikat_halal_st', 1) }}">
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
                                <button type="button" class="segmented-toggle-btn" id="btnBerlakuTidak" onclick="setHalalToggle('sertifikat_halal_berlaku', 0)">Tidak</button>
                                <button type="button" class="segmented-toggle-btn active ok" id="btnBerlakuYa" onclick="setHalalToggle('sertifikat_halal_berlaku', 1)">Ya</button>
                            </div>
                            <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_halal_berlaku" value="{{ old('sertifikat_halal_berlaku_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_berlaku" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_berlaku') }}" style="font-size: 0.85rem;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN KUALITAS PLASTIK KEMASAN (FORM HACCP RESMI)           --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border: 1.5px solid #99f6e4; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="card-step-badge teal" style="width: 28px; height: 28px; font-size: 0.85rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">2</span>
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f766e; margin: 0; line-height: 1.25;">Tahap 2: Pengujian Mutu Plastik Kemasan</h2>
                        <span style="font-size: 0.75rem; color: #0d9488; font-weight: 700;">Analisa mikrometer ketebalan, keutuhan seal, cacat kemasan &amp; toleransi reject</span>
                    </div>
                </div>
            </div>

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.15rem;">
                {{-- SUBCARD 1: ISI RAW MATERIAL & KEMASAN STATUS (MODERN CHOICE BUTTONS) --}}
                <div class="plastik-subcard">
                    <span class="plastik-subcard-title">
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
                            <input type="hidden" name="plastik_status_raw_material" id="inputRawMaterial" value="OK">
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
                            <input type="hidden" name="plastik_kemasan_kondisi" id="inputKemasanKondisi" value="OK">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 2: CHECKLIST CACAT KEMASAN (MODERN CHECKLIST CARDS) --}}
                <div class="plastik-subcard">
                    <span class="plastik-subcard-title" style="color: #475569;">
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
                            <input type="checkbox" name="plastik_kemasan_kotor" id="checkCacatKotor" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatApek')">
                            <div class="modern-check-label">
                                <span>👃</span> <span>APEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatApek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="plastik_kemasan_apek" id="checkCacatApek" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatBasah')">
                            <div class="modern-check-label">
                                <span>💧</span> <span>BASAH</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatBasah">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="plastik_kemasan_basah" id="checkCacatBasah" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        <div class="modern-check-card" onclick="toggleModernCheck('checkCacatSobek')">
                            <div class="modern-check-label">
                                <span>✂️</span> <span>SOBEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkCacatSobek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="plastik_kemasan_sobek" id="checkCacatSobek" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 3: ANALISA PARAMETER MIKROMETER (SPACIOUS MOBILE CARDS) --}}
                <div class="plastik-subcard">
                    <span class="plastik-subcard-title" style="color: #0f766e; margin-bottom: 0.75rem;">
                        <span>🔬</span> <span>ANALISA PARAMETER KETEBALAN &amp; KEUTUHAN</span>
                    </span>

                    <div class="param-analysis-wrap">
                        {{-- 1. KETEBALAN --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>📏</span> KETEBALAN MIKROMETER
                                </span>
                                <div class="param-std-badge">
                                    <span>Standar:</span>
                                    <input type="text" name="plastik_ketebalan_standar" id="inputKetebalanStandar" value="{{ old('plastik_ketebalan_standar', '0.08 mm') }}" style="width: 75px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #0f766e; text-align: right; padding: 0;" title="Standar Ketebalan">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.08 mm')">0.08 mm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.085 mm')">0.085 mm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.075 mm')">0.075 mm</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputKetebalanAnalisa', '0.09 mm')">0.09 mm</button>
                            </div>
                            <input type="text" name="plastik_ketebalan_analisa" id="inputKetebalanAnalisa" class="form-control param-input-spacious" placeholder="Ketik hasil analisa ketebalan (Contoh: 0.08 mm)..." value="{{ old('plastik_ketebalan_analisa') }}">
                        </div>

                        {{-- 2. KEUTUHAN --}}
                        <div class="param-item-card">
                            <div class="param-item-header">
                                <span class="param-item-name">
                                    <span>🛡️</span> KEUTUHAN SEAL &amp; FISIK
                                </span>
                                <div class="param-std-badge">
                                    <span>Standar:</span>
                                    <input type="text" name="plastik_keutuhan_standar" id="inputKeutuhanStandar" value="{{ old('plastik_keutuhan_standar', 'Tidak Sobek') }}" readonly style="width: 85px; border: none; background: transparent; font-weight: 800; font-size: 0.75rem; color: #0f766e; text-align: right; padding: 0;" title="Standar Keutuhan">
                                </div>
                            </div>
                            <div class="param-chip-row">
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Tidak Sobek')">✔ Tidak Sobek</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Sobek')">✖ Sobek</button>
                                <button type="button" class="param-chip-btn" onclick="setParamValue('inputKeutuhanAnalisa', 'Seal Kurang Rapat')">⚠️ Seal Kurang Rapat</button>
                            </div>
                            <input type="text" name="plastik_keutuhan_analisa" id="inputKeutuhanAnalisa" class="form-control param-input-spacious" placeholder="Ketik hasil analisa keutuhan (Contoh: Tidak Sobek)..." value="{{ old('plastik_keutuhan_analisa', 'Tidak Sobek') }}">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 4: HASIL PEMERIKSAAN KUANTITAS --}}
                <div class="plastik-subcard teal-accent">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                        <span class="plastik-subcard-title">
                            <span>⚖️</span> <span>HASIL PEMERIKSAAN KUANTITAS</span>
                        </span>
                        <span id="labelPlastikNetto" style="font-size: 0.95rem; font-weight: 900; color: #0d9488; background: #ffffff; padding: 4px 12px; border-radius: 8px; border: 1.5px solid #99f6e4;">
                            0 Pcs
                        </span>
                    </div>

                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 800; color: #0f172a;">Jumlah Lolos (<span class="label-satuan-plastik">PCS</span>) <span style="color:#ef4444;">*</span></label>
                            <div class="input-suffix-wrap">
                                <input type="number" step="1" min="0" name="plastik_qty_gross" id="plastikQtyGross" class="form-control" placeholder="0" value="{{ old('plastik_qty_gross') }}" style="font-weight: 800;" required>
                                <span class="input-suffix-text suffix-satuan-plastik">PCS</span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="color: #dc2626; font-weight: 800;">Qty Reject / Cacat (<span class="label-satuan-plastik">PCS</span>)</label>
                            <div class="input-suffix-wrap">
                                <input type="number" step="1" min="0" name="plastik_qty_reject" id="plastikQtyReject" class="form-control" placeholder="0" value="{{ old('plastik_qty_reject', 0) }}" style="font-weight: 800;">
                                <span class="input-suffix-text suffix-satuan-plastik">PCS</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 5: KOMENTAR & KESIMPULAN AKHIR --}}
                <div class="plastik-subcard">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 800; color: #0f172a;">KOMENTAR PEMERIKSAAN :</label>
                        <textarea name="plastik_komentar" id="inputPlastikKomentar" rows="2" class="form-control" placeholder="Catatan kondisi kualitas plastik kemasan...">{{ old('plastik_komentar') }}</textarea>
                    </div>

                    <div style="border-top: 1px solid #e2e8f0; padding-top: 0.85rem;">
                        <label class="form-label" style="font-weight: 900; color: #0f172a; margin-bottom: 0.5rem;">KESIMPULAN QC :</label>
                        <div class="kesimpulan-radio-wrap">
                            <div class="kesimpulan-radio-card active terima" id="cardKesimpulanTerima" onclick="setKesimpulanPlastik('TERIMA')">
                                <span>✅</span> <span>TERIMA</span>
                            </div>
                            <div class="kesimpulan-radio-card tolak" id="cardKesimpulanTolak" onclick="setKesimpulanPlastik('TOLAK')">
                                <span>❌</span> <span>TOLAK</span>
                            </div>
                        </div>
                        <input type="hidden" name="plastik_kesimpulan" id="inputKesimpulanQc" value="TERIMA">
                    </div>
                </div>

                {{-- SUBCARD 6: OTORISASI & PEMERIKSA (BISA DITAMBAHKAN/DIEDIT LANGSUNG) --}}
                <div class="plastik-subcard" style="padding: 0; background: transparent; border: none; box-shadow: none;">
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
                                </datalist>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FLOATING ACTION DOCK UNTUK MOBILE (DYNAMIC CAPSULE PILL PERSIS SINGKONG & MINYAK) --}}
        <div class="qc-form-floating-dock" id="qcFloatingDock">
            <button type="button" class="btn-dock-back" id="dockBtnBack" style="display: none;" onclick="switchPlastikTab(1)">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span id="dockBackText">Kembali</span>
            </button>
            <button type="button" class="btn-dock-next btn-primary-state" id="dockBtnNext" onclick="switchPlastikTab(2)">
                <span id="dockNextText">Lanjut ke Tahap 2</span>
                <svg id="dockNextIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
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
            poDetailsUrl: "{{ url('gudang/qc/inbound/po') }}",
            poList: {!! json_encode($poListData) !!}
        };
        window.qcConfig = window.appConfig;
    </script>
    <script src="{{ asset('js/gudang/qc/plastik/plastik-create.js') }}"></script>
@endpush
