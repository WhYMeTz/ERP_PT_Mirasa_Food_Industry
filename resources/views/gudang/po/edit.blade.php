@extends('layouts.app')

@section('title', 'Edit Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

@section('content')
<style>
    .order-station-grid {
        display: grid;
        grid-template-columns: 2.3fr 1fr;
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 2rem;
    }
    @media (max-width: 1100px) {
        .order-station-grid {
            grid-template-columns: 1fr;
        }
        .sticky-action-sidebar {
            position: static !important;
            top: auto !important;
        }
    }
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
        padding: 0.35rem 0.45rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        vertical-align: middle;
    }
    .excel-grid-table tr:nth-child(even) td {
        background: #fafafa;
    }
    .excel-grid-table tr:hover td {
        background: #f0f9ff;
    }
    .excel-grid-table .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 0.35rem 0.5rem !important;
        font-size: 0.825rem !important;
        height: 32px;
        box-sizing: border-box;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .excel-grid-table .form-control:focus {
        border-color: #0284c7 !important;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25) !important;
        background: #ffffff !important;
    }
</style>

{{-- TOP HEADER COMMAND --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.po.show', $po->po_id) }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Detail PO
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
                Edit Purchase Order: {{ $po->po_no }}
            </h1>
            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight: 700;">Status: {{ $po->status_cd }}</span>
        </div>
        <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
            Koreksi dan sesuaikan data dokumen pesanan, vendor mitra, kuantitas item, serta nilai kesepakatan harga.
        </p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <strong>Perhatian! Terdapat kesalahan pada input Anda:</strong>
        <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('gudang.po.update', $po->po_id) }}" method="POST" id="formEditPo">
    @csrf
    @method('PUT')

    <div class="order-station-grid">
        {{-- KOLOM UTAMA --}}
        <div>
            {{-- PANEL INFORMASI DASAR PO --}}
            <div class="card" style="margin-bottom: 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">Informasi Header Dokumen</strong>
                </div>
                <div style="padding: 1.25rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Nomor PO Resmi</label>
                            <input type="text" class="form-control" value="{{ $po->po_no }}" readonly style="background: #f1f5f9; font-weight: 700; color: #475569; font-family: monospace;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="po_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal PO <span style="color:#ef4444;">*</span></label>
                            <input type="date" id="po_tgl" name="po_tgl" class="form-control" value="{{ old('po_tgl', $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="supplier_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Supplier Mitra <span style="color:#ef4444;">*</span></label>
                            <select id="supplier_id" name="supplier_id" class="form-control" required>
                                <option value="">-- Pilih Supplier Mitra --</option>
                                @foreach ($supplierList as $sup)
                                    <option value="{{ $sup->supplier_id }}" {{ old('supplier_id', $po->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                        {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Gudang Tujuan Masuk <span style="color:#ef4444;">*</span></label>
                            <select id="gudang_id" name="gudang_id" class="form-control" required>
                                <option value="">-- Pilih Gudang Tujuan --</option>
                                @foreach ($gudangList as $gdg)
                                    <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $po->gudang_id) == $gdg->gudang_id ? 'selected' : '' }}>
                                        {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="tgl_estimasi_datang" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Estimasi Tanggal Datang (ETA)</label>
                            <input type="date" id="tgl_estimasi_datang" name="tgl_estimasi_datang" class="form-control" value="{{ old('tgl_estimasi_datang', $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="form-group" style="margin-bottom: 0; grid-column: 1 / -1;">
                            <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Instruksi &amp; Catatan Khusus Pengadaan</label>
                            <textarea id="catatan_txt" name="catatan_txt" class="form-control" rows="2" placeholder="Tuliskan catatan delivery, syarat bongkar muat, atau instruksi pembayaran...">{{ old('catatan_txt', $po->catatan_txt) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL RINCIAN ITEM PESANAN --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">Rincian Barang &amp; Bahan Baku Pesanan</strong>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;" id="badgeItemCount">({{ $po->details->count() }} item)</span>
                    </div>
                    <button type="button" onclick="addNewItemRow()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah Item Barang
                    </button>
                </div>

                <div style="overflow-x: auto; padding: 0.5rem;">
                    <table class="excel-grid-table" id="tablePoItems">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="min-width: 220px; text-align: left;">Bahan Baku / Komoditas <span style="color:#ef4444;">*</span></th>
                                <th style="width: 105px; text-align: right;">Kuantitas <span style="color:#ef4444;">*</span></th>
                                <th style="width: 60px; text-align: center;">Satuan</th>
                                <th style="width: 125px; text-align: right;">Harga Satuan (Rp)</th>
                                <th style="width: 75px; text-align: right;">Diskon %</th>
                                <th style="width: 95px; text-align: right;">Potongan Rp</th>
                                <th style="width: 95px; text-align: center;">PPN</th>
                                <th style="width: 135px; text-align: right;">Subtotal (Rp)</th>
                                <th style="min-width: 130px; text-align: left;">Catatan Item</th>
                                <th style="width: 45px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPoItems">
                            @foreach ($po->details as $idx => $dtl)
                                <tr class="po-item-row" data-row-index="{{ $idx }}">
                                    <td style="text-align: center; font-weight: 600; color: #64748b;" class="row-num">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][barang_id]" class="form-control item-barang-select" required onchange="handleBarangChange(this)">
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach ($barangList as $b)
                                                <option value="{{ $b->barang_id }}" 
                                                        data-satuan="{{ $b->satuanDasar?->satuan_cd ?? 'KG' }}"
                                                        data-harga="{{ (float) $b->harga_beli_standar }}"
                                                        {{ $dtl->barang_id == $b->barang_id ? 'selected' : '' }}>
                                                    {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0.01" name="items[{{ $idx }}][pesan_qty]" class="form-control input-qty" value="{{ (float) $dtl->pesan_qty }}" required style="text-align: right; font-weight: 700;" oninput="calculateRow(this)">
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #475569; font-size: 0.8rem;" class="satuan-badge">
                                        {{ $dtl->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0" name="items[{{ $idx }}][harga_nominal]" class="form-control input-harga" value="{{ (float) $dtl->harga_nominal }}" required style="text-align: right;" oninput="calculateRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0" max="100" name="items[{{ $idx }}][diskon_persen]" class="form-control input-diskon" value="{{ (float) $dtl->diskon_persen }}" style="text-align: right;" oninput="calculateRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0" name="items[{{ $idx }}][potongan_nominal]" class="form-control input-potongan" value="{{ (float) $dtl->potongan_nominal }}" style="text-align: right;" oninput="calculateRow(this)">
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][ppn_tipe]" class="form-control select-ppn" style="font-size: 0.75rem;" onchange="calculateRow(this)">
                                            <option value="NON_PPN" {{ $dtl->ppn_tipe == 'NON_PPN' ? 'selected' : '' }}>Non PPN</option>
                                            <option value="PPN_11" {{ $dtl->ppn_tipe == 'PPN_11' ? 'selected' : '' }}>PPN 11%</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace;" class="row-subtotal">
                                        Rp {{ number_format((float) $dtl->subtotal_tagihan, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][catatan_txt]" class="form-control" value="{{ $dtl->catatan_txt }}" placeholder="Catatan spesifikasi...">
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger" style="padding: 0.2rem 0.45rem; font-size: 0.75rem; border-radius: 4px;" title="Hapus Baris">
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: RINGKASAN HARGA & TOMBOL SUBMIT --}}
        <div class="sticky-action-sidebar" style="position: sticky; top: 1.5rem;">
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
                <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.85rem 1.25rem;">
                    <strong style="font-size: 0.95rem; display: flex; align-items: center; gap: 0.45rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Ringkasan Kalkulasi PO
                    </strong>
                </div>

                <div style="padding: 1.25rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                        <span style="color: #64748b;">Subtotal Bruto:</span>
                        <strong style="color: #0f172a;" id="summarySubtotalBruto">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                        <span style="color: #64748b;">Total Diskon / Potongan:</span>
                        <strong style="color: #b91c1c;" id="summaryDiskon">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem; border-top: 1px dashed #e2e8f0; padding-top: 0.65rem;">
                        <span style="color: #64748b;">DPP (Netto):</span>
                        <strong style="color: #0f172a;" id="summaryDpp">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: #64748b;">PPN 11%:</span>
                        <strong style="color: #0369a1;" id="summaryPpn">Rp 0</strong>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #0284c7; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem; text-align: center;">
                        <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; display: block;">
                            Total Kesepakatan Tagihan
                        </span>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #0284c7; margin-top: 0.15rem; font-family: monospace;" id="summaryGrandTotal">
                            Rp 0
                        </div>
                    </div>

                    <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.65rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Perubahan PO
                        </button>
                        <a href="{{ route('gudang.po.show', $po->po_id) }}" class="btn btn-secondary" style="width: 100%; text-align: center; padding: 0.55rem; font-size: 0.85rem;">
                            Batal &amp; Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- TEMPLATE BARIS BARANG BARU (HIDDEN) --}}
<template id="templateRow">
    <tr class="po-item-row" data-row-index="__INDEX__">
        <td style="text-align: center; font-weight: 600; color: #64748b;" class="row-num">
            __NUM__
        </td>
        <td>
            <select name="items[__INDEX__][barang_id]" class="form-control item-barang-select" required onchange="handleBarangChange(this)">
                <option value="">-- Pilih Barang --</option>
                @foreach ($barangList as $b)
                    <option value="{{ $b->barang_id }}" 
                            data-satuan="{{ $b->satuanDasar?->satuan_cd ?? 'KG' }}"
                            data-harga="{{ (float) $b->harga_beli_standar }}">
                        {{ $b->barang_nm }} ({{ $b->barang_cd }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" step="any" min="0.01" name="items[__INDEX__][pesan_qty]" class="form-control input-qty" value="1" required style="text-align: right; font-weight: 700;" oninput="calculateRow(this)">
        </td>
        <td style="text-align: center; font-weight: 600; color: #475569; font-size: 0.8rem;" class="satuan-badge">
            -
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[__INDEX__][harga_nominal]" class="form-control input-harga" value="0" required style="text-align: right;" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="number" step="any" min="0" max="100" name="items[__INDEX__][diskon_persen]" class="form-control input-diskon" value="0" style="text-align: right;" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[__INDEX__][potongan_nominal]" class="form-control input-potongan" value="0" style="text-align: right;" oninput="calculateRow(this)">
        </td>
        <td>
            <select name="items[__INDEX__][ppn_tipe]" class="form-control select-ppn" style="font-size: 0.75rem;" onchange="calculateRow(this)">
                <option value="NON_PPN">Non PPN</option>
                <option value="PPN_11">PPN 11%</option>
            </select>
        </td>
        <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace;" class="row-subtotal">
            Rp 0
        </td>
        <td>
            <input type="text" name="items[__INDEX__][catatan_txt]" class="form-control" placeholder="Catatan spesifikasi...">
        </td>
        <td style="text-align: center;">
            <button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger" style="padding: 0.2rem 0.45rem; font-size: 0.75rem; border-radius: 4px;" title="Hapus Baris">
                &times;
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    let rowIndex = {{ $po->details->count() }};

    function handleBarangChange(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const satuanBadge = row.querySelector('.satuan-badge');
        const inputHarga = row.querySelector('.input-harga');

        if (selectedOpt && selectedOpt.value) {
            satuanBadge.textContent = selectedOpt.getAttribute('data-satuan') || 'KG';
            const defaultHarga = parseFloat(selectedOpt.getAttribute('data-harga')) || 0;
            if (parseFloat(inputHarga.value) === 0 && defaultHarga > 0) {
                inputHarga.value = defaultHarga;
            }
        } else {
            satuanBadge.textContent = '-';
        }
        calculateRow(selectEl);
    }

    function calculateRow(element) {
        const row = element.closest('tr');
        const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
        const diskonPersen = parseFloat(row.querySelector('.input-diskon').value) || 0;
        const potonganNominal = parseFloat(row.querySelector('.input-potongan').value) || 0;
        const ppnTipe = row.querySelector('.select-ppn').value;

        const diskonUnit = harga * (diskonPersen / 100);
        const hargaNetto = Math.max(0, harga - diskonUnit);
        const subtotalNetto = Math.max(0, (qty * hargaNetto) - potonganNominal);
        const ppnNominal = (ppnTipe === 'PPN_11') ? Math.round(subtotalNetto * 0.11) : 0;
        const subtotalTagihan = subtotalNetto + ppnNominal;

        row.querySelector('.row-subtotal').textContent = 'Rp ' + subtotalTagihan.toLocaleString('id-ID');
        row.setAttribute('data-subtotal-bruto', (qty * harga));
        row.setAttribute('data-diskon-total', (qty * diskonUnit) + potonganNominal);
        row.setAttribute('data-dpp', subtotalNetto);
        row.setAttribute('data-ppn', ppnNominal);
        row.setAttribute('data-grand-total', subtotalTagihan);

        calculateAllTotals();
    }

    function calculateAllTotals() {
        let totalBruto = 0;
        let totalDiskon = 0;
        let totalDpp = 0;
        let totalPpn = 0;
        let grandTotal = 0;

        document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
            const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
            const diskonPersen = parseFloat(row.querySelector('.input-diskon').value) || 0;
            const potonganNominal = parseFloat(row.querySelector('.input-potongan').value) || 0;
            const ppnTipe = row.querySelector('.select-ppn').value;

            const diskonUnit = harga * (diskonPersen / 100);
            const hargaNetto = Math.max(0, harga - diskonUnit);
            const subtotalNetto = Math.max(0, (qty * hargaNetto) - potonganNominal);
            const ppnNominal = (ppnTipe === 'PPN_11') ? Math.round(subtotalNetto * 0.11) : 0;
            const subtotalTagihan = subtotalNetto + ppnNominal;

            totalBruto += (qty * harga);
            totalDiskon += (qty * diskonUnit) + potonganNominal;
            totalDpp += subtotalNetto;
            totalPpn += ppnNominal;
            grandTotal += subtotalTagihan;
        });

        document.getElementById('summarySubtotalBruto').textContent = 'Rp ' + totalBruto.toLocaleString('id-ID');
        document.getElementById('summaryDiskon').textContent = '- Rp ' + totalDiskon.toLocaleString('id-ID');
        document.getElementById('summaryDpp').textContent = 'Rp ' + totalDpp.toLocaleString('id-ID');
        document.getElementById('summaryPpn').textContent = 'Rp ' + totalPpn.toLocaleString('id-ID');
        document.getElementById('summaryGrandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

        const rowCount = document.querySelectorAll('#tbodyPoItems tr.po-item-row').length;
        document.getElementById('badgeItemCount').textContent = `(${rowCount} item)`;
    }

    function addNewItemRow() {
        const tbody = document.getElementById('tbodyPoItems');
        const template = document.getElementById('templateRow').innerHTML;
        const currentCount = tbody.querySelectorAll('tr.po-item-row').length + 1;

        const html = template
            .replace(/__INDEX__/g, rowIndex)
            .replace(/__NUM__/g, currentCount);

        tbody.insertAdjacentHTML('beforeend', html);
        rowIndex++;
        renumberRows();
        calculateAllTotals();
    }

    function removeRow(btn) {
        const tbody = document.getElementById('tbodyPoItems');
        const rows = tbody.querySelectorAll('tr.po-item-row');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 item barang dalam Purchase Order.');
            return;
        }

        btn.closest('tr').remove();
        renumberRows();
        calculateAllTotals();
    }

    function renumberRows() {
        document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach((row, idx) => {
            row.querySelector('.row-num').textContent = idx + 1;
        });
    }

    // Hitung total awal saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        calculateAllTotals();
    });
</script>
@endpush
@endsection
