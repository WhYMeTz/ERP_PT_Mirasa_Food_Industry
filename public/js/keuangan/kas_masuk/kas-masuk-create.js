/**
 * Modul Keuangan: Bukti Kas Masuk (BKM) - Smart Adaptive Form JS
 * PT Mirasa Food Industry - Enterprise ERP (PO Design System Standard)
 * 100% Selaras PO Design System: Bebas Warna Hijau, Monokromatik Slate Halus
 * Fitur Utama:
 * 1. Smart Category Switching (Single Direct Entry vs Multi-Item Table)
 * 2. Quick Presets (+ Minyak Jelantah, + Kulit Singkong, + Remah Keripik)
 * 3. Real-time Thousand Separators & Indonesian Terbilang Engine
 * 4. Progressive Disclosure Timbangan (Kg x Harga = Subtotal)
 * 5. Full Keyboard Navigation & Ctrl + Enter Shortcut
 */

let nextFocusRowIndex = 1;

document.addEventListener('DOMContentLoaded', function () {
    const existingCards = document.querySelectorAll('.bkm-item-card');
    nextFocusRowIndex = existingCards.length;

    initCategoryCards();
    initBankSelect();
    initSingleEntryListeners();
    initKeyboardShortcuts();
    recalcAllNominals();
    
    // Initial sync state based on pre-selected category
    const selectedRadio = document.querySelector('input[name="kategori_penerimaan"]:checked');
    if (selectedRadio) {
        syncWorkstationMode(selectedRadio.value, false);
    } else {
        updateGrandTotal();
    }
});

/**
 * Inisialisasi Pilihan Kategori Penerimaan (Segmented Cards)
 */
function initCategoryCards() {
    const cards = document.querySelectorAll('.category-card-option');
    cards.forEach(card => {
        card.addEventListener('click', function () {
            cards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                onCategoryChanged(radio.value);
            }
        });
    });
}

/**
 * Event Handler Saat Kategori Berubah
 */
function onCategoryChanged(kategori) {
    const penyetorInput = document.getElementById('penyetor_nama');
    const metodeSelect = document.getElementById('metode_terima');
    const singleNominalInput = document.getElementById('single_nominal');
    const singleDescInput = document.getElementById('single_deskripsi');
    const firstUraianInput = document.querySelector('#focusItemsList .item-uraian');

    // 1. Sinkronisasi Field Sesuai Kategori Bisnis
    if (kategori === 'SETORAN_MODAL') {
        if (penyetorInput) {
            penyetorInput.value = 'Owner / Pemilik PT Mirasa';
        }
        if (metodeSelect) {
            metodeSelect.value = 'TRANSFER';
        }
        if (singleDescInput) {
            singleDescInput.value = 'Injeksi modal kerja operasional pemilik';
        }
        syncWorkstationMode('SETORAN_MODAL', true);
        if (singleNominalInput) singleNominalInput.focus();

    } else if (kategori === 'PENDAPATAN_LAIN') {
        if (metodeSelect) {
            metodeSelect.value = 'TRANSFER';
        }
        if (singleDescInput && (!singleDescInput.value || singleDescInput.value === 'Injeksi modal kerja operasional pemilik')) {
            singleDescInput.value = 'Penerimaan bunga bank / pendapatan lain';
        }
        syncWorkstationMode('PENDAPATAN_LAIN', true);
        if (singleNominalInput) singleNominalInput.focus();

    } else if (kategori === 'PENJUALAN_LIMBAH') {
        if (penyetorInput && (penyetorInput.value === 'Owner / Pemilik PT Mirasa' || penyetorInput.value === 'Pembeli Eceran / Tamu Pabrik')) {
            penyetorInput.value = '';
            penyetorInput.placeholder = 'Nama pengepul / pembeli limbah pabrik...';
        }
        if (firstUraianInput && (!firstUraianInput.value || firstUraianInput.value.includes('Injeksi modal') || firstUraianInput.value.includes('bunga bank'))) {
            firstUraianInput.value = 'Penjualan Minyak Jelantah / Limbah Pabrik';
        }
        syncWorkstationMode('PENJUALAN_LIMBAH', true);

    } else if (kategori === 'PENJUALAN_TUNAI') {
        if (penyetorInput && (!penyetorInput.value || penyetorInput.value === 'Owner / Pemilik PT Mirasa')) {
            penyetorInput.value = 'Pembeli Eceran / Tamu Pabrik';
        }
        if (metodeSelect) {
            metodeSelect.value = 'CASH';
        }
        if (firstUraianInput && (!firstUraianInput.value || firstUraianInput.value.includes('Injeksi modal') || firstUraianInput.value.includes('Minyak Jelantah'))) {
            firstUraianInput.value = 'Penjualan Keripik Eceran / Afkir';
        }
        syncWorkstationMode('PENJUALAN_TUNAI', true);
    }
}

