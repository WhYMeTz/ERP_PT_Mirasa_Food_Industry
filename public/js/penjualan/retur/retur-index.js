/**
 * ══════════════════════════════════════════════════════════════════════════
 * RETUR PENJUALAN (SALES RETURN) - INDEX SCRIPT
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

function toggleSmartActionDropdown(btn, e, menuId) {
    e.stopPropagation();

    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isVisible = menu.style.display === 'block';

    // Tutup semua menu yang sedang terbuka
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-action-trigger').forEach(b => {
        b.classList.remove('active');
    });

    if (!isVisible) {
        const rect = btn.getBoundingClientRect();
        const menuWidth = 200;
        const menuHeight = 110;

        // Posisi X (Horizontal)
        let left = rect.right - menuWidth;
        if (left < 10) left = rect.left;

        // Posisi Y (Vertical)
        let top = rect.bottom + 4;
        if (top + menuHeight > window.innerHeight) {
            top = rect.top - menuHeight - 4;
        }

        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.style.display = 'block';
        btn.classList.add('active');
    }
}

function openVoidModal(returId, returNo) {
    const modal = document.getElementById('modalVoidReturIndex');
    if (!modal) return;

    const form = document.getElementById('formVoidReturIndex');
    if (form) {
        form.action = `/penjualan/retur/${returId}/void`;
    }

    const lbl = document.getElementById('modalVoidReturNoLbl');
    if (lbl) {
        lbl.textContent = `Dokumen: ${returNo}`;
    }

    modal.style.display = 'flex';
}

function closeVoidModal() {
    const modal = document.getElementById('modalVoidReturIndex');
    if (modal) {
        modal.style.display = 'none';
    }
}

/* ══════════════════════════════════════════════════════════════════════════
   MODAL CATAT RETUR BARU (INTEGRASI SO TERKIRIM)
   ══════════════════════════════════════════════════════════════════════════ */

function openCreateReturModal() {
    const modal = document.getElementById('modalCreateRetur');
    if (!modal) return;

    const select = document.getElementById('createReturSoSelect');
    if (select) select.value = '';

    const emptyPrompt = document.getElementById('returEmptyPrompt');
    if (emptyPrompt) emptyPrompt.style.display = 'block';

    const formFields = document.getElementById('returFormFields');
    if (formFields) formFields.style.display = 'none';

    const loading = document.getElementById('returLoadingIndicator');
    if (loading) loading.style.display = 'none';

    const btnSubmit = document.getElementById('btnSubmitCreateRetur');
    if (btnSubmit) btnSubmit.disabled = true;

    modal.style.display = 'flex';
}

function closeCreateReturModal() {
    const modal = document.getElementById('modalCreateRetur');
    if (modal) {
        modal.style.display = 'none';
    }
}

