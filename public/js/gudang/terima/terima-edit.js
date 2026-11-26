/**
 * =========================================================================
 * ERP PT MIRASA - TERIMA BARANG (GRN) EDIT JAVASCRIPT
 * =========================================================================
 * Fitur Utama:
 * 1. Pengecekan Perubahan Data (No Change Detection / Dirty Check)
 * 2. Proteksi & Peringatan Selisih Kuantitas PO (Over PO & Under PO / Parsial)
 * 3. Proteksi Pengeditan Dokumen Periode Bulan Sebelumnya
 * 4. Anti Double-Submit & Real-Time Calculation
 * =========================================================================
 */

const isEditMode = true;
const suppliersData = window.suppliersData || [];

let activeModalSupplierTab = 'ALL';
let highlightedSupIndex = -1;

const supSearchInput = document.getElementById('supplier_search_input');
const supDropdownList = document.getElementById('supplier_dropdown_list');
const supHiddenSelect = document.getElementById('supplier_id');
const selectedSupCard = document.getElementById('selected_supplier_card');
const supSearchWrapper = document.getElementById('supplier_search_wrapper');
const modalSearchInput = document.getElementById('modal_supplier_search');
const modalTbody = document.getElementById('supplier_modal_tbody');
const modalCountText = document.getElementById('modal_supplier_count_text');

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
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
    } else if (category === 'BP') {
        catBg = '#ffedd5';
        catColor = '#9a3412';
        label = 'Penolong Industri';
    }

    return {
        bg: catBg,
        color: catColor,
        label: label,
        html: `<span class="badge" style="background:${catBg}; color:${catColor}; font-size:0.725rem; font-weight:600; padding: 0.15rem 0.45rem; border-radius: 4px; border: 1px solid rgba(0,0,0,0.06); white-space:nowrap;">${escapeHtml(label)}</span>`
    };
}

// -------------------------------------------------------------------------
// SUPPLIER PICKER MODAL
// -------------------------------------------------------------------------
let modalCurrentPage = 1;
const modalPerPage = 8;

function changeSupplierModalPage(delta) {
    modalCurrentPage += delta;
    renderSupplierModalTable();
}

function openSupplierModal() {
    if (supDropdownList) supDropdownList.style.display = 'none';
    openModal('modalPilihSupplier');
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
    closeModal('modalPilihSupplier');
}

