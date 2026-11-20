/**
 * ══════════════════════════════════════════════════════════════════════════
 * PO PENJUALAN (SALES ORDER) - FORM SCRIPT (12 KOLOM EXCEL GRID)
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

let rowCounter = 0;

function formatRupiah(amount) {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

function formatNumber(num) {
    const val = Number(num || 0);
    return Number.isInteger(val)
        ? val.toLocaleString('id-ID')
        : val.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 3 });
}

// Fetch and update Nomor Faktur real-time via AJAX
function fetchFakturNo() {
    const fakturInput = document.getElementById('faktur_no');
    if (!fakturInput) return;

    const custSelect = document.getElementById('customerSelect');
    const selected = custSelect ? custSelect.options[custSelect.selectedIndex] : null;
    const custId = selected ? selected.value : '';
    const klasifikasi = selected ? (selected.getAttribute('data-klasifikasi') || '') : '';
    const wilayah = selected ? (selected.getAttribute('data-wilayah') || '') : '';
    const dateVal = document.getElementById('so_tgl')?.value || '';
    const ppnHeader = document.getElementById('ppnTipeHeader')?.value || '';

    const badgeFormat = document.getElementById('badgeFakturFormat');
    const helpFaktur = document.getElementById('helpFakturFormat');

    // Jika status PPN belum dipilih, jangan generate nomor faktur ber-PPN / non-PPN dulu
    if (!ppnHeader) {
        fakturInput.value = '';
        fakturInput.placeholder = 'Pilih status PPN terlebih dahulu...';
        if (badgeFormat) {
            badgeFormat.textContent = 'Pilih PPN Dulu';
            badgeFormat.style.background = '#fef3c7';
            badgeFormat.style.color = '#b45309';
        }
        if (helpFaktur) {
            helpFaktur.textContent = 'Nomor faktur akan otomatis digenerate setelah status PPN dipilih';
        }
        return;
    }

    const isPpn = (ppnHeader === 'PPN_11');

    const url = window.soConfig?.generateFakturUrl || '/penjualan/so/ajax/generate-faktur-no';
    const query = new URLSearchParams({
        customer_id: custId,
        is_ppn: isPpn ? '1' : '0',
        date: dateVal,
        klasifikasi: klasifikasi,
        wilayah: wilayah
    });

    fetch(`${url}?${query.toString()}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.faktur_no) {
                fakturInput.value = data.faktur_no;

                // Visual feedback: brief highlight
                fakturInput.style.transition = 'background-color 0.3s';
                fakturInput.style.backgroundColor = '#dcfce7';
                setTimeout(() => {
                    fakturInput.style.backgroundColor = '#f0fdf4';
                }, 400);

                // Update badge format & keterangan standar
                const badgeFormat = document.getElementById('badgeFakturFormat');
                const helpFaktur = document.getElementById('helpFakturFormat');
                if (badgeFormat) {
                    const fNo = data.faktur_no;
                    if (fNo.includes('MFI-CKL')) {
                        badgeFormat.textContent = 'MFI-CKL (Cikokol)';
                        badgeFormat.style.background = '#e0f2fe';
                        badgeFormat.style.color = '#0284c7';
                        if (helpFaktur) helpFaktur.textContent = 'Standar: Berurut/MFI-CKL/Bulan/Tahun (Cikokol - Tangerang)';
                    } else if (fNo.includes('/MFI/')) {
                        badgeFormat.textContent = 'MFI (Semarang)';
                        badgeFormat.style.background = '#e0f2fe';
                        badgeFormat.style.color = '#0284c7';
                        if (helpFaktur) helpFaktur.textContent = 'Standar: Berurut/MFI/Bulan/Tahun (Semarang)';
                    } else if (fNo.includes('PM.A')) {
                        badgeFormat.textContent = 'PM.A (Ber-PPN)';
                        badgeFormat.style.background = '#dcfce7';
                        badgeFormat.style.color = '#15803d';
                        if (helpFaktur) helpFaktur.textContent = 'Standar: Berurut/PM.BerPPN/Bulan Tahun';
                    } else if (fNo.includes('PM.B')) {
                        badgeFormat.textContent = 'PM.B (Non-PPN)';
                        badgeFormat.style.background = '#fef3c7';
                        badgeFormat.style.color = '#b45309';
                        if (helpFaktur) helpFaktur.textContent = 'Standar: Berurut/PM.Non PPN/Bulan Tahun';
                    } else if (fNo.startsWith('MFI') && !fNo.includes('/')) {
                        badgeFormat.textContent = 'MFI (Modern Trade)';
                        badgeFormat.style.background = '#f3e8ff';
                        badgeFormat.style.color = '#7e22ce';
                        if (helpFaktur) helpFaktur.textContent = 'Standar: MFI(TahunBulanBerurut)';
                    } else {
                        badgeFormat.textContent = 'Auto Standard';
                        badgeFormat.style.background = '#f1f5f9';
                        badgeFormat.style.color = '#475569';
                        if (helpFaktur) helpFaktur.textContent = 'Format otomatis sesuai standar kategori & PPN';
                    }
                }
            }
        })
        .catch(err => console.error('Error fetching real-time faktur no:', err));
}

// Customer Selection Listener -> Mengisi Kode, Alamat, Kontak, TOP, PPN, dan Klasifikasi
function initCustomerListener() {
    const custSelect = document.getElementById('customerSelect');
    if (!custSelect) return;

    custSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const code = selected ? (selected.getAttribute('data-code') || '') : '';
        const address = selected ? (selected.getAttribute('data-address') || '-') : '-';
        const contact = selected ? (selected.getAttribute('data-contact') || '-') : '-';
        const klasifikasi = selected ? (selected.getAttribute('data-klasifikasi') || '') : '';
        const wilayah = selected ? (selected.getAttribute('data-wilayah') || '') : '';
        const kodePelanggan = selected ? (selected.getAttribute('data-kode-pelanggan') || '') : '';
        const topTxt = selected ? (selected.getAttribute('data-top') || '') : '';
        const defaultPpn = selected ? (selected.getAttribute('data-default-ppn') || '') : '';

        // Update Kode Customer
        const inCd = document.getElementById('customerCdInput');
        if (inCd) inCd.value = code;

        // Update TOP (Terms of Payment)
        const inTop = document.getElementById('top_txt');
        if (inTop) {
            inTop.value = topTxt || '';
            inTop.placeholder = topTxt ? 'Contoh: 7 Hari / 1 Nota' : (['IDM', 'MIDI_ALFA', 'IDM_ALFA'].includes(klasifikasi) ? 'Tidak ada TOP (Modern Trade)' : 'Contoh: 7 Hari / 1 Nota');
        }

        // Update Status PPN Header jika ada default dari master customer
        const selPpnHeader = document.getElementById('ppnTipeHeader');
        if (selPpnHeader) {
            if (defaultPpn === 'PPN_11' || defaultPpn === 'NON_PPN') {
                const targetPpn = defaultPpn;
                if (selPpnHeader.value !== targetPpn) {
                    selPpnHeader.value = targetPpn;
                    syncAllRowsPpn(targetPpn);
                }
            } else if (!this.value) {
                selPpnHeader.value = '';
                syncAllRowsPpn('');
            }
        }

        // Update Dynamic PO / Nota label
        const lblPo = document.getElementById('lblCustomerPoNo');
        const inPo = document.getElementById('customer_po_no');
        const helpPo = document.getElementById('helpCustomerPoNo');

        if (klasifikasi === 'IFM') {
            if (lblPo) lblPo.textContent = 'No. PO Customer (Indofood)';
            if (inPo) inPo.placeholder = 'Contoh: 4506702351...';
            if (helpPo) helpPo.textContent = 'Nomor Purchase Order resmi dari PT Indofood Fortuna Makmur (Contoh: 4506702351)';
        } else if (klasifikasi === 'MANUAL') {
            if (lblPo) lblPo.textContent = 'No. Nota / Sales Lapangan';
            if (inPo) inPo.placeholder = 'Contoh: J.02.03 / M.01.20 / P.04.15...';
            if (helpPo) helpPo.textContent = 'Nomor referensi nota penjualan atau kode sales lapangan (Contoh: J.02.03, M.01.20, P.04.15)';
        } else if (['IDM', 'MIDI_ALFA', 'IDM_ALFA'].includes(klasifikasi)) {
            if (lblPo) lblPo.textContent = 'No. PO Modern Trade';
            if (inPo) inPo.placeholder = 'Contoh: OZ01POE26000866 / 5H5Q44496...';
            if (helpPo) helpPo.textContent = 'Nomor PO pesanan resmi (Contoh: OZ01POE26000866, 5H5Q44496, SQZ1POE26000869)';
        } else {
            if (lblPo) lblPo.textContent = 'No. Referensi PO / Nota';
            if (inPo) inPo.placeholder = 'Contoh: PO-CUST/09...';
            if (helpPo) helpPo.textContent = 'Untuk IFM: No PO | Untuk Manual: No Nota/Sales | Untuk Modern Trade: PO Mart';
        }

        // Update Klasifikasi Badge
        const badgeKlas = document.getElementById('badgeKlasifikasi');
        const txtWilayah = document.getElementById('textWilayah');
        if (badgeKlas) {
            if (klasifikasi === 'IFM') {
                badgeKlas.textContent = 'Indofood Fortuna (IFM)';
                badgeKlas.style.background = '#e0f2fe';
                badgeKlas.style.color = '#0284c7';
            } else if (klasifikasi === 'MANUAL') {
                badgeKlas.textContent = 'Manual (Sales/Toko)';
                badgeKlas.style.background = '#dcfce7';
                badgeKlas.style.color = '#15803d';
            } else if (klasifikasi === 'IDM') {
                badgeKlas.textContent = 'Modern Trade (IDM)';
                badgeKlas.style.background = '#eff6ff';
                badgeKlas.style.color = '#1d4ed8';
            } else if (klasifikasi === 'MIDI_ALFA' || klasifikasi === 'IDM_ALFA') {
                badgeKlas.textContent = 'Modern Trade (Midi/Alfa)';
                badgeKlas.style.background = '#fef2f2';
                badgeKlas.style.color = '#b91c1c';
            } else {
                badgeKlas.textContent = this.value ? 'Customer Umum' : 'Belum Memilih Customer';
                badgeKlas.style.background = '#f1f5f9';
                badgeKlas.style.color = '#475569';
            }
        }
        if (txtWilayah) {
            txtWilayah.textContent = wilayah ? `• Wilayah: ${wilayah}` : (this.value ? '• Wilayah: -' : '-');
        }

        // Update Customer Info Box
        const infoBox = document.getElementById('customerInfoBox');
        if (this.value && infoBox) {
            const elAddr = document.getElementById('custAddressText');
            const elContact = document.getElementById('custContactText');
            const elRef = document.getElementById('custRefText');
            if (elAddr) elAddr.textContent = address;
            if (elContact) elContact.textContent = contact;
            if (elRef) elRef.textContent = `${kodePelanggan || '-'} (${wilayah || '-'})`;
            infoBox.style.display = 'block';
        } else if (infoBox) {
            infoBox.style.display = 'none';
        }

        // Sync product options according to customer classification
        syncProductOptionsForCustomer(klasifikasi);

        // Fetch Nomor Faktur yang sesuai
        if (this.value) {
            fetchFakturNo();
            fetchCustomerTopStatus(this.value);
        } else {
            fetchCustomerTopStatus(null);
        }
    });

    if (custSelect.value) {
        custSelect.dispatchEvent(new Event('change'));
    }
}

// ══════════════════════════════════════════════════════════════════════════
// CUSTOMER TOP & CREDIT WARNING ENGINE (AJAX REAL-TIME)
// ══════════════════════════════════════════════════════════════════════════
let currentTopCheckData = null;

function fetchCustomerTopStatus(customerId) {
    const card = document.getElementById('customerTopWarningCard');
    if (!card) return;

    if (!customerId) {
        card.style.display = 'none';
        card.innerHTML = '';
        currentTopCheckData = null;
        return;
    }

    const baseUrl = window.soConfig?.topCheckUrl || '/penjualan/so/ajax/customer-top-check';
    const url = `${baseUrl}?customer_id=${customerId}`;

    // Loading State Ramping
    card.className = 'so-top-warning-card top-info';
    card.style.display = 'block';
    card.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem;">
            <svg style="animation: spin 1s linear infinite;" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Memeriksa status piutang &amp; batas TOP pelanggan...</span>
        </div>
    `;

    fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(json => {
        if (!json.success || !json.data) {
            throw new Error(json.message || 'Gagal memuat status TOP');
        }

        const data = json.data;
        currentTopCheckData = data;
        renderTopWarningCard(card, data);
    })
    .catch(err => {
        console.error('Error fetching TOP status:', err);
        card.style.display = 'none';
        currentTopCheckData = null;
    });
}

function renderTopWarningCard(card, data) {
    const badgeClass = data.badge_class || 'safe';
    const badgeLabel = data.badge_label || 'Status TOP';
    const title = data.title || 'Status Pembayaran';
    const message = data.message || '';
    const invoices = data.invoices || [];
    const needsOverride = !!data.needs_override;

    card.className = `so-top-warning-card top-${badgeClass}`;
    card.style.display = 'block';

    let iconSvg = '';
    if (badgeClass === 'danger') {
        iconSvg = `<svg width="18" height="18" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
    } else if (badgeClass === 'warning') {
        iconSvg = `<svg width="18" height="18" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
    } else {
        iconSvg = `<svg width="18" height="18" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
    }

    let invoicesTableHtml = '';
    if (invoices.length > 0) {
        const rowsHtml = invoices.map(inv => {
            const styleColor = inv.status === 'OVERDUE' ? 'color: #dc2626; font-weight: 700;' : (inv.status === 'DUE_SOON' ? 'color: #b45309; font-weight: 600;' : 'color: #475569;');
            return `
                <tr>
                    <td style="font-family: monospace; font-weight: 700;">${inv.doc_no}</td>
                    <td>${inv.doc_tgl}</td>
                    <td>${inv.tgl_jatuh_tempo}</td>
                    <td style="text-align: right; font-weight: 700;">${inv.sisa_formatted}</td>
                    <td style="${styleColor}">${inv.status_label}</td>
                </tr>
            `;
        }).join('');

        invoicesTableHtml = `
            <div style="margin-top: 0.5rem; overflow-x: auto;">
                <table class="top-invoices-mini-table">
                    <thead>
                        <tr>
                            <th>No. Faktur / Dokumen</th>
                            <th>Tgl Dokumen</th>
                            <th>Jatuh Tempo</th>
                            <th style="text-align: right;">Sisa Piutang</th>
                            <th>Status Tempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>
        `;
    }

    let overrideHtml = '';
    if (needsOverride) {
        overrideHtml = `
            <div class="top-override-box">
                <label class="top-override-label">
                    <input type="checkbox" id="chkTopOverride" name="top_override_approved" value="1" style="width: 16px; height: 16px; cursor: pointer;">
                    <span>Saya telah memverifikasi &amp; memperoleh otorisasi manajer untuk melanjutkan pesanan ini.</span>
                </label>
            </div>
        `;
    }

    card.innerHTML = `
        <div class="top-warning-header">
            <div class="top-warning-title-wrap">
                ${iconSvg}
                <span>${title}</span>
            </div>
            <span class="top-warning-badge">${badgeLabel}</span>
        </div>
        <div class="top-warning-desc">
            ${message}
        </div>
        ${invoicesTableHtml}
        ${overrideHtml}
    `;
}

// Sync all row items PPN setting with header
function syncAllRowsPpn(ppnValue) {
    const targetValue = (ppnValue === 'PPN_11') ? 'PPN_11' : 'NON_PPN';
    const rowPpnSelects = document.querySelectorAll('#itemsBody .item-ppn');
    rowPpnSelects.forEach(sel => {
        sel.value = targetValue;
        const tr = sel.closest('tr');
        if (tr) calculateRow(tr);
    });
    calculateGrandTotal();
}

// Header PPN Selector Listener -> Sinkronisasi ke baris dan ganti nomor faktur real-time
function initPpnHeaderListener() {
    const selPpnHeader = document.getElementById('ppnTipeHeader');
    if (!selPpnHeader) return;

    selPpnHeader.addEventListener('change', function() {
        const val = this.value;
        syncAllRowsPpn(val);
        fetchFakturNo();
    });
}

// Tanggal Listener -> Perbarui SO No dan Faktur No
function initDateListener() {
    const tglInput = document.getElementById('so_tgl');
    if (!tglInput) return;

    tglInput.addEventListener('change', function() {
        const dateVal = this.value;
        if (!dateVal) return;

        // Update SO No
        const soUrl = window.soConfig?.generateSoCodeUrl || '/ajax/generate-code';
        fetch(`${soUrl}?type=so&date=${dateVal}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.code) {
                    const soNoInput = document.getElementById('so_no');
                    if (soNoInput) soNoInput.value = data.code;
                }
            })
            .catch(err => console.error('Error generating SO code:', err));

        // Update Faktur No
        fetchFakturNo();
    });
}

// Sync all row items product options according to customer classification
function syncProductOptionsForCustomer(klasifikasi) {
    const isIfm = (klasifikasi || '').toUpperCase() === 'IFM';
    document.querySelectorAll('#itemsBody .item-barang-select').forEach(sel => {
        Array.from(sel.options).forEach(opt => {
            if (!opt.value) return;
            const code = (opt.dataset.code || '').toUpperCase();
            const text = (opt.textContent || '').toUpperCase();
            const isIfmProduct = code.includes('FCC') || code.includes('IFL') || text.includes('IFL') || text.includes('INDOFOOD');
            if (!isIfm && isIfmProduct) {
                opt.disabled = true;
                if (!opt.textContent.includes('[Khusus Mitra IFM]')) {
                    opt.textContent = opt.textContent.replace(' (Stok:', ' [Khusus Mitra IFM] (Stok:');
                }
            } else {
                opt.disabled = false;
                opt.textContent = opt.textContent.replace(' [Khusus Mitra IFM]', '');
            }
        });
    });
}

// Tambah Baris Baru pada Tabel Inputan
function addRow(data = {}) {
    rowCounter++;
    const idx = rowCounter;
    const tbody = document.getElementById('itemsBody');
    if (!tbody) return;

    const MASTER_BARANG = window.soConfig?.barangs || [];
    const stockMap = window.soConfig?.stockMap || {};

    const selCust = document.getElementById('customerSelect');
    const optCust = selCust?.selectedOptions?.[0];
    const klasifikasi = (optCust?.dataset?.klasifikasi || '').toUpperCase();
    const isIfm = klasifikasi === 'IFM';

    const tr = document.createElement('tr');
    tr.id = `row_${idx}`;

    // Pisahkan pilihan barang berdasarkan Kategori (FG vs WIP) untuk dikelompokkan dengan <optgroup>
    const fgItems = [];
    const wipItems = [];
    const otherItems = [];

    MASTER_BARANG.forEach(b => {
        const rawCd = (b.jenis_barang ? b.jenis_barang.jenis_barang_cd : (b.jenis_barang_cd || '')).toUpperCase();
        const code = (b.barang_cd || '').toUpperCase();
        if (rawCd === 'WIP' || code.startsWith('WIP') || rawCd.includes('SETENGAH')) {
            wipItems.push(b);
        } else if (rawCd === 'FG' || code.startsWith('FG') || rawCd.includes('JADI')) {
            fgItems.push(b);
        } else {
            fgItems.push(b);
        }
    });

    const renderOption = (b) => {
        const id = b.barang_id;
        const stok = parseFloat(stockMap[id] || 0);
        const isSelected = (data.barang_id == id) ? 'selected' : '';
        const rawCd = (b.jenis_barang ? b.jenis_barang.jenis_barang_cd : (b.jenis_barang_cd || 'FG')).toUpperCase();
        const jenisNm = b.jenis_barang ? b.jenis_barang.jenis_barang_nm : rawCd;
        const satuanNm = b.satuan_dasar ? b.satuan_dasar.satuan_nm : 'Karton';
        const hargaJual = b.harga_jual || b.harga_standar || b.harga_beli_standar || 0;

        const cd = (b.barang_cd || '').toUpperCase();
        const nm = (b.barang_nm || '').toUpperCase();
        const isIfmProduct = cd.includes('FCC') || cd.includes('IFL') || nm.includes('IFL') || nm.includes('INDOFOOD');

        let disabledAttr = '';
        let extraLabel = '';
        if (!isIfm && isIfmProduct) {
            disabledAttr = 'disabled';
            extraLabel = ' [Khusus Mitra IFM]';
        }

        return `
            <option value="${id}" 
                    data-code="${b.barang_cd || ''}" 
                    data-jenis-cd="${rawCd}"
                    data-jenis="${jenisNm}" 
                    data-satuan="${satuanNm}"
                    data-harga="${hargaJual}"
                    data-stok="${stok}"
                    ${disabledAttr}
                    ${isSelected}>
                ${b.barang_nm}${extraLabel} (Stok: ${stok} ${satuanNm})
            </option>
        `;
    };

    let optionsHtml = '';
    if (fgItems.length > 0) {
        optionsHtml += `<optgroup label="BARANG JADI (FINISHED GOODS / FG)" class="grp-fg">` + fgItems.map(renderOption).join('') + `</optgroup>`;
    }
    if (wipItems.length > 0) {
        optionsHtml += `<optgroup label="BARANG SETENGAH JADI (WIP / WORK IN PROCESS)" class="grp-wip">` + wipItems.map(renderOption).join('') + `</optgroup>`;
    }
    if (otherItems.length > 0) {
        optionsHtml += `<optgroup label="PRODUK LAINNYA" class="grp-other">` + otherItems.map(renderOption).join('') + `</optgroup>`;
    }

    const curSatuan = data.satuan || '';
    const knownUnits = ['Karton', 'Bungkus', 'Bal', 'Pack', 'Kilogram', 'Pieces', 'Dus', 'Sak', 'Ball 5 Kg', 'Karton 6 Kg', 'Karton 7 Kg'];
    const customUnitOpt = (curSatuan && !knownUnits.some(u => u.toLowerCase() === curSatuan.toLowerCase()))
        ? `<option value="${curSatuan}" selected>${curSatuan}</option>`
        : '';

    tr.innerHTML = `
        <td style="text-align: center; color: #64748b; font-size: 0.8rem;" class="row-num">${tbody.children.length + 1}</td>
        <td style="text-align: center; vertical-align: middle;">
            <input type="text" class="form-control item-barang-code" readonly placeholder="-" style="background: #f8fafc; font-family: monospace; font-size: 0.775rem; color: #0284c7; font-weight: 700; cursor: not-allowed; text-align: center; border: 1.5px solid #cbd5e1; border-radius: 6px; width: 100%;">
        </td>
        <td>
            <select name="items[${idx}][barang_id]" class="form-control item-barang-select" required style="font-size: 0.8rem; width: 100%;">
                <option value="">-- Pilih Nama Barang --</option>
                ${optionsHtml}
            </select>
            <div class="item-batch-info" style="font-size: 0.72rem; color: #475569; margin-top: 3px; display: none;"></div>
        </td>
        <td style="text-align: center; vertical-align: middle; padding: 4px;" class="col-sisa-stok">
            <div class="box-sisa-gudang stok-placeholder" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4px 6px; border-radius: 6px; font-size: 0.725rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                <span class="badge-stok-label" style="font-size: 0.65rem; font-weight: 700; color: #64748b; letter-spacing: 0.03em;">STOK GUDANG</span>
                <div class="val-stok-qty" style="font-weight: 700; font-size: 0.8rem; color: #334155;">-</div>
                <div class="sub-stok-label" style="font-size: 0.65rem; color: #94a3b8;">Pilih Produk</div>
            </div>
        </td>
        <td>
            <input type="number" step="any" min="0.001" name="items[${idx}][pesan_qty]" value="${data.pesan_qty || 1}" required class="form-control item-qty" style="text-align: right; font-weight: 700;">
        </td>
        <td>
            <select name="items[${idx}][satuan]" class="form-control item-satuan" style="font-size: 0.775rem; font-weight: 600; text-align: center; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.35rem 0.25rem; width: 100%; color: #1e293b; cursor: pointer;">
                <option value="Karton" ${(curSatuan.toLowerCase() === 'karton' || (!curSatuan && !data.barang_id)) ? 'selected' : ''}>Karton</option>
                <option value="Bungkus" ${(curSatuan.toLowerCase() === 'bungkus') ? 'selected' : ''}>Bungkus</option>
                <option value="Bal" ${(curSatuan.toLowerCase() === 'bal') ? 'selected' : ''}>Bal</option>
                <option value="Pack" ${(curSatuan.toLowerCase() === 'pack') ? 'selected' : ''}>Pack</option>
                <option value="Kilogram" ${(curSatuan.toLowerCase() === 'kilogram' || curSatuan.toLowerCase() === 'kg') ? 'selected' : ''}>Kilogram (Kg)</option>
                <option value="Pieces" ${(curSatuan.toLowerCase() === 'pieces' || curSatuan.toLowerCase() === 'pcs') ? 'selected' : ''}>Pieces (Pcs)</option>
                <option value="Dus" ${(curSatuan.toLowerCase() === 'dus') ? 'selected' : ''}>Dus</option>
                <option value="Sak" ${(curSatuan.toLowerCase() === 'sak') ? 'selected' : ''}>Sak</option>
                <option value="Ball 5 Kg" ${(curSatuan === 'Ball 5 Kg') ? 'selected' : ''}>Ball 5 Kg</option>
                <option value="Karton 6 Kg" ${(curSatuan === 'Karton 6 Kg') ? 'selected' : ''}>Karton 6 Kg</option>
                <option value="Karton 7 Kg" ${(curSatuan === 'Karton 7 Kg') ? 'selected' : ''}>Karton 7 Kg</option>
                ${customUnitOpt}
                <option value="__custom__" style="color: #0284c7; font-weight: 700;">+ Satuan Lainnya...</option>
            </select>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${idx}][harga_satuan]" value="${data.harga_satuan || 0}" required class="form-control item-harga" style="text-align: right; font-family: monospace; font-weight: 600;">
            <div class="item-price-source" style="font-size: 0.675rem; color: #059669; font-weight: 600; margin-top: 3px; display: none; line-height: 1.2;"></div>
        </td>
        <td>
            <input type="number" step="any" min="0" max="100" name="items[${idx}][diskon_persen]" value="${data.diskon_persen || 0}" class="form-control item-diskon" style="text-align: right;">
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${idx}][potongan_nominal]" value="${data.potongan_nominal || 0}" class="form-control item-potongan" style="text-align: right;">
        </td>
        <td>
            <select name="items[${idx}][ppn_tipe]" class="form-control item-ppn" style="font-size: 0.75rem;">
                <option value="NON_PPN" ${(data.ppn_tipe || document.getElementById('ppnTipeHeader')?.value || 'NON_PPN') === 'NON_PPN' ? 'selected' : ''}>Non PPN</option>
                <option value="PPN_11" ${(data.ppn_tipe || document.getElementById('ppnTipeHeader')?.value || 'NON_PPN') === 'PPN_11' ? 'selected' : ''}>PPN 11%</option>
            </select>
        </td>
        <td style="text-align: right; font-family: monospace; font-size: 0.85rem; font-weight: 700; color: #0f172a;">
            <span class="item-subtotal-text">Rp 0</span>
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn-remove-row" style="background: transparent; border: none; color: #ef4444; cursor: pointer; padding: 0.2rem; display: inline-flex; align-items: center;" title="Hapus Baris">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    checkEmptyState();

    const selBarang = tr.querySelector('.item-barang-select');
    const inCode = tr.querySelector('.item-barang-code');
    const inSatuan = tr.querySelector('.item-satuan');
    const inQty = tr.querySelector('.item-qty');
    const inHarga = tr.querySelector('.item-harga');
    const inDiskon = tr.querySelector('.item-diskon');
    const inPotongan = tr.querySelector('.item-potongan');
    const inPpn = tr.querySelector('.item-ppn');
    const btnRemove = tr.querySelector('.btn-remove-row');
    const priceSourceEl = tr.querySelector('.item-price-source');

    const boxSisa = tr.querySelector('.box-sisa-gudang');
    const badgeStokLabel = tr.querySelector('.badge-stok-label');
    const valStokQty = tr.querySelector('.val-stok-qty');
    const subStokLabel = tr.querySelector('.sub-stok-label');

    let isInitialLoad = !!data.barang_id;

    selBarang.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            const code = opt.getAttribute('data-code') || '';
            const stok = parseFloat(opt.getAttribute('data-stok') || 0);
            const satuan = opt.getAttribute('data-satuan') || 'Karton';

            inCode.value = code;

            // Jika belum ada nilai satuan manual dari data, pilih otomatis sesuai master barang
            if (!isInitialLoad || !data.satuan) {
                let unitFound = false;
                for (let i = 0; i < inSatuan.options.length; i++) {
                    if (inSatuan.options[i].value.toLowerCase() === satuan.toLowerCase()) {
                        inSatuan.selectedIndex = i;
                        unitFound = true;
                        break;
                    }
                }
                if (!unitFound && satuan) {
                    const optNew = document.createElement('option');
                    optNew.value = satuan;
                    optNew.text = satuan;
                    optNew.selected = true;
                    inSatuan.insertBefore(optNew, inSatuan.querySelector('option[value="__custom__"]'));
                }
            }

            const formattedStok = formatNumber(stok);

            if (stok <= 0) {
                if (boxSisa) {
                    boxSisa.className = 'box-sisa-gudang stok-habis';
                    boxSisa.style.background = '#fef2f2';
                    boxSisa.style.border = '1px solid #fecaca';
                }
                if (badgeStokLabel) {
                    badgeStokLabel.textContent = 'ANTRE PRODUKSI';
                    badgeStokLabel.style.color = '#dc2626';
                }
                if (valStokQty) {
                    valStokQty.innerHTML = `0 <small class="txt-stok-satuan" style="font-size: 0.725rem; font-weight: 700; color: #dc2626;">${satuan}</small>`;
                }
                if (subStokLabel) {
                    subStokLabel.textContent = 'Perlu Produksi';
                    subStokLabel.style.color = '#dc2626';
                }
            } else {
                if (boxSisa) {
                    boxSisa.className = 'box-sisa-gudang stok-ada';
                    boxSisa.style.background = '#f0fdf4';
                    boxSisa.style.border = '1px solid #bbf7d0';
                }
                if (badgeStokLabel) {
                    badgeStokLabel.textContent = 'READY GUDANG';
                    badgeStokLabel.style.color = '#15803d';
                }
                if (valStokQty) {
                    valStokQty.innerHTML = `${formattedStok} <small class="txt-stok-satuan" style="font-size: 0.725rem; font-weight: 700; color: #047857;">${satuan}</small>`;
                }
                if (subStokLabel) {
                    subStokLabel.textContent = 'Siap Kirim';
                    subStokLabel.style.color = '#166534';
                }
            }

            // Sinkronisasi Harga Satuan dari Katalog Barang
            const hargaBarang = parseFloat(opt.getAttribute('data-harga') || 0);

            if (isInitialLoad && data.harga_satuan !== undefined && Number(data.harga_satuan) > 0) {
                // Form edit / draft
                inHarga.value = data.harga_satuan;
            } else {
                inHarga.value = hargaBarang;
            }

            if (priceSourceEl && hargaBarang > 0) {
                priceSourceEl.style.display = 'block';
                priceSourceEl.style.color = '#059669';
                priceSourceEl.innerHTML = `<span style="font-weight:700;">✓</span> Standar Katalog: ${formatRupiah(hargaBarang)}`;
            } else if (priceSourceEl) {
                priceSourceEl.style.display = 'none';
            }

            // Reset flag inisialisasi setelah interaksi pertama
            isInitialLoad = false;

            const batchInfoEl = tr.querySelector('.item-batch-info');
            if (batchInfoEl && opt.value) {
                fetch(`/penjualan/so/ajax/stock-check?barang_id=${opt.value}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.success && res.data && res.data.batches && res.data.batches.length > 0) {
                            const b = res.data.batches[0];
                            const expFmt = b.expired_tgl ? b.expired_tgl.substring(0, 10).split('-').reverse().join('/') : '-';
                            batchInfoEl.innerHTML = `<strong>Batch Siap:</strong> ${b.batch_no} &bull; <span style="color:#be123c;">Exp: ${expFmt}</span>`;
                            batchInfoEl.style.display = 'block';
                        } else {
                            batchInfoEl.style.display = 'none';
                        }
                    })
                    .catch(() => {
                        if (batchInfoEl) batchInfoEl.style.display = 'none';
                    });
            }
        } else {
            // Revert ke Default Placeholder
            if (boxSisa) {
                boxSisa.className = 'box-sisa-gudang stok-placeholder';
                boxSisa.style.background = '#f8fafc';
                boxSisa.style.border = '1px solid #e2e8f0';
            }
            if (badgeStokLabel) {
                badgeStokLabel.textContent = 'STOK GUDANG';
                badgeStokLabel.style.color = '#64748b';
            }
            if (valStokQty) valStokQty.textContent = '-';
            if (subStokLabel) {
                subStokLabel.textContent = 'Pilih Produk Dulu';
                subStokLabel.style.color = '#94a3b8';
            }

            const batchInfoEl = tr.querySelector('.item-batch-info');
            if (batchInfoEl) batchInfoEl.style.display = 'none';
            if (priceSourceEl) priceSourceEl.style.display = 'none';
            inCode.value = '-';
            inSatuan.value = '';
        }
        calculateRow(tr);
        calculateGrandTotal();
    });

    // Deteksi jika user menyesuaikan harga secara manual
    inHarga.addEventListener('input', function() {
        if (priceSourceEl && priceSourceEl.style.display !== 'none') {
            priceSourceEl.style.color = '#d97706';
            priceSourceEl.innerHTML = `<em>Harga disesuaikan manual</em>`;
        }
    });

    // Deteksi jika user memilih satuan kustom / lainnya
    inSatuan.addEventListener('change', function() {
        if (this.value === '__custom__') {
            const customUnit = prompt('Masukkan nama satuan baru (misal: Bungkus 500g, Dus, Bal, Toples):');
            if (customUnit && customUnit.trim()) {
                const trimmed = customUnit.trim();
                const newOpt = document.createElement('option');
                newOpt.value = trimmed;
                newOpt.text = trimmed;
                newOpt.selected = true;
                this.insertBefore(newOpt, this.querySelector('option[value="__custom__"]'));
            } else {
                this.value = 'Karton';
            }
        }
    });

    if (data.barang_id) {
        selBarang.dispatchEvent(new Event('change'));
    }

    [inQty, inHarga, inDiskon, inPotongan, inPpn].forEach(el => {
        el.addEventListener('input', () => {
            calculateRow(tr);
            calculateGrandTotal();
        });
        el.addEventListener('change', () => {
            calculateRow(tr);
            calculateGrandTotal();
        });
    });

    btnRemove.addEventListener('click', function() {
        tr.remove();
        renumberRows();
        calculateGrandTotal();
    });

    calculateRow(tr);

    // Sync filter kategori yang sedang aktif ke baris baru
    const activeFilterBtn = document.querySelector('.category-segment-btn.active');
    const activeCat = activeFilterBtn?.dataset?.filter || 'ALL';
    if (activeCat !== 'ALL') {
        const grpFg = selBarang.querySelector('optgroup.grp-fg');
        const grpWip = selBarang.querySelector('optgroup.grp-wip');
        const grpOther = selBarang.querySelector('optgroup.grp-other');

        if (grpFg) grpFg.style.display = (activeCat === 'FG') ? '' : 'none';
        if (grpWip) grpWip.style.display = (activeCat === 'WIP') ? '' : 'none';
        if (grpOther) grpOther.style.display = 'none';

        Array.from(selBarang.options).forEach(opt => {
            if (!opt.value) return;
            const jenisCd = (opt.getAttribute('data-jenis-cd') || '').toUpperCase();
            if (activeCat === 'FG') {
                opt.style.display = (jenisCd === 'FG' || jenisCd.includes('JADI') || !jenisCd.includes('WIP')) ? '' : 'none';
            } else if (activeCat === 'WIP') {
                opt.style.display = (jenisCd === 'WIP' || jenisCd.includes('SETENGAH')) ? '' : 'none';
            }
        });
    }
}

/**
 * Render visual badge ringkas kategori barang (FG vs WIP)
 */
