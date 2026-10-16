/**
 * Master Kas & Rekening Bank - Index Interaction Scripts
 * ERP PT Mirasa Food Industry
 */

document.addEventListener('DOMContentLoaded', function () {
    initSmartDropdowns();
    initModalTipeAkunHandlers();
    initTransferCalculator();
    initPenyesuaianCalculator();
});

/**
 * 1. SMART ACTION DROPDOWN CONTROLLER (Standard PO AGENTS.md)
 */
let activeMenu = null;
let activeTrigger = null;

function toggleSmartActionDropdown(btn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Jika menu yang sama sedang terbuka, tutup
    if (activeMenu === menu) {
        closeSmartDropdown();
        return;
    }

    // Tutup menu sebelumnya jika ada
    closeSmartDropdown();

    // Tampilkan menu
    menu.style.display = 'block';
    menu.style.visibility = 'hidden'; // sembunyikan sejenak untuk ukur dimensi

    const btnRect = btn.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 180;
    const menuHeight = menu.offsetHeight || 120;
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    // Posisi horizontal: jika tidak muat di kanan, geser ke kiri tombol
    let left = btnRect.left;
    if (left + menuWidth > viewportWidth - 10) {
        left = btnRect.right - menuWidth;
    }
    if (left < 10) left = 10;

    // Posisi vertikal: jika tidak muat di bawah, buka di atas tombol
    let top = btnRect.bottom + 4;
    if (top + menuHeight > viewportHeight - 10) {
        top = btnRect.top - menuHeight - 4;
    }

    menu.style.left = `${left}px`;
    menu.style.top = `${top}px`;
    menu.style.visibility = 'visible';

    btn.classList.add('active');
    activeMenu = menu;
    activeTrigger = btn;
}

function closeSmartDropdown() {
    if (activeMenu) {
        activeMenu.style.display = 'none';
        activeMenu = null;
    }
    if (activeTrigger) {
        activeTrigger.classList.remove('active');
        activeTrigger = null;
    }
}

function initSmartDropdowns() {
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (activeMenu && !activeMenu.contains(e.target) && (!activeTrigger || !activeTrigger.contains(e.target))) {
            closeSmartDropdown();
        }
    });

    // Tutup dropdown saat scrolling tabel/halaman
    window.addEventListener('scroll', function () {
        if (activeMenu) {
            closeSmartDropdown();
        }
    }, true);
}

/**
 * 2. MODAL CONTROLLER HELPER
 */
function openModal(modalId) {
    closeSmartDropdown();
    const modalBackdrop = document.getElementById(modalId);
    if (modalBackdrop) {
        modalBackdrop.style.display = 'flex';
        setTimeout(() => {
            modalBackdrop.classList.add('show');
        }, 10);
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modalBackdrop = document.getElementById(modalId);
    if (modalBackdrop) {
        modalBackdrop.classList.remove('show');
        setTimeout(() => {
            modalBackdrop.style.display = 'none';
        }, 200);
        document.body.style.overflow = '';
    }
}

// Tutup modal jika klik backdrop di luar area box modal
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('kb-modal-backdrop')) {
        closeModal(e.target.id);
    }
});

/**
 * 3. LOGIKA FORM TAMBAH & EDIT AKUN (KAS vs BANK)
 */
function initModalTipeAkunHandlers() {
    const createTipe = document.getElementById('create_tipe_akun');
    if (createTipe) {
        createTipe.addEventListener('change', function () {
            toggleBankFields(this.value, 'create');
        });
        toggleBankFields(createTipe.value, 'create');
    }

    const editTipe = document.getElementById('edit_tipe_akun');
    if (editTipe) {
        editTipe.addEventListener('change', function () {
            toggleBankFields(this.value, 'edit');
        });
    }
}

function toggleBankFields(tipe, prefix) {
    const bankSection = document.getElementById(`${prefix}_bank_section`);
    const bankNamaInput = document.getElementById(`${prefix}_bank_nama`);
    const noRekInput = document.getElementById(`${prefix}_nomor_rekening`);

    if (bankSection) {
        if (tipe === 'KAS') {
            bankSection.style.display = 'none';
            if (bankNamaInput) bankNamaInput.value = 'TUNAI';
            if (noRekInput) noRekInput.value = '';
        } else {
            bankSection.style.display = 'block';
            if (bankNamaInput && bankNamaInput.value === 'TUNAI') bankNamaInput.value = '';
        }
    }
}

/**
 * 4. BUKA MODAL EDIT DENGAN DATA PRE-POPULATED
 */
