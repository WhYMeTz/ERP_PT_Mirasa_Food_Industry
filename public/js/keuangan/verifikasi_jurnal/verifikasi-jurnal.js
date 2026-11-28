/**
 * VERIFIKASI JURNAL KAS KELUAR - ERP PT MIRASA FOOD INDUSTRY
 * Location: public/js/keuangan/verifikasi_jurnal/verifikasi-jurnal.js
 */

let activeDropdownMenu = null;

// ==========================================================================
// 1. SMART ACTION DROPDOWN (Standard AGENTS.md)
// ==========================================================================
window.toggleSmartActionDropdown = function (button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activeDropdownMenu && activeDropdownMenu === menu) {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    const rect = button.getBoundingClientRect();
    menu.style.display = 'block';
    menu.style.position = 'fixed';

    const menuWidth = 185;
    const menuHeight = menu.offsetHeight || 120;
    const windowHeight = window.innerHeight;
    const windowWidth = window.innerWidth;

    let top = rect.bottom + 4;
    let left = rect.right - menuWidth;

    if (top + menuHeight > windowHeight - 10) {
        top = Math.max(10, rect.top - menuHeight - 4);
    }

    if (left < 10) {
        left = 10;
    } else if (left + menuWidth > windowWidth - 10) {
        left = windowWidth - menuWidth - 10;
    }

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';

    activeDropdownMenu = menu;
};

window.closeAllActionDropdowns = function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
};

document.addEventListener('click', function (e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target) && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', function () {
    if (activeDropdownMenu) {
        closeAllActionDropdowns();
    }
}, true);

// ==========================================================================
// 2. TABEL BATCH SELECTION & INDICATORS
// ==========================================================================
window.onRowCheckboxChange = function (checkbox) {
    const row = checkbox.closest('.vj-table-row');
    if (row) {
        if (checkbox.checked) {
            row.classList.add('row-selected');
        } else {
            row.classList.remove('row-selected');
        }
    }
    updateBatchToolbarState();
};

window.toggleSelectAll = function (masterCheckbox) {
    const isChecked = masterCheckbox.checked;
    const visibleRows = getVisibleRows();

    visibleRows.forEach(row => {
        // Hanya centang baris yang sudah siap jurnal (memiliki akun COA) bila master dicentang
        const isReady = row.getAttribute('data-ready') === '1';
        const checkbox = row.querySelector('.vj-row-checkbox');
        if (checkbox) {
            if (isChecked) {
                if (isReady) {
                    checkbox.checked = true;
                    row.classList.add('row-selected');
                } else {
                    checkbox.checked = false;
                    row.classList.remove('row-selected');
                }
            } else {
                checkbox.checked = false;
                row.classList.remove('row-selected');
            }
        }
    });

    updateBatchToolbarState();
};

function getVisibleRows() {
    const rows = document.querySelectorAll('.vj-table-row');
    return Array.from(rows).filter(row => row.style.display !== 'none');
}

function updateBatchToolbarState() {
    const checkboxes = document.querySelectorAll('.vj-row-checkbox:checked');
    const count = checkboxes.length;

    let totalNominal = 0;
    checkboxes.forEach(cb => {
        const row = cb.closest('.vj-table-row');
        if (row) {
            const nominal = parseFloat(row.getAttribute('data-nominal') || 0);
            totalNominal += nominal;
        }
    });

    const badge = document.getElementById('selectedCountBadge');
    const totalBadge = document.getElementById('selectedTotalBadge');
    const btnBatch = document.getElementById('btnBatchVerify');

    if (badge) {
        badge.textContent = `${count} BKK Terpilih`;
        if (count > 0) {
            badge.classList.add('active');
        } else {
            badge.classList.remove('active');
        }
    }

    if (totalBadge) {
        if (count > 0) {
            totalBadge.style.display = 'inline-block';
            totalBadge.textContent = `(Rp ${formatRupiah(totalNominal)})`;
        } else {
            totalBadge.style.display = 'none';
        }
    }

    if (btnBatch) {
        btnBatch.disabled = count === 0;
    }

    // Periksa status master checkbox
    const masterCheckbox = document.getElementById('checkAllReady');
    if (masterCheckbox) {
        const visibleReadyRows = getVisibleRows().filter(r => r.getAttribute('data-ready') === '1');
        if (visibleReadyRows.length > 0) {
            const allChecked = visibleReadyRows.every(r => {
                const cb = r.querySelector('.vj-row-checkbox');
                return cb && cb.checked;
            });
            masterCheckbox.checked = allChecked && count > 0;
        } else {
            masterCheckbox.checked = false;
        }
    }
}

