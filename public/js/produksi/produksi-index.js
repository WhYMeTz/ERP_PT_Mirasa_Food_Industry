/**
 * JavaScript Modul Rekap Produksi Harian & HPP
 * Path: public/js/produksi/produksi-index.js
 * PT Mirasa Food Industry
 */

let activeDropdownMenu = null;

/**
 * Smart Action Dropdown dengan Fixed Positioning
 * Mencegah menu terpotong overflow scroll horizontal tabel
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Jika menu ini sudah terbuka, tutup
    if (activeDropdownMenu === menu) {
        menu.classList.remove('show');
        activeDropdownMenu = null;
        return;
    }

    // Tutup menu lain yang sedang aktif
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
    }

    // Hitung posisi tombol di layar
    const rect = button.getBoundingClientRect();
    const menuWidth = 190;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    // Tentukan posisi horizontal (utamakan rata kanan tombol)
    let left = rect.right - menuWidth;
    if (left < 10) left = 10;
    if (left + menuWidth > windowWidth - 10) {
        left = windowWidth - menuWidth - 10;
    }

    // Tentukan posisi vertikal (bawah tombol, atau atas jika mepet bawah)
    let top = rect.bottom + 4;
    const estimatedHeight = 120;
    if (top + estimatedHeight > windowHeight - 10 && rect.top > estimatedHeight) {
        top = rect.top - estimatedHeight - 4;
    }

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
    menu.classList.add('show');
    activeDropdownMenu = menu;
}

// Tutup dropdown jika klik di luar atau saat scroll window
document.addEventListener('click', function (e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
});

window.addEventListener('resize', function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
});

/**
 * Buka modal konfirmasi hapus lembar produksi harian
 */
function openDeleteProduksiModal(id, no, tgl, shift, batch) {
    const modal = document.getElementById('modalDeleteProduksi');
    const form = document.getElementById('formDeleteProduksi');
    const txtNo = document.getElementById('deleteProduksiNo');
    const txtTgl = document.getElementById('deleteProduksiTgl');
    const txtShift = document.getElementById('deleteProduksiShift');
    const txtBatch = document.getElementById('deleteProduksiBatch');

    if (!modal || !form) return;

    // Buat route URL hapus
    form.action = `/produksi/${id}`;

    if (txtNo) txtNo.textContent = no || '-';
    if (txtTgl) txtTgl.textContent = tgl || '-';
    if (txtShift) txtShift.textContent = shift ? `Shift ${shift}` : '-';
    if (txtBatch) txtBatch.textContent = batch || '-';

    // Tutup dropdown jika masih terbuka
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }

    modal.classList.add('show');
}

/**
 * Tutup modal konfirmasi hapus
 */
