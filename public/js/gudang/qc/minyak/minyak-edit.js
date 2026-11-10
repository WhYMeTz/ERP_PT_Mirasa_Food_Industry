/**
 * QC INBOUND - MOBILE EDIT MINYAK GORENG LOGIC
 * PT Mirasa Food Industry
 * Logika Sinkronisasi Kuantitas, Uji FFA, Status Wadah, dan Interaksi UI
 */

(function () {
    'use strict';

    // 1. Toggle Segmented Pilihan Halal (SJH)
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
        } else if (field === 'ada_sertifikat') {
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
        } else if (field === 'sertifikat_berlaku') {
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

    // 3. Pill Toggle untuk Radio Buttons (Status Raw Material, Tipe Wadah, Kondisi Wadah, Keputusan)
    window.selectMinyakPill = function (groupName, value) {
        const radios = document.querySelectorAll(`input[name="${groupName}"]`);
        radios.forEach(r => {
            if (r.value === value) {
                r.checked = true;
                const parentPill = r.closest('.minyak-pill-btn');
                if (parentPill) {
                    const groupContainer = parentPill.closest('.minyak-pill-group') || parentPill.parentElement;
                    groupContainer.querySelectorAll('.minyak-pill-btn').forEach(p => {
                        p.classList.remove('is-active-ok', 'is-active-danger', 'is-active-blue');
                    });

                    // Terapkan class aktif berdasarkan tipe nilai
                    if (value === 'OK' || value === 'TERIMA') {
                        parentPill.classList.add('is-active-ok');
                    } else if (value === 'TDK_STD' || value === 'TIDAK_STANDARD' || value === 'TOLAK') {
                        parentPill.classList.add('is-active-danger');
                    } else {
                        parentPill.classList.add('is-active-blue');
                    }
                }

                // Trigger onchange manual jika ada
                if (typeof r.onchange === 'function') {
                    r.onchange();
                }
            }
        });

        // Sinkronisasi otomatis Kuantitas Reject & Netto jika Keputusan Akhir diubah
        if (groupName === 'kesimpulan_qc') {
            const rejectInput = document.getElementById('inputReject');
            const grossInput = document.getElementById('inputGross');
            const grossVal = parseFloat(grossInput?.value) || 0;
            const rejectVal = parseFloat(rejectInput?.value) || 0;

            if (value === 'TERIMA') {
                // Jika status sebelumnya DITOLAK (reject >= gross atau netto = 0), otomatis nolkan reject
                if (rejectVal >= grossVal && grossVal > 0) {
                    if (rejectInput) rejectInput.value = 0;
                    window.recalcMinyakNetto();
                }
            } else if (value === 'TOLAK') {
                // Jika user memilih TOLAK, seluruh kuantitas gross otomatis menjadi reject
                if (rejectInput && grossVal > 0) {
                    rejectInput.value = grossVal % 1 === 0 ? grossVal : grossVal.toFixed(2);
                    window.recalcMinyakNetto();
                }
            }
        }
    };

    // 4. Interactive Checkbox Card (Jernih & Tangki Bersih)
    window.toggleMinyakCheckCard = function (checkbox) {
        const card = checkbox.closest('.minyak-check-card');
        if (card) {
            if (checkbox.checked) {
                card.classList.add('is-checked');
            } else {
                card.classList.remove('is-checked');
            }
        }
    };

    // 5. Sinkronisasi Kuantitas Timbangan & Netto
    window.syncMinyakEditSJ = function (val) {
        const pabrikEl = document.getElementById('inputJumlahPabrik');
        if (pabrikEl && (!pabrikEl.value || parseFloat(pabrikEl.value) === 0)) {
            pabrikEl.value = val;
            window.syncMinyakEditPabrik(val);
        }
    };

    window.syncMinyakEditPabrik = function (val) {
        const grossEl = document.getElementById('inputGross');
        if (grossEl) {
            grossEl.value = val;
        }
        window.recalcMinyakNetto();
    };

    window.syncMinyakEditGross = function (val) {
        const pabrikEl = document.getElementById('inputJumlahPabrik');
        if (pabrikEl) {
            pabrikEl.value = val;
        }
        window.recalcMinyakNetto();
    };

    window.recalcMinyakNetto = function () {
        const gross = parseFloat(document.getElementById('inputGross')?.value) || 0;
        const reject = parseFloat(document.getElementById('inputReject')?.value) || 0;
        const netto = Math.max(0, gross - reject);

        const displayEl = document.getElementById('displayNettoText');
        const hiddenEl = document.getElementById('inputNettoLolos');

        const satuan = window.minyakSatuan || 'kg';
        if (displayEl) {
            displayEl.innerText = (netto % 1 === 0 ? netto : netto.toFixed(2)).toLocaleString('id-ID') + ' ' + satuan;
        }
        if (hiddenEl) {
            hiddenEl.value = (netto % 1 === 0 ? netto : netto.toFixed(2));
        }
    };

    // 6. Uji Laboratorium Kimia Asam Lemak Bebas (FFA)
    window.checkMinyakFfaAlert = function (val) {
        const ffaVal = parseFloat(val) || 0;
        const banner = document.getElementById('bannerFfaAlert');
        const radioTolak = document.getElementById('radioTolak');

        if (ffaVal > 0.20) {
            if (banner) banner.style.display = 'block';
            if (radioTolak) {
                window.selectMinyakPill('kesimpulan_qc', 'TOLAK');
            }
        } else {
            if (banner) banner.style.display = 'none';
        }
    };

    // 7. Filter & Pencarian Cepat Supplier
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

    // 8. Auto-fill Spesifikasi Minyak Saat Barang Berubah
    window.onEditBarangChanged = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const bNm = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
            const namaEl = document.getElementById('editNamaJenis');
            if (namaEl) {
                const upper = bNm.toUpperCase();
                if (upper.includes('KELAPA') && !upper.includes('SAWIT')) {
                    namaEl.value = 'Minyak Kelapa (RBD CNO)';
                } else {
                    namaEl.value = 'RBD Palm Olein / Curah Sawit';
                }
            }
        }
    };

    // 9. PO Selection Changed
    window.onEditPoChanged = function (select) {
        const opt = select.options[select.selectedIndex];
        const badgeSupplier = document.getElementById('badgeEditLockSupplier');
        const badgeGudang = document.getElementById('badgeEditLockGudang');
        const badgeBarang = document.getElementById('badgeEditLockBarang');
        const searchWrap = document.getElementById('wrapEditSupplierSearch');
        const sSelect = document.getElementById('editSupplierSelect');
        const gSelect = document.getElementById('editGudangSelect');
        const bSelect = document.getElementById('editBarangSelect');

        if (opt && opt.value) {
            const supId = opt.getAttribute('data-supplier-id');
            const supNm = opt.getAttribute('data-supplier-nm');
            const gudangId = opt.getAttribute('data-gudang-id');
            const barangId = opt.getAttribute('data-barang-id');

            if (sSelect && supId) {
                sSelect.value = supId;
                sSelect.style.pointerEvents = 'none';
                sSelect.style.backgroundColor = '#f1f5f9';
                sSelect.style.borderColor = '#cbd5e1';
                sSelect.setAttribute('tabindex', '-1');
            }
            if (badgeSupplier) {
                badgeSupplier.style.display = 'inline-flex';
                if (supNm) badgeSupplier.innerText = `🔒 Terkunci (${supNm})`;
            }
            if (searchWrap) searchWrap.style.display = 'none';

            if (gSelect && gudangId) {
                gSelect.value = gudangId;
                gSelect.style.pointerEvents = 'none';
                gSelect.style.backgroundColor = '#f1f5f9';
                gSelect.style.borderColor = '#cbd5e1';
                gSelect.setAttribute('tabindex', '-1');
            }
            if (badgeGudang) badgeGudang.style.display = 'inline-flex';

            if (bSelect && barangId) {
                bSelect.value = barangId;
                bSelect.style.pointerEvents = 'none';
                bSelect.style.backgroundColor = '#f1f5f9';
                bSelect.style.borderColor = '#cbd5e1';
                bSelect.setAttribute('tabindex', '-1');
                window.onEditBarangChanged(bSelect);
            }
            if (badgeBarang) badgeBarang.style.display = 'inline-flex';
        } else {
            // Unlock fields
            if (sSelect) {
                sSelect.style.pointerEvents = 'auto';
                sSelect.style.backgroundColor = '#ffffff';
                sSelect.style.borderColor = '#cbd5e1';
                sSelect.removeAttribute('tabindex');
            }
            if (badgeSupplier) badgeSupplier.style.display = 'none';
            if (searchWrap) searchWrap.style.display = '';

            if (gSelect) {
                gSelect.style.pointerEvents = 'auto';
                gSelect.style.backgroundColor = '#ffffff';
                gSelect.style.borderColor = '#cbd5e1';
                gSelect.removeAttribute('tabindex');
            }
            if (badgeGudang) badgeGudang.style.display = 'none';

            if (bSelect) {
                bSelect.style.pointerEvents = 'auto';
                bSelect.style.backgroundColor = '#ffffff';
                bSelect.style.borderColor = '#cbd5e1';
                bSelect.removeAttribute('tabindex');
            }
            if (badgeBarang) badgeBarang.style.display = 'none';
        }
    };

    // 10. Inisialisasi DOM Saat Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi status pill toggle dari input checked
        document.querySelectorAll('.minyak-pill-btn input[type="radio"]:checked').forEach(r => {
            const pill = r.closest('.minyak-pill-btn');
            if (pill) {
                if (r.value === 'OK' || r.value === 'TERIMA') {
                    pill.classList.add('is-active-ok');
                } else if (r.value === 'TDK_STD' || r.value === 'TIDAK_STANDARD' || r.value === 'TOLAK') {
                    pill.classList.add('is-active-danger');
                } else {
                    pill.classList.add('is-active-blue');
                }
            }
        });

        // Inisialisasi status check-card dari checkbox checked
        document.querySelectorAll('.minyak-check-card input[type="checkbox"]').forEach(cb => {
            window.toggleMinyakCheckCard(cb);
        });

        // Submit overlay handler
        document.getElementById('qcEditMinyakForm')?.addEventListener('submit', function () {
            const overlay = document.getElementById('qcSubmitOverlay');
            if (overlay) overlay.style.display = 'flex';
        });
    });

    console.log('[QC Minyak] Mobile Edit logic loaded successfully.');
})();

