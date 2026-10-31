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

function togglePoHeaderAccordion() {
    const body = document.getElementById('poHeaderAccordionBody');
    const btn = document.getElementById('btnToggleAccordion');
    if (!body) return;
    body.classList.toggle('open');
    const isOpen = body.classList.contains('open');
    if (btn) {
        btn.innerHTML = isOpen
            ? '▴ Sembunyikan Detail (Gudang, Penyetuju, TOP, Estimasi, Remark, Pajak)'
            : '▾ Lihat Selengkapnya (Gudang, Penyetuju, TOP, Estimasi, Remark, Pajak)';
    }
}
window.togglePoHeaderAccordion = togglePoHeaderAccordion;

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

    // Auto-fill Term of Payment jika belum diubah manual
    const elTopInput = document.getElementById('term_of_payment');
    if (elTopInput && (!elTopInput.value || elTopInput.value === 'Transfer' || elTopInput.dataset.autoFilled === 'true')) {
        if (sup.topHari && parseInt(sup.topHari, 10) > 0) {
            elTopInput.value = `Tempo ${sup.topHari} Hari (Transfer)`;
        } else {
            elTopInput.value = 'Transfer';
        }
        elTopInput.dataset.autoFilled = 'true';
    }

    if (currentPoMode === 'multi') {
        document.querySelectorAll('.item-supplier').forEach(sel => {
            if (!sel.value) {
                sel.value = sup.id;
            }
        });
    }
    calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }

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
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
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
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
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

    const headerIncCheck = document.getElementById('is_harga_termasuk_pajak');
    const rowIncCheck = tr.querySelector('.item-include-tax');
    if (rowIncCheck && headerIncCheck) {
        rowIncCheck.checked = headerIncCheck.checked;
    }

    const select = tr.querySelector('.item-barang');
    applyBarangCategoryFilterToSelect(select, activeBarangCategory);

    if (focusNew && select) {
        select.focus();
    }
    calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
    return tr;
}

function onHeaderTaxInclusiveToggle(headerCheckbox) {
    const isInc = headerCheckbox.checked;
    const badge = document.getElementById('badgeTaxMode');
    if (badge) {
        if (isInc) {
            badge.innerText = 'Gross';
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
        } else {
            badge.innerText = 'Net';
            badge.style.background = '#e2e8f0';
            badge.style.color = '#475569';
        }
    }
    calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) {
        return;
    }
    btn.closest('tr').remove();
    updateRowNumbers();
    calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
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
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
}

function calculateSubtotal() {
    calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
}