async function onSelectSoForRetur(soId) {
    const emptyPrompt = document.getElementById('returEmptyPrompt');
    const formFields = document.getElementById('returFormFields');
    const loading = document.getElementById('returLoadingIndicator');
    const btnSubmit = document.getElementById('btnSubmitCreateRetur');
    const form = document.getElementById('formCreateRetur');
    const tbody = document.getElementById('createReturItemsTbody');
    const batchLbl = document.getElementById('returBatchLabel');

    if (!soId) {
        if (emptyPrompt) emptyPrompt.style.display = 'block';
        if (formFields) formFields.style.display = 'none';
        if (loading) loading.style.display = 'none';
        if (btnSubmit) btnSubmit.disabled = true;
        return;
    }

    if (emptyPrompt) emptyPrompt.style.display = 'none';
    if (formFields) formFields.style.display = 'none';
    if (loading) loading.style.display = 'block';
    if (btnSubmit) btnSubmit.disabled = true;

    try {
        const res = await fetch(`/penjualan/retur/so-data/${soId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const json = await res.json();

        if (!res.ok || json.status !== 'success') {
            throw new Error(json.message || 'Gagal mengambil data pesanan.');
        }

        const so = json.data;

        // Set action form
        if (form) {
            form.action = `/penjualan/so/${so.so_id}/retur`;
        }

        // Set batch label
        if (batchLbl) {
            batchLbl.textContent = `RET-${so.so_no}`;
        }

        // Render tabel item
        if (tbody) {
            tbody.innerHTML = '';

            if (!so.items || so.items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 1.5rem; color:#64748b;">Tidak ada item barang dalam pesanan ini.</td></tr>`;
            } else {
                so.items.forEach((item, idx) => {
                    const maxRetur = parseFloat(item.max_returnable) || 0;
                    const isAvailable = maxRetur > 0;
                    const alreadyReturText = item.already_retur_qty > 0 
                        ? `<span style="display: block; font-size: 0.675rem; color: #ea580c; font-weight: 600;">(Retur terdahulu: ${item.already_retur_qty.toLocaleString('id-ID')})</span>` 
                        : '';

                    const rowHtml = `
                        <tr id="createReturRow_${idx}" data-idx="${idx}" style="border-bottom: 1px solid #e2e8f0; background: ${isAvailable ? '#ffffff' : '#f8fafc'};">
                            <td style="padding: 0.6rem 0.65rem; text-align: center; color: #64748b;">
                                ${idx + 1}
                                <input type="hidden" name="items[${idx}][sodtl_id]" value="${item.sodtl_id}">
                                <input type="hidden" name="items[${idx}][perlakuan_cd]" id="createReturPerlakuan_${idx}" value="MASUK_PERSEDIAAN">
                                <input type="hidden" name="items[${idx}][qty_masuk_persediaan]" id="createReturQtyMasuk_${idx}" value="0">
                                <input type="hidden" name="items[${idx}][qty_dimusnahkan]" id="createReturQtyMusnah_${idx}" value="0">
                            </td>
                            <td style="padding: 0.6rem 0.65rem;">
                                <strong style="color: #0f172a; display: block; font-size: 0.825rem;">${item.barang_nm}</strong>
                                <span style="font-family: monospace; font-size: 0.7rem; color: #64748b;">${item.barang_cd}</span>
                                ${alreadyReturText}
                            </td>
                            <td style="padding: 0.6rem 0.65rem; text-align: right; font-family: monospace; color: #475569;">
                                ${item.kirim_qty.toLocaleString('id-ID')}
                            </td>
                            <td style="padding: 0.6rem 0.65rem; text-align: right; font-family: monospace; font-weight: 700; color: ${isAvailable ? '#15803d' : '#94a3b8'};">
                                ${maxRetur.toLocaleString('id-ID')}
                            </td>
                            <td style="padding: 0.6rem 0.65rem; text-align: right;">
                                ${isAvailable ? `
                                    <input type="number" 
                                           name="items[${idx}][qty_retur]" 
                                           min="0" 
                                           max="${maxRetur}" 
                                           step="1" 
                                           value="0" 
                                           data-price="${item.effective_price}" 
                                           data-idx="${idx}"
                                           oninput="recalcCreateReturRow(this)"
                                           class="form-control"
                                           style="width: 78px; text-align: right; padding: 0.3rem 0.4rem; border: 1.5px solid #cbd5e1; border-radius: 4px; font-weight: 700; font-family: monospace; font-size: 0.85rem; display: inline-block;">
                                ` : `<span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Nihil</span>`}
                            </td>
                            <td style="padding: 0.6rem 0.65rem;">
                                ${isAvailable ? `
                                    <select name="items[${idx}][kondisi_cd]" class="form-control" style="width: 100%; padding: 0.3rem 0.4rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.75rem;">
                                        <option value="RUSAK_KEMASAN">Kemasan Rusak / Sobek</option>
                                        <option value="BOCOR_KEMPES">Bocor / Kempes Udara</option>
                                        <option value="CACAT_PRODUKSI">Cacat Mutu / Gosong</option>
                                        <option value="EXPIRED">Mendekati Kadaluarsa</option>
                                        <option value="LAINNYA">Lainnya / Sortir</option>
                                    </select>
                                ` : `-`}
                            </td>
                            <td style="padding: 0.6rem 0.75rem;">
                                ${isAvailable ? `
                                    <div class="disposisi-btn-group">
                                        <button type="button" 
                                                class="disposisi-btn active-persediaan" 
                                                data-mode="MASUK_PERSEDIAAN" 
                                                onclick="setCreateRowDisposisi(${idx}, 'MASUK_PERSEDIAAN')">
                                            <span>Masuk KW 2</span>
                                        </button>
                                        <button type="button" 
                                                class="disposisi-btn" 
                                                data-mode="DIMUSNAHKAN" 
                                                onclick="setCreateRowDisposisi(${idx}, 'DIMUSNAHKAN')">
                                            <span>Dimusnahkan</span>
                                        </button>
                                        <button type="button" 
                                                class="disposisi-btn" 
                                                data-mode="CAMPUR" 
                                                onclick="setCreateRowDisposisi(${idx}, 'CAMPUR')">
                                            <span>Campur</span>
                                        </button>
                                    </div>
                                    <div id="createReturSplitBox_${idx}" class="split-qty-box" style="display: none;">
                                        <span style="font-size: 0.7rem; color: #9a3412; font-weight: 600;">KW 2:</span>
                                        <input type="number" id="createSplitMasuk_${idx}" min="0" value="0" oninput="onCreateSplitQtyChange(${idx}, 'MASUK')" class="split-qty-input">
                                        <span style="font-size: 0.7rem; color: #991b1b; font-weight: 600; margin-left: 4px;">Musnah:</span>
                                        <input type="number" id="createSplitMusnah_${idx}" min="0" value="0" oninput="onCreateSplitQtyChange(${idx}, 'MUSNAH')" class="split-qty-input">
                                    </div>
                                ` : `-`}
                            </td>
                            <td style="padding: 0.6rem 0.75rem; text-align: right; font-family: monospace; font-weight: 700; color: #0f172a;" id="createReturRowSubtotal_${idx}">
                                Rp 0
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', rowHtml);
                });
            }
        }

        if (loading) loading.style.display = 'none';
        if (formFields) formFields.style.display = 'flex';
        updateCreateReturSummaryTotals();

    } catch (err) {
        if (loading) loading.style.display = 'none';
        if (emptyPrompt) {
            emptyPrompt.style.display = 'block';
            emptyPrompt.innerHTML = `<p style="color:#dc2626; font-weight:700;">Gagal memuat: ${err.message}</p>`;
        }
    }
}

function recalcCreateReturRow(input) {
    const idx = input.getAttribute('data-idx');
    const price = parseFloat(input.getAttribute('data-price')) || 0;
    const max = parseFloat(input.getAttribute('max')) || 0;
    let val = parseFloat(input.value) || 0;

    if (val < 0) val = 0;
    if (val > max) {
        val = max;
        input.value = max;
    }

    const subtotal = Math.round(val * price);
    const subtotalEl = document.getElementById('createReturRowSubtotal_' + idx);
    if (subtotalEl) {
        subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    }

    // Sync perlakuan values
    const perlakuanInp = document.getElementById('createReturPerlakuan_' + idx);
    const mode = perlakuanInp ? perlakuanInp.value : 'MASUK_PERSEDIAAN';
    const inpMasuk = document.getElementById('createReturQtyMasuk_' + idx);
    const inpMusnah = document.getElementById('createReturQtyMusnah_' + idx);

    if (mode === 'MASUK_PERSEDIAAN') {
        if (inpMasuk) inpMasuk.value = val;
        if (inpMusnah) inpMusnah.value = 0;
    } else if (mode === 'DIMUSNAHKAN') {
        if (inpMasuk) inpMasuk.value = 0;
        if (inpMusnah) inpMusnah.value = val;
    } else if (mode === 'CAMPUR') {
        let m = parseFloat(inpMasuk ? inpMasuk.value : 0) || 0;
        if (m > val) m = val;
        if (inpMasuk) inpMasuk.value = m;
        if (inpMusnah) inpMusnah.value = Math.max(0, val - m);
    }

    updateCreateReturSummaryTotals();
}

function setCreateRowDisposisi(idx, mode) {
    const perlakuanInp = document.getElementById('createReturPerlakuan_' + idx);
    if (perlakuanInp) perlakuanInp.value = mode;

    const row = document.getElementById('createReturRow_' + idx);
    if (row) {
        row.querySelectorAll('.disposisi-btn').forEach(btn => {
            btn.classList.remove('active-persediaan', 'active-musnah', 'active-campur');
        });

        const activeBtn = row.querySelector(`.disposisi-btn[data-mode="${mode}"]`);
        if (activeBtn) {
            if (mode === 'MASUK_PERSEDIAAN') activeBtn.classList.add('active-persediaan');
            else if (mode === 'DIMUSNAHKAN') activeBtn.classList.add('active-musnah');
            else if (mode === 'CAMPUR') activeBtn.classList.add('active-campur');
        }

        const splitBox = document.getElementById('createReturSplitBox_' + idx);
        const qtyInp = row.querySelector('input[name$="[qty_retur]"]');
        const totalQty = parseFloat(qtyInp ? qtyInp.value : 0) || 0;
        const inpMasuk = document.getElementById('createReturQtyMasuk_' + idx);
        const inpMusnah = document.getElementById('createReturQtyMusnah_' + idx);

        if (mode === 'CAMPUR') {
            if (splitBox) splitBox.style.display = 'flex';
            const half = Math.floor(totalQty / 2);
            if (inpMasuk) inpMasuk.value = half;
            if (inpMusnah) inpMusnah.value = Math.max(0, totalQty - half);
        } else {
            if (splitBox) splitBox.style.display = 'none';
            if (mode === 'MASUK_PERSEDIAAN') {
                if (inpMasuk) inpMasuk.value = totalQty;
                if (inpMusnah) inpMusnah.value = 0;
            } else {
                if (inpMasuk) inpMasuk.value = 0;
                if (inpMusnah) inpMusnah.value = totalQty;
            }
        }
    }

    updateCreateReturSummaryTotals();
}

function onCreateSplitQtyChange(idx, changedField) {
    const row = document.getElementById('createReturRow_' + idx);
    if (!row) return;

    const qtyInp = row.querySelector('input[name$="[qty_retur]"]');
    const totalQty = parseFloat(qtyInp ? qtyInp.value : 0) || 0;
    const inpMasuk = document.getElementById('createReturQtyMasuk_' + idx);
    const inpMusnah = document.getElementById('createReturQtyMusnah_' + idx);

    if (changedField === 'MASUK') {
        let masuk = parseFloat(inpMasuk ? inpMasuk.value : 0) || 0;
        if (masuk < 0) masuk = 0;
        if (masuk > totalQty) masuk = totalQty;
        if (inpMasuk) inpMasuk.value = masuk;
        if (inpMusnah) inpMusnah.value = Math.max(0, totalQty - masuk);
    } else {
        let musnah = parseFloat(inpMusnah ? inpMusnah.value : 0) || 0;
        if (musnah < 0) musnah = 0;
        if (musnah > totalQty) musnah = totalQty;
        if (inpMusnah) inpMusnah.value = musnah;
        if (inpMasuk) inpMasuk.value = Math.max(0, totalQty - musnah);
    }

    updateCreateReturSummaryTotals();
}

function setAllCreateDisposisi(mode) {
    document.querySelectorAll('#modalCreateRetur tr[id^="createReturRow_"]').forEach(row => {
        const idx = row.getAttribute('data-idx');
        if (idx !== null) {
            setCreateRowDisposisi(idx, mode);
        }
    });
}

function updateCreateReturSummaryTotals() {
    let totalQty = 0;
    let totalNilai = 0;
    let totalMasuk = 0;
    let totalMusnah = 0;

    document.querySelectorAll('#modalCreateRetur input[name$="[qty_retur]"]').forEach(inp => {
        const q = parseFloat(inp.value) || 0;
        const p = parseFloat(inp.getAttribute('data-price')) || 0;
        totalQty += q;
        totalNilai += Math.round(q * p);
    });

    document.querySelectorAll('#modalCreateRetur input[name$="[qty_masuk_persediaan]"]').forEach(inp => {
        totalMasuk += (parseFloat(inp.value) || 0);
    });

    document.querySelectorAll('#modalCreateRetur input[name$="[qty_dimusnahkan]"]').forEach(inp => {
        totalMusnah += (parseFloat(inp.value) || 0);
    });

    const sumQtyEl = document.getElementById('createReturSumQty');
    if (sumQtyEl) sumQtyEl.textContent = `${totalQty.toLocaleString('id-ID')} Karton`;

    const sumMasukEl = document.getElementById('createReturSumMasuk');
    if (sumMasukEl) sumMasukEl.textContent = `${totalMasuk.toLocaleString('id-ID')} Karton`;

    const sumMusnahEl = document.getElementById('createReturSumMusnah');
    if (sumMusnahEl) sumMusnahEl.textContent = `${totalMusnah.toLocaleString('id-ID')} Karton`;

    const sumNilaiEl = document.getElementById('createReturSumNilai');
    if (sumNilaiEl) sumNilaiEl.textContent = `Rp ${totalNilai.toLocaleString('id-ID')}`;

    // Enable button submit jika totalQty > 0
    const btnSubmit = document.getElementById('btnSubmitCreateRetur');
    if (btnSubmit) {
        btnSubmit.disabled = totalQty <= 0;
    }
}

// Validation on submit form retur
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCreateRetur');
    if (form) {
        form.addEventListener('submit', function(e) {
            let totalQty = 0;
            document.querySelectorAll('#modalCreateRetur input[name$="[qty_retur]"]').forEach(inp => {
                totalQty += (parseFloat(inp.value) || 0);
            });

            if (totalQty <= 0) {
                e.preventDefault();
                alert('Silakan masukkan minimal 1 barang dengan kuantitas retur lebih dari 0.');
                return false;
            }
        });
    }
});

// Tutup menu dan modal saat klik di luar
window.addEventListener('click', function(e) {
    // Dropdown Smart Action
    if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
        document.querySelectorAll('.action-dropdown-menu').forEach(m => {
            m.style.display = 'none';
        });
        document.querySelectorAll('.btn-action-trigger').forEach(b => {
            b.classList.remove('active');
        });
    }

    // Modal Void
    const modal = document.getElementById('modalVoidReturIndex');
    if (e.target === modal) {
        closeVoidModal();
    }

    // Modal Create Retur
    const modalCreate = document.getElementById('modalCreateRetur');
    if (e.target === modalCreate) {
        closeCreateReturModal();
    }
});

window.addEventListener('scroll', function() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-action-trigger').forEach(b => {
        b.classList.remove('active');
    });
}, true);