/**
 * Mengatur Mode Workstation (Mode 1: Single Direct Entry vs Mode 2: Multi-Item Table)
 */
function syncWorkstationMode(kategori, shouldRecalc = true) {
    const singlePanel = document.getElementById('singleEntryWorkstation');
    const multiPanel = document.getElementById('multiItemWorkstation');
    const mode1Fieldset = document.getElementById('mode1Fieldset');
    const mode2Fieldset = document.getElementById('mode2Fieldset');

    const isSingleMode = (kategori === 'SETORAN_MODAL' || kategori === 'PENDAPATAN_LAIN');

    if (isSingleMode) {
        if (singlePanel) singlePanel.style.display = 'block';
        if (multiPanel) multiPanel.style.display = 'none';
        if (mode1Fieldset) mode1Fieldset.disabled = false;
        if (mode2Fieldset) mode2Fieldset.disabled = true;
    } else {
        if (singlePanel) singlePanel.style.display = 'none';
        if (multiPanel) multiPanel.style.display = 'block';
        if (mode1Fieldset) mode1Fieldset.disabled = true;
        if (mode2Fieldset) mode2Fieldset.disabled = false;
    }

    if (shouldRecalc) {
        updateGrandTotal();
    }
}

/**
 * Inisialisasi Event Listener untuk Single Direct Entry
 */
function initSingleEntryListeners() {
    const singleNominal = document.getElementById('single_nominal');
    const singleDeskripsi = document.getElementById('single_deskripsi');
    const mode1Nominal = document.getElementById('mode1_nominal_input');
    const mode1Deskripsi = document.getElementById('mode1_deskripsi_input');

    if (singleNominal) {
        singleNominal.addEventListener('input', function () {
            formatNominalInput(this);
            const raw = parseNominal(this.value);
            if (mode1Nominal) {
                mode1Nominal.value = raw > 0 ? raw : '';
            }
            updateGrandTotal();
        });
    }

    if (singleDeskripsi) {
        singleDeskripsi.addEventListener('input', function () {
            if (mode1Deskripsi) {
                mode1Deskripsi.value = this.value;
            }
        });
    }
}

/**
 * Preset Cepat untuk Baris Multi-Item (+ Minyak Jelantah, + Kulit Singkong, + Remah Keripik)
 */
function applyItemPreset(nama, satuan = 'kg') {
    const container = document.getElementById('focusItemsList');
    if (!container) return;

    const cards = container.querySelectorAll('.bkm-item-card');
    let targetCard = null;

    // Cari baris pertama yang masih kosong uraiannya
    for (let card of cards) {
        const input = card.querySelector('.item-uraian');
        if (input && (!input.value || input.value.trim() === '')) {
            targetCard = card;
            break;
        }
    }

    if (!targetCard && cards.length === 1) {
        // Jika hanya ada 1 baris awal dan isinya belum diisi nominal
        const firstNominal = cards[0].querySelector('.item-nominal');
        if (firstNominal && parseNominal(firstNominal.value) === 0) {
            targetCard = cards[0];
        }
    }

    if (targetCard) {
        const uraianInput = targetCard.querySelector('.item-uraian');
        const satuanInput = targetCard.querySelector('.item-satuan');
        const nominalInput = targetCard.querySelector('.item-nominal');
        if (uraianInput) uraianInput.value = nama;
        if (satuanInput) satuanInput.value = satuan;
        if (nominalInput) nominalInput.focus();
    } else {
        // Buat baris baru dengan preset
        addItemRowWithPreset(nama, satuan);
    }
}

/**
 * Tambah Baris Baru dengan Preset Otomatis
 */
