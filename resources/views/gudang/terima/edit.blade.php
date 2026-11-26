@extends('layouts.app')

@section('title', 'Edit Penerimaan Barang ' . $terima->terima_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-workstation.css') }}?v={{ filemtime(public_path('css/gudang/po/po-workstation.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-form.css') }}?v={{ filemtime(public_path('css/gudang/terima/terima-form.css')) }}">
@endpush

@section('content')
    {{-- 1. WORKSTATION BREADCRUMB & HEADER BAR SESUAI PO_DESIGN_SYSTEM.MD --}}
    <nav class="terima-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('gudang.terima.index') }}">Pergudangan</a>
        <span class="breadcrumb-separator" aria-hidden="true">/</span>
        <a href="{{ route('gudang.terima.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-separator" aria-hidden="true">/</span>
        <span class="breadcrumb-current">Edit GRN: {{ $terima->terima_no }}</span>
    </nav>

    <div class="terima-header-bar">
        <div class="terima-header-title">
            <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="terima-back-link" aria-label="Kembali ke detail penerimaan">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h1>Edit Penerimaan Barang</h1>
            <span class="terima-pill-badge terima-pill-code">{{ $terima->terima_no }}</span>
            <span class="terima-pill-badge terima-pill-sky">Mode Edit</span>
        </div>

        <div class="terima-header-meta">
            <div class="terima-live-summary">
                <span class="live-label">TOTAL NETTO MASUK:</span>
                <span class="live-val" id="topLiveGrandTotal">0,00 KG</span>
                <span class="live-divider">|</span>
                <span class="live-count">Baris: <strong id="topLiveItemCount">{{ $terima->details->count() }} Item</strong></span>
            </div>
            <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn-header-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Lihat Dokumen</span>
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger" style="margin-bottom: 0.85rem; background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444; color: #991b1b; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.825rem;">
            <strong style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.25rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Terjadi kesalahan input:</span>
            </strong>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gudang.terima.update', $terima->terima_id) }}" method="POST" id="formTerima">
        @csrf
        @method('PUT')

        {{-- 1.5. VALIDATION & NO-CHANGE BANNER --}}
        @if (session('no_change_info'))
            <div id="terimaValidationBanner" class="po-validation-banner" style="display: block; background: #f8fafc; border: 1.5px solid #cbd5e1; border-left: 4px solid #64748b; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: flex-start; gap: 0.65rem;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b; flex-shrink: 0; margin-top: 2px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong style="display: block; font-size: 0.875rem; color: #1e293b; margin-bottom: 0.2rem;">
                                Tidak Ada Perubahan Data
                            </strong>
                            <div style="font-size: 0.8rem; color: #475569; line-height: 1.4;">
                                {{ session('no_change_info') }}
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                        <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn btn-sm" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <span>Kembali ke Detail Penerimaan</span>
                        </a>
                        <button type="button" onclick="document.getElementById('terimaValidationBanner').style.display='none'" style="background: none; border: none; color: #94a3b8; font-size: 1.25rem; line-height: 1; cursor: pointer; padding: 0.1rem 0.35rem;" title="Tutup">
                            &times;
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div id="terimaValidationBanner" class="po-validation-banner" style="display: none;"></div>
        @endif

        {{-- 2. TOOLBAR CARD RAMPING: SEMUA FIELD LANGSUNG TAMPIL 2 BARIS --}}
        <div class="po-toolbar-card">
            {{-- GRID UNIFIED 4 KOLOM X 2 BARIS --}}
            <div class="po-toolbar-grid">
                {{-- [R1-C1] Tanggal Masuk Fisik --}}
                <div>
                    <label for="terima_tgl" class="po-field-label">
                        Tanggal Masuk <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="date" id="terima_tgl" name="terima_tgl" value="{{ old('terima_tgl', $terima->terima_tgl ? $terima->terima_tgl->format('Y-m-d') : date('Y-m-d')) }}"
                        class="po-field-input" required>
                </div>

                {{-- [R1-C2] Nomor Penerimaan (GRN) --}}
                <div>
                    <label for="terima_no" class="po-field-label">
                        Nomor GRN <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="terima_no" name="terima_no" value="{{ old('terima_no', $terima->terima_no) }}"
                        class="po-field-input" style="font-family: monospace; font-weight: 700;" required>
                </div>

                {{-- [R1-C3] Supplier Pengirim (Smart Autocomplete / Picker) --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                        <label id="lblSupplierHeader" class="po-field-label" style="margin-bottom: 0;">
                            Supplier Pengirim <span style="color:#ef4444;">*</span>
                        </label>
                        <a href="javascript:void(0)" onclick="openSupplierModal()"
                            style="font-size: 0.7rem; color: #0284c7; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;"
                            title="Buka Daftar Master Supplier">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Daftar ({{ $supplierList->count() }})
                        </a>
                    </div>

                    {{-- Selected Supplier Card --}}
                    <div id="selected_supplier_card"
                        style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.25rem 0.65rem; justify-content: space-between; align-items: center; gap: 0.4rem; height: 36px; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; min-width: 0;">
                            <span style="background: #22c55e; color: #fff; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div style="min-width: 0; display: flex; align-items: center; gap: 0.3rem;">
                                <strong id="disp_sup_name" style="color: #14532d; font-size: 0.775rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></strong>
                                <span id="disp_sup_code" class="badge" style="background: #dcfce7; color: #166534; font-size: 0.65rem; font-weight: 700; font-family: monospace;"></span>
                                <span id="disp_sup_cat_badge" style="display: none;"></span>
                                <span id="disp_sup_contact" style="display: none;"></span>
                            </div>
                        </div>
                        <button type="button" onclick="clearSelectedSupplier()" class="btn btn-sm"
                            style="background: #ffffff; border: 1px solid #86efac; color: #15803d; font-size: 0.675rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 3px; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
                            Ganti
                        </button>
                    </div>

                    {{-- Search Box Autocomplete Supplier --}}
                    <div id="supplier_search_wrapper" style="position: relative;">
                        <div style="position: relative; width: 100%;">
                            <input type="text" id="supplier_search_input" class="po-field-input"
                                placeholder="Cari nama / kode supplier mitra..." autocomplete="off"
                                style="padding-left: 1.85rem !important; padding-right: 1.5rem !important;">
                            <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </span>
                            <span style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </div>
                        <div id="supplier_dropdown_list"
                            style="display: none; position: absolute; top: calc(100% + 3px); left: 0; right: 0; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.06); max-height: 280px; overflow-y: auto; z-index: 1050;">
                        </div>
                    </div>

                    <select id="supplier_id" name="supplier_id" style="display: none;" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($supplierList as $sup)
                            <option value="{{ $sup->supplier_id }}"
                                data-code="{{ $sup->supplier_cd }}"
                                data-name="{{ $sup->supplier_nm }}"
                                data-category="{{ $sup->jenisSupplier?->jenis_supplier_cd ?? 'OTHER' }}"
                                data-category-name="{{ $sup->jenisSupplier?->jenis_supplier_nm ?? 'Lainnya' }}"
                                data-contact="{{ $sup->kontak_no ?? '' }}"
                                data-address="{{ $sup->alamat_txt ?? '' }}"
                                {{ (old('supplier_id', $terima->supplier_id) == $sup->supplier_id) ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R1-C4] Perusahaan / Lokasi Penerima --}}
                <div>
                    <label for="gudang_id" class="po-field-label">
                        Gudang Lokasi Penerima <span style="color:#ef4444;">*</span>
                    </label>
                    <select id="gudang_id" name="gudang_id" class="po-field-select" required>
                        <option value="">-- Pilih Gudang Masuk --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $terima->gudang_id) == $gdg->gudang_id) ? 'selected' : '' }}>
                                {{ $gdg->display_name ?? $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R2-C1] Referensi Purchase Order --}}
                <div>
                    <label for="po_id" class="po-field-label">
                        Referensi PO (Opsional)
                    </label>
                    <select id="po_id" name="po_id" class="po-field-select" onchange="onPoSelected(this.value)">
                        <option value="">-- Non-PO / Pembelian Langsung --</option>
                        @foreach ($openPoList as $po)
                            <option value="{{ $po->po_id }}" 
                                data-supplier="{{ $po->supplier_id }}" 
                                data-gudang="{{ $po->gudang_id }}"
                                {{ (old('po_id', $terima->po_id) == $po->po_id) ? 'selected' : '' }}>
                                {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} ({{ $po->status_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- [R2-C2] No. Surat Jalan Supplier --}}
                <div>
                    <label for="suratjalan_no" class="po-field-label">
                        No. Surat Jalan (SJ) Supplier
                    </label>
                    <input type="text" id="suratjalan_no" name="suratjalan_no" value="{{ old('suratjalan_no', $terima->suratjalan_no) }}"
                        class="po-field-input" placeholder="Contoh: SJ-2026/09/88">
                </div>

                {{-- [R2-C3] Jatuh Tempo Surat Jalan --}}
                <div>
                    <label for="tgl_jatuh_tempo" class="po-field-label">
                        Jatuh Tempo SJ (TOP)
                    </label>
                    <input type="date" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo" value="{{ old('tgl_jatuh_tempo', $terima->tgl_jatuh_tempo ? \Carbon\Carbon::parse($terima->tgl_jatuh_tempo)->format('Y-m-d') : '') }}"
                        class="po-field-input">
                </div>

                {{-- [R2-C4] Catatan Singkat Armada / Lapangan --}}
                <div>
                    <label for="catatan_txt" class="po-field-label">
                        Keterangan Armada / Plat Truk
                    </label>
                    <input type="text" id="catatan_txt" name="catatan_txt" value="{{ old('catatan_txt', $terima->catatan_txt) }}"
                        class="po-field-input" placeholder="Contoh: Plat AA 2382 KL (Sopir: Kumar)">
                </div>
            </div>
        </div>

        {{-- 3. KARTU TABEL WORKSTATION FULL-WIDTH --}}
        <div class="po-workstation-table-card">
            <div style="background: #ffffff; padding: 0.55rem 0.85rem; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <strong style="color: #0f172a; font-size: 0.875rem; white-space: nowrap;">Fisik Barang Diterima:</strong>
                    <div class="category-segmented-control" role="group" aria-label="Filter Kategori Barang" style="padding: 2px;">
                        <button type="button" class="category-segment-btn active" data-category="ALL" onclick="filterBarangCategory('ALL', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Semua</span>
                            <span class="category-segment-count">{{ $barangList->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="BB" onclick="filterBarangCategory('BB', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Bahan Baku</span>
                            <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']))->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="BUMBU" onclick="filterBarangCategory('BUMBU', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Bumbu &amp; Penolong</span>
                            <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))->count() }}</span>
                        </button>
                        <button type="button" class="category-segment-btn" data-category="PACK" onclick="filterBarangCategory('PACK', this)" style="padding: 0.2rem 0.55rem; font-size: 0.725rem;">
                            <span>Kemasan</span>
                            <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)))->count() }}</span>
                        </button>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <span style="font-size: 0.725rem; color: #64748b;">
                        Tekan <kbd style="background:#e2e8f0; padding:1px 4px; border-radius:3px; font-weight:700;">Enter</kbd> untuk baris baru
                    </span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="excel-grid-table" id="tableTerimaItems" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 32px; text-align: center;">#</th>
                            <th style="min-width: 250px;">Nama Komoditas / Barang <span style="color:#ef4444;">*</span></th>
                            <th style="width: 155px;">Nomor Batch Fisik <span style="color:#ef4444;">*</span></th>
                            <th style="width: 120px; text-align: center;">Tgl Expired</th>
                            <th style="width: 115px; text-align: center;">Grade</th>
                            <th style="width: 110px; text-align: right;">Qty (Bruto) <span style="color:#ef4444;">*</span></th>
                            <th style="width: 90px; text-align: right;">Afkir</th>
                            <th style="width: 110px; text-align: right;">Netto</th>
                            <th style="width: 70px; text-align: center;">Satuan</th>
                            <th style="width: 40px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="terimaItemsContainer">
                        @foreach ($terima->details as $idx => $dtl)
                            @php
                                $nettoRow = max(0, (float)$dtl->terima_qty - (float)($dtl->reject_qty ?? 0));
                                $sisaQty = (float)($dtl->poDetail?->sisa_qty ?? 0);
                                $pesanQty = (float)($dtl->poDetail?->pesan_qty ?? 0);
                            @endphp
                            <tr class="terima-row" data-index="{{ $idx }}" data-sisa="{{ $sisaQty }}" data-pesan="{{ $pesanQty }}" data-barang-nm="{{ $dtl->barang?->barang_nm }}">
                                <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">{{ $idx + 1 }}</td>
                                <td>
                                    @if ($dtl->podtl_id)
                                        <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $dtl->podtl_id }}">
                                        <input type="hidden" name="items[{{ $idx }}][barang_id]" value="{{ $dtl->barang_id }}" class="item-barang-id">
                                        <input type="hidden" name="items[{{ $idx }}][harga_nominal]" value="{{ old("items.{$idx}.harga_nominal", (float) ($dtl->harga_nominal ?? 0)) }}" class="item-harga">
                                        <input type="hidden" name="items[{{ $idx }}][diskon_persen]" value="{{ old("items.{$idx}.diskon_persen", (float) ($dtl->diskon_persen ?? 0)) }}" class="item-diskon">
                                        <input type="hidden" name="items[{{ $idx }}][potongan_nominal]" value="{{ old("items.{$idx}.potongan_nominal", (float) ($dtl->potongan_nominal ?? 0)) }}" class="item-potongan">
                                        <input type="hidden" name="items[{{ $idx }}][ppn_tipe]" value="{{ old("items.{$idx}.ppn_tipe", $dtl->ppn_tipe ?? 'NON_PPN') }}" class="item-ppn-tipe">
                                        <strong style="color: #0f172a; display: block; font-size: 0.825rem;" class="item-barang-name">{{ $dtl->barang?->barang_nm }}</strong>
                                        <span style="font-size: 0.725rem; color: #64748b; display: block; margin-top: 2px;">
                                            Kode: <span style="font-family: monospace;">{{ $dtl->barang?->barang_cd }}</span> &bull; Satuan: {{ $dtl->barang?->satuanDasar?->satuan_nm ?? '-' }}
                                            @if ($terima->po)
                                                &bull; Ref PO: <span style="font-family: monospace; font-weight: 600; color: #0284c7;">{{ $terima->po->po_no }}</span>
                                            @endif
                                        </span>
                                        <div class="row-po-status-badge" style="margin-top: 3px;">
                                            @php
                                                $diffInitial = $nettoRow - $pesanQty;
                                            @endphp
                                            @if ($pesanQty > 0)
                                                @if ($diffInitial > 0.001)
                                                    <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                        Over PO (+{{ number_format($diffInitial, 2, ',', '.') }})
                                                    </span>
                                                @elseif ($diffInitial < -0.001)
                                                    <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                        Kurang / Parsial (-{{ number_format(abs($diffInitial), 2, ',', '.') }})
                                                    </span>
                                                @else
                                                    <span class="badge" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                        Sesuai PO (100%)
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    @else
                                        <select name="items[{{ $idx }}][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required style="font-size: 0.825rem; font-weight: 600;">
                                            <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                                            
                                            <optgroup label="BAHAN BAKU (RAW MATERIAL)" class="grp-bb">
                                                @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                                                    <option value="{{ $b->barang_id }}" 
                                                            data-category="BB"
                                                            data-cd="{{ $b->barang_cd }}" 
                                                            data-nm="{{ $b->barang_nm }}"
                                                            data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                            data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                            data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}"
                                                            {{ (old("items.{$idx}.barang_id", $dtl->barang_id) == $b->barang_id) ? 'selected' : '' }}>
                                                        {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                    </option>
                                                @endforeach
                                            </optgroup>

                                            <optgroup label="BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                                                @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                                                    <option value="{{ $b->barang_id }}" 
                                                            data-category="BUMBU"
                                                            data-cd="{{ $b->barang_cd }}" 
                                                            data-nm="{{ $b->barang_nm }}"
                                                            data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                            data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                            data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}"
                                                            {{ (old("items.{$idx}.barang_id", $dtl->barang_id) == $b->barang_id) ? 'selected' : '' }}>
                                                        {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                    </option>
                                                @endforeach
                                            </optgroup>

                                            <optgroup label="BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                                                @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                                                    <option value="{{ $b->barang_id }}" 
                                                            data-category="PACK"
                                                            data-cd="{{ $b->barang_cd }}" 
                                                            data-nm="{{ $b->barang_nm }}"
                                                            data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                            data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                            data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}"
                                                            {{ (old("items.{$idx}.barang_id", $dtl->barang_id) == $b->barang_id) ? 'selected' : '' }}>
                                                        {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                        <input type="hidden" name="items[{{ $idx }}][harga_nominal]" value="{{ old("items.{$idx}.harga_nominal", (float) ($dtl->harga_nominal ?? 0)) }}" class="item-harga">
                                        <input type="hidden" name="items[{{ $idx }}][diskon_persen]" value="{{ old("items.{$idx}.diskon_persen", (float) ($dtl->diskon_persen ?? 0)) }}" class="item-diskon">
                                        <input type="hidden" name="items[{{ $idx }}][potongan_nominal]" value="{{ old("items.{$idx}.potongan_nominal", (float) ($dtl->potongan_nominal ?? 0)) }}" class="item-potongan">
                                        <input type="hidden" name="items[{{ $idx }}][ppn_tipe]" value="{{ old("items.{$idx}.ppn_tipe", $dtl->ppn_tipe ?? 'NON_PPN') }}" class="item-ppn-tipe">
                                    @endif
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ old("items.{$idx}.batch_no", $dtl->batch_no) }}" placeholder="Nomor batch..." class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%; font-size: 0.8rem;" required>
                                </td>
                                <td>
                                    <input type="date" name="items[{{ $idx }}][expired_tgl]" value="{{ old("items.{$idx}.expired_tgl", $dtl->expired_tgl ? $dtl->expired_tgl->format('Y-m-d') : '') }}" class="form-control" style="font-size: 0.8rem;">
                                </td>
                                <td>
                                    @php
                                        $dtlGrade = old("items.{$idx}.grade_cd", $dtl->grade_cd ?: ($dtl->barang?->hasGrade() ? 'A' : 'NON-GRADE'));
                                    @endphp
                                    <select name="items[{{ $idx }}][grade_cd]" class="form-control po-grade-select">
                                        <option value="A" {{ $dtlGrade == 'A' ? 'selected' : '' }}>Grade A</option>
                                        <option value="B" {{ $dtlGrade == 'B' ? 'selected' : '' }}>Grade B</option>
                                        <option value="NON-GRADE" {{ in_array($dtlGrade, ['NON-GRADE', 'NON_GRADE', 'NON GRADE']) ? 'selected' : '' }}>Non Grade</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][terima_qty]" value="{{ old("items.{$idx}.terima_qty", (float) $dtl->terima_qty) }}" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][reject_qty]" value="{{ old("items.{$idx}.reject_qty", (float) ($dtl->reject_qty ?? 0)) }}" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                </td>
                                <td class="row-netto">
                                    {{ number_format($nettoRow, 2, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="row-satuan" style="font-weight: 600; color: #475569; font-size: 0.8rem;">{{ $dtl->barang?->satuanDasar?->satuan_nm ?? '-' }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" onclick="removeTerimaRow(this)" class="btn-remove-row" title="Hapus Baris">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                            <td colspan="5" style="padding: 0.55rem 0.75rem; text-align: right; color: #475569; font-size: 0.8rem; text-transform: uppercase;">
                                Akumulasi Total Fisik:
                            </td>
                            <td id="totalBrutoQtyDisplay" style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #0284c7; padding-right: 0.5rem;">0,00</td>
                            <td id="totalRejectQtyDisplay" style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #dc2626; padding-right: 0.5rem;">0,00</td>
                            <td id="totalNettoQtyDisplay" style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #0f172a; padding-right: 0.5rem;">0,00</td>
                            <td style="text-align: center; color: #64748b; font-size: 0.75rem;">Total</td>
                            <td></td>
                            <td style="display: none;" id="totalTerimaNilaiDisplay">Rp 0</td>
                            <td style="display: none;" id="totalTerimaDiskonDisplay">-</td>
                            <td style="display: none;" id="totalTerimaPotonganDisplay">-</td>
                            <td style="display: none;" id="totalTerimaPpnDisplay">-</td>
                        </tr>
                    </tfoot>
                </table>
                @include('gudang.terima.partials.item-row-template')
            </div>

            <div style="padding: 0.55rem 0.85rem; background: #ffffff; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn-add-row-wide" onclick="addTerimaRow(true)" style="max-width: 320px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Tambah Baris Baru</span>
                </button>
            </div>
        </div>

        {{-- 4. BOTTOM SECTION: CATATAN & FINANCIAL/PHYSICAL STACK --}}
        <div class="po-bottom-grid">
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                <div class="po-notes-card">
                    <label for="catatan_lengkap" class="po-field-label" style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Catatan Fisik Penerimaan &amp; Berita Acara</span>
                        <small style="font-weight: normal; color: #64748b;">Kondisi muatan &amp; armada</small>
                    </label>
                    <textarea id="catatan_lengkap" name="catatan_lengkap_display" rows="3" class="form-control"
                        placeholder="Tuliskan catatan kondisi muatan, kadar air, basah karena hujan, hasil sortir, atau catatan operasional lainnya..."
                        style="border: 1.5px solid #cbd5e1; border-radius: 5px; font-size: 0.8125rem; padding: 0.5rem;"
                        oninput="document.getElementById('catatan_txt').value = this.value">{{ old('catatan_txt', $terima->catatan_txt) }}</textarea>
                </div>

                <div class="po-notes-card" style="border-left: 3px solid #0f172a;">
                    <label for="alasan_edit" class="po-field-label" style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Alasan Perubahan / Revisi Dokumen</span>
                        <small style="font-weight: normal; color: #64748b;">Audit Trail Log</small>
                    </label>
                    <input type="text" id="alasan_edit" name="alasan_edit" value="{{ old('alasan_edit') }}"
                        class="form-control" placeholder="Contoh: Koreksi selisih timbangan bruto, revisi nomor batch fisik supplier..."
                        style="border: 1.5px solid #cbd5e1; border-radius: 5px; font-size: 0.8125rem; padding: 0.45rem 0.65rem;">
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.85rem; font-size: 0.725rem; color: #64748b; line-height: 1.4;">
                    <strong style="color: #334155;">Tips Pengeditan:</strong> Perubahan kuantitas fisik dan nomor batch akan otomatis menyesuaikan kembali saldo stok gudang dan mutasi kartu stok.
                </div>
            </div>

            <div class="po-financial-card">
                <div style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.65rem; display: flex; justify-content: space-between; align-items: center;">
                    <span>Ringkasan Fisik Penerimaan</span>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.675rem; font-weight: 700; padding: 2px 6px;">Fisik Gudang</span>
                </div>

                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Bruto Timbangan:</span>
                    <strong style="color: #0284c7; font-family: monospace; font-size: 0.95rem; white-space: nowrap; flex-shrink: 0;">
                        <span id="sideTotalBruto">0,00 KG</span>
                    </strong>
                </div>

                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Afkir / Reject:</span>
                    <strong style="color: #dc2626; font-family: monospace; font-size: 0.95rem; white-space: nowrap; flex-shrink: 0;">
                        <span id="sideTotalAfkir">0,00 KG</span>
                    </strong>
                </div>

                <div class="po-financial-row" style="background: #f0fdf4; margin: 0.2rem -0.4rem; padding: 0.35rem 0.4rem; border-radius: 4px;">
                    <span style="color: #14532d; font-weight: 700;">Total Netto Masuk (Stok):</span>
                    <strong style="color: #047857; font-family: monospace; font-size: 1.05rem; white-space: nowrap; flex-shrink: 0;">
                        <span id="sideTotalNetto">0,00 KG</span>
                    </strong>
                </div>



                {{-- Hidden financial elements for backward compatibility --}}
                <div id="sideGrandTotal" style="display: none;">Rp 0</div>
                <div id="rowSubtotalBruto" style="display: none;"><span id="sideSubtotalBruto">Rp 0</span></div>
                <div id="rowDiskon" style="display: none;"><span id="sideTotalDiskon">Rp 0</span></div>
                <div id="rowPotongan" style="display: none;"><span id="sideTotalPotongan">Rp 0</span></div>
                <div id="rowDpp" style="display: none;"><strong id="sideTotalDpp">Rp 0</strong></div>
                <div id="rowPpn" style="display: none;"><span id="sideTotalPpn">Rp 0</span></div>

                <span id="barTotalNetto" style="display: none;">0,00</span>
                <span id="barTotalReject" style="display: none;">0,00</span>
                <span id="barTotalItems" style="display: none;">0</span>
                <span id="barGrandTotal" style="display: none;">Rp 0</span>
                <input type="hidden" name="potongan_nominal" id="global_potongan" value="{{ old('potongan_nominal', (float) $terima->potongan_nominal) }}">

                <div class="po-action-bar">
                    <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn-pill-outline">
                        Batal
                    </a>
                    <button type="button" id="btnSubmitTerima" class="btn-pill-dark" onclick="submitTerimaEditForm(event)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        <span id="btnSubmitTerimaText">Simpan Perubahan Penerimaan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @include('gudang.terima.partials.modal-supplier-picker')
    @include('gudang.terima.partials.modal-qc-picker')
    @include('gudang.terima.partials.modal-confirm-previous-month')
    @include('gudang.terima.partials.modal-confirm-po-discrepancy')

    @php
        $suppliersJson = $supplierList->map(function($sup) {
            return [
                'id' => $sup->supplier_id,
                'code' => $sup->supplier_cd,
                'name' => $sup->supplier_nm,
                'category' => $sup->jenisSupplier?->jenis_supplier_cd ?? 'OTHER',
                'categoryName' => $sup->jenisSupplier?->jenis_supplier_nm ?? 'Lainnya',
                'contact' => $sup->kontak_no ?? '-',
                'address' => $sup->alamat_txt ?? '',
            ];
        })->values();
    @endphp

    @push('scripts')
    <script>
        window.isEditMode = true;
        window.terimaId = {{ $terima->terima_id }};
        window.suppliersData = @json($suppliersJson);
        window.qcSiapGudangUrl = "{{ route('qc.inbound.siap_gudang') }}";
        window.terimaCreateUrl = "{{ route('gudang.terima.create') }}";
        window.terimaShowUrl = "{{ route('gudang.terima.show', $terima->terima_id) }}";
        window.oldSupplierId = {{ old('supplier_id', $terima->supplier_id) }};
        window.selectedPoDetailsCount = {{ $terima->details->count() }};
    </script>
    <script src="{{ asset('js/gudang/terima/terima-edit.js') }}?v={{ filemtime(public_path('js/gudang/terima/terima-edit.js')) }}"></script>
    @endpush
@endsection