function filterSupplierModalTab(tab, btn) {
    activeModalSupplierTab = tab;
    modalCurrentPage = 1;
    document.querySelectorAll('.sup-modal-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderSupplierModalTable();
}

function renderSupplierModalTable() {
    if (!modalTbody || !modalSearchInput) return;
    const query = modalSearchInput.value.trim().toLowerCase();
    const filtered = suppliersData.filter(s => {
        const matchesTab = (activeModalSupplierTab === 'ALL' || s.category === activeModalSupplierTab);
        const matchesText = !query ||
            (s.name && s.name.toLowerCase().includes(query)) ||
            (s.code && s.code.toLowerCase().includes(query)) ||
            (s.contact && s.contact.toLowerCase().includes(query)) ||
            (s.address && s.address.toLowerCase().includes(query)) ||
            (s.categoryName && s.categoryName.toLowerCase().includes(query));
        return matchesTab && matchesText;
    });

    const total = filtered.length;
    const totalPages = Math.ceil(total / modalPerPage) || 1;
    if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;
    if (modalCurrentPage < 1) modalCurrentPage = 1;

    const start = (modalCurrentPage - 1) * modalPerPage;
    const paged = filtered.slice(start, start + modalPerPage);

    if (modalCountText) {
        modalCountText.innerText = `Menampilkan ${paged.length} dari ${total} Supplier (Halaman ${modalCurrentPage}/${totalPages})`;
    }

    if (paged.length === 0) {
        modalTbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding: 2.5rem 1rem; color: #94a3b8;">
                    Tidak ada supplier yang cocok dengan kata kunci "<strong>${escapeHtml(query)}</strong>"
                </td>
            </tr>
        `;
        return;
    }

    modalTbody.innerHTML = paged.map((s, idx) => {
        const badge = getCategoryBadge(s.category, s.categoryName);
        return `
            <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer;" onclick="selectSupplierFromModal(${s.id})" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <td style="padding: 0.65rem 0.75rem; color: #64748b; font-size: 0.75rem; text-align: center;">${start + idx + 1}</td>
                <td style="padding: 0.65rem 0.75rem;">
                    <strong style="color: #0f172a; font-size: 0.85rem; display: block;">${escapeHtml(s.name)}</strong>
                    <span style="font-family: monospace; font-size: 0.725rem; color: #64748b;">${escapeHtml(s.code)}</span>
                </td>
                <td style="padding: 0.65rem 0.75rem;">${badge.html}</td>
                <td style="padding: 0.65rem 0.75rem; font-size: 0.775rem; color: #475569;">${escapeHtml(s.contact || '-')}</td>
                <td style="padding: 0.65rem 0.75rem; text-align: right;">
                    <button type="button" class="btn btn-sm" style="background: #0f172a; color: #fff; font-size: 0.725rem; font-weight: 600; padding: 0.2rem 0.65rem; border-radius: 4px; border: none;">
                        Pilih
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function selectSupplierFromModal(id) {
    selectSupplier(id);
    closeSupplierModal();
}

function openModal(modalId) {
    const m = document.getElementById(modalId);
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const m = document.getElementById(modalId);
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = '';
    }
}

window.openSupplierModal = openSupplierModal;
window.closeSupplierModal = closeSupplierModal;
window.filterSupplierModalTab = filterSupplierModalTab;
window.changeSupplierModalPage = changeSupplierModalPage;
window.selectSupplierFromModal = selectSupplierFromModal;

// -------------------------------------------------------------------------
// SUPPLIER AUTOCOMPLETE & SELECTION
// -------------------------------------------------------------------------
function selectSupplier(supplierId) {
    const sup = suppliersData.find(s => s.id == supplierId);
    if (!sup) return;

    if (supHiddenSelect) supHiddenSelect.value = sup.id;

    const badge = getCategoryBadge(sup.category, sup.categoryName);
    const dispName = document.getElementById('disp_sup_name');
    const dispCode = document.getElementById('disp_sup_code');
    const catBadgeSpan = document.getElementById('disp_sup_cat_badge');
    const dispContact = document.getElementById('disp_sup_contact');

    if (dispName) dispName.innerText = sup.name;
    if (dispCode) dispCode.innerText = sup.code;
    if (catBadgeSpan) catBadgeSpan.innerHTML = badge.html;
    if (dispContact) {
        dispContact.innerText =
            (sup.contact && sup.contact !== '-' ? 'Telp: ' + sup.contact : '') +
            (sup.address ? (sup.contact && sup.contact !== '-' ? ' | ' : '') + 'Alamat: ' + sup.address : '');
    }

    if (selectedSupCard) selectedSupCard.style.display = 'flex';
    if (supSearchWrapper) supSearchWrapper.style.display = 'none';
    if (supDropdownList) supDropdownList.style.display = 'none';
}

function clearSelectedSupplier() {
    if (supHiddenSelect) supHiddenSelect.value = '';
    if (selectedSupCard) selectedSupCard.style.display = 'none';
    if (supSearchWrapper) supSearchWrapper.style.display = 'block';
    if (supSearchInput) {
        supSearchInput.value = '';
        renderSupplierDropdown('');
        supSearchInput.focus();
    }
    if (supDropdownList) supDropdownList.style.display = 'block';
}

function renderSupplierDropdown(query) {
    if (!supDropdownList) return;
    const q = (query || '').toLowerCase().trim();
    const filtered = suppliersData.filter(s =>
        !q ||
        (s.name && s.name.toLowerCase().includes(q)) ||
        (s.code && s.code.toLowerCase().includes(q))
    ).slice(0, 10);

    highlightedSupIndex = -1;

    if (filtered.length === 0) {
        supDropdownList.innerHTML = `
            <div style="padding: 0.75rem; text-align: center; color: #94a3b8; font-size: 0.8rem;">
                Tidak ada supplier "${escapeHtml(q)}"
            </div>
        `;
        return;
    }

    supDropdownList.innerHTML = filtered.map(s => {
        const badge = getCategoryBadge(s.category, s.categoryName);
        return `
            <div class="sup-dropdown-item" data-id="${s.id}" onclick="selectSupplier(${s.id})"
                style="padding: 0.5rem 0.75rem; cursor: pointer; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;"
                onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">
                <div style="min-width: 0;">
                    <strong style="color: #0f172a; font-size: 0.825rem; display: block;">${escapeHtml(s.name)}</strong>
                    <span style="font-family: monospace; font-size: 0.7rem; color: #64748b;">${escapeHtml(s.code)}</span>
                </div>
                ${badge.html}
            </div>
        `;
    }).join('');
}

window.selectSupplier = selectSupplier;
window.clearSelectedSupplier = clearSelectedSupplier;

if (supSearchInput) {
    supSearchInput.addEventListener('input', function () {
        renderSupplierDropdown(this.value);
        if (supDropdownList) supDropdownList.style.display = 'block';
    });

    supSearchInput.addEventListener('focus', function () {
        renderSupplierDropdown(this.value);
        if (supDropdownList) supDropdownList.style.display = 'block';
    });
}

document.addEventListener('click', function (e) {
    if (supDropdownList && !supDropdownList.contains(e.target) && !supSearchInput?.contains(e.target)) {
        supDropdownList.style.display = 'none';
    }
});

// -------------------------------------------------------------------------
// TABLE CALCULATION & PO COMPARISON REAL-TIME BADGES
// -------------------------------------------------------------------------
let terimaRowIndex = window.selectedPoDetailsCount || 10;

function calculateTotalTerima() {
    let totalBruto = 0;
    let totalReject = 0;
    let activeItemCount = 0;

    document.querySelectorAll('.terima-row').forEach(row => {
        const selectElem = row.querySelector('.item-barang') || row.querySelector('.item-barang-id');
        const terimaInput = row.querySelector('.item-terima-qty');
        const rejectInput = row.querySelector('.item-reject-qty');

        if (selectElem && selectElem.value) {
            activeItemCount++;
        }

        const terimaQty = Math.max(0, parseFloat(terimaInput?.value) || 0);
        const rejectQty = Math.max(0, parseFloat(rejectInput?.value) || 0);
        const nettoQty = Math.max(0, terimaQty - rejectQty);

        totalBruto += terimaQty;
        totalReject += rejectQty;

        // Update display netto di baris
        const nettoDisplay = row.querySelector('.row-netto');
        if (netorderDisplay) {
            netorderDisplay.innerText = nettoQty.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 4
            });
        }

        // Pengecekan Komparasi terhadap PO (Over PO vs Under PO / Parsial vs Sesuai PO)
        const pesanPo = parseFloat(row.dataset.pesan || 0);
        const statusBadgeContainer = row.querySelector('.row-po-status-badge') || row.querySelector('.row-over-po-badge');

        if (statusBadgeContainer && pesanPo > 0) {
            const diff = nettoQty - pesanPo;
            if (diff > 0.001) {
                // Over PO
                statusBadgeContainer.style.display = 'block';
                statusBadgeContainer.innerHTML = `
                    <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Kuantitas fisik melebihi pesanan PO">
                        Over PO (+${diff.toFixed(2)})
                    </span>
                `;
            } else if (diff < -0.001) {
                // Under PO / Parsial
                const kurang = Math.abs(diff);
                statusBadgeContainer.style.display = 'block';
                statusBadgeContainer.innerHTML = `
                    <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Penerimaan baru sebagian / kurang dari pesanan PO">
                        Kurang / Parsial (-${kurang.toFixed(2)})
                    </span>
                `;
            } else {
                // Sesuai PO
                statusBadgeContainer.style.display = 'block';
                statusBadgeContainer.innerHTML = `
                    <span class="badge" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Kuantitas fisik tepat sesuai pesanan PO">
                        Sesuai PO (100%)
                    </span>
                `;
            }
        }
    });

    const totalNetto = Math.max(0, totalBruto - totalReject);

    // Update Bottom Summary Cards
    const elSideBruto = document.getElementById('sideTotalBruto');
    const elSideAfkir = document.getElementById('sideTotalAfkir');
    const elSideNetto = document.getElementById('sideTotalNetto');

    if (elSideBruto) elSideBruto.innerText = totalBruto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 }) + ' KG';
    if (elSideAfkir) elSideAfkir.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 }) + ' KG';
    if (elSideNetto) elSideNetto.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 }) + ' KG';

    // Update TFoot Displays
    const tfootBruto = document.getElementById('totalBrutoQtyDisplay');
    const tfootReject = document.getElementById('totalRejectQtyDisplay');
    const tfootNetto = document.getElementById('totalNettoQtyDisplay');

    if (tfootBruto) tfootBruto.innerText = totalBruto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
    if (tfootReject) tfootReject.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
    if (tfootNetto) tfootNetto.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });

    // Update Top Header Live Bar
    const topGrand = document.getElementById('topLiveGrandTotal');
    const topCount = document.getElementById('topLiveItemCount');
    if (topGrand) topGrand.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + ' KG';
    if (topCount) topCount.innerText = activeItemCount + ' Item';
}
window.calculateTotalTerima = calculateTotalTerima;

