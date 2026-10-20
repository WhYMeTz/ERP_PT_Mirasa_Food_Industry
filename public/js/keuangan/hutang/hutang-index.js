/**
 * JavaScript Logika Interaktif - Hutang Usaha & Tagihan Supplier (AP)
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
            document.querySelectorAll('.btn-action-trigger').forEach(btn => {
                btn.classList.remove('active');
            });
        }
    });

    // Tutup modal saat klik tombol close atau di luar dialog
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});

/**
 * Toggle Smart Action Dropdown dengan posisi fixed (anti terpotong table overflow)
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isVisible = menu.style.display === 'block';

    // Tutup menu lain yang sedang terbuka
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-action-trigger').forEach(b => {
        b.classList.remove('active');
    });

    if (!isVisible) {
        const rect = button.getBoundingClientRect();
        menu.style.display = 'block';
        button.classList.add('active');

        // Posisikan menu tepat di bawah button
        menu.style.top = `${rect.bottom + 4}px`;
        menu.style.right = `${window.innerWidth - rect.right}px`;
        menu.style.left = 'auto';

        // Deteksi jika menu keluar layar bawah
        const menuRect = menu.getBoundingClientRect();
        if (menuRect.bottom > window.innerHeight) {
            menu.style.top = `${rect.top - menuRect.height - 4}px`;
        }
    }
}

/**
 * Buka Modal Pembatalan Tagihan
 */
function openDeleteHutangModal(hutangId, hutangNo, totalTagihan) {
    const modal = document.getElementById('modalDeleteHutang');
    if (!modal) return;

    document.getElementById('deleteHutangNoText').textContent = hutangNo;
    document.getElementById('deleteHutangValText').textContent = totalTagihan;
    document.getElementById('formDeleteHutang').action = `${window.appConfig.baseUrl}/keuangan/hutang/${hutangId}`;

    modal.classList.add('show');
}

/**
 * Tutup Modal Dialog
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * ==========================================================================
 * QUICK PAY INTERACTION CONTROLLER (BAYAR CEPAT DENGAN KARTU KAS/BANK)
 * ==========================================================================
 */
let qpCurrentHutang = null;

function openQuickPayModal(hutangData) {
    let hutang = hutangData;
    if (typeof hutangData === 'string') {
        try {
            hutang = JSON.parse(hutangData);
        } catch (e) {
            console.error('Invalid JSON for quick pay', e);
            return;
        }
    }

    qpCurrentHutang = hutang;
    const modal = document.getElementById('modalQuickPay');
    if (!modal) return;

    // Tutup dropdown menu aksi
    document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');
    document.querySelectorAll('.btn-action-trigger').forEach(b => b.classList.remove('active'));

    // Populate Data Tagihan
    document.getElementById('qp_hutang_id').value = hutang.hutang_id;
    document.getElementById('qp_supplier_nm').textContent = hutang.supplier_nm || '-';
    document.getElementById('qp_hutang_no').textContent = hutang.hutang_no || '-';
    document.getElementById('qp_sisa_hutang_disp').textContent = formatRupiah(hutang.sisa_hutang);
    document.getElementById('qp_nominal_bayar').value = Math.round(hutang.sisa_hutang);
    document.getElementById('qp_nominal_bayar').max = Math.round(hutang.sisa_hutang);

    if (document.getElementById('qp_penerima_nama')) {
        document.getElementById('qp_penerima_nama').value = hutang.supplier_nm || '';
    }

    // Default mode: 100% Lunas
    setQuickPayMode('full');

    // Auto-select kartu bank pertama yang mencukupi atau kartu pertama
    const bankCards = document.querySelectorAll('.qp-bank-card');
    let cardToSelect = bankCards[0];
    const sisa = parseFloat(hutang.sisa_hutang || 0);

    for (let card of bankCards) {
        const saldo = parseFloat(card.getAttribute('data-saldo') || 0);
        if (saldo >= sisa) {
            cardToSelect = card;
            break;
        }
    }

    if (cardToSelect) {
        selectQuickPayBank(cardToSelect);
    }

    recalculateQuickPayTotals();
    modal.classList.add('show');
}

/**
 * Pilih Kartu Rekening Sumber (Visual Card Selector)
 */
function selectQuickPayBank(cardElement) {
    if (!cardElement) return;

    document.querySelectorAll('.qp-bank-card').forEach(c => c.classList.remove('selected'));
    cardElement.classList.add('selected');

    const bankId = cardElement.getAttribute('data-id');
    const tipeAkun = cardElement.getAttribute('data-tipe');

    document.getElementById('qp_kas_bank_id').value = bankId;

    // Auto set metode bayar berdasarkan tipe akun
    const metodeSelect = document.getElementById('qp_metode_bayar');
    if (metodeSelect) {
        if (tipeAkun === 'KAS') {
            metodeSelect.value = 'CASH';
        } else {
            metodeSelect.value = 'TRANSFER';
        }
    }

    recalculateQuickPayTotals();
}

