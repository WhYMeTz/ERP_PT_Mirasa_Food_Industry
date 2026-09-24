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
                        <input type="text" class="form-control" value="{{ $lockedGdg?->gudang_nm }} ({{ $lockedGdg?->gudang_cd }})" disabled style="background: #f1f5f9; font-weight: 600;">
                        <input type="hidden" name="gudang_id" id="gudang_id" value="{{ $userGudangId }}">
                        <span class="badge badge-success" style="white-space: nowrap;">🔒 Terkunci</span>
                    </div>
                @else
                    <select name="gudang_id" id="gudang_id" class="form-control" required onchange="onGudangChanged()">
                        <option value="">-- Pilih Gudang Asal --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Tujuan Pemakaian / SPK <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="tujuan_pemakaian" id="tujuan_pemakaian" list="tujuanList" value="{{ old('tujuan_pemakaian', 'PRODUKSI IFM') }}" class="form-control" placeholder="Contoh: PRODUKSI IFM, PACKING EKSPOR" required>
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

    function fetchBatchesForRow(row, barangId) {
        const gudangId = getSelectedGudangId();
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');

        if (!gudangId) {
            batchSelect.innerHTML = '<option value="">-- Pilih Gudang di Atas --</option>';
            batchInfo.textContent = 'Gudang belum dipilih!';
            batchInfo.style.color = '#dc2626';
            return;
        }

        batchSelect.innerHTML = '<option value="">Memuat data batch...</option>';
        batchInfo.textContent = '';

        fetch(`{{ route('gudang.pemakaian.batches') }}?gudang_id=${gudangId}&barang_id=${barangId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if (res.batches.length === 0) {
                        batchSelect.innerHTML = '<option value="">(Stok Habis di Gudang ini)</option>';
                        batchInfo.textContent = '⚠️ Tidak ada batch dengan stok tersedia di gudang ini.';
                        batchInfo.style.color = '#dc2626';
                    } else {
                        let html = '<option value="">-- Pilih Batch --</option>';
                        res.batches.forEach(b => {
                            const exp = b.expired_tgl ? `Exp: ${b.expired_tgl}` : '';
                            html += `<option value="${b.batch_no}" data-sisa="${b.sisa_qty}" data-harga="${b.harga_satuan || 0}">
                                ${b.batch_no} (Sisa: ${parseFloat(b.sisa_qty).toLocaleString('id-ID')}) ${exp}
                            </option>`;
                        });
                        batchSelect.innerHTML = html;
                        batchInfo.textContent = `${res.batches.length} batch tersedia.`;
                        batchInfo.style.color = '#059669';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
            });
    }

    function onBatchSelect(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOpt = selectEl.selectedOptions[0];
        const sisa = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-sisa') || 0) : 0;
        const harga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;

        const batchInfo = row.querySelector('.batch-info');
        if (selectedOpt && selectedOpt.value) {
            batchInfo.textContent = `Maksimal keluar: ${sisa.toLocaleString('id-ID')} unit`;
            batchInfo.style.color = '#0284c7';
            
            const hargaInput = row.querySelector('.harga-input');
            if (harga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
                hargaInput.value = harga;
            }
        }

        calcRow(selectEl);
    }

    function calcRow(el) {
        const row = el.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const hargaInput = row.querySelector('.harga-input');
        const batchSelect = row.querySelector('.batch-select');
        const selectedBatchOpt = batchSelect ? batchSelect.selectedOptions[0] : null;

        const qty = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const sisa = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-sisa') || 0) : Infinity;

        // Warning jika qty > sisa
        if (selectedBatchOpt && selectedBatchOpt.value && qty > sisa) {
            qtyInput.style.borderColor = '#dc2626';
            qtyInput.style.backgroundColor = '#fef2f2';
        } else {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.backgroundColor = '#ffffff';
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