function addItemRowWithPreset(nama, satuan = 'kg') {
    const container = document.getElementById('focusItemsList');
    if (!container) return;

    const idx = nextFocusRowIndex++;
    const rowNum = container.querySelectorAll('.bkm-item-card').length + 1;

    const card = document.createElement('div');
    card.className = 'bkm-item-card';
    card.id = `itemCard_${idx}`;
    card.setAttribute('data-row-index', idx);

    card.innerHTML = `
        <div class="bkm-item-main-row">
            <div class="bkm-item-num">${rowNum}</div>

            <div class="bkm-item-col-uraian">
                <label class="bkm-mobile-label" for="item_uraian_${idx}">Uraian <span class="req-star">*</span></label>
                <input type="text"
                       id="item_uraian_${idx}"
                       name="items[${idx}][deskripsi]"
                       class="form-control-item item-uraian"
                       value="${nama}"
                       placeholder="Ketik keterangan barang..."
                       required>

                <div class="bkm-item-sub-actions">
                    <button type="button" class="btn-toggle-qty" onclick="toggleQtyPanel(${idx})" aria-expanded="false">
                        <svg class="toggle-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <span>Rincian Timbangan (Kg x Harga)</span>
                    </button>
                </div>

                <div class="bkm-item-expandable-panel" id="qtyPanel_${idx}" style="display: none;">
                    <div class="bkm-details-grid">
                        <div class="detail-field">
                            <label class="detail-label" for="item_qty_${idx}">Kuantitas (Qty)</label>
                            <input type="number"
                                   step="any"
                                   min="0.01"
                                   id="item_qty_${idx}"
                                   name="items[${idx}][qty]"
                                   class="form-control-detail item-qty"
                                   value="1"
                                   oninput="recalcRowTotal(${idx})">
                        </div>
                        <div class="detail-field">
                            <label class="detail-label" for="item_satuan_${idx}">Satuan</label>
                            <input type="text"
                                   id="item_satuan_${idx}"
                                   name="items[${idx}][satuan]"
                                   class="form-control-detail item-satuan"
                                   value="${satuan}"
                                   placeholder="kg/kaleng">
                        </div>
                        <div class="detail-field">
                            <label class="detail-label" for="item_harga_${idx}">Harga Satuan (Rp)</label>
                            <input type="number"
                                   step="any"
                                   min="0"
                                   id="item_harga_${idx}"
                                   name="items[${idx}][harga_satuan]"
                                   class="form-control-detail item-harga"
                                   placeholder="0"
                                   oninput="recalcRowTotal(${idx})">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bkm-item-col-nominal">
                <label class="bkm-mobile-label" for="item_nominal_${idx}">Nominal (Rp) <span class="req-star">*</span></label>
                <div class="bkm-nominal-input-wrap">
                    <span class="nominal-prefix" aria-hidden="true">Rp</span>
                    <input type="text"
                           id="item_nominal_${idx}"
                           name="items[${idx}][subtotal]"
                           class="form-control-nominal item-nominal"
                           placeholder="0"
                           required
                           oninput="formatNominalInput(this); updateGrandTotal();">
                </div>
            </div>

            <div class="bkm-item-col-action">
                <button type="button"
                        class="btn-delete-row"
                        onclick="removeFocusRow(${idx})"
                        title="Hapus baris ini"
                        aria-label="Hapus baris ${rowNum}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>
    `;

    container.appendChild(card);
    const nominalInput = card.querySelector('.item-nominal');
    if (nominalInput) nominalInput.focus();
    updateGrandTotal();
}

/**
 * Tambah Baris Multi-Item Kosong
 */