function openEditModal(accountJson) {
    closeSmartDropdown();
    let acc = accountJson;

    if (acc && (acc instanceof HTMLElement || acc.getAttribute)) {
        const raw = acc.getAttribute('data-account');
        try {
            acc = JSON.parse(raw);
        } catch (e) {
            console.error('Failed to parse data-account', e);
            return;
        }
    } else if (typeof accountJson === 'string') {
        try {
            acc = JSON.parse(accountJson);
        } catch (e) {
            console.error('Failed to parse account JSON', e);
            return;
        }
    }

    if (!acc) return;

    const form = document.getElementById('formEditKasBank');
    if (!form) return;

    const baseUrl = (window.appConfig && window.appConfig.baseUrl) ? window.appConfig.baseUrl : '';
    form.action = `${baseUrl}/keuangan/kas-bank/${acc.kas_bank_id}`;

    const codeEl = document.getElementById('edit_kode_akun_display');
    if (codeEl) codeEl.value = acc.kode_akun || '';

    const namaEl = document.getElementById('edit_nama_akun');
    if (namaEl) namaEl.value = acc.nama_akun || '';

    const tipeEl = document.getElementById('edit_tipe_akun');
    if (tipeEl) tipeEl.value = acc.tipe_akun || '';

    const bankEl = document.getElementById('edit_bank_nama');
    if (bankEl) bankEl.value = acc.bank_nama || '';

    const rekEl = document.getElementById('edit_nomor_rekening');
    if (rekEl) rekEl.value = acc.nomor_rekening || '';

    const anEl = document.getElementById('edit_atas_nama');
    if (anEl) anEl.value = acc.atas_nama || '';

    const cabEl = document.getElementById('edit_cabang');
    if (cabEl) cabEl.value = acc.cabang || '';

    const ketEl = document.getElementById('edit_keterangan');
    if (ketEl) ketEl.value = acc.keterangan || '';

    const saldoEl = document.getElementById('edit_saldo_berjalan_display');
    if (saldoEl) saldoEl.value = formatRupiah(acc.saldo_berjalan || 0);

    toggleBankFields(acc.tipe_akun, 'edit');
    openModal('modalEditKasBank');
}

/**
 * 5. BUKA MODAL TRANSFER DANA (PRE-SELECT ASAL)
 */
function openTransferModal(selectedAsalId = null) {
    closeSmartDropdown();
    const selectAsal = document.getElementById('transfer_kas_asal_id');
    if (selectAsal && selectedAsalId) {
        selectAsal.value = selectedAsalId;
    }
    updateTransferCalculation();
    openModal('modalTransferDana');
}

function initTransferCalculator() {
    const selectAsal = document.getElementById('transfer_kas_asal_id');
    const selectTujuan = document.getElementById('transfer_kas_tujuan_id');
    const inputNominal = document.getElementById('transfer_nominal');

    if (selectAsal) selectAsal.addEventListener('change', updateTransferCalculation);
    if (selectTujuan) selectTujuan.addEventListener('change', updateTransferCalculation);
    if (inputNominal) inputNominal.addEventListener('input', updateTransferCalculation);
}

function updateTransferCalculation() {
    const selectAsal = document.getElementById('transfer_kas_asal_id');
    const selectTujuan = document.getElementById('transfer_kas_tujuan_id');
    const inputNominal = document.getElementById('transfer_nominal');
    const calcBox = document.getElementById('transfer_calc_box');
    const calcAsalSaldo = document.getElementById('calc_asal_saldo');
    const calcSisaSaldo = document.getElementById('calc_sisa_saldo');
    const btnSubmit = document.getElementById('btnSubmitTransfer');
    const warnBox = document.getElementById('transfer_warning_box');

    if (!selectAsal || !inputNominal) return;

    const optAsal = selectAsal.options[selectAsal.selectedIndex];
    const saldoAsal = optAsal ? parseFloat(optAsal.getAttribute('data-saldo') || 0) : 0;
    const nominal = parseFloat(inputNominal.value || 0);

    if (calcAsalSaldo) calcAsalSaldo.textContent = formatRupiah(saldoAsal);

    if (nominal > 0) {
        const sisa = saldoAsal - nominal;
        if (calcSisaSaldo) {
            calcSisaSaldo.textContent = formatRupiah(sisa);
            calcSisaSaldo.style.color = (sisa < 0) ? '#dc2626' : '#059669';
        }

        if (sisa < 0) {
            if (warnBox) {
                warnBox.style.display = 'block';
                warnBox.textContent = 'Peringatan: Nominal transfer melebihi saldo akun sumber yang tersedia!';
            }
            if (btnSubmit) btnSubmit.disabled = true;
        } else {
            if (warnBox) warnBox.style.display = 'none';
            if (btnSubmit) btnSubmit.disabled = false;
        }
    } else {
        if (calcSisaSaldo) {
            calcSisaSaldo.textContent = formatRupiah(saldoAsal);
            calcSisaSaldo.style.color = '#334155';
        }
        if (warnBox) warnBox.style.display = 'none';
        if (btnSubmit) btnSubmit.disabled = false;
    }
}

/**
 * 6. BUKA MODAL PENYESUAIAN SALDO FISIK (OPNAME)
 */
