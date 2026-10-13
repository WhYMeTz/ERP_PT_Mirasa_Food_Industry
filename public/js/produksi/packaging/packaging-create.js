/**
 * =========================================================================
 * ERP PT MIRASA - PENGEMASAN BARANG JADI & HPP (PACKAGING) CREATE JS
 * Smart Backflush BOM Preview, HPP WIP Integration & Real-Time HPP Engine
 * =========================================================================
 */

let itemIndexCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    const barangSelect = document.getElementById('barang_jadi_id');
    const targetKartonInput = document.getElementById('target_karton_qty');
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
        fetchBomPreview();
    });

    // Sinkronisasi target ke hasil karton jika belum diubah
    let hasilTouched = false;
    hasilKartonInput?.addEventListener('input', function () {
        hasilTouched = true;
        recalculateTotals();
    });

    targetKartonInput?.addEventListener('input', function () {
        if (!hasilTouched && hasilKartonInput) {
            hasilKartonInput.value = this.value;
        }
        // Debounce BOM preview request
        debounce(fetchBomPreview, 400)();
    });

    isiPerKartonInput?.addEventListener('input', recalculateTotals);
    upahTkInput?.addEventListener('input', recalculateTotals);
    biayaLainInput?.addEventListener('input', recalculateTotals);

    // Initial batch generator
    updateBatchNumber();
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
 * Generate nomor lot batch default berdasarkan tanggal & produk
 */
function updateBatchNumber() {
    const batchInput = document.getElementById('batch_no');
    const packTgl = document.getElementById('pack_tgl')?.value || new Date().toISOString().slice(0, 10);
    const barangSelect = document.getElementById('barang_jadi_id');
    const barangId = barangSelect?.value;

    if (batchInput && !batchInput.getAttribute('data-customized')) {
        const dateStr = packTgl.replace(/-/g, '').slice(2); // YYMMDD
        const idStr = barangId ? String(barangId).padStart(3, '0') : '001';
        batchInput.value = 'LOT-FG-' + dateStr + '-' + idStr;
    }
}

/**
 * Mengambil pratinjau resep BOM dari server via AJAX
 */
