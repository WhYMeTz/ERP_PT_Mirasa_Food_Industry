/**
 * JAVASCRIPT FORMULIR PENYESUAIAN STOK (CREATE / OPNAME)
 * ERP PT Mirasa Food Industry - Standar Separation of Concerns & Blueprint Math
 */

let rowCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi baris pertama jika tabel masih kosong
    const tableBody = document.getElementById('adjItemsTbody');
    if (tableBody && tableBody.children.length === 0) {
        addAdjustmentRow();
    }

    // Listener jika gudang diubah, refresh stok sistem tiap baris yang sudah dipilih barangnya
    const gudangSelect = document.getElementById('adjGudangSelect');
    if (gudangSelect) {
        gudangSelect.addEventListener('change', function () {
            document.querySelectorAll('.row-barang-select').forEach(function (select) {
                if (select.value) {
                    const rowId = select.dataset.rowId;
                    fetchItemStock(rowId);
                }
            });
        });
    }
});

/**
 * Format Angka Rupiah
 */
function formatRp(val) {
    const num = parseFloat(val) || 0;
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

/**
 * Tambah Baris Barang Penyesuaian Baru
 */
function addAdjustmentRow() {
    rowCounter++;
    const tbody = document.getElementById('adjItemsTbody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'rowAdj_' + rowCounter;
    tr.className = 'adj-item-row';
    tr.dataset.rowId = rowCounter;

    // Ambil opsi barang dari template
    const templateSelect = document.getElementById('barangOptionsTemplate');
    const optionsHtml = templateSelect ? templateSelect.innerHTML : '<option value="">-- Pilih Barang --</option>';

    tr.innerHTML = `
        <td style="width: 260px;">
            <select name="items[${rowCounter}][barang_id]" 
                    class="adj-form-control row-barang-select" 
                    data-row-id="${rowCounter}" 
                    onchange="onBarangChanged(${rowCounter})" required>
                ${optionsHtml}
            </select>
            <div id="batchWrap_${rowCounter}" style="margin-top: 4px; display: none;">
                <select name="items[${rowCounter}][batch_no]" id="batchSelect_${rowCounter}" class="adj-form-control" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" onchange="onBatchChanged(${rowCounter})">
                    <option value="">[Semua Batch / Akumulatif]</option>
                </select>
            </div>
        </td>
        <td style="width: 80px; text-align: center;">
            <span id="satuanLabel_${rowCounter}" style="font-weight: 600; color: #475569;">-</span>
        </td>
        <td class="td-input" style="width: 110px;">
            <input type="text" 
                   name="items[${rowCounter}][stok_sistem_qty]" 
                   id="stokSistem_${rowCounter}" 
                   class="adj-input-compact adj-input-readonly" 
                   value="0" readonly>
        </td>
        <td class="td-input" style="width: 120px;">
            <input type="text" 
                   name="items[${rowCounter}][harga_satuan]" 
                   id="hargaSatuan_${rowCounter}" 
                   class="adj-input-compact" 
                   value="0" 
                   oninput="calculateRow(${rowCounter})" required>
        </td>
        <td class="td-input" style="width: 140px;">
            <input type="text" 
                   id="totalSistem_${rowCounter}" 
                   class="adj-input-compact adj-input-readonly" 
                   value="Rp 0" readonly>
        </td>
        <td class="td-input" style="width: 120px; background: #ecfeff;">
            <input type="number" 
                   step="any"
                   name="items[${rowCounter}][stok_fisik_qty]" 
                   id="stokFisik_${rowCounter}" 
                   class="adj-input-compact" 
                   style="border-color: #06b6d4; font-weight: 700;"
                   value="0" 
                   oninput="calculateRow(${rowCounter})" required>
        </td>
        <td class="td-input" style="width: 140px; background: #ecfeff;">
            <input type="text" 
                   id="totalFisik_${rowCounter}" 
                   class="adj-input-compact adj-input-readonly" 
                   value="Rp 0" readonly>
        </td>
        <td class="td-input" style="width: 120px; background: #fefce8; text-align: right;">
            <span id="badgeSelisih_${rowCounter}" class="badge-adj-match">0</span>
            <input type="hidden" id="selisihQty_${rowCounter}" value="0">
        </td>
        <td class="td-input" style="width: 140px; background: #fefce8;">
            <input type="text" 
                   id="totalSelisihNilai_${rowCounter}" 
                   class="adj-input-compact adj-input-readonly" 
                   style="font-weight: 700;" 
                   value="Rp 0" readonly>
        </td>
        <td style="min-width: 180px;">
            <input type="text" 
                   name="items[${rowCounter}][alasan_txt]" 
                   class="adj-form-control" 
                   style="padding: 0.35rem 0.5rem; font-size: 0.8rem;" 
                   placeholder="Contoh: Residu drum minyak, susut singkong">
        </td>
        <td style="width: 50px; text-align: center;">
            <button type="button" class="btn-remove-row" onclick="removeAdjustmentRow(${rowCounter})" title="Hapus Baris">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
}

/**
 * Hapus Baris
 */
function removeAdjustmentRow(rowId) {
    const tr = document.getElementById('rowAdj_' + rowId);
    const tbody = document.getElementById('adjItemsTbody');
    if (tr) {
        tr.remove();
    }
    // Jika semua baris dihapus, buat satu baris kosong
    if (tbody && tbody.children.length === 0) {
        addAdjustmentRow();
    }
    recalculateSummary();
}

/**
 * Event saat barang dipilih
 */
function onBarangChanged(rowId) {
    fetchItemStock(rowId);
}

/**
 * AJAX Ambil Stok Sistem & Harga Pokok Barang di Gudang yang dipilih
 */
function fetchItemStock(rowId) {
    const gudangSelect = document.getElementById('adjGudangSelect');
    const barangSelect = document.querySelector(`select[name="items[${rowId}][barang_id]"]`);

    if (!gudangSelect || !barangSelect) return;

    const gudangId = gudangSelect.value;
    const barangId = barangSelect.value;

    if (!gudangId || !barangId) return;

    fetch(`/gudang/adjustment/ajax-item-info?gudang_id=${gudangId}&barang_id=${barangId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                const data = result.data;

                // Satuan
                const satEl = document.getElementById(`satuanLabel_${rowId}`);
                if (satEl) satEl.textContent = data.satuan_cd || 'KG';

                // Stok Sistem
                const stokSisEl = document.getElementById(`stokSistem_${rowId}`);
                if (stokSisEl) stokSisEl.value = parseFloat(data.stok_sistem_qty) || 0;

                // Harga Satuan
                const hrgEl = document.getElementById(`hargaSatuan_${rowId}`);
                if (hrgEl) hrgEl.value = parseFloat(data.harga_satuan) || 0;

                // Stok Fisik Awal disamakan dengan sistem sebagai default
                const fisikEl = document.getElementById(`stokFisik_${rowId}`);
                if (fisikEl && (!fisikEl.value || parseFloat(fisikEl.value) === 0)) {
                    fisikEl.value = parseFloat(data.stok_sistem_qty) || 0;
                }

                // Batch Dropdown jika ada batch
                const batchWrap = document.getElementById(`batchWrap_${rowId}`);
                const batchSelect = document.getElementById(`batchSelect_${rowId}`);
                if (batchWrap && batchSelect && data.batches && data.batches.length > 0) {
                    batchSelect.innerHTML = '<option value="">[Semua Batch / Akumulatif]</option>';
                    data.batches.forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b.batch_no;
                        opt.textContent = `${b.batch_no} (Sisa: ${parseFloat(b.sisa_qty)})`;
                        opt.dataset.sisa = b.sisa_qty;
                        opt.dataset.harga = b.harga_satuan;
                        batchSelect.appendChild(opt);
                    });
                    batchWrap.style.display = 'block';
                } else if (batchWrap) {
                    batchWrap.style.display = 'none';
                }

                calculateRow(rowId);
            }
        })
        .catch(err => {
            console.error('Error fetching stock info:', err);
        });
}

