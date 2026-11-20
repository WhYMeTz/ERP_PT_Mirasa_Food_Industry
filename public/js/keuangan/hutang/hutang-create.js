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

    // Hitung ulang kalkulasi saat ada perubahan input pada tabel
    document.getElementById('itemsTableBody')?.addEventListener('input', function (e) {
        const target = e.target;
        if (target.matches('.calc-trigger')) {
            recalculateTotals();
        }
    });

    // Listener saat checkbox include PPN diubah pada baris
    document.getElementById('itemsTableBody')?.addEventListener('change', function (e) {
        const target = e.target;
        if (target.matches('.item-include-tax')) {
            recalculateTotals();
        }
    });

    const diskonGlobalEl = document.getElementById('global_diskon_persen');
    if (diskonGlobalEl) {
        diskonGlobalEl.addEventListener('input', function () {
            this.dataset.manualEdited = 'true';
            recalculateTotals();
        });
    }

    const potonganGlobalEl = document.getElementById('global_potongan_nominal');
    if (potonganGlobalEl) {
        potonganGlobalEl.addEventListener('input', function () {
            this.dataset.manualEdited = 'true';
            recalculateTotals();
        });
    }

    const returEl = document.getElementById('potonganReturInput');
    if (returEl) {
        returEl.addEventListener('input', function () {
            this.dataset.manualEdited = 'true';
            recalculateTotals();
        });
    }

    const tonaseEl = document.getElementById('tonaseKgInput');
    if (tonaseEl) {
        tonaseEl.addEventListener('input', function () {
            this.dataset.manualEdited = 'true';
            calculateOngkirFromTonase();
        });
    }

    const tarifOngkirEl = document.getElementById('ongkirTarifPerKgInput');
    if (tarifOngkirEl) {
        tarifOngkirEl.addEventListener('input', calculateOngkirFromTonase);
    }

    const dpEl = document.getElementById('uangMukaNominalInput');
    if (dpEl) {
        dpEl.addEventListener('input', recalculateTotals);
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

    const placeholderBox = document.getElementById('supplierPlaceholderBox');
    if (placeholderBox) placeholderBox.style.display = 'none';
    const activeCard = document.getElementById('supplierActiveCard');
    if (activeCard) activeCard.style.display = 'flex';

    // Highlight matching quick pill badge (if any)
    syncActivePillBadge(id);

    closeSupplierModal();

    // Reset manual edit markers agar data PO rekanan baru otomatis mengisi form
    const diskonEl = document.getElementById('global_diskon_persen');
    if (diskonEl) delete diskonEl.dataset.manualEdited;
    const potEl = document.getElementById('global_potongan_nominal');
    if (potEl) delete potEl.dataset.manualEdited;

    // Reset selection & fetch GRNs
    selectedGrnIds.clear();
    loadUnbilledGrn(id);
};

