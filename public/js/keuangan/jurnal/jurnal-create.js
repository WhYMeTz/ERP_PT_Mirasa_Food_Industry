/**
 * JavaScript Logika Ergonomis & Cepat: Form Jurnal Umum Manual - ERP PT Mirasa Food Industry
 * Separation of Concerns: public/js/keuangan/jurnal/jurnal-create.js
 */

document.addEventListener('DOMContentLoaded', function () {
    initJurnalForm();
});

let currentTargetRowIndex = null;
let activeCategoryFilter = '';

function initJurnalForm() {
    const tableBody = document.getElementById('entriesTableBody');
    if (!tableBody) return;

    const rows = tableBody.querySelectorAll('tr.entry-row');
    rows.forEach(row => {
        attachRowEvents(row);
    });

    calculateTotals();

    // Keyboard global shortcut: Alt + N untuk tambah baris
    document.addEventListener('keydown', function (e) {
        if (e.altKey && (e.key === 'n' || e.key === 'N')) {
            e.preventDefault();
            addEntryRow();
        }
    });
}

/**
 * Tambah Baris Transaksi Baru
 */
window.addEntryRow = function (prefilledCoa = null, defaultSide = 'debet') {
    const tableBody = document.getElementById('entriesTableBody');
    const template = document.getElementById('entryRowTemplate');
    if (!tableBody || !template) return;

    // Ambil indeks maksimal baris
    const rows = tableBody.querySelectorAll('tr.entry-row');
    let maxIdx = -1;
    rows.forEach(r => {
        const idx = parseInt(r.getAttribute('data-row-idx') || '0', 10);
        if (idx > maxIdx) maxIdx = idx;
    });
    const newIndex = maxIdx + 1;

    let rowHtml = template.innerHTML.replace(/__INDEX__/g, newIndex);
    const tempContainer = document.createElement('tbody');
    tempContainer.innerHTML = rowHtml.trim();
    const newRow = tempContainer.querySelector('tr');

    tableBody.appendChild(newRow);
    attachRowEvents(newRow);
    updateRowNumbers();

    if (prefilledCoa) {
        setRowCoa(newRow, prefilledCoa);
    }

    calculateTotals();

    // Auto-focus ke input yang tepat
    if (!prefilledCoa) {
        const btnPick = newRow.querySelector('.btn-pick-coa');
        if (btnPick) btnPick.focus();
    } else {
        const inputTarget = defaultSide === 'kredit' ? newRow.querySelector('.input-kredit') : newRow.querySelector('.input-debet');
        if (inputTarget) inputTarget.focus();
    }

    return newRow;
};

/**
 * Hapus Baris Transaksi
 */
window.removeEntryRow = function (btn) {
    const tableBody = document.getElementById('entriesTableBody');
    const rows = tableBody.querySelectorAll('tr.entry-row');

    if (rows.length <= 2) {
        alert('Minimal harus ada 2 baris transaksi untuk jurnal berpasangan (Double Entry).');
        return;
    }

    const row = btn.closest('tr');
    if (row) {
        row.remove();
        updateRowNumbers();
        calculateTotals();
    }
};

/**
 * Event Listener di Kolom Angka & Enter-Key Navigation
 */
