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
        const opt = sel.options[sel.selectedIndex];
        if (opt) {
            const nama = opt.getAttribute('data-nama') || '';
            const hiddenNama = document.getElementById(`nama_jenis_${idx}`);
            if (hiddenNama) hiddenNama.value = nama;
        }
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

        // Tentukan podtl_id aktif
        let activeId = selectedPodtlId;
        if (!activeId || !items.some(it => String(it.podtl_id) === String(activeId))) {
            activeId = items[0].podtl_id;
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
                opt.text = `${it.barang_nm} • Sisa PO: ${it.sisa_qty.toLocaleString('id-ID')} ${it.satuan || 'KG'}`;
                opt.setAttribute('data-nama', it.barang_nm);
                opt.setAttribute('data-satuan', it.satuan || 'KG');
                opt.setAttribute('data-podtl-id', it.podtl_id);
                opt.setAttribute('data-sisa-qty', it.sisa_qty);
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
                const sisa = it.sisa_qty > 0 ? it.sisa_qty : it.pesan_qty;
                const card = document.createElement('div');
                card.className = `po-item-card ${isActive ? 'is-active' : ''}`;
                card.id = `poItemCard_${it.podtl_id}`;
                card.onclick = function () {
                    window.selectSingkongPoItem(it.podtl_id);
                };

                card.innerHTML = `
                    <div class="po-item-card-left">
                        <span class="po-item-radio-circle"></span>
                        <div class="po-item-info">
                            <span class="po-item-nama">${it.barang_nm}</span>
                            <span class="po-item-sub">Pesan: ${it.pesan_qty.toLocaleString('id-ID')} ${it.satuan || 'KG'} • Masuk: ${it.terima_qty.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                    <span class="po-item-badge-sisa">
                        Sisa: ${sisa.toLocaleString('id-ID')} ${it.satuan || 'KG'}
                    </span>
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
        const poData = poList[poId];
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
        const sisaQty = targetItem.sisa_qty > 0 ? targetItem.sisa_qty : targetItem.pesan_qty;
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
        const dtlPodtl = document.querySelector('input[name="items[0][podtl_id]"]');
        if (dtlPodtl) dtlPodtl.value = podtlId;

        // Update barang_select_0 di dalam kartu pemeriksaan Tahap 2
        const bSel0 = document.getElementById('barang_select_0');
        if (bSel0) {
            bSel0.value = targetItem.barang_id;
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
            const poList = window.qcConfig?.poList || window.appConfig?.poListData || {};
            const poData = poList[poSel.value];
            if (poData && poData.items && poData.items.length > 0) {
                const initPodtlId = document.getElementById('inputSelectedPodtlId')?.value || poData.items[0].podtl_id;
                window.renderSingkongPoItemsCards(poData.items, initPodtlId);
            }
        }
    });

    console.log('[QC Modul] Singkong logic with PO Multi-Item Selector loaded successfully.');
})();
