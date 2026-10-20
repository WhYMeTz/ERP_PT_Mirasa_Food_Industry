/**
 * =========================================================================
 * ERP PT MIRASA - PENGEMASAN BARANG JADI & HPP (PACKAGING) CREATE JS
 * Smart Backflush BOM Preview & Real-Time HPP Engine (Satu Pintu Input)
 * =========================================================================
 */

let itemIndexCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    const barangSelect = document.getElementById('barang_jadi_id');
    const hasilKartonInput = document.getElementById('hasil_karton_qty');
    const isiPerKartonInput = document.getElementById('isi_per_karton_pcs');
    const upahTkInput = document.getElementById('total_biaya_tenaga_kerja');
    const biayaLainInput = document.getElementById('total_biaya_lain');

    const packTglInput = document.getElementById('pack_tgl');
    packTglInput?.addEventListener('change', function () {
        const prodSelect = document.getElementById('produksi_id');
        // Jika belum sinkron dengan tiket curah WIP, sesuaikan nomor batch default & expired
        if (!prodSelect || !prodSelect.value) {
            updateBatchNumber();
            const expInput = document.getElementById('expired_tgl');
            if (expInput && packTglInput.value) {
                const parts = packTglInput.value.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + 6);
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    expInput.value = `${yyyy}-${mm}-${dd}`;
                }
            }
        }
    });

    // Event listener pemilihan produk jadi
    barangSelect?.addEventListener('change', function () {
        updateBatchNumber();
        const isOperatorLinked = !!document.getElementById('laporan_operator_id')?.value;
        if (!isOperatorLinked && hasilKartonInput && parseFloat(hasilKartonInput.value) > 0) {
            fetchBomPreview();
        }
    });

    hasilKartonInput?.addEventListener('input', function () {
        recalculateTotals();
        const isOperatorLinked = !!document.getElementById('laporan_operator_id')?.value;
        if (!isOperatorLinked && barangSelect && barangSelect.value) {
            debounce(fetchBomPreview, 500)();
        }
    });

    isiPerKartonInput?.addEventListener('input', recalculateTotals);
    upahTkInput?.addEventListener('input', recalculateTotals);
    biayaLainInput?.addEventListener('input', recalculateTotals);

    // Format nomor batch default berupa tanggal
    updateBatchNumber();

    // Inisialisasi Timbangan Lakban
    const lakbanAtasIn = document.getElementById('lakban_atas_gr');
    const lakbanBawahIn = document.getElementById('lakban_bawah_gr');
    lakbanAtasIn?.addEventListener('input', onLakbanInputsChanged);
    lakbanBawahIn?.addEventListener('input', onLakbanInputsChanged);
    onLakbanInputsChanged();

    // Auto prefill jika datang dari link "Proses Jadi HPP FG" di web catatan operator
    if (window.preloadedOperatorLaporan) {
        cachedOperatorLaporanList = [window.preloadedOperatorLaporan];
        applyOperatorLaporan(window.preloadedOperatorLaporan);
    } else {
        // Auto-sync jika produksi sudah terpilih (misal form redirect back atau reload)
        const prodSelect = document.getElementById('produksi_id');
        if (prodSelect && prodSelect.value) {
            onProduksiSelect(prodSelect);
        }

        // Auto load jika barang sudah terpilih (misal form redirect back)
        if (barangSelect && barangSelect.value) {
            fetchBomPreview();
        }

        // Hitung kalkulasi awal saat halaman dimuat
        recalculateTotals();
    }
});

/**
 * Debounce helper
 */
