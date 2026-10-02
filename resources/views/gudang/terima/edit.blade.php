@extends('layouts.app')

@section('title', 'Edit Penerimaan Barang ' . $terima->terima_no . ' - ERP PT Mirasa')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-form.css') }}">
@endpush

<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Penerimaan
    </a>
    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Edit Penerimaan Barang Fisik: {{ $terima->terima_no }}</h1>
        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">Mode Edit</span>
    </div>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">Perubahan kuantitas fisik dan nomor batch akan otomatis menyesuaikan kembali saldo stok gudang dan mutasi kartu stok.</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 1.25rem; background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444; color: #991b1b; padding: 0.85rem 1rem; border-radius: 6px;">
        <strong style="display: block; margin-bottom: 0.35rem;">⚠️ Terjadi kesalahan input:</strong>
        <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem;">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('gudang.terima.update', $terima->terima_id) }}" method="POST" id="formTerima">
    @csrf
    @method('PUT')

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
                        <input type="text" id="terima_no" name="terima_no" value="{{ old('terima_no', $terima->terima_no) }}" class="form-control" style="background: #f8fafc; font-weight: 600; height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                        <small style="color: #64748b; font-size: 0.725rem;">Nomor Good Receipt Note (GRN).</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="terima_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal Masuk Fisik <span style="color:#ef4444;">*</span></label>
                        <input type="date" id="terima_tgl" name="terima_tgl" value="{{ old('terima_tgl', $terima->terima_tgl ? $terima->terima_tgl->format('Y-m-d') : date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                        <small style="color: #64748b; font-size: 0.725rem;">Waktu kedatangan armada di pabrik.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="suratjalan_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No. Surat Jalan Supplier</label>
                        <input type="text" id="suratjalan_no" name="suratjalan_no" value="{{ old('suratjalan_no', $terima->suratjalan_no) }}" class="form-control" placeholder="Contoh: SJ-2026/09/88" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
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
                                    {{ (old('po_id', $terima->po_id) == $po->po_id) ? 'selected' : '' }}>
                                    {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} ({{ $po->status_cd }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem;">Purchase order yang mendasari penerimaan.</small>
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
                                    {{ (old('supplier_id', $terima->supplier_id) == $sup->supplier_id) ? 'selected' : '' }}>
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
                                <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $terima->gudang_id) == $gdg->gudang_id) ? 'selected' : '' }}>
                                    {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Entitas perusahaan / cabang tempat barang diterima.</small>
                    </div>
                </div>

                {{-- BARIS 3: CATATAN TAMBAHAN PENERIMAAN --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Fisik Penerimaan / Kondisi Pengiriman</label>
                    <textarea id="catatan_txt" name="catatan_txt" class="form-control" rows="2" placeholder="Catatan armada, no plat truk, supir, kondisi cuaca, atau keterangan lain..." style="font-size: 0.85rem; border-radius: 6px;">{{ old('catatan_txt', $terima->catatan_txt) }}</textarea>
                </div>
            </div>
        </div>

        {{-- KARTU 2: RINCIAN KOMODITAS & BATCH FISIK --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">2. Rincian Komoditas, No. Batch Fisik &amp; Kuantitas Masuk</strong>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                        Periksa nomor batch fisik supplier dan tanggal kadaluarsa sebelum menyimpan.
                    </div>
                </div>
                <button type="button" onclick="addNewItemRow()" class="btn btn-primary btn-sm" style="background: #0284c7; border: none; font-weight: 600; font-size: 0.8rem; padding: 0.4rem 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Baris Bahan</span>
                </button>
            </div>

            <div style="padding: 1.25rem;">
                <div style="overflow-x: auto;">
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
                            @foreach ($terima->details as $idx => $dtl)
                                <tr class="terima-row" data-index="{{ $idx }}">
                                    <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">{{ $idx + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $dtl->podtl_id }}">
                                        <select name="items[{{ $idx }}][barang_id]" class="form-control item-barang-id" onchange="onBarangChanged(this, {{ $idx }})" required style="font-size: 0.825rem; font-weight: 600;">
                                            <option value="">-- Pilih Bahan --</option>
                                            @foreach ($barangList as $b)
                                                <option value="{{ $b->barang_id }}" 
                                                    data-nama="{{ $b->barang_nm }}"
                                                    data-kode="{{ $b->barang_cd }}"
                                                    data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}"
                                                    data-harga="{{ $b->harga_beli_standar ?? 0 }}"
                                                    {{ (old("items.{$idx}.barang_id", $dtl->barang_id) == $b->barang_id) ? 'selected' : '' }}>
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ old("items.{$idx}.batch_no", $dtl->batch_no) }}" placeholder="Nomor batch..." class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%; font-size: 0.8rem;" required>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $idx }}][expired_tgl]" value="{{ old("items.{$idx}.expired_tgl", $dtl->expired_tgl ? $dtl->expired_tgl->format('Y-m-d') : '') }}" class="form-control" style="font-size: 0.8rem;">
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][grade_cd]" class="form-control" style="font-size: 0.8rem;">
                                            <option value="A" {{ old("items.{$idx}.grade_cd", $dtl->grade_cd) == 'A' ? 'selected' : '' }}>Grade A Super</option>
                                            <option value="B" {{ old("items.{$idx}.grade_cd", $dtl->grade_cd) == 'B' ? 'selected' : '' }}>Grade B Standar</option>
                                            <option value="REJECT" {{ old("items.{$idx}.grade_cd", $dtl->grade_cd) == 'REJECT' ? 'selected' : '' }}>Reject / Afkir</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][terima_qty]" value="{{ old("items.{$idx}.terima_qty", (float) $dtl->terima_qty) }}" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][reject_qty]" value="{{ old("items.{$idx}.reject_qty", (float) ($dtl->reject_qty ?? 0)) }}" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                                        {{ number_format((float) ($dtl->terima_qty - ($dtl->reject_qty ?? 0)), 2, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">{{ $dtl->barang?->satuanDasar?->satuan_nm ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][harga_nominal]" value="{{ old("items.{$idx}.harga_nominal", (float) ($dtl->harga_nominal ?? 0)) }}" class="form-control item-harga" placeholder="0" style="text-align: right; font-weight: 600;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="100" name="items[{{ $idx }}][diskon_persen]" value="{{ old("items.{$idx}.diskon_persen", (float) ($dtl->diskon_persen ?? 0)) }}" class="form-control item-diskon" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][potongan_nominal]" value="{{ old("items.{$idx}.potongan_nominal", (float) ($dtl->potongan_nominal ?? 0)) }}" class="form-control item-potongan" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][ppn_tipe]" class="form-control item-ppn-tipe" onchange="calculateTotalTerima()" style="font-size: 0.775rem; font-weight: 600;">
                                            <option value="NON_PPN" {{ old("items.{$idx}.ppn_tipe", $dtl->ppn_tipe ?? 'NON_PPN') === 'NON_PPN' ? 'selected' : '' }}>Non (0%)</option>
                                            <option value="PPN_11" {{ old("items.{$idx}.ppn_tipe", $dtl->ppn_tipe ?? 'NON_PPN') === 'PPN_11' ? 'selected' : '' }}>PPN 11%</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a; background: #f8fafc;" class="row-subtotal">
                                        Rp {{ number_format((float) ($dtl->subtotal_tagihan ?: $dtl->subtotal_netto), 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" onclick="removeItemRow(this)" class="btn btn-sm" style="color: #ef4444; background: none; border: none; padding: 0.2rem 0.4rem; cursor: pointer;" title="Hapus baris ini">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @php
                            $initBruto = (float) $terima->details->sum('terima_qty');
                            $initReject = (float) $terima->details->sum('reject_qty');
                            $initNetto = max(0, $initBruto - $initReject);
                            $initSubtotal = (float) ($terima->subtotal_nominal ?: $terima->details->sum(function($d) {
                                $hargaNetto = max(0, (float)$d->harga_nominal - ((float)$d->harga_nominal * ((float)($d->diskon_persen ?? 0) / 100)));
                                return max(0, ((float)$d->terima_qty * $hargaNetto) - (float)($d->potongan_nominal ?? 0));
                            }));
                            $initPpn = (float) ($terima->ppn_nominal ?: $terima->details->sum('ppn_nominal'));
                            $initPotongan = (float) ($terima->potongan_nominal ?? 0);
                            $initGrandTotal = (float) ($terima->total_tagihan ?: max(0, $initSubtotal + $initPpn - $initPotongan));
                        @endphp
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                                <td colspan="5" style="text-align: right; padding: 0.55rem 0.75rem; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                    Total:
                                </td>
                                <td id="totalBrutoQtyDisplay" style="padding: 0.45rem 0.35rem; color: #0284c7; font-size: 0.825rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Bruto Timbangan">
                                    {{ number_format($initBruto, 2, ',', '.') }}
                                </td>
                                <td id="totalRejectQtyDisplay" style="padding: 0.45rem 0.35rem; color: #dc2626; font-size: 0.825rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Afkir / Reject">
                                    {{ number_format($initReject, 2, ',', '.') }}
                                </td>
                                <td id="totalNettoQtyDisplay" style="padding: 0.45rem 0.35rem; color: #047857; font-size: 0.85rem; text-align: right; background: #dcfce7; font-family: monospace; font-weight: 800;" title="Total Netto Bersih">
                                    {{ number_format($initNetto, 2, ',', '.') }}
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 0.75rem;">Total</td>
                                <td></td>
                                <td id="totalTerimaDiskonDisplay" style="padding: 0.45rem 0.35rem; color: #dc2626; font-size: 0.8rem; text-align: right; font-family: monospace;">-</td>
                                <td id="totalTerimaPotonganDisplay" style="padding: 0.45rem 0.35rem; color: #dc2626; font-size: 0.8rem; text-align: right; font-family: monospace;">-</td>
                                <td id="totalTerimaPpnDisplay" style="padding: 0.45rem 0.35rem; color: #0284c7; font-size: 0.8rem; text-align: center; font-family: monospace;">
                                    {{ $initPpn > 0 ? '+Rp ' . number_format($initPpn, 0, ',', '.') : '-' }}
                                </td>
                                <td id="totalTerimaNilaiDisplay" style="padding: 0.45rem 0.35rem; color: #0f172a; font-size: 0.875rem; text-align: right; font-family: monospace; font-weight: 800;">
                                    Rp {{ number_format($initSubtotal + $initPpn, 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('gudang.terima.partials.item-row-template')
                </div>

                {{-- TOTAL REKAPITULASI BAWAH --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.25rem; align-items: start;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
                        <strong style="color: #0f172a; font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">Ringkasan Kuantitas Fisik:</strong>
                        <div style="display: flex; justify-content: space-between; font-size: 0.825rem; color: #475569; margin-bottom: 0.25rem;">
                            <span>Total Kuantitas Bruto:</span>
                            <strong id="lblTotalBruto" style="color: #0f172a;">{{ number_format($initBruto, 2, ',', '.') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.825rem; color: #ef4444; margin-bottom: 0.25rem;">
                            <span>Total Afkir / Reject:</span>
                            <strong id="lblTotalAfkir">{{ number_format($initReject, 2, ',', '.') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 700; color: #059669; border-top: 1px solid #cbd5e1; padding-top: 0.35rem;">
                            <span>Total Kuantitas Bersih Masuk (Netto):</span>
                            <span id="lblTotalNetto">{{ number_format($initNetto, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #475569; margin-bottom: 0.35rem;">
                            <span>Subtotal Nilai Bahan:</span>
                            <strong id="lblSubtotalNominal" style="font-family: monospace; color: #0f172a;">Rp {{ number_format($initSubtotal, 0, ',', '.') }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #475569; margin-bottom: 0.35rem;">
                            <span>Potongan Global Faktur:</span>
                            <div style="width: 140px;">
                                <input type="number" step="0.01" min="0" name="potongan_nominal" id="global_potongan" value="{{ old('potongan_nominal', (float) $terima->potongan_nominal) }}" class="form-control" placeholder="0" style="text-align: right; height: 30px; font-size: 0.825rem;" oninput="calculateTotalTerima()">
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #475569; margin-bottom: 0.35rem;">
                            <span>Pajak PPN (11%):</span>
                            <strong id="lblTotalPpn" style="font-family: monospace; color: #0284c7;">{{ $initPpn > 0 ? '+Rp ' . number_format($initPpn, 0, ',', '.') : 'Rp 0' }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1.05rem; font-weight: 800; color: #0f172a; border-top: 2px solid #0f172a; padding-top: 0.5rem; margin-top: 0.5rem;">
                            <span>Total Tagihan Bersih:</span>
                            <span id="lblGrandTotal" style="font-family: monospace; color: #047857;">Rp {{ number_format($initGrandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL SUBMIT --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                    <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn btn-secondary" style="font-size: 0.875rem; font-weight: 600; padding: 0.55rem 1.15rem; border-radius: 6px;">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitTerima" class="btn btn-primary" style="background: #059669; font-size: 0.875rem; font-weight: 700; padding: 0.55rem 1.35rem; border-radius: 6px; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.25); display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer; border: none; color: #ffffff;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan Penerimaan</span>
                    </button>
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
    window.terimaCreateUrl = "{{ route('gudang.terima.create') }}";
    window.oldSupplierId = {{ old('supplier_id', $terima->supplier_id) }};
    window.selectedPoDetailsCount = {{ $terima->details->count() }};
</script>
<script src="{{ asset('js/gudang/terima/terima-create.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.oldSupplierId) {
            selectSupplier(window.oldSupplierId);
        }
        calculateTotalTerima();
    });
</script>
@endpush

@endsection
