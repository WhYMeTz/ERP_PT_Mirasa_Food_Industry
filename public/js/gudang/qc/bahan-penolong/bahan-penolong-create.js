/**
 * QC INBOUND - MODUL KOMODITAS: BAHAN PENOLONG (MSG, GARAM, PERENYAH)
 * PT Mirasa Food Industry
 * Dedicated JS controller untuk halaman Mobile QC Bahan Penolong
 * Mengikuti standar HACCP MFI (Dokumen HACCP-032 / 033 / 063)
 */

(function () {
    'use strict';

    const ACTIVE_TAB_KEY = 'mirasa_qc_bp_active_tab';
    const DRAFT_KEY = 'mirasa_qc_bp_draft_v1';
    let currentBpTab = 1;
    let draftSaveTimer = null;

    let currentPoCategory = 'BP';
    let currentPoSearch = '';
    let currentSupplierCategory = 'VENDOR';
    let currentSupplierSearch = '';
    let isSubmitting = false;

    // 1. Live Clock Timer & Set Current Datetime
    window.setCurrentDateTime = function () {
        const input = document.getElementById('inputTglDatang');
        if (input) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    };

    function startLiveInspectionClock() {
        const clockEl = document.getElementById('textInspectionBarTime');
        if (!clockEl) return;
        const updateClock = () => {
            const now = new Date();
            const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            clockEl.innerText = `${now.toLocaleDateString('id-ID', options).replace(',', ' •')} WIB`;
        };
        setInterval(updateClock, 30000);
    }

    // 2. Tab Navigation (Tahap 1: Dokumen & Armada, Tahap 2: Mutu Bahan Penolong)
    window.switchBpTab = function (tabNumber, isInitialLoad = false) {
        currentBpTab = tabNumber;
        const sec1 = document.getElementById('qcSection1');
        const sec2 = document.getElementById('qcSection2');
        const btn1 = document.getElementById('tabBtn1');
        const btn2 = document.getElementById('tabBtn2');

        if (!sec1 || !sec2) return;

        if (tabNumber === 1) {
            sec1.style.display = 'flex';
            sec2.style.display = 'none';

            if (btn1) {
                btn1.style.background = '#7c3aed';
                btn1.style.borderColor = '#7c3aed';
                btn1.style.color = '#ffffff';
            }
            if (btn2) {
                btn2.style.background = '#ffffff';
                btn2.style.borderColor = '#cbd5e1';
                btn2.style.color = '#475569';
            }
            updateDockingButtons(1);
            if (!isInitialLoad) window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            if (!isInitialLoad) {
                const sup = document.getElementById('supplierSelect')?.value;
                const gud = document.getElementById('gudangSelect')?.value;
                if (!sup) {
                    alert('Silakan pilih Mitra Supplier / Produsen Bahan terlebih dahulu!');
                    document.getElementById('supplierSelect')?.focus();
                    return;
                }
                if (!gud) {
                    alert('Silakan pilih Perusahaan / Gudang Tujuan Bongkar terlebih dahulu!');
                    document.getElementById('gudangSelect')?.focus();
                    return;
                }
            }

            // Sinkronkan Nama Barang & Spesifikasi ke Info Strip Tab 2
            updateTab2InfoStrip();

            sec1.style.display = 'none';
            sec2.style.display = 'block';

            if (btn1) {
                btn1.style.background = '#ffffff';
                btn1.style.borderColor = '#cbd5e1';
                btn1.style.color = '#475569';
            }
            if (btn2) {
                btn2.style.background = '#7c3aed';
                btn2.style.borderColor = '#7c3aed';
                btn2.style.color = '#ffffff';
            }
            updateDockingButtons(2);
            if (!isInitialLoad) window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        try {
            sessionStorage.setItem(ACTIVE_TAB_KEY, tabNumber.toString());
            const url = new URL(window.location.href);
            url.hash = 'tab' + tabNumber;
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}

        if (!isInitialLoad) {
            saveBpDraft();
        }
    };

    function updateDockingButtons(tab) {
        const btnPrev = document.getElementById('dockBtnPrev');
        const btnNext = document.getElementById('dockBtnNext');
        const nextText = document.getElementById('dockBtnNextText');
        const nextIcon = document.getElementById('dockBtnNextIcon');

        if (!btnNext) return;

        if (tab === 1) {
            if (btnPrev) btnPrev.style.display = 'none';
            btnNext.style.background = '#7c3aed';
            btnNext.style.borderColor = '#7c3aed';
            if (nextText) nextText.innerText = 'Lanjut: Mutu Bahan';
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function () { window.switchBpTab(2); };
        } else {
            if (btnPrev) btnPrev.style.display = 'inline-flex';
            btnNext.style.background = '#059669';
            btnNext.style.borderColor = '#059669';
            if (nextText) nextText.innerText = '💾 Simpan QC Bahan Penolong';
            if (nextIcon) nextIcon.style.display = 'none';
            btnNext.onclick = function () { window.submitBpForm(); };
        }
    }

    function updateTab2InfoStrip() {
        const bSel = document.getElementById('bpBarangSelect');
        const opt = bSel?.selectedOptions ? bSel.selectedOptions[0] : null;
        const bNm = opt ? (opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text) : '-';
        const jNm = document.getElementById('bpNamaJenisInput')?.value || '-';

        const stripBarang = document.getElementById('tab2InfoBarang');
        const stripJenis = document.getElementById('tab2InfoJenis');

        if (stripBarang) stripBarang.innerText = bNm.trim();
        if (stripJenis) stripJenis.innerText = jNm.trim();
    }

    // 3. Transportasi & Halal Toggles
    window.updateTransportCard = function (radio) {
        const cardClean = document.getElementById('cardTransportClean');
        const cardDirty = document.getElementById('cardTransportDirty');
        if (!cardClean || !cardDirty) return;

        if (radio.value === '1') {
            cardClean.classList.add('active');
            cardClean.classList.remove('is-danger');
            cardDirty.classList.remove('active', 'is-danger');
        } else {
            cardDirty.classList.add('active', 'is-danger');
            cardClean.classList.remove('active');
        }
    };

    window.setHalalToggle = function (field, value) {
        const input = document.getElementById(`input_${field}`);
        if (input) input.value = value;

        if (field === 'angkut_barang_haram') {
            const btnNo = document.getElementById('btn_angkut_haram_no');
            const btnYes = document.getElementById('btn_angkut_haram_yes');
            if (value === 0) {
                btnNo?.classList.add('active', 'ok');
                btnYes?.classList.remove('active', 'danger');
            } else {
                btnYes?.classList.add('active', 'danger');
                btnNo?.classList.remove('active', 'ok');
            }
        } else if (field === 'terdaftar_lppom') {
            const btnYes = document.getElementById('btn_lppom_yes');
            const btnNo = document.getElementById('btn_lppom_no');
            if (value === 1) {
                btnYes?.classList.add('active', 'ok');
                btnNo?.classList.remove('active', 'danger');
            } else {
                btnNo?.classList.add('active', 'danger');
                btnYes?.classList.remove('active', 'ok');
            }
        } else if (field === 'ada_sertifikat_halal') {
            const btnYes = document.getElementById('btn_sertifikat_yes');
            const btnNo = document.getElementById('btn_sertifikat_no');
            if (value === 1) {
                btnYes?.classList.add('active', 'ok');
                btnNo?.classList.remove('active', 'danger');
            } else {
                btnNo?.classList.add('active', 'danger');
                btnYes?.classList.remove('active', 'ok');
            }
        } else if (field === 'sertifikat_halal_berlaku') {
            const btnYes = document.getElementById('btn_berlaku_yes');
            const btnNo = document.getElementById('btn_berlaku_no');
            if (value === 1) {
                btnYes?.classList.add('active', 'ok');
                btnNo?.classList.remove('active', 'danger');
            } else {
                btnNo?.classList.add('active', 'danger');
                btnYes?.classList.remove('active', 'ok');
            }
        }
    };

    // 4. Modern Choice Buttons (Status Raw Material & Kemasan)
    window.setRawMaterialStatus = function (status) {
        const input = document.getElementById('inputRawMaterial');
        const btnOk = document.getElementById('btnRawOk');
        const btnTdkStd = document.getElementById('btnRawTdkStd');
        if (input) input.value = status;

        if (status === 'OK') {
            btnOk?.classList.add('active', 'ok');
            btnOk?.classList.remove('danger');
            btnTdkStd?.classList.remove('active', 'danger', 'ok');
        } else {
            btnTdkStd?.classList.add('active', 'danger');
            btnTdkStd?.classList.remove('ok');
            btnOk?.classList.remove('active', 'ok');
        }
    };

    window.setKemasanKondisi = function (status) {
        const input = document.getElementById('inputKemasanKondisi');
        const btnOk = document.getElementById('btnKemasanOk');
        const btnTdkStd = document.getElementById('btnKemasanTdkStd');
        if (input) input.value = status;

        if (status === 'OK') {
            btnOk?.classList.add('active', 'ok');
            btnOk?.classList.remove('danger');
            btnTdkStd?.classList.remove('active', 'danger', 'ok');
        } else {
            btnTdkStd?.classList.add('active', 'danger');
            btnTdkStd?.classList.remove('ok');
            btnOk?.classList.remove('active', 'ok');
        }
    };

    // 5. Modern Check Cards (Kondisi Fisik Isi & Wadah Kemasan)
    window.toggleModernCheck = function (checkId) {
        const cb = document.getElementById(checkId);
        if (!cb) return;
        cb.checked = !cb.checked;
        window.onModernCheckboxChanged(cb);
    };

    window.onModernCheckboxChanged = function (cb) {
        if (!cb) return;
        const card = cb.closest('.modern-check-card');
        if (!card) return;

        if (cb.checked) {
            card.classList.add('is-checked');
        } else {
            card.classList.remove('is-checked');
        }
    };

    // 6. Disposisi QC (TERIMA vs TOLAK)
    window.setKesimpulanBp = function (status) {
        const cardTerima = document.getElementById('cardDisposisiTerima');
        const cardTolak = document.getElementById('cardDisposisiTolak');
        const inputBpKesimpulan = document.getElementById('inputBpKesimpulan');
        const inputKesimpulanQc = document.getElementById('inputKesimpulanQc');

        if (inputBpKesimpulan) inputBpKesimpulan.value = status;
        if (inputKesimpulanQc) inputKesimpulanQc.value = status;

        if (status === 'TERIMA') {
            cardTerima?.classList.add('active', 'terima');
            cardTolak?.classList.remove('active', 'tolak');
        } else {
            cardTolak?.classList.add('active', 'tolak');
            cardTerima?.classList.remove('active', 'terima');
        }

        syncBpQuantity();
    };

    // 7. Kalkulasi Kuantitas & Sinkronisasi
    window.syncBpQuantity = function () {
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const inSJ = document.getElementById('inputJumlahSJ');
        const grossInput = document.getElementById('bpQtyGross');
        const rejectInput = document.getElementById('bpQtyReject');
        const labelNetto = document.getElementById('labelBpNetto');
        const labelSubcardNetto = document.getElementById('labelSubcardNetto');
        const inputQtyTerima = document.getElementById('inputQtyTerima');
        const inputQtyTolak = document.getElementById('inputQtyTolak');
        const status = document.getElementById('inputBpKesimpulan')?.value || 'TERIMA';

        if (grossInput && (!grossInput.value || grossInput.value == 0) && inPabrik && inPabrik.value) {
            grossInput.value = inPabrik.value;
        } else if (inPabrik && (!inPabrik.value || inPabrik.value == 0) && grossInput && grossInput.value) {
            inPabrik.value = grossInput.value;
        }

        const gross = parseFloat(grossInput?.value || inPabrik?.value || 0) || 0;
        const reject = parseFloat(rejectInput?.value || 0) || 0;

        if (status === 'TOLAK') {
            const tolakVal = gross > 0 ? gross : reject;
            if (labelNetto) labelNetto.innerText = '0.00 KG (DITOLAK)';
            if (labelSubcardNetto) labelSubcardNetto.innerText = 'Netto Lolos: 0.00 KG';
            if (inputQtyTerima) inputQtyTerima.value = 0;
            if (inputQtyTolak) inputQtyTolak.value = tolakVal;
        } else {
            const netto = Math.max(0, gross - reject);
            const formattedNetto = `${netto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;

            if (labelNetto) labelNetto.innerText = formattedNetto;
            if (labelSubcardNetto) labelSubcardNetto.innerText = `Netto Lolos: ${formattedNetto}`;
            if (inputQtyTerima) inputQtyTerima.value = netto;
            if (inputQtyTolak) inputQtyTolak.value = reject;
        }
    };

    // 8. Event Listener saat Master Barang Bahan Penolong Berubah
    window.onBpBarangChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt) {
            const nama = (opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text).trim();
            const upper = nama.toUpperCase();

            // Update badge nomor dokumen & kode dokumen sesuai jenis item
            const badgeDoc = document.getElementById('badgeDocNo');
            const badgeCode = document.getElementById('badgeInspectionDocCode');
            const katInput = document.getElementById('kategoriBarangInput');

            if (upper.includes('GARAM') || upper.includes('SALT')) {
                if (badgeDoc) badgeDoc.innerText = 'No. Dok: MFI/HACCP-04/FRM-03/033/VIII/2021';
                if (badgeCode) badgeCode.innerText = 'QC-GRM-033';
                if (katInput) katInput.value = 'GARAM';
            } else if (upper.includes('PERENYAH') || upper.includes('STPP')) {
                if (badgeDoc) badgeDoc.innerText = 'No. Dok: MFI/HACCP-04/FRM-03/063/IX/2023';
                if (badgeCode) badgeCode.innerText = 'QC-PRY-063';
                if (katInput) katInput.value = 'PERENYAH';
            } else if (upper.includes('BUMBU') || upper.includes('BALADO') || upper.includes('BBQ') || upper.includes('SEASALT')) {
                if (badgeDoc) badgeDoc.innerText = 'No. Dok: MFI/HACCP-04/FRM-03/032/VIII/2021';
                if (badgeCode) badgeCode.innerText = 'QC-BMB-032';
                if (katInput) katInput.value = 'BUMBU';
            } else {
                if (badgeDoc) badgeDoc.innerText = 'No. Dok: MFI/HACCP-04/FRM-03/032/VIII/2021';
                if (badgeCode) badgeCode.innerText = 'QC-MSG-032';
                if (katInput) katInput.value = 'MSG';
            }

            updateTab2InfoStrip();
        }
    };

    // 9. Vendor / Supplier Dropdown Filter
    window.onSupplierSelected = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : null;
        if (opt) {
            const supNm = (opt.getAttribute('data-supplier-nm') || opt.text.split('(')[0] || '').trim();
            const prodInput = document.getElementById('inputNamaProdusen');
            if (prodInput && (!prodInput.value || prodInput.dataset.autoFilled === 'true')) {
                prodInput.value = supNm;
                prodInput.dataset.autoFilled = 'true';
            }
        }
    };

    // 10. Multi-Item PO Selector Cards
    window.onPoSelected = function (select, targetPodtlId = null) {
        const poId = select?.value;
        const boxSelector = document.getElementById('boxPoItemsSelector');
        const podtlHidden = document.getElementById('inputSelectedPodtlId');

        if (!poId) {
            if (boxSelector) boxSelector.style.display = 'none';
            if (podtlHidden) podtlHidden.value = '';
            return;
        }

        const opt = select.selectedOptions ? select.selectedOptions[0] : null;
        const supplierId = opt ? opt.getAttribute('data-supplier-id') : null;
        const gudangId = opt ? opt.getAttribute('data-gudang-id') : null;

        const supplierSelect = document.getElementById('supplierSelect');
        if (supplierSelect && supplierId) {
            supplierSelect.value = supplierId;
            window.onSupplierSelected(supplierSelect);
        }

        const gudangSelect = document.getElementById('gudangSelect');
        if (gudangSelect && gudangId) {
            gudangSelect.value = gudangId;
        }

        renderPoItemsCards(poId, targetPodtlId);
    };

    function renderPoItemsCards(poId, targetPodtlId = null) {
        const boxSelector = document.getElementById('boxPoItemsSelector');
        const cardsList = document.getElementById('poItemsCardsList');
        const countBadge = document.getElementById('countPoItemsBadge');
        const podtlHidden = document.getElementById('inputSelectedPodtlId');

        if (!boxSelector || !cardsList) return;

        let poData = null;
        if (window.appConfig && window.appConfig.poListData && window.appConfig.poListData[poId]) {
            poData = window.appConfig.poListData[poId];
        }

        if (!poData || !poData.items || poData.items.length === 0) {
            boxSelector.style.display = 'none';
            if (podtlHidden) podtlHidden.value = '';

            // Fallback via AJAX jika tidak ada di pre-map
            if (window.appConfig && window.appConfig.poDetailsUrl) {
                fetch(`${window.appConfig.poDetailsUrl}/${poId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success && data.po && data.po.items) {
                            applyPoDetailData(data.po);
                        }
                    })
                    .catch(err => console.warn('[QC Bahan Penolong] Gagal memuat detail PO:', err));
            }
            return;
        }

        const items = poData.items;
        if (countBadge) countBadge.innerText = items.length;
        boxSelector.style.display = 'block';

        let targetIdx = 0;
        if (targetPodtlId) {
            const foundIdx = items.findIndex(it => String(it.podtl_id) === String(targetPodtlId));
            if (foundIdx !== -1) targetIdx = foundIdx;
        }

        let html = '';
        items.forEach((item, idx) => {
            const isSelected = idx === targetIdx;
            const sisaFmt = parseFloat(item.sisa_qty).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            const pesanFmt = parseFloat(item.pesan_qty).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

            html += `
                <div class="po-item-card ${isSelected ? 'is-active' : ''}"
                     id="poItemCard_${item.podtl_id}"
                     onclick="selectPoItem('${item.podtl_id}', '${item.barang_id}', '${item.sisa_qty}', '${item.barang_nm ? item.barang_nm.replace(/'/g, "\\'") : ''}')">
                    <div class="po-item-card-left">
                        <div class="po-item-radio-circle"></div>
                        <div class="po-item-info">
                            <span class="po-item-nama">${item.barang_nm || 'Item Bahan'}</span>
                            <span class="po-item-sub">PO: ${pesanFmt} KG &bull; Sisa Belum Datang: <strong>${sisaFmt} KG</strong></span>
                        </div>
                    </div>
                    <span class="po-item-badge-sisa">${sisaFmt} KG</span>
                </div>
            `;
        });

        cardsList.innerHTML = html;

        // Auto pilih item
        if (items.length > 0) {
            const chosen = items[targetIdx];
            selectPoItem(chosen.podtl_id, chosen.barang_id, chosen.sisa_qty, chosen.barang_nm);
        }
    }

    window.selectPoItem = function (podtlId, barangId, sisaQty, barangNm) {
        const podtlHidden = document.getElementById('inputSelectedPodtlId');
        if (podtlHidden) podtlHidden.value = podtlId;

        document.querySelectorAll('.po-item-card').forEach(card => card.classList.remove('is-active'));
        const activeCard = document.getElementById(`poItemCard_${podtlId}`);
        if (activeCard) activeCard.classList.add('is-active');

        const bSel = document.getElementById('bpBarangSelect');
        if (bSel && barangId) {
            bSel.value = barangId;
            window.onBpBarangChanged(bSel);
        }

        const sisa = parseFloat(sisaQty) || 0;
        if (sisa > 0) {
            const inSJ = document.getElementById('inputJumlahSJ');
            const inPabrik = document.getElementById('inputJumlahPabrik');
            const grossInput = document.getElementById('bpQtyGross');
            if (inSJ && (!inSJ.value || inSJ.value == 0)) inSJ.value = sisa;
            if (inPabrik && (!inPabrik.value || inPabrik.value == 0)) inPabrik.value = sisa;
            if (grossInput && (!grossInput.value || grossInput.value == 0)) grossInput.value = sisa;
            syncBpQuantity();
        }
    };

    function applyPoDetailData(poData) {
        if (!poData || !poData.items || poData.items.length === 0) return;
        const firstItem = poData.items[0];
        const bSel = document.getElementById('bpBarangSelect');
        if (bSel && firstItem.barang_id) {
            bSel.value = firstItem.barang_id;
            window.onBpBarangChanged(bSel);
        }
        if (firstItem.sisa_qty > 0) {
            const inSJ = document.getElementById('inputJumlahSJ');
            const inPabrik = document.getElementById('inputJumlahPabrik');
            const grossInput = document.getElementById('bpQtyGross');
            if (inSJ && (!inSJ.value || inSJ.value == 0)) inSJ.value = firstItem.sisa_qty;
            if (inPabrik && (!inPabrik.value || inPabrik.value == 0)) inPabrik.value = firstItem.sisa_qty;
            if (grossInput && (!grossInput.value || grossInput.value == 0)) grossInput.value = firstItem.sisa_qty;
            syncBpQuantity();
        }
    }

    // 11. Validasi & Submit Form Khusus Bahan Penolong
    function injectHiddenInput(name, value) {
        let el = document.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = name;
            document.getElementById('qcBpForm').appendChild(el);
        }
        el.value = value;
    }

    function showSubmitLoading(title = 'Menyimpan Data QC Bahan Penolong...') {
        isSubmitting = true;
        const overlay = document.getElementById('qcSubmitOverlay');
        const overlayTitle = document.getElementById('overlayTitle');
        if (overlayTitle) overlayTitle.innerText = title;
        if (overlay) overlay.style.display = 'flex';

        document.querySelectorAll('button[type="submit"], #dockBtnNext').forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
        });
    }

    window.submitBpForm = function () {
        if (isSubmitting) return;

        const supplierSelect = document.getElementById('supplierSelect');
        if (!supplierSelect || !supplierSelect.value) {
            alert('Silakan pilih Mitra Supplier / Produsen Bahan terlebih dahulu!');
            window.switchBpTab(1);
            supplierSelect?.focus();
            return;
        }

        const gudangSelect = document.getElementById('gudangSelect');
        if (!gudangSelect || !gudangSelect.value) {
            alert('Silakan pilih Perusahaan / Gudang Tujuan Bongkar!');
            window.switchBpTab(1);
            gudangSelect?.focus();
            return;
        }

        const inputJumlahSJ = document.getElementById('inputJumlahSJ');
        const sjVal = parseFloat(inputJumlahSJ?.value || 0);
        if (isNaN(sjVal) || sjVal <= 0) {
            alert('Jumlah kuantitas pada Surat Jalan wajib diisi dan lebih dari 0 KG!');
            window.switchBpTab(1);
            inputJumlahSJ?.focus();
            return;
        }

        const bpSelect = document.getElementById('bpBarangSelect');
        if (!bpSelect || !bpSelect.value) {
            alert('Pilih item komoditas bahan penolong yang diuji!');
            window.switchBpTab(2);
            bpSelect?.focus();
            return;
        }

        const grossInput = document.getElementById('bpQtyGross');
        const grossVal = parseFloat(grossInput?.value || 0);
        const pabrikVal = parseFloat(document.getElementById('inputJumlahPabrik')?.value || 0);
        if ((isNaN(grossVal) || grossVal <= 0) && (isNaN(pabrikVal) || pabrikVal <= 0)) {
            alert('Kuantitas jumlah lolos / netto kedatangan bahan penolong wajib diisi lebih dari 0 KG!');
            window.switchBpTab(2);
            grossInput?.focus();
            return;
        }

        // Siapkan Payload khusus Bahan Penolong
        const bId = bpSelect.value;
        const bOpt = bpSelect.selectedOptions[0];
        const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Bahan Penolong';
        const curNamaJenis = document.getElementById('bpNamaJenisInput')?.value?.trim() || bNm;

        const gross = grossVal > 0 ? grossVal : pabrikVal;
        const rejectInputVal = parseFloat(document.getElementById('bpQtyReject')?.value || 0) || 0;
        const kesimpulan = document.getElementById('inputBpKesimpulan')?.value || 'TERIMA';

        const finalReject = (kesimpulan === 'TOLAK') ? gross : rejectInputVal;
        const finalNetto = (kesimpulan === 'TOLAK') ? 0 : Math.max(0, gross - finalReject);

        injectHiddenInput('nama_jenis', curNamaJenis);
        injectHiddenInput('items[0][barang_id]', bId);
        injectHiddenInput('items[0][qty_timbang_gross]', gross);
        injectHiddenInput('items[0][qty_reject]', finalReject);
        injectHiddenInput('items[0][qty_netto_lolos]', finalNetto);
        injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TOLAK' ? 'REJECT_TOTAL' : (finalReject > 0 ? 'REJECT_PARTIAL' : 'PASSED'));
        injectHiddenInput('qty_terima', finalNetto);
        injectHiddenInput('qty_tolak', finalReject);
        injectHiddenInput('items[0][status_raw_material]', document.getElementById('inputRawMaterial')?.value || 'OK');
        injectHiddenInput('items[0][kemasan_kondisi]', document.getElementById('inputKemasanKondisi')?.value || 'OK');

        injectHiddenInput('items[0][isi_kering]', document.getElementById('checkIsiKering')?.checked ? 1 : 0);
        injectHiddenInput('items[0][isi_basah]', document.getElementById('checkIsiBasah')?.checked ? 1 : 0);
        injectHiddenInput('items[0][isi_gumpal]', document.getElementById('checkIsiGumpal')?.checked ? 1 : 0);
        injectHiddenInput('items[0][isi_berminyak]', document.getElementById('checkIsiBerminyak')?.checked ? 1 : 0);

        injectHiddenInput('items[0][kemasan_kotor]', document.getElementById('checkKemasanKotor')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_apek]', document.getElementById('checkKemasanApek')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_jamur]', document.getElementById('checkKemasanJamur')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_sobek]', document.getElementById('checkKemasanSobek')?.checked ? 1 : 0);

        const catatanKomentar = document.getElementById('inputBpKomentar')?.value || '';
        injectHiddenInput('items[0][catatan_dtl]', catatanKomentar);
        injectHiddenInput('catatan_umum', catatanKomentar);

        // Header level
        injectHiddenInput('kesimpulan_qc', kesimpulan);
        injectHiddenInput('jumlah_di_pabrik', gross);
        injectHiddenInput('jumlah_surat_jalan', sjVal > 0 ? sjVal : gross);

        const sampleGr = document.getElementById('inputJumlahSampleGr');
        injectHiddenInput('jumlah_sample_gr', parseFloat(sampleGr?.value || 200) || 200);

        injectHiddenInput('status_uji_goreng', 'SELESAI');
        injectHiddenInput('tahap_uji', 'PENGUJIAN_1');

        try {
            sessionStorage.removeItem(DRAFT_KEY);
            sessionStorage.removeItem(ACTIVE_TAB_KEY);
        } catch (e) {}

        showSubmitLoading(kesimpulan === 'TOLAK' ? 'Menyimpan Penolakan Bahan Penolong...' : 'Menyimpan QC Bahan Penolong & Menyiapkan Gudang...');
        document.getElementById('qcBpForm').submit();
    };

    // 12. Auto-Save Draft & Restore State (Mirip Standar Singkong/Plastik/Karton)
    function saveBpDraft() {
        if (isSubmitting) return;
        clearTimeout(draftSaveTimer);
        draftSaveTimer = setTimeout(function () {
            try {
                const draft = {
                    po_id: document.getElementById('poSelect')?.value || '',
                    podtl_id: document.getElementById('inputSelectedPodtlId')?.value || '',
                    gudang_id: document.getElementById('gudangSelect')?.value || '',
                    surat_jalan_supplier: document.getElementById('inputNoSJ')?.value || '',
                    tgl_periksa: document.getElementById('inputTglDatang')?.value || '',
                    supplier_id: document.getElementById('supplierSelect')?.value || '',
                    bp_barang_id: document.getElementById('bpBarangSelect')?.value || '',
                    nama_jenis: document.getElementById('bpNamaJenisInput')?.value || '',
                    nama_produsen: document.getElementById('inputNamaProdusen')?.value || '',
                    negara_produsen: document.getElementById('inputNegaraProdusen')?.value || '',
                    plat_nomor_truk: document.getElementById('inputPlatNomor')?.value || '',
                    sopir_nama: document.getElementById('inputNamaSupir')?.value || '',
                    jumlah_surat_jalan: document.getElementById('inputJumlahSJ')?.value || '',
                    jumlah_di_pabrik: document.getElementById('inputJumlahPabrik')?.value || '',
                    jumlah_sample_gr: document.getElementById('inputJumlahSampleGr')?.value || '',

                    bebas_cemaran_st: document.querySelector('input[name="bebas_cemaran_st"]:checked')?.value || '1',
                    angkut_barang_haram_st: document.getElementById('input_angkut_barang_haram')?.value || '0',
                    terdaftar_lppom_st: document.getElementById('input_terdaftar_lppom')?.value || '1',
                    ada_sertifikat_halal_st: document.getElementById('input_ada_sertifikat_halal')?.value || '1',
                    sertifikat_halal_berlaku_st: document.getElementById('input_sertifikat_halal_berlaku')?.value || '1',

                    komentar_transportasi: document.getElementById('inputKomentarTransportasi')?.value || '',
                    komentar_lppom: document.getElementById('inputKomentarLppom')?.value || '',
                    komentar_sertifikat: document.getElementById('inputKomentarSertifikat')?.value || '',
                    komentar_berlaku: document.getElementById('inputKomentarBerlaku')?.value || '',

                    bp_status_raw_material: document.getElementById('inputRawMaterial')?.value || 'OK',
                    bp_kemasan_kondisi: document.getElementById('inputKemasanKondisi')?.value || 'OK',

                    bp_isi_kering: document.getElementById('checkIsiKering')?.checked ? 1 : 0,
                    bp_isi_basah: document.getElementById('checkIsiBasah')?.checked ? 1 : 0,
                    bp_isi_gumpal: document.getElementById('checkIsiGumpal')?.checked ? 1 : 0,
                    bp_isi_berminyak: document.getElementById('checkIsiBerminyak')?.checked ? 1 : 0,

                    bp_kemasan_kotor: document.getElementById('checkKemasanKotor')?.checked ? 1 : 0,
                    bp_kemasan_apek: document.getElementById('checkKemasanApek')?.checked ? 1 : 0,
                    bp_kemasan_jamur: document.getElementById('checkKemasanJamur')?.checked ? 1 : 0,
                    bp_kemasan_sobek: document.getElementById('checkKemasanSobek')?.checked ? 1 : 0,

                    bp_qty_gross: document.getElementById('bpQtyGross')?.value || '',
                    bp_qty_reject: document.getElementById('bpQtyReject')?.value || '0',
                    bp_kesimpulan: document.getElementById('inputBpKesimpulan')?.value || 'TERIMA',
                    bp_komentar: document.getElementById('inputBpKomentar')?.value || '',
                    petugas_qc_nama: document.getElementById('inputPetugasQc')?.value || '',
                    qc_supervisor_nama: document.getElementById('inputSupervisorQc')?.value || '',
                    active_tab: currentBpTab
                };

                sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
            } catch (e) {
                console.warn('[QC Bahan Penolong] Gagal menyimpan draft formulir:', e);
            }
        }, 300);
    }

    function restoreBpDraft() {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (!raw) return false;
            const draft = JSON.parse(raw);
            if (!draft || typeof draft !== 'object') return false;

            if (draft.po_id) {
                const poSelect = document.getElementById('poSelect');
                if (poSelect) {
                    poSelect.value = draft.po_id;
                    window.onPoSelected(poSelect, draft.podtl_id);
                }
            }
            if (draft.gudang_id) {
                const gSel = document.getElementById('gudangSelect');
                if (gSel) gSel.value = draft.gudang_id;
            }
            if (draft.supplier_id) {
                const sSel = document.getElementById('supplierSelect');
                if (sSel) sSel.value = draft.supplier_id;
            }
            if (draft.surat_jalan_supplier !== undefined) {
                const el = document.getElementById('inputNoSJ');
                if (el) el.value = draft.surat_jalan_supplier;
            }
            if (draft.tgl_periksa) {
                const el = document.getElementById('inputTglDatang');
                if (el) el.value = draft.tgl_periksa;
            }
            if (draft.bp_barang_id) {
                const bSel = document.getElementById('bpBarangSelect');
                if (bSel) {
                    bSel.value = draft.bp_barang_id;
                    window.onBpBarangChanged(bSel);
                }
            }
            if (draft.nama_jenis !== undefined) {
                const el = document.getElementById('bpNamaJenisInput');
                if (el) el.value = draft.nama_jenis;
            }
            if (draft.nama_produsen !== undefined) {
                const el = document.getElementById('inputNamaProdusen');
                if (el) el.value = draft.nama_produsen;
            }
            if (draft.negara_produsen !== undefined) {
                const el = document.getElementById('inputNegaraProdusen');
                if (el) el.value = draft.negara_produsen;
            }
            if (draft.plat_nomor_truk !== undefined) {
                const el = document.getElementById('inputPlatNomor');
                if (el) el.value = draft.plat_nomor_truk;
            }
            if (draft.sopir_nama !== undefined) {
                const el = document.getElementById('inputNamaSupir');
                if (el) el.value = draft.sopir_nama;
            }
            if (draft.jumlah_surat_jalan !== undefined) {
                const el = document.getElementById('inputJumlahSJ');
                if (el) el.value = draft.jumlah_surat_jalan;
            }
            if (draft.jumlah_di_pabrik !== undefined) {
                const el = document.getElementById('inputJumlahPabrik');
                if (el) el.value = draft.jumlah_di_pabrik;
            }
            if (draft.jumlah_sample_gr !== undefined) {
                const el = document.getElementById('inputJumlahSampleGr');
                if (el) el.value = draft.jumlah_sample_gr;
            }

            if (draft.bebas_cemaran_st !== undefined) {
                const radio = document.querySelector(`input[name="bebas_cemaran_st"][value="${draft.bebas_cemaran_st}"]`);
                if (radio) {
                    radio.checked = true;
                    updateTransportCard(radio);
                }
            }

            if (draft.angkut_barang_haram_st !== undefined) {
                window.setHalalToggle('angkut_barang_haram', parseInt(draft.angkut_barang_haram_st, 10));
            }
            if (draft.terdaftar_lppom_st !== undefined) {
                window.setHalalToggle('terdaftar_lppom', parseInt(draft.terdaftar_lppom_st, 10));
            }
            if (draft.ada_sertifikat_halal_st !== undefined) {
                window.setHalalToggle('ada_sertifikat_halal', parseInt(draft.ada_sertifikat_halal_st, 10));
            }
            if (draft.sertifikat_halal_berlaku_st !== undefined) {
                window.setHalalToggle('sertifikat_halal_berlaku', parseInt(draft.sertifikat_halal_berlaku_st, 10));
            }

            if (draft.komentar_transportasi !== undefined) {
                const el = document.getElementById('inputKomentarTransportasi');
                if (el) el.value = draft.komentar_transportasi;
            }
            if (draft.komentar_lppom !== undefined) {
                const el = document.getElementById('inputKomentarLppom');
                if (el) el.value = draft.komentar_lppom;
            }
            if (draft.komentar_sertifikat !== undefined) {
                const el = document.getElementById('inputKomentarSertifikat');
                if (el) el.value = draft.komentar_sertifikat;
            }
            if (draft.komentar_berlaku !== undefined) {
                const el = document.getElementById('inputKomentarBerlaku');
                if (el) el.value = draft.komentar_berlaku;
            }

            if (draft.bp_status_raw_material) {
                window.setRawMaterialStatus(draft.bp_status_raw_material);
            }
            if (draft.bp_kemasan_kondisi) {
                window.setKemasanKondisi(draft.bp_kemasan_kondisi);
            }

            const applyCheck = (id, val) => {
                const chk = document.getElementById(id);
                if (chk && val !== undefined) {
                    chk.checked = !!parseInt(val, 10);
                    window.onModernCheckboxChanged(chk);
                }
            };
            applyCheck('checkIsiKering', draft.bp_isi_kering);
            applyCheck('checkIsiBasah', draft.bp_isi_basah);
            applyCheck('checkIsiGumpal', draft.bp_isi_gumpal);
            applyCheck('checkIsiBerminyak', draft.bp_isi_berminyak);
            applyCheck('checkKemasanKotor', draft.bp_kemasan_kotor);
            applyCheck('checkKemasanApek', draft.bp_kemasan_apek);
            applyCheck('checkKemasanJamur', draft.bp_kemasan_jamur);
            applyCheck('checkKemasanSobek', draft.bp_kemasan_sobek);

            if (draft.bp_qty_gross !== undefined) {
                const el = document.getElementById('bpQtyGross');
                if (el) el.value = draft.bp_qty_gross;
            }
            if (draft.bp_qty_reject !== undefined) {
                const el = document.getElementById('bpQtyReject');
                if (el) el.value = draft.bp_qty_reject;
            }
            if (draft.bp_kesimpulan) {
                window.setKesimpulanBp(draft.bp_kesimpulan);
            }
            if (draft.bp_komentar !== undefined) {
                const el = document.getElementById('inputBpKomentar');
                if (el) el.value = draft.bp_komentar;
            }
            if (draft.petugas_qc_nama !== undefined) {
                const el = document.getElementById('inputPetugasQc');
                if (el) el.value = draft.petugas_qc_nama;
            }
            if (draft.qc_supervisor_nama !== undefined) {
                const el = document.getElementById('inputSupervisorQc');
                if (el) el.value = draft.qc_supervisor_nama;
            }

            syncBpQuantity();
            updateTab2InfoStrip();

            return true;
        } catch (e) {
            console.warn('[QC Bahan Penolong] Gagal memulihkan draft:', e);
            return false;
        }
    }

    // 13. Inisialisasi Saat Halaman Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        startLiveInspectionClock();

        const form = document.getElementById('qcBpForm');
        if (form) {
            form.addEventListener('input', saveBpDraft);
            form.addEventListener('change', saveBpDraft);
        }

        const inputGross = document.getElementById('bpQtyGross');
        const inputReject = document.getElementById('bpQtyReject');
        const inputPabrik = document.getElementById('inputJumlahPabrik');
        const inputSJ = document.getElementById('inputJumlahSJ');

        if (inputGross) inputGross.addEventListener('input', syncBpQuantity);
        if (inputReject) inputReject.addEventListener('input', syncBpQuantity);
        if (inputPabrik) inputPabrik.addEventListener('input', syncBpQuantity);
        if (inputSJ) inputSJ.addEventListener('input', function () {
            if (inputPabrik && (!inputPabrik.value || inputPabrik.value == 0)) {
                inputPabrik.value = this.value;
                syncBpQuantity();
            }
        });

        const jInput = document.getElementById('bpNamaJenisInput');
        if (jInput) jInput.addEventListener('input', updateTab2InfoStrip);

        // Pulihkan draft formulir jika ada di sessionStorage
        const hasDraft = restoreBpDraft();

        if (!hasDraft) {
            const poSelect = document.getElementById('poSelect');
            if (poSelect && poSelect.value) {
                window.onPoSelected(poSelect);
            } else {
                const bSel = document.getElementById('bpBarangSelect');
                if (bSel) window.onBpBarangChanged(bSel);
            }
        }

        // Tentukan tab aktif terakhir (URL Hash > sessionStorage > Tab 1)
        let activeTab = 1;
        const hashMatch = window.location.hash.match(/tab(\d+)/);
        if (hashMatch) {
            activeTab = parseInt(hashMatch[1], 10);
        } else {
            const savedTab = sessionStorage.getItem(ACTIVE_TAB_KEY);
            if (savedTab) activeTab = parseInt(savedTab, 10);
        }
        window.switchBpTab(activeTab || 1, true);

        console.log('[QC Mobile] Dedicated Bahan Penolong (MSG/Garam/Perenyah/Bumbu) module loaded successfully.');
    });
})();
