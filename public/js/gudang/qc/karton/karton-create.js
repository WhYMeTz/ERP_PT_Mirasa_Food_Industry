/**
 * QC INBOUND - MODUL KOMODITAS: KARTON BOX
 * PT Mirasa Food Industry
 * Dedicated JS controller untuk halaman Mobile QC Karton Box
 * No. Dokumen: MFI/HACCP-04/FRM-03/031/VIII/2021
 * Tema Visual & Interaksi Sesuai Standar QC Plastik Kemasan & HACCP
 */

(function () {
    'use strict';

    let currentTab = 1;
    let isSubmitting = false;
    let currentKartonSatuan = 'PCS';
    let saveDraftTimer = null;
    let originalBarangOptionsHTML = null;
    let currentPodtlId = null;

    const DRAFT_KEY = 'QC_KARTON_DRAFT_V1';
    const ACTIVE_TAB_KEY = 'QC_KARTON_ACTIVE_TAB';

    function triggerSaveDraftDebounced() {
        if (isSubmitting) return;
        clearTimeout(saveDraftTimer);
        saveDraftTimer = setTimeout(saveKartonDraft, 400);
    }

    let currentKartonGrade = 'A';

    window.selectKartonGrade = function (grade) {
        currentKartonGrade = (grade === 'B') ? 'B' : 'A';
        const inputHidden = document.getElementById('inputKartonGradeCd');
        if (inputHidden) inputHidden.value = currentKartonGrade;

        const btnA = document.getElementById('btnKartonGradeA');
        const btnB = document.getElementById('btnKartonGradeB');
        const badge = document.getElementById('badgeCurrentKartonGrade');

        if (btnA && btnB) {
            if (currentKartonGrade === 'B') {
                btnA.classList.remove('active');
                btnB.classList.add('active');
                if (badge) {
                    badge.style.background = '#fef3c7';
                    badge.style.color = '#b45309';
                    badge.innerHTML = '🟡 GRADE B (PUTERAN)';
                }
            } else {
                btnB.classList.remove('active');
                btnA.classList.add('active');
                if (badge) {
                    badge.style.background = '#dcfce7';
                    badge.style.color = '#15803d';
                    badge.innerHTML = '🟢 GRADE A (BARU)';
                }
            }
        }
        triggerSaveDraftDebounced();
    };

    // 1. Tab Navigation (Tahap 1: Dokumen & Armada, Tahap 2: Mutu Karton Box)
    window.switchKartonTab = function (tabNumber, skipValidation) {
        const sec1 = document.getElementById('qcSection1');
        const sec2 = document.getElementById('qcSection2');
        const btn1 = document.getElementById('tabBtn1');
        const btn2 = document.getElementById('tabBtn2');

        if (!sec1 || !sec2) return;

        if (tabNumber === 2 && !skipValidation) {
            const sup = document.getElementById('supplierSelect')?.value;
            const gud = document.getElementById('gudangSelect')?.value;
            if (!sup) {
                alert('Silakan pilih Mitra Supplier Karton terlebih dahulu!');
                document.getElementById('supplierSelect')?.focus();
                return;
            }
            if (!gud) {
                alert('Silakan pilih Perusahaan / Gudang Bongkar terlebih dahulu!');
                document.getElementById('gudangSelect')?.focus();
                return;
            }
        }

        currentTab = tabNumber;
        sessionStorage.setItem(ACTIVE_TAB_KEY, tabNumber.toString());

        if (tabNumber === 1) {
            sec1.style.display = 'flex';
            sec2.style.display = 'none';

            if (btn1) {
                btn1.style.background = '#d97706';
                btn1.style.borderColor = '#d97706';
                btn1.style.color = '#ffffff';
                btn1.classList.add('active-tab-btn');
            }
            if (btn2) {
                btn2.style.background = '#ffffff';
                btn2.style.borderColor = '#cbd5e1';
                btn2.style.color = '#475569';
                btn2.classList.remove('active-tab-btn');
            }
            updateDockingButtons(1);
        } else {
            sec1.style.display = 'none';
            sec2.style.display = 'block';

            if (btn1) {
                btn1.style.background = '#ffffff';
                btn1.style.borderColor = '#cbd5e1';
                btn1.style.color = '#475569';
                btn1.classList.remove('active-tab-btn');
            }
            if (btn2) {
                btn2.style.background = '#d97706';
                btn2.style.borderColor = '#d97706';
                btn2.style.color = '#ffffff';
                btn2.classList.add('active-tab-btn');
            }
            updateDockingButtons(2);

            // Sinkronkan Nama Barang & Nama Jenis ke Info Strip Tab 2
            const bSel = document.getElementById('kartonBarangSelect');
            const opt = bSel?.selectedOptions?.[0];
            const barangNm = opt ? (opt.getAttribute('data-nama') || opt.text.split(' • ')[0]) : '-';
            const jenisNm = document.getElementById('kartonNamaJenisInput')?.value || '-';
            const tab2Barang = document.getElementById('tab2InfoBarang');
            const tab2Jenis = document.getElementById('tab2InfoJenis');
            if (tab2Barang) tab2Barang.innerText = barangNm;
            if (tab2Jenis) tab2Jenis.innerText = jenisNm;
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
        triggerSaveDraftDebounced();
    };

    function updateDockingButtons(tab) {
        const btnBack = document.getElementById('dockBtnBack') || document.getElementById('dockBtnPrev');
        const btnNext = document.getElementById('dockBtnNext');
        const backText = document.getElementById('dockBackText');
        const nextText = document.getElementById('dockNextText') || document.getElementById('dockBtnNextText');
        const nextIcon = document.getElementById('dockNextIcon') || document.getElementById('dockBtnNextIcon');

        if (!btnNext) return;

        if (tab === 1) {
            if (btnBack) btnBack.style.display = 'none';
            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-primary-state';
            btnNext.style.background = '';
            btnNext.style.borderColor = '';
            if (nextText) nextText.innerText = 'Lanjut: Mutu Karton';
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function () { window.switchKartonTab(2); };
        } else {
            if (btnBack) {
                btnBack.style.display = 'inline-flex';
                if (backText) backText.innerText = 'Kembali';
                btnBack.onclick = function () { window.switchKartonTab(1); };
            }

            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-success-state';
            btnNext.style.background = '';
            btnNext.style.borderColor = '';
            if (nextText) nextText.innerText = '💾 Simpan QC Karton';
            if (nextIcon) nextIcon.style.display = 'none';
            btnNext.onclick = function () { window.submitKartonForm(); };
        }
    }

    // 2. Waktu Inspeksi Lapangan
    function updateInspectionTimeDisplay(datetimeStr) {
        const textBar = document.getElementById('textInspectionBarTime');
        if (!textBar) return;
        if (!datetimeStr) return;

        try {
            const dt = new Date(datetimeStr);
            if (!isNaN(dt.getTime())) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const day = String(dt.getDate()).padStart(2, '0');
                const month = months[dt.getMonth()];
                const year = dt.getFullYear();
                const hours = String(dt.getHours()).padStart(2, '0');
                const minutes = String(dt.getMinutes()).padStart(2, '0');
                textBar.innerText = `${day} ${month} ${year} • ${hours}:${minutes} WIB`;
            }
        } catch (e) {
            console.warn('[QC Karton] Gagal memformat waktu inspeksi:', e);
        }
    }

    window.setCurrentDateTime = function () {
        const input = document.getElementById('inputTglDatang');
        if (!input) return;

        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const val = `${year}-${month}-${day}T${hours}:${minutes}`;
        input.value = val;
        updateInspectionTimeDisplay(val);
        triggerSaveDraftDebounced();
    };

    // 3. Auto-suggest Nama Jenis berdasarkan Master Barang Karton
    window.onKartonBarangChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt) {
            const podtlId = opt.getAttribute('data-podtl-id');
            if (podtlId) {
                currentPodtlId = podtlId;
                const hiddenPodtl = document.getElementById('inputSelectedPodtlId');
                if (hiddenPodtl) hiddenPodtl.value = podtlId;

                // Sync kartu aktif
                document.querySelectorAll('.po-item-card').forEach(c => c.classList.remove('is-active'));
                const activeCard = document.getElementById(`poItemCard_${podtlId}`);
                if (activeCard) activeCard.classList.add('is-active');

                // Auto-fill quantity dari sisa PO
                const sisa = parseFloat(opt.getAttribute('data-sisa-qty') || 0);
                const pesan = parseFloat(opt.getAttribute('data-pesan-qty') || 0);
                const targetQty = sisa > 0 ? sisa : pesan;

                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const grossInput = document.getElementById('kartonQtyGross');

                if (inSJ && (!inSJ.value || inSJ.dataset.autoFilled === 'true')) {
                    inSJ.value = targetQty;
                    inSJ.dataset.autoFilled = 'true';
                }
                if (inPabrik && (!inPabrik.value || inPabrik.dataset.autoFilled === 'true')) {
                    inPabrik.value = targetQty;
                    inPabrik.dataset.autoFilled = 'true';
                }
                if (grossInput && (!grossInput.value || grossInput.dataset.autoFilled === 'true')) {
                    grossInput.value = targetQty;
                    grossInput.dataset.autoFilled = 'true';
                }
                window.syncKartonQuantity();
            }

            // Nama Jenis adalah spesifikasi/varian kemasan tersendiri yang berbeda dengan Nama Master Barang
            const input = document.getElementById('kartonNamaJenisInput');
            if (input && !input.value) {
                input.value = 'Karton Box Mirasa 250g (K150/M125)';
            }

            const sat = opt.getAttribute('data-satuan-cd') || opt.getAttribute('data-satuan');
            if (sat && (sat.toUpperCase() === 'DUS' || sat.toUpperCase() === 'PCS')) {
                window.setKartonSatuan(sat.toUpperCase());
            }
        }
        triggerSaveDraftDebounced();
    };

    // 4. Penggantian Satuan (PCS / DUS)
    window.setKartonSatuan = function (satuan) {
        currentKartonSatuan = satuan;
        const hiddenInput = document.getElementById('inputKartonSatuan');
        if (hiddenInput) hiddenInput.value = satuan;

        const pillPcs = document.getElementById('pillSatuan_PCS');
        const pillDus = document.getElementById('pillSatuan_DUS');

        if (satuan === 'DUS') {
            if (pillDus) pillDus.classList.add('active');
            if (pillPcs) pillPcs.classList.remove('active');
        } else {
            if (pillPcs) pillPcs.classList.add('active');
            if (pillDus) pillDus.classList.remove('active');
        }

        document.querySelectorAll('.label-satuan-karton, .suffix-satuan-karton').forEach(el => {
            el.innerText = satuan;
        });

        // Update label satuan pada kartu disposisi (Kuantitas Diterima / Ditolak)
        document.querySelectorAll('.unit-label-disposisi-karton').forEach(el => {
            el.innerText = satuan.toLowerCase();
        });

        window.syncKartonQuantity();
        triggerSaveDraftDebounced();
    };

    // 5. Sinkronisasi Kuantitas Real-Time (Mendukung Split Grade A & Grade B)
    window.syncKartonQuantity = function () {
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const inSJ = document.getElementById('inputJumlahSJ');
        const inGradeA = document.getElementById('kartonQtyGradeA');
        const inGradeB = document.getElementById('kartonQtyGradeB');
        const rejectInput = document.getElementById('kartonQtyReject');
        const labelNetto = document.getElementById('labelKartonNetto');
        const subcardNetto = document.getElementById('labelSubcardNetto');

        // Auto-fill Grade A jika kosong dan ada nilai di inSJ/inPabrik
        if (inGradeA && (!inGradeA.value || inGradeA.value == 0) && (!inGradeB || !inGradeB.value || inGradeB.value == 0)) {
            const defQty = parseFloat(inSJ?.value || inPabrik?.value || 0) || 0;
            const defReject = parseFloat(rejectInput?.value || 0) || 0;
            if (defQty > 0) {
                inGradeA.value = Math.max(0, defQty - defReject);
                inGradeA.dataset.autoFilled = 'true';
            }
        }

        const qtyA = parseFloat(inGradeA?.value || 0) || 0;
        const qtyB = parseFloat(inGradeB?.value || 0) || 0;
        const reject = parseFloat(rejectInput?.value || 0) || 0;
        const netto = Math.max(0, qtyA + qtyB);
        const gross = netto + reject;

        if (inPabrik && (!inPabrik.value || inPabrik.dataset.autoFilled === 'true' || parseFloat(inPabrik.value) === 0)) {
            inPabrik.value = gross;
            inPabrik.dataset.autoFilled = 'true';
        }

        const txt = `${netto.toLocaleString('id-ID')} ${currentKartonSatuan}`;
        if (labelNetto) labelNetto.innerText = txt;
        if (subcardNetto) subcardNetto.innerText = `Netto Lolos: ${txt}`;

        // Sinkronisasi Kuantitas Diterima & Ditolak pada Kartu Disposisi
        const kesimpulan = document.getElementById('inputKesimpulanQc')?.value || document.getElementById('inputKartonKesimpulan')?.value || 'TERIMA';
        const inputQtyTerima = document.getElementById('inputQtyTerima');
        const inputQtyTolak = document.getElementById('inputQtyTolak');

        if (kesimpulan === 'TOLAK') {
            if (inputQtyTerima) inputQtyTerima.value = 0;
            if (inputQtyTolak) inputQtyTolak.value = Math.round(gross);
        } else {
            if (inputQtyTerima) inputQtyTerima.value = Math.round(netto);
            if (inputQtyTolak) inputQtyTolak.value = Math.round(reject);
        }

        triggerSaveDraftDebounced();
    };

    window.onKartonQtySplitChanged = function (source) {
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const inSJ = document.getElementById('inputJumlahSJ');
        const inGradeA = document.getElementById('kartonQtyGradeA');
        const inGradeB = document.getElementById('kartonQtyGradeB');
        const rejectInput = document.getElementById('kartonQtyReject');

        const totalSJ = parseFloat(inSJ?.value || inPabrik?.value || 0) || 0;
        let reject = parseFloat(rejectInput?.value || 0) || 0;
        let qtyA = parseFloat(inGradeA?.value || 0) || 0;
        let qtyB = parseFloat(inGradeB?.value || 0) || 0;

        if (source === 'reject' || source === 'sj') {
            if (totalSJ > 0) {
                if (reject > totalSJ) {
                    reject = totalSJ;
                    if (rejectInput) rejectInput.value = reject;
                }
                const remaining = Math.max(0, totalSJ - reject);
                if (qtyB > remaining) {
                    qtyB = remaining;
                    if (inGradeB) inGradeB.value = qtyB;
                }
                qtyA = Math.max(0, remaining - qtyB);
                if (inGradeA) inGradeA.value = qtyA;
            }
        } else if (source === 'grade_b') {
            if (totalSJ > 0) {
                const remaining = Math.max(0, totalSJ - reject);
                if (qtyB > remaining) {
                    qtyB = remaining;
                    if (inGradeB) inGradeB.value = qtyB;
                }
                qtyA = Math.max(0, remaining - qtyB);
                if (inGradeA) inGradeA.value = qtyA;
            }
        } else if (source === 'grade_a') {
            if (inGradeA) inGradeA.dataset.manuallyEdited = 'true';
        }

        window.syncKartonQuantity();
    };

    // 6. Transportasi & Halal Toggles
    window.updateTransportCard = function (radio) {
        const cleanCard = document.getElementById('cardTransportClean');
        const dirtyCard = document.getElementById('cardTransportDirty');

        if (radio && radio.value === '1') {
            cleanCard?.classList.add('active');
            dirtyCard?.classList.remove('active', 'is-danger');
        } else {
            cleanCard?.classList.remove('active');
            dirtyCard?.classList.add('active', 'is-danger');
        }
        triggerSaveDraftDebounced();
    };

    window.setHalalToggle = function (field, value) {
        const hidden = document.getElementById('input_' + field);
        if (hidden) hidden.value = value;

        if (field === 'angkut_barang_haram') {
            const btnNo = document.getElementById('btn_angkut_haram_no');
            const btnYes = document.getElementById('btn_angkut_haram_yes');
            if (value === 0) {
                btnNo?.classList.add('active', 'ok');
                btnYes?.classList.remove('active', 'danger');
            } else {
                btnNo?.classList.remove('active', 'ok');
                btnYes?.classList.add('active', 'danger');
            }
        } else if (field === 'terdaftar_lppom') {
            const btnYes = document.getElementById('btn_lppom_yes');
            const btnNo = document.getElementById('btn_lppom_no');
            if (value === 1) {
                btnYes?.classList.add('active', 'ok');
                btnNo?.classList.remove('active', 'danger');
            } else {
                btnYes?.classList.remove('active', 'ok');
                btnNo?.classList.add('active', 'danger');
            }
        } else if (field === 'ada_sertifikat_halal') {
            const btnYes = document.getElementById('btn_sertifikat_yes');
            const btnNo = document.getElementById('btn_sertifikat_no');
            if (value === 1) {
                btnYes?.classList.add('active', 'ok');
                btnNo?.classList.remove('active', 'danger');
            } else {
                btnYes?.classList.remove('active', 'ok');
                btnNo?.classList.add('active', 'danger');
            }
        } else if (field === 'sertifikat_halal_berlaku') {
            const btnYes = document.getElementById('btn_berlaku_yes');
            const btnNo = document.getElementById('btn_berlaku_no');
            if (value === 1) {
                btnYes?.classList.add('active', 'ok');
                btnNo?.classList.remove('active', 'danger');
            } else {
                btnYes?.classList.remove('active', 'ok');
                btnNo?.classList.add('active', 'danger');
            }
        }
        triggerSaveDraftDebounced();
    };

    // 7. Modern Choice Buttons (OK vs TDK STD)
    window.setRawMaterialStatus = function (val) {
        const inp = document.getElementById('inputRawMaterial');
        if (inp) inp.value = val;

        const btnOk = document.getElementById('btnRawOk');
        const btnTdk = document.getElementById('btnRawTdkStd');

        if (val === 'OK') {
            btnOk?.classList.add('active', 'ok');
            btnTdk?.classList.remove('active', 'danger');
        } else {
            btnOk?.classList.remove('active', 'ok');
            btnTdk?.classList.add('active', 'danger');
        }
        triggerSaveDraftDebounced();
    };

    window.setKemasanKondisi = function (val) {
        const inp = document.getElementById('inputKemasanKondisi');
        if (inp) inp.value = val;

        const btnOk = document.getElementById('btnKemasanOk');
        const btnTdk = document.getElementById('btnKemasanTdkStd');

        if (val === 'OK') {
            btnOk?.classList.add('active', 'ok');
            btnTdk?.classList.remove('active', 'danger');
        } else {
            btnOk?.classList.remove('active', 'ok');
            btnTdk?.classList.add('active', 'danger');
        }
        triggerSaveDraftDebounced();
    };

    // 8. Modern Checklist Cards (Cacat Kemasan)
    window.toggleModernCheck = function (checkboxId) {
        const chk = document.getElementById(checkboxId);
        if (!chk) return;
        chk.checked = !chk.checked;
        window.onModernCheckboxChanged(chk);
    };

    window.onModernCheckboxChanged = function (chk) {
        if (!chk) return;
        const card = chk.closest('.modern-check-card');
        if (card) {
            if (chk.checked) card.classList.add('is-checked');
            else card.classList.remove('is-checked');
        }
        triggerSaveDraftDebounced();
    };

    // 9. Quick Parameter Value Chip
    window.setParamValue = function (targetInputId, val) {
        const inp = document.getElementById(targetInputId);
        if (inp) {
            inp.value = val;
            inp.focus();
        }
        triggerSaveDraftDebounced();
    };

    // 10. Kesimpulan QC Cards (TERIMA / TOLAK)
    window.setKesimpulanKarton = function (val) {
        const inpKarton = document.getElementById('inputKartonKesimpulan');
        const inpQc = document.getElementById('inputKesimpulanQc');
        if (inpKarton) inpKarton.value = val;
        if (inpQc) inpQc.value = val;

        const cardTerima = document.getElementById('cardDisposisiTerima') || document.getElementById('cardKesimpulanTerima');
        const cardTolak = document.getElementById('cardDisposisiTolak') || document.getElementById('cardKesimpulanTolak');

        if (val === 'TERIMA') {
            cardTerima?.classList.add('active', 'terima');
            cardTolak?.classList.remove('active', 'tolak');
        } else {
            cardTerima?.classList.remove('active', 'terima');
            cardTolak?.classList.add('active', 'tolak');
        }

        window.syncKartonQuantity();
        triggerSaveDraftDebounced();
    };

    // 11. PO Multi-Item Selector & Selection Auto-fill
    function renderPoItemsCards(items, selectedPodtlId) {
        const box = document.getElementById('boxPoItemsSelector');
        const list = document.getElementById('poItemsCardsList');
        const badgeCount = document.getElementById('countPoItemsBadge');
        const bSel = document.getElementById('kartonBarangSelect');

        if (!items || items.length === 0) {
            if (box) box.style.display = 'none';
            if (bSel && originalBarangOptionsHTML) {
                bSel.innerHTML = originalBarangOptionsHTML;
            }
            currentPodtlId = null;
            const hiddenPodtl = document.getElementById('inputSelectedPodtlId');
            if (hiddenPodtl) hiddenPodtl.value = '';
            return;
        }

        if (box) box.style.display = 'block';
        if (badgeCount) badgeCount.innerText = items.length;

        // Tentukan podtl_id aktif (Prioritaskan item yang belum selesai diuji)
        let activeId = selectedPodtlId;
        if (!activeId || !items.some(it => String(it.podtl_id) === String(activeId))) {
            const unTested = items.find(it => !it.is_selesai_qc && (typeof it.sisa_belum_qc === 'undefined' || it.sisa_belum_qc > 0));
            activeId = unTested ? unTested.podtl_id : items[0].podtl_id;
        }
        currentPodtlId = activeId;
        const hiddenPodtl = document.getElementById('inputSelectedPodtlId');
        if (hiddenPodtl) hiddenPodtl.value = activeId;

        // Filter dropdown kartonBarangSelect agar berisi item-item PO ini
        if (bSel) {
            bSel.innerHTML = '';
            items.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it.barang_id;
                const sisa = (typeof it.sisa_belum_qc !== 'undefined') ? it.sisa_belum_qc : (it.sisa_qty > 0 ? it.sisa_qty : it.pesan_qty);
                const statusTag = it.is_selesai_qc ? '[SELESAI DIUJI] ' : (it.qc_tested_qty > 0 ? '[UJI SEBAGIAN] ' : '');
                opt.text = `${statusTag}${it.barang_nm} • Sisa Belum QC: ${sisa.toLocaleString('id-ID')} ${it.satuan_cd}`;
                opt.setAttribute('data-nama', it.barang_nm);
                opt.setAttribute('data-satuan', it.satuan);
                opt.setAttribute('data-satuan-cd', it.satuan_cd);
                opt.setAttribute('data-podtl-id', it.podtl_id);
                opt.setAttribute('data-sisa-qty', sisa);
                opt.setAttribute('data-pesan-qty', it.pesan_qty);
                if (String(it.podtl_id) === String(activeId)) {
                    opt.selected = true;
                }
                bSel.appendChild(opt);
            });
        }

        // Render Kartu Interaktif
        if (list) {
            list.innerHTML = '';
            items.forEach((it) => {
                const isActive = (String(it.podtl_id) === String(activeId));
                const sisa = (typeof it.sisa_belum_qc !== 'undefined') ? it.sisa_belum_qc : (it.sisa_qty > 0 ? it.sisa_qty : it.pesan_qty);
                const isCompleted = !!it.is_selesai_qc || sisa <= 0;
                const isPartial = !isCompleted && (it.qc_tested_qty > 0);

                const card = document.createElement('div');
                card.className = `po-item-card ${isActive ? 'is-active' : ''} ${isCompleted ? 'is-completed' : (isPartial ? 'is-partial' : 'is-pending')}`;
                card.id = `poItemCard_${it.podtl_id}`;
                card.onclick = function () {
                    window.selectPoItem(it.podtl_id);
                };

                let radioCircleHtml = '<span class="po-item-radio-circle"></span>';
                let badgeHtml = '';
                let subHtml = `Pesan: ${it.pesan_qty.toLocaleString('id-ID')} ${it.satuan_cd}`;

                if (isCompleted) {
                    radioCircleHtml = '<span class="po-item-check-icon" style="color: #16a34a; font-weight: 900; font-size: 1.1rem; width: 20px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">✓</span>';
                    badgeHtml = `
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                            <span class="po-item-badge-sisa badge-completed" style="background: #dcfce7; border-color: #86efac; color: #166534; font-weight: 800;">
                                ✅ Selesai Diuji
                            </span>
                            <span style="font-size: 0.68rem; color: #16a34a; font-weight: 700;">Diuji: ${it.qc_tested_qty.toLocaleString('id-ID')} ${it.satuan_cd}</span>
                        </div>
                    `;
                    subHtml += ` • <strong style="color: #16a34a;">Sudah Diuji: ${it.qc_tested_qty.toLocaleString('id-ID')} ${it.satuan_cd}</strong>`;
                } else if (isPartial) {
                    badgeHtml = `
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                            <span class="po-item-badge-sisa badge-partial" style="background: #fef3c7; border-color: #fde68a; color: #b45309; font-weight: 800;">
                                ⚠️ Uji Sebagian
                            </span>
                            <span style="font-size: 0.68rem; color: #b45309; font-weight: 700;">Sisa Belum QC: ${sisa.toLocaleString('id-ID')}</span>
                        </div>
                    `;
                    subHtml += ` • <span style="color: #b45309; font-weight: 700;">Sudah Diuji: ${it.qc_tested_qty.toLocaleString('id-ID')}</span>`;
                } else {
                    badgeHtml = `
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                            <span class="po-item-badge-sisa badge-pending" style="background: #e0f2fe; border-color: #bae6fd; color: #0284c7; font-weight: 800;">
                                ⏳ Belum Diuji
                            </span>
                            <span style="font-size: 0.68rem; color: #0284c7; font-weight: 700;">Sisa: ${sisa.toLocaleString('id-ID')} ${it.satuan_cd}</span>
                        </div>
                    `;
                    subHtml += ` • <span style="color: #64748b;">Belum Ada Uji</span>`;
                }

                card.innerHTML = `
                    <div class="po-item-card-left">
                        ${radioCircleHtml}
                        <div class="po-item-info">
                            <span class="po-item-nama">${it.barang_nm}</span>
                            <span class="po-item-sub">${subHtml}</span>
                        </div>
                    </div>
                    ${badgeHtml}
                `;
                list.appendChild(card);
            });
        }
    }

    window.selectPoItem = function (podtlId) {
        currentPodtlId = podtlId;
        const hiddenPodtl = document.getElementById('inputSelectedPodtlId');
        if (hiddenPodtl) hiddenPodtl.value = podtlId;

        const poId = document.getElementById('poSelect')?.value;
        const poData = window.appConfig?.poListData?.[poId];
        if (!poData || !poData.items) return;

        const targetItem = poData.items.find(it => String(it.podtl_id) === String(podtlId));
        if (!targetItem) return;

        // Update class is-active pada kartu
        document.querySelectorAll('.po-item-card').forEach(c => c.classList.remove('is-active'));
        const activeCard = document.getElementById(`poItemCard_${podtlId}`);
        if (activeCard) activeCard.classList.add('is-active');

        // Update select barang
        const bSel = document.getElementById('kartonBarangSelect');
        if (bSel) {
            bSel.value = targetItem.barang_id;
            window.onKartonBarangChanged(bSel);
        }

        // Update satuan
        if (targetItem.satuan_cd) {
            window.setKartonSatuan(targetItem.satuan_cd === 'DUS' ? 'DUS' : 'PCS');
        }

        // Update kuantitas
        const sisaQty = targetItem.sisa_qty > 0 ? targetItem.sisa_qty : targetItem.pesan_qty;
        const inSJ = document.getElementById('inputJumlahSJ');
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const inGradeA = document.getElementById('kartonQtyGradeA');
        const inGradeB = document.getElementById('kartonQtyGradeB');

        if (inSJ) {
            inSJ.value = sisaQty;
            inSJ.dataset.autoFilled = 'true';
        }
        if (inPabrik) {
            inPabrik.value = sisaQty;
            inPabrik.dataset.autoFilled = 'true';
        }
        if (inGradeA) {
            inGradeA.value = sisaQty;
            inGradeA.dataset.autoFilled = 'true';
        }
        if (inGradeB) {
            inGradeB.value = 0;
        }
        const inReject = document.getElementById('kartonQtyReject');
        if (inReject) {
            inReject.value = 0;
        }

        // Pertahankan Nama Jenis spesifikasi box (jangan ditimpa nama master barang)
        const inputNamaJenis = document.getElementById('kartonNamaJenisInput');
        if (inputNamaJenis && !inputNamaJenis.value) {
            inputNamaJenis.value = 'Karton Box Mirasa 250g (K150/M125)';
        }

        window.syncKartonQuantity();
        triggerSaveDraftDebounced();
    };

    // =========================================================================
    // KUNCI FIELD DARI PO (INTEGRITAS DATA PO - SUPPLIER, GUDANG & MASTER BARANG)
    // =========================================================================
    window.toggleKartonPoLockFields = function (isLocked, po) {
        const sSelect = document.getElementById('supplierSelect');
        const gSelect = document.getElementById('gudangSelect');
        const bSelect = document.getElementById('kartonBarangSelect');
        const produsenInput = document.getElementById('namaProdusenInput');

        const badgeSupplier = document.getElementById('badgeLockSupplier');
        const badgeGudang = document.getElementById('badgeLockGudang');
        const badgeBarang = document.getElementById('badgeLockBarang');

        if (isLocked) {
            // Lock Supplier
            if (sSelect) {
                sSelect.style.pointerEvents = 'none';
                sSelect.style.backgroundColor = '#f1f5f9';
                sSelect.style.borderColor = '#cbd5e1';
                sSelect.setAttribute('tabindex', '-1');
            }
            if (badgeSupplier) {
                badgeSupplier.style.display = 'inline-flex';
                if (po?.supplier_nm) badgeSupplier.innerText = `🔒 Terkunci (${po.supplier_nm})`;
            }

            // Lock Gudang
            if (gSelect) {
                gSelect.style.pointerEvents = 'none';
                gSelect.style.backgroundColor = '#f1f5f9';
                gSelect.style.borderColor = '#cbd5e1';
                gSelect.setAttribute('tabindex', '-1');
            }
            if (badgeGudang) badgeGudang.style.display = 'inline-flex';

            // Lock Master Barang Karton Box
            if (bSelect) {
                bSelect.style.pointerEvents = 'none';
                bSelect.style.backgroundColor = '#f1f5f9';
                bSelect.style.borderColor = '#cbd5e1';
                bSelect.setAttribute('tabindex', '-1');
            }
            if (badgeBarang) badgeBarang.style.display = 'inline-flex';

            if (produsenInput && po?.supplier_nm) {
                produsenInput.value = po.supplier_nm;
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
            if (badgeSupplier) badgeSupplier.style.display = 'none';

            // Unlock Gudang
            if (gSelect) {
                gSelect.style.pointerEvents = 'auto';
                gSelect.style.backgroundColor = '#ffffff';
                gSelect.style.borderColor = '#e2e8f0';
                gSelect.removeAttribute('tabindex');
            }
            if (badgeGudang) badgeGudang.style.display = 'none';

            // Unlock Master Barang Karton Box
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

    window.onPoSelected = function (select) {
        if (!select || !select.value) {
            renderPoItemsCards([], null);
            window.toggleKartonPoLockFields(false);
            return;
        }
        const poId = select.value;
        const poData = window.appConfig?.poListData?.[poId];
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        const supplierId = poData?.supplier_id || opt?.getAttribute('data-supplier-id');
        const supplierNm = poData?.supplier_nm || opt?.getAttribute('data-supplier-nm') || '';
        const gudangId = poData?.gudang_id || opt?.getAttribute('data-gudang-id');

        if (supplierId) {
            const sSel = document.getElementById('supplierSelect');
            if (sSel) {
                let targetOpt = sSel.querySelector(`option[value="${supplierId}"]`);
                if (!targetOpt && supplierNm) {
                    targetOpt = document.createElement('option');
                    targetOpt.value = supplierId;
                    targetOpt.text = `${supplierNm}`;
                    targetOpt.setAttribute('data-supplier-nm', supplierNm);
                    sSel.appendChild(targetOpt);
                }
                sSel.value = supplierId;
                window.onSupplierSelected(sSel);
            }
        }
        if (gudangId) {
            const gSel = document.getElementById('gudangSelect');
            if (gSel) gSel.value = gudangId;
        }

        // Auto-fill nama produsen dari nama supplier jika belum diisi manual
        const namaProd = document.getElementById('namaProdusenInput');
        if (namaProd && supplierNm && (!namaProd.value || namaProd.dataset.autoFilled === 'true')) {
            namaProd.value = supplierNm;
            namaProd.dataset.autoFilled = 'true';
        }

        if (poData && poData.items && poData.items.length > 0) {
            renderPoItemsCards(poData.items, poData.items[0].podtl_id);
            window.selectPoItem(poData.items[0].podtl_id);
        } else {
            renderPoItemsCards([], null);
        }

        // Kunci field dari PO
        window.toggleKartonPoLockFields(true, poData || { supplier_nm: supplierNm });

        triggerSaveDraftDebounced();
    };

    window.onSupplierSelected = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt && opt.value) {
            const namaProd = document.getElementById('namaProdusenInput');
            if (namaProd && (!namaProd.value || namaProd.dataset.autoFilled === 'true')) {
                const sNm = opt.getAttribute('data-supplier-nm') || opt.text.split(' (')[0] || opt.text;
                namaProd.value = sNm.trim();
                namaProd.dataset.autoFilled = 'true';
            }
        }
        triggerSaveDraftDebounced();
    };

    // 12. Autosave & Restore Draft (Pencegahan Kehilangan Data Saat Reload / Refresh)
    function saveKartonDraft() {
        if (isSubmitting) return;
        try {
            const form = document.getElementById('qcKartonForm');
            if (!form) return;

            const draft = {
                po_id: document.getElementById('poSelect')?.value || '',
                podtl_id: currentPodtlId || document.getElementById('inputSelectedPodtlId')?.value || '',
                gudang_id: document.getElementById('gudangSelect')?.value || '',
                supplier_id: document.getElementById('supplierSelect')?.value || '',
                surat_jalan_supplier: document.querySelector('input[name="surat_jalan_supplier"]')?.value || '',
                nomor_do: document.querySelector('input[name="nomor_do"]')?.value || '',
                nama_produsen: document.getElementById('namaProdusenInput')?.value || '',
                plat_nomor_truk: document.querySelector('input[name="plat_nomor_truk"]')?.value || '',
                sopir_nama: document.querySelector('input[name="sopir_nama"]')?.value || '',
                tgl_periksa: document.getElementById('inputTglDatang')?.value || '',
                karton_barang_id: document.getElementById('kartonBarangSelect')?.value || '',
                jumlah_surat_jalan: document.getElementById('inputJumlahSJ')?.value || '',
                jumlah_di_pabrik: document.getElementById('inputJumlahPabrik')?.value || '',
                jumlah_sample_pcs: document.getElementById('inputJumlahSamplePcs')?.value || '',
                nama_jenis: document.getElementById('kartonNamaJenisInput')?.value || '',

                bebas_cemaran_st: document.querySelector('input[name="bebas_cemaran_st"]:checked')?.value || '1',
                angkut_barang_haram_st: document.getElementById('input_angkut_barang_haram')?.value ?? '0',
                komentar_transportasi: document.querySelector('input[name="komentar_transportasi"]')?.value || '',
                terdaftar_lppom_st: document.getElementById('input_terdaftar_lppom')?.value ?? '1',
                komentar_lppom: document.querySelector('input[name="komentar_lppom"]')?.value || '',
                ada_sertifikat_halal_st: document.getElementById('input_ada_sertifikat_halal')?.value ?? '1',
                komentar_sertifikat: document.querySelector('input[name="komentar_sertifikat"]')?.value || '',
                sertifikat_halal_berlaku_st: document.getElementById('input_sertifikat_halal_berlaku')?.value ?? '1',
                komentar_berlaku: document.querySelector('input[name="komentar_berlaku"]')?.value || '',

                karton_status_raw_material: document.getElementById('inputRawMaterial')?.value || 'OK',
                karton_kemasan_kondisi: document.getElementById('inputKemasanKondisi')?.value || 'OK',
                karton_kemasan_kotor: document.getElementById('checkCacatKotor')?.checked ? 1 : 0,
                karton_kemasan_apek: document.getElementById('checkCacatApek')?.checked ? 1 : 0,
                karton_kemasan_basah: document.getElementById('checkCacatBasah')?.checked ? 1 : 0,
                karton_kemasan_jamur: document.getElementById('checkCacatJamur')?.checked ? 1 : 0,
                karton_kemasan_sobek: document.getElementById('checkCacatSobek')?.checked ? 1 : 0,
                karton_kemasan_berminyak: document.getElementById('checkCacatBerminyak')?.checked ? 1 : 0,
                karton_kemasan_berdebu: document.getElementById('checkCacatBerdebu')?.checked ? 1 : 0,

                karton_panjang_analisa: document.getElementById('inputPanjangAnalisa')?.value || '',
                karton_panjang_standar: document.getElementById('inputPanjangStandar')?.value || '',
                karton_lebar_analisa: document.getElementById('inputLebarAnalisa')?.value || '',
                karton_lebar_standar: document.getElementById('inputLebarStandar')?.value || '',
                karton_tinggi_analisa: document.getElementById('inputTinggiAnalisa')?.value || '',
                karton_tinggi_standar: document.getElementById('inputTinggiStandar')?.value || '',
                karton_spesifikasi_analisa: document.getElementById('inputSpesifikasiAnalisa')?.value || '',
                karton_spesifikasi_standar: document.getElementById('inputSpesifikasiStandar')?.value || '',

                karton_qty_grade_a: document.getElementById('kartonQtyGradeA')?.value || '',
                karton_qty_grade_b: document.getElementById('kartonQtyGradeB')?.value || '',
                karton_qty_reject: document.getElementById('kartonQtyReject')?.value || '0',
                karton_satuan: currentKartonSatuan,
                karton_komentar: document.getElementById('inputKartonKomentar')?.value || '',
                karton_kesimpulan: document.getElementById('inputKesimpulanQc')?.value || 'TERIMA',
                petugas_qc_nama: document.getElementById('inputPetugasQc')?.value || '',
                qc_supervisor_nama: document.getElementById('inputSupervisorQc')?.value || '',

                active_tab: currentTab
            };

            sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
        } catch (e) {
            console.warn('[QC Karton] Gagal menyimpan draft formulir:', e);
        }
    }

    function restoreKartonDraft() {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (!raw) return false;
            const draft = JSON.parse(raw);
            if (!draft || typeof draft !== 'object') return false;

            // 1. Text & Select fields
            if (draft.po_id) {
                const el = document.getElementById('poSelect');
                if (el) {
                    el.value = draft.po_id;
                    const poData = window.appConfig?.poListData?.[draft.po_id];
                    if (poData && poData.items && poData.items.length > 0) {
                        renderPoItemsCards(poData.items, draft.podtl_id || poData.items[0].podtl_id);
                    }
                }
            }
            if (draft.podtl_id) {
                currentPodtlId = draft.podtl_id;
                const hiddenPodtl = document.getElementById('inputSelectedPodtlId');
                if (hiddenPodtl) hiddenPodtl.value = draft.podtl_id;
            }
            if (draft.gudang_id) {
                const el = document.getElementById('gudangSelect');
                if (el) el.value = draft.gudang_id;
            }
            if (draft.supplier_id) {
                const el = document.getElementById('supplierSelect');
                if (el) el.value = draft.supplier_id;
            }
            if (draft.surat_jalan_supplier !== undefined) {
                const el = document.querySelector('input[name="surat_jalan_supplier"]');
                if (el) el.value = draft.surat_jalan_supplier;
            }
            if (draft.nomor_do !== undefined) {
                const el = document.querySelector('input[name="nomor_do"]');
                if (el) el.value = draft.nomor_do;
            }
            if (draft.nama_produsen !== undefined) {
                const el = document.getElementById('namaProdusenInput');
                if (el) el.value = draft.nama_produsen;
            }
            if (draft.plat_nomor_truk !== undefined) {
                const el = document.querySelector('input[name="plat_nomor_truk"]');
                if (el) el.value = draft.plat_nomor_truk;
            }
            if (draft.sopir_nama !== undefined) {
                const el = document.querySelector('input[name="sopir_nama"]');
                if (el) el.value = draft.sopir_nama;
            }
            if (draft.tgl_periksa) {
                const el = document.getElementById('inputTglDatang');
                if (el) {
                    el.value = draft.tgl_periksa;
                    updateInspectionTimeDisplay(draft.tgl_periksa);
                }
            }
            if (draft.karton_barang_id) {
                const el = document.getElementById('kartonBarangSelect');
                if (el) el.value = draft.karton_barang_id;
            }
            if (draft.jumlah_surat_jalan !== undefined) {
                const el = document.getElementById('inputJumlahSJ');
                if (el) el.value = draft.jumlah_surat_jalan;
            }
            if (draft.jumlah_di_pabrik !== undefined) {
                const el = document.getElementById('inputJumlahPabrik');
                if (el) el.value = draft.jumlah_di_pabrik;
            }
            if (draft.jumlah_sample_pcs !== undefined) {
                const el = document.getElementById('inputJumlahSamplePcs');
                if (el) el.value = draft.jumlah_sample_pcs;
            }
            if (draft.nama_jenis !== undefined) {
                const el = document.getElementById('kartonNamaJenisInput');
                if (el) el.value = draft.nama_jenis;
            }

            // 2. Transport Radio
            if (draft.bebas_cemaran_st !== undefined) {
                const radio = document.querySelector(`input[name="bebas_cemaran_st"][value="${draft.bebas_cemaran_st}"]`);
                if (radio) {
                    radio.checked = true;
                    window.updateTransportCard(radio);
                }
            }

            // 3. Halal Questions
            if (draft.angkut_barang_haram_st !== undefined) {
                window.setHalalToggle('angkut_barang_haram', parseInt(draft.angkut_barang_haram_st, 10));
            }
            if (draft.komentar_transportasi !== undefined) {
                const el = document.querySelector('input[name="komentar_transportasi"]');
                if (el) el.value = draft.komentar_transportasi;
            }
            if (draft.terdaftar_lppom_st !== undefined) {
                window.setHalalToggle('terdaftar_lppom', parseInt(draft.terdaftar_lppom_st, 10));
            }
            if (draft.komentar_lppom !== undefined) {
                const el = document.querySelector('input[name="komentar_lppom"]');
                if (el) el.value = draft.komentar_lppom;
            }
            if (draft.ada_sertifikat_halal_st !== undefined) {
                window.setHalalToggle('ada_sertifikat_halal', parseInt(draft.ada_sertifikat_halal_st, 10));
            }
            if (draft.komentar_sertifikat !== undefined) {
                const el = document.querySelector('input[name="komentar_sertifikat"]');
                if (el) el.value = draft.komentar_sertifikat;
            }
            if (draft.sertifikat_halal_berlaku_st !== undefined) {
                window.setHalalToggle('sertifikat_halal_berlaku', parseInt(draft.sertifikat_halal_berlaku_st, 10));
            }
            if (draft.komentar_berlaku !== undefined) {
                const el = document.querySelector('input[name="komentar_berlaku"]');
                if (el) el.value = draft.komentar_berlaku;
            }

            // 4. Tab 2 Fields
            if (draft.karton_status_raw_material) {
                window.setRawMaterialStatus(draft.karton_status_raw_material);
            }
            if (draft.karton_kemasan_kondisi) {
                window.setKemasanKondisi(draft.karton_kemasan_kondisi);
            }

            ['checkCacatKotor', 'checkCacatApek', 'checkCacatBasah', 'checkCacatJamur', 'checkCacatSobek', 'checkCacatBerminyak', 'checkCacatBerdebu'].forEach(id => {
                const key = id.replace('checkCacat', 'karton_kemasan_').toLowerCase();
                const chk = document.getElementById(id);
                if (chk && draft[key] !== undefined) {
                    chk.checked = !!draft[key];
                    window.onModernCheckboxChanged(chk);
                }
            });

            if (draft.karton_panjang_analisa !== undefined) {
                const el = document.getElementById('inputPanjangAnalisa');
                if (el) el.value = draft.karton_panjang_analisa;
            }
            if (draft.karton_panjang_standar !== undefined) {
                const el = document.getElementById('inputPanjangStandar');
                if (el) el.value = draft.karton_panjang_standar;
            }
            if (draft.karton_lebar_analisa !== undefined) {
                const el = document.getElementById('inputLebarAnalisa');
                if (el) el.value = draft.karton_lebar_analisa;
            }
            if (draft.karton_lebar_standar !== undefined) {
                const el = document.getElementById('inputLebarStandar');
                if (el) el.value = draft.karton_lebar_standar;
            }
            if (draft.karton_tinggi_analisa !== undefined) {
                const el = document.getElementById('inputTinggiAnalisa');
                if (el) el.value = draft.karton_tinggi_analisa;
            }
            if (draft.karton_tinggi_standar !== undefined) {
                const el = document.getElementById('inputTinggiStandar');
                if (el) el.value = draft.karton_tinggi_standar;
            }
            if (draft.karton_spesifikasi_analisa !== undefined) {
                const el = document.getElementById('inputSpesifikasiAnalisa');
                if (el) el.value = draft.karton_spesifikasi_analisa;
            }
            if (draft.karton_spesifikasi_standar !== undefined) {
                const el = document.getElementById('inputSpesifikasiStandar');
                if (el) el.value = draft.karton_spesifikasi_standar;
            }

            if (draft.karton_qty_grade_a !== undefined) {
                const el = document.getElementById('kartonQtyGradeA');
                if (el) el.value = draft.karton_qty_grade_a;
            }
            if (draft.karton_qty_grade_b !== undefined) {
                const el = document.getElementById('kartonQtyGradeB');
                if (el) el.value = draft.karton_qty_grade_b;
            }
            if (draft.karton_qty_reject !== undefined) {
                const el = document.getElementById('kartonQtyReject');
                if (el) el.value = draft.karton_qty_reject;
            }
            if (draft.karton_komentar !== undefined) {
                const el = document.getElementById('inputKartonKomentar');
                if (el) el.value = draft.karton_komentar;
            }
            if (draft.karton_kesimpulan) {
                window.setKesimpulanKarton(draft.karton_kesimpulan);
            }
            if (draft.karton_satuan) {
                window.setKartonSatuan(draft.karton_satuan);
            }
            if (draft.petugas_qc_nama !== undefined && draft.petugas_qc_nama !== '') {
                const el = document.getElementById('inputPetugasQc');
                if (el) el.value = draft.petugas_qc_nama;
            }
            if (draft.qc_supervisor_nama !== undefined && draft.qc_supervisor_nama !== '') {
                const el = document.getElementById('inputSupervisorQc');
                if (el) el.value = draft.qc_supervisor_nama;
            }

            window.syncKartonQuantity();
            return true;
        } catch (e) {
            console.warn('[QC Karton] Gagal memulihkan draft form:', e);
            return false;
        }
    }

    function clearKartonDraft() {
        try {
            sessionStorage.removeItem(DRAFT_KEY);
            sessionStorage.removeItem(ACTIVE_TAB_KEY);
        } catch (e) {}
    }

    // 13. Submit Payload Form Khusus Karton Box
    function injectHiddenInput(name, value) {
        let el = document.querySelector(`input[name="${name}"]`);
        if (el) {
            el.value = value;
            return;
        }
        el = document.createElement('input');
        el.type = 'hidden';
        el.name = name;
        el.value = value;
        document.getElementById('qcKartonForm').appendChild(el);
    }

    function showSubmitLoading(title) {
        isSubmitting = true;
        const overlay = document.getElementById('qcSubmitOverlay');
        const overlayTitle = document.getElementById('overlayTitle');
        if (overlay) {
            if (overlayTitle && title) overlayTitle.innerText = title;
            overlay.style.display = 'flex';
        }
    }

    window.submitKartonForm = function () {
        if (isSubmitting) return;

        const supplierSelect = document.getElementById('supplierSelect');
        if (!supplierSelect || !supplierSelect.value) {
            alert('Silakan pilih Mitra Supplier Karton terlebih dahulu!');
            window.switchKartonTab(1);
            supplierSelect?.focus();
            return;
        }

        const gudangSelect = document.getElementById('gudangSelect');
        if (!gudangSelect || !gudangSelect.value) {
            alert('Silakan pilih Perusahaan / Gudang Bongkar!');
            window.switchKartonTab(1);
            gudangSelect?.focus();
            return;
        }

        const inputJumlahSJ = document.getElementById('inputJumlahSJ');
        const sjVal = parseFloat(inputJumlahSJ?.value || 0);
        if (isNaN(sjVal) || sjVal <= 0) {
            alert('Jumlah kuantitas pada Surat Jalan wajib diisi dan lebih dari 0!');
            window.switchKartonTab(1);
            inputJumlahSJ?.focus();
            return;
        }

        const kartonSelect = document.getElementById('kartonBarangSelect');
        if (!kartonSelect || !kartonSelect.value) {
            alert('Pilih item komoditas karton box yang diuji!');
            window.switchKartonTab(2);
            kartonSelect?.focus();
            return;
        }

        const bId = kartonSelect.value;
        const bOpt = kartonSelect.selectedOptions ? kartonSelect.selectedOptions[0] : kartonSelect.options[kartonSelect.selectedIndex];
        const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Karton Box';
        const curNamaJenis = document.getElementById('kartonNamaJenisInput')?.value?.trim() || bNm;

        const qtyA = parseFloat(document.getElementById('kartonQtyGradeA')?.value || 0) || 0;
        const qtyB = parseFloat(document.getElementById('kartonQtyGradeB')?.value || 0) || 0;
        const reject = parseFloat(document.getElementById('kartonQtyReject')?.value || 0) || 0;
        const totalNetto = qtyA + qtyB;
        const totalGross = totalNetto + reject;

        if (totalGross <= 0) {
            alert('Kuantitas karton (Grade A, Grade B, atau Reject) wajib diisi lebih dari 0!');
            window.switchKartonTab(2);
            document.getElementById('kartonQtyGradeA')?.focus();
            return;
        }

        const kesimpulan = document.getElementById('inputKesimpulanQc')?.value || 'TERIMA';
        const komentarText = document.getElementById('inputKartonKomentar')?.value || '';

        function injectCommonParams(idx) {
            injectHiddenInput(`items[${idx}][status_raw_material]`, document.getElementById('inputRawMaterial')?.value || 'OK');
            injectHiddenInput(`items[${idx}][kemasan_kondisi]`, document.getElementById('inputKemasanKondisi')?.value || 'OK');
            injectHiddenInput(`items[${idx}][kemasan_kotor]`, document.getElementById('checkCacatKotor')?.checked ? 1 : 0);
            injectHiddenInput(`items[${idx}][kemasan_apek]`, document.getElementById('checkCacatApek')?.checked ? 1 : 0);
            injectHiddenInput(`items[${idx}][kemasan_basah]`, document.getElementById('checkCacatBasah')?.checked ? 1 : 0);
            injectHiddenInput(`items[${idx}][kemasan_jamur]`, document.getElementById('checkCacatJamur')?.checked ? 1 : 0);
            injectHiddenInput(`items[${idx}][kemasan_sobek]`, document.getElementById('checkCacatSobek')?.checked ? 1 : 0);
            injectHiddenInput(`items[${idx}][kemasan_berminyak]`, document.getElementById('checkCacatBerminyak')?.checked ? 1 : 0);
            injectHiddenInput(`items[${idx}][kemasan_berdebu]`, document.getElementById('checkCacatBerdebu')?.checked ? 1 : 0);

            injectHiddenInput(`items[${idx}][dimensi_panjang_analisa]`, document.getElementById('inputPanjangAnalisa')?.value || '');
            injectHiddenInput(`items[${idx}][dimensi_panjang_standar]`, document.getElementById('inputPanjangStandar')?.value || '40 cm');
            injectHiddenInput(`items[${idx}][dimensi_lebar_analisa]`, document.getElementById('inputLebarAnalisa')?.value || '');
            injectHiddenInput(`items[${idx}][dimensi_lebar_standar]`, document.getElementById('inputLebarStandar')?.value || '30 cm');
            injectHiddenInput(`items[${idx}][dimensi_tinggi_analisa]`, document.getElementById('inputTinggiAnalisa')?.value || '');
            injectHiddenInput(`items[${idx}][dimensi_tinggi_standar]`, document.getElementById('inputTinggiStandar')?.value || '25 cm');
            injectHiddenInput(`items[${idx}][spesifikasi_analisa]`, document.getElementById('inputSpesifikasiAnalisa')?.value || '');
            injectHiddenInput(`items[${idx}][spesifikasi_standar]`, document.getElementById('inputSpesifikasiStandar')?.value || 'Sesuai Spesifikasi MFI');
            injectHiddenInput(`items[${idx}][satuan]`, currentKartonSatuan);
        }

        injectHiddenInput('nama_jenis', curNamaJenis);
        if (currentPodtlId) {
            injectHiddenInput('podtl_id', currentPodtlId);
        }

        if (qtyA > 0 && qtyB > 0) {
            // MULTI-GRADE: Terdapat Grade A dan Grade B sekaligus dalam 1 kali inspeksi
            // Item 0: Grade A (Dus Baru)
            if (currentPodtlId) injectHiddenInput('items[0][podtl_id]', currentPodtlId);
            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', qtyA);
            injectHiddenInput('items[0][qty_reject]', 0);
            injectHiddenInput('items[0][qty_netto_lolos]', kesimpulan === 'TERIMA' ? qtyA : 0);
            injectHiddenInput('items[0][grade_cd]', kesimpulan === 'TERIMA' ? 'A' : 'REJECT');
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][catatan_dtl]', (komentarText ? komentarText + ' | ' : '') + 'Grade A (Dus Baru)');
            injectCommonParams(0);

            // Item 1: Grade B (Dus Puteran/Reuse)
            if (currentPodtlId) injectHiddenInput('items[1][podtl_id]', currentPodtlId);
            injectHiddenInput('items[1][barang_id]', bId);
            injectHiddenInput('items[1][qty_timbang_gross]', qtyB + reject);
            injectHiddenInput('items[1][qty_reject]', reject);
            injectHiddenInput('items[1][qty_netto_lolos]', kesimpulan === 'TERIMA' ? qtyB : 0);
            injectHiddenInput('items[1][grade_cd]', kesimpulan === 'TERIMA' ? 'B' : 'REJECT');
            injectHiddenInput('items[1][keputusan_qc]', kesimpulan === 'TERIMA' ? (reject > 0 ? 'REJECT_PARTIAL' : 'PASSED') : 'REJECT_TOTAL');
            injectHiddenInput('items[1][catatan_dtl]', (komentarText ? komentarText + ' | ' : '') + 'Grade B (Dus Puteran)');
            injectCommonParams(1);

            injectHiddenInput('grade_cd', 'A');
        } else {
            // SINGLE-GRADE: Hanya Grade A saja atau Grade B saja
            const singleGrade = (qtyB > 0 && qtyA === 0) ? 'B' : 'A';
            const lolosQty = (qtyB > 0 && qtyA === 0) ? qtyB : qtyA;

            if (currentPodtlId) injectHiddenInput('items[0][podtl_id]', currentPodtlId);
            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', totalGross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][qty_netto_lolos]', kesimpulan === 'TERIMA' ? lolosQty : 0);
            injectHiddenInput('items[0][grade_cd]', kesimpulan === 'TERIMA' ? singleGrade : 'REJECT');
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? (reject > 0 ? 'REJECT_PARTIAL' : 'PASSED') : 'REJECT_TOTAL');
            injectHiddenInput('items[0][catatan_dtl]', (komentarText ? komentarText + ' | ' : '') + `Grade ${singleGrade}`);
            injectCommonParams(0);

            injectHiddenInput('grade_cd', kesimpulan === 'TERIMA' ? singleGrade : 'REJECT');
        }

        injectHiddenInput('catatan_umum', komentarText);

        // Header level
        injectHiddenInput('satuan', currentKartonSatuan);
        injectHiddenInput('items[0][satuan]', currentKartonSatuan);
        injectHiddenInput('kesimpulan_qc', kesimpulan);
        injectHiddenInput('jumlah_di_pabrik', totalGross);
        injectHiddenInput('jumlah_surat_jalan', sjVal > 0 ? sjVal : totalGross);
        const samplePcs = document.getElementById('inputJumlahSamplePcs');
        injectHiddenInput('jumlah_sample_pcs', parseInt(samplePcs?.value || 10, 10) || 10);

        injectHiddenInput('status_uji_goreng', 'SELESAI');
        injectHiddenInput('tahap_uji', 'PENGUJIAN_1');

        const petugasQc = document.getElementById('inputPetugasQc')?.value?.trim();
        if (petugasQc) injectHiddenInput('petugas_qc_nama', petugasQc);

        const supervisorQc = document.getElementById('inputSupervisorQc')?.value?.trim();
        if (supervisorQc) injectHiddenInput('qc_supervisor_nama', supervisorQc);

        showSubmitLoading(kesimpulan === 'TOLAK' ? 'Menyimpan Penolakan Karton Box...' : 'Menyimpan QC Karton & Menyiapkan Gudang...');
        clearKartonDraft();

        document.getElementById('qcKartonForm').submit();
    };

    // 14. Inisialisasi Saat Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        const bSelInit = document.getElementById('kartonBarangSelect');
        if (bSelInit && !originalBarangOptionsHTML) {
            originalBarangOptionsHTML = bSelInit.innerHTML;
        }

        const initSatuan = document.getElementById('inputKartonSatuan')?.value;
        if (initSatuan) {
            window.setKartonSatuan(initSatuan);
        }

        const hasDraft = restoreKartonDraft();

        const poSelect = document.getElementById('poSelect');
        if (!hasDraft) {
            if (poSelect && poSelect.value) {
                window.onPoSelected(poSelect);
            } else {
                window.toggleKartonPoLockFields(false);
                const bSel = document.getElementById('kartonBarangSelect');
                if (bSel) window.onKartonBarangChanged(bSel);
            }
        } else {
            if (poSelect && poSelect.value) {
                const poId = poSelect.value;
                const poData = window.appConfig?.poListData?.[poId];
                window.toggleKartonPoLockFields(true, poData);
            } else {
                window.toggleKartonPoLockFields(false);
            }
        }

        let targetTab = 1;
        const hashMatch = window.location.hash.match(/tab(\d+)/);
        if (hashMatch) {
            targetTab = parseInt(hashMatch[1], 10);
        } else {
            const savedTab = sessionStorage.getItem(ACTIVE_TAB_KEY);
            if (savedTab) targetTab = parseInt(savedTab, 10);
        }

        if (targetTab === 2) {
            window.switchKartonTab(2, true);
        } else {
            window.switchKartonTab(1, true);
        }

        const form = document.getElementById('qcKartonForm');
        if (form) {
            form.addEventListener('input', triggerSaveDraftDebounced);
            form.addEventListener('change', triggerSaveDraftDebounced);
        }

        const inputGradeA = document.getElementById('kartonQtyGradeA');
        const inputGradeB = document.getElementById('kartonQtyGradeB');
        const inputReject = document.getElementById('kartonQtyReject');
        const inputPabrik = document.getElementById('inputJumlahPabrik');
        const inputSJ = document.getElementById('inputJumlahSJ');
        const inputTgl = document.getElementById('inputTglDatang');

        if (inputTgl) {
            inputTgl.addEventListener('change', function () {
                updateInspectionTimeDisplay(this.value);
            });
        }

        if (inputGradeA) inputGradeA.addEventListener('input', function () { window.onKartonQtySplitChanged('grade_a'); });
        if (inputGradeB) inputGradeB.addEventListener('input', function () { window.onKartonQtySplitChanged('grade_b'); });
        if (inputReject) inputReject.addEventListener('input', function () { window.onKartonQtySplitChanged('reject'); });

        if (inputSJ) {
            inputSJ.addEventListener('input', function () {
                this.dataset.autoFilled = 'false';
                window.onKartonQtySplitChanged('sj');
            });
        }

        console.log('[QC Mobile] Dedicated Karton Box module with autosave & modern cards loaded successfully.');
    });
})();