function addTerimaRow(focusNew = false) {
    const container = document.getElementById('terimaItemsContainer');
    if (!container) return null;

    const template = document.getElementById('terimaRowTemplate');
    let tr;

    if (template) {
        const tempDiv = document.createElement('tbody');
        tempDiv.innerHTML = template.innerHTML
            .replace(/__INDEX__/g, terimaRowIndex)
            .replace(/__NUM__/g, terimaRowIndex + 1);
        tr = tempDiv.firstElementChild;
    } else {
        tr = document.createElement('tr');
        tr.className = 'terima-row';
        tr.dataset.index = terimaRowIndex;
        tr.dataset.sisa = 0;
        tr.dataset.pesan = 0;
        tr.innerHTML = `<td class="row-num">${terimaRowIndex + 1}</td>`;
    }

    container.appendChild(tr);
    terimaRowIndex++;
    updateTerimaRowNumbers();
    calculateTotalTerima();

    if (focusNew) {
        const select = tr.querySelector('.item-barang');
        if (select) select.focus();
    }
    return tr;
}
window.addTerimaRow = addTerimaRow;

function removeTerimaRow(btn) {
    const rows = document.querySelectorAll('.terima-row');
    if (rows.length <= 1) {
        alert('Minimal harus ada 1 baris item barang yang diterima.');
        return;
    }
    btn.closest('tr').remove();
    updateTerimaRowNumbers();
    calculateTotalTerima();
}
window.removeTerimaRow = removeTerimaRow;

