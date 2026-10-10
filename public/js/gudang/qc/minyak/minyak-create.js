/**
 * QC MINYAK GORENG CREATE JAVASCRIPT - PT MIRASA FOOD INDUSTRY
 * Modul Interaksi & Kalkulasi Form QC Inbound Khusus Minyak Goreng (Native Mobile UX)
 * No. Dokumen: MFI/HACCP-04/FRM-03/029/VIII/2021
 */

(function () {
    'use strict';

    let currentTabNumber = 1;
    let isSubmitting = false;
    let currentSupplierCategory = 'BP';
    let currentSupplierSearch = '';
    let currentPoCategory = 'MINYAK';
    let currentPoSearch = '';
    let currentMinyakSatuan = 'KG';

    // 1. Tab Navigasi Khusus Minyak (Hanya 2 Tab, Tampilan Sama Persis Singkong)
    window.switchMinyakTab = function (tabNumber) {
        currentTabNumber = tabNumber;
        const sec1 = document.getElementById('qcSection1');
        const sec2 = document.getElementById('qcSection2');
        const tab1 = document.getElementById('tabBtn1');
        const tab2 = document.getElementById('tabBtn2');

        if (sec1) sec1.style.display = (tabNumber === 1) ? 'block' : 'none';
        if (sec2) sec2.style.display = (tabNumber === 2) ? 'block' : 'none';

        if (tab1) {
            if (tabNumber === 1) {
                tab1.style.background = '#0284c7';
                tab1.style.borderColor = '#0284c7';
                tab1.style.color = '#ffffff';
            } else {
                tab1.style.background = '#ffffff';
                tab1.style.borderColor = '#cbd5e1';
                tab1.style.color = '#475569';
            }
        }

        if (tab2) {
            if (tabNumber === 2) {
                tab2.style.background = '#0284c7';
                tab2.style.borderColor = '#0284c7';
                tab2.style.color = '#ffffff';
            } else {
                tab2.style.background = '#ffffff';
                tab2.style.borderColor = '#cbd5e1';
                tab2.style.color = '#475569';
            }
        }

        updateFloatingDock(tabNumber);

        try {
            sessionStorage.setItem('qc_minyak_active_tab', tabNumber);
            const url = new URL(window.location.href);
            url.hash = 'tab' + tabNumber;
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}

        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // 2. Waktu Inspeksi & Tanggal Datang (Auto-Now, Editable, dan Tombol Pintas)
    window.setCurrentDateTime = function () {
        const input = document.getElementById('inputTglDatang');
        if (!input) return;
        const now = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        const yyyy = now.getFullYear();
        const mm = pad(now.getMonth() + 1);
        const dd = pad(now.getDate());
        const hh = pad(now.getHours());
        const min = pad(now.getMinutes());
        input.value = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
        updateInspectionTimeDisplay(input.value);
    };

    function updateInspectionTimeDisplay(dtString) {
        const timeDisplay = document.getElementById('textInspectionBarTime');
        if (!timeDisplay || !dtString) return;
        try {
            // Langsung parse format YYYY-MM-DDTHH:mm agar akurat tanpa bug offset browser
            const parts = dtString.split('T');
            if (parts.length === 2) {
                const dateParts = parts[0].split('-');
                const timeParts = parts[1].split(':');
                if (dateParts.length === 3 && timeParts.length >= 2) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const year = dateParts[0];
                    const monthIdx = parseInt(dateParts[1], 10) - 1;
                    const day = String(parseInt(dateParts[2], 10)).padStart(2, '0');
                    const hour = timeParts[0];
                    const min = timeParts[1];
                    const formatted = `${day} ${months[monthIdx] || ''} ${year} • ${hour}:${min} WIB`;
                    timeDisplay.innerText = formatted;
                    return;
                }
            }

            const d = new Date(dtString);
            if (!isNaN(d.getTime())) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const pad = (n) => String(n).padStart(2, '0');
                const formatted = `${pad(d.getDate())} ${months[d.getMonth()]} ${d.getFullYear()} • ${pad(d.getHours())}:${pad(d.getMinutes())} WIB`;
                timeDisplay.innerText = formatted;
            }
        } catch (e) {
            // ignore
        }
    }

    // 3. Segmented Toggle Halal (Tidak / Ya)
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

    // 4. Transport Radio Card (Bebas Cemaran vs Ada Cemaran)
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

    // 5. Kondisi Wadah Card (OK vs TIDAK STANDAR)
    window.updateKondisiWadahCard = function (radio) {
        const cardOk = document.getElementById('cardKondisiOk');
        const cardTdk = document.getElementById('cardKondisiTdkStd');
        const hidden = document.getElementById('minyakStatusRawMaterialHidden');
        if (radio.value === 'OK') {
            cardOk?.classList.add('active-ok');
            cardTdk?.classList.remove('active-danger');
            if (hidden) hidden.value = 'OK';
        } else {
            cardTdk?.classList.add('active-danger');
            cardOk?.classList.remove('active-ok');
            if (hidden) hidden.value = 'TDK_STD';
        }
    };

    window.updateWadahChoice = function (radio) {
        // Tipe wadah radio handler
    };

    // 6. Checkbox Cards (Minyak Jernih / Tangki Bersih)
    window.updateCheckCard = function (chk) {
        const parent = chk.closest('.minyak-check-card');
        if (parent) {
            if (chk.checked) parent.classList.add('active');
            else parent.classList.remove('active');
        }
    };

    // 7. Kesimpulan QC Card (TERIMA vs TOLAK)
    window.updateKesimpulanCard = function (radio) {
        const cardTerima = document.getElementById('cardTerima');
        const cardTolak = document.getElementById('cardTolak');
        if (radio.value === 'TERIMA') {
            cardTerima?.classList.add('active-ok');
            cardTolak?.classList.remove('active-danger');
        } else {
            cardTolak?.classList.add('active-danger');
            cardTerima?.classList.remove('active-ok');
        }
    };

    // 8. Floating Dock Navigation (Sesuai qc-form.css di atas Bottom Navbar)
    function updateFloatingDock(tabNumber) {
        const btnBack = document.getElementById('dockBtnBack');
        const btnNext = document.getElementById('dockBtnNext');
        const backText = document.getElementById('dockBackText');
        const nextText = document.getElementById('dockNextText');
        const nextIcon = document.getElementById('dockNextIcon');
        if (!btnBack || !btnNext) return;

        if (tabNumber === 1) {
            btnBack.style.display = 'none';
            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-primary-state';
            if (nextText) nextText.innerText = 'Lanjut ke Tahap 2';
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function () { window.switchMinyakTab(2); };
        } else {
            btnBack.style.display = 'inline-flex';
            if (backText) backText.innerText = 'Kembali';
            btnBack.onclick = function () { window.switchMinyakTab(1); };

            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-success-state';
            if (nextText) nextText.innerText = '💾 Simpan QC Minyak';
            if (nextIcon) nextIcon.style.display = 'none';
            btnNext.onclick = function () { window.submitMinyakForm(); };
        }
    }

    // 3. Tactile Choice Cards & Toggle Status Cards (Native Mobile Touch UX)
    function initTactileChoiceCards() {
        document.querySelectorAll('.tactile-choice-card').forEach(function (card) {
            card.addEventListener('click', function (e) {
                const radio = card.querySelector('input[type="radio"]');
                if (!radio) return;

                radio.checked = true;

                // Sync sibling cards with the same radio name
                const radioName = radio.getAttribute('name');
                if (radioName) {
                    document.querySelectorAll(`input[type="radio"][name="${radioName}"]`).forEach(function (r) {
                        const parentCard = r.closest('.tactile-choice-card');
                        if (parentCard) {
                            parentCard.classList.remove('is-checked-ok', 'is-checked-danger', 'is-checked-primary', 'active-ok', 'active-danger', 'active-primary');
                        }
                    });
                }

                // Apply active styling class
                const choiceType = card.getAttribute('data-choice-type') || 'ok';
                if (choiceType === 'ok') {
                    card.classList.add('is-checked-ok');
                } else if (choiceType === 'danger') {
                    card.classList.add('is-checked-danger');
                } else if (choiceType === 'primary') {
                    card.classList.add('is-checked-primary');
                }

                radio.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        // Halal Choice Pills
        document.querySelectorAll('.halal-choice-pill').forEach(function (pill) {
            pill.addEventListener('click', function (e) {
                const radio = pill.querySelector('input[type="radio"]');
                if (!radio) return;

                radio.checked = true;

                const radioName = radio.getAttribute('name');
                if (radioName) {
                    document.querySelectorAll(`input[type="radio"][name="${radioName}"]`).forEach(function (r) {
                        const parentPill = r.closest('.halal-choice-pill');
                        if (parentPill) parentPill.classList.remove('active');
                    });
                }

                pill.classList.add('active');
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        // Toggle Status Cards (Minyak Jernih / Tangki Bersih)
        document.querySelectorAll('.toggle-status-card').forEach(function (card) {
            card.addEventListener('click', function (e) {
                const chk = card.querySelector('input[type="checkbox"]');
                if (!chk) return;

                if (e.target !== chk) {
                    chk.checked = !chk.checked;
                }

                if (chk.checked) {
                    card.classList.add('checked');
                } else {
                    card.classList.remove('checked');
                }

                chk.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    // 4. Pengaturan Satuan Dinamis (KG vs Liter) Sesuai PO & Master Barang
    window.setMinyakSatuan = function (satuan) {
        if (!satuan) return;
        const upper = String(satuan).trim().toUpperCase();
        if (upper === 'LITER' || upper === 'LTR' || upper === 'L') {
            currentMinyakSatuan = 'Liter';
        } else {
            currentMinyakSatuan = 'KG';
        }

        // 1. Update Hidden Input
        const hidden = document.getElementById('inputMinyakSatuan');
        if (hidden) hidden.value = currentMinyakSatuan;

        // 2. Update Toggle Pill Buttons
        const pillKg = document.getElementById('pillSatuan_KG');
        const pillLiter = document.getElementById('pillSatuan_LITER');
        if (pillKg && pillLiter) {
            if (currentMinyakSatuan === 'KG') {
                pillKg.classList.add('active');
                pillLiter.classList.remove('active');
            } else {
                pillLiter.classList.add('active');
                pillKg.classList.remove('active');
            }
        }

        // 3. Update semua label dan suffix di DOM
        const suffixText = (currentMinyakSatuan === 'KG') ? 'KG' : 'L';
        document.querySelectorAll('.label-satuan-text').forEach(function (el) {
            el.innerText = currentMinyakSatuan;
        });
        document.querySelectorAll('.suffix-satuan-text').forEach(function (el) {
            el.innerText = suffixText;
        });

        // 4. Kalkulasi ulang display kuantitas & formula netto
        window.syncMinyakQuantity();
    };

    // 5. Auto-suggest Nama Jenis & Satuan berdasarkan Master Barang Minyak
    window.onMinyakBarangChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt) {
            // Deteksi Satuan Dasar Barang
            const satCd = opt.getAttribute('data-satuan-cd') || opt.getAttribute('data-satuan') || '';
            if (satCd) {
                window.setMinyakSatuan(satCd);
            }

            const nama = (opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text).trim();
            const upper = nama.toUpperCase();

            let saranJenis = 'RBD Palm Olein / Curah Sawit';
            if (upper.includes('KELAPA') && !upper.includes('SAWIT')) {
                saranJenis = 'Minyak Kelapa (RBD CNO)';
            } else if (upper.includes('SAWIT')) {
                saranJenis = 'RBD Palm Olein / Curah Sawit';
            }

            const inputs = [
                document.getElementById('minyakNamaJenisInput'),
                document.getElementById('namaJenisInput')
            ];

            inputs.forEach(input => {
                if (input && (!input.value || input.dataset.autoFilled === 'true')) {
                    input.value = saranJenis;
                    input.dataset.autoFilled = 'true';
                }
            });
        }
    };

    // 6. Kalkulasi Timbangan Netto & Sinkronisasi
    window.syncMinyakQuantity = function (caller) {
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const inSJ = document.getElementById('inputJumlahSJ');
        const grossInput = document.getElementById('minyakQtyGross');
        const rejectInput = document.getElementById('minyakQtyReject');
        const labelNetto = document.getElementById('labelMinyakNetto');
        const labelNettoFormula = document.getElementById('labelMinyakNettoFormula');
        const cardNettoSJ = document.getElementById('labelSubcardNetto');

        // Sinkronisasi 2 arah Jumlah di Pabrik <-> Timbangan Gross
        if (caller === 'pabrik' && inPabrik && grossInput) {
            grossInput.value = inPabrik.value;
        } else if (caller === 'gross' && grossInput && inPabrik) {
            inPabrik.value = grossInput.value;
        } else {
            if (inPabrik && inPabrik.value && grossInput && (!grossInput.value || grossInput.value == 0)) {
                grossInput.value = inPabrik.value;
            } else if (grossInput && grossInput.value && inPabrik && (!inPabrik.value || inPabrik.value == 0)) {
                inPabrik.value = grossInput.value;
            }
        }

        const sj = parseFloat(inSJ?.value || 0) || 0;
        const gross = parseFloat(grossInput?.value || inPabrik?.value || 0) || 0;
        const reject = parseFloat(rejectInput?.value || 0) || 0;
        const netto = Math.max(0, gross - reject);

        // Satuan aktif saat ini
        const satText = currentMinyakSatuan || 'KG';

        // Format angka ribuan Indonesia
        const formatNum = (val) => val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (labelNetto) {
            labelNetto.innerText = `${formatNum(netto)} ${satText}`;
            labelNetto.style.color = (netto <= 0 && gross > 0) ? '#dc2626' : '#059669';
        }

        if (labelNettoFormula) {
            if (reject > 0) {
                labelNettoFormula.innerText = `${formatNum(gross)} ${satText} datang - ${formatNum(reject)} ${satText} reject = ${formatNum(netto)} ${satText} diterima`;
            } else {
                labelNettoFormula.innerText = `Sesuai timbangan datang (${formatNum(gross)} ${satText} tanpa reject)`;
            }
        }

        if (cardNettoSJ) {
            cardNettoSJ.innerText = `Netto: ${formatNum(netto)} ${satText}`;
            cardNettoSJ.style.color = (netto <= 0 && gross > 0) ? '#dc2626' : '#059669';
        }

        // Tampilkan indikator selisih Surat Jalan vs Kedatangan Pabrik
        const selisihBadge = document.getElementById('badgeSelisihTimbangan');
        if (selisihBadge) {
            if (sj > 0 && gross > 0) {
                const diff = gross - sj;
                const pct = ((diff / sj) * 100).toFixed(2);
                selisihBadge.style.display = 'inline-block';
                if (diff < 0) {
                    selisihBadge.className = 'badge-selisih-susut';
                    selisihBadge.innerText = `⚖️ Susut: ${formatNum(diff)} ${satText} (${pct}%)`;
                } else if (diff > 0) {
                    selisihBadge.className = 'badge-selisih-surplus';
                    selisihBadge.innerText = `⚖️ Surplus: +${formatNum(diff)} ${satText} (+${pct}%)`;
                } else {
                    selisihBadge.className = 'badge-selisih-pas';
                    selisihBadge.innerText = `⚖️ Sesuai DO (100%)`;
                }
            } else {
                selisihBadge.style.display = 'none';
            }
        }

        // Jika reject = gross dan gross > 0, otomatis alihkan ke TOLAK
        const radioTerima = document.getElementById('minyakRadioTerima');
        const radioTolak = document.getElementById('minyakRadioTolak');
        if (gross > 0 && reject >= gross) {
            if (radioTolak) {
                radioTolak.checked = true;
                if (typeof window.updateKesimpulanCard === 'function') window.updateKesimpulanCard(radioTolak);
            }
        }
    };

    // 6. Validasi FFA QC (Standard HACCP MFI: Max 0.20%)
    window.checkMinyakAcceptance = function () {
        const ffaInput = document.getElementById('inputMinyakFfaQc');
        const ffaQc = parseFloat(ffaInput?.value || 0);
        const radioTerima = document.getElementById('minyakRadioTerima');
        const radioTolak = document.getElementById('minyakRadioTolak');
        const badgeFfa = document.getElementById('badgeFfaStatus');
        const badgeText = document.getElementById('badgeFfaText');
        const badgeIcon = document.getElementById('badgeFfaIcon');

        if (badgeFfa) {
            if (ffaQc > 0.20) {
                badgeFfa.style.background = '#fee2e2';
                badgeFfa.style.borderColor = '#fca5a5';
                badgeFfa.style.color = '#dc2626';
                if (badgeIcon) badgeIcon.innerText = '⚠️';
                if (badgeText) badgeText.innerText = `Melebihi Standar (${ffaQc}% > 0.20%)`;
            } else if (ffaQc > 0) {
                badgeFfa.style.background = '#dcfce7';
                badgeFfa.style.borderColor = '#86efac';
                badgeFfa.style.color = '#166534';
                if (badgeIcon) badgeIcon.innerText = '✔';
                if (badgeText) badgeText.innerText = `Sesuai Standar (${ffaQc}% ≤ 0.20%)`;
            } else {
                badgeFfa.style.background = '#dcfce7';
                badgeFfa.style.borderColor = '#86efac';
                badgeFfa.style.color = '#166534';
                if (badgeIcon) badgeIcon.innerText = '✔';
                if (badgeText) badgeText.innerText = 'Sesuai Standar (≤0.20%)';
            }
        }

        if (ffaQc > 0.20) {
            if (radioTolak) {
                radioTolak.checked = true;
                if (typeof window.updateKesimpulanCard === 'function') window.updateKesimpulanCard(radioTolak);
            }

            if (typeof window.showQcToast === 'function') {
                window.showQcToast(
                    'FFA Melebihi Batas Maksimal',
                    `Kadar FFA ${ffaQc}% melebihi batas maksimal HACCP (0.20%). Disarankan TOLAK kedatangan minyak ini.`,
                    ffaInput,
                    2,
                    true
                );
            }
        } else if (ffaQc > 0 && ffaQc <= 0.20) {
            if (radioTerima && !radioTerima.checked && radioTolak && !radioTolak.dataset.userManual) {
                radioTerima.checked = true;
                if (typeof window.updateKesimpulanCard === 'function') window.updateKesimpulanCard(radioTerima);
            }
        }
    };

    // 7. Filter & Quick Search Rekanan Supplier
    window.setSupplierCategoryFilter = function (cat) {
        currentSupplierCategory = cat;
        ['BP', 'ALL'].forEach(c => {
            const chip = document.getElementById(`chipSupplier_${c}`);
            if (chip) {
                if (c === cat) chip.classList.add('active-chip');
                else chip.classList.remove('active-chip');
            }
        });
        filterSupplierDropdown();
    };

    window.onSearchSupplier = function (query) {
        currentSupplierSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearSupplierSearch');
        if (clearBtn) clearBtn.style.display = currentSupplierSearch ? 'block' : 'none';
        filterSupplierDropdown();
    };

    window.clearSupplierSearch = function () {
        const input = document.getElementById('supplierSearchInput');
        if (input) input.value = '';
        window.onSearchSupplier('');
    };

    function filterSupplierDropdown() {
        const select = document.getElementById('supplierSelect');
        if (!select) return;
        if (!document.getElementById('supplierFilterChips')) return;

        const badge = document.getElementById('supplierFilterBadge');
        let visibleCount = 0;
        const currentVal = select.value;
        let isCurrentValVisible = false;

        const q = currentSupplierSearch;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                opt.style.display = '';
                opt.disabled = false;
                continue;
            }

            const jenisCd = (opt.getAttribute('data-jenis-cd') || '').toUpperCase();
            const isPetani = opt.getAttribute('data-is-petani') === '1';
            const supNm = (opt.getAttribute('data-supplier-nm') || opt.text).toLowerCase();
            const supCd = (opt.getAttribute('data-supplier-cd') || '').toLowerCase();

            let matchCat = false;
            if (currentSupplierCategory === 'ALL') {
                matchCat = true;
            } else {
                // Khusus Bahan Penolong (BP / Pabrik Minyak Goreng)
                matchCat = (jenisCd === 'BP') || (!isPetani && !supCd.startsWith('skg-') && jenisCd !== 'RAW');
            }

            let matchSearch = true;
            if (q) matchSearch = supNm.includes(q) || supCd.includes(q);

            const isMatch = matchCat && matchSearch;
            if (isMatch) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                visibleCount++;
                if (opt.value === currentVal) isCurrentValVisible = true;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        if (badge) {
            badge.innerText = (currentSupplierCategory === 'ALL')
                ? `Filter: 🌐 Semua Supplier (${visibleCount})`
                : `Filter: 🏭 Bahan Penolong (${visibleCount})`;
        }

        if (!isCurrentValVisible && currentVal) {
            const selectedPoId = document.getElementById('poSelect')?.value;
            if (!selectedPoId) select.value = '';
        }
    }

    window.onSupplierSelected = function (select) {
        if (!select || !select.value) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt) {
            const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split(' (')[0];
            const produsenInput = document.getElementById('namaProdusenInput');
            if (produsenInput) {
                produsenInput.value = supNm.trim();
                produsenInput.dataset.autoFilled = 'true';
            }
        }
    };

    // 8. Filter & Quick Search PO
    window.setPoCategoryFilter = function (cat) {
        currentPoCategory = cat;
        ['AUTO', 'MINYAK', 'ALL'].forEach(k => {
            const btn = document.getElementById(`chipPo_${k}`);
            if (btn) {
                if (k === cat) btn.classList.add('active-chip');
                else btn.classList.remove('active-chip');
            }
        });
        filterPoDropdown();
    };

    window.onSearchPo = function (query) {
        currentPoSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearPoSearch');
        if (clearBtn) clearBtn.style.display = currentPoSearch ? 'block' : 'none';
        filterPoDropdown();
    };

    window.clearPoSearch = function () {
        const input = document.getElementById('poSearchInput');
        if (input) input.value = '';
        window.onSearchPo('');
    };

    function filterPoDropdown() {
        const select = document.getElementById('poSelect');
        if (!select) return;
        if (!document.getElementById('poFilterChips')) return;

        const badge = document.getElementById('poFilterBadge');
        let visibleCount = 0;
        const currentVal = select.value;
        let isCurrentValVisible = false;
        const q = currentPoSearch;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                opt.style.display = '';
                opt.disabled = false;
                continue;
            }

            const komoditasAttr = opt.getAttribute('data-komoditas') || '';
            const komoditasList = komoditasAttr.split(',').map(s => s.trim().toUpperCase());
            const searchText = (opt.getAttribute('data-search') || opt.text).toLowerCase();

            let matchCat = (currentPoCategory === 'ALL') ? true : komoditasList.includes('MINYAK');
            let matchSearch = q ? searchText.includes(q) : true;

            const isMatch = matchCat && matchSearch;
            if (isMatch) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                visibleCount++;
                if (opt.value === currentVal) isCurrentValVisible = true;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        if (badge) badge.innerText = `Filter: 🛢️ Minyak Goreng (${visibleCount} PO)`;
        if (currentVal && !isCurrentValVisible) select.value = '';
    }

    window.onPoSelected = function (select) {
        if (!select || !select.value) return;
        const poId = select.value;
        const poData = window.qcConfig?.poList?.[poId];
        if (!poData) return;

        // 1. Pilih Mitra Vendor & Sinkronkan
        if (poData.supplier_id) {
            const supSelect = document.getElementById('supplierSelect');
            if (supSelect) {
                let targetOpt = supSelect.querySelector(`option[value="${poData.supplier_id}"]`);
                if (!targetOpt && poData.supplier_nm) {
                    targetOpt = document.createElement('option');
                    targetOpt.value = poData.supplier_id;
                    targetOpt.text = `${poData.supplier_nm}`;
                    targetOpt.setAttribute('data-supplier-nm', poData.supplier_nm);
                    supSelect.appendChild(targetOpt);
                }
                supSelect.value = poData.supplier_id;
                window.onSupplierSelected(supSelect);
            }
        }

        // 2. Auto-fill Nama Produsen jika kosong / auto-filled
        const produsenInput = document.getElementById('namaProdusenInput');
        if (produsenInput && poData.supplier_nm && (!produsenInput.value || produsenInput.dataset.autoFilled === 'true')) {
            produsenInput.value = poData.supplier_nm;
            produsenInput.dataset.autoFilled = 'true';
        }

        // 3. Auto-fill Gudang Tujuan Bongkar
        if (poData.gudang_id) {
            const gudangSelect = document.getElementById('gudangSelect');
            if (gudangSelect) gudangSelect.value = poData.gudang_id;
        }

        // 4. Auto-fill Bahan Baku, Spesifikasi Jenis, Satuan & Kuantitas dari PO
        if (poData.items && poData.items.length > 0) {
            const firstItem = poData.items[0];
            const bSel = document.getElementById('minyakBarangSelect');
            if (bSel && firstItem.barang_id) {
                bSel.value = firstItem.barang_id;
                window.onMinyakBarangChanged(bSel);
            }

            // Sinkronkan Satuan dari Item PO (KG vs Liter)
            if (firstItem.satuan_cd || firstItem.satuan) {
                window.setMinyakSatuan(firstItem.satuan_cd || firstItem.satuan);
            }

            // Nama Jenis / Fraksi
            const namaJenisInputs = [
                document.getElementById('namaJenisInput'),
                document.getElementById('minyakNamaJenisInput')
            ];
            namaJenisInputs.forEach(input => {
                if (input && (!input.value || input.dataset.autoFilled === 'true')) {
                    input.value = firstItem.barang_nm || 'RBD Palm Olein / Curah Sawit';
                    input.dataset.autoFilled = 'true';
                }
            });

            // Kuantitas: Sisa Pesanan PO atau Pesan Qty
            const sisaQty = firstItem.sisa_qty > 0 ? firstItem.sisa_qty : (firstItem.pesan_qty || 0);
            if (sisaQty > 0) {
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const grossInput = document.getElementById('minyakQtyGross');

                if (inSJ && (!inSJ.value || inSJ.dataset.autoFilled === 'true')) {
                    inSJ.value = sisaQty;
                    inSJ.dataset.autoFilled = 'true';
                }
                if (inPabrik && (!inPabrik.value || inPabrik.dataset.autoFilled === 'true')) {
                    inPabrik.value = sisaQty;
                    inPabrik.dataset.autoFilled = 'true';
                }
                if (grossInput && (!grossInput.value || grossInput.dataset.autoFilled === 'true')) {
                    grossInput.value = sisaQty;
                    grossInput.dataset.autoFilled = 'true';
                }
                window.syncMinyakQuantity();
            }
        }
    };

    // 9. Validasi & Submit Form Khusus Minyak
    function injectHiddenInput(name, value) {
        let el = document.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = name;
            document.getElementById('qcMinyakForm').appendChild(el);
        }
        el.value = value;
    }

    function showSubmitLoading(title = 'Menyimpan Data QC Minyak...') {
        isSubmitting = true;
        const overlay = document.getElementById('qcSubmitOverlay');
        const overlayTitle = document.getElementById('overlayTitle');
        if (overlayTitle) overlayTitle.innerText = title;
        if (overlay) overlay.style.display = 'flex';

        document.querySelectorAll('button[type="submit"], #btnSimpanCepat, #dockBtnNext, #btnTopSimpanCepat').forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
        });
    }

    window.submitMinyakForm = function () {
        if (isSubmitting) return;

        const supplierSelect = document.getElementById('supplierSelect');
        if (!supplierSelect || !supplierSelect.value) {
            alert('Silakan pilih Mitra Supplier / Produsen Minyak terlebih dahulu!');
            window.switchMinyakTab(1);
            supplierSelect?.focus();
            return;
        }

        const gudangSelect = document.getElementById('gudangSelect');
        if (!gudangSelect || !gudangSelect.value) {
            alert('Silakan pilih Perusahaan / Gudang Tujuan Bongkar!');
            window.switchMinyakTab(1);
            gudangSelect?.focus();
            return;
        }

        const inputJumlahSJ = document.getElementById('inputJumlahSJ');
        const sjVal = parseFloat(inputJumlahSJ?.value || 0);
        if (isNaN(sjVal) || sjVal <= 0) {
            alert('Jumlah kuantitas pada Surat Jalan wajib diisi dan lebih dari 0 KG!');
            window.switchMinyakTab(1);
            inputJumlahSJ?.focus();
            return;
        }

        const minyakSelect = document.getElementById('minyakBarangSelect');
        if (!minyakSelect || !minyakSelect.value) {
            alert('Pilih item komoditas minyak goreng yang diuji!');
            window.switchMinyakTab(2);
            minyakSelect?.focus();
            return;
        }

        const grossInput = document.getElementById('minyakQtyGross');
        const grossVal = parseFloat(grossInput?.value || 0);
        const pabrikVal = parseFloat(document.getElementById('inputJumlahPabrik')?.value || 0);
        if ((isNaN(grossVal) || grossVal <= 0) && (isNaN(pabrikVal) || pabrikVal <= 0)) {
            alert('Kuantitas timbangan netto kedatangan minyak wajib diisi lebih dari 0 KG!');
            window.switchMinyakTab(2);
            grossInput?.focus();
            return;
        }

        // Siapkan Payload khusus Minyak
        const bId = minyakSelect.value;
        const bOpt = minyakSelect.selectedOptions[0];
        const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Minyak Goreng Kelapa Sawit';
        const curNamaJenis = document.getElementById('minyakNamaJenisInput')?.value?.trim() || document.getElementById('namaJenisInput')?.value?.trim() || bNm;

        const gross = grossVal > 0 ? grossVal : pabrikVal;
        const reject = parseFloat(document.getElementById('minyakQtyReject')?.value || 0) || 0;
        const kesimpulan = document.querySelector('input[name="minyak_kesimpulan"]:checked')?.value || 'TERIMA';

        injectHiddenInput('nama_jenis', curNamaJenis);
        injectHiddenInput('minyak_satuan', currentMinyakSatuan);
        injectHiddenInput('satuan', currentMinyakSatuan);
        injectHiddenInput('items[0][satuan]', currentMinyakSatuan);
        injectHiddenInput('items[0][barang_id]', bId);
        injectHiddenInput('items[0][qty_timbang_gross]', gross);
        injectHiddenInput('items[0][qty_reject]', reject);
        injectHiddenInput('items[0][qty_netto_lolos]', Math.max(0, gross - reject));
        injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
        injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="minyak_status_raw_material"]:checked')?.value || 'OK');
        injectHiddenInput('items[0][tipe_wadah_minyak]', document.querySelector('input[name="minyak_tipe_wadah"]:checked')?.value || 'TANGKI');
        injectHiddenInput('items[0][kondisi_tangki_jerigen]', document.querySelector('input[name="minyak_kondisi_wadah"]:checked')?.value || 'OK');
        injectHiddenInput('items[0][ffa_coa]', document.querySelector('input[name="minyak_ffa_coa"]')?.value || '');
        injectHiddenInput('items[0][ffa_qc]', document.querySelector('input[name="minyak_ffa_qc"]')?.value || '');
        injectHiddenInput('items[0][minyak_jernih_st]', document.querySelector('input[name="minyak_jernih_st"]')?.checked ? 1 : 0);
        injectHiddenInput('items[0][tangki_bersih_st]', document.querySelector('input[name="minyak_tangki_bersih_st"]')?.checked ? 1 : 0);
        injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="minyak_komentar"]')?.value || '');
        injectHiddenInput('catatan_umum', document.querySelector('textarea[name="minyak_komentar"]')?.value || '');

        // Header level
        injectHiddenInput('kesimpulan_qc', kesimpulan);
        injectHiddenInput('jumlah_di_pabrik', gross);
        injectHiddenInput('jumlah_surat_jalan', sjVal > 0 ? sjVal : gross);
        const sampleGr = document.querySelector('input[name="jumlah_sample_gr"]');
        injectHiddenInput('jumlah_sample_gr', parseFloat(sampleGr?.value || 250) || 250);

        injectHiddenInput('status_uji_goreng', 'SELESAI');
        injectHiddenInput('tahap_uji', 'PENGUJIAN_1');

        const petugasQc = document.getElementById('inputPetugasQc')?.value?.trim();
        if (petugasQc) injectHiddenInput('petugas_qc_nama', petugasQc);

        const supervisorQc = document.getElementById('inputSupervisorQc')?.value?.trim();
        if (supervisorQc) injectHiddenInput('qc_supervisor_nama', supervisorQc);

        showSubmitLoading(kesimpulan === 'TOLAK' ? 'Menyimpan Penolakan Minyak...' : 'Menyimpan QC Minyak & Menyiapkan Gudang...');
        document.getElementById('qcMinyakForm').submit();
    };

    // 10. Inisialisasi Saat Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        window.switchMinyakTab(1);
        initTactileChoiceCards();
        filterSupplierDropdown();
        filterPoDropdown();

        // Inisialisasi Tanggal & Waktu Kedatangan
        const inputTgl = document.getElementById('inputTglDatang');
        if (inputTgl) {
            const hasOld = inputTgl.getAttribute('data-has-old') === '1';
            if (!hasOld) {
                window.setCurrentDateTime();
            } else {
                updateInspectionTimeDisplay(inputTgl.value);
            }
            inputTgl.addEventListener('input', function () {
                updateInspectionTimeDisplay(this.value);
            });
            inputTgl.addEventListener('change', function () {
                updateInspectionTimeDisplay(this.value);
            });
        }

        // Inisialisasi Satuan Awal (Default KG atau sesuai old value)
        const initSatuanInput = document.getElementById('inputMinyakSatuan');
        if (initSatuanInput && initSatuanInput.value) {
            window.setMinyakSatuan(initSatuanInput.value);
        }

        const poSelect = document.getElementById('poSelect');
        if (poSelect && poSelect.value) {
            window.onPoSelected(poSelect);
        } else {
            const bSel = document.getElementById('minyakBarangSelect');
            if (bSel) window.onMinyakBarangChanged(bSel);
        }

        window.syncMinyakQuantity();
        if (typeof window.checkMinyakAcceptance === 'function') {
            window.checkMinyakAcceptance();
        }

        // Pulihkan tab aktif terakhir
        let activeTab = 1;
        const hashMatch = window.location.hash.match(/tab(\d+)/);
        if (hashMatch) {
            activeTab = parseInt(hashMatch[1], 10);
        } else {
            const savedTab = sessionStorage.getItem('qc_minyak_active_tab');
            if (savedTab) activeTab = parseInt(savedTab, 10);
        }
        window.switchMinyakTab(activeTab || 1);
    });

})();
