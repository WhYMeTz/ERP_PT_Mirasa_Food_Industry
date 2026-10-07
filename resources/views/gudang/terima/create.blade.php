@extends('layouts.app')

@section('title', 'Catat Penerimaan Barang Fisik - ERP PT Mirasa')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-form.css') }}">
@endpush

<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Penerimaan
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Catat Penerimaan Barang Fisik (Goods Receipt / GRN)</h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">Barang yang dicatat di formulir ini akan langsung menambah saldo fisik gudang, membuat nomor batch baru, dan dicatat pada Kartu Stok.</p>
</div>

{{-- BANNER INTEGRASI TIKET QC --}}
<div id="qcIntegrationBanner" style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border: 1.5px solid #0284c7; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.08);">
    <div style="display: flex; align-items: center; gap: 0.85rem;">
        <div style="width: 42px; height: 42px; border-radius: 10px; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; box-shadow: 0 2px 5px rgba(2, 132, 199, 0.3);">
            🔬
        </div>
        <div>
            <div style="font-weight: 800; font-size: 0.95rem; color: #0369a1;" id="qcBannerTitle">
                Sinkronisasi Inspeksi Mutu QC Lapangan
            </div>
            <div style="font-size: 0.8rem; color: #334155;" id="qcBannerSubtitle">
                Tarik hasil sampling kadar air, refraksi kotoran, dan timbangan truk yang telah diverifikasi tim QC.
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button type="button" onclick="openQcModal()" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 700; background: #0284c7; border: none; padding: 0.5rem 0.9rem; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
            ⚡ Tarik Data dari Tiket QC
        </button>
        <button type="button" id="btnDetachQc" onclick="detachQcTicket()" style="display: none; background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.45rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
            ✕ Lepas Tiket QC
        </button>
    </div>
</div>

