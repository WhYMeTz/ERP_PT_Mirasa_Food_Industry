/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER CREATE JAVASCRIPT
 * =========================================================================
 */

let activeModalSupplierTab = 'ALL';
let highlightedSupIndex = -1;
let modalCurrentPage = 1;
const modalPerPage = 8;
let activeBarangCategory = 'ALL';
let currentTableSupSearch = '';
let currentPoMode = 'single';
let rowIndex = 1;

function getSuppliersData() {
    return window.suppliersData || [];
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

function getCategoryBadge(category, categoryName) {
    let catBg = '#f1f5f9';
    let catColor = '#334155';
    let label = categoryName || 'Lainnya';

    if (category === 'RAW') {
        catBg = '#dcfce7';
        catColor = '#166534';
        label = 'Bahan Baku';
    } else if (category === 'BUMBU') {
        catBg = '#fef3c7';
        catColor = '#92400e';
        label = 'Bumbu & Penolong';
    } else if (category === 'KEMASAN') {
        catBg = '#f3e8ff';
        catColor = '#6b21a8';
        label = 'Kemasan & Karton';
    } else if (category === 'SPAREPART') {
        catBg = '#ffedd5';
        catColor = '#9a3412';
        label = 'Suku Cadang';
    } else if (category === 'UMUM') {
        catBg = '#f1f5f9';
        catColor = '#334155';
        label = 'Umum & Jasa';
    }

    return {
        bg: catBg,
        color: catColor,
        label: label,
        html: `<span class="badge" style="background:${catBg}; color:${catColor}; font-size:0.725rem; font-weight:600; padding: 0.15rem 0.45rem; border-radius: 4px; border: 1px solid rgba(0,0,0,0.06); white-space:nowrap;">${escapeHtml(label)}</span>`
    };
}

function changeSupplierModalPage(delta) {
    modalCurrentPage += delta;
    renderSupplierModalTable();
}

function openSupplierModal() {
    const supDropdownList = document.getElementById('supplier_dropdown_list');
    const supSearchInput = document.getElementById('supplier_search_input');
    const modalSearchInput = document.getElementById('modal_supplier_search');

    if (supDropdownList) supDropdownList.style.display = 'none';
    if (typeof openModal === 'function') {
        openModal('modalPilihSupplier');
    }
    const currentTyped = supSearchInput ? supSearchInput.value.trim() : '';
    if (modalSearchInput) modalSearchInput.value = currentTyped;
    modalCurrentPage = 1;
    renderSupplierModalTable();
    setTimeout(() => {
        if (modalSearchInput) {
            modalSearchInput.focus();
            modalSearchInput.select();
        }
    }, 100);
}

function closeSupplierModal() {
    if (typeof closeModal === 'function') {
        closeModal('modalPilihSupplier');
    }
}

function filterSupplierModalTab(tab, btn) {
    activeModalSupplierTab = tab;
    modalCurrentPage = 1;
    document.querySelectorAll('.sup-modal-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderSupplierModalTable();
}

function renderSupplierModalTable() {
    const modalSearchInput = document.getElementById('modal_supplier_search');
    const modalTbody = document.getElementById('supplier_modal_tbody');
    const modalCountText = document.getElementById('modal_supplier_count_text');
    if (!modalSearchInput || !modalTbody) return;

    const query = modalSearchInput.value.trim().toLowerCase();
    const suppliers = getSuppliersData();

    const filtered = suppliers.filter(s => {
        const matchesTab = (activeModalSupplierTab === 'ALL' || s.category === activeModalSupplierTab);
        const matchesText = !query || 
            (s.name && s.name.toLowerCase().includes(query)) || 
            (s.code && s.code.toLowerCase().includes(query)) || 
            (s.contact && s.contact.toLowerCase().includes(query)) || 
            (s.address && s.address.toLowerCase().includes(query)) ||
            (s.categoryName && s.categoryName.toLowerCase().includes(query));
        return matchesTab && matchesText;
    });

    const totalItems = filtered.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / modalPerPage));
    if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;
    if (modalCurrentPage < 1) modalCurrentPage = 1;

    const startIdx = (modalCurrentPage - 1) * modalPerPage;
    const endIdx = Math.min(startIdx + modalPerPage, totalItems);
    const pageItems = filtered.slice(startIdx, endIdx);

    if (modalCountText) {
        modalCountText.innerText = totalItems > 0 
            ? `Menampilkan ${startIdx + 1}–${endIdx} dari ${totalItems} supplier`
            : `Menampilkan 0 supplier`;
    }

    const paginationDiv = document.getElementById('modal_supplier_pagination');
    if (paginationDiv) {
        paginationDiv.style.display = totalPages > 1 ? 'flex' : 'none';
        const pageInfo = document.getElementById('modal_page_info');
        if (pageInfo) pageInfo.innerText = `Hal ${modalCurrentPage} dari ${totalPages}`;
        const prevBtn = document.getElementById('modal_prev_btn');
        const nextBtn = document.getElementById('modal_next_btn');
        if (prevBtn) {
            prevBtn.disabled = (modalCurrentPage <= 1);
            prevBtn.style.opacity = (modalCurrentPage <= 1) ? '0.4' : '1';
            prevBtn.style.cursor = (modalCurrentPage <= 1) ? 'not-allowed' : 'pointer';
        }
        if (nextBtn) {
            nextBtn.disabled = (modalCurrentPage >= totalPages);
            nextBtn.style.opacity = (modalCurrentPage >= totalPages) ? '0.4' : '1';
            nextBtn.style.cursor = (modalCurrentPage >= totalPages) ? 'not-allowed' : 'pointer';
        }
    }

    if (totalItems === 0) {
        modalTbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                    Tidak ditemukan data supplier dengan kata kunci "<strong>${escapeHtml(query)}</strong>".
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    pageItems.forEach((s, idx) => {
        const rowNo = startIdx + idx + 1;
        const badge = getCategoryBadge(s.category, s.categoryName);
        html += `
            <tr class="sup-modal-row" onclick="selectSupplier(${s.id}); closeSupplierModal();">
                <td style="text-align: center; color: #64748b; font-size: 0.775rem;">${rowNo}</td>
                <td>
                    <strong style="color: #0284c7; font-family: monospace; font-size: 0.8rem;">${escapeHtml(s.code)}</strong>
                </td>
                <td>
                    <strong style="color: #0f172a; font-size: 0.825rem;">${escapeHtml(s.name)}</strong>
                </td>
                <td>${badge.html}</td>
                <td style="font-size: 0.8rem; color: #334155;">
                    ${s.contact && s.contact !== '-' ? escapeHtml(s.contact) : '-'}
                </td>
                <td style="font-size: 0.8rem; color: #475569;">
                    ${s.address ? escapeHtml(s.address) : '-'}
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; font-weight: 600;" onclick="event.stopPropagation(); selectSupplier(${s.id}); closeSupplierModal();">
                        Pilih
                    </button>
                </td>
            </tr>
        `;
    });

    modalTbody.innerHTML = html;
}

function renderSupplierDropdown(query = '') {
    const supDropdownList = document.getElementById('supplier_dropdown_list');
    if (!supDropdownList) return;

    const q = query.trim().toLowerCase();
    const suppliers = getSuppliersData();

    let filtered = suppliers.filter(s => {
        if (!q) return true;
        return (s.name && s.name.toLowerCase().includes(q)) || 
               (s.code && s.code.toLowerCase().includes(q)) ||
               (s.contact && s.contact.toLowerCase().includes(q)) ||
               (s.address && s.address.toLowerCase().includes(q)) ||
               (s.categoryName && s.categoryName.toLowerCase().includes(q));
    });

    if (filtered.length === 0) {
        supDropdownList.innerHTML = `
            <div style="padding: 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                Tidak ditemukan supplier untuk "<strong>${escapeHtml(query)}</strong>"
            </div>
        `;
        return;
    }

    const displayLimit = 7;
    const visibleItems = filtered.slice(0, displayLimit);

    let html = `
        <div style="padding: 0.4rem 0.85rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; font-size: 0.7rem; color: #64748b; font-weight: 600;">
            <span>${filtered.length} supplier ditemukan</span>
        </div>
    `;

    visibleItems.forEach((s, idx) => {
        const badge = getCategoryBadge(s.category, s.categoryName);
        html += `
            <div class="sup-option-item" data-id="${s.id}" data-idx="${idx}" onclick="selectSupplier(${s.id})">
                <div style="min-width: 0;">
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        ${escapeHtml(s.name)}
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.15rem; font-size: 0.725rem; color: #64748b;">
                        <span style="font-family: monospace; font-weight: 700; color: #0284c7;">${escapeHtml(s.code)}</span>
                        ${s.contact && s.contact !== '-' ? `<span>&bull; Telp: ${escapeHtml(s.contact)}</span>` : ''}
                        ${s.address ? `<span>&bull; ${escapeHtml(s.address)}</span>` : ''}
                    </div>
                </div>
                <div style="flex-shrink: 0; margin-left: 0.5rem;">
                    ${badge.html}
                </div>
            </div>
        `;
    });

    supDropdownList.innerHTML = html;
    highlightedSupIndex = -1;
}

function selectSupplier(supplierId) {
    const suppliers = getSuppliersData();
    const sup = suppliers.find(s => s.id == supplierId);
    if (!sup) return;

    const supHiddenSelect = document.getElementById('supplier_id');
    const selectedSupCard = document.getElementById('selected_supplier_card');
    const supSearchWrapper = document.getElementById('supplier_search_wrapper');
    const supDropdownList = document.getElementById('supplier_dropdown_list');

    if (supHiddenSelect) supHiddenSelect.value = sup.id;

    const badge = getCategoryBadge(sup.category, sup.categoryName);
    const elName = document.getElementById('disp_sup_name');
    const elCode = document.getElementById('disp_sup_code');
    const elCat = document.getElementById('disp_sup_cat_badge');
    const elContact = document.getElementById('disp_sup_contact');

    if (elName) elName.innerText = sup.name;
    if (elCode) elCode.innerText = sup.code;
    if (elCat) elCat.innerHTML = badge.html;
    if (elContact) {
        elContact.innerText = 
            (sup.contact && sup.contact !== '-' ? 'Telp: ' + sup.contact : '') + 
            (sup.address ? (sup.contact && sup.contact !== '-' ? ' | ' : '') + 'Alamat: ' + sup.address : '');
    }

    if (selectedSupCard) selectedSupCard.style.display = 'flex';
    if (supSearchWrapper) supSearchWrapper.style.display = 'none';
    if (supDropdownList) supDropdownList.style.display = 'none';

    if (currentPoMode === 'multi') {
        document.querySelectorAll('.item-supplier').forEach(sel => {
            if (!sel.value) {
                sel.value = sup.id;
            }
        });
    }
    calculateGrandTotal();

    if (sup.category === 'RAW') {
        const bbChip = document.querySelector('.barang-filter-chip[data-category="BB"]');
        if (bbChip) filterBarangCategory('BB', bbChip);
    } else if (sup.category === 'BUMBU') {
        const bumbuChip = document.querySelector('.barang-filter-chip[data-category="BUMBU"]');
        if (bumbuChip) filterBarangCategory('BUMBU', bumbuChip);
    } else if (sup.category === 'KEMASAN') {
        const packChip = document.querySelector('.barang-filter-chip[data-category="PACK"]');
        if (packChip) filterBarangCategory('PACK', packChip);
    }
}

function clearSelectedSupplier() {
    const supHiddenSelect = document.getElementById('supplier_id');
    const selectedSupCard = document.getElementById('selected_supplier_card');
    const supSearchWrapper = document.getElementById('supplier_search_wrapper');
    const supSearchInput = document.getElementById('supplier_search_input');
    const supDropdownList = document.getElementById('supplier_dropdown_list');

    if (supHiddenSelect) supHiddenSelect.value = '';
    if (selectedSupCard) selectedSupCard.style.display = 'none';
    if (supSearchWrapper) supSearchWrapper.style.display = 'block';
    if (supSearchInput) {
        supSearchInput.value = '';
        renderSupplierDropdown('');
        supSearchInput.focus();
    }
    if (supDropdownList) supDropdownList.style.display = 'none';
    calculateGrandTotal();
}

function generateSupplierOptionsHtml(category = 'ALL', selectedId = '', searchQuery = '') {
    const q = (searchQuery || '').trim().toLowerCase();
    const suppliers = getSuppliersData();

    const groups = [
        { key: 'RAW', label: '🌾 BAHAN BAKU / PETANI SINGKONG' },
        { key: 'BUMBU', label: '🧂 BUMBU & BAHAN PENOLONG' },
        { key: 'KEMASAN', label: '📦 KEMASAN & PACKAGING' },
        { key: 'BP', label: '🏭 PENOLONG INDUSTRI' },
        { key: 'OTHER', label: 'LAINNYA' }
    ];

    let html = '<option value="">-- Pilih Supplier Mitra --</option>';
    let totalCount = 0;

    groups.forEach(grp => {
        let isAllowed = false;
        if (category === 'ALL') {
            isAllowed = true;
        } else if (category === 'BB' || category === 'RAW') {
            isAllowed = (grp.key === 'RAW');
        } else if (category === 'BUMBU') {
            isAllowed = (grp.key === 'BUMBU' || grp.key === 'BP');
        } else if (category === 'PACK' || category === 'KEMASAN') {
            isAllowed = (grp.key === 'KEMASAN');
        }

        if (isAllowed) {
            const list = suppliers.filter(s => {
                const matchCat = (s.category === grp.key);
                const matchText = !q || s.name.toLowerCase().includes(q) || s.code.toLowerCase().includes(q);
                return matchCat && matchText;
            });

            if (list.length > 0) {
                totalCount += list.length;
                html += `<optgroup label="${grp.label} (${list.length})">`;
                list.forEach(s => {
                    const isSelected = (s.id == selectedId) ? 'selected' : '';
                    html += `<option value="${s.id}" data-category="${s.category}" ${isSelected}>${escapeHtml(s.name)} (${escapeHtml(s.code)})</option>`;
                });
                html += `</optgroup>`;
            }
        }
    });

    if (totalCount === 0) {
        html = `<option value="">-- Tidak ada supplier yang cocok (${escapeHtml(searchQuery || category)}) --</option>`;
    }

    return { html, totalCount };
}

function renderSupplierSelectOptions(selectElem, category = 'ALL', searchQuery = '') {
    if (!selectElem) return;
    const currentVal = selectElem.value;
    const { html, totalCount } = generateSupplierOptionsHtml(category, currentVal, searchQuery);
    selectElem.innerHTML = html;

    if (currentVal) {
        selectElem.value = currentVal;
    }

    const row = selectElem.closest('tr');
    if (row) {
        const hint = row.querySelector('.row-supplier-hint');
        if (hint) {
            if (category === 'BB' || category === 'RAW') {
                hint.innerHTML = `<span style="color:#15803d; font-weight:600;">Rekanan Bahan Baku (${totalCount})</span>`;
                hint.style.display = 'block';
            } else if (category === 'BUMBU') {
                hint.innerHTML = `<span style="color:#b45309; font-weight:600;">Rekanan Bumbu &amp; Penolong (${totalCount})</span>`;
                hint.style.display = 'block';
            } else if (category === 'PACK') {
                hint.innerHTML = `<span style="color:#7c3aed; font-weight:600;">Rekanan Kemasan (${totalCount})</span>`;
                hint.style.display = 'block';
            } else if (searchQuery) {
                hint.innerHTML = `<span style="color:#0284c7; font-weight:600;">Supplier Terfilter (${totalCount})</span>`;
                hint.style.display = 'block';
            } else {
                hint.style.display = 'none';
            }
        }
    }
}

function filterBarangCategory(category, btn) {
    activeBarangCategory = category;
    document.querySelectorAll('.category-segment-btn, .barang-filter-chip').forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');

    document.querySelectorAll('.item-row').forEach(row => {
        const barangSelect = row.querySelector('.item-barang');
        applyBarangCategoryFilterToSelect(barangSelect, category);

        const supSelect = row.querySelector('.item-supplier');
        if (supSelect) {
            const selectedBarangOpt = barangSelect ? barangSelect.options[barangSelect.selectedIndex] : null;
            const targetCat = (selectedBarangOpt && selectedBarangOpt.value) ? selectedBarangOpt.dataset.category : category;
            renderSupplierSelectOptions(supSelect, targetCat, currentTableSupSearch);
        }
    });

    document.querySelectorAll('.safety-stock-row').forEach(row => {
        const rowCat = row.dataset.category;
        if (category === 'ALL' || rowCat === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function applyBarangCategoryFilterToSelect(selectElem, category) {
    if (!selectElem) return;
    const grpBb = selectElem.querySelector('.grp-bb');
    const grpBumbu = selectElem.querySelector('.grp-bumbu');
    const grpPack = selectElem.querySelector('.grp-pack');

    if (category === 'ALL') {
        if (grpBb) grpBb.style.display = '';
        if (grpBumbu) grpBumbu.style.display = '';
        if (grpPack) grpPack.style.display = '';
    } else if (category === 'BB') {
        if (grpBb) grpBb.style.display = '';
        if (grpBumbu) grpBumbu.style.display = 'none';
        if (grpPack) grpPack.style.display = 'none';
    } else if (category === 'BUMBU') {
        if (grpBb) grpBb.style.display = 'none';
        if (grpBumbu) grpBumbu.style.display = '';
        if (grpPack) grpPack.style.display = 'none';
    } else if (category === 'PACK') {
        if (grpBb) grpBb.style.display = 'none';
        if (grpBumbu) grpBumbu.style.display = 'none';
        if (grpPack) grpPack.style.display = '';
    }
}

function filterTableSuppliersBySearch(query) {
    currentTableSupSearch = query;
    document.querySelectorAll('.item-row').forEach(row => {
        const barangSelect = row.querySelector('.item-barang');
        const selectedBarangOpt = barangSelect ? barangSelect.options[barangSelect.selectedIndex] : null;
        const targetCat = (selectedBarangOpt && selectedBarangOpt.value) ? selectedBarangOpt.dataset.category : activeBarangCategory;

        const supSelect = row.querySelector('.item-supplier');
        if (supSelect) {
            renderSupplierSelectOptions(supSelect, targetCat, query);
        }
    });
}

function clearTableSupSearch() {
    const inp = document.getElementById('table_sup_search_input');
    if (inp) inp.value = '';
    filterTableSuppliersBySearch('');
}

function setPoMode(mode) {
    currentPoMode = mode;
    const btnSingle = document.getElementById('btnModeSingle');
    const btnMulti = document.getElementById('btnModeMulti');
    const modeDesc = document.getElementById('modeDescription');
    const colSuppliers = document.querySelectorAll('.col-supplier');
    const tfootQty = document.getElementById('tfootQtyColspan');
    const supHeaderLabel = document.getElementById('lblSupplierHeader');
    const supHeaderRequired = document.getElementById('reqSupplierHeader');
    const supHeaderSelect = document.getElementById('supplier_id');
    const sideAutoSplitBox = document.getElementById('sideAutoSplitBox');
    const poNoHelp = document.getElementById('po_no_help');
    const tblSupSearchBox = document.getElementById('table_supplier_search_box');

    if (mode === 'multi') {
        if (btnSingle) btnSingle.classList.remove('active');
        if (btnMulti) btnMulti.classList.add('active');
        if (modeDesc) modeDesc.innerHTML = `<span style="color:#0284c7; font-weight:700;">⚡ Konsep 1 Aktif:</span> Anda dapat menentukan supplier mitra berbeda pada setiap baris barang. Sistem akan memecah secara otomatis menjadi beberapa dokumen PO resmi terpisah (1 PO per supplier).`;

        if (poNoHelp) poNoHelp.innerText = 'Prefix / nomor urut dasar untuk pemecahan PO otomatis per-supplier.';
        if (supHeaderRequired) supHeaderRequired.style.display = 'none';
        if (supHeaderLabel) supHeaderLabel.innerHTML = 'Supplier Utama <span style="font-size:0.75rem; color:#64748b; font-weight:normal;">(Opsional / Default Baris)</span>';
        if (supHeaderSelect) supHeaderSelect.removeAttribute('required');

        colSuppliers.forEach(el => el.style.display = '');
        if (tfootQty) tfootQty.setAttribute('colspan', '4');
        if (tblSupSearchBox) tblSupSearchBox.style.display = 'flex';

        document.querySelectorAll('.item-row').forEach(row => {
            const barangSelect = row.querySelector('.item-barang');
            const selectedBarangOpt = barangSelect ? barangSelect.options[barangSelect.selectedIndex] : null;
            const targetCat = (selectedBarangOpt && selectedBarangOpt.value) ? selectedBarangOpt.dataset.category : activeBarangCategory;

            const supSelect = row.querySelector('.item-supplier');
            if (supSelect) {
                supSelect.setAttribute('required', 'required');
                renderSupplierSelectOptions(supSelect, targetCat, currentTableSupSearch);
                const supHiddenSelect = document.getElementById('supplier_id');
                if (!supSelect.value && supHiddenSelect && supHiddenSelect.value) {
                    supSelect.value = supHiddenSelect.value;
                }
            }
        });

        if (sideAutoSplitBox) sideAutoSplitBox.style.display = 'block';
    } else {
        if (btnSingle) btnSingle.classList.add('active');
        if (btnMulti) btnMulti.classList.remove('active');
        if (modeDesc) modeDesc.innerHTML = `Mode standar: Seluruh barang dalam formulir ini dipesan ke 1 supplier utama di bawah (diterbitkan sebagai 1 dokumen PO resmi).`;

        if (poNoHelp) poNoHelp.innerText = 'Nomor urut otomatis sistem pengadaan.';
        if (supHeaderRequired) supHeaderRequired.style.display = 'inline';
        if (supHeaderLabel) supHeaderLabel.innerHTML = 'Supplier Mitra <span style="color:#ef4444;">*</span>';
        if (supHeaderSelect) supHeaderSelect.setAttribute('required', 'required');

        colSuppliers.forEach(el => el.style.display = 'none');
        if (tfootQty) tfootQty.setAttribute('colspan', '3');
        if (tblSupSearchBox) tblSupSearchBox.style.display = 'none';

        document.querySelectorAll('.item-supplier').forEach(sel => {
            sel.removeAttribute('required');
        });

        if (sideAutoSplitBox) sideAutoSplitBox.style.display = 'none';
    }

    calculateGrandTotal();
}

function addRow(focusNew = false) {
    const container = document.getElementById('itemsContainer');
    if (!container) return null;

    const template = document.getElementById('itemRowTemplate');
    let tr;

    if (template) {
        const tempDiv = document.createElement('tbody');
        tempDiv.innerHTML = template.innerHTML
            .replace(/__INDEX__/g, rowIndex)
            .replace(/__NUM__/g, rowIndex + 1)
            .replace(/__SUP_DISPLAY__/g, currentPoMode === 'multi' ? '' : 'display: none;')
            .replace(/__SUP_REQUIRED__/g, currentPoMode === 'multi' ? 'required' : '');
        tr = tempDiv.firstElementChild;
    } else {
        // Fallback row creation
        tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = rowIndex;
        tr.innerHTML = `<td class="row-num">${rowIndex + 1}</td>`;
    }

    container.appendChild(tr);
    rowIndex++;
    updateRowNumbers();
    attachExcelKeyboardEvents(tr);

    const supSelect = tr.querySelector('.item-supplier');
    if (supSelect) {
        renderSupplierSelectOptions(supSelect, activeBarangCategory, currentTableSupSearch);
        const supHiddenSelect = document.getElementById('supplier_id');
        if (currentPoMode === 'multi' && supHiddenSelect && supHiddenSelect.value) {
            supSelect.value = supHiddenSelect.value;
        }
    }

    const select = tr.querySelector('.item-barang');
    applyBarangCategoryFilterToSelect(select, activeBarangCategory);

    if (focusNew && select) {
        select.focus();
    }
    calculateGrandTotal();
    return tr;
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) {
        alert('Minimal harus ada 1 baris item barang dalam Purchase Order.');
        return;
    }
    btn.closest('tr').remove();
    updateRowNumbers();
    calculateGrandTotal();
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.item-row');
    rows.forEach((row, idx) => {
        const num = row.querySelector('.row-num');
        if (num) num.innerText = idx + 1;
    });
    const sideTotal = document.getElementById('sideTotalItems');
    if (sideTotal) sideTotal.innerText = rows.length;
}

function updateRowSatuan(selectElem) {
    const row = selectElem.closest('tr');
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    const satuan = selectedOption ? (selectedOption.dataset.satuan || '-') : '-';
    const defaultHarga = parseFloat(selectedOption ? (selectedOption.dataset.harga || 0) : 0);
    const barangCategory = selectedOption ? (selectedOption.dataset.category || 'ALL') : 'ALL';
    const barangCd = selectedOption ? (selectedOption.dataset.code || '') : '';

    const elSatuan = row.querySelector('.row-satuan');
    if (elSatuan) elSatuan.innerText = satuan;

    const hint = row.querySelector('.row-barang-hint');
    if (hint) {
        if (barangCd) {
            hint.innerHTML = `<span style="font-family: monospace; font-weight: 600; color: #0284c7;">${escapeHtml(barangCd)}</span>`;
            hint.style.display = 'block';
        } else {
            hint.style.display = 'none';
        }
    }

    const hargaInput = row.querySelector('.item-harga');
    if (hargaInput) hargaInput.value = defaultHarga;

    const supSelect = row.querySelector('.item-supplier');
    if (supSelect) {
        renderSupplierSelectOptions(supSelect, barangCategory, currentTableSupSearch);
    }

    calculateGrandTotal();
}

function calculateSubtotal() {
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let totalQty = 0;
    let totalBruto = 0;
    let totalDiskon = 0;
    let totalPotongan = 0;
    let totalDpp = 0;
    let totalPpn = 0;
    let grandTotal = 0;
    const supplierItemCount = {};
    const supHiddenSelect = document.getElementById('supplier_id');

    document.querySelectorAll('.item-row').forEach(row => {
        const qty = Math.max(0, parseFloat(row.querySelector('.item-qty')?.value) || 0);
        const harga = Math.max(0, parseFloat(row.querySelector('.item-harga')?.value) || 0);
        const diskonPersen = Math.min(100, Math.max(0, parseFloat(row.querySelector('.item-diskon')?.value) || 0));
        const potonganNominal = Math.max(0, parseFloat(row.querySelector('.item-potongan')?.value) || 0);
        const ppnSelect = row.querySelector('.item-ppn-tipe');
        const isPpn11 = ppnSelect ? ppnSelect.value === 'PPN_11' : false;

        const diskonUnit = harga * (diskonPersen / 100);
        const hargaNetto = Math.max(0, harga - diskonUnit);
        const rowSubtotalNetto = Math.max(0, (qty * hargaNetto) - potonganNominal);
        const itemPpnNominal = isPpn11 ? Math.round(rowSubtotalNetto * 0.11) : 0;
        const rowSubtotalTagihan = rowSubtotalNetto + itemPpnNominal;

        const rowSubtotalCell = row.querySelector('.row-subtotal');
        if (rowSubtotalCell) {
            rowSubtotalCell.innerText = 'Rp ' + Math.round(rowSubtotalTagihan).toLocaleString('id-ID');
        }

        totalQty += qty;
        totalBruto += (qty * harga);
        totalDiskon += (qty * diskonUnit);
        totalPotongan += potonganNominal;
        totalDpp += rowSubtotalNetto;
        totalPpn += itemPpnNominal;
        grandTotal += rowSubtotalTagihan;

        if (currentPoMode === 'multi') {
            const supSelect = row.querySelector('.item-supplier');
            let supId = supSelect ? supSelect.value : '';
            if (!supId && supHiddenSelect && supHiddenSelect.value) {
                supId = supHiddenSelect.value;
            }
            if (supId) {
                supplierItemCount[supId] = (supplierItemCount[supId] || 0) + 1;
            }
        }
    });

    const elTotalQty = document.getElementById('totalQtyDisplay');
    const elDiskonDisplay = document.getElementById('totalDiskonDisplay');
    const elPotonganDisplay = document.getElementById('totalPotonganDisplay');
    const elPpnDisplay = document.getElementById('totalPpnDisplay');
    const elGrandTotalDisplay = document.getElementById('grandTotalDisplay');

    if (elTotalQty) elTotalQty.innerText = totalQty.toFixed(2);
    if (elDiskonDisplay) elDiskonDisplay.innerText = totalDiskon > 0 ? '-Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : '-';
    if (elPotonganDisplay) elPotonganDisplay.innerText = totalPotongan > 0 ? '-Rp ' + Math.round(totalPotongan).toLocaleString('id-ID') : '-';
    if (elPpnDisplay) elPpnDisplay.innerText = totalPpn > 0 ? '+Rp ' + Math.round(totalPpn).toLocaleString('id-ID') : '-';
    if (elGrandTotalDisplay) elGrandTotalDisplay.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const sideGrandTotal = document.getElementById('sideGrandTotal');
    const sideTotalQty = document.getElementById('sideTotalQty');
    const sideSubtotal = document.getElementById('sideSubtotalBruto');
    const sideDiskon = document.getElementById('sideTotalDiskon');
    const sidePotongan = document.getElementById('sideTotalPotongan');
    const sidePpn = document.getElementById('sideTotalPpn');

    if (sideGrandTotal) sideGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    if (sideTotalQty) sideTotalQty.innerText = totalQty.toFixed(2);
    if (sideSubtotal) sideSubtotal.innerText = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (sideDiskon) sideDiskon.innerText = totalDiskon > 0 ? '-Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : 'Rp 0';
    if (sidePotongan) sidePotongan.innerText = totalPotongan > 0 ? '-Rp ' + Math.round(totalPotongan).toLocaleString('id-ID') : 'Rp 0';
    if (sidePpn) sidePpn.innerText = totalPpn > 0 ? '+Rp ' + Math.round(totalPpn).toLocaleString('id-ID') : 'Rp 0';

    const submitBtnText = document.getElementById('btnSubmitPoText');
    const sidePoCountBadge = document.getElementById('sidePoCountBadge');
    const sidePoSupplierList = document.getElementById('sidePoSupplierList');

    if (currentPoMode === 'multi') {
        const uniqueSupIds = Object.keys(supplierItemCount);
        const splitCount = Math.max(1, uniqueSupIds.length);

        if (sidePoCountBadge) {
            sidePoCountBadge.innerText = `${splitCount} Dokumen PO`;
            sidePoCountBadge.style.background = splitCount > 1 ? '#dcfce7' : '#e0f2fe';
            sidePoCountBadge.style.color = splitCount > 1 ? '#15803d' : '#0284c7';
        }

        if (submitBtnText) {
            submitBtnText.innerText = (splitCount > 1) 
                ? `⚡ Simpan & Pecah Jadi ${splitCount} PO`
                : `Simpan & Terbitkan PO`;
        }

        if (sidePoSupplierList) {
            if (uniqueSupIds.length === 0) {
                sidePoSupplierList.innerHTML = `<span style="color:#94a3b8; font-style:italic;">Pilih supplier pada setiap baris item...</span>`;
            } else {
                const suppliers = getSuppliersData();
                let listHtml = '<ul style="margin: 0; padding-left: 1.15rem; list-style-type: disc;">';
                uniqueSupIds.forEach(id => {
                    const sup = suppliers.find(s => s.id == id);
                    const supName = sup ? sup.name : `Supplier #${id}`;
                    const count = supplierItemCount[id];
                    listHtml += `<li style="margin-bottom: 0.2rem;"><strong>${escapeHtml(supName)}</strong>: ${count} item</li>`;
                });
                listHtml += '</ul>';
                sidePoSupplierList.innerHTML = listHtml;
            }
        }
    } else {
        if (submitBtnText) {
            submitBtnText.innerText = 'Simpan & Terbitkan PO';
        }
    }
}

function toggleSafetyStockDrawer() {
    const drawer = document.getElementById('drawerSafetyStock');
    const chevron = document.getElementById('chevronSafetyStock');
    if (!drawer) return;

    if (drawer.style.display === 'none' || drawer.style.display === '') {
        drawer.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
    } else {
        drawer.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
}

function addBelowMinimumItem(barangId, barangNm, satuanNm, defaultHarga, deficitQty) {
    const rows = document.querySelectorAll('.item-row');
    let targetRow = null;

    if (rows.length === 1 && !rows[0].querySelector('.item-barang').value) {
        targetRow = rows[0];
    } else {
        for (let r of rows) {
            if (r.querySelector('.item-barang').value == barangId) {
                const qtyInput = r.querySelector('.item-qty');
                qtyInput.value = parseFloat(qtyInput.value) + deficitQty;
                calculateSubtotal();
                qtyInput.focus();
                return;
            }
        }
        targetRow = addRow(false);
    }

    if (!targetRow) return;
    const select = targetRow.querySelector('.item-barang');
    select.value = barangId;
    targetRow.querySelector('.row-satuan').innerText = satuanNm;
    targetRow.querySelector('.item-qty').value = deficitQty;
    targetRow.querySelector('.item-harga').value = defaultHarga;

    const supHiddenSelect = document.getElementById('supplier_id');
    if (currentPoMode === 'multi' && supHiddenSelect && supHiddenSelect.value) {
        const rowSup = targetRow.querySelector('.item-supplier');
        if (rowSup && !rowSup.value) rowSup.value = supHiddenSelect.value;
    }

    updateRowSatuan(select);
    calculateSubtotal();
}

function addAllBelowMinimumItems() {
    document.querySelectorAll('.safety-stock-row').forEach(row => {
        if (row.style.display !== 'none') {
            const btn = row.querySelector('.btn-add-safety');
            if (btn) btn.click();
        }
    });
}

function attachExcelKeyboardEvents(rowElement) {
    const inputs = rowElement.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const allRows = Array.from(document.querySelectorAll('.item-row'));
                const currentRowIdx = allRows.indexOf(rowElement);

                if (input.classList.contains('item-harga') || input.classList.contains('item-qty') || input.classList.contains('item-diskon') || input.classList.contains('item-potongan')) {
                    if (currentRowIdx === allRows.length - 1) {
                        addRow(true);
                    } else {
                        const nextRow = allRows[currentRowIdx + 1];
                        const target = nextRow.querySelector('.' + input.classList[1]);
                        if (target) target.focus();
                    }
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const supSearchInput = document.getElementById('supplier_search_input');
    const supSearchWrapper = document.getElementById('supplier_search_wrapper');
    const supDropdownList = document.getElementById('supplier_dropdown_list');

    if (supSearchInput) {
        supSearchInput.addEventListener('input', function() {
            renderSupplierDropdown(this.value);
            if (supDropdownList) supDropdownList.style.display = 'block';
        });

        supSearchInput.addEventListener('focus', function() {
            renderSupplierDropdown(this.value);
            if (supDropdownList) supDropdownList.style.display = 'block';
        });

        supSearchInput.addEventListener('keydown', function(e) {
            if (!supDropdownList) return;
            const items = supDropdownList.querySelectorAll('.sup-option-item');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightedSupIndex = (highlightedSupIndex + 1) % items.length;
                updateHighlightedSupItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightedSupIndex = (highlightedSupIndex - 1 + items.length) % items.length;
                updateHighlightedSupItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (highlightedSupIndex >= 0 && items[highlightedSupIndex]) {
                    const id = items[highlightedSupIndex].dataset.id;
                    selectSupplier(id);
                } else if (items.length > 0) {
                    const id = items[0].dataset.id;
                    selectSupplier(id);
                }
            } else if (e.key === 'Escape') {
                supDropdownList.style.display = 'none';
            }
        });
    }

    function updateHighlightedSupItem(items) {
        items.forEach((item, idx) => {
            if (idx === highlightedSupIndex) {
                item.classList.add('highlighted');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('highlighted');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (supSearchWrapper && !supSearchWrapper.contains(e.target)) {
            if (supDropdownList) supDropdownList.style.display = 'none';
        }
    });

    document.querySelectorAll('.item-row').forEach(r => attachExcelKeyboardEvents(r));
    updateRowNumbers();
    calculateGrandTotal();
});
