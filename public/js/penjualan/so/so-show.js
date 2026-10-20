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

// Tutup modal saat klik overlay luar
window.addEventListener('click', function(e) {
    const modalDelivery = document.getElementById('modalConfirmDelivery');
    const modalVoid = document.getElementById('modalVoidDelivery');
    const modalCancel = document.getElementById('modalCancelSo');
    const modalPayment = document.getElementById('modalRecordPayment');

    if (e.target === modalDelivery) closeConfirmDeliveryModal();
    if (e.target === modalVoid) closeVoidDeliveryModal();
    if (e.target === modalCancel) closeCancelSoModal();
    if (e.target === modalPayment) closeRecordPaymentModal();
});

