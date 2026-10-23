/**
 * ══════════════════════════════════════════════════════════════════════════
 * PO PENJUALAN (SALES ORDER) - SHOW SCRIPT
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

function openConfirmDeliveryModal(soId, soNo, customerNm) {
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

    let totalQty = 0;
    let totalNilai = 0;
    document.querySelectorAll('#modalReturPenjualan input[name$="[qty_retur]"]').forEach(inp => {
        const q = parseFloat(inp.value) || 0;
        const p = parseFloat(inp.getAttribute('data-price')) || 0;
        totalQty += q;
        totalNilai += Math.round(q * p);
    });

    const lblQty = document.getElementById('lblTotalReturQty');
    const lblNilai = document.getElementById('lblTotalReturNilai');
    if (lblQty) lblQty.textContent = totalQty.toLocaleString('id-ID');
    if (lblNilai) lblNilai.textContent = 'Rp ' + totalNilai.toLocaleString('id-ID');
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

    if (e.target === modalDelivery) closeConfirmDeliveryModal();
    if (e.target === modalVoid) closeVoidDeliveryModal();
    if (e.target === modalCancel) closeCancelSoModal();
    if (e.target === modalPayment) closeRecordPaymentModal();
    if (e.target === modalRetur) closeReturModal();
    if (e.target === modalVoidRetur) closeVoidReturModal();
});


