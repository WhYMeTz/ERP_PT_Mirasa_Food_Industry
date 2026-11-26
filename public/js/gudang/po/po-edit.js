/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER EDIT JAVASCRIPT
 * =========================================================================
 */

let activeModalSupplierTab = 'ALL';
let highlightedSupIndex = -1;
let modalCurrentPage = 1;
const modalPerPage = 8;

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
window.changeSupplierModalPage = changeSupplierModalPage;

function openSupplierModal() {
    const supDropdownList = document.getElementById('supplier_dropdown_list');
    const supSearchInput = document.getElementById('supplier_search_input');
    const modalSearchInput = document.getElementById('modal_supplier_search');

    if (supDropdownList) supDropdownList.style.display = 'none';
    if (typeof openModal === 'function') {
        openModal('modalPilihSupplier');
    } else {
        const modal = document.getElementById('modalPilihSupplier');
        if (modal) modal.style.display = 'flex';
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
window.openSupplierModal = openSupplierModal;

function closeSupplierModal() {
    if (typeof closeModal === 'function') {
        closeModal('modalPilihSupplier');
    } else {
        const modal = document.getElementById('modalPilihSupplier');
        if (modal) modal.style.display = 'none';
    }
}
window.closeSupplierModal = closeSupplierModal;

function filterSupplierModalTab(tab, btn) {
    activeModalSupplierTab = tab;
    modalCurrentPage = 1;
    document.querySelectorAll('.sup-modal-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderSupplierModalTable();
}
window.filterSupplierModalTab = filterSupplierModalTab;

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
               (s.categoryName && s.categoryName.toLowerCase().includes(query));
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

    if (supHiddenSelect) {
        supHiddenSelect.value = sup.id;
        supHiddenSelect.dispatchEvent(new Event('change'));
    }

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

    // Auto-fill Term of Payment jika kosong atau baru
    const elTopInput = document.getElementById('term_of_payment');
    if (elTopInput && (!elTopInput.value || elTopInput.value === 'Transfer' || elTopInput.dataset.autoFilled === 'true')) {
        if (sup.topHari && parseInt(sup.topHari, 10) > 0) {
            elTopInput.value = `Tempo ${sup.topHari} Hari (Transfer)`;
        } else {
            elTopInput.value = 'Transfer';
        }
        elTopInput.dataset.autoFilled = 'true';
    }
}
window.selectSupplier = selectSupplier;

function clearSelectedSupplier() {
    const supHiddenSelect = document.getElementById('supplier_id');
    const selectedSupCard = document.getElementById('selected_supplier_card');
    const supSearchWrapper = document.getElementById('supplier_search_wrapper');
    const supSearchInput = document.getElementById('supplier_search_input');
    const supDropdownList = document.getElementById('supplier_dropdown_list');

    if (supHiddenSelect) {
        supHiddenSelect.value = '';
        supHiddenSelect.dispatchEvent(new Event('change'));
    }
    if (selectedSupCard) selectedSupCard.style.display = 'none';
    if (supSearchWrapper) supSearchWrapper.style.display = 'block';
    if (supSearchInput) {
        supSearchInput.value = '';
        renderSupplierDropdown('');
        supSearchInput.focus();
    }
    if (supDropdownList) supDropdownList.style.display = 'none';
}
window.clearSelectedSupplier = clearSelectedSupplier;

function initSupplierSearchEvents() {
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
}

function togglePoHeaderAccordion() {
    const body = document.getElementById('poHeaderAccordionBody');
    const btn = document.getElementById('btnToggleAccordion');
    if (!body) return;
    body.classList.toggle('open');
    const isOpen = body.classList.contains('open');
    if (btn) {
        btn.innerHTML = isOpen
            ? '▴ Sembunyikan Detail (Gudang, Penyetuju, TOP, Estimasi, Franco, Catatan, Pajak)'
            : '▾ Lihat Selengkapnya (Gudang, Penyetuju, TOP, Estimasi, Franco, Catatan, Pajak)';
    }
}
window.togglePoHeaderAccordion = togglePoHeaderAccordion;

function handleBarangChange(selectEl) {
    const row = selectEl.closest('tr');
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const satuanBadge = row.querySelector('.satuan-badge');
    const inputHarga = row.querySelector('.input-harga');

    if (selectedOpt && selectedOpt.value) {
        satuanBadge.textContent = selectedOpt.getAttribute('data-satuan') || 'KG';
        const defaultHarga = parseFloat(selectedOpt.getAttribute('data-harga')) || 0;
        if (parseFloat(inputHarga.value) === 0 && defaultHarga > 0) {
            inputHarga.value = defaultHarga;
        }
    } else {
        satuanBadge.textContent = '-';
    }
    calculateRow(selectEl);
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

    // Sinkronisasi otomatis ke SEMUA baris barang (1 barang maupun lebih dari 1 barang)
    document.querySelectorAll('.item-include-tax').forEach(cb => {
        cb.checked = isInc;
    });

    calculateAllTotals();
}
window.onHeaderTaxInclusiveToggle = onHeaderTaxInclusiveToggle;

function onRowTaxInclusiveChange(rowCheckbox) {
    const rowCheckboxes = document.querySelectorAll('.item-include-tax');
    const headerCheckbox = document.getElementById('is_harga_termasuk_pajak');
    const badge = document.getElementById('badgeTaxMode');

    if (rowCheckboxes.length > 0 && headerCheckbox) {
        let allChecked = true;
        let anyChecked = false;
        rowCheckboxes.forEach(cb => {
            if (cb.checked) anyChecked = true;
            else allChecked = false;
        });

        if (allChecked) {
            headerCheckbox.checked = true;
            if (badge) {
                badge.innerText = 'Gross';
                badge.style.background = '#dcfce7';
                badge.style.color = '#15803d';
            }
        } else if (!anyChecked) {
            headerCheckbox.checked = false;
            if (badge) {
                badge.innerText = 'Net';
                badge.style.background = '#e2e8f0';
                badge.style.color = '#475569';
            }
        }
    }

    calculateAllTotals();
}
window.onRowTaxInclusiveChange = onRowTaxInclusiveChange;

function syncHeaderTaxLabel() {
    const lbl = document.getElementById('lblHeaderTaxInclusive');
    if (!lbl) return;
    const headerPajakSelect = document.getElementById('global_pajak_id');
    if (!headerPajakSelect || headerPajakSelect.selectedIndex < 0) {
        lbl.textContent = 'Harga Termasuk Pajak';
        return;
    }
    const selectedOpt = headerPajakSelect.options[headerPajakSelect.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) {
        lbl.textContent = 'Harga Termasuk Pajak';
        return;
    }
    const sifat = (selectedOpt.dataset.sifat || '').toUpperCase();
    const text = (selectedOpt.text || '').toUpperCase();
    const tarif = parseFloat(selectedOpt.dataset.tarif) || 0;

    if (tarif === 0) {
        lbl.textContent = 'Harga Termasuk Pajak';
    } else if (sifat === 'POTONG' || text.includes('PPH')) {
        lbl.textContent = 'Harga Termasuk PPh';
    } else if (sifat === 'TAMBAH' || text.includes('PPN')) {
        lbl.textContent = 'Harga Termasuk PPN';
    } else {
        lbl.textContent = 'Harga Termasuk Pajak';
    }
}
window.syncHeaderTaxLabel = syncHeaderTaxLabel;

function onHeaderPajakChange(headerSelect) {
    syncHeaderTaxLabel();
    calculateAllTotals();
}
window.onHeaderPajakChange = onHeaderPajakChange;

function calculateRow(element) {
    calculateAllTotals();
}

function calculateAllTotals() {
    let totalQty = 0;
    let totalBruto = 0;
    let rowCount = 0;

    const diskonPersen = Math.min(100, Math.max(0, parseFloat(document.getElementById('global_diskon_persen')?.value) || 0));
    const potonganNominal = Math.max(0, parseFloat(document.getElementById('global_potongan_nominal')?.value) || 0);

    const headerPajakSelect = document.getElementById('global_pajak_id');
    const headerOpt = headerPajakSelect ? headerPajakSelect.options[headerPajakSelect.selectedIndex] : null;
    const headerTarif = headerOpt ? (parseFloat(headerOpt.dataset.tarif) || 0) : 0;
    const headerSifat = headerOpt ? (headerOpt.dataset.sifat || 'TAMBAH') : 'TAMBAH';
    const headerIsInc = document.getElementById('is_harga_termasuk_pajak')?.checked || false;

    const rows = document.querySelectorAll('#tbodyPoItems tr.po-item-row');

    // Pass 1: Total kuantitas & bruto untuk prorata diskon & potongan
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
        totalQty += qty;
        totalBruto += (qty * harga);
    });

    const totalDiskon = totalBruto * (diskonPersen / 100);
    const totalPotongan = potonganNominal;
    const sumGrossAfterDiskon = Math.max(0, totalBruto - totalDiskon);

    let totalDpp = 0;
    let totalPpn = 0;
    let grandTotal = 0;
    let hasIncTax = false;
    let hasPotongTax = false;

    // Pass 2: Per-row calculation
    rows.forEach(row => {
        rowCount++;
        const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
        const rowDiskonUnit = harga * (diskonPersen / 100);
        const rowHargaNetto = Math.max(0, harga - rowDiskonUnit);

        let rowPotongan = 0;
        if (totalPotongan > 0 && sumGrossAfterDiskon > 0) {
            const rowGross = qty * rowHargaNetto;
            rowPotongan = (rowGross / sumGrossAfterDiskon) * totalPotongan;
        }

        const grossRowTotal = Math.max(0, (qty * rowHargaNetto) - rowPotongan);

        // Pajak PO diterapkan ke baris, dengan status inclusive/exclusive per baris
        const rowTarif = headerTarif;
        const rowSifat = headerSifat;

        const rowIncCheck = row.querySelector('.item-include-tax');
        const rowIsInc = rowIncCheck ? rowIncCheck.checked : headerIsInc;

        let rowDpp = 0;
        let rowPpn = 0;
        let rowTagihan = 0;

        if (rowIsInc && rowTarif > 0) {
            hasIncTax = true;
            if (rowSifat === 'POTONG') {
                hasPotongTax = true;
                rowDpp = grossRowTotal;
                rowPpn = Math.round((rowDpp * (rowTarif / 100)) * 10000) / 10000;
                rowTagihan = grossRowTotal;
            } else {
                rowDpp = Math.round((grossRowTotal / (1 + (rowTarif / 100))) * 10000) / 10000;
                rowPpn = Math.round((grossRowTotal - rowDpp) * 10000) / 10000;
                rowTagihan = grossRowTotal;
            }
        } else {
            rowDpp = grossRowTotal;
            rowPpn = Math.round((rowDpp * (rowTarif / 100)) * 10000) / 10000;
            rowTagihan = (rowSifat === 'POTONG')
                ? Math.max(0, rowDpp - rowPpn)
                : (rowDpp + rowPpn);
            if (rowSifat === 'POTONG' && rowTarif > 0) hasPotongTax = true;
        }

        const cellSubtotal = row.querySelector('.row-subtotal');
        if (cellSubtotal) {
            cellSubtotal.textContent = 'Rp ' + Math.round(rowTagihan).toLocaleString('id-ID');
        }

        totalDpp += rowDpp;
        totalPpn += rowPpn;
        grandTotal += rowTagihan;
    });

    const elTotalQty = document.getElementById('totalQtyDisplay');
    const elTotalSubtotalDisplay = document.getElementById('totalSubtotalDisplay');
    if (elTotalQty) elTotalQty.innerText = totalQty.toFixed(2);
    if (elTotalSubtotalDisplay) elTotalSubtotalDisplay.innerText = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');

    const elSummaryTotalQty = document.getElementById('summaryTotalQty');
    const elSubtotalBruto = document.getElementById('summarySubtotalBruto');
    const elDiskon = document.getElementById('summaryDiskon');
    const elPotongan = document.getElementById('summaryPotongan');
    const elDpp = document.getElementById('summaryDpp');
    const elPpn = document.getElementById('summaryPpn');
    const elGrandTotal = document.getElementById('summaryGrandTotal');
    const elBadgeItemCount = document.getElementById('badgeItemCount');

    if (elSummaryTotalQty) elSummaryTotalQty.textContent = totalQty.toFixed(2);
    if (elSubtotalBruto) elSubtotalBruto.textContent = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (elDiskon) elDiskon.textContent = totalDiskon > 0 ? '- Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : 'Rp 0';
    if (elPotongan) elPotongan.textContent = totalPotongan > 0 ? '- Rp ' + Math.round(totalPotongan).toLocaleString('id-ID') : 'Rp 0';
    if (elDpp) {
        elDpp.innerHTML = 'Rp ' + Math.round(totalDpp).toLocaleString('id-ID') + (hasIncTax ? ' <small style="color: #64748b; font-size: 0.7rem; font-weight: 500;">(DPP)</small>' : '');
    }
    if (elPpn) {
        if (totalPpn > 0) {
            if (hasPotongTax && !hasIncTax) {
                elPpn.innerHTML = `<span style="color: #92400e;">- Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #b45309; font-size: 0.7rem; font-weight: 600;">(Potong)</small>`;
            } else if (hasIncTax) {
                elPpn.innerHTML = `<span style="color: #334155;">Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #64748b; font-size: 0.7rem; font-weight: 600;">(Termasuk)</small>`;
            } else {
                elPpn.innerHTML = `<span style="color: #0f172a;">+ Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span>`;
            }
        } else {
            elPpn.innerHTML = '<span style="color: #64748b;">Rp 0</span>';
        }
    }
    if (elGrandTotal) elGrandTotal.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    if (elBadgeItemCount) elBadgeItemCount.textContent = `(${rowCount} item)`;

    // Live KPI Widget di Siku Kanan Atas
    const topLiveGrandTotal = document.getElementById('topLiveGrandTotal');
    const topLiveItemCount = document.getElementById('topLiveItemCount');
    if (topLiveGrandTotal) topLiveGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    if (topLiveItemCount) topLiveItemCount.innerText = rowCount + ' Item';
}