function calculateGrandTotal() {
    let totalQty = 0;
    let totalBruto = 0;
    let rowCount = 0;
    const supplierItemCount = {};
    const supHiddenSelect = document.getElementById('supplier_id');

    document.querySelectorAll('.item-row').forEach(row => {
        rowCount++;
        const qty = Math.max(0, parseFloat(row.querySelector('.item-qty')?.value) || 0);
        const harga = Math.max(0, parseFloat(row.querySelector('.item-harga')?.value) || 0);
        const rowSubtotal = qty * harga;

        const rowSubtotalCell = row.querySelector('.row-subtotal');
        if (rowSubtotalCell) {
            rowSubtotalCell.innerText = 'Rp ' + Math.round(rowSubtotal).toLocaleString('id-ID');
        }

        totalQty += qty;
        totalBruto += rowSubtotal;

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

    // Kontrol Finansial Global Ringkasan
    const diskonPersen = Math.min(100, Math.max(0, parseFloat(document.getElementById('global_diskon_persen')?.value) || 0));
    const potonganNominal = Math.max(0, parseFloat(document.getElementById('global_potongan_nominal')?.value) || 0);

    const pajakSelect = document.getElementById('global_pajak_id');
    const selectedOpt = pajakSelect ? pajakSelect.options[pajakSelect.selectedIndex] : null;
    const tarif = selectedOpt ? (parseFloat(selectedOpt.dataset.tarif) || 0) : 0;
    const sifat = selectedOpt ? (selectedOpt.dataset.sifat || 'TAMBAH') : 'TAMBAH';
    const isInc = document.getElementById('is_harga_termasuk_pajak')?.checked || false;

    const totalDiskon = totalBruto * (diskonPersen / 100);
    const totalPotongan = potonganNominal;
    const subtotalNetto = Math.max(0, totalBruto - totalDiskon - totalPotongan);

    let totalDpp = 0;
    let totalPpn = 0;
    let grandTotal = 0;

    if (isInc && tarif > 0 && sifat !== 'POTONG') {
        // Harga Termasuk PPN (Inclusive): Total yang dibayar tetap, DPP dihitung mundur
        totalDpp = Math.round((subtotalNetto / (1 + (tarif / 100))) * 10000) / 10000;
        totalPpn = Math.round((subtotalNetto - totalDpp) * 10000) / 10000;
        grandTotal = subtotalNetto;
    } else {
        // Harga Belum Termasuk PPN (Exclude) ATAU Pajak Sifat Potong (PPh 22, PPh 23)
        totalDpp = subtotalNetto;
        totalPpn = Math.round((totalDpp * (tarif / 100)) * 10000) / 10000;
        grandTotal = (sifat === 'POTONG')
            ? Math.max(0, totalDpp - totalPpn)
            : (totalDpp + totalPpn);
    }

    const elTotalQty = document.getElementById('totalQtyDisplay');
    const elTotalSubtotalDisplay = document.getElementById('totalSubtotalDisplay');
    const elGrandTotalDisplay = document.getElementById('grandTotalDisplay');

    if (elTotalQty) elTotalQty.innerText = totalQty.toFixed(2);
    if (elTotalSubtotalDisplay) elTotalSubtotalDisplay.innerText = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (elGrandTotalDisplay) elGrandTotalDisplay.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const sideGrandTotal = document.getElementById('sideGrandTotal');
    const sideTotalQty = document.getElementById('sideTotalQty');
    const sideSubtotal = document.getElementById('sideSubtotalBruto');
    const sideDiskon = document.getElementById('sideTotalDiskon');
    const sidePotongan = document.getElementById('sideTotalPotongan');
    const sideDpp = document.getElementById('sideTotalDpp');
    const sidePpn = document.getElementById('sideTotalPpn');

    if (sideGrandTotal) sideGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    if (sideTotalQty) sideTotalQty.innerText = totalQty.toFixed(2);
    if (sideSubtotal) sideSubtotal.innerText = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (sideDiskon) sideDiskon.innerText = totalDiskon > 0 ? '-Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : 'Rp 0';
    if (sidePotongan) sidePotongan.innerText = totalPotongan > 0 ? '-Rp ' + Math.round(totalPotongan).toLocaleString('id-ID') : 'Rp 0';
    if (sideDpp) {
        sideDpp.innerHTML = 'Rp ' + Math.round(totalDpp).toLocaleString('id-ID') + (isInc && tarif > 0 && sifat !== 'POTONG' ? ' <small style="color: #64748b; font-size: 0.7rem; font-weight: 500;">(DPP)</small>' : '');
    }
    if (sidePpn) {
        if (totalPpn > 0) {
            if (sifat === 'POTONG') {
                sidePpn.innerHTML = `<span style="color: #dc2626;">-Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #ef4444; font-size: 0.7rem; font-weight: 600;">(Potong)</small>`;
            } else if (isInc) {
                sidePpn.innerHTML = `<span style="color: #16a34a;">Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #15803d; font-size: 0.7rem; font-weight: 600;">(Termasuk)</small>`;
            } else {
                sidePpn.innerHTML = `<span style="color: #0284c7;">+Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span>`;
            }
        } else {
            sidePpn.innerHTML = '<span style="color: #64748b;">Rp 0</span>';
        }
    }

    // Live KPI Widget di Siku Kanan Atas
    const topLiveGrandTotal = document.getElementById('topLiveGrandTotal');
    const topLiveItemCount = document.getElementById('topLiveItemCount');
    if (topLiveGrandTotal) topLiveGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    if (topLiveItemCount) topLiveItemCount.innerText = rowCount + ' Item';

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

/* =========================================================================
 * MODAL REKOMENDASI STOK KRITIS & SAFETY STOCK POPUP (macOS Style)
 * ========================================================================= */
let activeSafetyModalTab = 'ALL';

function updateSafetySegmentCounts() {
    const data = window.safetyStockData || [];
    const countAll = data.length;
    const countBb = data.filter(i => i.category === 'BB').length;
    const countBumbu = data.filter(i => i.category === 'BUMBU').length;
    const countPack = data.filter(i => i.category === 'PACK').length;

    const elAll = document.getElementById('countTabAll');
    const elBb = document.getElementById('countTabBb');
    const elBumbu = document.getElementById('countTabBumbu');
    const elPack = document.getElementById('countTabPack');

    if (elAll) elAll.innerText = countAll;
    if (elBb) elBb.innerText = countBb;
    if (elBumbu) elBumbu.innerText = countBumbu;
    if (elPack) elPack.innerText = countPack;

    const badge = document.getElementById('modal_safety_count_badge');
    if (badge) badge.innerText = `${countAll} Item`;
}

function openSafetyStockModal() {
    const modal = document.getElementById('modalSafetyStock');
    if (!modal) return;

    if (typeof openModal === 'function') {
        openModal('modalSafetyStock');
    } else {
        modal.classList.add('show');
    }

    const searchInput = document.getElementById('modal_safety_search');
    if (searchInput) {
        searchInput.value = '';
        setTimeout(() => searchInput.focus(), 150);
    }

    activeSafetyModalTab = 'ALL';
    document.querySelectorAll('.mac-segment-btn').forEach(t => t.classList.remove('active'));
    const defaultTab = document.querySelector('.mac-segment-btn[data-tab="ALL"]');
    if (defaultTab) defaultTab.classList.add('active');

    updateSafetySegmentCounts();
    renderSafetyStockModalTable();
}

function closeSafetyStockModal() {
    if (typeof closeModal === 'function') {
        closeModal('modalSafetyStock');
    } else {
        const modal = document.getElementById('modalSafetyStock');
        if (modal) modal.classList.remove('show');
    }
}

function toggleSafetyModalMaximize() {
    const dialog = document.querySelector('.mac-window-dialog');
    if (!dialog) return;
    if (dialog.classList.contains('is-maximized')) {
        dialog.style.maxWidth = '';
        dialog.style.width = '';
        dialog.style.maxHeight = '';
        dialog.classList.remove('is-maximized');
    } else {
        dialog.style.maxWidth = '98vw';
        dialog.style.width = '98vw';
        dialog.style.maxHeight = '96vh';
        dialog.classList.add('is-maximized');
    }
}

function filterSafetyStockModalTab(tab, btn) {
    activeSafetyModalTab = tab;
    document.querySelectorAll('.mac-segment-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderSafetyStockModalTable();
}

function renderSafetyStockModalTable() {
    const tbody = document.getElementById('safety_modal_tbody');
    const searchInput = document.getElementById('modal_safety_search');
    const countText = document.getElementById('modal_safety_count_text');
    if (!tbody) return;

    const data = window.safetyStockData || [];
    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

    const filtered = data.filter(item => {
        const matchesTab = (activeSafetyModalTab === 'ALL' || item.category === activeSafetyModalTab);
        if (!matchesTab) return false;
        if (!query) return true;
        return (item.name && item.name.toLowerCase().includes(query))
            || (item.code && item.code.toLowerCase().includes(query));
    });

    if (countText) {
        countText.innerText = `${filtered.length} dari ${data.length} komoditas`;
    }

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                    <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">🔍</div>
                    <div style="font-weight: 700; color: #334155; font-size: 0.9rem;">Tidak ada komoditas stok kritis yang cocok.</div>
                    <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.35rem;">Coba periksa kata kunci pencarian atau tab filter di atas.</div>
                </td>
            </tr>
        `;
        return;
    }

    // Cek item mana saja yang sudah terpilih di tabel PO saat ini
    const currentPoItemIds = new Set();
    document.querySelectorAll('.item-row').forEach(row => {
        const val = row.querySelector('.item-barang')?.value;
        if (val) currentPoItemIds.add(String(val));
    });

    let html = '';
    filtered.forEach((item, idx) => {
        const isAlreadyAdded = currentPoItemIds.has(String(item.id));
        const badgeCategory = item.category === 'BB' 
            ? '<span class="badge" style="background:#eff6ff; color:#1d4ed8; font-size:0.675rem; border:1px solid #bfdbfe; font-weight:700; padding:2px 8px; border-radius:12px;">Bahan Baku</span>'
            : (item.category === 'BUMBU'
                ? '<span class="badge" style="background:#fefce8; color:#a16207; font-size:0.675rem; border:1px solid #fef08a; font-weight:700; padding:2px 8px; border-radius:12px;">Bumbu & Penolong</span>'
                : '<span class="badge" style="background:#f1f5f9; color:#475569; font-size:0.675rem; border:1px solid #cbd5e1; font-weight:700; padding:2px 8px; border-radius:12px;">Kemasan & Karton</span>');

        const btnClass = isAlreadyAdded ? 'mac-btn-row-add added' : 'mac-btn-row-add';
        const btnLabel = isAlreadyAdded ? '✓ Ditambahkan (+)' : '+ Masukkan';

        html += `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="text-align: center; color: #64748b; font-weight: 600; font-size: 0.775rem;">${idx + 1}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.825rem; letter-spacing: -0.01em;">${escapeHtml(item.name)}</div>
                    <div style="font-size: 0.7rem; font-family: monospace; color: #0284c7; font-weight: 600; margin-top: 1px;">${escapeHtml(item.code || '-')}</div>
                </td>
                <td style="vertical-align: middle;">${badgeCategory}</td>
                <td style="text-align: right; font-family: monospace; font-size: 0.8rem; color: #ef4444; font-weight: 700;">
                    ${item.currentStock.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} <span style="font-size: 0.7rem; color: #dc2626;">${escapeHtml(item.satuanCd)}</span>
                </td>
                <td style="text-align: right; font-family: monospace; font-size: 0.8rem; color: #64748b;">
                    ${item.minStock.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} <span style="font-size: 0.7rem; color: #94a3b8;">${escapeHtml(item.satuanCd)}</span>
                </td>
                <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: #b45309; font-weight: 800; background: #fffdf5;">
                    +${item.deficitQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} <span style="font-size: 0.7rem; color: #b45309;">${escapeHtml(item.satuanCd)}</span>
                </td>
                <td style="text-align: right; font-family: monospace; font-size: 0.8rem; color: #1e293b; font-weight: 600;">
                    Rp ${Math.round(item.defaultPrice).toLocaleString('id-ID')}
                </td>
                <td style="text-align: center;">
                    <button type="button" 
                            id="btnSafetyAdd_${item.id}"
                            class="${btnClass}"
                            onclick="handleSafetyItemInsert(${item.id})">
                        ${btnLabel}
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function handleSafetyItemInsert(barangId) {
    const data = window.safetyStockData || [];
    const item = data.find(i => Number(i.id) === Number(barangId));
    if (!item) return;

    const success = addBelowMinimumItem(item.id, item.name, item.satuan, item.defaultPrice, item.deficitQty);
    if (success) {
        const btn = document.getElementById(`btnSafetyAdd_${item.id}`);
        if (btn) {
            btn.innerHTML = '✓ Ditambahkan (+)';
            btn.className = 'mac-btn-row-add added';
        }
        showSafetyToast(`"${item.name}" (+${item.deficitQty} ${item.satuanCd}) berhasil dimasukkan ke tabel PO!`);
    }
}

function insertAllFilteredSafetyItems() {
    const data = window.safetyStockData || [];
    const searchInput = document.getElementById('modal_safety_search');
    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

    const filtered = data.filter(item => {
        const matchesTab = (activeSafetyModalTab === 'ALL' || item.category === activeSafetyModalTab);
        if (!matchesTab) return false;
        if (!query) return true;
        return (item.name && item.name.toLowerCase().includes(query))
            || (item.code && item.code.toLowerCase().includes(query));
    });

    if (filtered.length === 0) return;

    let addedCount = 0;
    filtered.forEach(item => {
        const res = addBelowMinimumItem(item.id, item.name, item.satuan, item.defaultPrice, item.deficitQty);
        if (res) addedCount++;
    });

    closeSafetyStockModal();
    showSafetyToast(`${addedCount} komoditas stok kritis berhasil dimasukkan ke tabel PO!`);
}

function showSafetyToast(message) {
    let toast = document.getElementById('safetyStockToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'safetyStockToast';
        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #ffffff;
            padding: 0.65rem 1.15rem;
            border-radius: 8px;
            font-size: 0.825rem;
            font-weight: 600;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.25s ease;
            pointer-events: none;
        `;
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<svg width="16" height="16" fill="none" stroke="#34d399" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <span>${escapeHtml(message)}</span>`;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    clearTimeout(window._safetyToastTimer);
    window._safetyToastTimer = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
    }, 3200);
}

function addBelowMinimumItem(barangId, barangNm, satuanNm, defaultHarga, deficitQty) {
    const rows = document.querySelectorAll('.item-row');
    let targetRow = null;

    // Cek apakah ada baris yang sudah berisi barangId yang sama
    for (let r of rows) {
        const itemBarangSelect = r.querySelector('.item-barang');
        if (itemBarangSelect && String(itemBarangSelect.value) === String(barangId)) {
            const qtyInput = r.querySelector('.item-qty');
            if (qtyInput) {
                const currentVal = parseFloat(qtyInput.value) || 0;
                qtyInput.value = currentVal + parseFloat(deficitQty || 1);
                calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
                qtyInput.focus();
                return true;
            }
        }
    }

    // Jika baris pertama masih kosong (belum pilih barang)
    if (rows.length === 1 && (!rows[0].querySelector('.item-barang')?.value)) {
        targetRow = rows[0];
    } else {
        targetRow = addRow(false);
    }

    if (!targetRow) return false;

    const select = targetRow.querySelector('.item-barang');
    if (!select) return false;

    // Pastikan optgroup tidak disembunyikan oleh filter kategori
    applyBarangCategoryFilterToSelect(select, 'ALL');

    select.value = String(barangId);

    // Update satuan dan harga standar
    const elSatuan = targetRow.querySelector('.row-satuan');
    if (elSatuan) elSatuan.innerText = satuanNm || '-';

    const qtyInput = targetRow.querySelector('.item-qty');
    if (qtyInput) qtyInput.value = parseFloat(deficitQty) > 0 ? parseFloat(deficitQty) : 1;

    const hargaInput = targetRow.querySelector('.item-harga');
    if (hargaInput) hargaInput.value = parseFloat(defaultHarga) || 0;

    const supHiddenSelect = document.getElementById('supplier_id');
    if (currentPoMode === 'multi' && supHiddenSelect && supHiddenSelect.value) {
        const rowSup = targetRow.querySelector('.item-supplier');
        if (rowSup && !rowSup.value) rowSup.value = supHiddenSelect.value;
    }

    updateRowSatuan(select);

    // Pertahankan defaultHarga jika updateRowSatuan mengosongkan harga
    if (parseFloat(defaultHarga) > 0 && hargaInput && (!parseFloat(hargaInput.value))) {
        hargaInput.value = defaultHarga;
    }

    calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }
    return true;
}

function insertItemFromSafetyStock(barangId, defisitQty, hargaBeliStandar, satuanNm, barangCd, barangNm) {
    addBelowMinimumItem(barangId, barangNm, satuanNm, hargaBeliStandar, defisitQty);
}

window.openSafetyStockModal = openSafetyStockModal;
window.closeSafetyStockModal = closeSafetyStockModal;
window.filterSafetyStockModalTab = filterSafetyStockModalTab;
window.renderSafetyStockModalTable = renderSafetyStockModalTable;
window.handleSafetyItemInsert = handleSafetyItemInsert;
window.insertAllFilteredSafetyItems = insertAllFilteredSafetyItems;
window.addBelowMinimumItem = addBelowMinimumItem;
window.insertItemFromSafetyStock = insertItemFromSafetyStock;

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
    const headerIncCheck = document.getElementById('is_harga_termasuk_pajak');
    if (headerIncCheck && headerIncCheck.checked) {
        onHeaderTaxInclusiveToggle(headerIncCheck);
    }
    updateRowNumbers();
    calculateGrandTotal();
    if (typeof savePoCreateDraft === 'function') {
        savePoCreateDraft();
    }

    initCreatePoDateNumberSync();
    initCreatePreviousMonthProtection();
    initPoDraftAutoSave();
});

/**
 * =========================================================================
 * PROTEKSI PERIODE BULAN SEBELUMNYA & LIVE SYNC NOMOR PO DENGAN BULAN (CREATE)
 * =========================================================================
 */
function formatTanggalIndo(dateStr) {
    if (!dateStr) return '-';
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const parts = dateStr.split('-');
    if (parts.length === 3) {
        const year = parts[0];
        const monthIdx = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);
        return `${day} ${months[monthIdx] || ''} ${year}`;
    }
    return dateStr;
}

