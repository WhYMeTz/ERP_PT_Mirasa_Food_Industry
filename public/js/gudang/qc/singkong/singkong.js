/**
 * QC INBOUND - MODUL KOMODITAS: SINGKONG
 * PT Mirasa Food Industry
 * Logika Kartu Inspeksi, Kalkulasi Timbangan, Pengujian Fryer & PO Multi-Item Selector
 * Tema Visual: Clean Sky Blue (#0284c7)
 */

(function () {
    'use strict';

    let originalBarangOptionsHTML = null;
    let currentPodtlId = null;

    window.onGradeChanged = function (idx, grade) {
        const warnEl = document.getElementById(`gradeB_warning_${idx}`);
        if (warnEl) {
            warnEl.style.display = (grade === 'B') ? 'block' : 'none';
        }
    };

    window.quickRejectGradeB = function (idx) {
        if (typeof window.selectDisposisi === 'function') {
            window.selectDisposisi('TOLAK');
        } else {
            const radioTolak = document.getElementById('radioTolak');
            if (radioTolak) {
                radioTolak.checked = true;
                if (typeof window.onKesimpulanChange === 'function') {
                    window.onKesimpulanChange('TOLAK');
                }
            }
        }
        if (typeof window.showQcToast === 'function') {
            const cardTolak = document.getElementById('cardDisposisiTolak') || document.getElementById('radioTolak');
            window.showQcToast('Truk Singkong Ditolak', 'Keputusan dialihkan ke TOLAK TOTAL karena stok Grade B gudang sudah penuh.', cardTolak, 3, true);
        }
    };

    window.calculateCard = function (idx) {
        const grossInput = document.getElementById(`gross_${idx}`);
        const refraksiInput = document.getElementById(`refraksi_${idx}`);
        const rejectInput = document.getElementById(`reject_${idx}`);
        const labelRefraksi = document.getElementById(`label_qty_refraksi_${idx}`) || document.getElementById(`potongan_kg_display_${idx}`);
        const labelNetto = document.getElementById(`label_netto_${idx}`) || document.getElementById(`netto_display_${idx}`);

        if (!grossInput) return;

        const gross = parseFloat(grossInput.value) || 0;
        const refraksiPersen = parseFloat(refraksiInput ? refraksiInput.value : 0) || 0;
        const reject = parseFloat(rejectInput ? rejectInput.value : 0) || 0;

        const potonganKg = (gross * refraksiPersen) / 100;
        let netto = gross - potonganKg - reject;
        if (netto < 0) netto = 0;

        if (labelRefraksi) {
            labelRefraksi.innerText = `${potonganKg.toFixed(2)} KG`;
        }
        if (labelNetto) {
            labelNetto.innerText = `${netto.toFixed(2)} KG`;
        }

        if (idx === 0) {
            const inputPabrik = document.getElementById('inputJumlahPabrik');
            if (inputPabrik && gross > 0 && document.activeElement === grossInput) {
                inputPabrik.value = gross;
            }
        }

        if (typeof window.calculateAll === 'function') {
            window.calculateAll();
        } else if (typeof window.updateTotalCalculations === 'function') {
            window.updateTotalCalculations();
        }
    };

    window.updateTotalCalculations = function () {
        if (typeof window.calculateAll === 'function') {
            window.calculateAll();
        }
    };

    window.onItemBarangChanged = function (idx) {
        const sel = document.getElementById(`barang_select_${idx}`);
        if (!sel) return;
        const opt = sel.selectedOptions ? sel.selectedOptions[0] : (sel.options ? sel.options[sel.selectedIndex] : null);
        const nama = (opt ? opt.getAttribute('data-nama') : null) || sel.getAttribute('data-nama') || sel.dataset?.nama || '';
        const hiddenNama = document.getElementById(`nama_jenis_${idx}`);
        if (hiddenNama && nama) hiddenNama.value = nama;
    };

    // =========================================================================
    // PO MULTI-ITEM SELECTOR UNTUK SINGKONG
    // =========================================================================
    window.renderSingkongPoItemsCards = function (items, selectedPodtlId) {
        const box = document.getElementById('boxPoItemsSelector');
        const list = document.getElementById('poItemsCardsList');
        const badgeCount = document.getElementById('countPoItemsBadge');
        const bSel = document.getElementById('singkongBarangSelect');

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

        // Tentukan podtl_id aktif:
        // Prioritaskan item yang BELUM SELESAI diuji QC!
        let activeId = selectedPodtlId;
        if (!activeId || !items.some(it => String(it.podtl_id) === String(activeId))) {
            const unTested = items.find(it => !it.is_selesai_qc && (typeof it.sisa_belum_qc === 'undefined' || it.sisa_belum_qc > 0));
            activeId = unTested ? unTested.podtl_id : items[0].podtl_id;
        }
        currentPodtlId = activeId;
        const hiddenPodtl = document.getElementById('inputSelectedPodtlId');
        if (hiddenPodtl) hiddenPodtl.value = activeId;

        // Filter dropdown singkongBarangSelect agar berisi item-item PO ini
        if (bSel) {
            bSel.innerHTML = '';
            items.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it.barang_id;
                const sisa = (typeof it.sisa_belum_qc !== 'undefined') ? it.sisa_belum_qc : (it.sisa_qty > 0 ? it.sisa_qty : it.pesan_qty);
                const statusTag = it.is_selesai_qc ? '[SELESAI DIUJI] ' : (it.qc_tested_qty > 0 ? '[UJI SEBAGIAN] ' : '');
                opt.text = `${statusTag}${it.barang_nm} • Sisa Belum QC: ${sisa.toLocaleString('id-ID')} ${it.satuan || 'KG'}`;
                opt.setAttribute('data-nama', it.barang_nm);
                opt.setAttribute('data-satuan', it.satuan || 'KG');
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
                    window.selectSingkongPoItem(it.podtl_id);
                };

                let radioCircleHtml = '<span class="po-item-radio-circle"></span>';
                let badgeHtml = '';
                let subHtml = `Pesan: ${it.pesan_qty.toLocaleString('id-ID')} ${it.satuan || 'KG'}`;

                if (isCompleted) {
                    radioCircleHtml = '<span class="po-item-check-icon" style="color: #16a34a; font-weight: 900; font-size: 1.1rem; width: 20px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">✓</span>';
                    badgeHtml = `
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                            <span class="po-item-badge-sisa badge-completed" style="background: #dcfce7; border-color: #86efac; color: #166534; font-weight: 800;">
                                ✅ Selesai Diuji
                            </span>
                            <span style="font-size: 0.68rem; color: #16a34a; font-weight: 700;">Diuji: ${it.qc_tested_qty.toLocaleString('id-ID')} ${it.satuan || 'KG'}</span>
                        </div>
                    `;
                    subHtml += ` • <strong style="color: #16a34a;">Sudah Diuji: ${it.qc_tested_qty.toLocaleString('id-ID')} ${it.satuan || 'KG'}</strong>`;
                } else if (isPartial) {
                    badgeHtml = `
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                            <span class="po-item-badge-sisa badge-partial" style="background: #fef3c7; border-color: #fde68a; color: #b45309; font-weight: 800;">
                                ⚠️ Uji Sebagian
                            </span>
                            <span style="font-size: 0.68rem; color: #b45309; font-weight: 700;">Sisa Belum QC: ${sisa.toLocaleString('id-ID')} ${it.satuan || 'KG'}</span>
                        </div>
                    `;
                    subHtml += ` • <span style="color: #b45309; font-weight: 700;">Sudah Diuji: ${it.qc_tested_qty.toLocaleString('id-ID')} ${it.satuan || 'KG'}</span>`;
                } else {
                    badgeHtml = `
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                            <span class="po-item-badge-sisa badge-pending" style="background: #e0f2fe; border-color: #bae6fd; color: #0284c7; font-weight: 800;">
                                ⏳ Belum Diuji
                            </span>
                            <span style="font-size: 0.68rem; color: #0284c7; font-weight: 700;">Sisa: ${sisa.toLocaleString('id-ID')} ${it.satuan || 'KG'}</span>
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
    };

    window.selectSingkongPoItem = function (podtlId) {
        currentPodtlId = podtlId;
        const hiddenPodtl = document.getElementById('inputSelectedPodtlId');
        if (hiddenPodtl) hiddenPodtl.value = podtlId;

        const poId = document.getElementById('poSelect')?.value;
        const poList = window.qcConfig?.poList || window.appConfig?.poListData || {};
        const poData = poList[poId] || (Array.isArray(poList) ? poList.find(p => String(p?.po_id) === String(poId)) : Object.values(poList).find(p => String(p?.po_id) === String(poId)));
        if (!poData || !poData.items) return;

        const targetItem = poData.items.find(it => String(it.podtl_id) === String(podtlId));
        if (!targetItem) return;

        // Update status aktif pada kartu
        document.querySelectorAll('.po-item-card').forEach(c => c.classList.remove('is-active'));
        const activeCard = document.getElementById(`poItemCard_${podtlId}`);
        if (activeCard) activeCard.classList.add('is-active');

        // Update select barang di header
        const bSel = document.getElementById('singkongBarangSelect');
        if (bSel) {
            bSel.value = targetItem.barang_id;
            window.onSingkongBarangChanged(bSel);
        }

        // Update kuantitas (Hanya auto-fill dari sisa PO jika bukan Pengujian 2)
        const sisaQty = (typeof targetItem.sisa_belum_qc !== 'undefined' && targetItem.sisa_belum_qc > 0)
            ? targetItem.sisa_belum_qc
            : (targetItem.sisa_qty > 0 ? targetItem.sisa_qty : targetItem.pesan_qty);
        const inSJ = document.getElementById('inputJumlahSJ');
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const isUji2Active = (window.currentTahapUji === 2 || !!document.getElementById('parentQcIdInput')?.value);

        if (!isUji2Active) {
            if (inSJ && (!inSJ.value || inSJ.dataset.autoFilled === 'true' || inSJ.value == 0)) {
                inSJ.value = sisaQty;
                inSJ.dataset.autoFilled = 'true';
            }
            if (inPabrik && (!inPabrik.value || inPabrik.dataset.autoFilled === 'true' || inPabrik.value == 0)) {
                inPabrik.value = sisaQty;
                inPabrik.dataset.autoFilled = 'true';
            }
        }

        // Update nama jenis
        const inputNamaJenis = document.getElementById('namaJenisInput');
        if (inputNamaJenis) {
            inputNamaJenis.value = targetItem.barang_nm;
        }

        // Update input hidden podtl_id di dalam items[0]
        let dtlPodtl = document.querySelector('input[name="items[0][podtl_id]"]');
        if (!dtlPodtl) {
            dtlPodtl = document.createElement('input');
            dtlPodtl.type = 'hidden';
            dtlPodtl.name = 'items[0][podtl_id]';
            const qcForm = document.getElementById('qcForm') || document.querySelector('form');
            if (qcForm) qcForm.appendChild(dtlPodtl);
        }
        if (dtlPodtl) dtlPodtl.value = podtlId;

        // Update barang_select_0 di dalam kartu pemeriksaan Tahap 2
        const bSel0 = document.getElementById('barang_select_0');
        if (bSel0) {
            bSel0.value = targetItem.barang_id;
            bSel0.setAttribute('data-nama', targetItem.barang_nm);
            window.onItemBarangChanged(0);
        }

        // Update gross_0
        const gross0 = document.getElementById('gross_0');
        if (gross0) {
            gross0.value = sisaQty;
            window.calculateCard(0);
        }

        if (typeof window.syncQuantityFields === 'function') {
            window.syncQuantityFields();
        }
    };

    // Sinkronisasi saat pilihan di singkongBarangSelect berubah manual
    window.onSingkongBarangSelectChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (!opt) return;

        const podtlId = opt.getAttribute('data-podtl-id');
        if (podtlId) {
            window.selectSingkongPoItem(podtlId);
        }
    };

    // =========================================================================
    // KUNCI FIELD DARI PO (INTEGRITAS DATA PO - SUPPLIER, GUDANG & MASTER BARANG)
    // =========================================================================
    window.togglePoLockFields = function (isLocked, po) {
        const sSelect = document.getElementById('supplierSelect');
        const gSelect = document.getElementById('gudangSelect');
        const bSelect = document.getElementById('singkongBarangSelect');
        const produsenInput = document.getElementById('namaProdusenInput');
        const chipsSupplier = document.getElementById('supplierFilterChips');
        const searchSupplier = document.getElementById('supplierSearchInput');

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
                badgeSupplier.innerText = (po && po.supplier_nm) ? `🔒 Terkunci (${po.supplier_nm})` : '🔒 Terkunci dari PO';
            }
            if (chipsSupplier) chipsSupplier.style.display = 'none';
            if (searchSupplier) searchSupplier.closest('.supplier-search-wrap')?.setAttribute('style', 'display: none;');

            // Lock Gudang
            if (gSelect) {
                gSelect.style.pointerEvents = 'none';
                gSelect.style.backgroundColor = '#f1f5f9';
                gSelect.style.borderColor = '#cbd5e1';
                gSelect.setAttribute('tabindex', '-1');
            }
            if (badgeGudang) badgeGudang.style.display = 'inline-flex';

            // Lock Barang Singkong
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
            if (chipsSupplier) chipsSupplier.style.display = 'flex';
            if (searchSupplier) searchSupplier.closest('.supplier-search-wrap')?.removeAttribute('style');

            // Unlock Gudang
            if (gSelect) {
                gSelect.style.pointerEvents = 'auto';
                gSelect.style.backgroundColor = '#ffffff';
                gSelect.style.borderColor = '#e2e8f0';
                gSelect.removeAttribute('tabindex');
            }
            if (badgeGudang) badgeGudang.style.display = 'none';

            // Unlock Barang Singkong
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

    // Implementasi Mandiri onPoSelected untuk Singkong (Konsisten dengan Minyak, Karton, Plastik & BP)
    window.onPoSelected = function (select) {
        if (!select || !select.value) {
            window.renderSingkongPoItemsCards([], null);
            window.togglePoLockFields(false);
            return;
        }

        const poId = select.value;
        const poList = window.qcConfig?.poList || window.appConfig?.poListData || {};
        const poData = poList[poId] || (Array.isArray(poList) ? poList.find(p => String(p?.po_id) === String(poId)) : Object.values(poList).find(p => String(p?.po_id) === String(poId)));

        if (!poData) {
            window.renderSingkongPoItemsCards([], null);
            window.togglePoLockFields(false);
            return;
        }

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
                if (typeof window.onSupplierSelected === 'function') {
                    window.onSupplierSelected(supSelect);
                }
            }
        }

        // 2. Auto-fill Gudang jika ada
        if (poData.gudang_id) {
            const gudangSelect = document.getElementById('gudangSelect');
            if (gudangSelect) gudangSelect.value = poData.gudang_id;
        }

        // 3. Auto-fill Nama Produsen
        const produsenInput = document.getElementById('namaProdusenInput');
        if (produsenInput && poData.supplier_nm) {
            produsenInput.value = poData.supplier_nm;
        }

        // 4. Kunci / Nonaktifkan Field Master agar Sesuai PO
        window.togglePoLockFields(true, poData);

        // 5. Render Selector Item Barang dari PO
        const unTested = (poData.items || []).find(it => !it.is_selesai_qc && (typeof it.sisa_belum_qc === 'undefined' || it.sisa_belum_qc > 0));
        const activePodtlId = unTested ? unTested.podtl_id : (poData.items?.[0]?.podtl_id ?? null);
        window.renderSingkongPoItemsCards(poData.items || [], activePodtlId);

        // 6. Pilih item pertama / belum selesai secara otomatis
        if (activePodtlId) {
            window.selectSingkongPoItem(activePodtlId);
        }
    };

    // Inisialisasi awal saat DOM dimuat
    document.addEventListener('DOMContentLoaded', function () {
        const bSelInit = document.getElementById('singkongBarangSelect');
        if (bSelInit && !originalBarangOptionsHTML) {
            originalBarangOptionsHTML = bSelInit.innerHTML;
            bSelInit.addEventListener('change', function () {
                window.onSingkongBarangSelectChanged(this);
            });
        }

        const poSel = document.getElementById('poSelect');
        if (poSel && poSel.value) {
            window.onPoSelected(poSel);
        }
    });

    console.log('[QC Modul] Singkong logic with PO Multi-Item Selector loaded successfully.');
})();
