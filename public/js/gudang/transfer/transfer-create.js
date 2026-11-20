/**
 * Transfer Antar-Gudang Create JS
 * ERP PT Mirasa Food Industry
 * Fitur: Filter Kategori (Bahan Baku, Bahan Penolong, WIP, FG),
 * Desain Cepat Batch Picker, Quick Max Button, dan Modal Katalog Stok.
 */

let availableBatches = [];
let rowIndexCounter = 0;
let currentCategoryFilter = 'ALL';
let currentSearchQuery = '';

// State untuk Modal Picker
let modalCategoryFilter = 'ALL';
let modalSearchQuery = '';

document.addEventListener('DOMContentLoaded', function() {
    const asalSelect = document.getElementById('gudang_asal_id');
    if (asalSelect && asalSelect.value) {
        fetchAvailableBatches(asalSelect.value);
    }
});

/**
 * Pengelompokan Kategori Barang
 */
function getCategoryGroup(batch) {
    const cd = (batch.barang && batch.barang.jenis_barang && batch.barang.jenis_barang.jenis_barang_cd)
        ? batch.barang.jenis_barang.jenis_barang_cd.toUpperCase()
        : '';

    if (['RAW', 'BB'].includes(cd)) return 'BAHAN_BAKU';
    if (['BP', 'PACK', 'SUPP'].includes(cd)) return 'BAHAN_PENOLONG';
    if (['WIP'].includes(cd)) return 'WIP';
    if (['FG'].includes(cd)) return 'FG';
    return 'LAINNYA';
}

function getCategoryBadgeHtml(catGroup) {
    switch (catGroup) {
        case 'BAHAN_BAKU':
            return '<span class="badge-cat badge-cat-bb">Bahan Baku</span>';
        case 'BAHAN_PENOLONG':
            return '<span class="badge-cat badge-cat-bp">Bahan Penolong</span>';
        case 'WIP':
            return '<span class="badge-cat badge-cat-wip">Stok WIP</span>';
        case 'FG':
            return '<span class="badge-cat badge-cat-fg">Barang Jadi</span>';
        default:
            return '<span class="badge-cat" style="background:#e2e8f0;color:#475569;">Lainnya</span>';
    }
}

function getCategoryShortName(catGroup) {
    switch (catGroup) {
        case 'BAHAN_BAKU': return 'Bahan Baku';
        case 'BAHAN_PENOLONG': return 'Bahan Penolong';
        case 'WIP': return 'Stok WIP';
        case 'FG': return 'Barang Jadi (FG)';
        default: return 'Lainnya';
    }
}

/**
 * Handle perubahan Gudang Asal
 */