function openPreviousMonthModal(options) {
    const modal = document.getElementById('modalConfirmPreviousMonth');
    if (!modal) return;
    const elPoNo = document.getElementById('mcpmPoNo');
    const elPoTgl = document.getElementById('mcpmPoTgl');
    const elTitle = document.getElementById('mcpmTitle');
    const elSubtitle = document.getElementById('mcpmSubtitle');
    const elContent = document.getElementById('mcpmAlertContent');
    const elQuestion = document.getElementById('mcpmQuestion');

    if (elPoNo) elPoNo.textContent = options.poNo || '-';
    if (elPoTgl) elPoTgl.textContent = formatTanggalIndo(options.poTgl);
    if (elTitle) elTitle.textContent = options.title || 'Peringatan Dokumen Pengadaan';
    if (elSubtitle) elSubtitle.textContent = options.subtitle || 'Perhatian khusus penomoran dokumen & periode pembukuan';
    if (elContent) elContent.innerHTML = options.contentHtml || '';
    if (elQuestion) elQuestion.textContent = options.question || 'Apakah Anda yakin ingin melanjutkan penyimpanan?';

    modal.style.display = 'flex';
}

function closePreviousMonthModal() {
    const modal = document.getElementById('modalConfirmPreviousMonth');
    if (modal) modal.style.display = 'none';
}