function updateTerimaRowNumbers() {
    document.querySelectorAll('.terima-row').forEach((row, idx) => {
        const numElem = row.querySelector('.row-num');
        if (numElem) numElem.innerText = idx + 1;
    });
}

function updateTerimaSatuanAndBatch(selectElem) {
    const row = selectElem.closest('tr');
    const selectedOption = selectElem.options ? selectElem.options[selectElem.selectedIndex] : null;
    const batchInput = row.querySelector('.item-batch');
    const satuanDisplay = row.querySelector('.row-satuan');

    if (selectedOption && selectedOption.value) {
        const satuan = selectedOption.getAttribute('data-satuan') || '-';
        const acronym = selectedOption.getAttribute('data-acronym') || 'BRG';

        if (satuanDisplay) satuanDisplay.innerText = satuan;

        if (batchInput && (!batchInput.value || batchInput.value.endsWith('-'))) {
            batchInput.value = `${acronym}-`;
        }
    }
    calculateTotalTerima();
}
window.updateTerimaSatuanAndBatch = updateTerimaSatuanAndBatch;

// -------------------------------------------------------------------------
// 1. FITUR PENGECEKAN PERUBAHAN DATA (DIRTY CHECK / NO CHANGE DETECTION)
// -------------------------------------------------------------------------
let initialTerimaSerialized = '';
let initialTerimaEditSnapshot = null;

