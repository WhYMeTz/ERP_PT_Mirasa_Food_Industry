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

    // Auto load jika barang sudah terpilih (misal form redirect back)
    if (barangSelect && barangSelect.value) {
        fetchBomPreview();
    }

    // Event listener pemilihan produk jadi
    barangSelect?.addEventListener('change', function () {
        updateBatchNumber();
        if (hasilKartonInput && parseFloat(hasilKartonInput.value) > 0) {
            fetchBomPreview();
        }
    });

    hasilKartonInput?.addEventListener('input', function () {
        recalculateTotals();
        if (barangSelect && barangSelect.value) {
            debounce(fetchBomPreview, 500)();
        }
    });

    isiPerKartonInput?.addEventListener('input', recalculateTotals);
    upahTkInput?.addEventListener('input', recalculateTotals);
    biayaLainInput?.addEventListener('input', recalculateTotals);

    // Format nomor batch default berupa tanggal
    updateBatchNumber();

    // Auto-sync jika produksi sudah terpilih (misal form redirect back atau reload)
    const prodSelect = document.getElementById('produksi_id');
    if (prodSelect && prodSelect.value) {
        onProduksiSelect(prodSelect);
    }

    // Hitung kalkulasi awal saat halaman dimuat
    recalculateTotals();
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

            // Render Tabel Komponen Bahan
            if (tbody) {
                tbody.innerHTML = '';
                itemIndexCounter = 0;

                if (data.items && data.items.length > 0) {
                    data.items.forEach(item => {
                        addMaterialRow(item);
                    });
                } else {
                    if (emptyRow) tbody.appendChild(emptyRow);
                }
            }

            recalculateTotals();
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
function onProduksiSelect(selectEl) {
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
    const hppVal = parseFloat(opt.getAttribute('data-hpp')) || 0;
    const batchVal = opt.getAttribute('data-batch') || '';
    const expVal = opt.getAttribute('data-exp') || '';
    const qtyVal = parseFloat(opt.getAttribute('data-qty')) || 0;
    const prodNo = opt.getAttribute('data-no') || '';

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
        syncText.innerHTML = `Tersinkron dengan <strong>${prodNo}</strong>: Keripik Curah KS Berko &bull; Lot: <code>${batchVal}</code> &bull; Exp: <strong>${formatDateIndo(expVal)}</strong> (HPP: Rp ${formatRupiah(hppVal)}/Kg)`;
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
                originTag.innerHTML = `
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    ✓ HPP Produksi ${prodNo}
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

    // Jika belum ada baris WIP di tabel (misal belum ada produk FG/BOM), tarik detail master barang KS Berko via AJAX
    if (!foundWip) {
        fetch(`/produksi/packaging/ajax-produksi-details?produksi_id=${prodId}`)
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
                addMaterialRow({
                    kategori_bahan: 'WIP',
                    barang_id: 45,
                    barang_nm: 'KS BERKO (Keripik Curah)',
                    barang_cd: 'WIP-BRK',
                    satuan_cd: 'KG',
                    qty_standar: 0,
                    qty_aktual: 0,
                    harga_satuan: hppVal,
                    subtotal_nilai: 0,
                    batch_no: batchVal,
                    stok_tersedia: qtyVal,
                    cukup_stok: true,
                    is_wip: true,
                    sync_source: prodNo
                });
                recalculateTotals();
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

function addCustomMaterialRow() {
    addNewMaterialRow();
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
                <div class="pack-origin-tag">Harga Modal</div>
            `}
        </td>
        <td style="text-align: right; font-weight: 700; color: #0f172a; width: 150px;">
            <span id="subtotalLabel_${idx}">Rp ${formatRupiah(qtyAktual * hargaSatuan)}</span>
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

    const qty = parseFloat(qtyInput?.value) || 0;
    const price = parseFloat(priceInput?.value) || 0;
    const subtotal = Math.round(qty * price * 100) / 100;

    if (subtotalLabel) subtotalLabel.textContent = 'Rp ' + formatRupiah(subtotal);
    if (subtotalVal) subtotalVal.value = subtotal;

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
