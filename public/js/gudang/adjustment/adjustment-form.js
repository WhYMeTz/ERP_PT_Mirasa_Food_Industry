/**
 * JAVASCRIPT FORMULIR PENYESUAIAN STOK (CREATE / OPNAME)
 * ERP PT Mirasa Food Industry - Standar Input Mirasa & Baris Pertama Permanen
 */

let rowCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi baris pertama secara default saat formulir dibuka
    const tableBody = document.getElementById('adjItemsTbody');
    if (tableBody && tableBody.children.length === 0) {
        addAdjustmentRow();
    }

    const gudangSelect = document.getElementById('adjGudangSelect');
    if (gudangSelect && gudangSelect.value) {
        // Jika gudang sudah terpilih (misal reload dari old input), muat barang gudang tersebut
        onGudangChanged(gudangSelect.value);
    } else {
        // Jika gudang belum dipilih, set template awal peringatan pilih gudang
        const templateSelect = document.getElementById('barangOptionsTemplate');
        if (templateSelect) {
            templateSelect.innerHTML = '<option value="">-- Pilih Gudang Penyimpanan Terlebih Dahulu --</option>';
        }
        document.querySelectorAll('.row-barang-select').forEach(function (select) {
            select.innerHTML = '<option value="">-- Pilih Gudang Penyimpanan Terlebih Dahulu --</option>';
        });
    }

    // Listener jika gudang diubah, muat ulang daftar barang khusus gudang tersebut
    if (gudangSelect) {
        gudangSelect.addEventListener('change', function () {
            onGudangChanged(this.value);
        });
    }

    // Validasi submit form
    const form = document.querySelector('form[action*="adjustment"]');
    if (form) {
        form.addEventListener('submit', function (e) {
            const gudangSelect = document.getElementById('adjGudangSelect');
            if (!gudangSelect || !gudangSelect.value) {
                e.preventDefault();
                gudangSelect.focus();
                alert('Silakan pilih Gudang Penyimpanan terlebih dahulu.');
                return false;
            }


            // Cek apakah ada barang yang dipilih
            let hasValidItem = false;
            document.querySelectorAll('.row-barang-select').forEach(function (sel) {
                if (sel.value) {
                    hasValidItem = true;
                }
            });

            if (!hasValidItem) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 komoditas/barang untuk disesuaikan.');
                return false;
            }
        });
    }
});

/**
 * Format Angka Rupiah Bersih
 */
function formatRp(val) {
    const num = Math.round(parseFloat(val) || 0);
    return 'Rp ' + num.toLocaleString('id-ID');
}

/**
 * Format Angka Desimal Bersih (Indonesian locale)
 */
function formatNumber(val, decimals = 2) {
    const num = parseFloat(val) || 0;
    return num.toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: decimals
    });
}

/**
 * Memuat komoditas/barang yang tersedia di gudang yang dipilih via AJAX
 */
function onGudangChanged(gudangId) {
    const badge = document.getElementById('gudangInfoBadge');
    if (!gudangId) {
        if (badge) {
            badge.style.color = '#64748b';
            badge.innerHTML = 'Pilih gudang agar daftar komoditas yang tersedia di gudang tersebut tampil otomatis.';
        }
        updateBarangDropdowns([]);
        return;
    }

    if (badge) {
        badge.style.color = '#0284c7';
        badge.innerHTML = '<span style="display:inline-block; animation: spin 1s linear infinite; margin-right: 4px;">⌛</span> Memuat daftar komoditas di gudang terpilih...';
    }

    const url = (window.adjConfig && window.adjConfig.ajaxBarangByGudangUrl) 
        ? `${window.adjConfig.ajaxBarangByGudangUrl}?gudang_id=${encodeURIComponent(gudangId)}`
        : `/gudang/adjustment/ajax-barang-by-gudang?gudang_id=${encodeURIComponent(gudangId)}`;

    fetch(url)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const data = res.data;
                updateBarangDropdowns(data.grouped_items, data.total_barang);

                if (badge) {
                    badge.style.color = '#059669';
                    badge.innerHTML = `✓ Menampilkan <strong>${data.total_barang} komoditas</strong> yang relevan &amp; tersimpan di <strong>${data.gudang_nm}</strong>.`;
                }
            } else {
                if (badge) {
                    badge.style.color = '#dc2626';
                    badge.innerHTML = 'Gagal memuat barang di gudang: ' + (res.message || 'Terjadi kesalahan');
                }
            }
        })
        .catch(err => {
            console.error('Error fetching barang by gudang:', err);
            if (badge) {
                badge.style.color = '#dc2626';
                badge.innerHTML = 'Gagal menghubungi server untuk memuat daftar barang.';
            }
        });
}