// ==========================================================================
// 3. COA DROPDOWN & DYNAMIC STATUS
// ==========================================================================
window.onCoaSelectChange = function (bkkId) {
    const select = document.getElementById(`coa_select_${bkkId}`);
    const row = document.getElementById(`row-bkk-${bkkId}`);
    const badgeWrap = document.getElementById(`coa_badge_${bkkId}`);

    if (!select || !row || !badgeWrap) return;

    const val = select.value;
    if (val) {
        row.setAttribute('data-ready', '1');
        row.classList.remove('row-unmapped');
        row.classList.add('row-ready');
        badgeWrap.innerHTML = `
            <span class="vj-status-pill vj-status-ready">
                <span class="vj-status-dot"></span> Sesuai Kategori
            </span>
        `;
    } else {
        row.setAttribute('data-ready', '0');
        row.classList.remove('row-ready');
        row.classList.add('row-unmapped');
        badgeWrap.innerHTML = `
            <span class="vj-status-pill vj-status-unmapped">
                <span class="vj-status-dot"></span> Perlu Dipilih
            </span>
        `;
    }

    recalculateTabCounts();
    updateBatchToolbarState();
};

function recalculateTabCounts() {
    const rows = document.querySelectorAll('.vj-table-row');
    let readyCount = 0;
    let unmappedCount = 0;

    rows.forEach(r => {
        if (r.getAttribute('data-ready') === '1') {
            readyCount++;
        } else {
            unmappedCount++;
        }
    });

    const readyBadge = document.getElementById('tabCountReady');
    const unmappedBadge = document.getElementById('tabCountUnmapped');

    if (readyBadge) readyBadge.textContent = readyCount;
    if (unmappedBadge) unmappedBadge.textContent = unmappedCount;
}

// ==========================================================================
// 4. QUICK FILTER TABS (CLIENT-SIDE INSTANT FILTER)
// ==========================================================================
window.applyClientFilter = function (filterType, tabButton) {
    const tabs = document.querySelectorAll('.vj-quick-tab');
    tabs.forEach(t => t.classList.remove('active'));
    tabButton.classList.add('active');

    const rows = document.querySelectorAll('.vj-table-row');
    rows.forEach(row => {
        const isReady = row.getAttribute('data-ready') === '1';
        if (filterType === 'all') {
            row.style.display = '';
        } else if (filterType === 'ready') {
            row.style.display = isReady ? '' : 'none';
        } else if (filterType === 'unmapped') {
            row.style.display = !isReady ? '' : 'none';
        }
    });

    updateBatchToolbarState();
};

