/**
 * ══════════════════════════════════════════════════════════════════════════
 * PO PENJUALAN (SALES ORDER) - FORM SCRIPT (12 KOLOM EXCEL GRID)
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

let rowCounter = 0;

function formatRupiah(amount) {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

// Customer Selection Listener -> Mengisi Kode, Alamat, Kontak
function initCustomerListener() {
    const custSelect = document.getElementById('customerSelect');
    if (!custSelect) return;

    custSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const code = selected ? (selected.getAttribute('data-code') || '') : '';
        const address = selected ? (selected.getAttribute('data-address') || '-') : '-';
        const contact = selected ? (selected.getAttribute('data-contact') || '-') : '-';

        const inCd = document.getElementById('customerCdInput');
        if (inCd) inCd.value = code;

        const infoBox = document.getElementById('customerInfoBox');
        if (this.value && infoBox) {
            const elAddr = document.getElementById('custAddressText');
            const elContact = document.getElementById('custContactText');
            if (elAddr) elAddr.textContent = address;
            if (elContact) elContact.textContent = contact;
            infoBox.style.display = 'block';
        } else if (infoBox) {
            infoBox.style.display = 'none';
        }
    });

    if (custSelect.value) {
        custSelect.dispatchEvent(new Event('change'));
    }
}

// Tambah Baris Baru pada Tabel Inputan
function addRow(data = {}) {
    rowCounter++;
    const idx = rowCounter;
    const tbody = document.getElementById('itemsBody');
    if (!tbody) return;

    const MASTER_BARANG = window.soConfig?.barangs || [];
    const stockMap = window.soConfig?.stockMap || {};

    const tr = document.createElement('tr');
    tr.id = `row_${idx}`;

    // Generate pilihan barang
    const optionsHtml = MASTER_BARANG.map(b => {
        const id = b.barang_id;
        const stok = parseFloat(stockMap[id] || 0);
        const isSelected = (data.barang_id == id) ? 'selected' : '';
        const jenisNm = b.jenis_barang ? b.jenis_barang.jenis_barang_nm : (b.jenis_barang_cd || 'FG');
        const satuanNm = b.satuan_dasar ? b.satuan_dasar.satuan_nm : 'Karton';
        const hargaJual = b.harga_jual || b.harga_standar || b.harga_beli_standar || 0;

        return `
            <option value="${id}" 
                    data-code="${b.barang_cd || ''}" 
                    data-jenis="${jenisNm}" 
                    data-satuan="${satuanNm}"
                    data-harga="${hargaJual}"
                    data-stok="${stok}"
                    ${isSelected}>
                ${b.barang_nm} (Stok: ${stok} ${satuanNm})
            </option>
        `;
    }).join('');

    tr.innerHTML = `
        <td style="text-align: center; color: #64748b; font-size: 0.8rem;" class="row-num">${tbody.children.length + 1}</td>
        <td>
            <input type="text" class="form-control item-jenis" readonly placeholder="-" style="background: #f8fafc; font-size: 0.75rem; cursor: not-allowed; text-align: center;">
        </td>
        <td>
            <select name="items[${idx}][barang_id]" class="form-control item-barang-select" required style="font-size: 0.8rem; width: 100%;">
                <option value="">-- Pilih Nama Barang --</option>
                ${optionsHtml}
            </select>
            <div class="item-stock-badge-container" style="margin-top: 3px;">
                <span class="stock-badge ready item-stock-badge" style="display: none;">
                    Stok FG: <strong class="item-stock-val">0</strong>
                </span>
            </div>
        </td>
        <td>
            <input type="text" class="form-control item-barang-code" readonly placeholder="Otomatis..." style="background: #f8fafc; font-family: monospace; font-size: 0.75rem; color: #0284c7; font-weight: 700; cursor: not-allowed; text-align: center;">
        </td>
        <td>
            <input type="number" step="any" min="0.001" name="items[${idx}][pesan_qty]" value="${data.pesan_qty || 1}" required class="form-control item-qty" style="text-align: right; font-weight: 700;">
        </td>
        <td>
            <input type="text" class="form-control item-satuan" readonly placeholder="-" style="background: #f8fafc; font-size: 0.75rem; cursor: not-allowed; text-align: center;">
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${idx}][harga_satuan]" value="${data.harga_satuan || 0}" required class="form-control item-harga" style="text-align: right; font-family: monospace; font-weight: 600;">
        </td>
        <td>
            <input type="number" step="any" min="0" max="100" name="items[${idx}][diskon_persen]" value="${data.diskon_persen || 0}" class="form-control item-diskon" style="text-align: right;">
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${idx}][potongan_nominal]" value="${data.potongan_nominal || 0}" class="form-control item-potongan" style="text-align: right;">
        </td>
        <td>
            <select name="items[${idx}][ppn_tipe]" class="form-control item-ppn" style="font-size: 0.75rem;">
                <option value="NON_PPN" ${data.ppn_tipe === 'NON_PPN' ? 'selected' : ''}>Non PPN</option>
                <option value="PPN_11" ${data.ppn_tipe === 'PPN_11' ? 'selected' : ''}>PPN 11%</option>
            </select>
        </td>
        <td style="text-align: right; font-family: monospace; font-size: 0.85rem; font-weight: 700; color: #0f172a;">
            <span class="item-subtotal-text">Rp 0</span>
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn-remove-row" style="background: transparent; border: none; color: #ef4444; cursor: pointer; padding: 0.2rem; display: inline-flex; align-items: center;" title="Hapus Baris">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    checkEmptyState();

    const selBarang = tr.querySelector('.item-barang-select');
    const inCode = tr.querySelector('.item-barang-code');
    const inJenis = tr.querySelector('.item-jenis');
    const inSatuan = tr.querySelector('.item-satuan');
    const inQty = tr.querySelector('.item-qty');
    const inHarga = tr.querySelector('.item-harga');
    const inDiskon = tr.querySelector('.item-diskon');
    const inPotongan = tr.querySelector('.item-potongan');
    const inPpn = tr.querySelector('.item-ppn');
    const btnRemove = tr.querySelector('.btn-remove-row');
    const badge = tr.querySelector('.item-stock-badge');
    const stockVal = tr.querySelector('.item-stock-val');

    selBarang.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            inCode.value = opt.getAttribute('data-code') || '';
            inJenis.value = opt.getAttribute('data-jenis') || '';
            inSatuan.value = opt.getAttribute('data-satuan') || '';
            if (Number(inHarga.value) === 0) {
                inHarga.value = opt.getAttribute('data-harga') || 0;
            }

            const stok = parseFloat(opt.getAttribute('data-stok') || 0);
            const satuan = opt.getAttribute('data-satuan') || 'Karton';

            badge.style.display = 'inline-flex';
            if (stok <= 0) {
                badge.className = 'stock-badge empty item-stock-badge';
                stockVal.textContent = `Kosong (0 ${satuan})`;
            } else {
                badge.className = 'stock-badge ready item-stock-badge';
                stockVal.textContent = `${stok} ${satuan}`;
            }
        } else {
            badge.style.display = 'none';
            inCode.value = '';
            inJenis.value = '';
            inSatuan.value = '';
        }
        calculateRow(tr);
        calculateGrandTotal();
    });

    if (data.barang_id) {
        selBarang.dispatchEvent(new Event('change'));
    }

    [inQty, inHarga, inDiskon, inPotongan, inPpn].forEach(el => {
        el.addEventListener('input', () => {
            calculateRow(tr);
            calculateGrandTotal();
        });
        el.addEventListener('change', () => {
            calculateRow(tr);
            calculateGrandTotal();
        });
    });

    btnRemove.addEventListener('click', function() {
        tr.remove();
        renumberRows();
        calculateGrandTotal();
    });

    calculateRow(tr);
}

function calculateRow(tr) {
    const qty = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
    const harga = parseFloat(tr.querySelector('.item-harga')?.value) || 0;
    const diskonPersen = parseFloat(tr.querySelector('.item-diskon')?.value) || 0;
    const potonganRp = parseFloat(tr.querySelector('.item-potongan')?.value) || 0;
    const ppnTipe = tr.querySelector('.item-ppn')?.value || 'NON_PPN';

    const selBarang = tr.querySelector('.item-barang-select');
    const opt = selBarang?.selectedOptions[0];
    const badge = tr.querySelector('.item-stock-badge');
    const stockVal = tr.querySelector('.item-stock-val');

    if (badge && opt && opt.value) {
        const stok = parseFloat(opt.getAttribute('data-stok') || 0);
        const satuan = opt.getAttribute('data-satuan') || 'Karton';

        if (stok <= 0) {
            badge.className = 'stock-badge empty item-stock-badge';
            stockVal.textContent = `Kosong (0 ${satuan})`;
        } else if (qty > stok) {
            badge.className = 'stock-badge warning item-stock-badge';
            stockVal.innerHTML = `${stok} ${satuan} <span style="font-weight:normal;">(Kurang ${qty - stok})</span>`;
        } else {
            badge.className = 'stock-badge ready item-stock-badge';
            stockVal.textContent = `${stok} ${satuan}`;
        }
    }

    const bruto = qty * harga;
    const diskonNominal = bruto * (diskonPersen / 100);
    const netto = Math.max(0, bruto - diskonNominal - potonganRp);
    const ppnNominal = ppnTipe === 'PPN_11' ? (netto * 0.11) : 0;
    const subtotal = netto + ppnNominal;

    const subtotalTxt = tr.querySelector('.item-subtotal-text');
    if (subtotalTxt) subtotalTxt.textContent = formatRupiah(subtotal);

    tr.dataset.bruto = bruto;
    tr.dataset.diskon = diskonNominal;
    tr.dataset.netto = netto;
    tr.dataset.ppn = ppnNominal;
    tr.dataset.subtotal = subtotal;
}

function calculateGrandTotal() {
    const rows = document.querySelectorAll('#itemsBody tr');
    let totalBruto = 0;
    let totalDiskon = 0;
    let totalDpp = 0;
    let totalPpn = 0;

    rows.forEach(tr => {
        totalBruto += parseFloat(tr.dataset.bruto || 0);
        totalDiskon += parseFloat(tr.dataset.diskon || 0);
        totalDpp += parseFloat(tr.dataset.netto || 0);
        totalPpn += parseFloat(tr.dataset.ppn || 0);
    });

    const headerPotongan = parseFloat(document.getElementById('headerPotonganInput')?.value) || 0;
    const finalDpp = Math.max(0, totalDpp - headerPotongan);
    const grandTotal = finalDpp + totalPpn;

    const elBruto = document.getElementById('lblSubtotalBruto');
    const elDiskon = document.getElementById('lblTotalDiskon');
    const elDpp = document.getElementById('lblDppNominal');
    const elPpn = document.getElementById('lblTotalPpn');
    const elGrand = document.getElementById('lblTotalTagihan');

    if (elBruto) elBruto.textContent = formatRupiah(totalBruto);
    if (elDiskon) elDiskon.textContent = '- ' + formatRupiah(totalDiskon);
    if (elDpp) elDpp.textContent = formatRupiah(finalDpp);
    if (elPpn) elPpn.textContent = '+ ' + formatRupiah(totalPpn);
    if (elGrand) elGrand.textContent = formatRupiah(grandTotal);
}

function renumberRows() {
    const rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach((tr, i) => {
        const numCell = tr.querySelector('.row-num');
        if (numCell) numCell.textContent = i + 1;
    });
    checkEmptyState();
}

function checkEmptyState() {
    const rows = document.querySelectorAll('#itemsBody tr');
    const notice = document.getElementById('noItemsNotice');
    if (notice) {
        notice.style.display = rows.length === 0 ? 'block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initCustomerListener();

    const btnAdd = document.getElementById('btnAddRow');
    if (btnAdd) {
        btnAdd.addEventListener('click', () => addRow());
    }

    const potInput = document.getElementById('headerPotonganInput');
    if (potInput) {
        potInput.addEventListener('input', calculateGrandTotal);
    }

    const existingItems = window.soConfig?.existingItems || [];
    if (existingItems.length > 0) {
        existingItems.forEach(item => addRow(item));
    } else {
        const rows = document.querySelectorAll('#itemsBody tr');
        if (rows.length === 0) {
            addRow();
        }
    }
});