function onGudangAsalChange(selectEl) {
    const gudangId = selectEl.value;
    const itemsTbody = document.getElementById('itemsBody');
    if (itemsTbody) {
        itemsTbody.innerHTML = '';
        rowIndexCounter = 0;
    }

    if (!gudangId) {
        availableBatches = [];
        updateCategoryCounts();
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

/**
 * Mengambil daftar stok batch dari server
 */
function fetchAvailableBatches(gudangId) {
    const statusNotice = document.getElementById('batchLoadingNotice');
    if (statusNotice) statusNotice.style.display = 'inline-block';

    fetch(`/gudang/transfer/batches?gudang_id=${gudangId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                availableBatches = (res.data || []).map(b => {
                    b._catGroup = getCategoryGroup(b);
                    return b;
                });

                updateCategoryCounts();

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

/**
 * Perbarui jumlah item per kategori di chips dan tab modal
 */
function updateCategoryCounts() {
    let countAll = availableBatches.length;
    let countBB = 0;
    let countBP = 0;
    let countWIP = 0;
    let countFG = 0;

    availableBatches.forEach(b => {
        if (b._catGroup === 'BAHAN_BAKU') countBB++;
        else if (b._catGroup === 'BAHAN_PENOLONG') countBP++;
        else if (b._catGroup === 'WIP') countWIP++;
        else if (b._catGroup === 'FG') countFG++;
    });

    const setEl = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    setEl('countAll', countAll);
    setEl('countBB', countBB);
    setEl('countBP', countBP);
    setEl('countWIP', countWIP);
    setEl('countFG', countFG);

    setEl('modalCountAll', countAll);
    setEl('modalCountBB', countBB);
    setEl('modalCountBP', countBP);
    setEl('modalCountWIP', countWIP);
    setEl('modalCountFG', countFG);
}

/**
 * Filter Chip Kategori di halaman utama
 */
function onCategoryFilterClick(category) {
    currentCategoryFilter = category;

    // Update active class
    const container = document.getElementById('categoryChipsContainer');
    if (container) {
        container.querySelectorAll('.btn-chip-filter').forEach(btn => {
            if (btn.getAttribute('data-category') === category) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    // Refresh dropdown barang di setiap baris yang belum memilih batch
    refreshBarangDropdowns();
}

/**
 * Inline Quick Search
 */
function onInlineSearchChange(query) {
    currentSearchQuery = (query || '').toLowerCase().trim();
    refreshBarangDropdowns();
}

/**
 * Memperoleh daftar barang unik berdasarkan kategori & pencarian saat ini
 */
function getFilteredBarangList() {
    const barangMap = new Map();

    availableBatches.forEach(b => {
        if (!b.barang) return;

        // Filter Kategori
        if (currentCategoryFilter !== 'ALL' && b._catGroup !== currentCategoryFilter) {
            return;
        }

        // Filter Search
        if (currentSearchQuery) {
            const matchName = (b.barang.barang_nm || '').toLowerCase().includes(currentSearchQuery);
            const matchCode = (b.barang.barang_cd || '').toLowerCase().includes(currentSearchQuery);
            const matchBatch = (b.batch_no || '').toLowerCase().includes(currentSearchQuery);
            if (!matchName && !matchCode && !matchBatch) {
                return;
            }
        }

        if (!barangMap.has(b.barang_id)) {
            barangMap.set(b.barang_id, {
                barang_id: b.barang_id,
                barang_cd: b.barang.barang_cd,
                barang_nm: b.barang.barang_nm,
                catGroup: b._catGroup,
                satuan: b.barang.satuan_dasar ? b.barang.satuan_dasar.satuan_nm : 'Unit',
                batches: []
            });
        }

        barangMap.get(b.barang_id).batches.push(b);
    });

    return Array.from(barangMap.values());
}

/**
 * Refresh opsi dropdown barang di baris yang belum locked
 */
function refreshBarangDropdowns() {
    const barangList = getFilteredBarangList();

    const rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach(row => {
        const barangSelect = row.querySelector('.select-barang');
        if (!barangSelect) return;

        const currentVal = barangSelect.value;
        const currentBatchSelect = row.querySelector('.select-batch');
        const hasSelectedBatch = currentBatchSelect && currentBatchSelect.value;

        // Jika baris sudah ada batch yang dipilih, pertahankan
        if (hasSelectedBatch) return;

        let optionsHtml = '<option value="">-- Pilih Barang Asal --</option>';
        if (barangList.length === 0) {
            optionsHtml = '<option value="">(Tidak ada barang sesuai filter)</option>';
        } else {
            barangList.forEach(bg => {
                const isSel = String(bg.barang_id) === String(currentVal) ? 'selected' : '';
                optionsHtml += `<option value="${bg.barang_id}" ${isSel}>
                    [${getCategoryShortName(bg.catGroup)}] ${bg.barang_cd} - ${bg.barang_nm} (${bg.batches.length} batch)
                </option>`;
            });
        }
        barangSelect.innerHTML = optionsHtml;
    });
}

/**
 * Tambah Baris Transfer
 */
function addItemRow(preset = null) {
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

    const barangList = getFilteredBarangList();
    let barangOptionsHtml = '<option value="">-- Pilih Barang Asal --</option>';
    if (barangList.length === 0 && availableBatches.length > 0) {
        barangOptionsHtml = '<option value="">(Tidak ada barang pada kategori ini)</option>';
    } else if (availableBatches.length === 0) {
        barangOptionsHtml = '<option value="">(Stok gudang asal kosong)</option>';
    } else {
        barangList.forEach(bg => {
            barangOptionsHtml += `<option value="${bg.barang_id}">
                [${getCategoryShortName(bg.catGroup)}] ${bg.barang_cd} - ${bg.barang_nm} (${bg.batches.length} batch)
            </option>`;
        });
    }

    tr.innerHTML = `
        <td style="width: 32%;">
            <select class="form-control select-barang" id="barangSelect_${rowIdx}" onchange="onBarangSelected(this, ${rowIdx})" required>
                ${barangOptionsHtml}
            </select>
            <input type="hidden" name="items[${rowIdx}][barang_id]" id="barangId_${rowIdx}">
            <input type="hidden" name="items[${rowIdx}][grade_cd]" id="gradeCd_${rowIdx}" value="A">
        </td>
        <td style="width: 25%;">
            <select name="items[${rowIdx}][batch_no]" id="batchSelect_${rowIdx}" class="form-control select-batch" required onchange="onBatchSelected(this, ${rowIdx})">
                <option value="">-- Pilih Batch Stok --</option>
            </select>
            <div id="batchSubtext_${rowIdx}" class="batch-info-subtext"></div>
        </td>
        <td style="width: 13%; text-align: right;">
            <span id="sisaDisplay_${rowIdx}" style="font-weight: 800; color: #0f766e; font-family: monospace; font-size: 0.9rem;">-</span>
            <div id="satuanDisplay_${rowIdx}" style="font-size: 0.7rem; color: #64748b;"></div>
        </td>
        <td style="width: 16%;">
            <div class="qty-input-group">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIdx}][qty_transfer]" id="qtyInput_${rowIdx}" 
                       class="form-control" style="font-weight: 700; font-family: monospace; text-align: right;" 
                       placeholder="0.00" required oninput="onQtyChange(${rowIdx})">
                <button type="button" class="btn-max-qty" onclick="applyMaxQty(${rowIdx})" title="Salin seluruh sisa stok batch">Max</button>
            </div>
        </td>
        <td style="width: 10%;">
            <input type="text" name="items[${rowIdx}][catatan_txt]" id="catatanInput_${rowIdx}" class="form-control" placeholder="Opsional">
        </td>
        <td style="width: 4%; text-align: center;">
            <button type="button" class="btn-remove-row" onclick="removeRow(${rowIdx})" title="Hapus baris">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    // Jika dipanggil dengan preset (misalnya dari modal catalog)
    if (preset) {
        applyPresetToRow(rowIdx, preset);
    }
}

/**
 * Handle ketika Barang dipilih di baris
 */
function onBarangSelected(selectEl, rowIdx) {
    const barangId = selectEl.value;
    const batchSelect = document.getElementById(`batchSelect_${rowIdx}`);
    const barangIdInput = document.getElementById(`barangId_${rowIdx}`);
    const subtext = document.getElementById(`batchSubtext_${rowIdx}`);

    if (barangIdInput) barangIdInput.value = barangId;

    if (!batchSelect) return;

    if (!barangId) {
        batchSelect.innerHTML = '<option value="">-- Pilih Batch Stok --</option>';
        if (subtext) subtext.innerHTML = '';
        resetRowMetric(rowIdx);
        return;
    }

    // Ambil semua batch untuk barang ini
    const batches = availableBatches.filter(b => String(b.barang_id) === String(barangId));

    let optionsHtml = '<option value="">-- Pilih Batch Stok --</option>';
    if (batches.length === 0) {
        optionsHtml = '<option value="">(Tidak ada stok batch)</option>';
    } else {
        batches.forEach((b, idx) => {
            const sisa = parseFloat(b.sisa_qty || 0).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            const satuan = b.barang && b.barang.satuan_dasar ? b.barang.satuan_dasar.satuan_nm : '';
            const grade = b.grade_cd || 'A';
            const isFifo = idx === 0 ? ' [FIFO Terlama]' : '';
            const exp = b.expired_tgl ? ` | Exp: ${formatTglSingkat(b.expired_tgl)}` : '';

            optionsHtml += `<option value="${b.batch_no}" 
                data-grade="${grade}" 
                data-sisa="${b.sisa_qty}" 
                data-satuan="${satuan}"
                data-tgl="${b.created_at || ''}"
                data-exp="${b.expired_tgl || ''}"
                data-fifo="${idx === 0 ? '1' : '0'}">
                ${b.batch_no} &bull; Grd ${grade} &bull; Sisa: ${sisa} ${satuan}${isFifo}${exp}
            </option>`;
        });
    }

    batchSelect.innerHTML = optionsHtml;

    // Jika hanya ada 1 batch, otomatis pilihkan
    if (batches.length === 1) {
        batchSelect.selectedIndex = 1;
        onBatchSelected(batchSelect, rowIdx);
    } else {
        resetRowMetric(rowIdx);
    }
}

/**
 * Handle ketika Batch dipilih di baris
 */
function onBatchSelected(selectEl, rowIdx) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const gradeCdInput = document.getElementById(`gradeCd_${rowIdx}`);
    const sisaDisplay = document.getElementById(`sisaDisplay_${rowIdx}`);
    const satuanDisplay = document.getElementById(`satuanDisplay_${rowIdx}`);
    const qtyInput = document.getElementById(`qtyInput_${rowIdx}`);
    const subtext = document.getElementById(`batchSubtext_${rowIdx}`);

    if (!selectedOpt || !selectedOpt.value) {
        resetRowMetric(rowIdx);
        return;
    }

    const grade = selectedOpt.getAttribute('data-grade') || 'A';
    const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
    const satuan = selectedOpt.getAttribute('data-satuan') || '';
    const exp = selectedOpt.getAttribute('data-exp');
    const isFifo = selectedOpt.getAttribute('data-fifo') === '1';

    if (gradeCdInput) gradeCdInput.value = grade;
    if (sisaDisplay) sisaDisplay.textContent = sisa.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
    if (satuanDisplay) satuanDisplay.textContent = satuan;

    if (subtext) {
        let pills = [];
        if (isFifo) pills.push('<span style="background:#ecfdf5;color:#047857;padding:1px 5px;border-radius:4px;font-weight:700;">FIFO Disarankan</span>');
        pills.push(`<span>Grade: <strong>${grade}</strong></span>`);
        if (exp) pills.push(`<span>Exp: <strong>${formatTglSingkat(exp)}</strong></span>`);
        subtext.innerHTML = pills.join(' &bull; ');
    }

    if (qtyInput) {
        qtyInput.max = sisa;
        const currentQty = parseFloat(qtyInput.value || 0);
        if (currentQty <= 0) {
            // Biarkan kosong agar user isi atau klik Max
        } else if (currentQty > sisa) {
            qtyInput.value = sisa;
        }
    }

    updateLiveSummary();
}

