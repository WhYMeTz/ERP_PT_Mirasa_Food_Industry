/**
 * public/js/keuangan/pengaturan-jurnal/pengaturan-jurnal.js
 * Logika interaksi UI: Tab switcher, tombol cepat kustom variabel, dan real-time live preview.
 */

document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initQuickTagButtons();
    initLivePresetsAndTyping();
    initDirtyStateWarning();
});

/**
 * Tab switcher antar modul (HPP Produksi vs Pembelian)
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
 * Tombol Cepat Variabel: Sisipkan {variabel} ke posisi kursor input & langsung trigger preview update.
 */
function initQuickTagButtons() {
    const quickButtons = document.querySelectorAll('.quick-tag-btn');

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