function attachRowEvents(row) {
    const debetInput = row.querySelector('.input-debet');
    const kreditInput = row.querySelector('.input-kredit');
    const ketInput = row.querySelector('.input-keterangan');

    if (debetInput) {
        debetInput.addEventListener('input', function () {
            if (parseFloat(this.value) > 0 && kreditInput) {
                kreditInput.value = '';
            }

            // Auto-sync nominal ke baris kedua jika transaksi 2-baris standar
            const allRows = document.querySelectorAll('tr.entry-row');
            if (allRows.length === 2 && row === allRows[0]) {
                const secondRow = allRows[1];
                const secondKredit = secondRow.querySelector('.input-kredit');
                const secondDebet = secondRow.querySelector('.input-debet');
                if (secondKredit && (!secondDebet || !secondDebet.value || parseFloat(secondDebet.value) === 0)) {
                    secondKredit.value = this.value;
                }
            }

            calculateTotals();
        });

        // Enter key di Debet -> lompat ke baris berikutnya atau tambah baris
        debetInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleEnterNavigation(row, 'debet');
            }
        });
    }

    if (kreditInput) {
        kreditInput.addEventListener('input', function () {
            if (parseFloat(this.value) > 0 && debetInput) {
                debetInput.value = '';
            }
            calculateTotals();
        });

        // Enter key di Kredit -> lompat ke baris berikutnya atau tambah baris
        kreditInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleEnterNavigation(row, 'kredit');
            }
        });
    }

    if (ketInput) {
        ketInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (debetInput) debetInput.focus();
            }
        });
    }
}

/**
 * Navigasi Cepat Menggunakan Tombol Enter
 */
function handleEnterNavigation(currentRow, currentSide) {
    const nextRow = currentRow.nextElementSibling;
    if (nextRow && nextRow.classList.contains('entry-row')) {
        const nextTarget = currentSide === 'debet' ? nextRow.querySelector('.input-kredit') : nextRow.querySelector('.input-debet');
        if (nextTarget) nextTarget.focus();
    } else {
        // Jika baris terakhir dan belum balance, otomatis tambah baris baru
        const selisih = getUnbalancedDifference();
        if (selisih > 0) {
            const addedRow = addEntryRow(null, currentSide === 'debet' ? 'kredit' : 'debet');
            // Buka modal picker akun untuk baris baru
            const newIdx = addedRow.getAttribute('data-row-idx');
            openCoaPickerForIndex(newIdx);
        }
    }
}

/**
 * Helper Auto Balance: Isi otomatis selisih ke baris ini
 */
window.balanceThisRow = function (rowIdx) {
    const row = document.querySelector(`tr.entry-row[data-row-idx="${rowIdx}"]`);
    if (!row) return;

    const debetInput = row.querySelector('.input-debet');
    const kreditInput = row.querySelector('.input-kredit');

    // Hitung total di luar baris ini
    const allRows = document.querySelectorAll('tr.entry-row');
    let otherDebet = 0;
    let otherKredit = 0;

    allRows.forEach(r => {
        if (r !== row) {
            const d = parseFloat(r.querySelector('.input-debet')?.value || 0);
            const k = parseFloat(r.querySelector('.input-kredit')?.value || 0);
            otherDebet += isNaN(d) ? 0 : d;
            otherKredit += isNaN(k) ? 0 : k;
        }
    });

    const diff = otherDebet - otherKredit;

    if (diff > 0) {
        // Debet lebih besar -> baris ini harus Kredit
        if (kreditInput) kreditInput.value = Math.round(diff * 100) / 100;
        if (debetInput) debetInput.value = '';
    } else if (diff < 0) {
        // Kredit lebih besar -> baris ini harus Debet
        if (debetInput) debetInput.value = Math.round(Math.abs(diff) * 100) / 100;
        if (kreditInput) kreditInput.value = '';
    }

    calculateTotals();
};

/**
 * Update Nomor Urut
 */
function updateRowNumbers() {
    const tableBody = document.getElementById('entriesTableBody');
    if (!tableBody) return;
    const rows = tableBody.querySelectorAll('tr.entry-row');
    rows.forEach((row, idx) => {
        const numSpan = row.querySelector('.row-number');
        if (numSpan) numSpan.textContent = idx + 1;

        const btnRemove = row.querySelector('.btn-remove-row');
        if (btnRemove) {
            btnRemove.disabled = (rows.length <= 2);
        }
    });
}

/**
 * Ambil Selisih
 */