// ==========================================================================
// 5. SINGLE VERIFICATION (AKSI CEPAT 1 BARIS)
// ==========================================================================
window.verifySingleBkk = async function (bkkId) {
    const select = document.getElementById(`coa_select_${bkkId}`);
    const dateInput = document.getElementById(`tgl_jurnal_${bkkId}`);
    const btn = document.getElementById(`btnVerifySingle_${bkkId}`);

    if (!select || !select.value) {
        showEnterpriseAlert('Pilih Akun Beban', 'Silakan pilih Pos Akun Beban (COA Debit) terlebih dahulu untuk menerbitkan Jurnal Umum.');
        if (select) select.focus();
        return;
    }

    const coaId = select.value;
    const tglJurnal = dateInput ? dateInput.value : '';

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor" stroke-dasharray="32" stroke-linecap="round" style="opacity: 0.25;"></circle>
            </svg>
            <span>Memproses...</span>
        `;
    }

    try {
        const url = `${window.vjConfig.singleBaseUrl}/${bkkId}`;
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.vjConfig.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                coa_id: coaId,
                tgl_jurnal: tglJurnal
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Animasi penghapusan baris yang telah diverifikasi
            const row = document.getElementById(`row-bkk-${bkkId}`);
            if (row) {
                row.style.transition = 'opacity 0.25s ease-out, transform 0.25s ease-out';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    row.remove();
                    recalculateTabCounts();
                    updateBatchToolbarState();
                    checkIfTableEmpty();
                }, 260);
            }
        } else {
            showEnterpriseAlert('Gagal Verifikasi', data.message || 'Terjadi kesalahan sistem saat menerbitkan jurnal.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Verifikasi</span>
                `;
            }
        }
    } catch (err) {
        showEnterpriseAlert('Kesalahan Koneksi', 'Gagal menghubungi server: ' + err.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Verifikasi</span>
            `;
        }
    }
};

function checkIfTableEmpty() {
    const rows = document.querySelectorAll('.vj-table-row');
    if (rows.length === 0) {
        window.location.reload();
    }
}

// ==========================================================================
// 6. BATCH VERIFICATION MODAL & SUBMISSION
// ==========================================================================
window.openBatchConfirmModal = function () {
    const checkedCheckboxes = document.querySelectorAll('.vj-row-checkbox:checked');
    if (checkedCheckboxes.length === 0) {
        showEnterpriseAlert('Peringatan', 'Pilih minimal satu transaksi Kas Keluar untuk diverifikasi.');
        return;
    }

    // Periksa apakah ada baris terpilih yang belum punya COA
    let unmappedBkk = [];
    let items = [];
    let totalNominal = 0;

    checkedCheckboxes.forEach(cb => {
        const bkkId = cb.value;
        const row = document.getElementById(`row-bkk-${bkkId}`);
        const select = document.getElementById(`coa_select_${bkkId}`);
        const dateInput = document.getElementById(`tgl_jurnal_${bkkId}`);
        const bkkNo = row.getAttribute('data-bkk-no');
        const nominal = parseFloat(row.getAttribute('data-nominal') || 0);

        if (!select || !select.value) {
            unmappedBkk.push(bkkNo);
        } else {
            const selectedOpt = select.options[select.selectedIndex];
            items.push({
                bkk_id: bkkId,
                bkk_no: bkkNo,
                nominal: nominal,
                coa_id: select.value,
                coa_name: selectedOpt ? selectedOpt.textContent.trim() : '-',
                tgl_jurnal: dateInput ? dateInput.value : ''
            });
            totalNominal += nominal;
        }
    });

    if (unmappedBkk.length > 0) {
        showEnterpriseAlert(
            'Pos Akun Beban Belum Lengkap',
            `Transaksi berikut belum dipetakan pos akun bebannya:\n• ${unmappedBkk.slice(0, 5).join('\n• ')}${unmappedBkk.length > 5 ? '\n...dan ' + (unmappedBkk.length - 5) + ' lainnya.' : ''}\n\nSilakan pilih akun beban terlebih dahulu.`
        );
        return;
    }

    // Tampilkan data di modal konfirmasi
    document.getElementById('batchConfirmCount').textContent = `${items.length} Dokumen`;
    document.getElementById('batchConfirmTotal').textContent = `Rp ${formatRupiah(totalNominal)}`;

    const previewList = document.getElementById('batchPreviewList');
    if (previewList) {
        previewList.innerHTML = items.map(item => `
            <div class="vj-preview-row">
                <div>
                    <strong style="font-family: monospace; color: #0f172a;">${item.bkk_no}</strong>
                    <span style="color: #64748b; margin-left: 6px;">${item.coa_name}</span>
                </div>
                <div style="font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">
                    Rp ${formatRupiah(item.nominal)}
                </div>
            </div>
        `).join('');
    }

    // Simpan items sementara di window
    window.vjPendingBatchItems = items;

    const modal = document.getElementById('modalConfirmBatch');
    if (modal) {
        modal.style.display = 'flex';
    }
};

window.closeBatchConfirmModal = function () {
    const modal = document.getElementById('modalConfirmBatch');
    if (modal) {
        modal.style.display = 'none';
    }
    window.vjPendingBatchItems = null;
};

window.submitBatchVerification = async function () {
    if (!window.vjPendingBatchItems || window.vjPendingBatchItems.length === 0) {
        closeBatchConfirmModal();
        return;
    }

    const btnExecute = document.getElementById('btnExecuteBatch');
    const btnCancel = document.getElementById('btnCancelBatch');

    if (btnExecute) {
        btnExecute.disabled = true;
        btnExecute.innerHTML = `
            <svg class="animate-spin" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor" stroke-dasharray="32" stroke-linecap="round" style="opacity: 0.25;"></circle>
            </svg>
            <span>Menerbitkan ${window.vjPendingBatchItems.length} Jurnal...</span>
        `;
    }
    if (btnCancel) btnCancel.disabled = true;

    try {
        const payload = {
            items: window.vjPendingBatchItems.map(it => ({
                bkk_id: parseInt(it.bkk_id, 10),
                coa_id: parseInt(it.coa_id, 10),
                tgl_jurnal: it.tgl_jurnal || null
            }))
        };

        const response = await fetch(window.vjConfig.batchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.vjConfig.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok && data.success) {
            closeBatchConfirmModal();
            window.location.reload();
        } else {
            showEnterpriseAlert('Gagal Verifikasi Masal', data.message || 'Terjadi kesalahan saat memproses verifikasi masal.');
            if (btnExecute) {
                btnExecute.disabled = false;
                btnExecute.innerHTML = `
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Ya, Terbitkan Jurnal Sekarang</span>
                `;
            }
            if (btnCancel) btnCancel.disabled = false;
        }
    } catch (err) {
        showEnterpriseAlert('Kesalahan Koneksi', 'Gagal menghubungi server: ' + err.message);
        if (btnExecute) {
            btnExecute.disabled = false;
            btnExecute.innerHTML = `
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Ya, Terbitkan Jurnal Sekarang</span>
            `;
        }
        if (btnCancel) btnCancel.disabled = false;
    }
};

// ==========================================================================
// 7. DETAIL MODAL (MODAL RINCIAN ITEM BKK)
// ==========================================================================
window.openDetailModal = function (bkkId) {
    closeAllActionDropdowns();

    const row = document.getElementById(`row-bkk-${bkkId}`);
    if (!row) return;

    const bkkNo = row.getAttribute('data-bkk-no');
    const tgl = row.getAttribute('data-tgl');
    const kasBank = row.getAttribute('data-kas-bank');
    const penerima = row.getAttribute('data-penerima');
    const pemohon = row.getAttribute('data-pemohon');
    const metode = row.getAttribute('data-metode');
    const catatan = row.getAttribute('data-catatan');
    const totalFormatted = row.getAttribute('data-nominal-formatted');
    const itemsJson = row.getAttribute('data-items');

    document.getElementById('dtlBkkNo').textContent = bkkNo || '-';
    document.getElementById('dtlTglKeluar').textContent = tgl || '-';
    document.getElementById('dtlKasBank').textContent = kasBank || '-';
    document.getElementById('dtlPenerima').textContent = penerima || '-';
    document.getElementById('dtlPemohon').textContent = pemohon || '-';
    document.getElementById('dtlMetodeBayar').textContent = metode || '-';
    document.getElementById('dtlTotalNominal').textContent = totalFormatted || 'Rp 0';

    const noteWrap = document.getElementById('dtlNoteWrapper');
    const noteText = document.getElementById('dtlNoteText');
    if (catatan && catatan.trim()) {
        noteWrap.style.display = 'block';
        noteText.textContent = catatan;
    } else {
        noteWrap.style.display = 'none';
    }

    const tableBody = document.getElementById('dtlItemsTableBody');
    if (tableBody) {
        try {
            const items = JSON.parse(itemsJson || '[]');
            if (items.length > 0) {
                tableBody.innerHTML = items.map((it, idx) => `
                    <tr>
                        <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                        <td style="font-weight: 600; color: #0f172a;">${escapeHtml(it.uraian)}</td>
                        <td style="text-align: center;">${it.qty} ${escapeHtml(it.satuan)}</td>
                        <td style="text-align: right; font-feature-settings: 'tnum';">Rp ${formatRupiah(it.harga_satuan)}</td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-feature-settings: 'tnum';">Rp ${formatRupiah(it.nominal)}</td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 1rem;">Tidak ada item rincian.</td></tr>`;
            }
        } catch (e) {
            tableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #dc2626; padding: 1rem;">Gagal membaca rincian item.</td></tr>`;
        }
    }

    const linkSlip = document.getElementById('dtlLinkSlip');
    if (linkSlip) {
        linkSlip.href = `${window.vjConfig.showBaseUrl}/${bkkId}`;
    }

    const modal = document.getElementById('modalDetailBkk');
    if (modal) {
        modal.style.display = 'flex';
    }
};