function renderJenisBadge(container, cd, nm) {
    if (!container) return;
    const cleanCd = (cd || '').toUpperCase();
    const cleanNm = nm || cleanCd;

    if (cleanCd === 'FG' || cleanNm.toLowerCase().includes('barang jadi') || cleanNm.toLowerCase().includes('finished')) {
        container.innerHTML = `<span class="badge-kategori fg" title="${cleanNm}">FG &bull; Barang Jadi</span>`;
    } else if (cleanCd === 'WIP' || cleanNm.toLowerCase().includes('setengah')) {
        container.innerHTML = `<span class="badge-kategori wip" title="${cleanNm}">WIP &bull; Setengah Jadi</span>`;
    } else if (cleanCd) {
        container.innerHTML = `<span class="badge-kategori other" title="${cleanNm}">${cleanCd}</span>`;
    } else {
        container.innerHTML = `<span class="badge-kategori-empty">-</span>`;
    }
}

function calculateRow(tr) {
    const qty = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
    const harga = parseFloat(tr.querySelector('.item-harga')?.value) || 0;
    const diskonPersen = parseFloat(tr.querySelector('.item-diskon')?.value) || 0;
    const potonganRp = parseFloat(tr.querySelector('.item-potongan')?.value) || 0;
    const ppnTipe = tr.querySelector('.item-ppn')?.value || 'NON_PPN';

    const selBarang = tr.querySelector('.item-barang-select');
    const opt = selBarang?.selectedOptions[0];
    const subStokLabel = tr.querySelector('.sub-stok-label');

    if (opt && opt.value) {
        const stok = parseFloat(opt.getAttribute('data-stok') || 0);
        if (subStokLabel && stok > 0) {
            if (qty > stok) {
                subStokLabel.innerHTML = `<span style="color: #dc2626; font-weight: 700;">Kurang ${(qty - stok).toLocaleString('id-ID')}</span>`;
            } else {
                subStokLabel.textContent = 'Siap Digunakan';
            }
        }
    }

    const bruto = qty * harga;
    const diskonNominal = bruto * (diskonPersen / 100);
    const netto = Math.max(0, bruto - diskonNominal - potonganRp);
    const ppnNominal = ppnTipe === 'PPN_11' ? (netto * 0.11) : 0;
    const subtotal = netto + ppnNominal;

    const subtotalTxt = tr.querySelector('.item-subtotal-text');
    if (subtotalTxt) subtotalTxt.textContent = formatRupiah(subtotal);

    tr.dataset.bruto = bruto;
    tr.dataset.diskon = diskonNominal;
    tr.dataset.netto = netto;
    tr.dataset.ppn = ppnNominal;
    tr.dataset.subtotal = subtotal;
}

