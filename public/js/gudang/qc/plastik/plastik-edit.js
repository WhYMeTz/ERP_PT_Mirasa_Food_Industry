/**
 * QC INBOUND - MOBILE EDIT PLASTIK KEMASAN JAVASCRIPT
 * PT Mirasa Food Industry
 * Logika interaksi form edit mobile Plastik:
 * 1. Kalkulasi Netto = Gross - Reject
 * 2. Segmented Pill Buttons (Status Raw Material, Kemasan, Keputusan)
 * 3. Pemulihan Otomatis Reject = 0 saat beralih ke TERIMA
 * 4. Pilihan Satuan (Pcs / Lembar / KG)
 * 5. Quick Chips untuk Ketebalan & Keutuhan
 * 6. Audit Halal (SJH) & Transportasi
 */

(function () {
    'use strict';

    // 1. Audit Halal - Segmented Toggle (Tidak / Ya)
    window.setHalalToggle = function (field, value) {
        const hidden = document.getElementById(`input_${field}`);
        if (hidden) hidden.value = value;

        if (field === 'angkut_barang_haram') {
            const btnTidak = document.getElementById('btnHaramTidak');
            const btnYa = document.getElementById('btnHaramYa');
            if (value == 0) {
                btnTidak?.classList.add('active', 'ok');
                btnTidak?.classList.remove('danger');
                btnYa?.classList.remove('active', 'ok', 'danger');
            } else {
                btnYa?.classList.add('active', 'danger');
                btnYa?.classList.remove('ok');
                btnTidak?.classList.remove('active', 'ok', 'danger');
            }
        } else if (field === 'terdaftar_lppom') {
            const btnTidak = document.getElementById('btnLppomTidak');
            const btnYa = document.getElementById('btnLppomYa');
            if (value == 1) {
                btnYa?.classList.add('active', 'ok');
                btnYa?.classList.remove('danger');
                btnTidak?.classList.remove('active', 'ok', 'danger');
            } else {
                btnTidak?.classList.add('active', 'danger');
                btnTidak?.classList.remove('ok');
                btnYa?.classList.remove('active', 'ok', 'danger');
            }
        } else if (field === 'ada_sertifikat_halal') {
            const btnTidak = document.getElementById('btnSertifikatTidak');
            const btnYa = document.getElementById('btnSertifikatYa');
            if (value == 1) {
                btnYa?.classList.add('active', 'ok');
                btnYa?.classList.remove('danger');
                btnTidak?.classList.remove('active', 'ok', 'danger');
            } else {
                btnTidak?.classList.add('active', 'danger');
                btnTidak?.classList.remove('ok');
                btnYa?.classList.remove('active', 'ok', 'danger');
            }
        } else if (field === 'sertifikat_halal_berlaku') {
            const btnTidak = document.getElementById('btnBerlakuTidak');
            const btnYa = document.getElementById('btnBerlakuYa');
            if (value == 1) {
                btnYa?.classList.add('active', 'ok');
                btnYa?.classList.remove('danger');
                btnTidak?.classList.remove('active', 'ok', 'danger');
            } else {
                btnTidak?.classList.add('active', 'danger');
                btnTidak?.classList.remove('ok');
                btnYa?.classList.remove('active', 'ok', 'danger');
            }
        }
    };

    // 2. Transport Radio Card (Bebas Cemaran vs Ada Cemaran)
    window.updateTransportCard = function (radio) {
        const cardBebas = document.getElementById('cardTransportBebas');
        const cardCemar = document.getElementById('cardTransportCemar');
        if (radio.value === '1') {
            cardBebas?.classList.add('active');
            cardCemar?.classList.remove('active');
        } else {
            cardCemar?.classList.add('active');
            cardBebas?.classList.remove('active');
        }
    };

    // 3. Pill Toggle untuk Radio Buttons (Status Raw Material, Kemasan, Keputusan)
    window.selectPlastikPill = function (groupName, value) {
        const radios = document.querySelectorAll(`input[name="${groupName}"]`);
        radios.forEach(r => {
            if (r.value === value) {
                r.checked = true;
                const parentPill = r.closest('.plastik-pill-btn');
                if (parentPill) {
                    const groupContainer = parentPill.closest('.plastik-pill-group') || parentPill.parentElement;
                    groupContainer.querySelectorAll('.plastik-pill-btn').forEach(p => {
                        p.classList.remove('is-active-ok', 'is-active-danger', 'is-active-teal');
                    });

                    // Terapkan class aktif berdasarkan nilai
                    if (value === 'OK' || value === 'TERIMA') {
                        parentPill.classList.add('is-active-ok');
                    } else if (value === 'TDK_STD' || value === 'TIDAK_STANDARD' || value === 'TOLAK') {
                        parentPill.classList.add('is-active-danger');
                    } else {
                        parentPill.classList.add('is-active-teal');
                    }
                }

                if (typeof r.onchange === 'function') {
                    r.onchange();
                }
            }
        });

        // Sinkronisasi otomatis Kuantitas Reject & Netto saat Keputusan Akhir diubah
        if (groupName === 'kesimpulan_qc') {
            const rejectInput = document.getElementById('inputReject');
            const grossInput = document.getElementById('inputGross');
            const grossVal = parseFloat(grossInput?.value) || 0;
            const rejectVal = parseFloat(rejectInput?.value) || 0;

            if (value === 'TERIMA') {
                // Jika sebelumnya berstatus DITOLAK (reject >= gross atau netto 0), kembalikan reject ke 0
                if (rejectVal >= grossVal && grossVal > 0) {
                    if (rejectInput) rejectInput.value = 0;
                    window.recalcPlastikNetto();
                }
            } else if (value === 'TOLAK') {
                // Jika memilih tolak, seluruh kuantitas gross otomatis menjadi reject
                if (rejectInput && grossVal > 0) {
                    rejectInput.value = grossVal % 1 === 0 ? grossVal : grossVal.toFixed(2);
                    window.recalcPlastikNetto();
                }
            }
        }
    };

    // 4. Interactive Checkbox Card (Cacat Kemasan: Kotor, Apek, Basah, Sobek)
    window.togglePlastikCheckCard = function (checkbox) {
        const card = checkbox.closest('.plastik-check-card');
        if (card) {
            if (checkbox.checked) {
                card.classList.add('is-checked');
            } else {
                card.classList.remove('is-checked');
            }
        }
    };

    // 5. Quick Chips Selector (Ketebalan & Keutuhan)
    window.setParamValue = function (targetId, val) {
        const el = document.getElementById(targetId);
        if (el) el.value = val;
    };

    // 6. Satuan Toggle (PCS / Lembar / KG)
    window.setPlastikEditSatuan = function (sat) {
        window.plastikSatuan = sat;
        const inputSatuan = document.getElementById('inputPlastikSatuan');
        const inputItemSatuan = document.getElementById('inputPlastikItemSatuan');
        if (inputSatuan) inputSatuan.value = sat;
        if (inputItemSatuan) inputItemSatuan.value = sat;

        const pillPcs = document.getElementById('pillSatuan_PCS');
        const pillKg = document.getElementById('pillSatuan_KG');
        if (sat === 'KG') {
            pillKg?.classList.add('active');
            pillPcs?.classList.remove('active');
        } else {
            pillPcs?.classList.add('active');
            pillKg?.classList.remove('active');
        }

        document.querySelectorAll('.label-satuan-text').forEach(el => {
            el.innerText = sat;
        });

        const stepVal = (sat === 'KG') ? '0.01' : '1';
        ['inputJumlahSJ', 'inputJumlahPabrik', 'inputGross', 'inputReject'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.step = stepVal;
        });

        window.recalcPlastikNetto();
    };

    // 7. Sinkronisasi Kuantitas Timbangan & Netto
    window.syncPlastikEditSJ = function (val) {
        const pabrikEl = document.getElementById('inputJumlahPabrik');
        if (pabrikEl && (!pabrikEl.value || parseFloat(pabrikEl.value) === 0)) {
            pabrikEl.value = val;
            window.syncPlastikEditPabrik(val);
        }
    };

    window.syncPlastikEditPabrik = function (val) {
        const grossEl = document.getElementById('inputGross');
        if (grossEl) {
            grossEl.value = val;
        }
        window.recalcPlastikNetto();
    };

    window.syncPlastikEditGross = function (val) {
        const pabrikEl = document.getElementById('inputJumlahPabrik');
        if (pabrikEl) {
            pabrikEl.value = val;
        }
        window.recalcPlastikNetto();
    };

    window.recalcPlastikNetto = function () {
        const gross = parseFloat(document.getElementById('inputGross')?.value) || 0;
        const reject = parseFloat(document.getElementById('inputReject')?.value) || 0;
        const netto = Math.max(0, gross - reject);

        const displayEl = document.getElementById('displayNettoText');
        const hiddenEl = document.getElementById('inputNettoLolos');

        const satuan = window.plastikSatuan || 'Pcs';
        if (displayEl) {
            displayEl.innerText = (netto % 1 === 0 ? netto : netto.toFixed(2)).toLocaleString('id-ID') + ' ' + satuan;
        }
        if (hiddenEl) {
            hiddenEl.value = (netto % 1 === 0 ? netto : netto.toFixed(2));
        }
    };

    // 8. Filter & Pencarian Cepat Supplier
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

    window.onEditSupplierChange = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split('(')[0].trim();
            const produsenInput = document.getElementById('editNamaProdusen');
            if (produsenInput) produsenInput.value = supNm;
        }
    };

    window.onEditBarangChanged = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const bNm = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
            const namaEl = document.getElementById('editNamaJenis');
            if (namaEl && !namaEl.value) {
                namaEl.value = bNm;
            }
            const satCd = opt.getAttribute('data-satuan-cd') || opt.getAttribute('data-satuan');
            if (satCd) {
                window.setPlastikEditSatuan(satCd);
            }
        }
    };

    // 9. Pemilihan PO Khusus Plastik Kemasan
    window.onEditPoChanged = function (select) {
        const poId = select.value;
        const opt = select.options[select.selectedIndex];

        const badgeSup = document.getElementById('badgeEditLockSupplier');
        const badgeGdg = document.getElementById('badgeEditLockGudang');
        const badgeBrg = document.getElementById('badgeEditLockBarang');
        const wrapSupSearch = document.getElementById('wrapEditSupplierSearch');

        const supSelect = document.getElementById('editSupplierSelect');
        const gdgSelect = document.getElementById('editGudangSelect');
        const brgSelect = document.getElementById('editBarangSelect');

        if (poId) {
            const supId = opt.getAttribute('data-supplier-id');
            const supNm = opt.getAttribute('data-supplier-nm');
            const gdgId = opt.getAttribute('data-gudang-id');
            const brgId = opt.getAttribute('data-barang-id');

            if (supId && supSelect) {
                supSelect.value = supId;
                supSelect.style.pointerEvents = 'none';
                supSelect.style.backgroundColor = '#f1f5f9';
                supSelect.style.borderColor = '#cbd5e1';
                supSelect.tabIndex = -1;
                window.onEditSupplierChange(supSelect);
            }
            if (badgeSup) {
                badgeSup.style.display = 'inline-flex';
                badgeSup.innerText = '🔒 Terkunci (' + (supNm || 'PO') + ')';
            }
            if (wrapSupSearch) wrapSupSearch.style.display = 'none';

            if (gdgId && gdgSelect) {
                gdgSelect.value = gdgId;
                gdgSelect.style.pointerEvents = 'none';
                gdgSelect.style.backgroundColor = '#f1f5f9';
                gdgSelect.style.borderColor = '#cbd5e1';
                gdgSelect.tabIndex = -1;
            }
            if (badgeGdg) badgeGdg.style.display = 'inline-flex';

            if (brgId && brgSelect) {
                brgSelect.value = brgId;
                brgSelect.style.pointerEvents = 'none';
                brgSelect.style.backgroundColor = '#f1f5f9';
                brgSelect.style.borderColor = '#cbd5e1';
                brgSelect.tabIndex = -1;
                window.onEditBarangChanged(brgSelect);
            }
            if (badgeBrg) badgeBrg.style.display = 'inline-flex';
        } else {
            if (supSelect) {
                supSelect.style.pointerEvents = '';
                supSelect.style.backgroundColor = '';
                supSelect.style.borderColor = '';
                supSelect.tabIndex = 0;
            }
            if (badgeSup) badgeSup.style.display = 'none';
            if (wrapSupSearch) wrapSupSearch.style.display = '';

            if (gdgSelect) {
                gdgSelect.style.pointerEvents = '';
                gdgSelect.style.backgroundColor = '';
                gdgSelect.style.borderColor = '';
                gdgSelect.tabIndex = 0;
            }
            if (badgeGdg) badgeGdg.style.display = 'none';

            if (brgSelect) {
                brgSelect.style.pointerEvents = '';
                brgSelect.style.backgroundColor = '';
                brgSelect.style.borderColor = '';
                brgSelect.tabIndex = 0;
            }
            if (badgeBrg) badgeBrg.style.display = 'none';
        }
    };

    // 10. Submit Handler & Loading Overlay
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('qcEditPlastikForm');
        const overlay = document.getElementById('qcSubmitOverlay');

        if (form && overlay) {
            form.addEventListener('submit', function () {
                overlay.style.display = 'flex';
                const submitBtn = document.getElementById('btnEditSubmit');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                }
            });
        }
    });

})();

