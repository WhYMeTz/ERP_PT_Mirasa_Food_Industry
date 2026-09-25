@extends('layouts.app')

@section('title', 'Catat Penerimaan Barang Fisik - ERP PT Mirasa')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        &larr; Kembali ke Daftar Penerimaan
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Catat Penerimaan Barang Fisik (Goods Receipt)</h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Barang yang dicatat di formulir ini akan langsung menambah saldo fisik gudang dan dicatat pada Kartu Stok.</p>
</div>

<form action="{{ route('gudang.terima.store') }}" method="POST" id="formTerima">
    @csrf
    @if ($selectedPo)
        <input type="hidden" name="redirect_to" value="po">
    @endif

    {{-- KARTU 1: INFORMASI HEADER PENERIMAAN --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a; font-size: 1rem;">1. Dokumen Penerimaan & Pengirim</strong>
        </div>
        <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="terima_no" class="form-label">Nomor Penerimaan <span style="color:#ef4444;">*</span></label>
                <input type="text" id="terima_no" name="terima_no" value="{{ old('terima_no', $nextTerimaNo ?? '') }}" class="form-control" required>
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

            <div class="form-group" style="margin-bottom: 0;">
                <label for="supplier_id" class="form-label">Supplier Pengirim <span style="color:#ef4444;">*</span></label>
                <select id="supplier_id" name="supplier_id" class="form-control" required>
                    <option value="">-- Pilih Supplier --</option>
                    @foreach ($supplierList as $sup)
                        <option value="{{ $sup->supplier_id }}" {{ (old('supplier_id', $selectedPo?->supplier_id) == $sup->supplier_id) ? 'selected' : '' }}>
                            {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="gudang_id" class="form-label">Gudang Penyimpanan <span style="color:#ef4444;">*</span></label>
                <select id="gudang_id" name="gudang_id" class="form-control" required>
                    <option value="">-- Pilih Gudang Masuk --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ (old('gudang_id', $selectedPo?->gudang_id) == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
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
        <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e1; padding: 0.75rem 1.25rem;">
            <div>
                <strong style="color: #0f172a; font-size: 1rem;">2. Fisik Barang Diterima & Alokasi Batch</strong>
                <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                    💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk berpindah baris layaknya Excel
                </span>
            </div>
            <button type="button" onclick="addTerimaRow(true)" class="btn btn-primary btn-sm" style="font-weight: 600; background: #059669; padding: 0.4rem 0.85rem;">
                + Tambah Baris (Enter)
            </button>
        </div>

        <style>
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

        <div style="overflow-x: auto;">
            <table class="excel-grid-table" id="tableTerimaItems" style="width: 100%; min-width: 1050px;">
                <thead>
                    <tr>
                        <th style="width: 32px; text-align: center;">No</th>
                        <th style="min-width: 190px; text-align: left;">Nama Komoditas / Barang <span style="color:#ef4444;">*</span></th>
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
                        @foreach ($selectedPo->details as $idx => $pdtl)
                            @if ((float) $pdtl->sisa_qty > 0)
                                <tr class="terima-row" data-index="{{ $idx }}" data-sisa="{{ (float) $pdtl->sisa_qty }}">
                                    <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">{{ $idx + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $pdtl->podtl_id }}">
                                        <input type="hidden" name="items[{{ $idx }}][barang_id]" value="{{ $pdtl->barang_id }}">
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">{{ $pdtl->barang?->barang_nm }}</strong>
                                        <span style="font-size: 0.725rem; color: #64748b;">
                                            Pesanan: {{ number_format((float) $pdtl->pesan_qty, 2) }} | <strong>Sisa: {{ number_format((float) $pdtl->sisa_qty, 2) }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][batch_no]" value="{{ app(\App\Services\Common\CodeGeneratorService::class)->generateBatchNo($pdtl->barang?->barang_cd, date('Y-m-d'), $pdtl->barang?->barang_nm) }}" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7;" required>
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
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($barangList as $b)
                                        <option value="{{ $b->barang_id }}" 
                                                data-cd="{{ $b->barang_cd }}" 
                                                data-nm="{{ $b->barang_nm }}"
                                                data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                        </option>
                                    @endforeach
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
                ✓ Simpan & Masukkan ke Stok Gudang
            </button>
        </div>
    </div>
</form>

<script>
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

        const dateStr = "{{ date('ymd') }}";
        const padNum = String(terimaRowIndex + 1).padStart(2, '0');

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">${terimaRowIndex + 1}</td>
            <td>
                <select name="items[${terimaRowIndex}][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barangList as $b)
                        <option value="{{ $b->barang_id }}" 
                                data-cd="{{ $b->barang_cd }}" 
                                data-nm="{{ $b->barang_nm }}"
                                data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
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

        if (focusNew) {
            tr.querySelector('.item-barang').focus();
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

    function updateTerimaSatuanAndBatch(selectElem) {
        const row = selectElem.closest('tr');
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const satuan = selectedOption.dataset.satuan || '-';
        const acronym = selectedOption.dataset.acronym || 'BRG';
        const defaultHarga = parseFloat(selectedOption.dataset.harga || 0);

        const satuanSpan = row.querySelector('.row-satuan');
        if (satuanSpan) satuanSpan.innerText = satuan;

        // Ambil tanggal dari input terima_tgl (format DDMMYYYY)
        const tglInput = document.getElementById('terima_tgl')?.value;
        let dateFormatted = '';
        if (tglInput) {
            const parts = tglInput.split('-');
            if (parts.length === 3) {
                dateFormatted = parts[2] + parts[1] + parts[0];
            }
        }
        if (!dateFormatted) {
            const today = new Date();
            dateFormatted = String(today.getDate()).padStart(2, '0') + String(today.getMonth() + 1).padStart(2, '0') + today.getFullYear();
        }

        // Hitung urutan khusus untuk barang ini di form agar mandiri per barang & reset ke 01
        const prefix = `${acronym}-${dateFormatted}-`;
        let seq = 1;
        document.querySelectorAll('.terima-row').forEach(r => {
            if (r !== row) {
                const otherBatch = r.querySelector('.item-batch')?.value || '';
                if (otherBatch.startsWith(prefix)) {
                    seq++;
                }
            }
        });

        const seqStr = String(seq).padStart(2, '0');
        const batchInput = row.querySelector('.item-batch');
        if (batchInput) {
            batchInput.value = `${prefix}${seqStr}`;
        }

        const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');
        if (hargaInput && defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
            hargaInput.value = defaultHarga;
        }
        calculateTotalTerima();
    }

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

            // Update row netto display
            const nettoSpan = row.querySelector('.row-netto');
            if (nettoSpan) {
                nettoSpan.innerText = netto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            totalBruto += terimaQty;
            totalReject += rejectQty;
            totalNilai += (terimaQty * hargaNominal);
        });

        const totalNetto = Math.max(0, totalBruto - totalReject);

        // Update displays in tfoot
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
                            // Di baris terakhir -> buat baris baru dan fokus
                            addTerimaRow(true);
                        } else {
                            // Pindah ke baris berikutnya di kolom yang sama
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

    // Attach initial rows
    document.querySelectorAll('.terima-row').forEach(r => attachExcelKeyboardEventsTerima(r));

    // Run initial calculate on page load
    calculateTotalTerima();

    function fillAllSisaCreate() {
        copyAllRemainingPoQty();
    }

    function clearAllInputsCreate() {
        clearAllTerimaQty();
    }
</script>
@endsection
