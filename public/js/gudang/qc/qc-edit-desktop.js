/**
 * =========================================================================
 * ERP PT MIRASA FOOD INDUSTRY - KOREKSI DOKUMEN QC INBOUND DESKTOP JS
 * Kalkulasi interaktif tonase netto, refraksi, dan sinkronisasi sidebar
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    const grossInput = document.getElementById('inputGross');
    const refraksiInput = document.getElementById('inputRefraksiPersen');
    const rejectInput = document.getElementById('inputReject');
    const nettoDisplay = document.getElementById('displayNettoVal');
    const nettoInput = document.getElementById('inputNettoLolos');

    // Sidebar elements
    const sidebarGross = document.getElementById('sidebarGross');
    const sidebarRefraksi = document.getElementById('sidebarRefraksi');
    const sidebarReject = document.getElementById('sidebarReject');
    const sidebarNetto = document.getElementById('sidebarNetto');
    const statusSelect = document.getElementById('selectStatusQc');
    const sidebarStatusBadge = document.getElementById('sidebarStatusBadge');

    function calculateNetto() {
        if (!grossInput) return;

        const gross = parseFloat(grossInput.value) || 0;
        const refraksiPct = parseFloat(refraksiInput?.value) || 0;
        const reject = parseFloat(rejectInput?.value) || 0;

        const refraksiKg = gross * (refraksiPct / 100);
        const netto = Math.max(0, gross - refraksiKg - reject);

        // Update inline display & hidden input
        if (nettoDisplay) {
            nettoDisplay.textContent = Math.round(netto).toLocaleString('id-ID') + ' kg';
        }
        if (nettoInput) {
            nettoInput.value = netto.toFixed(4);
        }

        // Update sidebar summary
        if (sidebarGross) {
            sidebarGross.textContent = Math.round(gross).toLocaleString('id-ID') + ' kg';
        }
        if (sidebarRefraksi) {
            sidebarRefraksi.textContent = `${Math.round(refraksiKg).toLocaleString('id-ID')} kg (${refraksiPct}%)`;
        }
        if (sidebarReject) {
            sidebarReject.textContent = Math.round(reject).toLocaleString('id-ID') + ' kg';
        }
        if (sidebarNetto) {
            sidebarNetto.textContent = Math.round(netto).toLocaleString('id-ID') + ' kg';
        }

        // Update Pengujian 2 akumulasi jika ada
        const p1Netto = typeof window.p1Netto === 'number' ? window.p1Netto : 0;
        const totalAkumulasi = p1Netto + netto;
        const inlineNettoUji2 = document.getElementById('inlineNettoUji2');
        const inlineTotal = document.getElementById('inlineTotalNettoGabungan');
        const sidebarNettoUji2 = document.getElementById('sidebarNettoUji2');
        const sidebarTotal = document.getElementById('sidebarTotalAkumulasi');

        if (inlineNettoUji2) inlineNettoUji2.textContent = Math.round(netto).toLocaleString('id-ID') + ' kg';
        if (inlineTotal) inlineTotal.textContent = Math.round(totalAkumulasi).toLocaleString('id-ID') + ' kg';
        if (sidebarNettoUji2) sidebarNettoUji2.textContent = Math.round(netto).toLocaleString('id-ID') + ' kg';
        if (sidebarTotal) sidebarTotal.textContent = Math.round(totalAkumulasi).toLocaleString('id-ID') + ' kg';
    }

    if (grossInput) grossInput.addEventListener('input', calculateNetto);
    if (refraksiInput) refraksiInput.addEventListener('input', calculateNetto);
    if (rejectInput) rejectInput.addEventListener('input', calculateNetto);

    if (statusSelect && sidebarStatusBadge) {
        statusSelect.addEventListener('change', function () {
            sidebarStatusBadge.textContent = statusSelect.value;
            if (statusSelect.value === 'SIAP_GUDANG') {
                sidebarStatusBadge.style.background = '#fef3c7';
                sidebarStatusBadge.style.color = '#b45309';
            } else if (statusSelect.value === 'DITERIMA_GUDANG') {
                sidebarStatusBadge.style.background = '#dcfce7';
                sidebarStatusBadge.style.color = '#15803d';
            } else if (statusSelect.value === 'DITOLAK_TOTAL') {
                sidebarStatusBadge.style.background = '#fee2e2';
                sidebarStatusBadge.style.color = '#b91c1c';
            } else {
                sidebarStatusBadge.style.background = '#e0f2fe';
                sidebarStatusBadge.style.color = '#0369a1';
            }
        });
    }

    // Initial calculation on load
    calculateNetto();
});

// Tutup dengan Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHaccpModal();
    }
});

/**
 * Modal Popup Preview Dokumen Mutu HACCP
 */
function openHaccpModal(qcId, qcNo) {
    const modal = document.getElementById('modalHaccpPreview');
    const iframe = document.getElementById('haccpPreviewIframe');
    const title = document.getElementById('modalHaccpTitle');
    const printBtn = document.getElementById('modalHaccpPrintBtn');
    const editBtn = document.getElementById('modalHaccpEditBtn');
    const loading = document.getElementById('modalHaccpLoading');

    if (!modal || !iframe) return;

    if (title) title.textContent = `Dokumen Mutu (HACCP): ${qcNo}`;
    if (editBtn) editBtn.href = `/qc/inbound/${qcId}/edit`;
    if (loading) loading.style.display = 'flex';

    // Set source iframe ke dokumen dengan layout popup
    iframe.src = `/qc/inbound/${qcId}?popup=1`;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeHaccpModal() {
    const modal = document.getElementById('modalHaccpPreview');
    const iframe = document.getElementById('haccpPreviewIframe');

    if (modal) modal.style.display = 'none';
    if (iframe) iframe.src = 'about:blank';
    document.body.style.overflow = '';
}

function onHaccpIframeLoaded() {
    const loading = document.getElementById('modalHaccpLoading');
    const iframe = document.getElementById('haccpPreviewIframe');
    if (loading && iframe && iframe.src !== 'about:blank') {
        loading.style.display = 'none';
    }
}

function printHaccpModalIframe() {
    const iframe = document.getElementById('haccpPreviewIframe');
    if (iframe && iframe.contentWindow) {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            const editBtn = document.getElementById('modalHaccpEditBtn');
            const qcIdMatch = editBtn?.href?.match(/\/qc\/inbound\/(\d+)\/edit/);
            if (qcIdMatch) {
                window.open(`/qc-antrean/${qcIdMatch[1]}/haccp-cetak`, '_blank');
            }
        }
    }
}