window.handleSupplierCardSelect = function (el) {
    if (!el) return;
    const id = parseInt(el.getAttribute('data-supplier-id'), 10);
    const name = el.getAttribute('data-supplier-nm') || '';
    const code = el.getAttribute('data-supplier-cd') || '';
    const grnCnt = parseInt(el.getAttribute('data-grn-count') || '0', 10);
    const contact = el.getAttribute('data-contact') || '';
    const address = el.getAttribute('data-address') || '';
    const topHari = parseInt(el.getAttribute('data-top') || '14', 10);
    selectSupplierFromModal(id, name, code, grnCnt, contact, address, topHari);
};

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modalSupplierPicker');
        if (modal && modal.style.display !== 'none') {
            closeSupplierModal();
        }
    }
});

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
        renderReturCreditNotes([]);
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

                // Render Dokumen Retur Pembelian (Credit Note) jika ada
                if (res.returs) {
                    renderReturCreditNotes(res.returs);
                } else {
                    renderReturCreditNotes([]);
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
   SKEMA PAJAK PPN & TAX CALCULATION (IDENTIK PO PEMBELIAN)
   ======================================================== */

window.onHeaderTaxInclusiveToggle = function (el) {
    const isChecked = el ? el.checked : false;
    const badge = document.getElementById('badgeTaxMode');
    if (badge) {
        badge.textContent = isChecked ? 'Gross' : 'Net';
        badge.style.background = isChecked ? '#dcfce7' : '#e2e8f0';
        badge.style.color = isChecked ? '#15803d' : '#475569';
    }

    // Sinkronisasi ke seluruh checkbox baris
    document.querySelectorAll('.item-include-tax').forEach(cb => {
        cb.checked = isChecked;
    });

    document.querySelectorAll('.item-calc-row').forEach(row => {
        updateRowPpnState(row, false);
    });
    recalculateTotals();
};

window.onHeaderPajakChange = function (selectEl) {
    recalculateTotals();
};
window.applyGlobalTaxToAllRows = window.onHeaderPajakChange;

function rebuildItemsTable() {
    const tbody = document.getElementById('itemsTableBody');
    if (!tbody) return;

    if (selectedGrnIds.size === 0) {
        tbody.innerHTML = `
            <tr id="emptyItemsRow">
                <td colspan="6" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.825rem;">
                    Centang salah satu atau beberapa Surat Jalan / GRN di atas untuk memuat rincian tagihan.
                </td>
            </tr>
        `;
        const tonaseInput = document.getElementById('tonaseKgInput');
        if (tonaseInput && !tonaseInput.dataset.manualEdited) {
            tonaseInput.value = 0;
        }
        calculateOngkirFromTonase();
        recalculateTotals();
        return;
    }

    // Cek pewarisan status & komersial dari PO (Pajak PPN/PPh, Diskon, Potongan Mutu, TOP)
    let anyPoInclude = false;
    let anyPoExplicit = false;
    let autoDiskonPersen = 0;
    let autoPotonganMutu = 0;
    let autoPajakId = null;
    let hasPoData = false;
    let poTopHari = null;

    unbilledGrnData.forEach(grn => {
        if (selectedGrnIds.has(grn.terima_id)) {
            if (grn.has_po || grn.po_no || (grn.details && grn.details.some(d => d.po_no))) {
                hasPoData = true;
            }

            if (typeof grn.po_is_harga_termasuk_pajak !== 'undefined') {
                anyPoExplicit = true;
                if (grn.po_is_harga_termasuk_pajak) anyPoInclude = true;
            } else if (grn.is_harga_termasuk_pajak) {
                anyPoExplicit = true;
                anyPoInclude = true;
            }

            if (grn.po_pajak_id && !autoPajakId) {
                autoPajakId = String(grn.po_pajak_id);
            }

            if (parseFloat(grn.po_diskon_persen || 0) > autoDiskonPersen) {
                autoDiskonPersen = parseFloat(grn.po_diskon_persen);
            }

            if (parseFloat(grn.po_potongan_nominal || 0) > 0) {
                autoPotonganMutu += parseFloat(grn.po_potongan_nominal);
            }

            if (grn.po_top_hari && !poTopHari) {
                poTopHari = grn.po_top_hari;
            }

            (grn.details || []).forEach(dtl => {
                if (parseFloat(dtl.diskon_persen || 0) > autoDiskonPersen) {
                    autoDiskonPersen = parseFloat(dtl.diskon_persen);
                }
                if (parseFloat(dtl.potongan_nominal || 0) > 0 && !(parseFloat(grn.po_potongan_nominal || 0) > 0)) {
                    autoPotonganMutu += parseFloat(dtl.potongan_nominal);
                }
                if (dtl.pajak_id && !autoPajakId) {
                    autoPajakId = String(dtl.pajak_id);
                }
                if (dtl.is_harga_termasuk_pajak) {
                    anyPoExplicit = true;
                    anyPoInclude = true;
                }
            });
        }
    });

    // 1. Sinkronisasi Pajak (PPN / PPh / Non PPN) dari PO
    const globalPajakSelect = document.getElementById('global_pajak_id');
    if (globalPajakSelect && hasPoData) {
        if (autoPajakId) {
            globalPajakSelect.value = autoPajakId;
        } else {
            globalPajakSelect.value = 'NON_PPN';
        }
        if (typeof onHeaderPajakChange === 'function') {
            onHeaderPajakChange(globalPajakSelect);
        }
    }

    // 2. Sinkronisasi Status Harga Termasuk Pajak (Include vs Net) dari PO
    const incCheckbox = document.getElementById('is_harga_termasuk_pajak');
    if (incCheckbox && hasPoData && anyPoExplicit) {
        incCheckbox.checked = anyPoInclude;
        if (typeof onHeaderTaxInclusiveToggle === 'function') {
            onHeaderTaxInclusiveToggle(incCheckbox);
        }
    }

    // 3. Auto-isi Diskon Global jika berasal dari PO
    const diskonEl = document.getElementById('global_diskon_persen');
    if (diskonEl && hasPoData && !diskonEl.dataset.manualEdited) {
        diskonEl.value = autoDiskonPersen > 0 ? autoDiskonPersen : 0;
    }

    // 4. Auto-isi Potongan Mutu jika berasal dari PO / QC
    const potEl = document.getElementById('global_potongan_nominal');
    if (potEl && hasPoData && !potEl.dataset.manualEdited) {
        potEl.value = autoPotonganMutu > 0 ? Math.round(autoPotonganMutu) : 0;
    }

    // 5. Sinkronisasi TOP Hari jika ada di PO
    if (poTopHari && parseInt(poTopHari, 10) > 0) {
        currentSupplierTopHari = parseInt(poTopHari, 10);
        const topInput = document.getElementById('topHariInput');
        if (topInput) topInput.value = currentSupplierTopHari;
        if (typeof updateTempoDateFromTagihan === 'function') {
            updateTempoDateFromTagihan(false);
        }
    }

    let html = '';
    let rowIndex = 0;
    let totalNettoTonase = 0;
    let satuanSet = new Set();

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

            // Akumulasi kuantitas fisik
            const satStr = (satuan || 'KG').trim();
            satuanSet.add(satStr);
            totalNettoTonase += qty;

            const harga = parseFloat(
                dtl.harga_satuan || 
                dtl.harga_nominal || 
                (dtl.po_detail ? dtl.po_detail.harga_nominal : 0) || 
                (dtl.barang ? dtl.barang.harga_beli_standar : 0) || 
                0
            );

            const isItemInclude = typeof dtl.is_harga_termasuk_pajak !== 'undefined'
                ? Boolean(dtl.is_harga_termasuk_pajak)
                : Boolean(anyPoInclude || document.getElementById('is_harga_termasuk_pajak')?.checked);

            const poBadgeHtml = dtl.po_no 
                ? `<span style="background: #e0f2fe; color: #0369a1; font-size: 0.68rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; border: 1px solid #bae6fd; margin-right: 4px;">PO: ${dtl.po_no}</span>`
                : '';

            html += `
                <tr class="item-calc-row item-row" data-index="${rowIndex}" style="border-bottom: 1px solid #f1f5f9;">
                    <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc; width: 35px; padding: 0.65rem 0.25rem;">
                        ${rowIndex + 1}
                    </td>
                    <td style="padding: 0.65rem 0.75rem;">
                        <input type="hidden" name="items[${rowIndex}][terima_id]" value="${grn.terima_id}">
                        <input type="hidden" name="items[${rowIndex}][terima_dtl_id]" value="${dtl.terimadtl_id || ''}">
                        <input type="hidden" name="items[${rowIndex}][barang_id]" value="${dtl.barang_id || ''}">
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8125rem; display: flex; align-items: center; flex-wrap: wrap; gap: 4px;">
                            ${poBadgeHtml}
                            <span>${barangNm}</span>
                        </div>
                        <input type="text" 
                               name="items[${rowIndex}][deskripsi]" 
                               value="${barangNm}" 
                               class="form-control" 
                               style="font-size: 0.72rem; height: 26px; padding: 2px 6px; margin-top: 3px;">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 85px; text-align: center;">
                        <span style="font-weight: 700; color: #475569; font-size: 0.8rem; background: #f1f5f9; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                            ${satuan}
                        </span>
                        <input type="hidden" name="items[${rowIndex}][satuan]" value="${satuan}">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 130px; text-align: right;">
                        <input type="number" 
                               step="0.0001" 
                               min="0.0001" 
                               name="items[${rowIndex}][qty]" 
                               value="${qty}" 
                               class="form-control calc-trigger input-qty item-qty" 
                               style="font-size: 0.8rem; height: 32px; text-align: right; font-weight: 700;">
                    </td>
                    <td style="padding: 0.65rem 0.5rem; width: 170px; text-align: right;">
                        <input type="number" 
                               step="any" 
                               min="0" 
                               name="items[${rowIndex}][harga_satuan]" 
                               value="${harga}" 
                               class="form-control calc-trigger input-harga item-harga" 
                               style="font-size: 0.8rem; height: 32px; text-align: right; font-weight: 700; color: #0f172a;">
                        <label style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; font-size: 0.7rem; color: #475569; margin-top: 3px; cursor: pointer; user-select: none;" title="Centang jika harga satuan barang ini sudah termasuk PPN (Gross / Include)">
                            <input type="hidden" name="items[${rowIndex}][is_harga_termasuk_pajak]" value="0">
                            <input type="checkbox" 
                                   name="items[${rowIndex}][is_harga_termasuk_pajak]" 
                                   value="1" 
                                   class="item-include-tax calc-trigger" 
                                   ${isItemInclude ? 'checked' : ''} 
                                   style="accent-color: #059669; width: 12px; height: 12px; cursor: pointer;">
                            <span style="font-weight: 600;">Termasuk (Inc)</span>
                        </label>
                        <input type="hidden" name="items[${rowIndex}][ppn_nominal]" class="input-ppn" value="0">
                        <input type="hidden" name="items[${rowIndex}][dpp_nominal]" class="input-dpp" value="0">
                        <input type="hidden" name="items[${rowIndex}][diskon_nominal]" class="input-diskon-nominal" value="0">
                        <input type="hidden" name="items[${rowIndex}][diskon_persen]" class="input-diskon-persen" value="${dtl.diskon_persen || ''}">
                        <input type="hidden" name="items[${rowIndex}][potongan_nominal]" class="input-potongan-item" value="0">
                        <input type="hidden" name="items[${rowIndex}][pajak_id]" class="input-pajak-id" value="${dtl.pajak_id || ''}">
                        <input type="hidden" name="items[${rowIndex}][ppn_tipe]" class="input-ppn-tipe" value="${dtl.ppn_tipe || ''}">
                    </td>
                    <td style="padding: 0.65rem 0.75rem; width: 160px; text-align: right; font-weight: 800; color: #0284c7; font-size: 0.85rem; font-family: monospace;" class="cell-netto row-subtotal">
                        Rp 0
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

    // Deteksi satuan utama
    let detectedSatuan = 'KG';
    if (satuanSet.size === 1) {
        detectedSatuan = Array.from(satuanSet)[0];
    } else if (satuanSet.has('KG') || satuanSet.has('Kilogram') || satuanSet.has('kg')) {
        detectedSatuan = 'KG';
    } else if (satuanSet.size > 0) {
        detectedSatuan = Array.from(satuanSet)[0];
    }

    const spanTonase = document.getElementById('spanSatuanTonase');
    const badgeTonase = document.getElementById('badgeSatuanTonase');
    const spanTarif = document.getElementById('spanSatuanTarif');
    const badgeTarif = document.getElementById('badgeSatuanTarif');
    if (spanTonase) spanTonase.textContent = detectedSatuan;
    if (badgeTonase) badgeTonase.textContent = detectedSatuan;
    if (spanTarif) spanTarif.textContent = detectedSatuan;
    if (badgeTarif) badgeTarif.textContent = '/ ' + detectedSatuan;

    const tonaseInput = document.getElementById('tonaseKgInput');
    if (tonaseInput && !tonaseInput.dataset.manualEdited) {
        tonaseInput.value = totalNettoTonase > 0 ? parseFloat(totalNettoTonase.toFixed(2)) : 0;
    }

    calculateOngkirFromTonase();
    recalculateTotals();
}

/**
 * Toggle Mode Ongkir (Hitung dari Tonase vs Input Manual Flat)
 */
window.toggleOngkirMode = function (mode) {
    const tonaseBox = document.getElementById('ongkirTonaseBox');
    if (tonaseBox) {
        tonaseBox.style.display = (mode === 'tonase') ? 'block' : 'none';
    }
    if (mode === 'tonase') {
        calculateOngkirFromTonase();
    }
};

/**
 * Kalkulasi Biaya Ongkos Angkut / Bongkar Muat dari Tonase / Kuantitas x Tarif
 */
window.calculateOngkirFromTonase = function () {
    const tonase = parseDecimal(document.getElementById('tonaseKgInput')?.value);
    const tarif = parseDecimal(document.getElementById('ongkirTarifPerKgInput')?.value);
    const totalOngkir = Math.round(tonase * tarif);

    const isTonaseMode = document.getElementById('ongkirModeTonase')?.checked ?? true;
    if (isTonaseMode) {
        const ongkirInput = document.getElementById('biayaOngkirInput');
        if (ongkirInput) {
            ongkirInput.value = totalOngkir > 0 ? totalOngkir : 0;
        }
    }

    const detectedSatuan = document.getElementById('badgeSatuanTonase')?.textContent || 'KG';
    const formulaEl = document.getElementById('ongkirFormulaText');
    if (formulaEl) {
        formulaEl.innerHTML = `${tonase.toLocaleString('id-ID')} ${detectedSatuan} &times; Rp ${tarif.toLocaleString('id-ID')} = <strong>Rp ${totalOngkir.toLocaleString('id-ID')}</strong>`;
    }

    recalculateTotals();
};

/**
 * Render Daftar Dokumen Retur Pembelian (Credit Note) yang siap dipotongkan
 */
function renderReturCreditNotes(returs) {
    const card = document.getElementById('cardReturCreditNote');
    const container = document.getElementById('returItemsList');
    const badge = document.getElementById('badgeReturCount');

    if (!card || !container) return;

    if (!returs || returs.length === 0) {
        card.style.display = 'none';
        container.innerHTML = '';
        recalculateTotals();
        return;
    }

    card.style.display = 'block';
    if (badge) badge.textContent = `${returs.length} Dokumen Tersedia`;

    let html = '';
    returs.forEach(ret => {
        const itemInfo = (ret.details && ret.details.length > 0)
            ? `${ret.details[0].retur_qty.toLocaleString('id-ID')} ${ret.details[0].satuan} ${ret.details[0].barang_nm} &bull; Alasan: <em>"${ret.details[0].alasan_reject || ret.alasan_txt || 'Cacat Mutu'}"</em>`
            : (ret.alasan_txt || 'Barang Rusak');

        html += `
            <div style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 6px; padding: 0.5rem 0.75rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; transition: all 0.15s ease;">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem; margin: 0; cursor: pointer; flex: 1;">
                    <input type="checkbox" 
                           name="retur_ids[]" 
                           value="${ret.retur_id}" 
                           data-nominal="${ret.total_nominal}" 
                           onchange="onReturCheckboxChange(this)" 
                           style="margin-top: 3px; width: 16px; height: 16px; accent-color: #d97706; cursor: pointer;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                            <span class="badge" style="background: #fef3c7; color: #92400e; font-family: monospace; font-size: 0.725rem; font-weight: 800; padding: 1px 6px; border-radius: 4px;">
                                ${ret.retur_no}
                            </span>
                            <span style="font-size: 0.725rem; color: #78350f; font-weight: 600;">
                                Tgl: ${ret.retur_tgl}
                            </span>
                        </div>
                        <div style="font-size: 0.725rem; color: #475569; margin-top: 2px;">
                            ${itemInfo}
                        </div>
                    </div>
                </label>
                <div style="text-align: right; flex-shrink: 0;">
                    <strong style="color: #dc2626; font-family: monospace; font-size: 0.85rem;">
                        - Rp ${Math.round(ret.total_nominal).toLocaleString('id-ID')}
                    </strong>
                    <div style="font-size: 0.65rem; color: #92400e; font-weight: 700;">
                        Credit Note
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    recalculateTotals();
}

window.onReturCheckboxChange = function () {
    recalculateTotals();
};

window.toggleManualReturBox = function () {
    const box = document.getElementById('boxManualRetur');
    if (box) {
        const isHidden = (box.style.display === 'none' || !box.style.display);
        box.style.display = isHidden ? 'flex' : 'none';
        if (isHidden) {
            document.getElementById('potonganReturInput')?.focus();
        }
    }
};

/**
 * Hitung seluruh nilai akumulasi tagihan secara otomatis (Identik Logika PO Workstation)
 */
function recalculateTotals() {
    let totalQty = 0;
    let totalBruto = 0;

    const rows = document.querySelectorAll('.item-calc-row');
    rows.forEach(row => {
        const qty = parseDecimal(row.querySelector('.input-qty')?.value);
        const harga = parseDecimal(row.querySelector('.input-harga')?.value);
        totalQty += qty;
        totalBruto += (qty * harga);
    });

    // Kontrol Finansial Global Ringkasan (Identik PO)
    const diskonPersen = Math.min(100, Math.max(0, parseFloat(document.getElementById('global_diskon_persen')?.value) || 0));
    const potonganNominal = Math.max(0, parseFloat(document.getElementById('global_potongan_nominal')?.value) || 0);

    const globalPajakSelect = document.getElementById('global_pajak_id');
    const opt = globalPajakSelect ? globalPajakSelect.options[globalPajakSelect.selectedIndex] : null;
    const tarif = opt ? (parseFloat(opt.dataset.tarif) || 0) : 0;
    const sifat = opt ? (opt.dataset.sifat || 'TAMBAH') : 'TAMBAH';
    const isHeaderInc = document.getElementById('is_harga_termasuk_pajak')?.checked || false;

    const totalDiskon = totalBruto * (diskonPersen / 100);
    const totalPotongan = potonganNominal;
    const sumGrossAfterDiskon = Math.max(0, totalBruto - totalDiskon);

    let totalDpp = 0;
    let totalPpn = 0;
    let totalNetto = 0;

    rows.forEach(row => {
        const qty = parseDecimal(row.querySelector('.input-qty')?.value);
        const harga = parseDecimal(row.querySelector('.input-harga')?.value);
        const rowDiskonUnit = harga * (diskonPersen / 100);
        const rowHargaNetto = Math.max(0, harga - rowDiskonUnit);

        let rowPotongan = 0;
        if (totalPotongan > 0 && sumGrossAfterDiskon > 0) {
            const rowGross = qty * rowHargaNetto;
            rowPotongan = (rowGross / sumGrossAfterDiskon) * totalPotongan;
        }

        const grossRowTotal = Math.max(0, (qty * rowHargaNetto) - rowPotongan);

        const rowIncCheck = row.querySelector('.item-include-tax');
        const rowIsInc = rowIncCheck ? rowIncCheck.checked : isHeaderInc;

        let rowDpp = 0;
        let rowPpn = 0;
        let rowNetto = 0;

        if (tarif <= 0 || opt?.value === 'NON_PPN') {
            rowDpp = grossRowTotal;
            rowPpn = 0;
            rowNetto = grossRowTotal;
        } else if (rowIsInc && sifat !== 'POTONG') {
            rowDpp = Math.round((grossRowTotal / (1 + (tarif / 100))) * 100) / 100;
            rowPpn = Math.round(grossRowTotal - rowDpp);
            rowNetto = grossRowTotal;
        } else {
            rowDpp = grossRowTotal;
            rowPpn = Math.round(rowDpp * (tarif / 100));
            rowNetto = (sifat === 'POTONG') ? Math.max(0, rowDpp - rowPpn) : (rowDpp + rowPpn);
        }

        const inputDpp = row.querySelector('.input-dpp');
        if (inputDpp) inputDpp.value = rowDpp;
        const inputPpn = row.querySelector('.input-ppn');
        if (inputPpn) inputPpn.value = rowPpn;
        const inputDiskon = row.querySelector('.input-diskon-nominal');
        if (inputDiskon) inputDiskon.value = Math.round(qty * rowDiskonUnit);
        const inputDiskonPersen = row.querySelector('.input-diskon-persen');
        if (inputDiskonPersen) inputDiskonPersen.value = diskonPersen;
        const inputPot = row.querySelector('.input-potongan-item');
        if (inputPot) inputPot.value = Math.round(rowPotongan);
        const inputPajakId = row.querySelector('.input-pajak-id');
        if (inputPajakId) inputPajakId.value = (opt?.value !== 'NON_PPN' && opt?.value) ? opt.value : '';
        const inputPpnTipe = row.querySelector('.input-ppn-tipe');
        if (inputPpnTipe) inputPpnTipe.value = opt?.value || 'NON_PPN';

        const cellNetto = row.querySelector('.cell-netto');
        if (cellNetto) {
            cellNetto.textContent = 'Rp ' + Math.round(rowNetto).toLocaleString('id-ID');
        }

        totalDpp += rowDpp;
        totalPpn += (sifat === 'POTONG' ? -rowPpn : rowPpn);
        totalNetto += rowNetto;
    });

    // Hitung total potongan retur: akumulasi retur terpilih (Credit Note) + input manual (jika ada)
    let totalReturSelected = 0;
    document.querySelectorAll('input[name="retur_ids[]"]:checked').forEach(cb => {
        totalReturSelected += parseFloat(cb.getAttribute('data-nominal') || 0);
    });

    const manualRetur = parseFloat(document.getElementById('potonganReturInput')?.value || 0);
    const potonganRetur = totalReturSelected + manualRetur;
    const biayaOngkir = parseFloat(document.getElementById('biayaOngkirInput')?.value || 0);
    const grandTotal = Math.max(0, (totalNetto - potonganRetur) + biayaOngkir);

    const uangMuka = parseFloat(document.getElementById('uangMukaNominalInput')?.value || 0);
    const sisaHutang = Math.max(0, grandTotal - uangMuka);

    // Update elemen sticky panel ringkasan
    const elQty = document.getElementById('summaryTotalQty');
    if (elQty) elQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 });

    const elBruto = document.getElementById('summaryTotalBruto');
    const elDiskon = document.getElementById('summaryTotalDiskon');
    const elPotonganItem = document.getElementById('summaryTotalPotonganItem');
    const elDpp = document.getElementById('summaryTotalDpp');
    const elPpn = document.getElementById('summaryTotalPpn');
    const elRetur = document.getElementById('summaryPotonganRetur');
    const rowOngkir = document.getElementById('rowSummaryOngkir');
    const elOngkir = document.getElementById('summaryBiayaOngkir');
    const elGrand = document.getElementById('summaryGrandTotal');
    const rowUangMuka = document.getElementById('rowSummaryUangMuka');
    const elUangMuka = document.getElementById('summaryUangMukaNominal');
    const elSisaHutang = document.getElementById('summarySisaHutang');

    if (elBruto) elBruto.textContent = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (elDiskon) elDiskon.textContent = totalDiskon > 0 ? ('- Rp ' + Math.round(totalDiskon).toLocaleString('id-ID')) : 'Rp 0';
    if (elPotonganItem) elPotonganItem.textContent = totalPotongan > 0 ? ('- Rp ' + Math.round(totalPotongan).toLocaleString('id-ID')) : '- Rp 0';
    if (elDpp) elDpp.textContent = 'Rp ' + Math.round(totalDpp).toLocaleString('id-ID');

    if (elPpn) {
        if (tarif <= 0 || opt?.value === 'NON_PPN' || totalPpn === 0) {
            elPpn.textContent = 'Rp 0';
        } else {
            elPpn.textContent = (sifat === 'POTONG' ? '- Rp ' : '+ Rp ') + Math.round(Math.abs(totalPpn)).toLocaleString('id-ID');
        }
    }

    const rowRetur = document.getElementById('rowSummaryRetur');
    if (rowRetur && elRetur) {
        if (potonganRetur > 0) {
            rowRetur.style.display = 'flex';
            elRetur.textContent = '- Rp ' + Math.round(potonganRetur).toLocaleString('id-ID');
        } else {
            rowRetur.style.display = 'none';
        }
    } else if (elRetur) {
        elRetur.textContent = potonganRetur > 0 ? '- Rp ' + Math.round(potonganRetur).toLocaleString('id-ID') : 'Rp 0';
    }
    
    if (rowOngkir && elOngkir) {
        if (biayaOngkir > 0) {
            rowOngkir.style.display = 'flex';
            elOngkir.textContent = '+ Rp ' + Math.round(biayaOngkir).toLocaleString('id-ID');
        } else {
            rowOngkir.style.display = 'none';
        }
    }

    if (elGrand) elGrand.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    // Live KPI Widget Header (Identik PO Pembelian)
    const elTopGrand = document.getElementById('topLiveGrandTotal');
    if (elTopGrand) {
        elTopGrand.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    }
    const elTopItems = document.getElementById('topLiveItemCount');
    if (elTopItems) {
        const rowCount = document.querySelectorAll('.item-calc-row').length;
        elTopItems.textContent = rowCount + ' Item';
    }

    if (rowUangMuka && elUangMuka) {
        if (uangMuka > 0) {
            rowUangMuka.style.display = 'flex';
            elUangMuka.textContent = '- Rp ' + Math.round(uangMuka).toLocaleString('id-ID');
        } else {
            rowUangMuka.style.display = 'none';
        }
    }

    if (elSisaHutang) {
        elSisaHutang.textContent = 'Rp ' + Math.round(sisaHutang).toLocaleString('id-ID');
    }
}