function closeDeleteProduksiModal() {
    const modal = document.getElementById('modalDeleteProduksi');
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Tab Switching: Tab 1 (Hasil Produksi) vs Tab 2 (Rekap HPP)
 */
function switchProduksiTab(tabName) {
    const btnHasil = document.getElementById('btn-tab-hasil');
    const btnRekap = document.getElementById('btn-tab-rekap');
    const paneHasil = document.getElementById('pane-tab-hasil');
    const paneRekap = document.getElementById('pane-tab-rekap');

    if (!paneHasil || !paneRekap) return;

    if (tabName === 'hasil') {
        btnHasil?.classList.add('active');
        btnRekap?.classList.remove('active');
        paneHasil.classList.add('active');
        paneRekap.classList.remove('active');
    } else {
        btnRekap?.classList.add('active');
        btnHasil?.classList.remove('active');
        paneRekap.classList.add('active');
        paneHasil.classList.remove('active');
    }

    // Perbarui URL tanpa reload penuh agar bookmark/refresh konsisten
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

/**
 * Kontrol Modal Import Excel Hasil Produksi
 */
function openModalImportHasil() {
    const modal = document.getElementById('modal-import-hasil');
    if (modal) modal.style.display = 'flex';
}

function closeModalImportHasil() {
    const modal = document.getElementById('modal-import-hasil');
    if (modal) modal.style.display = 'none';
}

/**
 * Kontrol Modal Import Excel Rekap HPP
 */
function openModalImportRekap() {
    const modal = document.getElementById('modal-import-rekap');
    if (modal) modal.style.display = 'flex';
}

function closeModalImportRekap() {
    const modal = document.getElementById('modal-import-rekap');
    if (modal) modal.style.display = 'none';
}

/**
 * ══════════════════════════════════════════════════════════════════
 * KONTROL EXECUTIVE COMPACT ACCORDION & VIEW MODE SWITCHER
 * ══════════════════════════════════════════════════════════════════
 */

/**
 * Toggle Baris Detail Akordion Biaya
 */
function toggleCostAccordion(dayNum, btn) {
    const detailRow = document.getElementById('detail-row-' + dayNum);
    const mainRow = document.getElementById('main-row-' + dayNum);
    if (!detailRow) return;

    const isShowing = detailRow.classList.contains('show');
    if (isShowing) {
        detailRow.classList.remove('show');
        if (mainRow) mainRow.classList.remove('row-expanded');
        if (btn) btn.classList.remove('active');
    } else {
        detailRow.classList.add('show');
        if (mainRow) mainRow.classList.add('row-expanded');
        if (btn) btn.classList.add('active');
    }
}

/**
 * Switch Antara Mode Tampilan Ringkas (Compact) dan Mode Spreadsheet (Excel)
 */
function switchHppViewMode(mode) {
    const compactContainer = document.getElementById('compact-view-container');
    const spreadsheetContainer = document.getElementById('spreadsheet-view-container');
    const btnCompact = document.getElementById('btn-mode-compact');
    const btnSpreadsheet = document.getElementById('btn-mode-spreadsheet');

    if (mode === 'compact') {
        if (compactContainer) compactContainer.style.display = 'block';
        if (spreadsheetContainer) spreadsheetContainer.style.display = 'none';
        if (btnCompact) btnCompact.classList.add('active');
        if (btnSpreadsheet) btnSpreadsheet.classList.remove('active');
        localStorage.setItem('mirasa_hpp_view_mode', 'compact');
    } else {
        if (compactContainer) compactContainer.style.display = 'none';
        if (spreadsheetContainer) spreadsheetContainer.style.display = 'block';
        if (btnCompact) btnCompact.classList.remove('active');
        if (btnSpreadsheet) btnSpreadsheet.classList.add('active');
        localStorage.setItem('mirasa_hpp_view_mode', 'spreadsheet');
    }
}

// Inisialisasi preferensi view mode saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
    const savedMode = localStorage.getItem('mirasa_hpp_view_mode') || 'compact';
    switchHppViewMode(savedMode);
});

/* ═════════════════════════════════════════════════════════════
   MODAL REKONSILIASI PENYESUAIAN BIAYA UTILITAS BULANAN
   ═════════════════════════════════════════════════════════════ */
function openModalAdjustUtilitas() {
    const modal = document.getElementById('modal-adjust-utilitas');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        updateLivePreview();
    }
}

function closeModalAdjustUtilitas() {
    const modal = document.getElementById('modal-adjust-utilitas');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
    }
}

function toggleListrikSection() {
    const chk = document.getElementById('chk_adjust_listrik');
    const sec = document.getElementById('section_input_listrik');
    if (sec && chk) {
        sec.style.display = chk.checked ? 'block' : 'none';
    }
    updateLivePreview();
}

function toggleCngSection() {
    const chk = document.getElementById('chk_adjust_cng');
    const sec = document.getElementById('section_input_cng');
    if (sec && chk) {
        sec.style.display = chk.checked ? 'block' : 'none';
    }
    updateLivePreview();
}

function toggleListrikMode() {
    const mode = document.querySelector('input[name="mode_alokasi_listrik"]:checked')?.value || 'tarif_per_kg';
    const boxTarif = document.getElementById('listrik_box_tarif');
    const boxTotal = document.getElementById('listrik_box_total');
    if (boxTarif && boxTotal) {
        if (mode === 'tarif_per_kg') {
            boxTarif.style.display = 'block';
            boxTotal.style.display = 'none';
        } else {
            boxTarif.style.display = 'none';
            boxTotal.style.display = 'block';
        }
    }
    updateLivePreview();
}

