/**
 * JavaScript untuk Formulir Pengeluaran Sampel / Promosi (Non-PO)
 * PT Mirasa Food Industry
 */

let sampleRowIndex = 0;

document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('sampleItemsBody');
    const btnAdd = document.getElementById('btnAddSampleRow');
    const form = document.getElementById('sampleCreateForm');

    if (btnAdd) {
        btnAdd.addEventListener('click', function () {
            addSampleRow();
        });
    }

    // Tambah 1 baris awal secara otomatis jika tabel kosong
    if (tableBody && tableBody.children.length === 0) {
        addSampleRow();
    }

    // Auto-fill penerima saat memilih customer terdaftar
    const customerSelect = document.getElementById('customerSelect');
    const penerimaInput = document.getElementById('penerimaInput');
    const alamatInput = document.getElementById('alamatTujuanInput');

    if (customerSelect && penerimaInput) {
        customerSelect.addEventListener('change', function () {
            const selectedOpt = customerSelect.options[customerSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const custName = selectedOpt.getAttribute('data-name');
                const custAlamat = selectedOpt.getAttribute('data-alamat');
                if (custName && (!penerimaInput.value || confirm('Gunakan nama dan alamat mitra terpilih sebagai penerima?'))) {
                    penerimaInput.value = custName;
                    if (alamatInput && custAlamat) {
                        alamatInput.value = custAlamat;
                    }
                }
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            const rows = document.querySelectorAll('.sample-item-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu produk jadi untuk dikeluarkan.');
                return false;
            }

            let valid = false;
            rows.forEach(row => {
                const qtyInput = row.querySelector('.sample-qty-input');
                if (qtyInput && parseFloat(qtyInput.value) > 0) {
                    valid = true;
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Pastikan minimal ada 1 barang dengan kuantitas lebih dari 0.');
                return false;
            }
        });
    }
});

function addSampleRow() {
    const tableBody = document.getElementById('sampleItemsBody');
    if (!tableBody) return;

    const products = window.sampleProducts || [];
    if (products.length === 0) {
        alert('Tidak ada produk jadi dengan stok tersedia di Gudang FG.');
        return;
    }

    const idx = sampleRowIndex++;
    const tr = document.createElement('tr');
    tr.className = 'sample-item-row';
    tr.id = `sampleRow_${idx}`;

    let productOptions = '<option value="">-- Pilih Produk Jadi --</option>';
    products.forEach(p => {
        productOptions += `<option value="${p.barang_id}" data-stok="${p.total_stok}" data-satuan="${p.satuan_nm}">
            ${p.barang_nm} (${p.barang_cd}) — Stok: ${p.total_stok} ${p.satuan_nm}
        </option>`;
    });

    tr.innerHTML = `
        <td style="text-align: center; vertical-align: middle; color: #64748b; font-weight: 600;">
            ${sampleRowIndex}
        </td>
        <td style="vertical-align: middle;">
            <select name="items[${idx}][barang_id]" class="sj-form-select sample-product-select" required onchange="handleProductSelectChange(${idx})" style="font-weight: 600; font-size: 0.8125rem;">
                ${productOptions}
            </select>
        </td>
        <td style="vertical-align: middle;">
            <select name="items[${idx}][batch_no]" id="batchSelect_${idx}" class="sj-form-select sample-batch-select" style="font-size: 0.8rem; font-family: monospace;">
                <option value="AUTO_FIFO">[Otomatis FEFO - Batch Tertua]</option>
            </select>
        </td>
        <td style="vertical-align: middle; text-align: center;">
            <span id="stokTersedia_${idx}" style="font-weight: 700; color: #059669; font-size: 0.85rem;">-</span>
        </td>
        <td style="vertical-align: middle;">
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <input type="number" name="items[${idx}][kirim_qty]" id="qtyInput_${idx}" 
                       step="any" min="0.01" value="1" required 
                       class="sj-form-input sample-qty-input" 
                       style="text-align: right; font-weight: 700; width: 85px;" 
                       oninput="calculateSampleTotal()">
                <span id="satuanText_${idx}" style="font-size: 0.775rem; color: #64748b; font-weight: 600;">Karton</span>
            </div>
        </td>
        <td style="vertical-align: middle;">
            <input type="text" name="items[${idx}][catatan_txt]" placeholder="Keterangan item (opsional)..." class="sj-form-input" style="font-size: 0.775rem;">
        </td>
        <td style="vertical-align: middle; text-align: center;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="removeSampleRow(${idx})" title="Hapus baris" style="padding: 0.35rem 0.6rem; color: #dc2626; border-color: #fca5a5;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tableBody.appendChild(tr);

    // Auto-select first product if only 1 exists
    const pSelect = tr.querySelector('.sample-product-select');
    if (products.length === 1 && pSelect) {
        pSelect.selectedIndex = 1;
        handleProductSelectChange(idx);
    }

    calculateSampleTotal();
}

function removeSampleRow(idx) {
    const tr = document.getElementById(`sampleRow_${idx}`);
    if (tr) {
        tr.remove();
        calculateSampleTotal();
    }
}

function handleProductSelectChange(idx) {
    const row = document.getElementById(`sampleRow_${idx}`);
    if (!row) return;

    const pSelect = row.querySelector('.sample-product-select');
    const bSelect = document.getElementById(`batchSelect_${idx}`);
    const stokSpan = document.getElementById(`stokTersedia_${idx}`);
    const satuanSpan = document.getElementById(`satuanText_${idx}`);
    const qtyInput = document.getElementById(`qtyInput_${idx}`);

    const selectedBrgId = parseInt(pSelect.value);
    const products = window.sampleProducts || [];
    const product = products.find(p => p.barang_id === selectedBrgId);

    if (product) {
        stokSpan.textContent = product.total_stok + ' ' + product.satuan_nm;
        satuanSpan.textContent = product.satuan_nm;
        qtyInput.max = product.total_stok;

        // Populate batches
        bSelect.innerHTML = '<option value="AUTO_FIFO">[Otomatis FEFO - Batch Tertua]</option>';
        if (product.batches && product.batches.length > 0) {
            product.batches.forEach((b, bIdx) => {
                const isFirst = bIdx === 0;
                bSelect.innerHTML += `<option value="${b.batch_no}">
                    ${isFirst ? '[FEFO Prioritas] ' : ''}Batch: ${b.batch_no} (Sisa: ${b.sisa_qty} ${product.satuan_nm}, Exp: ${b.expired_fmt})
                </option>`;
            });
        }
    } else {
        stokSpan.textContent = '-';
        bSelect.innerHTML = '<option value="AUTO_FIFO">[Otomatis FEFO - Batch Tertua]</option>';
    }

    calculateSampleTotal();
}

function calculateSampleTotal() {
    const qtyInputs = document.querySelectorAll('.sample-qty-input');
    let total = 0;
    qtyInputs.forEach(input => {
        const val = parseFloat(input.value) || 0;
        total += val;
    });

    const totalDisplay = document.getElementById('totalSampleKartonText');
    if (totalDisplay) {
        totalDisplay.textContent = total.toLocaleString('id-ID');
    }
}
