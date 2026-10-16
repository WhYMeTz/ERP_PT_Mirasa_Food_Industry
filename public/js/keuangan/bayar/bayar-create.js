/**
 * JavaScript Logika Form Pembayaran Hutang / BKK - ERP PT Mirasa
 * Modern Split-Screen 2-Panel (Master Supplier Directory & Detail Invoices)
 */

document.addEventListener('DOMContentLoaded', function () {
    initKasBankSelector();
    initSupplierDirectory();
    initSupplierSearch();
    initInvoiceSearch();
    initTableEvents();
    initHeadCheckbox();
    initFormSubmitProtection();

    // Inisialisasi awal
    updateAccountBalancePreview();
    activateInitialSupplier();
});

/**
 * 1. SELEKTOR REKENING KAS / BANK
 */
function initKasBankSelector() {
    const kasBankSelect = document.getElementById('kasBankSelect');
    if (kasBankSelect) {
        kasBankSelect.addEventListener('change', updateAccountBalancePreview);
    }
}

function updateAccountBalancePreview() {
    const select = document.getElementById('kasBankSelect');
    if (!select) return;

    const opt = select.options[select.selectedIndex];
    const saldo = parseFloat(opt?.getAttribute('data-saldo') || 0);

    const elBadge = document.getElementById('fastSaldoBadge');
    const elSaldoTersedia = document.getElementById('previewSaldoTersedia');

    const formattedSaldo = 'Rp ' + Math.round(saldo).toLocaleString('id-ID');

    if (elBadge) elBadge.textContent = 'Saldo: ' + formattedSaldo;
    if (elSaldoTersedia) elSaldoTersedia.textContent = formattedSaldo;

    recalculatePaymentTotals();
}

/**
 * 2. MASTER SUPPLIER DIRECTORY (PANEL KIRI)
 */
function initSupplierDirectory() {
    const container = document.getElementById('supplierListContainer');
    if (!container) return;

    container.addEventListener('click', function (e) {
        const item = e.target.closest('.supplier-master-item');
        if (!item) return;

        selectSupplier(item);
    });
}

function selectSupplier(itemElement) {
    document.querySelectorAll('.supplier-master-item').forEach(el => el.classList.remove('active'));
    itemElement.classList.add('active');

    const suppId = itemElement.getAttribute('data-supplier-id') || '';
    const name = itemElement.getAttribute('data-name') || '-';
    const count = itemElement.getAttribute('data-count') || '0';
    const total = parseFloat(itemElement.getAttribute('data-total') || 0);

    // Update hidden input
    const hiddenSupp = document.getElementById('selectedSupplierId');
    if (hiddenSupp) hiddenSupp.value = suppId;

    // Update banner di panel kanan
    const elName = document.getElementById('bannerSupplierName');
    const elSub = document.getElementById('bannerTagihanSub');
    const elTotal = document.getElementById('bannerTotalDebt');

    if (elName) elName.textContent = name;
    if (elSub) elSub.textContent = `${count} Faktur Belum Lunas`;
    if (elTotal) elTotal.textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');

    // Reset pencarian faktur
    const searchInv = document.getElementById('searchInvoiceInput');
    if (searchInv) searchInv.value = '';

    filterInvoicesForActiveSupplier();
}

function activateInitialSupplier() {
    let activeItem = document.querySelector('.supplier-master-item.active');
    if (!activeItem) {
        activeItem = document.querySelector('.supplier-master-item');
    }

    if (activeItem) {
        selectSupplier(activeItem);
    } else {
        filterInvoicesForActiveSupplier();
    }
}

/**
 * Filter Supplier di Panel Kiri
 */