function calculateGrandTotal() {
    const rows = document.querySelectorAll('#itemsBody tr');
    let totalBruto = 0;
    let totalDiskon = 0;
    let totalDpp = 0;
    let totalPpn = 0;
    let totalQty = 0;

    rows.forEach(tr => {
        totalBruto += parseFloat(tr.dataset.bruto || 0);
        totalDiskon += parseFloat(tr.dataset.diskon || 0);
        totalDpp += parseFloat(tr.dataset.netto || 0);
        totalPpn += parseFloat(tr.dataset.ppn || 0);

        const qtyVal = parseFloat(tr.querySelector('.item-qty')?.value || 0);
        totalQty += isNaN(qtyVal) ? 0 : qtyVal;
    });

    const headerPotongan = parseFloat(document.getElementById('headerPotonganInput')?.value) || 0;
    const finalDpp = Math.max(0, totalDpp - headerPotongan);
    const grandTotal = finalDpp + totalPpn;

    const elBruto = document.getElementById('lblSubtotalBruto');
    const elDiskon = document.getElementById('lblTotalDiskon');
    const elDpp = document.getElementById('lblDppNominal');
    const elPpn = document.getElementById('lblTotalPpn');
    const elGrand = document.getElementById('lblTotalTagihan');
    const elSideQty = document.getElementById('sideTotalQty');

    if (elBruto) elBruto.textContent = formatRupiah(totalBruto);
    if (elDiskon) elDiskon.textContent = '- ' + formatRupiah(totalDiskon);
    if (elDpp) elDpp.textContent = formatRupiah(finalDpp);
    if (elPpn) elPpn.textContent = '+ ' + formatRupiah(totalPpn);
    if (elGrand) elGrand.textContent = formatRupiah(grandTotal);
    if (elSideQty) elSideQty.textContent = formatNumber(totalQty);

    // Live KPI Widget di Bar Navigasi Atas (Siku Kanan Atas)
    const elTopGrand = document.getElementById('topLiveGrandTotal');
    const elTopCount = document.getElementById('topLiveItemCount');
    const elTopIndicator = document.getElementById('badgeTopIndicator');

    if (elTopGrand) elTopGrand.textContent = formatRupiah(grandTotal);
    if (elTopCount) elTopCount.textContent = `${rows.length} Item`;

    if (elTopIndicator) {
        const ppnVal = document.getElementById('ppnTipeHeader')?.value;
        if (ppnVal === 'PPN_11') {
            elTopIndicator.textContent = 'Ber-PPN (11%)';
            elTopIndicator.style.background = '#dcfce7';
            elTopIndicator.style.color = '#15803d';
        } else if (ppnVal === 'NON_PPN') {
            elTopIndicator.textContent = 'Non-PPN';
            elTopIndicator.style.background = '#f1f5f9';
            elTopIndicator.style.color = '#475569';
        } else {
            elTopIndicator.textContent = 'Menunggu PPN';
            elTopIndicator.style.background = '#fef3c7';
            elTopIndicator.style.color = '#b45309';
        }
    }
}

