/**
 * =========================================================================
 * ERP PT MIRASA - TERIMA BARANG (GRN) CREATE JAVASCRIPT
 * =========================================================================
 */

    // =========================================================================
    // 1. DATA SUPPLIER & FILTER PENCARIAN
    // =========================================================================
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
            document.getElementById('modal_page_info').innerText = `Hal ${modalCurrentPage} dari ${totalPages}`;
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
                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; font-weight: 600; background: #059669;" onclick="event.stopPropagation(); selectSupplier(${s.id}); closeSupplierModal();">
                            Pilih
                        </button>
                    </td>
                </tr>
            `;
        });

        modalTbody.innerHTML = html;
    }

    function renderSupplierDropdown(query = '') {
        const q = query.trim().toLowerCase();
        let filtered = suppliersData.filter(s => {
            if (!q) return true;
            return (s.name && s.name.toLowerCase().includes(q)) || 
                   (s.code && s.code.toLowerCase().includes(q)) ||
                   (s.contact && s.contact.toLowerCase().includes(q));
        });

        const badgeCount = document.getElementById('supplier_count_badge');
        if (badgeCount) badgeCount.innerText = `Total: ${suppliersData.length} supplier`;

        if (filtered.length === 0) {
            supDropdownList.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                    Tidak ditemukan supplier dengan kata kunci "<strong>${escapeHtml(query)}</strong>".
                    <div style="margin-top: 0.4rem;">
                        <a href="javascript:void(0)" onclick="openSupplierModal()" style="color: #0284c7; font-weight: 600; font-size: 0.75rem;">Buka Tabel Daftar Supplier</a>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.slice(0, 15).forEach((s, idx) => {
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
        const sup = suppliersData.find(s => s.id == supplierId);
        if (!sup) return;

        supHiddenSelect.value = sup.id;

        const badge = getCategoryBadge(sup.category, sup.categoryName);
        document.getElementById('disp_sup_name').innerText = sup.name;
        document.getElementById('disp_sup_code').innerText = sup.code;
        const catBadgeSpan = document.getElementById('disp_sup_cat_badge');
        if (catBadgeSpan) catBadgeSpan.innerHTML = badge.html;
        const legacyCatSpan = document.getElementById('disp_sup_cat');
        if (legacyCatSpan) legacyCatSpan.innerText = sup.categoryName;
        document.getElementById('disp_sup_contact').innerText = 
            (sup.contact && sup.contact !== '-' ? 'Telp: ' + sup.contact : '') + 
            (sup.address ? (sup.contact && sup.contact !== '-' ? ' | ' : '') + 'Alamat: ' + sup.address : '');

        selectedSupCard.style.display = 'flex';
        supSearchWrapper.style.display = 'none';
        supDropdownList.style.display = 'none';

        // Auto filter barang kategori sesuai supplier jika relevan
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
        supHiddenSelect.value = '';
        selectedSupCard.style.display = 'none';
        supSearchWrapper.style.display = 'block';
        supSearchInput.value = '';
        renderSupplierDropdown('');
        supDropdownList.style.display = 'block';
        supSearchInput.focus();
    }

    // Search input listeners
    supSearchInput.addEventListener('input', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    supSearchInput.addEventListener('focus', function() {
        renderSupplierDropdown(this.value);
        supDropdownList.style.display = 'block';
    });

    // Keyboard navigation
    supSearchInput.addEventListener('keydown', function(e) {
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
            }
        } else if (e.key === 'Escape') {
            supDropdownList.style.display = 'none';
        }
    });

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
        if (!supSearchWrapper.contains(e.target) && !e.target.classList.contains('sup-filter-chip')) {
            supDropdownList.style.display = 'none';
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Prefill selected supplier if available (from old input or selected PO)
    // Supplier initialization handled in page wrapper


    // =========================================================================
    // 2. FILTER KATEGORI BARANG PADA TABEL GRN
    // =========================================================================
    let activeBarangCategory = 'ALL';

    function filterBarangCategory(category, btn) {
        activeBarangCategory = category;
        document.querySelectorAll('.category-segment-btn, .barang-filter-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');

        document.querySelectorAll('.item-barang').forEach(select => {
            applyBarangCategoryFilterToSelect(select, category);
        });
    }

    function applyBarangCategoryFilterToSelect(selectElem, category) {
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


    // =========================================================================
    // 3. LOGIKA BARIS PENERIMAAN BARANG, BATCH GENERATOR & KALKULASI
    // =========================================================================
    let terimaRowIndex = window.selectedPoDetailsCount || 1;

    function onPoSelected(poId) {
        if (!poId) {
            window.location.href = window.terimaCreateUrl || '/gudang/terima/create';
            return;
        }
        window.location.href = "{{ route('gudang.terima.create') }}?po_id=" + poId;
    }

    // ⚡ Salin Semua Sisa PO langsung ke kolom Kuantitas Terima
    function copyAllRemainingPoQty() {
        document.querySelectorAll('.terima-row').forEach(row => {
            const sisa = parseFloat(row.dataset.sisa || 0);
            if (sisa > 0) {
                const qtyInput = row.querySelector('.item-terima-qty');
                if (qtyInput) qtyInput.value = sisa;
            }
        });
        calculateTotalTerima();
    }

    // 🧹 Kosongkan Semua Kuantitas agar bisa diketik manual sesuai fisik yang datang
    function clearAllTerimaQty() {
        document.querySelectorAll('.item-terima-qty').forEach(input => {
            input.value = 0;
        });
        calculateTotalTerima();
    }

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
        tr.innerHTML = `<td class="row-num">${terimaRowIndex + 1}</td>`;
    }

    container.appendChild(tr);
        terimaRowIndex++;
        updateTerimaRowNumbers();
        attachExcelKeyboardEventsTerima(tr);
        calculateTotalTerima();

        const select = tr.querySelector('.item-barang');
        applyBarangCategoryFilterToSelect(select, activeBarangCategory);

        if (focusNew) {
            select.focus();
        }
    }

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

    function updateTerimaRowNumbers() {
        document.querySelectorAll('.terima-row').forEach((row, idx) => {
            const numElem = row.querySelector('.row-num');
            if (numElem) numElem.innerText = idx + 1;
        });
    }

    function updateTerimaSatuanAndBatch(selectElem, isUserChange = true) {
        const row = selectElem.closest('tr');
        const selectedOption = selectElem.options ? selectElem.options[selectElem.selectedIndex] : null;
        const barangId = selectElem.value;
        const batchInput = row.querySelector('.item-batch');

        if (!barangId) {
            if (batchInput) {
                batchInput.value = '';
                batchInput.placeholder = 'Pilih barang dahulu';
            }
            const satuanSpan = row.querySelector('.row-satuan');
            if (satuanSpan) satuanSpan.innerText = '-';
            const hargaInput = row.querySelector('.item-harga');
            if (hargaInput) hargaInput.value = 0;
            calculateTotalTerima();
            return;
        }

        const satuan = selectedOption?.dataset?.satuan || '-';
        const defaultHarga = parseFloat(selectedOption?.dataset?.harga || 0);

        const satuanSpan = row.querySelector('.row-satuan');
        if (satuanSpan && satuan !== '-') satuanSpan.innerText = satuan;

        // Otomatis generate inisial barang saja di depan (misal MS-, SK-, UU-)
        // Tidak ada tambahan kode lain di belakangnya, sehingga kosong (misal MS-) agar admin mengisi nomor batch supplier
        if (batchInput) {
            let acronym = selectedOption?.dataset?.acronym || '';
            if (!acronym) {
                const nm = (selectedOption?.dataset?.nm || '').toUpperCase().trim();
                const cd = (selectedOption?.dataset?.cd || '').toUpperCase().trim();
                if (nm.includes('MINYAK SAWIT')) acronym = 'MS';
                else if (nm.includes('MINYAK KELAPA')) acronym = 'MK';
                else if (nm.includes('PERENYAH')) acronym = 'PR';
                else if (nm.includes('PLASTIK HD')) acronym = 'HD';
                else if (nm.includes('LAKBAN KECIL')) acronym = 'LK';
                else if (nm.includes('LAKBAN SEDANG')) acronym = 'LS';
                else if (nm.includes('SINGKONG')) acronym = 'SK';
                else if (nm.includes('UBI UNGU')) acronym = 'UU';
                else if (cd.startsWith('BB-')) {
                    const match = cd.match(/^BB-([A-Z]{2,4})\d/i);
                    acronym = match ? match[1] : 'BB';
                } else if (cd && !cd.startsWith('BRG-')) {
                    const match = cd.match(/^([A-Z]{2,4})\d/i);
                    acronym = match ? match[1] : cd.replace(/[^A-Z]/g, '').substring(0, 3);
                } else if (nm) {
                    const words = nm.split(/\s+/);
                    if (words.length >= 3) {
                        acronym = words[0][0] + words[1][0] + words[2][0];
                    } else if (words.length === 2) {
                        acronym = words[0][0] + words[1][0];
                    } else {
                        acronym = nm.substring(0, 3);
                    }
                } else {
                    acronym = 'BRG';
                }
            }
            const prefix = (acronym ? acronym.toUpperCase() : 'BRG') + '-';
            const currentVal = batchInput.value.trim();

            if (!currentVal || currentVal.endsWith('-') || currentVal === prefix) {
                batchInput.value = prefix;
            } else if (currentVal.indexOf('-') > 0) {
                // Pertahankan nomor fisik supplier yang sudah diketik jika user mengganti pilihan barang
                const suffix = currentVal.substring(currentVal.indexOf('-') + 1);
                batchInput.value = prefix + suffix;
            } else {
                batchInput.value = prefix + currentVal;
            }

            batchInput.placeholder = `${prefix}... (isi batch supplier)`;

            // Arahkan kursor langsung ke akhir prefix agar user langsung mengetik nomor batch fisik
            if (document.activeElement === selectElem) {
                setTimeout(() => {
                    batchInput.focus();
                    const len = batchInput.value.length;
                    batchInput.setSelectionRange(len, len);
                }, 50);
            }
        }

        const hargaInput = row.querySelector('.item-harga');
        if (hargaInput) {
            if (isUserChange) {
                hargaInput.value = defaultHarga;
            } else if (defaultHarga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
                hargaInput.value = defaultHarga;
            }
        }
        calculateTotalTerima();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.terima-row').forEach(row => {
            const selectElem = row.querySelector('.item-barang');
            if (selectElem && selectElem.value) {
                updateTerimaSatuanAndBatch(selectElem, false);
            }
        });
    });

    function calculateTotalTerima() {
        let totalBruto = 0;
        let totalReject = 0;
        let totalSubtotalBruto = 0;
        let totalDiskon = 0;
        let totalPotongan = 0;
        let totalSubtotalNetto = 0;
        let totalPpn = 0;
        let activeItemCount = 0;

        document.querySelectorAll('.terima-row').forEach(row => {
            const selectElem = row.querySelector('.item-barang') || row.querySelector('.item-barang-id');
            const terimaInput = row.querySelector('.item-terima-qty');
            const rejectInput = row.querySelector('.item-reject-qty');
            const hargaInput = row.querySelector('.item-harga');
            const diskonInput = row.querySelector('.item-diskon');
            const potonganInput = row.querySelector('.item-potongan');
            const ppnSelect = row.querySelector('.item-ppn-tipe');

            if (selectElem && selectElem.value) {
                activeItemCount++;
            }

            const terimaQty = parseFloat(terimaInput?.value) || 0;
            const rejectQty = parseFloat(rejectInput?.value) || 0;
            const hargaNominal = parseFloat(hargaInput?.value) || 0;
            const diskonPersen = Math.min(100, Math.max(0, parseFloat(diskonInput?.value) || 0));
            const potonganNominal = Math.max(0, parseFloat(potonganInput?.value) || 0);
            const isPpn11 = ppnSelect ? ppnSelect.value === 'PPN_11' : false;

            // Netto Fisik
            const netto = Math.max(0, terimaQty - rejectQty);
            const nettoSpan = row.querySelector('.row-netto');
            if (nettoSpan) {
                nettoSpan.innerText = netto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Komersial & Pajak Baris
            const diskonUnit = hargaNominal * (diskonPersen / 100);
            const hargaNetto = Math.max(0, hargaNominal - diskonUnit);
            const rowSubtotalNetto = Math.max(0, (terimaQty * hargaNetto) - potonganNominal);
            const itemPpnNominal = isPpn11 ? Math.round(rowSubtotalNetto * 0.11) : 0;
            const rowSubtotalTagihan = rowSubtotalNetto + itemPpnNominal;

            const rowSubtotalCell = row.querySelector('.row-subtotal');
            if (rowSubtotalCell) {
                rowSubtotalCell.innerText = 'Rp ' + Math.round(rowSubtotalTagihan).toLocaleString('id-ID');
            }

            totalBruto += terimaQty;
            totalReject += rejectQty;
            totalSubtotalBruto += (terimaQty * hargaNominal);
            totalDiskon += (terimaQty * diskonUnit);
            totalPotongan += potonganNominal;
            totalSubtotalNetto += rowSubtotalNetto;
            totalPpn += itemPpnNominal;
        });

        const totalNetto = Math.max(0, totalBruto - totalReject);
        const grandTotal = Math.max(0, totalSubtotalNetto + totalPpn);

        // Update Tabel Footer
        const brutoDisplay = document.getElementById('totalBrutoQtyDisplay');
        const rejectDisplay = document.getElementById('totalRejectQtyDisplay');
        const nettoDisplay = document.getElementById('totalNettoQtyDisplay');
        const diskonDisplay = document.getElementById('totalTerimaDiskonDisplay');
        const potonganDisplay = document.getElementById('totalTerimaPotonganDisplay');
        const ppnDisplay = document.getElementById('totalTerimaPpnDisplay');
        const nilaiDisplay = document.getElementById('totalTerimaNilaiDisplay');

        if (brutoDisplay) brutoDisplay.innerText = totalBruto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (rejectDisplay) rejectDisplay.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (nettoDisplay) nettoDisplay.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (diskonDisplay) diskonDisplay.innerText = totalDiskon > 0 ? '-Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : '-';
        if (potonganDisplay) potonganDisplay.innerText = totalPotongan > 0 ? '-Rp ' + Math.round(totalPotongan).toLocaleString('id-ID') : '-';
        if (ppnDisplay) ppnDisplay.innerText = totalPpn > 0 ? '+Rp ' + Math.round(totalPpn).toLocaleString('id-ID') : '-';
        if (nilaiDisplay) nilaiDisplay.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

        // Update Bar Ringkasan Terpadu di Footer Tabel
        const barTotalNetto = document.getElementById('barTotalNetto');
        const barTotalReject = document.getElementById('barTotalReject');
        const barTotalItems = document.getElementById('barTotalItems');
        const barGrandTotal = document.getElementById('barGrandTotal');
        const barTaxSummaryLine = document.getElementById('barTaxSummaryLine');

        if (barTotalNetto) barTotalNetto.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (barTotalReject) barTotalReject.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (barTotalItems) barTotalItems.innerText = activeItemCount;
        if (barGrandTotal) barGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

        // Update Card Ringkasan Tambahan / Halaman Edit
        const globalPotonganInput = document.getElementById('global_potongan');
        const globalPotonganNominal = Math.max(0, parseFloat(globalPotonganInput?.value) || 0);
        const finalGrandTotal = Math.max(0, grandTotal - globalPotonganNominal);

        const lblTotalBruto = document.getElementById('lblTotalBruto');
        const lblTotalAfkir = document.getElementById('lblTotalAfkir');
        const lblTotalNetto = document.getElementById('lblTotalNetto');
        const lblSubtotalNominal = document.getElementById('lblSubtotalNominal');
        const lblTotalPpn = document.getElementById('lblTotalPpn');
        const lblGrandTotal = document.getElementById('lblGrandTotal');

        if (lblTotalBruto) lblTotalBruto.innerText = totalBruto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (lblTotalAfkir) lblTotalAfkir.innerText = totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (lblTotalNetto) lblTotalNetto.innerText = totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (lblSubtotalNominal) lblSubtotalNominal.innerText = 'Rp ' + Math.round(totalSubtotalNetto).toLocaleString('id-ID');
        if (lblTotalPpn) lblTotalPpn.innerText = 'Rp ' + Math.round(totalPpn).toLocaleString('id-ID');
        if (lblGrandTotal) lblGrandTotal.innerText = 'Rp ' + Math.round(finalGrandTotal).toLocaleString('id-ID');

        if (barTaxSummaryLine) {
            if (totalPpn > 0 || totalDiskon > 0 || totalPotongan > 0) {
                let parts = [];
                if (totalDiskon > 0) parts.push(`Diskon: -Rp ${Math.round(totalDiskon).toLocaleString('id-ID')}`);
                if (totalPotongan > 0) parts.push(`Potongan: -Rp ${Math.round(totalPotongan).toLocaleString('id-ID')}`);
                if (totalPpn > 0) parts.push(`PPN 11%: +Rp ${Math.round(totalPpn).toLocaleString('id-ID')}`);
                barTaxSummaryLine.style.display = 'block';
                barTaxSummaryLine.innerHTML = `<span style="color:#0f172a; font-weight:600;">DPP Netto: Rp ${Math.round(totalSubtotalNetto).toLocaleString('id-ID')}</span> &bull; ${parts.join(' &bull; ')}`;
            } else {
                barTaxSummaryLine.style.display = 'none';
                barTaxSummaryLine.innerHTML = '';
            }
        }
    }

    // Expose Global Aliases
    window.calculateTotalTerima = calculateTotalTerima;
    window.addTerimaRow = addTerimaRow;
    window.removeTerimaRow = removeTerimaRow;
    window.addNewItemRow = () => addTerimaRow(true);
    window.removeItemRow = (btn) => removeTerimaRow(btn);

    // Validasi Kelengkapan Batch Fisik Supplier Sebelum Form Disubmit
    const formTerimaElem = document.getElementById('formTerima');
    if (formTerimaElem) {
        formTerimaElem.addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.terima-row');
            let errorFound = false;

            rows.forEach((row, idx) => {
                if (errorFound) return;

                const barangSelect = row.querySelector('.item-barang') || row.querySelector('.item-barang-id');
                const qtyInput = row.querySelector('.item-terima-qty');
                const batchInput = row.querySelector('.item-batch');

                const hasBarang = barangSelect && barangSelect.value;
                const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;

                if (hasBarang && qty > 0 && batchInput) {
                    const batchVal = batchInput.value.trim();
                    if (!batchVal || batchVal.endsWith('-')) {
                        e.preventDefault();
                        errorFound = true;
                        batchInput.style.border = '2px solid #ef4444';
                        batchInput.style.backgroundColor = '#fef2f2';
                        batchInput.focus();
                        const len = batchInput.value.length;
                        batchInput.setSelectionRange(len, len);

                        alert(`⚠️ Nomor Batch Fisik Supplier pada baris ke-${idx + 1} belum diisi lengkap!\n\nSilakan ketikkan kode lot / faktur yang tertera pada surat jalan atau kemasan supplier di belakang tanda strip.`);
                    } else {
                        batchInput.style.border = '';
                        batchInput.style.backgroundColor = '';
                    }
                }
            });

            if (errorFound) {
                return false;
            }
        });

        // Reset style error saat user mulai mengetik di kolom batch
        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('item-batch')) {
                const val = e.target.value.trim();
                if (val && !val.endsWith('-')) {
                    e.target.style.border = '';
                    e.target.style.backgroundColor = '';
                }
            }
        });
    }

    // Shortcut Pintasan Keyboard Ctrl+S / Cmd+S untuk Simpan Cepat
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitTerima');
            if (btn && !btn.disabled) {
                btn.click();
            }
        }
    });

    // Excel Keyboard Navigation untuk Penerimaan Barang
    function attachExcelKeyboardEventsTerima(rowElement) {
        const inputs = rowElement.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const allRows = Array.from(document.querySelectorAll('.terima-row'));
                    const currentRowIdx = allRows.indexOf(rowElement);

                    if (input.classList.contains('item-terima-qty') || input.classList.contains('item-reject-qty') || input.classList.contains('item-harga') || input.classList.contains('item-diskon') || input.classList.contains('item-potongan')) {
                        if (currentRowIdx === allRows.length - 1) {
                            addTerimaRow(true);
                        } else {
                            const nextRow = allRows[currentRowIdx + 1];
                            const targetClass = input.classList[1] || input.classList[0];
                            const target = nextRow.querySelector('.' + targetClass);
                            if (target) target.focus();
                        }
                    }
                }
            });
        });
    }

    document.querySelectorAll('.terima-row').forEach(r => attachExcelKeyboardEventsTerima(r));
    calculateTotalTerima();

    function fillAllSisaCreate() {
        copyAllRemainingPoQty();
    }

    function clearAllInputsCreate() {
        clearAllTerimaQty();
    }

    // =========================================================================
    // 4. INTEGRASI TIKET QC INBOUND (SAMPLING KADAR AIR, REFRAKSI & TIMBANGAN)
    // =========================================================================
    let currentLoadedQcTicket = null;
    let allQcTickets = [];
    let activeQcCategory = '';

    function openQcModal() {
        openModal('modalPilihQc');
        const loading = document.getElementById('qcModalLoading');
        const empty = document.getElementById('qcModalEmpty');
        const table = document.getElementById('qcModalTable');
        const tbody = document.getElementById('qcModalTbody');
        const searchInput = document.getElementById('qcSearchInput');
        const filterPo = document.getElementById('qcFilterPo');
        const counter = document.getElementById('qcResultCount');

        if (searchInput) searchInput.value = '';
        if (filterPo) filterPo.value = '';
        const btnClear = document.getElementById('btnQcSearchClear');
        if (btnClear) btnClear.style.display = 'none';

        activeQcCategory = '';
        document.querySelectorAll('.qc-filter-chip').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-cat') === '');
        });

        if (loading) loading.style.display = 'block';
        if (empty) empty.style.display = 'none';
        if (table) table.style.display = 'none';
        if (tbody) tbody.innerHTML = '';
        if (counter) counter.innerText = 'Memuat...';

        fetch(window.qcSiapGudangUrl || '/qc/inbound/siap-gudang')
            .then(res => res.json())
            .then(json => {
                if (loading) loading.style.display = 'none';
                allQcTickets = json.tickets || [];

                if (allQcTickets.length === 0) {
                    if (empty) empty.style.display = 'block';
                    if (counter) counter.innerText = '0 tiket';
                    return;
                }

                if (table) table.style.display = 'table';
                filterQcTickets();
                if (searchInput) setTimeout(() => searchInput.focus(), 150);
            })
            .catch(err => {
                if (loading) loading.style.display = 'none';
                alert('Gagal memuat tiket QC: ' + err.message);
            });
    }

    function setQcCategoryFilter(cat, btn) {
        activeQcCategory = cat;
        document.querySelectorAll('.qc-filter-chip').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterQcTickets();
    }

    function clearQcSearch() {
        const input = document.getElementById('qcSearchInput');
        if (input) {
            input.value = '';
            input.focus();
        }
        filterQcTickets();
    }

    function filterQcTickets() {
        const searchVal = (document.getElementById('qcSearchInput')?.value || '').toLowerCase().trim();
        const poFilter = document.getElementById('qcFilterPo')?.value || '';
        const btnClear = document.getElementById('btnQcSearchClear');
        if (btnClear) btnClear.style.display = searchVal ? 'block' : 'none';

        const filtered = allQcTickets.filter(t => {
            // 1. Kategori Komoditas
            if (activeQcCategory) {
                const itemSummary = (t.item_summary || '').toUpperCase();
                const tCat = (t.kategori_barang || '').toUpperCase();
                const tNamaJenis = (t.nama_jenis || '').toUpperCase();

                if (activeQcCategory === 'SINGKONG') {
                    if (tCat !== 'SINGKONG' && !itemSummary.includes('SINGKONG') && !tNamaJenis.includes('SINGKONG')) return false;
                } else if (activeQcCategory === 'MINYAK') {
                    if (tCat !== 'MINYAK' && !itemSummary.includes('MINYAK') && !tNamaJenis.includes('MINYAK')) return false;
                } else if (activeQcCategory === 'PLASTIK') {
                    if (tCat !== 'PLASTIK' && !itemSummary.includes('PLASTIK') && !tNamaJenis.includes('PLASTIK')) return false;
                } else if (activeQcCategory === 'KARTON') {
                    if (tCat !== 'KARTON' && !itemSummary.includes('KARTON') && !tNamaJenis.includes('KARTON')) return false;
                } else if (activeQcCategory === 'MSG') {
                    if (tCat !== 'MSG' && !itemSummary.includes('MSG') && !tNamaJenis.includes('MSG')) return false;
                } else if (activeQcCategory === 'GARAM') {
                    if (tCat !== 'GARAM' && !itemSummary.includes('GARAM') && !tNamaJenis.includes('GARAM')) return false;
                } else if (activeQcCategory === 'PERENYAH') {
                    if (tCat !== 'PERENYAH' && !itemSummary.includes('PERENYAH') && !tNamaJenis.includes('PERENYAH')) return false;
                }
            }

            // 2. Status PO
            if (poFilter === 'PO') {
                if (!t.po_no || t.po_no === 'Non-PO') return false;
            } else if (poFilter === 'NON_PO') {
                if (t.po_no && t.po_no !== 'Non-PO') return false;
            }

            // 3. Kata Kunci Pencarian (No QC, Supplier, Barang, PO, Truk, Sopir, Surat Jalan)
            if (searchVal) {
                const haystack = [
                    t.qc_no || '',
                    t.supplier_nm || '',
                    t.po_no || '',
                    t.item_summary || '',
                    t.plat_nomor_truk || '',
                    t.sopir_nama || '',
                    t.surat_jalan_supplier || '',
                    t.tgl_periksa || '',
                    t.kategori_barang || '',
                    t.nama_jenis || '',
                ].join(' ').toLowerCase();

                if (!haystack.includes(searchVal)) return false;
            }

            return true;
        });

        renderQcTicketsTable(filtered);
    }

    function renderQcTicketsTable(tickets) {
        const tbody = document.getElementById('qcModalTbody');
        const counter = document.getElementById('qcResultCount');
        if (!tbody) return;

        if (counter) {
            counter.innerText = `Menampilkan ${tickets.length} dari ${allQcTickets.length} tiket`;
        }

        if (tickets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
                        <div style="font-size: 1.6rem; margin-bottom: 0.35rem;">🔍</div>
                        <strong style="color: #1e293b; font-size: 0.9rem;">Tidak ada tiket QC yang cocok</strong>
                        <div style="font-size: 0.775rem; margin-top: 0.25rem;">Coba sesuaikan kata kunci pencarian atau ubah pilihan filter komoditas/PO.</div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        tickets.forEach((t, idx) => {
            let catBadge = '';
            const kCat = (t.kategori_barang || 'SINGKONG').toUpperCase();
            if (kCat === 'SINGKONG') {
                catBadge = `<span class="badge" style="background: #ecfdf5; color: #047857; font-size: 0.675rem; font-weight: 700;">🥔 Singkong</span>`;
            } else if (kCat === 'MINYAK') {
                catBadge = `<span class="badge" style="background: #fefce8; color: #a16207; font-size: 0.675rem; font-weight: 700;">🛢️ Minyak</span>`;
            } else if (kCat === 'PLASTIK') {
                catBadge = `<span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.675rem; font-weight: 700;">🛍️ Plastik</span>`;
            } else if (kCat === 'KARTON') {
                catBadge = `<span class="badge" style="background: #fff7ed; color: #c2410c; font-size: 0.675rem; font-weight: 700;">📦 Karton</span>`;
            } else if (kCat === 'MSG') {
                catBadge = `<span class="badge" style="background: #f5f3ff; color: #6d28d9; font-size: 0.675rem; font-weight: 700;">🧂 MSG</span>`;
            } else if (kCat === 'GARAM') {
                catBadge = `<span class="badge" style="background: #f0fdfa; color: #0f766e; font-size: 0.675rem; font-weight: 700;">🧂 Garam</span>`;
            } else if (kCat === 'PERENYAH') {
                catBadge = `<span class="badge" style="background: #fdf4ff; color: #a21caf; font-size: 0.675rem; font-weight: 700;">✨ Perenyah</span>`;
            }

            html += `
                <tr class="sup-modal-row" onclick="applyQcTicket(${t.qc_id}); closeModal('modalPilihQc');" style="cursor: pointer;">
                    <td style="text-align: center; color: #64748b; font-size: 0.775rem;">${idx + 1}</td>
                    <td>
                        <strong style="color: #0284c7; font-family: monospace; font-size: 0.85rem;">${escapeHtml(t.qc_no)}</strong>
                    </td>
                    <td style="font-size: 0.8rem; color: #334155;">${escapeHtml(t.tgl_periksa)}</td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.85rem;">${escapeHtml(t.supplier_nm || '-')}</strong>
                        <div style="display: flex; gap: 0.35rem; align-items: center; margin-top: 2px; flex-wrap: wrap;">
                            ${catBadge}
                            ${t.po_no && t.po_no !== 'Non-PO' ? `<span style="font-size:0.725rem; font-weight: 600; color:#0284c7;">PO: ${escapeHtml(t.po_no)}</span>` : `<span style="font-size:0.725rem; color:#94a3b8;">Non-PO</span>`}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">${escapeHtml(t.item_summary || '-')}</div>
                    </td>
                    <td style="font-size: 0.8rem; color: #475569;">
                        <div><strong>${escapeHtml(t.plat_nomor_truk || '-')}</strong></div>
                        ${t.sopir_nama ? `<div style="font-size: 0.725rem; color: #64748b;">${escapeHtml(t.sopir_nama)}</div>` : ''}
                    </td>
                    <td style="text-align: right; font-weight: 600; font-size: 0.85rem;">
                        ${parseFloat(t.total_gross).toLocaleString('id-ID', {minimumFractionDigits: 2})}
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #15803d; font-size: 0.9rem; background: #f0fdf4;">
                        ${parseFloat(t.total_netto).toLocaleString('id-ID', {minimumFractionDigits: 2})}
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.65rem; font-size: 0.75rem; font-weight: 700; background: #0284c7;" onclick="event.stopPropagation(); applyQcTicket(${t.qc_id}); closeModal('modalPilihQc');">
                            Pilih
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function applyQcTicket(qcId) {
        if (!qcId) return;

        fetch(`{{ url('qc/inbound/ticket-data') }}/${qcId}`)
            .then(res => res.json())
            .then(json => {
                if (json.status !== 'success' || !json.data) {
                    alert('Data tiket QC tidak ditemukan.');
                    return;
                }

                const data = json.data;
                currentLoadedQcTicket = data;

                // 1. Set Hidden Input
                const inputQcId = document.getElementById('input_qc_id');
                if (inputQcId) inputQcId.value = data.qc_id;

                // 2. Set Supplier
                if (data.supplier_id) {
                    selectSupplier(data.supplier_id);
                }

                // 3. Set Gudang
                if (data.gudang_id) {
                    const gdgSelect = document.getElementById('gudang_id');
                    if (gdgSelect) gdgSelect.value = data.gudang_id;
                }

                // 4. Set PO (jika ada)
                if (data.po_id) {
                    const poSelect = document.getElementById('po_id');
                    if (poSelect) poSelect.value = data.po_id;
                }

                // 5. Set Surat Jalan
                const sjInput = document.getElementById('suratjalan_no');
                if (sjInput) {
                    sjInput.value = data.surat_jalan_supplier || '';
                }

                // 6. Set Catatan dengan info Sopir & Plat
                const catInput = document.getElementById('catatan_txt');
                if (catInput && (!catInput.value || catInput.value.includes('QC Tiket'))) {
                    let cat = `QC Tiket: ${data.qc_no}`;
                    if (data.plat_nomor_truk) cat += `, Plat Truk: ${data.plat_nomor_truk}`;
                    if (data.sopir_nama) cat += `, Sopir: ${data.sopir_nama}`;
                    catInput.value = cat;
                }

                // 7. Update Banner
                const bannerTitle = document.getElementById('qcBannerTitle');
                const bannerSub = document.getElementById('qcBannerSubtitle');
                const btnDetach = document.getElementById('btnDetachQc');

                if (bannerTitle) {
                    bannerTitle.innerHTML = `<span style="color:#15803d;">✅ Terhubung dengan Tiket QC: ${escapeHtml(data.qc_no)}</span>`;
                }
                if (bannerSub) {
                    bannerSub.innerHTML = `Supplier: <strong>${escapeHtml(data.supplier_nm || '-')}</strong> &bull; Truk: <strong>${escapeHtml(data.plat_nomor_truk || '-')}</strong> &bull; Petugas QC: <strong>${escapeHtml(data.petugas_qc_nama || '-')}</strong>`;
                }
                if (btnDetach) btnDetach.style.display = 'inline-block';

                // 8. Muat Item Barang dari Tiket QC ke Tabel
                const container = document.getElementById('terimaItemsContainer');
                if (container && data.items && data.items.length > 0) {
                    container.innerHTML = '';
                    terimaRowIndex = 0;

                    data.items.forEach((it, idx) => {
                        const tr = document.createElement('tr');
                        tr.className = 'terima-row';
                        tr.dataset.index = idx;
                        tr.dataset.sisa = 0;

                        const rowGross = parseFloat(it.gross_qty) || 0;
                        const rowRefraksiPersen = parseFloat(it.refraksi_persen) || 0;
                        const rowRefraksiQty = parseFloat(it.refraksi_qty) || 0;
                        const rowRejectQty = parseFloat(it.reject_qty) || 0;
                        const rowNetto = parseFloat(it.netto_qty) || 0;
                        const defaultHarga = parseFloat(it.std_harga) || 0;

                        tr.innerHTML = `
                            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9;">${idx + 1}</td>
                            <td>
                                <input type="hidden" name="items[${idx}][podtl_id]" value="${it.podtl_id || ''}">
                                <input type="hidden" name="items[${idx}][qcdtl_id]" value="${it.qcdtl_id || ''}">
                                <input type="hidden" name="items[${idx}][kadar_air_persen]" value="${it.kadar_air || 0}">
                                <input type="hidden" name="items[${idx}][refraksi_persen]" value="${rowRefraksiPersen}">
                                <input type="hidden" name="items[${idx}][barang_id]" value="${it.barang_id}" class="item-barang-id">
                                
                                <strong style="color: #0f172a; display: block; font-size: 0.85rem;">${escapeHtml(it.barang_nm)}</strong>
                                <div style="display: flex; gap: 0.4rem; align-items: center; margin-top: 2px; flex-wrap: wrap;">
                                    <span style="font-size: 0.725rem; font-family: monospace; color: #64748b;">${escapeHtml(it.barang_cd)}</span>
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.675rem; font-weight: 700;">
                                        Kadar Air: ${it.kadar_air}%
                                    </span>
                                    ${rowRefraksiPersen > 0 ? `
                                        <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 0.675rem; font-weight: 700;">
                                            Refraksi: ${rowRefraksiPersen}% (-${rowRefraksiQty.toFixed(1)} KG)
                                        </span>
                                    ` : ''}
                                </div>
                            </td>
                            <td>
                                <input type="text" name="items[${idx}][batch_no]" value="${escapeHtml(it.batch_prefix || 'BRG-')}" placeholder="${escapeHtml(it.batch_prefix || 'BRG-')}... (isi batch supplier)" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%;" required>
                            </td>
                            <td>
                                <input type="date" name="items[${idx}][expired_tgl]" class="form-control" style="font-size: 0.8rem;">
                            </td>
                            <td>
                                <select name="items[${idx}][grade_cd]" class="form-control" style="font-size: 0.8rem;">
                                    <option value="A" ${it.grade_cd === 'A' ? 'selected' : ''}>Grade A Super</option>
                                    <option value="B" ${it.grade_cd === 'B' ? 'selected' : ''}>Grade B Standar</option>
                                    <option value="C" ${it.grade_cd === 'C' ? 'selected' : ''}>Grade C Campur</option>
                                    <option value="REJECT" ${it.grade_cd === 'REJECT' ? 'selected' : ''}>Reject / Afkir</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0" name="items[${idx}][terima_qty]" value="${rowNetto > 0 ? rowNetto : rowGross}" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required title="Netto bersih diterima (Gross ${rowGross} KG dikurangi refraksi & reject)">
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0" name="items[${idx}][reject_qty]" value="${rowRejectQty}" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
                                ${rowNetto.toLocaleString('id-ID', {minimumFractionDigits: 2})}
                            </td>
                            <td style="text-align: center;">
                                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">${escapeHtml(it.satuan_nm)}</span>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[${idx}][harga_nominal]" value="${defaultHarga}" class="form-control item-harga" placeholder="0" style="text-align: right; font-weight: 600;" oninput="calculateTotalTerima()">
                            </td>
                            <td>
                                <input type="number" step="0.1" min="0" max="100" name="items[${idx}][diskon_persen]" value="0" class="form-control item-diskon" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[${idx}][potongan_nominal]" value="0" class="form-control item-potongan" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
                            </td>
                            <td>
                                <select name="items[${idx}][ppn_tipe]" class="form-control item-ppn-tipe" onchange="calculateTotalTerima()" style="font-size: 0.775rem; font-weight: 600;">
                                    <option value="NON_PPN" selected>Non (0%)</option>
                                    <option value="PPN_11">PPN 11%</option>
                                </select>
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0f172a;" class="row-subtotal">
                                Rp 0
                            </td>
                            <td style="text-align: center;">
                                <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
                            </td>
                        `;
                        container.appendChild(tr);
                        terimaRowIndex++;
                        attachExcelKeyboardEventsTerima(tr);
                    });

                    calculateTotalTerima();
                }
            })
            .catch(err => {
                alert('Gagal mengambil data tiket QC: ' + err.message);
            });
    }

    function detachQcTicket() {
        if (!confirm('Lepas tiket QC ini? Seluruh data supplier dan daftar barang yang terisi akan dikosongkan.')) {
            return;
        }

        const inputQcId = document.getElementById('input_qc_id');
        if (inputQcId) inputQcId.value = '';

        const bannerTitle = document.getElementById('qcBannerTitle');
        const bannerSub = document.getElementById('qcBannerSubtitle');
        const btnDetach = document.getElementById('btnDetachQc');

        if (bannerTitle) bannerTitle.innerText = 'Sinkronisasi Inspeksi Mutu QC Lapangan';
        if (bannerSub) bannerSub.innerText = 'Tarik hasil sampling kadar air, refraksi kotoran, dan timbangan truk yang telah diverifikasi tim QC.';
        if (btnDetach) btnDetach.style.display = 'none';

        currentLoadedQcTicket = null;

        // 1. Kosongkan & Buka Kembali Pilihan Supplier
        if (typeof clearSelectedSupplier === 'function') {
            clearSelectedSupplier();
            const supDropdownList = document.getElementById('supDropdownList');
            if (supDropdownList) supDropdownList.style.display = 'none';
        }

        // 2. Kosongkan PO, Surat Jalan, dan Catatan jika terisi dari QC
        const poSelect = document.getElementById('po_id');
        if (poSelect) poSelect.value = '';

        const sjInput = document.getElementById('suratjalan_no');
        if (sjInput) sjInput.value = '';

        const catInput = document.getElementById('catatan_txt');
        if (catInput && catInput.value.includes('QC Tiket')) {
            catInput.value = '';
        }

        // 3. Kosongkan seluruh item dan sediakan 1 baris kosong default
        const container = document.getElementById('terimaItemsContainer');
        if (container) {
            container.innerHTML = '';
            terimaRowIndex = 0;
            addTerimaRow(false);
        }

        // 4. Hitung ulang total sehingga semua kembali ke 0
        calculateTotalTerima();

        // 5. Bersihkan parameter qc_id di URL agar tidak reload tiket lama saat refresh
        if (window.history && window.history.replaceState) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('qc_id');
            window.history.replaceState({}, document.title, currentUrl.toString());
        }
    }

    // Auto-load QC ticket if qc_id parameter is present in URL or old input
    document.addEventListener('DOMContentLoaded', function() {
        const qcIdInput = document.getElementById('input_qc_id');
        if (qcIdInput && qcIdInput.value) {
            applyQcTicket(qcIdInput.value);
        }
    });
