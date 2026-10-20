/**
 * JavaScript Logika Form Catat Tagihan Hutang (Create AP) - ERP PT Mirasa
 */

let unbilledGrnData = [];
let selectedGrnIds = new Set();
let currentSupplierTab = 'siap_tagih'; // 'siap_tagih' | 'semua'
let currentDateFilter = 'all'; // 'all' | '7days' | 'month'

document.addEventListener('DOMContentLoaded', function () {
    const selectedSupplierId = document.getElementById('selectedSupplierIdInput')?.value;
    if (selectedSupplierId) {
        loadUnbilledGrn(selectedSupplierId);
    }

    // Hitung ulang kalkulasi saat ada perubahan input angka di tabel
    document.getElementById('itemsTableBody')?.addEventListener('input', function (e) {
        if (e.target.matches('.calc-trigger')) {
            recalculateTotals();
        }
    });

    document.getElementById('potonganReturInput')?.addEventListener('input', recalculateTotals);

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSupplierModal();
        }
    });
});

/* ========================================================
   MODAL SUPPLIER PICKER FUNCTIONS
   ======================================================== */

window.openSupplierModal = function () {
    const modal = document.getElementById('modalSupplierPicker');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    const searchInput = document.getElementById('modalSupplierSearchInput');
    if (searchInput) {
        searchInput.value = '';
        setTimeout(() => searchInput.focus(), 100);
    }
    filterSupplierModal();
};

window.closeSupplierModal = function () {
    const modal = document.getElementById('modalSupplierPicker');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
};

window.switchSupplierTab = function (tab) {
    currentSupplierTab = tab;
    document.getElementById('tabSiapTagih')?.classList.toggle('active', tab === 'siap_tagih');
    document.getElementById('tabSemua')?.classList.toggle('active', tab === 'semua');
    filterSupplierModal();
};