let debounceTimer;
function debounce(func, delay) {
    return function (...args) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Generate nomor lot batch default: format tanggal murni (contoh: 15 10 2026)
 */
function updateBatchNumber() {
    const batchInput = document.getElementById('batch_no');
    if (!batchInput || batchInput.getAttribute('data-customized')) return;

    const packTgl = document.getElementById('pack_tgl')?.value;
    if (packTgl) {
        const parts = packTgl.split('-');
        if (parts.length === 3) {
            // parts[0] = YYYY, parts[1] = MM, parts[2] = DD
            batchInput.value = `${parts[2]} ${parts[1]} ${parts[0]}`;
            return;
        }
    }

    const now = new Date();
    const dd = String(now.getDate()).padStart(2, '0');
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const yyyy = now.getFullYear();
    batchInput.value = `${dd} ${mm} ${yyyy}`;
}

/**
 * Mengambil pratinjau resep BOM dari server via AJAX
 */
function fetchBomPreview() {
    const barangId = document.getElementById('barang_jadi_id')?.value;
    const hasilQty = parseFloat(document.getElementById('hasil_karton_qty')?.value) || 0;
    const produksiId = document.getElementById('produksi_id')?.value || '';
    const bannerBox = document.getElementById('bomBannerContainer');
    const tbody = document.getElementById('materialItemsTbody');
    const emptyRow = document.getElementById('emptyMaterialRow');
    const bomIdHidden = document.getElementById('bom_id_hidden');

    if (!barangId) {
        if (bannerBox) bannerBox.innerHTML = '';
        if (tbody && tbody.querySelectorAll('.material-row').length === 0 && emptyRow) {
            emptyRow.style.display = 'table-row';
        }
        recalculateTotals();
        return;
    }

    if (hasilQty <= 0) {
        if (bannerBox) {
            bannerBox.innerHTML = `
                <div class="bom-banner" style="background: #f0fdf4; border-color: #86efac;">
                    <div style="color: #166534; font-size: 0.825rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1rem;">💡</span>
                        <span>Produk jadi terpilih. Masukkan <strong>Jumlah Hasil Kemas (Karton)</strong> di Bagian 4 untuk otomatis menarik estimasi standar bahan resep BOM.</span>
                    </div>
                </div>
            `;
        }
        recalculateTotals();
        return;
    }

    // Tampilkan loading indicator di banner
    if (bannerBox) {
        bannerBox.innerHTML = `
            <div class="bom-banner" style="background: #f8fafc; border-color: #cbd5e1;">
                <div style="display: flex; align-items: center; gap: 0.6rem; color: #64748b; font-size: 0.825rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="animate-spin"><circle cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor" stroke-dasharray="32" stroke-linecap="round"/></svg>
                    <span>Memuat formula resep standar (BOM), menarik saldo stok FIFO &amp; HPP curah gudang...</span>
                </div>
            </div>
        `;
    }

    const url = `/produksi/packaging/ajax-bom-preview?barang_jadi_id=${barangId}&target_karton_qty=${hasilQty}&produksi_id=${produksiId}`;

    fetch(url, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(response => {
            if (!response.success) {
                if (bannerBox) {
                    bannerBox.innerHTML = `
                        <div class="bom-banner" style="background: #fffbeb; border-color: #fde68a;">
                            <div style="color: #92400e; font-size: 0.825rem;">
                                ⚠️ ${response.message || 'Formula BOM tidak ditemukan. Anda dapat menambahkan bahan secara manual.'}
                            </div>
                        </div>
                    `;
                }
                if (bomIdHidden) bomIdHidden.value = '';
                return;
            }

            const data = response.data;

            if (bomIdHidden) {
                bomIdHidden.value = data.bom_id || '';
            }

            // Render Banner BOM
            if (bannerBox) {
                if (data.has_bom) {
                    bannerBox.innerHTML = `
                        <div class="bom-banner">
                            <div class="bom-banner-info">
                                <span class="bom-banner-badge">Formula Resep Standar (BOM)</span>
                                <div style="font-size: 0.825rem; font-weight: 700; color: #166534;">
                                    ${data.bom_no} - ${data.bom_nm} 
                                    <span style="font-weight: 500; color: #15803d;">(Batch Acuan: ${data.batch_ukuran_qty} Karton)</span>
                                </div>
                            </div>
                            <div style="font-size: 0.725rem; color: #15803d; font-weight: 600;">
                                ⚡ Kebutuhan bahan terhitung proporsional untuk target ${data.target_karton} karton &amp; otomatis tercatat di Gudang.
                            </div>
                        </div>
                    `;
                } else {
                    bannerBox.innerHTML = `
                        <div class="bom-banner" style="background: #fffbeb; border-color: #fde68a;">
                            <div style="color: #92400e; font-size: 0.825rem;">
                                ⚠️ ${data.message}
                            </div>
                        </div>
                    `;
                }
            }

            // Render Tabel Komponen Bahan (Hanya jika TIDAK sedang terhubung dengan inputan aktual Operator HP)
            const isOperatorLinked = !!document.getElementById('laporan_operator_id')?.value;
            if (tbody && !isOperatorLinked) {
                tbody.innerHTML = '';
                itemIndexCounter = 0;

                if (data.items && data.items.length > 0) {
                    data.items.forEach(item => {
                        addMaterialRow(item);
                    });
                } else {
                    if (emptyRow) tbody.appendChild(emptyRow);
                }
                recalculateTotals();

                // Jika di dalam hasil BOM ada komponen WIP, sinkronkan opsi dropdown produksi_id dengan varian WIP tersebut
                const wipBomItem = (data.items || []).find(it => it.kategori_bahan === 'WIP');
                if (wipBomItem && wipBomItem.barang_id) {
                    const prodSelect = document.getElementById('produksi_id');
                    if (prodSelect && prodSelect.value) {
                        const currentProdId = prodSelect.value;
                        const matchedOpt = Array.from(prodSelect.options).find(opt => 
                            opt.value == currentProdId && opt.getAttribute('data-barang-id') == wipBomItem.barang_id
                        );
                        if (matchedOpt && prodSelect.selectedIndex !== matchedOpt.index) {
                            prodSelect.selectedIndex = matchedOpt.index;
                            onProduksiSelect(prodSelect, true);
                        }
                    }
                }
            }
        })
        .catch(err => {
            console.error('BOM fetch error:', err);
            if (bannerBox) {
                bannerBox.innerHTML = `
                    <div class="bom-banner" style="background: #fef2f2; border-color: #fca5a5; color: #b91c1c; font-size: 0.825rem;">
                        Terjadi kesalahan saat memuat resep BOM: ${err.message}
                    </div>
                `;
            }
        });
}

/**
 * Helper format tanggal YYYY-MM-DD ke DD/MM/YYYY
 */
function formatDateIndo(dateStr) {
    if (!dateStr) return '-';
    const parts = dateStr.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
}

/**
 * Handler ketika Lembar HPP Produksi (WIP) dipilih di Bagian 1
 */
function onProduksiSelect(selectEl, skipAddRow = false) {
    const prodId = selectEl?.value;
    const syncBox = document.getElementById('wipSyncStatusBox');
    const syncText = document.getElementById('wipSyncStatusText');
    const batchInput = document.getElementById('batch_no');
    const expiredInput = document.getElementById('expired_tgl');

    if (!prodId) {
        clearProduksiSync();
        return;
    }

    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt) return;

    const hppVal = parseFloat(opt.getAttribute('data-hpp')) || 0;
    const batchVal = opt.getAttribute('data-batch') || '';
    const expVal = opt.getAttribute('data-exp') || '';
    const qtyVal = parseFloat(opt.getAttribute('data-qty')) || 0;
    const prodNo = opt.getAttribute('data-no') || '';
    const barangNm = opt.getAttribute('data-barang-nm') || 'Keripik Curah';
    const barangId = opt.getAttribute('data-barang-id') || '';

    // Isi batch_no & expired_tgl di Bagian 1
    if (batchInput && batchVal) {
        batchInput.value = batchVal;
        batchInput.setAttribute('data-customized', 'true');
    }
    if (expiredInput && expVal) {
        expiredInput.value = expVal;
    }

    // Tampilkan notifikasi sinkron ringkas & profesional
    if (syncBox && syncText) {
        syncBox.style.display = 'flex';
        syncText.innerHTML = `Tersinkron dengan <strong>${prodNo}</strong>: <strong>${barangNm}</strong> &bull; Lot: <code>${batchVal}</code> &bull; Exp: <strong>${formatDateIndo(expVal)}</strong> (HPP: Rp ${formatRupiah(hppVal)}/Kg)`;
    }

    // Cek apakah di tabel material sudah ada baris WIP
    let foundWip = false;
    const rows = document.querySelectorAll('.material-row');
    rows.forEach(tr => {
        const catInput = tr.querySelector('input[name*="[kategori_bahan]"]');
        if (catInput && catInput.value.toUpperCase() === 'WIP') {
            foundWip = true;
            // Update harga satuan HPP
            const priceInput = tr.querySelector('.row-price-input');
            const batchHidden = tr.querySelector('input[name*="[batch_no]"]');
            if (priceInput) priceInput.value = hppVal;
            if (batchHidden) batchHidden.value = batchVal;

            // Update badge info sumber
            const originTag = tr.querySelector('.wip-origin-tag');
            if (originTag) {
                const rowBarangNm = tr.querySelector('.barang-name-cell strong')?.textContent?.trim() || barangNm;
                originTag.innerHTML = `
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    ✓ HPP Produksi ${prodNo} (${rowBarangNm})
                `;
                originTag.style.background = '#dcfce7';
                originTag.style.color = '#15803d';
                originTag.style.borderColor = '#86efac';
            }

            // Highlight baris
            tr.style.transition = 'background-color 0.4s';
            tr.style.backgroundColor = '#ecfdf5';
            setTimeout(() => { tr.style.backgroundColor = ''; }, 1200);

            // Recalculate baris ini
            const rowIdParts = tr.id.split('_');
            if (rowIdParts.length > 1) {
                recalculateRow(rowIdParts[1]);
            }
        }
    });

    // Jika belum ada baris WIP di tabel (misal belum ada produk FG/BOM), tarik detail master barang curah via AJAX
    const isOperatorLinked = !!document.getElementById('laporan_operator_id')?.value;
    if (!foundWip && !skipAddRow && !isOperatorLinked) {
        const ajaxUrl = `/produksi/packaging/ajax-produksi-details?produksi_id=${prodId}` + (barangId ? `&barang_id=${barangId}` : '');
        fetch(ajaxUrl)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const d = res.data;
                    const b = d.berko_barang || {
                        barang_id: 45,
                        barang_cd: 'WIP-BRK',
                        barang_nm: 'KS BERKO (Keripik Singkong Goreng)',
                        satuan_cd: 'KG'
                    };

                    if (d.exp_date && expiredInput && !expiredInput.value) {
                        expiredInput.value = d.exp_date;
                    }

                    // Cek lagi apakah baris WIP sudah dibuat oleh operator atau proses lain
                    const existingWip = Array.from(document.querySelectorAll('.material-row')).some(tr => {
                        const cat = tr.querySelector('input[name*="[kategori_bahan]"]')?.value;
                        return cat && cat.toUpperCase() === 'WIP';
                    });
                    if (existingWip || document.getElementById('laporan_operator_id')?.value) {
                        return; // Abaikan penambahan agar tidak memunculkan baris ganda
                    }

                    addMaterialRow({
                        kategori_bahan: 'WIP',
                        barang_id: b.barang_id,
                        barang_nm: b.barang_nm,
                        barang_cd: b.barang_cd,
                        satuan_cd: b.satuan_cd || 'KG',
                        qty_standar: 0,
                        qty_aktual: 0,
                        harga_satuan: d.hpp_per_kg || hppVal,
                        subtotal_nilai: 0,
                        batch_no: d.batch_wip_no || batchVal,
                        stok_tersedia: d.total_berko_qty || qtyVal,
                        cukup_stok: true,
                        is_wip: true,
                        sync_source: prodNo
                    });

                    recalculateTotals();
                }
            })
            .catch(err => {
                console.warn('Gagal memuat detail AJAX produksi:', err);
            });
    }

    // Jika produk jadi kemasan sudah terpilih di Bagian 1 & hasil karton > 0, refresh BOM
    const barangSelect = document.getElementById('barang_jadi_id');
    const hasilKartonInput = document.getElementById('hasil_karton_qty');
    if (barangSelect && barangSelect.value && hasilKartonInput && parseFloat(hasilKartonInput.value) > 0) {
        fetchBomPreview();
    }
}

