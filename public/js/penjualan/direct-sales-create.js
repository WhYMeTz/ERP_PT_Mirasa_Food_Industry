/**
 * ══════════════════════════════════════════════════════════════════════════
 * PENJUALAN LANGSUNG / TUNAI (DIRECT SALES) - CREATE CASHIER JAVASCRIPT
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
    const dppRowEl = document.getElementById('rowDpp');
    const displayDppEl = document.getElementById('displayDpp');
    const ppnRowEl = document.getElementById('rowPpn');
    const displayPpnEl = document.getElementById('displayPpn');
    const grandTotalEl = document.getElementById('displayGrandTotal');

    const metodeBayarHidden = document.getElementById('metodeBayar');
    const tabTunai = document.getElementById('tabTunai');
    const tabTransfer = document.getElementById('tabTransfer');
    const cashTenderSection = document.getElementById('cashTenderSection');
    const uangDiterimaInput = document.getElementById('uangDiterima');
    const displayKembalianEl = document.getElementById('displayKembalian');
    const kembalianBox = document.getElementById('kembalianBox');
    const submitBtn = document.getElementById('btnSubmitCashier');
    const directSalesForm = document.getElementById('directSalesForm');

    let rowCounter = 0;

    function formatRupiah(number) {
        return 'Rp ' + Math.round(number).toLocaleString('id-ID');
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
        recalculateTotals();
    }

    function attachRowEvents(row) {
        const productSelect = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const discInput = row.querySelector('.disc-input');
        const btnRemove = row.querySelector('.btn-remove-row');

        productSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const price = parseFloat(opt.dataset.price || 0);
            const stock = parseFloat(opt.dataset.stock || 0);
            const unit = opt.dataset.unit || 'Karton';

            priceInput.value = price;

            const stockInfo = row.querySelector('.stock-info');
            if (this.value) {
                if (stock > 0) {
                    stockInfo.innerHTML = `<span class="stock-tag in-stock">Stok: ${stock.toLocaleString('id-ID')} ${unit}</span>`;
                } else {
                    stockInfo.innerHTML = `<span class="stock-tag out-stock">Stok Kosong (0 ${unit})</span>`;
                }
            } else {
                stockInfo.innerHTML = '';
            }

            calculateRow(row);
        });

        qtyInput.addEventListener('input', function () {
            validateRowStock(row);
            calculateRow(row);
        });

        priceInput.addEventListener('input', function () {
            calculateRow(row);
        });

        discInput.addEventListener('input', function () {
            calculateRow(row);
        });

        btnRemove.addEventListener('click', function () {
            const rows = tableBody.querySelectorAll('.item-row');
            if (rows.length > 1) {
                row.remove();
                recalculateTotals();
            } else {
                alert('Transaksi minimal memiliki 1 baris produk.');
            }
        });
    }

    function validateRowStock(row) {
        const select = row.querySelector('.product-select');
        const opt = select.options[select.selectedIndex];
        const stock = parseFloat(opt?.dataset.stock || 0);
        const qty = parseFloat(row.querySelector('.qty-input').value || 0);
        const stockInfo = row.querySelector('.stock-info');
        const unit = opt?.dataset.unit || 'Karton';

        if (select.value && qty > stock) {
            stockInfo.innerHTML = `<span class="stock-tag out-stock">Perhatian: Qty (${qty}) melebihi stok siap jual (${stock} ${unit})</span>`;
        } else if (select.value && stock > 0) {
            stockInfo.innerHTML = `<span class="stock-tag in-stock">Stok: ${stock.toLocaleString('id-ID')} ${unit}</span>`;
        }
    }

    function calculateRow(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discPersen = parseFloat(row.querySelector('.disc-input').value) || 0;

        const gross = qty * price;
        const discNominal = gross * (discPersen / 100);
        const subtotal = Math.max(0, gross - discNominal);

        row.querySelector('.subtotal-display').textContent = formatRupiah(subtotal);
        row.querySelector('.subtotal-display').dataset.value = subtotal;

        recalculateTotals();
    }

    function recalculateTotals() {
        let subtotalBruto = 0;
        tableBody.querySelectorAll('.item-row').forEach(row => {
            const val = parseFloat(row.querySelector('.subtotal-display').dataset.value || 0);
            subtotalBruto += val;
        });

        subtotalBrutoEl.textContent = formatRupiah(subtotalBruto);

        const potonganGlobal = parseFloat(potonganGlobalInput.value) || 0;
        const dpp = Math.max(0, subtotalBruto - potonganGlobal);

        let ppn = 0;
        if (isPpnCheck && isPpnCheck.checked) {
            ppn = Math.round(dpp * 0.11);
            dppRowEl.style.display = 'flex';
            ppnRowEl.style.display = 'flex';
            displayDppEl.textContent = formatRupiah(dpp);
            displayPpnEl.textContent = formatRupiah(ppn);
        } else {
            dppRowEl.style.display = 'none';
            ppnRowEl.style.display = 'none';
        }

        const grandTotal = dpp + ppn;
        grandTotalEl.textContent = formatRupiah(grandTotal);
        grandTotalEl.dataset.grandTotal = grandTotal;

        calculateKembalian();
    }

    function calculateKembalian() {
        const grandTotal = parseFloat(grandTotalEl.dataset.grandTotal || 0);
        const metode = metodeBayarHidden.value;

        if (metode === 'TRANSFER') {
            uangDiterimaInput.value = grandTotal;
            displayKembalianEl.textContent = 'Rp 0';
            kembalianBox.classList.remove('kurang');
            submitBtn.disabled = false;
            return;
        }

        const uangDiterima = parseFloat(uangDiterimaInput.value) || 0;
        const kembalian = uangDiterima - grandTotal;

        if (uangDiterima <= 0 && grandTotal > 0) {
            displayKembalianEl.textContent = 'Rp 0';
            kembalianBox.classList.remove('kurang');
            submitBtn.disabled = false;
        } else if (kembalian >= 0) {
            displayKembalianEl.textContent = formatRupiah(kembalian);
            kembalianBox.classList.remove('kurang');
            submitBtn.disabled = false;
        } else {
            displayKembalianEl.textContent = 'Kurang ' + formatRupiah(Math.abs(kembalian));
            kembalianBox.classList.add('kurang');
        }
    }

    // Toggle Metode Bayar
    tabTunai.addEventListener('click', function () {
        metodeBayarHidden.value = 'TUNAI';
        tabTunai.classList.add('active');
        tabTransfer.classList.remove('active');
        cashTenderSection.style.display = 'block';
        calculateKembalian();
    });

    tabTransfer.addEventListener('click', function () {
        metodeBayarHidden.value = 'TRANSFER';
        tabTransfer.classList.add('active');
        tabTunai.classList.remove('active');
        cashTenderSection.style.display = 'none';
        calculateKembalian();
    });

    // Quick Cash Buttons
    document.querySelectorAll('.ds-quick-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const grandTotal = parseFloat(grandTotalEl.dataset.grandTotal || 0);
            const val = this.dataset.val;

            if (val === 'exact') {
                uangDiterimaInput.value = grandTotal;
            } else {
                uangDiterimaInput.value = parseFloat(val);
            }
            calculateKembalian();
        });
    });

    potonganGlobalInput.addEventListener('input', recalculateTotals);
    if (isPpnCheck) {
        isPpnCheck.addEventListener('change', recalculateTotals);
    }
    uangDiterimaInput.addEventListener('input', calculateKembalian);

    btnAddItem.addEventListener('click', addRow);

    // Initial Row Setup
    tableBody.querySelectorAll('.item-row').forEach(row => {
        attachRowEvents(row);
    });

    // Form submit validation
    directSalesForm.addEventListener('submit', function (e) {
        const rows = tableBody.querySelectorAll('.item-row');
        let hasValidItem = false;

        rows.forEach(row => {
            const select = row.querySelector('.product-select');
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            if (select.value && qty > 0) {
                hasValidItem = true;
            }
        });

        if (!hasValidItem) {
            e.preventDefault();
            alert('Silakan pilih minimal 1 produk siap jual dengan kuantitas lebih dari 0.');
            return;
        }

        const grandTotal = parseFloat(grandTotalEl.dataset.grandTotal || 0);
        const metode = metodeBayarHidden.value;
        const uangDiterima = parseFloat(uangDiterimaInput.value) || 0;

        if (metode === 'TUNAI' && uangDiterima < grandTotal) {
            const confirmProceed = confirm(
                `Uang yang diterima (Rp ${uangDiterima.toLocaleString('id-ID')}) kurang dari total tagihan (Rp ${grandTotal.toLocaleString('id-ID')}).\n\nTetap lanjutkan transaksi?`
            );
            if (!confirmProceed) {
                e.preventDefault();
                return;
            }
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin" width="18" height="18" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Memproses Transaksi...
        `;
    });
});