window.closeDetailModal = function () {
    const modal = document.getElementById('modalDetailBkk');
    if (modal) {
        modal.style.display = 'none';
    }
};

// ==========================================================================
// 8. VOID MODAL (PEMBATALAN TRANSAKSI DRAFT)
// ==========================================================================
window.openVoidModal = function (bkkId, bkkNo) {
    closeAllActionDropdowns();

    const form = document.getElementById('formVoidBkk');
    if (form) {
        form.action = `${window.vjConfig.voidBaseUrl}/${bkkId}/void`;
    }

    document.getElementById('voidBkkNo').textContent = bkkNo || '-';
    const inputAlasan = document.getElementById('alasanVoidInput');
    if (inputAlasan) {
        inputAlasan.value = '';
    }

    const modal = document.getElementById('modalVoidBkk');
    if (modal) {
        modal.style.display = 'flex';
    }
};

window.closeVoidModal = function () {
    const modal = document.getElementById('modalVoidBkk');
    if (modal) {
        modal.style.display = 'none';
    }
};

// ==========================================================================
// 9. UTILITIES & INITIALIZATION
// ==========================================================================
function formatRupiah(amount) {
    const num = Math.round(Number(amount) || 0);
    return num.toLocaleString('id-ID');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showEnterpriseAlert(title, message) {
    // Standard Enterprise clean prompt fallback
    alert(`${title}\n\n${message}`);
}

// Global Keyboard Handler (Escape key)
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeAllActionDropdowns();
        closeAllCoaDropdowns();
        closeDetailModal();
        closeDetailModalBkm();
        closeBatchConfirmModal();
        closeBatchConfirmModalBkm();
        closeVoidModal();
        closeVoidModalBkm();
    }
});

// Click outside handler for Searchable COA Pickers
document.addEventListener('click', function (e) {
    if (!e.target.closest('.vj-coa-picker-wrapper')) {
        closeAllCoaDropdowns();
    }
});

// DOMContentLoaded Init
document.addEventListener('DOMContentLoaded', function () {
    if (window.vjConfig && window.vjConfig.activeTab === 'bkm') {
        recalculateTabCountsBkm();
        updateBatchToolbarStateBkm();
    } else {
        recalculateTabCounts();
        updateBatchToolbarState();
    }

    // Auto submit filter form saat tekan Enter di kolom search
    const searchInput = document.getElementById('filterSearch');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const form = document.getElementById('vjFilterForm');
                if (form) form.submit();
            }
        });
    }
});

