/**
 * JavaScript Form Bukti Kas Keluar (BKK Operasional) - Modern Full-Width ERP
 */
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('bkkItemsBody');
    const btnAddRow = document.getElementById('btnAddRow');
    const totalDisplay = document.getElementById('bkkTotalDisplay');
    const terbilangDisplay = document.getElementById('bkkTerbilangDisplay');
    const categoryChips = document.querySelectorAll('.category-chip-label');
    const selectCoaHeader = document.getElementById('coa_id');
    const selectKasBank = document.getElementById('kas_bank_id');
    const saldoDisplay = document.getElementById('liveSaldoDisplay');
    const quickTags = document.querySelectorAll('.quick-tag-pill');

    // Pemetaan Kategori ke Kode COA Default
    const categoryCoaMap = {
        'LISTRIK_AIR': '6102',
        'BBM_TRANSPORT': '6107',
        'PEMELIHARAAN_MESIN': '6106',
        'PERLENGKAPAN_ATK': '6100',
        'KONSUMSI_RUMAH_TANGGA': '6105',
        'PENGIRIMAN_EKSPEDISI': '6111',
        'BIAYA_OPERASIONAL': '6110',
        'LAINNYA': '6110'
    };

    // 1. Event Listener Kartu Kategori (Pills)
    categoryChips.forEach(chip => {
        chip.addEventListener('click', function () {
            categoryChips.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                const catVal = radio.value;
                const targetCoaKode = categoryCoaMap[catVal];
                if (targetCoaKode && selectCoaHeader) {
                    for (let i = 0; i < selectCoaHeader.options.length; i++) {
                        if (selectCoaHeader.options[i].text.includes(targetCoaKode)) {
                            selectCoaHeader.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
        });
    });

    // 2. Event Listener Perubahan Akun Kas/Bank (Tampilkan Saldo Berjalan)
    function updateLiveSaldo() {
        if (!selectKasBank || !saldoDisplay) return;
        const selectedOption = selectKasBank.options[selectKasBank.selectedIndex];
        if (selectedOption && selectedOption.dataset.saldo !== undefined) {
            const saldo = parseFloat(selectedOption.dataset.saldo) || 0;
            saldoDisplay.textContent = 'Rp ' + saldo.toLocaleString('id-ID');
        } else {
            saldoDisplay.textContent = 'Rp 0';
        }
    }

    if (selectKasBank) {
        selectKasBank.addEventListener('change', updateLiveSaldo);
        updateLiveSaldo();
    }

    // 3. Quick Preset Tags
    quickTags.forEach(tag => {
        tag.addEventListener('click', function () {
            const uraianText = this.dataset.uraian || this.textContent.trim();
            const defaultNominal = parseFloat(this.dataset.nominal) || 0;
            const categoryTarget = this.dataset.kategori;

            // Cari baris pertama yang masih kosong, atau buat baris baru
            let targetRow = null;
            const rows = tableBody.querySelectorAll('tr.bkk-item-row');
            rows.forEach(r => {
                const inputUraian = r.querySelector('.item-uraian');
                if (inputUraian && !inputUraian.value.trim() && !targetRow) {
                    targetRow = r;
                }
            });

            if (!targetRow) {
                addNewRow();
                const allRows = tableBody.querySelectorAll('tr.bkk-item-row');
                targetRow = allRows[allRows.length - 1];
            }

            if (targetRow) {
                const inputUraian = targetRow.querySelector('.item-uraian');
                const inputNominal = targetRow.querySelector('.item-nominal');
                if (inputUraian) inputUraian.value = uraianText;
                if (inputNominal && defaultNominal > 0) inputNominal.value = defaultNominal;
            }

            // Pilih kategori otomatis jika ada
            if (categoryTarget) {
                const targetChip = document.querySelector(`.category-chip-label input[value="${categoryTarget}"]`);
                if (targetChip) {
                    targetChip.closest('.category-chip-label').click();
                }
            }

            recalculateTotals();
        });
    });

    // 4. Hitung Baris & Grand Total
    function recalculateTotals() {
        let grandTotal = 0;
        const rows = tableBody.querySelectorAll('tr.bkk-item-row');

        rows.forEach(row => {
            const qtyInput = row.querySelector('.item-qty');
            const hargaInput = row.querySelector('.item-harga');
            const nominalInput = row.querySelector('.item-nominal');

            const qty = parseFloat(qtyInput ? qtyInput.value : 1) || 0;
            const harga = parseFloat(hargaInput ? hargaInput.value : 0) || 0;
            let nominal = parseFloat(nominalInput ? nominalInput.value : 0) || 0;

            if (qty > 0 && harga > 0) {
                nominal = qty * harga;
                if (nominalInput) {
                    nominalInput.value = nominal;
                }
            }

            grandTotal += nominal;
        });

        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        if (terbilangDisplay) {
            terbilangDisplay.textContent = terbilang(grandTotal) + ' Rupiah';
        }
    }

    // 5. Tambah Baris Baru
    let rowIndex = tableBody.querySelectorAll('tr.bkk-item-row').length;

    function addNewRow() {
        rowIndex++;
        const tr = document.createElement('tr');
        tr.className = 'bkk-item-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${rowIndex}][uraian]" class="form-control item-uraian" placeholder="Uraian pengeluaran..." required>
            </td>
            <td style="width: 85px;">
                <input type="number" step="any" name="items[${rowIndex}][qty]" class="form-control item-qty" value="1" min="0.01">
            </td>
            <td style="width: 100px;">
                <input type="text" name="items[${rowIndex}][satuan]" class="form-control item-satuan" placeholder="Pcs/Ltr/Unit">
            </td>
            <td style="width: 140px;">
                <input type="number" step="any" name="items[${rowIndex}][harga_satuan]" class="form-control item-harga" placeholder="0">
            </td>
            <td style="width: 160px;">
                <input type="number" step="any" name="items[${rowIndex}][nominal]" class="form-control item-nominal" placeholder="0" required>
            </td>
            <td style="width: 45px; text-align: center;">
                <button type="button" class="btn-remove-row" title="Hapus baris">&times;</button>
            </td>
        `;

        tableBody.appendChild(tr);
        attachRowListeners(tr);
    }

    if (btnAddRow) {
        btnAddRow.addEventListener('click', function () {
            addNewRow();
            recalculateTotals();
        });
    }

    function attachRowListeners(row) {
        const qty = row.querySelector('.item-qty');
        const harga = row.querySelector('.item-harga');
        const nominal = row.querySelector('.item-nominal');
        const btnRemove = row.querySelector('.btn-remove-row');

        if (qty) qty.addEventListener('input', recalculateTotals);
        if (harga) harga.addEventListener('input', recalculateTotals);
        if (nominal) nominal.addEventListener('input', recalculateTotals);

        if (btnRemove) {
            btnRemove.addEventListener('click', function () {
                const totalRows = tableBody.querySelectorAll('tr.bkk-item-row').length;
                if (totalRows <= 1) {
                    alert('Minimal harus ada 1 baris rincian pengeluaran.');
                    return;
                }
                row.remove();
                recalculateTotals();
            });
        }
    }

    tableBody.querySelectorAll('tr.bkk-item-row').forEach(attachRowListeners);
    recalculateTotals();

    // 6. Terbilang
    function terbilang(bilangan) {
        bilangan = Math.abs(bilangan);
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