function addNewItemRow() {
    const tbody = document.getElementById('tbodyPoItems');
    const template = document.getElementById('templateRow');
    if (!tbody || !template) return;

    window.editRowIndex = (window.editRowIndex || tbody.querySelectorAll('tr.po-item-row').length) + 1;
    const currentCount = tbody.querySelectorAll('tr.po-item-row').length + 1;

    const html = template.innerHTML
        .replace(/__INDEX__/g, window.editRowIndex)
        .replace(/__NUM__/g, currentCount);

    tbody.insertAdjacentHTML('beforeend', html);

    const newRow = tbody.lastElementChild;
    if (newRow) {
        const headerIncCheck = document.getElementById('is_harga_termasuk_pajak');
        const rowIncCheck = newRow.querySelector('.item-include-tax');
        if (rowIncCheck && headerIncCheck) {
            rowIncCheck.checked = headerIncCheck.checked;
        }
    }

    renumberRows();
    calculateAllTotals();
}

function removeRow(btn) {
    const tbody = document.getElementById('tbodyPoItems');
    const rows = tbody.querySelectorAll('tr.po-item-row');
    if (rows.length <= 1) {
        alert('Minimal harus ada 1 item barang dalam Purchase Order.');
        return;
    }

    btn.closest('tr').remove();
    renumberRows();
    calculateAllTotals();
}