window.filterSupplierModal = function () {
    const searchVal = (document.getElementById('modalSupplierSearchInput')?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.supplier-modal-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const code = card.getAttribute('data-code') || '';
        const hasGrn = card.getAttribute('data-has-grn') === '1';

        const matchSearch = (!searchVal || name.includes(searchVal) || code.includes(searchVal));
        const matchTab = (currentSupplierTab === 'semua' || (currentSupplierTab === 'siap_tagih' && hasGrn));

        if (matchSearch && matchTab) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const emptyEl = document.getElementById('supplierModalEmpty');
    if (emptyEl) {
        emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
    }
};

window.selectSupplierFromModal = function (id, name, code, grnCnt, contact, address) {
    const input = document.getElementById('selectedSupplierIdInput');
    if (input) input.value = id;

    // Update active supplier card
    const nameEl = document.getElementById('activeSupplierName');
    const codeEl = document.getElementById('activeSupplierCode');
    const badgeEl = document.getElementById('activeSupplierBadge');
    const contactEl = document.getElementById('activeSupplierContact');
    const addressEl = document.getElementById('activeSupplierAddress');

    if (nameEl) nameEl.textContent = name;
    if (codeEl) codeEl.textContent = code || '-';
    if (contactEl) contactEl.textContent = '📞 ' + (contact || 'Belum ada kontak');
    if (addressEl) addressEl.textContent = '📍 ' + (address || 'Alamat belum diatur');

    if (badgeEl) {
        badgeEl.textContent = `⚡ ${grnCnt} GRN Siap Tagih`;
        badgeEl.style.display = grnCnt > 0 ? 'inline-block' : 'none';
    }

    document.getElementById('supplierPlaceholderBox').style.display = 'none';
    document.getElementById('supplierActiveCard').style.display = 'flex';

    // Highlight matching quick pill badge (if any)
    syncActivePillBadge(id);

    closeSupplierModal();

    // Reset selection & fetch GRNs
    selectedGrnIds.clear();
    loadUnbilledGrn(id);
};

function syncActivePillBadge(supplierId) {
    document.querySelectorAll('.btn-supplier-pill').forEach(btn => {
        const id = btn.getAttribute('data-supplier-id');
        const isActive = (id == supplierId);
        btn.classList.toggle('active', isActive);
        if (isActive) {
            btn.style.background = '#0284c7';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#0284c7';
            const badge = btn.querySelector('span:last-child');
            if (badge) {
                badge.style.background = 'rgba(255,255,255,0.25)';
                badge.style.color = '#ffffff';
            }
        } else {
            btn.style.background = '#ffffff';
            btn.style.color = '#0f172a';
            btn.style.borderColor = '#cbd5e1';
            const badge = btn.querySelector('span:last-child');
            if (badge) {
                badge.style.background = '#dbeafe';
                badge.style.color = '#1e40af';
            }
        }
    });
}

/* ========================================================
   GRN LOADER & COMPACT TABLE SELECTION
   ======================================================== */

function loadUnbilledGrn(supplierId) {
    const loading = document.getElementById('grnLoadingIndicator');
    const emptyState = document.getElementById('grnEmptyState');
    const toolbar = document.getElementById('grnToolbar');
    const tableWrapper = document.getElementById('grnTableWrapper');
    const statusBar = document.getElementById('grnStatusBar');

    if (!supplierId) {
        if (toolbar) toolbar.style.display = 'none';
        if (tableWrapper) tableWrapper.style.display = 'none';
        if (statusBar) statusBar.style.display = 'none';
        if (emptyState) {
            emptyState.textContent = 'Silakan pilih mitra supplier di atas terlebih dahulu untuk menampilkan daftar GRN.';
            emptyState.style.display = 'block';
        }
        rebuildItemsTable();
        return;
    }

    if (loading) loading.style.display = 'block';
    if (emptyState) emptyState.style.display = 'none';
    if (toolbar) toolbar.style.display = 'none';
    if (tableWrapper) tableWrapper.style.display = 'none';
    if (statusBar) statusBar.style.display = 'none';

    fetch(`${window.appConfig.baseUrl}/keuangan/hutang/ajax-unbilled-grn?supplier_id=${supplierId}`)
        .then(res => res.json())
        .then(res => {
            if (loading) loading.style.display = 'none';

            if (res.status === 'success' && res.data && res.data.length > 0) {
                unbilledGrnData = res.data;
                if (toolbar) toolbar.style.display = 'flex';
                if (tableWrapper) tableWrapper.style.display = 'block';
                if (statusBar) statusBar.style.display = 'flex';
                if (emptyState) emptyState.style.display = 'none';

                renderGrnTable(res.data);
            } else {
                unbilledGrnData = [];
                if (toolbar) toolbar.style.display = 'none';
                if (tableWrapper) tableWrapper.style.display = 'none';
                if (statusBar) statusBar.style.display = 'none';
                if (emptyState) {
                    emptyState.textContent = 'Semua penerimaan barang (GRN) dari supplier ini sudah dibuatkan invoice tagihan.';
                    emptyState.style.display = 'block';
                }
            }
            rebuildItemsTable();
        })
        .catch(err => {
            if (loading) loading.style.display = 'none';
            console.error('Gagal mengambil data GRN:', err);
        });
}

/**
 * Render Tabel Kompak GRN
 */
function renderGrnTable(grnList) {
    const tbody = document.getElementById('grnTbodyContainer');
    if (!tbody) return;

    let html = '';
    grnList.forEach(grn => {
        const isChecked = selectedGrnIds.has(grn.terima_id);
        const tgl = grn.terima_tgl ? new Date(grn.terima_tgl).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
        const sjNo = grn.suratjalan_no || grn.surat_jalan_no || '-';

        // Hitung estimasi nominal & preview komoditas
        let estimasiGrnNominal = 0;
        let komoditasNames = [];
        let totalNettoKg = parseFloat(grn.timbangan_netto_kg || 0);

        (grn.details || []).forEach(d => {
            const q = parseFloat(d.terima_qty || 0);
            const h = parseFloat(d.harga_nominal || d.harga_satuan || (d.po_detail ? d.po_detail.harga_nominal : 0) || 0);
            estimasiGrnNominal += (q * h);

            const bName = d.barang ? d.barang.barang_nm : 'Barang';
            if (!komoditasNames.includes(bName)) {
                komoditasNames.push(bName);
            }
            if (totalNettoKg === 0) {
                totalNettoKg += q;
            }
        });

        const komoditasSummary = komoditasNames.join(', ');

        html += `
            <tr class="grn-row ${isChecked ? 'selected' : ''}" 
                data-id="${grn.terima_id}"
                data-date="${grn.terima_tgl || ''}"
                data-search="${(grn.terima_no + ' ' + sjNo + ' ' + komoditasSummary).toLowerCase()}"
                onclick="toggleSelectGrn(${grn.terima_id})">
                <td style="padding: 0.65rem 0.75rem; text-align: center;">
                    <input type="checkbox" 
                           id="chk_grn_${grn.terima_id}" 
                           class="grn-row-checkbox"
                           ${isChecked ? 'checked' : ''} 
                           style="cursor: pointer; accent-color: #0284c7; width: 16px; height: 16px;"
                           onclick="event.stopPropagation(); toggleSelectGrn(${grn.terima_id})">
                </td>
                <td style="padding: 0.65rem 0.5rem;">
                    <div style="font-weight: 800; font-family: monospace; color: #0284c7; font-size: 0.825rem;">
                        ${grn.terima_no}
                    </div>
                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 1px;">
                        SJ: <strong>${sjNo}</strong>
                    </div>
                </td>
                <td style="padding: 0.65rem 0.5rem; color: #334155; font-size: 0.775rem;">
                    ${tgl}
                </td>
                <td style="padding: 0.65rem 0.5rem;">
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.8rem;">
                        ${komoditasSummary}
                    </div>
                    <div style="font-size: 0.7rem; color: #16a34a; font-weight: 600;">
                        ${totalNettoKg > 0 ? parseFloat(totalNettoKg).toLocaleString('id-ID') + ' KG' : (grn.details || []).length + ' Item'}
                    </div>
                </td>
                <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800; color: #0f172a; font-size: 0.85rem;">
                    Rp ${Math.round(estimasiGrnNominal).toLocaleString('id-ID')}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    filterGrnList();
}

/**
 * Filter GRN List berdasarkan Pencarian & Filter Tanggal
 */
window.filterGrnList = function () {
    const searchVal = (document.getElementById('grnSearchInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#grnTbodyContainer .grn-row');
    const now = new Date();
    let visibleCount = 0;

    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        const dateStr = row.getAttribute('data-date') || '';
        const rowDate = dateStr ? new Date(dateStr) : null;

        let matchDate = true;
        if (currentDateFilter === '7days' && rowDate) {
            const diffDays = (now - rowDate) / (1000 * 60 * 60 * 24);
            matchDate = (diffDays <= 7 && diffDays >= -1);
        } else if (currentDateFilter === 'month' && rowDate) {
            matchDate = (rowDate.getMonth() === now.getMonth() && rowDate.getFullYear() === now.getFullYear());
        }

        const matchSearch = (!searchVal || searchData.includes(searchVal));

        if (matchSearch && matchDate) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update counter status
    const shownEl = document.getElementById('grnShownCount');
    if (shownEl) shownEl.textContent = visibleCount;

    updateGrnStatusBar();
};

window.filterGrnByDate = function (filterType, btnEl) {
    currentDateFilter = filterType;
    document.querySelectorAll('.grn-date-filter-btn').forEach(b => b.classList.remove('active'));
    btnEl?.classList.add('active');
    filterGrnList();
};

/**
 * Toggle satu GRN
 */
window.toggleSelectGrn = function (terimaId) {
    if (selectedGrnIds.has(terimaId)) {
        selectedGrnIds.delete(terimaId);
    } else {
        selectedGrnIds.add(terimaId);
    }

    // Update row visual
    const row = document.querySelector(`.grn-row[data-id="${terimaId}"]`);
    const chk = document.getElementById(`chk_grn_${terimaId}`);
    if (row && chk) {
        const isChecked = selectedGrnIds.has(terimaId);
        chk.checked = isChecked;
        row.classList.toggle('selected', isChecked);
    }

    updateGrnStatusBar();
    rebuildItemsTable();
};

/**
 * Pilih Semua GRN yang saat ini tampak
 */
window.selectAllGrn = function () {
    const visibleRows = document.querySelectorAll('#grnTbodyContainer .grn-row:not([style*="display: none"])');
    visibleRows.forEach(row => {
        const id = parseInt(row.getAttribute('data-id'));
        if (id) {
            selectedGrnIds.add(id);
            const chk = document.getElementById(`chk_grn_${id}`);
            if (chk) chk.checked = true;
            row.classList.add('selected');
        }
    });

    const masterCheck = document.getElementById('headerMasterCheck');
    if (masterCheck) masterCheck.checked = true;

    updateGrnStatusBar();
    rebuildItemsTable();
};

/**
 * Batalkan Semua Pilihan GRN
 */
window.clearAllGrn = function () {
    selectedGrnIds.clear();
    document.querySelectorAll('#grnTbodyContainer .grn-row').forEach(row => {
        row.classList.remove('selected');
        const chk = row.querySelector('.grn-row-checkbox');
        if (chk) chk.checked = false;
    });

    const masterCheck = document.getElementById('headerMasterCheck');
    if (masterCheck) masterCheck.checked = false;

    updateGrnStatusBar();
    rebuildItemsTable();
};

/**
 * Checkbox master di header tabel
 */
window.toggleMasterCheck = function (masterEl) {
    if (masterEl.checked) {
        selectAllGrn();
    } else {
        clearAllGrn();
    }
};

/**
 * Update Baris Status Bar Seleksi GRN
 */
function updateGrnStatusBar() {
    const countEl = document.getElementById('grnSelectedCount');
    const totalEl = document.getElementById('grnSelectedTotal');

    let totalEst = 0;
    unbilledGrnData.forEach(g => {
        if (selectedGrnIds.has(g.terima_id)) {
            (g.details || []).forEach(d => {
                const q = parseFloat(d.terima_qty || 0);
                const h = parseFloat(d.harga_nominal || d.harga_satuan || (d.po_detail ? d.po_detail.harga_nominal : 0) || 0);
                totalEst += (q * h);
            });
        }
    });

    if (countEl) countEl.textContent = selectedGrnIds.size;
    if (totalEl) totalEl.textContent = 'Rp ' + Math.round(totalEst).toLocaleString('id-ID');

    // Update master check status
    const visibleRows = document.querySelectorAll('#grnTbodyContainer .grn-row:not([style*="display: none"])');
    const masterCheck = document.getElementById('headerMasterCheck');
    if (masterCheck && visibleRows.length > 0) {
        let allVisibleChecked = true;
        visibleRows.forEach(r => {
            const id = parseInt(r.getAttribute('data-id'));
            if (!selectedGrnIds.has(id)) allVisibleChecked = false;
        });
        masterCheck.checked = allVisibleChecked;
    }
}

/* ========================================================
   TABLE RINCIAN ITEM & AKUMULASI NILAI
   ======================================================== */

function rebuildItemsTable() {
    const tbody = document.getElementById('itemsTableBody');
    if (!tbody) return;

    if (selectedGrnIds.size === 0) {
        tbody.innerHTML = `
            <tr id="emptyItemsRow">
                <td colspan="7" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.825rem;">
                    Centang salah satu atau beberapa Surat Jalan / GRN di atas untuk memuat rincian tagihan.
                </td>
            </tr>
        `;
        recalculateTotals();
        return;
    }

    let html = '';
    let rowIndex = 0;

    unbilledGrnData.forEach(grn => {
        if (!selectedGrnIds.has(grn.terima_id)) return;

        (grn.details || []).forEach(dtl => {
            const barangNm = dtl.barang ? dtl.barang.barang_nm : 'Komoditas';
            const satuan = (dtl.barang && dtl.barang.satuan_dasar) 
                ? (dtl.barang.satuan_dasar.satuan_nm || dtl.barang.satuan_dasar.satuan_cd) 
                : 'KG';

            const qty = parseFloat(dtl.terima_qty || 0);
            const harga = parseFloat(
                dtl.harga_nominal || 
                dtl.harga_satuan || 
                (dtl.po_detail ? dtl.po_detail.harga_nominal : 0) || 
                (dtl.barang ? dtl.barang.harga_beli_standar : 0) || 
                0
            );
            const diskon = parseFloat(dtl.diskon_nominal || 0);
            const ppn = parseFloat(dtl.ppn_nominal || 0);
            const subtotal = qty * harga;
            const netto = subtotal - diskon + ppn;

            html += `
                <tr class="item-calc-row" data-index="${rowIndex}" style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 0.65rem 0.75rem;">
                        <input type="hidden" name="items[${rowIndex}][terima_id]" value="${grn.terima_id}">
                        <input type="hidden" name="items[${rowIndex}][terima_dtl_id]" value="${dtl.terimadtl_id || ''}">
                        <input type="hidden" name="items[${rowIndex}][barang_id]" value="${dtl.barang_id || ''}">
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8125rem;">
                            ${barangNm}
                        </div>
                        <input type="text" 
                               name="items[${rowIndex}][deskripsi]" 
                               value="${barangNm} (GRN: ${grn.terima_no})" 
                               class="form-control" 
                               style="font-size: 0.72rem; height: 26px; padding: 2px 6px; margin-top: 3px;">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 110px;">
                        <input type="number" 
                               step="0.0001" 
                               min="0.0001" 
                               name="items[${rowIndex}][qty]" 
                               value="${qty}" 
                               class="form-control calc-trigger input-qty" 
                               style="font-size: 0.8rem; height: 32px; text-align: right; font-weight: 700;">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 70px;">
                        <input type="text" 
                               name="items[${rowIndex}][satuan]" 
                               value="${satuan}" 
                               class="form-control" 
                               style="font-size: 0.75rem; height: 32px; text-align: center;" 
                               readonly>
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 140px;">
                        <input type="number" 
                               step="1" 
                               min="0" 
                               name="items[${rowIndex}][harga_satuan]" 
                               value="${harga}" 
                               class="form-control calc-trigger input-harga" 
                               style="font-size: 0.8rem; height: 32px; text-align: right; font-weight: 700; color: #0f172a;">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 110px;">
                        <input type="number" 
                               step="1" 
                               min="0" 
                               name="items[${rowIndex}][diskon_nominal]" 
                               value="${diskon}" 
                               class="form-control calc-trigger input-diskon" 
                               style="font-size: 0.8rem; height: 32px; text-align: right;" 
                               placeholder="0">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 110px;">
                        <input type="number" 
                               step="1" 
                               min="0" 
                               name="items[${rowIndex}][ppn_nominal]" 
                               value="${ppn}" 
                               class="form-control calc-trigger input-ppn" 
                               style="font-size: 0.8rem; height: 32px; text-align: right;" 
                               placeholder="0">
                    </td>
                    <td style="padding: 0.65rem 0.75rem; width: 140px; text-align: right; font-weight: 800; color: #0284c7; font-size: 0.85rem;" class="cell-netto">
                        Rp ${Math.round(netto).toLocaleString('id-ID')}
                    </td>
                </tr>
            `;
            rowIndex++;
        });
    });

    tbody.innerHTML = html;
    recalculateTotals();
}

/**
 * Hitung seluruh nilai akumulasi tagihan secara otomatis
 */
function recalculateTotals() {
    let totalBruto = 0;
    let totalDiskon = 0;
    let totalPpn = 0;
    let totalNetto = 0;

    document.querySelectorAll('.item-calc-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.input-qty')?.value || 0);
        const harga = parseFloat(row.querySelector('.input-harga')?.value || 0);
        const diskon = parseFloat(row.querySelector('.input-diskon')?.value || 0);
        const ppn = parseFloat(row.querySelector('.input-ppn')?.value || 0);

        const subtotal = qty * harga;
        const netto = subtotal - diskon + ppn;

        totalBruto += subtotal;
        totalDiskon += diskon;
        totalPpn += ppn;
        totalNetto += netto;

        const cellNetto = row.querySelector('.cell-netto');
        if (cellNetto) {
            cellNetto.textContent = 'Rp ' + Math.round(netto).toLocaleString('id-ID');
        }
    });

    const potonganRetur = parseFloat(document.getElementById('potonganReturInput')?.value || 0);
    const grandTotal = Math.max(0, totalNetto - potonganRetur);

    // Update elemen sticky panel ringkasan
    const elBruto = document.getElementById('summaryTotalBruto');
    const elDiskon = document.getElementById('summaryTotalDiskon');
    const elPpn = document.getElementById('summaryTotalPpn');
    const elRetur = document.getElementById('summaryPotonganRetur');
    const elGrand = document.getElementById('summaryGrandTotal');

    if (elBruto) elBruto.textContent = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (elDiskon) elDiskon.textContent = 'Rp ' + Math.round(totalDiskon).toLocaleString('id-ID');
    if (elPpn) elPpn.textContent = 'Rp ' + Math.round(totalPpn).toLocaleString('id-ID');
    if (elRetur) elRetur.textContent = 'Rp ' + Math.round(potonganRetur).toLocaleString('id-ID');
    if (elGrand) elGrand.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
}
