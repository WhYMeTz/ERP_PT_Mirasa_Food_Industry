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
    const ppnHeader = document.getElementById('ppnTipeHeader')?.value || 'NON_PPN';
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
        if (selPpnHeader && defaultPpn) {
            const targetPpn = defaultPpn === 'PPN_11' ? 'PPN_11' : 'NON_PPN';
            if (selPpnHeader.value !== targetPpn) {
                selPpnHeader.value = targetPpn;
                // Sync all row items
                syncAllRowsPpn(targetPpn);
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
        }
    });

    if (custSelect.value) {
        custSelect.dispatchEvent(new Event('change'));
    }
}

// Sync all row items PPN setting with header
function syncAllRowsPpn(ppnValue) {
    const rowPpnSelects = document.querySelectorAll('#itemsBody .item-ppn');
    rowPpnSelects.forEach(sel => {
        sel.value = ppnValue;
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

    // Generate pilihan barang
    const optionsHtml = MASTER_BARANG.map(b => {
        const id = b.barang_id;
        const stok = parseFloat(stockMap[id] || 0);
        const isSelected = (data.barang_id == id) ? 'selected' : '';
        const jenisNm = b.jenis_barang ? b.jenis_barang.jenis_barang_nm : (b.jenis_barang_cd || 'FG');
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
                    data-jenis="${jenisNm}" 
                    data-satuan="${satuanNm}"
                    data-harga="${hargaJual}"
                    data-stok="${stok}"
                    ${disabledAttr}
                    ${isSelected}>
                ${b.barang_nm}${extraLabel} (Stok: ${stok} ${satuanNm})
            </option>
        `;
    }).join('');

    tr.innerHTML = `
        <td style="text-align: center; color: #64748b; font-size: 0.8rem;" class="row-num">${tbody.children.length + 1}</td>
        <td>
            <input type="text" class="form-control item-jenis" readonly placeholder="-" style="background: #f8fafc; font-size: 0.75rem; cursor: not-allowed; text-align: center;">
        </td>
        <td>
            <select name="items[${idx}][barang_id]" class="form-control item-barang-select" required style="font-size: 0.8rem; width: 100%;">
                <option value="">-- Pilih Nama Barang --</option>
                ${optionsHtml}
            </select>
            <div class="item-stock-badge-container" style="margin-top: 3px;">
                <span class="stock-badge ready item-stock-badge" style="display: none;">
                    Stok FG: <strong class="item-stock-val">0</strong>
                </span>
            </div>
        </td>
        <td>
            <input type="text" class="form-control item-barang-code" readonly placeholder="Otomatis..." style="background: #f8fafc; font-family: monospace; font-size: 0.75rem; color: #0284c7; font-weight: 700; cursor: not-allowed; text-align: center;">
        </td>
        <td>
            <input type="number" step="any" min="0.001" name="items[${idx}][pesan_qty]" value="${data.pesan_qty || 1}" required class="form-control item-qty" style="text-align: right; font-weight: 700;">
        </td>
        <td>
            <input type="text" class="form-control item-satuan" readonly placeholder="-" style="background: #f8fafc; font-size: 0.75rem; cursor: not-allowed; text-align: center;">
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
    const inJenis = tr.querySelector('.item-jenis');
    const inSatuan = tr.querySelector('.item-satuan');
    const inQty = tr.querySelector('.item-qty');
    const inHarga = tr.querySelector('.item-harga');
    const inDiskon = tr.querySelector('.item-diskon');
    const inPotongan = tr.querySelector('.item-potongan');
    const inPpn = tr.querySelector('.item-ppn');
    const btnRemove = tr.querySelector('.btn-remove-row');
    const badge = tr.querySelector('.item-stock-badge');
    const stockVal = tr.querySelector('.item-stock-val');
    const priceSourceEl = tr.querySelector('.item-price-source');

    let isInitialLoad = !!data.barang_id;

    selBarang.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            inCode.value = opt.getAttribute('data-code') || '';
            inJenis.value = opt.getAttribute('data-jenis') || '';
            inSatuan.value = opt.getAttribute('data-satuan') || '';

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

            const stok = parseFloat(opt.getAttribute('data-stok') || 0);
            const satuan = opt.getAttribute('data-satuan') || 'Karton';

            badge.style.display = 'inline-flex';
            if (stok <= 0) {
                badge.className = 'stock-badge empty item-stock-badge';
                stockVal.textContent = `Kosong (0 ${satuan})`;
            } else {
                badge.className = 'stock-badge ready item-stock-badge';
                stockVal.textContent = `${stok} ${satuan}`;
            }
        } else {
            badge.style.display = 'none';
            if (priceSourceEl) priceSourceEl.style.display = 'none';
            inCode.value = '';
            inJenis.value = '';
            inSatuan.value = '';
        }
        calculateRow(tr);
        calculateGrandTotal();
    });

    // Deteksi jika user menyesuaikan harga secara manual
    inHarga.addEventListener('input', function() {
        if (priceSourceEl && priceSourceEl.style.display !== 'none') {
            priceSourceEl.style.color = '#d97706';
            priceSourceEl.innerHTML = `✏️ <em>Harga disesuaikan manual</em>`;
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
}

function calculateRow(tr) {
    const qty = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
    const harga = parseFloat(tr.querySelector('.item-harga')?.value) || 0;
    const diskonPersen = parseFloat(tr.querySelector('.item-diskon')?.value) || 0;
    const potonganRp = parseFloat(tr.querySelector('.item-potongan')?.value) || 0;
    const ppnTipe = tr.querySelector('.item-ppn')?.value || 'NON_PPN';

    const selBarang = tr.querySelector('.item-barang-select');
    const opt = selBarang?.selectedOptions[0];
    const badge = tr.querySelector('.item-stock-badge');
    const stockVal = tr.querySelector('.item-stock-val');

    if (badge && opt && opt.value) {
        const stok = parseFloat(opt.getAttribute('data-stok') || 0);
        const satuan = opt.getAttribute('data-satuan') || 'Karton';

        if (stok <= 0) {
            badge.className = 'stock-badge empty item-stock-badge';
            stockVal.textContent = `Kosong (0 ${satuan})`;
        } else if (qty > stok) {
            badge.className = 'stock-badge warning item-stock-badge';
            stockVal.innerHTML = `${stok} ${satuan} <span style="font-weight:normal;">(Kurang ${qty - stok})</span>`;
        } else {
            badge.className = 'stock-badge ready item-stock-badge';
            stockVal.textContent = `${stok} ${satuan}`;
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

    rows.forEach(tr => {
        totalBruto += parseFloat(tr.dataset.bruto || 0);
        totalDiskon += parseFloat(tr.dataset.diskon || 0);
        totalDpp += parseFloat(tr.dataset.netto || 0);
        totalPpn += parseFloat(tr.dataset.ppn || 0);
    });

    const headerPotongan = parseFloat(document.getElementById('headerPotonganInput')?.value) || 0;
    const finalDpp = Math.max(0, totalDpp - headerPotongan);
    const grandTotal = finalDpp + totalPpn;

    const elBruto = document.getElementById('lblSubtotalBruto');
    const elDiskon = document.getElementById('lblTotalDiskon');
    const elDpp = document.getElementById('lblDppNominal');
    const elPpn = document.getElementById('lblTotalPpn');
    const elGrand = document.getElementById('lblTotalTagihan');

    if (elBruto) elBruto.textContent = formatRupiah(totalBruto);
    if (elDiskon) elDiskon.textContent = '- ' + formatRupiah(totalDiskon);
    if (elDpp) elDpp.textContent = formatRupiah(finalDpp);
    if (elPpn) elPpn.textContent = '+ ' + formatRupiah(totalPpn);
    if (elGrand) elGrand.textContent = formatRupiah(grandTotal);
}

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
});
