/**
 * Modul Keuangan: Bukti Kas Masuk (BKM) - Create Form JS
 * PT Mirasa Food Industry - Enterprise ERP
 */

let itemRowIndex = 0;

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi index berdasarkan baris yang sudah ada
    const existingRows = document.querySelectorAll('.item-row');
    itemRowIndex = existingRows.length;

    // Hitung total awal
    calculateGrandTotal();

    // Event listener untuk kategori cards
    initCategoryCards();
});

/**
 * Inisialisasi card pilihan kategori
 */
function initCategoryCards() {
    const cards = document.querySelectorAll('.category-card-option');
    cards.forEach(card => {
        card.addEventListener('click', function () {
            cards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                onCategoryChanged(radio.value);
            }
        });
    });
}

/**
 * Respon saat kategori penerimaan berubah
 */
function onCategoryChanged(kategori) {
    const customerGroup = document.getElementById('groupCustomerPelanggan');
    const penyetorInput = document.getElementById('diterima_dari');
    const quickTemplateContainer = document.getElementById('quickTemplates');

    if (kategori === 'PELUNASAN_PIUTANG') {
        if (customerGroup) customerGroup.style.display = 'block';
    } else {
        if (customerGroup) customerGroup.style.display = 'none';
    }

    // Saran template deskripsi cepat jika tabel masih kosong atau hanya 1 baris kosong
    const firstDesc = document.querySelector('input[name="items[0][deskripsi]"]');
    if (firstDesc && !firstDesc.value) {
        if (kategori === 'PENJUALAN_LIMBAH') {
            firstDesc.value = 'Penjualan Minyak Goreng Bekas (Jelantah)';
            const firstSatuan = document.querySelector('input[name="items[0][satuan]"]');
            if (firstSatuan) firstSatuan.value = 'kg';
        } else if (kategori === 'PENJUALAN_TUNAI') {
            firstDesc.value = 'Penjualan Tunai Remah Keripik / Eceran Pabrik';
            const firstSatuan = document.querySelector('input[name="items[0][satuan]"]');
            if (firstSatuan) firstSatuan.value = 'kg';
        } else if (kategori === 'SETORAN_MODAL') {
            firstDesc.value = 'Setoran Tambahan Modal Kerja Operasional Pabrik';
            const firstSatuan = document.querySelector('input[name="items[0][satuan]"]');
            if (firstSatuan) firstSatuan.value = 'paket';
            if (penyetorInput && !penyetorInput.value) penyetorInput.value = 'Owner / Pemilik PT Mirasa';
        } else if (kategori === 'PENDAPATAN_LAIN') {
            firstDesc.value = 'Penerimaan Pendapatan Lain-lain / Jasa Operasional';
        }
    }
}

/**
 * Tambah Baris Rincian Item Baru
 */
