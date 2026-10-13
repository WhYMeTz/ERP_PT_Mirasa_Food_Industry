/**
 * QC MOBILE EDIT JAVASCRIPT - PT MIRASA FOOD INDUSTRY
 * Logika Interaktif Koreksi & Perhitungan Form QC Mobile
 */

(function () {
    'use strict';

    window.syncMobileGrossFromPabrik = function (val) {
        const grossEl = document.getElementById('inputGross');
        if (grossEl) {
            grossEl.value = val;
        }
        window.recalcMobileNetto();
    };

    window.syncMobileGrossFromSJ = function (val) {
        const pabrikEl = document.getElementById('inputJumlahPabrik');
        if (pabrikEl && (!pabrikEl.value || parseFloat(pabrikEl.value) === 0)) {
            pabrikEl.value = val;
            window.syncMobileGrossFromPabrik(val);
        }
    };

    window.syncMobilePabrikFromGross = function (val) {
        const pabrikEl = document.getElementById('inputJumlahPabrik');
        if (pabrikEl) {
            pabrikEl.value = val;
        }
        window.recalcMobileNetto();
    };

    window.recalcMobileNetto = function () {
        const gross = parseFloat(document.getElementById('inputGross')?.value) || 0;
        const refPersen = parseFloat(document.getElementById('inputRefraksiPersen')?.value) || 0;
        const reject = parseFloat(document.getElementById('inputReject')?.value) || 0;

        const refKg = (gross * refPersen) / 100;
        let netto = gross - refKg - reject;
        if (netto < 0) netto = 0;

        const displayEl = document.getElementById('displayNettoText');
        const hiddenEl = document.getElementById('inputNettoLolos');

        if (displayEl) {
            displayEl.innerText = (netto % 1 === 0 ? netto : netto.toFixed(2)).toLocaleString('id-ID') + ' kg';
        }
        if (hiddenEl) {
            hiddenEl.value = (netto % 1 === 0 ? netto : netto.toFixed(2));
        }
    };

    window.onEditSupplierChange = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split('(')[0].trim();
            const produsenInput = document.getElementById('editNamaProdusen');
            if (produsenInput) produsenInput.value = supNm;
        }
    };

    window.filterEditSuppliers = function (query) {
        const q = (query || '').toLowerCase().trim();
        const select = document.getElementById('editSupplierSelect');
        if (!select) return;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            const text = (opt.text || '').toLowerCase();
            if (!q || text.includes(q)) {
                opt.style.display = '';
                opt.disabled = false;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        }
    };

    window.onEditBarangChanged = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const bNm = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
            const namaEl = document.getElementById('editNamaJenis');
            if (namaEl) namaEl.value = bNm.trim();
        }
    };

    /**
     * REAKTIF RASA FRYER (PAHIT = REJECT & TOLAK TOTAL)
     */
    window.onRasaGorengChanged = function (val) {
        const banner = document.getElementById('bannerPahitWarning');
        const radioTolak = document.getElementById('radioTolak');
        const radioTerima = document.getElementById('radioTerima');
        const selectGrade = document.getElementById('selectGradeCd');

        if (val === 'PAHIT') {
            if (banner) banner.style.display = 'block';
            if (radioTolak) radioTolak.checked = true;
            if (selectGrade) selectGrade.value = 'REJECT';
        } else {
            if (banner) banner.style.display = 'none';
            if (selectGrade && selectGrade.value === 'REJECT') {
                selectGrade.value = 'A';
            }
            if (radioTerima && (!selectGrade || selectGrade.value !== 'REJECT')) {
                radioTerima.checked = true;
            }
        }
    };

    /**
     * REAKTIF PENETAPAN GRADE MUTU
     */
    window.onGradeChanged = function (val) {
        const radioTolak = document.getElementById('radioTolak');
        const radioTerima = document.getElementById('radioTerima');
        const rasa = document.getElementById('selectFryerRasa')?.value;

        if (val === 'REJECT') {
            if (radioTolak) radioTolak.checked = true;
        } else {
            if (rasa !== 'PAHIT' && radioTerima) {
                radioTerima.checked = true;
            }
        }
    };

    let isSubmitting = false;
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('qcEditMobileForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                const overlay = document.getElementById('qcSubmitOverlay');
                if (overlay) overlay.style.display = 'flex';
                const btn = document.getElementById('btnEditSubmit');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.pointerEvents = 'none';
                }
            });
        }
    });

    console.log('[QC Mobile Edit] Script loaded successfully.');
})();