/**
 * Melepas sinkronisasi dokumen produksi WIP
 */
function clearProduksiSync() {
    const selectEl = document.getElementById('produksi_id');
    const syncBox = document.getElementById('wipSyncStatusBox');
    if (selectEl) selectEl.value = '';
    if (syncBox) syncBox.style.display = 'none';

    // Reset origin tag pada baris WIP agar tidak meninggalkan jejak tiket yang sudah dilepas
    const rows = document.querySelectorAll('.material-row');
    rows.forEach(tr => {
        const catInput = tr.querySelector('input[name*="[kategori_bahan]"]');
        if (catInput && catInput.value.toUpperCase() === 'WIP') {
            const originTag = tr.querySelector('.wip-origin-tag');
            if (originTag) {
                originTag.innerHTML = `Keripik Curah (WIP)`;
                originTag.style.background = '#f1f5f9';
                originTag.style.color = '#475569';
                originTag.style.borderColor = '#cbd5e1';
            }
        }
    });

    // Refresh BOM jika aktif
    const barangSelect = document.getElementById('barang_jadi_id');
    const hasilKartonInput = document.getElementById('hasil_karton_qty');
    if (barangSelect && barangSelect.value && hasilKartonInput && parseFloat(hasilKartonInput.value) > 0) {
        fetchBomPreview();
    } else {
        recalculateTotals();
    }
}

/**
 * Tambah baris baru secara langsung (inline) ke tabel bahan
 */
function addNewMaterialRow() {
    addMaterialRow({
        is_manual: true,
        kategori_bahan: 'KEMASAN',
        barang_id: '',
        barang_nm: '',
        barang_cd: '',
        satuan_cd: 'PCS',
        qty_standar: 0,
        qty_aktual: 1,
        harga_satuan: 0,
        subtotal_nilai: 0,
        batch_no: '',
        stok_tersedia: null,
        cukup_stok: true
    });
}



/**
 * Tambah baris material ke tabel (Tepat 6 Kolom sesuai Thead)
 */