// ==========================================================================
// 10. SEARCHABLE COA PICKER UNTUK KAS MASUK (BKM)
// ==========================================================================
window.openCoaDropdown = function (bkmId) {
    closeAllCoaDropdowns();
    closeAllActionDropdowns();

    const dropdown = document.getElementById(`coa_dropdown_${bkmId}`);
    const input = document.getElementById(`coa_search_${bkmId}`);
    if (!dropdown || !input) return;

    dropdown.style.display = 'block';
    filterCoaOptions(bkmId, input.value);
};

window.closeAllCoaDropdowns = function () {
    const dropdowns = document.querySelectorAll('.vj-coa-dropdown');
    dropdowns.forEach(dd => {
        dd.style.display = 'none';
    });
};

window.filterCoaOptions = function (bkmId, query) {
    const dropdown = document.getElementById(`coa_dropdown_${bkmId}`);
    if (!dropdown) return;

    dropdown.style.display = 'block';

    const cleanQuery = (query || '').toLowerCase().trim();
    const options = dropdown.querySelectorAll('.vj-coa-option');
    const emptyMsg = dropdown.querySelector('.vj-coa-empty-msg');
    let visibleCount = 0;

    options.forEach(opt => {
        const kode = (opt.getAttribute('data-kode') || '').toLowerCase();
        const nama = (opt.getAttribute('data-nama') || '').toLowerCase();
        const kategori = (opt.getAttribute('data-kategori') || '').toLowerCase();

        if (!cleanQuery || kode.includes(cleanQuery) || nama.includes(cleanQuery) || kategori.includes(cleanQuery)) {
            opt.style.display = 'flex';
            visibleCount++;
        } else {
            opt.style.display = 'none';
        }
        opt.classList.remove('highlighted');
    });

    if (emptyMsg) {
        emptyMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
};

window.selectCoaOption = function (bkmId, coaId, kode, nama) {
    const input = document.getElementById(`coa_search_${bkmId}`);
    const hidden = document.getElementById(`bkm_coa_id_${bkmId}`);
    const clearBtn = document.getElementById(`coa_clear_${bkmId}`);
    const row = document.getElementById(`row-bkm-${bkmId}`);
    const btnVerify = document.getElementById(`btnVerifySingleBkm_${bkmId}`);
    const dropdown = document.getElementById(`coa_dropdown_${bkmId}`);

    if (input) input.value = `${kode} - ${nama}`;
    if (hidden) hidden.value = coaId;
    if (clearBtn) clearBtn.style.display = 'flex';
    if (dropdown) dropdown.style.display = 'none';

    if (row) {
        row.classList.remove('row-unmapped');
        row.classList.add('row-ready');
        row.setAttribute('data-ready', '1');
    }

    if (btnVerify) {
        btnVerify.disabled = false;
    }

    recalculateTabCountsBkm();
    updateBatchToolbarStateBkm();
};

window.clearCoaSelection = function (bkmId) {
    const input = document.getElementById(`coa_search_${bkmId}`);
    const hidden = document.getElementById(`bkm_coa_id_${bkmId}`);
    const clearBtn = document.getElementById(`coa_clear_${bkmId}`);
    const row = document.getElementById(`row-bkm-${bkmId}`);
    const btnVerify = document.getElementById(`btnVerifySingleBkm_${bkmId}`);

    if (input) {
        input.value = '';
        input.focus();
    }
    if (hidden) hidden.value = '';
    if (clearBtn) clearBtn.style.display = 'none';

    if (row) {
        row.classList.remove('row-ready');
        row.classList.add('row-unmapped');
        row.setAttribute('data-ready', '0');

        const checkbox = row.querySelector('.vj-row-checkbox-bkm');
        if (checkbox) {
            checkbox.checked = false;
            row.classList.remove('row-selected');
        }
    }

    if (btnVerify) {
        btnVerify.disabled = true;
    }

    recalculateTabCountsBkm();
    updateBatchToolbarStateBkm();
};

window.handleCoaPickerKey = function (event, bkmId) {
    const dropdown = document.getElementById(`coa_dropdown_${bkmId}`);
    if (!dropdown || dropdown.style.display === 'none') {
        if (event.key === 'ArrowDown' || event.key === 'Enter') {
            openCoaDropdown(bkmId);
            event.preventDefault();
        }
        return;
    }

    const visibleOptions = Array.from(dropdown.querySelectorAll('.vj-coa-option')).filter(o => o.style.display !== 'none');
    if (visibleOptions.length === 0) return;

    let currentIndex = visibleOptions.findIndex(o => o.classList.contains('highlighted'));

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (currentIndex >= 0) visibleOptions[currentIndex].classList.remove('highlighted');
        currentIndex = (currentIndex + 1) % visibleOptions.length;
        visibleOptions[currentIndex].classList.add('highlighted');
        visibleOptions[currentIndex].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (currentIndex >= 0) visibleOptions[currentIndex].classList.remove('highlighted');
        currentIndex = (currentIndex - 1 + visibleOptions.length) % visibleOptions.length;
        visibleOptions[currentIndex].classList.add('highlighted');
        visibleOptions[currentIndex].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'Enter') {
        event.preventDefault();
        if (currentIndex >= 0 && visibleOptions[currentIndex]) {
            visibleOptions[currentIndex].click();
        } else if (visibleOptions.length > 0) {
            visibleOptions[0].click();
        }
    } else if (event.key === 'Escape') {
        dropdown.style.display = 'none';
    }
};