function openPenyesuaianModal(accountJson) {
    closeSmartDropdown();
    let acc = accountJson;

    if (acc && (acc instanceof HTMLElement || acc.getAttribute)) {
        const raw = acc.getAttribute('data-account');
        try {
            acc = JSON.parse(raw);
        } catch (e) {
            console.error('Failed to parse data-account', e);
            return;
        }
    } else if (typeof accountJson === 'string') {
        try {
            acc = JSON.parse(accountJson);
        } catch (e) {
            console.error('Failed to parse account JSON', e);
            return;
        }
    }

    if (!acc) return;

    const idInput = document.getElementById('penyesuaian_kas_bank_id');
    if (idInput) idInput.value = acc.kas_bank_id;

    const namaText = document.getElementById('penyesuaian_nama_akun');
    if (namaText) namaText.textContent = `${acc.nama_akun} (${acc.kode_akun})`;

    const sistemInput = document.getElementById('penyesuaian_saldo_sistem');
    if (sistemInput) sistemInput.value = acc.saldo_berjalan;

    const dispText = document.getElementById('penyesuaian_saldo_sistem_disp');
    if (dispText) dispText.textContent = formatRupiah(acc.saldo_berjalan);
    
    const inputFisik = document.getElementById('penyesuaian_saldo_fisik');
    if (inputFisik) inputFisik.value = '';

    updatePenyesuaianCalculation();

    openModal('modalPenyesuaianSaldo');
}

function initPenyesuaianCalculator() {
    const inputFisik = document.getElementById('penyesuaian_saldo_fisik');
    if (inputFisik) {
        inputFisik.addEventListener('input', updatePenyesuaianCalculation);
    }
}

function updatePenyesuaianCalculation() {
    const saldoSistemEl = document.getElementById('penyesuaian_saldo_sistem');
    const inputFisik = document.getElementById('penyesuaian_saldo_fisik');
    const calcSelisih = document.getElementById('calc_selisih_saldo');
    const calcStatus = document.getElementById('calc_status_selisih');
    const btnSubmit = document.getElementById('btnSubmitPenyesuaian');

    if (!saldoSistemEl || !inputFisik) return;

    const saldoSistem = parseFloat(saldoSistemEl.value || 0);
    const valFisik = inputFisik.value.trim();

    if (valFisik === '') {
        if (calcSelisih) calcSelisih.textContent = 'Rp 0';
        if (calcStatus) calcStatus.textContent = '-';
        if (btnSubmit) btnSubmit.disabled = true;
        return;
    }

    const saldoFisik = parseFloat(valFisik);
    const selisih = saldoFisik - saldoSistem;

    if (btnSubmit) {
        btnSubmit.disabled = (selisih === 0);
    }

    if (calcSelisih) {
        const sign = selisih > 0 ? '+' : (selisih < 0 ? '-' : '');
        calcSelisih.textContent = `${sign}${formatRupiah(Math.abs(selisih))}`;
        calcSelisih.style.color = (selisih > 0) ? '#059669' : (selisih < 0 ? '#dc2626' : '#64748b');
    }

    if (calcStatus) {
        if (selisih > 0) {
            calcStatus.innerHTML = '<span style="color: #059669; font-weight: 700;">Surplus Kas (Uang Fisik Lebih Banyak / Mutasi Masuk)</span>';
        } else if (selisih < 0) {
            calcStatus.innerHTML = '<span style="color: #dc2626; font-weight: 700;">Defisit Kas (Uang Fisik Kurang / Mutasi Keluar)</span>';
        } else {
            calcStatus.innerHTML = '<span style="color: #64748b;">Saldo Fisik Sesuai Buku (Tidak Ada Perubahan)</span>';
        }
    }
}

/**
 * 7. BUKA MODAL HAPUS / NONAKTIFKAN (Danger Modal AGENTS.md)
 */
function openDeleteModal(accId, accNama) {
    closeSmartDropdown();
    const form = document.getElementById('formDeleteKasBank');
    if (!form) return;

    const baseUrl = (window.appConfig && window.appConfig.baseUrl) ? window.appConfig.baseUrl : '';
    form.action = `${baseUrl}/keuangan/kas-bank/${accId}`;

    const namaEl = document.getElementById('delete_nama_akun');
    if (namaEl) namaEl.textContent = accNama;

    openModal('modalDeleteKasBank');
}

/**
 * Utility: Format Angka ke Rupiah
 */
function formatRupiah(nominal) {
    const num = Math.round(parseFloat(nominal) || 0);
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Global window mappings for seamless inline onclick execution
window.toggleSmartActionDropdown = toggleSmartActionDropdown;
window.closeSmartDropdown = closeSmartDropdown;
window.openModal = openModal;
window.closeModal = closeModal;
window.openTransferModal = openTransferModal;
window.openModalEdit = openEditModal;
window.openEditModal = openEditModal;
window.openModalPenyesuaian = openPenyesuaianModal;
window.openPenyesuaianModal = openPenyesuaianModal;
window.openModalDelete = openDeleteModal;
window.openDeleteModal = openDeleteModal;
window.formatRupiah = formatRupiah;