/**
 * Toggle Mode Bayar: 100% Lunas vs Parsial/Cicilan
 */
function setQuickPayMode(mode) {
    const btnFull = document.getElementById('btnModeFull');
    const btnPartial = document.getElementById('btnModePartial');
    const inputNominal = document.getElementById('qp_nominal_bayar');

    if (!qpCurrentHutang || !inputNominal) return;

    const sisaHutang = Math.round(parseFloat(qpCurrentHutang.sisa_hutang || 0));

    if (mode === 'full') {
        btnFull.classList.add('active');
        btnPartial.classList.remove('active');
        inputNominal.value = sisaHutang;
    } else {
        btnFull.classList.remove('active');
        btnPartial.classList.add('active');
        inputNominal.focus();
        inputNominal.select();
    }

    recalculateQuickPayTotals();
}

/**
 * Live Recalculate Sisa Saldo Kas & Validasi
 */
function recalculateQuickPayTotals() {
    const inputNominal = document.getElementById('qp_nominal_bayar');
    const selectedCard = document.querySelector('.qp-bank-card.selected');
    const btnSubmit = document.getElementById('btnSubmitQuickPay');
    const warningBox = document.getElementById('qp_warning_saldo');
    const elSaldoSumber = document.getElementById('qp_calc_saldo_sumber');
    const elNominalBayar = document.getElementById('qp_calc_nominal_bayar');
    const elSisaSaldo = document.getElementById('qp_calc_sisa_saldo');

    if (!inputNominal) return;

    const nominal = parseFloat(inputNominal.value || 0);
    const saldoRekening = selectedCard ? parseFloat(selectedCard.getAttribute('data-saldo') || 0) : 0;
    const sisaHutang = qpCurrentHutang ? parseFloat(qpCurrentHutang.sisa_hutang || 0) : 0;
    const sisaSaldoSesudah = saldoRekening - nominal;

    if (elSaldoSumber) elSaldoSumber.textContent = formatRupiah(saldoRekening);
    if (elNominalBayar) elNominalBayar.textContent = formatRupiah(nominal);

    if (elSisaSaldo) {
        elSisaSaldo.textContent = (sisaSaldoSesudah < 0 ? '-' : '') + formatRupiah(Math.abs(sisaSaldoSesudah));
        elSisaSaldo.style.color = (sisaSaldoSesudah < 0) ? '#dc2626' : '#059669';
    }

    // Validasi submit button
    const isValidAmount = (nominal > 0 && nominal <= (sisaHutang + 0.01));
    const isSufficientBalance = (sisaSaldoSesudah >= 0);

    if (warningBox) {
        if (!isSufficientBalance && nominal > 0) {
            warningBox.style.display = 'block';
            warningBox.textContent = `⚠️ Saldo rekening sumber (${formatRupiah(saldoRekening)}) tidak mencukupi untuk pembayaran sebesar ${formatRupiah(nominal)}.`;
        } else if (nominal > sisaHutang) {
            warningBox.style.display = 'block';
            warningBox.textContent = `⚠️ Nominal bayar (${formatRupiah(nominal)}) melebihi sisa hutang (${formatRupiah(sisaHutang)}).`;
        } else {
            warningBox.style.display = 'none';
        }
    }

    if (btnSubmit) {
        btnSubmit.disabled = (!isValidAmount || !isSufficientBalance || !selectedCard);
    }
}

// Inisialisasi event input nominal
document.addEventListener('DOMContentLoaded', function () {
    const inputNominal = document.getElementById('qp_nominal_bayar');
    if (inputNominal) {
        inputNominal.addEventListener('input', function () {
            const btnFull = document.getElementById('btnModeFull');
            const btnPartial = document.getElementById('btnModePartial');
            const val = parseFloat(this.value || 0);
            const sisa = qpCurrentHutang ? Math.round(parseFloat(qpCurrentHutang.sisa_hutang || 0)) : 0;

            if (val === sisa) {
                btnFull?.classList.add('active');
                btnPartial?.classList.remove('active');
            } else {
                btnFull?.classList.remove('active');
                btnPartial?.classList.add('active');
            }

            recalculateQuickPayTotals();
        });
    }
});

function formatRupiah(nominal) {
    const num = Math.round(parseFloat(nominal) || 0);
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

