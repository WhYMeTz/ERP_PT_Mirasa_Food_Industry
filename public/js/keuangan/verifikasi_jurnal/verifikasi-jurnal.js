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
        closeDetailModal();
        closeBatchConfirmModal();
        closeVoidModal();
    }
});

// DOMContentLoaded Init
document.addEventListener('DOMContentLoaded', function () {
    recalculateTabCounts();
    updateBatchToolbarState();

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