function addItemRow() {
    const container = document.getElementById('focusItemsList');
    if (!container) return;

    const idx = nextFocusRowIndex++;
    const rowNum = container.querySelectorAll('.bkm-item-card').length + 1;

    const card = document.createElement('div');
    card.className = 'bkm-item-card';
    card.id = `itemCard_${idx}`;
    card.setAttribute('data-row-index', idx);

    card.innerHTML = `
        <div class="bkm-item-main-row">
            <div class="bkm-item-num">${rowNum}</div>

            <div class="bkm-item-col-uraian">
                <label class="bkm-mobile-label" for="item_uraian_${idx}">Uraian <span class="req-star">*</span></label>
                <input type="text"
                       id="item_uraian_${idx}"
                       name="items[${idx}][deskripsi]"
                       class="form-control-item item-uraian"
                       placeholder="Ketik keterangan barang..."
                       required>

                <div class="bkm-item-sub-actions">
                    <button type="button" class="btn-toggle-qty" onclick="toggleQtyPanel(${idx})" aria-expanded="false">
                        <svg class="toggle-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <span>Rincian Timbangan (Kg x Harga)</span>
                    </button>
                </div>

                <div class="bkm-item-expandable-panel" id="qtyPanel_${idx}" style="display: none;">
                    <div class="bkm-details-grid">
                        <div class="detail-field">
                            <label class="detail-label" for="item_qty_${idx}">Kuantitas (Qty)</label>
                            <input type="number"
                                   step="any"
                                   min="0.01"
                                   id="item_qty_${idx}"
                                   name="items[${idx}][qty]"
                                   class="form-control-detail item-qty"
                                   value="1"
                                   oninput="recalcRowTotal(${idx})">
                        </div>
                        <div class="detail-field">
                            <label class="detail-label" for="item_satuan_${idx}">Satuan</label>
                            <input type="text"
                                   id="item_satuan_${idx}"
                                   name="items[${idx}][satuan]"
                                   class="form-control-detail item-satuan"
                                   value="kg"
                                   placeholder="kg/kaleng">
                        </div>
                        <div class="detail-field">
                            <label class="detail-label" for="item_harga_${idx}">Harga Satuan (Rp)</label>
                            <input type="number"
                                   step="any"
                                   min="0"
                                   id="item_harga_${idx}"
                                   name="items[${idx}][harga_satuan]"
                                   class="form-control-detail item-harga"
                                   placeholder="0"
                                   oninput="recalcRowTotal(${idx})">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bkm-item-col-nominal">
                <label class="bkm-mobile-label" for="item_nominal_${idx}">Nominal (Rp) <span class="req-star">*</span></label>
                <div class="bkm-nominal-input-wrap">
                    <span class="nominal-prefix" aria-hidden="true">Rp</span>
                    <input type="text"
                           id="item_nominal_${idx}"
                           name="items[${idx}][subtotal]"
                           class="form-control-nominal item-nominal"
                           placeholder="0"
                           required
                           oninput="formatNominalInput(this); updateGrandTotal();">
                </div>
            </div>

            <div class="bkm-item-col-action">
                <button type="button"
                        class="btn-delete-row"
                        onclick="removeFocusRow(${idx})"
                        title="Hapus baris ini"
                        aria-label="Hapus baris ${rowNum}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>
    `;

    container.appendChild(card);
    const inputBaru = card.querySelector('.item-uraian');
    if (inputBaru) inputBaru.focus();

    updateGrandTotal();
}

/**
 * Hapus Baris Multi-Item
 */
function removeFocusRow(idx) {
    const card = document.getElementById(`itemCard_${idx}`);
    if (!card) return;

    const container = document.getElementById('focusItemsList');
    if (container && container.querySelectorAll('.bkm-item-card').length <= 1) {
        // Jangan hapus jika hanya 1 baris, bersihkan saja isinya
        const uraian = card.querySelector('.item-uraian');
        const nominal = card.querySelector('.item-nominal');
        const harga = card.querySelector('.item-harga');
        const qty = card.querySelector('.item-qty');
        if (uraian) uraian.value = '';
        if (nominal) nominal.value = '';
        if (harga) harga.value = '';
        if (qty) qty.value = '1';
        updateGrandTotal();
        return;
    }

    card.remove();

    // Renumber item numbers
    if (container) {
        container.querySelectorAll('.bkm-item-card').forEach((c, i) => {
            const numEl = c.querySelector('.bkm-item-num');
            if (numEl) numEl.textContent = i + 1;
        });
    }

    updateGrandTotal();
}

/**
 * Toggle Expandable Qty & Satuan Panel
 */
function toggleQtyPanel(idx) {
    const panel = document.getElementById(`qtyPanel_${idx}`);
    const btn = document.querySelector(`#itemCard_${idx} .btn-toggle-qty`);
    const icon = btn ? btn.querySelector('.toggle-icon') : null;

    if (!panel) return;

    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
        if (btn) btn.setAttribute('aria-expanded', 'true');
        if (icon) icon.classList.add('is-rotated');
        const qtyInput = document.getElementById(`item_qty_${idx}`);
        if (qtyInput) qtyInput.focus();
    } else {
        panel.style.display = 'none';
        if (btn) btn.setAttribute('aria-expanded', 'false');
        if (icon) icon.classList.remove('is-rotated');
    }
}

/**
 * Hitung Ulang Subtotal Baris Dari Qty x Harga
 */
