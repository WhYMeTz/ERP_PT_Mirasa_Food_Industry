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

        // Periksa Over PO secara real-time
        checkOverPoLive();
    }

    const sjInput = document.getElementById('inputJumlahSj');

    function checkOverPoLive() {
        if (!window.poData || !window.poData.hasPo) return;

        const gross = parseFloat(grossInput?.value) || 0;
        const sj = parseFloat(sjInput?.value) || 0;
        // Prioritaskan tonase gross timbangan fisik jika ada; jika non-timbangan gunakan surat jalan
        const muatanAcuan = gross > 0 ? gross : sj;
        const poBatas = typeof window.poData.poSisaKuota !== 'undefined' ? window.poData.poSisaKuota : window.poData.poTotalPesan;
        const poTotal = window.poData.poTotalPesan || 0;
        const banner = document.getElementById('bannerOverPoAlert');
        const badge = document.getElementById('badgeOverPoField');
        const textEl = document.getElementById('bannerOverPoText');
        const satuan = window.poData.satuan || 'kg';

        if (poTotal > 0 && muatanAcuan > poBatas) {
            const selisih = muatanAcuan - poBatas;
            const persen = poBatas > 0 ? ((selisih / poBatas) * 100).toFixed(1) : '100.0';

            if (banner) {
                banner.style.display = 'flex';
                if (textEl) {
                    textEl.innerHTML = `Tonase muatan fisik yang diinput (<strong>${Math.round(muatanAcuan).toLocaleString('id-ID')} ${satuan}</strong>) melebihi sisa kuota pesanan <strong>PO #${window.poData.poNo}</strong> (<strong>${Math.round(poBatas).toLocaleString('id-ID')} ${satuan}</strong> dari total PO ${Math.round(poTotal).toLocaleString('id-ID')} ${satuan}). Selisih lebih: <strong style="color: #b45309;">+${Math.round(selisih).toLocaleString('id-ID')} ${satuan} (+${persen}%)</strong>.`;
                }
            }
            if (badge) badge.style.display = 'inline-block';
        } else {
            if (banner) banner.style.display = 'none';
            if (badge) badge.style.display = 'none';
        }
    }

    if (grossInput) grossInput.addEventListener('input', calculateNetto);
    if (refraksiInput) refraksiInput.addEventListener('input', calculateNetto);
    if (rejectInput) rejectInput.addEventListener('input', calculateNetto);
    if (sjInput) sjInput.addEventListener('input', checkOverPoLive);

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

    // Form submission & Over PO confirmation dialog
    const desktopForm = document.getElementById('qcEditDesktopForm');
    let lastSubmitter = null;
    let allowOverPoSubmit = false;

    if (desktopForm) {
        desktopForm.addEventListener('submit', function (e) {
            if (allowOverPoSubmit) return; // Sudah dikonfirmasi user

            if (!window.poData || !window.poData.hasPo) {
                return; // Non-PO, langsung izinkan submit
            }

            const gross = parseFloat(grossInput?.value) || 0;
            const sj = parseFloat(sjInput?.value) || 0;
            const muatanAcuan = gross > 0 ? gross : sj;
            const poBatas = typeof window.poData.poSisaKuota !== 'undefined' ? window.poData.poSisaKuota : window.poData.poTotalPesan;
            const poTotal = window.poData.poTotalPesan || 0;

            if (poTotal > 0 && muatanAcuan > poBatas) {
                e.preventDefault();
                lastSubmitter = e.submitter;

                const selisih = muatanAcuan - poBatas;
                const persen = poBatas > 0 ? ((selisih / poBatas) * 100).toFixed(1) : '100.0';
                const satuan = window.poData.satuan || 'kg';

                const modal = document.getElementById('modalConfirmOverPo');
                const modalMuatan = document.getElementById('modalOverPoMuatan');
                const modalPoNo = document.getElementById('modalOverPoPoNo');
                const modalKuota = document.getElementById('modalOverPoKuota');
                const modalSelisih = document.getElementById('modalOverPoSelisih');
                const modalPersen = document.getElementById('modalOverPoPersen');

                if (modalMuatan) modalMuatan.textContent = `${Math.round(muatanAcuan).toLocaleString('id-ID')} ${satuan}`;
                if (modalPoNo) modalPoNo.textContent = `PO #${window.poData.poNo}`;
                if (modalKuota) modalKuota.textContent = `Sisa Kuota: ${Math.round(poBatas).toLocaleString('id-ID')} ${satuan} (dari Total PO ${Math.round(poTotal).toLocaleString('id-ID')} ${satuan})`;
                if (modalSelisih) modalSelisih.textContent = `+${Math.round(selisih).toLocaleString('id-ID')} ${satuan}`;
                if (modalPersen) modalPersen.textContent = `+${persen}%`;

                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            }
        });
    }

    const btnConfirmSubmit = document.getElementById('btnConfirmOverPoSubmit');
    if (btnConfirmSubmit && desktopForm) {
        btnConfirmSubmit.addEventListener('click', function () {
            allowOverPoSubmit = true;
            closeOverPoModal();

            if (lastSubmitter && lastSubmitter.name) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = lastSubmitter.name;
                hiddenInput.value = lastSubmitter.value;
                desktopForm.appendChild(hiddenInput);
            }
            desktopForm.submit();
        });
    }

    // Initial calculation on load
    calculateNetto();
    checkOverPoLive();
});

function closeOverPoModal() {
    const modal = document.getElementById('modalConfirmOverPo');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Tutup dengan Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHaccpModal();
        closeOverPoModal();
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