/**
 * Event jika pengguna memilih nomor batch tertentu
 */
function onBatchChanged(rowId) {
    const batchSelect = document.getElementById(`batchSelect_${rowId}`);
    if (!batchSelect) return;

    const selectedOpt = batchSelect.options[batchSelect.selectedIndex];
    if (selectedOpt && selectedOpt.value) {
        const sisa = parseFloat(selectedOpt.dataset.sisa) || 0;
        const harga = parseFloat(selectedOpt.dataset.harga) || 0;

        const stokSisEl = document.getElementById(`stokSistem_${rowId}`);
        if (stokSisEl) stokSisEl.value = sisa;

        if (harga > 0) {
            const hrgEl = document.getElementById(`hargaSatuan_${rowId}`);
            if (hrgEl) hrgEl.value = harga;
        }

        const fisikEl = document.getElementById(`stokFisik_${rowId}`);
        if (fisikEl) fisikEl.value = sisa;

        calculateRow(rowId);
    }
}

/**
 * Kalkulasi Matematika Baris Sesuai Blueprint:
 * d = Stok Sistem
 * e = Harga Satuan
 * f = Total Sistem = d * e
 * g = Stok Fisik (Gudang)
 * h = Harga Satuan
 * i = Total Fisik = g * h
 * j = Selisih = g - d
 * k = Harga Satuan
 * l = Total Selisih Nilai = j * k
 */