// ==========================================================================
// 11. BKM SINGLE VERIFY
// ==========================================================================
window.verifySingleBkm = function (bkmId) {
    closeAllActionDropdowns();
    closeAllCoaDropdowns();

    const coaInput = document.getElementById(`bkm_coa_id_${bkmId}`);
    const coaId = coaInput ? parseInt(coaInput.value, 10) : null;

    if (!coaId) {
        showEnterpriseAlert('Pilih Akun Kredit Terlebih Dahulu', 'Silakan pilih Akun Kredit (COA Lawan) melalui kolom pencarian sebelum menerbitkan Jurnal Umum BKM ini.');
        const searchInput = document.getElementById(`coa_search_${bkmId}`);
        if (searchInput) searchInput.focus();
        return;
    }

    const tglInput = document.getElementById(`tgl_jurnal_bkm_${bkmId}`);
    const tglJurnal = tglInput ? tglInput.value : '';

    const ketInput = document.getElementById(`bkm_keterangan_${bkmId}`);
    const keteranganJurnal = ketInput ? ketInput.value : '';

    const btn = document.getElementById(`btnVerifySingleBkm_${bkmId}`);
    const originalContent = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span style="display: inline-block; animation: spin 1s linear infinite;">⏳</span> Memproses...`;
    }

    fetch(`${window.vjConfig.singleBkmBaseUrl}/${bkmId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.vjConfig.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            coa_id: coaId,
            tgl_jurnal: tglJurnal,
            keterangan_jurnal: keteranganJurnal
        })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Gagal memverifikasi dan menerbitkan jurnal BKM.');
        }
        return data;
    })
    .then(data => {
        const row = document.getElementById(`row-bkm-${bkmId}`);
        if (row) {
            row.style.transition = 'all 0.3s ease-out';
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            setTimeout(() => {
                row.remove();
                recalculateTabCountsBkm();
                updateBatchToolbarStateBkm();

                const remainingRows = document.querySelectorAll('#vjMainTableBkm .vj-table-row');
                if (remainingRows.length === 0) {
                    window.location.reload();
                }
            }, 300);
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
        showEnterpriseAlert('Gagal Verifikasi BKM', err.message);
    });
};

// ==========================================================================
// 12. BKM BATCH SELECTION & VERIFY
// ==========================================================================
window.onRowCheckboxChangeBkm = function (checkbox) {
    const row = checkbox.closest('.vj-table-row');
    if (row) {
        if (checkbox.checked) {
            row.classList.add('row-selected');
        } else {
            row.classList.remove('row-selected');
        }
    }
    updateBatchToolbarStateBkm();
};

window.toggleSelectAllBkm = function (masterCheckbox) {
    const isChecked = masterCheckbox.checked;
    const rows = Array.from(document.querySelectorAll('#vjMainTableBkm .vj-table-row')).filter(r => r.style.display !== 'none');

    rows.forEach(row => {
        const isReady = row.getAttribute('data-ready') === '1';
        const checkbox = row.querySelector('.vj-row-checkbox-bkm');
        if (checkbox) {
            if (isChecked) {
                if (isReady) {
                    checkbox.checked = true;
                    row.classList.add('row-selected');
                } else {
                    checkbox.checked = false;
                    row.classList.remove('row-selected');
                }
            } else {
                checkbox.checked = false;
                row.classList.remove('row-selected');
            }
        }
    });

    updateBatchToolbarStateBkm();
};

function updateBatchToolbarStateBkm() {
    const checkboxes = document.querySelectorAll('.vj-row-checkbox-bkm:checked');
    const count = checkboxes.length;

    let totalNominal = 0;
    checkboxes.forEach(cb => {
        const row = cb.closest('.vj-table-row');
        if (row) {
            const nominal = parseFloat(row.getAttribute('data-nominal') || 0);
            totalNominal += nominal;
        }
    });

    const badge = document.getElementById('selectedCountBadgeBkm');
    const totalBadge = document.getElementById('selectedTotalBadgeBkm');
    const btnBatch = document.getElementById('btnBatchVerifyBkm');

    if (badge) {
        badge.textContent = `${count} BKM Terpilih`;
        if (count > 0) {
            badge.classList.add('active');
        } else {
            badge.classList.remove('active');
        }
    }

    if (totalBadge) {
        if (count > 0) {
            totalBadge.style.display = 'inline-block';
            totalBadge.textContent = `(Rp ${formatRupiah(totalNominal)})`;
        } else {
            totalBadge.style.display = 'none';
        }
    }

    if (btnBatch) {
        btnBatch.disabled = (count === 0);
    }
}

