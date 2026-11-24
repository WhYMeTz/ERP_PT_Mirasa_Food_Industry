/**
 * RETUR PENJUALAN - CREATE JAVASCRIPT
 * ERP PT Mirasa Food Industry
 */

document.addEventListener('DOMContentLoaded', function () {
    const soSelect = document.getElementById('so_id');
    const itemsTbody = document.getElementById('returItemsTbody');
    const returForm = document.getElementById('returCreateForm');

    // Dynamic SO change
    if (soSelect) {
        soSelect.addEventListener('change', function () {
            const soId = this.value;
            if (!soId) {
                clearSoDetails();
                return;
            }
            loadSoData(soId);
        });
    }

    // Inisialisasi listener pada baris yang sudah ada saat load
    bindAllRowEvents();
    calculateTotals();

    // Form submit check
    if (returForm) {
        returForm.addEventListener('submit', function (e) {
            let totalQty = 0;
            const qtyInputs = document.querySelectorAll('.qty-retur-input');
            qtyInputs.forEach(inp => {
                totalQty += parseFloat(inp.value || 0);
            });

            if (totalQty <= 0) {
                e.preventDefault();
                alert('Peringatan: Kuantitas barang yang diretur harus lebih dari 0 pada minimal 1 item.');
            }
        });
    }
});

function loadSoData(soId) {
    const infoBox = document.getElementById('soInfoBox');
    const itemsTbody = document.getElementById('returItemsTbody');

    fetch(`/penjualan/retur/so-data/${soId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                const data = res.data;

                // Update Info Box
                document.getElementById('infoSoNo').textContent = data.so_no || '-';
                document.getElementById('infoCustNm').textContent = data.customer_nm || '-';
                document.getElementById('infoSoTgl').textContent = data.so_tgl_formatted || '-';
                const sjInput = document.getElementById('infoSjNo');
                if (sjInput) sjInput.value = data.sj_no || '-';
                const fakturInput = document.getElementById('infoFakturNo');
                if (fakturInput) fakturInput.value = data.faktur_no || '-';
                if (infoBox) infoBox.style.display = 'flex';

                // Render Table Items
                itemsTbody.innerHTML = '';
                if (data.items && data.items.length > 0) {
                    data.items.forEach((item, idx) => {
                        const tr = document.createElement('tr');
                        tr.className = 'retur-item-row';
                        tr.dataset.sodtlId = item.sodtl_id;
                        tr.dataset.price = item.effective_price;
                        tr.dataset.max = item.max_returnable;

                        tr.innerHTML = `
                            <td style="text-align: center; color: #64748b; font-weight: 700;">${idx + 1}</td>
                            <td>
                                <strong style="color: #0f172a; display: block;">${item.barang_nm}</strong>
                                <span style="font-size: 0.725rem; color: #0284c7; font-family: monospace;">${item.barang_cd}</span>
                                <input type="hidden" name="items[${idx}][sodtl_id]" value="${item.sodtl_id}">
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #475569;">
                                ${item.kirim_qty}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #059669;">
                                ${item.max_returnable} ${item.satuan_nm}
                            </td>
                            <td style="width: 110px;">
                                <input type="number" step="any" min="0" max="${item.max_returnable}"
                                    name="items[${idx}][qty_retur]"
                                    class="form-control qty-retur-input"
                                    value="0"
                                    style="text-align: right; font-weight: 800; font-family: monospace; border: 1.5px solid #cbd5e1; border-radius: 5px; height: 32px;"
                                    data-idx="${idx}">
                            </td>
                            <td style="text-align: center; color: #475569; font-weight: 600;">
                                ${item.satuan_nm}
                            </td>
                            <td style="text-align: right; font-family: monospace; color: #475569;">
                                ${item.formatted_price}
                            </td>
                            <td style="width: 240px;">
                                <div class="disposisi-card-group">
                                    <label class="disposisi-option opt-simpan">
                                        <input type="radio" name="items[${idx}][perlakuan_cd]" value="MASUK_PERSEDIAAN" checked class="disposisi-radio" data-idx="${idx}">
                                        <span>Gunakan Kembali (Masuk Stok)</span>
                                    </label>
                                    <label class="disposisi-option opt-musnah">
                                        <input type="radio" name="items[${idx}][perlakuan_cd]" value="DIMUSNAHKAN" class="disposisi-radio" data-idx="${idx}">
                                        <span>Dimusnahkan (Scrap / Afkir)</span>
                                    </label>
                                </div>
                            </td>
                            <td style="width: 160px;">
                                <select name="items[${idx}][kondisi_cd]" class="form-control" style="font-size: 0.775rem; border: 1.5px solid #cbd5e1; border-radius: 5px; height: 32px;">
                                    <option value="RUSAK_KEMASAN">Kemasan Rusak / Cacat</option>
                                    <option value="REMUK_MELEMPEM">Remuk / Melempem</option>
                                    <option value="KADALUARSA">Kadaluarsa (Expired)</option>
                                    <option value="SALAH_KIRIM">Salah Varian / Varian Lebih</option>
                                    <option value="REJECT_CUSTOMER">Reject Mitra / Customer</option>
                                </select>
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 800; color: #0f172a;" class="row-subtotal">
                                Rp 0
                            </td>
                        `;
                        itemsTbody.appendChild(tr);
                    });
                    bindAllRowEvents();
                } else {
                    itemsTbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">Tidak ada item terkirim pada dokumen pesanan ini.</td></tr>`;
                }
                calculateTotals();
            } else {
                alert(res.message || 'Gagal mengambil rincian data SO.');
            }
        })
        .catch(err => {
            console.error('Error fetching SO data:', err);
            alert('Terjadi kesalahan saat memuat data pesanan.');
        });
}