<form action="{{ route('gudang.terima.store') }}" method="POST" id="formTerima">
    @csrf
    <input type="hidden" name="qc_id" id="input_qc_id" value="{{ old('qc_id', request('qc_id')) }}">
    @if ($selectedPo)
        <input type="hidden" name="redirect_to" value="po">
    @endif

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            {{-- KARTU 1: INFORMASI DOKUMEN & PENGIRIM --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Pengirim</strong>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    {{-- BARIS 1: NOMOR PENERIMAAN, TANGGAL MASUK, REFERENSI PO, NO SURAT JALAN --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="terima_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Nomor Penerimaan <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="terima_no" name="terima_no" value="{{ old('terima_no', $nextTerimaNo ?? '') }}" class="form-control" style="background: #f8fafc; font-weight: 600; height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor urut Good Receipt Note (GRN).</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="terima_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal Masuk Fisik <span style="color:#ef4444;">*</span></label>
                            <input type="date" id="terima_tgl" name="terima_tgl" value="{{ old('terima_tgl', date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Waktu kedatangan armada di pabrik.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="suratjalan_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No. Surat Jalan Supplier</label>
                            <input type="text" id="suratjalan_no" name="suratjalan_no" value="{{ old('suratjalan_no') }}" class="form-control" placeholder="Contoh: SJ-2026/09/88" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor surat jalan fisik supplier.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="po_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Referensi Purchase Order</label>
                            <select id="po_id" name="po_id" class="form-control" onchange="onPoSelected(this.value)" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                                <option value="">-- Non-PO / Pembelian Langsung --</option>
                                @foreach ($openPoList as $po)
                                    <option value="{{ $po->po_id }}" 
                                        data-supplier="{{ $po->supplier_id }}" 
                                        data-gudang="{{ $po->gudang_id }}"
                                        {{ (old('po_id', $selectedPo?->po_id) == $po->po_id) ? 'selected' : '' }}>
                                        {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} ({{ $po->status_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem;">Pilih PO untuk otomatis memuat barang.</small>
                        </div>
                    </div>

                    {{-- BARIS 2: SUPPLIER PENGIRIM & GUDANG TUJUAN MASUK --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; align-items: start;">
                        
                        {{-- Kolom Supplier Pengirim --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <label id="lblSupplierHeader" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0;">
                                    Supplier Pengirim <span style="color:#ef4444;">*</span>
                                </label>
                                <a href="javascript:void(0)" onclick="openSupplierModal()" style="font-size: 0.75rem; color: #0284c7; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;" title="Buka Daftar Supplier Lengkap">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Daftar Supplier ({{ $supplierList->count() }})
                                </a>
                            </div>

                            {{-- Card Tampilan Supplier Terpilih --}}
                            <div id="selected_supplier_card" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.45rem 0.75rem; justify-content: space-between; align-items: center; gap: 0.75rem; min-height: 38px; box-sizing: border-box;">
                                <div style="display: flex; align-items: center; gap: 0.55rem; min-width: 0;">
                                    <div style="background: #22c55e; color: #fff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; flex-shrink: 0; font-weight: 700;">✓</div>
                                    <div style="min-width: 0;">
                                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                            <strong id="disp_sup_name" style="color: #14532d; font-size: 0.85rem;"></strong>
                                            <span id="disp_sup_code" class="badge" style="background: #dcfce7; color: #166534; font-size: 0.7rem; font-weight: 700; font-family: monospace;"></span>
                                            <span id="disp_sup_cat_badge"></span>
                                        </div>
                                        <div id="disp_sup_contact" style="font-size: 0.7rem; color: #15803d; margin-top: 0.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelectedSupplier()" class="btn btn-sm" style="background: #ffffff; border: 1px solid #86efac; color: #15803d; font-size: 0.725rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 4px; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
                                    Ganti
                                </button>
                            </div>

                            <div id="supplier_search_wrapper" style="position: relative;">
                                <div style="position: relative; width: 100%;">
                                    <input type="text" id="supplier_search_input" class="form-control" placeholder="Cari nama atau kode supplier..." autocomplete="off" style="padding-left: 2.2rem !important; padding-right: 2rem !important; height: 38px; font-size: 0.85rem; border-radius: 6px; width: 100%; box-sizing: border-box;">
                                    <span style="position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.65rem;">
                                        ▼
                                    </span>
                                </div>

                                <div id="supplier_dropdown_list" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.06); max-height: 280px; overflow-y: auto; z-index: 1050;">
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
                                        {{ (old('supplier_id', $selectedPo?->supplier_id) == $sup->supplier_id) ? 'selected' : '' }}>
                                        {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Ketik untuk memilih cepat, atau klik <strong>Daftar Supplier</strong>.</small>
                        </div>

                        {{-- Kolom Perusahaan / Lokasi Penerima --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem;">
                                Perusahaan / Lokasi Penerima <span style="color:#ef4444;">*</span>
                            </label>
                            <select id="gudang_id" name="gudang_id" class="form-control" required style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                                <option value="">-- Pilih Perusahaan / Lokasi Penerima --</option>
                                @foreach ($gudangList as $gdg)
                                    <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $selectedPo?->gudang_id) == $gdg->gudang_id) ? 'selected' : '' }}>
                                        {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Entitas perusahaan / cabang tempat barang diterima.</small>
                        </div>
                    </div>

                    {{-- BARIS 3: CATATAN TAMBAHAN PENERIMAAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem;">
                            Catatan Tambahan Penerimaan
                        </label>
                        <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control" style="border-radius: 6px; font-size: 0.85rem;" placeholder="Contoh: Diterima dalam kondisi baik, kadar air 18%, plat truk AB-1234-CD.">{{ old('catatan_txt') }}</textarea>
                    </div>

                </div>
            </div>

            @if ($selectedPo)
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
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
                        <button type="button" onclick="copyAllRemainingPoQty()" class="btn btn-sm" style="background: #0284c7; color: #ffffff; font-weight: 700; font-size: 0.775rem; padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;" title="Isi kuantitas terima dengan seluruh sisa PO">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                            Salin Sisa PO
                        </button>
                        <button type="button" onclick="clearAllTerimaQty()" class="btn btn-secondary btn-sm" style="font-weight: 600; font-size: 0.775rem; padding: 0.35rem 0.65rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Kosongkan kuantitas agar bisa diketik manual">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Kosongkan Qty
                        </button>
                    </div>
                </div>
            @endif

            {{-- KARTU 2: DETAIL BARANG & NOMOR BATCH (INVENTORY ENGINE - EXCEL STYLE SATU BARIS) --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div class="card-header" style="background: #ffffff; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e1; padding: 0.875rem 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">2. Fisik Barang Diterima, Alokasi Batch &amp; Komersial</strong>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                            💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk berpindah baris layaknya Excel
                        </span>
                    </div>
                    <button type="button" onclick="addTerimaRow(true)" class="btn btn-primary btn-sm" style="font-weight: 600; background: #059669; padding: 0.4rem 0.85rem;">
                        + Tambah Baris
                    </button>
                </div>

                {{-- FILTER BAR CEPAT UNTUK KATEGORI BARANG --}}
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.55rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.65rem;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                        <span style="font-size: 0.725rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                            Kategori:
                        </span>
                        <div class="category-segmented-control" role="group" aria-label="Filter Kategori Barang">
                            <button type="button" class="category-segment-btn active" data-category="ALL" onclick="filterBarangCategory('ALL', this)">
                                <span>Semua</span>
                                <span class="category-segment-count">{{ $barangList->count() }}</span>
                            </button>
                            <button type="button" class="category-segment-btn" data-category="BB" onclick="filterBarangCategory('BB', this)">
                                <span>Bahan Baku</span>
                                <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']))->count() }}</span>
                            </button>
                            <button type="button" class="category-segment-btn" data-category="BUMBU" onclick="filterBarangCategory('BUMBU', this)">
                                <span>Bumbu &amp; Penolong</span>
                                <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))->count() }}</span>
                            </button>
                            <button type="button" class="category-segment-btn" data-category="PACK" onclick="filterBarangCategory('PACK', this)">
                                <span>Kemasan</span>
                                <span class="category-segment-count">{{ $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)))->count() }}</span>
                            </button>
                        </div>
                    </div>
                    <div style="font-size: 0.725rem; color: #059669; font-weight: 600; display: flex; align-items: center; gap: 0.25rem;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Hanya bahan masuk supplier (WIP &amp; FG tidak ditampilkan)</span>
                    </div>
                </div>

                <div>
                    <table class="excel-grid-table" id="tableTerimaItems" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 32px; text-align: center;">No</th>
                                <th style="min-width: 180px; text-align: left;">Nama Komoditas / Barang <span style="color:#ef4444;">*</span></th>
                                <th style="width: 140px; text-align: left;">Nomor Batch Fisik <span style="color:#ef4444;">*</span></th>
                                <th style="width: 110px; text-align: center;">Tgl Expired</th>
                                <th style="width: 90px; text-align: center;">Grade</th>
                                <th style="width: 85px; text-align: right;">Qty (Bruto) <span style="color:#ef4444;">*</span></th>
                                <th style="width: 70px; text-align: right;">Afkir</th>
                                <th style="width: 75px; text-align: right; background: #064e3b; color: #ffffff;">Netto</th>
                                <th style="width: 45px; text-align: center;">Satuan</th>
                                <th style="width: 95px; text-align: right;">Harga Satuan</th>
                                <th style="width: 60px; text-align: right;" title="Diskon dagang per item (%)">Disc (%)</th>
                                <th style="width: 85px; text-align: right;" title="Potongan nominal item (Rp)">Potongan</th>
                                <th style="width: 80px; text-align: center;" title="Pajak Pertambahan Nilai">PPN</th>
                                <th style="width: 95px; text-align: right; background: #0f172a; color: #ffffff;" title="Subtotal bersih setelah diskon, potongan & PPN">Subtotal</th>
                                <th style="width: 35px; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="terimaItemsContainer">
                            @if ($selectedPo && $selectedPo->details->isNotEmpty())
                                {{-- Prefilled items from selected PO --}}
                                @foreach ($selectedPo->details as $idx => $pdtl)
                                    @if ((float) $pdtl->sisa_qty > 0)
                                        @php
                                            $acronym = app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym(
                                                $pdtl->barang?->barang_nm, 
                                                $pdtl->barang?->barang_cd
                                            );
                                            $batchPrefix = ($acronym ?: 'BRG') . '-';
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
                                                <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ old("items.{$idx}.batch_no", $batchPrefix) }}" placeholder="{{ $batchPrefix }}... (ketik nomor batch dari faktur supplier)" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%;" required>
                                            </td>
                                            <td>
                                                <input type="date" name="items[{{ $idx }}][expired_tgl]" value="{{ old("items.{$idx}.expired_tgl") }}" class="form-control" style="font-size: 0.8rem;">
                                            </td>
                                            <td>
                                                <select name="items[{{ $idx }}][grade_cd]" class="form-control" style="font-size: 0.8rem;">
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
                                                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">{{ $pdtl->barang?->satuanDasar?->satuan_nm ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][harga_nominal]" value="{{ (float) $pdtl->harga_nominal }}" class="form-control item-harga" placeholder="0" style="text-align: right; font-weight: 600;" oninput="calculateTotalTerima()">
                                            </td>
                                            <td>
                                                <input type="number" step="0.1" min="0" max="100" name="items[{{ $idx }}][diskon_persen]" value="{{ old("items.{$idx}.diskon_persen", (float)($pdtl->diskon_persen ?? 0)) }}" class="form-control item-diskon" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][potongan_nominal]" value="{{ old("items.{$idx}.potongan_nominal", (float)($pdtl->potongan_nominal ?? 0)) }}" class="form-control item-potongan" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                            </td>
                                            <td>
                                                <select name="items[{{ $idx }}][ppn_tipe]" class="form-control item-ppn-tipe" onchange="calculateTotalTerima()" style="font-size: 0.775rem; font-weight: 600;">
                                                    <option value="NON_PPN" {{ old("items.{$idx}.ppn_tipe", $pdtl->ppn_tipe ?? 'NON_PPN') === 'NON_PPN' ? 'selected' : '' }}>Non (0%)</option>
                                                    <option value="PPN_11" {{ old("items.{$idx}.ppn_tipe", $pdtl->ppn_tipe ?? 'NON_PPN') === 'PPN_11' ? 'selected' : '' }}>PPN 11%</option>
                                                </select>
                                            </td>
                                            <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0f172a;" class="row-subtotal">
                                                Rp 0
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
                                            
                                            <optgroup label="BAHAN BAKU (RAW MATERIAL)" class="grp-bb">
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

                                            <optgroup label="BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
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

                                            <optgroup label="BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
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
                                        <input type="text" name="items[0][batch_no]" id="batch_0" value="" placeholder="Contoh: BC-... (ketik nomor batch dari faktur supplier)" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%;" required>
                                    </td>
                                    <td>
                                        <input type="date" name="items[0][expired_tgl]" class="form-control" style="font-size: 0.8rem;">
                                    </td>
                                    <td>
                                        <select name="items[0][grade_cd]" class="form-control" style="font-size: 0.8rem;">
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
                                        <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right; font-weight: 600;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="100" name="items[0][diskon_persen]" value="0" class="form-control item-diskon" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[0][potongan_nominal]" value="0" class="form-control item-potongan" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td>
                                        <select name="items[0][ppn_tipe]" class="form-control item-ppn-tipe" onchange="calculateTotalTerima()" style="font-size: 0.775rem; font-weight: 600;">
                                            <option value="NON_PPN" selected>Non (0%)</option>
                                            <option value="PPN_11">PPN 11%</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0f172a;" class="row-subtotal">
                                        Rp 0
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                                <td colspan="5" style="text-align: right; padding: 0.55rem 0.75rem; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                    Total:
                                </td>
                                <td id="totalBrutoQtyDisplay" style="padding: 0.45rem 0.35rem; color: #0284c7; font-size: 0.825rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Bruto Timbangan">
                                    0,00
                                </td>
                                <td id="totalRejectQtyDisplay" style="padding: 0.45rem 0.35rem; color: #dc2626; font-size: 0.825rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Afkir / Reject">
                                    0,00
                                </td>
                                <td id="totalNettoQtyDisplay" style="padding: 0.45rem 0.35rem; color: #047857; font-size: 0.85rem; text-align: right; background: #dcfce7; font-family: monospace; font-weight: 800;" title="Total Netto Bersih">
                                    0,00
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 0.75rem;">Total</td>
                                <td></td>
                                <td id="totalTerimaDiskonDisplay" style="padding: 0.45rem 0.35rem; color: #dc2626; font-size: 0.8rem; text-align: right; font-family: monospace;">-</td>
                                <td id="totalTerimaPotonganDisplay" style="padding: 0.45rem 0.35rem; color: #dc2626; font-size: 0.8rem; text-align: right; font-family: monospace;">-</td>
                                <td id="totalTerimaPpnDisplay" style="padding: 0.45rem 0.35rem; color: #0284c7; font-size: 0.8rem; text-align: center; font-family: monospace;">-</td>
                                <td id="totalTerimaNilaiDisplay" style="padding: 0.45rem 0.35rem; color: #0f172a; font-size: 0.875rem; text-align: right; font-family: monospace; font-weight: 800;">
                                    Rp 0
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('gudang.terima.partials.item-row-template')
                </div>

                {{-- CARD FOOTER: RINGKASAN EFISIEN & TOMBOL SIMPAN / BATAL TERPADU --}}
                <div class="card-footer" style="background: #ffffff; border-top: 1px solid #cbd5e1; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    
                    {{-- Kiri: 3 Metrik Fisik & Stok (Netto, Afkir, Komoditas) Terstruktur Rapi & Seragam --}}
                    <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                        {{-- 1. Netto Masuk --}}
                        <div>
                            <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; display: block; line-height: 1;">Total Netto Masuk</span>
                            <div style="margin-top: 0.2rem; font-size: 1.15rem; font-weight: 800; color: #047857; font-family: monospace; line-height: 1.2;">
                                <span id="barTotalNetto">0,00</span>
                            </div>
                        </div>

                        <div style="width: 1px; height: 26px; background: #e2e8f0;"></div>

                        {{-- 2. Afkir / Reject --}}
                        <div>
                            <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; display: block; line-height: 1;">Afkir / Reject</span>
                            <div style="margin-top: 0.2rem; font-size: 1.15rem; font-weight: 800; color: #dc2626; font-family: monospace; line-height: 1.2;">
                                <span id="barTotalReject">0,00</span>
                            </div>
                        </div>

                        <div style="width: 1px; height: 26px; background: #e2e8f0;"></div>

                        {{-- 3. Komoditas --}}
                        <div>
                            <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; display: block; line-height: 1;">Total Komoditas</span>
                            <div style="margin-top: 0.2rem; font-size: 1.15rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                                <span id="barTotalItems">0</span> <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Item</span>
                            </div>
                        </div>
                    </div>

                    {{-- Kanan: Total Estimasi Tagihan & Tombol Aksi --}}
                    <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                        <div style="text-align: right;">
                            <span style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; display: block; line-height: 1;">Total Estimasi Tagihan</span>
                            <strong id="barGrandTotal" style="font-size: 1.35rem; color: #0f172a; font-family: monospace; font-weight: 800; line-height: 1.3; display: block;">Rp 0</strong>
                            <div id="barTaxSummaryLine" style="font-size: 0.725rem; color: #64748b; margin-top: 1px; display: none;"></div>
                        </div>

                        <div style="width: 1px; height: 32px; background: #e2e8f0;"></div>

                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <a href="{{ route('gudang.terima.index') }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.55rem 1.15rem; border-radius: 6px; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; color: #475569;">
                                Batal
                            </a>

                            <button type="submit" id="btnSubmitTerima" class="btn btn-primary" style="background: #059669; font-size: 0.875rem; font-weight: 700; padding: 0.55rem 1.35rem; border-radius: 6px; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.25); display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer; border: none; color: #ffffff;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Penerimaan Fisik</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</form>

@include('gudang.terima.partials.modal-supplier-picker')
@include('gudang.terima.partials.modal-qc-picker')

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
    window.suppliersData = @json($suppliersJson);
    window.qcSiapGudangUrl = "{{ route('qc.inbound.siap_gudang') }}";
    window.qcTicketDataBaseUrl = "{{ url('qc/inbound/ticket-data') }}";
    window.terimaCreateUrl = "{{ route('gudang.terima.create') }}";
    window.selectedPoDetailsCount = {{ $selectedPo ? count($selectedPo->details) : 1 }};
    @if(old('supplier_id', $selectedPo?->supplier_id))
        window.oldSupplierId = {{ old('supplier_id', $selectedPo?->supplier_id) }};
    @endif
</script>
<script src="{{ asset('js/gudang/terima/terima-create.js') }}"></script>
<script>
    if (window.oldSupplierId) {
        selectSupplier(window.oldSupplierId);
    }
</script>
@endpush

@endsection
