/**
 * JavaScript Form Bukti Kas Keluar (BKK Operasional) - Minimalist Enterprise (WaveDash)
 * Standard: Separation of Concerns, Clean Calculations & Smart Recipient Autocomplete
 * PT Mirasa Food Industry (AGENTS.md Bagian 6)
 */
document.addEventListener('DOMContentLoaded', function () {
    const itemsContainer = document.getElementById('bkkItemsList') || document.getElementById('bkkItemsBody');
    const tableBody = itemsContainer;
    const btnAddRow = document.getElementById('btnAddRow');
    const btnAddRowLarge = document.getElementById('btnAddRowLarge');
    const totalDisplay = document.getElementById('bkkTotalDisplay');
    const terbilangDisplay = document.getElementById('bkkTerbilangDisplay');
    // Smart Kategori Elements
    const smartKatContainer = document.getElementById('smartKategoriContainer');
    const inputKategori = document.getElementById('kategori_input');
    const hiddenKategoriId = document.getElementById('kategori_id');
    const hiddenKategoriKode = document.getElementById('kategori_pengeluaran');
    const btnClearKategori = document.getElementById('btnClearKategori');
    const kategoriDropdown = document.getElementById('kategoriDropdown');
    const kategoriSuggestionsList = document.getElementById('kategoriSuggestionsList');
    const liveKategoriCoa = document.getElementById('liveKategoriCoa');
    const badgeKategori = document.getElementById('kategoriSelectedBadge');

    const kategoriMasterList = (window.appConfig && window.appConfig.kategoriMasterList) 
        ? window.appConfig.kategoriMasterList 
        : [];
    const selectKasBank = document.getElementById('kas_bank_id');
    const saldoDisplay = document.getElementById('liveSaldoDisplay');
    const sisaSaldoDisplay = document.getElementById('sisaSaldoDisplay');
    const rowNotice = document.getElementById('bkkRowNotice');
    const formKasKeluar = document.getElementById('formKasKeluar');
    const btnSubmit = document.getElementById('btnSubmitBkk');

    // Modal Saldo Minus Elements
    const modalSaldoMinus = document.getElementById('modalSaldoMinus');
    const modalSaldoMinusMsg = document.getElementById('modalSaldoMinusMsg');
    const btnCloseSaldoMinus = document.getElementById('btnCloseSaldoMinus');
    const btnBatalSaldoMinus = document.getElementById('btnBatalSaldoMinus');
    const btnLanjutSaldoMinus = document.getElementById('btnLanjutSaldoMinus');
    let allowBypassNegativeSaldo = false;

    // Smart Recipient Elements
    const smartContainer = document.getElementById('smartRecipientContainer');
    const inputPenerima = document.getElementById('penerima_nama');
    const inputPemohonId = document.getElementById('pemohon_karyawan_id');
    const inputDept = document.getElementById('departemen_pemohon');
    const inputTipe = document.getElementById('tipe_penerima');
    const badgeRecipient = document.getElementById('recipientTypeBadge');
    const btnClearRecipient = document.getElementById('btnClearRecipient');
    const recipientDropdown = document.getElementById('recipientDropdown');
    const suggestionsList = document.getElementById('recipientSuggestionsList');

    const karyawanList = (window.appConfig && window.appConfig.karyawanList) ? window.appConfig.karyawanList : [];

    let currentKasSaldo = null;
    let currentKasTipe = '';
    let currentGrandTotal = 0;

    // Notice alert bar
    function showRowNotice(msg) {
        if (!rowNotice) return;
        rowNotice.textContent = msg;
        rowNotice.style.display = 'block';
        setTimeout(() => {
            rowNotice.style.display = 'none';
        }, 3500);
    }

    // --------------------------------------------------------------------------
    // 1. LIVE SALDO KAS / BANK & ESTIMASI SISA SALDO REAL-TIME
    // --------------------------------------------------------------------------
    function updateSisaSaldoPreview() {
        if (!sisaSaldoDisplay) return;
        if (currentKasSaldo === null) {
            sisaSaldoDisplay.textContent = 'Pilih akun Kas/Bank untuk estimasi sisa saldo';
            sisaSaldoDisplay.classList.remove('dock-saldo-warn');
            return;
        }

        const sisa = currentKasSaldo - currentGrandTotal;
        const prefix = sisa < 0 ? '-Rp ' : 'Rp ';
        const formattedSaldo = 'Rp ' + currentKasSaldo.toLocaleString('id-ID');
        const formattedSisa = prefix + Math.abs(sisa).toLocaleString('id-ID');

        sisaSaldoDisplay.textContent = `Saldo Awal: ${formattedSaldo} → Estimasi Sisa: ${formattedSisa}`;

        if (sisa < 0 && currentKasTipe === 'KAS') {
            sisaSaldoDisplay.classList.add('dock-saldo-warn');
            sisaSaldoDisplay.textContent += ' (Saldo Fisik Kurang)';
        } else {
            sisaSaldoDisplay.classList.remove('dock-saldo-warn');
        }
    }

    function updateLiveSaldo() {
        if (!selectKasBank || !saldoDisplay) return;
        const selectedOption = selectKasBank.options[selectKasBank.selectedIndex];

        if (selectedOption && selectedOption.value && selectedOption.dataset.saldo !== undefined) {
            const saldo = parseFloat(selectedOption.dataset.saldo) || 0;
            currentKasSaldo = saldo;
            currentKasTipe = (selectedOption.dataset.tipe || '').toUpperCase();
            saldoDisplay.textContent = 'Rp ' + saldo.toLocaleString('id-ID');
        } else {
            currentKasSaldo = null;
            currentKasTipe = '';
            saldoDisplay.textContent = 'Rp 0';
        }
        updateSisaSaldoPreview();
    }

    if (selectKasBank) {
        selectKasBank.addEventListener('change', updateLiveSaldo);
        updateLiveSaldo();
    }

    // --------------------------------------------------------------------------
    // 2. SMART RECIPIENT AUTOCOMPLETE (FIELD TUNGGAL CERDAS)
    // --------------------------------------------------------------------------
    function setBadge(type, label) {
        if (!badgeRecipient) return;
        badgeRecipient.className = 'recipient-pill-badge';
        if (type === 'KARYAWAN') {
            badgeRecipient.classList.add('badge-karyawan');
            badgeRecipient.textContent = `Karyawan (${label || 'Staff'})`;
        } else if (type === 'PIHAK_KETIGA') {
            badgeRecipient.classList.add('badge-vendor');
            badgeRecipient.textContent = 'Pihak Luar / Vendor';
        } else {
            badgeRecipient.classList.add('badge-neutral');
            badgeRecipient.textContent = 'Karyawan / Vendor';
        }
    }

    function selectEmployee(karyawan) {
        if (!inputPenerima) return;
        inputPenerima.value = karyawan.karyawan_nm;
        if (inputPemohonId) inputPemohonId.value = karyawan.karyawan_id;
        const dept = karyawan.departemen_cd || karyawan.jabatan_nm || 'Staff';
        if (inputDept) inputDept.value = dept;
        if (inputTipe) inputTipe.value = 'KARYAWAN';

        setBadge('KARYAWAN', dept);
        if (btnClearRecipient) btnClearRecipient.style.display = 'inline-flex';
        closeDropdown();
    }

    function markAsVendor(customName) {
        if (inputPemohonId) inputPemohonId.value = '';
        if (inputDept) inputDept.value = '';
        if (inputTipe) inputTipe.value = 'PIHAK_KETIGA';

        if (customName && customName.trim().length > 0) {
            setBadge('PIHAK_KETIGA');
            if (btnClearRecipient) btnClearRecipient.style.display = 'inline-flex';
        } else {
            setBadge('NEUTRAL');
            if (btnClearRecipient) btnClearRecipient.style.display = 'none';
        }
    }

    function renderSuggestions(query = '') {
        if (!suggestionsList) return;
        suggestionsList.innerHTML = '';

        const cleanQuery = query.toLowerCase().trim();
        const filtered = cleanQuery === '' 
            ? karyawanList.slice(0, 10) 
            : karyawanList.filter(k => {
                const name = (k.karyawan_nm || '').toLowerCase();
                const dept = (k.departemen_cd || '').toLowerCase();
                const jab = (k.jabatan_nm || '').toLowerCase();
                return name.includes(cleanQuery) || dept.includes(cleanQuery) || jab.includes(cleanQuery);
            }).slice(0, 12);

        if (filtered.length > 0) {
            filtered.forEach(k => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                const initial = (k.karyawan_nm || 'K').charAt(0).toUpperCase();
                const dept = k.departemen_cd || k.jabatan_nm || 'Staff';

                item.innerHTML = `
                    <div class="emp-info-wrap">
                        <div class="emp-avatar-initial">${initial}</div>
                        <span class="emp-name-text">${escapeHtml(k.karyawan_nm)}</span>
                    </div>
                    <span class="emp-dept-pill">${escapeHtml(dept)}</span>
                `;

                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectEmployee(k);
                });

                suggestionsList.appendChild(item);
            });
        }

        if (cleanQuery.length > 0) {
            const vendorOption = document.createElement('div');
            vendorOption.className = 'autocomplete-item';
            vendorOption.style.background = '#f8fafc';
            vendorOption.innerHTML = `
                <div class="emp-info-wrap" style="color: #334155;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="emp-name-text" style="color: #0f172a; font-weight: 600;">Gunakan pihak luar: "<strong>${escapeHtml(query)}</strong>"</span>
                </div>
                <span class="emp-dept-pill">Vendor Luar</span>
            `;

            vendorOption.addEventListener('click', function (e) {
                e.stopPropagation();
                markAsVendor(query);
                closeDropdown();
            });

            suggestionsList.appendChild(vendorOption);
        } else if (filtered.length === 0) {
            suggestionsList.innerHTML = `<div class="autocomplete-empty-hint">Ketik nama karyawan atau pihak ketiga...</div>`;
        }
    }

    function openDropdown() {
        if (!recipientDropdown) return;
        renderSuggestions(inputPenerima ? inputPenerima.value : '');
        recipientDropdown.style.display = 'block';
    }

    function closeDropdown() {
        if (!recipientDropdown) return;
        recipientDropdown.style.display = 'none';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    if (inputPenerima) {
        inputPenerima.addEventListener('focus', function () {
            openDropdown();
        });

        inputPenerima.addEventListener('input', function () {
            const val = this.value;
            if (inputPemohonId && inputPemohonId.value) {
                const match = karyawanList.find(k => k.karyawan_id == inputPemohonId.value);
                if (!match || match.karyawan_nm !== val) {
                    markAsVendor(val);
                }
            } else {
                markAsVendor(val);
            }

            renderSuggestions(val);
            if (recipientDropdown) recipientDropdown.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            if (smartContainer && !smartContainer.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    if (btnClearRecipient) {
        btnClearRecipient.addEventListener('click', function () {
            if (inputPenerima) {
                inputPenerima.value = '';
                inputPenerima.focus();
            }
            if (inputPemohonId) inputPemohonId.value = '';
            if (inputDept) inputDept.value = '';
            if (inputTipe) inputTipe.value = 'PIHAK_KETIGA';
            setBadge('NEUTRAL');
            btnClearRecipient.style.display = 'none';
            openDropdown();
        });
    }

    // Initial state check for recipient
    if (inputPemohonId && inputPemohonId.value) {
        const found = karyawanList.find(k => k.karyawan_id == inputPemohonId.value);
        if (found) {
            setBadge('KARYAWAN', found.departemen_cd || found.jabatan_nm);
            if (btnClearRecipient) btnClearRecipient.style.display = 'inline-flex';
        }
    } else if (inputPenerima && inputPenerima.value.trim().length > 0) {
        setBadge('PIHAK_KETIGA');
        if (btnClearRecipient) btnClearRecipient.style.display = 'inline-flex';
    }

    // --------------------------------------------------------------------------
    // 3. SMART CATEGORY AUTOCOMPLETE (SEARCHABLE AUTOCOMPLETE SEPERTI KARYAWAN)
    // --------------------------------------------------------------------------
    function setKategoriBadge(isSelected) {
        if (!badgeKategori) return;
        badgeKategori.className = 'recipient-pill-badge';
        if (isSelected) {
            badgeKategori.classList.add('badge-karyawan');
            badgeKategori.textContent = 'Kategori Terpilih';
        } else {
            badgeKategori.classList.add('badge-neutral');
            badgeKategori.textContent = 'Pilih Kategori';
        }
    }

    function selectKategoriItem(kat) {
        if (!inputKategori) return;
        inputKategori.value = kat.nama_kategori;
        if (hiddenKategoriId) hiddenKategoriId.value = kat.kategori_id;
        if (hiddenKategoriKode) hiddenKategoriKode.value = kat.kode_kategori;

        if (liveKategoriCoa) {
            if (kat.coa_default && kat.coa_default.kode_akun) {
                liveKategoriCoa.textContent = `${kat.coa_default.kode_akun} - ${kat.coa_default.nama_akun}`;
            } else if (kat.coa_id_default) {
                liveKategoriCoa.textContent = `COA Default #${kat.coa_id_default}`;
            } else {
                liveKategoriCoa.textContent = 'Auto Jurnal Akuntansi';
            }
        }

        setKategoriBadge(true);
        if (btnClearKategori) btnClearKategori.style.display = 'inline-flex';
        closeKategoriDropdown();
    }

    function clearKategoriSelection() {
        if (inputKategori) inputKategori.value = '';
        if (hiddenKategoriId) hiddenKategoriId.value = '';
        if (hiddenKategoriKode) hiddenKategoriKode.value = '';
        if (liveKategoriCoa) liveKategoriCoa.textContent = 'Pilih Kategori';
        setKategoriBadge(false);
        if (btnClearKategori) btnClearKategori.style.display = 'none';
        openKategoriDropdown();
        if (inputKategori) inputKategori.focus();
    }

    function renderKategoriSuggestions(query = '') {
        if (!kategoriSuggestionsList) return;
        kategoriSuggestionsList.innerHTML = '';

        const cleanQuery = query.toLowerCase().trim();
        const filtered = cleanQuery === ''
            ? kategoriMasterList
            : kategoriMasterList.filter(k => {
                const nama = (k.nama_kategori || '').toLowerCase();
                const kode = (k.kode_kategori || '').toLowerCase();
                const ket = (k.keterangan || '').toLowerCase();
                const coaNm = (k.coa_default && k.coa_default.nama_akun) ? k.coa_default.nama_akun.toLowerCase() : '';
                const coaKd = (k.coa_default && k.coa_default.kode_akun) ? k.coa_default.kode_akun.toLowerCase() : '';
                return nama.includes(cleanQuery) || kode.includes(cleanQuery) || ket.includes(cleanQuery) || coaNm.includes(cleanQuery) || coaKd.includes(cleanQuery);
            });

        if (filtered.length > 0) {
            filtered.forEach(k => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';

                // Initial tag dari 2 huruf nama kategori
                const words = (k.nama_kategori || 'K').trim().split(/\s+/);
                const tag = (words[0].charAt(0) + (words[1] ? words[1].charAt(0) : (words[0].length > 1 ? words[0].charAt(1) : ''))).toUpperCase();

                const coaLabel = (k.coa_default && k.coa_default.kode_akun)
                    ? `${k.coa_default.kode_akun} - ${k.coa_default.nama_akun}`
                    : (k.keterangan || 'Pos Pengeluaran');

                item.innerHTML = `
                    <div class="emp-info-wrap">
                        <div class="kat-avatar-initial">${escapeHtml(tag)}</div>
                        <div>
                            <span class="emp-name-text">${escapeHtml(k.nama_kategori)}</span>
                            ${k.keterangan ? `<div style="font-size: 0.7rem; color: #64748b; margin-top: 1px;">${escapeHtml(k.keterangan)}</div>` : ''}
                        </div>
                    </div>
                    <span class="emp-dept-pill" style="max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(coaLabel)}">
                        ${escapeHtml(coaLabel)}
                    </span>
                `;

                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectKategoriItem(k);
                });

                kategoriSuggestionsList.appendChild(item);
            });
        } else {
            kategoriSuggestionsList.innerHTML = `<div class="autocomplete-empty-hint">Kategori "${escapeHtml(query)}" tidak ditemukan.</div>`;
        }
    }

    function openKategoriDropdown() {
        if (!kategoriDropdown) return;
        renderKategoriSuggestions(inputKategori ? inputKategori.value : '');
        kategoriDropdown.style.display = 'block';
    }

    function closeKategoriDropdown() {
        if (!kategoriDropdown) return;
        kategoriDropdown.style.display = 'none';
    }

    if (inputKategori) {
        inputKategori.addEventListener('focus', function () {
            openKategoriDropdown();
        });

        inputKategori.addEventListener('input', function () {
            const val = this.value;
            renderKategoriSuggestions(val);
            if (kategoriDropdown) kategoriDropdown.style.display = 'block';

            if (btnClearKategori) {
                btnClearKategori.style.display = val.trim().length > 0 ? 'inline-flex' : 'none';
            }

            // Jika user menghapus atau mengubah teks yang tidak cocok dengan yang tersimpan
            if (hiddenKategoriId && hiddenKategoriId.value) {
                const match = kategoriMasterList.find(k => k.kategori_id == hiddenKategoriId.value);
                if (!match || match.nama_kategori !== val) {
                    hiddenKategoriId.value = '';
                    hiddenKategoriKode.value = '';
                    if (liveKategoriCoa) liveKategoriCoa.textContent = 'Pilih Kategori';
                    setKategoriBadge(false);
                }
            }
        });

        if (btnClearKategori) {
            btnClearKategori.addEventListener('click', function (e) {
                e.stopPropagation();
                clearKategoriSelection();
            });
        }

        document.addEventListener('click', function (e) {
            if (smartKatContainer && !smartKatContainer.contains(e.target)) {
                closeKategoriDropdown();
            }
        });
    }

    // Set initial state jika kategori sudah terpilih
    if (hiddenKategoriId && hiddenKategoriId.value) {
        setKategoriBadge(true);
    }

    // --------------------------------------------------------------------------
    // 4. KALKULATOR DIRECT NOMINAL & ROW MANAGEMENT (DUAL-COLUMN CLEAN FOCUS)
    // --------------------------------------------------------------------------
    function getItemRows() {
        if (!itemsContainer) return [];
        return itemsContainer.querySelectorAll('.bkk-item-card, tr.bkk-item-row');
    }

    function updateRowNumbers() {
        const rows = getItemRows();
        rows.forEach((row, idx) => {
            const currentNum = idx + 1;
            const numCol = row.querySelector('.row-num-cell');
            if (numCol) {
                numCol.textContent = currentNum;
            }
            const itemNumWrap = row.querySelector('.bkk-item-num');
            if (itemNumWrap) {
                itemNumWrap.setAttribute('title', `Baris ${currentNum}`);
            }
            const deleteBtn = row.querySelector('.btn-delete-row');
            if (deleteBtn) {
                deleteBtn.setAttribute('aria-label', `Hapus baris ${currentNum}`);
            }

            // Sinkronkan nama array input: items[idx][...]
            const uraian = row.querySelector('.item-uraian');
            if (uraian) {
                uraian.setAttribute('name', `items[${idx}][uraian]`);
                uraian.id = `item_uraian_${idx}`;
            }
            const qty = row.querySelector('.item-qty');
            if (qty) {
                qty.setAttribute('name', `items[${idx}][qty]`);
                qty.id = `item_qty_${idx}`;
            }
            const satuan = row.querySelector('.item-satuan');
            if (satuan) {
                satuan.setAttribute('name', `items[${idx}][satuan]`);
                satuan.id = `item_satuan_${idx}`;
            }
            const harga = row.querySelector('.item-harga');
            if (harga) {
                harga.setAttribute('name', `items[${idx}][harga_satuan]`);
                harga.id = `item_harga_${idx}`;
            }
            const nominal = row.querySelector('.item-nominal');
            if (nominal) {
                nominal.setAttribute('name', `items[${idx}][nominal]`);
                nominal.id = `item_nominal_${idx}`;
            }
        });
    }

    function recalculateTotals() {
        let grandTotal = 0;
        const rows = getItemRows();

        rows.forEach(row => {
            const nominalInput = row.querySelector('.item-nominal');
            const nominal = parseFloat(nominalInput ? nominalInput.value : 0) || 0;
            grandTotal += nominal;
        });

        currentGrandTotal = grandTotal;

        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        if (terbilangDisplay) {
            terbilangDisplay.textContent = '# ' + terbilang(grandTotal) + ' Rupiah #';
        }

        updateSisaSaldoPreview();
    }

    function addNewRow(initialUraian = '', initialNominal = '', initialQty = 1, initialSatuan = 'LS', initialHarga = '') {
        if (!itemsContainer) return;
        const currentRows = getItemRows();
        const currentCount = currentRows.length + 1;
        const idx = currentRows.length;

        const card = document.createElement('div');
        card.className = 'bkk-item-card';
        card.setAttribute('data-row-index', idx);
        card.innerHTML = `
            <div class="bkk-item-main-row">
                <div class="bkk-item-num" title="Baris ${currentCount}">
                    <span class="row-num-cell">${currentCount}</span>
                </div>

                <div class="bkk-item-col-uraian">
                    <label class="bkk-mobile-label" for="item_uraian_${idx}">
                        Uraian / Keperluan Pengeluaran <span class="req-star">*</span>
                    </label>
                    <input type="text"
                           name="items[${idx}][uraian]"
                           id="item_uraian_${idx}"
                           class="form-control-item item-uraian"
                           value="${escapeHtml(initialUraian)}"
                           placeholder="Ketik keperluan pengeluaran (contoh: Biaya operasional servis, bensin, konsumsi)..."
                           required
                           autocomplete="off">

                    <div class="bkk-item-sub-actions">
                        <button type="button" class="btn-toggle-qty" aria-expanded="false">
                            <svg class="toggle-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                            <span class="toggle-text">+ Rincian Qty & Satuan (Opsional)</span>
                        </button>
                    </div>

                    <div class="bkk-item-expandable-panel" style="display: none;">
                        <div class="bkk-details-grid">
                            <div class="detail-field">
                                <label class="detail-label" for="item_qty_${idx}">Qty / Jumlah</label>
                                <input type="number"
                                       step="any"
                                       name="items[${idx}][qty]"
                                       id="item_qty_${idx}"
                                       class="form-control-detail item-qty"
                                       value="${initialQty}"
                                       min="0.01"
                                       placeholder="1">
                            </div>
                            <div class="detail-field">
                                <label class="detail-label" for="item_satuan_${idx}">Satuan</label>
                                <input type="text"
                                       name="items[${idx}][satuan]"
                                       id="item_satuan_${idx}"
                                       class="form-control-detail item-satuan"
                                       value="${escapeHtml(initialSatuan)}"
                                       placeholder="LS / Pcs / Liter">
                            </div>
                            <div class="detail-field">
                                <label class="detail-label" for="item_harga_${idx}">Harga Satuan (Rp)</label>
                                <div class="detail-input-prefix">
                                    <span class="prefix-text">Rp</span>
                                    <input type="number"
                                           step="any"
                                           name="items[${idx}][harga_satuan]"
                                           id="item_harga_${idx}"
                                           class="form-control-detail item-harga with-prefix"
                                           value="${initialHarga}"
                                           placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bkk-item-col-nominal">
                    <label class="bkk-mobile-label" for="item_nominal_${idx}">
                        Nominal Keluar (Rp) <span class="req-star">*</span>
                    </label>
                    <div class="bkk-nominal-input-wrap">
                        <span class="nominal-prefix">Rp</span>
                        <input type="number"
                               step="any"
                               name="items[${idx}][nominal]"
                               id="item_nominal_${idx}"
                               class="form-control-nominal item-nominal"
                               value="${initialNominal}"
                               placeholder="0"
                               required
                               autocomplete="off">
                    </div>
                </div>

                <div class="bkk-item-col-action">
                    <button type="button" class="btn-delete-row" title="Hapus baris pengeluaran" aria-label="Hapus baris ${currentCount}">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        itemsContainer.appendChild(card);
        attachRowListeners(card);
        updateRowNumbers();

        const inputUraian = card.querySelector('.item-uraian');
        if (inputUraian) {
            inputUraian.focus();
        }
    }

    if (btnAddRow) {
        btnAddRow.addEventListener('click', function () {
            addNewRow();
            recalculateTotals();
        });
    }

    if (btnAddRowLarge) {
        btnAddRowLarge.addEventListener('click', function () {
            addNewRow();
            recalculateTotals();
        });
    }

    function attachRowListeners(row) {
        const qty = row.querySelector('.item-qty');
        const satuan = row.querySelector('.item-satuan');
        const harga = row.querySelector('.item-harga');
        const nominal = row.querySelector('.item-nominal');
        const btnRemove = row.querySelector('.btn-delete-row');
        const btnToggleQty = row.querySelector('.btn-toggle-qty');
        const expandablePanel = row.querySelector('.bkk-item-expandable-panel');
        const toggleIcon = row.querySelector('.toggle-icon');
        const toggleText = row.querySelector('.toggle-text');

        // Toggle Rincian Expandable Panel
        if (btnToggleQty && expandablePanel) {
            btnToggleQty.addEventListener('click', function (e) {
                e.preventDefault();
                const isOpen = expandablePanel.style.display !== 'none';
                if (isOpen) {
                    expandablePanel.style.display = 'none';
                    btnToggleQty.setAttribute('aria-expanded', 'false');
                    if (toggleIcon) toggleIcon.classList.remove('is-rotated');
                    if (toggleText) toggleText.textContent = '+ Rincian Qty & Satuan (Opsional)';
                } else {
                    expandablePanel.style.display = 'block';
                    btnToggleQty.setAttribute('aria-expanded', 'true');
                    if (toggleIcon) toggleIcon.classList.add('is-rotated');
                    if (toggleText) toggleText.textContent = 'Sembunyikan Rincian Qty';
                    if (qty) qty.focus();
                }
            });
        }

        if (nominal) {
            nominal.addEventListener('input', function () {
                const nomVal = parseFloat(nominal.value) || 0;
                const qtyVal = parseFloat(qty ? qty.value : 1) || 1;
                if (harga && !harga.value && qtyVal > 1 && nomVal > 0) {
                    harga.placeholder = (nomVal / qtyVal).toFixed(0);
                }
                recalculateTotals();
            });

            nominal.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const allRows = getItemRows();
                    if (row === allRows[allRows.length - 1] && (parseFloat(nominal.value) || 0) > 0) {
                        addNewRow();
                        recalculateTotals();
                    }
                }
            });
        }

        if (harga) {
            harga.addEventListener('input', function () {
                const hargaVal = parseFloat(harga.value) || 0;
                const qtyVal = parseFloat(qty ? qty.value : 1) || 1;
                if (hargaVal > 0 && nominal) {
                    nominal.value = Math.round(hargaVal * qtyVal);
                }
                recalculateTotals();
            });
        }

        if (qty) {
            qty.addEventListener('input', function () {
                const qtyVal = parseFloat(qty.value) || 1;
                const hargaVal = parseFloat(harga ? harga.value : 0) || 0;
                if (hargaVal > 0 && nominal) {
                    nominal.value = Math.round(hargaVal * qtyVal);
                }
                recalculateTotals();
            });
        }

        if (btnRemove) {
            btnRemove.addEventListener('click', function () {
                const totalRows = getItemRows().length;
                if (totalRows <= 1) {
                    showRowNotice('Minimal harus ada 1 baris rincian pengeluaran kas.');
                    return;
                }
                row.remove();
                updateRowNumbers();
                recalculateTotals();
            });
        }
    }

    getItemRows().forEach(attachRowListeners);
    updateRowNumbers();
    recalculateTotals();

    // --------------------------------------------------------------------------
    // 5. SHORTCUT KEYBOARD GLOBAL (CTRL + ENTER)
    // --------------------------------------------------------------------------
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            if (formKasKeluar) {
                if (typeof formKasKeluar.requestSubmit === 'function') {
                    formKasKeluar.requestSubmit();
                } else if (btnSubmit) {
                    btnSubmit.click();
                }
            }
        }
    });

    // --------------------------------------------------------------------------
    // 6. MODAL KONFIRMASI SALDO FISIK KURANG (AGENTS.MD COMPLIANT)
    // --------------------------------------------------------------------------
    function openSaldoMinusModal() {
        if (!modalSaldoMinus) return;
        if (modalSaldoMinusMsg && currentKasSaldo !== null) {
            modalSaldoMinusMsg.textContent = `Saldo Kas Fisik saat ini (Rp ${currentKasSaldo.toLocaleString('id-ID')}) lebih kecil dari total pengeluaran (Rp ${currentGrandTotal.toLocaleString('id-ID')}).`;
        }
        modalSaldoMinus.style.display = 'flex';
    }

    function closeSaldoMinusModal() {
        if (!modalSaldoMinus) return;
        modalSaldoMinus.style.display = 'none';
    }

    if (btnCloseSaldoMinus) btnCloseSaldoMinus.addEventListener('click', closeSaldoMinusModal);
    if (btnBatalSaldoMinus) btnBatalSaldoMinus.addEventListener('click', closeSaldoMinusModal);
    if (btnLanjutSaldoMinus) {
        btnLanjutSaldoMinus.addEventListener('click', function () {
            allowBypassNegativeSaldo = true;
            closeSaldoMinusModal();
            if (formKasKeluar) {
                if (typeof formKasKeluar.requestSubmit === 'function') {
                    formKasKeluar.requestSubmit();
                } else {
                    formKasKeluar.submit();
                }
            }
        });
    }

    // --------------------------------------------------------------------------
    // 7. FORM SUBMISSION VALIDATION & SUBMIT SPINNER
    // --------------------------------------------------------------------------
    if (formKasKeluar) {
        formKasKeluar.addEventListener('submit', function (e) {
            if (currentGrandTotal <= 0) {
                e.preventDefault();
                showRowNotice('Total pengeluaran harus lebih besar dari Rp 0.');
                const firstNominal = tableBody.querySelector('.item-nominal');
                if (firstNominal) firstNominal.focus();
                return;
            }

            if (!allowBypassNegativeSaldo && currentKasSaldo !== null && currentKasTipe === 'KAS' && currentGrandTotal > currentKasSaldo) {
                e.preventDefault();
                openSaldoMinusModal();
                return;
            }

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin" style="animation: spin 1s linear infinite;">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                    </svg>
                    <span>Menyimpan Kas Keluar...</span>
                `;
            }
        });
    }

    // --------------------------------------------------------------------------
    // 8. FUNGSI TERBILANG RUPIAH
    // --------------------------------------------------------------------------
    function terbilang(bilangan) {
        bilangan = Math.abs(Math.floor(bilangan));
        if (bilangan === 0) {
            return 'Nol';
        }
        const angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        let temp = "";

        if (bilangan < 12) {
            temp = " " + angka[bilangan];
        } else if (bilangan < 20) {
            temp = terbilang(bilangan - 10) + " Belas";
        } else if (bilangan < 100) {
            temp = terbilang(Math.floor(bilangan / 10)) + " Puluh" + terbilang(bilangan % 10);
        } else if (bilangan < 200) {
            temp = " Seratus" + terbilang(bilangan - 100);
        } else if (bilangan < 1000) {
            temp = terbilang(Math.floor(bilangan / 100)) + " Ratus" + terbilang(bilangan % 100);
        } else if (bilangan < 2000) {
            temp = " Seribu" + terbilang(bilangan - 1000);
        } else if (bilangan < 1000000) {
            temp = terbilang(Math.floor(bilangan / 1000)) + " Ribu" + terbilang(bilangan % 1000);
        } else if (bilangan < 1000000000) {
            temp = terbilang(Math.floor(bilangan / 1000000)) + " Juta" + terbilang(bilangan % 1000000);
        } else if (bilangan < 1000000000000) {
            temp = terbilang(Math.floor(bilangan / 1000000000)) + " Milyar" + terbilang(bilangan % 1000000000);
        } else if (bilangan < 1000000000000000) {
            temp = terbilang(Math.floor(bilangan / 1000000000000)) + " Triliun" + terbilang(bilangan % 1000000000000);
        }

        return temp.trim();
    }
});