function getUnbalancedDifference() {
    const allRows = document.querySelectorAll('tr.entry-row');
    let totalD = 0;
    let totalK = 0;
    allRows.forEach(r => {
        const d = parseFloat(r.querySelector('.input-debet')?.value || 0);
        const k = parseFloat(r.querySelector('.input-kredit')?.value || 0);
        totalD += isNaN(d) ? 0 : d;
        totalK += isNaN(k) ? 0 : k;
    });
    return Math.abs(totalD - totalK);
}

/**
 * Hitung Total & Status Balance
 */
function calculateTotals() {
    const tableBody = document.getElementById('entriesTableBody');
    if (!tableBody) return;

    const rows = tableBody.querySelectorAll('tr.entry-row');
    let totalDebet = 0;
    let totalKredit = 0;

    rows.forEach(row => {
        const debetVal = parseFloat(row.querySelector('.input-debet')?.value || 0);
        const kreditVal = parseFloat(row.querySelector('.input-kredit')?.value || 0);

        totalDebet += isNaN(debetVal) ? 0 : debetVal;
        totalKredit += isNaN(kreditVal) ? 0 : kreditVal;
    });

    const selisih = Math.abs(totalDebet - totalKredit);
    const isBalanced = (selisih < 0.01) && (totalDebet > 0);

    // Update Elements
    const lblTotalDebet = document.getElementById('lblTotalDebet');
    const lblTotalKredit = document.getElementById('lblTotalKredit');
    const stickyDebet = document.getElementById('stickyTotalDebet');
    const stickyKredit = document.getElementById('stickyTotalKredit');
    const stickySelisih = document.getElementById('stickySelisih');
    const stickyBar = document.getElementById('bottomStickyBar');
    const stickyBadge = document.getElementById('stickyBalanceBadge');
    const btnSubmit = document.getElementById('btnSubmitJurnal');

    const strDebet = formatRupiah(totalDebet);
    const strKredit = formatRupiah(totalKredit);
    const strSelisih = formatRupiah(selisih);

    if (lblTotalDebet) lblTotalDebet.textContent = strDebet;
    if (lblTotalKredit) lblTotalKredit.textContent = strKredit;
    if (stickyDebet) stickyDebet.textContent = strDebet;
    if (stickyKredit) stickyKredit.textContent = strKredit;
    if (stickySelisih) stickySelisih.textContent = strSelisih;

    if (stickyBar && stickyBadge) {
        if (isBalanced) {
            stickyBar.className = 'bottom-sticky-bar balanced';
            stickyBadge.className = 'balance-status-badge badge-balanced';
            stickyBadge.innerHTML = `
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>100% Balanced</span>
            `;
            if (btnSubmit) btnSubmit.disabled = false;
        } else {
            stickyBar.className = 'bottom-sticky-bar unbalanced';
            stickyBadge.className = 'balance-status-badge badge-unbalanced';
            stickyBadge.innerHTML = `
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>${totalDebet === 0 && totalKredit === 0 ? 'Belum Diisi' : 'Unbalanced'}</span>
            `;
            if (btnSubmit) btnSubmit.disabled = true;
        }
    }
}

/**
 * ========================================================
 * MODAL QUICK PICKER COA LOGIC
 * ========================================================
 */
window.openCoaPickerForIndex = function (rowIdx) {
    currentTargetRowIndex = rowIdx;
    const modal = document.getElementById('modalCoaPicker');
    const searchInput = document.getElementById('modalCoaSearchInput');

    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (searchInput) {
        searchInput.value = '';
        setTimeout(() => searchInput.focus(), 100);
    }

    // Reset filter
    activeCategoryFilter = '';
    const pills = document.querySelectorAll('.coa-filter-pill');
    pills.forEach((p, i) => {
        if (i === 0) p.classList.add('active');
        else p.classList.remove('active');
    });

    filterCoaPickerModal();
};

window.closeCoaPickerModal = function () {
    const modal = document.getElementById('modalCoaPicker');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
    currentTargetRowIndex = null;
};

window.filterCoaCategory = function (category, btnElement) {
    activeCategoryFilter = category.toUpperCase();
    const pills = document.querySelectorAll('.coa-filter-pill');
    pills.forEach(p => p.classList.remove('active'));
    if (btnElement) btnElement.classList.add('active');
    filterCoaPickerModal();
};