function parsePoNoParts(val) {
    if (!val) return null;
    const match = val.trim().match(/^(\d+)\/PO\/MFI\/(\d{1,2})\/(\d{2,4})$/i);
    if (match) {
        const rawYear = match[3];
        const year2 = rawYear.length === 4 ? rawYear.slice(-2) : rawYear.padStart(2, '0');
        return {
            seq: match[1],
            month: match[2].padStart(2, '0'),
            year: year2,
        };
    }
    return null;
}

let lastSystemSuggestedPoNo = '';

function initCreatePoDateNumberSync() {
    const poTglInput = document.getElementById('po_tgl');
    const poNoInput = document.getElementById('po_no');
    const poNoHelp = document.getElementById('po_no_help');

    if (!poTglInput || !poNoInput) return;

    lastSystemSuggestedPoNo = poNoInput.value.trim();
    let initialParts = parsePoNoParts(poNoInput.value);
    let activeMonthYear = initialParts 
        ? `${initialParts.year}-${initialParts.month}` 
        : (poTglInput.value ? poTglInput.value.slice(2, 4) + '-' + poTglInput.value.slice(5, 7) : '');

    function fetchAndApplyPoCode(targetDateStr, reasonText) {
        poNoInput.style.opacity = '0.5';
        poNoInput.style.pointerEvents = 'none';
        if (poNoHelp) {
            poNoHelp.innerHTML = `<span style="color:#0284c7;">⚡ Memeriksa urutan nomor PO untuk periode ${formatTanggalIndo(targetDateStr)}...</span>`;
        }

        fetch(`/ajax/generate-code?type=po&date=${encodeURIComponent(targetDateStr)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.code) {
                    poNoInput.value = data.code;
                    lastSystemSuggestedPoNo = data.code;
                    const newParts = parsePoNoParts(data.code);
                    if (newParts) {
                        activeMonthYear = `${newParts.year}-${newParts.month}`;
                    }

                    // Visual highlight lembut
                    poNoInput.style.background = '#f0fdf4';
                    setTimeout(() => {
                        poNoInput.style.background = '#f8fafc';
                    }, 1200);

                    if (poNoHelp) {
                        poNoHelp.innerHTML = `<span style="color:#15803d; font-weight:600;">✓ Nomor PO disesuaikan otomatis (${reasonText}): <strong>${data.code}</strong></span>`;
                    }
                }
            })
            .catch(err => {
                console.error('Gagal generate nomor PO:', err);
                if (poNoHelp) {
                    poNoHelp.innerHTML = `<span style="color:#dc2626;">Gagal menyinkronkan nomor urut otomatis. Anda dapat mengetiknya secara manual.</span>`;
                }
            })
            .finally(() => {
                poNoInput.style.opacity = '1';
                poNoInput.style.pointerEvents = 'auto';
            });
    }

    // 1. KETIKA TANGGAL PO DIUBAH PENGGUNA (LANGSUNG LIVE)
    ['change', 'input'].forEach(evtName => {
        poTglInput.addEventListener(evtName, function () {
            const newDate = this.value;
            if (!newDate) return;

            const newYM = newDate.slice(2, 4) + '-' + newDate.slice(5, 7);
            if (newYM !== activeMonthYear) {
                fetchAndApplyPoCode(newDate, `urutan bulan ${formatTanggalIndo(newDate)}`);
            }
        });
    });

    // 2. KETIKA BULAN/TAHUN PADA INPUT NOMOR PO DIUBAH LANGSUNG (INSTANT / TANPA HARUS KLIK LUAR)
    let poNoDebounceTimer = null;

    function handlePoNoInputSync() {
        const parts = parsePoNoParts(poNoInput.value);
        if (!parts) return;

        const mNum = parseInt(parts.month, 10);
        if (isNaN(mNum) || mNum < 1 || mNum > 12) return;

        const typedYM = `${parts.year}-${parts.month}`;
        if (typedYM !== activeMonthYear) {
            const targetDate = `20${parts.year}-${parts.month}-01`;
            
            if (poTglInput.value) {
                const currentDay = poTglInput.value.slice(8, 10);
                const safeDate = `20${parts.year}-${parts.month}-${currentDay}`;
                const testD = new Date(safeDate);
                poTglInput.value = (!isNaN(testD.getTime())) ? safeDate : targetDate;
            } else {
                poTglInput.value = targetDate;
            }

            fetchAndApplyPoCode(targetDate, `bulan ${parts.month} tahun 20${parts.year}`);
        }
    }

    // Respon instan saat mengetik (debounce 300ms agar halus saat mengetik 2 digit bulan)
    poNoInput.addEventListener('input', function () {
        clearTimeout(poNoDebounceTimer);
        poNoDebounceTimer = setTimeout(handlePoNoInputSync, 300);
    });

    // Respon instan saat change atau blur
    ['change', 'blur'].forEach(evtName => {
        poNoInput.addEventListener(evtName, function () {
            clearTimeout(poNoDebounceTimer);
            handlePoNoInputSync();
        });
    });
}

function initCreatePreviousMonthProtection() {
    const formPo = document.getElementById('formPo');
    if (!formPo) return;

    let previousMonthConfirmed = false;

    formPo.addEventListener('submit', function (e) {
        if (previousMonthConfirmed) return;

        const poTglInput = document.getElementById('po_tgl');
        const poNoInput = document.getElementById('po_no');
        const poTglVal = poTglInput ? poTglInput.value : '';
        const poNoVal = poNoInput ? poNoInput.value.trim() : '';
        const currentServerYM = window.currentServerYearMonth || (new Date().toISOString().slice(0, 7));

        const docYM = poTglVal ? poTglVal.slice(0, 7) : '';
        const isPreviousMonth = Boolean(docYM && docYM < currentServerYM);

        const currentParts = parsePoNoParts(poNoVal);
        const suggestedParts = parsePoNoParts(lastSystemSuggestedPoNo);
        const isManualSequence = Boolean(currentParts && suggestedParts && (currentParts.seq !== suggestedParts.seq));

        if (isPreviousMonth || isManualSequence) {
            e.preventDefault();

            let title = '';
            let subtitle = '';
            let alerts = [];

            if (isPreviousMonth && isManualSequence) {
                title = 'Peringatan: Periode Sebelumnya & Nomor Urut Manual';
                subtitle = 'Dua penyesuaian khusus terdeteksi pada dokumen pengadaan ini';
            } else if (isPreviousMonth) {
                title = 'Konfirmasi Penambahan Dokumen Periode Sebelumnya';
                subtitle = 'Dokumen ditujukan untuk periode bulan yang telah lewat';
            } else {
                title = 'Peringatan: Nomor Urut PO Telah Diubah Manual';
                subtitle = 'Nomor urut berbeda dari rekomendasi otomatis sistem';
            }

            if (isPreviousMonth) {
                alerts.push(`
                    <div style="margin-bottom: 0.45rem;">
                        <strong>1. Periode Bulan Sebelumnya:</strong> Dokumen ini tercatat pada tanggal <strong>${formatTanggalIndo(poTglVal)}</strong>, sedangkan periode aktif saat ini adalah <strong>${formatTanggalIndo(new Date().toISOString().slice(0, 10))}</strong>. Menambahkan data PO ke periode bulan sebelumnya dapat berdampak pada laporan penutupan buku (closing) dan rekonsiliasi pengadaan gudang.
                    </div>
                `);
            }

            if (isManualSequence) {
                alerts.push(`
                    <div style="margin-bottom: 0.45rem;">
                        <strong>${isPreviousMonth ? '2. ' : ''}Nomor Urut Manual:</strong> Anda memasukkan nomor urut <strong>${currentParts.seq}</strong> (dokumen: <code>${poNoVal}</code>), sedangkan nomor urut otomatis yang disarankan sistem untuk periode ini adalah <strong>${suggestedParts.seq}</strong> (dokumen: <code>${lastSystemSuggestedPoNo}</code>). Mengubah nomor urut manual dapat menimbulkan nomor loncat atau potensi duplikasi arsip di masa mendatang.
                    </div>
                `);
            }

            openPreviousMonthModal({
                poNo: poNoVal,
                poTgl: poTglVal,
                title: title,
                subtitle: subtitle,
                contentHtml: alerts.join(''),
                question: 'Apakah Anda yakin data ini sudah benar dan tetap ingin menyimpan dokumen PO ini?'
            });
        }
    });

    const btnConfirm = document.getElementById('btnConfirmSubmitPreviousMonth');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            previousMonthConfirmed = true;
            clearPoCreateDraft();
            closePreviousMonthModal();
            formPo.submit();
        });
    }
}

/**
 * =========================================================================
 * FITUR DRAFT PERSISTENCE (AUTO-SAVE & RESTORE DATA PADA REFRESH HALAMAN)
 * & MODAL KONFIRMASI HAPUS SEMUA BARIS (ENTERPRISE UX)
 * =========================================================================
 */
const DRAFT_STORAGE_KEY = 'erp_mirasa_po_create_draft';
let draftSaveDebounceTimer = null;

function showDraftIndicator(text) {
    const ind = document.getElementById('draftSaveIndicator');
    if (ind) {
        ind.textContent = text;
        ind.style.display = 'inline-flex';
    }
}

function clearPoCreateDraft() {
    try {
        localStorage.removeItem(DRAFT_STORAGE_KEY);
        const ind = document.getElementById('draftSaveIndicator');
        if (ind) ind.style.display = 'none';
    } catch (e) {
        console.warn('Gagal menghapus draft PO:', e);
    }
}
window.clearPoCreateDraft = clearPoCreateDraft;

function savePoCreateDraft() {
    clearTimeout(draftSaveDebounceTimer);
    draftSaveDebounceTimer = setTimeout(() => {
        try {
            const formPo = document.getElementById('formPo');
            if (!formPo) return;

            const supplierId = document.getElementById('supplier_id')?.value || '';
            const poTgl = document.getElementById('po_tgl')?.value || '';
            const poNo = document.getElementById('po_no')?.value || '';
            const gudangId = document.getElementById('gudang_id')?.value || '';
            const tglEta = document.getElementById('tgl_estimasi_datang')?.value || '';
            const top = document.getElementById('term_of_payment')?.value || '';
            const catatan = document.getElementById('catatan_txt')?.value || '';
            const remark = document.getElementById('remark_txt')?.value || '';
            const isInc = document.getElementById('is_harga_termasuk_pajak')?.checked ?? false;
            const globalDiskon = document.getElementById('global_diskon_persen')?.value || '';
            const globalPotongan = document.getElementById('global_potongan_nominal')?.value || '';
            const globalPajak = document.getElementById('global_pajak_id')?.value || '';

            const items = [];
            document.querySelectorAll('.item-row').forEach(r => {
                const bId = r.querySelector('.item-barang')?.value || '';
                const sId = r.querySelector('.item-supplier')?.value || '';
                const qty = r.querySelector('.item-qty')?.value || '';
                const harga = r.querySelector('.item-harga')?.value || '';
                const diskon = r.querySelector('.item-diskon')?.value || '';
                const potongan = r.querySelector('.item-potongan')?.value || '';
                const pajak = r.querySelector('.item-pajak')?.value || '';
                const inc = r.querySelector('.item-include-tax')?.checked ?? false;
                const note = r.querySelector('.item-catatan')?.value || '';

                if (bId || qty || harga || diskon || potongan || note) {
                    items.push({
                        barang_id: bId,
                        supplier_id: sId,
                        qty: qty,
                        harga: harga,
                        diskon: diskon,
                        potongan: potongan,
                        pajak_id: pajak,
                        include_tax: inc,
                        catatan: note
                    });
                }
            });

            // Hanya simpan jika pengguna sudah mulai menginputkan sesuatu
            if (!supplierId && !catatan && !remark && items.length === 0) {
                return;
            }

            const payload = {
                timestamp: Date.now(),
                header: {
                    po_mode: currentPoMode,
                    supplier_id: supplierId,
                    po_tgl: poTgl,
                    po_no: poNo,
                    gudang_id: gudangId,
                    tgl_estimasi_datang: tglEta,
                    term_of_payment: top,
                    catatan_txt: catatan,
                    remark_txt: remark,
                    is_harga_termasuk_pajak: isInc,
                    global_diskon_persen: globalDiskon,
                    global_potongan_nominal: globalPotongan,
                    global_pajak_id: globalPajak
                },
                items: items
            };

            localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(payload));
            showDraftIndicator('💾 Draft Tersimpan');
        } catch (e) {
            console.warn('Gagal menyimpan draft PO:', e);
        }
    }, 300);
}
window.savePoCreateDraft = savePoCreateDraft;

function restorePoCreateDraft() {
    try {
        const raw = localStorage.getItem(DRAFT_STORAGE_KEY);
        if (!raw) return false;

        const draft = JSON.parse(raw);
        if (!draft || !draft.header) return false;

        // Jika halaman dibuka kembali setelah submit/error form server, prioritaskan server
        if (window.oldSupplierId || (window.oldHasMultiSupplier && window.oldHasMultiSupplier !== '0')) {
            return false;
        }

        const h = draft.header;

        // 1. Mode Dokumen (Single / Multi)
        if (h.po_mode === 'multi') {
            setPoMode('multi');
        } else {
            setPoMode('single');
        }

        // 2. Supplier
        if (h.supplier_id) {
            selectSupplier(h.supplier_id);
        }

        // 3. Header Fields
        if (h.gudang_id) {
            const el = document.getElementById('gudang_id');
            if (el) el.value = h.gudang_id;
        }
        if (h.tgl_estimasi_datang) {
            const el = document.getElementById('tgl_estimasi_datang');
            if (el) el.value = h.tgl_estimasi_datang;
        }
        if (h.term_of_payment) {
            const el = document.getElementById('term_of_payment');
            if (el) el.value = h.term_of_payment;
        }
        if (h.catatan_txt) {
            const el = document.getElementById('catatan_txt');
            if (el) el.value = h.catatan_txt;
        }
        if (h.remark_txt) {
            const el = document.getElementById('remark_txt');
            if (el) el.value = h.remark_txt;
        }
        if (h.is_harga_termasuk_pajak !== undefined) {
            const el = document.getElementById('is_harga_termasuk_pajak');
            if (el) {
                el.checked = Boolean(h.is_harga_termasuk_pajak);
                onHeaderTaxInclusiveToggle(el);
            }
        }
        if (h.global_diskon_persen) {
            const el = document.getElementById('global_diskon_persen');
            if (el) el.value = h.global_diskon_persen;
        }
        if (h.global_potongan_nominal) {
            const el = document.getElementById('global_potongan_nominal');
            if (el) el.value = h.global_potongan_nominal;
        }
        if (h.global_pajak_id) {
            const el = document.getElementById('global_pajak_id');
            if (el) el.value = h.global_pajak_id;
        }

        // 4. Baris Item Tabel PO
        if (draft.items && draft.items.length > 0) {
            const container = document.getElementById('itemsContainer');
            if (container) {
                container.innerHTML = '';
                rowIndex = 0;

                draft.items.forEach(it => {
                    const tr = addRow(false);
                    if (!tr) return;

                    const bSelect = tr.querySelector('.item-barang');
                    if (bSelect && it.barang_id) {
                        applyBarangCategoryFilterToSelect(bSelect, 'ALL');
                        bSelect.value = String(it.barang_id);
                        updateRowSatuan(bSelect);
                    }

                    if (it.supplier_id) {
                        const sSelect = tr.querySelector('.item-supplier');
                        if (sSelect) sSelect.value = it.supplier_id;
                    }
                    if (it.qty !== undefined && it.qty !== '') {
                        const qtyInp = tr.querySelector('.item-qty');
                        if (qtyInp) qtyInp.value = it.qty;
                    }
                    if (it.harga !== undefined && it.harga !== '') {
                        const hrgInp = tr.querySelector('.item-harga');
                        if (hrgInp) hrgInp.value = it.harga;
                    }
                    if (it.diskon !== undefined && it.diskon !== '') {
                        const dInp = tr.querySelector('.item-diskon');
                        if (dInp) dInp.value = it.diskon;
                    }
                    if (it.potongan !== undefined && it.potongan !== '') {
                        const pInp = tr.querySelector('.item-potongan');
                        if (pInp) pInp.value = it.potongan;
                    }
                    if (it.pajak_id !== undefined && it.pajak_id !== '') {
                        const pjInp = tr.querySelector('.item-pajak');
                        if (pjInp) pjInp.value = it.pajak_id;
                    }
                    if (it.include_tax !== undefined) {
                        const incInp = tr.querySelector('.item-include-tax');
                        if (incInp) incInp.checked = Boolean(it.include_tax);
                    }
                    if (it.catatan !== undefined && it.catatan !== '') {
                        const catInp = tr.querySelector('.item-catatan');
                        if (catInp) catInp.value = it.catatan;
                    }
                });

                updateRowNumbers();
                calculateGrandTotal();
            }
        }

        showDraftIndicator('💾 Draft Dipulihkan dari Sesi Terakhir');
        return true;
    } catch (e) {
        console.warn('Gagal me-restore draft PO:', e);
        return false;
    }
}
window.restorePoCreateDraft = restorePoCreateDraft;

function initPoDraftAutoSave() {
    const formPo = document.getElementById('formPo');
    if (!formPo) return;

    // Restore jika ada data tersimpan
    restorePoCreateDraft();

    // Listener auto-save pada input & change
    formPo.addEventListener('input', savePoCreateDraft);
    formPo.addEventListener('change', savePoCreateDraft);

    // Hapus draft saat submit form berhasil
    formPo.addEventListener('submit', function() {
        clearPoCreateDraft();
    });
}

// -------------------------------------------------------------------------
// FITUR MODAL KONFIRMASI HAPUS SEMUA BARIS (CLEAR ALL ROWS)
// -------------------------------------------------------------------------
function openConfirmClearRowsModal() {
    const m = document.getElementById('modalConfirmClearRows');
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}
window.openConfirmClearRowsModal = openConfirmClearRowsModal;

function closeConfirmClearRowsModal() {
    const m = document.getElementById('modalConfirmClearRows');
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = '';
    }
}
window.closeConfirmClearRowsModal = closeConfirmClearRowsModal;

function executeClearAllRows() {
    const container = document.getElementById('itemsContainer');
    if (container) {
        container.innerHTML = '';
        rowIndex = 0;
        addRow(true);
        updateRowNumbers();
        calculateGrandTotal();
        savePoCreateDraft();
    }
    closeConfirmClearRowsModal();
}
window.executeClearAllRows = executeClearAllRows;
