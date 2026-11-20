/**
 * ══════════════════════════════════════════════════════════════════════════
 * PO PENJUALAN (SALES ORDER) - SHOW SCRIPT
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

// Auto-open PDF jika ada session auto_open_pdf dari controller setelah konfirmasi kirim
document.addEventListener('DOMContentLoaded', function() {
    if (window.appConfig && window.appConfig.autoOpenPdf) {
        window.open(window.appConfig.autoOpenPdf, '_blank');
    }
});

function openModalArmada() {
    const modal = document.getElementById('modalUpdateArmada');
    if (modal) modal.style.display = 'flex';
}

function closeModalArmada() {
    const modal = document.getElementById('modalUpdateArmada');
    if (modal) modal.style.display = 'none';
}

function openConfirmDeliveryModal(soId, soNo, customerNm, supirNm, nopol) {
    const modal = document.getElementById('modalConfirmDelivery');
    if (!modal) return;

    const form = document.getElementById('formConfirmDelivery');
    if (form) {
        form.action = `/penjualan/so/${soId}/confirm-delivery`;
    }

    const lblSoNo = document.getElementById('modalDeliverySoNo');
    const lblCust = document.getElementById('modalDeliveryCustomer');
    const tglInput = document.getElementById('deliveryTglInput');

    if (lblSoNo) lblSoNo.textContent = soNo;
    if (lblCust) lblCust.textContent = customerNm;
    if (tglInput) tglInput.value = new Date().toISOString().split('T')[0];

    if (supirNm !== undefined) {
        const supirInput = document.getElementById('deliverySupirInput');
        if (supirInput) supirInput.value = supirNm || '';
    }
    if (nopol !== undefined) {
        const nopolInput = document.getElementById('deliveryNopolInput');
        if (nopolInput) nopolInput.value = nopol || '';
    }

    modal.style.display = 'flex';
}

function closeConfirmDeliveryModal() {
    const modal = document.getElementById('modalConfirmDelivery');
    if (modal) modal.style.display = 'none';
}

function openVoidDeliveryModal(soId, soNo) {
    const modal = document.getElementById('modalVoidDelivery');
    if (!modal) return;

    const form = document.getElementById('formVoidDelivery');
    if (form) {
        form.action = `/penjualan/so/${soId}/void-delivery`;
    }

    modal.style.display = 'flex';
}

function closeVoidDeliveryModal() {
    const modal = document.getElementById('modalVoidDelivery');
    if (modal) modal.style.display = 'none';
}

function openCancelSoModal(soId, soNo) {
    const modal = document.getElementById('modalCancelSo');
    if (!modal) return;

    const form = document.getElementById('formCancelSo');
    if (form) {
        form.action = `/penjualan/so/${soId}/cancel`;
    }

    const lbl = document.getElementById('modalCancelSoNo');
    if (lbl) lbl.textContent = soNo;

    modal.style.display = 'flex';
}

function closeCancelSoModal() {
    const modal = document.getElementById('modalCancelSo');
    if (modal) modal.style.display = 'none';
}

function openRecordPaymentModal() {
    const modal = document.getElementById('modalRecordPayment');
    if (modal) modal.style.display = 'flex';
}

function closeRecordPaymentModal() {
    const modal = document.getElementById('modalRecordPayment');
    if (modal) modal.style.display = 'none';
}

function fillFullPayment(amount) {
    const input = document.getElementById('modalNominalInput');
    if (input) input.value = amount;
}

function openReturModal() {
    const modal = document.getElementById('modalReturPenjualan');
    if (modal) modal.style.display = 'flex';
}

function closeReturModal() {
    const modal = document.getElementById('modalReturPenjualan');
    if (modal) modal.style.display = 'none';
}

function recalcReturRow(input) {
    const idx = input.getAttribute('data-idx');
    const price = parseFloat(input.getAttribute('data-price')) || 0;
    const max = parseFloat(input.getAttribute('max')) || 0;
    let val = parseFloat(input.value) || 0;
    if (val < 0) val = 0;
    if (val > max) {
        val = max;
        input.value = max;
    }
    const subtotal = Math.round(val * price);
    const subtotalEl = document.getElementById('returRowSubtotal_' + idx);
    if (subtotalEl) {
        subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    }

    // Sync perlakuan values
    const perlakuanInp = document.getElementById('returPerlakuan_' + idx);
    const mode = perlakuanInp ? perlakuanInp.value : 'MASUK_PERSEDIAAN';
    const inpMasuk = document.getElementById('returQtyMasuk_' + idx);
    const inpMusnah = document.getElementById('returQtyMusnah_' + idx);

    if (mode === 'MASUK_PERSEDIAAN') {
        if (inpMasuk) inpMasuk.value = val;
        if (inpMusnah) inpMusnah.value = 0;
    } else if (mode === 'DIMUSNAHKAN') {
        if (inpMasuk) inpMasuk.value = 0;
        if (inpMusnah) inpMusnah.value = val;
    } else if (mode === 'CAMPUR') {
        let m = parseFloat(inpMasuk ? inpMasuk.value : 0) || 0;
        if (m > val) m = val;
        if (inpMasuk) inpMasuk.value = m;
        if (inpMusnah) inpMusnah.value = Math.max(0, val - m);
    }

    updateReturSummaryTotals();
}

function setRowDisposisi(idx, mode) {
    const perlakuanInp = document.getElementById('returPerlakuan_' + idx);
    if (perlakuanInp) perlakuanInp.value = mode;

    const row = document.getElementById('returRow_' + idx);
    if (row) {
        row.querySelectorAll('.disposisi-btn').forEach(btn => {
            btn.classList.remove('active-persediaan', 'active-musnah', 'active-campur');
        });

        const activeBtn = row.querySelector(`.disposisi-btn[data-mode="${mode}"]`);
        if (activeBtn) {
            if (mode === 'MASUK_PERSEDIAAN') activeBtn.classList.add('active-persediaan');
            else if (mode === 'DIMUSNAHKAN') activeBtn.classList.add('active-musnah');
            else if (mode === 'CAMPUR') activeBtn.classList.add('active-campur');
        }

        const splitBox = document.getElementById('returSplitBox_' + idx);
        const qtyInp = row.querySelector('input[name$="[qty_retur]"]');
        const totalQty = parseFloat(qtyInp ? qtyInp.value : 0) || 0;
        const inpMasuk = document.getElementById('returQtyMasuk_' + idx);
        const inpMusnah = document.getElementById('returQtyMusnah_' + idx);

        if (mode === 'CAMPUR') {
            if (splitBox) splitBox.style.display = 'flex';
            const half = Math.floor(totalQty / 2);
            if (inpMasuk) inpMasuk.value = half;
            if (inpMusnah) inpMusnah.value = Math.max(0, totalQty - half);
        } else {
            if (splitBox) splitBox.style.display = 'none';
            if (mode === 'MASUK_PERSEDIAAN') {
                if (inpMasuk) inpMasuk.value = totalQty;
                if (inpMusnah) inpMusnah.value = 0;
            } else {
                if (inpMasuk) inpMasuk.value = 0;
                if (inpMusnah) inpMusnah.value = totalQty;
            }
        }
    }

    updateReturSummaryTotals();
}

function onSplitQtyChange(idx, changedField) {
    const row = document.getElementById('returRow_' + idx);
    if (!row) return;

    const qtyInp = row.querySelector('input[name$="[qty_retur]"]');
    const totalQty = parseFloat(qtyInp ? qtyInp.value : 0) || 0;
    const inpMasuk = document.getElementById('returQtyMasuk_' + idx);
    const inpMusnah = document.getElementById('returQtyMusnah_' + idx);

    if (changedField === 'MASUK') {
        let masuk = parseFloat(inpMasuk ? inpMasuk.value : 0) || 0;
        if (masuk < 0) masuk = 0;
        if (masuk > totalQty) masuk = totalQty;
        if (inpMasuk) inpMasuk.value = masuk;
        if (inpMusnah) inpMusnah.value = Math.max(0, totalQty - masuk);
    } else {
        let musnah = parseFloat(inpMusnah ? inpMusnah.value : 0) || 0;
        if (musnah < 0) musnah = 0;
        if (musnah > totalQty) musnah = totalQty;
        if (inpMusnah) inpMusnah.value = musnah;
        if (inpMasuk) inpMasuk.value = Math.max(0, totalQty - musnah);
    }

    updateReturSummaryTotals();
}

function setAllDisposisi(mode) {
    document.querySelectorAll('#modalReturPenjualan tr[id^="returRow_"]').forEach(row => {
        const idx = row.getAttribute('data-idx');
        if (idx !== null) {
            setRowDisposisi(idx, mode);
        }
    });
}

function updateReturSummaryTotals() {
    let totalQty = 0;
    let totalNilai = 0;
    let totalMasuk = 0;
    let totalMusnah = 0;

    document.querySelectorAll('#modalReturPenjualan input[name$="[qty_retur]"]').forEach(inp => {
        const q = parseFloat(inp.value) || 0;
        const p = parseFloat(inp.getAttribute('data-price')) || 0;
        totalQty += q;
        totalNilai += Math.round(q * p);
    });

    document.querySelectorAll('#modalReturPenjualan input[name$="[qty_masuk_persediaan]"]').forEach(inp => {
        totalMasuk += (parseFloat(inp.value) || 0);
    });

    document.querySelectorAll('#modalReturPenjualan input[name$="[qty_dimusnahkan]"]').forEach(inp => {
        totalMusnah += (parseFloat(inp.value) || 0);
    });

    const lblQty = document.getElementById('lblTotalReturQty');
    const lblNilai = document.getElementById('lblTotalReturNilai');
    const lblMasuk = document.getElementById('lblTotalReturMasuk');
    const lblMusnah = document.getElementById('lblTotalReturMusnah');

    if (lblQty) lblQty.textContent = totalQty.toLocaleString('id-ID');
    if (lblNilai) lblNilai.textContent = 'Rp ' + totalNilai.toLocaleString('id-ID');
    if (lblMasuk) lblMasuk.textContent = totalMasuk.toLocaleString('id-ID') + ' Karton';
    if (lblMusnah) lblMusnah.textContent = totalMusnah.toLocaleString('id-ID') + ' Karton';
}

function openVoidReturModal(returId, returNo) {
    const modal = document.getElementById('modalVoidRetur');
    if (!modal) return;
    const form = document.getElementById('formVoidRetur');
    if (form) form.action = `/penjualan/so/retur/${returId}/void`;
    const lbl = document.getElementById('modalVoidReturNo');
    if (lbl) lbl.textContent = `Dokumen Retur: ${returNo}`;
    modal.style.display = 'flex';
}

function closeVoidReturModal() {
    const modal = document.getElementById('modalVoidRetur');
    if (modal) modal.style.display = 'none';
}

function togglePrintMenu(e) {
    if (e) e.stopPropagation();
    const dropdown = document.getElementById('printMenuDropdown');
    if (!dropdown) return;
    const isShown = dropdown.style.display === 'block';
    dropdown.style.display = isShown ? 'none' : 'block';
}



// Tutup modal / dropdown saat klik overlay luar
window.addEventListener('click', function(e) {
    const printDropdown = document.getElementById('printMenuDropdown');
    if (printDropdown && !printDropdown.contains(e.target)) {
        printDropdown.style.display = 'none';
    }

    const modalDelivery = document.getElementById('modalConfirmDelivery');
    const modalVoid = document.getElementById('modalVoidDelivery');
    const modalCancel = document.getElementById('modalCancelSo');
    const modalPayment = document.getElementById('modalRecordPayment');
    const modalRetur = document.getElementById('modalReturPenjualan');
    const modalVoidRetur = document.getElementById('modalVoidRetur');
    const modalArmada = document.getElementById('modalUpdateArmada');

    if (e.target === modalDelivery) closeConfirmDeliveryModal();
    if (e.target === modalVoid) closeVoidDeliveryModal();
    if (e.target === modalCancel) closeCancelSoModal();
    if (e.target === modalPayment) closeRecordPaymentModal();
    if (e.target === modalRetur) closeReturModal();
    if (e.target === modalVoidRetur) closeVoidReturModal();
    if (e.target === modalArmada) closeModalArmada();
});