function recalculateTabCountsBkm() {
    const allRows = document.querySelectorAll('#vjMainTableBkm .vj-table-row');
    let readyCount = 0;
    let unmappedCount = 0;

    allRows.forEach(row => {
        if (row.getAttribute('data-ready') === '1') {
            readyCount++;
        } else {
            unmappedCount++;
        }
    });

    const tabAll = document.getElementById('tabCountAllBkm');
    const tabReady = document.getElementById('tabCountReadyBkm');
    const tabUnmapped = document.getElementById('tabCountUnmappedBkm');

    if (tabAll) tabAll.textContent = allRows.length;
    if (tabReady) tabReady.textContent = readyCount;
    if (tabUnmapped) tabUnmapped.textContent = unmappedCount;
}

window.applyClientFilterBkm = function (filterType, button) {
    const tabs = document.querySelectorAll('.vj-quick-filter-group .vj-quick-tab');
    tabs.forEach(t => t.classList.remove('active'));
    if (button) button.classList.add('active');

    const rows = document.querySelectorAll('#vjMainTableBkm .vj-table-row');
    rows.forEach(row => {
        const isReady = row.getAttribute('data-ready') === '1';
        if (filterType === 'all') {
            row.style.display = '';
        } else if (filterType === 'ready') {
            row.style.display = isReady ? '' : 'none';
        } else if (filterType === 'unmapped') {
            row.style.display = !isReady ? '' : 'none';
        }
    });

    const masterCheckbox = document.getElementById('checkAllReadyBkm');
    if (masterCheckbox) masterCheckbox.checked = false;
    updateBatchToolbarStateBkm();
};

window.openBatchConfirmModalBkm = function () {
    const checkboxes = document.querySelectorAll('.vj-row-checkbox-bkm:checked');
    if (checkboxes.length === 0) return;

    let totalNominal = 0;
    const previewList = document.getElementById('batchPreviewListBkm');
    if (!previewList) return;

    previewList.innerHTML = '';

    checkboxes.forEach(cb => {
        const row = cb.closest('.vj-table-row');
        if (!row) return;

        const bkmNo = row.getAttribute('data-bkm-no');
        const bkmId = row.getAttribute('data-bkm-id');
        const nominal = parseFloat(row.getAttribute('data-nominal') || 0);
        totalNominal += nominal;

        const coaInput = document.getElementById(`coa_search_${bkmId}`);
        const coaLabel = coaInput ? coaInput.value : '-';

        const rowDiv = document.createElement('div');
        rowDiv.className = 'vj-preview-row';
        rowDiv.innerHTML = `
            <div>
                <strong style="color: #0f172a; font-family: monospace;">${escapeHtml(bkmNo)}</strong>
                <span style="color: #64748b; margin-left: 6px;">Kredit: <strong>${escapeHtml(coaLabel)}</strong></span>
            </div>
            <strong style="color: #047857; font-feature-settings: 'tnum';">Rp ${formatRupiah(nominal)}</strong>
        `;
        previewList.appendChild(rowDiv);
    });

    document.getElementById('batchConfirmBkmCount').textContent = `${checkboxes.length} Dokumen`;
    document.getElementById('batchConfirmBkmTotal').textContent = `Rp ${formatRupiah(totalNominal)}`;

    const modal = document.getElementById('modalConfirmBatchBkm');
    if (modal) modal.style.display = 'flex';
};

window.closeBatchConfirmModalBkm = function () {
    const modal = document.getElementById('modalConfirmBatchBkm');
    if (modal) modal.style.display = 'none';
};

window.submitBatchVerificationBkm = function () {
    const checkboxes = document.querySelectorAll('.vj-row-checkbox-bkm:checked');
    if (checkboxes.length === 0) return;

    const items = [];
    for (let cb of checkboxes) {
        const row = cb.closest('.vj-table-row');
        if (!row) continue;

        const bkmId = parseInt(cb.value, 10);
        const coaInput = document.getElementById(`bkm_coa_id_${bkmId}`);
        const coaId = coaInput ? parseInt(coaInput.value, 10) : null;

        if (!coaId) {
            const bkmNo = row.getAttribute('data-bkm-no');
            showEnterpriseAlert('Akun Kredit Belum Dipilih', `Transaksi ${bkmNo} belum memiliki Akun Kredit (COA Lawan). Silakan tentukan terlebih dahulu.`);
            closeBatchConfirmModalBkm();
            return;
        }

        const tglInput = document.getElementById(`tgl_jurnal_bkm_${bkmId}`);
        const ketInput = document.getElementById(`bkm_keterangan_${bkmId}`);

        items.push({
            bkm_id: bkmId,
            coa_id: coaId,
            tgl_jurnal: tglInput ? tglInput.value : null,
            keterangan_jurnal: ketInput ? ketInput.value : null
        });
    }

    const btnExec = document.getElementById('btnExecuteBatchBkm');
    const btnCancel = document.getElementById('btnCancelBatchBkm');

    if (btnExec) {
        btnExec.disabled = true;
        btnExec.innerHTML = `Memproses...`;
    }
    if (btnCancel) btnCancel.disabled = true;

    fetch(window.vjConfig.batchBkmUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.vjConfig.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ items: items })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Gagal memproses verifikasi masal BKM.');
        }
        return data;
    })
    .then(data => {
        closeBatchConfirmModalBkm();
        window.location.reload();
    })
    .catch(err => {
        if (btnExec) {
            btnExec.disabled = false;
            btnExec.innerHTML = `Ya, Terbitkan Jurnal Sekarang`;
        }
        if (btnCancel) btnCancel.disabled = false;
        showEnterpriseAlert('Gagal Verifikasi Masal BKM', err.message);
    });
};

