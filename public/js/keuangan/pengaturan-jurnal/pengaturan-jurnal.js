/**
 * public/js/keuangan/pengaturan-jurnal/pengaturan-jurnal.js
 * Modul Pemetaan Akun Jurnal Otomatis - Refined Enterprise 3-Tone
 * Tabs, live template typing, instant voucher updates, dan Smart Modal COA Picker.
 */

document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initQuickTagButtons();
    initLivePresetsAndTyping();
    initPresetCardStyles();
    initDirtyStateWarning();
    initDragAndDropReorder();
});

/**
 * Tab Switcher antar modul (1. HPP WIP, 2. HPP FG, 3. Pembelian & Hutang AP)
 */
function initTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.tab;

            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.classList.add('active');
            }
        });
    });
}

/**
 * Visual Active Styling untuk Kartu Radio Preset
 */
function initPresetCardStyles() {
    const radioGroups = ['preset_hpp', 'preset_fg', 'preset_ap'];

    radioGroups.forEach(groupName => {
        const radios = document.querySelectorAll(`input[name="${groupName}"]`);
        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                radios.forEach(r => {
                    const cardLabel = r.closest('.preset-card-option');
                    if (cardLabel) {
                        cardLabel.classList.toggle('selected', r.checked);
                    }
                });
            });
        });
    });
}

/**
 * Tombol Cepat Variabel: Sisipkan {variabel} ke posisi kursor input & langsung trigger preview update.
 */
function initQuickTagButtons() {
    const quickButtons = document.querySelectorAll('.quick-chip');

    quickButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const tag = this.dataset.tag;
            const targetInputId = this.dataset.target;
            const inputEl = document.getElementById(targetInputId);

            if (!inputEl || !tag) return;

            const startPos = inputEl.selectionStart ?? inputEl.value.length;
            const endPos = inputEl.selectionEnd ?? inputEl.value.length;
            const currentVal = inputEl.value;

            inputEl.value = currentVal.substring(0, startPos) + tag + currentVal.substring(endPos);
            inputEl.focus();

            const newPos = startPos + tag.length;
            inputEl.setSelectionRange(newPos, newPos);

            // Trigger event input agar real-time preview langsung ter-update
            inputEl.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
}

/**
 * Real-time Live Typing & Preset Synchronization
 */
function initLivePresetsAndTyping() {
    // 1. MODUL HPP WIP
    const hppInput = document.getElementById('setting_HPP_WIP_TEMPLATE_KET');
    const hppPreview = document.getElementById('preview_HPP_WIP_TEMPLATE_KET');
    const hppCustomBox = document.getElementById('customBox_HPP_WIP');
    const hppRadios = document.querySelectorAll('input[name="preset_hpp"]');

    const mockHpp = {
        tgl: '16/11/2026',
        shift: 'A',
        total_kg: '300,00',
        lini: 'PRODUKSI IFM'
    };

    const renderHppPreview = () => {
        if (!hppInput || !hppPreview) return;
        let text = hppInput.value;
        Object.entries(mockHpp).forEach(([k, v]) => {
            text = text.replaceAll(`{${k}}`, v);
        });
        hppPreview.textContent = text.trim() || '(Keterangan jurnal kosong)';
    };

    hppRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'CUSTOM') {
                if (hppCustomBox) hppCustomBox.style.display = 'block';
                if (hppInput) {
                    hppInput.focus();
                    renderHppPreview();
                }
            } else {
                if (hppCustomBox) hppCustomBox.style.display = 'none';
                if (hppInput) {
                    hppInput.value = this.value;
                    renderHppPreview();
                }
            }
        });
    });

    if (hppInput) {
        hppInput.addEventListener('input', renderHppPreview);
        hppInput.addEventListener('keyup', renderHppPreview);
        hppInput.addEventListener('change', renderHppPreview);
        renderHppPreview();
    }

    // 2. MODUL HPP PENGEMASAN / BARANG JADI (FG)
    const fgInput = document.getElementById('setting_HPP_FG_TEMPLATE_KET');
    const fgPreview = document.getElementById('preview_HPP_FG_TEMPLATE_KET');
    const fgCustomBox = document.getElementById('customBox_HPP_FG');
    const fgRadios = document.querySelectorAll('input[name="preset_fg"]');

    const mockFg = {
        tgl: '16/11/2026',
        barang_jadi: 'Keripik Singkong Balado 250gr',
        hasil_karton: '150',
        hasil_pcs: '6.000',
        batch_no: 'BCH-202611-01'
    };

    const renderFgPreview = () => {
        if (!fgInput || !fgPreview) return;
        let text = fgInput.value;
        Object.entries(mockFg).forEach(([k, v]) => {
            text = text.replaceAll(`{${k}}`, v);
        });
        fgPreview.textContent = text.trim() || '(Keterangan jurnal kosong)';
    };

    fgRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'CUSTOM') {
                if (fgCustomBox) fgCustomBox.style.display = 'block';
                if (fgInput) {
                    fgInput.focus();
                    renderFgPreview();
                }
            } else {
                if (fgCustomBox) fgCustomBox.style.display = 'none';
                if (fgInput) {
                    fgInput.value = this.value;
                    renderFgPreview();
                }
            }
        });
    });

    if (fgInput) {
        fgInput.addEventListener('input', renderFgPreview);
        fgInput.addEventListener('keyup', renderFgPreview);
        fgInput.addEventListener('change', renderFgPreview);
        renderFgPreview();
    }

    // 3. MODUL PEMBELIAN / HUTANG USAHA (AP)
    const apInput = document.getElementById('setting_PEMBELIAN_TEMPLATE_KET');
    const apPreview = document.getElementById('preview_PEMBELIAN_TEMPLATE_KET');
    const apCustomBox = document.getElementById('customBox_PEMBELIAN');
    const apRadios = document.querySelectorAll('input[name="preset_ap"]');

    const mockAp = {
        supplier: 'PT Singkong Lestari Makmur',
        qty: '5.000',
        satuan: 'kg',
        nama_barang: 'Singkong Basah Super',
        harga: '2.400',
        no_faktur: 'INV-202611-09'
    };

    const renderApPreview = () => {
        if (!apInput || !apPreview) return;
        let text = apInput.value;
        Object.entries(mockAp).forEach(([k, v]) => {
            text = text.replaceAll(`{${k}}`, v);
        });
        apPreview.textContent = text.trim() || '(Keterangan jurnal kosong)';
    };

    apRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'CUSTOM') {
                if (apCustomBox) apCustomBox.style.display = 'block';
                if (apInput) {
                    apInput.focus();
                    renderApPreview();
                }
            } else {
                if (apCustomBox) apCustomBox.style.display = 'none';
                if (apInput) {
                    apInput.value = this.value;
                    renderApPreview();
                }
            }
        });
    });

    if (apInput) {
        apInput.addEventListener('input', renderApPreview);
        apInput.addEventListener('keyup', renderApPreview);
        apInput.addEventListener('change', renderApPreview);
        renderApPreview();
    }
}

