/**
 * JavaScript Logika Form Catat Tagihan Hutang (Create AP) - ERP PT Mirasa
 */

let unbilledGrnData = [];
let selectedGrnIds = new Set();
let currentSupplierTab = 'siap_tagih'; // 'siap_tagih' | 'semua'
let currentDateFilter = 'all'; // 'all' | '7days' | 'month'
let currentSupplierTopHari = 14;

function parseDecimal(val) {
    if (typeof val === 'number') return val;
    if (!val) return 0;
    val = String(val).replace(',', '.').trim();
    const parsed = parseFloat(val);
    return isNaN(parsed) ? 0 : parsed;
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.appConfig && typeof window.appConfig.initialTopHari !== 'undefined') {
        currentSupplierTopHari = parseInt(window.appConfig.initialTopHari, 10);
    }

    const selectedSupplierId = document.getElementById('selectedSupplierIdInput')?.value;
    if (selectedSupplierId) {
        loadUnbilledGrn(selectedSupplierId);
    }

    // Tangani input koma agar otomatis menjadi titik desimal yang valid di semua browser
    document.addEventListener('keydown', function (e) {
        if (e.target && (e.target.matches('input[type="number"]') || e.target.matches('.calc-trigger'))) {
            if (e.key === ',' || e.key === 'Decimal') {
                e.preventDefault();
                const input = e.target;
                const val = input.value || '';
                if (!val.includes('.')) {
                    input.value = val + '.';
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
    });

    document.addEventListener('paste', function (e) {
        if (e.target && (e.target.matches('input[type="number"]') || e.target.matches('.calc-trigger'))) {
            const pasteData = (e.clipboardData || window.clipboardData)?.getData('text');
            if (pasteData && pasteData.includes(',')) {
                e.preventDefault();
                e.target.value = pasteData.replace(',', '.');
                e.target.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    });

    // Hitung ulang kalkulasi & sinkronisasi diskon serta perlakuan PPN saat ada perubahan di tabel
    document.getElementById('itemsTableBody')?.addEventListener('input', function (e) {
        const target = e.target;
        const row = target.closest('.item-calc-row');

        if (row) {
            const qty = parseDecimal(row.querySelector('.input-qty')?.value);
            const harga = parseDecimal(row.querySelector('.input-harga')?.value);
            const subtotal = qty * harga;

            if (target.matches('.input-diskon-persen')) {
                // Input % berubah -> otomatis hitung nominal diskon Rp
                const persen = parseFloat(target.value || 0);
                const diskonNominal = (persen > 0 && subtotal > 0) ? Math.round(subtotal * (persen / 100)) : 0;
                const nomInput = row.querySelector('.input-diskon-nominal');
                if (nomInput) nomInput.value = diskonNominal > 0 ? diskonNominal : 0;
                updateRowPpnState(row, false);
            } else if (target.matches('.input-diskon-nominal')) {
                // Input Rp berubah -> otomatis hitung diskon %
                const diskonNominal = parseFloat(target.value || 0);
                const persen = (subtotal > 0 && diskonNominal > 0) ? parseFloat(((diskonNominal / subtotal) * 100).toFixed(2)) : 0;
                const persenInput = row.querySelector('.input-diskon-persen');
                if (persenInput) persenInput.value = persen > 0 ? persen : '';
                updateRowPpnState(row, false);
            } else if (target.matches('.input-qty') || target.matches('.input-harga') || target.matches('.input-potongan-item')) {
                // Kuantitas, harga satuan, atau potongan mutu berubah
                const persenInput = row.querySelector('.input-diskon-persen');
                const persen = parseFloat(persenInput?.value || 0);
                if (persen > 0) {
                    const diskonNominal = Math.round(subtotal * (persen / 100));
                    const nomInput = row.querySelector('.input-diskon-nominal');
                    if (nomInput) nomInput.value = diskonNominal;
                }
                updateRowPpnState(row, false);
            } else if (target.matches('.input-ppn')) {
                // User mengoreksi nilai nominal PPN secara manual (misal selisih pembulatan sen dari supplier)
                updateRowPpnState(row, true);
            }
        }

        if (target.matches('.calc-trigger')) {
            recalculateTotals();
        }
    });

    // Listener saat tipe perlakuan PPN diubah pada dropdown
    document.getElementById('itemsTableBody')?.addEventListener('change', function (e) {
        const target = e.target;
        if (target.matches('.input-ppn-tipe')) {
            const row = target.closest('.item-calc-row');
            if (row) {
                updateRowPpnState(row, false);
                recalculateTotals();
            }
        }
    });

    const returEl = document.getElementById('potonganReturInput');
    if (returEl) {
        returEl.addEventListener('input', function () {
            this.dataset.manualEdited = 'true';
            recalculateTotals();
        });
    }
    document.getElementById('biayaOngkirInput')?.addEventListener('input', recalculateTotals);
    document.getElementById('biayaOngkirInput')?.addEventListener('change', recalculateTotals);

    // Event listener 2 arah antara Tanggal Tagihan, TOP Hari, dan Tanggal Jatuh Tempo
    const tglTagihanEl = document.getElementById('tglTagihanInput');
    if (tglTagihanEl) {
        tglTagihanEl.addEventListener('change', function () { updateTempoDateFromTagihan(false); });
        tglTagihanEl.addEventListener('input', function () { updateTempoDateFromTagihan(false); });
    }

    const topHariEl = document.getElementById('topHariInput');
    if (topHariEl) {
        const handleTopInput = function () {
            const customTop = parseInt(this.value, 10);
            if (!isNaN(customTop) && customTop >= 0) {
                currentSupplierTopHari = customTop;
                updateTempoDateFromTagihan(true);
            }
        };
        topHariEl.addEventListener('input', handleTopInput);
        topHariEl.addEventListener('change', handleTopInput);
    }

    document.getElementById('tglJatuhTempoInput')?.addEventListener('change', function () {
        const tglTagihan = document.getElementById('tglTagihanInput')?.value;
        const tglTempo = this.value;
        if (tglTagihan && tglTempo) {
            const p1 = tglTagihan.split('-');
            const p2 = tglTempo.split('-');
            const d1 = new Date(parseInt(p1[0], 10), parseInt(p1[1], 10) - 1, parseInt(p1[2], 10));
            const d2 = new Date(parseInt(p2[0], 10), parseInt(p2[1], 10) - 1, parseInt(p2[2], 10));
            const diffDays = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
            if (!isNaN(diffDays) && diffDays >= 0) {
                currentSupplierTopHari = diffDays;
                const topInput = document.getElementById('topHariInput');
                if (topInput) topInput.value = diffDays;
                const helpText = document.getElementById('jatuhTempoHelpText');
                if (helpText) {
                    helpText.innerHTML = `✏️ Disesuaikan via kalender: Selisih <strong>${diffDays} Hari</strong> dari Tgl Tagihan`;
                    helpText.style.color = '#d97706';
                }
            }
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSupplierModal();
        }
    });
});

function applySupplierTopData(topHari, name, code, contact, address) {
    if (typeof topHari !== 'undefined' && topHari !== null) {
        currentSupplierTopHari = parseInt(topHari, 10) >= 0 ? parseInt(topHari, 10) : 14;

        const topInput = document.getElementById('topHariInput');
        if (topInput) topInput.value = currentSupplierTopHari;

        const hintEl = document.getElementById('topDefaultDaysText');
        if (hintEl) hintEl.textContent = currentSupplierTopHari + ' Hari';

        const badgeTopDays = document.getElementById('activeSupplierTopDays');
        if (badgeTopDays) badgeTopDays.textContent = currentSupplierTopHari;

        updateTempoDateFromTagihan(false);
    }
}

function updateTempoDateFromTagihan(isManual = false) {
    const tglTagihanInput = document.getElementById('tglTagihanInput') || document.querySelector('input[name="tgl_tagihan"]');
    const tglTempoInput = document.getElementById('tglJatuhTempoInput') || document.querySelector('input[name="tgl_jatuh_tempo"]');
    const topInput = document.getElementById('topHariInput');
    const helpText = document.getElementById('jatuhTempoHelpText');

    const topDays = topInput && !isNaN(parseInt(topInput.value, 10)) 
        ? parseInt(topInput.value, 10) 
        : (parseInt(currentSupplierTopHari, 10) >= 0 ? parseInt(currentSupplierTopHari, 10) : 14);

    if (tglTagihanInput && tglTempoInput) {
        const rawDate = tglTagihanInput.value || new Date().toISOString().split('T')[0];
        const parts = rawDate.split('-');
        const baseDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        baseDate.setDate(baseDate.getDate() + topDays);
        const yyyy = baseDate.getFullYear();
        const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
        const dd = String(baseDate.getDate()).padStart(2, '0');
        tglTempoInput.value = `${yyyy}-${mm}-${dd}`;

        // Feedback teks dinamis
        if (helpText) {
            if (isManual) {
                helpText.innerHTML = `✏️ Disesuaikan manual: Tgl Tagihan + <strong>${topDays} Hari</strong>`;
                helpText.style.color = '#d97706';
            } else {
                helpText.innerHTML = `⏱️ Otomatis: Tgl Tagihan + <strong>${topDays} Hari</strong> (Sesuai TOP Master Supplier)`;
                helpText.style.color = '#0284c7';
            }
        }

        // Efek visual highlight sesaat
        tglTempoInput.style.transition = 'all 0.3s ease';
        tglTempoInput.style.borderColor = '#10b981';
        tglTempoInput.style.backgroundColor = '#f0fdf4';
        setTimeout(() => {
            tglTempoInput.style.borderColor = '';
            tglTempoInput.style.backgroundColor = '';
        }, 600);
    }
}

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

window.selectSupplierFromModal = function (id, name, code, grnCnt, contact, address, topHari) {
    const input = document.getElementById('selectedSupplierIdInput');
    if (input) input.value = id;

    // Set TOP hari supplier dan auto-update tanggal jatuh tempo
    applySupplierTopData(topHari, name, code, contact, address);

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

            if (res.status === 'success') {
                if (res.supplier) {
                    applySupplierTopData(res.supplier.top_hari, res.supplier.name, res.supplier.code, res.supplier.kontak_no, res.supplier.alamat);
                }

                if (res.data && res.data.length > 0) {
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
            }
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
        let estimasiGrnNominal = parseFloat(grn.total_tagihan || 0);
        let komoditasNames = [];
        let totalNettoKg = parseFloat(grn.timbangan_netto_kg || 0);

        if (estimasiGrnNominal <= 0) {
            let subtotalBrutoCalc = 0;
            let diskonCalc = 0;
            let ppnCalc = 0;
            (grn.details || []).forEach(d => {
                const q = (typeof d.netto_qty !== 'undefined') ? parseFloat(d.netto_qty) : parseFloat(d.terima_qty || 0);
                const h = parseFloat(d.harga_satuan || d.harga_nominal || (d.po_detail ? d.po_detail.harga_nominal : 0) || 0);
                const disc = (typeof d.diskon_total !== 'undefined') ? parseFloat(d.diskon_total) : parseFloat(d.diskon_nominal || 0);
                const ppn = (typeof d.ppn_total !== 'undefined') ? parseFloat(d.ppn_total) : parseFloat(d.ppn_nominal || 0);

                subtotalBrutoCalc += (q * h);
                diskonCalc += disc;
                ppnCalc += ppn;
            });
            const potonganGlobal = parseFloat(grn.potongan_global || grn.potongan_nominal || 0);
            estimasiGrnNominal = Math.max(0, (subtotalBrutoCalc - diskonCalc - potonganGlobal) + ppnCalc);
        }

        (grn.details || []).forEach(d => {
            const q = (typeof d.netto_qty !== 'undefined') ? parseFloat(d.netto_qty) : parseFloat(d.terima_qty || 0);
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
            let grnVal = parseFloat(g.total_tagihan || 0);
            if (grnVal <= 0) {
                let subB = 0, disc = 0, ppn = 0;
                (g.details || []).forEach(d => {
                    const q = (typeof d.netto_qty !== 'undefined') ? parseFloat(d.netto_qty) : parseFloat(d.terima_qty || 0);
                    const h = parseFloat(d.harga_satuan || d.harga_nominal || (d.po_detail ? d.po_detail.harga_nominal : 0) || 0);
                    subB += (q * h);
                    disc += (typeof d.diskon_total !== 'undefined') ? parseFloat(d.diskon_total) : parseFloat(d.diskon_nominal || 0);
                    ppn += (typeof d.ppn_total !== 'undefined') ? parseFloat(d.ppn_total) : parseFloat(d.ppn_nominal || 0);
                });
                const pot = parseFloat(g.potongan_global || g.potongan_nominal || 0);
                grnVal = Math.max(0, (subB - disc - pot) + ppn);
            }
            totalEst += grnVal;
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

/**
 * Sinkronisasi status PPN, DPP, dan nilai input per baris tabel
 */
function updateRowPpnState(row, manualPpnEdited = false) {
    const qty = parseDecimal(row.querySelector('.input-qty')?.value);
    const harga = parseDecimal(row.querySelector('.input-harga')?.value);
    const diskon = parseDecimal(row.querySelector('.input-diskon-nominal')?.value);
    const potongan = parseDecimal(row.querySelector('.input-potongan-item')?.value);
    const subtotal = qty * harga;
    const nilaiBersih = Math.max(0, subtotal - diskon - potongan);

    const tipeSelect = row.querySelector('.input-ppn-tipe');
    const ppnInput = row.querySelector('.input-ppn');
    const dppInput = row.querySelector('.input-dpp');
    const dppBadge = row.querySelector('.ppn-dpp-badge');

    const ppnTipe = tipeSelect ? tipeSelect.value : 'NON_PPN';

    let ppn = manualPpnEdited ? parseDecimal(ppnInput?.value) : 0;
    let dpp = nilaiBersih;

    if (ppnTipe === 'NON_PPN') {
        ppn = 0;
        dpp = nilaiBersih;
        if (ppnInput) {
            ppnInput.value = 0;
            ppnInput.readOnly = true;
            ppnInput.style.background = '#f8fafc';
            ppnInput.style.color = '#94a3b8';
        }
        if (dppBadge) dppBadge.style.display = 'none';
    } else if (ppnTipe === 'PPN_11') {
        dpp = nilaiBersih;
        if (!manualPpnEdited) {
            ppn = Math.round(dpp * 0.11);
            if (ppnInput) ppnInput.value = ppn;
        }
        if (ppnInput) {
            ppnInput.readOnly = false;
            ppnInput.style.background = '#ffffff';
            ppnInput.style.color = '#0284c7';
        }
        if (dppBadge) dppBadge.style.display = 'none';
    } else if (ppnTipe === 'PPN_INCLUDE') {
        if (!manualPpnEdited) {
            dpp = Math.round((nilaiBersih / 1.11) * 100) / 100;
            ppn = Math.round(nilaiBersih - dpp);
            if (ppnInput) ppnInput.value = ppn;
        } else {
            dpp = Math.max(0, nilaiBersih - ppn);
        }
        if (ppnInput) {
            ppnInput.readOnly = false;
            ppnInput.style.background = '#ffffff';
            ppnInput.style.color = '#0284c7';
        }
        if (dppBadge) {
            dppBadge.style.display = 'block';
            dppBadge.textContent = 'DPP: Rp ' + Math.round(dpp).toLocaleString('id-ID');
        }
    }

    if (dppInput) dppInput.value = dpp;
}

function rebuildItemsTable() {
    const tbody = document.getElementById('itemsTableBody');
    if (!tbody) return;

    if (selectedGrnIds.size === 0) {
        tbody.innerHTML = `
            <tr id="emptyItemsRow">
                <td colspan="8" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.825rem;">
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

            // Kuantitas otomatis netto bersih lolos QC (terima - reject/afkir)
            const qty = (typeof dtl.netto_qty !== 'undefined') 
                ? parseFloat(dtl.netto_qty) 
                : Math.max(0, parseFloat(dtl.terima_qty || 0) - parseFloat(dtl.reject_qty || 0));

            const harga = parseFloat(
                dtl.harga_satuan || 
                dtl.harga_nominal || 
                (dtl.po_detail ? dtl.po_detail.harga_nominal : 0) || 
                (dtl.barang ? dtl.barang.harga_beli_standar : 0) || 
                0
            );

            // Diskon murni (% dan nominal Rp)
            const diskonPersen = (typeof dtl.diskon_persen !== 'undefined') ? parseFloat(dtl.diskon_persen) : 0;
            const diskonNominal = (typeof dtl.diskon_nominal !== 'undefined') 
                ? parseFloat(dtl.diskon_nominal) 
                : (typeof dtl.diskon_total !== 'undefined' ? parseFloat(dtl.diskon_total) : 0);

            // Potongan mutu / sortasi fisik timbangan
            const potonganNominal = (typeof dtl.potongan_nominal !== 'undefined') ? parseFloat(dtl.potongan_nominal) : 0;

            // PPN per-item: 'NON_PPN' | 'PPN_11' | 'PPN_INCLUDE'
            let ppnTipe = dtl.ppn_tipe || (dtl.ppn_total > 0 || dtl.ppn_nominal > 0 ? 'PPN_11' : 'NON_PPN');
            let ppn = (typeof dtl.ppn_total !== 'undefined')
                ? parseFloat(dtl.ppn_total)
                : parseFloat(dtl.ppn_nominal || 0);

            const subtotal = qty * harga;
            const nilaiBersih = Math.max(0, subtotal - diskonNominal - potonganNominal);

            let dpp = nilaiBersih;
            let netto = nilaiBersih;

            if (ppnTipe === 'PPN_INCLUDE') {
                netto = nilaiBersih;
                if (ppn > 0) {
                    dpp = Math.max(0, nilaiBersih - ppn);
                } else {
                    dpp = Math.round((nilaiBersih / 1.11) * 100) / 100;
                    ppn = Math.round(nilaiBersih - dpp);
                }
            } else if (ppnTipe === 'PPN_11') {
                dpp = nilaiBersih;
                if (ppn <= 0) {
                    ppn = Math.round(dpp * 0.11);
                }
                netto = dpp + ppn;
            } else {
                ppnTipe = 'NON_PPN';
                dpp = nilaiBersih;
                ppn = 0;
                netto = nilaiBersih;
            }

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
                    <td style="padding: 0.65rem 0.5rem; width: 105px;">
                        <input type="number" 
                               step="0.0001" 
                               min="0.0001" 
                               name="items[${rowIndex}][qty]" 
                               value="${qty}" 
                               class="form-control calc-trigger input-qty" 
                               style="font-size: 0.8rem; height: 32px; text-align: right; font-weight: 700;">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 60px;">
                        <input type="text" 
                               name="items[${rowIndex}][satuan]" 
                               value="${satuan}" 
                               class="form-control" 
                               style="font-size: 0.75rem; height: 32px; text-align: center;" 
                               readonly>
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 125px;">
                        <input type="number" 
                               step="any" 
                               min="0" 
                               name="items[${rowIndex}][harga_satuan]" 
                               value="${harga}" 
                               class="form-control calc-trigger input-harga" 
                               style="font-size: 0.8rem; height: 32px; text-align: right; font-weight: 700; color: #0f172a;">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 165px;">
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <div style="position: relative; width: 60px; flex-shrink: 0;">
                                <input type="number" 
                                       step="any" 
                                       min="0" 
                                       max="100" 
                                       name="items[${rowIndex}][diskon_persen]" 
                                       value="${diskonPersen > 0 ? diskonPersen : ''}" 
                                       class="form-control calc-trigger input-diskon-persen" 
                                       style="font-size: 0.775rem; height: 32px; text-align: right; padding-right: 14px;" 
                                       placeholder="0">
                                <span style="position: absolute; right: 4px; top: 7px; font-size: 0.7rem; color: #64748b; font-weight: 700; pointer-events: none;">%</span>
                            </div>
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="items[${rowIndex}][diskon_nominal]" 
                                   value="${diskonNominal > 0 ? Math.round(diskonNominal) : 0}" 
                                   class="form-control calc-trigger input-diskon-nominal" 
                                   style="font-size: 0.775rem; height: 32px; text-align: right; flex: 1; min-width: 80px;" 
                                   placeholder="0">
                        </div>
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 120px;">
                        <input type="number" 
                               step="any" 
                               min="0" 
                               name="items[${rowIndex}][potongan_nominal]" 
                               value="${potonganNominal > 0 ? Math.round(potonganNominal) : 0}" 
                               class="form-control calc-trigger input-potongan-item" 
                               style="font-size: 0.775rem; height: 32px; text-align: right; color: #b45309; font-weight: 700;" 
                               placeholder="0">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 175px;">
                        <select name="items[${rowIndex}][ppn_tipe]" 
                                class="form-control calc-trigger input-ppn-tipe" 
                                style="font-size: 0.75rem; height: 30px; font-weight: 700; margin-bottom: 4px; padding: 2px 6px; border: 1.5px solid #cbd5e1;">
                            <option value="NON_PPN" ${ppnTipe === 'NON_PPN' ? 'selected' : ''}>Non PPN (0%)</option>
                            <option value="PPN_11" ${ppnTipe === 'PPN_11' ? 'selected' : ''}>PPN 11% (Exclude)</option>
                            <option value="PPN_INCLUDE" ${ppnTipe === 'PPN_INCLUDE' ? 'selected' : ''}>Include PPN (11%)</option>
                        </select>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="items[${rowIndex}][ppn_nominal]" 
                                   value="${ppnTipe === 'NON_PPN' ? 0 : Math.round(ppn)}" 
                                   class="form-control calc-trigger input-ppn" 
                                   style="font-size: 0.775rem; height: 28px; text-align: right; color: #0284c7; font-weight: 600; flex: 1; min-width: 65px; ${ppnTipe === 'NON_PPN' ? 'background: #f8fafc; color: #94a3b8;' : ''}" 
                                   placeholder="0"
                                   ${ppnTipe === 'NON_PPN' ? 'readonly' : ''}>
                            <input type="hidden" name="items[${rowIndex}][dpp_nominal]" class="input-dpp" value="${dpp}">
                        </div>
                        <div class="ppn-dpp-badge" style="font-size: 0.68rem; color: #0369a1; font-weight: 700; margin-top: 2px; ${ppnTipe === 'PPN_INCLUDE' ? '' : 'display: none;'}">
                            DPP: Rp ${Math.round(dpp).toLocaleString('id-ID')}
                        </div>
                    </td>
                    <td style="padding: 0.65rem 0.75rem; width: 130px; text-align: right; font-weight: 800; color: #0284c7; font-size: 0.85rem;" class="cell-netto">
                        Rp ${Math.round(netto).toLocaleString('id-ID')}
                    </td>
                </tr>
            `;
            rowIndex++;
        });
    });

    tbody.innerHTML = html;

    // Akumulasi potongan retur global dari GRN yang dicentang jika ada
    let autoPotonganGlobal = 0;
    unbilledGrnData.forEach(grn => {
        if (selectedGrnIds.has(grn.terima_id)) {
            autoPotonganGlobal += parseFloat(grn.potongan_global || 0);
        }
    });
    const returInput = document.getElementById('potonganReturInput');
    if (returInput && !returInput.dataset.manualEdited) {
        returInput.value = Math.round(autoPotonganGlobal);
    }

    recalculateTotals();
}

/**
 * Hitung seluruh nilai akumulasi tagihan secara otomatis
 */
function recalculateTotals() {
    let totalBruto = 0;
    let totalDiskon = 0;
    let totalPotonganItem = 0;
    let totalPpn = 0;
    let totalNetto = 0;

    document.querySelectorAll('.item-calc-row').forEach(row => {
        const qty = parseDecimal(row.querySelector('.input-qty')?.value);
        const harga = parseDecimal(row.querySelector('.input-harga')?.value);
        const diskon = parseDecimal(row.querySelector('.input-diskon-nominal')?.value);
        const potongan = parseDecimal(row.querySelector('.input-potongan-item')?.value);
        const ppn = parseDecimal(row.querySelector('.input-ppn')?.value);
        const ppnTipe = row.querySelector('.input-ppn-tipe')?.value || 'NON_PPN';

        const subtotal = qty * harga;
        const nilaiBersih = Math.max(0, subtotal - diskon - potongan);

        let netto = nilaiBersih;
        if (ppnTipe === 'PPN_INCLUDE') {
            netto = nilaiBersih; // Harga sudah include PPN
        } else if (ppnTipe === 'PPN_11') {
            netto = nilaiBersih + ppn; // Exclude PPN
        } else {
            netto = nilaiBersih;
        }

        totalBruto += subtotal;
        totalDiskon += diskon;
        totalPotonganItem += potongan;
        totalPpn += ppn;
        totalNetto += netto;

        const cellNetto = row.querySelector('.cell-netto');
        if (cellNetto) {
            cellNetto.textContent = 'Rp ' + Math.round(netto).toLocaleString('id-ID');
        }
    });

    const potonganRetur = parseFloat(document.getElementById('potonganReturInput')?.value || 0);
    const biayaOngkir = parseFloat(document.getElementById('biayaOngkirInput')?.value || 0);
    const grandTotal = Math.max(0, (totalNetto - potonganRetur) + biayaOngkir);

    // Update elemen sticky panel ringkasan
    const elBruto = document.getElementById('summaryTotalBruto');
    const elDiskon = document.getElementById('summaryTotalDiskon');
    const rowPotonganItem = document.getElementById('rowSummaryPotonganItem');
    const elPotonganItem = document.getElementById('summaryTotalPotonganItem');
    const elPpn = document.getElementById('summaryTotalPpn');
    const elRetur = document.getElementById('summaryPotonganRetur');
    const rowOngkir = document.getElementById('rowSummaryOngkir');
    const elOngkir = document.getElementById('summaryBiayaOngkir');
    const elGrand = document.getElementById('summaryGrandTotal');

    if (elBruto) elBruto.textContent = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (elDiskon) elDiskon.textContent = totalDiskon > 0 ? '- Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : 'Rp 0';
    
    if (rowPotonganItem && elPotonganItem) {
        if (totalPotonganItem > 0) {
            rowPotonganItem.style.display = 'flex';
            elPotonganItem.textContent = '- Rp ' + Math.round(totalPotonganItem).toLocaleString('id-ID');
        } else {
            rowPotonganItem.style.display = 'none';
        }
    }

    if (elPpn) elPpn.textContent = totalPpn > 0 ? '+ Rp ' + Math.round(totalPpn).toLocaleString('id-ID') : 'Rp 0';
    if (elRetur) elRetur.textContent = potonganRetur > 0 ? '- Rp ' + Math.round(potonganRetur).toLocaleString('id-ID') : 'Rp 0';
    
    if (rowOngkir && elOngkir) {
        if (biayaOngkir > 0) {
            rowOngkir.style.display = 'flex';
            elOngkir.textContent = '+ Rp ' + Math.round(biayaOngkir).toLocaleString('id-ID');
        } else {
            rowOngkir.style.display = 'none';
        }
    }

    if (elGrand) elGrand.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
}