function clearSoDetails() {
    const infoBox = document.getElementById('soInfoBox');
    if (infoBox) infoBox.style.display = 'none';

    const itemsTbody = document.getElementById('returItemsTbody');
    if (itemsTbody) {
        itemsTbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 2.5rem; color: #94a3b8;">Silakan pilih Dokumen Pesanan (SO) di atas untuk memuat rincian barang yang dapat diretur.</td></tr>`;
    }
    calculateTotals();
}

function bindAllRowEvents() {
    const rows = document.querySelectorAll('.retur-item-row');
    rows.forEach(row => {
        const qtyInput = row.querySelector('.qty-retur-input');
        const radios = row.querySelectorAll('.disposisi-radio');

        if (qtyInput) {
            qtyInput.addEventListener('input', function () {
                const max = parseFloat(row.dataset.max || 0);
                let val = parseFloat(this.value || 0);
                if (val > max) {
                    alert(`Kuantitas retur (${val}) melebihi kuantitas maksimal yang tersedia (${max}).`);
                    this.value = max;
                    val = max;
                }
                const price = parseFloat(row.dataset.price || 0);
                const subtotal = Math.round(val * price);
                const subtotalCell = row.querySelector('.row-subtotal');
                if (subtotalCell) {
                    subtotalCell.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
                }
                calculateTotals();
            });
        }

        radios.forEach(r => {
            r.addEventListener('change', calculateTotals);
        });
    });
}

function calculateTotals() {
    let totQtyRetur = 0;
    let totQtyMasuk = 0;
    let totQtyMusnah = 0;
    let totNilai = 0;

    const rows = document.querySelectorAll('.retur-item-row');
    rows.forEach(row => {
        const qtyInput = row.querySelector('.qty-retur-input');
        const qty = parseFloat(qtyInput?.value || 0);
        const price = parseFloat(row.dataset.price || 0);

        if (qty > 0) {
            totQtyRetur += qty;
            totNilai += (qty * price);

            const selectedRadio = row.querySelector('.disposisi-radio:checked');
            const disposisi = selectedRadio ? selectedRadio.value : 'MASUK_PERSEDIAAN';

            if (disposisi === 'DIMUSNAHKAN') {
                totQtyMusnah += qty;
            } else {
                totQtyMasuk += qty;
            }
        }
    });

    // Update Display Elements
    const dispQtyRetur = document.getElementById('dispTotQtyRetur');
    const dispQtyMasuk = document.getElementById('dispTotQtyMasuk');
    const dispQtyMusnah = document.getElementById('dispTotQtyMusnah');
    const dispTotNilai = document.getElementById('dispTotNilai');
    const warningMusnahBanner = document.getElementById('musnahWarningBanner');

    if (dispQtyRetur) dispQtyRetur.textContent = totQtyRetur.toLocaleString('id-ID') + ' Karton / Satuan';
    if (dispQtyMasuk) dispQtyMasuk.textContent = totQtyMasuk.toLocaleString('id-ID') + ' Karton';
    if (dispQtyMusnah) dispQtyMusnah.textContent = totQtyMusnah.toLocaleString('id-ID') + ' Karton';
    if (dispTotNilai) dispTotNilai.textContent = 'Rp ' + Math.round(totNilai).toLocaleString('id-ID');

    // Update Top Live KPI Widget
    const topLiveReturTotal = document.getElementById('topLiveReturTotal');
    const topLiveQtyRetur = document.getElementById('topLiveQtyRetur');
    if (topLiveReturTotal) topLiveReturTotal.textContent = 'Rp ' + Math.round(totNilai).toLocaleString('id-ID');
    if (topLiveQtyRetur) topLiveQtyRetur.textContent = totQtyRetur.toLocaleString('id-ID') + ' Karton';

    if (warningMusnahBanner) {
        if (totQtyMusnah > 0) {
            warningMusnahBanner.style.display = 'block';
            document.getElementById('countMusnahText').textContent = totQtyMusnah.toLocaleString('id-ID');
        } else {
            warningMusnahBanner.style.display = 'none';
        }
    }
}