function getTerimaFormSerialized() {
    const form = document.getElementById('formTerima');
    if (!form) return '';
    try {
        const formData = new FormData(form);
        formData.delete('_token');
        formData.delete('_method');
        formData.delete('alasan_edit');
        formData.delete('catatan_lengkap_display');
        return new URLSearchParams(formData).toString();
    } catch (e) {
        return '';
    }
}

function captureTerimaEditFormSnapshot() {
    const supId = document.getElementById('supplier_id')?.value?.trim() || '';
    const supName = document.getElementById('disp_sup_name')?.innerText?.trim().toLowerCase() || '';

    const header = {
        terima_tgl: document.getElementById('terima_tgl')?.value?.trim() || '',
        terima_no: document.getElementById('terima_no')?.value?.trim() || '',
        supplier_id: supId,
        supplier_nm: supName,
        gudang_id: document.getElementById('gudang_id')?.value?.trim() || '',
        suratjalan_no: document.getElementById('suratjalan_no')?.value?.trim() || '',
        tgl_jatuh_tempo: document.getElementById('tgl_jatuh_tempo')?.value?.trim() || '',
        catatan_txt: document.getElementById('catatan_txt')?.value?.trim() || '',
    };

    const items = [];
    document.querySelectorAll('#terimaItemsContainer tr.terima-row').forEach(r => {
        const bId = r.querySelector('.item-barang-id')?.value || r.querySelector('.item-barang')?.value || '';
        const batch = r.querySelector('.item-batch')?.value?.trim() || '';
        const exp = r.querySelector('input[name*="[expired_tgl]"]')?.value?.trim() || '';
        const grade = r.querySelector('select[name*="[grade_cd]"]')?.value?.trim() || '';
        const tQty = parseFloat(r.querySelector('.item-terima-qty')?.value) || 0;
        const rQty = parseFloat(r.querySelector('.item-reject-qty')?.value) || 0;

        items.push({
            barang_id: String(bId).trim(),
            batch_no: batch,
            expired_tgl: exp,
            grade_cd: grade,
            terima_qty: Math.round(tQty * 10000) / 10000,
            reject_qty: Math.round(rQty * 10000) / 10000
        });
    });

    return { header, items };
}

function isTerimaEditFormDirty() {
    // 1. Prioritaskan perbandingan serialisasi FormData (Presisi 100% tanpa delay/kesalahan tipe data)
    const currentSerialized = getTerimaFormSerialized();
    if (initialTerimaSerialized && currentSerialized) {
        return currentSerialized !== initialTerimaSerialized;
    }

    // 2. Fallback perbandingan snapshot objek jika FormData tidak tersedia
    if (!initialTerimaEditSnapshot) return false;

    const current = captureTerimaEditFormSnapshot();
    const init = initialTerimaEditSnapshot;

    if (current.header.terima_tgl !== init.header.terima_tgl) return true;
    if (current.header.terima_no !== init.header.terima_no) return true;
    if (current.header.gudang_id !== init.header.gudang_id) return true;
    if (current.header.suratjalan_no !== init.header.suratjalan_no) return true;
    if (current.header.tgl_jatuh_tempo !== init.header.tgl_jatuh_tempo) return true;
    if (current.header.catatan_txt !== init.header.catatan_txt) return true;

    if (current.header.supplier_id !== init.header.supplier_id) {
        if (!current.header.supplier_nm || !init.header.supplier_nm || current.header.supplier_nm !== init.header.supplier_nm) {
            return true;
        }
    }

    if (current.items.length !== init.items.length) return true;

    for (let i = 0; i < current.items.length; i++) {
        const c = current.items[i];
        const o = init.items[i];

        if (c.barang_id !== o.barang_id) return true;
        if (c.batch_no !== o.batch_no) return true;
        if (c.expired_tgl !== o.expired_tgl) return true;
        if (c.grade_cd !== o.grade_cd) return true;
        if (Math.abs(c.terima_qty - o.terima_qty) > 0.0001) return true;
        if (Math.abs(c.reject_qty - o.reject_qty) > 0.0001) return true;
    }

    return false;
}