function toggleCngMode() {
    const mode = document.querySelector('input[name="mode_cng"]:checked')?.value || 'update_tarif';
    const boxTarif = document.getElementById('cng_box_tarif');
    const boxTotal = document.getElementById('cng_box_total');
    if (boxTarif && boxTotal) {
        if (mode === 'update_tarif') {
            boxTarif.style.display = 'block';
            boxTotal.style.display = 'none';
        } else {
            boxTarif.style.display = 'none';
            boxTotal.style.display = 'block';
        }
    }
    updateLivePreview();
}

function toggleDateAccordion() {
    const btn = document.getElementById('btn_toggle_date_preview');
    const container = document.getElementById('container_date_preview');
    if (!container || !btn) return;
    const isShowing = container.classList.contains('show');
    if (isShowing) {
        container.classList.remove('show');
        btn.classList.remove('active');
    } else {
        container.classList.add('show');
        btn.classList.add('active');
    }
}

function updateLivePreview() {
    const modalEl = document.getElementById('modal-adjust-utilitas');
    if (!modalEl) return;

    const totalWip = parseFloat(modalEl.dataset.wip || '0');
    const totalMmbtu = parseFloat(modalEl.dataset.mmbtu || '0');

    const chkListrik = document.getElementById('chk_adjust_listrik')?.checked ?? true;
    const chkCng = document.getElementById('chk_adjust_cng')?.checked ?? true;

    // Listrik
    const modeListrik = document.querySelector('input[name="mode_alokasi_listrik"]:checked')?.value || 'tarif_per_kg';
    const tarifListrik = parseFloat(document.getElementById('listrik_tarif_per_kg')?.value || 0);
    const totalListrikFaktur = parseFloat(document.getElementById('total_listrik_air')?.value || 0);

    // CNG
    const modeCng = document.querySelector('input[name="mode_cng"]:checked')?.value || 'update_tarif';
    const tarifCng = parseFloat(document.getElementById('cng_tarif_baru')?.value || 0);
    const totalCngFaktur = parseFloat(document.getElementById('total_cng_tagihan')?.value || 0);

    // Update 3 Strip KPI
    const kpiListrik = document.getElementById('kpi_summary_listrik');
    if (kpiListrik) {
        if (!chkListrik) {
            kpiListrik.textContent = '-';
        } else {
            const totListrik = modeListrik === 'tarif_per_kg' ? Math.round(totalWip * tarifListrik) : totalListrikFaktur;
            kpiListrik.textContent = 'Rp ' + totListrik.toLocaleString('id-ID');
        }
    }

    const kpiCng = document.getElementById('kpi_summary_cng');
    if (kpiCng) {
        if (!chkCng) {
            kpiCng.textContent = '-';
        } else {
            const totCng = modeCng === 'update_tarif' ? Math.round(totalMmbtu * tarifCng) : totalCngFaktur;
            kpiCng.textContent = 'Rp ' + totCng.toLocaleString('id-ID');
        }
    }

    // Update individual preview rows in accordion (if rendered)
    const rows = modalEl.querySelectorAll('.preview-row');
    rows.forEach(tr => {
        const rowWip = parseFloat(tr.dataset.wip || '0');
        const rowMmbtu = parseFloat(tr.dataset.mmbtu || '0');
        const cellListrik = tr.querySelector('.cell-new-listrik');
        const cellCng = tr.querySelector('.cell-new-cng');

        if (cellListrik) {
            if (!chkListrik) {
                cellListrik.textContent = '-';
                cellListrik.style.color = '#94a3b8';
            } else {
                let val = 0;
                if (modeListrik === 'tarif_per_kg') {
                    val = Math.round(rowWip * tarifListrik);
                } else {
                    val = totalWip > 0 ? Math.round(totalListrikFaktur * (rowWip / totalWip)) : 0;
                }
                cellListrik.textContent = 'Rp ' + val.toLocaleString('id-ID');
                cellListrik.style.color = '#0284c7';
            }
        }

        if (cellCng) {
            if (!chkCng) {
                cellCng.textContent = '-';
                cellCng.style.color = '#94a3b8';
            } else {
                let val = 0;
                if (modeCng === 'update_tarif') {
                    val = Math.round(rowMmbtu * tarifCng);
                } else {
                    val = totalMmbtu > 0 ? Math.round(totalCngFaktur * (rowMmbtu / totalMmbtu)) : 0;
                }
                cellCng.textContent = 'Rp ' + val.toLocaleString('id-ID');
                cellCng.style.color = '#059669';
            }
        }
    });
}



