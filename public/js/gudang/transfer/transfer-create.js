/**
 * Transfer Antar-Gudang Create JS
 * ERP PT Mirasa Food Industry
 */

let availableBatches = [];
let rowIndexCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    const asalSelect = document.getElementById('gudang_asal_id');
    if (asalSelect && asalSelect.value) {
        fetchAvailableBatches(asalSelect.value);
    }
});

function onGudangAsalChange(selectEl) {
    const gudangId = selectEl.value;
    const itemsTbody = document.getElementById('itemsBody');
    if (itemsTbody) {
        itemsTbody.innerHTML = '';
        rowIndexCounter = 0;
    }

    if (!gudangId) {
        availableBatches = [];
        updateLiveSummary();
        return;
    }

    // Pastikan gudang tujuan tidak sama
    const tujuanSelect = document.getElementById('gudang_tujuan_id');
    if (tujuanSelect && tujuanSelect.value === gudangId) {
        tujuanSelect.value = '';
    }

    fetchAvailableBatches(gudangId);
}

function fetchAvailableBatches(gudangId) {
    const statusNotice = document.getElementById('batchLoadingNotice');
    if (statusNotice) statusNotice.style.display = 'inline-block';

    fetch(`/gudang/transfer/batches?gudang_id=${gudangId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                availableBatches = res.data || [];
                // Tambahkan 1 baris awal jika kosong
                const itemsTbody = document.getElementById('itemsBody');
                if (itemsTbody && itemsTbody.children.length === 0) {
                    addItemRow();
                }
            } else {
                alert('Gagal memuat daftar stok barang: ' + (res.message || ''));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan saat memuat daftar stok barang.');
        })
        .finally(() => {
            if (statusNotice) statusNotice.style.display = 'none';
        });
}

function addItemRow() {
    const asalSelect = document.getElementById('gudang_asal_id');
    if (!asalSelect || !asalSelect.value) {
        alert('Silakan pilih Gudang Asal terlebih dahulu.');
        asalSelect.focus();
        return;
    }

    const tbody = document.getElementById('itemsBody');
    if (!tbody) return;

    const rowIdx = rowIndexCounter++;
    const tr = document.createElement('tr');
    tr.id = `itemRow_${rowIdx}`;

    // Buat options batch
    let batchOptionsHtml = '<option value="">-- Pilih Barang & Batch Stok --</option>';
    if (availableBatches.length === 0) {
        batchOptionsHtml = '<option value="">(Tidak ada stok tersedia di gudang ini)</option>';
    } else {
        availableBatches.forEach(b => {
            const barangNm = b.barang ? b.barang.barang_nm : `Barang #${b.barang_id}`;
            const satuan = b.barang && b.barang.satuan_dasar ? b.barang.satuan_dasar.satuan_nm : 'Unit';
            const sisa = parseFloat(b.sisa_qty || 0).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            const exp = b.expired_tgl ? ` [Exp: ${b.expired_tgl}]` : '';
            batchOptionsHtml += `<option value="${b.batch_no}" 
                data-barang-id="${b.barang_id}" 
                data-grade="${b.grade_cd || 'A'}" 
                data-sisa="${b.sisa_qty}" 
                data-satuan="${satuan}"
                data-harga="${b.harga_satuan || 0}">
                ${barangNm} &bull; Batch: ${b.batch_no} (${b.grade_cd || 'A'})${exp} - Sisa: ${sisa} ${satuan}
            </option>`;
        });
    }

    tr.innerHTML = `
        <td style="width: 45%;">
            <select name="items[${rowIdx}][batch_no]" class="form-control select-batch" required onchange="onBatchSelected(this, ${rowIdx})">
                ${batchOptionsHtml}
            </select>
            <input type="hidden" name="items[${rowIdx}][barang_id]" id="barangId_${rowIdx}">
            <input type="hidden" name="items[${rowIdx}][grade_cd]" id="gradeCd_${rowIdx}" value="A">
        </td>
        <td style="width: 15%; text-align: center;">
            <span id="sisaDisplay_${rowIdx}" style="font-weight: 700; color: #0f766e; font-family: monospace;">-</span>
            <span id="satuanDisplay_${rowIdx}" style="font-size: 0.725rem; color: #64748b;"></span>
        </td>
        <td style="width: 18%;">
            <input type="number" step="0.0001" min="0.0001" name="items[${rowIdx}][qty_transfer]" id="qtyInput_${rowIdx}" 
                   class="form-control" style="font-weight: 700; font-family: monospace; text-align: right;" 
                   placeholder="0.00" required oninput="onQtyChange(${rowIdx})">
        </td>
        <td style="width: 18%;">
            <input type="text" name="items[${rowIdx}][catatan_txt]" class="form-control" placeholder="Keterangan opsional">
        </td>
        <td style="width: 4%; text-align: center;">
            <button type="button" class="btn-remove-row" onclick="removeRow(${rowIdx})" title="Hapus baris">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
}

function onBatchSelected(selectEl, rowIdx) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const barangIdInput = document.getElementById(`barangId_${rowIdx}`);
    const gradeCdInput = document.getElementById(`gradeCd_${rowIdx}`);
    const sisaDisplay = document.getElementById(`sisaDisplay_${rowIdx}`);
    const satuanDisplay = document.getElementById(`satuanDisplay_${rowIdx}`);
    const qtyInput = document.getElementById(`qtyInput_${rowIdx}`);

    if (!selectedOpt || !selectedOpt.value) {
        if (barangIdInput) barangIdInput.value = '';
        if (gradeCdInput) gradeCdInput.value = 'A';
        if (sisaDisplay) sisaDisplay.textContent = '-';
        if (satuanDisplay) satuanDisplay.textContent = '';
        updateLiveSummary();
        return;
    }

    const barangId = selectedOpt.getAttribute('data-barang-id');
    const grade = selectedOpt.getAttribute('data-grade');
    const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
    const satuan = selectedOpt.getAttribute('data-satuan');

    if (barangIdInput) barangIdInput.value = barangId;
    if (gradeCdInput) gradeCdInput.value = grade;
    if (sisaDisplay) sisaDisplay.textContent = sisa.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
    if (satuanDisplay) satuanDisplay.textContent = satuan;

    if (qtyInput) {
        qtyInput.max = sisa;
        if (parseFloat(qtyInput.value || 0) > sisa) {
            qtyInput.value = sisa;
        }
    }

    updateLiveSummary();
}

function onQtyChange(rowIdx) {
    const qtyInput = document.getElementById(`qtyInput_${rowIdx}`);
    if (!qtyInput) return;

    const val = parseFloat(qtyInput.value || 0);
    const max = parseFloat(qtyInput.max || 0);

    if (max > 0 && val > max) {
        alert(`Kuantitas transfer (${val}) melebihi sisa stok yang tersedia (${max})!`);
        qtyInput.value = max;
    }

    updateLiveSummary();
}

function removeRow(rowIdx) {
    const row = document.getElementById(`itemRow_${rowIdx}`);
    if (row) {
        row.remove();
        updateLiveSummary();
    }
}

function updateLiveSummary() {
    let totalQty = 0;
    let totalRows = 0;

    const qtyInputs = document.querySelectorAll('#itemsBody input[name*="[qty_transfer]"]');
    qtyInputs.forEach(input => {
        const val = parseFloat(input.value || 0);
        if (val > 0) {
            totalQty += val;
            totalRows++;
        }
    });

    const displayQty = document.getElementById('liveTotalQty');
    const displayRows = document.getElementById('liveTotalRows');

    if (displayQty) {
        displayQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
    }
    if (displayRows) {
        displayRows.textContent = totalRows;
    }
}
