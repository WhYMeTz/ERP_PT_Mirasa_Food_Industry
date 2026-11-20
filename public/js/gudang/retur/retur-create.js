/**
 * ERP PT MIRASA - RETUR PEMBELIAN (OUTBOUND) WORKSTATION ENGINE
 * Standar Antarmuka Terpadu: PO Pembelian & Catat Tagihan Hutang
 */

(function () {
    'use strict';

    let MASTER_BARANG = [];
    let availableBatches = [];
    let rowCounter = 0;
    let currentCategoryFilter = 'ALL';

    document.addEventListener('DOMContentLoaded', function () {
        if (window.returConfig && window.returConfig.masterBarang) {
            MASTER_BARANG = window.returConfig.masterBarang;
        }

        // 1. Inisialisasi Gudang & Batch
        onGudangChanged(function () {
            const poSelect = document.getElementById('poSelect');
            if (poSelect && poSelect.value) {
                onPoSelected();
            } else if (document.getElementById('itemTableBody').children.length === 0) {
                addRow();
            }
        });

        // 2. Inisialisasi Switcher & Filter
        onSourceTypeChanged();
        onTindakanChanged();

        // 3. Form Submit Guard (Cegah Double Submit)
        const form = document.getElementById('returForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const btnSubmit = document.getElementById('btnSubmitRetur');
                const tbody = document.getElementById('itemTableBody');

                if (!tbody || tbody.children.length === 0) {
                    e.preventDefault();
                    alert('Harap tambahkan minimal satu baris item yang akan diretur.');
                    return false;
                }

                // Cek kuantitas retur
                let hasValidQty = false;
                document.querySelectorAll('#itemTableBody input[name*="[retur_qty]"]').forEach(inp => {
                    if (parseFloat(inp.value || 0) > 0) hasValidQty = true;
                });

                if (!hasValidQty) {
                    e.preventDefault();
                    alert('Kuantitas retur pada baris item tidak boleh 0 atau kosong.');
                    return false;
                }

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = `
                        <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" style="animation: spin 1s linear infinite; display: inline-block;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Menyimpan Retur...</span>
                    `;
                }
            });
        }
    });

    function getSelectedGudangId() {
        const el = document.getElementById('gudang_id');
        return el ? el.value : '';
    }

    // Switcher Sumber Asal: PO vs Non-PO
    window.onSourceTypeChanged = function () {
        const checkedRadio = document.querySelector('input[name="source_type"]:checked');
        const type = checkedRadio ? checkedRadio.value : 'PO';
        const poGroup = document.getElementById('poSelectGroup');
        const poSelect = document.getElementById('poSelect');
        const labelPo = document.getElementById('pillSourcePo');
        const labelNonPo = document.getElementById('pillSourceNonPo');

        if (labelPo && labelNonPo) {
            if (type === 'PO') {
                labelPo.classList.add('active');
                labelNonPo.classList.remove('active');
            } else {
                labelNonPo.classList.add('active');
                labelPo.classList.remove('active');
            }
        }

        if (type === 'NON_PO') {
            if (poGroup) poGroup.style.display = 'none';
            if (poSelect) poSelect.value = '';
        } else {
            if (poGroup) poGroup.style.display = 'block';
        }
    };

    // Switcher Tindakan Retur: REPLACE vs CREDIT_NOTE
    window.onTindakanChanged = function () {
        const checkedRadio = document.querySelector('input[name="tindakan_cd"]:checked');
        const val = checkedRadio ? checkedRadio.value : 'REPLACE';
        const labelRep = document.getElementById('pillTindakanReplace');
        const labelCre = document.getElementById('pillTindakanCredit');
        const tindakanInfo = document.getElementById('tindakanExplanation');

        if (labelRep && labelCre) {
            if (val === 'REPLACE') {
                labelRep.classList.add('active-success');
                labelCre.classList.remove('active-amber');
                if (tindakanInfo) {
                    tindakanInfo.innerHTML = `<strong>🔄 Minta Kirim Ulang (Pengganti):</strong> Kuota penerimaan PO akan dibuka kembali ke status <strong>PARTIAL</strong> agar kiriman barang baru dapat diterima di sistem nanti.`;
                    tindakanInfo.style.color = '#065f46';
                    tindakanInfo.style.background = '#f0fdf4';
                    tindakanInfo.style.borderColor = '#bbf7d0';
                }
            } else {
                labelCre.classList.add('active-amber');
                labelRep.classList.remove('active-success');
                if (tindakanInfo) {
                    tindakanInfo.innerHTML = `<strong>💰 Potong Tagihan (Credit Note):</strong> Mengurangi kewajiban hutang pembayaran supplier tanpa membuka kuota baru. Faktur tagihan di modul Keuangan akan otomatis terpotong nota kredit ini.`;
                    tindakanInfo.style.color = '#92400e';
                    tindakanInfo.style.background = '#fffbeb';
                    tindakanInfo.style.borderColor = '#fde68a';
                }
            }
        }
    };

    // Event Handler saat PO dipilih
    window.onPoSelected = function () {
        const poSelect = document.getElementById('poSelect');
        if (!poSelect) return;
        const poId = poSelect.value;
        if (!poId) return;

        const url = (window.returConfig && window.returConfig.poDataUrl) 
            ? `${window.returConfig.poDataUrl}/${poId}` 
            : `/gudang/retur/po-data/${poId}`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const poData = res.data;

                    // 1. Auto-fill Supplier
                    if (poData.supplier_id) {
                        const supSelect = document.getElementById('supplierSelect');
                        if (supSelect) supSelect.value = poData.supplier_id;
                    }

                    // 2. Auto-fill Surat Jalan Supplier (dari GRN / PO yang diterima)
                    if (poData.suratjalan_supplier_no) {
                        const sjInput = document.getElementById('suratjalan_supplier_no');
                        if (sjInput) {
                            sjInput.value = poData.suratjalan_supplier_no;
                        }
                    }

                    // 3. Switch Gudang jika berbeda
                    if (poData.gudang_id) {
                        const gdSelect = document.getElementById('gudang_id');
                        if (gdSelect && gdSelect.tagName === 'SELECT' && gdSelect.value != poData.gudang_id) {
                            gdSelect.value = poData.gudang_id;
                            onGudangChanged(function () {
                                populatePoItems(poData.items);
                            });
                            return;
                        }
                    }

                    // 4. Masukkan item barang PO ke tabel retur
                    populatePoItems(poData.items);
                }
            })
            .catch(err => console.error("Error fetching PO data:", err));
    };

    function populatePoItems(items) {
        const tbody = document.getElementById('itemTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            alert("PO ini belum memiliki catatan penerimaan barang fisik di gudang. Silakan input item secara manual atau pilih PO yang sudah pernah diterima.");
            addRow();
            return;
        }

        items.forEach(item => {
            addRowFromPoItem(item);
        });

        updateTotals();
    }

    function addRowFromPoItem(poItem) {
        const tbody = document.getElementById('itemTableBody');
        const rowId = rowCounter++;

        let barangOptions = `<option value="${poItem.barang_id}" selected data-harga="${poItem.harga_nominal}" data-satuan="${poItem.satuan}">${poItem.barang_nm} (${poItem.barang_cd})</option>`;
        MASTER_BARANG.forEach(b => {
            if (b.barang_id !== poItem.barang_id) {
                const satuan = b.satuan_dasar?.satuan_nm || '';
                barangOptions += `<option value="${b.barang_id}" data-harga="${b.harga_beli_standar || 0}" data-satuan="${satuan}">${b.barang_nm} (${b.barang_cd})</option>`;
            }
        });

        const tr = document.createElement('tr');
        tr.id = `row_${rowId}`;
        tr.innerHTML = `
            <td style="text-align: center; color: #64748b; font-size: 0.775rem;">
                ${tbody.children.length + 1}
            </td>
            <td>
                <input type="hidden" name="items[${rowId}][podtl_id]" value="${poItem.podtl_id}">
                <select name="items[${rowId}][barang_id]" class="form-control barang-select" data-row-id="${rowId}" onchange="onBarangChanged(${rowId})" required style="font-size: 0.825rem; font-weight: 600;">
                    ${barangOptions}
                </select>
                <div style="font-size: 0.7rem; color: #0284c7; margin-top: 2px; font-weight: 600;">
                    ✓ Dari PO (Diterima: ${poItem.terima_qty} ${poItem.satuan})
                </div>
            </td>
            <td>
                <select name="items[${rowId}][batch_no]" id="batch_select_${rowId}" class="form-control batch-select" data-row-id="${rowId}" onchange="onBatchSelected(${rowId})" required style="font-size: 0.8rem;">
                    <option value="">-- Pilih Batch --</option>
                </select>
                <div id="batch_info_${rowId}" style="font-size: 0.7rem; color: #64748b; margin-top: 2px;"></div>
            </td>
            <td>
                <input type="number" step="0.0001" min="0.0001" name="items[${rowId}][retur_qty]" id="qty_${rowId}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" required style="font-size: 0.85rem; font-weight: 700; color: #dc2626; text-align: right;">
            </td>
            <td style="font-size: 0.8rem; color: #475569; text-align: center;">
                <span id="satuan_label_${rowId}">${poItem.satuan}</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowId}][harga_satuan]" id="harga_${rowId}" value="${poItem.harga_nominal}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" style="font-size: 0.825rem; text-align: right; font-family: monospace;">
            </td>
            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.85rem; vertical-align: middle; font-family: monospace;">
                <span id="subtotal_text_${rowId}">Rp 0</span>
            </td>
            <td>
                <select name="items[${rowId}][alasan_reject]" class="form-control" style="font-size: 0.775rem;">
                    <option value="Singkong Busuk / Basah">Singkong Busuk / Basah</option>
                    <option value="Berserat / Keras (Gagal Goreng)">Berserat / Keras (Gagal Goreng)</option>
                    <option value="Berjamur / Kutu">Berjamur / Kutu</option>
                    <option value="Kemasan Rusak / Bocor">Kemasan Rusak / Bocor</option>
                    <option value="Bau Asam / Tengik">Bau Asam / Tengik</option>
                    <option value="Spesifikasi Tidak Sesuai">Spesifikasi Tidak Sesuai</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(${rowId})" style="color: #dc2626; padding: 0.2rem 0.45rem; font-size: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background: #ffffff;" title="Hapus Baris">
                    ✕
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        updateBatchDropdownForRow(rowId, poItem.batches);
    }

    // Ambil daftar batch fisik yang tersedia di gudang terpilih
    window.onGudangChanged = function (callback) {
        const gudangId = getSelectedGudangId();
        if (!gudangId) return;

        const url = (window.returConfig && window.returConfig.batchesUrl)
            ? `${window.returConfig.batchesUrl}?gudang_id=${gudangId}`
            : `/gudang/retur/batches?gudang_id=${gudangId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    availableBatches = data.data;

                    if (typeof callback === 'function') {
                        callback();
                    } else {
                        const poSelect = document.getElementById('poSelect');
                        const tbody = document.getElementById('itemTableBody');
                        if (tbody && tbody.children.length === 0 && (!poSelect || !poSelect.value)) {
                            addRow();
                        } else {
                            document.querySelectorAll('.barang-select').forEach((sel) => {
                                updateBatchDropdownForRow(sel.getAttribute('data-row-id'));
                            });
                        }
                    }
                }
            })
            .catch(err => console.error("Error fetching batches:", err));
    };

    // Filter Katalog Barang Cepat
    window.filterBarangCatalog = function (kategori) {
        currentCategoryFilter = kategori;

        document.getElementById('chipSemua')?.classList.toggle('active', kategori === 'ALL');
        document.getElementById('chipBB')?.classList.toggle('active', kategori === 'BAHAN_BAKU');
        document.getElementById('chipBP')?.classList.toggle('active', kategori === 'BAHAN_PENOLONG');
        document.getElementById('chipPACK')?.classList.toggle('active', kategori === 'KEMASAN');

        renderQuickSelectOptions();
    };

    window.searchCatalog = function () {
        renderQuickSelectOptions();
    };

    function renderQuickSelectOptions() {
        const queryInput = document.getElementById('catalogSearchInput');
        const query = queryInput ? queryInput.value.toLowerCase().trim() : '';
        const select = document.getElementById('quickBarangSelect');
        if (!select) return;

        select.innerHTML = '<option value="">-- Pilih Barang dari Katalog --</option>';

        const filtered = MASTER_BARANG.filter(b => {
            const matchKategori = (currentCategoryFilter === 'ALL' || b.kategori_kelompok === currentCategoryFilter);
            const matchSearch = (!query || b.barang_nm.toLowerCase().includes(query) || (b.barang_cd && b.barang_cd.toLowerCase().includes(query)));
            return matchKategori && matchSearch;
        });

        filtered.forEach(b => {
            select.innerHTML += `
                <option value="${b.barang_id}" 
                        data-kategori="${b.kategori_kelompok}"
                        data-satuan="${b.satuan_dasar?.satuan_nm || ''}"
                        data-harga="${b.harga_beli_standar || 0}">
                    ${b.barang_nm} (${b.barang_cd})
                </option>
            `;
        });
    }

    window.addSelectedBarangToTable = function () {
        const select = document.getElementById('quickBarangSelect');
        if (!select) return;
        const barangId = select.value;
        if (!barangId) {
            alert("Silakan pilih barang dari daftar terlebih dahulu.");
            return;
        }

        const selectedOpt = select.options[select.selectedIndex];
        const satuan = selectedOpt.getAttribute('data-satuan') || '';
        const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);

        addRowWithBarang(parseInt(barangId), harga, satuan);
    };

    function addRowWithBarang(barangId, defaultHarga = 0, defaultSatuan = '') {
        const tbody = document.getElementById('itemTableBody');
        if (!tbody) return;
        const rowId = rowCounter++;

        let barangOptions = '<option value="">-- Pilih Barang --</option>';
        MASTER_BARANG.forEach(b => {
            const isSel = (b.barang_id === barangId) ? 'selected' : '';
            const satuan = b.satuan_dasar?.satuan_nm || '';
            barangOptions += `<option value="${b.barang_id}" ${isSel} data-harga="${b.harga_beli_standar || 0}" data-satuan="${satuan}">${b.barang_nm} (${b.barang_cd})</option>`;
        });

        const tr = document.createElement('tr');
        tr.id = `row_${rowId}`;
        tr.innerHTML = `
            <td style="text-align: center; color: #64748b; font-size: 0.775rem;">
                ${tbody.children.length + 1}
            </td>
            <td>
                <select name="items[${rowId}][barang_id]" class="form-control barang-select" data-row-id="${rowId}" onchange="onBarangChanged(${rowId})" required style="font-size: 0.825rem; font-weight: 600;">
                    ${barangOptions}
                </select>
            </td>
            <td>
                <select name="items[${rowId}][batch_no]" id="batch_select_${rowId}" class="form-control batch-select" data-row-id="${rowId}" onchange="onBatchSelected(${rowId})" required style="font-size: 0.8rem;">
                    <option value="">-- Pilih Batch --</option>
                </select>
                <div id="batch_info_${rowId}" style="font-size: 0.7rem; color: #64748b; margin-top: 2px;"></div>
            </td>
            <td>
                <input type="number" step="0.0001" min="0.0001" name="items[${rowId}][retur_qty]" id="qty_${rowId}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" required style="font-size: 0.85rem; font-weight: 700; color: #dc2626; text-align: right;">
            </td>
            <td style="font-size: 0.8rem; color: #475569; text-align: center;">
                <span id="satuan_label_${rowId}">${defaultSatuan}</span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowId}][harga_satuan]" id="harga_${rowId}" value="${defaultHarga}" class="form-control" placeholder="0" oninput="calculateSubtotal(${rowId})" style="font-size: 0.825rem; text-align: right; font-family: monospace;">
            </td>
            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.85rem; vertical-align: middle; font-family: monospace;">
                <span id="subtotal_text_${rowId}">Rp 0</span>
            </td>
            <td>
                <select name="items[${rowId}][alasan_reject]" class="form-control" style="font-size: 0.775rem;">
                    <option value="Singkong Busuk / Basah">Singkong Busuk / Basah</option>
                    <option value="Berserat / Keras (Gagal Goreng)">Berserat / Keras (Gagal Goreng)</option>
                    <option value="Berjamur / Kutu">Berjamur / Kutu</option>
                    <option value="Kemasan Rusak / Bocor">Kemasan Rusak / Bocor</option>
                    <option value="Bau Asam / Tengik">Bau Asam / Tengik</option>
                    <option value="Spesifikasi Tidak Sesuai">Spesifikasi Tidak Sesuai</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(${rowId})" style="color: #dc2626; padding: 0.2rem 0.45rem; font-size: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; background: #ffffff;" title="Hapus Baris">
                    ✕
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        updateBatchDropdownForRow(rowId);
        updateTotals();

        setTimeout(() => {
            document.getElementById(`qty_${rowId}`)?.focus();
        }, 50);
    }

    window.addRow = function () {
        addRowWithBarang(null);
    };

    window.resetItemsTable = function () {
        if (confirm("Kosongkan semua baris item pada tabel?")) {
            const tbody = document.getElementById('itemTableBody');
            if (tbody) tbody.innerHTML = '';
            addRow();
            updateTotals();
        }
    };

    window.removeRow = function (rowId) {
        const row = document.getElementById(`row_${rowId}`);
        if (row) {
            row.remove();
            renumberRows();
            updateTotals();
        }
    };

    function renumberRows() {
        const rows = document.querySelectorAll('#itemTableBody tr');
        rows.forEach((tr, idx) => {
            const firstCell = tr.cells[0];
            if (firstCell) {
                firstCell.textContent = idx + 1;
            }
        });
    }

    window.onBarangChanged = function (rowId) {
        const barangSelect = document.querySelector(`#row_${rowId} .barang-select`);
        if (!barangSelect) return;
        const selectedOpt = barangSelect.options[barangSelect.selectedIndex];

        const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : '';
        const defaultHarga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;

        const lblSatuan = document.getElementById(`satuan_label_${rowId}`);
        if (lblSatuan) lblSatuan.textContent = satuan;

        const inpHarga = document.getElementById(`harga_${rowId}`);
        if (inpHarga) inpHarga.value = defaultHarga;

        updateBatchDropdownForRow(rowId);
        calculateSubtotal(rowId);
    };

    function updateBatchDropdownForRow(rowId, preferredBatches = []) {
        const barangSelect = document.querySelector(`#row_${rowId} .barang-select`);
        if (!barangSelect) return;

        const barangId = parseInt(barangSelect.value);
        const batchSelect = document.getElementById(`batch_select_${rowId}`);
        const batchInfo = document.getElementById(`batch_info_${rowId}`);
        if (!batchSelect) return;

        batchSelect.innerHTML = '<option value="">-- Pilih Batch --</option>';
        if (batchInfo) batchInfo.innerHTML = '';

        if (!barangId) {
            batchSelect.innerHTML = '<option value="">-- Pilih Barang Dahulu --</option>';
            return;
        }

        const filtered = availableBatches.filter(b => b.barang_id === barangId);
        if (filtered.length === 0) {
            batchSelect.innerHTML = '<option value="">⚠️ Stok Kosong (0 batch)</option>';
            if (batchInfo) batchInfo.innerHTML = '<span style="color: #dc2626;">Tidak ada saldo batch di gudang ini.</span>';
            return;
        }

        let autoSelectedBatch = null;

        filtered.forEach(b => {
            const exp = b.expired_tgl ? ` (Exp: ${b.expired_tgl})` : '';
            const isPreferred = Array.isArray(preferredBatches) && preferredBatches.includes(b.batch_no);
            const prefLabel = isPreferred ? ' ⭐ [Batch PO]' : '';

            if (isPreferred && !autoSelectedBatch) {
                autoSelectedBatch = b.batch_no;
            }

            batchSelect.innerHTML += `
                <option value="${b.batch_no}" 
                        data-sisa="${b.sisa_qty}" 
                        data-harga="${b.harga_satuan}"
                        data-exp="${b.expired_tgl || '-'}"
                        ${isPreferred ? 'style="font-weight: 700; color: #0284c7;"' : ''}>
                    ${b.batch_no}${prefLabel} [Sisa: ${b.sisa_qty} ${b.satuan}]${exp}
                </option>
            `;
        });

        if (autoSelectedBatch) {
            batchSelect.value = autoSelectedBatch;
            onBatchSelected(rowId);
        } else if (filtered.length === 1) {
            batchSelect.value = filtered[0].batch_no;
            onBatchSelected(rowId);
        }
    }

    window.onBatchSelected = function (rowId) {
        const batchSelect = document.getElementById(`batch_select_${rowId}`);
        if (!batchSelect) return;
        const selectedOpt = batchSelect.options[batchSelect.selectedIndex];
        const batchInfo = document.getElementById(`batch_info_${rowId}`);
        const qtyInput = document.getElementById(`qty_${rowId}`);
        const hargaInput = document.getElementById(`harga_${rowId}`);

        if (selectedOpt && selectedOpt.value) {
            const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
            const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);

            if (qtyInput) {
                qtyInput.max = sisa;
                if (!qtyInput.value || parseFloat(qtyInput.value) > sisa) {
                    qtyInput.value = sisa;
                }
            }

            if (hargaInput && harga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
                hargaInput.value = harga;
            }

            if (batchInfo) {
                batchInfo.innerHTML = `<span style="color: #059669; font-weight: 600;">✓ Saldo fisik: ${sisa}</span>`;
            }
            calculateSubtotal(rowId);
        } else {
            if (batchInfo) batchInfo.innerHTML = '';
        }
    };

    window.calculateSubtotal = function (rowId) {
        const qty = parseFloat(document.getElementById(`qty_${rowId}`)?.value || 0);
        const harga = parseFloat(document.getElementById(`harga_${rowId}`)?.value || 0);
        const subtotal = qty * harga;

        const subtotalText = document.getElementById(`subtotal_text_${rowId}`);
        if (subtotalText) {
            subtotalText.textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
        }

        updateTotals();
    };

    function updateTotals() {
        let totalItems = 0;
        let totalQty = 0;
        let totalNominal = 0;

        document.querySelectorAll('#itemTableBody tr').forEach(tr => {
            const rowId = tr.id.replace('row_', '');
            const qty = parseFloat(document.getElementById(`qty_${rowId}`)?.value || 0);
            const harga = parseFloat(document.getElementById(`harga_${rowId}`)?.value || 0);

            if (qty > 0) {
                totalItems++;
                totalQty += qty;
                totalNominal += (qty * harga);
            }
        });

        const formattedNominal = 'Rp ' + Math.round(totalNominal).toLocaleString('id-ID');
        const formattedQty = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });

        // Update Top Live Widget
        const topGrand = document.getElementById('topLiveGrandTotal');
        if (topGrand) topGrand.textContent = formattedNominal;

        const topCount = document.getElementById('topLiveItemCount');
        if (topCount) topCount.textContent = `${totalItems} Item`;

        // Update Tabel Footer
        const tblCount = document.getElementById('totalItemCount');
        if (tblCount) tblCount.textContent = totalItems;

        const tblNominal = document.getElementById('totalNominalText');
        if (tblNominal) tblNominal.textContent = formattedNominal;

        // Update Bottom Financial Card
        const sideItems = document.getElementById('summaryTotalItem');
        if (sideItems) sideItems.textContent = `${totalItems} Item`;

        const sideQty = document.getElementById('summaryTotalQty');
        if (sideQty) sideQty.textContent = formattedQty;

        const sideGrand = document.getElementById('summaryGrandTotal');
        if (sideGrand) sideGrand.textContent = formattedNominal;
    }
})();

