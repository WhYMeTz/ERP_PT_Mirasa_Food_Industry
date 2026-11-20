/**
 * FAKTUR PENJUALAN - CREATE JAVASCRIPT
 * PT Mirasa Food Industry
 */

document.addEventListener('DOMContentLoaded', function () {
    const pengeluaranSelect = document.getElementById('pengeluaran_id');
    const ppnTipeSelect = document.getElementById('ppn_tipe');
    const totalPotonganInput = document.getElementById('total_potongan');
    const tableBody = document.getElementById('fakturItemsBody');
    const btnSubmit = document.getElementById('btnSubmitFaktur');
    const fakturForm = document.getElementById('fakturCreateForm');

    // Inisialisasi jika ada prefilled data
    if (window.appConfig && window.appConfig.prefilledData) {
        populateFakturData(window.appConfig.prefilledData);
    }

    // Event listener pemilihan Surat Jalan
    if (pengeluaranSelect) {
        pengeluaranSelect.addEventListener('change', function () {
            const pengeluaranId = this.value;
            if (!pengeluaranId) {
                resetFormDetails();
                return;
            }

            loadSuratJalanDetail(pengeluaranId);
        });
    }

    // Event listener pergantian tipe PPN
    if (ppnTipeSelect) {
        ppnTipeSelect.addEventListener('change', function () {
            recalculateAll();
        });
    }

    // Event listener input potongan tambahan
    if (totalPotonganInput) {
        totalPotonganInput.addEventListener('input', function () {
            recalculateSummary();
        });
    }

    // Event submit form validasi
    if (fakturForm) {
        fakturForm.addEventListener('submit', function (e) {
            if (!pengeluaranSelect.value) {
                e.preventDefault();
                showFeedback('Pilih dokumen Surat Jalan terlebih dahulu.', 'warning');
                pengeluaranSelect.focus();
                return;
            }

            const rowCount = tableBody.querySelectorAll('tr.item-row').length;
            if (rowCount === 0) {
                e.preventDefault();
                showFeedback('Rincian barang dalam faktur tidak boleh kosong.', 'warning');
                return;
            }

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `
                    <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" style="margin-right: 0.4rem;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Menerbitkan Faktur...</span>
                `;
            }
        });
    }
});

/**
 * Fetch detail Surat Jalan via AJAX
 */