window.filterCoaPickerModal = function () {
    const query = (document.getElementById('modalCoaSearchInput')?.value || '').trim().toLowerCase();
    const items = document.querySelectorAll('.coa-picker-item, .coa-pair-item');
    let visibleCount = 0;

    items.forEach(item => {
        const text = item.getAttribute('data-search') || '';
        const cat = item.getAttribute('data-kategori') || '';

        const matchQuery = !query || text.includes(query);
        const matchCat = !activeCategoryFilter || cat === activeCategoryFilter;

        if (matchQuery && matchCat) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const emptyMsg = document.getElementById('coaEmptySearch');
    if (emptyMsg) {
        emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
};

window.selectCoaFromModal = function (item) {
    if (currentTargetRowIndex === null) return;

    const row = document.querySelector(`tr.entry-row[data-row-idx="${currentTargetRowIndex}"]`);
    if (!row) return;

    const coaData = {
        id: item.getAttribute('data-id'),
        kode: item.getAttribute('data-kode'),
        nama: item.getAttribute('data-nama') || item.querySelector('strong')?.textContent?.trim() || '',
        kategori: item.getAttribute('data-kategori')
    };

    setRowCoa(row, coaData);

    // Auto-pairing pintar: Jika memilih akun di Baris 1 dan Baris 2 masih kosong, otomatis isi Baris 2 dengan pasangan alaminya
    const allRows = document.querySelectorAll('tr.entry-row');
    if (allRows.length >= 2 && row === allRows[0]) {
        const secondRow = allRows[1];
        const secondCoaInput = secondRow.querySelector('.input-coa-id');
        if (secondCoaInput && !secondCoaInput.value) {
            let defaultCounterpartCode = null;
            const code = String(coaData.kode || '');

            if (code.startsWith('6')) {
                // Beban Operasional -> Kas Operasional
                defaultCounterpartCode = '1104';
            } else if (code === '1150' || code === '1151') {
                // Persediaan -> Hutang Usaha
                defaultCounterpartCode = '2100';
            } else if (code.startsWith('4')) {
                // Pendapatan -> Piutang Usaha
                defaultCounterpartCode = '1120';
            } else if (code === '1104') {
                // Kas Operasional -> Bank BCA (Tarik Tunai)
                defaultCounterpartCode = '1103';
            } else if (code === '2100') {
                // Hutang Usaha -> Bank BCA
                defaultCounterpartCode = '1103';
            }

            if (defaultCounterpartCode) {
                const allCoaItems = Array.from(document.querySelectorAll('.coa-picker-item[data-id]'));
                const counterpartItem = allCoaItems.find(it => it.getAttribute('data-kode') === defaultCounterpartCode);
                if (counterpartItem) {
                    setRowCoa(secondRow, {
                        id: counterpartItem.getAttribute('data-id'),
                        kode: counterpartItem.getAttribute('data-kode'),
                        nama: counterpartItem.getAttribute('data-nama') || counterpartItem.querySelector('strong')?.textContent?.trim() || '',
                        kategori: counterpartItem.getAttribute('data-kategori')
                    });
                }
            }
        }
    }

    closeCoaPickerModal();

    // Focus ke kolom nominal
    const inputDebet = row.querySelector('.input-debet');
    const inputKredit = row.querySelector('.input-kredit');

    // Jika baris pertama, default ke debet. Jika baris kedua dan debet baris 1 sudah ada isinya, auto-balance ke kredit
    const isRow1 = row.previousElementSibling === null || !row.previousElementSibling.classList.contains('entry-row');
    if (isRow1) {
        if (inputDebet) inputDebet.focus();
    } else {
        const selisih = getUnbalancedDifference();
        if (selisih > 0 && !inputKredit.value && !inputDebet.value) {
            balanceThisRow(currentTargetRowIndex);
            if (inputKredit) inputKredit.focus();
        } else {
            if (inputDebet) inputDebet.focus();
        }
    }
};

/**
 * Handler Pemilihan dari Elemen DOM Pasangan Cepat
 */
window.selectCoaPairItem = function (el) {
    if (!el) return;
    const dId = el.getAttribute('data-did');
    const dCode = el.getAttribute('data-dcode');
    const dName = el.getAttribute('data-dname') || '';
    const kId = el.getAttribute('data-kid');
    const kCode = el.getAttribute('data-kcode');
    const kName = el.getAttribute('data-kname') || '';
    const desc = el.getAttribute('data-desc') || '';

    window.selectCoaPairDirect(dId, dCode, dName, kId, kCode, kName, desc);
};

/**
 * Pemilihan Paket Pasangan Cepat Langsung (Dengan Data Lengkap)
 */
window.selectCoaPairDirect = function (dId, dCode, dName, kId, kCode, kName, defaultDesc) {
    const tableBody = document.getElementById('entriesTableBody');
    if (!tableBody) return;

    let rows = tableBody.querySelectorAll('tr.entry-row');
    while (rows.length < 2) {
        addEntryRow();
        rows = tableBody.querySelectorAll('tr.entry-row');
    }

    // Isi Baris 1 (Debet)
    setRowCoa(rows[0], {
        id: dId,
        kode: dCode,
        nama: dName,
        kategori: 'DEBET'
    });

    // Isi Baris 2 (Kredit)
    setRowCoa(rows[1], {
        id: kId,
        kode: kCode,
        nama: kName,
        kategori: 'KREDIT'
    });

    // Isi Keterangan Header jika masih kosong
    const inputKet = document.getElementById('keterangan');
    if (inputKet && (!inputKet.value || inputKet.value.trim() === '')) {
        inputKet.value = defaultDesc;
    }

    closeCoaPickerModal();
    calculateTotals();

    // Auto-focus ke kolom Debet Baris 1
    const debetRow1 = rows[0].querySelector('.input-debet');
    if (debetRow1) debetRow1.focus();
};

/**
 * Pemilihan Paket Pasangan Cepat (Fallback)
 */
window.selectCoaPairFromModal = function (debetCode, kreditCode, defaultDesc) {
    const allCoaItems = Array.from(document.querySelectorAll('.coa-picker-item[data-id]'));
    const itemD = allCoaItems.find(it => it.getAttribute('data-kode') === String(debetCode));
    const itemK = allCoaItems.find(it => it.getAttribute('data-kode') === String(kreditCode));

    if (itemD && itemK) {
        window.selectCoaPairDirect(
            itemD.getAttribute('data-id'),
            debetCode,
            itemD.getAttribute('data-nama') || itemD.querySelector('strong')?.textContent?.trim() || '',
            itemK.getAttribute('data-id'),
            kreditCode,
            itemK.getAttribute('data-nama') || itemK.querySelector('strong')?.textContent?.trim() || '',
            defaultDesc
        );
    }
};

function setRowCoa(row, coaData) {
    const inputId = row.querySelector('.input-coa-id');
    const codeSpan = row.querySelector('.coa-display-code');
    const nameSpan = row.querySelector('.coa-display-name');

    if (inputId) inputId.value = coaData.id;
    if (codeSpan) {
        codeSpan.textContent = coaData.kode;
        codeSpan.style.display = 'inline-block';
    }
    if (nameSpan) {
        nameSpan.textContent = coaData.nama;
        nameSpan.style.color = '#0f172a';
    }
}

/**
 * ========================================================
 * TEMPLATE PRESETS (UPAH KEMAS, LISTRIK, PEMELIHARAAN)
 * ========================================================
 */
window.applyQuickTemplate = function (templateKey) {
    const presets = {
        'upah_kemas': {
            keterangan: 'Pembayaran upah tenaga kerja kemas & packing keripik singkong',
            tipe: 'JURNAL_UMUM',
            refPrefix: 'UPAH-KEMAS',
            lines: [
                { match: ['6101', 'GAJI'], side: 'debet', ket: 'Beban Upah Tenaga Kerja Kemas' },
                { match: ['1110', 'KAS', 'BANK'], side: 'kredit', ket: 'Kas Pabrik / Kas Kecil' }
            ]
        },
        'listrik_air': {
            keterangan: 'Pembayaran tagihan listrik PLN & utilitas pabrik',
            tipe: 'JURNAL_UMUM',
            refPrefix: 'PLN',
            lines: [
                { match: ['6102', 'LISTRIK'], side: 'debet', ket: 'Beban Air & Listrik Pabrik' },
                { match: ['1120', '1110', 'BANK', 'KAS'], side: 'kredit', ket: 'Pembayaran Rekening Bank / Kas' }
            ]
        },
        'servis_mesin': {
            keterangan: 'Biaya servis pemeliharaan mesin fryer & penggantian sparepart',
            tipe: 'JURNAL_UMUM',
            refPrefix: 'SRV',
            lines: [
                { match: ['6106', 'PEMELIHARAAN'], side: 'debet', ket: 'Beban Pemeliharaan Mesin' },
                { match: ['1110', 'KAS', 'BANK'], side: 'kredit', ket: 'Kas Pabrik' }
            ]
        },
        'tarik_tunai': {
            keterangan: 'Tarik tunai dari Bank BCA untuk pengisian Kas Kecil (Petty Cash)',
            tipe: 'JURNAL_UMUM',
            refPrefix: 'TRX',
            lines: [
                { match: ['1110', 'KAS'], side: 'debet', ket: 'Kas Pabrik / Kas Kecil' },
                { match: ['1120', 'BANK', 'BCA'], side: 'kredit', ket: 'Rekening Bank BCA' }
            ]
        }
    };

    const tpl = presets[templateKey];
    if (!tpl) return;

    // Isi Header
    const inputKet = document.getElementById('keterangan');
    const inputTipe = document.getElementById('tipe_transaksi');
    const inputRef = document.getElementById('ref_no');

    if (inputKet) inputKet.value = tpl.keterangan;
    if (inputTipe) inputTipe.value = tpl.tipe;
    if (inputRef && !inputRef.value) {
        inputRef.value = tpl.refPrefix + '-' + new Date().toISOString().slice(2, 10).replace(/-/g, '');
    }

    // Reset dan Buat Baris Jurnal Sesuai Template
    const tableBody = document.getElementById('entriesTableBody');
    if (!tableBody) return;
    tableBody.innerHTML = '';

    const allCoaItems = Array.from(document.querySelectorAll('.coa-picker-item'));

    tpl.lines.forEach((lineTpl, idx) => {
        // Cari akun COA yang cocok
        let matchedCoa = null;
        for (const kw of lineTpl.match) {
            matchedCoa = allCoaItems.find(item => {
                const search = item.getAttribute('data-search') || '';
                return search.includes(kw.toLowerCase());
            });
            if (matchedCoa) break;
        }

        const coaData = matchedCoa ? {
            id: matchedCoa.getAttribute('data-id'),
            kode: matchedCoa.getAttribute('data-kode'),
            nama: matchedCoa.getAttribute('data-nama'),
            kategori: matchedCoa.getAttribute('data-kategori')
        } : null;

        const row = addEntryRow(coaData, lineTpl.side);
        const ketField = row.querySelector('.input-keterangan');
        if (ketField) ketField.value = lineTpl.ket;
    });

    calculateTotals();

    // Focus ke baris pertama debet
    const firstDebet = tableBody.querySelector('.input-debet');
    if (firstDebet) firstDebet.focus();
};

// Keyboard ESC untuk modal picker
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modalCoaPicker');
        if (modal && modal.style.display !== 'none') {
            closeCoaPickerModal();
        }
    }
});

function formatRupiah(nominal) {
    return 'Rp ' + Math.round(nominal).toLocaleString('id-ID');
}