function showTerimaEditNoChangeBanner() {
    const banner = document.getElementById('terimaValidationBanner');
    if (!banner) return;

    banner.className = 'po-validation-banner';
    banner.style.display = 'block';
    banner.style.background = '#f8fafc';
    banner.style.border = '1.5px solid #cbd5e1';
    banner.style.borderLeft = '4px solid #64748b';
    banner.style.borderRadius = '8px';
    banner.style.padding = '0.75rem 1rem';
    banner.style.marginBottom = '1rem';

    const showUrl = window.terimaShowUrl || '#';

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
                        Seluruh data dokumen Penerimaan Barang sama dengan data awal. Tidak ada proses perubahan yang perlu disimpan ke sistem.
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                <a href="${showUrl}" class="btn btn-sm" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <span>Kembali ke Detail Penerimaan</span>
                </a>
                <button type="button" onclick="document.getElementById('terimaValidationBanner').style.display='none'" style="background: none; border: none; color: #94a3b8; font-size: 1.25rem; line-height: 1; cursor: pointer; padding: 0.1rem 0.35rem;" title="Tutup">
                    &times;
                </button>
            </div>
        </div>
    `;
    banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// -------------------------------------------------------------------------
// 2. FITUR PERINGATAN SELISIH KUANTITAS PO (OVER PO & UNDER PO / PARSIAL)
// -------------------------------------------------------------------------
let poDiscrepancyConfirmed = false;

function getPoDiscrepancies() {
    const discrepancies = [];

    document.querySelectorAll('.terima-row').forEach(row => {
        const pesanPo = parseFloat(row.dataset.pesan || 0);
        if (pesanPo <= 0) return;

        const namaBarang = row.querySelector('.item-barang-name')?.innerText?.trim() || 
            (row.querySelector('.item-barang option:checked')?.text?.trim() || 'Barang');
        const satuan = row.querySelector('.row-satuan')?.innerText?.trim() || 'Satuan';

        const terimaQty = Math.max(0, parseFloat(row.querySelector('.item-terima-qty')?.value) || 0);
        const rejectQty = Math.max(0, parseFloat(row.querySelector('.item-reject-qty')?.value) || 0);
        const netto = Math.max(0, terimaQty - rejectQty);

        const diff = netto - pesanPo;

        if (diff > 0.001) {
            // Over PO
            discrepancies.push({
                nama: namaBarang,
                satuan: satuan,
                pesanPo: pesanPo,
                netto: netto,
                diff: diff,
                tipe: 'OVER'
            });
        } else if (diff < -0.001) {
            // Under PO / Parsial
            discrepancies.push({
                nama: namaBarang,
                satuan: satuan,
                pesanPo: pesanPo,
                netto: netto,
                diff: diff,
                tipe: 'PARSIAL'
            });
        }
    });

    return discrepancies;
}

function openConfirmPoDiscrepancyModal(discrepancies) {
    const modal = document.getElementById('modalConfirmPoDiscrepancyTerima');
    const tbody = document.getElementById('poDiscrepancyItemsListBody');
    const modalTitle = document.getElementById('poDiscrepancyModalTitle');
    const modalSubtitle = document.getElementById('poDiscrepancyModalSubtitle');
    const iconWrap = document.getElementById('poDiscrepancyIconWrap');
    const introText = document.getElementById('poDiscrepancyIntroText');
    const btnProceed = document.getElementById('btnProceedPoDiscrepancy');

    if (!modal || !tbody) return;

    const hasOver = discrepancies.some(d => d.tipe === 'OVER');
    const hasUnder = discrepancies.some(d => d.tipe === 'PARSIAL');

    if (hasOver && !hasUnder) {
        modalTitle.innerText = 'Konfirmasi Penerimaan Over PO';
        modalSubtitle.innerText = 'Kuantitas fisik diterima melebihi pesanan Purchase Order';
        if (iconWrap) {
            iconWrap.style.background = '#d97706';
            iconWrap.style.boxShadow = '0 2px 6px rgba(217, 119, 6, 0.3)';
        }
        if (btnProceed) {
            btnProceed.style.background = '#d97706';
            btnProceed.innerText = 'Lanjutkan Simpan Over PO';
        }
    } else if (hasUnder && !hasOver) {
        modalTitle.innerText = 'Konfirmasi Penerimaan Parsial (Kurang dari PO)';
        modalSubtitle.innerText = 'Kuantitas fisik diterima baru sebagian / belum lengkap dari pesanan PO';
        if (iconWrap) {
            iconWrap.style.background = '#0284c7';
            iconWrap.style.boxShadow = '0 2px 6px rgba(2, 132, 199, 0.3)';
        }
        if (btnProceed) {
            btnProceed.style.background = '#0284c7';
            btnProceed.innerText = 'Lanjutkan Simpan Penerimaan Parsial';
        }
    } else {
        modalTitle.innerText = 'Konfirmasi Penyesuaian Kuantitas PO';
        modalSubtitle.innerText = 'Terdapat penerimaan yang melebihi (Over) dan kurang dari (Parsial) pesanan PO';
        if (iconWrap) {
            iconWrap.style.background = '#0f172a';
            iconWrap.style.boxShadow = 'none';
        }
        if (btnProceed) {
            btnProceed.style.background = '#0f172a';
            btnProceed.innerText = 'Lanjutkan Simpan Perubahan';
        }
    }

    tbody.innerHTML = discrepancies.map(d => {
        let badgeHtml = '';
        if (d.tipe === 'OVER') {
            badgeHtml = `<span class="badge" style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; font-weight:700; font-size:0.75rem; padding: 2px 6px; border-radius: 4px;">Over PO (+${d.diff.toFixed(2)} ${escapeHtml(d.satuan)})</span>`;
        } else {
            badgeHtml = `<span class="badge" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-weight:700; font-size:0.75rem; padding: 2px 6px; border-radius: 4px;">Kurang (-${Math.abs(d.diff).toFixed(2)} ${escapeHtml(d.satuan)})</span>`;
        }

        return `
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 0.55rem 0.75rem; font-weight:600; color:#0f172a;">${escapeHtml(d.nama)}</td>
                <td style="padding: 0.55rem 0.75rem; text-align:right; font-family:monospace; color:#475569;">${d.pesanPo.toLocaleString('id-ID', { minimumFractionDigits: 2 })} ${escapeHtml(d.satuan)}</td>
                <td style="padding: 0.55rem 0.75rem; text-align:right; font-family:monospace; font-weight:700; color:#0f172a;">${d.netto.toLocaleString('id-ID', { minimumFractionDigits: 2 })} ${escapeHtml(d.satuan)}</td>
                <td style="padding: 0.55rem 0.75rem; text-align:right;">${badgeHtml}</td>
            </tr>
        `;
    }).join('');

    openModal('modalConfirmPoDiscrepancyTerima');
}