/**
 * Salin kuantitas maksimal sisa stok
 */
function applyMaxQty(rowIdx) {
    const qtyInput = document.getElementById(`qtyInput_${rowIdx}`);
    const batchSelect = document.getElementById(`batchSelect_${rowIdx}`);
    if (!batchSelect || !batchSelect.value) {
        alert('Pilih batch stok terlebih dahulu.');
        return;
    }

    const selectedOpt = batchSelect.options[batchSelect.selectedIndex];
    const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);

    if (qtyInput) {
        qtyInput.value = sisa;
        onQtyChange(rowIdx);
    }
}

/**
 * Validasi perubahan Qty
 */
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

function resetRowMetric(rowIdx) {
    const sisaDisplay = document.getElementById(`sisaDisplay_${rowIdx}`);
    const satuanDisplay = document.getElementById(`satuanDisplay_${rowIdx}`);
    const qtyInput = document.getElementById(`qtyInput_${rowIdx}`);
    const subtext = document.getElementById(`batchSubtext_${rowIdx}`);

    if (sisaDisplay) sisaDisplay.textContent = '-';
    if (satuanDisplay) satuanDisplay.textContent = '';
    if (subtext) subtext.innerHTML = '';
    if (qtyInput) {
        qtyInput.max = '';
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

function formatTglSingkat(isoStr) {
    if (!isoStr) return '-';
    const d = new Date(isoStr);
    if (isNaN(d.getTime())) return isoStr;
    const tgl = String(d.getDate()).padStart(2, '0');
    const bln = String(d.getMonth() + 1).padStart(2, '0');
    const thn = d.getFullYear();
    return `${tgl}/${bln}/${thn}`;
}

/**
 * =========================================================================
 * MODAL KATALOG & PICKER STOK GUDANG ASAL
 * =========================================================================
 */

function openStokPickerModal() {
    const asalSelect = document.getElementById('gudang_asal_id');
    if (!asalSelect || !asalSelect.value) {
        alert('Silakan pilih Gudang Asal terlebih dahulu.');
        asalSelect.focus();
        return;
    }

    const modal = document.getElementById('modalStokPicker');
    if (!modal) return;

    modal.style.display = 'flex';
    modalCategoryFilter = 'ALL';
    modalSearchQuery = '';

    const searchInput = document.getElementById('modalSearchInput');
    if (searchInput) searchInput.value = '';

    // Set tab aktif
    const tabsContainer = document.getElementById('modalCategoryTabs');
    if (tabsContainer) {
        tabsContainer.querySelectorAll('.btn-picker-tab').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-category') === 'ALL');
        });
    }

    renderModalBatches();
}

