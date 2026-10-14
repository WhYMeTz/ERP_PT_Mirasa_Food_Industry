/**
 * JavaScript Logika Form Pembayaran Hutang / BKK - ERP PT Mirasa
 */

document.addEventListener('DOMContentLoaded', function () {
    const kasBankSelect = document.getElementById('kasBankSelect');
    if (kasBankSelect) {
        kasBankSelect.addEventListener('change', updateAccountBalancePreview);
    }

    // Event listener pada tabel alokasi pembayaran
    const tableBody = document.getElementById('unpaidInvoicesTbody');
    if (tableBody) {
        tableBody.addEventListener('input', function (e) {
            if (e.target.matches('.input-nominal-bayar')) {
                const row = e.target.closest('.unpaid-row');
                const checkbox = row?.querySelector('.row-invoice-check');
                const val = parseFloat(e.target.value || 0);

                // Otomatis centang jika ada nominal > 0
                if (checkbox) {
                    checkbox.checked = (val > 0);
                    row.classList.toggle('checked-row', val > 0);
                }
                recalculatePaymentTotals();
            }
        });
    }

    // Inisialisasi awal
    updateAccountBalancePreview();
    recalculatePaymentTotals();
});

/**
 * Update Preview Saldo Rekening Sumber
 */
function updateAccountBalancePreview() {
    const select = document.getElementById('kasBankSelect');
    if (!select) return;

    const opt = select.options[select.selectedIndex];
    const saldo = parseFloat(opt?.getAttribute('data-saldo') || 0);
    const namaAkun = opt?.getAttribute('data-nama') || 'Rekening';

    const elNama = document.getElementById('previewAkunNama');
    const elSaldo = document.getElementById('previewSaldoTersedia');

    if (elNama) elNama.textContent = namaAkun;
    if (elSaldo) elSaldo.textContent = 'Rp ' + Math.round(saldo).toLocaleString('id-ID');

    recalculatePaymentTotals();
}

/**
 * Toggle Centang Baris Tagihan
 */
window.toggleRowInvoice = function (hutangId) {
    const row = document.querySelector(`.unpaid-row[data-hutang-id="${hutangId}"]`);
    const chk = document.getElementById(`chk_hutang_${hutangId}`);
    const input = document.getElementById(`nominal_bayar_${hutangId}`);
    if (!row || !chk || !input) return;

    if (chk.checked) {
        row.classList.add('checked-row');
        // Jika nominal masih 0, otomatis isi penuh
        if (parseFloat(input.value || 0) <= 0) {
            const sisa = parseFloat(row.getAttribute('data-sisa') || 0);
            input.value = sisa;
        }
    } else {
        row.classList.remove('checked-row');
        input.value = 0;
    }

    recalculatePaymentTotals();
};

/**
 * Bayar Penuh 100% untuk 1 Baris
 */
window.payFullInvoice = function (hutangId) {
    const row = document.querySelector(`.unpaid-row[data-hutang-id="${hutangId}"]`);
    const chk = document.getElementById(`chk_hutang_${hutangId}`);
    const input = document.getElementById(`nominal_bayar_${hutangId}`);
    if (!row || !chk || !input) return;

    const sisa = parseFloat(row.getAttribute('data-sisa') || 0);
    input.value = sisa;
    chk.checked = true;
    row.classList.add('checked-row');

    recalculatePaymentTotals();
};

/**
 * Pilih & Bayar Penuh Semua Tagihan yang Tampil
 */
window.selectAllInvoices = function () {
    document.querySelectorAll('.unpaid-row').forEach(row => {
        const chk = row.querySelector('.row-invoice-check');
        const input = row.querySelector('.input-nominal-bayar');
        const sisa = parseFloat(row.getAttribute('data-sisa') || 0);

        if (chk && input) {
            chk.checked = true;
            row.classList.add('checked-row');
            input.value = sisa;
        }
    });

    recalculatePaymentTotals();
};

/**
 * Batalkan Semua Pilihan
 */
window.clearAllInvoices = function () {
    document.querySelectorAll('.unpaid-row').forEach(row => {
        const chk = row.querySelector('.row-invoice-check');
        const input = row.querySelector('.input-nominal-bayar');

        if (chk && input) {
            chk.checked = false;
            row.classList.remove('checked-row');
            input.value = 0;
        }
    });

    recalculatePaymentTotals();
};

/**
 * Hitung Akumulasi Total Pembayaran & Cek Kecukupan Saldo
 */
function recalculatePaymentTotals() {
    let totalBayar = 0;
    let countChecked = 0;

    document.querySelectorAll('.unpaid-row').forEach(row => {
        const chk = row.querySelector('.row-invoice-check');
        const input = row.querySelector('.input-nominal-bayar');
        const nominal = parseFloat(input?.value || 0);

        if (chk && chk.checked && nominal > 0) {
            totalBayar += nominal;
            countChecked++;
        }
    });

    // Update elemen ringkasan di sticky card
    const elCount = document.getElementById('summaryCountInvoices');
    const elTotal = document.getElementById('summaryTotalBayar');
    const btnSubmit = document.getElementById('btnSubmitBayar');

    if (elCount) elCount.textContent = `${countChecked} Tagihan`;
    if (elTotal) elTotal.textContent = 'Rp ' + Math.round(totalBayar).toLocaleString('id-ID');

    // Cek saldo kas/bank
    const select = document.getElementById('kasBankSelect');
    const opt = select?.options[select.selectedIndex];
    const saldoTersedia = parseFloat(opt?.getAttribute('data-saldo') || 0);
    const sisaSaldoSesudah = saldoTersedia - totalBayar;

    const elSisaSaldo = document.getElementById('previewSaldoSesudah');
    const boxWarning = document.getElementById('boxSaldoWarning');

    if (elSisaSaldo) {
        elSisaSaldo.textContent = 'Rp ' + Math.round(sisaSaldoSesudah).toLocaleString('id-ID');
        elSisaSaldo.style.color = (sisaSaldoSesudah < 0) ? '#dc2626' : '#16a34a';
    }

    if (boxWarning) {
        boxWarning.style.display = (totalBayar > 0 && sisaSaldoSesudah < 0) ? 'block' : 'none';
    }

    // Validasi tombol submit
    if (btnSubmit) {
        btnSubmit.disabled = (totalBayar <= 0);
    }
}