function recalcRowTotal(idx) {
    const qtyInput = document.getElementById(`item_qty_${idx}`);
    const hargaInput = document.getElementById(`item_harga_${idx}`);
    const nominalInput = document.getElementById(`item_nominal_${idx}`);

    const qty = parseFloat(qtyInput?.value || 0);
    const harga = parseFloat(hargaInput?.value || 0);

    if (qty > 0 && harga > 0 && nominalInput) {
        const subtotal = Math.round(qty * harga);
        nominalInput.value = subtotal.toLocaleString('id-ID');
        updateGrandTotal();
    }
}

/**
 * Inisialisasi Rekening Kas / Bank Tujuan & Monitoring Saldo
 */
function initBankSelect() {
    const bankSelect = document.getElementById('kas_bank_id');
    const liveSaldo = document.getElementById('liveSaldoDisplay');
    const dockAccount = document.getElementById('dockAccountDisplay');

    function updateBankInfo() {
        if (!bankSelect) return;
        const opt = bankSelect.options[bankSelect.selectedIndex];
        if (opt && opt.value) {
            const saldo = parseFloat(opt.getAttribute('data-saldo') || 0);
            if (liveSaldo) {
                liveSaldo.textContent = 'Rp ' + Math.round(saldo).toLocaleString('id-ID');
            }
            if (dockAccount) {
                dockAccount.textContent = opt.textContent.trim();
            }
        } else {
            if (liveSaldo) liveSaldo.textContent = 'Rp 0';
            if (dockAccount) dockAccount.textContent = 'Belum memilih rekening kas/bank';
        }
    }

    if (bankSelect) {
        bankSelect.addEventListener('change', updateBankInfo);
        updateBankInfo();
    }
}

/**
 * Format Input Nominal (Thousand Separator)
 */
function formatNominalInput(input) {
    let clean = input.value.replace(/\D/g, '');
    if (clean === '') {
        input.value = '';
    } else {
        input.value = parseInt(clean, 10).toLocaleString('id-ID');
    }
}

function parseNominal(val) {
    if (!val) return 0;
    const clean = val.toString().replace(/\./g, '').replace(/\D/g, '');
    return clean ? parseInt(clean, 10) : 0;
}

function recalcAllNominals() {
    // Single entry nominal
    const singleNominal = document.getElementById('single_nominal');
    if (singleNominal && singleNominal.value) {
        const raw = parseNominal(singleNominal.value);
        if (raw > 0) singleNominal.value = raw.toLocaleString('id-ID');
    }

    // Multi-item nominals
    const nominalInputs = document.querySelectorAll('.item-nominal');
    nominalInputs.forEach(input => {
        if (input.value) {
            const raw = parseNominal(input.value);
            if (raw > 0) {
                input.value = raw.toLocaleString('id-ID');
            }
        }
    });
}

/**
 * Hitung Grand Total & Update Sticky Dock
 */
function updateGrandTotal() {
    let grandTotal = 0;
    const singlePanel = document.getElementById('singleEntryWorkstation');
    const isSingleActive = singlePanel && singlePanel.style.display !== 'none';

    if (isSingleActive) {
        const singleNominal = document.getElementById('single_nominal');
        grandTotal = parseNominal(singleNominal?.value || 0);
    } else {
        const nominalInputs = document.querySelectorAll('#focusItemsList .item-nominal');
        nominalInputs.forEach(input => {
            const val = parseNominal(input.value);
            if (val > 0) {
                grandTotal += val;
            }
        });
    }

    const dockTotal = document.getElementById('dockTotalAmount');
    const dockTerbilang = document.getElementById('dockTerbilang');

    if (dockTotal) {
        dockTotal.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    }

    if (dockTerbilang) {
        if (grandTotal > 0) {
            dockTerbilang.textContent = `# ${terbilangClient(grandTotal)} Rupiah #`;
        } else {
            dockTerbilang.textContent = '# Nol Rupiah #';
        }
    }
}

/**
 * Helper Terbilang Bahasa Indonesia
 */