function addItemRow(deskripsi = '', qty = 1, satuan = 'kg', harga = 0) {
    const tbody = document.getElementById('itemTableBody');
    if (!tbody) return;

    const idx = itemRowIndex++;
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.id = `row_${idx}`;

    tr.innerHTML = `
        <td style="text-align: center; color: #64748b; font-weight: 700;" class="row-number">
            ${tbody.children.length + 1}
        </td>
        <td>
            <input type="text" 
                   name="items[${idx}][deskripsi]" 
                   class="form-control" 
                   value="${deskripsi}" 
                   placeholder="Misal: Penjualan Minyak Jelantah / Kulit Singkong..." 
                   required>
        </td>
        <td style="width: 120px;">
            <input type="number" 
                   name="items[${idx}][qty]" 
                   class="form-control input-qty" 
                   value="${qty}" 
                   step="any" 
                   min="0.0001" 
                   style="text-align: right;" 
                   oninput="calculateRowSubtotal(${idx})" 
                   required>
        </td>
        <td style="width: 110px;">
            <input type="text" 
                   name="items[${idx}][satuan]" 
                   class="form-control" 
                   value="${satuan}" 
                   placeholder="kg / sak / drum">
        </td>
        <td style="width: 180px;">
            <div style="position: relative;">
                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: #64748b; font-weight: 600;">Rp</span>
                <input type="number" 
                       name="items[${idx}][harga_satuan]" 
                       class="form-control input-harga" 
                       value="${harga}" 
                       step="any" 
                       min="0" 
                       style="padding-left: 2rem; text-align: right;" 
                       oninput="calculateRowSubtotal(${idx})" 
                       required>
            </div>
        </td>
        <td style="width: 180px; text-align: right;">
            <span class="subtotal-display" id="subtotalDisplay_${idx}" style="font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                Rp 0
            </span>
            <input type="hidden" name="items[${idx}][subtotal]" id="subtotalInput_${idx}" value="0">
        </td>
        <td style="width: 50px; text-align: center;">
            <button type="button" class="btn-remove-row" onclick="removeItemRow(${idx})" title="Hapus Baris">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    calculateRowSubtotal(idx);
    updateRowNumbers();
}

/**
 * Hapus Baris Rincian Item
 */
function removeItemRow(idx) {
    const tbody = document.getElementById('itemTableBody');
    if (!tbody) return;

    if (tbody.children.length <= 1) {
        alert('Minimal harus ada 1 rincian item penerimaan.');
        return;
    }

    const row = document.getElementById(`row_${idx}`);
    if (row) {
        row.remove();
        updateRowNumbers();
        calculateGrandTotal();
    }
}

/**
 * Update Nomor Urut Baris di Tabel
 */
function updateRowNumbers() {
    const rows = document.querySelectorAll('#itemTableBody tr');
    rows.forEach((row, i) => {
        const numCell = row.querySelector('.row-number');
        if (numCell) numCell.textContent = i + 1;
    });
}

/**
 * Hitung Subtotal per Baris
 */
function calculateRowSubtotal(idx) {
    const row = document.getElementById(`row_${idx}`);
    if (!row) return;

    const qtyInput = row.querySelector('.input-qty');
    const hargaInput = row.querySelector('.input-harga');
    const displaySpan = document.getElementById(`subtotalDisplay_${idx}`);
    const hiddenInput = document.getElementById(`subtotalInput_${idx}`);

    const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
    const harga = parseFloat(hargaInput ? hargaInput.value : 0) || 0;
    const subtotal = Math.round(qty * harga);

    if (displaySpan) {
        displaySpan.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    }
    if (hiddenInput) {
        hiddenInput.value = subtotal;
    }

    calculateGrandTotal();
}

/**
 * Hitung Grand Total Seluruh Baris
 */
function calculateGrandTotal() {
    const subtotalInputs = document.querySelectorAll('input[name^="items"][name$="[subtotal]"]');
    let grandTotal = 0;

    subtotalInputs.forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });

    const displayGrand = document.getElementById('grandTotalDisplay');
    const displayTerbilang = document.getElementById('terbilangDisplay');

    if (displayGrand) {
        displayGrand.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    if (displayTerbilang) {
        displayTerbilang.textContent = terbilang(grandTotal) + ' Rupiah';
    }
}

/**
 * Fungsi Konversi Angka ke Kata Terbilang Rupiah
 */
function terbilang(n) {
    n = Math.floor(n);
    if (n === 0) return 'Nol';

    const bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    let hasil = '';

    if (n < 12) {
        hasil = bilangan[n];
    } else if (n < 20) {
        hasil = terbilang(n - 10) + ' Belas';
    } else if (n < 100) {
        hasil = terbilang(Math.floor(n / 10)) + ' Puluh ' + terbilang(n % 10);
    } else if (n < 200) {
        hasil = 'Seratus ' + terbilang(n - 100);
    } else if (n < 1000) {
        hasil = terbilang(Math.floor(n / 100)) + ' Ratus ' + terbilang(n % 100);
    } else if (n < 2000) {
        hasil = 'Seribu ' + terbilang(n - 1000);
    } else if (n < 1000000) {
        hasil = terbilang(Math.floor(n / 1000)) + ' Ribu ' + terbilang(n % 1000);
    } else if (n < 1000000000) {
        hasil = terbilang(Math.floor(n / 1000000)) + ' Juta ' + terbilang(n % 1000000);
    } else if (n < 1000000000000) {
        hasil = terbilang(Math.floor(n / 1000000000)) + ' Miliar ' + terbilang(n % 1000000000);
    } else {
        hasil = terbilang(Math.floor(n / 1000000000000)) + ' Triliun ' + terbilang(n % 1000000000000);
    }

    return hasil.trim().replace(/\s+/g, ' ');
}
