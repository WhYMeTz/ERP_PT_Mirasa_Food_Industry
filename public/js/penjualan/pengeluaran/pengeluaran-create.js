/**
 * ==============================================================
 * PENGELUARAN BARANG / SURAT JALAN - CREATE FORM JAVASCRIPT
 * PT Mirasa Food Industry (Cap Payung)
 * ==============================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    const soSelect = document.getElementById('soSelect');
    const tableBody = document.getElementById('itemsTableBody');
    const totalQtyDisplay = document.getElementById('totalKirimQtyDisplay');
    const customerInfoBox = document.getElementById('customerInfoBox');

    // Customer Info Elements
    const custNmText = document.getElementById('custNmText');
    const custBadge = document.getElementById('custKlasifikasiBadge');
    const custAlamatText = document.getElementById('custAlamatText');
    const custWilayahText = document.getElementById('custWilayahText');
    const custPoText = document.getElementById('custPoText');

    function calculateTotalQty() {
        let total = 0;
        document.querySelectorAll('.input-qty-kirim').forEach(input => {
            const val = parseFloat(input.value) || 0;
            total += val;
        });

        if (totalQtyDisplay) {
            totalQtyDisplay.textContent = new Intl.NumberFormat('id-ID').format(total) + ' Karton';
        }
    }

    function renderItems(items) {
        if (!tableBody) return;
        tableBody.innerHTML = '';

        if (!items || items.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #64748b;">
                        Tidak ada rincian barang yang dapat dikirim dari PO ini.
                    </td>
                </tr>
            `;
            calculateTotalQty();
            return;
        }

        items.forEach((item, index) => {
            const sisa = parseFloat(item.sisa_qty) || 0;
            const stokFg = parseFloat(item.stok_fg) || 0;
            const isStockOk = stokFg >= sisa;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                    ${index + 1}
                    <input type="hidden" name="items[${index}][sodtl_id]" value="${item.sodtl_id}">
                </td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">${item.barang_nm}</div>
                    <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">Kode: ${item.barang_cd}</div>
                </td>
                <td style="text-align: right; color: #475569; font-weight: 600;">
                    ${new Intl.NumberFormat('id-ID').format(item.pesan_qty)} ${item.satuan_nm}
                </td>
                <td style="text-align: right; color: #0284c7; font-weight: 600;">
                    ${new Intl.NumberFormat('id-ID').format(item.kirim_qty_old)}
                </td>
                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                    ${new Intl.NumberFormat('id-ID').format(sisa)}
                </td>
                <td style="text-align: center;">
                    <span class="badge-stok ${isStockOk ? 'cukup' : 'kurang'}">
                        ${isStockOk ? '✓ Tersedia' : '⚠ Stok Kurang'} (${new Intl.NumberFormat('id-ID').format(stokFg)})
                    </span>
                </td>
                <td style="text-align: right;">
                    <input type="number" step="any" min="0" max="${sisa}"
                           name="items[${index}][kirim_qty]"
                           value="${sisa}"
                           class="input-qty-kirim"
                           data-sisa="${sisa}"
                           data-stok="${stokFg}"
                           required>
                </td>
            `;

            tableBody.appendChild(tr);
        });

        // Attach event listeners to quantity inputs
        document.querySelectorAll('.input-qty-kirim').forEach(input => {
            input.addEventListener('input', function () {
                const maxSisa = parseFloat(this.getAttribute('data-sisa')) || 0;
                let currentVal = parseFloat(this.value) || 0;

                if (currentVal > maxSisa) {
                    showFeedback(`Kuantitas kirim tidak boleh melebihi sisa pesanan (${maxSisa} karton).`);
                    this.value = maxSisa;
                }

                calculateTotalQty();
            });
        });

        calculateTotalQty();
    }

    function showFeedback(msg) {
        if (typeof window.showToast === 'function') {
            window.showToast('warning', msg);
        } else {
            console.warn(msg);
        }
    }

    if (soSelect) {
        soSelect.addEventListener('change', function () {
            const soId = this.value;
            if (!soId) {
                if (customerInfoBox) customerInfoBox.style.display = 'none';
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                                Silakan pilih dokumen PO Penjualan di atas untuk memuat rincian barang.
                            </td>
                        </tr>
                    `;
                }
                calculateTotalQty();
                return;
            }

            // AJAX load
            fetch(`/penjualan/pengeluaran/ajax-so-detail/${soId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const data = res.data;
                        const cust = data.customer;
                        const order = data.order;

                        if (customerInfoBox) {
                            customerInfoBox.style.display = 'grid';
                        }
                        if (custNmText) custNmText.textContent = cust ? cust.customer_nm : '-';
                        if (custAlamatText) custAlamatText.textContent = cust && cust.alamat_txt ? cust.alamat_txt : '-';
                        if (custWilayahText) custWilayahText.textContent = cust && cust.wilayah ? cust.wilayah : '-';
                        if (custPoText) custPoText.textContent = order && order.customer_po_no ? order.customer_po_no : '(Tidak ada PO Ext)';

                        if (custBadge) {
                            const k = cust ? cust.klasifikasi : 'MANUAL';
                            custBadge.textContent = k || 'MANUAL';
                            custBadge.className = 'badge-klasifikasi ' + (k === 'IFM' ? 'ifm' : (['IDM', 'MIDI_ALFA', 'IDM_ALFA'].includes(k) ? 'modern' : 'manual'));
                        }

                        renderItems(data.items);
                    } else {
                        showFeedback(res.message || 'Gagal memuat rincian pesanan.');
                    }
                })
                .catch(err => {
                    console.error('Error fetching SO detail:', err);
                    showFeedback('Terjadi kesalahan saat memuat data pesanan.');
                });
        });
    }

    // Form submit validation
    const sjForm = document.getElementById('sjCreateForm');
    if (sjForm) {
        sjForm.addEventListener('submit', function (e) {
            let total = 0;
            document.querySelectorAll('.input-qty-kirim').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            if (total <= 0) {
                e.preventDefault();
                showFeedback('Kuantitas barang yang dikirim tidak boleh kosong (minimal 1 item memiliki kuantitas > 0).');
                return false;
            }
        });
    }

    // Initial calculation if prefilled
    calculateTotalQty();
});