/**
 * Peringatan jika pengguna meninggalkan halaman saat ada perubahan yang belum disimpan
 */
function initDirtyStateWarning() {
    const form = document.getElementById('formPengaturanJurnal');
    if (!form) return;

    let isDirty = false;
    let isSubmitting = false;

    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('change', () => isDirty = true);
        el.addEventListener('input', () => isDirty = true);
    });

    form.addEventListener('submit', () => {
        isSubmitting = true;
    });

    window.addEventListener('beforeunload', (e) => {
        if (isDirty && !isSubmitting) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
}

/**
 * ==========================================================================
 * SMART MODAL COA PICKER (MACOS WINDOW)
 * ==========================================================================
 */
let currentPickerKunci = null;
let currentPickerCat = '';

window.openCoaModalPicker = function (kunci, label) {
    currentPickerKunci = kunci;
    const modal = document.getElementById('modalCoaPicker');
    const labelEl = document.getElementById('modalTargetLabel');
    const searchInput = document.getElementById('modalCoaSearchInput');
    const currentVal = document.getElementById('input_' + kunci)?.value;

    if (labelEl) labelEl.textContent = label;
    if (modal) modal.classList.add('show');

    // Highlight item yang terpilih saat ini
    document.querySelectorAll('.coa-picker-item').forEach(item => {
        item.classList.toggle('selected', String(item.dataset.id) === String(currentVal));
    });

    // Reset pencarian & kategori
    if (searchInput) searchInput.value = '';
    currentPickerCat = '';
    document.querySelectorAll('.coa-filter-pill').forEach((pill, idx) => {
        pill.classList.toggle('active', idx === 0);
    });

    window.filterCoaPickerModal();

    setTimeout(() => {
        if (searchInput) searchInput.focus();
    }, 50);
};

window.closeCoaPickerModal = function () {
    const modal = document.getElementById('modalCoaPicker');
    if (modal) modal.classList.remove('show');
    currentPickerKunci = null;
};

window.filterCoaPickerModal = function () {
    const keyword = (document.getElementById('modalCoaSearchInput')?.value || '').toLowerCase().trim();
    const items = document.querySelectorAll('.coa-picker-item');
    const emptyEl = document.getElementById('coaEmptySearch');
    let visibleCount = 0;

    items.forEach(item => {
        const itemCat = item.dataset.kategori;
        const searchStr = item.dataset.search;

        const catMatches = !currentPickerCat || itemCat === currentPickerCat;
        const textMatches = !keyword || searchStr.includes(keyword);

        if (catMatches && textMatches) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    if (emptyEl) {
        emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
    }
};

window.filterCoaCategory = function (cat, btn) {
    currentPickerCat = cat;
    document.querySelectorAll('.coa-filter-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    window.filterCoaPickerModal();
    const searchInput = document.getElementById('modalCoaSearchInput');
    if (searchInput) searchInput.focus();
};

window.selectCoaFromModal = function (itemEl) {
    if (!currentPickerKunci) return;

    const coaId = itemEl.dataset.id;
    const kode = itemEl.dataset.kode;
    const nama = itemEl.dataset.nama;

    const inputEl = document.getElementById('input_' + currentPickerKunci);
    const badgeEl = document.getElementById('badge_' + currentPickerKunci);
    const nameEl = document.getElementById('name_' + currentPickerKunci);
    const btnEl = inputEl?.parentElement?.querySelector('.btn-pick-coa');

    // Juga update baris di voucher slip secara otomatis
    const slipBadgeEl = document.getElementById('slip_badge_' + currentPickerKunci);
    const slipNameEl = document.getElementById('slip_name_' + currentPickerKunci);

    if (inputEl) {
        inputEl.value = coaId;
        inputEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (badgeEl) badgeEl.textContent = kode;
    if (nameEl) nameEl.textContent = nama;
    if (slipBadgeEl) slipBadgeEl.textContent = kode;
    if (slipNameEl) slipNameEl.textContent = nama;

    if (btnEl) {
        btnEl.classList.add('changed');
    }

    window.closeCoaPickerModal();
};

/**
 * ==========================================================================
 * FITUR REORDERING / PENGGESERAN POSISI TEMPLATE JURNAL
 * ==========================================================================
 */

/**
 * Geser Baris Aturan ke Atas (-1) atau ke Bawah (+1).
 * Langsung memperbarui nomor urut, nilai input hidden, dan sinkronkan tabel voucher slip di panel kanan.
 */
window.moveRuleRow = function (btn, direction) {
    const row = btn.closest('.rule-row-box');
    if (!row) return;

    const wrap = row.parentElement;
    if (!wrap) return;

    if (direction === -1) {
        // Pindah ke Atas
        const prev = row.previousElementSibling;
        if (prev && prev.classList.contains('rule-row-box')) {
            wrap.insertBefore(row, prev);
            flashRowHighlight(row);
            syncRowPositions(wrap);
        }
    } else if (direction === 1) {
        // Pindah ke Bawah
        const next = row.nextElementSibling;
        if (next && next.classList.contains('rule-row-box')) {
            wrap.insertBefore(next, row);
            flashRowHighlight(row);
            syncRowPositions(wrap);
        }
    }
};

/**
 * Beri efek animasi halus saat baris berhasil digeser
 */
function flashRowHighlight(row) {
    row.classList.remove('row-reordered-flash');
    void row.offsetWidth; // Trigger reflow
    row.classList.add('row-reordered-flash');
}

/**
 * Sinkronkan nomor urutan (#1, #2, dll), state tombol Up/Down,
 * dan urutan baris di tabel slip voucher jurnal sebelah kanan.
 */
function syncRowPositions(wrap) {
    const rows = wrap.querySelectorAll('.rule-row-box');
    const tabContent = wrap.closest('.tab-content');
    let voucherTbody = null;

    if (tabContent) {
        voucherTbody = tabContent.querySelector('.voucher-ledger-table tbody');
    }

    rows.forEach((r, idx) => {
        const seqNum = idx + 1;
        const seqBadge = r.querySelector('.row-seq-badge');
        const urutanInput = r.querySelector('.input-row-urutan');
        const btnUp = r.querySelector('.btn-up');
        const btnDown = r.querySelector('.btn-down');
        const rowKunci = r.dataset.kunci;

        // Perbarui badge tampilan nomor urut
        if (seqBadge) {
            seqBadge.textContent = '#' + seqNum;
        }

        // Perbarui value input hidden yang dikirim saat submit form
        if (urutanInput) {
            urutanInput.value = seqNum;
            urutanInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Disable / enable tombol navigasi Up dan Down
        if (btnUp) btnUp.disabled = (idx === 0);
        if (btnDown) btnDown.disabled = (idx === rows.length - 1);

        // Sinkronkan urutan baris di tabel simulasi voucher slip sebelah kanan
        if (voucherTbody && rowKunci) {
            const slipRow = voucherTbody.querySelector(`tr[data-slip-kunci="${rowKunci}"]`);
            if (slipRow) {
                voucherTbody.appendChild(slipRow);
            }
        }
    });
}

/**
 * Inisialisasi Drag & Drop untuk Penggeseran Baris yang Fleksibel
 */
function initDragAndDropReorder() {
    const wraps = document.querySelectorAll('.rules-rows-wrap');

    wraps.forEach(wrap => {
        let draggedRow = null;

        wrap.querySelectorAll('.rule-row-box').forEach(row => {
            row.addEventListener('dragstart', function (e) {
                draggedRow = this;
                this.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', this.dataset.kunci || '');
            });

            row.addEventListener('dragend', function () {
                this.classList.remove('is-dragging');
                draggedRow = null;
                syncRowPositions(wrap);
            });

            row.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                const target = e.target.closest('.rule-row-box');
                if (target && target !== draggedRow && target.parentElement === wrap) {
                    const rect = target.getBoundingClientRect();
                    const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
                    wrap.insertBefore(draggedRow, next ? target.nextSibling : target);
                }
            });
        });
    });
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        window.closeCoaPickerModal();
    }
});