function terbilangClient(nilai) {
    nilai = Math.floor(Math.abs(nilai));
    const huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

    if (nilai < 12) {
        return huruf[nilai];
    } else if (nilai < 20) {
        return terbilangClient(nilai - 10) + ' Belas';
    } else if (nilai < 100) {
        return terbilangClient(Math.floor(nilai / 10)) + ' Puluh ' + terbilangClient(nilai % 10);
    } else if (nilai < 200) {
        return 'Seratus ' + terbilangClient(nilai - 100);
    } else if (nilai < 1000) {
        return terbilangClient(Math.floor(nilai / 100)) + ' Ratus ' + terbilangClient(nilai % 100);
    } else if (nilai < 2000) {
        return 'Seribu ' + terbilangClient(nilai - 1000);
    } else if (nilai < 1000000) {
        return terbilangClient(Math.floor(nilai / 1000)) + ' Ribu ' + terbilangClient(nilai % 1000);
    } else if (nilai < 1000000000) {
        return terbilangClient(Math.floor(nilai / 1000000)) + ' Juta ' + terbilangClient(nilai % 1000000);
    } else if (nilai < 1000000000000) {
        return terbilangClient(Math.floor(nilai / 1000000000)) + ' Miliar ' + terbilangClient(nilai % 1000000000);
    } else {
        return terbilangClient(Math.floor(nilai / 1000000000000)) + ' Triliun ' + terbilangClient(nilai % 1000000000000);
    }
}

/**
 * Inisialisasi Tombol Submit & Shortcut Keyboard (Ctrl + Enter)
 */
function initKeyboardShortcuts() {
    const form = document.getElementById('formKasMasuk');
    const actionInput = document.getElementById('submitActionInput');
    const btnDraft = document.getElementById('btnSubmitDraft');
    const btnPrint = document.getElementById('btnSubmitPrint');

    function prepareAndSubmit(actionValue) {
        if (!form) return;
        if (actionInput) {
            actionInput.value = actionValue;
        }

        const singlePanel = document.getElementById('singleEntryWorkstation');
        const isSingleActive = singlePanel && singlePanel.style.display !== 'none';
        const mode1Fieldset = document.getElementById('mode1Fieldset');
        const mode2Fieldset = document.getElementById('mode2Fieldset');

        if (isSingleActive) {
            // Mode 1: Single Direct Entry
            if (mode1Fieldset) mode1Fieldset.disabled = false;
            if (mode2Fieldset) mode2Fieldset.disabled = true;

            const singleNominal = document.getElementById('single_nominal');
            const singleDesc = document.getElementById('single_deskripsi');
            const mode1NominalInput = document.getElementById('mode1_nominal_input');
            const mode1DescInput = document.getElementById('mode1_deskripsi_input');

            const rawNominal = parseNominal(singleNominal?.value || 0);
            const descVal = (singleDesc?.value || '').trim();

            if (rawNominal <= 0) {
                if (singleNominal) {
                    singleNominal.focus();
                    singleNominal.setCustomValidity('Nominal penerimaan dana wajib diisi minimal Rp 1.');
                    singleNominal.reportValidity();
                }
                return;
            } else if (singleNominal) {
                singleNominal.setCustomValidity('');
            }

            if (!descVal) {
                if (singleDesc) {
                    singleDesc.focus();
                    singleDesc.setCustomValidity('Keterangan / keperluan penerimaan kas wajib diisi.');
                    singleDesc.reportValidity();
                }
                return;
            } else if (singleDesc) {
                singleDesc.setCustomValidity('');
            }

            if (mode1NominalInput) mode1NominalInput.value = rawNominal;
            if (mode1DescInput) mode1DescInput.value = descVal;

        } else {
            // Mode 2: Multi-Item Table
            if (mode1Fieldset) mode1Fieldset.disabled = true;
            if (mode2Fieldset) mode2Fieldset.disabled = false;

            // Bersihkan nominal ribuan dari semua input item
            document.querySelectorAll('#focusItemsList .item-nominal').forEach(input => {
                input.value = parseNominal(input.value);
            });
        }

        if (form.reportValidity && !form.reportValidity()) {
            // Jika ada field required lain (misal kas bank / tanggal) yang kosong, format kembali nominal
            recalcAllNominals();
            return;
        }

        form.submit();
    }

    if (btnDraft) {
        btnDraft.addEventListener('click', function (e) {
            e.preventDefault();
            prepareAndSubmit('save');
        });
    }

    if (btnPrint) {
        btnPrint.addEventListener('click', function (e) {
            e.preventDefault();
            prepareAndSubmit('save_and_print');
        });
    }

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            prepareAndSubmit('save_and_print');
        }
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            const singlePanel = document.getElementById('singleEntryWorkstation');
            const isSingleActive = singlePanel && singlePanel.style.display !== 'none';
            if (!isSingleActive) {
                document.querySelectorAll('#focusItemsList .item-nominal').forEach(input => {
                    input.value = parseNominal(input.value);
                });
            }
        });
    }
}