/**
 * Memperbarui template opsi barang dan seluruh select barang di tabel grid
 */
function updateBarangDropdowns(groupedItems, totalCount = 0) {
    const templateSelect = document.getElementById('barangOptionsTemplate');
    if (!templateSelect) return;

    let html = '';
    if (!groupedItems || Object.keys(groupedItems).length === 0) {
        html = '<option value="">-- Tidak ada komoditas terdaftar di gudang ini --</option>';
    } else {
        html = '<option value="">-- Pilih Barang yang Disesuaikan --</option>';
        for (const [groupName, items] of Object.entries(groupedItems)) {
            html += `<optgroup label="${groupName}">`;
            items.forEach(item => {
                const stokFormatted = item.stok_sistem > 0 ? `• Stok: ${item.stok_label}` : '• Stok: 0 ' + item.satuan;
                html += `<option value="${item.barang_id}" data-cd="${item.barang_cd}" data-satuan="${item.satuan}" data-stok="${item.stok_sistem}">
                    ${item.barang_nm} (${item.barang_cd}) ${stokFormatted}
                </option>`;
            });
            html += `</optgroup>`;
        }
    }

    templateSelect.innerHTML = html;

    // Perbarui seluruh select barang di tabel grid
    document.querySelectorAll('.row-barang-select').forEach(select => {
        const currentVal = select.value;
        select.innerHTML = html;

        // Cek apakah nilai barang sebelumnya masih ada di daftar baru
        const stillExists = select.querySelector(`option[value="${currentVal}"]`);
        if (currentVal && stillExists) {
            select.value = currentVal;
            const rowId = select.dataset.rowId;
            fetchItemStock(rowId);
        } else {
            select.value = '';
            const rowId = select.dataset.rowId;
            resetRowValues(rowId);
        }
    });
}

/**
 * Reset nilai baris saat barang dikosongkan
 */
function resetRowValues(rowId) {
    const satEl = document.getElementById(`satuanLabel_${rowId}`);
    if (satEl) satEl.textContent = '-';
    const stokSisInput = document.getElementById(`stokSistem_${rowId}`);
    if (stokSisInput) stokSisInput.value = 0;
    const hargaSatuanInput = document.getElementById(`hargaSatuan_${rowId}`);
    if (hargaSatuanInput) hargaSatuanInput.value = 0;
    const stokSisDisplay = document.getElementById(`stokSistemDisplay_${rowId}`);
    if (stokSisDisplay) stokSisDisplay.textContent = '0,00';
    const hargaDisplay = document.getElementById(`hargaSatuanDisplay_${rowId}`);
    if (hargaDisplay) hargaDisplay.textContent = 'Rp 0';
    const fisikEl = document.getElementById(`stokFisik_${rowId}`);
    if (fisikEl) fisikEl.value = 0;
    const batchWrap = document.getElementById(`batchWrap_${rowId}`);
    if (batchWrap) batchWrap.style.display = 'none';
    calculateRow(rowId);
}

/**
 * Tambah Baris Barang Penyesuaian Baru (Baris 1 permanen tidak dapat dihapus)
 */