// Filter Segmented Kategori Produk di Header Tabel
window.filterProdukCategory = function(cat, btn) {
    document.querySelectorAll('.category-segment-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const selects = document.querySelectorAll('#itemsBody .item-barang-select');
    selects.forEach(sel => {
        // Toggle Optgroup Header
        const grpFg = sel.querySelector('optgroup.grp-fg');
        const grpWip = sel.querySelector('optgroup.grp-wip');
        const grpOther = sel.querySelector('optgroup.grp-other');

        if (grpFg) grpFg.style.display = (cat === 'ALL' || cat === 'FG') ? '' : 'none';
        if (grpWip) grpWip.style.display = (cat === 'ALL' || cat === 'WIP') ? '' : 'none';
        if (grpOther) grpOther.style.display = (cat === 'ALL') ? '' : 'none';

        // Toggle Individual Options
        Array.from(sel.options).forEach(opt => {
            if (!opt.value) return;
            const jenisCd = (opt.getAttribute('data-jenis-cd') || '').toUpperCase();
            if (cat === 'ALL') {
                opt.style.display = '';
            } else if (cat === 'FG') {
                opt.style.display = (jenisCd === 'FG' || jenisCd.includes('JADI') || !jenisCd.includes('WIP')) ? '' : 'none';
            } else if (cat === 'WIP') {
                opt.style.display = (jenisCd === 'WIP' || jenisCd.includes('SETENGAH')) ? '' : 'none';
            }
        });
    });
};

// Kosongkan Seluruh Baris Item Pesanan
window.clearAllRows = function() {
    const tbody = document.getElementById('itemsBody');
    if (!tbody || tbody.children.length === 0) return;

    if (confirm('Apakah Anda yakin ingin mengosongkan seluruh baris item produk pada tabel pesanan ini?')) {
        tbody.innerHTML = '';
        rowCounter = 0;
        addRow();
        calculateGrandTotal();
    }
};

function renumberRows() {
    const rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach((tr, i) => {
        const numCell = tr.querySelector('.row-num');
        if (numCell) numCell.textContent = i + 1;
    });
    checkEmptyState();
}

function checkEmptyState() {
    const rows = document.querySelectorAll('#itemsBody tr');
    const notice = document.getElementById('noItemsNotice');
    if (notice) {
        notice.style.display = rows.length === 0 ? 'block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initCustomerListener();
    initPpnHeaderListener();
    initDateListener();

    const btnAdd = document.getElementById('btnAddRow');
    if (btnAdd) {
        btnAdd.addEventListener('click', () => addRow());
    }

    const potInput = document.getElementById('headerPotonganInput');
    if (potInput) {
        potInput.addEventListener('input', calculateGrandTotal);
    }

    const existingItems = window.soConfig?.existingItems || [];
    if (existingItems.length > 0) {
        existingItems.forEach(item => addRow(item));
    } else {
        const rows = document.querySelectorAll('#itemsBody tr');
        if (rows.length === 0) {
            addRow();
        }
    }

    // Interlocking Submit Guard untuk Pelanggaran TOP & Status Pajak (PPN)
    const formPo = document.getElementById('formPo');
    if (formPo) {
        formPo.addEventListener('submit', function(e) {
            const selPpn = document.getElementById('ppnTipeHeader');
            if (selPpn && !selPpn.value) {
                e.preventDefault();
                alert('⚠️ Status Pajak Faktur (PPN) wajib dipilih.\n\nSilakan tentukan apakah transaksi pesanan ini "Ber-PPN (11%)" atau "Non-PPN" sebelum menyimpan dokumen.');
                selPpn.focus();
                selPpn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                selPpn.style.outline = '2px solid #ef4444';
                setTimeout(() => selPpn.style.outline = '', 3000);
                return false;
            }

            if (currentTopCheckData && currentTopCheckData.needs_override) {
                const chk = document.getElementById('chkTopOverride');
                if (!chk || !chk.checked) {
                    e.preventDefault();
                    alert(`⚠️ PERINGATAN SYARAT PEMBAYARAN:\n\n${currentTopCheckData.title}\n${currentTopCheckData.badge_label}\n\nPelanggan memiliki tunggakan yang melanggar batas TOP.\nJika Anda telah mengonfirmasi pengecualian ini kepada Manajer Keuangan / Penjualan, silakan centang kotak persetujuan pada kartu peringatan di atas sebelum menyimpan.`);
                    const warnCard = document.getElementById('customerTopWarningCard');
                    if (warnCard) {
                        warnCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        warnCard.style.outline = '3px solid #dc2626';
                        setTimeout(() => warnCard.style.outline = '', 3000);
                    }
                    return false;
                }
            }
        });
    }
});