function fetchBomPreview() {
    const barangId = document.getElementById('barang_jadi_id')?.value;
    const targetQty = parseFloat(document.getElementById('target_karton_qty')?.value) || 1;
    const bannerBox = document.getElementById('bomBannerContainer');
    const tbody = document.getElementById('materialItemsTbody');
    const emptyRow = document.getElementById('emptyMaterialRow');
    const bomIdHidden = document.getElementById('bom_id_hidden');

    if (!barangId) {
        if (bannerBox) bannerBox.innerHTML = '';
        if (tbody) tbody.innerHTML = '';
        if (emptyRow) emptyRow.style.display = 'table-row';
        recalculateTotals();
        return;
    }

    // Tampilkan loading indicator di banner
    if (bannerBox) {
        bannerBox.innerHTML = `
            <div class="bom-banner" style="background: #f8fafc; border-color: #cbd5e1;">
                <div style="display: flex; align-items: center; gap: 0.6rem; color: #64748b; font-size: 0.825rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="animate-spin"><circle cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor" stroke-dasharray="32" stroke-linecap="round"/></svg>
                    <span>Memuat formula resep standar (BOM), menarik saldo stok FIFO & HPP curah gudang...</span>
                </div>
            </div>
        `;
    }

    const url = `/produksi/packaging/ajax-bom-preview?barang_jadi_id=${barangId}&target_karton_qty=${targetQty}`;

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
                                <span class="bom-banner-badge">Formula BOM Aktif</span>
                                <div style="font-size: 0.825rem; font-weight: 700; color: #166534;">
                                    ${data.bom_no} - ${data.bom_nm} 
                                    <span style="font-weight: 500; color: #15803d;">(Batch Standar: ${data.batch_ukuran_qty} Karton)</span>
                                </div>
                            </div>
                            <div style="font-size: 0.725rem; color: #15803d; font-weight: 600;">
                                ⚡ Smart Backflush Diterapkan: Kebutuhan bahan proporsional sesuai target ${data.target_karton} karton.
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
 * Tambah baris material ke tabel
 */
function addMaterialRow(item = {}) {
    const tbody = document.getElementById('materialItemsTbody');
    const emptyRow = document.getElementById('emptyMaterialRow');
    if (emptyRow) emptyRow.style.display = 'none';

    const idx = itemIndexCounter++;
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
    const wipSource = item.wip_source || null;

    let catClass = 'cat-kemasan';
    if (kategori === 'WIP') catClass = 'cat-wip';
    else if (kategori === 'BUMBU') catClass = 'cat-bumbu';
    else if (kategori === 'LAIN') catClass = 'cat-lain';

    const tr = document.createElement('tr');
    tr.id = `materialRow_${idx}`;
    tr.className = 'material-row';

    tr.innerHTML = `
        <td>
            <span class="cat-badge ${catClass}">${kategori}</span>
            <input type="hidden" name="items[${idx}][kategori_bahan]" value="${kategori}">
            <input type="hidden" name="items[${idx}][barang_id]" value="${barangId}">
            <input type="hidden" name="items[${idx}][satuan_cd]" value="${satuanCd}">
            <input type="hidden" name="items[${idx}][batch_no]" value="${batchNo}">
        </td>
        <td>
            <div style="font-weight: 700; color: #0f172a;">${barangNm}</div>
            <div style="font-size: 0.7rem; color: #64748b;">
                Kode: ${barangCd}
                ${batchNo ? ` &bull; Lot: ${batchNo}` : ''}
            </div>
            ${stokTersedia !== null ? `
                <div style="margin-top: 0.2rem;">
                    <span class="${cukupStok ? 'stock-tag-ok' : 'stock-tag-warn'}">
                        Stok Fisik: ${formatNumber(stokTersedia)} ${satuanCd} ${cukupStok ? '✓ Cukup' : '⚠️ Kurang'}
                    </span>
                </div>
            ` : ''}
        </td>
        <td style="text-align: right; color: #64748b; font-weight: 600;">
            <input type="hidden" name="items[${idx}][qty_standar]" value="${qtyStandar}">
            <span>${formatNumber(qtyStandar)}</span> <small>${satuanCd}</small>
        </td>
        <td style="width: 140px;">
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <input type="number" step="0.0001" min="0" required
                       name="items[${idx}][qty_aktual]" 
                       value="${qtyAktual}"
                       class="form-input row-qty-input" 
                       style="text-align: right; font-weight: 700; height: 32px; padding: 0.2rem 0.5rem;"
                       oninput="recalculateRow(${idx})">
                <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">${satuanCd}</span>
            </div>
        </td>
        <td style="width: 140px; text-align: right;">
            <input type="number" step="0.01" min="0" required
                   name="items[${idx}][harga_satuan]" 
                   value="${hargaSatuan}"
                   class="form-input row-price-input" 
                   style="text-align: right; height: 32px; padding: 0.2rem 0.5rem; font-weight: ${isWip ? '700' : '500'}; color: ${isWip ? '#065f46' : '#0f172a'};"
                   oninput="recalculateRow(${idx})">
            ${isWip ? `
                <div class="wip-origin-tag" id="wipOriginTag_${idx}">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    ✓ HPP Saldo Gudang WIP (FIFO)
                </div>
            ` : `
                <div class="pack-origin-tag">Harga Beli Kemasan</div>
            `}
        </td>
        <td style="text-align: right; font-weight: 800; color: #0f172a; width: 140px;">
            <span id="subtotalLabel_${idx}">Rp ${formatRupiah(qtyAktual * hargaSatuan)}</span>
            <input type="hidden" id="subtotalVal_${idx}" value="${qtyAktual * hargaSatuan}">
        </td>
        <td style="text-align: center; width: 45px;">
            <button type="button" class="btn btn-secondary" style="padding: 0.25rem 0.45rem; color: #dc2626;" title="Hapus Komponen" onclick="removeMaterialRow(${idx})">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    recalculateTotals();
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