function loadSuratJalanDetail(pengeluaranId) {
    const url = `/penjualan/faktur/ajax-surat-jalan-detail/${pengeluaranId}`;

    // Tampilkan placeholder loading pada tabel
    const tableBody = document.getElementById('fakturItemsBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 2rem; color: #64748b;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="animate-spin" style="margin: 0 auto 0.5rem auto; display: block; color: #059669;">
                    <circle cx="12" cy="12" r="10" stroke-width="4" stroke-opacity="0.25"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Mengambil data pengiriman &amp; rincian barang...</span>
            </td>
        </tr>
    `;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal mengambil data pengiriman.');
            }
            return response.json();
        })
        .then(res => {
            if (res.success && res.data) {
                populateFakturData(res.data);
                showFeedback('Data pengiriman dan harga berhasil dimuat.', 'info');
            } else {
                showFeedback(res.message || 'Gagal memuat rincian Surat Jalan.', 'danger');
                resetFormDetails();
            }
        })
        .catch(err => {
            console.error(err);
            showFeedback('Terjadi kesalahan saat memuat rincian Surat Jalan.', 'danger');
            resetFormDetails();
        });
}

/**
 * Mengisi nilai input formulir dari payload data
 */
function populateFakturData(data) {
    // 1. Informasi Customer
    const customer = data.customer || {};
    document.getElementById('infoCustomerNm').textContent = customer.customer_nm || '-';
    document.getElementById('infoCustomerCd').textContent = customer.customer_cd || '-';
    document.getElementById('infoCustomerAlamat').textContent = customer.alamat_txt || customer.wilayah || '-';
    
    const badgeKlasifikasi = document.getElementById('badgeKlasifikasi');
    if (badgeKlasifikasi) {
        const klas = customer.klasifikasi || 'MANUAL';
        badgeKlasifikasi.textContent = (klas === 'MIDI_ALFA') ? 'Midi/Alfa' : klas;
        badgeKlasifikasi.className = 'badge-klasifikasi ' + (['IDM', 'MIDI_ALFA', 'IDM_ALFA'].includes(klas) ? 'modern' : klas.toLowerCase());
    }

    // 2. Referensi PO & Pengiriman
    const pengeluaran = data.pengeluaran || {};
    const order = data.order || {};

    document.getElementById('infoSjNo').textContent = pengeluaran.pengeluaran_no || '-';
    document.getElementById('infoSoNo').textContent = order.so_no || '-';
    document.getElementById('customer_po_no').value = data.customer_po_no || order.customer_po_no || '';
    document.getElementById('top_txt').value = data.top_txt || order.top_txt || customer.top_txt || '14 Hari';

    // 3. Nomor Faktur & Tanggal
    if (data.generated_faktur_no) {
        document.getElementById('faktur_no').value = data.generated_faktur_no;
    }
    if (data.tgl_jatuh_tempo) {
        document.getElementById('tgl_jatuh_tempo').value = data.tgl_jatuh_tempo;
    }

    // 4. Pengaturan PPN
    const ppnTipeSelect = document.getElementById('ppn_tipe');
    if (ppnTipeSelect) {
        ppnTipeSelect.value = data.ppn_tipe || (data.is_ppn ? 'PPN_11' : 'NON_PPN');
    }

    // 5. Render Baris Barang
    renderItemRows(data.items || []);

    // 6. Hitung Akumulasi
    recalculateAll();
}

/**
 * Render baris barang ke tabel
 */
function renderItemRows(items) {
    const tableBody = document.getElementById('fakturItemsBody');
    tableBody.innerHTML = '';

    if (!items || items.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 1.5rem; color: #94a3b8;">
                    Tidak ada barang dalam dokumen pengeluaran ini.
                </td>
            </tr>
        `;
        return;
    }

    items.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = index;

        const qty = parseFloat(item.qty || 0);
        const harga = parseFloat(item.harga_satuan || 0);
        const diskon = parseFloat(item.diskon_nominal || 0);

        tr.innerHTML = `
            <td style="text-align: center; color: #64748b; font-weight: 600;">
                ${index + 1}
                <input type="hidden" name="items[${index}][pengeluaran_dtl_id]" value="${item.pengeluaran_dtl_id || ''}">
                <input type="hidden" name="items[${index}][sodtl_id]" value="${item.sodtl_id || ''}">
                <input type="hidden" name="items[${index}][barang_id]" value="${item.barang_id}">
                <input type="hidden" name="items[${index}][barang_nm]" value="${item.barang_nm}">
                <input type="hidden" name="items[${index}][satuan]" value="${item.satuan || 'Karton'}">
            </td>
            <td>
                <div style="font-weight: 700; color: #1e293b;">${item.barang_nm}</div>
                <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">Kode: #${item.barang_id}</div>
            </td>
            <td style="text-align: right; width: 100px;">
                <input type="number" step="any" min="0.0001" name="items[${index}][qty]" 
                       class="fk-input input-qty" value="${qty}" style="text-align: right; font-weight: 700;" oninput="onItemFieldChange(${index})">
                <span style="font-size: 0.7rem; color: #64748b;">${item.satuan || 'Karton'}</span>
            </td>
            <td style="text-align: right; width: 140px;">
                <input type="number" step="any" min="0" name="items[${index}][harga_satuan]" 
                       class="fk-input input-harga" value="${harga}" style="text-align: right; font-family: monospace;" oninput="onItemFieldChange(${index})">
            </td>
            <td style="text-align: right; width: 120px;">
                <input type="number" step="any" min="0" name="items[${index}][diskon_nominal]" 
                       class="fk-input input-diskon" value="${diskon}" style="text-align: right; font-family: monospace;" oninput="onItemFieldChange(${index})">
            </td>
            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #1e293b; width: 130px;" class="col-netto">
                Rp 0
            </td>
            <td style="text-align: right; font-family: monospace; color: #64748b; width: 110px;" class="col-ppn">
                Rp 0
                <input type="hidden" name="items[${index}][ppn_nominal]" class="input-ppn-nominal" value="0">
            </td>
            <td style="text-align: right; font-family: monospace; font-weight: 800; color: #059669; width: 140px;" class="col-total">
                Rp 0
            </td>
        `;

        tableBody.appendChild(tr);
    });
}

