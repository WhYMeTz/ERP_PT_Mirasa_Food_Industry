@extends('layouts.app')

@section('title', 'Catat Penerimaan Barang Fisik - ERP PT Mirasa')

@section('content')
<style>
    /* Chips Filter */
    .sup-filter-chip, .barang-filter-chip {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 0.725rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        line-height: 1.2;
    }
    .sup-filter-chip:hover, .barang-filter-chip:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .sup-filter-chip.active, .barang-filter-chip.active {
        background: #059669;
        border-color: #059669;
        color: #ffffff;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3);
    }

    /* Supplier Dropdown Items */
    .sup-option-item {
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.1s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sup-option-item:hover, .sup-option-item.highlighted {
        background: #f0fdf4;
    }
    .sup-option-item:last-child {
        border-bottom: none;
    }

    /* Excel Table Styling */
    .excel-grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }
    .excel-grid-table th {
        background: #0f172a;
        color: #f8fafc;
        font-weight: 600;
        padding: 0.55rem 0.45rem;
        border: 1px solid #334155;
        font-size: 0.775rem;
        letter-spacing: 0.02em;
    }
    .excel-grid-table td {
        padding: 0.3rem 0.4rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        vertical-align: middle;
    }
    .excel-grid-table tr:nth-child(even) td {
        background: #f8fafc;
    }
    .excel-grid-table tr:hover td {
        background: #f0fdf4;
    }
    .excel-grid-table .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 0.35rem 0.45rem !important;
        font-size: 0.825rem !important;
        height: 32px;
        box-sizing: border-box;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .excel-grid-table .form-control:focus {
        border-color: #059669 !important;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.25) !important;
        background: #ffffff !important;
    }
</style>

<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        &larr; Kembali ke Daftar Penerimaan
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Catat Penerimaan Barang Fisik (Goods Receipt / GRN)</h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Barang yang dicatat di formulir ini akan langsung menambah saldo fisik gudang, membuat nomor batch baru, dan dicatat pada Kartu Stok.</p>
</div>

