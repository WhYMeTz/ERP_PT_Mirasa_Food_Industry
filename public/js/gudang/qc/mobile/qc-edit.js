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
            if (namaEl) {
                const upper = bNm.toUpperCase();
                if (upper.includes('KELAPA') && !upper.includes('SAWIT')) {
                    namaEl.value = 'Minyak Kelapa (RBD CNO)';
                } else if (upper.includes('MINYAK') || upper.includes('SAWIT')) {
                    namaEl.value = 'RBD Palm Olein / Curah Sawit';
                } else {
                    namaEl.value = bNm.trim();
                }
            }
            const satCd = opt.getAttribute('data-satuan-cd') || opt.getAttribute('data-satuan');
            if (satCd && typeof window.setPlastikEditSatuan === 'function') {
                window.setPlastikEditSatuan(satCd);
            }
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

    /**
     * KUNCI / BUKA FIELD SESUAI STATUS PO (SUPPLIER, GUDANG, BARANG)
     */
    window.onEditPoChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        const poId = select.value;

        const supId = opt?.getAttribute('data-supplier-id');
        const supNm = opt?.getAttribute('data-supplier-nm');
        const gudangId = opt?.getAttribute('data-gudang-id');
        const barangId = opt?.getAttribute('data-barang-id');

        const sSelect = document.getElementById('editSupplierSelect');
        const gSelect = document.getElementById('editGudangSelect') || document.querySelector('select[name="gudang_id"]');
        const bSelect = document.getElementById('editBarangSelect') || document.getElementById('selectEditBarang');
        const produsenInput = document.getElementById('editNamaProdusen');
        const wrapSearch = document.getElementById('wrapEditSupplierSearch');

        const badgeSup = document.getElementById('badgeEditLockSupplier');
        const badgeGudang = document.getElementById('badgeEditLockGudang');
        const badgeBarang = document.getElementById('badgeEditLockBarang');

        if (poId) {
            // Lock Supplier
            if (supId && sSelect) {
                sSelect.value = supId;
                sSelect.style.pointerEvents = 'none';
                sSelect.style.backgroundColor = '#f1f5f9';
                sSelect.style.borderColor = '#cbd5e1';
                sSelect.setAttribute('tabindex', '-1');
                window.onEditSupplierChange(sSelect);
            }
            if (badgeSup) {
                badgeSup.style.display = 'inline-flex';
                if (supNm) badgeSup.innerText = `🔒 Terkunci (${supNm})`;
            }
            if (wrapSearch) wrapSearch.style.display = 'none';

            // Lock Gudang
            if (gudangId && gSelect) {
                gSelect.value = gudangId;
                gSelect.style.pointerEvents = 'none';
                gSelect.style.backgroundColor = '#f1f5f9';
                gSelect.style.borderColor = '#cbd5e1';
                gSelect.setAttribute('tabindex', '-1');
            }
            if (badgeGudang) badgeGudang.style.display = 'inline-flex';

            // Lock Barang
            if (barangId && bSelect) {
                const hasMatchingOption = Array.from(bSelect.options).some(o => o.value == barangId);
                if (hasMatchingOption) {
                    bSelect.value = barangId;
                }
                bSelect.style.pointerEvents = 'none';
                bSelect.style.backgroundColor = '#f1f5f9';
                bSelect.style.borderColor = '#cbd5e1';
                bSelect.setAttribute('tabindex', '-1');
                if (typeof window.onEditBarangChanged === 'function') {
                    window.onEditBarangChanged(bSelect);
                }
            }
            if (badgeBarang) badgeBarang.style.display = 'inline-flex';

            if (produsenInput && supNm) {
                produsenInput.value = supNm;
                produsenInput.readOnly = true;
                produsenInput.style.backgroundColor = '#f1f5f9';
            }
        } else {
            // Unlock Supplier
            if (sSelect) {
                sSelect.style.pointerEvents = 'auto';
                sSelect.style.backgroundColor = '#ffffff';
                sSelect.style.borderColor = '#e2e8f0';
                sSelect.removeAttribute('tabindex');
            }
            if (badgeSup) badgeSup.style.display = 'none';
            if (wrapSearch) wrapSearch.style.display = 'block';

            // Unlock Gudang
            if (gSelect) {
                gSelect.style.pointerEvents = 'auto';
                gSelect.style.backgroundColor = '#ffffff';
                gSelect.style.borderColor = '#e2e8f0';
                gSelect.removeAttribute('tabindex');
            }
            if (badgeGudang) badgeGudang.style.display = 'none';

            // Unlock Barang
            if (bSelect) {
                bSelect.style.pointerEvents = 'auto';
                bSelect.style.backgroundColor = '#ffffff';
                bSelect.style.borderColor = '#e2e8f0';
                bSelect.removeAttribute('tabindex');
            }
            if (badgeBarang) badgeBarang.style.display = 'none';

            if (produsenInput) {
                produsenInput.readOnly = false;
                produsenInput.style.backgroundColor = '#ffffff';
            }
        }
    };

    let isSubmitting = false;
    document.addEventListener('DOMContentLoaded', function () {
        const editPoSelect = document.getElementById('editPoSelect');
        if (editPoSelect && editPoSelect.value) {
            window.onEditPoChanged(editPoSelect);
        }

        const form = document.getElementById('qcEditMobileForm')
            || document.getElementById('qcEditMinyakForm')
            || document.getElementById('qcEditPlastikForm')
            || document.getElementById('qcEditKartonForm')
            || document.getElementById('qcBpEditForm');
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