function renumberRows() {
    document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach((row, idx) => {
        row.querySelector('.row-num').textContent = idx + 1;
    });
}

/**
 * =========================================================================
 * PROTEKSI PERIODE BULAN SEBELUMNYA & LIVE SYNC NOMOR PO DENGAN BULAN
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
    if (typeof setPoEditSubmitButtonLoading === 'function') {
        setPoEditSubmitButtonLoading(false);
    }
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

function initPoDateNumberSync() {
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

        const excludeParam = window.poId ? `&exclude_id=${encodeURIComponent(window.poId)}` : '';
        fetch(`/ajax/generate-code?type=po&date=${encodeURIComponent(targetDateStr)}${excludeParam}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.code) {
                    poNoInput.value = data.code;
                    lastSystemSuggestedPoNo = data.code;
                    const newParts = parsePoNoParts(data.code);
                    if (newParts) {
                        activeMonthYear = `${newParts.year}-${newParts.month}`;
                    }

                    // Visual highlight
                    poNoInput.style.background = '#f0fdf4';
                    setTimeout(() => {
                        poNoInput.style.background = '#ffffff';
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

/* =========================================================================
 * VALIDASI INTERAKTIF & ANTI DOUBLE-SUBMIT EDIT PO (ENTERPRISE STANDARD)
 * ========================================================================= */