function closeStokPickerModal() {
    const modal = document.getElementById('modalStokPicker');
    if (modal) modal.style.display = 'none';
}

function filterModalCategory(category) {
    modalCategoryFilter = category;

    const tabsContainer = document.getElementById('modalCategoryTabs');
    if (tabsContainer) {
        tabsContainer.querySelectorAll('.btn-picker-tab').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-category') === category);
        });
    }

    renderModalBatches();
}

function filterModalSearch(query) {
    modalSearchQuery = (query || '').toLowerCase().trim();
    renderModalBatches();
}

function renderModalBatches() {
    const tbody = document.getElementById('modalBatchesBody');
    if (!tbody) return;

    if (availableBatches.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:2.5rem;">Tidak ada stok batch tersedia di gudang asal ini.</td></tr>`;
        updateModalSelectedCounter();
        return;
    }

    const filtered = availableBatches.filter(b => {
        if (modalCategoryFilter !== 'ALL' && b._catGroup !== modalCategoryFilter) return false;
        if (modalSearchQuery) {
            const matchNm = (b.barang && b.barang.barang_nm ? b.barang.barang_nm : '').toLowerCase().includes(modalSearchQuery);
            const matchCd = (b.barang && b.barang.barang_cd ? b.barang.barang_cd : '').toLowerCase().includes(modalSearchQuery);
            const matchBatch = (b.batch_no || '').toLowerCase().includes(modalSearchQuery);
            if (!matchNm && !matchCd && !matchBatch) return false;
        }
        return true;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:2.5rem;">Tidak ada batch yang cocok dengan filter / pencarian.</td></tr>`;
        updateModalSelectedCounter();
        return;
    }

    let html = '';
    filtered.forEach((b, idx) => {
        const sisa = parseFloat(b.sisa_qty || 0);
        const sisaFormatted = sisa.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
        const satuan = b.barang && b.barang.satuan_dasar ? b.barang.satuan_dasar.satuan_nm : 'Unit';
        const barangNm = b.barang ? b.barang.barang_nm : `Barang #${b.barang_id}`;
        const barangCd = b.barang ? b.barang.barang_cd : '-';
        const exp = b.expired_tgl ? ` | Exp: ${formatTglSingkat(b.expired_tgl)}` : '';

        html += `
            <tr data-batch-no="${b.batch_no}">
                <td style="text-align: center;">
                    <input type="checkbox" class="modal-batch-checkbox" value="${b.batch_no}" 
                           data-idx="${idx}" onchange="onModalCheckboxChange(this, ${idx})">
                </td>
                <td>${getCategoryBadgeHtml(b._catGroup)}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">${barangNm}</div>
                    <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">${barangCd}</div>
                </td>
                <td>
                    <div style="font-family: monospace; font-weight: 800; color: #0f766e;">${b.batch_no}</div>
                    <div style="font-size: 0.7rem; color: #64748b;">Grade: <strong>${b.grade_cd || 'A'}</strong>${exp}</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-family: monospace; font-weight: 800; color: #047857;">${sisaFormatted}</div>
                    <div style="font-size: 0.7rem; color: #64748b;">${satuan}</div>
                </td>
                <td style="text-align: right;">
                    <div style="display: flex; gap: 4px; align-items: center; justify-content: flex-end;">
                        <input type="number" step="0.0001" min="0.0001" max="${sisa}" 
                               id="modalQty_${idx}" class="form-control" style="width: 95px; text-align: right; font-weight: 700; font-family: monospace; padding: 0.35rem 0.5rem;" 
                               placeholder="${sisaFormatted}" oninput="onModalQtyInput(${idx})">
                        <button type="button" class="btn-max-qty" style="position: static; padding: 4px 6px;" 
                                onclick="fillModalMaxQty(${idx}, ${sisa})">Max</button>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    updateModalSelectedCounter();
}

function onModalCheckboxChange(cb, idx) {
    const qtyInput = document.getElementById(`modalQty_${idx}`);
    if (cb.checked && qtyInput && (!qtyInput.value || parseFloat(qtyInput.value) <= 0)) {
        qtyInput.value = qtyInput.max || '';
    }
    updateModalSelectedCounter();
}

function onModalQtyInput(idx) {
    const qtyInput = document.getElementById(`modalQty_${idx}`);
    const row = qtyInput.closest('tr');
    const cb = row.querySelector('.modal-batch-checkbox');

    if (qtyInput && parseFloat(qtyInput.value || 0) > 0) {
        if (cb) cb.checked = true;
    }
    updateModalSelectedCounter();
}

function fillModalMaxQty(idx, max) {
    const qtyInput = document.getElementById(`modalQty_${idx}`);
    const row = qtyInput.closest('tr');
    const cb = row.querySelector('.modal-batch-checkbox');

    if (qtyInput) {
        qtyInput.value = max;
        if (cb) cb.checked = true;
    }
    updateModalSelectedCounter();
}

function toggleSelectAllModal(masterCb) {
    const checkboxes = document.querySelectorAll('#modalBatchesBody .modal-batch-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = masterCb.checked;
        const row = cb.closest('tr');
        const qtyInput = row.querySelector('input[type="number"]');
        if (masterCb.checked && qtyInput && (!qtyInput.value || parseFloat(qtyInput.value) <= 0)) {
            qtyInput.value = qtyInput.max || '';
        }
    });
    updateModalSelectedCounter();
}

function updateModalSelectedCounter() {
    const checked = document.querySelectorAll('#modalBatchesBody .modal-batch-checkbox:checked');
    const counter = document.getElementById('modalSelectedCount');
    if (counter) counter.textContent = checked.length;
}

/**
 * Tambahkan item batch terpilih dari modal ke tabel transfer utama
 */
function confirmModalSelection() {
    const checkedBoxes = document.querySelectorAll('#modalBatchesBody .modal-batch-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Silakan centang minimal 1 nomor batch.');
        return;
    }

    // Cek baris kosong pertama di tabel utama untuk di-reuse
    const existingRows = document.querySelectorAll('#itemsBody tr');
    let emptyRowIdx = null;
    if (existingRows.length === 1) {
        const firstBarangSelect = existingRows[0].querySelector('.select-barang');
        const firstBatchSelect = existingRows[0].querySelector('.select-batch');
        if ((!firstBarangSelect || !firstBarangSelect.value) && (!firstBatchSelect || !firstBatchSelect.value)) {
            emptyRowIdx = existingRows[0].id.replace('itemRow_', '');
        }
    }

    checkedBoxes.forEach(cb => {
        const row = cb.closest('tr');
        const batchNo = cb.value;
        const batchObj = availableBatches.find(b => b.batch_no === batchNo);
        if (!batchObj) return;

        const qtyInput = row.querySelector('input[type="number"]');
        const qtyVal = qtyInput && parseFloat(qtyInput.value) > 0 ? parseFloat(qtyInput.value) : parseFloat(batchObj.sisa_qty || 0);

        const preset = {
            barang_id: batchObj.barang_id,
            batch_no: batchObj.batch_no,
            grade_cd: batchObj.grade_cd || 'A',
            qty_transfer: qtyVal
        };

        if (emptyRowIdx !== null) {
            applyPresetToRow(emptyRowIdx, preset);
            emptyRowIdx = null;
        } else {
            addItemRow(preset);
        }
    });

    closeStokPickerModal();
    updateLiveSummary();
}

/**
 * Menerapkan data preset ke suatu baris
 */
function applyPresetToRow(rowIdx, preset) {
    const barangSelect = document.getElementById(`barangSelect_${rowIdx}`);
    if (barangSelect) {
        barangSelect.value = preset.barang_id;
        onBarangSelected(barangSelect, rowIdx);
    }

    const batchSelect = document.getElementById(`batchSelect_${rowIdx}`);
    if (batchSelect) {
        batchSelect.value = preset.batch_no;
        onBatchSelected(batchSelect, rowIdx);
    }

    const qtyInput = document.getElementById(`qtyInput_${rowIdx}`);
    if (qtyInput && preset.qty_transfer) {
        qtyInput.value = preset.qty_transfer;
        onQtyChange(rowIdx);
    }
}