function addMaterialRow(item = {}) {
    const tbody = document.getElementById('materialItemsTbody');
    const emptyRow = document.getElementById('emptyMaterialRow');
    if (emptyRow) emptyRow.style.display = 'none';

    const idx = itemIndexCounter++;
    const isManual = !!item.is_manual;
    const kategori = item.kategori_bahan || 'KEMASAN';
    const barangId = item.barang_id || '';
    const barangNm = item.barang_nm || '';
    const barangCd = item.barang_cd || '';
    const satuanCd = item.satuan_cd || 'PCS';
    const qtyStandar = item.qty_standar !== undefined ? parseFloat(item.qty_standar) : 0;
    const qtyAktual = item.qty_aktual !== undefined ? parseFloat(item.qty_aktual) : qtyStandar;
    const hargaSatuan = item.harga_satuan !== undefined ? parseFloat(item.harga_satuan) : 0;
    const batchNo = item.batch_no || '';
    const stokTersedia = item.stok_tersedia !== undefined ? parseFloat(item.stok_tersedia) : null;
    const cukupStok = item.cukup_stok !== undefined ? item.cukup_stok : true;
    const isWip = item.is_wip || (kategori === 'WIP');
    const syncSource = item.sync_source || '';

    let catClass = 'cat-kemasan';
    if (kategori === 'WIP') catClass = 'cat-wip';
    else if (kategori === 'BUMBU') catClass = 'cat-bumbu';
    else if (kategori === 'LAIN') catClass = 'cat-lain';

    const tr = document.createElement('tr');
    tr.id = `materialRow_${idx}`;
    tr.className = 'material-row';

    let itemColumnHtml = '';
    if (isManual) {
        // Tampilkan dropdown pilihan bahan langsung di dalam baris
        let optionsHtml = '<option value="">-- Pilih Komponen Bahan --</option>';
        const groups = {
            'KEMASAN': { label: '📦 Dus & Kemasan (Karton, Roll, Lakban, Rafia)', items: [] },
            'BUMBU':   { label: '🧂 Bumbu & Perasa Makanan', items: [] },
            'WIP':     { label: '🍘 Keripik Curah (WIP / KS Berko)', items: [] },
            'LAIN':    { label: '⚙️ Bahan Penolong Lainnya', items: [] },
        };

        const list = window.masterKemasanList || [];
        list.forEach(m => {
            const k = groups[m.kategori] ? m.kategori : 'LAIN';
            groups[k].items.push(m);
        });

        for (const [k, grp] of Object.entries(groups)) {
            if (grp.items.length > 0) {
                optionsHtml += `<optgroup label="${grp.label}">`;
                grp.items.forEach(m => {
                    const sel = (m.barang_id == barangId) ? 'selected' : '';
                    optionsHtml += `<option value="${m.barang_id}" data-kategori="${m.kategori}" data-nama="${m.barang_nm}" data-kode="${m.barang_cd}" data-satuan="${m.satuan_cd}" data-harga="${m.harga_standar}" ${sel}>
                        [${m.barang_cd}] ${m.barang_nm} (${m.satuan_cd})
                    </option>`;
                });
                optionsHtml += `</optgroup>`;
            }
        }

        itemColumnHtml = `
            <select name="items[${idx}][barang_id]" required
                    class="form-control row-barang-select" 
                    style="height: 34px; font-size: 0.8125rem; font-weight: 600; border-radius: 5px;" 
                    onchange="onManualBarangChange(this, ${idx})">
                ${optionsHtml}
            </select>
        `;
    } else {
        itemColumnHtml = `
            <input type="hidden" name="items[${idx}][barang_id]" value="${barangId}">
            <div style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">${barangNm}</div>
            <div style="font-size: 0.7rem; color: #64748b;">
                Kode: ${barangCd}
                ${batchNo ? ` &bull; Lot: ${batchNo}` : ''}
            </div>
            ${stokTersedia !== null ? `
                <div style="margin-top: 0.15rem;">
                    <span class="${cukupStok ? 'stock-tag-ok' : 'stock-tag-warn'}">
                        Stok: ${formatNumber(stokTersedia)} ${satuanCd} ${cukupStok ? '✓ Cukup' : '⚠️ Kurang'}
                    </span>
                </div>
            ` : ''}
        `;
    }

    tr.innerHTML = `
        <td>
            <span class="cat-badge ${catClass}" id="catBadge_${idx}">${kategori}</span>
            <input type="hidden" name="items[${idx}][kategori_bahan]" id="catInput_${idx}" value="${kategori}">
            <input type="hidden" name="items[${idx}][satuan_cd]" id="satuanInput_${idx}" value="${satuanCd}">
            <input type="hidden" name="items[${idx}][batch_no]" value="${batchNo}">
            <input type="hidden" name="items[${idx}][qty_standar]" value="${qtyStandar}">
        </td>
        <td>
            ${itemColumnHtml}
        </td>
        <td style="width: 140px;">
            <div style="display: flex; align-items: center; gap: 0.35rem; justify-content: flex-end;">
                <input type="number" step="0.0001" min="0" required
                       name="items[${idx}][qty_aktual]" 
                       value="${qtyAktual}"
                       class="form-control row-qty-input" 
                       style="text-align: right; font-weight: 600; height: 32px; padding: 0.2rem 0.5rem; width: 95px; font-size: 0.8125rem;"
                       oninput="recalculateRow(${idx})">
                <span class="row-satuan-label" id="satuanLabel_${idx}" style="font-size: 0.75rem; color: #64748b; font-weight: 600; min-width: 25px;">${satuanCd}</span>
            </div>
            ${qtyStandar > 0 ? `
                <div style="font-size: 0.7rem; color: #64748b; text-align: right; margin-top: 0.15rem;">
                    BOM: ${formatNumber(qtyStandar)} ${satuanCd}
                </div>
            ` : ''}
        </td>
        <td style="width: 140px; text-align: right;">
            <input type="number" step="0.01" min="0" required
                   name="items[${idx}][harga_satuan]" 
                   value="${hargaSatuan}"
                   class="form-control row-price-input" 
                   style="text-align: right; height: 32px; padding: 0.2rem 0.5rem; font-weight: ${isWip ? '600' : '500'}; color: ${isWip ? '#065f46' : '#1e293b'}; font-size: 0.8125rem;"
                   oninput="recalculateRow(${idx})">
            ${isWip ? `
                <div class="wip-origin-tag" id="wipOriginTag_${idx}">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    ${syncSource ? `HPP ${syncSource}` : 'HPP Curah WIP'}
                </div>
            ` : `
                <div class="pack-origin-tag" title="Standar Acuan Master Data Kemasan">Standar Acuan</div>
            `}
        </td>
        <td style="text-align: right; width: 160px;">
            <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                <span id="subtotalLabel_${idx}">Rp ${formatRupiah(qtyAktual * hargaSatuan)}</span>
            </div>
            <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.15rem;" id="subtotalFormula_${idx}">
                ${formatNumber(qtyAktual)} ${satuanCd} &times; Rp ${formatRupiah(hargaSatuan)}
            </div>
            <input type="hidden" id="subtotalVal_${idx}" value="${qtyAktual * hargaSatuan}">
        </td>
        <td style="text-align: center; width: 45px;">
            <button type="button" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.45rem; color: #dc2626; border-radius: 4px;" title="Hapus Baris" onclick="removeMaterialRow(${idx})">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    if (isManual) {
        const selectEl = tr.querySelector('.row-barang-select');
        if (selectEl) selectEl.focus();
    }

    recalculateTotals();
}

/**
 * Handler ketika item dipilih dari dropdown select baris manual
 */
function onManualBarangChange(selectEl, idx) {
    const barangId = selectEl.value;
    const tr = document.getElementById(`materialRow_${idx}`);
    if (!tr) return;

    const opt = selectEl.selectedOptions[0];
    const catBadge = document.getElementById(`catBadge_${idx}`);
    const catInput = document.getElementById(`catInput_${idx}`);
    const satuanInput = document.getElementById(`satuanInput_${idx}`);
    const satuanLabel = document.getElementById(`satuanLabel_${idx}`);
    const priceInput = tr.querySelector('.row-price-input');
    const qtyInput = tr.querySelector('.row-qty-input');

    if (!barangId || !opt) {
        if (priceInput) priceInput.value = 0;
        recalculateRow(idx);
        return;
    }

    const kategori = opt.getAttribute('data-kategori') || 'KEMASAN';
    const satuan = opt.getAttribute('data-satuan') || 'PCS';
    const harga = parseFloat(opt.getAttribute('data-harga')) || 0;

    if (catBadge) {
        catBadge.className = 'cat-badge';
        if (kategori === 'WIP') catBadge.classList.add('cat-wip');
        else if (kategori === 'BUMBU') catBadge.classList.add('cat-bumbu');
        else if (kategori === 'LAIN') catBadge.classList.add('cat-lain');
        else catBadge.classList.add('cat-kemasan');
        catBadge.textContent = kategori;
    }

    if (catInput) catInput.value = kategori;
    if (satuanInput) satuanInput.value = satuan;
    if (satuanLabel) satuanLabel.textContent = satuan;
    if (priceInput) priceInput.value = harga;

    recalculateRow(idx);

    if (qtyInput) {
        qtyInput.focus();
        qtyInput.select();
    }
}

/**
 * Kompatibilitas dengan pemanggilan terdahulu
 */
function addCustomMaterialRow() {
    openTambahKomponenModal();
}

/**
 * Hitung subtotal untuk sebuah baris ketika qty atau harga diedit
 */
function recalculateRow(idx) {
    const tr = document.getElementById(`materialRow_${idx}`);
    if (!tr) return;

    const qtyInput = tr.querySelector('.row-qty-input');
    const priceInput = tr.querySelector('.row-price-input');
    const subtotalLabel = document.getElementById(`subtotalLabel_${idx}`);
    const subtotalVal = document.getElementById(`subtotalVal_${idx}`);
    const subtotalFormula = document.getElementById(`subtotalFormula_${idx}`);
    const satuanInput = document.getElementById(`satuanInput_${idx}`);
    const satuanCd = satuanInput ? satuanInput.value : '';

    const qty = parseFloat(qtyInput?.value) || 0;
    const price = parseFloat(priceInput?.value) || 0;
    const subtotal = Math.round(qty * price * 100) / 100;

    if (subtotalLabel) subtotalLabel.textContent = 'Rp ' + formatRupiah(subtotal);
    if (subtotalVal) subtotalVal.value = subtotal;
    if (subtotalFormula) {
        subtotalFormula.innerHTML = `${formatNumber(qty)} ${satuanCd} &times; Rp ${formatRupiah(price)}`;
    }

    recalculateTotals();
}

/**
 * Hapus baris material
 */
function removeMaterialRow(idx) {
    const tr = document.getElementById(`materialRow_${idx}`);
    if (tr) {
        tr.remove();
        const tbody = document.getElementById('materialItemsTbody');
        const emptyRow = document.getElementById('emptyMaterialRow');
        if (tbody && tbody.querySelectorAll('.material-row').length === 0 && emptyRow) {
            emptyRow.style.display = '';
        }
        recalculateTotals();
    }
}

/**
 * Kalkulasi menyeluruh Biaya Produksi & HPP Kemasan secara Real-Time
 */
function recalculateTotals() {
    let totalWip = 0;
    let totalKemasan = 0;
    let totalBumbu = 0;
    let totalLain = 0;

    const rows = document.querySelectorAll('.material-row');
    rows.forEach(tr => {
        const catInput = tr.querySelector('input[name*="[kategori_bahan]"]');
        const qtyInput = tr.querySelector('.row-qty-input');
        const priceInput = tr.querySelector('.row-price-input');

        const cat = (catInput?.value || 'KEMASAN').toUpperCase();
        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const subtotal = qty * price;

        if (cat === 'WIP') totalWip += subtotal;
        else if (cat === 'KEMASAN') totalKemasan += subtotal;
        else if (cat === 'BUMBU') totalBumbu += subtotal;
        else totalLain += subtotal;
    });

    // Periksa apakah nilai lakban sudah tercakup di baris tabel material
    let hasLakbanRow = false;
    rows.forEach(tr => {
        const catInput = tr.querySelector('input[name*="[kategori_bahan]"]');
        const text = tr.textContent.toUpperCase();
        if ((catInput?.value || '').toUpperCase() === 'LAKBAN' || text.includes('LAKBAN') || text.includes('LKB')) {
            hasLakbanRow = true;
        }
    });

    const lakbanNilaiExtra = parseFloat(document.getElementById('lakban_nilai')?.value) || 0;
    if (!hasLakbanRow && lakbanNilaiExtra > 0) {
        totalKemasan += lakbanNilaiExtra;
    }

    const upahTk = parseFloat(document.getElementById('total_biaya_tenaga_kerja')?.value) || 0;
    const overheadLain = parseFloat(document.getElementById('total_biaya_lain')?.value) || 0;

    const totalBiayaProduksi = totalWip + totalKemasan + totalBumbu + totalLain + upahTk + overheadLain;

    const hasilKarton = parseFloat(document.getElementById('hasil_karton_qty')?.value) || 0;
    const isiPerKarton = parseFloat(document.getElementById('isi_per_karton_pcs')?.value) || 1;
    const totalPcs = hasilKarton * (isiPerKarton > 0 ? isiPerKarton : 1);

    const hppPerKarton = hasilKarton > 0 ? Math.round(totalBiayaProduksi / hasilKarton) : 0;
    const hppPerPcs = totalPcs > 0 ? Math.round(totalBiayaProduksi / totalPcs) : 0;

    // Update Summary Display Elements
    const sumWip = document.getElementById('sumTotalWip');
    const sumKemasan = document.getElementById('sumTotalKemasan');
    const sumUpah = document.getElementById('sumTotalUpah');
    const sumTotalBiaya = document.getElementById('sumTotalBiayaProduksi');
    const sumHasilKemasInfo = document.getElementById('sumHasilKemasInfo');
    const sumTotalPcsInfo = document.getElementById('sumTotalPcsInfo');
    const sumHppKarton = document.getElementById('sumHppPerKarton');
    const sumHppPcs = document.getElementById('sumHppPerPcs');
    const submitBtn = document.getElementById('btnSubmitPackaging');

    if (sumWip) sumWip.textContent = 'WIP Rp ' + formatRupiah(totalWip);
    if (sumKemasan) sumKemasan.textContent = 'Kemas Rp ' + formatRupiah(totalKemasan + totalBumbu + totalLain);
    if (sumUpah) sumUpah.textContent = 'Upah Rp ' + formatRupiah(upahTk + overheadLain);
    if (sumTotalBiaya) sumTotalBiaya.textContent = 'Rp ' + formatRupiah(totalBiayaProduksi);
    if (sumHasilKemasInfo) sumHasilKemasInfo.textContent = formatRupiah(hasilKarton) + ' Karton';
    if (sumTotalPcsInfo) sumTotalPcsInfo.textContent = 'Setara ' + formatRupiah(totalPcs) + ' Pcs Siap Edar';
    if (sumHppKarton) sumHppKarton.textContent = 'Rp ' + formatRupiah(hppPerKarton);
    if (sumHppPcs) sumHppPcs.textContent = 'Rp ' + formatRupiah(hppPerPcs) + ' / Pcs';

    const livePcsBadge = document.getElementById('livePcsOutputBadge');
    if (livePcsBadge) {
        livePcsBadge.innerHTML = `Total Setara: <strong>${formatRupiah(totalPcs)} Pcs</strong> Kemasan Siap Edar.`;
    }

    if (submitBtn) {
        submitBtn.disabled = rows.length === 0 || hasilKarton <= 0;
        if (submitBtn.disabled) {
            submitBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    }
}

/**
 * Format string angka ke format ribuan rupiah
 */
function formatRupiah(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function formatNumber(num) {
    return Number(num).toLocaleString('id-ID', { maximumFractionDigits: 4 });
}

/**
 * =========================================================================
 * INTEGRASI TARIK LAPORAN PENGEMASAN OPERATOR LAPANGAN (PENGGANTI GOOGLE FORM)
 * =========================================================================
 */
let cachedOperatorLaporanList = [];

function openPullOperatorModal() {
    const modal = document.getElementById('modalPullOperator');
    const loading = document.getElementById('pullOperatorLoading');
    const emptyState = document.getElementById('pullOperatorEmpty');
    const content = document.getElementById('pullOperatorContent');
    const tbody = document.getElementById('pullOperatorTableBody');

    if (!modal) return;

    modal.style.display = 'flex';
    if (loading) loading.style.display = 'block';
    if (emptyState) emptyState.style.display = 'none';
    if (content) content.style.display = 'none';
    if (tbody) tbody.innerHTML = '';

    fetch('/produksi/packaging/ajax-unprocessed-laporan')
        .then(res => res.json())
        .then(res => {
            if (loading) loading.style.display = 'none';
            if (res.status === 'success' && res.data && res.data.length > 0) {
                cachedOperatorLaporanList = res.data;
                renderPullOperatorTable(res.data);
                if (content) content.style.display = 'block';
            } else {
                if (emptyState) emptyState.style.display = 'block';
            }
        })
        .catch(err => {
            if (loading) loading.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
            console.error('Gagal mengambil laporan operator:', err);
        });
}

function closePullOperatorModal() {
    const modal = document.getElementById('modalPullOperator');
    if (modal) modal.style.display = 'none';
}

function renderPullOperatorTable(list) {
    const tbody = document.getElementById('pullOperatorTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    list.forEach((item, idx) => {
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #f1f5f9';

        const tglKemas = item.tgl_kemas ? new Date(item.tgl_kemas).toLocaleDateString('id-ID') : '-';
        const barangNm = item.barang_jadi ? item.barang_jadi.barang_nm : '-';
        const outputQty = `${formatRupiah(item.hasil_karton_qty)} ${item.satuan_hasil || 'KARTON'}`;

        tr.innerHTML = `
            <td style="padding: 0.65rem 0.75rem;">
                <strong style="color: #0f172a; font-family: ui-monospace, monospace;">${item.laporan_no}</strong>
                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">📅 ${tglKemas}</div>
            </td>
            <td style="padding: 0.65rem 0.75rem;">
                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; background: #eff6ff; color: #1d4ed8;">Shift ${item.shift}</span>
                <div style="font-size: 0.775rem; font-weight: 600; color: #1e293b; margin-top: 2px;">${item.operator_nama} (${item.jumlah_personil || 1} Org)</div>
            </td>
            <td style="padding: 0.65rem 0.75rem;">
                <div style="font-weight: 700; color: #0f172a;">${barangNm}</div>
                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                    Curah: ${formatNumber(item.wip_qty_kg)} ${item.wip_satuan || 'Kg'} &bull; ${item.bumbu_barang ? 'Bumbu: ' + item.bumbu_barang.barang_nm + ' (' + formatNumber(item.bumbu_qty_kg) + ' Kg)' : 'Tanpa Bumbu'}
                </div>
            </td>
            <td style="padding: 0.65rem 0.75rem; text-align: right;">
                <strong style="font-size: 0.95rem; font-weight: 900; color: #059669; font-family: ui-monospace, monospace;">${outputQty}</strong>
            </td>
            <td style="padding: 0.65rem 0.75rem; text-align: center;">
                <button type="button" class="btn" onclick="applyOperatorLaporan(${idx})" style="background: #059669; color: #ffffff; border: none; font-size: 0.775rem; font-weight: 700; padding: 0.4rem 0.75rem; border-radius: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(5, 150, 105, 0.2);">
                    Gunakan
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function findMasterKemasan(barangId) {
    if (!barangId) return null;
    return (window.masterKemasanList || []).find(m => parseInt(m.barang_id) === parseInt(barangId)) || null;
}

function applyOperatorLaporan(itemOrIdx) {
    let item = null;
    if (typeof itemOrIdx === 'object' && itemOrIdx !== null) {
        item = itemOrIdx;
    } else if (typeof itemOrIdx === 'number' || typeof itemOrIdx === 'string') {
        item = cachedOperatorLaporanList[itemOrIdx];
    }
    if (!item) return;

    // 1. Tautkan ID Laporan
    const hiddenId = document.getElementById('laporan_operator_id');
    if (hiddenId) hiddenId.value = item.laporan_id;

    // 2. Set Tanggal & Produk Jadi
    const packTgl = document.getElementById('pack_tgl');
    if (packTgl && item.tgl_kemas) {
        packTgl.value = typeof item.tgl_kemas === 'string' ? item.tgl_kemas.split('T')[0] : item.tgl_kemas;
    }

    const barangSelect = document.getElementById('barang_jadi_id');
    if (barangSelect && item.barang_jadi_id) {
        barangSelect.value = item.barang_jadi_id;
    }

    // 3. Set Jumlah Hasil Kemas & Target
    const hasilInput = document.getElementById('hasil_karton_qty');
    const targetInput = document.getElementById('target_karton_qty');
    if (hasilInput) hasilInput.value = item.hasil_karton_qty;
    if (targetInput) targetInput.value = item.hasil_karton_qty;

    // Set isi per karton jika ada
    const isiPerKartonInput = document.getElementById('isi_per_karton_pcs');
    if (isiPerKartonInput && item.hasil_pack_qty && item.hasil_karton_qty) {
        const calculatedIsi = Math.round(parseFloat(item.hasil_pack_qty) / parseFloat(item.hasil_karton_qty));
        if (calculatedIsi > 0) {
            isiPerKartonInput.value = calculatedIsi;
        }
    }

    // Nomor Batch dari WIP
    const batchInput = document.getElementById('batch_no');
    if (batchInput && item.wip_batch_no) {
        batchInput.value = item.wip_batch_no;
    }

    // 4. Bangun baris-baris material LANGSUNG DARI DATA INPUT OPERATOR terlebih dahulu
    buildMaterialRowsFromOperator(item);

    // 5. Set Produksi WIP jika ada (dengan skipAddRow = true agar tidak memunculkan baris WIP phantom)
    const prodSelect = document.getElementById('produksi_id');
    if (prodSelect && item.produksi_id) {
        const targetWipId = item.wip_barang_id || (item.wip_barang ? item.wip_barang.barang_id : null);
        let matchedOpt = null;
        if (targetWipId) {
            matchedOpt = Array.from(prodSelect.options).find(opt => 
                opt.value == item.produksi_id && opt.getAttribute('data-barang-id') == targetWipId
            );
        }
        if (!matchedOpt) {
            matchedOpt = Array.from(prodSelect.options).find(opt => opt.value == item.produksi_id);
        }
        if (matchedOpt) {
            prodSelect.selectedIndex = matchedOpt.index;
        } else {
            prodSelect.value = item.produksi_id;
        }
        onProduksiSelect(prodSelect, true);
    }

    // 6. Tampilkan Banner Status Tarik Data
    const badge = document.getElementById('operatorSyncBadge');
    const title = document.getElementById('operatorSyncTitle');
    const subtitle = document.getElementById('operatorSyncSubtitle');
    if (badge && title && subtitle) {
        title.textContent = `Data Terhubung dengan Laporan Operator: ${item.laporan_no}`;
        subtitle.innerHTML = `Leader: <strong>${item.operator_nama || '-'}</strong> &bull; Shift <strong>${item.shift}</strong> &bull; Curah: <strong>${formatNumber(item.wip_qty_kg)} ${item.wip_satuan || 'Kg'}</strong> &bull; Output: <strong>${formatNumber(item.hasil_karton_qty)} ${item.satuan_hasil || 'Karton'}</strong>`;
        badge.style.display = 'flex';
    }

    // Tambah info ke catatan form jika ada afkir
    const catatanInput = document.getElementById('catatan_txt');
    if (catatanInput) {
        const afkirParts = [];
        if (parseFloat(item.plastik_rusak_pcs) > 0) afkirParts.push(`Plastik afkir: ${item.plastik_rusak_pcs} pcs`);
        if (parseFloat(item.karton_rusak_lembar) > 0) afkirParts.push(`Dus afkir: ${item.karton_rusak_lembar} lbr`);
        
        let extraNote = `Ditarik dari Laporan Operator ${item.laporan_no} (Shift ${item.shift}, Operator: ${item.operator_nama || '-'}).`;
        if (afkirParts.length > 0) {
            extraNote += ` ${afkirParts.join(', ')}.`;
        }
        if (item.catatan) {
            extraNote += ` Catatan: "${item.catatan}"`;
        }
        if (!catatanInput.value || catatanInput.value.includes('Ditarik dari Laporan Operator')) {
            catatanInput.value = extraNote;
        }
    }

    // 7. Sinkronkan Timbangan Lakban Atas & Bawah
    const lakbanAtasInput = document.getElementById('lakban_atas_gr');
    const lakbanBawahInput = document.getElementById('lakban_bawah_gr');
    let atasGr = parseFloat(item.lakban_atas_gr) || 0;
    let bawahGr = parseFloat(item.lakban_bawah_gr) || 0;
    if (atasGr <= 0 && bawahGr <= 0 && parseFloat(item.lakban_qty) > 0) {
        atasGr = Math.round((parseFloat(item.lakban_qty) / 2) * 10) / 10;
        bawahGr = Math.round((parseFloat(item.lakban_qty) / 2) * 10) / 10;
    }
    if (lakbanAtasInput) lakbanAtasInput.value = atasGr;
    if (lakbanBawahInput) lakbanBawahInput.value = bawahGr;
    onLakbanInputsChanged();

    // 8. Tetap ambil ID BOM secara pasif tanpa mereset tabel material
    fetchBomPreview();

    closePullOperatorModal();
}

/**
 * Bangun baris-baris material HPP FG tepat sesuai inputan aktual operator packaging di HP
 */
function buildMaterialRowsFromOperator(item) {
    const tbody = document.getElementById('materialItemsTbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    itemIndexCounter = 0;

    // Helper untuk mengambil harga HPP WIP Curah
    let hppWip = 0;
    if (item.produksi && parseFloat(item.produksi.hpp_per_kg) > 0) {
        hppWip = parseFloat(item.produksi.hpp_per_kg);
    } else {
        const prodSelect = document.getElementById('produksi_id');
        if (prodSelect && prodSelect.selectedIndex > 0) {
            const opt = prodSelect.options[prodSelect.selectedIndex];
            hppWip = parseFloat(opt.getAttribute('data-hpp')) || 0;
        }
    }

    // 1. Bahan Keripik Curah (WIP)
    if (item.wip_barang_id || (parseFloat(item.wip_qty_kg) > 0)) {
        const wipMaster = findMasterKemasan(item.wip_barang_id);
        const wipObj = item.wip_barang || wipMaster;
        const bId = item.wip_barang_id || wipObj?.barang_id || 45;
        const bNm = wipObj?.barang_nm || 'KS BERKO (Keripik Curah WIP)';
        const bCd = wipObj?.barang_cd || 'WIP-BRK';
        const satuan = item.wip_satuan || wipObj?.satuan_dasar?.satuan_cd || wipMaster?.satuan_cd || 'KG';
        const qtyWip = parseFloat(item.wip_qty_kg) || 0;
        const finalHppWip = hppWip > 0 ? hppWip : (parseFloat(wipMaster?.harga_standar) || 0);

        addMaterialRow({
            kategori_bahan: 'WIP',
            barang_id: bId,
            barang_nm: bNm,
            barang_cd: bCd,
            satuan_cd: satuan,
            qty_standar: qtyWip,
            qty_aktual: qtyWip,
            harga_satuan: finalHppWip,
            subtotal_nilai: qtyWip * finalHppWip,
            batch_no: item.wip_batch_no || '',
            is_wip: true,
            sync_source: item.produksi?.produksi_no || 'Operator HP'
        });
    }

    // 2. Bumbu Tabur (Seasoning)
    if (item.bumbu_barang_id && parseFloat(item.bumbu_qty_kg) > 0) {
        const bumbuMaster = findMasterKemasan(item.bumbu_barang_id);
        const bumbuObj = item.bumbu_barang || bumbuMaster;
        const qtyBumbu = parseFloat(item.bumbu_qty_kg);
        const hargaBumbu = parseFloat(bumbuMaster?.harga_standar || bumbuObj?.harga_beli_standar || 0);

        addMaterialRow({
            kategori_bahan: 'BUMBU',
            barang_id: item.bumbu_barang_id,
            barang_nm: bumbuObj?.barang_nm || bumbuMaster?.barang_nm || 'Bumbu Tabur',
            barang_cd: bumbuObj?.barang_cd || bumbuMaster?.barang_cd || 'BUMBU',
            satuan_cd: bumbuObj?.satuan_dasar?.satuan_cd || bumbuMaster?.satuan_cd || 'KG',
            qty_standar: qtyBumbu,
            qty_aktual: qtyBumbu,
            harga_satuan: hargaBumbu,
            subtotal_nilai: qtyBumbu * hargaBumbu,
            batch_no: '',
            is_wip: false
        });
    }

    // 3. Plastik Kemasan (Roll / Pouch)
    if (item.plastik_barang_id && parseFloat(item.plastik_qty) > 0) {
        const plastikMaster = findMasterKemasan(item.plastik_barang_id);
        const plastikObj = item.plastik_barang || plastikMaster;
        const qtyPlastik = parseFloat(item.plastik_qty);
        const hargaPlastik = parseFloat(plastikMaster?.harga_standar || plastikObj?.harga_beli_standar || 0);

        addMaterialRow({
            kategori_bahan: 'KEMASAN',
            barang_id: item.plastik_barang_id,
            barang_nm: plastikObj?.barang_nm || plastikMaster?.barang_nm || 'Plastik Kemasan',
            barang_cd: plastikObj?.barang_cd || plastikMaster?.barang_cd || 'PLASTIK',
            satuan_cd: plastikObj?.satuan_dasar?.satuan_cd || plastikMaster?.satuan_cd || 'PCS',
            qty_standar: qtyPlastik,
            qty_aktual: qtyPlastik,
            harga_satuan: hargaPlastik,
            subtotal_nilai: qtyPlastik * hargaPlastik,
            batch_no: '',
            is_wip: false
        });
    }

    // 4. Karton Dus (Box)
    if (item.karton_barang_id && parseFloat(item.karton_qty) > 0) {
        const kartonMaster = findMasterKemasan(item.karton_barang_id);
        const kartonObj = item.karton_barang || kartonMaster;
        const qtyKarton = parseFloat(item.karton_qty);
        const hargaKarton = parseFloat(kartonMaster?.harga_standar || kartonObj?.harga_beli_standar || 0);

        addMaterialRow({
            kategori_bahan: 'KEMASAN',
            barang_id: item.karton_barang_id,
            barang_nm: kartonObj?.barang_nm || kartonMaster?.barang_nm || 'Karton Box (Dus)',
            barang_cd: kartonObj?.barang_cd || kartonMaster?.barang_cd || 'KARTON',
            satuan_cd: kartonObj?.satuan_dasar?.satuan_cd || kartonMaster?.satuan_cd || 'LEMBAR',
            qty_standar: qtyKarton,
            qty_aktual: qtyKarton,
            harga_satuan: hargaKarton,
            subtotal_nilai: qtyKarton * hargaKarton,
            batch_no: '',
            is_wip: false
        });
    }

    // 5. Plastik / Kertas Expired (Kadaluarsa)
    if (item.expired_barang_id && parseFloat(item.expired_qty) > 0) {
        const expiredMaster = findMasterKemasan(item.expired_barang_id);
        const expiredObj = item.expired_barang || expiredMaster;
        const qtyExpired = parseFloat(item.expired_qty);
        const hargaExpired = parseFloat(expiredMaster?.harga_standar || expiredObj?.harga_beli_standar || 0);

        addMaterialRow({
            kategori_bahan: 'KEMASAN',
            barang_id: item.expired_barang_id,
            barang_nm: expiredObj?.barang_nm || expiredMaster?.barang_nm || 'Label / Kertas Expired',
            barang_cd: expiredObj?.barang_cd || expiredMaster?.barang_cd || 'EXP',
            satuan_cd: expiredObj?.satuan_dasar?.satuan_cd || expiredMaster?.satuan_cd || 'LEMBAR',
            qty_standar: qtyExpired,
            qty_aktual: qtyExpired,
            harga_satuan: hargaExpired,
            subtotal_nilai: qtyExpired * hargaExpired,
            batch_no: '',
            is_wip: false
        });
    }

    // 6. Lakban Timbangan (Atas & Bawah)
    const lakbanAtas = parseFloat(item.lakban_atas_gr) || 0;
    const lakbanBawah = parseFloat(item.lakban_bawah_gr) || 0;
    let totalLakbanGr = Math.round((lakbanAtas + lakbanBawah) * 10) / 10;
    if (totalLakbanGr <= 0 && parseFloat(item.lakban_qty) > 0) {
        totalLakbanGr = parseFloat(item.lakban_qty);
    }

    if (totalLakbanGr > 0) {
        // Cari master lakban
        let lakbanMaster = findMasterKemasan(item.lakban_barang_id);
        if (!lakbanMaster) {
            lakbanMaster = (window.masterKemasanList || []).find(m => 
                (m.barang_cd && m.barang_cd.includes('LKB')) || 
                (m.barang_nm && m.barang_nm.toUpperCase().includes('LAKBAN'))
            );
        }
        const lakbanObj = item.lakban_barang || lakbanMaster;
        // Prioritaskan tarif lakban timbangan per gram dari Master Data Tarif Kemasan
        let tarifLakban = (typeof window.tarifLakbanDefault !== 'undefined' && window.tarifLakbanDefault > 0)
            ? parseFloat(window.tarifLakbanDefault)
            : 36.70;
        if (lakbanMaster && (lakbanMaster.satuan_cd === 'GRAM' || lakbanMaster.satuan_cd === 'GR') && parseFloat(lakbanMaster.harga_standar) > 0) {
            tarifLakban = parseFloat(lakbanMaster.harga_standar);
        }

        addMaterialRow({
            kategori_bahan: 'LAKBAN',
            barang_id: lakbanObj?.barang_id || lakbanMaster?.barang_id || '',
            barang_nm: lakbanObj?.barang_nm || lakbanMaster?.barang_nm || 'Lakban Timbangan (Selotip)',
            barang_cd: lakbanObj?.barang_cd || lakbanMaster?.barang_cd || 'LKB',
            satuan_cd: 'GRAM',
            qty_standar: totalLakbanGr,
            qty_aktual: totalLakbanGr,
            harga_satuan: tarifLakban,
            subtotal_nilai: Math.round(totalLakbanGr * tarifLakban),
            batch_no: '',
            is_wip: false
        });
    }

    // 7. Kemasan Tambahan (Multi Item - Support Bebas Berapa Saja Barang yang Diinput Operator)
    let extraList = [];
    if (item.formatted_kemasan_tambahan && Array.isArray(item.formatted_kemasan_tambahan)) {
        extraList = item.formatted_kemasan_tambahan;
    } else if (item.kemasan_tambahan_items) {
        if (Array.isArray(item.kemasan_tambahan_items)) {
            extraList = item.kemasan_tambahan_items;
        } else if (typeof item.kemasan_tambahan_items === 'string') {
            try {
                let parsed = JSON.parse(item.kemasan_tambahan_items);
                if (typeof parsed === 'string') parsed = JSON.parse(parsed);
                if (Array.isArray(parsed)) extraList = parsed;
            } catch (e) {
                console.warn('Gagal parse kemasan_tambahan_items:', e);
            }
        }
    }

    if (extraList && extraList.length > 0) {
        extraList.forEach(extra => {
            const extraId = extra.barang_id || (extra.barang ? extra.barang.barang_id : null);
            const extraQty = parseFloat(extra.qty) || 0;
            if (extraId && extraQty > 0) {
                const extraMaster = findMasterKemasan(extraId);
                const extraHarga = parseFloat(extra.harga_standar || extraMaster?.harga_standar || 0);

                addMaterialRow({
                    kategori_bahan: 'KEMASAN',
                    barang_id: extraId,
                    barang_nm: extra.barang_nm || extraMaster?.barang_nm || 'Kemasan Tambahan',
                    barang_cd: extra.barang_cd || extraMaster?.barang_cd || 'KEMASAN',
                    satuan_cd: extra.satuan_nm || extraMaster?.satuan_cd || 'PCS',
                    qty_standar: extraQty,
                    qty_aktual: extraQty,
                    harga_satuan: extraHarga,
                    subtotal_nilai: extraQty * extraHarga,
                    batch_no: '',
                    is_wip: false
                });
            }
        });
    } else if (item.kemasan_lain_barang_id && parseFloat(item.kemasan_lain_qty) > 0) {
        const lainMaster = findMasterKemasan(item.kemasan_lain_barang_id);
        const lainObj = item.kemasanLainBarang || lainMaster;
        const qtyLain = parseFloat(item.kemasan_lain_qty);
        const hargaLain = parseFloat(lainMaster?.harga_standar || lainObj?.harga_beli_standar || 0);

        addMaterialRow({
            kategori_bahan: 'KEMASAN',
            barang_id: item.kemasan_lain_barang_id,
            barang_nm: lainObj?.barang_nm || lainMaster?.barang_nm || 'Kemasan Tambahan',
            barang_cd: lainObj?.barang_cd || lainMaster?.barang_cd || 'KEMASAN',
            satuan_cd: lainObj?.satuan_dasar?.satuan_cd || lainMaster?.satuan_cd || 'PCS',
            qty_standar: qtyLain,
            qty_aktual: qtyLain,
            harga_satuan: hargaLain,
            subtotal_nilai: qtyLain * hargaLain,
            batch_no: '',
            is_wip: false
        });
    }

    recalculateTotals();
}

/**
 * Handler ketika input timbangan Lakban Atas atau Bawah diubah
 */
function onLakbanInputsChanged() {
    const atas = parseFloat(document.getElementById('lakban_atas_gr')?.value) || 0;
    const bawah = parseFloat(document.getElementById('lakban_bawah_gr')?.value) || 0;
    const totalGr = Math.round((atas + bawah) * 10) / 10;

    const totalInput = document.getElementById('lakban_jumlah_gr');
    if (totalInput) totalInput.value = totalGr;

    // Ambil harga standar lakban timbangan (per gram) dari Master Data Tarif Kemasan
    let tarif = (typeof window.tarifLakbanDefault !== 'undefined' && window.tarifLakbanDefault > 0)
        ? parseFloat(window.tarifLakbanDefault)
        : 36.70;

    const tarifInfo = document.getElementById('tarifLakbanInfo');
    if (tarifInfo) tarifInfo.textContent = formatRupiah(tarif);

    const totalNilai = Math.round(totalGr * tarif);
    const hiddenNilai = document.getElementById('lakban_nilai');
    if (hiddenNilai) hiddenNilai.value = totalNilai;

    const displayNilai = document.getElementById('lakbanNilaiDisplay');
    if (displayNilai) displayNilai.textContent = 'Rp ' + formatRupiah(totalNilai);

    // Sinkronkan ke baris tabel material jika ada baris komponen lakban
    const rows = document.querySelectorAll('.material-row');
    rows.forEach(tr => {
        const catInput = tr.querySelector('input[name*="[kategori_bahan]"]');
        const text = tr.textContent.toUpperCase();
        const cat = (catInput?.value || '').toUpperCase();

        if (cat === 'LAKBAN' || text.includes('LAKBAN') || text.includes('LKB')) {
            const qtyInput = tr.querySelector('.row-qty-input');
            const priceInput = tr.querySelector('.row-price-input');
            if (qtyInput) qtyInput.value = totalGr;
            if (priceInput && (!priceInput.value || parseFloat(priceInput.value) <= 0)) {
                priceInput.value = tarif;
            }
            const rowIdParts = tr.id.split('_');
            if (rowIdParts.length > 1) {
                recalculateRow(rowIdParts[1]);
            }
        }
    });

    recalculateTotals();
}

function clearOperatorSync() {
    const hiddenId = document.getElementById('laporan_operator_id');
    const badge = document.getElementById('operatorSyncBadge');
    if (hiddenId) hiddenId.value = '';
    if (badge) badge.style.display = 'none';

    // Refresh formula BOM standar
    fetchBomPreview();
}

/**
 * Modal Quick Matrix Update (Standar Harga Kemasan & Lakban Langsung dari Form)
 */
function openQuickUpdateModal() {
    const modal = document.getElementById('modalQuickUpdateTarif');
    if (modal) {
        modal.classList.add('show');
    }
}

function closeQuickUpdateModal() {
    const modal = document.getElementById('modalQuickUpdateTarif');
    if (modal) {
        modal.classList.remove('show');
    }
}

// Inisialisasi AJAX submit handler untuk Modal Quick Update Tarif Kemasan
document.addEventListener('DOMContentLoaded', function () {
    const formQuick = document.getElementById('formQuickUpdateTarif');
    if (formQuick) {
        formQuick.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const origHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Menyimpan...</span>';
            }

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }

                if (res.success) {
                    const map = res.data || {};

                    // 1. Perbarui default tarif lakban gram
                    if (typeof map.LAKBAN_GRAM !== 'undefined') {
                        window.tarifLakbanDefault = parseFloat(map.LAKBAN_GRAM);
                        const elInfo = document.getElementById('tarifLakbanInfo');
                        if (elInfo) elInfo.textContent = formatRupiah(window.tarifLakbanDefault);
                        onLakbanInputsChanged();
                    }

                    // 2. Perbarui harga standar pada masterKemasanList
                    if (window.masterKemasanList && Array.isArray(window.masterKemasanList)) {
                        window.masterKemasanList.forEach(item => {
                            const nm = (item.barang_nm || '').toUpperCase();
                            if (nm.includes('KARTON') && (nm.includes('MAKSI') || nm.includes('MX') || nm.includes('MK'))) {
                                if (map.KARTON_MX) item.harga_standar = parseFloat(map.KARTON_MX);
                            } else if (nm.includes('KARTON')) {
                                if (map.KARTON_XX) item.harga_standar = parseFloat(map.KARTON_XX);
                            } else if (nm.includes('BUMBU') || nm.includes('BALADO')) {
                                if (map.BUMBU_BALADO) item.harga_standar = parseFloat(map.BUMBU_BALADO);
                            } else if (nm.includes('ROLL') && (nm.includes('MAKSI') || nm.includes('MX') || nm.includes('MK'))) {
                                if (map.ROLL_MX) item.harga_standar = parseFloat(map.ROLL_MX);
                            } else if (nm.includes('ROLL') || nm.includes('PLASTIK')) {
                                if (map.ROLL_XX) item.harga_standar = parseFloat(map.ROLL_XX);
                            } else if (nm.includes('LAKBAN')) {
                                if (map.LAKBAN_ROLL) item.harga_standar = parseFloat(map.LAKBAN_ROLL);
                            } else if (nm.includes('EXP') || nm.includes('KERTAS')) {
                                if (map.KERTAS_EXP) item.harga_standar = parseFloat(map.KERTAS_EXP);
                            } else if (nm.includes('BERKO')) {
                                if (map.WIP_BERKO) item.harga_standar = parseFloat(map.WIP_BERKO);
                            } else if (item.kategori === 'WIP') {
                                if (map.WIP_CURAH) item.harga_standar = parseFloat(map.WIP_CURAH);
                            }
                        });
                    }

                    // 3. Sinkronkan harga satuan di baris tabel material jika belum disesuaikan manual
                    const rows = document.querySelectorAll('.material-row');
                    rows.forEach(tr => {
                        const selectEl = tr.querySelector('.row-barang-select');
                        const priceInput = tr.querySelector('.row-price-input');
                        const barangId = selectEl ? selectEl.value : tr.querySelector('input[name*="[barang_id]"]')?.value;
                        const rowIdx = tr.id ? tr.id.replace('materialRow_', '') : null;

                        if (barangId && priceInput) {
                            const m = findMasterKemasan(barangId);
                            if (m && parseFloat(m.harga_standar) > 0) {
                                priceInput.value = m.harga_standar;
                                if (rowIdx !== null) recalculateRow(rowIdx);
                            }
                        }
                    });

                    recalculateTotals();
                    closeQuickUpdateModal();

                    // Notifikasi visual
                    const alertBox = document.createElement('div');
                    alertBox.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; background: #059669; color: #ffffff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;';
                    alertBox.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Standar harga kemasan berhasil disinkronkan &amp; diterapkan langsung!</span>';
                    document.body.appendChild(alertBox);
                    setTimeout(() => alertBox.remove(), 4000);
                } else {
                    alert(res.message || 'Gagal menyimpan perubahan tarif kemasan.');
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }
                console.error(err);
                alert('Terjadi kendala jaringan saat menyimpan standar tarif.');
            });
        });
    }
});