/**
 * Event listener saat kuantitas, harga satuan, atau diskon pada baris item berubah
 */
function onItemFieldChange(index) {
    const row = document.querySelector(`tr.item-row[data-index="${index}"]`);
    if (!row) return;

    recalculateRow(row);
    recalculateSummary();
}

/**
 * Hitung kalkulasi untuk 1 baris
 */
function recalculateRow(row) {
    const isPpn = document.getElementById('ppn_tipe').value === 'PPN_11';
    const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
    const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
    const diskon = parseFloat(row.querySelector('.input-diskon').value) || 0;

    const bruto = qty * harga;
    const netto = Math.max(0, bruto - diskon);
    const ppn = isPpn ? Math.round(netto * 0.11) : 0;
    const total = netto + ppn;

    row.querySelector('.col-netto').textContent = formatRupiah(netto);
    row.querySelector('.col-ppn').textContent = formatRupiah(ppn);
    row.querySelector('.input-ppn-nominal').value = ppn;
    row.querySelector('.col-total').textContent = formatRupiah(total);

    row.dataset.bruto = bruto;
    row.dataset.diskon = diskon;
    row.dataset.netto = netto;
    row.dataset.ppn = ppn;
    row.dataset.total = total;
}

/**
 * Recalculate semua baris dan summary
 */
function recalculateAll() {
    const rows = document.querySelectorAll('tr.item-row');
    rows.forEach(row => recalculateRow(row));
    recalculateSummary();
}

/**
 * Hitung ulang total box ringkasan
 */
function recalculateSummary() {
    const rows = document.querySelectorAll('tr.item-row');
    let subtotalBruto = 0;
    let totalDiskon = 0;
    let totalDpp = 0;
    let totalPpn = 0;
    let totalTagihan = 0;

    rows.forEach(row => {
        subtotalBruto += parseFloat(row.dataset.bruto || 0);
        totalDiskon += parseFloat(row.dataset.diskon || 0);
        totalDpp += parseFloat(row.dataset.netto || 0);
        totalPpn += parseFloat(row.dataset.ppn || 0);
        totalTagihan += parseFloat(row.dataset.total || 0);
    });

    const potongan = parseFloat(document.getElementById('total_potongan').value) || 0;
    const grandTotal = Math.max(0, totalTagihan - potongan);

    document.getElementById('summarySubtotalBruto').textContent = formatRupiah(subtotalBruto);
    document.getElementById('summaryTotalDiskon').textContent = formatRupiah(totalDiskon);
    document.getElementById('summaryDpp').textContent = formatRupiah(totalDpp);
    document.getElementById('summaryPpn').textContent = formatRupiah(totalPpn);
    document.getElementById('summaryTotalTagihan').textContent = formatRupiah(grandTotal);
}

/**
 * Reset form saat pemilihan dibatalkan
 */
function resetFormDetails() {
    document.getElementById('infoCustomerNm').textContent = '-';
    document.getElementById('infoCustomerCd').textContent = '-';
    document.getElementById('infoCustomerAlamat').textContent = '-';
    document.getElementById('infoSjNo').textContent = '-';
    document.getElementById('infoSoNo').textContent = '-';
    document.getElementById('customer_po_no').value = '';
    document.getElementById('faktur_no').value = '';
    document.getElementById('total_potongan').value = '0';

    const tableBody = document.getElementById('fakturItemsBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">
                Silakan pilih dokumen Surat Jalan terlebih dahulu.
            </td>
        </tr>
    `;

    recalculateSummary();
}

/**
 * Format angka ke format mata uang Rupiah
 */
function formatRupiah(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
}

/**
 * Feedback Alert Toast (Pengganti alert bawaan browser - Standar AGENTS.md)
 */
function showFeedback(message, type = 'info') {
    let container = document.getElementById('erpToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'erpToastContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '999999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '8px';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgColors = {
        success: '#059669',
        danger: '#dc2626',
        warning: '#d97706',
        info: '#0284c7'
    };

    toast.style.background = bgColors[type] || bgColors.info;
    toast.style.color = '#ffffff';
    toast.style.padding = '10px 16px';
    toast.style.borderRadius = '8px';
    toast.style.fontSize = '0.825rem';
    toast.style.fontWeight = '600';
    toast.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    toast.style.transition = 'all 0.2s ease';
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 250);
    }, 3500);
}
