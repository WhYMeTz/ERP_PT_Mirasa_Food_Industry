/**
 * ══════════════════════════════════════════════════════════════════════════
 * PENJUALAN LANGSUNG PABRIK (DIRECT SALES) - CREATE ENTERPRISE JAVASCRIPT
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('itemsTableBody');
    const btnAddItem = document.getElementById('btnAddItem');
    const itemTemplate = document.getElementById('itemRowTemplate');

    const subtotalBrutoEl = document.getElementById('displaySubtotalBruto');
    const potonganGlobalInput = document.getElementById('potonganGlobal');
    const isPpnCheck = document.getElementById('isPpnCheck');
    const displayPpnStatus = document.getElementById('displayPpnStatus');
    const dppRowEl = document.getElementById('rowDpp');
    const displayDppEl = document.getElementById('displayDpp');
    const ppnRowEl = document.getElementById('rowPpn');
    const displayPpnEl = document.getElementById('displayPpn');
    const grandTotalEl = document.getElementById('displayGrandTotal');

    const metodeBayarHidden = document.getElementById('metodeBayar');
    const tabTunai = document.getElementById('tabTunai');
    const tabTransfer = document.getElementById('tabTransfer');
    const tabQris = document.getElementById('tabQris');
    const submitBtn = document.getElementById('btnSubmitDirectSale');
    const directSalesForm = document.getElementById('directSalesForm');

    let rowCounter = 0;

    // Dynamic Generator Nomor Dokumen Resmi saat Tanggal Berubah
    const tglTransaksiInput = document.getElementById('tgl_transaksi');
    const noTransaksiInput = document.getElementById('no_transaksi');
    if (tglTransaksiInput && noTransaksiInput && window.directSalesConfig?.generateCodeUrl) {
        tglTransaksiInput.addEventListener('change', function () {
            const dateVal = this.value;
            if (!dateVal) return;
            fetch(`${window.directSalesConfig.generateCodeUrl}?type=direct_sale&date=${dateVal}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.code) {
                        noTransaksiInput.value = data.code;
                    }
                })
                .catch(err => console.error('Error generating direct sale code:', err));
        });
    }

    // Dynamic Sinkronisasi Ketersediaan Stok saat Gudang Pengeluaran Berubah
    const gudangSelect = document.getElementById('gudang_id');
    if (gudangSelect) {
        gudangSelect.addEventListener('change', function () {
            updateAllProductStocks(this.value);
        });
    }

    function updateAllProductStocks(selectedGudangId) {
        const selects = document.querySelectorAll('.product-select');
        selects.forEach(sel => {
            Array.from(sel.options).forEach(opt => {
                if (!opt.value) return;
                let stocksMap = {};
                try {
                    stocksMap = JSON.parse(opt.dataset.stocks || '{}');
                } catch (e) {
                    stocksMap = {};
                }
                const stock = parseFloat(stocksMap[selectedGudangId] || 0);
                opt.dataset.stock = stock;
                const unit = opt.dataset.unit || '-';
                const name = opt.dataset.name || '';
                const code = opt.dataset.code || '';
                if (name && code) {
                    opt.textContent = `${name} [${code}] - Stok: ${stock.toLocaleString('id-ID')} ${unit}`;
                }
            });

            // Update badge stok pada baris aktif
            const row = sel.closest('.item-row');
            if (row) {
                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.value) {
                    const stock = parseFloat(opt.dataset.stock) || 0;
                    const unit = opt.dataset.unit || '-';
                    const stockInfo = row.querySelector('.stock-info');
                    updateStockBadge(stockInfo, stock, unit);
                }
            }
        });

        // Update juga pada template baris untuk baris yang baru ditambah
        if (itemTemplate) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = itemTemplate.innerHTML;
            const tplSelect = tempDiv.querySelector('.product-select');
            if (tplSelect) {
                Array.from(tplSelect.options).forEach(opt => {
                    if (!opt.value) return;
                    let stocksMap = {};
                    try {
                        stocksMap = JSON.parse(opt.dataset.stocks || '{}');
                    } catch (e) {
                        stocksMap = {};
                    }
                    const stock = parseFloat(stocksMap[selectedGudangId] || 0);
                    opt.dataset.stock = stock;
                    const unit = opt.dataset.unit || '-';
                    const name = opt.dataset.name || '';
                    const code = opt.dataset.code || '';
                    if (name && code) {
                        opt.textContent = `${name} [${code}] - Stok: ${stock.toLocaleString('id-ID')} ${unit}`;
                    }
                });
                itemTemplate.innerHTML = tempDiv.innerHTML;
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // 2. HELPER FORMATTING & KALKULASI TABEL ITEM
    // ══════════════════════════════════════════════════════════════════════
    function formatRupiah(number) {
        return 'Rp ' + Math.round(number).toLocaleString('id-ID');
    }

    function renumberRows() {
        const rows = tableBody.querySelectorAll('.item-row');
        rows.forEach((row, idx) => {
            const numCell = row.querySelector('.row-num');
            if (numCell) numCell.textContent = idx + 1;
        });
    }

    function addRow() {
        rowCounter++;
        const index = rowCounter;
        const html = itemTemplate.innerHTML.replace(/__INDEX__/g, index);
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = index;
        tr.innerHTML = html;
        tableBody.appendChild(tr);

        attachRowEvents(tr);
        renumberRows();
        recalculateTotals();
    }

    function updateStockBadge(stockInfoEl, stock, unit) {
        if (!stockInfoEl) return;
        if (stock <= 0) {
            stockInfoEl.innerHTML = `<span class="stock-tag out-stock">Kosong di Gudang Ini (0 ${unit})</span>`;
        } else {
            stockInfoEl.innerHTML = `<span class="stock-tag in-stock">Ready ${stock.toLocaleString('id-ID')} ${unit}</span>`;
        }
    }

    function attachRowEvents(row) {
        const productSelect = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const discInput = row.querySelector('.disc-input');
        const unitDisplay = row.querySelector('.unit-display');
        const stockInfo = row.querySelector('.stock-info');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        const btnRemove = row.querySelector('.btn-remove-row');

        productSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                const price = parseFloat(opt.dataset.price) || 0;
                const stock = parseFloat(opt.dataset.stock) || 0;
                const unit = opt.dataset.unit || '-';

                priceInput.value = price;
                if (unitDisplay) unitDisplay.textContent = unit;
                updateStockBadge(stockInfo, stock, unit);
            } else {
                if (stockInfo) stockInfo.innerHTML = '-';
                if (unitDisplay) unitDisplay.textContent = '-';
            }
            calculateRowSubtotal(row);
        });

        [qtyInput, priceInput, discInput].forEach(inp => {
            if (inp) {
                inp.addEventListener('input', () => calculateRowSubtotal(row));
                inp.addEventListener('change', () => calculateRowSubtotal(row));
            }
        });

        if (btnRemove) {
            btnRemove.addEventListener('click', function () {
                const totalRows = tableBody.querySelectorAll('.item-row').length;
                if (totalRows <= 1) {
                    alert('Minimal harus ada 1 baris produk penjualan.');
                    return;
                }
                row.remove();
                renumberRows();
                recalculateTotals();
            });
        }
    }

    function calculateRowSubtotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const disc = parseFloat(row.querySelector('.disc-input')?.value) || 0;

        const bruto = qty * price;
        const discNominal = bruto * (disc / 100);
        const subtotal = Math.max(0, bruto - discNominal);

        const subtotalDisplay = row.querySelector('.subtotal-display');
        if (subtotalDisplay) {
            subtotalDisplay.dataset.value = subtotal;
            subtotalDisplay.textContent = formatRupiah(subtotal);
        }

        recalculateTotals();
    }

    function recalculateTotals() {
        let totalBruto = 0;
        const rows = tableBody.querySelectorAll('.item-row');

        rows.forEach(row => {
            const sub = parseFloat(row.querySelector('.subtotal-display')?.dataset?.value) || 0;
            totalBruto += sub;
        });

        if (subtotalBrutoEl) {
            subtotalBrutoEl.textContent = formatRupiah(totalBruto);
        }

        const potonganGlobal = parseFloat(potonganGlobalInput?.value) || 0;
        const isPpn = isPpnCheck && isPpnCheck.checked;

        const nettoAfterPotongan = Math.max(0, totalBruto - potonganGlobal);
        let ppnNominal = 0;
        let dppNominal = nettoAfterPotongan;

        if (isPpn) {
            ppnNominal = Math.round(dppNominal * 0.11);
            if (dppRowEl) dppRowEl.style.display = 'flex';
            if (displayDppEl) displayDppEl.textContent = formatRupiah(dppNominal);
            if (ppnRowEl) ppnRowEl.style.display = 'flex';
            if (displayPpnEl) displayPpnEl.textContent = '+ ' + formatRupiah(ppnNominal);
            if (displayPpnStatus) displayPpnStatus.textContent = 'PPN 11%';
        } else {
            if (dppRowEl) dppRowEl.style.display = 'none';
            if (ppnRowEl) ppnRowEl.style.display = 'none';
            if (displayPpnStatus) displayPpnStatus.textContent = 'Non-PPN';
        }

        const grandTotal = dppNominal + ppnNominal;
        if (grandTotalEl) {
            grandTotalEl.dataset.grandTotal = grandTotal;
            grandTotalEl.textContent = formatRupiah(grandTotal);
        }
    }

    if (btnAddItem) {
        btnAddItem.addEventListener('click', addRow);
    }

    if (potonganGlobalInput) {
        potonganGlobalInput.addEventListener('input', recalculateTotals);
        potonganGlobalInput.addEventListener('change', recalculateTotals);
    }

    if (isPpnCheck) {
        isPpnCheck.addEventListener('change', recalculateTotals);
    }

    // ══════════════════════════════════════════════════════════════════════
    // 3. METODE PEMBAYARAN (TUNAI vs TRANSFER BANK vs QRIS)
    // ══════════════════════════════════════════════════════════════════════
    function selectPaymentTab(tab, method) {
        [tabTunai, tabTransfer, tabQris].forEach(t => { if (t) t.classList.remove('active'); });
        if (tab) tab.classList.add('active');
        if (metodeBayarHidden) metodeBayarHidden.value = method;
    }

    if (tabTunai) tabTunai.addEventListener('click', () => selectPaymentTab(tabTunai, 'TUNAI'));
    if (tabTransfer) tabTransfer.addEventListener('click', () => selectPaymentTab(tabTransfer, 'TRANSFER'));
    if (tabQris) tabQris.addEventListener('click', () => selectPaymentTab(tabQris, 'QRIS'));

    // Inisialisasi baris awal
    tableBody.querySelectorAll('.item-row').forEach(row => {
        attachRowEvents(row);
    });

    // ══════════════════════════════════════════════════════════════════════
    // 4. SUBMIT FORM VALIDATION
    // ══════════════════════════════════════════════════════════════════════
    if (directSalesForm) {
        directSalesForm.addEventListener('submit', function (e) {
            const rows = tableBody.querySelectorAll('.item-row');
            let hasValidItem = false;

            rows.forEach(row => {
                const select = row.querySelector('.product-select');
                const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
                if (select && select.value && qty > 0) {
                    hasValidItem = true;
                }
            });

            if (!hasValidItem) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 produk siap jual dengan kuantitas lebih dari 0.');
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin" width="18" height="18" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Memproses Penjualan...</span>
                `;
            }
        });
    }
});