let isPoEditFormSubmitting = false;

function clearPoEditValidationErrors() {
    const banner = document.getElementById('poValidationBanner');
    if (banner) {
        banner.style.display = 'none';
        banner.innerHTML = '';
    }
    document.querySelectorAll('.po-input-error').forEach(el => {
        el.classList.remove('po-input-error');
        el.style.borderColor = '';
        el.style.boxShadow = '';
    });
}

function showPoEditValidationBanner(errors) {
    const banner = document.getElementById('poValidationBanner');
    if (!banner) return;

    banner.className = 'po-validation-banner';
    banner.style.background = '';
    banner.style.borderColor = '';
    banner.style.borderLeft = '';
    banner.style.color = '';

    let listHtml = '';
    errors.forEach(err => {
        listHtml += `<li>${err}</li>`;
    });

    banner.innerHTML = `
        <div class="po-validation-banner-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Terdapat ${errors.length} data yang belum lengkap atau perlu diperiksa:</span>
        </div>
        <ul class="po-validation-banner-list">
            ${listHtml}
        </ul>
    `;
    banner.style.display = 'block';
    banner.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showPoEditNoChangeBanner() {
    const banner = document.getElementById('poValidationBanner');
    if (!banner) return;

    banner.className = 'po-validation-banner po-no-change-banner';
    banner.style.display = 'block';
    banner.style.background = '#f8fafc';
    banner.style.borderColor = '#cbd5e1';
    banner.style.borderLeft = '4px solid #64748b';
    banner.style.color = '#334155';

    const showUrl = window.poShowUrl || 'javascript:void(0)';

    banner.innerHTML = `
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: flex-start; gap: 0.65rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b; flex-shrink: 0; margin-top: 2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <strong style="display: block; font-size: 0.875rem; color: #1e293b; margin-bottom: 0.2rem;">
                        Tidak Ada Perubahan Data
                    </strong>
                    <div style="font-size: 0.8rem; color: #475569; line-height: 1.4;">
                        Seluruh data dokumen Purchase Order sama dengan data awal. Tidak ada proses perubahan yang perlu disimpan ke sistem.
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                ${window.poShowUrl ? `
                    <a href="${showUrl}" class="btn btn-sm" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <span>Kembali ke Detail PO</span>
                    </a>
                ` : ''}
                <button type="button" onclick="this.closest('.po-validation-banner').style.display='none'" style="background: none; border: none; color: #94a3b8; font-size: 1.25rem; line-height: 1; cursor: pointer; padding: 0.1rem 0.35rem;" title="Tutup">
                    &times;
                </button>
            </div>
        </div>
    `;
    banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/* =========================================================================
 * CHANGE DETECTION (NO-CHANGE GUARD)
 * ========================================================================= */
let initialPoEditSnapshot = null;

function capturePoEditFormSnapshot() {
    const supId = document.getElementById('supplier_id')?.value?.trim() || '';
    const supName = document.getElementById('disp_sup_name')?.innerText?.trim().toLowerCase() || '';

    const header = {
        po_tgl: document.getElementById('po_tgl')?.value?.trim() || '',
        po_no: document.getElementById('po_no')?.value?.trim() || '',
        supplier_id: supId,
        supplier_nm: supName,
        gudang_id: document.getElementById('gudang_id')?.value?.trim() || '',
        approver_id: document.getElementById('approver_id')?.value?.trim() || '',
        term_of_payment: document.getElementById('term_of_payment')?.value?.trim() || '',
        remark_txt: document.getElementById('remark_txt')?.value?.trim() || '',
        franco_txt: document.getElementById('franco_txt')?.value?.trim() || '',
        tgl_estimasi_datang: document.getElementById('tgl_estimasi_datang')?.value?.trim() || '',
        is_harga_termasuk_pajak: Boolean(document.getElementById('is_harga_termasuk_pajak')?.checked),
        diskon_persen: parseFloat(document.getElementById('global_diskon_persen')?.value || 0) || 0,
        potongan_nominal: parseFloat(document.getElementById('global_potongan_nominal')?.value || 0) || 0,
        pajak_id: document.getElementById('global_pajak_id')?.value?.trim() || ''
    };

    const items = [];
    document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach(row => {
        const podtlId = row.querySelector('input[name*="[podtl_id]"]')?.value || '';
        const bSelect = row.querySelector('.item-barang-select');
        const bHidden = row.querySelector('input[name*="[barang_id]"]');
        const barangId = (bSelect ? bSelect.value : (bHidden ? bHidden.value : '')) || '';
        const qty = parseFloat(row.querySelector('.input-qty')?.value || 0) || 0;
        const harga = parseFloat(row.querySelector('.input-harga')?.value || 0) || 0;
        const isTaxInc = Boolean(row.querySelector('.item-include-tax')?.checked);
        const catatan = row.querySelector('input[name*="[catatan_txt]"]')?.value?.trim() || '';

        items.push({
            podtl_id: podtlId,
            barang_id: barangId,
            pesan_qty: qty,
            harga_nominal: harga,
            is_harga_termasuk_pajak: isTaxInc,
            catatan_txt: catatan
        });
    });

    return { header, items };
}

function isPoEditFormDirty() {
    if (!initialPoEditSnapshot) return true;

    const current = capturePoEditFormSnapshot();
    const init = initialPoEditSnapshot;

    // 1. Cek Header
    if (current.header.po_tgl !== init.header.po_tgl) return true;
    if (current.header.po_no !== init.header.po_no) return true;

    // Pengecekan Supplier: jika ID berbeda, periksa apakah nama supplier sama (misal memilih supplier dengan nama identik)
    if (current.header.supplier_id !== init.header.supplier_id) {
        if (!current.header.supplier_nm || !init.header.supplier_nm || current.header.supplier_nm !== init.header.supplier_nm) {
            return true;
        }
    }

    if (current.header.gudang_id !== init.header.gudang_id) return true;
    if (current.header.approver_id !== init.header.approver_id) return true;
    if (current.header.term_of_payment !== init.header.term_of_payment) return true;
    if (current.header.remark_txt !== init.header.remark_txt) return true;
    if (current.header.franco_txt !== init.header.franco_txt) return true;
    if (current.header.tgl_estimasi_datang !== init.header.tgl_estimasi_datang) return true;
    if (current.header.is_harga_termasuk_pajak !== init.header.is_harga_termasuk_pajak) return true;
    if (Math.abs(current.header.diskon_persen - init.header.diskon_persen) > 0.0001) return true;
    if (Math.abs(current.header.potongan_nominal - init.header.potongan_nominal) > 0.01) return true;
    if (current.header.pajak_id !== init.header.pajak_id) return true;

    // 2. Cek Items
    if (current.items.length !== init.items.length) return true;

    for (let i = 0; i < current.items.length; i++) {
        const c = current.items[i];
        const o = init.items[i];

        if (c.podtl_id !== o.podtl_id) return true;
        if (c.barang_id !== o.barang_id) return true;
        if (Math.abs(c.pesan_qty - o.pesan_qty) > 0.0001) return true;
        if (Math.abs(c.harga_nominal - o.harga_nominal) > 0.01) return true;
        if (c.is_harga_termasuk_pajak !== o.is_harga_termasuk_pajak) return true;
        if (c.catatan_txt !== o.catatan_txt) return true;
    }

    return false;
}

function setPoEditSubmitButtonLoading(isLoading) {
    const btn = document.getElementById('btnSubmitEditPo');
    if (!btn) return;

    if (isLoading) {
        isPoEditFormSubmitting = true;
        btn.disabled = true;
        btn.classList.add('is-loading');
        if (!btn.dataset.originalHtml) {
            btn.dataset.originalHtml = btn.innerHTML;
        }
        btn.innerHTML = `
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="animation: spin 1s linear infinite; display: inline-block;">
                <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Menyimpan Perubahan PO...</span>
        `;
    } else {
        isPoEditFormSubmitting = false;
        btn.disabled = false;
        btn.classList.remove('is-loading');
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
        }
    }
}

function validatePoEditForm() {
    clearPoEditValidationErrors();

    const errors = [];
    let firstErrorElement = null;

    function markError(element, message) {
        if (!element) return;
        element.classList.add('po-input-error');

        const clearHandler = function () {
            element.classList.remove('po-input-error');
            element.removeEventListener('input', clearHandler);
            element.removeEventListener('change', clearHandler);
            element.removeEventListener('click', clearHandler);
        };
        element.addEventListener('input', clearHandler);
        element.addEventListener('change', clearHandler);
        element.addEventListener('click', clearHandler);

        errors.push(message);
        if (!firstErrorElement) {
            firstErrorElement = element;
        }
    }

    // 1. Validasi Tanggal PO
    const tglInput = document.getElementById('po_tgl');
    if (!tglInput || !tglInput.value) {
        markError(tglInput, 'Tanggal PO wajib diisi.');
    }

    // 2. Validasi Nomor PO
    const noInput = document.getElementById('po_no');
    if (!noInput || !noInput.value.trim()) {
        markError(noInput, 'Nomor PO Resmi wajib diisi.');
    }

    // 3. Validasi Supplier (jika tidak disabled)
    const supSelect = document.getElementById('supplier_id');
    const supSearchInput = document.getElementById('supplier_search_input');
    if (supSelect && !supSelect.disabled && !supSelect.value) {
        markError(supSearchInput || supSelect, 'Supplier Mitra wajib dipilih.');
    }

    // 4. Validasi Penyetuju (Approver)
    const approverSelect = document.getElementById('approver_id');
    if (!approverSelect || !approverSelect.value) {
        markError(approverSelect, 'Atasan Penyetuju (Approver) wajib dipilih.');
    }

    // 5. Validasi Gudang (jika tidak disabled)
    const gudangSelect = document.getElementById('gudang_id');
    if (gudangSelect && !gudangSelect.disabled && !gudangSelect.value) {
        markError(gudangSelect, 'Gudang Tujuan Masuk wajib dipilih.');
    }

    // 6. Validasi Baris Barang
    const itemRows = document.querySelectorAll('#tbodyPoItems tr.po-item-row');
    if (itemRows.length === 0) {
        errors.push('Minimal harus ada 1 baris item barang dalam Purchase Order.');
    } else {
        itemRows.forEach((row, idx) => {
            const rowNum = idx + 1;
            const barangSelect = row.querySelector('.item-barang-select');
            const barangHidden = row.querySelector('input[name*="[barang_id]"]');
            const barangVal = barangSelect ? barangSelect.value : (barangHidden ? barangHidden.value : '');
            const qtyInput = row.querySelector('.input-qty');
            const hargaInput = row.querySelector('.input-harga');

            if (!barangVal) {
                markError(barangSelect || row, `Baris #${rowNum}: Komoditas / Barang wajib dipilih.`);
            }

            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const minAllowed = parseFloat(qtyInput ? qtyInput.getAttribute('min') : 0) || 0;

            if (!qtyInput || qty <= 0 || isNaN(qty)) {
                markError(qtyInput, `Baris #${rowNum}: Kuantitas pesan harus lebih besar dari 0.`);
            } else if (minAllowed > 0 && qty < minAllowed) {
                markError(qtyInput, `Baris #${rowNum}: Kuantitas pesan (${qty}) tidak boleh lebih kecil dari kuantitas yang sudah diterima di gudang (${minAllowed}).`);
            }

            const harga = parseFloat(hargaInput ? hargaInput.value : 0);
            if (!hargaInput || harga < 0 || isNaN(harga)) {
                markError(hargaInput, `Baris #${rowNum}: Harga satuan tidak boleh bernilai negatif.`);
            }
        });
    }

    return {
        isValid: errors.length === 0,
        errors: errors,
        firstErrorElement: firstErrorElement
    };
}

function initPreviousMonthProtection() {
    const formEditPo = document.getElementById('formEditPo');
    if (!formEditPo) return;

    let previousMonthConfirmed = false;

    formEditPo.addEventListener('submit', function (e) {
        // Proteksi 1: Cegah klik ganda / double submit
        if (isPoEditFormSubmitting) {
            e.preventDefault();
            return false;
        }

        // Proteksi 2: Jalankan validasi komprehensif form edit
        const validation = validatePoEditForm();
        if (!validation.isValid) {
            e.preventDefault();
            showPoEditValidationBanner(validation.errors);
            if (validation.firstErrorElement) {
                validation.firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                try { validation.firstErrorElement.focus(); } catch (err) {}
            }
            return false;
        }

        clearPoEditValidationErrors();

        // Proteksi 3: Pengecekan Perubahan Data (No Changes Detection)
        if (!isPoEditFormDirty()) {
            e.preventDefault();
            showPoEditNoChangeBanner();
            return false;
        }

        if (previousMonthConfirmed) {
            setPoEditSubmitButtonLoading(true);
            return true;
        }

        const poTglInput = document.getElementById('po_tgl');
        const poNoInput = document.getElementById('po_no');
        const poTglVal = poTglInput ? poTglInput.value : '';
        const poNoVal = poNoInput ? poNoInput.value.trim() : '';
        const originalTglVal = window.originalPoTgl || '';
        const originalPoNoVal = window.originalPoNo || '';
        const currentServerYM = window.currentServerYearMonth || (new Date().toISOString().slice(0, 7));

        const checkDate = poTglVal || originalTglVal;
        const docYM = checkDate ? checkDate.slice(0, 7) : '';
        const isPreviousMonth = Boolean(docYM && docYM < currentServerYM);

        const currentParts = parsePoNoParts(poNoVal);
        const baselineParts = parsePoNoParts(lastSystemSuggestedPoNo || originalPoNoVal);
        const isManualSequence = Boolean(currentParts && baselineParts && (currentParts.seq !== baselineParts.seq));

        if (isPreviousMonth || isManualSequence) {
            e.preventDefault();

            let title = '';
            let subtitle = '';
            let alerts = [];

            if (isPreviousMonth && isManualSequence) {
                title = 'Peringatan: Dokumen Periode Sebelumnya & Nomor Urut Manual';
                subtitle = 'Dua penyesuaian khusus terdeteksi pada pembaruan dokumen ini';
            } else if (isPreviousMonth) {
                title = 'Konfirmasi Perubahan Dokumen Periode Sebelumnya';
                subtitle = 'Dokumen berasal dari bulan/periode yang telah lewat';
            } else {
                title = 'Peringatan: Nomor Urut PO Telah Diubah Manual';
                subtitle = 'Nomor urut berbeda dari standar nomor resmi sistem';
            }

            if (isPreviousMonth) {
                alerts.push(`
                    <div style="margin-bottom: 0.45rem;">
                        <strong>1. Periode Bulan Sebelumnya:</strong> Dokumen Purchase Order ini tercatat pada periode <strong>${formatTanggalIndo(checkDate)}</strong>, sedangkan periode aktif sistem saat ini adalah <strong>${formatTanggalIndo(new Date().toISOString().slice(0, 10))}</strong>. Mengubah data pada dokumen periode lalu berpotensi memengaruhi laporan penutupan buku (closing) dan rekonsiliasi pengadaan gudang.
                    </div>
                `);
            }

            if (isManualSequence) {
                alerts.push(`
                    <div style="margin-bottom: 0.45rem;">
                        <strong>${isPreviousMonth ? '2. ' : ''}Nomor Urut Manual:</strong> Anda memasukkan nomor urut <strong>${currentParts.seq}</strong> (dokumen: <code>${poNoVal}</code>), sedangkan nomor urut yang disarankan sistem/sebelumnya adalah <strong>${baselineParts.seq}</strong> (dokumen: <code>${lastSystemSuggestedPoNo || originalPoNoVal}</code>). Mengubah nomor urut secara manual dapat menyebabkan nomor loncat atau potensi duplikasi arsip.
                    </div>
                `);
            }

            openPreviousMonthModal({
                poNo: poNoVal,
                poTgl: checkDate,
                title: title,
                subtitle: subtitle,
                contentHtml: alerts.join(''),
                question: 'Apakah Anda yakin data perubahan ini sudah benar dan ingin tetap menyimpannya?'
            });
            return false;
        }

        // Lolos seluruh validasi: kunci tombol simpan dan tampilkan loading state (Anti Double-Submit)
        setPoEditSubmitButtonLoading(true);
    });

    const btnConfirm = document.getElementById('btnConfirmSubmitPreviousMonth');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            previousMonthConfirmed = true;
            closePreviousMonthModal();
            setPoEditSubmitButtonLoading(true);
            formEditPo.submit();
        });
    }
}

// Hitung total awal saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
    initSupplierSearchEvents();
    if (window.activeSupplierId) {
        selectSupplier(window.activeSupplierId);
    }
    syncHeaderTaxLabel();
    const headerIncCheck = document.getElementById('is_harga_termasuk_pajak');
    if (headerIncCheck && headerIncCheck.checked) {
        onHeaderTaxInclusiveToggle(headerIncCheck);
    }
    calculateAllTotals();
    initPoDateNumberSync();
    initPreviousMonthProtection();

    // Rekam snapshot data awal setelah seluruh rendering & kalkulasi selesai
    setTimeout(() => {
        initialPoEditSnapshot = capturePoEditFormSnapshot();
    }, 150);
});