function closeConfirmPoDiscrepancyModal() {
    closeModal('modalConfirmPoDiscrepancyTerima');
}
window.closeConfirmPoDiscrepancyModal = closeConfirmPoDiscrepancyModal;

function proceedSubmitPoDiscrepancy() {
    poDiscrepancyConfirmed = true;
    closeConfirmPoDiscrepancyModal();
    const form = document.getElementById('formTerima');
    if (form) {
        setSubmitButtonLoading(true);
        form.submit();
    }
}
window.proceedSubmitPoDiscrepancy = proceedSubmitPoDiscrepancy;

// -------------------------------------------------------------------------
// 3. FITUR PROTEKSI PERIODE BULAN SEBELUMNYA (PREVIOUS MONTH PROTECTION)
// -------------------------------------------------------------------------
let previousMonthConfirmed = false;

function submitTerimaEditForm(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const form = document.getElementById('formTerima');
    if (!form) return false;

    // Step 1: Pengecekan Perubahan Data (No Changes Detection) - INSTAN 0 DETIK
    if (!isTerimaEditFormDirty()) {
        showTerimaEditNoChangeBanner();
        return false;
    }

    // Step 2: Pengecekan Selisih Kuantitas PO (Over PO vs Under PO)
    if (!poDiscrepancyConfirmed) {
        const discrepancies = getPoDiscrepancies();
        if (discrepancies.length > 0) {
            openConfirmPoDiscrepancyModal(discrepancies);
            return false;
        }
    }

    // Step 3: Pengecekan Bulan Sebelumnya
    if (!previousMonthConfirmed) {
        const inputDate = document.getElementById('terima_tgl');
        if (inputDate && inputDate.value) {
            const currentYearMonth = (window.currentServerYearMonth || new Date().toISOString().slice(0, 7));
            const inputYearMonth = inputDate.value.slice(0, 7);

            if (inputYearMonth < currentYearMonth) {
                openPreviousMonthModal(inputDate.value);
                return false;
            }
        }
    }

    // Hanya jika benar-benar ada perubahan riil: tampilkan status loading & kirim form
    setSubmitButtonLoading(true);
    form.submit();
    return true;
}
window.submitTerimaEditForm = submitTerimaEditForm;