function addAdjustmentRow() {
    rowCounter++;
    const tbody = document.getElementById('adjItemsTbody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'rowAdj_' + rowCounter;
    tr.className = 'adj-item-row';
    tr.dataset.rowId = rowCounter;

    // Ambil opsi barang dari template
    const templateSelect = document.getElementById('barangOptionsTemplate');
    const optionsHtml = templateSelect ? templateSelect.innerHTML : '<option value="">-- Pilih Barang --</option>';

    // Baris pertama (rowCounter === 1 atau tbody kosong) tidak dapat dihapus
    const isFirstRow = (tbody.children.length === 0);
    const deleteButtonHtml = isFirstRow 
        ? `<button type="button" class="btn btn-secondary btn-sm" disabled style="padding: 0.2rem 0.45rem; font-size: 0.8rem; opacity: 0.3; cursor: not-allowed; border-radius: 4px;" title="Baris pertama tidak dapat dihapus">&times;</button>`
        : `<button type="button" onclick="removeAdjustmentRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem; background: #dc2626; border: 1px solid #dc2626; color: #ffffff; border-radius: 4px; cursor: pointer;" title="Hapus Baris">&times;</button>`;

    tr.innerHTML = `
        <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9; width: 35px;">
            ${tbody.children.length + 1}
        </td>
        <td class="col-adj-barang" style="width: 380px; min-width: 350px; max-width: 420px;">
            <div class="row-barang-cell">
                <select name="items[${rowCounter}][barang_id]" 
                        class="form-control item-barang row-barang-select" 
                        data-row-id="${rowCounter}" 
                        onchange="onBarangChanged(${rowCounter})" required>
                    ${optionsHtml}
                </select>
                <div id="batchWrap_${rowCounter}" style="display: none; margin-top: 0.25rem;">
                    <select name="items[${rowCounter}][batch_no]" id="batchSelect_${rowCounter}" class="form-control" style="font-size: 0.775rem; height: 28px; padding: 0.15rem 0.4rem;" onchange="onBatchChanged(${rowCounter})">
                        <option value="">[Semua Batch / Akumulatif]</option>
                    </select>
                </div>
            </div>
        </td>
        <td style="text-align: center; width: 55px;">
            <span id="satuanLabel_${rowCounter}" class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">-</span>
        </td>
        <td style="text-align: right; width: 90px;">
            <span id="stokSistemDisplay_${rowCounter}" style="font-weight: 700; font-family: monospace; font-size: 0.85rem; color: #334155;">0,00</span>
            <input type="hidden" name="items[${rowCounter}][stok_sistem_qty]" id="stokSistem_${rowCounter}" value="0">
            <input type="hidden" name="items[${rowCounter}][harga_satuan]" id="hargaSatuan_${rowCounter}" value="0">
        </td>
        <td style="text-align: right; background: #f0fdf4; width: 100px;">
            <input type="number" 
                   step="any"
                   min="0"
                   name="items[${rowCounter}][stok_fisik_qty]" 
                   id="stokFisik_${rowCounter}" 
                   class="form-control item-fisik adj-input-fisik" 
                   value="0" 
                   placeholder="0"
                   style="text-align: right; font-weight: 700; font-family: monospace;"
                   oninput="calculateRow(${rowCounter})" required>
        </td>
        <td style="text-align: right; width: 85px;" id="cellSelisihQty_${rowCounter}">
            <span id="badgeSelisih_${rowCounter}" class="badge-selisih-qty match">0,00</span>
            <input type="hidden" id="selisihQty_${rowCounter}" value="0">
        </td>
        <td style="text-align: right; width: 105px;">
            <span id="hargaSatuanDisplay_${rowCounter}" style="font-family: monospace; font-weight: 600; color: #475569; font-size: 0.825rem;">Rp 0</span>
        </td>
        <td style="text-align: right; width: 115px;">
            <span id="totalFisikDisplay_${rowCounter}" style="font-family: monospace; font-weight: 700; color: #0f172a; font-size: 0.825rem;">Rp 0</span>
        </td>
        <td style="text-align: right; width: 115px;" id="cellSelisihNilai_${rowCounter}">
            <span id="totalSelisihNilaiDisplay_${rowCounter}" style="font-family: monospace; font-weight: 800; color: #64748b; font-size: 0.825rem;">Rp 0</span>
        </td>
        <td class="col-adj-catatan" style="min-width: 200px;">
            <input type="text" 
                   name="items[${rowCounter}][alasan_txt]" 
                   class="form-control" 
                   style="font-size: 0.8rem; width: 100%;" 
                   placeholder="Alasan selisih (opsional)">
        </td>
        <td style="text-align: center; width: 40px;">
            ${deleteButtonHtml}
        </td>
    `;

    tbody.appendChild(tr);
    updateRowNumbers();
    recalculateSummary();
}

/**
 * Hapus Baris (Baris pertama tidak dapat dihapus)
 */
function removeAdjustmentRow(btn) {
    const rows = document.querySelectorAll('.adj-item-row');
    if (rows.length <= 1) {
        alert('Minimal harus ada 1 baris barang penyesuaian. Baris pertama tidak dapat dihapus.');
        return;
    }

    const tr = btn.closest('tr');
    if (!tr) return;

    // Pastikan bukan baris pertama
    const tbody = document.getElementById('adjItemsTbody');
    if (tbody && tbody.firstElementChild === tr) {
        alert('Baris pertama tidak dapat dihapus.');
        return;
    }

    tr.remove();
    updateRowNumbers();
    recalculateSummary();
}

/**
 * Perbarui Nomor Urut Baris (1, 2, 3...)
 */
function updateRowNumbers() {
    const rows = document.querySelectorAll('.adj-item-row');
    rows.forEach((row, idx) => {
        const numCol = row.querySelector('.row-num');
        if (numCol) {
            numCol.textContent = idx + 1;
        }

        // Pastikan baris index ke-0 (pertama) tombol hapusnya selalu disabled
        const removeBtn = row.querySelector('button[title*="Hapus"], button[title*="pertama"]');
        if (removeBtn) {
            if (idx === 0) {
                removeBtn.disabled = true;
                removeBtn.className = 'btn btn-secondary btn-sm';
                removeBtn.removeAttribute('onclick');
                removeBtn.title = 'Baris pertama tidak dapat dihapus';
                removeBtn.style.opacity = '0.3';
                removeBtn.style.cursor = 'not-allowed';
                removeBtn.style.background = '';
                removeBtn.style.borderColor = '';
                removeBtn.style.color = '';
                removeBtn.innerHTML = '&times;';
            } else {
                removeBtn.disabled = false;
                removeBtn.className = 'btn btn-danger btn-sm';
                removeBtn.setAttribute('onclick', 'removeAdjustmentRow(this)');
                removeBtn.title = 'Hapus Baris';
                removeBtn.style.opacity = '1';
                removeBtn.style.cursor = 'pointer';
                removeBtn.style.background = '#dc2626';
                removeBtn.style.borderColor = '#dc2626';
                removeBtn.style.color = '#ffffff';
                removeBtn.innerHTML = '&times;';
            }
        }
    });
}

/**
 * Event saat barang dipilih
 */
function onBarangChanged(rowId) {
    fetchItemStock(rowId);
}

/**
 * AJAX Ambil Stok Sistem & Harga Pokok Barang di Gudang yang dipilih
 */
function fetchItemStock(rowId) {
    const gudangSelect = document.getElementById('adjGudangSelect');
    const barangSelect = document.querySelector(`select[name="items[${rowId}][barang_id]"]`);

    if (!gudangSelect || !barangSelect) return;

    const gudangId = gudangSelect.value;
    const barangId = barangSelect.value;

    if (!gudangId) {
        alert('Silakan pilih Gudang Penyimpanan terlebih dahulu.');
        gudangSelect.focus();
        barangSelect.value = '';
        return;
    }

    if (!barangId) {
        const satEl = document.getElementById(`satuanLabel_${rowId}`);
        if (satEl) satEl.textContent = '-';
        const stokSisInput = document.getElementById(`stokSistem_${rowId}`);
        if (stokSisInput) stokSisInput.value = 0;
        const hargaSatuanInput = document.getElementById(`hargaSatuan_${rowId}`);
        if (hargaSatuanInput) hargaSatuanInput.value = 0;
        const stokSisDisplay = document.getElementById(`stokSistemDisplay_${rowId}`);
        if (stokSisDisplay) stokSisDisplay.textContent = '0,00';
        const hargaDisplay = document.getElementById(`hargaSatuanDisplay_${rowId}`);
        if (hargaDisplay) hargaDisplay.textContent = 'Rp 0';
        const fisikEl = document.getElementById(`stokFisik_${rowId}`);
        if (fisikEl) fisikEl.value = 0;
        const batchWrap = document.getElementById(`batchWrap_${rowId}`);
        if (batchWrap) batchWrap.style.display = 'none';
        calculateRow(rowId);
        return;
    }

    fetch(`/gudang/adjustment/ajax-item-info?gudang_id=${gudangId}&barang_id=${barangId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                const data = result.data;
                const satuan = data.satuan_cd || 'KG';

                // Satuan
                const satEl = document.getElementById(`satuanLabel_${rowId}`);
                if (satEl) satEl.textContent = satuan;

                // Stok Sistem
                const stokSistemVal = parseFloat(data.stok_sistem_qty) || 0;
                const hargaSatuanVal = parseFloat(data.harga_satuan) || 0;

                const stokSisInput = document.getElementById(`stokSistem_${rowId}`);
                if (stokSisInput) stokSisInput.value = stokSistemVal;

                const hargaSatuanInput = document.getElementById(`hargaSatuan_${rowId}`);
                if (hargaSatuanInput) hargaSatuanInput.value = hargaSatuanVal;

                const stokSisDisplay = document.getElementById(`stokSistemDisplay_${rowId}`);
                if (stokSisDisplay) {
                    stokSisDisplay.textContent = formatNumber(stokSistemVal);
                }

                const hargaDisplay = document.getElementById(`hargaSatuanDisplay_${rowId}`);
                if (hargaDisplay) {
                    hargaDisplay.textContent = formatRp(hargaSatuanVal);
                }

                // Stok Fisik Aktual otomatis mengikuti stok sistem barang yang baru dipilih
                const fisikEl = document.getElementById(`stokFisik_${rowId}`);
                if (fisikEl) {
                    fisikEl.value = stokSistemVal;
                }

                // Batch Dropdown jika ada batch
                const batchWrap = document.getElementById(`batchWrap_${rowId}`);
                const batchSelect = document.getElementById(`batchSelect_${rowId}`);
                if (batchWrap && batchSelect && data.batches && data.batches.length > 0) {
                    batchSelect.innerHTML = '<option value="">[Semua Batch / Akumulatif]</option>';
                    data.batches.forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b.batch_no;
                        opt.textContent = `${b.batch_no} (Sisa: ${formatNumber(b.sisa_qty)} ${satuan})`;
                        opt.dataset.sisa = b.sisa_qty;
                        opt.dataset.harga = b.harga_satuan;
                        batchSelect.appendChild(opt);
                    });
                    batchWrap.style.display = 'block';
                } else if (batchWrap) {
                    batchWrap.style.display = 'none';
                }

                calculateRow(rowId);
            }
        })
        .catch(err => {
            console.error('Error fetching stock info:', err);
        });
}

/**
 * Event jika pengguna memilih nomor batch tertentu
 */
function onBatchChanged(rowId) {
    const batchSelect = document.getElementById(`batchSelect_${rowId}`);
    if (!batchSelect) return;

    const selectedOpt = batchSelect.options[batchSelect.selectedIndex];
    if (selectedOpt && selectedOpt.value) {
        const sisa = parseFloat(selectedOpt.dataset.sisa) || 0;
        const harga = parseFloat(selectedOpt.dataset.harga) || 0;

        const stokSisInput = document.getElementById(`stokSistem_${rowId}`);
        if (stokSisInput) stokSisInput.value = sisa;

        const stokSisDisplay = document.getElementById(`stokSistemDisplay_${rowId}`);
        if (stokSisDisplay) stokSisDisplay.textContent = formatNumber(sisa);

        if (harga > 0) {
            const hargaInput = document.getElementById(`hargaSatuan_${rowId}`);
            if (hargaInput) hargaInput.value = harga;
            const hargaDisplay = document.getElementById(`hargaSatuanDisplay_${rowId}`);
            if (hargaDisplay) hargaDisplay.textContent = formatRp(harga);
        }

        const fisikEl = document.getElementById(`stokFisik_${rowId}`);
        if (fisikEl) fisikEl.value = sisa;

        calculateRow(rowId);
    }
}

/**
 * Kalkulasi Nilai Baris Realtime (Setiap Kolom Mandiri Tanpa Tumpukan)
 */
function calculateRow(rowId) {
    const stokSistemEl = document.getElementById(`stokSistem_${rowId}`);
    const hargaSatuanEl = document.getElementById(`hargaSatuan_${rowId}`);
    const stokFisikEl = document.getElementById(`stokFisik_${rowId}`);

    if (!stokSistemEl || !hargaSatuanEl || !stokFisikEl) return;

    const stokSistem = parseFloat(stokSistemEl.value) || 0;
    const hargaSatuan = parseFloat(hargaSatuanEl.value) || 0;
    const stokFisik = parseFloat(stokFisikEl.value) || 0;

    // 1. Tampilkan Harga Pokok
    const hargaDisplay = document.getElementById(`hargaSatuanDisplay_${rowId}`);
    if (hargaDisplay) {
        hargaDisplay.textContent = formatRp(hargaSatuan);
    }

    // 2. Total Fisik Nilai = Fisik * Harga (Kolom Mandiri)
    const totalFisik = stokFisik * hargaSatuan;
    const totalFisikDisplay = document.getElementById(`totalFisikDisplay_${rowId}`);
    if (totalFisikDisplay) {
        totalFisikDisplay.textContent = formatRp(totalFisik);
    }

    // 3. Selisih Qty = Fisik - Sistem
    const selisihQty = Math.round((stokFisik - stokSistem) * 10000) / 10000;
    const selisihQtyInput = document.getElementById(`selisihQty_${rowId}`);
    if (selisihQtyInput) selisihQtyInput.value = selisihQty;

    // Badge Selisih Qty Tampilan (Hanya angka kuantitas, bersih)
    const badgeEl = document.getElementById(`badgeSelisih_${rowId}`);
    if (badgeEl) {
        if (selisihQty < 0) {
            badgeEl.className = 'badge-selisih-qty defisit';
            badgeEl.textContent = formatNumber(selisihQty);
        } else if (selisihQty > 0) {
            badgeEl.className = 'badge-selisih-qty surplus';
            badgeEl.textContent = '+' + formatNumber(selisihQty);
        } else {
            badgeEl.className = 'badge-selisih-qty match';
            badgeEl.textContent = '0,00';
        }
    }

    // 4. Selisih Nilai = Selisih Qty * Harga (Kolom Mandiri)
    const selisihNilai = selisihQty * hargaSatuan;
    const nilaiDisplayEl = document.getElementById(`totalSelisihNilaiDisplay_${rowId}`);
    if (nilaiDisplayEl) {
        if (selisihNilai < 0) {
            nilaiDisplayEl.style.color = '#dc2626';
            nilaiDisplayEl.textContent = '-' + formatRp(Math.abs(selisihNilai));
        } else if (selisihNilai > 0) {
            nilaiDisplayEl.style.color = '#16a34a';
            nilaiDisplayEl.textContent = '+' + formatRp(selisihNilai);
        } else {
            nilaiDisplayEl.style.color = '#64748b';
            nilaiDisplayEl.textContent = 'Rp 0';
        }
    }

    recalculateSummary();
}

/**
 * Hitung Akumulasi Total di Bar Ringkasan & Table Footer (Sama dengan Terima Barang)
 */
function recalculateSummary() {
    let totalItems = 0;
    let sumSistemQty = 0;
    let sumFisikQty = 0;
    let sumSelisihQty = 0;
    let sumFisikNilai = 0;
    let sumSelisihNilai = 0;

    document.querySelectorAll('.adj-item-row').forEach(tr => {
        const rowId = tr.dataset.rowId;
        const barangSelect = tr.querySelector('.row-barang-select');
        if (barangSelect && barangSelect.value) {
            totalItems++;
            const stokSistem = parseFloat(document.getElementById(`stokSistem_${rowId}`)?.value) || 0;
            const stokFisik = parseFloat(document.getElementById(`stokFisik_${rowId}`)?.value) || 0;
            const selisihQty = parseFloat(document.getElementById(`selisihQty_${rowId}`)?.value) || 0;
            const harga = parseFloat(document.getElementById(`hargaSatuan_${rowId}`)?.value) || 0;

            sumSistemQty += stokSistem;
            sumFisikQty += stokFisik;
            sumSelisihQty += selisihQty;
            sumFisikNilai += (stokFisik * harga);
            sumSelisihNilai += (selisihQty * harga);
        }
    });

    // 1. Update Table Footer (tfoot)
    const tfootSistemEl = document.getElementById('tfootTotalSistemDisplay');
    const tfootFisikEl = document.getElementById('tfootTotalFisikDisplay');
    const tfootSelisihEl = document.getElementById('tfootTotalSelisihDisplay');
    const tfootFisikNilaiEl = document.getElementById('tfootTotalFisikNilaiDisplay');
    const tfootSelisihNilaiEl = document.getElementById('tfootTotalSelisihNilaiDisplay');

    if (tfootSistemEl) tfootSistemEl.textContent = formatNumber(sumSistemQty);
    if (tfootFisikEl) tfootFisikEl.textContent = formatNumber(sumFisikQty);
    if (tfootSelisihEl) {
        const sign = sumSelisihQty > 0 ? '+' : '';
        tfootSelisihEl.textContent = sign + formatNumber(sumSelisihQty);
        tfootSelisihEl.style.color = sumSelisihQty < 0 ? '#dc2626' : (sumSelisihQty > 0 ? '#16a34a' : '#0f172a');
    }
    if (tfootFisikNilaiEl) {
        tfootFisikNilaiEl.textContent = formatRp(sumFisikNilai);
    }
    if (tfootSelisihNilaiEl) {
        const sign = sumSelisihNilai > 0 ? '+' : (sumSelisihNilai < 0 ? '-' : '');
        tfootSelisihNilaiEl.textContent = sign + formatRp(Math.abs(sumSelisihNilai));
        tfootSelisihNilaiEl.style.color = sumSelisihNilai < 0 ? '#dc2626' : (sumSelisihNilai > 0 ? '#16a34a' : '#0f172a');
    }

    // 2. Update Card Footer Summary Metrics
    const sumItemEl = document.getElementById('summaryTotalItem');
    const sumQtyEl = document.getElementById('summarySelisihQty');
    const sumNilaiEl = document.getElementById('summarySelisihNilai');

    if (sumItemEl) sumItemEl.textContent = totalItems + ' Komoditas';
    if (sumQtyEl) {
        const sign = sumSelisihQty > 0 ? '+' : '';
        sumQtyEl.textContent = sign + formatNumber(sumSelisihQty);
        sumQtyEl.style.color = sumSelisihQty < 0 ? '#dc2626' : (sumSelisihQty > 0 ? '#16a34a' : '#0f172a');
    }
    if (sumNilaiEl) {
        const sign = sumSelisihNilai > 0 ? '+' : (sumSelisihNilai < 0 ? '-' : '');
        sumNilaiEl.textContent = sign + formatRp(Math.abs(sumSelisihNilai));
        sumNilaiEl.style.color = sumSelisihNilai < 0 ? '#dc2626' : (sumSelisihNilai > 0 ? '#16a34a' : '#0f172a');
    }
}
