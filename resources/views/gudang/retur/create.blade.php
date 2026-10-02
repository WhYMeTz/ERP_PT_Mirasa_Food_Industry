@extends('layouts.app')

@section('title', 'Form Retur Pembelian ke Supplier - ERP PT Mirasa')

<style>
    .order-station-grid {
        display: grid;
        grid-template-columns: 2.3fr 1fr;
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 2rem;
    }
    @media (max-width: 1100px) {
        .order-station-grid { grid-template-columns: 1fr; }
        .sticky-action-sidebar { position: static !important; top: auto !important; }
    }

    /* Excel Table Styling (Seragam dengan Pemakaian Bahan & Barang Masuk) */
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
        text-align: left;
    }
    .excel-grid-table td {
        padding: 0.35rem 0.45rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        vertical-align: middle;
    }
    .excel-grid-table tr:nth-child(even) td {
        background: #fafafa;
    }
    .excel-grid-table tr:hover td {
        background: #fff1f2;
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
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2) !important;
        background: #ffffff !important;
    }

    /* Category Chip Button Styling */
    .btn-chip {
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 5px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        line-height: 1.2;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .btn-chip:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .btn-chip.active {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.2);
    }
</style>

@section('content')
<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('gudang.retur.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Retur Pembelian
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
        Form Retur Pembelian ke Supplier (Outbound Retur)
    </h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
        Pencatatan pengembalian bahan baku cacat/reject ke supplier, pemotongan stok otomatis, dan penyesuaian kuota PO / tagihan.
    </p>
</div>

@if ($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fca5a5; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; color: #991b1b; font-size: 0.875rem;">
        <div style="font-weight: 700; margin-bottom: 0.25rem;">Gagal menyimpan dokumen retur:</div>
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('gudang.retur.store') }}" id="returForm">
    @csrf

    <div class="order-station-grid">
        {{-- ========================================================================= --}}
        {{-- KOLOM KIRI: FORMULIR UTAMA & TABEL EXCEL GRID (2.3fr)                     --}}
        {{-- ========================================================================= --}}
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            {{-- KARTU 1: INFORMASI DOKUMEN & KESEPAKATAN RETUR --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 0; overflow: hidden;">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Kesepakatan Retur</strong>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.75rem;">
                            No Retur: {{ $nextReturNo }}
                        </span>
                        <input type="hidden" name="retur_no" id="retur_no" value="{{ $nextReturNo }}">
                    </div>
                </div>
                
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    {{-- BARIS 1: TANGGAL, GUDANG ASAL, SUMBER ASAL (PO VS NON-PO) --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                        {{-- Tanggal Retur --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="retur_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Tanggal Retur Fisik <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="date" id="retur_tgl" name="retur_tgl" value="{{ old('retur_tgl', date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="updateSidebarInfo()">
                            <small style="color: #64748b; font-size: 0.725rem;">Waktu armada supplier menarik barang dari gudang.</small>
                        </div>

                        {{-- Perusahaan Asal Retur --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Perusahaan / Cabang Asal Retur <span style="color: #ef4444;">*</span>
                            </label>
                            @if (!empty($userGudangId))
                                @php $lockedGdg = $gudangList->firstWhere('gudang_id', $userGudangId); @endphp
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="text" class="form-control" value="{{ $lockedGdg?->gudang_nm }} ({{ $lockedGdg?->gudang_cd }})" disabled style="background: #f1f5f9; font-weight: 600; height: 38px; border-radius: 6px; font-size: 0.85rem;">
                                    <input type="hidden" name="gudang_id" id="gudang_id" value="{{ $userGudangId }}">
                                    <span class="badge" style="background: #e2e8f0; color: #475569; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; white-space: nowrap;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Terkunci
                                    </span>
                                </div>
                            @else
                                <select name="gudang_id" id="gudang_id" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="onGudangChanged()">
                                    @foreach ($gudangList as $gdg)
                                        <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                            {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <small style="color: #64748b; font-size: 0.725rem;">Entitas perusahaan / cabang asal tempat stok batch diretur.</small>
                        </div>

                        {{-- Sumber Asal Barang (Radio PO vs Non-PO) --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Sumber Asal Pengadaan <span style="color: #ef4444;">*</span>
                            </label>
                            <div style="display: flex; gap: 0.65rem; margin-top: 0.35rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 600; cursor: pointer; background: #f8fafc; border: 1px solid #cbd5e1; padding: 0.4rem 0.75rem; border-radius: 6px;">
                                    <input type="radio" name="source_type" value="PO" {{ old('source_type', $selectedPo ? 'PO' : 'PO') === 'PO' ? 'checked' : '' }} onchange="onSourceTypeChanged()">
                                    Dari PO Terkait
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 600; cursor: pointer; background: #f8fafc; border: 1px solid #cbd5e1; padding: 0.4rem 0.75rem; border-radius: 6px;">
                                    <input type="radio" name="source_type" value="NON_PO" {{ old('source_type') === 'NON_PO' ? 'checked' : '' }} onchange="onSourceTypeChanged()">
                                    Non-PO / Stok Bebas
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- BARIS 2: PILIH PO, SUPPLIER MITRA, NO SURAT JALAN SUPPLIER --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                        {{-- Dropdown PO Terkait --}}
                        <div class="form-group" id="poSelectGroup" style="margin-bottom: 0;">
                            <label for="poSelect" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Dokumen Purchase Order (PO) <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="po_id" id="poSelect" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" onchange="onPoSelected()">
                                <option value="">-- Pilih Dokumen PO --</option>
                                @foreach ($poList as $po)
                                    <option value="{{ $po->po_id }}" 
                                            data-supplier-id="{{ $po->supplier_id }}"
                                            data-supplier-name="{{ $po->supplier?->supplier_nm }}"
                                            data-gudang-id="{{ $po->gudang_id }}"
                                            {{ old('po_id', $selectedPo?->po_id) == $po->po_id ? 'selected' : '' }}>
                                        {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} ({{ $po->po_tgl?->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #0284c7; font-size: 0.725rem;">Pilih PO untuk otomatis memuat item &amp; batch yang pernah diterima.</small>
                        </div>

                        {{-- Supplier Mitra --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="supplierSelect" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Mitra Rekanan Supplier <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="supplier_id" id="supplierSelect" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="updateSidebarInfo()">
                                <option value="">-- Pilih Mitra Supplier --</option>
                                @foreach ($supplierList as $sup)
                                    <option value="{{ $sup->supplier_id }}" {{ old('supplier_id', $selectedPo?->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                        {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem;">Pihak petani atau vendor penerima pengembalian.</small>
                        </div>

                        {{-- No Surat Jalan Supplier --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="suratjalan_supplier_no" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                No Surat Jalan Asal (Opsional)
                            </label>
                            <input type="text" name="suratjalan_supplier_no" id="suratjalan_supplier_no" value="{{ old('suratjalan_supplier_no') }}" placeholder="Contoh: SJ-2026/09/881" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor surat jalan supplier saat barang datang.</small>
                        </div>
                    </div>

                    {{-- BARIS 3: TINDAKAN KESEPAKATAN RETUR (REPLACE VS CREDIT_NOTE) --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            Kesepakatan Tindakan Penanganan Retur dengan Supplier <span style="color: #ef4444;">*</span>
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.75rem;">
                            {{-- Opsi REPLACE --}}
                            <label style="display: flex; align-items: flex-start; gap: 0.65rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; cursor: pointer; transition: all 0.2s;" id="labelReplace">
                                <input type="radio" name="tindakan_cd" value="REPLACE" style="margin-top: 0.2rem;" {{ old('tindakan_cd', 'REPLACE') === 'REPLACE' ? 'checked' : '' }} onchange="onTindakanChanged()">
                                <div>
                                    <div style="font-weight: 700; color: #047857; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
                                        🔄 Minta Kirim Ulang (Pengganti)
                                    </div>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem; line-height: 1.35;">
                                        Supplier bersedia mengirimkan fisik barang baru. Kuota penerimaan PO akan diturunkan kembali dan status PO dikembalikan ke <strong>PARTIAL</strong> agar kiriman pengganti dapat diterima di sistem nanti.
                                    </div>
                                </div>
                            </label>

                            {{-- Opsi CREDIT_NOTE --}}
                            <label style="display: flex; align-items: flex-start; gap: 0.65rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; cursor: pointer; transition: all 0.2s;" id="labelCredit">
                                <input type="radio" name="tindakan_cd" value="CREDIT_NOTE" style="margin-top: 0.2rem;" {{ old('tindakan_cd') === 'CREDIT_NOTE' ? 'checked' : '' }} onchange="onTindakanChanged()">
                                <div>
                                    <div style="font-weight: 700; color: #b45309; font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
                                        💰 Potong Tagihan (Credit Note)
                                    </div>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem; line-height: 1.35;">
                                        Supplier tidak mengganti fisik barang. Nilai retur ini langsung memotong tagihan utang pembayaran atau dikompensasikan ke saldo deposit.
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- BARIS 4: ALASAN UMUM --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="alasan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                            Alasan Umum Retur / Catatan Memo Lapangan
                        </label>
                        <input type="text" id="alasan_txt" name="alasan_txt" value="{{ old('alasan_txt') }}" placeholder="Contoh: Ditemukan singkong hitam & busuk saat proses pembersihan di lini produksi" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                    </div>
                </div>
            </div>

            {{-- KARTU 2: FILTER PENCARIAN & PEMILIHAN BARANG CEPAT --}}
            <div class="card" style="border: 1px solid #cbd5e1; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.03); padding: 0; overflow: hidden;">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding: 0.85rem 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="width: 28px; height: 28px; border-radius: 6px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <div>
                            <strong style="font-size: 0.95rem; color: #0f172a; margin: 0; display: block;">
                                2. Filter &amp; Pemilihan Barang untuk Diretur
                            </strong>
                            <p style="color: #64748b; font-size: 0.775rem; margin: 0.15rem 0 0 0;">
                                Filter katalog barang berdasarkan kelompok kategori bahan sebelum ditambahkan ke tabel retur.
                            </p>
                        </div>
                    </div>
                    
                    {{-- CHIP KATEGORI CEPAT --}}
                    <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                        <button type="button" class="btn-chip active" id="chipSemua" onclick="filterBarangCatalog('ALL')">
                            <span>Semua</span>
                        </button>
                        <button type="button" class="btn-chip" id="chipBB" onclick="filterBarangCatalog('BAHAN_BAKU')">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #d97706;"></span>
                            <span>Bahan Baku</span>
                        </button>
                        <button type="button" class="btn-chip" id="chipBP" onclick="filterBarangCatalog('BAHAN_PENOLONG')">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                            <span>Bahan Penolong</span>
                        </button>
                        <button type="button" class="btn-chip" id="chipPACK" onclick="filterBarangCatalog('KEMASAN')">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #6366f1;"></span>
                            <span>Kemasan</span>
                        </button>
                    </div>
                </div>

                {{-- TOOLBAR PEMILIHAN BARANG KE TABEL --}}
                <div style="padding: 1rem 1.25rem; background: #ffffff;">
                    <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 0.75rem; align-items: flex-end;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.35rem;">
                                Pilih Barang dari Katalog
                            </label>
                            <select id="quickBarangSelect" class="form-control" style="font-weight: 600; font-size: 0.85rem; height: 38px; border-radius: 6px;">
                                <option value="">-- Pilih Barang yang Akan Diretur --</option>
                                @foreach ($barangList as $b)
                                    <option value="{{ $b->barang_id }}" 
                                            data-kategori="{{ $b->kategori_kelompok }}"
                                            data-satuan="{{ $b->satuanDasar?->satuan_nm }}"
                                            data-harga="{{ $b->harga_beli_standar || 0 }}">
                                        {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.35rem;">
                                Cari Cepat (Ketik Nama / Kode)
                            </label>
                            <input type="text" id="catalogSearchInput" placeholder="Ketik singkong, bumbu, dus..." class="form-control" style="height: 38px; font-size: 0.85rem; border-radius: 6px;" oninput="searchCatalog()">
                        </div>

                        <div>
                            <button type="button" class="btn btn-primary" onclick="addSelectedBarangToTable()" style="background: #0f172a; height: 38px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0 1rem;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                + Tambah ke Tabel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU 3: TABEL EXCEL GRID RINCIAN ITEM RETUR --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 0; overflow: hidden;">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">3. Rincian Item Fisik yang Diretur &amp; Alokasi Batch</strong>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                            💡 Qty retur dibatasi oleh saldo sisa batch yang ada di gudang
                        </span>
                    </div>
                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()" style="background: #ffffff; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.775rem; border-radius: 6px; padding: 0.35rem 0.65rem;">
                            + Baris Kosong
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetItemsTable()" style="background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-size: 0.775rem; border-radius: 6px; padding: 0.35rem 0.65rem;" title="Kosongkan Tabel">
                            Reset
                        </button>
                    </div>
                </div>

                {{-- TABEL EXCEL --}}
                <div style="overflow-x: auto;">
                    <table class="excel-grid-table" id="itemTable">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="min-width: 220px;">Barang Baku / Penolong</th>
                                <th style="min-width: 190px;">Nomor Batch &amp; Sisa Fisik</th>
                                <th style="width: 110px; text-align: right;">Qty Retur</th>
                                <th style="width: 70px;">Satuan</th>
                                <th style="width: 120px; text-align: right;">Harga (Rp)</th>
                                <th style="width: 130px; text-align: right;">Subtotal (Rp)</th>
                                <th style="min-width: 180px;">Alasan Cacat / Reject</th>
                                <th style="width: 45px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody">
                            {{-- Baris item akan dimasukkan via JavaScript --}}
                        </tbody>
                    </table>
                </div>

                {{-- FOOTER TABEL --}}
                <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <span style="font-size: 0.75rem; color: #64748b;">
                        * Pastikan fisik barang telah ditimbang/dihitung bersama perwakilan driver supplier sebelum submit.
                    </span>
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <span style="font-size: 0.85rem; color: #64748b;">
                            Total Item: <strong id="totalItemCount" style="color: #0f172a; font-family: monospace;">0</strong>
                        </span>
                        <span style="font-size: 0.85rem; color: #64748b;">
                            Total Nilai: <strong id="totalNominalText" style="color: #dc2626; font-size: 1.15rem; font-family: monospace; margin-left: 0.25rem;">Rp 0</strong>
                        </span>
                    </div>
                </div>
            </div>

        </div> {{-- End Kolom Kiri (2.3fr) --}}


        {{-- ========================================================================= --}}
        {{-- KOLOM KANAN: STICKY ACTION SIDEBAR (1fr)                                  --}}
        {{-- ========================================================================= --}}
        <div class="sticky-action-sidebar" style="position: sticky; top: 1rem; display: flex; flex-direction: column; gap: 1.25rem;">
            
            {{-- KARTU RINGKASAN TRANSAKSI RETUR --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); background: #ffffff; padding: 0; overflow: hidden;">
                <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="font-size: 0.95rem; letter-spacing: 0.02em;">Ringkasan Dokumen Retur</strong>
                    <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; font-size: 0.7rem; font-weight: 700;">
                        OUTBOUND
                    </span>
                </div>
                
                <div style="padding: 1.25rem;">
                    {{-- Detail Meta Info --}}
                    <div style="display: flex; flex-direction: column; gap: 0.65rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; font-size: 0.825rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">No Dokumen:</span>
                            <strong style="color: #0f172a; font-family: monospace;">{{ $nextReturNo }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Tanggal:</span>
                            <span id="sideTanggal" style="font-weight: 600; color: #0f172a;">{{ date('d/m/Y') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Gudang Asal:</span>
                            <span id="sideGudangName" style="font-weight: 600; color: #0f172a; text-align: right; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">-</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Supplier:</span>
                            <span id="sideSupplierName" style="font-weight: 600; color: #0f172a; text-align: right; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">- Belum Dipilih -</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Referensi Asal:</span>
                            <span id="sidePoName" style="font-weight: 600; color: #0284c7;">Dari PO</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Tindakan Retur:</span>
                            <span id="sideTindakanBadge" class="badge" style="background: #d1fae5; color: #047857; font-weight: 700; font-size: 0.7rem;">
                                🔄 Kirim Ulang
                            </span>
                        </div>
                    </div>

                    {{-- METRIK KUANTITAS & ESTIMASI NILAI --}}
                    <div style="padding: 1rem 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 0.85rem;">Total Item Diretur:</span>
                            <strong id="sideTotalItem" style="color: #0f172a; font-size: 1rem; font-family: monospace;">0 Item</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 0.85rem;">Total Fisik Dikembalikan:</span>
                            <strong id="sideTotalQty" style="color: #dc2626; font-size: 1.05rem; font-family: monospace;">0,00</strong>
                        </div>

                        {{-- BOX TOTAL NOMINAL (MERAH MENCOLOK KHAS MIRASA) --}}
                        <div style="padding: 0.75rem 0.85rem; background: #fee2e2; border: 1.5px solid #fca5a5; border-radius: 6px; margin-top: 0.25rem;">
                            <div style="color: #991b1b; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em;">
                                Total Estimasi Nilai Retur:
                            </div>
                            <div id="sideGrandTotal" style="color: #b91c1c; font-size: 1.35rem; font-weight: 800; font-family: monospace; margin-top: 0.25rem;">
                                Rp 0
                            </div>
                            <div style="font-size: 0.7rem; color: #991b1b; margin-top: 0.25rem;">
                                Saldo stok batch dipotong otomatis &bull; Kartu stok mutasi terbit
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL SUBMIT UTAMA --}}
                    <button type="submit" id="btnSubmitRetur" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 700; background: #dc2626; border: none; justify-content: center; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.25); display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Proses Dokumen Retur</span>
                    </button>

                    <a href="{{ route('gudang.retur.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 0.65rem; font-size: 0.85rem; padding: 0.5rem;">
                        Batal &amp; Kembali ke Daftar
                    </a>
                </div>
            </div>

            {{-- KARTU PANDUAN CEPAT OPERATOR GUDANG --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.85rem;">SOP Retur Barang PT Mirasa</strong>
                </div>
                <div style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #dc2626; font-weight: 700;">&bull;</span>
                        <span><strong>Minta Kirim Ulang (Replace):</strong> Kuota penerimaan PO dibuka kembali ke status <strong>PARTIAL</strong> agar kiriman pengganti dapat diterima nanti.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #dc2626; font-weight: 700;">&bull;</span>
                        <span><strong>Potong Tagihan (Credit Note):</strong> Mengurangi kewajiban hutang pembayaran supplier tanpa membuka kuota baru.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #dc2626; font-weight: 700;">&bull;</span>
                        <span><strong>Surat Jalan Resmi:</strong> Setelah disimpan, Anda dapat mencetak Surat Jalan Retur untuk ditandatangani driver supplier.</span>
                    </div>
                </div>
            </div>

        </div> {{-- End Kolom Kanan (.sticky-action-sidebar) --}}
    </div> {{-- End .order-station-grid --}}
</form>

{{-- ========================================================================= --}}
{{-- CLIENT-SIDE JAVASCRIPT & AJAX ENGINE                                      --}}
{{-- ========================================================================= --}}
<script>
    const MASTER_BARANG = @json($barangList);
    let availableBatches = [];
    let rowCounter = 0;
    let currentCategoryFilter = 'ALL';

    document.addEventListener('DOMContentLoaded', function() {
        onGudangChanged(function() {
            const poSelect = document.getElementById('poSelect');
            if (poSelect && poSelect.value) {
                onPoSelected();
            }
        });
        onTindakanChanged();
        updateSidebarInfo();
    });

    function getSelectedGudangId() {
        const el = document.getElementById('gudang_id');
        return el ? el.value : '';
    }

    function updateSidebarInfo() {
        // Update Tanggal
        const tglInput = document.getElementById('retur_tgl');
        const sideTgl = document.getElementById('sideTanggal');
        if (sideTgl && tglInput && tglInput.value) {
            const parts = tglInput.value.split('-');
            sideTgl.innerText = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : tglInput.value;
        }

        // Update Gudang
        const gdgSelect = document.getElementById('gudang_id');
        const sideGdg = document.getElementById('sideGudangName');
        if (sideGdg && gdgSelect) {
            if (gdgSelect.tagName === 'SELECT') {
                const opt = gdgSelect.options[gdgSelect.selectedIndex];
                sideGdg.innerText = (opt && opt.value) ? opt.text : '-';
            } else {
                sideGdg.innerText = gdgSelect.value ? `Gudang #${gdgSelect.value}` : '-';
            }
        }

        // Update Supplier
        const supSelect = document.getElementById('supplierSelect');
        const sideSup = document.getElementById('sideSupplierName');
        if (sideSup && supSelect) {
            const opt = supSelect.options[supSelect.selectedIndex];
            sideSup.innerText = (opt && opt.value) ? opt.text : '- Belum Dipilih -';
        }

        // Update Asal PO
        const type = document.querySelector('input[name="source_type"]:checked')?.value;
        const poSelect = document.getElementById('poSelect');
        const sidePo = document.getElementById('sidePoName');
        if (sidePo) {
            if (type === 'NON_PO') {
                sidePo.innerText = 'Non-PO (Bebas)';
                sidePo.style.color = '#d97706';
            } else {
                const opt = poSelect.options[poSelect.selectedIndex];
                sidePo.innerText = (opt && opt.value) ? opt.text.split(' - ')[0] : 'Dari PO Terkait';
                sidePo.style.color = '#0284c7';
            }
        }
    }

    function onSourceTypeChanged() {
        const type = document.querySelector('input[name="source_type"]:checked').value;
        const poGroup = document.getElementById('poSelectGroup');
        const poSelect = document.getElementById('poSelect');
        const supSelect = document.getElementById('supplierSelect');

        if (type === 'NON_PO') {
            poGroup.style.display = 'none';
            poSelect.value = '';
            supSelect.removeAttribute('disabled');
        } else {
            poGroup.style.display = 'block';
        }
        updateSidebarInfo();
    }

    function onPoSelected() {
        const poSelect = document.getElementById('poSelect');
        const poId = poSelect.value;
        updateSidebarInfo();
        if (!poId) return;

        fetch(`{{ url('/gudang/retur/po-data') }}/${poId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const poData = res.data;
                    
                    if (poData.supplier_id) {
                        document.getElementById('supplierSelect').value = poData.supplier_id;
                    }

                    if (poData.gudang_id) {
                        const gdSelect = document.getElementById('gudang_id');
                        if (gdSelect.tagName === 'SELECT' && gdSelect.value != poData.gudang_id) {
                            gdSelect.value = poData.gudang_id;
                            onGudangChanged(function() {
                                populatePoItems(poData.items);
                            });
                            return;
                        }
                    }

                    populatePoItems(poData.items);
                    updateSidebarInfo();
                }
            })
            .catch(err => console.error("Error fetching PO data:", err));
    }

    function populatePoItems(items) {
        const tbody = document.getElementById('itemTableBody');
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            alert("PO ini belum memiliki catatan penerimaan barang fisik di gudang. Silakan input item secara manual atau pilih PO yang sudah pernah diterima.");
            addRow();
            return;
        }

        items.forEach(item => {
            addRowFromPoItem(item);
        });

        updateTotals();
    }

    function addRowFromPoItem(poItem) {
        const tbody = document.getElementById('itemTableBody');
        const rowId = rowCounter++;

        let barangOptions = `<option value="${poItem.barang_id}" selected data-harga="${poItem.harga_nominal}" data-satuan="${poItem.satuan}">${poItem.barang_nm} (${poItem.barang_cd})</option>`;
        MASTER_BARANG.forEach(b => {
            if (b.barang_id !== poItem.barang_id) {
                const satuan = b.satuan_dasar?.satuan_nm || '';
                barangOptions += `<option value="${b.barang_id}" data-harga="${b.harga_beli_standar || 0}" data-satuan="${satuan}">${b.barang_nm} (${b.barang_cd})</option>`;
            }
        });

        const tr = document.createElement('tr');
        tr.id = `row_${rowId}`;
        tr.innerHTML = `
            <td style="text-align: center; color: #64748b; font-size: 0.775rem;">
                ${tbody.children.length + 1}
            </td>
            <td>
                <input type="hidden" name="items[${rowId}][podtl_id]" value="${poItem.podtl_id}">
                <select name="items[${rowId}][barang_id]" class="form-control barang-select" data-row-id="${rowId}" onchange="onBarangChanged(${rowId})" required style="font-size: 0.825rem; font-weight: 600;">
                    ${barangOptions}
                </select>
                <div style="font-size: 0.7rem; color: #0284c7; margin-top: 2px; font-weight: 600;">
                    ✓ Dari PO (Diterima: ${poItem.terima_qty} ${poItem.satuan})
                </div>
            </td>
            <td>
                <select name="items[${rowId}][batch_no]" id="batch_select_${rowId}" class="form-control batch-select" data-row-id="${rowId}" onchange="onBatchSelected(${rowId})" required style="font-size: 0.8rem;">
                    <option value="">-- Pilih Batch --</option>
                </select>
                <div id="batch_info_${rowId}" style="font-size: 0.7rem; color: #64748b; margin-top: 2px;"></div>
            </td>
            <td>
                <input type="number" step="0.0001" min="0.0001" name="items[${rowId}][retur_qty]" id="qty_${rowId}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" required style="font-size: 0.85rem; font-weight: 700; color: #dc2626; text-align: right;">
            </td>
            <td style="font-size: 0.8rem; color: #475569;">
                <span id="satuan_label_${rowId}">${poItem.satuan}</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowId}][harga_satuan]" id="harga_${rowId}" value="${poItem.harga_nominal}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" style="font-size: 0.825rem; text-align: right; font-family: monospace;">
            </td>
            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.85rem; vertical-align: middle; font-family: monospace;">
                <span id="subtotal_text_${rowId}">Rp 0</span>
            </td>
            <td>
                <select name="items[${rowId}][alasan_reject]" class="form-control" style="font-size: 0.775rem;">
                    <option value="Singkong Busuk / Basah">Singkong Busuk / Basah</option>
                    <option value="Berserat / Keras (Gagal Goreng)">Berserat / Keras (Gagal Goreng)</option>
                    <option value="Berjamur / Kutu">Berjamur / Kutu</option>
                    <option value="Kemasan Rusak / Bocor">Kemasan Rusak / Bocor</option>
                    <option value="Bau Asam / Tengik">Bau Asam / Tengik</option>
                    <option value="Spesifikasi Tidak Sesuai">Spesifikasi Tidak Sesuai</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(${rowId})" style="color: #dc2626; padding: 0.2rem 0.45rem; font-size: 0.75rem;" title="Hapus Baris">
                    ✕
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        updateBatchDropdownForRow(rowId, poItem.batches);
    }

    function onTindakanChanged() {
        const val = document.querySelector('input[name="tindakan_cd"]:checked')?.value || 'REPLACE';
        const lRep = document.getElementById('labelReplace');
        const lCre = document.getElementById('labelCredit');
        const sideBadge = document.getElementById('sideTindakanBadge');

        if (val === 'REPLACE') {
            lRep.style.borderColor = '#059669';
            lRep.style.background = '#f0fdf4';
            lCre.style.borderColor = '#cbd5e1';
            lCre.style.background = '#ffffff';
            if (sideBadge) {
                sideBadge.innerText = '🔄 Kirim Ulang';
                sideBadge.style.background = '#d1fae5';
                sideBadge.style.color = '#047857';
            }
        } else {
            lCre.style.borderColor = '#d97706';
            lCre.style.background = '#fffbeb';
            lRep.style.borderColor = '#cbd5e1';
            lRep.style.background = '#ffffff';
            if (sideBadge) {
                sideBadge.innerText = '💰 Potong Nota';
                sideBadge.style.background = '#fef3c7';
                sideBadge.style.color = '#b45309';
            }
        }
    }

    function onGudangChanged(callback) {
        updateSidebarInfo();
        const gudangId = getSelectedGudangId();
        if (!gudangId) return;

        fetch(`{{ route('gudang.retur.batches') }}?gudang_id=${gudangId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    availableBatches = data.data;

                    if (typeof callback === 'function') {
                        callback();
                    } else {
                        const poSelect = document.getElementById('poSelect');
                        if (document.getElementById('itemTableBody').children.length === 0 && (!poSelect || !poSelect.value)) {
                            addRow();
                        } else {
                            document.querySelectorAll('.barang-select').forEach((sel) => {
                                updateBatchDropdownForRow(sel.getAttribute('data-row-id'));
                            });
                        }
                    }
                }
            })
            .catch(err => console.error("Error fetching batches:", err));
    }

    {{-- FILTER KATALOG BARANG CEPAT --}}
    function filterBarangCatalog(kategori) {
        currentCategoryFilter = kategori;

        document.getElementById('chipSemua').classList.toggle('active', kategori === 'ALL');
        document.getElementById('chipBB').classList.toggle('active', kategori === 'BAHAN_BAKU');
        document.getElementById('chipBP').classList.toggle('active', kategori === 'BAHAN_PENOLONG');
        document.getElementById('chipPACK').classList.toggle('active', kategori === 'KEMASAN');

        renderQuickSelectOptions();
    }

    function searchCatalog() {
        renderQuickSelectOptions();
    }

    function renderQuickSelectOptions() {
        const query = document.getElementById('catalogSearchInput').value.toLowerCase().trim();
        const select = document.getElementById('quickBarangSelect');
        select.innerHTML = '<option value="">-- Pilih Barang yang Akan Diretur --</option>';

        const filtered = MASTER_BARANG.filter(b => {
            const matchKategori = (currentCategoryFilter === 'ALL' || b.kategori_kelompok === currentCategoryFilter);
            const matchSearch = (!query || b.barang_nm.toLowerCase().includes(query) || (b.barang_cd && b.barang_cd.toLowerCase().includes(query)));
            return matchKategori && matchSearch;
        });

        filtered.forEach(b => {
            select.innerHTML += `
                <option value="${b.barang_id}" 
                        data-kategori="${b.kategori_kelompok}"
                        data-satuan="${b.satuan_dasar?.satuan_nm || ''}"
                        data-harga="${b.harga_beli_standar || 0}">
                    ${b.barang_nm} (${b.barang_cd})
                </option>
            `;
        });
    }

    function addSelectedBarangToTable() {
        const select = document.getElementById('quickBarangSelect');
        const barangId = select.value;
        if (!barangId) {
            alert("Silakan pilih barang terlebih dahulu.");
            return;
        }

        const selectedOpt = select.options[select.selectedIndex];
        const barangNm = selectedOpt.text;
        const satuan = selectedOpt.getAttribute('data-satuan');
        const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);

        addRowWithBarang(parseInt(barangId), harga, satuan);
    }

    function addRowWithBarang(barangId, defaultHarga = 0, defaultSatuan = '') {
        const tbody = document.getElementById('itemTableBody');
        const rowId = rowCounter++;

        let barangOptions = '<option value="">-- Pilih Barang --</option>';
        MASTER_BARANG.forEach(b => {
            const isSel = (b.barang_id === barangId) ? 'selected' : '';
            const satuan = b.satuan_dasar?.satuan_nm || '';
            barangOptions += `<option value="${b.barang_id}" ${isSel} data-harga="${b.harga_beli_standar || 0}" data-satuan="${satuan}">${b.barang_nm} (${b.barang_cd})</option>`;
        });

        const tr = document.createElement('tr');
        tr.id = `row_${rowId}`;
        tr.innerHTML = `
            <td style="text-align: center; color: #64748b; font-size: 0.775rem;">
                ${tbody.children.length + 1}
            </td>
            <td>
                <select name="items[${rowId}][barang_id]" class="form-control barang-select" data-row-id="${rowId}" onchange="onBarangChanged(${rowId})" required style="font-size: 0.825rem; font-weight: 600;">
                    ${barangOptions}
                </select>
            </td>
            <td>
                <select name="items[${rowId}][batch_no]" id="batch_select_${rowId}" class="form-control batch-select" data-row-id="${rowId}" onchange="onBatchSelected(${rowId})" required style="font-size: 0.8rem;">
                    <option value="">-- Pilih Batch --</option>
                </select>
                <div id="batch_info_${rowId}" style="font-size: 0.7rem; color: #64748b; margin-top: 2px;"></div>
            </td>
            <td>
                <input type="number" step="0.0001" min="0.0001" name="items[${rowId}][retur_qty]" id="qty_${rowId}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" required style="font-size: 0.85rem; font-weight: 700; color: #dc2626; text-align: right;">
            </td>
            <td style="font-size: 0.8rem; color: #475569;">
                <span id="satuan_label_${rowId}">${defaultSatuan}</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowId}][harga_satuan]" id="harga_${rowId}" value="${defaultHarga}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" style="font-size: 0.825rem; text-align: right; font-family: monospace;">
            </td>
            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.85rem; vertical-align: middle; font-family: monospace;">
                <span id="subtotal_text_${rowId}">Rp 0</span>
            </td>
            <td>
                <select name="items[${rowId}][alasan_reject]" class="form-control" style="font-size: 0.775rem;">
                    <option value="Singkong Busuk / Basah">Singkong Busuk / Basah</option>
                    <option value="Berserat / Keras (Gagal Goreng)">Berserat / Keras (Gagal Goreng)</option>
                    <option value="Berjamur / Kutu">Berjamur / Kutu</option>
                    <option value="Kemasan Rusak / Bocor">Kemasan Rusak / Bocor</option>
                    <option value="Bau Asam / Tengik">Bau Asam / Tengik</option>
                    <option value="Spesifikasi Tidak Sesuai">Spesifikasi Tidak Sesuai</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(${rowId})" style="color: #dc2626; padding: 0.2rem 0.45rem; font-size: 0.75rem;" title="Hapus Baris">
                    ✕
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        updateBatchDropdownForRow(rowId);
        updateTotals();

        // Focus ke input Qty
        setTimeout(() => {
            document.getElementById(`qty_${rowId}`)?.focus();
        }, 50);
    }

    function addRow() {
        addRowWithBarang(null);
    }

    function resetItemsTable() {
        if (confirm("Kosongkan semua baris item pada tabel?")) {
            document.getElementById('itemTableBody').innerHTML = '';
            addRow();
            updateTotals();
        }
    }

    function removeRow(rowId) {
        const row = document.getElementById(`row_${rowId}`);
        if (row) {
            row.remove();
            renumberRows();
            updateTotals();
        }
    }

    function renumberRows() {
        const rows = document.querySelectorAll('#itemTableBody tr');
        rows.forEach((tr, idx) => {
            const firstCell = tr.cells[0];
            if (firstCell) {
                firstCell.textContent = idx + 1;
            }
        });
    }

    function onBarangChanged(rowId) {
        const barangSelect = document.querySelector(`#row_${rowId} .barang-select`);
        const barangId = parseInt(barangSelect.value);
        const selectedOpt = barangSelect.options[barangSelect.selectedIndex];

        const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : '';
        const defaultHarga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;

        document.getElementById(`satuan_label_${rowId}`).textContent = satuan;
        document.getElementById(`harga_${rowId}`).value = defaultHarga;

        updateBatchDropdownForRow(rowId);
        calculateSubtotal(rowId);
    }

    function updateBatchDropdownForRow(rowId, preferredBatches = []) {
        const barangSelect = document.querySelector(`#row_${rowId} .barang-select`);
        if (!barangSelect) return;

        const barangId = parseInt(barangSelect.value);
        const batchSelect = document.getElementById(`batch_select_${rowId}`);
        const batchInfo = document.getElementById(`batch_info_${rowId}`);

        batchSelect.innerHTML = '<option value="">-- Pilih Batch --</option>';
        batchInfo.innerHTML = '';

        if (!barangId) {
            batchSelect.innerHTML = '<option value="">-- Pilih Barang Dahulu --</option>';
            return;
        }

        const filtered = availableBatches.filter(b => b.barang_id === barangId);
        if (filtered.length === 0) {
            batchSelect.innerHTML = '<option value="">⚠️ Stok Kosong (0 batch)</option>';
            batchInfo.innerHTML = '<span style="color: #dc2626;">Tidak ada saldo batch di gudang ini.</span>';
            return;
        }

        let autoSelectedBatch = null;

        filtered.forEach(b => {
            const exp = b.expired_tgl ? ` (Exp: ${b.expired_tgl})` : '';
            const isPreferred = Array.isArray(preferredBatches) && preferredBatches.includes(b.batch_no);
            const prefLabel = isPreferred ? ' ⭐ [Batch PO]' : '';
            
            if (isPreferred && !autoSelectedBatch) {
                autoSelectedBatch = b.batch_no;
            }

            batchSelect.innerHTML += `
                <option value="${b.batch_no}" 
                        data-sisa="${b.sisa_qty}" 
                        data-harga="${b.harga_satuan}"
                        data-exp="${b.expired_tgl || '-'}"
                        ${isPreferred ? 'style="font-weight: 700; color: #0284c7;"' : ''}>
                    ${b.batch_no}${prefLabel} [Sisa: ${b.sisa_qty} ${b.satuan}]${exp}
                </option>
            `;
        });

        if (autoSelectedBatch) {
            batchSelect.value = autoSelectedBatch;
            onBatchSelected(rowId);
        } else if (filtered.length === 1) {
            batchSelect.value = filtered[0].batch_no;
            onBatchSelected(rowId);
        }
    }

    function onBatchSelected(rowId) {
        const batchSelect = document.getElementById(`batch_select_${rowId}`);
        const selectedOpt = batchSelect.options[batchSelect.selectedIndex];
        const batchInfo = document.getElementById(`batch_info_${rowId}`);
        const qtyInput = document.getElementById(`qty_${rowId}`);
        const hargaInput = document.getElementById(`harga_${rowId}`);

        if (selectedOpt && selectedOpt.value) {
            const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
            const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);

            qtyInput.max = sisa;
            if (!qtyInput.value || parseFloat(qtyInput.value) > sisa) {
                qtyInput.value = sisa;
            }

            if (harga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
                hargaInput.value = harga;
            }

            batchInfo.innerHTML = `<span style="color: #059669; font-weight: 600;">✓ Saldo fisik: ${sisa}</span>`;
            calculateSubtotal(rowId);
        } else {
            batchInfo.innerHTML = '';
        }
    }

    function calculateSubtotal(rowId) {
        const qty = parseFloat(document.getElementById(`qty_${rowId}`)?.value || 0);
        const harga = parseFloat(document.getElementById(`harga_${rowId}`)?.value || 0);
        const subtotal = qty * harga;

        const subtotalText = document.getElementById(`subtotal_text_${rowId}`);
        if (subtotalText) {
            subtotalText.textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
        }

        updateTotals();
    }

    function updateTotals() {
        let totalItems = 0;
        let totalQty = 0;
        let totalNominal = 0;

        document.querySelectorAll('#itemTableBody tr').forEach(tr => {
            const rowId = tr.id.replace('row_', '');
            const qty = parseFloat(document.getElementById(`qty_${rowId}`)?.value || 0);
            const harga = parseFloat(document.getElementById(`harga_${rowId}`)?.value || 0);

            if (qty > 0) {
                totalItems++;
                totalQty += qty;
                totalNominal += (qty * harga);
            }
        });

        // Update Tabel Footer
        document.getElementById('totalItemCount').textContent = totalItems;
        document.getElementById('totalNominalText').textContent = 'Rp ' + Math.round(totalNominal).toLocaleString('id-ID');

        // Update Right Sidebar
        document.getElementById('sideTotalItem').textContent = `${totalItems} Item`;
        document.getElementById('sideTotalQty').textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        document.getElementById('sideGrandTotal').textContent = 'Rp ' + Math.round(totalNominal).toLocaleString('id-ID');
    }
</script>
@endsection