function initCreatePreviousMonthProtection() {
    const formTerima = document.getElementById('formTerima');
    if (formTerima) {
        formTerima.addEventListener('submit', function (e) {
            submitTerimaEditForm(e);
        });
    }

    const btnConfirm = document.getElementById('btnConfirmSubmitPreviousMonth');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            previousMonthConfirmed = true;
            closePreviousMonthModal();
            const form = document.getElementById('formTerima');
            if (form) {
                setSubmitButtonLoading(true);
                form.submit();
            }
        });
    }
}

function openPreviousMonthModal(dateVal) {
    const modal = document.getElementById('modalConfirmPreviousMonthTerima');
    const label = document.getElementById('modalSelectedPeriodLabel');
    if (modal) {
        if (label) label.innerText = dateVal;
        openModal('modalConfirmPreviousMonthTerima');
    }
}

function closePreviousMonthModal() {
    closeModal('modalConfirmPreviousMonthTerima');
}
window.closePreviousMonthModal = closePreviousMonthModal;

function setSubmitButtonLoading(isLoading) {
    const btn = document.getElementById('btnSubmitTerima');
    const textEl = document.getElementById('btnSubmitTerimaText');
    if (!btn) return;

    if (isLoading) {
        btn.disabled = true;
        btn.classList.add('is-loading');
        if (textEl) textEl.innerText = 'Menyimpan Perubahan...';
    } else {
        btn.disabled = false;
        btn.classList.remove('is-loading');
        if (textEl) textEl.innerText = 'Simpan Perubahan Penerimaan';
    }
}

// -------------------------------------------------------------------------
// INITIALIZATION (Mendukung DOMContentLoaded & ReadyState Complete/Interactive)
// -------------------------------------------------------------------------
function initTerimaEdit() {
    if (window.oldSupplierId) {
        selectSupplier(window.oldSupplierId);
    }

    calculateTotalTerima();
    initCreatePreviousMonthProtection();

    initialTerimaSerialized = getTerimaFormSerialized();
    initialTerimaEditSnapshot = captureTerimaEditFormSnapshot();
    setTimeout(() => {
        initialTerimaSerialized = getTerimaFormSerialized();
        initialTerimaEditSnapshot = captureTerimaEditFormSnapshot();
    }, 150);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTerimaEdit);
} else {
    initTerimaEdit();
}