function calculateRow(rowId) {
    const stokSistemEl = document.getElementById(`stokSistem_${rowId}`);
    const hargaSatuanEl = document.getElementById(`hargaSatuan_${rowId}`);
    const stokFisikEl = document.getElementById(`stokFisik_${rowId}`);

    if (!stokSistemEl || !hargaSatuanEl || !stokFisikEl) return;

    const d = parseFloat(stokSistemEl.value) || 0;
    const e = parseFloat(hargaSatuanEl.value) || 0;
    const g = parseFloat(stokFisikEl.value) || 0;

    // f = d * e
    const f = d * e;
    const totalSistemEl = document.getElementById(`totalSistem_${rowId}`);
    if (totalSistemEl) totalSistemEl.value = formatRp(f);

    // i = g * e
    const i = g * e;
    const totalFisikEl = document.getElementById(`totalFisik_${rowId}`);
    if (totalFisikEl) totalFisikEl.value = formatRp(i);

    // j = g - d
    const j = Math.round((g - d) * 10000) / 10000;
    const selisihQtyEl = document.getElementById(`selisihQty_${rowId}`);
    if (selisihQtyEl) selisihQtyEl.value = j;

    // Badge Selisih
    const badgeEl = document.getElementById(`badgeSelisih_${rowId}`);
    if (badgeEl) {
        if (j < 0) {
            badgeEl.className = 'badge-adj-defisit';
            badgeEl.textContent = j.toString() + ' (Susut)';
        } else if (j > 0) {
            badgeEl.className = 'badge-adj-surplus';
            badgeEl.textContent = '+' + j.toString() + ' (Surplus)';
        } else {
            badgeEl.className = 'badge-adj-match';
            badgeEl.textContent = '0 (Cocok)';
        }
    }

    // l = j * e
    const l = j * e;
    const totalSelisihNilaiEl = document.getElementById(`totalSelisihNilai_${rowId}`);
    if (totalSelisihNilaiEl) {
        totalSelisihNilaiEl.value = (l < 0 ? '-' : (l > 0 ? '+' : '')) + formatRp(Math.abs(l));
        totalSelisihNilaiEl.style.color = l < 0 ? '#b91c1c' : (l > 0 ? '#15803d' : '#475569');
    }

    recalculateSummary();
}

/**
 * Hitung Akumulasi Total di Bar Ringkasan Bawah
 */
function recalculateSummary() {
    let totalItems = 0;
    let sumSelisihQty = 0;
    let sumSelisihNilai = 0;

    document.querySelectorAll('.adj-item-row').forEach(tr => {
        const rowId = tr.dataset.rowId;
        const barangSelect = tr.querySelector('.row-barang-select');
        if (barangSelect && barangSelect.value) {
            totalItems++;
            const selisihQty = parseFloat(document.getElementById(`selisihQty_${rowId}`)?.value) || 0;
            const harga = parseFloat(document.getElementById(`hargaSatuan_${rowId}`)?.value) || 0;

            sumSelisihQty += selisihQty;
            sumSelisihNilai += (selisihQty * harga);
        }
    });

    const sumItemEl = document.getElementById('summaryTotalItem');
    const sumQtyEl = document.getElementById('summarySelisihQty');
    const sumNilaiEl = document.getElementById('summarySelisihNilai');

    if (sumItemEl) sumItemEl.textContent = totalItems;
    if (sumQtyEl) {
        sumQtyEl.textContent = (sumSelisihQty > 0 ? '+' : '') + Math.round(sumSelisihQty * 100) / 100;
        sumQtyEl.style.color = sumSelisihQty < 0 ? '#b91c1c' : (sumSelisihQty > 0 ? '#15803d' : '#0f172a');
    }
    if (sumNilaiEl) {
        sumNilaiEl.textContent = (sumSelisihNilai < 0 ? '-' : (sumSelisihNilai > 0 ? '+' : '')) + formatRp(Math.abs(sumSelisihNilai));
        sumNilaiEl.style.color = sumSelisihNilai < 0 ? '#b91c1c' : (sumSelisihNilai > 0 ? '#15803d' : '#0f172a');
    }
}
