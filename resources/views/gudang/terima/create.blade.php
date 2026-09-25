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
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.875rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="font-size: 0.875rem; color: #334155;">
                <strong>Referensi Dokumen:</strong> {{ $selectedPo->po_no }} &bull; Supplier: {{ $selectedPo->supplier?->supplier_nm }}
                @if ($selectedPo->status_cd == 'PARTIAL')
                    <span class="badge badge-info" style="margin-left: 0.35rem; font-size: 0.75rem;">Sebagian Diterima</span>
                @endif
            </div>
            @if ($selectedPo->tgl_estimasi_datang)
                <span style="font-size: 0.8rem; color: #64748b;">
                    Estimasi Tiba: {{ \Carbon\Carbon::parse($selectedPo->tgl_estimasi_datang)->format('d/m/Y') }}
                </span>
            @endif
        </div>
    @endif

    {{-- KARTU 2: DETAIL BARANG & NOMOR BATCH (INVENTORY ENGINE) --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="color: #0f172a; font-size: 1rem;">2. Fisik Barang Diterima & Alokasi Batch</strong>
                <p style="color: #64748b; font-size: 0.8rem; margin-top: 0.15rem;">Setiap baris akan menghasilkan nomor batch unik untuk pelacakan FIFO & kadaluarsa di gudang.</p>
            </div>
            <button type="button" onclick="addTerimaRow()" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                + Tambah Baris Barang
            </button>
        </div>

        <style>
            #tableTerimaItems th, #tableTerimaItems td {
                padding: 0.5rem 0.35rem !important;
                vertical-align: middle;
                font-size: 0.825rem;
            }
            #tableTerimaItems .form-control {
                padding: 0.35rem 0.45rem !important;
                font-size: 0.825rem !important;
                height: 34px;
                border-radius: 6px;
                box-sizing: border-box;
            }
        </style>

        <div style="overflow-x: auto;">
            <table id="tableTerimaItems" style="width: 100%; min-width: 960px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                        <th style="width: 32px; text-align: center;">No</th>
                        <th style="min-width: 180px;">Nama Barang / Komoditas <span style="color:#ef4444;">*</span></th>
                        <th style="width: 140px;">Nomor Batch / Lot <span style="color:#ef4444;">*</span></th>
                        <th style="width: 120px;">Tgl Expired</th>
                        <th style="width: 95px;">Grade</th>
                        <th style="width: 90px; text-align: right;">Qty Masuk <span style="color:#ef4444;">*</span></th>
                        <th style="width: 75px; text-align: right;">Reject</th>
                        <th style="width: 50px; text-align: center;">Satuan</th>
                        <th style="width: 105px; text-align: right;">Harga (Rp)</th>
                        <th style="width: 35px; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="terimaItemsContainer">
                    @if ($selectedPo && $selectedPo->details->isNotEmpty())
                        {{-- Prefilled items from selected PO --}}
                        @foreach ($selectedPo->details as $idx => $pdtl)
                            @if ((float) $pdtl->sisa_qty > 0)
                                <tr class="terima-row" data-index="{{ $idx }}">
                                    <td class="row-num" style="font-weight: 600; text-align: center;">{{ $idx + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $idx }}][podtl_id]" value="{{ $pdtl->podtl_id }}">
                                        <input type="hidden" name="items[{{ $idx }}][barang_id]" value="{{ $pdtl->barang_id }}">
                                        <strong style="color: #0f172a; display: block; font-size: 0.85rem;">{{ $pdtl->barang?->barang_nm }}</strong>
                                        <span style="font-size: 0.75rem; color: #64748b;">
                                            Pesan: {{ number_format((float) $pdtl->pesan_qty, 2) }} | Sisa: {{ number_format((float) $pdtl->sisa_qty, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][batch_no]" value="BATCH-{{ preg_replace('/[^A-Za-z0-9]/', '', $pdtl->barang?->barang_cd ?? 'ITEM') }}-{{ date('ymd') }}-{{ str_pad($idx+1, 2, '0', STR_PAD_LEFT) }}" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $idx }}][expired_tgl]" class="form-control">
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][grade_cd]" class="form-control">
                                            <option value="A" selected>Grade A</option>
                                            <option value="B">Grade B</option>
                                            <option value="REJECT">Reject</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0.0001" max="{{ $pdtl->sisa_qty }}" name="items[{{ $idx }}][terima_qty]" value="{{ (float) $pdtl->sisa_qty }}" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][reject_qty]" value="0" class="form-control" placeholder="0" style="text-align: right;">
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-weight: 600; color: #475569; font-size: 0.8rem;">{{ $pdtl->barang?->satuanDasar?->satuan_nm ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][harga_nominal]" value="{{ (float) $pdtl->harga_nominal }}" class="form-control" placeholder="0" style="text-align: right;">
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem;" title="Hapus Baris">&times;</button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        {{-- Row Kosong Default --}}
                        <tr class="terima-row" data-index="0">
                            <td class="row-num" style="font-weight: 600; text-align: center;">1</td>
                            <td>
                                <select name="items[0][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($barangList as $b)
                                        <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[0][batch_no]" id="batch_0" value="BATCH-{{ date('ymd') }}-01" class="form-control item-batch" required>
                            </td>
                            <td>
                                <input type="date" name="items[0][expired_tgl]" class="form-control">
                            </td>
                            <td>
                                <select name="items[0][grade_cd]" class="form-control">
                                    <option value="A" selected>Grade A</option>
                                    <option value="B">Grade B</option>
                                    <option value="REJECT">Reject</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0.0001" name="items[0][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0" name="items[0][reject_qty]" value="0" class="form-control" placeholder="0" style="text-align: right;">
                            </td>
                            <td style="text-align: center;">
                                <span class="row-satuan" style="font-weight: 600; color: #475569; font-size: 0.8rem;">-</span>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right;">
                            </td>
                            <td style="text-align: center;">
                                <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem;" title="Hapus Baris">&times;</button>
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 700;">
                        <td colspan="5" style="text-align: right; padding: 0.75rem 1rem;">Total Kuantitas Masuk Fisik:</td>
                        <td id="totalTerimaQtyDisplay" style="padding: 0.75rem 0.5rem; color: #059669; font-size: 1.05rem; text-align: right;">1.00</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- TOMBOL AKSI FORM --}}
    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <a href="{{ route('gudang.terima.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" style="background:#059669; padding: 0.65rem 1.75rem; font-size: 0.95rem;">
            Simpan & Tambahkan ke Stok Gudang
        </button>
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

    function addTerimaRow() {
        const container = document.getElementById('terimaItemsContainer');
        const tr = document.createElement('tr');
        tr.className = 'terima-row';
        tr.dataset.index = terimaRowIndex;

        const dateStr = "{{ date('ymd') }}";
        const padNum = String(terimaRowIndex + 1).padStart(2, '0');

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 600; text-align: center;">${terimaRowIndex + 1}</td>
            <td>
                <select name="items[${terimaRowIndex}][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barangList as $b)
                        <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="items[${terimaRowIndex}][batch_no]" value="BATCH-${dateStr}-${padNum}" class="form-control item-batch" required>
            </td>
            <td>
                <input type="date" name="items[${terimaRowIndex}][expired_tgl]" class="form-control">
            </td>
            <td>
                <select name="items[${terimaRowIndex}][grade_cd]" class="form-control">
                    <option value="A" selected>Grade A</option>
                    <option value="B">Grade B</option>
                    <option value="REJECT">Reject</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.0001" min="0.0001" name="items[${terimaRowIndex}][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
            </td>
            <td>
                <input type="number" step="0.0001" min="0" name="items[${terimaRowIndex}][reject_qty]" value="0" class="form-control" placeholder="0" style="text-align: right;">
            </td>
            <td style="text-align: center;">
                <span class="row-satuan" style="font-weight: 600; color: #475569; font-size: 0.8rem;">-</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${terimaRowIndex}][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right;">
            </td>
            <td style="text-align: center;">
                <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem;" title="Hapus Baris">&times;</button>
            </td>
        `;

        container.appendChild(tr);
        terimaRowIndex++;
        updateTerimaRowNumbers();
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
        const satuan = selectedOption.dataset.satuan || '-';
        const cd = selectedOption.dataset.cd || 'ITEM';
        const cleanCd = cd.replace(/[^A-Za-z0-9]/g, '');
        const defaultHarga = parseFloat(selectedOption.dataset.harga || 0);

        row.querySelector('.row-satuan').innerText = satuan;

        const dateStr = "{{ date('ymd') }}";
        const idx = row.dataset.index || '01';
        row.querySelector('.item-batch').value = `BATCH-${cleanCd}-${dateStr}-${idx}`;

        const hargaInput = row.querySelector('.item-harga') || row.querySelector('input[name*="[harga_nominal]"]');
        if (hargaInput && defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
            hargaInput.value = defaultHarga;
        }
    }

    function calculateTotalTerima() {
        let total = 0;
        document.querySelectorAll('.item-terima-qty').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('totalTerimaQtyDisplay').innerText = total.toFixed(2);
    }

    // Run initial calculate
    calculateTotalTerima();
</script>
@endsection