<form action="{{ route('gudang.terima.store') }}" method="POST" id="formTerima">
    @csrf
    @if ($selectedPo)
        <input type="hidden" name="redirect_to" value="po">
    @endif

    {{-- KARTU 1: INFORMASI HEADER PENERIMAAN --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a; font-size: 1rem;">1. Dokumen Penerimaan &amp; Pengirim</strong>
        </div>
        <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="terima_no" class="form-label">Nomor Penerimaan <span style="color:#ef4444;">*</span></label>
                <input type="text" id="terima_no" name="terima_no" value="{{ old('terima_no', $nextTerimaNo ?? '') }}" class="form-control" style="background: #f8fafc; font-weight: 600;" required>
                <small style="color: #64748b; font-size: 0.75rem;">Nomor urut Good Receipt Note (GRN).</small>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="terima_tgl" class="form-label">Tanggal Masuk Fisik <span style="color:#ef4444;">*</span></label>
                <input type="date" id="terima_tgl" name="terima_tgl" value="{{ old('terima_tgl', date('Y-m-d')) }}" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="po_id" class="form-label">Referensi Purchase Order (Opsional)</label>
                <select id="po_id" name="po_id" class="form-control" onchange="onPoSelected(this.value)">
                    <option value="">-- Penerimaan Non-PO / Pembelian Langsung --</option>
                    @foreach ($openPoList as $po)
                        <option value="{{ $po->po_id }}" 
                            data-supplier="{{ $po->supplier_id }}" 
                            data-gudang="{{ $po->gudang_id }}"
                            {{ (old('po_id', $selectedPo?->po_id) == $po->po_id) ? 'selected' : '' }}>
                            {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} ({{ $po->status_cd }})
                        </option>
                    @endforeach
                </select>
                <small style="color: #64748b; font-size: 0.75rem;">Pilih PO untuk otomatis memuat daftar barang pesanan.</small>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="suratjalan_no" class="form-label">No Surat Jalan Supplier</label>
                <input type="text" id="suratjalan_no" name="suratjalan_no" value="{{ old('suratjalan_no') }}" class="form-control" placeholder="Contoh: SJ-2026/09/8812">
            </div>

            {{-- SUPPLIER MITRA DENGAN FILTER CHIPS & LIVE SEARCH (BEBAS SCROLL) --}}
            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0; background: #fafafa; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.875rem; margin-bottom: 0; color: #0f172a;">
                        Supplier Pengirim <span style="color:#ef4444;">*</span>
                    </label>
                    <span id="supplier_count_badge" style="font-size: 0.75rem; color: #64748b;">
                        Total: {{ $supplierList->count() }} supplier
                    </span>
                </div>

                {{-- Filter Chips Kategori Supplier --}}
                <div style="display: flex; gap: 0.35rem; margin-bottom: 0.6rem; flex-wrap: wrap;">
                    <button type="button" class="sup-filter-chip active" data-filter="ALL" onclick="filterSupplierCategory('ALL', this)">
                        Semua ({{ $supplierList->count() }})
                    </button>
                    <button type="button" class="sup-filter-chip" data-filter="RAW" onclick="filterSupplierCategory('RAW', this)">
                        🌾 Singkong / Bahan Baku ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'RAW')->count() }})
                    </button>
                    <button type="button" class="sup-filter-chip" data-filter="BUMBU" onclick="filterSupplierCategory('BUMBU', this)">
                        🧂 Bumbu &amp; Rasa ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BUMBU')->count() }})
                    </button>
                    <button type="button" class="sup-filter-chip" data-filter="KEMASAN" onclick="filterSupplierCategory('KEMASAN', this)">
                        📦 Kemasan &amp; Karton ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'KEMASAN')->count() }})
                    </button>
                    <button type="button" class="sup-filter-chip" data-filter="BP" onclick="filterSupplierCategory('BP', this)">
                        🏭 Penolong Industri ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BP')->count() }})
                    </button>
                </div>

                {{-- Card Konfirmasi Supplier Terpilih --}}
                <div id="selected_supplier_card" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.65rem 0.85rem; margin-bottom: 0.4rem; justify-content: space-between; align-items: center; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="background: #22c55e; color: #fff; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; font-weight: 700;">✓</div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                                <strong id="disp_sup_name" style="color: #14532d; font-size: 0.875rem;"></strong>
                                <span id="disp_sup_code" class="badge" style="background: #dcfce7; color: #166534; font-size: 0.7rem; font-weight: 700;"></span>
                                <span id="disp_sup_cat" class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.7rem;"></span>
                            </div>
                            <div id="disp_sup_contact" style="font-size: 0.75rem; color: #15803d; margin-top: 0.15rem;"></div>
                        </div>
                    </div>
                    <button type="button" onclick="clearSelectedSupplier()" class="btn btn-sm" style="background: #ffffff; border: 1px solid #86efac; color: #15803d; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.55rem; border-radius: 4px; cursor: pointer; white-space: nowrap;">
                        Ganti Supplier
                    </button>
                </div>

                {{-- Live Search Input --}}
                <div id="supplier_search_wrapper" style="position: relative;">
                    <div style="position: relative;">
                        <input type="text" id="supplier_search_input" class="form-control" placeholder="🔍 Ketik nama supplier atau kode (misal: Sawit, Singkong, SUP-001)..." autocomplete="off" style="padding-left: 2.1rem !important; height: 36px; font-size: 0.85rem;">
                        <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                    </div>

                    {{-- Dropdown Container --}}
                    <div id="supplier_dropdown_list" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); max-height: 250px; overflow-y: auto; z-index: 1050;">
                        {{-- Diisi dinamis via JS --}}
                    </div>
                </div>

                {{-- Hidden select yang tetap disinkronkan untuk form POST --}}
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
                            {{ (old('supplier_id', $selectedPo?->supplier_id) == $sup->supplier_id) ? 'selected' : '' }}>
                            {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                        </option>
                    @endforeach
                </select>
                <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.35rem;">Ketik nama rekanan atau klik tombol kategori di atas untuk memilih tanpa scroll.</small>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="gudang_id" class="form-label">Gudang Penyimpanan <span style="color:#ef4444;">*</span></label>
                <select id="gudang_id" name="gudang_id" class="form-control" required style="height: 36px;">
                    <option value="">-- Pilih Gudang Masuk --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $selectedPo?->gudang_id) == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="catatan_txt" class="form-label">Catatan Tambahan Penerimaan</label>
                <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control" placeholder="Contoh: Diterima dalam kondisi baik, kadar air singkong 18%, plat truk AB-1234-CD.">{{ old('catatan_txt') }}</textarea>
            </div>
        </div>
    </div>

    @if ($selectedPo)
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.875rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="font-size: 0.875rem; color: #334155;">
                <strong style="color: #0f172a;">PO Terpilih:</strong> <span style="font-family: monospace; font-weight: 700; color: #0284c7;">{{ $selectedPo->po_no }}</span> &bull; 
                Supplier: <strong>{{ $selectedPo->supplier?->supplier_nm }}</strong>
                @if ($selectedPo->status_cd == 'PARTIAL')
                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; margin-left: 0.35rem; font-size: 0.75rem;">Sebagian Diterima</span>
                @else
                    <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:700; margin-left: 0.35rem; font-size: 0.75rem;">Terbuka Penuh</span>
                @endif
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="button" onclick="copyAllRemainingPoQty()" class="btn btn-sm" style="background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.775rem; padding: 0.35rem 0.75rem; border-radius: 6px;" title="Isi kuantitas terima dengan seluruh sisa PO">
                    ⚡ Salin Semua Sisa PO
                </button>
                <button type="button" onclick="clearAllTerimaQty()" class="btn btn-secondary btn-sm" style="font-weight: 600; font-size: 0.775rem; padding: 0.35rem 0.65rem;" title="Kosongkan kuantitas agar bisa diketik manual">
                    🧹 Kosongkan Qty
                </button>
            </div>
        </div>
    @endif

    {{-- KARTU 2: DETAIL BARANG & NOMOR BATCH (INVENTORY ENGINE - EXCEL STYLE) --}}
    <div class="card" style="margin-bottom: 1.5rem; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e1; padding: 0.75rem 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <strong style="color: #0f172a; font-size: 1rem;">2. Fisik Barang Diterima &amp; Alokasi Batch</strong>
                <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                    💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk berpindah baris layaknya Excel
                </span>
            </div>
            <button type="button" onclick="addTerimaRow(true)" class="btn btn-primary btn-sm" style="font-weight: 600; background: #059669; padding: 0.4rem 0.85rem;">
                + Tambah Baris (Enter)
            </button>
        </div>

        {{-- FILTER BAR CEPAT UNTUK KATEGORI BARANG --}}
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.6rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.03em; margin-right: 0.25rem;">
                    Filter Kategori Barang:
                </span>
                <button type="button" class="barang-filter-chip active" data-category="ALL" onclick="filterBarangCategory('ALL', this)">
                    Semua ({{ $barangList->count() }})
                </button>
                <button type="button" class="barang-filter-chip" data-category="BB" onclick="filterBarangCategory('BB', this)">
                    🌾 Bahan Baku Mentah ({{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']))->count() }})
                </button>
                <button type="button" class="barang-filter-chip" data-category="BUMBU" onclick="filterBarangCategory('BUMBU', this)">
                    🧂 Bumbu &amp; Penolong ({{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))->count() }})
                </button>
                <button type="button" class="barang-filter-chip" data-category="PACK" onclick="filterBarangCategory('PACK', this)">
                    📦 Kemasan &amp; Packaging ({{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)))->count() }})
                </button>
            </div>
            <div style="font-size: 0.725rem; color: #059669; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Hanya menampilkan Bahan Masuk Supplier (WIP &amp; FG tidak ditampilkan)</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="excel-grid-table" id="tableTerimaItems" style="width: 100%; min-width: 1050px;">
                <thead>
                    <tr>
                        <th style="width: 32px; text-align: center;">No</th>
                        <th style="min-width: 220px; text-align: left;">Nama Komoditas / Barang <span style="color:#ef4444;">*</span></th>
                        <th style="width: 160px; text-align: left;">Nomor Batch Fisik <span style="color:#ef4444;">*</span></th>
                        <th style="width: 120px; text-align: center;">Tgl Expired</th>
                        <th style="width: 95px; text-align: center;">Grade Mutu</th>
                        <th style="width: 110px; text-align: right;">Qty Masuk (Bruto) <span style="color:#ef4444;">*</span></th>
                        <th style="width: 95px; text-align: right;">Afkir (Reject)</th>
                        <th style="width: 105px; text-align: right; background: #064e3b;">Netto Bersih</th>
                        <th style="width: 60px; text-align: center;">Satuan</th>
                        <th style="width: 115px; text-align: right;">Harga Satuan (Rp)</th>
                        <th style="width: 40px; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="terimaItemsContainer">
                    @if ($selectedPo && $selectedPo->details->isNotEmpty())
                        {{-- Prefilled items from selected PO --}}
                        @php $prefilledBatches = []; @endphp
                        @foreach ($selectedPo->details as $idx => $pdtl)
                            @if ((float) $pdtl->sisa_qty > 0)
                                @php
                                    $batchVal = app(\App\Services\Common\CodeGeneratorService::class)->generateBatchNo(
                                        $pdtl->barang?->barang_cd, 
                                        old('terima_tgl', date('Y-m-d')), 
                                        $pdtl->barang?->barang_nm,
                                        $prefilledBatches
                                    );
                                    $prefilledBatches[] = $batchVal;
                                @endphp
                                <tr class="terima-row" data-index="{{ $idx }}" data-sisa="{{ (float) $pdtl->sisa_qty }}">
                                    <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">{{ $idx + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $pdtl->podtl_id }}">
                                        <input type="hidden" name="items[{{ $idx }}][barang_id]" value="{{ $pdtl->barang_id }}" class="item-barang-id">
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">{{ $pdtl->barang?->barang_nm }}</strong>
                                        <span style="font-size: 0.725rem; color: #64748b;">
                                            Pesanan: {{ number_format((float) $pdtl->pesan_qty, 2) }} | <strong>Sisa: {{ number_format((float) $pdtl->sisa_qty, 2) }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ old("items.{$idx}.batch_no", $batchVal) }}" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7;" required>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $idx }}][expired_tgl]" class="form-control">
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][grade_cd]" class="form-control">
                                            <option value="A" selected>Grade A Super</option>
                                            <option value="B">Grade B Standar</option>
                                            <option value="REJECT">Reject / Afkir</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" max="{{ $pdtl->sisa_qty }}" name="items[{{ $idx }}][terima_qty]" value="{{ (float) $pdtl->sisa_qty }}" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                                        {{ number_format((float) $pdtl->sisa_qty, 2, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-weight: 700; color: #475569; font-size: 0.8rem;">{{ $pdtl->barang?->satuanDasar?->satuan_nm ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][harga_nominal]" value="{{ (float) $pdtl->harga_nominal }}" class="form-control item-harga" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        {{-- Row Kosong Default --}}
                        <tr class="terima-row" data-index="0" data-sisa="0">
                            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">1</td>
                            <td>
                                <select name="items[0][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
                                    <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                                    
                                    <optgroup label="🌾 BAHAN BAKU (KOMODITAS UTAMA)" class="grp-bb">
                                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                                            <option value="{{ $b->barang_id }}" 
                                                    data-category="BB"
                                                    data-cd="{{ $b->barang_cd }}" 
                                                    data-nm="{{ $b->barang_nm }}"
                                                    data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                    data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                            </option>
                                        @endforeach
                                    </optgroup>

                                    <optgroup label="🧂 BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                                            <option value="{{ $b->barang_id }}" 
                                                    data-category="BUMBU"
                                                    data-cd="{{ $b->barang_cd }}" 
                                                    data-nm="{{ $b->barang_nm }}"
                                                    data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                    data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                            </option>
                                        @endforeach
                                    </optgroup>

                                    <optgroup label="📦 BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                                            <option value="{{ $b->barang_id }}" 
                                                    data-category="PACK"
                                                    data-cd="{{ $b->barang_cd }}" 
                                                    data-nm="{{ $b->barang_nm }}"
                                                    data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                    data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[0][batch_no]" id="batch_0" value="" placeholder="Otomatis saat barang dipilih" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7;" required>
                            </td>
                            <td>
                                <input type="date" name="items[0][expired_tgl]" class="form-control">
                            </td>
                            <td>
                                <select name="items[0][grade_cd]" class="form-control">
                                    <option value="A" selected>Grade A Super</option>
                                    <option value="B">Grade B Standar</option>
                                    <option value="REJECT">Reject / Afkir</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0" name="items[0][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0" name="items[0][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                                1,00
                            </td>
                            <td style="text-align: center;">
                                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">-</span>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                            </td>
                            <td style="text-align: center;">
                                <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                        <td colspan="5" style="text-align: right; padding: 0.65rem 0.75rem; color: #334155;">
                            Ringkasan Timbangan Fisik Masuk:
                        </td>
                        <td id="totalBrutoQtyDisplay" style="padding: 0.65rem 0.45rem; color: #0284c7; font-size: 0.95rem; text-align: right;">
                            0.00
                        </td>
                        <td id="totalRejectQtyDisplay" style="padding: 0.65rem 0.45rem; color: #dc2626; font-size: 0.95rem; text-align: right;">
                            0.00
                        </td>
                        <td id="totalNettoQtyDisplay" style="padding: 0.65rem 0.45rem; color: #047857; font-size: 1.05rem; text-align: right; background: #dcfce7; font-weight: 800;">
                            0.00
                        </td>
                        <td style="text-align: center; color: #64748b; font-size: 0.75rem;">Total Nilai:</td>
                        <td id="totalTerimaNilaiDisplay" style="text-align: right; padding: 0.65rem 0.45rem; font-size: 0.95rem; color: #0284c7; font-family: monospace;">
                            Rp 0
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- TOMBOL AKSI FORM --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <span style="font-size: 0.825rem; color: #64748b;">
            ⚠️ Saldo fisik gudang akan langsung bertambah dan dicatat pada Kartu Stok setelah formulir ini disimpan.
        </span>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('gudang.terima.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" style="background:#059669; padding: 0.65rem 1.75rem; font-size: 0.95rem; font-weight: 700;">
                ✓ Simpan &amp; Masukkan ke Stok Gudang
            </button>
        </div>
    </div>
</form>

<script>
    // =========================================================================
    // 1. DATA SUPPLIER & FILTER PENCARIAN
    // =========================================================================
    const suppliersData = [
        @foreach ($supplierList as $sup)
            {
                id: {{ $sup->supplier_id }},
                code: '{{ addslashes($sup->supplier_cd) }}',
                name: '{{ addslashes($sup->supplier_nm) }}',
                category: '{{ $sup->jenisSupplier?->jenis_supplier_cd ?? "OTHER" }}',
                categoryName: '{{ addslashes($sup->jenisSupplier?->jenis_supplier_nm ?? "Lainnya") }}',
                contact: '{{ addslashes($sup->kontak_no ?? "-") }}',
                address: '{{ addslashes($sup->alamat_txt ?? "") }}'
            },
        @endforeach
    ];

    let activeSupplierCategory = 'ALL';
    let highlightedSupIndex = -1;

    const supSearchInput = document.getElementById('supplier_search_input');
    const supDropdownList = document.getElementById('supplier_dropdown_list');
    const supHiddenSelect = document.getElementById('supplier_id');
    const selectedSupCard = document.getElementById('selected_supplier_card');
    const supSearchWrapper = document.getElementById('supplier_search_wrapper');

    function filterSupplierCategory(cat, btn) {
        activeSupplierCategory = cat;
        document.querySelectorAll('.sup-filter-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');

        renderSupplierDropdown(supSearchInput.value);
        supDropdownList.style.display = 'block';
        supSearchInput.focus();
    }

    function renderSupplierDropdown(query = '') {
        const q = query.trim().toLowerCase();
        let filtered = suppliersData.filter(s => {
            const matchesCat = (activeSupplierCategory === 'ALL' || s.category === activeSupplierCategory);
            const matchesText = !q || s.name.toLowerCase().includes(q) || s.code.toLowerCase().includes(q);
            return matchesCat && matchesText;
        });

        document.getElementById('supplier_count_badge').innerText = `Menampilkan ${filtered.length} supplier`;

        if (filtered.length === 0) {
            supDropdownList.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                    Tidak ditemukan supplier yang sesuai dengan pencarian "<strong>${escapeHtml(query)}</strong>".
                </div>
            `;
            return;
        }

        let html = '';
        filtered.forEach((s, idx) => {
            let catColor = '#0284c7';
            let catBg = '#e0f2fe';
            if (s.category === 'RAW') { catColor = '#15803d'; catBg = '#dcfce7'; }
            else if (s.category === 'BUMBU') { catColor = '#b45309'; catBg = '#fef3c7'; }
            else if (s.category === 'KEMASAN') { catColor = '#6b21a8'; catBg = '#f3e8ff'; }

            html += `
                <div class="sup-option-item" data-id="${s.id}" data-idx="${idx}" onclick="selectSupplier(${s.id})">
                    <div>
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                            ${escapeHtml(s.name)}
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.15rem; font-size: 0.725rem; color: #64748b;">
                            <span style="font-family: monospace; font-weight: 600;">${s.code}</span>
                            ${s.contact && s.contact !== '-' ? `<span>&bull; Telp: ${escapeHtml(s.contact)}</span>` : ''}
                        </div>
                    </div>
                    <span class="badge" style="background: ${catBg}; color: ${catColor}; font-size: 0.7rem; font-weight: 600; white-space: nowrap; margin-left: 0.5rem;">
                        ${escapeHtml(s.categoryName)}
                    </span>
                </div>
            `;
        });

        supDropdownList.innerHTML = html;
        highlightedSupIndex = -1;
    }

    function selectSupplier(supplierId) {
        const sup = suppliersData.find(s => s.id == supplierId);
        if (!sup) return;

        supHiddenSelect.value = sup.id;

        document.getElementById('disp_sup_name').innerText = sup.name;
        document.getElementById('disp_sup_code').innerText = sup.code;
        document.getElementById('disp_sup_cat').innerText = sup.categoryName;
        document.getElementById('disp_sup_contact').innerText = (sup.contact && sup.contact !== '-' ? 'Telp: ' + sup.contact : '') + (sup.address ? ' | ' + sup.address : '');

        selectedSupCard.style.display = 'flex';
        supSearchWrapper.style.display = 'none';
        supDropdownList.style.display = 'none';

        // Auto filter barang kategori sesuai supplier jika relevan
        if (sup.category === 'RAW') {
            const bbChip = document.querySelector('.barang-filter-chip[data-category="BB"]');
            if (bbChip) filterBarangCategory('BB', bbChip);
        } else if (sup.category === 'BUMBU') {
            const bumbuChip = document.querySelector('.barang-filter-chip[data-category="BUMBU"]');
            if (bumbuChip) filterBarangCategory('BUMBU', bumbuChip);
        } else if (sup.category === 'KEMASAN') {
            const packChip = document.querySelector('.barang-filter-chip[data-category="PACK"]');
            if (packChip) filterBarangCategory('PACK', packChip);
        }
    }

    function clearSelectedSupplier() {
        supHiddenSelect.value = '';
        selectedSupCard.style.display = 'none';
        supSearchWrapper.style.display = 'block';
        supSearchInput.value = '';
        renderSupplierDropdown('');
        supDropdownList.style.display = 'block';
        supSearchInput.focus();
    }

    // Search input listeners
    supSearchInput.addEventListener('input', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    supSearchInput.addEventListener('focus', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    // Keyboard navigation
    supSearchInput.addEventListener('keydown', function(e) {
        const items = supDropdownList.querySelectorAll('.sup-option-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedSupIndex = (highlightedSupIndex + 1) % items.length;
            updateHighlightedSupItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedSupIndex = (highlightedSupIndex - 1 + items.length) % items.length;
            updateHighlightedSupItem(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedSupIndex >= 0 && items[highlightedSupIndex]) {
                const id = items[highlightedSupIndex].dataset.id;
                selectSupplier(id);
            }
        } else if (e.key === 'Escape') {
            supDropdownList.style.display = 'none';
        }
    });

    function updateHighlightedSupItem(items) {
        items.forEach((item, idx) => {
            if (idx === highlightedSupIndex) {
                item.classList.add('highlighted');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('highlighted');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!supSearchWrapper.contains(e.target) && !e.target.classList.contains('sup-filter-chip')) {
            supDropdownList.style.display = 'none';
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Prefill selected supplier if available (from old input or selected PO)
    @php
        $initSupplierId = old('supplier_id', $selectedPo?->supplier_id);
    @endphp
    @if($initSupplierId)
        selectSupplier({{ $initSupplierId }});
    @endif


    // =========================================================================
    // 2. FILTER KATEGORI BARANG PADA TABEL GRN
    // =========================================================================
    let activeBarangCategory = 'ALL';

    function filterBarangCategory(category, btn) {
        activeBarangCategory = category;
        document.querySelectorAll('.barang-filter-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');

        document.querySelectorAll('.item-barang').forEach(select => {
            applyBarangCategoryFilterToSelect(select, category);
        });
    }

    function applyBarangCategoryFilterToSelect(selectElem, category) {
        const grpBb = selectElem.querySelector('.grp-bb');
        const grpBumbu = selectElem.querySelector('.grp-bumbu');
        const grpPack = selectElem.querySelector('.grp-pack');

        if (category === 'ALL') {
            if (grpBb) grpBb.style.display = '';
            if (grpBumbu) grpBumbu.style.display = '';
            if (grpPack) grpPack.style.display = '';
        } else if (category === 'BB') {
            if (grpBb) grpBb.style.display = '';
            if (grpBumbu) grpBumbu.style.display = 'none';
            if (grpPack) grpPack.style.display = 'none';
        } else if (category === 'BUMBU') {
            if (grpBb) grpBb.style.display = 'none';
            if (grpBumbu) grpBumbu.style.display = '';
            if (grpPack) grpPack.style.display = 'none';
        } else if (category === 'PACK') {
            if (grpBb) grpBb.style.display = 'none';
            if (grpBumbu) grpBumbu.style.display = 'none';
            if (grpPack) grpPack.style.display = '';
        }
    }


    // =========================================================================
    // 3. LOGIKA BARIS PENERIMAAN BARANG, BATCH GENERATOR & KALKULASI
    // =========================================================================
    let terimaRowIndex = {{ $selectedPo ? count($selectedPo->details) : 1 }};

    function onPoSelected(poId) {
        if (!poId) {
            window.location.href = "{{ route('gudang.terima.create') }}";
            return;
        }
        window.location.href = "{{ route('gudang.terima.create') }}?po_id=" + poId;
    }

    // ⚡ Salin Semua Sisa PO langsung ke kolom Kuantitas Terima
    function copyAllRemainingPoQty() {
        document.querySelectorAll('.terima-row').forEach(row => {
            const sisa = parseFloat(row.dataset.sisa || 0);
            if (sisa > 0) {
                const qtyInput = row.querySelector('.item-terima-qty');
                if (qtyInput) qtyInput.value = sisa;
            }
        });
        calculateTotalTerima();
    }

    // 🧹 Kosongkan Semua Kuantitas agar bisa diketik manual sesuai fisik yang datang
    function clearAllTerimaQty() {
        document.querySelectorAll('.item-terima-qty').forEach(input => {
            input.value = 0;
        });
        calculateTotalTerima();
    }

    function addTerimaRow(focusNew = false) {
        const container = document.getElementById('terimaItemsContainer');
        const tr = document.createElement('tr');
        tr.className = 'terima-row';
        tr.dataset.index = terimaRowIndex;
        tr.dataset.sisa = 0;

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">${terimaRowIndex + 1}</td>
            <td>
                <select name="items[${terimaRowIndex}][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
                    <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                    
                    <optgroup label="🌾 BAHAN BAKU (KOMODITAS UTAMA)" class="grp-bb">
                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                            <option value="{{ $b->barang_id }}" 
                                    data-category="BB"
                                    data-cd="{{ $b->barang_cd }}" 
                                    data-nm="{{ $b->barang_nm }}"
                                    data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                    data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="🧂 BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                            <option value="{{ $b->barang_id }}" 
                                    data-category="BUMBU"
                                    data-cd="{{ $b->barang_cd }}" 
                                    data-nm="{{ $b->barang_nm }}"
                                    data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                    data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="📦 BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                        @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                            <option value="{{ $b->barang_id }}" 
                                    data-category="PACK"
                                    data-cd="{{ $b->barang_cd }}" 
                                    data-nm="{{ $b->barang_nm }}"
                                    data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                    data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </td>
            <td>
                <input type="text" name="items[${terimaRowIndex}][batch_no]" value="" placeholder="Otomatis saat barang dipilih" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7;" required>
            </td>
            <td>
                <input type="date" name="items[${terimaRowIndex}][expired_tgl]" class="form-control">
            </td>
            <td>
                <select name="items[${terimaRowIndex}][grade_cd]" class="form-control">
                    <option value="A" selected>Grade A Super</option>
                    <option value="B">Grade B Standar</option>
                    <option value="REJECT">Reject / Afkir</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.0001" min="0" name="items[${terimaRowIndex}][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
            </td>
            <td>
                <input type="number" step="0.0001" min="0" name="items[${terimaRowIndex}][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
            </td>
            <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                1,00
            </td>
            <td style="text-align: center;">
                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">-</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${terimaRowIndex}][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
            </td>
            <td style="text-align: center;">
                <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
            </td>
        `;

        container.appendChild(tr);
        terimaRowIndex++;
        updateTerimaRowNumbers();
        attachExcelKeyboardEventsTerima(tr);
        calculateTotalTerima();

        // Terapkan filter kategori barang yang aktif
        const select = tr.querySelector('.item-barang');
        applyBarangCategoryFilterToSelect(select, activeBarangCategory);

        if (focusNew) {
            select.focus();
        }
    }

    function removeTerimaRow(btn) {
        const rows = document.querySelectorAll('.terima-row');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 baris item barang yang diterima.');
            return;
        }
        btn.closest('tr').remove();
        updateTerimaRowNumbers();
        calculateTotalTerima();
    }

    function updateTerimaRowNumbers() {
        document.querySelectorAll('.terima-row').forEach((row, idx) => {
            row.querySelector('.row-num').innerText = idx + 1;
        });
    }

    async function updateTerimaSatuanAndBatch(selectElem) {
        const row = selectElem.closest('tr');
        const selectedOption = selectElem.options ? selectElem.options[selectElem.selectedIndex] : null;
        const barangId = selectElem.value;
        if (!barangId) {
            const batchInput = row.querySelector('.item-batch');
            if (batchInput) {
                batchInput.value = '';
                batchInput.placeholder = 'Otomatis saat barang dipilih';
            }
            const satuanSpan = row.querySelector('.row-satuan');
            if (satuanSpan) satuanSpan.innerText = '-';
            const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');
            if (hargaInput) hargaInput.value = 0;
            calculateTotalTerima();
            return;
        }

        const satuan = selectedOption?.dataset?.satuan || '-';
        const defaultHarga = parseFloat(selectedOption?.dataset?.harga || 0);

        const satuanSpan = row.querySelector('.row-satuan');
        if (satuanSpan && satuan !== '-') satuanSpan.innerText = satuan;

        // Ambil tanggal dari input terima_tgl
        const tglInput = document.getElementById('terima_tgl');
        const tglMasuk = tglInput?.value || '';

        // Otomatis generate nomor batch via AJAX
        const batchInput = row.querySelector('.item-batch');
        if (batchInput) {
            batchInput.value = 'Membuat batch...';
            try {
                const url = `{{ route('ajax.generate_code') }}?type=batch_no&barang_id=${barangId}&date=${tglMasuk}`;
                const resp = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await resp.json();
                if (data.status === 'success' && data.code) {
                    batchInput.value = data.code;
                }
            } catch (err) {
                console.error('Gagal mengambil nomor batch berikutnya:', err);
            }
        }

        const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');
        if (hargaInput && defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
            hargaInput.value = defaultHarga;
        }
        calculateTotalTerima();
    }

    // Ketika tanggal penerimaan diubah, sinkronkan otomatis seluruh nomor batch di tabel
    document.getElementById('terima_tgl')?.addEventListener('change', async function() {
        const rows = document.querySelectorAll('.terima-row');
        for (const row of rows) {
            const selectElem = row.querySelector('.item-barang') || row.querySelector('.item-barang-id');
            if (selectElem && selectElem.value) {
                await updateTerimaSatuanAndBatch(selectElem);
            }
        }
    });

    // Inisialisasi awal saat halaman selesai dimuat: jika ada baris yang sudah terpilih barangnya, ambil batch berikutnya
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.terima-row').forEach(row => {
            const selectElem = row.querySelector('.item-barang');
            if (selectElem && selectElem.value) {
                updateTerimaSatuanAndBatch(selectElem);
            }
        });
    });

    // Kalkulasi Lengkap Timbangan Pabrik: Bruto, Reject/Afkir, Netto Bersih, dan Nilai Fisik
    function calculateTotalTerima() {
        let totalBruto = 0;
        let totalReject = 0;
        let totalNilai = 0;

        document.querySelectorAll('.terima-row').forEach(row => {
            const terimaInput = row.querySelector('.item-terima-qty');
            const rejectInput = row.querySelector('.item-reject-qty');
            const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');

            const terimaQty = parseFloat(terimaInput?.value) || 0;
            const rejectQty = parseFloat(rejectInput?.value) || 0;
            const hargaNominal = parseFloat(hargaInput?.value) || 0;

            const netto = Math.max(0, terimaQty - rejectQty);

            const nettoSpan = row.querySelector('.row-netto');
            if (nettoSpan) {
                nettoSpan.innerText = netto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            totalBruto += terimaQty;
            totalReject += rejectQty;
            totalNilai += (terimaQty * hargaNominal);
        });

        const totalNetto = Math.max(0, totalBruto - totalReject);

        const brutoDisplay = document.getElementById('totalBrutoQtyDisplay');
        const rejectDisplay = document.getElementById('totalRejectQtyDisplay');
        const nettoDisplay = document.getElementById('totalNettoQtyDisplay');
        const nilaiDisplay = document.getElementById('totalTerimaNilaiDisplay');

        if (brutoDisplay) brutoDisplay.innerText = totalBruto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (rejectDisplay) rejectDisplay.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (nettoDisplay) nettoDisplay.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (nilaiDisplay) nilaiDisplay.innerText = 'Rp ' + totalNilai.toLocaleString('id-ID');
    }

    // Excel Keyboard Navigation untuk Penerimaan Barang
    function attachExcelKeyboardEventsTerima(rowElement) {
        const inputs = rowElement.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const allRows = Array.from(document.querySelectorAll('.terima-row'));
                    const currentRowIdx = allRows.indexOf(rowElement);

                    if (input.classList.contains('item-terima-qty') || input.classList.contains('item-reject-qty') || input.classList.contains('item-harga')) {
                        if (currentRowIdx === allRows.length - 1) {
                            addTerimaRow(true);
                        } else {
                            const nextRow = allRows[currentRowIdx + 1];
                            const targetClass = input.classList[1] || input.classList[0];
                            const target = nextRow.querySelector('.' + targetClass);
                            if (target) target.focus();
                        }
                    }
                }
            });
        });
    }

    document.querySelectorAll('.terima-row').forEach(r => attachExcelKeyboardEventsTerima(r));
    calculateTotalTerima();

    function fillAllSisaCreate() {
        copyAllRemainingPoQty();
    }

    function clearAllInputsCreate() {
        clearAllTerimaQty();
    }
</script>
@endsection
