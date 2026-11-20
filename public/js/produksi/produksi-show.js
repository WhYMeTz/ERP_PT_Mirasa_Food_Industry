/**
 * JavaScript Detail Produksi Harian (Show)
 * Path: public/js/produksi/produksi-show.js
 * PT Mirasa Food Industry
 */

/**
 * Tab Navigation Controls
 */
function switchProdTab(tabName, event) {
    if (event) {
        event.preventDefault();
    }
    
    // Update tab buttons
    const buttons = document.querySelectorAll('.prod-tab-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('data-tab') === tabName) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update tab panes
    const panes = document.querySelectorAll('.prod-tab-pane');
    panes.forEach(pane => {
        if (pane.id === 'pane-' + tabName) {
            pane.classList.add('active');
        } else {
            pane.classList.remove('active');
        }
    });

    // Update URL hash without jumping
    if (history.replaceState) {
        history.replaceState(null, null, '#' + tabName);
    }
}
window.switchProdTab = switchProdTab;

document.addEventListener('DOMContentLoaded', function() {
    // Add click listeners to buttons
    document.querySelectorAll('.prod-tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const tab = this.getAttribute('data-tab');
            if (tab) {
                switchProdTab(tab, e);
            }
        });
    });

    // Check hash on load
    const hash = window.location.hash.replace('#', '');
    if (hash && ['output', 'biaya', 'audit'].includes(hash)) {
        switchProdTab(hash);
    } else if (hash === 'assign-batch') {
        openModalAssignBatch();
    }
});

function updateLiveStickerCard(kartonNum) {
    const el = document.getElementById('previewStickerKartonNo');
    if (el && kartonNum) {
        el.textContent = kartonNum;
    }
}

/**
 * Modal Assign Batch (Buffer Stock IFM)
 */
function openModalAssignBatch() {
    const modal = document.getElementById('modalAssignBatch');
    if (!modal) return;

    const data = window.produksiData || {};
    const docNoEl = document.getElementById('assignBatchDocNo');
    const form = document.getElementById('formAssignBatch');
    const qtyDisplay = document.getElementById('assign_qty_karton_display');
    const qtyInput = document.getElementById('assign_qty_karton');

    if (docNoEl) docNoEl.textContent = data.no || '-';
    if (form && data.assignBatchUrl) form.action = data.assignBatchUrl;
    if (qtyDisplay) qtyDisplay.value = (data.qtyKarton || 0) + ' Box';
    if (qtyInput) qtyInput.value = data.qtyKarton || 0;

    calculateAssignBatchPreview();

    modal.style.display = 'flex';
}
window.openModalAssignBatch = openModalAssignBatch;

function closeModalAssignBatch() {
    const modal = document.getElementById('modalAssignBatch');
    if (modal) {
        modal.style.display = 'none';
    }
}
window.closeModalAssignBatch = closeModalAssignBatch;

function calculateAssignBatchPreview() {
    const tglInput = document.getElementById('assign_tgl_keberangkatan');
    const shiftSelect = document.getElementById('assign_shift_cd');
    const awalInput = document.getElementById('assign_no_karton_awal');
    const qtyInput = document.getElementById('assign_qty_karton');

    const liveBatchText = document.getElementById('assignLiveBatchText');
    const liveExpDate = document.getElementById('assignLiveExpDate');
    const badgeRentang = document.getElementById('assignBadgeRentang');

    const tgl = tglInput?.value || '';
    const shift = shiftSelect?.value || 'A';
    const noAwal = parseInt(awalInput?.value) || 1;
    const qty = parseInt(qtyInput?.value) || 0;
    const noAkhir = qty > 0 ? (noAwal + qty - 1) : noAwal;

    const padAwal = String(noAwal).padStart(4, '0');
    const padAkhir = String(noAkhir).padStart(4, '0');

    const batchCode = `${shift}${padAwal} - ${shift}${padAkhir}`;

    if (liveBatchText) liveBatchText.textContent = batchCode;
    if (badgeRentang) badgeRentang.textContent = `${qty} Karton (No. ${padAwal} s/d ${padAkhir})`;

    if (tgl && liveExpDate) {
        const d = new Date(tgl);
        if (!isNaN(d.getTime())) {
            d.setMonth(d.getMonth() + 6);
            const expDay = String(d.getDate()).padStart(2, '0');
            const expMonth = String(d.getMonth() + 1).padStart(2, '0');
            const expYear = d.getFullYear();
            liveExpDate.textContent = `${expDay}/${expMonth}/${expYear}`;
        }
    }
}
window.calculateAssignBatchPreview = calculateAssignBatchPreview;

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModalAssignBatch();
    }
});