function initSupplierSearch() {
    const input = document.getElementById('searchSupplierInput');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const items = document.querySelectorAll('.supplier-master-item');
        let visibleCount = 0;

        items.forEach(item => {
            const name = (item.getAttribute('data-name') || '').toLowerCase();
            if (!q || name.includes(q)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const emptyMsg = document.getElementById('suppEmptySearch');
        if (emptyMsg) {
            emptyMsg.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
        }
    });
}

/**
 * 3. FILTER FAKTUR SESUAI SUPPLIER AKTIF & PENCARIAN
 */
function initInvoiceSearch() {
    const searchInput = document.getElementById('searchInvoiceInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterInvoicesForActiveSupplier);
    }
}

function filterInvoicesForActiveSupplier() {
    const hiddenSupp = document.getElementById('selectedSupplierId');
    const activeSuppId = hiddenSupp ? hiddenSupp.value : '';

    const searchInput = document.getElementById('searchInvoiceInput');
    const q = searchInput ? searchInput.value.toLowerCase().trim() : '';

    const rows = document.querySelectorAll('.invoice-table-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowSuppId = row.getAttribute('data-supplier-id') || '';
        const rowText = row.textContent.toLowerCase();

        const matchesSupplier = (rowSuppId === activeSuppId);
        const matchesQuery = (!q || rowText.includes(q));

        if (matchesSupplier && matchesQuery) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const emptyRow = document.getElementById('invoicesEmptyState');
    if (emptyRow) {
        emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
    }

    const checkAllHead = document.getElementById('checkAllHead');
    if (checkAllHead) {
        checkAllHead.checked = false;
    }

    recalculatePaymentTotals();
}

/**
 * 4. EVENT LISTENER TABEL FAKTUR & INPUT NOMINAL
 */
function initTableEvents() {
    const tbody = document.getElementById('invoiceTableBody');
    if (!tbody) return;

    tbody.addEventListener('input', function (e) {
        if (e.target.matches('.fast-nominal-input')) {
            const row = e.target.closest('.invoice-table-row');
            const chk = row?.querySelector('.invoice-chk');
            const sisa = parseFloat(row?.getAttribute('data-sisa') || 0);
            let val = parseFloat(e.target.value || 0);

            if (val > sisa) {
                val = sisa;
                e.target.value = sisa;
            } else if (val < 0) {
                val = 0;
                e.target.value = 0;
            }

            if (chk) {
                chk.checked = (val > 0);
                row.classList.toggle('row-selected', val > 0);
            }

            recalculatePaymentTotals();
        }
    });
}

function initHeadCheckbox() {
    const chkHead = document.getElementById('checkAllHead');
    if (chkHead) {
        chkHead.addEventListener('change', function () {
            if (this.checked) {
                selectAllVisibleInvoices();
            } else {
                clearAllInvoices();
            }
        });
    }
}

/**
 * Toggle Baris lewat Checkbox
 */
window.toggleInvoiceRow = function (hutangId) {
    const row = document.querySelector(`.invoice-table-row[data-hutang-id="${hutangId}"]`);
    const chk = document.getElementById(`chk_hutang_${hutangId}`);
    const input = document.getElementById(`nominal_bayar_${hutangId}`);
    if (!row || !chk || !input) return;

    if (chk.checked) {
        row.classList.add('row-selected');
        if (parseFloat(input.value || 0) <= 0) {
            const sisa = parseFloat(row.getAttribute('data-sisa') || 0);
            input.value = sisa;
        }
    } else {
        row.classList.remove('row-selected');
        input.value = 0;
    }

    recalculatePaymentTotals();
};

/**
 * Shortcut Cepat: Bayar 100% Lunas
 */
window.payFullInvoice = function (hutangId) {
    const row = document.querySelector(`.invoice-table-row[data-hutang-id="${hutangId}"]`);
    const chk = document.getElementById(`chk_hutang_${hutangId}`);
    const input = document.getElementById(`nominal_bayar_${hutangId}`);
    if (!row || !chk || !input) return;

    const sisa = parseFloat(row.getAttribute('data-sisa') || 0);
    input.value = sisa;
    chk.checked = true;
    row.classList.add('row-selected');

    recalculatePaymentTotals();
};

/**
 * Shortcut Cepat: Reset Nominal ke 0
 */
window.resetInvoiceRow = function (hutangId) {
    const row = document.querySelector(`.invoice-table-row[data-hutang-id="${hutangId}"]`);
    const chk = document.getElementById(`chk_hutang_${hutangId}`);
    const input = document.getElementById(`nominal_bayar_${hutangId}`);
    if (!row || !chk || !input) return;

    input.value = 0;
    chk.checked = false;
    row.classList.remove('row-selected');

    recalculatePaymentTotals();
};

/**
 * Shortcut Toolbar: Bayar Penuh Semua yang Terlihat
 */
window.selectAllVisibleInvoices = function () {
    document.querySelectorAll('.invoice-table-row').forEach(row => {
        if (row.style.display !== 'none') {
            const chk = row.querySelector('.invoice-chk');
            const input = row.querySelector('.fast-nominal-input');
            const sisa = parseFloat(row.getAttribute('data-sisa') || 0);

            if (chk && input) {
                chk.checked = true;
                row.classList.add('row-selected');
                input.value = sisa;
            }
        }
    });

    const chkHead = document.getElementById('checkAllHead');
    if (chkHead) chkHead.checked = true;

    recalculatePaymentTotals();
};

/**
 * Shortcut Toolbar: Batalkan Semua Pilihan Faktur yang Terlihat
 */
window.clearAllInvoices = function () {
    document.querySelectorAll('.invoice-table-row').forEach(row => {
        if (row.style.display !== 'none') {
            const chk = row.querySelector('.invoice-chk');
            const input = row.querySelector('.fast-nominal-input');

            if (chk && input) {
                chk.checked = false;
                row.classList.remove('row-selected');
                input.value = 0;
            }
        }
    });

    const chkHead = document.getElementById('checkAllHead');
    if (chkHead) chkHead.checked = false;

    recalculatePaymentTotals();
};

/**
 * 5. HITUNG TOTAL & MONITORING SALDO KAS
 */
function recalculatePaymentTotals() {
    let totalBayar = 0;
    let countChecked = 0;

    // Hanya hitung baris yang saat ini terlihat (milik supplier yang aktif)
    document.querySelectorAll('.invoice-table-row').forEach(row => {
        if (row.style.display !== 'none') {
            const chk = row.querySelector('.invoice-chk');
            const input = row.querySelector('.fast-nominal-input');
            const nominal = parseFloat(input?.value || 0);

            if (chk && chk.checked && nominal > 0) {
                totalBayar += nominal;
                countChecked++;
            }
        }
    });

    // Update elemen strip summary
    const elCount = document.getElementById('summaryCountInvoices');
    const elTotal = document.getElementById('summaryTotalBayar');
    const btnSubmit = document.getElementById('btnSubmitBayar');

    if (elCount) elCount.textContent = `${countChecked} Faktur`;
    if (elTotal) elTotal.textContent = 'Rp ' + Math.round(totalBayar).toLocaleString('id-ID');

    // Monitoring Saldo Kas/Bank
    const select = document.getElementById('kasBankSelect');
    const opt = select?.options[select.selectedIndex];
    const saldoTersedia = parseFloat(opt?.getAttribute('data-saldo') || 0);
    const sisaSaldoSesudah = saldoTersedia - totalBayar;

    const elSisaSaldo = document.getElementById('previewSaldoSesudah');
    const boxWarning = document.getElementById('boxSaldoWarning');

    if (elSisaSaldo) {
        elSisaSaldo.textContent = (sisaSaldoSesudah < 0 ? '-' : '') + 'Rp ' + Math.round(Math.abs(sisaSaldoSesudah)).toLocaleString('id-ID');
        elSisaSaldo.style.color = (sisaSaldoSesudah < 0) ? '#dc2626' : (totalBayar > 0 ? '#059669' : '#0f172a');
    }

    if (boxWarning) {
        boxWarning.style.display = (totalBayar > 0 && sisaSaldoSesudah < 0) ? 'inline-block' : 'none';
    }

    // Validasi Tombol Submit
    if (btnSubmit) {
        btnSubmit.disabled = (totalBayar <= 0 || sisaSaldoSesudah < 0);
    }
}

/**
 * 6. PERLINDUNGAN SUBMIT FORM
 * Menonaktifkan baris yang tidak dicentang atau di luar supplier aktif
 */
function initFormSubmitProtection() {
    const formBayar = document.getElementById('formBayarHutang');
    if (formBayar) {
        formBayar.addEventListener('submit', function () {
            document.querySelectorAll('.invoice-table-row').forEach(row => {
                const isHidden = (row.style.display === 'none');
                const chk = row.querySelector('.invoice-chk');
                const input = row.querySelector('.fast-nominal-input');
                const hiddenId = row.querySelector('input[type="hidden"][name*="hutang_id"]');
                const nominal = parseFloat(input?.value || 0);

                if (isHidden || !chk || !chk.checked || nominal <= 0) {
                    if (input) input.disabled = true;
                    if (hiddenId) hiddenId.disabled = true;
                }
            });
        });
    }
}