// ==========================================================================
// 13. DETAIL & VOID MODALS BKM
// ==========================================================================
window.openDetailModalBkm = function (bkmId) {
    closeAllActionDropdowns();
    closeAllCoaDropdowns();

    const row = document.getElementById(`row-bkm-${bkmId}`);
    if (!row) return;

    const bkmNo = row.getAttribute('data-bkm-no');
    const tgl = row.getAttribute('data-tgl');
    const kasBank = row.getAttribute('data-kas-bank');
    const penyetor = row.getAttribute('data-penyetor');
    const kasir = row.getAttribute('data-kasir');
    const metode = row.getAttribute('data-metode');
    const catatan = row.getAttribute('data-catatan');
    const totalFormatted = row.getAttribute('data-nominal-formatted');
    const itemsJson = row.getAttribute('data-items');

    document.getElementById('dtlBkmNo').textContent = bkmNo || '-';
    document.getElementById('dtlBkmTgl').textContent = tgl || '-';
    document.getElementById('dtlBkmKasBank').textContent = kasBank || '-';
    document.getElementById('dtlBkmPenyetor').textContent = penyetor || '-';
    document.getElementById('dtlBkmKasir').textContent = kasir || '-';
    document.getElementById('dtlBkmMetode').textContent = metode || '-';
    document.getElementById('dtlBkmTotalNominal').textContent = totalFormatted || 'Rp 0';

    const noteWrap = document.getElementById('dtlBkmNoteWrapper');
    const noteText = document.getElementById('dtlBkmNoteText');
    if (catatan && catatan.trim()) {
        noteWrap.style.display = 'block';
        noteText.textContent = catatan;
    } else {
        noteWrap.style.display = 'none';
    }

    const tableBody = document.getElementById('dtlBkmItemsTableBody');
    if (tableBody) {
        try {
            const items = JSON.parse(itemsJson || '[]');
            if (items.length > 0) {
                tableBody.innerHTML = items.map((it, idx) => `
                    <tr>
                        <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                        <td style="font-weight: 600; color: #0f172a;">${escapeHtml(it.uraian)}</td>
                        <td style="text-align: center;">${it.qty} ${escapeHtml(it.satuan)}</td>
                        <td style="text-align: right; font-feature-settings: 'tnum';">Rp ${formatRupiah(it.harga_satuan)}</td>
                        <td style="text-align: right; font-weight: 700; color: #047857; font-feature-settings: 'tnum';">Rp ${formatRupiah(it.nominal)}</td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 1rem;">Tidak ada item rincian.</td></tr>`;
            }
        } catch (e) {
            tableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #dc2626; padding: 1rem;">Gagal membaca rincian item.</td></tr>`;
        }
    }

    const linkSlip = document.getElementById('dtlBkmLinkSlip');
    if (linkSlip) {
        linkSlip.href = `${window.vjConfig.showBkmBaseUrl}/${bkmId}`;
    }

    const modal = document.getElementById('modalDetailBkm');
    if (modal) modal.style.display = 'flex';
};

window.closeDetailModalBkm = function () {
    const modal = document.getElementById('modalDetailBkm');
    if (modal) modal.style.display = 'none';
};

window.openVoidModalBkm = function (bkmId, bkmNo) {
    closeAllActionDropdowns();
    closeAllCoaDropdowns();

    const form = document.getElementById('formVoidBkm');
    if (form) {
        form.action = `${window.vjConfig.voidBkmBaseUrl}/${bkmId}/void`;
    }

    document.getElementById('voidBkmNo').textContent = bkmNo || '-';
    const inputAlasan = document.getElementById('alasanVoidBkmInput');
    if (inputAlasan) {
        inputAlasan.value = '';
    }

    const modal = document.getElementById('modalVoidBkm');
    if (modal) modal.style.display = 'flex';
};

window.closeVoidModalBkm = function () {
    const modal = document.getElementById('modalVoidBkm');
    if (modal) modal.style.display = 'none';
};
