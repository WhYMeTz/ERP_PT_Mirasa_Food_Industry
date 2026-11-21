/**
 * ══════════════════════════════════════════════════════════════════════════
 * PENJUALAN BAHAN PENOLONG & BUMBU - CREATE ENTERPRISE JAVASCRIPT
 * Mengikuti standar modul Penjualan Langsung Pabrik (ERP PT Mirasa)
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
    const tabTempo = document.getElementById('tabTempo');

    let rowCounter = 0;

    // Sinkronisasi Ketersediaan Stok saat Gudang Berubah
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

    function updateStockBadge(stockInfoEl, stock, unit) {
        if (!stockInfoEl) return;
        if (stock > 0) {
            stockInfoEl.innerHTML = `<span style="display: inline-block; background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.725rem;">
                Tersedia ${stock.toLocaleString('id-ID')} ${unit}
            </span>`;
        } else {
            stockInfoEl.innerHTML = `<span style="display: inline-block; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.725rem;">
                Stok Kosong
            </span>`;
        }
    }

    // Event Handler untuk Row
    function bindRowEvents(row) {
        const prodSelect = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const discInput = row.querySelector('.disc-input');
        const removeBtn = row.querySelector('.btn-remove-row');
        const unitDisplay = row.querySelector('.unit-display');
        const stockInfo = row.querySelector('.stock-info');

        if (prodSelect) {
            prodSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    const price = parseFloat(opt.dataset.price) || 0;
                    const stock = parseFloat(opt.dataset.stock) || 0;
                    const unit = opt.dataset.unit || '-';

                    if (priceInput && (!priceInput.value || parseFloat(priceInput.value) === 0)) {
                        priceInput.value = price;
                    }
                    if (unitDisplay) unitDisplay.textContent = unit;
                    updateStockBadge(stockInfo, stock, unit);
                } else {
                    if (unitDisplay) unitDisplay.textContent = '-';
                    if (stockInfo) stockInfo.textContent = '-';
                }
                calculateRowSubtotal(row);
                calculateGrandTotals();
            });
        }

        [qtyInput, priceInput, discInput].forEach(inp => {
            if (inp) {
                inp.addEventListener('input', function () {
                    calculateRowSubtotal(row);
                    calculateGrandTotals();
                });
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                const totalRows = tableBody.querySelectorAll('.item-row').length;
                if (totalRows <= 1) {
                    alert('Minimal harus ada 1 baris item penjualan.');
                    return;
                }
                row.remove();
                reindexRows();
                calculateGrandTotals();
            });
        }
    }

    function calculateRowSubtotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const disc = parseFloat(row.querySelector('.disc-input')?.value) || 0;

        const gross = qty * price;
        const discAmount = gross * (disc / 100);
        const subtotal = Math.max(0, gross - discAmount);

        const subtotalDisplay = row.querySelector('.subtotal-display');
        if (subtotalDisplay) {
            subtotalDisplay.dataset.value = subtotal;
            subtotalDisplay.textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
        }
    }

    function calculateGrandTotals() {
        let subtotalBruto = 0;
        const rows = tableBody.querySelectorAll('.item-row');

        rows.forEach(r => {
            const disp = r.querySelector('.subtotal-display');
            if (disp) {
                subtotalBruto += parseFloat(disp.dataset.value) || 0;
            }
        });

        const potongan = parseFloat(potonganGlobalInput?.value) || 0;
        const dpp = Math.max(0, subtotalBruto - potongan);

        const isPpn = isPpnCheck && isPpnCheck.checked;
        let ppn = 0;

        if (isPpn) {
            ppn = Math.round(dpp * 0.11);
            if (displayPpnStatus) displayPpnStatus.textContent = 'PPN 11%';
            if (dppRowEl) dppRowEl.style.display = 'flex';
            if (ppnRowEl) ppnRowEl.style.display = 'flex';
            if (displayDppEl) displayDppEl.textContent = 'Rp ' + Math.round(dpp).toLocaleString('id-ID');
            if (displayPpnEl) displayPpnEl.textContent = '+ Rp ' + ppn.toLocaleString('id-ID');
        } else {
            if (displayPpnStatus) displayPpnStatus.textContent = 'Non-PPN';
            if (dppRowEl) dppRowEl.style.display = 'none';
            if (ppnRowEl) ppnRowEl.style.display = 'none';
        }

        const grandTotal = Math.round(dpp + ppn);

        if (subtotalBrutoEl) subtotalBrutoEl.textContent = 'Rp ' + Math.round(subtotalBruto).toLocaleString('id-ID');
        if (grandTotalEl) {
            grandTotalEl.dataset.grandTotal = grandTotal;
            grandTotalEl.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }
    }

    function reindexRows() {
        const rows = tableBody.querySelectorAll('.item-row');
        rows.forEach((r, idx) => {
            const numEl = r.querySelector('.row-num');
            if (numEl) numEl.textContent = idx + 1;
        });
    }

    // Tambah Baris Baru
    if (btnAddItem && itemTemplate && tableBody) {
        btnAddItem.addEventListener('click', function () {
            rowCounter++;
            const totalRows = tableBody.querySelectorAll('.item-row').length;
            const newIndex = totalRows;

            let html = itemTemplate.innerHTML;
            html = html.replace(/__INDEX__/g, newIndex);
            html = html.replace(/__NUM__/g, newIndex + 1);

            const tempWrap = document.createElement('tbody');
            tempWrap.innerHTML = html;
            const newRow = tempWrap.firstElementChild;

            tableBody.appendChild(newRow);
            bindRowEvents(newRow);
            reindexRows();
        });
    }

    // Inisialisasi baris pertama yang sudah ada di HTML
    const initialRows = tableBody.querySelectorAll('.item-row');
    initialRows.forEach(row => bindRowEvents(row));

    // Global listeners
    if (potonganGlobalInput) {
        potonganGlobalInput.addEventListener('input', calculateGrandTotals);
    }
    if (isPpnCheck) {
        isPpnCheck.addEventListener('change', calculateGrandTotals);
    }

    // Tab Metode Pembayaran
    function setActiveTab(activeBtn, methodValue) {
        [tabTunai, tabTransfer, tabTempo].forEach(b => {
            if (b) b.classList.remove('active');
        });
        if (activeBtn) activeBtn.classList.add('active');
        if (metodeBayarHidden) metodeBayarHidden.value = methodValue;
    }

    if (tabTunai) {
        tabTunai.addEventListener('click', function () {
            setActiveTab(this, 'TUNAI');
        });
    }
    if (tabTransfer) {
        tabTransfer.addEventListener('click', function () {
            setActiveTab(this, 'TRANSFER');
        });
    }
    if (tabTempo) {
        tabTempo.addEventListener('click', function () {
            setActiveTab(this, 'TEMPO');
        });
    }

    // Hitung awal saat load
    calculateGrandTotals();
});
