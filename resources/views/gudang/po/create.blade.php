@extends('layouts.app')

@section('title', 'Buat Purchase Order Baru - ERP PT Mirasa')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('gudang.po.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        &larr; Kembali ke Daftar PO
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Buat Purchase Order (PO) Baru</h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Formulir pemesanan material & bahan baku singkong/bumbu/kemasan ke mitra supplier.</p>
</div>

@if (!empty($belowMinimumList) && $belowMinimumList->count() > 0)
    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <strong style="color: #b45309; font-size: 0.9375rem; display: flex; align-items: center; gap: 0.35rem;">
                <span>⚠️</span> Peringatan Stok Minimum (Reorder Point Alert):
            </strong>
            <button type="button" onclick="addAllBelowMinimumItems()" class="btn btn-sm" style="background: #d97706; color: #ffffff; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.65rem; border-radius: 5px;">
                ⚡ Masukkan Semua Bahan Menipis ke PO
            </button>
        </div>
        <p style="color: #92400e; font-size: 0.8125rem; margin-top: 0.35rem; margin-bottom: 0.65rem;">
            Terdapat <strong>{{ $belowMinimumList->count() }}</strong> bahan baku/penolong yang stok fisiknya di bawah batas safety stock pabrik. Klik tombol di bawah untuk otomatis memasukkan ke tabel PO:
        </p>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @foreach ($belowMinimumList as $bm)
                @php
                    $deficit = max(1, (float)($bm->batas_minimum_qty - $bm->current_stock));
                @endphp
                <div style="display: inline-flex; align-items: center; background: #ffffff; border: 1px solid #fde68a; border-radius: 6px; padding: 0.25rem 0.5rem; gap: 0.4rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <span style="font-weight: 700; color: #92400e; font-size: 0.8rem;">
                        {{ $bm->barang_nm }}
                    </span>
                    <span style="font-size: 0.75rem; color: #b45309; background: #fef3c7; padding: 0.1rem 0.35rem; border-radius: 4px;">
                        Sisa: {{ number_format($bm->current_stock, 0) }} / Min: {{ number_format($bm->batas_minimum_qty, 0) }} {{ $bm->satuanDasar?->satuan_cd }}
                    </span>
                    <button type="button" 
                            onclick="addBelowMinimumItem({{ $bm->barang_id }}, '{{ addslashes($bm->barang_nm) }}', '{{ $bm->satuanDasar?->satuan_nm ?? '-' }}', {{ (float)($bm->harga_beli_standar ?? 0) }}, {{ $deficit }})"
                            style="background: #d97706; color: #ffffff; border: none; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem; cursor: pointer;"
                            title="Klik untuk langsung tambah ke tabel pesanan">
                        + Tambah ({{ number_format($deficit, 0) }})
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif

<form action="{{ route('gudang.po.store') }}" method="POST" id="formPo">
    @csrf

    {{-- KARTU 1: INFORMASI HEADER DOKUMEN --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a; font-size: 1rem;">1. Informasi Utama Dokumen</strong>
        </div>
        <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="po_no" class="form-label">Nomor PO <span style="color:#ef4444;">*</span></label>
                <input type="text" id="po_no" name="po_no" value="{{ old('po_no', $nextPoNo ?? '') }}" class="form-control" required>
                <small style="color: #64748b; font-size: 0.75rem;">Nomor urut otomatis sistem bulanan.</small>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="po_tgl" class="form-label">Tanggal PO <span style="color:#ef4444;">*</span></label>
                <input type="date" id="po_tgl" name="po_tgl" value="{{ old('po_tgl', date('Y-m-d')) }}" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="tgl_estimasi_datang" class="form-label">Estimasi Tanggal Tiba</label>
                <input type="date" id="tgl_estimasi_datang" name="tgl_estimasi_datang" value="{{ old('tgl_estimasi_datang') }}" class="form-control">
                <small style="color: #64748b; font-size: 0.75rem;">Perkiraan tanggal pengiriman barang tiba di pabrik.</small>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="supplier_id" class="form-label">Supplier Mitra <span style="color:#ef4444;">*</span></label>
                <select id="supplier_id" name="supplier_id" class="form-control" required>
                    <option value="">-- Pilih Supplier Mitra --</option>
                    @foreach ($supplierList as $sup)
                        <option value="{{ $sup->supplier_id }}" {{ old('supplier_id') == $sup->supplier_id ? 'selected' : '' }}>
                            {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="gudang_id" class="form-label">
                    Gudang Tujuan Masuk <span style="color:#ef4444;">*</span>
                    @if (!empty($isGudangLocked))
                        <span class="badge" style="background:#dcfce7; color:#15803d; font-size:0.75rem; margin-left:0.25rem;">
                            🔒 Terkunci (Lokasi Akun Anda)
                        </span>
                    @endif
                </label>
                @if (!empty($isGudangLocked))
                    <input type="hidden" name="gudang_id" value="{{ $assignedGudangId }}">
                    <select id="gudang_id" class="form-control" disabled style="background: #f8fafc; color: #1e293b; font-weight: 600; cursor: not-allowed;">
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ $assignedGudangId == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <select id="gudang_id" name="gudang_id" class="form-control" required>
                        <option value="">-- Pilih Gudang Masuk --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $assignedGudangId ?? '') == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label for="catatan_txt" class="form-label">Catatan / Keterangan Khusus</label>
                <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control" placeholder="Contoh: Estimasi pengiriman hari Jumat jam 08.00 pagi, singkong kadar air standar pabrik.">{{ old('catatan_txt') }}</textarea>
            </div>
        </div>
    </div>

    {{-- KARTU 2: RINCIAN ITEM BARANG (DETAIL TABLE - EXCEL STYLE) --}}
    <div class="card" style="margin-bottom: 1.5rem; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e1; padding: 0.75rem 1.25rem;">
            <div>
                <strong style="color: #0f172a; font-size: 1rem;">2. Rincian Barang yang Dipesan</strong>
                <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                    💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> pada kolom kuantitas/harga untuk langsung tambah baris baru
                </span>
            </div>
            <button type="button" onclick="addRow(true)" class="btn btn-primary btn-sm" style="font-weight: 600; background: #0284c7; padding: 0.4rem 0.85rem;">
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
                padding: 0.55rem 0.5rem;
                border: 1px solid #334155;
                font-size: 0.775rem;
                letter-spacing: 0.03em;
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
                border-color: #0284c7 !important;
                box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25) !important;
                background: #ffffff !important;
            }
        </style>

        <div style="overflow-x: auto;">
            <table class="excel-grid-table" id="tableItems">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">No</th>
                        <th style="min-width: 260px; text-align: left;">Nama Komoditas / Bahan Baku <span style="color:#ef4444;">*</span></th>
                        <th style="width: 110px; text-align: center;">Satuan</th>
                        <th style="width: 150px; text-align: right;">Kuantitas Pesanan <span style="color:#ef4444;">*</span></th>
                        <th style="width: 180px; text-align: right;">Harga Satuan (Rp)</th>
                        <th style="width: 180px; text-align: right;">Subtotal (Rp)</th>
                        <th style="width: 50px; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="itemsContainer">
                    {{-- Row pertama bawaan --}}
                    <tr class="item-row" data-index="0">
                        <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">1</td>
                        <td>
                            <select name="items[0][barang_id]" class="form-control item-barang" onchange="updateRowSatuan(this)" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach($barangList as $b)
                                    <option value="{{ $b->barang_id }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                                        {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td style="text-align: center;">
                            <span class="row-satuan" style="font-weight: 700; color: #475569;">-</span>
                        </td>
                        <td>
                            <input type="number" step="0.0001" min="0.0001" name="items[0][pesan_qty]" class="form-control item-qty" value="1" oninput="calculateSubtotal(this)" style="text-align: right; font-weight: 700;" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="items[0][harga_nominal]" class="form-control item-harga" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace; font-size: 0.9rem;" class="row-subtotal">
                            Rp 0
                        </td>
                        <td style="text-align: center;">
                            <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                        <td colspan="3" style="text-align: right; padding: 0.75rem 1rem; color: #334155;">Total Kuantitas Pesanan:</td>
                        <td id="totalQtyDisplay" style="padding: 0.75rem 0.5rem; text-align: right; color: #0284c7; font-size: 1.05rem;">1.00</td>
                        <td style="text-align: right; padding: 0.75rem 1rem; color: #334155;">Grand Total Pesanan:</td>
                        <td id="grandTotalDisplay" style="text-align: right; padding: 0.75rem 0.5rem; font-size: 1.15rem; color: #0284c7; font-family: monospace;">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- TOMBOL AKSI FORM --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <span style="font-size: 0.825rem; color: #64748b;">
            Semua perubahan akan langsung disimpan ke database dan nomor PO dialokasikan otomatis.
        </span>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('gudang.po.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.75rem; font-size: 0.95rem; font-weight: 700;">
                ✓ Simpan & Terbitkan PO
            </button>
        </div>
    </div>
</form>

<script>
    let rowIndex = 1;

    function addRow(focusNew = false) {
        const container = document.getElementById('itemsContainer');
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = rowIndex;

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">${rowIndex + 1}</td>
            <td>
                <select name="items[${rowIndex}][barang_id]" class="form-control item-barang" onchange="updateRowSatuan(this)" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barangList as $b)
                        <option value="{{ $b->barang_id }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td style="text-align: center;">
                <span class="row-satuan" style="font-weight: 700; color: #475569;">-</span>
            </td>
            <td>
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][pesan_qty]" class="form-control item-qty" value="1" oninput="calculateSubtotal(this)" style="text-align: right; font-weight: 700;" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_nominal]" class="form-control item-harga" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
            </td>
            <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace; font-size: 0.9rem;" class="row-subtotal">
                Rp 0
            </td>
            <td style="text-align: center;">
                <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
            </td>
        `;

        container.appendChild(tr);
        rowIndex++;
        updateRowNumbers();
        attachExcelKeyboardEvents(tr);

        if (focusNew) {
            tr.querySelector('.item-barang').focus();
        }
        return tr;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 baris item barang dalam Purchase Order.');
            return;
        }
        btn.closest('tr').remove();
        updateRowNumbers();
        calculateGrandTotal();
    }

    function updateRowNumbers() {
        document.querySelectorAll('.item-row').forEach((row, idx) => {
            row.querySelector('.row-num').innerText = idx + 1;
        });
    }

    function updateRowSatuan(selectElem) {
        const row = selectElem.closest('tr');
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const satuan = selectedOption.dataset.satuan || '-';
        const defaultHarga = parseFloat(selectedOption.dataset.harga || 0);

        row.querySelector('.row-satuan').innerText = satuan;

        const hargaInput = row.querySelector('.item-harga');
        if (defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
            hargaInput.value = defaultHarga;
            calculateSubtotal(hargaInput);
        }
    }

    function calculateSubtotal(inputElem) {
        const row = inputElem.closest('tr');
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const harga = parseFloat(row.querySelector('.item-harga').value) || 0;
        const subtotal = qty * harga;

        row.querySelector('.row-subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let totalQty = 0;
        let grandTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const harga = parseFloat(row.querySelector('.item-harga').value) || 0;
            totalQty += qty;
            grandTotal += (qty * harga);
        });

        document.getElementById('totalQtyDisplay').innerText = totalQty.toFixed(2);
        document.getElementById('grandTotalDisplay').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    // 1-Click Reorder untuk bahan baku yang menipis
    function addBelowMinimumItem(barangId, barangNm, satuanNm, defaultHarga, deficitQty) {
        const rows = document.querySelectorAll('.item-row');
        let targetRow = null;

        // Cek apakah baris pertama masih kosong belum dipilih barang
        if (rows.length === 1 && !rows[0].querySelector('.item-barang').value) {
            targetRow = rows[0];
        } else {
            // Cek apakah barang sudah ada di tabel
            for (let r of rows) {
                if (r.querySelector('.item-barang').value == barangId) {
                    const qtyInput = r.querySelector('.item-qty');
                    qtyInput.value = parseFloat(qtyInput.value) + deficitQty;
                    calculateSubtotal(qtyInput);
                    qtyInput.focus();
                    return;
                }
            }
            targetRow = addRow(false);
        }

        const select = targetRow.querySelector('.item-barang');
        select.value = barangId;
        targetRow.querySelector('.row-satuan').innerText = satuanNm;
        targetRow.querySelector('.item-qty').value = deficitQty;
        targetRow.querySelector('.item-harga').value = defaultHarga;
        calculateSubtotal(targetRow.querySelector('.item-qty'));
    }

    function addAllBelowMinimumItems() {
        @if (!empty($belowMinimumList) && $belowMinimumList->count() > 0)
            @foreach ($belowMinimumList as $bm)
                addBelowMinimumItem(
                    {{ $bm->barang_id }}, 
                    '{{ addslashes($bm->barang_nm) }}', 
                    '{{ $bm->satuanDasar?->satuan_nm ?? '-' }}', 
                    {{ (float)($bm->harga_beli_standar ?? 0) }}, 
                    {{ max(1, (float)($bm->batas_minimum_qty - $bm->current_stock)) }}
                );
            @endforeach
        @endif
    }

    // Excel Keyboard Navigation (Enter to move or create new row)
    function attachExcelKeyboardEvents(rowElement) {
        const inputs = rowElement.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const allRows = Array.from(document.querySelectorAll('.item-row'));
                    const currentRowIdx = allRows.indexOf(rowElement);

                    if (input.classList.contains('item-harga') || input.classList.contains('item-qty')) {
                        if (currentRowIdx === allRows.length - 1) {
                            // Di baris terakhir -> buat baris baru dan langsung fokus
                            addRow(true);
                        } else {
                            // Pindah ke input kolom yang sama di baris bawahnya
                            const nextRow = allRows[currentRowIdx + 1];
                            const target = nextRow.querySelector('.' + input.classList[1]);
                            if (target) target.focus();
                        }
                    }
                }
            });
        });
    }

    // Attach initial rows
    document.querySelectorAll('.item-row').forEach(r => attachExcelKeyboardEvents(r));
</script>
@endsection
