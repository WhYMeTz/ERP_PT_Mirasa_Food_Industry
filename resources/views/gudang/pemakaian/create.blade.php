@extends('layouts.app')

@section('title', 'Catat Barang Keluar / Pemakaian Bahan - ERP PT Mirasa')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('gudang.pemakaian.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem;">
        &larr; Kembali ke Daftar Barang Keluar
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.5rem;">
        Form Pengeluaran / Pemakaian Bahan
    </h1>
    <p style="color: #64748b; font-size: 0.875rem;">
        Pencatatan pemakaian bahan baku/penolong ke lini produksi atau packing dengan pemotongan stok otomatis (FIFO).
    </p>
</div>

<form action="{{ route('gudang.pemakaian.store') }}" method="POST" id="pemakaianForm">
    @csrf

    {{-- KARTU HEADER --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">Informasi Pengeluaran</h2>
            <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 600;">Transaksi Outbound</span>
        </div>
        <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Nomor Dokumen
                </label>
                <input type="text" name="pakai_no" value="{{ old('pakai_no', $autoNo) }}" class="form-control" style="font-family: monospace; font-weight: 600;" required>
                <small style="color: #64748b; font-size: 0.75rem;">Otomatis dibuat oleh sistem (bisa disesuaikan).</small>
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Tanggal Pengeluaran <span style="color: #ef4444;">*</span>
                </label>
                <input type="date" name="pakai_tgl" value="{{ old('pakai_tgl', date('Y-m-d')) }}" class="form-control" required>
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Gudang Asal Barang <span style="color: #ef4444;">*</span>
                </label>
                @if ($userGudangId)
                    @php $lockedGdg = $gudangList->firstWhere('gudang_id', $userGudangId); @endphp
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="text" class="form-control" value="{{ $lockedGdg?->display_name }} ({{ $lockedGdg?->gudang_cd }})" disabled style="background: #f1f5f9; font-weight: 600;">
                        <input type="hidden" name="gudang_id" id="gudang_id" value="{{ $userGudangId }}">
                        <span class="badge badge-success" style="white-space: nowrap;">🔒 Terkunci</span>
                    </div>
                @else
                    <select name="gudang_id" id="gudang_id" class="form-control" required onchange="onGudangChanged()">
                        <option value="">-- Pilih Lokasi Asal --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Tujuan Pemakaian / SPK <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="tujuan_pemakaian" id="tujuan_pemakaian" list="tujuanList" value="{{ old('tujuan_pemakaian', 'PRODUKSI IFM') }}" class="form-control" placeholder="Contoh: PRODUKSI IFM, PRODUKSI BWF, PACKING EKSPOR" required>
                <datalist id="tujuanList">
                    @foreach ($tujuanOptions as $opt)
                        <option value="{{ $opt }}"></option>
                    @endforeach
                </datalist>
                <small style="color: #64748b; font-size: 0.75rem;">Pilih saran atau ketik no SPK / Work Order.</small>
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Catatan Tambahan (Opsional)
                </label>
                <input type="text" name="catatan_txt" value="{{ old('catatan_txt') }}" placeholder="Keterangan shift kerja, operator penerima, dll." class="form-control">
            </div>
        </div>
    </div>

    {{-- KARTU REKOMENDASI RESEP PRODUKSI (AUTO-FIFO) --}}
    <div class="card" style="margin-bottom: 1.5rem; border: 1.5px solid #93c5fd; background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%); box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.08);">
        <div class="card-header" style="background: transparent; border-bottom: 1px solid #bfdbfe; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.35rem;">⚡</span>
                <div>
                    <h2 style="font-size: 1.05rem; font-weight: 700; color: #1e3a8a; margin: 0;">
                        Tarik Kebutuhan Bahan Berdasarkan Resep Produksi (Auto-FIFO)
                    </h2>
                    <p style="color: #2563eb; font-size: 0.8rem; margin: 0.15rem 0 0 0;">
                        Kalkulasi otomatis proporsi bahan baku & alokasi nomor batch terlama yang dibeli (FIFO/FEFO).
                    </p>
                </div>
            </div>
            <span class="badge" style="background: #dbeafe; color: #1e40af; font-weight: 700; font-size: 0.75rem; padding: 0.35rem 0.65rem; border: 1px solid #93c5fd;">
                🛡️ Auto-Draft Pick List (Bukan Potong Buta)
            </span>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; align-items: flex-end;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #1e3a8a; margin-bottom: 0.35rem;">
                        Pilih Formula Resep Produk (BOM)
                    </label>
                    <select id="bom_select" class="form-control" style="font-weight: 600; border-color: #93c5fd; font-size: 0.875rem;">
                        <option value="">-- Pilih Formula Resep Standar --</option>
                        @foreach ($bomList as $bom)
                            <option value="{{ $bom->bom_id }}" data-nomor="{{ $bom->bom_no }}" data-nama="{{ $bom->bom_nm }}" data-batch="{{ (float) $bom->batch_ukuran_qty }}">
                                [{{ $bom->bom_no }}] {{ $bom->bom_nm }} (Basis: {{ number_format($bom->batch_ukuran_qty, 0, ',', '.') }} Karton)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="max-width: 220px;">
                    <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #1e3a8a; margin-bottom: 0.35rem;">
                        Target Rencana Produksi
                    </label>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <input type="number" id="target_produksi_qty" value="100" min="1" step="1" class="form-control" style="font-weight: 700; font-size: 1rem; text-align: right; border-color: #93c5fd;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">Karton</span>
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" id="btnTarikResep" class="btn btn-primary" onclick="tarikBahanResepFifo()" style="background: #2563eb; border-color: #1d4ed8; font-weight: 700; padding: 0.6rem 1.25rem; font-size: 0.875rem; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                        <span id="btnTarikText">⚡ Muat Batch FIFO Tertua</span>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetItemsTable()" style="background: #ffffff; border-color: #cbd5e1; color: #64748b;" title="Kosongkan Baris">
                        Reset Baris
                    </button>
                </div>
            </div>

            {{-- Container Notifikasi / Status Alokasi --}}
            <div id="resepFeedback" style="margin-top: 1rem; display: none;"></div>
        </div>
    </div>

    {{-- KARTU RINCIAN BARANG --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">Rincian Bahan yang Dikeluarkan</h2>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">
                + Tambah Baris Barang
            </button>
        </div>

        <div style="overflow-x: auto; padding: 1rem;">
            <table style="width: 100%;" id="itemsTable">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; text-align: left; font-size: 0.85rem; color: #475569;">
                        <th style="padding: 0.5rem; width: 28%;">Nama Barang</th>
                        <th style="padding: 0.5rem; width: 25%;">Pilih Batch (Sisa Stok)</th>
                        <th style="padding: 0.5rem; width: 13%;">Qty Keluar</th>
                        <th style="padding: 0.5rem; width: 14%;">Harga Satuan (Rp)</th>
                        <th style="padding: 0.5rem; width: 15%; text-align: right;">Total (Rp)</th>
                        <th style="padding: 0.5rem; width: 5%; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    {{-- Row Template rendered via JS --}}
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e2e8f0; font-weight: 700; background: #f8fafc;">
                        <td colspan="2" style="padding: 0.75rem 0.5rem; text-align: right;">Total Keseluruhan:</td>
                        <td style="padding: 0.75rem 0.5rem; color: #dc2626;" id="grandTotalQty">0,00</td>
                        <td></td>
                        <td style="padding: 0.75rem 0.5rem; text-align: right; color: #0f172a; font-size: 1.05rem;" id="grandTotalNilai">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-bottom: 3rem;">
        <a href="{{ route('gudang.pemakaian.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" style="background: #dc2626; padding: 0.65rem 1.75rem; font-size: 0.95rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan & Potong Stok Sekarang
        </button>
    </div>
</form>

{{-- Data Barang Cache untuk Client-side JS --}}
<script>
    const BARANG_LIST = @json($barangList);
    let rowIndex = 0;

    function getSelectedGudangId() {
        const el = document.getElementById('gudang_id');
        return el ? el.value : '';
    }

    function onGudangChanged() {
        // Reset all batch selections when warehouse changes
        document.querySelectorAll('.batch-select').forEach(select => {
            const row = select.closest('tr');
            const barangSelect = row.querySelector('.barang-select');
            if (barangSelect && barangSelect.value) {
                fetchBatchesForRow(row, barangSelect.value);
            }
        });
    }

    function addRow() {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;
        tr.style.borderBottom = '1px solid #f1f5f9';

        let barangOptions = '<option value="">-- Pilih Barang --</option>';
        BARANG_LIST.forEach(b => {
            const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
            const harga = b.harga_beli_standar || 0;
            barangOptions += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${b.satuan_dasar?.satuan_nm || ''}">[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
        });

        tr.innerHTML = `
            <td style="padding: 0.5rem;">
                <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" style="font-size: 0.85rem;" required onchange="onBarangSelect(this)">
                    ${barangOptions}
                </select>
                <div class="satuan-label" style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;"></div>
            </td>
            <td style="padding: 0.5rem;">
                <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" style="font-size: 0.85rem;" required onchange="onBatchSelect(this)">
                    <option value="">-- Pilih Barang Dulu --</option>
                </select>
                <div class="batch-info" style="font-size: 0.75rem; color: #059669; font-weight: 600; margin-top: 0.2rem;"></div>
            </td>
            <td style="padding: 0.5rem;">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" class="form-control qty-input" placeholder="0" style="font-weight: 700; text-align: right; font-size: 0.9rem;" required oninput="calcRow(this)">
            </td>
            <td style="padding: 0.5rem;">
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" class="form-control harga-input" placeholder="0" style="text-align: right; font-size: 0.85rem;" oninput="calcRow(this)">
            </td>
            <td style="padding: 0.5rem; text-align: right; font-weight: 700; color: #0f172a; font-size: 0.9rem;" class="subtotal-cell">
                Rp 0
            </td>
            <td style="padding: 0.5rem; text-align: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)" style="color: #ef4444; padding: 0.25rem 0.5rem;" title="Hapus Baris">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        rowIndex++;
    }

    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calculateGrandTotal();
        } else {
            alert('Minimal harus ada 1 item barang yang dikeluarkan.');
        }
    }

    function resetItemsTable() {
        const tbody = document.getElementById('itemsBody');
        tbody.innerHTML = '';
        rowIndex = 0;
        addRow();
        calculateGrandTotal();
        const feedback = document.getElementById('resepFeedback');
        if (feedback) feedback.style.display = 'none';
    }

    function addRowFromAllocation(item) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;
        tr.style.borderBottom = '1px solid #f1f5f9';

        let foundInList = false;
        let barangOptions = '<option value="">-- Pilih Barang --</option>';
        BARANG_LIST.forEach(b => {
            const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
            const harga = b.harga_beli_standar || 0;
            const isSelected = (parseInt(b.barang_id) === parseInt(item.barang_id)) ? 'selected' : '';
            if (isSelected) foundInList = true;
            barangOptions += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${b.satuan_dasar?.satuan_nm || ''}" ${isSelected}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
        });
        if (!foundInList && item.barang_id) {
            barangOptions += `<option value="${item.barang_id}" selected>[${item.barang_cd}] ${item.barang_nm}</option>`;
        }

        let batchOptions = '';
        if (!item.all_batches || item.all_batches.length === 0) {
            batchOptions = '<option value="">(Stok Fisik Habis di Gudang ini)</option>';
        } else {
            item.all_batches.forEach(b => {
                const isSelected = (b.batch_no === item.batch_no) ? 'selected' : '';
                const prefix = b.is_fifo_top ? '⭐ [FIFO PRIORITAS] ' : '• ';
                const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
                const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';
                batchOptions += `<option value="${b.batch_no}" data-sisa="${b.sisa_qty}" data-harga="${b.harga_satuan || 0}" data-masuk="${b.tgl_terima}" data-exp="${b.expired_tgl || '-'}" ${isSelected}>
                    ${prefix}${b.batch_no} (Sisa: ${parseFloat(b.sisa_qty).toLocaleString('id-ID')})${tglTerima}${expInfo}
                </option>`;
            });
        }

        let badgeHtml = '';
        if (item.is_allocated) {
            const badgeBg = item.is_split ? '#fef3c7' : '#ecfdf5';
            const badgeColor = item.is_split ? '#92400e' : '#065f46';
            const badgeBorder = item.is_split ? '#fde68a' : '#a7f3d0';
            badgeHtml = `<span style="color: ${badgeColor}; font-weight: 700; background: ${badgeBg}; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid ${badgeBorder}; display: inline-flex; align-items: center; gap: 0.25rem;">
                ${item.catatan_fifo} • Sisa: ${item.sisa_batch.toLocaleString('id-ID')} unit
            </span>`;
        } else {
            badgeHtml = `<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #fecaca; display: inline-block;">
                ${item.catatan_fifo}
            </span>`;
        }

        const subtotal = (parseFloat(item.qty_keluar) || 0) * (parseFloat(item.harga_satuan) || 0);

        tr.innerHTML = `
            <td style="padding: 0.5rem;">
                <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" style="font-size: 0.85rem;" required onchange="onBarangSelect(this)">
                    ${barangOptions}
                </select>
                <div class="satuan-label" style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">Satuan: ${item.satuan_nm}</div>
            </td>
            <td style="padding: 0.5rem;">
                <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" style="font-size: 0.85rem;" required onchange="onBatchSelect(this)">
                    ${batchOptions}
                </select>
                <div class="batch-info" style="font-size: 0.75rem; margin-top: 0.2rem;">
                    ${badgeHtml}
                </div>
            </td>
            <td style="padding: 0.5rem;">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" value="${item.qty_keluar}" class="form-control qty-input" placeholder="0" style="font-weight: 700; text-align: right; font-size: 0.9rem;" required oninput="calcRow(this)">
            </td>
            <td style="padding: 0.5rem;">
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" value="${item.harga_satuan}" class="form-control harga-input" placeholder="0" style="text-align: right; font-size: 0.85rem;" oninput="calcRow(this)">
            </td>
            <td style="padding: 0.5rem; text-align: right; font-weight: 700; color: #0f172a; font-size: 0.9rem;" class="subtotal-cell">
                Rp ${subtotal.toLocaleString('id-ID', { minimumFractionDigits: 2 })}
            </td>
            <td style="padding: 0.5rem; text-align: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)" style="color: #ef4444; padding: 0.25rem 0.5rem;" title="Hapus Baris">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        rowIndex++;
    }

    function tarikBahanResepFifo() {
        const gudangId = getSelectedGudangId();
        if (!gudangId) {
            alert('⚠️ Harap pilih Gudang Asal Barang terlebih dahulu di formulir bagian atas!');
            document.getElementById('gudang_id')?.focus();
            return;
        }

        const bomSelect = document.getElementById('bom_select');
        const bomId = bomSelect ? bomSelect.value : '';
        if (!bomId) {
            alert('⚠️ Harap pilih Formula Resep Produk (BOM) terlebih dahulu!');
            bomSelect?.focus();
            return;
        }

        const targetQty = parseFloat(document.getElementById('target_produksi_qty').value) || 0;
        if (targetQty <= 0) {
            alert('⚠️ Target Rencana Produksi harus lebih dari 0 karton!');
            document.getElementById('target_produksi_qty')?.focus();
            return;
        }

        const btn = document.getElementById('btnTarikResep');
        const btnText = document.getElementById('btnTarikText');
        const feedback = document.getElementById('resepFeedback');

        btn.disabled = true;
        btnText.innerHTML = '⏳ Menghitung Alokasi Batch FIFO...';
        feedback.style.display = 'none';

        fetch(`{{ route('gudang.pemakaian.alokasi-resep') }}?gudang_id=${gudangId}&bom_id=${bomId}&target_qty=${targetQty}`)
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btnText.innerHTML = '⚡ Muat Batch FIFO Tertua';

                if (res.status === 'success') {
                    const data = res.data;
                    const items = data.items || [];

                    if (items.length === 0) {
                        alert('Resep ini belum memiliki rincian bahan baku terdaftar.');
                        return;
                    }

                    // Sesuaikan Tujuan Pemakaian otomatis sesuai resep jika masih default
                    const tujuanInput = document.getElementById('tujuan_pemakaian');
                    if (tujuanInput) {
                        const namaResep = (data.bom_nm || '').toUpperCase();
                        if (namaResep.includes('IFM') || namaResep.includes('INDOFOOD')) {
                            tujuanInput.value = 'PRODUKSI IFM';
                        } else if (namaResep.includes('2000')) {
                            tujuanInput.value = 'PRODUKSI PING-PING 2000';
                        } else if (namaResep.includes('PING-PING')) {
                            tujuanInput.value = 'PRODUKSI PING-PING';
                        } else {
                            tujuanInput.value = `PRODUKSI ${data.bom_no}`;
                        }
                    }

                    // Kosongkan baris tabel dan render baris alokasi FIFO
                    const tbody = document.getElementById('itemsBody');
                    tbody.innerHTML = '';
                    rowIndex = 0;

                    items.forEach(item => {
                        addRowFromAllocation(item);
                    });

                    calculateGrandTotal();

                    // Render banner status / feedback
                    feedback.style.display = 'block';
                    if (data.is_lengkap) {
                        feedback.innerHTML = `
                            <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 6px; padding: 0.75rem 1rem; color: #065f46; display: flex; align-items: flex-start; gap: 0.65rem;">
                                <span style="font-size: 1.25rem;">✅</span>
                                <div>
                                    <strong style="display: block; font-size: 0.875rem;">Alokasi Batch Tertua (FIFO) Berhasil Dimuat:</strong>
                                    <span style="font-size: 0.8rem; line-height: 1.4;">
                                        Total <strong>${items.length} baris bahan baku</strong> telah dialokasikan otomatis dari batch paling lama untuk target <strong>${targetQty} ${data.satuan_target}</strong>.
                                        Operator gudang dapat memeriksa dan menyesuaikan angka timbangan aktual jika terdapat deviasi sebelum klik simpan.
                                    </span>
                                </div>
                            </div>
                        `;
                    } else {
                        const peringatanItems = (data.peringatan || []).map(p => `<li>${p}</li>`).join('');
                        feedback.innerHTML = `
                            <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 6px; padding: 0.75rem 1rem; color: #92400e;">
                                <div style="display: flex; align-items: flex-start; gap: 0.65rem; margin-bottom: 0.35rem;">
                                    <span style="font-size: 1.25rem;">⚠️</span>
                                    <div>
                                        <strong style="display: block; font-size: 0.875rem;">Perhatian: Ada Bahan Fisik yang Kurang di Gudang Ini</strong>
                                        <span style="font-size: 0.8rem;">
                                            Sistem berhasil memuat batch tertua yang tersedia, namun beberapa item tidak mencukupi untuk memenuhi seluruh kebutuhan resep (${targetQty} ${data.satuan_target}):
                                        </span>
                                    </div>
                                </div>
                                <ul style="margin: 0.35rem 0 0 1.75rem; font-size: 0.8rem; padding: 0; line-height: 1.5;">
                                    ${peringatanItems}
                                </ul>
                            </div>
                        `;
                    }

                    // Smooth scroll ke tabel rincian bahan
                    document.getElementById('itemsTable')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert('Gagal: ' + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btnText.innerHTML = '⚡ Muat Batch FIFO Tertua';
                alert('Terjadi kesalahan koneksi saat menghitung alokasi resep.');
            });
    }

    function onBarangSelect(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOpt = selectEl.selectedOptions[0];
        const barangId = selectEl.value;
        const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : '';
        const defaultHarga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;

        row.querySelector('.satuan-label').textContent = satuan ? `Satuan: ${satuan}` : '';
        const hargaInput = row.querySelector('.harga-input');
        if (defaultHarga > 0) {
            hargaInput.value = defaultHarga;
        }

        if (barangId) {
            fetchBatchesForRow(row, barangId);
        } else {
            const batchSelect = row.querySelector('.batch-select');
            batchSelect.innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
            row.querySelector('.batch-info').textContent = '';
        }

        calcRow(selectEl);
    }

    function getSelectedGudangId() {
        const el = document.getElementById('gudang_id');
        return el ? el.value : '';
    }

    function onGudangChanged() {
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            if (barangSelect && barangSelect.value) {
                fetchBatchesForRow(row, barangSelect.value);
            }
        });
    }

    function fetchBatchesForRow(row, barangId) {
        const gudangId = getSelectedGudangId();
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');

        if (!gudangId) {
            batchSelect.innerHTML = '<option value="">-- Pilih Gudang di Atas Dulu --</option>';
            batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 600;">⚠️ Gudang belum dipilih di bagian atas!</span>';
            return;
        }

        batchSelect.innerHTML = '<option value="">Memuat data batch FIFO...</option>';
        batchInfo.innerHTML = '<span style="color: #64748b;">Memeriksa stok fisik & urutan batch...</span>';

        fetch(`{{ route('gudang.pemakaian.batches') }}?gudang_id=${gudangId}&barang_id=${barangId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if (!res.batches || res.batches.length === 0) {
                        batchSelect.innerHTML = '<option value="">(Stok Fisik Habis di Gudang ini)</option>';
                        batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px;">⚠️ Stok fisik habis total di gudang ini.</span>';
                        row.querySelector('.harga-input').value = 0;
                    } else {
                        let html = '';
                        res.batches.forEach((b, idx) => {
                            const isTop = (idx === 0);
                            const prefix = isTop ? '⭐ [FIFO PRIORITAS] ' : '• ';
                            const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
                            const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';
                            html += `<option value="${b.batch_no}" data-sisa="${b.sisa_qty}" data-harga="${b.harga_satuan || 0}" data-masuk="${b.tgl_terima}" data-exp="${b.expired_tgl || '-'}" ${isTop ? 'selected' : ''}>
                                ${prefix}${b.batch_no} (Sisa: ${parseFloat(b.sisa_qty).toLocaleString('id-ID')})${tglTerima}${expInfo}
                            </option>`;
                        });
                        batchSelect.innerHTML = html;

                        // Otomatis pilih batch pertama (FIFO Prioritas) & isi harga beli riil
                        onBatchSelect(batchSelect);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
                batchInfo.innerHTML = '<span style="color: #dc2626;">Koneksi gagal saat mengambil batch.</span>';
            });
    }

    function onBatchSelect(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOpt = selectEl.selectedOptions[0];
        if (!selectedOpt || !selectedOpt.value) return;

        const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);
        const tglMasuk = selectedOpt.getAttribute('data-masuk') || '-';
        const batchInfo = row.querySelector('.batch-info');
        const isFirstBatch = (selectEl.selectedIndex === 0);

        if (isFirstBatch) {
            batchInfo.innerHTML = `<span style="color: #065f46; font-weight: 700; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 0.25rem;">
                ⭐ Rekomendasi FIFO: Batch masuk paling awal (${tglMasuk}) • Maks: ${sisa.toLocaleString('id-ID')} unit
            </span>`;
        } else {
            batchInfo.innerHTML = `<span style="color: #0369a1; font-weight: 600; background: #f0f9ff; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #bae6fd;">
                Pilihan Manual: Masuk ${tglMasuk} • Maks: ${sisa.toLocaleString('id-ID')} unit
            </span>`;
        }

        const hargaInput = row.querySelector('.harga-input');
        if (harga > 0) {
            hargaInput.value = harga;
        }

        calcRow(selectEl);
    }

    function calcRow(el) {
        const row = el.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const hargaInput = row.querySelector('.harga-input');
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');
        const selectedBatchOpt = batchSelect ? batchSelect.selectedOptions[0] : null;

        const qty = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const sisa = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-sisa') || 0) : Infinity;

        // Warning tegas jika qty yang diminta melebihi sisa fisik batch
        if (selectedBatchOpt && selectedBatchOpt.value && qty > sisa) {
            qtyInput.style.borderColor = '#dc2626';
            qtyInput.style.backgroundColor = '#fef2f2';
            batchInfo.innerHTML = `<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid #fecaca; display: inline-block;">
                ⚠️ Melebihi sisa batch (${sisa.toLocaleString('id-ID')}). Ambil ${sisa.toLocaleString('id-ID')} di baris ini, lalu klik "+ Tambah Baris" untuk sisa ${(qty - sisa).toLocaleString('id-ID')} dari batch berikutnya.
            </span>`;
        } else if (selectedBatchOpt && selectedBatchOpt.value) {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.backgroundColor = '#ffffff';
            const tglMasuk = selectedBatchOpt.getAttribute('data-masuk') || '-';
            const isFirstBatch = (batchSelect.selectedIndex === 0);
            if (isFirstBatch) {
                batchInfo.innerHTML = `<span style="color: #065f46; font-weight: 700; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0;">
                    ⭐ Rekomendasi FIFO: Batch masuk paling awal (${tglMasuk}) • Maks: ${sisa.toLocaleString('id-ID')} unit
                </span>`;
            } else {
                batchInfo.innerHTML = `<span style="color: #0369a1; font-weight: 600; background: #f0f9ff; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #bae6fd;">
                    Pilihan Manual: Masuk ${tglMasuk} • Maks: ${sisa.toLocaleString('id-ID')} unit
                </span>`;
            }
        }

        const subtotal = qty * harga;
        row.querySelector('.subtotal-cell').textContent = 'Rp ' + subtotal.toLocaleString('id-ID', { minimumFractionDigits: 2 });

        calculateGrandTotal();
    }


    function calculateGrandTotal() {
        let totalQty = 0;
        let totalNilai = 0;

        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
            const harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;
            totalQty += qty;
            totalNilai += (qty * harga);
        });

        document.getElementById('grandTotalQty').textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2 });
        document.getElementById('grandTotalNilai').textContent = 'Rp ' + totalNilai.toLocaleString('id-ID', { minimumFractionDigits: 2 });
    }

    // Inisialisasi baris pertama saat form dibuka
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endsection
